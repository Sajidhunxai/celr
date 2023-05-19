<?php

namespace WeDevs\DokanPro\Modules\Stripe;

use Stripe\BalanceTransaction;
use Stripe\Charge;
use Stripe\Stripe;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\Stripe\Settings\RetrieveSettings;

defined( 'ABSPATH' ) || exit;

/**
 * Stripe Helper class
 *
 * @since 3.0.3
 */
class Helper {

    public static function get_settings() {
        return RetrieveSettings::instance()->settings;
    }

    /**
     * Check wheter the 3d secure is enabled or not
     *
     * @since 3.0.3
     *
     * @return bool
     */
    public static function is_3d_secure_enabled() {
        $settings = self::get_settings();

        if ( empty( $settings['enable_3d_secure'] ) || 'yes' !== $settings['enable_3d_secure'] ) {
            return false;
        }

        return true;
    }

    /**
     * Check wheter we are paying with 3ds or non_3ds payment method
     *
     * @since 3.0.3
     *
     * @return string
     */
    public static function payment_method() {
        return self::is_3d_secure_enabled() ? '3ds' : 'non_3ds';
    }

    /**
     * Check wheter the gateway in test mode or not
     *
     * @since 3.0.3
     *
     * @return bool
     */
    public static function is_test_mode() {
        $settings = self::get_settings();

        if ( empty( $settings['testmode'] ) || 'yes' !== $settings['testmode'] ) {
            return false;
        }

        return 'yes' === $settings['testmode'];
    }

    /**
     * Check if this gateway is enabled and ready to use
     *
     * @since 3.0.3
     *
     * @return bool
     */
    public static function is_ready() {
        if ( ! self::is_enabled() || ! self::get_secret_key() || ! self::get_client_id() ) {
            return false;
        }

        if ( ! self::are_keys_set() ) {
            return false;
        }

        if ( ! is_ssl() && ! self::is_test_mode() ) {
            return false;
        }

        return true;
    }

    /**
     * Check if secret key and publishable keys are valid
     *
     * @since 3.2.2
     *
     * @return bool
     */
    public static function are_keys_set() {
        if ( self::is_test_mode() ) {
            return preg_match( '/^pk_test_/', self::get_publishable_key() )
                && preg_match( '/^[rs]k_test_/', self::get_secret_key() );
        } else {
            return preg_match( '/^pk_live_/', self::get_publishable_key() )
                && preg_match( '/^[rs]k_live_/', self::get_secret_key() );
        }
    }

    /**
     * Check wheter it's enabled or not
     *
     * @since 3.0.3
     *
     * @return bool
     */
    public static function is_enabled() {
        $settings = self::get_settings();

        return ! empty( $settings['enabled'] ) && 'yes' === $settings['enabled'];
    }

    /**
     * Get secret key
     *
     * @since 3.0.3
     *
     * @return string
     */
    public static function get_secret_key() {
        $key      = self::is_test_mode() ? 'test_secret_key' : 'secret_key';
        $settings = self::get_settings();

        if ( ! empty( $settings[ $key ] ) ) {
            return $settings[ $key ];
        }
    }

    /**
     * Get secret key
     *
     * @since 3.0.3
     *
     * @return string
     */
    public static function get_publishable_key() {
        $key      = self::is_test_mode() ? 'test_publishable_key' : 'publishable_key';
        $settings = self::get_settings();

        if ( ! empty( $settings[ $key ] ) ) {
            return $settings[ $key ];
        }
    }

    /**
     * Get client id
     *
     * @since 3.0.3
     *
     * @return string
     */
    public static function get_client_id() {
        $key      = self::is_test_mode() ? 'test_client_id' : 'client_id';
        $settings = self::get_settings();

        if ( ! empty( $settings[ $key ] ) ) {
            return $settings[ $key ];
        }
    }

    /**
     * Check whether non-connected sellers can sell product or not
     *
     * @since 3.0.3
     *
     * @return bool
     */
    public static function allow_non_connected_sellers() {
        $settings = self::get_settings();

        return ! empty( $settings['allow_non_connected_sellers'] ) && 'yes' === $settings['allow_non_connected_sellers'];
    }

