<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\PaymentGateways;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WC_AJAX;
use WC_Order;
use Exception;
use WeDevs\Dokan\Cache;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Settings;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Token;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Order;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Api\PaymentMethod;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Payment;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Subscription;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\PaymentGateway;

/**
 * Gateway handler class.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\PaymentGateways
 */
class Stripe extends PaymentGateway {

    /**
     * ID for the gateway
     *
     * @since 3.6.1
     *
     * @param string
     */
    const ID = 'dokan_stripe_express';

    /**
     * Class constructor.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function __construct() {
        // Load necessary fields info
        $this->init_fields();
        // Load the settings
        $this->init_form_fields();
        $this->init_settings();
        // Load necessary hooks
        $this->hooks();
    }

    /**
     * Initiates all required info for payment gateway
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function init_fields() {
        $this->has_fields               = true;
        $this->id                       = self::ID;
        $this->method_title             = Helper::get_gateway_title();
        $this->method_description       = __( 'Accept debit and credit cards in different currencies, methods such as iDEAL, and wallets like Google Pay or Apple Pay with one-touch checkout.', 'dokan' );
        $this->payment_methods          = Helper::get_available_method_instances();
        $this->order_button_text        = Helper::get_order_button_text();
        $this->title                    = $this->get_option( 'title' );
        $this->testmode                 = 'yes' === $this->get_option( 'testmode' );
        $this->secret_key               = $this->testmode ? $this->get_option( 'test_secret_key' ) : $this->get_option( 'secret_key' );
        $this->publishable_key          = $this->testmode ? $this->get_option( 'test_publishable_key' ) : $this->get_option( 'publishable_key' );
        $this->debug                    = $this->get_option( 'debug' );
        $this->description              = $this->get_option( 'description', '' );
        $this->capture                  = 'yes' === $this->get_option( 'capture', 'no' );
        $this->payment_request          = 'yes' === $this->get_option( 'payment_request', 'yes' );
        $this->enabled                  = $this->get_option( 'enabled' );
        $this->saved_cards              = 'yes' === $this->get_option( 'saved_cards' );
        $this->icon                     = apply_filters( 'dokan_stripe_express_icon', '' );
        $this->statement_descriptor     = Helper::clean_statement_descriptor( $this->get_option( 'statement_descriptor', '' ) );
        $this->selected_payment_methods = (array) $this->get_option( 'enabled_payment_methods', [ 'card' ] );
        $this->supports                 = apply_filters(
            'dokan_stripe_express_gateway_support',
            [
                'products',
                'refunds',
                'tokenization',
                'add_payment_method',
            ]
        );

        if ( ! is_add_payment_method_page() && ! Subscription::is_changing_payment_method() ) {
            $active_payment_methods = Helper::get_enabled_payment_methods_at_checkout();
            if ( count( $active_payment_methods ) === 1 ) {
                $active_payment_method = $this->payment_methods[ $active_payment_methods[0] ];
                $this->title       = $active_payment_method->get_label();
                $this->description = $active_payment_method->get_title();
            }
        }

        if ( empty( $this->title ) ) {
            $this->title = __( 'Stripe Express', 'dokan' );
        }

        // Show the count of enabled payment methods on settings page.
        if ( isset( $_GET['page'] ) && 'wc-settings' === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $total_enabled_payment_methods = count( $this->selected_payment_methods );
            $this->title                   = $total_enabled_payment_methods
                /* translators: Count of enabled payment methods. */
                ? sprintf( _n( '%d payment method', '%d payment methods', $total_enabled_payment_methods, 'dokan' ), $total_enabled_payment_methods )
                : $this->method_title;
        }
    }

    /**
     * Initiates all necessary hooks
     *
     * @since 3.6.1
     *
     * @uses add_action() To add action hooks
     * @uses add_filter() To add filter hooks
     *
     * @return void
     */
    private function hooks() {
        add_action( "woocommerce_update_options_payment_gateways_{$this->id}", [ $this, 'process_admin_options' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'payment_scripts' ] );
        add_filter( 'woocommerce_payment_successful_result', [ $this, 'modify_successful_payment_result' ], 99999, 2 );
        add_action( 'woocommerce_customer_save_address', [ $this, 'show_update_card_notice' ], 10, 2 );
    }

    /**
     * Retrieves the gateway ID.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public function get_gateway_id() {
        return $this->id;
    }

    /**
     * Checks if the gateways is available for use.
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public function is_available() {
        $is_available = parent::is_available() &&
        (
            is_add_payment_method_page()
            /*
             * For the add payment method page, we need to check
             * if the available payment methods supports tokenization.
             */
            ? count( Helper::get_enabled_reusable_payment_methods() ) > 0
            /*
             * If it is cart or checkout page or the gateway is being used to
             * pay for an order, we need to validate all the cart items.
             * This payment method can't be used if a Vendor is not connected
             * to Stripe express. So we need to traverse all the cart items
             * to check if any vendor is not connected.
             */
            : Order::validate_cart_items()
        );

        /**
         * Filter to modify the availablity of the Stripe Express payment gateway.
         *
         * @since 3.7.8
         *
         * @param bool $is_available
         */
        return apply_filters( 'dokan_stripe_express_is_gateway_available', $is_available );
    }

    /**
     * Initiates form fields for admin settings
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function init_form_fields() {
        $this->form_fields = require DOKAN_STRIPE_EXPRESS_TEMPLATE_PATH . 'admin/gateway-settings.php';
    }

    /**
     * Init settings for gateways.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function init_settings() {
        parent::init_settings();
        $this->enabled = ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'] ? 'yes' : 'no';
    }

    /**
     * Processes the admin options.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function process_admin_options() {
        parent::process_admin_options();

        /**
         * @var \WeDevs\DokanPro\Modules\StripeExpress\Controllers\Webhook
         */
        $webhook = dokan_pro()->module->stripe_express->webhook;

        // Automatically create webhook if gateway is enabled. Delete otherwise
        if ( 'yes' === $this->enabled ) {
            $webhook->register();
        } else {
            $webhook->deregister();
        }

        Cache::invalidate_transient_group( 'stripe_express_platform_data' );
        Cache::invalidate_transient_group( 'stripe_express_country_specs' );
    }

    /**
     * Renders the input fields needed
     * to get the user's payment information
     * on the checkout page.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function payment_fields() {
        try {
            global $wp;
            $user_email           = '';
            $first_name           = '';
            $last_name            = '';
            $total                = 0;
            $user                 = wp_get_current_user();
            $display_tokenization = $this->supports( 'tokenization' ) && is_checkout();

            // If paying from order, we need to get total from order not cart.
            if ( isset( $_GET['pay_for_order'] ) && ! empty( $_GET['key'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $order      = wc_get_order( wc_clean( $wp->query_vars['order-pay'] ) );
                $total      = $order->get_total();
                $user_email = $order->get_billing_email();
            } else {
                if ( $user->ID ) {
                    $user_email = get_user_meta( $user->ID, 'billing_email', true );
                    $user_email = $user_email ? $user_email : $user->user_email;
                }
            }

            if ( is_add_payment_method_page() ) {
                $first_name = $user->user_firstname;
                $last_name  = $user->user_lastname;
            }

            ob_start();

            ?>
            <div
                id="dokan-stripe-express-payment-data"
                data-email="<?php echo esc_attr( $user_email ); ?>"
                data-full-name="<?php echo esc_attr( "$first_name $last_name" ); ?>"
                data-order-total="<?php echo esc_attr( $total ); ?>"
                data-currency="<?php echo esc_attr( strtolower( get_woocommerce_currency() ) ); ?>"
            >
            <?php

            $this->maybe_show_description();

            if ( $display_tokenization ) {
                $this->tokenization_script();
                $this->saved_payment_methods();
            }

            $this->element_form();

            if ( $this->saved_cards && ! empty( Helper::get_enabled_reusable_payment_methods() ) && is_user_logged_in() ) {
                $force_save_payment = Subscription::cart_contains_recurring_vendor_subscription() ||
                    is_add_payment_method_page() ||
                    (
                        $display_tokenization &&
                        ! apply_filters( 'dokan_stripe_express_display_save_payment_method_checkbox', $display_tokenization )
                    );

                $this->save_payment_method_checkbox( $force_save_payment );
            }

            do_action( 'dokan_stripe_express_payment_fields', $this->id );

            ?>
            </div>
            <?php

            ob_end_flush();
        } catch ( \Exception $e ) {
            // Output the error message.
            Helper::log( 'Error: ' . $e->getMessage() );
            /* translators: 1) opening div tag, 2) closing div tag */
            echo esc_html( sprintf( __( '%1$sAn error was encountered when preparing the payment form. Please try again later.%2$s', 'dokan' ), '<div>', '</div>' ) );
        }
    }

    /**
     * Enqueues payment scripts.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function payment_scripts() {
        if ( ! is_product() && ! is_cart() && ! is_checkout() && ! isset( $_GET['pay_for_order'] ) && ! is_add_payment_method_page() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        if ( ! $this->is_available() ) {
            return;
        }

        wp_localize_script(
            'dokan-stripe-express-checkout',
            'dokanStripeExpress',
            $this->localized_params()
        );

        wp_enqueue_script( 'dokan-stripe-express-checkout' );
        wp_enqueue_style( 'dokan-stripe-express-checkout' );
    }

    /**
     * Generates localized javascript parameters
     *
     * @since 3.6.1
     *
     * @return array
     */
    private function localized_params() {
        $stripe_params = [
            'title'                => $this->title,
            'key'                  => $this->publishable_key,
            'locale'               => Helper::convert_locale( get_locale() ),
            'billingFields'        => Helper::get_enabled_billing_fields(),
            'isCheckout'           => is_checkout() && empty( $_GET['pay_for_order'] ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            'isAddPaymentMethod'   => is_add_payment_method_page(),
            'errors'               => Helper::get_error_message(),
            'messages'             => Helper::get_payment_message(),
            'ajaxurl'              => WC_AJAX::get_endpoint( '%%endpoint%%' ),
            'nonce'                => wp_create_nonce( 'dokan_stripe_express_checkout' ),
            'paymentMethodsConfig' => $this->get_enabled_payment_method_config(),
            'addPaymentReturnURL'  => wc_get_account_endpoint_url( 'payment-methods' ),
            'accountDescriptor'    => $this->statement_descriptor,
            'genericErrorMessage'  => __( 'There was a problem processing the payment. Please check your email inbox and refresh the page to try again.', 'dokan' ),
            'assets'               => [
                'applePayLogo' => DOKAN_STRIPE_EXPRESS_ASSETS . 'images/apple-pay.svg',
            ],
            'i18n'                 => [
                'confirmApplePayment' => __( 'Proceed to payment via Apple Pay?', 'dokan' ),
                'proceed'             => __( 'Yes, Proceed', 'dokan' ),
                'decline'             => __( 'Decline', 'dokan' ),
                'emptyFields'         => __( 'Please fill all the fields', 'dokan' ),
                'paymentDismissed'    => __( 'Payment process dismissed', 'dokan' ),
                'tryAgain'            => __( 'An error was encountered when preparing the payment form. Please try again later.', 'dokan' ),
                'incompleteInfo'      => __( 'Your payment information is incomplete.', 'dokan' ),
            ],
            'sepaElementsOptions'  => apply_filters(
                'dokan_stripe_express_sepa_elements_options',
                [
                    'supportedCountries' => [ 'SEPA' ],
                    'placeholderCountry' => WC()->countries->get_base_country(),
                ]
            ),
            'appearance'           => apply_filters(
                'dokan_stripe_express_payment_element_appearance',
                [
                    'theme' => $this->get_option( 'element_theme', 'stripe' ),
                ]
            ),
        ];

        $order_id = null;

        if ( is_wc_endpoint_url( 'order-pay' ) ) {
            if ( Subscription::has_wc_subscription() && Subscription::is_changing_payment_method() ) {
                $stripe_params['isChangingPayment']   = true;
                $stripe_params['addPaymentReturnURL'] = esc_url_raw( home_url( add_query_arg( [] ) ) );

                if ( Helper::is_setup_intent_success_creation_redirection() && isset( $_GET['_wpnonce'] ) && wp_verify_nonce( wc_clean( wp_unslash( $_GET['_wpnonce'] ) ) ) ) {
                    $setup_intent_id = isset( $_GET['setup_intent'] ) ? wc_clean( wp_unslash( $_GET['setup_intent'] ) ) : '';
                    $token           = $this->create_token_from_setup_intent( $setup_intent_id, get_current_user_id() );

                    if ( ! empty( $token ) && $token instanceof \WC_Payment_Token ) {
                        $stripe_params['newTokenFormId'] = '#wc-' . $token->get_gateway_id() . '-payment-token-' . $token->get_id();
                        $stripe_params['stripeToken']    = $token->get_token();
                    }
                }

                return $stripe_params;
            }

            $order_id = absint( get_query_var( 'order-pay' ) );
            $order    = wc_get_order( $order_id );

            if ( is_a( $order, 'WC_Order' ) ) {
                $stripe_params['orderReturnURL'] = esc_url_raw(
                    add_query_arg(
                        [
                            'order_id'          => $order_id,
                            'wc_payment_method' => Helper::get_gateway_id(),
                            '_wpnonce'          => wp_create_nonce( 'dokan_stripe_express_process_redirect_order' ),
                        ],
                        $this->get_return_url( $order )
                    )
                );
            }

            $stripe_params['orderId']    = $order_id;
            $stripe_params['isOrderPay'] = true;
        }

        $stripe_params['isPaymentNeeded'] = Helper::is_payment_needed( $order_id );

        return $stripe_params;
    }

    /**
     * Process the payment for a given order.
     *
     * @since 3.6.1
     *
     * @param int   $order_id          ID of the order being processed.
     * @param bool  $retry             Should we retry on fail.
     * @param bool  $force_save_source Force save the payment source.
     * @param mixed $previous_error    Any error message from previous request.
     * @param bool  $use_order_source  Whether to use the source, which should already be attached to the order.
     *
     * @return array|null An array with result of payment and redirect URL, or nothing.
     */
    public function process_payment( $order_id, $retry = true, $force_save_source = false, $previous_error = false, $use_order_source = false ) {
        // phpcs:disable WordPress.Security.NonceVerification.Missing

        if ( Subscription::subcription_payment_method_needs_change( $order_id ) ) {
            return $this->change_subscription_payment_method( $order_id );
        }

        if ( Subscription::is_recurring_vendor_subscription_order( $order_id ) && ! empty( $_POST['subscription_id'] ) ) {
            return $this->process_subscription( $order_id );
        }

        $payment_intent_id = ! empty( $_POST['payment_intent_id'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_intent_id'] ) ) : '';

        if ( Helper::is_using_saved_payment_method() ) {
            return $this->process_payment_with_saved_payment_method( $order_id, true, $payment_intent_id );
        }

        if ( empty( $payment_intent_id ) ) {
            return parent::process_payment( $order_id, $retry, $force_save_source, $previous_error, $use_order_source );
        }

        $order                 = wc_get_order( $order_id );
        $payment_needed        = Helper::is_payment_needed( $order_id );
        $save_payment_method   = Subscription::is_subscription_order( $order_id ) || ! empty( $_POST[ 'wc-' . self::ID . '-new-payment-method' ] );
        $selected_payment_type = ! empty( $_POST['dokan_stripe_express_payment_type'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan_stripe_express_payment_type'] ) ) : '';
        $payment_needed        = Helper::is_payment_needed( $order_id );

        OrderMeta::update_debug_payment_intent( $order, $payment_intent_id );
        OrderMeta::update_save_payment_method( $order );
        OrderMeta::save( $order );

        if ( $payment_needed ) {
            if ( ! empty( $selected_payment_type ) && ! $this->payment_methods[ $selected_payment_type ]->is_allowed_on_country( $order->get_billing_country() ) ) {
                throw new Exception( __( 'This payment method is not available on the selected country', 'dokan' ) );
            }

            Payment::update_intent( $payment_intent_id, $order_id, [], false, $save_payment_method, $selected_payment_type );
        }

        return [
            'result'         => 'success',
            'payment_needed' => $payment_needed,
            'order_id'       => $order_id,
            'redirect_url'   => wp_sanitize_redirect(
                esc_url_raw(
                    add_query_arg(
                        [
                            'order_id'            => $order_id,
                            'wc_payment_method'   => self::ID,
                            '_wpnonce'            => wp_create_nonce( 'dokan_stripe_express_process_redirect_order' ),
                            'save_payment_method' => $save_payment_method ? 'yes' : 'no',
                        ],
                        $this->get_return_url( $order )
                    )
                )
            ),
        ];
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }

    /**
     * Process payment using saved payment method.
     * This follows Stripe::process_payment,
     * but uses Payment Methods instead of Sources.
     *
     * @since 3.6.1
     *
     * @param int    $order_id          The order ID being processed.
     * @param bool   $can_retry         Should we retry on fail.
     * @param string $payment_intent_id The payment intent ID.
     *
     * @return mixed
     */
    public function process_payment_with_saved_payment_method( $order_id, $can_retry = true, $payment_intent_id = null ) {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
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

            Helper::log( "Processing payment with saved payment method for order $order_id for the amount of {$order->get_total()}", 'Order', 'info' );

            // If we are retrying request, maybe intent has been saved to order.
            $intent      = Payment::get_intent( $order, $payment_intent_id, [], ! $payment_needed );
            $intent_data = [
                'payment_method'       => $payment_method->id,
                'payment_method_types' => [ $payment_method->type ],
                'customer'             => $payment_method->customer,
            ];

            if ( $payment_needed ) {
                // This will throw exception if not valid.
                $this->validate_minimum_order_amount( $order );

                $intent_data['capture_method'] = Settings::is_manual_capture_enabled() ? 'manual' : 'automatic';

                if ( ! $intent ) {
                    $intent_data['confirm'] = 'true';
                    $intent = Payment::create_intent( $order, $intent_data );
                } else {
                    $intent = Payment::update_intent( $intent->id, $order->get_id(), $intent_data );
                }
            } else {
                if ( ! $intent ) {
                    $intent_data['confirm'] = 'true';
                    // SEPA setup intents require mandate data.
                    if ( Helper::get_sepa_payment_method_type() === $payment_method->type ) {
                        $intent_data['mandate_data'] = [
                            'customer_acceptance' => [
                                'type'   => 'online',
                                'online' => [
                                    'ip_address' => dokan_get_client_ip(),
                                    'user_agent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : '', // phpcs:ignore
                                ],
                            ],
                        ];
                    }

                    $intent = Payment::create_intent( $order, $intent_data, true );
                } else {
                    $intent = Payment::update_intent( $intent->id, $order->get_id(), $intent_data, true );
                }
            }
            // phpcs:enable WordPress.Security.NonceVerification.Missing

            /**
             * Process payment when needed.
             *
             * @since 3.7.8
             *
             * @param WC_Order $order             The order being processed.
             * @param string   $payment_method_id The source of the payment.
             */
            do_action( 'dokan_stripe_express_process_payment', $order, $payment_method->id );

            if ( ! empty( $intent->error ) ) {
                $this->maybe_remove_non_existent_customer( $intent->error, $order );

                // We want to retry (apparently).
                if ( Helper::is_retryable_error( $intent->error ) ) {
                    return $this->retry_after_error( $intent, $order, $can_retry );
                }

                $this->throw_error_message( $intent, $order );
            }

            OrderMeta::update_payment_type( $order, $payment_method->type );
            OrderMeta::save( $order );

            if ( 'requires_action' === $intent->status || 'requires_confirmation' === $intent->status ) {
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

            if ( $payment_needed ) {
                Order::lock_processing( $order->get_id(), 'intent', $intent->id );

                // Use the last charge within the intent to proceed or the original response in case of SEPA
                $response = Payment::get_latest_charge_from_intent( $intent );
                if ( ! $response ) {
                    $response = $intent;
                }
                Payment::process_response( $response, $order );
                Order::unlock_processing( $order->get_id(), 'intent', $intent->id );
            } else {
                $order->payment_complete();
                do_action( 'dokan_stripe_express_payment_completed', $order, $intent );
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
     * Checks whether an order is refundable.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return boolean
     */
    public function can_refund_order( $order ) {
        // Check if the default refund method is enabled.
        if ( ! parent::can_refund_order( $order ) ) {
            return false;
        }

        // Check whether order is processed or completed
        if ( ! $order->has_status( [ 'processing', 'completed' ] ) ) {
            return false;
        }

        /**
         * We will not allow refund from the parent order.
         * The refund should always be given from the
         * sub orders if exists.
         * If it is a parent order, the refund button for
         * Stripe Express will not be shown.
         */
        if ( $order->get_meta( 'has_sub_order' ) ) {
            return false;
        }

        /*
         * We need to check if the payment method
         * used for this order supports refunds via Stripe.
         * To get the payment method, we need to get the
         * parent order if exists as the payment method
         * meta is stored on the parent order.
         */
        if ( $order->get_parent_id() ) {
            $order = wc_get_order( $order->get_parent_id() );
        }

        // Check if the payment method can refund via Stripe
        $payment_method = OrderMeta::get_payment_type( $order );
        if ( ! $this->payment_methods[ $payment_method ]->can_refund_via_stripe() ) {
            return false;
        }

        return true;
    }

    /**
     * Adds a notice for customer when they update their billing address.
     *
     * @since 3.7.8
     *
     * @param int    $user_id      The ID of the current user.
     * @param string $load_address The address to load.
     *
     * @return void
     */
    public function show_update_card_notice( $user_id, $load_address ) {
        if ( ! $this->saved_cards || ! $this->customer_has_saved_methods( $user_id ) || 'billing' !== $load_address ) {
            return;
        }

        wc_add_notice(
            sprintf(
                /* translators: 1) opening anchor tag with link, 2) closing anchor tag */
                __(
                    'If your billing address has been changed for saved payment methods, be sure to remove any %1$ssaved payment methods%2$s on file and re-add them.',
                    'dokan'
                ),
                sprintf(
                    '<a href="%s" class="dokan-stripe-express-update-card-notice" style="text-decoration:underline;">',
                    esc_url( wc_get_endpoint_url( 'payment-methods' ) )
                ),
                '</a>'
            ),
            'notice'
        );
    }
}
