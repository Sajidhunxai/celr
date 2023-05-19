<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Support;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Api\Transaction;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\User;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Payment;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Webhook;
use WeDevs\DokanPro\Modules\StripeExpress\PaymentGateways\Stripe;

/**
 * Helper class for Stripe gateway.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Support
 */
class Helper {

    /**
     * Stripe API version
     *
     * @since 3.6.1
     *
     * @var string
     */
    private static $api_version = '2020-08-27';

    /**
     * Class names for available payment methods
     *
     * @since 3.6.1
     *
     * @var string[]
     */
    private static $available_method_classes = [ 'Card', 'Ideal', 'Sepa' ];

    /**
     * Retrievs gateway ID.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function get_gateway_id() {
        return Stripe::ID;
    }

    /**
     * Retrievs gateway title.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function get_gateway_title() {
        $title = Settings::get_gateway_title();

        if ( empty( $title ) ) {
            $title = __( 'Stripe Express', 'dokan' );
        }

        return self::sanitize_html( $title );
    }

    /**
     * Retrieves the Stripe gateway instance.
     *
     * @since 3.7.8
     *
     * @return Stripe
     */
    public static function get_gateway_instance() {
        $gateways = WC()->payment_gateways()->payment_gateways();
        return ! empty( $gateways[ self::get_gateway_id() ] ) ? $gateways[ self::get_gateway_id() ] : ( new Stripe() );
    }

    /**
     * Creates and retrieves meta key with prefix.
     *
     * @since 3.7.8
     *
     * @param string $key
     *
     * @return string
     */
    public static function meta_key( $key ) {
        return '_' . self::get_gateway_id() . "_$key";
    }

    /**
     * Retrievs gateway description.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function get_gateway_description() {
        $description = Settings::get_gateway_description();
        $description = empty( $description ) ? __( 'Pay with different payment methods via Stripe Express', 'dokan' ) : $description;

        return self::sanitize_html( $description );
    }

    /**
     * Retrieves API version of Stripe.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function get_api_version() {
        return self::$api_version;
    }

    /**
     * Retrieves text for order button on checkout page
     *
     * @since 3.6.1
     * @since 3.7.8 Added filter `dokan_stripe_express_order_button_text`
     *
     * @return string
     */
    public static function get_order_button_text() {
        return apply_filters( 'dokan_stripe_express_order_button_text', __( 'Place Order', 'dokan' ) );
    }

    /**
     * Retrieves gateway id for Stripe SEPA.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function get_sepa_gateway_id() {
        return self::get_gateway_id() . '_sepa';
    }

    /**
     * Retrieves payment method type for Stripe SEPA.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function get_sepa_payment_method_type() {
        return \WeDevs\DokanPro\Modules\StripeExpress\PaymentMethods\Sepa::STRIPE_ID;
    }

    /**
     * Checks if gateway is ready to be used
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public static function is_gateway_ready() {
        $config = Config::instance();
        if ( ! $config->is_api_ready() ) {
            return false;
        }

        if ( ! is_ssl() && $config->is_live_mode() ) {
            return false;
        }

        return true;
    }

    /**
     * Checks if Stripe express api is ready.
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public static function is_api_ready() {
        return Config::instance()->is_api_ready();
    }

    /**
     * Checks if a seller is connected to Stripe express.
     *
     * @since 3.6.1
     *
     * @param int|string $seller_id
     *
     * @return boolean
     */
    public static function is_seller_connected( $seller_id ) {
        if ( ! self::is_gateway_ready() ) {
            return false;
        }

        return User::set( $seller_id )->is_connected();
    }

    /**
     * Checks if a seller is connected and enabled for payouts.
     *
     * @since 3.7.8
     *
     * @param int|string $seller_id
     *
     * @return boolean
     */
    public static function is_seller_activated( $seller_id ) {
        if ( ! self::is_gateway_ready() ) {
            return false;
        }

        return User::set( $seller_id )->is_activated();
    }

    /**
     * Retrieves available payment methods.
     *
     * @since 3.6.1
     * @since 3.7.8 Added optional `$available_method_classes` parameter
     *
     * @param array $available_method_classes (optional)
     *
     * @return array<string,string>
     */
    public static function get_available_methods( $available_method_classes = [] ) {
        $available_methods = [];

        if ( empty( $available_method_classes ) ) {
            $available_method_classes = self::$available_method_classes;
        }

        foreach ( $available_method_classes as $method ) {
            $method_class = "\\WeDevs\\DokanPro\\Modules\\StripeExpress\\PaymentMethods\\$method";
            $available_methods[ $method_class::STRIPE_ID ] = self::get_method_label( $method_class::STRIPE_ID );
        }
        return $available_methods;
    }

