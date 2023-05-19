<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\PaymentRequest;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\Config;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Traits\PaymentRequestUtils;

/**
 * Stripe Payment Request API controller
 * Adds support for Apple Pay and Chrome Payment Request API buttons.
 * Utilizes the Stripe Payment Request Button to support checkout from the product detail and cart pages.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\PaymentRequest
 */
class Manager {

    use PaymentRequestUtils;

    /**
     * Payment request types.
     *
     * @since 3.7.8
     *
     * @var array
     */
    const PAYMENT_REQUEST_TYPES = [
        'apple_pay',
        'google_pay',
        'payment_request_api',
    ];

    /**
     * Indicates if payment request is enabled.
     *
     * @since 3.7.8
     *
     * @var bool
     */
    private $enabled;

    /**
     * Publishable key.
     *
     * @since 3.7.8
     *
     * @var string
     */
    private $publishable_key;

    /**
     * Secret key.
     *
     * @since 3.7.8
     *
     * @var string
     */
    private $secret_key;

    /**
     * Is test mode active?
     *
     * @since 3.7.8
     *
     * @var bool
     */
    private $testmode;

    /**
     * Indicates if stripe express gateway is enabled.
     *
     * @since 3.7.8
     *
     * @var bool
     */
    protected $gateway_enabled;

    /**
     * Indicates whether gateway is ready or not.
     *
     * @since 3.7.12
     *
     * @var bool
     */
    protected $gateway_ready;

    /**
     * Indicates if API is ready to be connected.
     *
     * @since 3.7.8
     *
     * @var bool
     */
    protected $api_ready;

    /**
     * Holds the instance of configuration.
     *
     * @since 3.7.8
     *
     * @var Config
     */
    protected $config;

    /**
     * Class constructor.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function __construct() {
        $this->config          = Config::instance();
        $this->testmode        = ! $this->config->is_live_mode();
        $this->publishable_key = $this->config->get_publishable_key();
        $this->secret_key      = $this->config->get_secret_key();
        $this->api_ready       = $this->config->verify_api_keys();
        $this->gateway_ready   = $this->config->is_api_ready();
        $this->enabled         = 'yes' === $this->get_option( 'payment_request' );
        $this->gateway_enabled = 'yes' === $this->get_option( 'enabled' );

        $this->hooks();
        $this->init_classes();
    }

    /**
     * Determines whether Payment Request is enabled in settings.
     *
     * @since 3.7.8
     *
     * @return boolean
     */
    public function is_enabled() {
        return $this->enabled;
    }

    /**
     * Determines whether Stripe Express is enabled in settings.
     *
     * @since 3.7.8
     *
     * @return boolean
     */
    public function is_gateway_enabled() {
        return $this->gateway_enabled;
    }

    /**
     * Retrieves option value.
     *
     * @since 3.7.8
     *
     * @param string $key
     *
     * @return mixed
     */
    protected function get_option( $key ) {
        return $this->config->get_option( $key );
    }

    /**
     * Initialize hooks.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function hooks() {
        // Handle gateway data
        add_filter( 'woocommerce_gateway_title', [ $this, 'filter_gateway_title' ], 10, 2 );
        add_action( 'woocommerce_checkout_order_processed', [ $this, 'add_order_meta' ], 10, 2 );
        // Manage sessions and redirections
        add_action( 'template_redirect', [ $this, 'set_session' ] );
        add_action( 'template_redirect', [ $this, 'handle_payment_request_redirect' ] );
        add_filter( 'woocommerce_login_redirect', [ $this, 'get_login_redirect_url' ], 10, 3 );
        add_filter( 'woocommerce_registration_redirect', [ $this, 'get_login_redirect_url' ], 10, 3 );
        // Render payment request button at product detail page
        add_action( 'woocommerce_after_add_to_cart_quantity', [ $this, 'render_payment_request_button' ], 1 );
        // Render payment request button at cart page
        add_action( 'woocommerce_proceed_to_checkout', [ $this, 'render_payment_request_button' ], 1 );
    }

    /**
     * Instantiates all necessary classes.
     *
     * @since 3.7.8
     *
     * @return void
     */
    protected function init_classes() {
        new Ajax();
    }