    /**
     * Check if non-connected sellers gets notice to connect their stripe account
     *
     * @since 3.2.2
     *
     * @return bool
     */
    public static function display_notice_to_non_connected_sellers() {
        $settings = self::get_settings();

        return ! empty( $settings['display_notice_to_non_connected_sellers'] ) && 'yes' === $settings['display_notice_to_non_connected_sellers'];
    }

    /**
     * Check if non-connected sellers gets notice to connect their stripe account
     *
     * @since 3.2.2
     *
     * @return int
     */
    public static function non_connected_sellers_display_notice_intervals() {
        $settings = self::get_settings();

        return ! empty( $settings['display_notice_interval'] ) ? absint( $settings['display_notice_interval'] ) : 7;
    }

    /**
     * Show checkout modal
     *
     * @since  3.0.3
     *
     * @return bool
     */
    public static function show_checkout_modal() {
        $settings = self::get_settings();

        return ! empty( $settings['stripe_checkout'] ) && 'yes' === $settings['stripe_checkout'];
    }

    /**
     * Get gateway title
     *
     * @since 3.0.3
     *
     * @return string
     */
    public static function get_gateway_title() {
        $settings = self::get_settings();

        $title = ! empty( $settings['title'] ) ? $settings['title'] : __( 'Stripe Connect', 'dokan' );

        return wp_kses(
            stripslashes( $title ),
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


    public static function save_cards() {
        $settings = self::get_settings();

        return ! empty( $settings['saved_cards'] ) && wc_string_to_bool( $settings['saved_cards'] );
    }

    /**
     * Does seller pay the Stripe processing fee
     *
     * @since 3.1.0
     *
     * @return bool
     */
    public static function seller_pays_the_processing_fee() {
        $settings = self::get_settings();

        return isset( $settings['seller_pays_the_processing_fee'] ) && dokan_validate_boolean( $settings['seller_pays_the_processing_fee'] );
    }

    /**
     * Check wheter subscription module is enabled or not
     *
     * @since 3.0.3
     *
     * @return bool
     */
    public static function has_subscription_module() {
        return dokan_pro()->module->is_active( 'product_subscription' );
    }

    /**
     * Set stripe app info
     *
     * @since 3.0.3
     *
     * @return void
     */
    public static function set_app_info() {
        Stripe::setAppInfo(
            'Dokan Stripe-Connect',
            DOKAN_PRO_PLUGIN_VERSION,
            'https://wedevs.com/dokan/modules/stripe-connect/',
            'pp_partner_Ee9F0QbhSGowvH'
        );
    }

    /**
     * Set stripe API version
     *
     * @since 3.0.3
     *
     * @return void
     */
    public static function set_api_version() {
        Stripe::setApiVersion( '2020-08-27' );
    }

    /**
    * Check if the order is a subscription order
    *
    * @since 1.3.3
    *
    * @return bool
    **/
    public static function is_subscription_order( $order ) {
        if ( ! self::has_subscription_module() ) {
            return false;
        }

        $product = self::get_subscription_product_by_order( $order );

        return $product ? true : false;
    }

    /**
     * Is $order_id a subscription?
     * @param  int  $order_id
     * @return boolean
     */
    public static function has_subscription( $order_id ) {
        return ( function_exists( 'wcs_order_contains_subscription' ) && ( wcs_order_contains_subscription( $order_id ) || wcs_is_subscription( $order_id ) || wcs_order_contains_renewal( $order_id ) ) );
    }

    /**
     * Checks if page is pay for order and change subs payment page.
     *
     * @since 3.2.2
     * @return bool
     */
    public static function is_subs_change_payment() {
        return ( isset( $_GET['pay_for_order'] ) && isset( $_GET['change_payment_method'] ) ); // phpcs:ignore WordPress.Security.NonceVerification
    }

    /**
     * Get subscription product from an order
     *
     * @param \WC_Order $order
     *
     * @return \WC_Order|null
     */
    public static function get_subscription_product_by_order( $order ) {
        foreach ( $order->get_items() as $item ) {
            $product = $item->get_product();

            if ( $product && 'product_pack' === $product->get_type() ) {
                return $product;
            }
        }

        return null;
    }

    /**
     * Bootstrap stripe
     *
     * @since 3.0.3
     *
     * @return void
     */
    public static function bootstrap_stripe() {
        if ( ! self::is_ready() ) {
            return;
        }

        self::set_app_info();
        self::set_api_version();

        if ( self::is_test_mode() ) {
            Stripe::setVerifySslCerts( false );
        }

        Stripe::setClientId( self::get_client_id() );
        Stripe::setApiKey( self::get_secret_key() );
    }

    /**
     * Get gateway id
     *
     * @since 3.0.3
     *
     * @return string
     */
    public static function get_gateway_id() {
        return 'dokan-stripe-connect';
    }

    /**
     * Calculate the processing fee for a single vendor for an order
     *
     * @since 3.1.0
     *
     * @param float $order_processing_fee
     * @param \WC_ORDER $suborder
     * @param \WC_ORDER $order
     *
     * @return float
     */
    public static function calculate_processing_fee_for_suborder( $order_processing_fee, $suborder, $order ) {
        $stripe_fee_for_vendor = $order_processing_fee * ( $suborder->get_total() / $order->get_total() );
        return number_format( $stripe_fee_for_vendor, 10 );
    }

    /**
     * Get vendor id by subscriptoin id
     *
     * @since 3.0.3
     *
     * @param string $subscription_id
     *
     * @return int
     */
    public static function get_vendor_id_by_subscription( $subscription_id ) {
        global $wpdb;

        $vendor_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT `user_id` FROM $wpdb->usermeta WHERE `meta_key` = %s AND `meta_value`= %s",
                '_stripe_subscription_id',
                $subscription_id
            )
        );

        return absint( $vendor_id );
    }