    /**
     * Retrieves human readable payment method label.
     *
     * @since 3.6.2
     *
     * @param string $method_id
     *
     * @return string
     */
    public static function get_method_label( $method_id ) {
        $labels = [
            'card'                => __( 'Credit/Debit Card', 'dokan' ),
            'ideal'               => __( 'iDEAL', 'dokan' ),
            'sepa_debit'          => __( 'SEPA Direct Debit', 'dokan' ),
            'apple_pay'           => __( 'Apple Pay (Stripe)', 'dokan' ),
            'google_pay'          => __( 'Google Pay (Stripe)', 'dokan' ),
            'payment_request_api' => __( 'Payment Request (Stripe)', 'dokan' ),
        ];

        return isset( $labels[ $method_id ] ) ? $labels[ $method_id ] : self::get_gateway_title();
    }

    /**
     * Retrieves instances of available payment methods' classes.
     *
     * @since 3.6.1
     *
     * @return array<string,\WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\PaymentMethod>
     */
    public static function get_available_method_instances() {
        $available_method_instances = [];
        foreach ( self::$available_method_classes as $method ) {
            $method_class = "\\WeDevs\\DokanPro\\Modules\\StripeExpress\\PaymentMethods\\$method";
            $available_method_instances[ $method_class::STRIPE_ID ] = new $method_class();
        }
        return $available_method_instances;
    }

    /**
     * Returns the list of enabled payment method types that will function with the current checkout.
     *
     * @since 3.7.8
     *
     * @param int $order_id
     *
     * @return string[]
     */
    public static function get_enabled_payment_methods_at_checkout( $order_id = null ) {
        $available_method_ids     = [];
        $payment_method_instances = self::get_available_method_instances();
        $selected_payment_methods = Settings::get_enabled_payment_methods();

        foreach ( $selected_payment_methods as $payment_method ) {
            if ( ! isset( $payment_method_instances[ $payment_method ] ) ) {
                continue;
            }

            $method = $payment_method_instances[ $payment_method ];
            if ( ! $method->is_enabled_at_checkout( $order_id ) ) {
                continue;
            }

            if ( Settings::is_manual_capture_enabled() && $method->requires_automatic_capture() ) {
                continue;
            }

            $available_method_ids[] = $payment_method;
        }

        return $available_method_ids;
    }

    /**
     * Returns the list of enabled payment method types which are reusable
     * and will function with the current checkout.
     *
     * @since 3.7.8
     *
     * @param int $order_id
     *
     * @return string[]
     */
    public static function get_enabled_reusable_payment_methods( $order_id = null ) {
        return self::get_reusable_payment_methods( self::get_enabled_payment_methods_at_checkout( $order_id ) );
    }

    /**
     * Returns the list of enabled payment method according to their retrievable types
     * which are reusable and will be originally used.
     *
     * @since 3.7.8
     *
     * @param boolean $include_original (Optional) Indicates whether or not the original method is also needed to be included with its retrievable type
     * @param int     $order_id         (Optional) To determines the method based on the order
     *
     * @return string[]
     */
    public static function get_enabled_retrievable_payment_methods( $include_original = false, $order_id = null ) {
        $payment_method_types     = [];
        $reusable_payment_methods = self::get_enabled_reusable_payment_methods( $order_id );
        $payment_method_instances = self::get_available_method_instances();

        foreach ( $reusable_payment_methods as $payment_method ) {
            if ( ! isset( $payment_method_instances[ $payment_method ] ) ) {
                continue;
            }
            $method_instance = $payment_method_instances[ $payment_method ];

            if ( $method_instance->get_retrievable_type() !== $method_instance->get_id() ) {
                $payment_method_types[] = $method_instance->get_retrievable_type();
                if ( $include_original ) {
                    $payment_method_types[] = $method_instance->get_id();
                }
            } else {
                $payment_method_types[] = $method_instance->get_id();
            }
        }

        return $payment_method_types;
    }

    /**
     * Checks if viewing payment methods page.
     *
     * @since 3.6.1
     *
     * @return bool
     */
    public static function is_payment_methods_page() {
        global $wp;

        $page_id = wc_get_page_id( 'myaccount' );

        return ( $page_id && is_page( $page_id ) && ( isset( $wp->query_vars['payment-methods'] ) ) );
    }

    /**
     * Retrieves reuasable payment method ids.
     *
     * @since 3.6.1
     *
     * @param array $payment_methods
     *
     * @return string[]
     */
    public static function get_reusable_payment_methods( $payment_methods = [] ) {
        $payment_methods = empty( $payment_methods ) ? Settings::get_enabled_payment_methods() : (array) $payment_methods;
        return array_filter( $payment_methods, [ __CLASS__, 'is_payment_method_reusable' ] );
    }

    /**
     * Checks if a payment method is enabled for saved payments.
     *
     * @since 3.6.1
     *
     * @param string $payment_method_id Stripe payment method.
     *
     * @return boolean
     */
    public static function is_payment_method_reusable( $payment_method_id ) {
        $payment_methods = self::get_available_method_instances();
        if ( ! isset( $payment_methods[ $payment_method_id ] ) ) {
            return false;
        }
        return $payment_methods[ $payment_method_id ]->is_reusable();
    }