    /**
     * Gets the button type.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public function get_button_type() {
        return ! empty( $this->get_option( 'payment_request_button_type' ) )
            ? $this->get_option( 'payment_request_button_type' )
            : 'default';
    }

    /**
     * Gets the button theme.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public function get_button_theme() {
        return ! empty( $this->get_option( 'payment_request_button_theme' ) )
            ? $this->get_option( 'payment_request_button_theme' )
            : 'dark';
    }

    /**
     * Gets the button height.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public function get_button_height() {
        $height = ! empty( $this->get_option( 'payment_request_button_size' ) )
            ? $this->get_option( 'payment_request_button_size' )
            : 'default';

        if ( 'medium' === $height ) {
            return '48';
        }

        if ( 'large' === $height ) {
            return '56';
        }

        // for the "default" and "catch-all" scenarios.
        return '40';
    }

    /**
     * Retrieves value of button locations option.
     *
     * @since 3.7.8
     *
     * @return array
     */
    public function get_button_locations() {
        // If the locations have not been set return the default setting.
        if ( empty( $this->get_option( 'payment_request_button_locations' ) ) ) {
            return [ 'product' ];
        }

        /*
         * If all locations are removed through the settings UI the location config will be set to
         * an empty string "". If that's the case (and if the settings are not an array for any
         * other reason) we should return an empty array.
         */
        if ( ! is_array( $this->get_option( 'payment_request_button_locations' ) ) ) {
            return [];
        }

        return $this->get_option( 'payment_request_button_locations' );
    }

    /**
     * The settings for the `button` attribute - they depend on the "settings redesign" flag value.
     *
     * @since 3.7.8
     *
     * @return array
     */
    public function get_button_settings() {
        return [
            'type'   => $this->get_button_type(),
            'theme'  => $this->get_button_theme(),
            'height' => $this->get_button_height(),
            // Default format is en_US.
            'locale' => substr( get_locale(), 0, 2 ),
        ];
    }

