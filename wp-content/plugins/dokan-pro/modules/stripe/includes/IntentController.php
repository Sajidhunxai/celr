<?php

namespace WeDevs\DokanPro\Modules\Stripe;

use Exception;
use Stripe\Charge;
use Stripe\SetupIntent;
use WeDevs\DokanPro\Modules\Stripe\Helper;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\Stripe\DokanStripe;
use WeDevs\DokanPro\Modules\Stripe\Abstracts\StripePaymentGateway;

defined( 'ABSPATH' ) || exit;

class IntentController extends StripePaymentGateway {

    /**
     * Constructor method
     *
     * @since 3.0.3
     */
    public function __construct() {
        Helper::bootstrap_stripe();
        $this->hooks();
    }

    /**
     * Hooks
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function hooks() {
        add_action( 'wc_ajax_dokan_stripe_verify_intent', [ $this, 'verify_intent' ] );
        add_action( 'wc_ajax_dokan_stripe_create_setup_intent', [ $this, 'create_setup_intent' ] );
        add_action( 'dokan_stripe_payment_completed', [ $this, 'process_vendor_payment' ], 10, 2 );
    }

    /**
     * Loads the order from the current request.
     *
     * @return \WC_Order
     * @throws DokanException An exception if there is no order key or the order does not exist.
     *
     * @since 3.0.3
     */
    protected function get_order_from_request() {
        /*
        if ( ! isset( $_GET['nonce'] ) || ! wp_verify_nonce( $_GET['nonce'], 'dokan_stripe_confirm_pi' ) ) {
            throw new DokanException( 'missing-nonce', __( 'CSRF verification failed.', 'dokan' ) );
        }
        */

        $order_id = null;

        if ( isset( $_GET['order'] ) ) {
            $order_id = absint( wp_unslash( $_GET['order'] ) );
        }

        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            throw new DokanException( 'missing-order', __( 'Missing order ID for payment confirmation', 'dokan' ) );
        }

        if ( ! isset( $_GET['key'] ) || $order->get_order_key() !== sanitize_text_field( wp_unslash( $_GET['key'] ) ) ) {
            throw new DokanException( 'missing-order-key', __( 'Invalid order id. Please try again.', 'dokan' ) );
        }

