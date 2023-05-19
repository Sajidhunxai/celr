<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts;

defined( 'ABSPATH' ) || exit;

use WC_Order;
use Exception;
use WC_Payment_Tokens;
use WC_Payment_Gateway_CC;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Order;
use WeDevs\DokanPro\Modules\StripeExpress\Support\UserMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Payment;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Customer;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Subscription;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Traits\PaymentUtils;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Traits\SubscriptionUtils;

/**
 * Base class for Stripe payment gateway.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts
 */
abstract class PaymentGateway extends WC_Payment_Gateway_CC {

    use PaymentUtils;
    use SubscriptionUtils;

    /**
     * The delay between retries.
     *
     * @since 3.6.1
     *
     * @var int
     */
    protected $retry_interval = 1;

    /**
     * Checks whether the gateway is enabled.
     *
     * @since 3.6.1
     *
     * @return bool The result.
     */
    public function is_enabled() {
        return 'yes' === $this->get_option( 'enabled' );
    }

    /**
     * Disables gateway.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function disable() {
        $this->update_option( 'enabled', 'no' );
    }

    /**
     * Enables gateway.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function enable() {
        $this->update_option( 'enabled', 'yes' );
    }

    /**
     * Admin options in WC payments settings
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function admin_options() {
        wp_enqueue_script( 'dokan-stripe-express-admin' );
        Helper::get_admin_template(
            'settings-header',
            [
                'gateway'       => $this,
                'dashboard_url' => Helper::get_payment_settings_url(),
            ]
        );
    }

    /**
     * Checks if the gateways is available for use.
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public function is_available() {
        return Helper::is_gateway_ready();
    }

    /**
     * Process the payment.
     *
     * @since 3.6.2
     *
     * @param int    $order_id Reference.
     * @param bool   $retry Should we retry on fail.
     * @param bool   $force_save_source Force save the payment source.
     * @param string $previous_error Any error message from previous request.
     * @param bool   $use_order_source Whether to use the source, which should already be attached to the order.
     *
     * @throws Exception If payment will not be accepted.
     * @return array|void
     */
    public function process_payment( $order_id, $retry = true, $force_save_source = false, $previous_error = false, $use_order_source = false ) {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        try {
            $order = wc_get_order( $order_id );

            if ( Subscription::is_subscription_order( $order_id ) ) {
                $force_save_source = true;
            }

            if ( ! empty( $_POST['subscription_id'] ) ) {
                $subscription_id = sanitize_text_field( wp_unslash( $_POST['subscription_id'] ) );
                $subscription    = Subscription::get( $subscription_id );
                $intent          = ! is_wp_error( $subscription ) ? $subscription->latest_invoice->payment_intent : false;

                OrderMeta::update_stripe_subscription_id( $order, $subscription_id );
                UserMeta::update_stripe_debug_subscription_id( $order->get_customer_id(), $subscription_id );
                OrderMeta::save( $order );
            } else {
                // Check whether there is an existing intent.
                $intent = Payment::get_intent( $order );
                if ( isset( $intent->object ) && 'setup_intent' === $intent->object ) {
                    $intent = false; // We will only deal with payment intent here
                }
            }

            $stripe_customer_id = null;
            if ( $intent && ! empty( $intent->customer ) ) {
                $stripe_customer_id = $intent->customer;
            }

            // For some payments the source should already be present in the order.
            if ( $use_order_source ) {
                $prepared_source = Order::prepare_source( $order );
            } else {
                $prepared_source = Payment::prepare_source( get_current_user_id(), $force_save_source, $stripe_customer_id );
            }

            /*
             * If we are using a saved payment method that is
             * PaymentMethod (pm_) and not a Source (src_),
             * we need to use the process_payment() from the
             * Stripe class that uses Payment Method api
             * instead of Source api.
             */
            if (
                Helper::is_using_saved_payment_method() &&
                ! empty( $prepared_source->payment_method ) &&
                substr( $prepared_source->payment_method, 0, 3 ) === 'pm_'
            ) {
                return Helper::get_gateway_instance()->process_payment_with_saved_payment_method( $order_id );
            }

            Helper::maybe_disallow_prepaid_card( $prepared_source->payment_method_object );

            if ( empty( $prepared_source->payment_method ) ) {
                throw new DokanException(
                    'invalid-source',
                    __( 'Invalid Payment Source: Payment processing failed. Please retry.', 'dokan' )
                );
            }

            Order::save_source( $order, $prepared_source );

            /**
             * Process payment when needed.
             *
             * @since 3.7.8
             *
             * @param WC_Order $order             The order being processed.
             * @param string   $payment_method_id The source of the payment.
             */
            do_action( 'dokan_stripe_express_process_payment', $order, $prepared_source->payment_method );

            // This may be needed at a point next and so we need to store it as it will be unset.
            $payment_method_object = $prepared_source->payment_method_object;
            // Unset unnecessary indexes before hitting the intent api
            unset( $prepared_source->payment_method_object );

            if ( 0 >= $order->get_total() ) {
                return $this->complete_free_order( $order, $prepared_source, $force_save_source );
            }

            // This will throw exception if not valid.
            $this->validate_minimum_order_amount( $order );

            Helper::log( "Processing payment for order $order_id for the amount of {$order->get_total()}", 'Order', 'info' );

            if ( ! Subscription::is_recurring_vendor_subscription_order( $order_id ) ) {
                if ( $intent ) {
                    $intent = Payment::update_intent( $intent->id, $order->get_id() );
                } else {
                    $intent = Payment::create_intent( $order, $prepared_source );
                }

                // Confirm the intent after locking the order to make sure webhooks will not interfere.
                if ( empty( $intent->error ) ) {
                    Order::lock_processing( $order->get_id(), 'intent', $intent->id );
                    $intent = Payment::confirm_intent( $intent, $prepared_source->payment_method );
                }

                // Handle intent error (if any) after confirming the intent.
                if ( ! empty( $intent->error ) ) {
                    $this->maybe_remove_non_existent_customer( $intent->error, $order );
                    Order::unlock_processing( $order->get_id() );

                    $error_message = Helper::get_error_message_from_response( $intent, $order );
                    $order->add_order_note( $error_message );

                    throw new Exception( $error_message );
                }
            }

            $force_save_source = apply_filters( 'dokan_stripe_express_force_save_source', $force_save_source, $prepared_source->payment_method );

            if ( ! empty( $intent ) ) {
                if ( 'requires_action' === $intent->status ) {
                    Order::unlock_processing( $order );

                    if ( is_wc_endpoint_url( 'order-pay' ) ) {
                        return [
                            'result'   => 'success',
                            'redirect' => add_query_arg( 'dokan_stripe_express_confirmation', 1, $order->get_checkout_payment_url( false ) ),
                        ];
                    }

                    /*
                     * This URL contains only a hash, which will be sent to `checkout.js` where it will be set like this:
                     * `window.location = result.redirect`
                     * Once this redirect is sent to JS, the `onHashChange` function will execute `handleCardPayment`.
                     */
                    return [
                        'result'                => 'success',
                        'redirect'              => $this->get_return_url( $order ),
                        'payment_intent_secret' => $intent->client_secret,
                        'save_payment_method'   => $this->save_payment_method_requested(),
                    ];
                }

                if ( 'succeeded' === $intent->status && ! Helper::is_using_saved_payment_method() && ( $this->save_payment_method_requested() || $force_save_source ) ) {
                    $user_id = get_current_user_id();

                    if ( ( $user_id ) ) {
                        $customer = Customer::set( $user_id );
                        $response = $customer->attach_payment_method( $prepared_source->payment_method );

                        if ( is_wp_error( $response ) ) {
                            throw new Exception( $response->get_error_message() );
                        }

                        do_action( 'dokan_stripe_express_add_payment_method_success', $prepared_source->payment_method, $payment_method_object );
                    }
                }

                // Use the last charge within the intent to proceed or the original response in case of SEPA
                $response = Payment::get_latest_charge_from_intent( $intent );
                if ( ! $response ) {
                    $response = $intent;
                }

                Payment::process_response( $response, $order );
            }

            // Remove cart.
            if ( isset( WC()->cart ) ) {
                WC()->cart->empty_cart();
            }

            // Unlock the order.
            Order::unlock_processing( $order );

            // Return thank you page redirect.
            return [
                'result'       => 'success',
                'redirect'     => $this->get_return_url( $order ),
            ];
        } catch ( Exception $e ) {
            wc_add_notice( $e->getMessage(), 'error' );
            Helper::log( 'Error: ' . $e->getMessage() );

            do_action( 'dokan_stripe_express_process_payment_error', $e, $order );

            $order->update_status( 'failed' );

            return [
                'result'   => 'fail',
                'redirect' => '',
            ];
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }

    /**
     * Displays the save to account checkbox.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function save_payment_method_checkbox( $force_checked = false ) {
        Helper::get_template(
            'payment-method-checkbox',
            [
                'force_checked' => $force_checked,
                'id'            => "wc-{$this->id}-new-payment-method",
            ]
        );
    }

    /**
     * Attached to `woocommerce_payment_successful_result` with a late priority,
     * this method will combine the "naturally" generated redirect URL from
     * WooCommerce and a payment/setup intent secret into a hash, which contains both
     * the secret, and a proper URL, which will confirm whether the intent succeeded.
     *
     * @since 3.6.1
     *
     * @param array $result   The result from `process_payment`.
     * @param int   $order_id The ID of the order which is being paid for.
     *
     * @return array
     */
    public function modify_successful_payment_result( $result, $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order || Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return $result;
        }

        if ( ! isset( $result['payment_intent_secret'] ) && ! isset( $result['setup_intent_secret'] ) ) {
            // Only redirects with intents need to be modified.
            return $result;
        }

        // Put the final thank you page redirect into the verification URL.
        $query_params = [
            'order'       => $order_id,
            'nonce'       => wp_create_nonce( 'dokan_stripe_express_confirm_pi' ),
            'redirect_to' => rawurlencode( $result['redirect'] ),
        ];

        $force_save_source_value = apply_filters( 'dokan_stripe_express_force_save_source', false );

        if ( $this->save_payment_method_requested() || $force_save_source_value ) {
            $query_params['save_payment_method'] = true;
        }

        $verification_url = add_query_arg( $query_params, \WC_AJAX::get_endpoint( 'dokan_stripe_express_verify_intent' ) );

        if ( isset( $result['payment_intent_secret'] ) ) {
            $redirect = sprintf( '#confirm-pi-%s:%s', $result['payment_intent_secret'], rawurlencode( $verification_url ) );
        } elseif ( isset( $result['setup_intent_secret'] ) ) {
            $redirect = sprintf( '#confirm-si-%s:%s', $result['setup_intent_secret'], rawurlencode( $verification_url ) );
        }

        return [
            'result'   => 'success',
            'redirect' => $redirect,
        ];
    }