    /**
     * Returns true if a payment is needed for the current cart or order.
     * Subscriptions may not require an upfront payment, so we need to check whether
     * or not the payment is necessary to decide for either a setup intent or a payment intent.
     *
     * @since 3.6.1
     *
     * @param int $order_id The order ID being processed.
     *
     * @return bool Whether a payment is necessary.
     */
    public static function is_payment_needed( $order_id = null ) {
        /*
         * Free trial subscriptions without a sign up fee, or any other type
         * of order with a `0` amount should fall into the logic below.
         */
        $amount = is_null( WC()->cart ) ? 0 : WC()->cart->get_total( false );
        $order  = isset( $order_id ) ? wc_get_order( $order_id ) : null;

        if ( is_a( $order, 'WC_Order' ) ) {
            $amount = $order->get_total();
        }

        $is_payment_needed = 0 < self::get_stripe_amount( $amount, strtolower( get_woocommerce_currency() ) );

        /**
         * Modify the result of whether payment is needed.
         *
         * For example, the vendor subscription plan that has a free trial period
         * should not require a payment. This is a filter that can be used to modify
         * the result to satisfy that need.
         *
         * @since 3.7.8
         *
         * @param bool $is_payment_needed Whether payment is needed.
         * @param int  $order_id          The order ID being processed.
         */
        return apply_filters( 'dokan_stripe_express_is_payment_needed', $is_payment_needed, $order_id );
    }