        return $order;
    }

    /**
     * Handles successful PaymentIntent authentications.
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function verify_intent() {
        global $woocommerce;

        try {
            $order = $this->get_order_from_request();
        } catch ( Exception $e ) {
            $message = sprintf( __( 'Payment verification error: %s', 'dokan' ), $e->getMessage() );

            wc_add_notice( esc_html( $message ), 'error' );

            $redirect_url = $woocommerce->cart->is_empty()
                ? get_permalink( wc_get_page_id( 'shop' ) )
                : wc_get_checkout_url();

            $this->handle_error( '[Stripe Connect] Error getting order from intent request.', $e, $redirect_url );
        }

        try {
            $this->verify_intent_after_checkout( $order );

            if ( ! isset( $_GET['is_ajax'] ) ) {
                $redirect_url = isset( $_GET['redirect_to'] ) // wpcs: csrf ok.
                    ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) ) // wpcs: csrf ok.
                    : $this->get_return_url( $order );

                wp_safe_redirect( $redirect_url );
            }
            exit;
        } catch ( Exception $e ) {
            $this->handle_error( '[Stripe Connect] Error verifying intent after checkout.', $e, $this->get_return_url( $order ) );
        }
    }

    /**
     * Handles exceptions during intent verification.
     *
     * @since 3.0.3
     *
     * @param DokanException $e
     * @param string $redirect_url An URL to use if a redirect is needed.
     */
    protected function handle_error( $message, $e, $redirect_url ) {
        // Log the exception before redirecting.
        if ( $e instanceof DokanException ) {
            $errors = [
                'code'    => $e->get_error_code(),
                'message' => $e->get_message(),
            ];
        } else {
            $errors = [
                'message' => $e->getMessage(),
            ];
        }

        dokan_log( "$message\n" . print_r( $errors, true ), 'error' );

        // `is_ajax` is only used for PI error reporting, a response is not expected.
        if ( isset( $_GET['is_ajax'] ) ) {
            exit;
        }

        wp_safe_redirect( $redirect_url );
        exit;
    }

    /**
     * Executed between the "Checkout" and "Thank you" pages, this
     * method updates orders based on the status of associated PaymentIntents.
     *
     * @since 3.0.3
     *
     * @param \WC_Order $order The order which is in a transitional state
     *
     * @return void
     */
    public function verify_intent_after_checkout( $order ) {
        $intent = $this->get_intent_from_order( $order );

        // No intent, redirect to the order received page for further actions.
        if ( ! $intent ) {
            return;
        }

        // A webhook might have modified or locked the order while the intent was retreived. This ensures we are reading the right status.
        clean_post_cache( $order->get_id() );
        $order = wc_get_order( $order->get_id() );

        if ( ! $order->has_status( [ 'pending', 'failed' ] ) ) {
            // If payment has already been completed, this function is redundant.
            return;
        }

        if ( $this->lock_order_payment( $order, $intent ) ) {
            return;
        }

        if ( 'succeeded' === $intent->status ) {
            WC()->cart->empty_cart();
            $this->handle_intent_verification_success( $order, $intent );
            do_action( 'dokan_stripe_payment_completed', $order, $intent );
        } elseif ( 'requires_capture' === $intent->status ) {
            // Proceed with the payment completion.
            $this->handle_intent_verification_success( $order, $intent );
        } elseif ( 'requires_payment_method' === $intent->status ) {
            // `requires_payment_method` means that SCA got denied for the current payment method.
            $this->handle_intent_verification_failure( $order, $intent );
        }

        $this->unlock_order_payment( $order );
    }

    /**
     * Called after an intent verification succeeds, this allows
     * specific APNs or children of this class to modify its behavior.
     *
     * @param WC_Order $order The order whose verification succeeded.
     * @param stdClass $intent The Payment Intent object.
     * @throws DokanException
     * @since 3.0.3
     */
    protected function handle_intent_verification_success( $order, $intent ) {
        $this->process_response( end( $intent->charges->data ), $order );

        if ( isset( $_GET['early_renewal'] ) && function_exists( 'wcs_update_dates_after_early_renewal' ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            wcs_update_dates_after_early_renewal( wcs_get_subscription( $order->get_meta( '_subscription_renewal' ) ), $order );
            wc_add_notice( __( 'Your early renewal order was successful.', 'dokan' ), 'success' );
        }
    }

    /**
     * Called after an intent verification fails, this allows
     * specific APNs or children of this class to modify its behavior.
     *
     * @param WC_Order $order The order whose verification failed.
     * @param stdClass $intent The Payment Intent object.
     */
    protected function handle_intent_verification_failure( $order, $intent ) {
        $this->failed_sca_auth( $order, $intent );
    }

    /**
     * Checks if the payment intent associated with an order failed and records the event.
     *
     * @since 3.0.3
     * @param \WC_Order $order  The order which should be checked.
     * @param object   $intent The intent, associated with the order.
     *
     * @return void
     */
    public function failed_sca_auth( $order, $intent ) {
        // If the order has already failed, do not repeat the same message.
        if ( $order->has_status( 'failed' ) ) {
            return;
        }

        // Load the right message and update the status.
        $status_message = isset( $intent->last_payment_error )
            /* translators: 1) The error message that was received from Stripe. */
            ? sprintf( __( 'Stripe SCA authentication failed. Reason: %s', 'dokan' ), $intent->last_payment_error->message )
            : __( 'Stripe SCA authentication failed.', 'dokan' );
        $order->update_status( 'failed', $status_message );
    }

    /**
     * Process vendor payment
     *
     * @param \WC_Order $order
     *
     * @return void
     * @throws DokanException
     * @since 3.0.3
     */
    public function process_vendor_payment( $order, $intent ) {
        if ( Helper::is_subscription_order( $order ) ) {
            $is_recurring = false;
            do_action( 'dokan_process_subscription_order', $order, $intent, $is_recurring );
            return;
        }

        $all_withdraws = [];
        $currency      = $order->get_currency();
        $charge_id     = $this->get_charge_id_from_order( $order );
        $all_orders    = $this->get_all_orders_to_be_processed( $order );
        $order_total   = $order->get_total();
        $stripe_fee    = Helper::format_gateway_balance_fee( $intent->charges->first()->balance_transaction );

        // This will make sure the parent order has processing_fee that requires in
        // Commission::calculate_gateway_fee() method at beginning.
        $order->update_meta_data( 'dokan_gateway_stripe_fee', $stripe_fee );

        // In case of we have sub orders, lets add the gateway fee in the parent order.
        if ( $order->get_meta( 'has_sub_order' ) ) {
            $order->update_meta_data( 'dokan_gateway_fee', $stripe_fee );
            $order->add_order_note( sprintf( __( 'Payment gateway processing fee %s', 'dokan' ), $stripe_fee ) );
        }

        if ( ! $charge_id ) {
            throw new DokanException( 'dokan_charge_id_not_found', __( 'No charge id is found to process the order!', 'dokan' ) );
        }

        if ( ! $all_orders ) {
            throw new DokanException( 'dokan_no_order_found', __( 'No orders found to be processed!', 'dokan' ) );
        }

        foreach ( $all_orders as $tmp_order ) {
            //return if $tmp_order not instance of WC_Order
            if ( ! $tmp_order instanceof \WC_Order ) {
                continue;
            }

            $tmp_order_id          = $tmp_order->get_id();
            $vendor_id             = dokan_get_seller_id_by_order( $tmp_order_id );
            $vendor_raw_earning    = dokan()->commission->get_earning_by_order( $tmp_order, 'seller' );
            $connected_vendor_id   = get_user_meta( $vendor_id, 'dokan_connected_vendor_id', true );
            $tmp_order_total       = $tmp_order->get_total();
            $stripe_fee_for_vendor = 0;

            if ( $tmp_order_total == 0 ) {
                $tmp_order->add_order_note( sprintf( __( 'Order %s payment completed', 'dokan' ), $tmp_order->get_order_number() ) );
                continue;
            }

            if ( Helper::seller_pays_the_processing_fee() && ! empty( $order_total ) && ! empty( $tmp_order_total ) && ! empty( $stripe_fee ) ) {
                $stripe_fee_for_vendor = Helper::calculate_processing_fee_for_suborder( $stripe_fee, $tmp_order, $order );
                $vendor_raw_earning    = $vendor_raw_earning - $stripe_fee_for_vendor;

                $tmp_order->update_meta_data( 'dokan_gateway_stripe_fee', $stripe_fee_for_vendor );
                $tmp_order->update_meta_data( 'dokan_gateway_fee_paid_by', 'seller' );
            }

            $vendor_earning = Helper::get_stripe_amount( $vendor_raw_earning );

            if ( ! $connected_vendor_id ) {
                // old order note for reference: Vendor's payment will be transferred to admin account since the vendor had not connected to Stripe.
                $tmp_order->add_order_note( sprintf( __( 'Vendor payment will be transferred to the admin account since the vendor had not connected to Stripe.', 'dokan' ) ) );
                $tmp_order->save_meta_data();
                continue;
            }

            if ( $vendor_earning < 1 ) {
                $tmp_order->add_order_note( sprintf( __( 'Transfer to the vendor stripe account skipped due to a negative balance: %1$s %2$s', 'dokan' ), $vendor_raw_earning, $currency ) );
                $tmp_order->save_meta_data();
                continue;
            }

            // get currency and symbol
            $currency        = $order->get_currency();
            $currency_symbol = html_entity_decode( get_woocommerce_currency_symbol( $order->get_currency() ) );

            // prepare extra metadata
            $application_fee = dokan()->commission->get_earning_by_order( $tmp_order, 'admin' );
            $metadata = [
                'stripe_processing_fee' => $currency_symbol . wc_format_decimal( $stripe_fee_for_vendor, 2 ),
                'application_fee'       => $currency_symbol . wc_format_decimal( $application_fee, 2 ),
            ];

            // get payment info
            $payment_info = Helper::generate_payment_info( $order, $tmp_order, $metadata );

            try {
                $transfer = DokanStripe::transfer()
                    ->description( $payment_info['description'] )
                    ->transaction( $charge_id )
                    ->group( $payment_info['transfer_group'] )
                    ->meta( $payment_info['metadata'] )
                    ->amount( $vendor_earning, $currency )
                    ->from( $charge_id )
                    ->to( $connected_vendor_id )
                    ->send();

                $tmp_order->update_meta_data( '_dokan_stripe_transfer_id', $transfer->id );
            } catch ( Exception $e ) {
                dokan_log( 'Could not transfer amount to connected vendor account via 3ds. Order ID: ' . $tmp_order->get_id() . ', Amount tried to transfer: ' . $vendor_raw_earning . " $currency" );
                $tmp_order->add_order_note( sprintf( __( 'Transfer failed to vendor account (%s)', 'dokan' ), $e->getMessage() ) );
                $tmp_order->add_order_note( __( 'Vendor payment will be transferred to the admin account since the transfer to the vendor stripe account had failed.', 'dokan' ) );
                $tmp_order->save_meta_data();
                continue;
            }

            // update vendor payment meta
            try {
                $vendor_charge = Charge::update(
                    $transfer->destination_payment,
                    [
                        'description'    => $payment_info['description'],
                        'transfer_group' => $payment_info['transfer_group'],
                        'metadata'       => $payment_info['metadata'],
                    ],
                    [
                        'stripe_account' => $transfer->destination,
                    ]
                );
            } catch ( Exception $e ) {
                dokan_log( 'Could not update charge information: ' . $e->getMessage() );
            }

            if ( $order->get_id() !== $tmp_order_id ) {
                $tmp_order->update_meta_data( 'paid_with_dokan_3ds', true );
                $tmp_order->add_order_note(
                    sprintf(
                        __( 'Order %1$s payment is completed via %2$s with 3d secure on (Charge ID: %3$s)', 'dokan' ),
                        $tmp_order->get_order_number(),
                        Helper::get_gateway_title(),
                        $charge_id
                    )
                );
            }

            $tmp_order->update_meta_data( '_stripe_customer_id', $intent->customer );
            $tmp_order->update_meta_data( '_stripe_source_id', $intent->source );
            $tmp_order->update_meta_data( '_stripe_intent_id', $intent->id );
            $tmp_order->update_meta_data( '_stripe_charge_captured', 'yes' );

            $tmp_order->save_meta_data();

            // set transaction id
            $tmp_order->set_transaction_id( $intent->charges->first()->id );
            $tmp_order->save();

            $withdraw_data = [
                'user_id'  => $vendor_id,
                'amount'   => $vendor_raw_earning,
                'order_id' => $tmp_order_id,
            ];

            $all_withdraws[] = $withdraw_data;
        }

        $this->insert_into_vendor_balance( $all_withdraws );
        $this->process_seller_withdraws( $all_withdraws );
        $order->add_order_note(
            sprintf(
                __( 'Order %1$s payment is completed via %2$s 3d secure. (Charge ID: %3$s)', 'dokan' ),
                $order->get_order_number(),
                Helper::get_gateway_title(),
                $charge_id
            )
        );

        $order->save_meta_data();
        dokan()->commission->calculate_gateway_fee( $order->get_id() );
    }

    /**
     * Creates a Setup Intent through AJAX while adding cards.
     * @since 3.2.2
     */
    public function create_setup_intent() {
        if (
            ! is_user_logged_in()
            || ! isset( $_POST['stripe_source_id'] )
            || ! isset( $_POST['nonce'] )
        ) {
            return;
        }

        try {
            $source_id = wc_clean( wp_unslash( $_POST['stripe_source_id'] ) );

            // 1. Verify.
            if (
                ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'dokan_stripe_create_si' )
                || ! preg_match( '/^src_.*$/', $source_id )
            ) {
                throw new Exception( __( 'Unable to verify your request. Please reload the page and try again.', 'dokan' ) );
            }

            // 2. Load the customer ID (and create a customer eventually).
            $customer = new Customer( get_current_user_id() );

            // 3. Attach the source to the customer (Setup Intents require that).
            $source_object = $customer->add_source( $source_id );
            if ( is_wp_error( $source_object ) ) {
                throw new Exception( $source_object->get_error_message() );
            }

            // 4. Generate the setup intent
            try {
                $setup_intent = SetupIntent::create(
                    [
                        'customer'       => $customer->get_id(),
                        'confirm'        => 'true',
                        'payment_method' => $source_id,
                        'usage'          => 'off_session',
                    ]
                );
            } catch ( Exception $e ) {
                $error_response_message = $e->getMessage();
                dokan_log( 'Failed create Setup Intent while saving a card.' );
                dokan_log( "Response: $error_response_message" );
                throw new Exception( __( 'Your card could not be set up for future usage.', 'dokan' ) );
            }

            // 5. Respond.
            if ( 'requires_action' === $setup_intent->status ) {
                $response = [
                    'status'        => 'requires_action',
                    'client_secret' => $setup_intent->client_secret,
                ];
            } elseif ( 'requires_payment_method' === $setup_intent->status
                || 'requires_confirmation' === $setup_intent->status
                || 'canceled' === $setup_intent->status ) {
                // These statuses should not be possible, as such we return an error.
                $response = [
                    'status' => 'error',
                    'error'  => [
                        'type'    => 'setup_intent_error',
                        'message' => __( 'Failed to save payment method.', 'dokan' ),
                    ],
                ];
            } else {
                // This should only be reached when status is `processing` or `succeeded`, which are
                // the only statuses that we haven't explicitly handled.
                $response = [
                    'status' => 'success',
                ];
            }
        } catch ( Exception $e ) {
            $response = [
                'status' => 'error',
                'error'  => array(
                    'type'    => 'setup_intent_error',
                    'message' => $e->getMessage(),
                ),
            ];
        }

        echo wp_json_encode( $response );
        exit;
    }
}