    /**
     * Handles payment complete process of free orders.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     * @param object   $prepared_source
     * @param boolean  $force_save_source
     *
     * @return array
     */
    public function complete_free_order( $order, $prepared_source, $force_save_source ) {
        if ( $force_save_source ) {
            $intent = Payment::create_intent( $order, $prepared_source, true );

            if ( ! empty( $intent->client_secret ) ) {
                // `get_return_url()` must be called immediately before returning a value.
                return [
                    'result'              => 'success',
                    'redirect'            => $this->get_return_url( $order ),
                    'setup_intent_secret' => $intent->client_secret,
                ];
            }
        }

        // Remove cart.
        WC()->cart->empty_cart();

        $order->payment_complete();

        // Return thank you page redirect.
        return [
            'result'   => 'success',
            'redirect' => $this->get_return_url( $order ),
        ];
    }

    /**
     * Includes the template for Stripe element form.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function element_form() {
        Helper::get_template( 'stripe-element' );
    }

    /**
     * Retrieves description for test mode.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public function testmode_description() {
        return sprintf(
            /* translators: 1) opening strong tag, 2) closing strong tag, 3) opening anchor tag with link to stripe testing doc, 4) closing anchor tag  */
            __( '%1$sTest mode:%2$s use the test VISA card 4242424242424242 with any expiry date and CVC. Other payment methods may redirect to a Stripe test page to authorize payment. For example, 4000002500003155 is a 3D secure test card. More test card numbers are listed %3$shere%4$s.', 'dokan' ),
            '<strong>',
            '</strong>',
            '<a href="https://stripe.com/docs/testing" target="_blank">',
            '</a>'
        );
    }

    /**
     * Checks if save payment request requested.
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public function save_payment_method_requested() {
        $payment_method = isset( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Missing

        return Helper::is_saved_card( $payment_method );
    }

    /**
     * Checks if customer has saved payment methods.
     *
     * @since 3.7.8
     *
     * @param int $customer_id
     *
     * @return bool
     */
    public static function customer_has_saved_methods( $customer_id ) {
        if ( empty( $customer_id ) ) {
            return false;
        }

        $has_token = false;

        $gateways = [
            Helper::get_gateway_id(),
            Helper::get_sepa_gateway_id(),
        ];

        foreach ( $gateways as $gateway ) {
            $tokens = WC_Payment_Tokens::get_customer_tokens( $customer_id, $gateway );

            if ( ! empty( $tokens ) ) {
                $has_token = true;
                break;
            }
        }

        return $has_token;
    }

    /**
     * Builds the return URL from redirects.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order (optional)
     * @param int      $id    Stripe session id.
     *
     * @return string
     */
    public function get_stripe_return_url( $order = null, $id = null ) {
        if ( is_object( $order ) ) {
            if ( empty( $id ) ) {
                $id = uniqid();
            }

            $order_id = $order->get_id();

            $args = [
                'utm_nooverride' => '1',
                'order_id'       => $order_id,
            ];

            return wp_sanitize_redirect( esc_url_raw( add_query_arg( $args, $this->get_return_url( $order ) ) ) );
        }

        return wp_sanitize_redirect( esc_url_raw( add_query_arg( [ 'utm_nooverride' => '1' ], $this->get_return_url() ) ) );
    }

    /**
     * Customer param wrong? The user may have been deleted on stripe's end. Remove customer_id. Can be retried without.
     *
     * @since 3.6.1
     *
     * @param object   $error The error that was returned from Stripe's API.
     * @param WC_Order $order The order those payment is being processed.
     *
     * @return bool    A flag that indicates that the customer does not exist and should be removed.
     */
    public function maybe_remove_non_existent_customer( $error, $order ) {
        if ( ! Helper::is_no_such_customer_error( $error ) ) {
            return false;
        }

        UserMeta::delete_stripe_customer_id( $order->get_customer_id() );
        OrderMeta::delete_customer_id( $order );
        OrderMeta::save( $order );

        return true;
    }

    /**
     * Check to see if we need to update the idempotency
     * key to be different from previous charge request.
     *
     * @since 3.7.8
     *
     * @param object $source_object
     * @param object $error
     *
     * @return bool
     */
    public function idempotency_key_update_needed( $source_object, $error ) {
        return (
            $error &&
            1 < $this->retry_interval &&
            ! empty( $source_object ) &&
            'chargeable' === $source_object->status &&
            Helper::is_same_idempotency_error( $error )
        );
    }

    /**
     * Gets a localized message for an error from a response, adds it as a note to the order, and throws it.
     *
     * @since 3.6.1
     *
     * @param  stdClass $response  The response from the Stripe API.
     * @param  WC_Order $order     The order to add a note to.
     *
     * @return void
     * @throws DokanException An exception with the right message.
     */
    public function throw_error_message( $response, $order ) {
        $localized_message = Helper::get_error_message_from_response( $response );

        $order->add_order_note( $localized_message );

        throw new DokanException( print_r( $response, true ), $localized_message );
    }
}
