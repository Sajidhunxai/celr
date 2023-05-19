<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Controllers;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WC_Order;
use Exception;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Settings;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Order;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Api\PaymentIntent;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Payment;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Customer;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Subscription;

/**
 * Ajax controller class for checkout.
 *
 * Handles in-checkout AJAX calls, related to Payment Intents.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Controllers
 */
class Checkout {

    /**
     * Holds an instance of the gateway class.
     *
     * @since 3.6.1
     *
     * @var \WeDevs\DokanPro\Modules\StripeExpress\PaymentGateways\Stripe
     */
    protected $gateway;

    /**
     * Class constructor.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * Registers all necessary hooks.
     *
     * @since 3.6.1
     *
     * @return void
     */
    private function hooks() {
        add_action( 'wc_ajax_dokan_stripe_express_init_setup_intent', [ $this, 'init_setup_intent' ] );
        add_action( 'wc_ajax_dokan_stripe_express_create_payment_intent', [ $this, 'create_payment_intent' ] );
        add_action( 'wc_ajax_dokan_stripe_express_update_payment_intent', [ $this, 'update_payment_intent' ] );
        add_action( 'wc_ajax_dokan_stripe_express_verify_intent', [ $this, 'verify_intent' ] );
        add_action( 'wc_ajax_dokan_stripe_express_create_subscription', [ $this, 'create_subscription' ] );

        add_action( 'wc_ajax_dokan_stripe_express_update_order_status', [ $this, 'update_order_status' ] );
        add_action( 'wc_ajax_dokan_stripe_express_update_failed_order', [ $this, 'update_failed_order' ] );

        add_action( 'woocommerce_checkout_order_review', [ $this, 'maybe_set_subscription_data' ] );
        add_filter( 'woocommerce_available_payment_gateways', [ $this, 'filter_available_payment_gateways' ] );
    }

    /**
     * Returns an instantiated gateway.
     *
     * @since 3.6.1
     *
     * @return \WeDevs\DokanPro\Modules\StripeExpress\PaymentGateways\Stripe
     */
    protected function gateway() {
        if ( ! isset( $this->gateway ) ) {
            $this->gateway = Helper::get_gateway_instance();
        }

        return $this->gateway;
    }

    /**
     * Handle AJAX requests for creating a payment intent for Stripe.
     *
     * @since 3.6.1
     *
     * @return mixed
     */
    public function create_payment_intent() {
        try {
            if ( ! check_ajax_referer( 'dokan_stripe_express_checkout', false, false ) ) {
                throw new DokanException( 'create_payment_intent_error', __( "We're not able to process this payment. Please refresh the page and try again.", 'dokan' ) );
            }

            // If paying from order, we need to get the total from the order instead of the cart.
            $order_id = isset( $_POST['order_id'] ) ? absint( wp_unslash( $_POST['order_id'] ) ) : null;
            $order    = wc_get_order( $order_id );

            if ( ! is_a( $order, 'WC_Order' ) ) {
                $amount   = WC()->cart->get_total( false );
                $currency = get_woocommerce_currency();
            } else {
                $amount   = $order->get_total();
                $currency = $order->get_currency();
            }

            $payment_intent = PaymentIntent::create(
                [
                    'amount'               => Helper::get_stripe_amount( $amount, strtolower( $currency ) ),
                    'currency'             => strtolower( $currency ),
                    'payment_method_types' => Helper::get_enabled_payment_methods_at_checkout( $order_id ),
                    'capture_method'       => Settings::is_manual_capture_enabled() ? 'manual' : 'automatic',
                ]
            );

            if ( ! empty( $payment_intent->error ) ) {
                throw new DokanException( 'create_payment_intent_error', $payment_intent->error->message );
            }

            wp_send_json_success(
                [
                    'id'            => $payment_intent->id,
                    'client_secret' => $payment_intent->client_secret,
                ],
                200
            );
        } catch ( DokanException $e ) {
            Helper::log( 'Create payment intent error: ' . $e->get_message() );
            // Send back error so it can be displayed to the customer.
            wp_send_json_error(
                [
                    'error' => [
                        'message' => $e->get_message(),
                    ],
                ]
            );
        }
    }