    /**
     * Get list of supported webhook events
     *
     * @since 3.0.3
     *
     * @return array
     */
    public static function get_supported_webhook_events() {
        return apply_filters(
            'dokan_stripe_get_supported_webhook_events',
            [
                'charge.dispute.closed'                => 'ChargeDisputeClosed',
                'charge.dispute.created'               => 'ChargeDisputeCreated',
                'invoice.payment_failed'               => 'InvoicePaymentFailed',
                'invoice.payment_succeeded'            => 'InvoicePaymentSucceeded',
                'invoice.payment_action_required'      => 'InvoicePaymentActionRequired',
                'customer.subscription.updated'        => 'SubscriptionUpdated',
                'customer.subscription.deleted'        => 'SubscriptionDeleted',
                'customer.subscription.trial_will_end' => 'SubscriptionTrialWillEnd',
            ]
        );
    }

    /**
     * Get Stripe amount to pay
     *
     * @since 3.0.3
     *
     * @return float
     */
    public static function get_stripe_amount( $total ) {
        switch ( get_woocommerce_currency() ) {
            /* Zero decimal currencies*/
            case 'BIF':
            case 'CLP':
            case 'DJF':
            case 'GNF':
            case 'JPY':
            case 'KMF':
            case 'KRW':
            case 'MGA':
            case 'PYG':
            case 'RWF':
            case 'VND':
            case 'VUV':
            case 'XAF':
            case 'XOF':
            case 'XPF':
                $total = absint( $total );
                break;
            default:
                $total = wc_format_decimal( $total, 2 ) * 100; /* In cents*/
                break;
        }

        return $total;
    }

