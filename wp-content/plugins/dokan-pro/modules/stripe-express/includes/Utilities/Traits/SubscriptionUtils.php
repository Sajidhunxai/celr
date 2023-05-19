<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Utilities\Traits;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WC_AJAX;
use WC_Order;
use Exception;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Api\SetupIntent;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Order;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Token;
use WeDevs\DokanPro\Modules\StripeExpress\Support\UserMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Api\PaymentMethod;
use WeDevs\DokanPro\Modules\StripeExpress\Api\PaymentIntent;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Payment;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Customer;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Subscription;

/**
 * Trait for subscription utility functions.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Utilities\Traits
 */
trait SubscriptionUtils {

    /**
     * Processes payment for subscriptions.
     *
     * @since 3.7.8
     *
     * @param float    $amount
     * @param WC_Order $renewal_order
     * @param boolean  $retry
     * @param object   $previous_error
     *
     * @return void
     */
    public function process_subscription_payment( $amount, $renewal_order, $retry = true, $previous_error = false ) {
        try {
            if ( ! $renewal_order instanceof WC_Order ) {
                throw new Exception( 'invalid_order_object', __( 'Invalid renewal order.', 'dokan' ) );
            }

            $order_id = $renewal_order->get_id();

            /*
             * Unlike regular off-session subscription payments,
             * early renewals are treated as on-session payments, involving the customer.
             * This makes the SCA authorization popup show up for the "Renew early" modal
             * (Subscriptions settings > Accept Early Renewal Payments via a Modal).
             */
            if ( isset( $_REQUEST['process_early_renewal'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $response = $this->process_payment( $order_id, true, false, $previous_error, true );

                if ( 'success' === $response['result'] && isset( $response['payment_intent_secret'] ) ) {
                    $verification_url = add_query_arg(
                        [
                            'order'         => $order_id,
                            'nonce'         => wp_create_nonce( 'dokan_stripe_express_confirm_pi' ),
                            'redirect_to'   => esc_url_raw( remove_query_arg( [ 'process_early_renewal', 'subscription_id', 'wcs_nonce' ] ) ),
                            'early_renewal' => true,
                        ],
                        WC_AJAX::get_endpoint( 'dokan_stripe_express_verify_intent' )
                    );

                    echo wp_json_encode(
                        [
                            'intent_secret'                     => $response['payment_intent_secret'],
                            'redirect_url'                      => $verification_url,
                            'dokan_stripe_express_sca_required' => true,
                        ]
                    );

                    exit;
                }

                // Hijack all other redirects in order to do the redirection in JavaScript.
                add_action( 'wp_redirect', [ $this, 'redirect_after_early_renewal' ], 100 );

                return;
            }

            if ( Order::check_if_authentication_failed( $renewal_order ) ) {
                return;
            }

            // Get source from order
            $prepared_source = Order::prepare_source( $renewal_order );

            if ( ! $prepared_source->customer ) {
                throw new Exception(
                    sprintf(
                        /* translators: %s) order id */
                        __( 'Failed to process renewal for order %s. Stripe customer id is missing in the order', 'dokan' ),
                        $renewal_order->get_id()
                    )
                );
            }

            Helper::log( "Info: Begin processing subscription payment for order {$order_id} for the amount of {$amount}" );

            if ( ( Helper::is_no_such_source_error( $previous_error ) || Helper::is_no_linked_source_error( $previous_error ) ) && apply_filters( 'dokan_stripe_express_use_default_customer_source', true ) ) {
                // Passing empty source will charge customer default.
                $prepared_source->payment_method = '';
            }

            Order::lock_processing( $renewal_order->get_id() );

            $intent_data = [
                'payment_method'      => $prepared_source->payment_method,
                'customer'            => $prepared_source->customer,
                'confirm'             => 'true',
                'off_session'         => 'true',
                'confirmation_method' => 'automatic',
            ];

            if ( isset( $prepared_source->payment_method_object ) ) {
                if ( isset( $prepared_source->payment_method_object->type ) ) {
                    // The payment method type needs to be bound with the intent
                    $intent_data['payment_method_types'] = [ $prepared_source->payment_method_object->type ];
                }
                // This parameter is not available for intent
                unset( $prepared_source->payment_method_object );
            }

            $intent = Payment::create_intent( $renewal_order, $intent_data );

            $intent_id = ! empty( $intent->error )
                ? $intent->error->payment_intent->id
                : $intent->id;

            $payment_intent = ! empty( $intent->error )
                ? $intent->error->payment_intent
                : $intent;

            $order_id = $renewal_order->get_id();
            Helper::log( "Stripe PaymentIntent $intent_id initiated for renewal order: $order_id" );

            // Save the intent ID to the order.
            Payment::save_intent_data( $renewal_order, $payment_intent );

            $is_authentication_required = Payment::is_authentication_required( $intent );

            /*
             * It's only a failed payment if it's an error and it's not of the type 'authentication_required'.
             * If it's 'authentication_required', then we should email the user and ask them to authenticate.
             */
            if ( ! empty( $intent->error ) && ! $is_authentication_required ) {
                if ( Helper::is_retryable_error( $intent->error ) ) {
                    if ( $retry ) {
                        // Don't do anymore retries after this.
                        if ( 5 <= $this->retry_interval ) {
                            return $this->process_subscription_payment( $amount, $renewal_order, false, $intent->error );
                        }

                        sleep( $this->retry_interval );

                        $this->retry_interval++;

                        return $this->process_subscription_payment( $amount, $renewal_order, true, $intent->error );
                    } else {
                        $localized_message = __( 'Sorry, we are unable to process your payment at this time. Please retry later.', 'dokan' );

                        Order::add_note( $renewal_order, $localized_message );
                        throw new Exception( $localized_message );
                    }
                }

                $error_message = Helper::get_error_message_from_response( $intent );

                Order::add_note( $renewal_order, $error_message );
                throw new Exception( $error_message );
            }

            // Either the charge was successfully captured, or it requires further authentication.
            if ( $is_authentication_required ) {
                do_action( 'dokan_stripe_express_process_payment_authentication_required', $renewal_order, $intent );

                $error_message = __( 'This transaction requires authentication.', 'dokan' );
                Order::add_note( $renewal_order, $error_message );

                $charge   = Payment::get_latest_charge_from_intent( $intent->error->payment_intent );
                $order_id = $renewal_order->get_id();

                $renewal_order->set_transaction_id( $charge->id );
                $renewal_order->update_status(
                    'failed',
                    /* translators: %1$s) gateway title */
                    sprintf(
                        __( 'Stripe charge%s has awaiting authentication by user.', 'dokan' ),
                        isset( $charge->id ) ? " ($charge->id)" : ''
                    )
                );

                OrderMeta::save( $renewal_order );
            } else {
                // Use the last charge within the intent to proceed or the original response in case of SEPA
                $response = Payment::get_latest_charge_from_intent( $intent );
                if ( ! $response ) {
                    $response = $intent;
                }
                Payment::process_response( $response, $renewal_order );
            }

            Order::unlock_processing( $renewal_order->get_id() );
        } catch ( Exception $e ) {
            Helper::log( 'Subscription payment processing error: ' . $e->getMessage() );

            do_action( 'dokan_stripe_express_process_payment_error', $e, $renewal_order );

            $renewal_order->update_status( 'failed' );
        }
    }

    /**
     * Processes subscription payments.
     *
     * @since 3.7.8
     *
     * @param int     $order_id ID of the order being processed.
     * @param boolean $retry    (Optional) Whether this is a retry attempt.
     *
     * @return array
     */
    public function process_subscription( $order_id, $retry = true ) {
        // phpcs:disable WordPress.Security.NonceVerification.Missing

        $subscription_id = ! empty( $_POST['subscription_id'] ) ? sanitize_text_field( wp_unslash( $_POST['subscription_id'] ) ) : '';
        $order           = wc_get_order( $order_id );

        OrderMeta::update_stripe_subscription_id( $order, $subscription_id );
        UserMeta::update_stripe_debug_subscription_id( $order->get_customer_id(), $subscription_id );
        OrderMeta::save( $order );

        $payment_needed = Helper::is_payment_needed( $order_id );
        if ( $payment_needed ) {
            $this->validate_minimum_order_amount( $order_id );
        }

        // Synchronise the user/customer with Stripe.
        $user        = Order::get_user_from_order( $order );
        $customer_id = Order::get_stripe_customer_id_from_order( $order );
        if ( empty( $customer_id ) ) {
            $customer = Customer::set( $user->ID );
            $customer = $customer->update_or_create();
            if ( is_wp_error( $customer ) ) {
                throw new Exception( $customer->get_error_message() );
            }
        }

        $selected_payment_type = ! empty( $_POST['dokan_stripe_express_payment_type'] )
            ? sanitize_text_field( wp_unslash( $_POST['dokan_stripe_express_payment_type'] ) )
            : '';

        if ( ! empty( $selected_payment_type ) ) {
            Payment::set_method_title( $order, $selected_payment_type );

            if ( ! $this->payment_methods[ $selected_payment_type ]->is_allowed_on_country( $order->get_billing_country() ) ) {
                throw new Exception( __( 'This payment method is not available on the selected country', 'dokan' ) );
            }

            OrderMeta::update_payment_type( $order, $selected_payment_type );
            OrderMeta::save( $order );
        }

        if ( Helper::is_using_saved_payment_method() ) {
            return $this->process_subscription_with_saved_payment_method( $order_id, $subscription_id, $retry );
        }

        $order->update_status( 'pending', __( 'Awaiting payment.', 'dokan' ) );

        return [
            'result'         => 'success',
            'payment_needed' => $payment_needed,
            'order_id'       => $order_id,
            'redirect_url'   => wp_sanitize_redirect(
                esc_url_raw(
                    add_query_arg(
                        [
                            'order_id'            => $order_id,
                            'wc_payment_method'   => Helper::get_gateway_id(),
                            '_wpnonce'            => wp_create_nonce( 'dokan_stripe_express_process_redirect_order' ),
                            'save_payment_method' => Helper::is_using_saved_payment_method() ? 'no' : 'yes',
                        ],
                        $this->get_return_url( $order )
                    )
                )
            ),
        ];
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }

    /**
     * Processes subscription that is being created using a saved payment method.
     *
     * @since 3.7.8
     *
     * @param int     $order_id        ID of the order being processed.
     * @param string  $subscription_id The Stripe subscription ID.
     * @param boolean $can_retry       (Optional) Indicates if the order can be retried if failed.
     *
     * @return array
     */
    public function process_subscription_with_saved_payment_method( $order_id, $subscription_id, $can_retry = true ) {
        try {
            $token = Token::parse_from_request();
            if ( empty( $token ) ) {
                throw new DokanException(
                    'dokan_stripe_express_invalid_payment_method',
                    __( 'The payment method cannot be used to place the order. Please choose another and try again.', 'dokan' )
                );
            }

            $payment_method = PaymentMethod::get( $token->get_token() );
            if ( ! $payment_method ) {
                throw new DokanException(
                    'dokan_stripe_express_invalid_payment_method',
                    __( 'The payment method cannot be used to place the order. Please choose another and try again.', 'dokan' )
                );
            }

            $payment_needed = Helper::is_payment_needed( $order_id );
            $order          = wc_get_order( $order_id );

            Helper::maybe_disallow_prepaid_card( $payment_method );
            Payment::save_payment_method_data( $order, $payment_method );
            OrderMeta::update_payment_type( $order, $payment_method->type );
            OrderMeta::save( $order );

            Helper::log( "Processing vendor subscription with saved payment method for order $order_id for the amount of {$order->get_total()}", 'Order', 'info' );

            $subscription = Subscription::get( $subscription_id );
            $intent_data  = [ 'payment_method' => $payment_method->id ];

            if ( $payment_needed && ! empty( $subscription->latest_invoice->payment_intent ) ) {
                $intent = PaymentIntent::update( $subscription->latest_invoice->payment_intent->id, $intent_data );
                OrderMeta::add_payment_intent( $order, $intent->id );
            } else {
                $intent = SetupIntent::update( $subscription->pending_setup_intent->id, $intent_data );
                OrderMeta::add_setup_intent( $order, $intent->id );
            }

            OrderMeta::save( $order );

            /**
             * Process payment when needed.
             *
             * @since 3.7.8
             *
             * @param WC_Order $order             The order being processed.
             * @param string   $payment_method_id The source of the payment.
             */
            do_action( 'dokan_stripe_express_process_payment', $order, $payment_method->id );

            $is_authentication_required = isset( $intent->status ) &&
                (
                    'requires_action' === $intent->status ||
                    'requires_confirmation' === $intent->status
                );

            /*
             * It's only a failed payment if it's an error and it's not of the type 'authentication_required'.
             * If it's 'authentication_required', then we should email the user and ask them to authenticate.
             */
            if ( ! empty( $intent->error ) && ! $is_authentication_required ) {
                if ( Helper::is_retryable_error( $intent->error ) ) {
                    if ( $can_retry ) {
                        if ( 5 <= $this->retry_interval ) {
                            return $this->process_subscription_with_saved_payment_method( $order_id, $subscription_id, $can_retry );
                        }

                        sleep( $this->retry_interval );

                        $this->retry_interval++;

                        return $this->process_subscription_with_saved_payment_method( $order_id, true, $intent->error );
                    } else {
                        $localized_message = __( 'Sorry, we are unable to process your payment at this time. Please retry later.', 'dokan' );

                        Order::add_note( $order, $localized_message );
                        throw new Exception( $localized_message );
                    }
                }

                $error_message = Helper::get_error_message_from_response( $intent );

                Order::add_note( $order, $error_message );

                throw new Exception( $error_message );
            }

            if ( $is_authentication_required ) {
                if (
                    isset( $intent->next_action->type ) &&
                    'redirect_to_url' === $intent->next_action->type &&
                    ! empty( $intent->next_action->redirect_to_url->url )
                ) {
                    return [
                        'result'   => 'success',
                        'redirect' => $intent->next_action->redirect_to_url->url,
                    ];
                }

                return [
                    'result'   => 'success',
                    /*
                        * Include a new nonce for update_order_status
                        * to ensure the update order status call works
                        * when a guest user creates an account during checkout.
                        */
                    'redirect' => sprintf(
                        '#dokan-stripe-express-confirm-%s:%s:%s:%s:%s',
                        $payment_needed ? 'pi' : 'si',
                        $order_id,
                        $intent->client_secret,
                        $payment_method->type,
                        wp_create_nonce( 'dokan_stripe_express_update_order_status' )
                    ),
                ];
            }

            list( $payment_method_type, $payment_method_details ) = Payment::get_method_data_from_intent( $intent );

            Payment::set_method_title( $order, $payment_method_type );

            // Remove cart.
            if ( isset( WC()->cart ) ) {
                WC()->cart->empty_cart();
            }

            // Return thank you page redirect.
            return [
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order ),
            ];
        } catch ( DokanException $e ) {
            wc_add_notice( $e->get_message(), 'error' );
            Helper::log( 'Error: ' . $e->get_message() );

            do_action( 'dokan_stripe_express_process_payment_error', $e, $order );

            /* translators: error message */
            $order->update_status( 'failed' );

            return [
                'result'   => 'fail',
                'redirect' => '',
            ];
        }
    }

    /**
     * Process the payment method change for subscriptions.
     *
     * @since 3.7.8
     *
     * @param int $order_id
     *
     * @return array|null
     */
    public function change_subscription_payment_method( $order_id ) {
        try {
            $subscription    = wc_get_order( $order_id );
            $prepared_source = Payment::prepare_source( get_current_user_id(), true );

            Helper::maybe_disallow_prepaid_card( $prepared_source->payment_method_object );

            if ( empty( $prepared_source->payment_method ) ) {
                throw new Exception( __( 'Invalid Payment Source: Payment processing failed. Please retry.', 'dokan' ) );
            }

            Order::save_source( $subscription, $prepared_source );

            /**
             * Fires when a subscription's payment method is changed.
             *
             * @since 3.7.8
             *
             * @param string $payment_method_id The ID of the Stripe payment method to change to.
             * @param object $prepared_source   The source object to use for the payment method change.
             */
            do_action( 'dokan_stripe_express_change_subscription_payment_method_success', $prepared_source->payment_method, $prepared_source );

            return [
                'result'   => 'success',
                'redirect' => $this->get_return_url( $subscription ),
            ];
        } catch ( \Exception $e ) {
            wc_add_notice( $e->getMessage(), 'error' );
            Helper::log( 'Error: ' . $e->getMessage() );
        }
    }

    /**
     * Hijacks `wp_redirect` in order to generate a JS-friendly object with the URL.
     *
     * @since 3.7.8
     *
     * @param string $url The URL that Subscriptions attempts a redirect to.
     *
     * @return void
     */
    public function redirect_after_early_renewal( $url ) {
        echo wp_json_encode(
            [
                'redirect_url'                      => $url,
                'dokan_stripe_express_sca_required' => false,
            ]
        );

        exit;
    }

    /**
     * Retrieves card statuses to update subscription payment methods.
     *
     * @since 3.7.8
     *
     * @return string[]
     */
    public function get_payment_method_card_statuses() {
        return apply_filters(
            'dokan_stripe_express_subscription_payment_method_card_statuses',
            [
                'card',
            ]
        );
    }
}
