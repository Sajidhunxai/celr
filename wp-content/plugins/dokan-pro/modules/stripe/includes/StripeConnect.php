<?php

namespace WeDevs\DokanPro\Modules\Stripe;

use Stripe\SetupIntent;
use WC_AJAX;
use Exception;
use Stripe\PaymentIntent;
use WeDevs\DokanPro\Modules\Stripe\Payment_Tokens;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\Stripe\Helper;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\Stripe\DokanStripe;
use WeDevs\DokanPro\Modules\Stripe\Abstracts\StripePaymentGateway;

defined( 'ABSPATH' ) || exit;

class StripeConnect extends StripePaymentGateway {

    /**
     * The delay between retries.
     *
     * @var int
     */
    public $retry_interval;

    /**
     * Constructor method
     *
     * @since 3.0.3
     *
     * @return vois
     */
    public function __construct() {
        $this->retry_interval     = 1;
        $this->id                 = 'dokan-stripe-connect';
        $this->method_title       = __( 'Dokan Stripe Connect', 'dokan' );
        $this->method_description = __( 'Have your customers pay with credit card.', 'dokan' );
        $this->icon               = DOKAN_STRIPE_ASSETS . 'images/cards.png';
        $this->has_fields         = true;
        $this->supports           = [
            'products',
            'refunds',
            'tokenization',
            'subscriptions',
            'subscription_cancellation',
            'subscription_suspension',
            'subscription_reactivation',
            'subscription_amount_changes',
            'subscription_date_changes',
            'subscription_payment_method_change',
            'subscription_payment_method_change_customer',
            'subscription_payment_method_change_admin',
        ];

        $this->init_form_fields();
        $this->init_settings();

        $this->title           = $this->get_option( 'title' );
        $this->description     = $this->get_option( 'description' );
        $this->enabled         = $this->get_option( 'enabled' );
        $this->testmode        = 'yes' === $this->get_option( 'testmode' );
        $this->secret_key      = $this->testmode ? $this->get_option( 'test_secret_key' ) : $this->get_option( 'secret_key' );
        $this->publishable_key = $this->testmode ? $this->get_option( 'test_publishable_key' ) : $this->get_option( 'publishable_key' );
        $this->saved_cards     = 'yes' === $this->get_option( 'saved_cards' );
        $this->checkout_modal  = 'yes' === $this->get_option( 'stripe_checkout' );
        $this->checkout_locale = $this->get_option( 'stripe_checkout_locale' );
        $this->checkout_image  = $this->get_option( 'stripe_checkout_image' );
        $this->checkout_label  = $this->get_option( 'stripe_checkout_label' );
        $this->currency        = strtolower( get_woocommerce_currency() );
        $this->stripe_meta_key = '_dokan_stripe_charge_id_';

        Helper::bootstrap_stripe();

        $this->hooks();
    }