    /**
     * Format gateway balance fee
     *
     * @param $balance_transaction
     * @return string|void
     */
    public static function format_gateway_balance_fee( $balance_transaction ) {
        if ( ! is_object( $balance_transaction ) ) {
            try {
                $balance_transaction = BalanceTransaction::retrieve( $balance_transaction );
            } catch ( \Exception $exception ) {
                dokan_log( 'Retrieving Balance Transaction Error: ' . $exception->getMessage() );
                return;
            }
        }

        if ( ! is_object( $balance_transaction ) ) {
            return;
        }

        $fee = $balance_transaction->fee;
        foreach ( $balance_transaction->fee_details as $fee_details ) {
            if ( $fee_details->type === 'stripe_fee' ) {
                $fee = $fee_details->amount;
                break;
            }
        }

        if ( ! in_array( strtolower( $balance_transaction->currency ), self::no_decimal_currencies(), true ) ) {
            $fee = number_format( $fee / 100, 2, '.', '' );
        }

        if ( $balance_transaction->exchange_rate ) {
            return number_format( $fee / $balance_transaction->exchange_rate, 2, '.', '' );
        }

        return $fee;
    }

    /**
     * Get Stripe Invoice Gateway Fee From Charge ID.
     *
     * @since 3.4.3
     *
     * @param string  $charge_id
     * @param boolean $is_formatted
     *
     * @return int|float|string
     */
    public static function get_gateway_fee_from_charge_id( $charge_id, $is_formatted = false ) {
        $charge      = Charge::retrieve( $charge_id, [ 'expand' => [ 'balance_transaction' ] ] );
        $gateway_fee = self::format_gateway_balance_fee( $charge->balance_transaction );

        return $is_formatted ? $gateway_fee : abs( $gateway_fee );
    }

    /**
     * Get no decimal currencies
     *
     * @return array
     */
    public static function no_decimal_currencies() {
        return array(
            'bif',
            'clp',
            'djf',
            'gnf',
            'jpy',
            'kmf',
            'krw',
            'mga',
            'pyg',
            'rwf',
            'ugx',
            'vnd',
            'vuv',
            'xaf',
            'xof',
            'xpf',
        );
    }

    /**
     * Checks to see if error is no such subscription error.
     *
     * @param string $error_message
     * @return false|int
     * @since 3.0.3
     */
    public static function is_no_such_subscription_error( $error_message ) {
        return preg_match( '/No such subscription/i', $error_message );
    }

    /**
     * Checks to see if error is no such subscription error.
     *
     * @param string $error_message
     * @return false|int
     * @since 3.2.2
     */
    public static function is_customer_without_source_error( $error_message ) {
        if ( preg_match( '/You provided a customer without specifying a source./i', $error_message ) ) {
            return true;
        }

        if ( preg_match( '/The customer must have an active payment source attached./i', $error_message ) ) {
            return true;
        }

        if ( preg_match( '/Cannot charge a customer that has no active card/i', $error_message ) ) {
            return true;
        }

        return false;
    }

    /**
     * Checks to see if error is of invalid request
     * error and it is no such token.
     *
     * @param array $error_message
     * @return false|int
     * @since 3.2.2
     */
    public static function is_no_such_token_error( $error_message ) {
        return preg_match( '/No such token./i', $error_message );
    }

    /**
     * Include module template
     *
     * @since 3.1.0
     *
     * @param string $name
     * @param array  $args
     *
     * @return void
     */
    public static function get_template( $name, $args = [] ) {
        dokan_get_template( "$name.php", $args, 'dokan/modules/stripe', trailingslashit( DOKAN_STRIPE_TEMPLATE_PATH ) );
    }

    /**
     * Localize Stripe messages based on code
     *
     * @since 3.1.4
     * @return array
     */
    public static function get_localized_messages() {
        return apply_filters(
            'dokan_stripe_localized_messages',
            array(
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
                'invalid_expiry_year_past' => __( 'The card\'s expiration year is in the past', 'dokan' ),
                'card_declined'            => __( 'The card was declined.', 'dokan' ),
                'missing'                  => __( 'There is no card on a customer that is being charged.', 'dokan' ),
                'processing_error'         => __( 'An error occurred while processing the card.', 'dokan' ),
                'invalid_request_error'    => __( 'Unable to process this payment, please try again or use alternative method.', 'dokan' ),
                'invalid_sofort_country'   => __( 'The billing country is not accepted by SOFORT. Please try another country.', 'dokan' ),
                'email_invalid'            => __( 'Invalid email address, please correct and try again.', 'dokan' ),
            )
        );
    }