    /**
     * Handle AJAX request for updating a payment intent for Stripe.
     *
     * @since 3.6.1
     *
     * @return mixed
     */
    public function update_payment_intent() {
        try {
            if ( ! check_ajax_referer( 'dokan_stripe_express_checkout', false, false ) ) {
                throw new DokanException( 'update_payment_intent_error', __( "We're not able to process this payment. Please refresh the page and try again.", 'dokan' ) );
            }

            $order_id            = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : null;
            $payment_intent_id   = isset( $_POST['payment_intent_id'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_intent_id'] ) ) : '';
            $save_payment_method = isset( $_POST['save_payment_method'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['save_payment_method'] ) );
            $payment_type        = ! empty( $_POST['payment_type'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_type'] ) ) : '';

            $payment_intent = Payment::update_intent( $payment_intent_id, $order_id, [], false, $save_payment_method, $payment_type );

            wp_send_json_success( $payment_intent, 200 );
        } catch ( DokanException $e ) {
            // Send back error so it can be displayed to the customer.
            wp_send_json_error(
                [
                    'error' => [
                        'message' => $e->get_message(),
                    ],
                ]
            );
        }
    }

    /**
     * Verifies the payment intent.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function verify_intent() {
        try {
            if ( ! check_ajax_referer( 'dokan_stripe_express_confirm_pi', false, false ) ) {
                throw new DokanException(
                    'intent_verification_error',
                    __( "We're not able to process this payment. Please refresh the page and try again.", 'dokan' )
                );
            }

            $order = false;
            if ( ! empty( $_GET['order'] ) ) {
                $order = wc_get_order( absint( $_GET['order'] ) );
            }

            if ( ! $order ) {
                throw new DokanException( 'missing-order', __( 'Missing order ID for payment confirmation', 'dokan' ) );
            }
        } catch ( DokanException $e ) {
            /* translators: error message */
            $message = sprintf( __( 'Payment verification error: %s', 'dokan' ), $e->get_message() );
            wc_add_notice( esc_html( $message ), 'error' );

            Helper::log( $message, 'Order' );

            $redirect_url = WC()->cart->is_empty() ? get_permalink( wc_get_page_id( 'shop' ) ) : wc_get_checkout_url();

            wp_safe_redirect( $redirect_url );
            exit;
        }

        try {
            $gateway = $this->gateway();
            Payment::verify_intent( $order );

            if ( isset( $_GET['save_payment_method'] ) && ! empty( $_GET['save_payment_method'] ) ) {
                $intent   = Payment::get_intent( $order );
                $customer = Customer::set( get_current_user_id() );

                if ( isset( $intent->last_payment_error ) ) {
                    /*
                     * Currently, Stripe saves the payment method even if the authentication fails for 3DS cards.
                     * Though the card is not stored in DB we need to remove the source from the customer on Stripe
                     * in order to keep the sources in sync with the data in DB.
                     */
                    $customer->detach_payment_method( $intent->last_payment_error->payment_method->id );
                    $customer->detach_payment_method( $intent->last_payment_error->source->id );
                } else {
                    if ( isset( $intent->metadata->save_payment_method ) && '1' === $intent->metadata->save_payment_method ) {
                        $customer->attach_payment_method( $intent->payment_method );
                    }
                }
            }

            if ( ! isset( $_GET['is_ajax'] ) ) {
                $redirect_url = isset( $_GET['redirect_to'] )
                    ? esc_url_raw( wp_unslash( $_GET['redirect_to'] ) )
                    : $gateway->get_return_url( $order );

                wp_safe_redirect( $redirect_url );
            }

            exit;
        } catch ( DokanException $e ) {
            Helper::log( sprintf( 'Payment verification error: %s', $e->get_message() ), 'Order' );

            if ( isset( $_GET['is_ajax'] ) ) {
                exit;
            }

            wp_safe_redirect( $gateway->get_return_url( $order ) );
            exit;
        }
    }

    /**
     * Handle AJAX requests for creating a setup intent without confirmation for Stripe.
     *
     * @since 3.6.1
     *
     * @return mixed
     */
    public function init_setup_intent() {
        try {
            if ( ! check_ajax_referer( 'dokan_stripe_express_checkout', false, false ) ) {
                throw new DokanException( 'setup_intent_error', __( "We're not able to add this payment method. Please refresh the page and try again.", 'dokan' ) );
            }

            // Determine the customer managing the payment methods, create one if we don't have one already.
            $customer     = Customer::set( get_current_user_id() );
            $setup_intent = $customer->setup_intent();

            $intent = [
                'id'            => $setup_intent->id,
                'client_secret' => $setup_intent->client_secret,
            ];

            wp_send_json_success( $intent, 200 );
        } catch ( DokanException $e ) {
            $message = $e->get_message();

            /*
             * In case of no such customer error, the reason is
             * probably that the customer is removed from stripe end
             * or the stripe API setup in the payment gateway settings
             * has been changed.
             * So we need to create a new stripe customer to synchronize
             * and retry creating the setup intent.
             */
            if ( Helper::is_no_such_customer_error( $e ) ) {
                try {
                    $customer->set_id( 0 )->setup_intent();
                } catch ( DokanException $e ) {
                    $message = $e->get_message();
                }
            }

            wp_send_json_error(
                [
                    'error' => [
                        'message' => $message,
                    ],
                ]
            );
        }
    }

    /**
     * Handle AJAX request after authenticating payment at checkout.
     *
     * This function is used to update the order status after the user has
     * been asked to authenticate their payment.
     *
     * This function is used for both:
     * - regular checkout
     * - Pay for Order page (in theory).
     *
     * @since 3.6.1
     *
     * @return mixed
     * @throws Exception
     */
    public function update_order_status() {
        try {
            if ( ! check_ajax_referer( 'dokan_stripe_express_update_order_status', false, false ) ) {
                throw new DokanException( 'nonce_verification_error', __( 'CSRF verification failed.', 'dokan' ) );
            }

            $order_id = isset( $_POST['order_id'] ) ? absint( wp_unslash( $_POST['order_id'] ) ) : false;
            $order    = wc_get_order( $order_id );

            if ( ! $order ) {
                throw new DokanException( 'invalid_order', __( "We're not able to process this payment. Please try again later.", 'dokan' ) );
            }

            $save_payment_method = ! empty( sanitize_text_field( wp_unslash( $_POST['payment_method_id'] ) ) );
            $is_setup            = isset( $_POST['is_setup'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['is_setup'] ) );
            $intent_id           = ! $is_setup ? OrderMeta::get_payment_intent( $order ) : OrderMeta::get_setup_intent( $order );
            $intent_id_received  = isset( $_POST['intent_id'] ) ? sanitize_text_field( wp_unslash( $_POST['intent_id'] ) ) : null;

            if ( empty( $intent_id_received ) || $intent_id_received !== $intent_id ) {
                Order::add_note(
                    $order,
                    sprintf(
                        /* translators: %1: transaction ID of the payment or a translated string indicating an unknown ID. */
                        esc_html__( 'A payment with ID %s was used in an attempt to pay for this order. This payment intent ID does not match any payments for this order, so it was ignored and the order was not updated.', 'dokan' ),
                        $intent_id_received
                    )
                );
                throw new DokanException( 'duplicate_intent', __( "We're not able to process this payment. Please try again later.", 'dokan' ) );
            }

            Payment::process_confirmed_intent( $order, $intent_id_received, $save_payment_method );

            wp_send_json_success(
                [
                    'return_url' => $this->gateway()->get_return_url( $order ),
                ],
                200
            );
        } catch ( DokanException $e ) {
            wc_add_notice( $e->get_message(), 'error' );
            Helper::log( sprintf( 'Error: %s', $e->get_message() ) );

            if ( $order instanceof WC_Order ) {
                $order->update_status( 'failed' );
            }

            // Send back error so it can be displayed to the customer.
            wp_send_json_error(
                [
                    'error' => [
                        'message' => $e->get_message(),
                    ],
                ]
            );
        }
    }

    /**
     * Handle AJAX request if error occurs while confirming intent.
     * We will log the error and update the order.
     *
     * @since 3.6.1
     *
     * @return mixed
     * @throws Exception
     */
    public function update_failed_order() {
        try {
            $order_id  = isset( $_POST['order_id'] ) ? absint( $_POST['order_id'] ) : null;
            $order     = wc_get_order( $order_id );

            if ( ! check_ajax_referer( 'dokan_stripe_express_checkout', false, false ) ) {
                throw new DokanException( 'nonce_verification_error', __( 'CSRF verification failed.', 'dokan' ) );
            }

            $intent_id = isset( $_POST['intent_id'] ) ? sanitize_text_field( wp_unslash( $_POST['intent_id'] ) ) : '';
            if ( ! empty( $order_id ) && ! empty( $intent_id ) && is_object( $order ) ) {
                $payment_needed = Helper::is_payment_needed( $order_id );
                $intent         = Payment::get_intent( null, $intent_id, [], $payment_needed );

                if ( ! $intent ) {
                    throw new DokanException( 'payment_intent_error', __( "We're not able to process this payment. Please try again later.", 'dokan' ) );
                }

                // Use the last charge within the intent to proceed or the original response in case of SEPA
                $response = Payment::get_latest_charge_from_intent( $intent );
                if ( ! $response ) {
                    $response = $intent;
                }

                Payment::process_response( $response, $order );
                Payment::save_intent_data( $order, $intent );
                Payment::save_charge_data( $order, $intent );
            }
        } catch ( DokanException $e ) {
            // We are expecting an exception to be thrown here.
            wc_add_notice( $e->get_message(), 'error' );
            Helper::log( 'Error: ' . $e->get_message() );

            do_action( 'dokan_stripe_express_process_payment_error', $e, $order );

            if ( $order instanceof WC_Order ) {
                $order->update_status( 'failed' );
            }
        }

        wp_send_json_success();
    }

    /**
     * Creates initial subscription.
     *
     * @since 3.7.8
     *
     * @return mixed
     */
    public function create_subscription() {
        try {
            if ( ! check_ajax_referer( 'dokan_stripe_express_checkout', false, false ) ) {
                throw new Exception( __( "We're not able to process this payment. Please refresh the page and try again.", 'dokan' ) );
            }

            if ( empty( WC()->session ) ) {
                throw new Exception( __( "We're not able to process this payment as no session exists. Please refresh the page and try again.", 'dokan' ) );
            }

            $product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
            if ( ! $product_id || ! Subscription::is_recurring_vendor_subscription_product( $product_id ) ) {
                throw new Exception( __( "We're not able to process this payment. Please try again later.", 'dokan' ) );
            }

            $order_id = isset( $_POST['order_id'] ) ? absint( wp_unslash( $_POST['order_id'] ) ) : null;

            $subscription_data = [
                'payment_behavior' => 'default_incomplete',
                'payment_settings' => [
                    'save_default_payment_method' => 'on_subscription',
                ],
            ];

            /**
             * Process the subscription.
             *
             * @since 3.7.8
             *
             * @param array      $subscription_data
             * @param int|string $product_id
             * @param int|string $order_id
             */
            $subscription_data = apply_filters( 'dokan_stripe_express_process_subscription_data', $subscription_data, $product_id, $order_id );

            $subscription = Subscription::create( $subscription_data );

            if ( is_wp_error( $subscription ) ) {
                throw new Exception( $subscription->get_error_message() );
            }

            if ( empty( $subscription ) || ! $subscription instanceof \Stripe\Subscription ) {
                throw new Exception( __( "We're not able to process this payment. Please try again later.", 'dokan' ) );
            }

            $client_secret = ! empty( $subscription->latest_invoice->payment_intent->client_secret )
                ? $subscription->latest_invoice->payment_intent->client_secret
                : $subscription->pending_setup_intent->client_secret;

            $intent_id = ! empty( $subscription->latest_invoice->payment_intent->id )
                ? $subscription->latest_invoice->payment_intent->id
                : $subscription->pending_setup_intent->id;

            wp_send_json_success(
                [
                    'subscription_id' => $subscription->id,
                    'client_secret'   => $client_secret,
                    'id'              => $intent_id,
                ]
            );
        } catch ( Exception $e ) {
            wp_send_json_error(
                [
                    'error' => [
                        'message' => $e->getMessage(),
                    ],
                ]
            );
        }
    }

    /**
     * Sets subscription data if needed.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function maybe_set_subscription_data() {
        if ( empty( WC()->session ) ) {
            return;
        }

        $session = WC()->session;
        foreach ( $session->cart as $data ) {
            if ( empty( $data['product_id'] ) ) {
                return;
            }
            $product_id = $data['product_id'];
            break;
        }

        if ( ! Subscription::is_recurring_vendor_subscription_product( $product_id ) ) {
            return;
        }

        ?>
        <div class="dokan-stripe-express-subscription">
            <input type="hidden" name="subscription_product_id" id="dokan-stripe-express-subscription-product-id" value="<?php echo esc_attr( $product_id ); ?>">
        </div>
        <?php
    }

    /**
     * Filters available payment gateways as necessary.
     *
     * In this case, we are disabling Stripe Connect from
     * checkout page when Stripe Express is available for use.
     *
     * @since 3.7.8
     *
     * @param array $available_gateways
     *
     * @return array
     */
    public function filter_available_payment_gateways( $available_gateways ) {
        if ( ! is_checkout() ) {
            return $available_gateways;
        }

        if ( ! $this->gateway()->is_available() ) {
            return $available_gateways;
        }

        if ( class_exists( 'WeDevs\DokanPro\Modules\Stripe\Helper' ) ) {
            unset( $available_gateways[ \WeDevs\DokanPro\Modules\Stripe\Helper::get_gateway_id() ] );
        }

        return $available_gateways;
    }
}