    /**
     * Verifies whether or not payment request button should be rendered.
     *
     * @since 3.7.8
     *
     * @return boolean
     */
    public function should_render_payment_request_button() {
        if ( ! $this->is_page_supported() ) {
            return false;
        }

        if ( ! $this->is_gateway_available() ) {
            return false;
        }

        if ( ! $this->verify_gateway_env() ) {
            return false;
        }

        /*
         * Don't show if on the cart page, or if page contains the cart
         * shortcodes, with items in the cart that aren't supported.
         */
        if ( is_cart() ) {
            return $this->is_payment_request_button_enabled( 'cart' )
                && $this->validate_cart_contents();
        }

        if ( $this->is_product() ) {
            if ( ! $this->is_payment_request_button_enabled( 'product' ) ) {
                return false;
            }

            $product = $this->get_product();

            $seller_id = dokan_get_vendor_by_product( $product, true );
            if ( ! Helper::is_seller_connected( $seller_id ) ) {
                return false;
            }

            if ( ! $this->is_product_supported( $product ) ) {
                return false;
            }

            if ( in_array( $product->get_type(), [ 'variable' ], true ) ) {
                $stock_availability = array_column( $product->get_available_variations(), 'is_in_stock' );
                // Don't show if all product variations are out-of-stock.
                if ( ! in_array( true, $stock_availability, true ) ) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Checks whether or not payment request button is enabled on certain page.
     *
     * @since 3.7.8
     *
     * @param string $page Indicates the page to check. Expected values are 'cart', 'product', and 'checkout'.
     *
     * @return boolean
     */
    public function is_payment_request_button_enabled( $page ) {
        return in_array( $page, $this->get_button_locations(), true );
    }

    /**
     * Sets the WC customer session if one is not set.
     * This is needed so nonces can be verified by AJAX Request.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function set_session() {
        if ( ! $this->is_product() || ( isset( WC()->session ) && WC()->session->has_session() ) ) {
            return;
        }

        WC()->session->set_customer_session_cookie( true );
    }

    /**
     * Handles payment request redirect when the redirect dialog "Continue" button is clicked.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function handle_payment_request_redirect() {
        if (
            isset( $_GET['dokan_stripe_express_payment_request_redirect_url'], $_GET['_wpnonce'] ) &&
            wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'dokan-stripe-express-set-redirect-url' )
        ) {
            $url = rawurldecode( esc_url_raw( wp_unslash( $_GET['dokan_stripe_express_payment_request_redirect_url'] ) ) );

            /*
             * Sets a redirect URL cookie for 10 minutes, which we will redirect to after authentication.
             * Users will have a 10 minute timeout to login/create account, otherwise redirect URL expires.
             */
            wc_setcookie( 'dokan_stripe_express_payment_request_redirect_url', $url, time() + MINUTE_IN_SECONDS * 10 );
            // Redirects to "my-account" page.
            wp_safe_redirect( get_permalink( get_option( 'woocommerce_myaccount_page_id' ) ) );
            exit;
        }
    }

    /**
     * Load public scripts and styles.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function scripts() {
        wp_localize_script(
            'dokan-stripe-express-payment-request',
            'dokanStripeExpressPRData',
            apply_filters(
                'dokan_stripe_express_payment_request_script_params',
                $this->localized_data()
            )
        );

        wp_enqueue_script( 'dokan-stripe-express-payment-request' );

        // Enqueues all the payment scripts of Stripe
        Helper::get_gateway_instance()->payment_scripts();
    }

    /**
     * Returns the JavaScript configuration object used for any pages with a payment request button.
     *
     * @since 3.7.8
     *
     * @return array The settings used for the payment request button in JavaScript.
     */
    public function localized_data() {
        $needs_shipping = 'no';
        if ( ! is_null( WC()->cart ) && WC()->cart->needs_shipping() ) {
            $needs_shipping = 'yes';
        }

        return [
            'ajaxUrl'             => \WC_AJAX::get_endpoint( '%%endpoint%%' ),
            'stripe'              => [
                'key'              => $this->publishable_key,
                'allowPrepaidCard' => apply_filters( 'dokan_stripe_express_allow_prepaid_card', true ) ? 'yes' : 'no',
                'paymentMethod'    => Helper::get_gateway_id(),
                'apiVersion'       => Helper::get_api_version(),
            ],
            'customer'            => $this->get_customer_data(),
            'nonce'               => [
                'payment'                => wp_create_nonce( 'dokan-stripe-express-payment-request' ),
                'shipping'               => wp_create_nonce( 'dokan-stripe-express-payment-request-shipping' ),
                'updateShipping'         => wp_create_nonce( 'dokan-stripe-express-update-shipping-method' ),
                'checkout'               => wp_create_nonce( 'woocommerce-process_checkout' ),
                'addToCart'              => wp_create_nonce( 'dokan-stripe-express-add-to-cart' ),
                'getSelectedProductData' => wp_create_nonce( 'dokan-stripe-express-get-selected-product-data' ),
                'logErrors'              => wp_create_nonce( 'dokan-stripe-express-log-errors' ),
                'clearCart'              => wp_create_nonce( 'dokan-stripe-express-clear-cart' ),
            ],
            'i18n'                => [
                'error'              => [
                    'noPrepaidCard'   => __( 'Sorry, we\'re not accepting prepaid cards at this time.', 'dokan' ),
                    /* translators: Do not translate the [option] placeholder */
                    'unknownShipping' => __( 'Unknown shipping option "[option]".', 'dokan' ),
                ],
                'applePay'           => __( 'Apple Pay', 'dokan' ),
                'googlePay'          => __( 'Google Pay', 'dokan' ),
                'login'              => __( 'Log In', 'dokan' ),
                'cancel'             => __( 'Cancel', 'dokan' ),
                'makeSelection'      => esc_attr__( 'Please select some product options before adding this product to your cart.', 'dokan' ),
                'productUnavailable' => esc_attr__( 'Sorry, this product is unavailable. Please choose a different combination.', 'dokan' ),
            ],
            'checkout'            => [
                'url'              => wc_get_checkout_url(),
                'currencyCode'     => strtolower( get_woocommerce_currency() ),
                'countryCode'      => substr( get_option( 'woocommerce_default_country' ), 0, 2 ),
                'shippingNeeded'   => $needs_shipping,
                // Defaults to 'required' to match how core initializes this option.
                'payerPhoneNeeded' => 'required' === get_option( 'woocommerce_checkout_phone_field', 'required' ),
            ],
            'button'              => $this->get_button_settings(),
            'loginStatus'         => $this->get_login_confirmation_settings(),
            'isProductPage'       => $this->is_product(),
            'product'             => $this->get_product_data(),
        ];
    }

    /**
     * Renders the payment request button.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function render_payment_request_button() {
        if ( ! $this->should_render_payment_request_button() ) {
            return;
        }

        $this->scripts();
        ?>
        <!-- Button wrapper. -->
        <div id="dokan-stripe-express-payment-request-wrapper" style="clear:both;padding-top:1.5em;display:none;">
            <div id="dokan-stripe-express-payment-request-button">
                <!-- A Stripe Element will be inserted here. -->
            </div>
        </div>
        <!-- Button wrapper. -->

         <!-- Button seperator. -->
        <p id="dokan-stripe-express-payment-request-button-separator" style="margin-top:1.5em;text-align:center;display:none;">
            &mdash; <?php esc_html_e( 'OR', 'dokan' ); ?> &mdash;
        </p>
        <!-- Button seperator. -->
        <?php
    }

    /**
     * Settings array for the user authentication dialog and redirection.
     *
     * @since 3.7.8
     *
     * @return array
     */
    public function get_login_confirmation_settings() {
        if ( is_user_logged_in() || ! $this->is_authentication_required() ) {
            return false;
        }

        /* translators: The text encapsulated in `**` can be replaced with "Apple Pay" or "Google Pay". Please translate this text, but don't remove the `**`. */
        $message      = __( 'To complete your transaction with **the selected payment method**, you must log in or create an account with our site.', 'dokan' );
        $redirect_url = add_query_arg(
            [
                '_wpnonce'                                          => wp_create_nonce( 'dokan-stripe-express-set-redirect-url' ),
                'dokan_stripe_express_payment_request_redirect_url' => rawurlencode( home_url( add_query_arg( [] ) ) ),              // Current URL to redirect to after login.
            ],
            home_url()
        );

        return [
            'message'      => $message,
            'redirect_url' => $redirect_url,
        ];
    }

    /**
     * Filters the gateway title to reflect Payment Request type.
     *
     * @since 3.7.8
     *
     * @param string $title The gateway title.
     * @param string $id    The gateway ID.
     *
     * @return string
     */
    public function filter_gateway_title( $title, $id ) {
        global $post;

        if ( ! is_object( $post ) ) {
            return $title;
        }

        $order        = wc_get_order( $post->ID );
        $method_title = is_object( $order ) ? $order->get_payment_method_title() : '';

        if ( Helper::get_gateway_id() === $id && ! empty( $method_title ) ) {
            return $method_title;
        }

        return $title;
    }

    /**
     * Add needed order meta.
     *
     * @since 3.7.8
     *
     * @param integer $order_id    The order ID.
     * @param array   $posted_data The posted data from checkout form.
     *
     * @return  void
     */
    public function add_order_meta( $order_id, $posted_data ) {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        if ( empty( $_POST['payment_request_type'] ) ) {
            return;
        }

        $order = wc_get_order( $order_id );

        $payment_request_type = sanitize_text_field( wp_unslash( $_POST['payment_request_type'] ) );
        if ( in_array( $payment_request_type, self::PAYMENT_REQUEST_TYPES, true ) ) {
            $order->set_payment_method_title( Helper::get_method_label( $payment_request_type ) );
            $order->save();
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }

    /**
     * Returns the login redirect URL.
     *
     * @since 3.7.8
     *
     * @param string $redirect Default redirect URL.
     *
     * @return string Redirect URL.
     */
    public function get_login_redirect_url( $redirect ) {
        $url = isset( $_COOKIE['dokan_stripe_express_payment_request_redirect_url'] )
            ? esc_url_raw( wp_unslash( $_COOKIE['dokan_stripe_express_payment_request_redirect_url'] ) )
            : '';

        if ( empty( $url ) ) {
            return $redirect;
        }

        wc_setcookie( 'dokan_stripe_express_payment_request_redirect_url', null );

        return $url;
    }

    /**
     * Verifies whether or not gateway environment is ready to host the Payment Request Buttons.
     *
     * @since 3.7.8
     *
     * @return bool
     */
    public function verify_gateway_env() {
        if ( ! $this->is_enabled() ) {
            return false;
        }

        if ( ! $this->is_gateway_enabled() ) {
            return false;
        }

        if ( ! dokan_is_withdraw_method_enabled( Helper::get_gateway_id() ) ) {
            return false;
        }

        if ( ! $this->api_ready ) {
            Helper::log( 'Keys are not set correctly.', 'Payment Request Buttons' );
            return false;
        }

        return true;
    }

    /**
     * Validates cart contents to ensure they are allowed to be used with Payment Request API.
     *
     * @since 3.7.8
     *
     * @return boolean
     */
    public function validate_cart_contents() {
        $cart = WC()->cart;
        if ( empty( $cart ) ) {
            return false;
        }

        foreach ( $cart->get_cart() as $key => $item ) {
            $product = apply_filters( 'woocommerce_cart_item_product', $item['data'], $item, $key );

            if ( ! $this->is_product_supported( $product ) ) {
                return false;
            }
        }

        /*
         * This is needed in case all the necessary
         * calculations has not been done yet.
         */
        $cart->calculate_totals();

        /*
         * For now, payment request doesn't work with
         * multiple shipping packages.
         */
        if ( 1 < count( $cart->get_shipping_packages() ) ) {
            return false;
        }

        return true;
    }
}