    /**
     * Check if saved card is used.
     *
     * @since 3.6.1
     *
     * @param string $payment_method
     *
     * @return boolean
     */
    public static function is_saved_card( $payment_method = '' ) {
        if ( empty( $payment_method ) ) {
            $payment_method = self::get_gateway_id();
        }

        return ! empty( $_POST[ "wc-$payment_method-new-payment-method" ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    }

    /**
     * Checks if payment is via saved payment source.
     *
     * @since 3.6.1
     *
     * @return bool
     */
    public static function is_using_saved_payment_method() {
        $payment_method = ! empty( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : self::get_gateway_id(); // phpcs:ignore WordPress.Security.NonceVerification.Missing

        return ( ! empty( $_POST[ 'wc-' . $payment_method . '-payment-token' ] ) && 'new' !== $_POST[ 'wc-' . $payment_method . '-payment-token' ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
    }

    /**
     * Checks if card is a prepaid card.
     *
     * @since 3.6.1
     *
     * @param object $source_object
     *
     * @return bool
     */
    public static function is_prepaid_card( $source_object ) {
        return (
            $source_object &&
            in_array( $source_object->object, [ 'token', 'source', 'payment_method' ], true ) &&
            'prepaid' === $source_object->card->funding
        );
    }

    /**
     * Checks if a payment method object represents a prepaid credit card and
     * throws an exception if it is one, but that is not allowed.
     *
     * @since 3.6.1
     *
     * @param \Stripe\PaymentMethod $payment_method
     *
     * @return void
     * @throws DokanException An exception if the card is prepaid, but prepaid cards are not allowed.
     */
    public static function maybe_disallow_prepaid_card( $payment_method ) {
        // Check if we don't allow prepaid credit cards.
        if ( apply_filters( 'dokan_stripe_express_allow_prepaid_card', true ) || ! self::is_prepaid_card( $payment_method ) ) {
            return;
        }

        throw new DokanException(
            print_r( $payment_method, true ),
            __( 'Sorry, we\'re not accepting prepaid cards at this time. Your credit card has not been charged. Please try with alternative payment method.', 'dokan' )
        );
    }

    /**
     * Retrives the formatted blogname.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function get_blogname() {
        return wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES );
    }

    /**
     * Sanitizes user given title/descriptions.
     *
     * @since 3.7.8
     *
     * @param string $data
     *
     * @return string
     */
    public static function sanitize_html( $data ) {
        return wp_kses(
            stripslashes( $data ),
            [
                'br'   => true,
                'img'  => [
                    'alt'   => true,
                    'class' => true,
                    'src'   => true,
                    'title' => true,
                ],
                'p'    => [
                    'class' => true,
                ],
                'span' => [
                    'class' => true,
                    'title' => true,
                ],
            ]
        );
    }

    /**
     * Sanitize statement descriptor text.
     *
     * Stripe requires max of 22 characters and no special characters.
     *
     * @since 3.6.1
     *
     * @param string $statement_descriptor
     *
     * @return string $statement_descriptor Sanitized statement descriptor
     */
    public static function clean_statement_descriptor( $statement_descriptor ) {
        if ( empty( $statement_descriptor ) ) {
            return '';
        }

        $disallowed_characters = [ '<', '>', '\\', '*', '"', "'", '/', '(', ')', '{', '}' ];

        // Strip any tags.
        $statement_descriptor = wp_strip_all_tags( $statement_descriptor );

        // Remove html entities.
        $statement_descriptor = preg_replace( '/&#?[a-z0-9]{2,8};/i', '', $statement_descriptor );

        // Next, remove any remaining disallowed characters.
        $statement_descriptor = str_replace( $disallowed_characters, '', $statement_descriptor );

        // Trim any whitespace at the ends and limit to 22 characters.
        $statement_descriptor = substr( trim( $statement_descriptor ), 0, 22 );

        return $statement_descriptor;
    }

    /**
     * Retrieves enabled billing fields.
     *
     * @since 3.6.1
     *
     * @return string[]
     */
    public static function get_enabled_billing_fields() {
        $enabled_billing_fields = [];
        if ( ! WC()->checkout() ) {
            return $enabled_billing_fields;
        }

        foreach ( WC()->checkout()->get_checkout_fields( 'billing' ) as $billing_field => $billing_field_options ) {
            if ( ! isset( $billing_field_options['enabled'] ) || $billing_field_options['enabled'] ) {
                $enabled_billing_fields[] = $billing_field;
            }
        }
        return $enabled_billing_fields;
    }

    /**
     * Get Stripe amount to pay.
     *
     * @since 3.6.1
     *
     * @param float  $total Amount due.
     * @param string $currency Accepted currency.
     *
     * @return float|int
     */
    public static function get_stripe_amount( $total, $currency = '' ) {
        if ( ! $currency ) {
            $currency = get_woocommerce_currency();
        }

        /*
         * Note: It's necessary to cast the amount to integer.
         *      Otherwise, in some cases, the returned amount
         *      gets engineered in weird form in the javascript end.
         *
         *      For example, if we return amount 1710,
         *      it gets casted to 1710.00000000000002.
         */

        if ( in_array( strtolower( $currency ), self::no_decimal_currencies(), true ) ) {
            return absint( $total );
        }

        return absint( wc_format_decimal( $total, 2 ) * 100 );
    }

    /**
     * List of currencies supported by Stripe that has no decimals.
     *
     * @see https://stripe.com/docs/currencies#zero-decimal
     * @see https://stripe.com/docs/currencies#presentment-currencies
     *
     * @since 3.6.1
     *
     * @return string[] $currencies
     */
    public static function no_decimal_currencies() {
        return [
            'bif', // Burundian Franc
            'clp', // Chilean Peso
            'djf', // Djiboutian Franc
            'gnf', // Guinean Franc
            'jpy', // Japanese Yen
            'kmf', // Comorian Franc
            'krw', // South Korean Won
            'mga', // Malagasy Ariary
            'pyg', // Paraguayan Guaraní
            'rwf', // Rwandan Franc
            'ugx', // Ugandan Shilling
            'vnd', // Vietnamese Đồng
            'vuv', // Vanuatu Vatu
            'xaf', // Central African Cfa Franc
            'xof', // West African Cfa Franc
            'xpf', // Cfp Franc
        ];
    }

    /**
     * Stripe uses smallest denomination in currencies such as cents.
     * We need to format the returned currency from Stripe into human readable form.
     * The amount is not used in any calculations so returning string is sufficient.
     *
     * @since 3.6.1
     *
     * @param \Stripe\BalanceTransaction $balance_transaction Stripe Balance transaction object
     * @param string                     $type                Type of number to format
     *
     * @return string
     */
    public static function format_balance_fee( $balance_transaction, $type = 'fee' ) {
        if ( is_string( $balance_transaction ) ) {
            $balance_transaction = Transaction::get( $balance_transaction );
        }

        if ( empty( $balance_transaction ) || ! is_object( $balance_transaction ) ) {
            return 0;
        }

        $fee = $balance_transaction->fee;
        foreach ( $balance_transaction->fee_details as $fee_details ) {
            if ( $fee_details->type === 'stripe_fee' ) {
                $fee = $fee_details->amount;
                break;
            }
        }

        if ( ! in_array( strtolower( $balance_transaction->currency ), self::no_decimal_currencies(), true ) ) {
            if ( 'net' === $type ) {
                return number_format( $balance_transaction->net / 100, 2, '.', '' );
            }

            $fee = number_format( $fee / 100, 2, '.', '' );
        }

        if ( 'net' === $type ) {
            return $balance_transaction->net;
        }

        if ( $balance_transaction->exchange_rate ) {
            $fee = number_format( $fee / $balance_transaction->exchange_rate, 2, '.', '' );
        }

        return $fee;
    }

    /**
     * Initiates the WP Filesystem API.
     *
     * @since 3.7.8
     *
     * @uses WP_Filesystem()
     *
     * @return void
     */
    public static function init_filesystem() {
        if ( ! function_exists( 'WP_Filesystem' ) ) {
            require_once ABSPATH . '/wp-admin/includes/file.php';
        }

        /*
         * Fix the filesystem method to `direct`. It will be filtered for this operation
         * only and the hook will be removed after initialization.
         */
        add_filter( 'filesystem_method', [ __CLASS__, 'get_filesystem_method' ], 10, 1 );

        WP_Filesystem();

        remove_filter( 'filesystem_method', [ __CLASS__, 'get_filesystem_method' ], 10, 1 );
    }

    /**
     * Modifies filesystem method as necessary.
     *
     * @since 3.7.8
     *
     * @param string $method
     *
     * @return string
     */
    public static function get_filesystem_method( $method ) {
        return 'direct';
    }

    /**
     * Checks if this page is a cart or checkout page.
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public static function has_cart_or_checkout_on_current_page() {
        return is_cart() || is_checkout();
    }

    /**
     * Retrieves current url.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function get_current_url() {
        $http_host   = isset( $_SERVER['HTTP_HOST'] ) ? esc_url_raw( wp_unslash( $_SERVER['HTTP_HOST'] ) ) : '';
        $request_uri = isset( $_SERVER['REQUEST_URI'] ) ? esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) : '';

        return "{$http_host}{$request_uri}";
    }

    /**
     * Retrieves the url for payment settings of Stripe express in vendor dashboard.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function get_payment_settings_url() {
        return dokan_get_navigation_url( 'settings/payment-manage-' . self::get_gateway_id() );
    }

    /**
     * Checks if the request contains the values that indicates
     * a redirection after a successful setup intent creation.
     *
     * @since 3.6.1
     *
     * @return bool
     */
    public static function is_setup_intent_success_creation_redirection() {
        return ( ! empty( $_GET['setup_intent_client_secret'] ) & ! empty( $_GET['setup_intent'] ) & ! empty( $_GET['redirect_status'] ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    }

    /**
     * Retrives dropdown options for the payment method selector.
     *
     * @since 3.7.8
     *
     * @return string[]
     */
    public static function get_payment_methods_dropdown() {
        return self::get_available_methods(
            [
                'Card',
                'Ideal',
            ]
        );
    }

    /**
     * Verifies whether a certain ZIP code is valid for a country.
     * The default country is US incl. 4-digit extensions.
     *
     * @since 3.6.1
     *
     * @todo Implement validation for all countries needed
     *
     * @param string $zip The ZIP code to verify.
     * @param string $country
     *
     * @return boolean
     */
    public static function is_valid_zip_code( $zip, $country = 'US' ) {
        $is_valid = false;
        switch ( strtolower( $country ) ) {
            case 'us':
                $is_valid = ! empty( preg_match( '/^\d{5,5}(-\d{4,4})?$/', $zip ) );
                break;
        }

        return apply_filters( 'dokan_stripe_express_is_valid_zip_code', $is_valid, $zip, $country );
    }

    /**
     * Checks if a date is valid.
     *
     * @since 3.6.1
     *
     * @param string $date
     *
     * @return boolean
     */
    public static function is_valid_date( $date ) {
        if ( ! preg_match( '/^(\d{4,4})\-(\d{2,2})\-(\d{2,2})$/', $date, $offset ) ) {
            return false;
        }

        if ( ! wp_checkdate( $offset[2], $offset[3], $offset[1], $date ) ) {
            return false;
        }

        return true;
    }

    /**
     * Converts a WooCommerce locale to the closest supported by Stripe.js.
     *
     * Stripe.js supports only a subset of IETF language tags,
     * if a country specific locale is not supported we use the default for that language.
     * If no match is found we return 'auto' so Stripe.js uses the browser locale.
     *
     * @see https://stripe.com/docs/js/appendix/supported_locales
     *
     * @since 3.6.1
     *
     * @param string $wc_locale The locale to convert.
     *
     * @return string Closest locale supported by Stripe ('auto' if NONE).
     */
    public static function convert_locale( $wc_locale ) {
        $supported = [
            'ar',     // Arabic.
            'bg',     // Bulgarian (Bulgaria).
            'cs',     // Czech (Czech Republic).
            'da',     // Danish.
            'de',     // German (Germany).
            'el',     // Greek (Greece).
            'en',     // English.
            'en-GB',  // English (United Kingdom).
            'es',     // Spanish (Spain).
            'es-419', // Spanish (Latin America).
            'et',     // Estonian (Estonia).
            'fi',     // Finnish (Finland).
            'fr',     // French (France).
            'fr-CA',  // French (Canada).
            'he',     // Hebrew (Israel).
            'hu',     // Hungarian (Hungary).
            'id',     // Indonesian (Indonesia).
            'it',     // Italian (Italy).
            'ja',     // Japanese.
            'lt',     // Lithuanian (Lithuania).
            'lv',     // Latvian (Latvia).
            'ms',     // Malay (Malaysia).
            'mt',     // Maltese (Malta).
            'nb',     // Norwegian Bokmål.
            'nl',     // Dutch (Netherlands).
            'pl',     // Polish (Poland).
            'pt-BR',  // Portuguese (Brazil).
            'pt',     // Portuguese (Brazil).
            'ro',     // Romanian (Romania).
            'ru',     // Russian (Russia).
            'sk',     // Slovak (Slovakia).
            'sl',     // Slovenian (Slovenia).
            'sv',     // Swedish (Sweden).
            'th',     // Thai.
            'tr',     // Turkish (Turkey).
            'zh',     // Chinese Simplified (China).
            'zh-HK',  // Chinese Traditional (Hong Kong).
            'zh-TW',  // Chinese Traditional (Taiwan).
        ];

        // Stripe uses '-' instead of '_' (used in WordPress).
        $locale = str_replace( '_', '-', $wc_locale );

        if ( in_array( $locale, $supported, true ) ) {
            return $locale;
        }

        /*
         * We need to map these locales to Stripe's Spanish
         * 'es-419' locale and other variations.
         * This list should be updated if more localized versions of
         * Latin American Spanish are made available.
         */
        $lowercase_locale                  = strtolower( $wc_locale );
        $translated_latin_american_locales = [
            'es_co', // Spanish (Colombia).
            'es_ec', // Spanish (Ecuador).
            'es_mx', // Spanish (Mexico).
            'es_ve', // Spanish (Venezuela).
        ];
        if ( in_array( $lowercase_locale, $translated_latin_american_locales, true ) ) {
            return 'es-419';
        }

        // Finally, we check if the "base locale" is available.
        $base_locale = substr( $wc_locale, 0, 2 );
        if ( in_array( $base_locale, $supported, true ) ) {
            return $base_locale;
        }

        // Default to 'auto' so Stripe.js uses the browser locale.
        return 'auto';
    }

    /**
     * Retrieves locale options for Stripe.
     *
     * @see https://support.stripe.com/questions/language-options-for-customer-emails
     *
     * @since 3.6.1
     *
     * @return string[]
     */
    public static function get_stripe_locale_options() {
        return [
            'ar'    => 'ar-AR',
            'da_DK' => 'da-DK',
            'de_DE' => 'de-DE',
            'en'    => 'en-US',
            'es_ES' => 'es-ES',
            'es_CL' => 'es-419',
            'es_AR' => 'es-419',
            'es_CO' => 'es-419',
            'es_PE' => 'es-419',
            'es_UY' => 'es-419',
            'es_PR' => 'es-419',
            'es_GT' => 'es-419',
            'es_EC' => 'es-419',
            'es_MX' => 'es-419',
            'es_VE' => 'es-419',
            'es_CR' => 'es-419',
            'fi'    => 'fi-FI',
            'fr_FR' => 'fr-FR',
            'he_IL' => 'he-IL',
            'it_IT' => 'it-IT',
            'ja'    => 'ja-JP',
            'nl_NL' => 'nl-NL',
            'nn_NO' => 'no-NO',
            'pt_BR' => 'pt-BR',
            'sv_SE' => 'sv-SE',
        ];
    }

    /**
     * Get supported countries from where vendors can sign up.
     *
     * @since 3.7.17
     *
     * @param bool $include_restricted_countries Whether or not the restricted countries should be included.
     *
     * @return array<string,string>
     */
    public static function get_supported_countries_for_vendors( $include_restricted_countries = false ) {
        $supported_countries = Payment::get_supported_transfer_countries();
        if ( empty( $supported_countries ) ) {
            return [];
        }

        $countries = new \WC_Countries();
        $countries = $countries->get_countries();

        if ( ! $include_restricted_countries ) {
            $supported_countries = array_values( array_diff( $supported_countries, Settings::get_restricted_countries() ) );
        }

        $vendor_countries = array_filter(
            $countries,
            function ( $country_code ) use ( $supported_countries ) {
                return in_array( $country_code, $supported_countries, true );
            },
            ARRAY_FILTER_USE_KEY
        );

        return $vendor_countries;
    }

    /**
     * Retrieves the supported European countries.
     *
     * @since 3.7.17
     *
     * @return array
     */
    public static function get_european_countries() {
        $eu_countries = WC()->countries->get_european_union_countries();
        $non_eu_sepa_countries = [ 'AD', 'CH', 'GB', 'MC', 'SM', 'VA' ];
        return array_merge( $eu_countries, $non_eu_sepa_countries );
    }

    /**
     * Gets the customer's locale/language based on their setting or the site settings.
     *
     * @since 3.6.1
     *
     * @param WP_User $user
     *
     * @return string The locale/language set in the user profile or the site itself.
     */
    public static function get_locale( $user = false ) {
        // If we have a user, get their locale with a site fallback.
        return ! empty( $user ) ? \get_user_locale( $user->ID ) : \get_locale();
    }

    /**
     * Checks to see if error is of invalid request
     * error and it is no such customer.
     *
     * @since 3.6.1
     *
     * @param object|string $error
     *
     * @return boolean
     */
    public static function is_no_such_customer_error( $error ) {
        if ( is_object( $error ) ) {
            if ( $error instanceof DokanException ) {
                return preg_match( '/no-such-customer/', $error->get_error_code() );
            }

            return (
                'invalid_request_error' === $error->type &&
                preg_match( '/No such customer/i', $error->message )
            );
        }

        return preg_match( '/No such customer/i', $error );
    }

    /**
     * Checks if the error message is pointing to a missing subscription.
     *
     * @since 3.7.8
     *
     * @param string $error_message
     *
     * @return boolean
     */
    public static function is_no_such_subscription_error( $error_message ) {
        return preg_match( '/No such subscription/i', $error_message );
    }

    /**
     * Checks to see if error is of same idempotency key
     * error due to retries with different parameters.
     *
     * @since 3.7.8
     *
     * @param object $error
     *
     * @return boolean
     */
    public static function is_same_idempotency_error( $error ) {
        return (
            is_object( $error ) &&
            'idempotency_error' === $error->type &&
            preg_match( '/Keys for idempotent requests can only be used with the same parameters they were first used with./i', $error->message )
        );
    }

    /**
     * Checks if the error os retryable.
     *
     * @since 3.6.1
     *
     * @param object $error
     *
     * @return boolean
     */
    public static function is_retryable_error( $error ) {
        return (
            is_object( $error ) &&
            'invalid_request_error' === $error->type ||
            'idempotency_error' === $error->type ||
            'rate_limit_error' === $error->type ||
            'api_connection_error' === $error->type ||
            'api_error' === $error->type
        );
    }

    /**
     * Checks to see if error is of invalid request
     * error and it is no such source.
     *
     * @since 3.7.8
     *
     * @param object $error
     *
     * @return boolean
     */
    public static function is_no_such_source_error( $error ) {
        return (
            is_object( $error ) &&
            'invalid_request_error' === $error->type &&
            preg_match( '/No such (source|PaymentMethod)/i', $error->message )
        );
    }

    /**
     * Checks to see if error is of invalid request
     * error and it is no such source linked to customer.
     *
     * @since 3.7.8
     *
     * @param object|string $error
     *
     * @return boolean
     */
    public static function is_no_linked_source_error( $error ) {
        if ( is_object( $error ) ) {
            return (
                'invalid_request_error' === $error->type &&
                preg_match( '/does not have a linked source with ID/i', $error->message )
            );
        }

        return preg_match( '/does not have a linked source with ID/i', $error );
    }

    /**
     * Checks to see if error is of invalid request
     * error and it is no such customer.
     *
     * @since 3.6.1
     *
     * @param object|string $error
     *
     * @return bool
     */
    public static function is_source_already_attached_error( $error ) {
        if ( is_object( $error ) ) {
            return (
                'invalid_request_error' === $error->type &&
                preg_match( '/already been attached to a customer/i', $error->message )
            );
        }

        return preg_match( '/already been attached to a customer/i', $error );
    }

    /**
     * Retrieves localized message based on code.
     *
     * @since 3.6.1
     *
     * @return array<string,string>|string
     */
    public static function get_payment_message( $key = '' ) {
        $messages = apply_filters(
            'dokan_stripe_express_localized_messages',
            [
                'invalid_number'           => __( 'The card number is not a valid credit card number.', 'dokan' ),
                'invalid_expiry_month'     => __( 'The card\'s expiration month is invalid.', 'dokan' ),
                'invalid_expiry_year'      => __( 'The card\'s expiration year is invalid.', 'dokan' ),
                'invalid_cvc'              => __( 'The card\'s security code is invalid.', 'dokan' ),
                'incorrect_number'         => __( 'The card number is incorrect.', 'dokan' ),
                'incomplete_number'        => __( 'The card number is incomplete.', 'dokan' ),
                'incomplete_cvc'           => __( 'The card\'s security code is incomplete.', 'dokan' ),
                'incomplete_expiry'        => __( 'The card\'s expiration date is incomplete.', 'dokan' ),
                'expired_card'             => __( 'The card has expired.', 'dokan' ),
                'incorrect_cvc'            => __( 'The card\'s security code is incorrect.', 'dokan' ),
                'incorrect_zip'            => __( 'The card\'s zip code failed validation.', 'dokan' ),
                'postal_code_invalid'      => __( 'Invalid zip code, please correct and try again', 'dokan' ),
                'invalid_expiry_year_past' => __( 'The card\'s expiration year is in the past', 'dokan' ),
                'card_declined'            => __( 'The card was declined.', 'dokan' ),
                'missing'                  => __( 'There is no card on a customer that is being charged.', 'dokan' ),
                'processing_error'         => __( 'An error occurred while processing the card.', 'dokan' ),
                'email_invalid'            => __( 'Invalid email address, please correct and try again.', 'dokan' ),
                'invalid_request_error'    => is_add_payment_method_page()
                    ? __( 'Unable to save this payment method, please try again or use alternative method.', 'dokan' )
                    : __( 'Unable to process this payment, please try again or use alternative method.', 'dokan' ),
                'amount_too_large'         => __( 'The order total is too high for this payment method', 'dokan' ),
                'amount_too_small'         => __( 'The order total is too low for this payment method', 'dokan' ),
                'country_code_invalid'     => __( 'Invalid country code, please try again with a valid country code', 'dokan' ),
                'tax_id_invalid'           => __( 'Invalid Tax Id, please try again with a valid tax id', 'dokan' ),
            ]
        );

        if ( ! empty( $key ) && isset( $messages[ $key ] ) ) {
            return $messages[ $key ];
        }

        return $messages;
    }

    /**
     * Retrieves error message from response object.
     *
     * @since 3.6.1
     *
     * @param object $response
     *
     * @return string
     */
    public static function get_error_message_from_response( $response ) {
        $messages = self::get_payment_message();

        if ( 'card_error' === $response->error->type ) {
            $message = isset( $messages[ $response->error->code ] ) ? $messages[ $response->error->code ] : $response->error->message;
        } else {
            $message = isset( $messages[ $response->error->type ] ) ? $messages[ $response->error->type ] : $response->error->message;
        }

        return $message;
    }

    /**
     * Retrieves possible error messages.
     *
     * @since 3.6.1
     *
     * @param string $key
     *
     * @return array<string,string>|string
     */
    public static function get_error_message( $key = '' ) {
        $messages = [
            'timeout' => __( 'A timeout occurred while connecting to the server. Please try again.', 'dokan' ),
            'abort'   => __( 'The connection to the server was aborted. Please try again.', 'dokan' ),
            'default' => __( 'An error occurred while connecting to the server. Please try again.', 'dokan' ),
        ];

        if ( empty( $key ) ) {
            return $messages;
        }

        if ( isset( $messages[ $key ] ) ) {
            return $messages[ $key ];
        }

        return $messages['default'];
    }

    /**
     * Retrieves admin settings webhook description.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function get_webhook_description() {
        return wp_kses(
            sprintf(
                /* translators: 1) opening strong tag, 2) non-breaking space, 3) webhook url, 4) non-breaking space, 5) closing strong tag, 6) opening anchor tag with stripe dashboard link, 7) closing anchor tag, 8) <br> tag, 9) <br> tag, 10) opening code tag, 11) webhook status, 12) closing code tag */
                __(
                    'You must add the following webhook endpoint %1$s%2$s%3$s%4$s%5$s to your %6$sStripe account settings%7$s (if there isn\'t one already enabled). This will enable you to receive notifications on the charge statuses. The webhook endpoint will be attempted to be configured automatically on saving these admin settings. If it is not configured automatically, please register it manually.%8$s%9$s%10$s%11$s%12$s',
                    'dokan'
                ),
                '<strong style="background-color:#ddd;">',
                '&nbsp;',
                Webhook::generate_url(),
                '&nbsp;',
                '</strong>',
                '<a href="https://dashboard.stripe.com/account/webhooks" target="_blank">',
                '</a>',
                '<br>',
                '<br>',
                '<code>',
                Webhook::get_status_notice(),
                '</code>'
            ),
            [
                'strong' => [
                    'style' => true,
                ],
                'a'      => [
                    'href'   => true,
                    'target' => true,
                ],
                'br'     => [],
                'code'   => [],
            ]
        );
    }

    /**
     * Retrieves description API keys.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function get_api_keys_description() {
        return wp_kses(
            sprintf(
                // translators: 1) help documentation link, 2) opening anchor tag, 3) closing anchor tag, 4) line break tag, 5) opening <strong> and <span> tags combined, 6) closing </strong> and </span> tags combined
                __( 'Your API credentials are a publishable key and a secret key, which authenticate API requests from your account. You can collect these credentials from a REST API app in the Developer Dashboard. Visit %1$sthis link%2$s for more information about getting your api details.%3$s%4$sNote: Even if you enable test mode, please provide your live API keys as well. For some extra configurations for payment methods like Apple Pay and payment request buttons, live API keys are required even in test mode.%5$s', 'dokan' ),
                '<a href="https://dashboard.stripe.com/apikeys" target="_blank">',
                '</a>',
                '<br>',
                '<span style="font-style: italic;">',
                '</span>'
            ),
            [
                'a'      => [
                    'href'   => true,
                    'target' => true,
                ],
                'br'     => [],
                'strong' => [],
                'span'   => [
                    'style' => true,
                ],
            ]
        );
    }

    /**
     * Retrieves the express settings url in Stripe dashboard.
     *
     * @since 3.7.17
     *
     * @return string
     */
    public static function get_stripe_express_dashboard_settings_url() {
        return sprintf(
            'https://dashboard.stripe.com%s/settings/connect/express',
            Settings::is_test_mode() ? '/test' : '',
        );
    }

    /**
     * Includes module template
     *
     * @since 3.6.1
     *
     * @param string $file_name Template file name
     * @param array  $args     Necessary variables (Optional)
     * @param string $location Sub folder name inside template (Optional)
     *
     * @return void
     */
    public static function get_template( $file_name, $args = [], $location = '' ) {
        $file_name = sanitize_key( $file_name ) . '.php';
        $location  = ! empty( $location ) ? "$location/" : '';
        dokan_get_template( $file_name, $args, '', trailingslashit( DOKAN_STRIPE_EXPRESS_TEMPLATE_PATH ) . $location );
    }

    /**
     * Includes module template for Admin
     *
     * @since 3.6.1
     *
     * @param string $file_name Template file name
     * @param array  $args      Necessary variables (Optional)
     *
     * @return void
     */
    public static function get_admin_template( $file_name, $args = [] ) {
        self::get_template( $file_name, $args, 'admin' );
    }

    /**
     * Writes error log messages.
     *
     * @since 3.6.1
     *
     * @param string $message
     * @param string $level
     *
     * @return void
     */
    public static function log( $message, $category = '', $level = 'debug' ) {
        if ( ! Settings::is_debug_mode() ) {
            return;
        }

        \dokan_log( sprintf( '[Dokan Stripe Express] %s: ', $category ) . print_r( $message, true ), $level );
    }
}