    /**
     * Generates a localized message for an error from a response.
     *
     * @since 3.1.4
     *
     * @param stdClass $response The response from the Stripe API.
     *
     * @return string The localized error message.
     */
    public static function get_localized_error_message_from_response( $response ) {
        $localized_messages = self::get_localized_messages();

        if ( 'card_error' === $response->error->type ) {
            $localized_message = isset( $localized_messages[ $response->error->code ] ) ? $localized_messages[ $response->error->code ] : $response->error->message;
        } else {
            $localized_message = isset( $localized_messages[ $response->error->type ] ) ? $localized_messages[ $response->error->type ] : $response->error->message;
        }

        return $localized_message;
    }

    /**
     * Generate extra information for orders to send with stripe.
     *
     * @since 3.2.2
     * @param  WC_Order $order
     * @param  WC_Order $sub_order
     * @param  array $extra_metadata
     * @return array
     */
    public static function generate_payment_info( $order, $sub_order = null, $extra_metadata = [] ) {
        $post_data = [];

        // add transfer group
        $transfer_group = __( 'Dokan Order# ', 'dokan' ) . $order->get_order_number();
        $post_data['transfer_group'] = apply_filters( 'dokan_stripe_transfer_group', $transfer_group, $order, $sub_order );

        $billing_email            = $order->get_billing_email();
        $billing_first_name       = $order->get_billing_first_name();
        $billing_last_name        = $order->get_billing_last_name();

        $metadata = [
            'customer_name'     => sanitize_text_field( $billing_first_name ) . ' ' . sanitize_text_field( $billing_last_name ),
            'customer_email'    => sanitize_email( $billing_email ),
            'order_id'          => $order->get_id(),
            'site_url'          => esc_url( get_site_url() ),
        ];

        if ( self::has_subscription( $order->get_id() ) ) {
            $metadata += array(
                'payment_type' => 'recurring',
            );
        }

        if ( is_array( $extra_metadata ) && ! empty( $extra_metadata ) ) {
            $metadata += $extra_metadata;
        }

        if ( ! is_null( $sub_order ) && $sub_order->get_id() !== $order->get_id() ) {
            /* translators: 1) blog name 2) order number 3) sub order number */
            $post_data['description'] = sprintf( __( '%1$1s - Order %2$2s, suborder of %3$3s', 'dokan' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $sub_order->get_order_number(), $order->get_order_number() );

            //fix sub order metadata
            $metadata['order_id'] = $sub_order->get_id();
            $metadata['parent_order_id'] = $order->get_id();
        } else {
            /* translators: 1) blog name 2) order number */
            $post_data['description'] = sprintf( __( '%1$s - Order %2$s', 'dokan' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $order->get_order_number() );
        }

        $post_data['metadata'] = apply_filters( 'dokan_stripe_payment_metadata', $metadata, $order, $sub_order );

        return apply_filters( 'dokan_stripe_generate_payment_info', $post_data, $order, $sub_order );
    }

    /**
     * Checks whether a source exists.
     *
     * @since 3.2.2
     * @param  object $prepared_source The source that should be verified.
     * @throws DokanException     An exception if the source ID is missing.
     */
    public static function check_source( $prepared_source ) {
        if ( empty( $prepared_source->source ) ) {
            $localized_message = __( 'Checking Payment Source: Payment processing failed. Please retry.', 'dokan' );
            throw new DokanException( print_r( $prepared_source, true ), $localized_message );
        }
    }

    /**
     * Check whether subscription module is enabled or not.
     *
     * @since 3.4.3
     *
     * @return bool
     */
    public static function has_vendor_subscription_module() {
        // don't confused with product_subscription, id for vendor subscription module is product_subscription
        return function_exists( 'dokan_pro' ) && dokan_pro()->module->is_active( 'product_subscription' );
    }

    /**
     * Check if a seller is connected
     *
     * @since 3.6.1
     *
     * @param int $seller_id
     *
     * @return bool
     */
    public static function is_seller_connected( $seller_id ) {
        return '' !== get_user_meta( $seller_id, 'dokan_connected_vendor_id', true );
    }
}