    /**
     * Initialise Gateway Settings Form Fields
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function init_form_fields() {
        $this->form_fields = require dirname( __FILE__ ) . '/Settings/StripeConnect.php';
    }

    /**
     * Init all the hooks
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function hooks() {
        add_action( 'wp_enqueue_scripts', [ $this, 'payment_scripts' ] );
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, [ $this, 'process_admin_options' ] );
        add_filter( 'woocommerce_payment_successful_result', [ $this, 'modify_successful_payment_result' ], 99999, 2 );
        add_action( 'woocommerce_checkout_order_review', [ $this, 'set_subscription_data' ] );
        add_action( 'woocommerce_customer_save_address', [ $this, 'show_update_card_notice' ], 10, 2 );

        if ( class_exists( 'WC_Subscriptions_Order' ) ) {
            add_action( 'woocommerce_scheduled_subscription_payment_' . $this->id, array( $this, 'scheduled_subscription_payment' ), 20, 2 );
            add_action( 'wcs_resubscribe_order_created', array( $this, 'delete_resubscribe_meta' ), 10 );
            add_filter( 'wcs_renewal_order_created', array( $this, 'delete_renewal_meta' ), 10 );
            add_filter( 'dokan_gateway_stripe_renewal_process_payment', array( $this, 'handle_transfer_payment' ), 10 );
            add_filter( 'woocommerce_subscription_activation_next_payment_not_recalculated', array( $this, 'calculate_next_payment_date' ), 10, 3 );
            add_action( 'woocommerce_subscription_failing_payment_method_updated_dokan-stripe-connect', array( $this, 'update_failing_payment_method' ), 10, 2 );
            add_action( 'dokan_stripe_cards_payment_fields', array( $this, 'display_update_subs_payment_checkout' ) );
            add_action( 'dokan_stripe_connect_add_payment_method_' . $this->id . '_success', array( $this, 'handle_add_payment_method_success' ), 10, 2 );
            add_action( 'dokan_stripe_payment_completed', array( $this, 'update_payment_meta_for_subscription' ), 12, 2 );

            // display the credit card used for a subscription in the "My Subscriptions" table
            add_filter( 'woocommerce_my_subscriptions_payment_method', array( $this, 'maybe_render_subscription_payment_method' ), 10, 2 );

            // allow store managers to manually set Stripe as the payment method on a subscription
            add_filter( 'woocommerce_subscription_payment_meta', array( $this, 'add_subscription_payment_meta' ), 10, 2 );
            add_filter( 'woocommerce_subscription_validate_payment_meta', array( $this, 'validate_subscription_payment_meta' ), 10, 2 );
            add_filter( 'dokan_stripe_display_save_payment_method_checkbox', array( $this, 'maybe_hide_save_checkbox' ) );

            /*
             * WC subscriptions hooks into the "template_redirect" hook with priority 100.
             * If the screen is "Pay for order" and the order is a subscription renewal, it redirects to the plain checkout.
             * See: https://github.com/woocommerce/woocommerce-subscriptions/blob/99a75687e109b64cbc07af6e5518458a6305f366/includes/class-wcs-cart-renewal.php#L165
             * If we are in the "You just need to authorize SCA" flow, we don't want that redirection to happen.
             */
            add_action( 'template_redirect', array( $this, 'remove_order_pay_var' ), 99 );
            add_action( 'template_redirect', array( $this, 'restore_order_pay_var' ), 101 );
        }

        // hooks related to order pay page.
        add_filter( 'woocommerce_get_checkout_payment_url', [ $this, 'get_checkout_payment_url' ], 10, 2 );
        add_filter( 'woocommerce_available_payment_gateways', [ $this, 'prepare_order_pay_page' ] );
    }

    /**
     * Process the payment method change for subscriptions.
     *
     * @param int $order_id
     * @return array
     * @since 4.0.4
     * @since 4.1.11 Remove 3DS check as it is not needed.
     */
    public function change_subs_payment_method( $order_id ) {
        try {
            $subscription    = wc_get_order( $order_id );
            $prepared_source = $this->prepare_source( get_current_user_id(), true );

            Helper::check_source( $prepared_source );
            $this->save_source_to_order( $subscription, $prepared_source );

            do_action( 'dokan_stripe_change_subs_payment_method_success', $prepared_source->source, $prepared_source );

            return [
                'result'   => 'success',
                'redirect' => $this->get_return_url( $subscription ),
            ];
        } catch ( DokanException $e ) {
            wc_add_notice( $e->getMessage(), 'error' );
            dokan_log( 'Error: ' . $e->getMessage() );
        }
    }

    /**
     * Schedule subscription payment
     *
     * @since 3.1.0
     *
     * @param float $amount_to_charge
     * @param Object $renewal_order
     *
     * @return Success|Exceptions
     */
    public function scheduled_subscription_payment( $amount_to_charge, $renewal_order ) {
        $this->process_subscription_payment( $amount_to_charge, $renewal_order, true, false );
    }

    /**
     * Scheduled_subscription_payment function.
     *
     * @param float $amount
     * @param $renewal_order \WC_Order A WC_Order object created to record the renewal payment.
     * @param bool $retry
     * @param bool $previous_error
     * @return void
     */
    public function process_subscription_payment( $amount = 0.0, $renewal_order = null, $retry = true, $previous_error = false ) {
        try {
            // check if $renewal_order is a instance of WC_Order
            if ( ! $renewal_order instanceof \WC_Order ) {
                throw new DokanException( 'invalid_order_object', __( 'Invalid renewal order object.', 'dokan' ) );
            }

            $order_id = $renewal_order->get_id();

            $this->ensure_subscription_has_customer_id( $order_id );

            // Unlike regular off-session subscription payments, early renewals are treated as on-session payments, involving the customer.
            if ( isset( $_REQUEST['process_early_renewal'] ) && Helper::payment_method() === '3ds' ) { // phpcs:ignore WordPress.Security.NonceVerification
                // early renewal only works with stripe3ds payment method.
                $payment_method = '\\WeDevs\\DokanPro\\Modules\\Stripe\\PaymentMethods\\Stripe3DSPayment';
                $response = ( new $payment_method( $renewal_order, false, true ) )->pay();

                if ( 'success' === $response['result'] && isset( $response['payment_intent_secret'] ) ) {
                    $verification_url = add_query_arg(
                        [
                            'order'         => $order_id,
                            'key'           => $renewal_order->get_order_key(),
                            'nonce'         => wp_create_nonce( 'dokan_stripe_confirm_pi' ),
                            'redirect_to'   => remove_query_arg( [ 'process_early_renewal', 'subscription_id', 'wcs_nonce' ] ),
                            'early_renewal' => true,
                        ],
                        WC_AJAX::get_endpoint( 'dokan_stripe_verify_intent' )
                    );

                    echo wp_json_encode(
                        [
                            'stripe_sca_required' => true,
                            'intent_secret'       => $response['payment_intent_secret'],
                            'redirect_url'        => $verification_url,
                        ]
                    );

                    exit;
                }

                // Hijack all other redirects in order to do the redirection in JavaScript.
                add_action( 'wp_redirect', array( $this, 'redirect_after_early_renewal' ), 100 );

                return;
            }

            // Check for an existing intent, which is associated with the order.
            if ( $this->has_authentication_already_failed( $renewal_order ) ) {
                dokan_log( 'has_authentication_already_failed is true' );
                return;
            }

            // Get source from order
            $prepared_source = $this->prepare_order_source( $renewal_order );
            $source_object   = $prepared_source->source_object;

            if ( ! $prepared_source->customer ) {
                throw new DokanException(
                    'Failed to process renewal for order ' . $renewal_order->get_id() . '. Stripe customer id is missing in the order',
                    __( 'Customer not found', 'dokan' )
                );
            }

            /* If we're doing a retry and source is chargeable, we need to pass
             * a different idempotency key and retry for success.
             */
            if ( is_object( $source_object ) && empty( $source_object->error ) && $this->need_update_idempotency_key( $source_object, $previous_error ) ) {
                add_filter( 'wc_stripe_idempotency_key', array( $this, 'change_idempotency_key' ), 10, 2 );
            }

            if ( ( $this->is_no_such_source_error( $previous_error ) || $this->is_no_linked_source_error( $previous_error ) ) && apply_filters( 'wc_stripe_use_default_customer_source', true ) ) {
                // Passing empty source will charge customer default.
                $prepared_source->source = '';
            }

            $this->lock_order_payment( $renewal_order );

            $response                   = $this->create_intent_for_renewal_order( $renewal_order, $prepared_source, $amount );
            $is_authentication_required = $this->is_authentication_required_for_payment( $response );

            // It's only a failed payment if it's an error and it's not of the type 'authentication_required'.
            // If it's 'authentication_required', then we should email the user and ask them to authenticate.
            if ( ! empty( $response->error ) && ! $is_authentication_required ) {
                // We want to retry.
                if ( $this->is_retryable_error( $response->error ) ) {
                    if ( $retry ) {
                        // Don't do anymore retries after this.
                        if ( 5 <= $this->retry_interval ) {
                            return $this->process_subscription_payment( $amount, $renewal_order, false, $response->error );
                        }

                        sleep( $this->retry_interval );

                        $this->retry_interval++;

                        return $this->process_subscription_payment( $amount, $renewal_order, true, $response->error );
                    } else {
                        $localized_message = __( 'Sorry, we are unable to process your payment at this time. Please retry later.', 'dokan' );
                        $renewal_order->add_order_note( $localized_message );
                        throw new DokanException( print_r( $response, true ), $localized_message );
                    }
                }

                $localized_message = Helper::get_localized_error_message_from_response( $response );

                $renewal_order->add_order_note( $localized_message );

                throw new DokanException( print_r( $response, true ), $localized_message );
            }

            // Either the charge was successfully captured, or it requires further authentication.
            if ( $is_authentication_required ) {
                do_action( 'wc_gateway_stripe_process_payment_authentication_required', $renewal_order, $response );

                $error_message = __( 'This transaction requires authentication.', 'dokan' );
                $renewal_order->add_order_note( $error_message );

                $charge = end( $response->error->payment_intent->charges->data );
                $id = $charge->id;
                $order_id = $renewal_order->get_id();

                $renewal_order->set_transaction_id( $id );
                $renewal_order->update_status( 'failed', sprintf( __( 'Stripe charge awaiting authentication by user: %s.', 'dokan' ), $id ) );
                if ( is_callable( array( $renewal_order, 'save' ) ) ) {
                    $renewal_order->save();
                }
            } else {
                // The charge was successfully captured
                do_action( 'wc_gateway_stripe_process_payment', $response, $renewal_order );
                do_action( 'dokan_stripe_payment_completed', $renewal_order, $response );

                $this->process_response( end( $response->charges->data ), $renewal_order );
            }

            $this->unlock_order_payment( $renewal_order );
        } catch ( DokanException $e ) {
            do_action( 'wc_gateway_stripe_process_payment_error', $e, $renewal_order );
            dokan_log( 'caught exception on process_subscription_payment, code: ' . $e->get_error_code() . ', message: ' . $e->getMessage() );

            /* translators: error message */
            $renewal_order->update_status( 'failed' );
        } catch ( Exception $e ) {
            do_action( 'wc_gateway_stripe_process_payment_error', $e, $renewal_order );
            dokan_log( 'caught exception on process_subscription_payment: ' . $e->getMessage() );

            /* translators: error message */
            $renewal_order->update_status( 'failed' );
        }
    }

    /**
     * Include the payment meta data required to process automatic recurring payments so that store managers can
     * manually set up automatic recurring payments for a customer via the Edit Subscriptions screen in 2.0+.
     *
     * @since 3.1.0
     *
     * @param array $payment_meta associative array of meta data required for automatic payments
     * @param WC_Subscription $subscription An instance of a subscription object
     *
     * @return array
     */
    public function add_subscription_payment_meta( $payment_meta, $subscription ) {
        $subscription_id = $subscription->get_id();
        $source_id       = get_post_meta( $subscription_id, '_stripe_source_id', true );

        // For BW compat will remove in future.
        if ( empty( $source_id ) ) {
            $source_id = get_post_meta( $subscription_id, '_stripe_card_id', true );

            // Take this opportunity to update the key name.
            update_post_meta( $subscription_id, '_stripe_source_id', $source_id );
            delete_post_meta( $subscription_id, '_stripe_card_id', $source_id );
        }

        $payment_meta[ $this->id ] = array(
            'post_meta' => array(
                '_stripe_customer_id' => array(
                    'value' => get_post_meta( $subscription_id, '_stripe_customer_id', true ),
                    'label' => 'Stripe Customer ID',
                ),
                '_stripe_source_id'   => array(
                    'value' => $source_id,
                    'label' => 'Stripe Source ID',
                ),
            ),
        );

        return $payment_meta;
    }

    /**
     * Update subscription payment meta when order created
     *
     * @since 3.1.0
     *
     * @return void
     */
    public function update_payment_meta_for_subscription( $order, $intent ) {
        if ( empty( $order ) ) {
            return;
        }

        $subscriptions = wcs_get_subscriptions_for_order( $order->get_id() );

        if ( empty( $subscriptions ) ) {
            return;
        }

        foreach ( $subscriptions as $key => $subscription ) {
            update_post_meta( $subscription->get_id(), '_stripe_customer_id', $intent->customer );
            update_post_meta( $subscription->get_id(), '_transaction_id', $intent->charges->first()->id );
            update_post_meta( $subscription->get_id(), '_stripe_source_id', $intent->source );
            update_post_meta( $subscription->get_id(), '_stripe_intent_id', $intent->id );
        }
	}

    /**
     * Validate the payment meta data required to process automatic recurring payments so that store managers can
     * manually set up automatic recurring payments for a customer via the Edit Subscriptions screen in 2.0+.
     *
     * @param string $payment_method_id The ID of the payment method to validate
     * @param array $payment_meta associative array of meta data required for automatic payments
     * @return void
     * @throws Exception
     */
    public function validate_subscription_payment_meta( $payment_method_id, $payment_meta ) {
        if ( $this->id === $payment_method_id ) {
            if ( ! isset( $payment_meta['post_meta']['_stripe_customer_id']['value'] ) || empty( $payment_meta['post_meta']['_stripe_customer_id']['value'] ) ) {

                // Allow empty stripe customer id during subscription renewal. It will be added when processing payment if required.
                if ( ! isset( $_POST['wc_order_action'] ) || 'wcs_process_renewal' !== sanitize_text_field( wp_unslash( $_POST['wc_order_action'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
                    throw new Exception( __( 'A "Stripe Customer ID" value is required.', 'dokan' ) );
                }
            } elseif ( 0 !== strpos( $payment_meta['post_meta']['_stripe_customer_id']['value'], 'cus_' ) ) {
                throw new Exception( __( 'Invalid customer ID. A valid "Stripe Customer ID" must begin with "cus_".', 'dokan' ) );
            }

            if (
                ( ! empty( $payment_meta['post_meta']['_stripe_source_id']['value'] )
                && 0 !== strpos( $payment_meta['post_meta']['_stripe_source_id']['value'], 'card_' ) )
                && ( ! empty( $payment_meta['post_meta']['_stripe_source_id']['value'] )
                && 0 !== strpos( $payment_meta['post_meta']['_stripe_source_id']['value'], 'src_' ) ) ) {
                throw new Exception( __( 'Invalid source ID. A valid source "Stripe Source ID" must begin with "src_" or "card_".', 'dokan' ) );
            }
        }
    }

    /**
     * Calculate next payment date
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function calculate_next_payment_date( $stored_next_payment, $old_status, $subscription ) {
        $calculated_next_payment = $subscription->calculate_date( 'next_payment' );

        if ( $calculated_next_payment > 0 ) {
            $subscription->update_dates( array( 'next_payment' => $calculated_next_payment ) );
        } elseif ( $stored_next_payment < gmdate( 'U' ) ) { // delete the stored date if it's in the past as we're not updating it (the calculated next payment date is 0 or none)
            $subscription->delete_date( 'next_payment' );
        }

        $subscription->save();
    }

    /**
     * Update the customer_id for a subscription after using Stripe to complete a payment to make up for
     * an automatic renewal payment which previously failed.
     *
     * @since DOKAN_PRO_VERSION
     *
     * @param WC_Subscription $subscription The subscription for which the failing payment method relates.
     * @param WC_Order $renewal_order The order which recorded the successful payment (to make up for the failed automatic payment).
     *
     * @return void
     */
    public function update_failing_payment_method( $subscription, $renewal_order ) {
        update_post_meta( $subscription->get_id(), '_stripe_customer_id', $renewal_order->get_meta( '_stripe_customer_id', true ) );
        update_post_meta( $subscription->get_id(), '_stripe_source_id', $renewal_order->get_meta( '_stripe_source_id', true ) );
    }

    /**
     * Displays a checkbox to allow users to update all subs payments with new
     * payment.
     *
     * @since 3.1.0
     *
     * @return HTML
     */
    public function display_update_subs_payment_checkout() {
        $subs_statuses = apply_filters( 'dokan_stripe_update_subs_payment_method_card_statuses', array( 'active' ) );
        if (
            apply_filters( 'dokan_stripe_display_update_subs_payment_method_card_checkbox', true ) &&
            function_exists( 'wcs_user_has_subscription' ) &&
            wcs_user_has_subscription( get_current_user_id(), '', $subs_statuses ) &&
            is_add_payment_method_page()
        ) {
            $label = esc_html( apply_filters( 'dokan_stripe_save_to_subs_text', __( 'Update the Payment Method used for all of my active subscriptions.', 'dokan' ) ) );
            $id    = sprintf( 'dokan-%1$s-update-subs-payment-method-card', $this->id );
            woocommerce_form_field(
                $id,
                array(
                    'type'    => 'checkbox',
                    'label'   => $label,
                    'default' => apply_filters( 'dokan_stripe_save_to_subs_checked', false ),
                )
            );
        }
    }

    /**
     * Updates all active subscriptions payment method.
     *
     * @since 3.1.0
     *
     * @param string $source_id
     * @param object $source_object
     *
     * @return Void
     */
    public function handle_add_payment_method_success( $source_id, $source_object ) {
        if ( isset( $_POST[ 'dokan-' . $this->id . '-update-subs-payment-method-card' ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            $all_subs        = wcs_get_users_subscriptions();
            $subs_statuses   = apply_filters( 'dokan_stripe_update_subs_payment_method_card_statuses', array( 'active' ) );
            $stripe_customer = new Customer( get_current_user_id() );

            if ( ! empty( $all_subs ) ) {
                foreach ( $all_subs as $sub ) {
                    if ( $sub->has_status( $subs_statuses ) ) {
                        update_post_meta( $sub->get_id(), '_stripe_source_id', $source_id );
                        update_post_meta( $sub->get_id(), '_stripe_customer_id', $stripe_customer->get_id() );
                        update_post_meta( $sub->get_id(), '_payment_method', $this->id );
                        update_post_meta( $sub->get_id(), '_payment_method_title', $this->method_title );
                    }
                }
            }
        }
    }

    /**
     * Render the payment method used for a subscription in the "My Subscriptions" table
     *
     * @since 1.7.5
     * @param string $payment_method_to_display the default payment method text to display
     * @param WC_Subscription $subscription the subscription details
     * @return string the subscription payment method
     */
    public function maybe_render_subscription_payment_method( $payment_method_to_display, $subscription ) {
        $customer_user = $subscription->get_customer_id();

        // bail for other payment methods
        if ( $subscription->get_payment_method() !== $this->id || ! $customer_user ) {
            return $payment_method_to_display;
        }

        $stripe_source_id = get_post_meta( $subscription->get_id(), '_stripe_source_id', true );

        // For BW compat will remove in future.
        if ( empty( $stripe_source_id ) ) {
            $stripe_source_id = get_post_meta( $subscription->get_id(), '_stripe_card_id', true );

            // Take this opportunity to update the key name.
            update_post_meta( $subscription->get_id(), '_stripe_source_id', $stripe_source_id );
        }

        $stripe_customer    = new Customer();
        $stripe_customer_id = get_post_meta( $subscription->get_id(), '_stripe_customer_id', true );

        // If we couldn't find a Stripe customer linked to the subscription, fallback to the user meta data.
        if ( ! $stripe_customer_id || ! is_string( $stripe_customer_id ) ) {
            $user_id            = $customer_user;
            $stripe_customer_id = get_user_option( '_stripe_customer_id', $user_id );
            $stripe_source_id   = get_user_option( '_stripe_source_id', $user_id );

            // For BW compat will remove in future.
            if ( empty( $stripe_source_id ) ) {
                $stripe_source_id = get_user_option( '_stripe_card_id', $user_id );

                // Take this opportunity to update the key name.
                update_user_option( $user_id, '_stripe_source_id', $stripe_source_id, false );
            }
        }

        // If we couldn't find a Stripe customer linked to the account, fallback to the order meta data.
        if ( ( ! $stripe_customer_id || ! is_string( $stripe_customer_id ) ) && false !== $subscription->order ) {
            $stripe_customer_id = get_post_meta( $subscription->get_parent_id(), '_stripe_customer_id', true );
            $stripe_source_id   = get_post_meta( $subscription->get_parent_id(), '_stripe_source_id', true );

            // For BW compat will remove in future.
            if ( empty( $stripe_source_id ) ) {
                $stripe_source_id = get_post_meta( $subscription->get_parent_id(), '_stripe_card_id', true );

                // Take this opportunity to update the key name.
                update_post_meta( $subscription->get_parent_id(), '_stripe_source_id', $stripe_source_id );
            }
        }

        $stripe_customer->set_id( $stripe_customer_id );

        $sources                   = $stripe_customer->get_sources();
        $payment_method_to_display = __( 'N/A', 'dokan' );

        if ( $sources ) {
            $card = false;

            foreach ( $sources as $source ) {
                if ( isset( $source->type ) && 'card' === $source->type ) {
                    $card = $source->card;
                } elseif ( isset( $source->object ) && 'card' === $source->object ) {
                    $card = $source;
                }

                if ( $source->id === $stripe_source_id ) {
                    if ( $card ) {
                        /* translators: 1) card brand 2) last 4 digits */
                        $payment_method_to_display = sprintf( __( 'Via %1$s card ending in %2$s', 'dokan' ), ( isset( $card->brand ) ? $card->brand : __( 'N/A', 'dokan' ) ), $card->last4 );
                    }

                    break;
                }
            }
        }

        return $payment_method_to_display;
    }

    /**
     * Create a new PaymentIntent
     *
     * @param \WC_Order $order
     * @param object $prepared_source The source that is used for the payment
     *
     * @return object
     * @throws DokanException
     * @since 3.0.3
     */
    public function create_intent_for_renewal_order( $order, $prepared_source, $amount = null ) {
        $description = sprintf(
            __( '%1$s - Order %2$s', 'dokan' ),
            wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ),
            $order->get_order_number()
        );

        $request = [
            'amount'               => $amount ? Helper::get_stripe_amount( $amount ) : Helper::get_stripe_amount( $order->get_total() ),
            'currency'             => strtolower( $order->get_currency() ),
            'description'          => $description,
            'confirm'              => 'true',
            'off_session'          => 'true',
            'confirmation_method'  => 'automatic',
            'payment_method_types' => [
                'card',
            ],
        ];

        if ( $prepared_source->customer ) {
            $request['customer'] = $prepared_source->customer;
        }

        if ( ! empty( $prepared_source->source ) ) {
            $request['source'] = $prepared_source->source;
        }

        try {
            $intent = PaymentIntent::create( $request );
        } catch ( Exception $e ) {
            throw new DokanException( 'unable_to_create_payment_intent', $e->getMessage() );
        }

        $order->update_meta_data( 'dokan_stripe_intent_id', $intent->id );
        $order->update_meta_data( '_stripe_intent_id', $intent->id );

        if ( is_callable( [ $order, 'save' ] ) ) {
            $order->save();
        }

        return $intent;
    }

    /**
     * Don't transfer Stripe customer/token meta to resubscribe orders.
     * @param int $resubscribe_order The order created for the customer to resubscribe to the old expired/cancelled subscription
     */
    public function delete_resubscribe_meta( $resubscribe_order ) {
        delete_post_meta( $resubscribe_order->get_id(), '_stripe_customer_id' );
        delete_post_meta( $resubscribe_order->get_id(), '_stripe_source_id' );
        // For BW compat will remove in future
        delete_post_meta( $resubscribe_order->get_id(), '_stripe_card_id' );
        // delete payment intent ID
        delete_post_meta( $resubscribe_order->get_id(), '_stripe_intent_id' );
        delete_post_meta( $resubscribe_order->get_id(), 'dokan_stripe_intent_id' );
    }

    /**
     * Don't transfer Stripe fee/ID meta to renewal orders.
     * @param \WC_Order $renewal_order
     * @return \WC_Order
     */
    public function delete_renewal_meta( $renewal_order ) {
        // delete payment intent ID
        delete_post_meta( $renewal_order->get_id(), '_stripe_intent_id' );
        delete_post_meta( $renewal_order->get_id(), 'dokan_stripe_intent_id' );

        return $renewal_order;
    }

    /**
     * Hijacks `wp_redirect` in order to generate a JS-friendly object with the URL.
     *
     * @param string $url The URL that Subscriptions attempts a redirect to.
     * @return void
     */
    public function redirect_after_early_renewal( $url ) {
        echo wp_json_encode(
            [
                'stripe_sca_required' => false,
                'redirect_url'        => $url,
            ]
        );

        exit;
    }

    /**
     * Handle transfer payment
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function handle_transfer_payment( $response, $order ) {
    }

    /**
     * Get payment source from an order. This could be used in the future for
     * a subscription as an example, therefore using the current user ID would
     * not work - the customer won't be logged in :)
     *
     * Not using 2.6 tokens for this part since we need a customer AND a card
     * token, and not just one.
     *
     * @param object $order
     * @return object
     * @throws DokanException
     * @since 3.2.2
     */
    public function prepare_order_source( $order = null ) {
        $stripe_customer = new Customer();
        $stripe_source   = false;
        $token_id        = false;
        $source_object   = false;

        if ( $order ) {
            $order_id = $order->get_id();

            $stripe_customer_id = get_post_meta( $order_id, '_stripe_customer_id', true );

            if ( $stripe_customer_id ) {
                $stripe_customer->set_id( $stripe_customer_id );
            }

            $source_id = $order->get_meta( '_stripe_source_id', true );

            // Since 4.0.0, we changed card to source so we need to account for that.
            if ( empty( $source_id ) ) {
                $source_id = $order->get_meta( '_stripe_card_id', true );

                // Take this opportunity to update the key name.
                $order->update_meta_data( '_stripe_source_id', $source_id );

                if ( is_callable( array( $order, 'save' ) ) ) {
                    $order->save();
                }
            }

            if ( $source_id ) {
                $stripe_source = $source_id;
                $source_object = $this->get_source_object( $source_id );
            } elseif ( apply_filters( 'wc_stripe_use_default_customer_source', true ) ) {
                /*
                 * We can attempt to charge the customer's default source
                 * by sending empty source id.
                 */
                $stripe_source = '';
            }
        }

        return (object) array(
            'token_id'      => $token_id,
            'customer'      => $stripe_customer ? $stripe_customer->get_id() : false,
            'source'        => $stripe_source,
            'source_object' => $source_object,
        );
    }

    /**
     * Checks if a renewal already failed because a manual authentication is required.
     *
     * @param WC_Order $renewal_order The renewal order.
     * @return boolean
     */
    public function has_authentication_already_failed( $renewal_order ) {
        $intent_id = $this->get_intent_from_order( $renewal_order );

        if (
            ! $intent_id
            || 'requires_payment_method' !== $intent_id->status
            || 'requires_source_action' !== $intent_id->status
            || empty( $intent_id->last_payment_error )
            || 'authentication_required' !== $intent_id->last_payment_error->code
        ) {
            return false;
        }

        // Make sure all emails are instantiated.
        \WC_Emails::instance();

        /**
         * A payment attempt failed because SCA authentication is required.
         *
         * @param WC_Order $renewal_order The order that is being renewed.
         */
        do_action( 'wc_gateway_stripe_process_payment_authentication_required', $renewal_order );

        // Fail the payment attempt (order would be currently pending because of retry rules).
        $charge    = end( $intent_id->charges->data );
        $charge_id = $charge->id;
        $renewal_order->update_status( 'failed', sprintf( __( 'Stripe charge awaiting authentication by user: %s.', 'dokan' ), $charge_id ) );

        return true;
    }

    /**
     * Retrieves intent from Stripe API by intent id.
     *
     * @param string $intent_type   Either 'payment_intents' or 'setup_intents'.
     * @param string $intent_id     Intent id.
     * @return object|bool          Either the intent object or `false`.
     * @throws Exception            Throws exception for unknown $intent_type.
     */
    public function get_intent( $intent_type, $intent_id ) {
        if ( ! in_array( $intent_type, [ 'payment_intents', 'setup_intents' ], true ) ) {
            throw new Exception( "Failed to get intent of type $intent_type. Type is not allowed" );
        }

        switch ( $intent_type ) {
            case 'payment_intents':
                try {
                    $intent = PaymentIntent::retrieve(
                        $intent_id,
                        [
                            'expand' => [
                                'charges.data.balance_transaction',
                            ],
                        ]
                    );
                } catch ( Exception $e ) {
                    return false;
                }
                break;

            case 'setup_intents':
                try {
                    $intent = SetupIntent::retrieve(
                        $intent_id,
                        [
                            'expand' => [
                                'charges.data.balance_transaction',
                            ],
                        ]
                    );
                } catch ( Exception $e ) {
                    return false;
                }
                break;
        }

        return $intent;
    }

    /**
     * Checks to see if we need to hide the save checkbox field.
     * Because when cart contains a subs product, it will save regardless.
     *
     * @since 4.0.0
     * @version 4.0.0
     */
    public function maybe_hide_save_checkbox( $display_tokenization ) {
        if ( \WC_Subscriptions_Cart::cart_contains_subscription() ) {
            return false;
        }

        return $display_tokenization;
    }

    /**
     * Checks if gateway should be available to use
     *
     * @since 3.0.3
     *
     * @return bool
     */
    public function is_available() {
        if ( is_add_payment_method_page() && ! $this->saved_cards && ! Helper::is_3d_secure_enabled() ) {
            return false;
        }

        return parent::is_available();
    }

    /**
     * Adds a notice for customer when they update their billing address.
     *
     * @since 3.0.3
     *
     * @param int    $user_id      The ID of the current user.
     * @param string $load_address The address to load.
     *
     * @return void
     */
    public function show_update_card_notice( $user_id, $load_address ) {
        if ( ! $this->saved_cards || ! PaymentTokens::customer_has_saved_methods( $user_id ) || 'billing' !== $load_address ) {
            return;
        }

        wc_add_notice( sprintf( __( 'If your billing address has been changed for saved payment methods, be sure to remove any %1$ssaved payment methods%2$s on file and re-add them.', 'dokan' ), '<a href="' . esc_url( wc_get_endpoint_url( 'payment-methods' ) ) . '" class="wc-stripe-update-card-notice" style="text-decoration:underline;">', '</a>' ), 'notice' );
    }

    /**
     * Setup subcription data
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function set_subscription_data() {
        if ( empty( WC()->session ) ) {
            return;
        }

        $session = WC()->session;

        foreach ( $session->cart as $data ) {
            $product_id = ! empty( $data['product_id'] ) ? $data['product_id'] : 0;
            break;
        }

        $contains_subscription_product = ! empty( $product_id )
            && Helper::has_subscription_module()
            && SubscriptionHelper::is_subscription_product( $product_id );

        if ( $contains_subscription_product ) {
            // @todo in the upcoming version, allow saving cards while purchasing subscription product
            set_transient( $this->id . '_contains_subscription_product', true, MINUTE_IN_SECONDS );
        }

        if ( $contains_subscription_product && SubscriptionHelper::is_recurring_pack( $product_id ) ) {
            $name        = $session->customer['first_name'] . ' ' . $session->customer['last_name'];
            $address_1   = $session->customer['address_1'];
            $address_2   = $session->customer['address_2'];
            $city        = $session->customer['city'];
            $state       = $session->customer['state'];
            $country     = $session->customer['country'];
            $postal_code = $session->customer['postcode'];
            $email       = $session->customer['email'];
            ?>
            <div class="dokan-stripe-intent">
                <input type="hidden" name="dokan_payment_customer_name" id="dokan-payment-customer-name" value="<?php echo esc_attr( $name ); ?>">
                <input type="hidden" name="dokan_payment_customer_email" id="dokan-payment-customer-email" value="<?php echo esc_attr( $email ); ?>">
                <input type="hidden" name="dokan_payment_customer_address_1" id="dokan-payment-customer-address_1" value="<?php echo esc_attr( $address_1 ); ?>">
                <input type="hidden" name="dokan_payment_customer_address_2" id="dokan-payment-customer-address_2" value="<?php echo esc_attr( $address_2 ); ?>">
                <input type="hidden" name="dokan_payment_customer_postal_code" id="dokan-payment-customer-postal_code" value="<?php echo esc_attr( $postal_code ); ?>">
                <input type="hidden" name="dokan_payment_customer_city" id="dokan-payment-customer-city" value="<?php echo esc_attr( $city ); ?>">
                <input type="hidden" name="dokan_payment_customer_state" id="dokan-payment-customer-state" value="<?php echo esc_attr( $state ); ?>">
                <input type="hidden" name="dokan_payment_customer_country" id="dokan-payment-customer-country" value="<?php echo esc_attr( $country ); ?>">
                <input type="hidden" name="dokan_subscription_product_id" id="dokan-subscription-product-id" value="<?php echo esc_attr( $product_id ); ?>">
            </div>
            <?php
        }
    }

    /**
     * Enqueue assets
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function payment_scripts() {
        // Don't load the scripts if the gateway is not available.
        if ( ! $this->is_available() ) {
            return;
        }

        if (
            ! is_checkout()
            && ! isset( $_GET['pay_for_order'] ) // wpcs: csrf ok.
            && ! is_add_payment_method_page()
            && ! isset( $_GET['change_payment_method'] ) // wpcs: csrf ok.
            && ! ( ! empty( get_query_var( 'view-subscription' ) ) && is_callable( '\WCS_Early_Renewal_Manager::is_early_renewal_via_modal_enabled' ) && \WCS_Early_Renewal_Manager::is_early_renewal_via_modal_enabled() )
            || ( is_order_received_page() )
        ) {
            return;
        }

        wp_enqueue_style( 'dokan_stripe', DOKAN_STRIPE_ASSETS . 'css/stripe.css' );

        if ( ! Helper::is_3d_secure_enabled() && $this->checkout_modal && ! is_add_payment_method_page() ) {
            wp_enqueue_script( 'stripe', 'https://checkout.stripe.com/v2/checkout.js', [], '2.0', true );

            $dokan_stripe_version = filemtime( plugin_dir_path( __FILE__ ) . '../assets/js/stripe-checkout.js' );
            wp_enqueue_script( 'dokan_stripe', plugins_url( 'assets/js/stripe-checkout.js', dirname( __FILE__ ) ), [ 'stripe' ], $dokan_stripe_version, true );
        } elseif ( ! Helper::is_3d_secure_enabled() && ! is_add_payment_method_page() ) {
            $this->tokenization_script();
            wp_enqueue_script( 'stripe', 'https://js.stripe.com/v1/', [], '1.0', true );

            $dokan_stripe_version = filemtime( plugin_dir_path( __FILE__ ) . '../assets/js/stripe.js' );
            wp_enqueue_script( 'dokan_stripe', plugins_url( 'assets/js/stripe.js', dirname( __FILE__ ) ), [ 'jquery', 'stripe' ], $dokan_stripe_version, false );
        }

        if ( Helper::is_3d_secure_enabled() || is_add_payment_method_page() ) {
            $this->tokenization_script();
            wp_enqueue_script( 'stripe', 'https://js.stripe.com/v3/', [], [], true );

            $dokan_stripe_version = filemtime( plugin_dir_path( __FILE__ ) . '../assets/js/stripe-3ds.js' );
            wp_enqueue_script( 'dokan_stripe', plugins_url( 'assets/js/stripe-3ds.js', dirname( __FILE__ ) ), [ 'jquery', 'stripe' ], $dokan_stripe_version, true );
        }

        $stripe_params = [
            'is_3ds'                => Helper::is_3d_secure_enabled(),
            'key'                   => $this->publishable_key,
            'is_checkout'           => is_checkout() & empty( $_GET['pay_for_order'] ) ? 'yes' : 'no',
            'is_pay_for_order_page' => is_wc_endpoint_url( 'order-pay' ) ? 'yes' : 'no',
            'is_change_payment_page' => isset( $_GET['change_payment_method'] ) ? 'yes' : 'no', // wpcs: csrf ok.
            'is_add_payment_page'   => is_wc_endpoint_url( 'add-payment-method' ) ? 'yes' : 'no',
            'name'                  => get_bloginfo( 'name' ),
            'description'           => get_bloginfo( 'description' ),
            'label'                 => $this->checkout_label,
            'locale'                => $this->checkout_locale,
            'image'                 => $this->checkout_image,
            'i18n_terms'            => __( 'Please accept the terms and conditions first', 'dokan' ),
            'i18n_required_fields'  => __( 'Please fill in required checkout fields first', 'dokan' ),
            'invalid_request_error' => __( 'Unable to process this payment, please try again or use alternative method.', 'dokan' ),
            'email_invalid'         => __( 'Invalid email address, please correct and try again.', 'dokan' ),
            'add_card_nonce'        => wp_create_nonce( 'dokan_stripe_create_si' ),
            'ajaxurl'               => WC_AJAX::get_endpoint( '%%endpoint%%' ),
        ];

        if ( is_checkout_pay_page() || isset( $_GET['pay_for_order'] ) && 'true' === $_GET['pay_for_order'] ) { // phpcs:ignore WordPress.Security.NonceVerification
            if ( is_checkout_pay_page() && isset( $_GET['order'] ) && isset( $_GET['order_id'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
                $order_key = urldecode( wp_unslash( $_GET['order'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
                $order_id  = absint( wp_unslash( $_GET['order_id'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
                $order     = wc_get_order( $order_id );
            }

            // If we're on the pay page we need to pass stripe.js the address of the order.
            if ( isset( $_GET['pay_for_order'] ) && 'true' === sanitize_text_field( wp_unslash( $_GET['pay_for_order'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification
                $order_key = urldecode( wp_unslash( $_GET['key'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
                $order_id  = wc_get_order_id_by_order_key( $order_key );
                $order     = wc_get_order( $order_id );
            }

            if ( empty( $order ) ) {
                return;
            }

            if ( dokan_get_prop( $order, 'id' ) == $order_id && dokan_get_prop( $order, 'order_key' ) == $order_key ) {
                $stripe_params['billing_first_name'] = dokan_get_prop( $order, 'billing_first_name' );
                $stripe_params['billing_last_name']  = dokan_get_prop( $order, 'billing_last_name' );
                $stripe_params['billing_address_1']  = dokan_get_prop( $order, 'billing_address_1' );
                $stripe_params['billing_address_2']  = dokan_get_prop( $order, 'billing_address_2' );
                $stripe_params['billing_state']      = dokan_get_prop( $order, 'billing_state' );
                $stripe_params['billing_city']       = dokan_get_prop( $order, 'billing_city' );
                $stripe_params['billing_postcode']   = dokan_get_prop( $order, 'billing_postcode' );
                $stripe_params['billing_country']    = dokan_get_prop( $order, 'billing_country' );
            }
        }

        wp_localize_script( 'dokan_stripe', 'dokan_stripe_connect_params', apply_filters( 'dokan_stripe_js_params', $stripe_params ) );
    }

    /**
     * Admin options in WC payments settings
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function admin_options() {
        Helper::get_template( 'admin-options', [ 'gateway' => $this ] );
    }

    /**
     * Payment form on checkout page
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function payment_fields() {
        $user                 = wp_get_current_user();
        $display_tokenization = $this->supports( 'tokenization' ) && is_checkout() && $this->saved_cards;
        $total                = WC()->cart->total;
        $user_email           = '';
        $description          = $this->get_description();
        $description          = ! empty( $description ) ? $description : '';
        $firstname            = '';
        $lastname             = '';

        // If paying from order, we need to get total from order not cart.
        if ( isset( $_GET['pay_for_order'] ) && ! empty( $_GET['key'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            $order      = wc_get_order( wc_get_order_id_by_order_key( wc_clean( wp_unslash( $_GET['key'] ) ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
            $total      = $order->get_total();
            $user_email = $order->get_billing_email();
        } else {
            if ( $user->ID ) {
                $user_email = get_user_meta( $user->ID, 'billing_email', true );
                $user_email = $user_email ? $user_email : $user->user_email;
            }
        }

        if ( is_add_payment_method_page() ) {
            $firstname = $user->user_firstname;
            $lastname  = $user->user_lastname;
        }

        ob_start();
        echo '<div
            id="dokan-stripe-payment-data"
            data-email="' . esc_attr( $user_email ) . '"
            data-full-name="' . esc_attr( $firstname . ' ' . $lastname ) . '"
            data-currency="' . esc_attr( strtolower( get_woocommerce_currency() ) ) . '"
        >';

        if ( $this->testmode ) {
            $description .= ' ' . sprintf( __( 'TEST MODE ENABLED. In test mode, you can use the card number 4242424242424242 and 4000002500003155 for testing 3D Secure with any CVC and a valid expiration date or check the %1$s Testing Stripe documentation %2$s for more card numbers.', 'dokan' ), '<a href="https://stripe.com/docs/testing" target="_blank">', '</a>' );
        }

        $description                   = trim( $description );
        $contains_subscription_product = get_transient( $this->id . '_contains_subscription_product' );
        echo apply_filters( 'dokan_stripe_description', wpautop( wp_kses_post( $description ) ), $this->id ); // wpcs: xss ok.

        if ( ! $contains_subscription_product && $display_tokenization ) {
            $this->tokenization_script();
            $this->saved_payment_methods();
        }

        $this->elements_form();

        if ( ! $contains_subscription_product
            && ! is_add_payment_method_page()
            && apply_filters( 'dokan_stripe_display_save_payment_method_checkbox', $display_tokenization ) ) {
            $this->save_payment_method_checkbox();
        }

        do_action( 'dokan_stripe_cards_payment_fields', $this->id );
        echo '</div>';
        ob_end_flush();
    }

    /**
     * Renders the Stripe elements form
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function elements_form() {
        ?>
        <fieldset id="wc-<?php echo esc_attr( $this->id ); ?>-cc-form" class="wc-credit-card-form wc-payment-form" style="background:transparent;">
            <?php do_action( 'woocommerce_credit_card_form_start', $this->id ); ?>

            <?php if ( Helper::is_3d_secure_enabled() || is_add_payment_method_page() ) : ?>
                <label for="dokan-stripe-card-element">
                    <?php esc_html_e( 'Credit or debit card', 'dokan' ); ?>
                </label>

                <div id="dokan-stripe-card-element" class="dokan-stripe-elements-field">
                    <!-- a Stripe Element will be inserted here. -->
                </div>

                <div class="dokan-stripe-intent"></div>
                <div class="stripe-source-errors" role="alert">
                    <!-- Used to display form errors -->
                </div>

            <?php else : ?>
                <div
                    class="stripe_new_card"
                    data-amount="<?php echo esc_attr( Helper::get_stripe_amount( WC()->cart->total ) ); ?>"
                    data-currency="<?php echo esc_attr( strtolower( get_woocommerce_currency() ) ); ?>"
                >
                    <?php
                    if ( ! $this->checkout_modal ) {
                        $this->form();
                    }
                    ?>
                </div>
            <?php endif; ?>

            <?php do_action( 'woocommerce_credit_card_form_end', $this->id ); ?>
            <div class="clear"></div>
        </fieldset>
        <?php
    }

    /**
     * Process payment for the order
     *
     * @since 3.0.3
     *
     * @param int $oder_id
     * @throws DokanException
     * @return array
     */
    public function process_payment( $order_id ) {
        $order          = wc_get_order( $order_id );
        $payment_method = Helper::payment_method(); // 3ds or non_3ds
        $response       = [];

        try {
            $response = DokanStripe::process( $order )->with( $payment_method )->pay();
        } catch( DokanException $e ) {
            throw new DokanException(
                'dokan_process_payment_failed',
                $e->get_message()
            );
        }

        return $response;
    }

    /**
     * Attached to `woocommerce_payment_successful_result` with a late priority,
     * this method will combine the "naturally" generated redirect URL from
     * WooCommerce and a payment/setup intent secret into a hash, which contains both
     * the secret, and a proper URL, which will confirm whether the intent succeeded.
     *
     * @since 3.0.3
     *
     * @param array $result   The result from `process_payment`.
     * @param int   $order_id The ID of the order which is being paid for.
     *
     * @return array
     */
    public function modify_successful_payment_result( $result, $order_id ) {
        // Only redirects with intents need to be modified.
        if ( ! isset( $result['payment_intent_secret'] ) ) {
            return $result;
        }

        // get order object
        $order = wc_get_order( $order_id );

        // Put the final thank you page redirect into the verification URL.
        $verification_url = add_query_arg(
            [
                'order'       => $order_id,
                'nonce'       => wp_create_nonce( 'dokan_stripe_confirm_pi' ),
                'key'         => $order->get_order_key(),
                'redirect_to' => rawurlencode( $result['redirect'] ),
            ],
            WC_AJAX::get_endpoint( 'dokan_stripe_verify_intent' )
        );

        return [
            'result'   => 'success',
            'redirect' => sprintf( '#confirm-pi-%s:%s', $result['payment_intent_secret'], rawurlencode( $verification_url ) ),
        ];
    }

    /**
     * Preserves the "dokan-stripe-confirmation" URL parameter so the user can complete the SCA authentication after logging in.
     *
     * @since 3.2.2
     * @param string $pay_url Current computed checkout URL for the given order.
     * @param WC_Order $order Order object.
     *
     * @return string Checkout URL for the given order.
     */
    public function get_checkout_payment_url( $pay_url, $order ) {
        global $wp;
        if ( isset( $_GET['dokan-stripe-confirmation'] ) && isset( $wp->query_vars['order-pay'] ) && (int) $wp->query_vars['order-pay'] === (int) $order->get_id() ) { // phpcs:ignore WordPress.Security.NonceVerification
            $pay_url = add_query_arg( 'dokan-stripe-confirmation', 1, $pay_url );
        }
        return $pay_url;
    }

    /**
     * Adds the necessary hooks to modify the "Pay for order" page in order to clean
     * it up and prepare it for the Stripe PaymentIntents modal to confirm a payment.
     *
     * @param WC_Payment_Gateway[] $gateways A list of all available gateways.
     * @return WC_Payment_Gateway[]          Either the same list or an empty one in the right conditions.
     * @since 3.2.2
     */
    public function prepare_order_pay_page( $gateways ) {
        if ( ! is_wc_endpoint_url( 'order-pay' ) || ! isset( $_GET['dokan-stripe-confirmation'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            return $gateways;
        }

        try {
            $this->prepare_intent_for_order_pay_page();
        } catch ( DokanException $e ) {
            dokan_log( 'Stripe prepare order pay page exception: ' . $e->getMessage() );
            // Just show the full order pay page if there was a problem preparing the Payment Intent
            return $gateways;
        }

        add_filter( 'woocommerce_checkout_show_terms', '__return_false' );
        add_filter( 'woocommerce_pay_order_button_html', '__return_false' );
        add_filter( 'woocommerce_available_payment_gateways', '__return_empty_array' );
        add_filter( 'woocommerce_no_available_payment_methods_message', [ $this, 'change_no_available_methods_message' ] );
        add_action( 'woocommerce_pay_order_after_submit', [ $this, 'render_payment_intent_inputs' ] );

        return [];
    }

    /**
     * Changes the text of the "No available methods" message to one that indicates
     * the need for a PaymentIntent to be confirmed.
     *
     * @since 3.2.2
     * @return string the new message.
     */
    public function change_no_available_methods_message() {
        return wpautop( __( "Almost there!\n\nYour order has already been created, the only thing that still needs to be done is for you to authorize the payment with your bank.", 'dokan' ) );
    }

    /**
     * Renders hidden inputs on the "Pay for Order" page in order to let Stripe handle PaymentIntents.
     *
     * @param WC_Order|null $order Order object, or null to get the order from the "order-pay" URL parameter
     *
     * @throws DokanException
     * @since 3.2.2
     */
    public function render_payment_intent_inputs( $order = null ) {
        if ( ! isset( $order ) || empty( $order ) ) {
            $order = wc_get_order( absint( get_query_var( 'order-pay' ) ) );
        }
        if ( ! isset( $this->order_pay_intent ) ) {
            $this->prepare_intent_for_order_pay_page( $order );
        }

        $verification_url = add_query_arg(
            array(
                'order'            => $order->get_id(),
                'key'              => $order->get_order_key(),
                'nonce'            => wp_create_nonce( 'dokan_stripe_confirm_pi' ),
                'redirect_to'      => rawurlencode( $this->get_return_url( $order ) ),
                'is_pay_for_order' => true,
            ),
            WC_AJAX::get_endpoint( 'dokan_stripe_verify_intent' )
        );

        echo '<input type="hidden" id="dokan-stripe-intent-id" value="' . esc_attr( $this->order_pay_intent->client_secret ) . '" />';
        echo '<input type="hidden" id="dokan-stripe-intent-return" value="' . esc_attr( $verification_url ) . '" />';
    }

    /**
     * Prepares the Payment Intent for it to be completed in the "Pay for Order" page.
     *
     * @param WC_Order|null $order Order object, or null to get the order from the "order-pay" URL parameter
     *
     * @throws DokanException
     * @since 3.2.2
     */
    public function prepare_intent_for_order_pay_page( $order = null ) {
        if ( ! isset( $order ) || empty( $order ) ) {
            $order = wc_get_order( absint( get_query_var( 'order-pay' ) ) );
        }

        $intent = $this->get_intent_from_order( $order );

        if ( ! $intent ) {
            throw new DokanException( 'Payment Intent not found', sprintf( __( 'Payment Intent not found for order #%s', 'dokan' ), $order->get_id() ) );
        }

        if ( 'requires_payment_method' === $intent->status && isset( $intent->last_payment_error )
            && 'authentication_required' === $intent->last_payment_error->code ) {
            try {
                $intent = PaymentIntent::confirm(
                    [
                        'payment_method' => $intent->last_payment_error->source->id,
                    ]
                );

                if ( isset( $intent->error ) ) {
                    throw new DokanException( print_r( $intent, true ), $intent->error->message );
                }
            } catch ( Exception $e ) {
                throw new DokanException( print_r( $intent, true ), $e->getMessage() );
            }
        }

        $this->order_pay_intent = $intent;
    }

    /**
     * If this is the "Pass the SCA challenge" flow, remove a variable that is checked by WC Subscriptions
     * so WC Subscriptions doesn't redirect to the checkout
     */
    public function remove_order_pay_var() {
        global $wp;
        if ( isset( $_GET['dokan-stripe-confirmation'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            $this->order_pay_var = $wp->query_vars['order-pay'];
            $wp->query_vars['order-pay'] = null;
        }
    }

    /**
     * Restore the variable that was removed in remove_order_pay_var()
     */
    public function restore_order_pay_var() {
        global $wp;
        if ( isset( $this->order_pay_var ) ) {
            $wp->query_vars['order-pay'] = $this->order_pay_var;
        }
    }

    /**
     * Create webhook url on stripe end via api.
     *
     * @since 3.2.2
     * @return void
     */
    public function process_admin_options() {
        parent::process_admin_options();

        $instance = dokan_pro()->module->stripe->webhook;
        if ( ! $instance instanceof WebhookHandler ) {
            return;
        }

        // todo: will store webhook id in option table
        $instance->register_webhook();
    }
}
