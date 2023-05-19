<?php

namespace WeDevs\DokanPro\Modules\Razorpay;

use WC_Order;
use WP_Error;
use WeDevs\DokanPro\Modules\Razorpay\PaymentMethods\Razorpay;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Helper.
 *
 * @package WeDevs\DokanPro\Modules\Razorpay
 *
 * @since 3.5.0
 */
class Helper {
    /**
     * Get Razorpay gateway id.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_gateway_id() {
        // do not change this value ever, otherwise this will cause inconsistancy while retrieving data
        return 'dokan_razorpay';
    }

    /**
     * Get settings of the gateway.
     *
     * @since 3.5.0
     *
     * @param string $key
     *
     * @return mixed|void
     */
    public static function get_settings( $key = null ) {
        $settings = get_option( 'woocommerce_' . static::get_gateway_id() . '_settings', [] );

        if ( isset( $key ) && isset( $settings[ $key ] ) ) {
            return $settings[ $key ];
        }

        return $settings;
    }

    /**
     * Get Gateway title name.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_gateway_title() {
        $settings = static::get_settings();

        return ! empty( $settings['title'] ) ? $settings['title'] : __( 'Dokan Razorpay', 'dokan' );
    }

    /**
     * Check whether it's enabled or not.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function is_enabled() {
        $settings = static::get_settings();

        return ! empty( $settings['enabled'] ) && 'yes' === $settings['enabled'];
    }

    /**
     * Check if this gateway is enabled and ready to use.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function is_ready() {
        if ( ! static::is_enabled() ||
            empty( static::get_key_id() ) ||
            empty( static::get_key_secret() ) ||
            'INR' !== get_woocommerce_currency()
            ) {
            return false;
        }

        return true;
    }

    /**
     * Check if this gateway is enabled and ready to use.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function is_api_ready() {
        if (
            empty( static::get_key_id() ) ||
            empty( static::get_key_secret() ) ) {
            return false;
        }

        return true;
    }

    /**
     * Check if the seller is enabled for receive razorpay payment.
     *
     * @since 3.5.0
     *
     * @param int $seller_id
     *
     * @return bool
     */
    public static function is_seller_enable_for_receive_payment( $seller_id ) {
        return static::get_seller_account_id( $seller_id ) && static::get_seller_enabled_for_received_payment( $seller_id );
    }

    /**
     * Check whether the gateway in test mode or not.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function is_test_mode() {
        $settings = static::get_settings();

        return ! empty( $settings['test_mode'] ) && 'yes' === $settings['test_mode'];
    }

    /**
     * Get Seller Account ID Key.
     *
     * @since 3.5.0
     *
     * @param bool|null $test_mode
     *
     * @return string
     */
    public static function get_seller_account_id_key( $test_mode = null ) {
        if ( null === $test_mode ) {
            $test_mode = static::is_test_mode();
        }
        return $test_mode ? '_dokan_razorpay_test_account_id' : '_dokan_razorpay_account_id';
    }

    /**
     * Get Seller Account ID Key in trash mode.
     *
     * @since 3.5.0
     *
     * @param bool|null $test_mode
     *
     * @return string
     */
    public static function get_seller_account_id_key_trashed( $test_mode = null ) {
        return self::get_seller_account_id_key( $test_mode ) . '_trashed';
    }

    /**
     * Get Seller Account ID for razorpay.
     *
     * @since 3.5.0
     *
     * @param int $seller_id
     *
     * @return string
     */
    public static function get_seller_account_id( $seller_id ) {
        return get_user_meta( $seller_id, static::get_seller_account_id_key(), true );
    }

    /**
     * Get seller enabled received payment key.
     *
     * @since 3.5.0
     *
     * @param bool|null $test_mode
     *
     * @return string
     */
    public static function get_seller_enabled_for_received_payment_key( $test_mode = null ) {
        if ( null === $test_mode ) {
            $test_mode = static::is_test_mode();
        }
        return $test_mode ? '_dokan_razorpay_test_enable_for_receive_payment' : '_dokan_razorpay_enable_for_receive_payment';
    }

    /**
     * Get Seller Payment Receivable Key.
     *
     * @since 3.5.0
     *
     * @param bool|null $test_mode
     *
     * @return string
     */
    public static function get_seller_payments_receivable_key( $test_mode = null ) {
        if ( null === $test_mode ) {
            $test_mode = static::is_test_mode();
        }
        return $test_mode ? '_dokan_razorpay_test_payments_receivable' : '_dokan_razorpay_payments_receivable';
    }

    /**
     * Check Seller Enable for recive payment or not.
     *
     * @since 3.5.0
     *
     * @param int $seller_id
     *
     * @return string
     */
    public static function get_seller_enabled_for_received_payment( $seller_id ) {
        return get_user_meta( $seller_id, static::get_seller_enabled_for_received_payment_key(), true );
    }

    /**
     * Get Razorpay Key Id.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_key_id() {
        $key      = static::is_test_mode() ? 'test_key_id' : 'key_id';
        $settings = static::get_settings();

        return ! empty( $settings[ $key ] ) ? $settings[ $key ] : '';
    }

    /**
     * Get Razorpay Key Secret.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_key_secret() {
        $key      = static::is_test_mode() ? 'test_key_secret' : 'key_secret';
        $settings = static::get_settings();

        return ! empty( $settings[ $key ] ) ? $settings[ $key ] : '';
    }

    /**
     * Get Disbursement mode for transfer.
     *
     * Values could be - 'INSTANT', 'ON_ORDER_COMPLETE', 'DELAYED'
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_disbursement_mode() {
        $key      = 'disbursement_mode';
        $settings = static::get_settings();

        return ! empty( $settings[ $key ] ) ? $settings[ $key ] : 'INSTANT';
    }

    /**
     * Get disbersement delay period.
     *
     * @since 3.5.0
     *
     * @return int
     */
    public static function get_disbursement_delay_period() {
        $key      = 'razorpay_disbursement_delay_period';
        $settings = static::get_settings();

        return ! empty( $settings[ $key ] ) ? (int) $settings[ $key ] : 0;
    }

    /**
     * Get on hold until time for transfer or disburse.
     *
     * For disbursement mode, it will subtract the delay period from current time.
     * For transfer mode, it will add the delay period from current time.
     *
     * @since 3.5.0
     *
     * @param bool $is_disbursement. eg: true for disbursement.
     *
     * @return DateTimeImmutable
     */
    public static function get_on_hold_until_time( $is_disbursement = false ) {
        $time_now      = dokan_current_datetime();
        $time_now      = $time_now->setTime( 23, 59, 59 );
        $interval_days = static::get_disbursement_delay_period();

        if ( $interval_days > 0 ) {
            // Razorpay has no day limitation, transfer will be on hold indefinitely.
            $interval = new \DateInterval( "P{$interval_days}D" );
            $time_now = $is_disbursement ? $time_now->sub( $interval ) : $time_now->add( $interval );
        }

        return $time_now;
    }

    /**
     * Check if non-connected sellers sees notice on their dashboard to connect their Razorpay account.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function display_notice_on_vendor_dashboard() {
        $key      = 'display_notice_on_vendor_dashboard';
        $settings = static::get_settings();

        return ! empty( $settings[ $key ] ) && 'yes' === $settings[ $key ];
    }

    /**
     * Check if non-connected sellers gets announcement to connect their Razorpay account.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function display_announcement_to_non_connected_sellers() {
        $key      = 'display_notice_to_non_connected_sellers';
        $settings = static::get_settings();

        return ! empty( $settings[ $key ] ) && 'yes' === $settings[ $key ];
    }

    /**
     * Get Connect announcement interval.
     *
     * @since DOKAN_PRO_SiNCE
     *
     * @return int
     */
    public static function non_connected_sellers_display_notice_intervals() {
        $key      = 'display_notice_interval';
        $settings = static::get_settings();

        return ! empty( $settings[ $key ] ) ? absint( $settings[ $key ] ) : 7;
    }

    /**
     * Does seller pays the Razorpay processing fee.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function seller_pays_the_processing_fee() {
        $settings = self::get_settings();

        return isset( $settings['seller_pays_the_processing_fee'] ) && dokan_validate_boolean( $settings['seller_pays_the_processing_fee'] );
    }

    /**
     * Get webhook key name registered in razorpay for dokan.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_webhook_key() {
        return 'dokan_razorpay_webhook_key';
    }

    /**
     * Get razorpay webhook id registered for dokan.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_webhook_id() {
        return get_option( self::get_webhook_key(), '' );
    }

    /**
     * Get webhook secret key for webhook matching.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_webhook_secret_key() {
        return 'webhook_secret';
    }

    /**
     * Get webhook URL.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_webhook_url() {
        return home_url( 'wc-api/' . static::get_gateway_id(), 'https' );
    }

    /**
     * Get Webhook Secret value.
     *
     * We've to check this secret value to verify
     * any webhook request from Razorpay.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_webhook_secret() {
        $settings = static::get_settings();
        return ! empty( $settings[ static::get_webhook_secret_key() ] ) ? $settings[ static::get_webhook_secret_key() ] : '';
    }

    /**
     * Get list of webhook events managed by dokan.
     *
     * @since 3.5.0
     *
     * @param bool $is_formatted Pass it true to get formatted events list to send Razorpay.
     *
     * @return array
     */
    public static function get_webhook_events( $is_formatted = false ) {
        $events = apply_filters(
            'dokan_razorpay_supported_webhook_events', []
        );

        if ( ! $is_formatted ) {
            return $events;
        }

        $formatted_events = [];
        foreach ( $events as $key => $name ) {
            $formatted_events[ $key ] = 1;
        }

        return $formatted_events;
    }

    /**
     * Get human readable error message.
     *
     * @since 3.5.0
     *
     * @param WP_Error $error
     * @return mixed|string
     */
    public static function get_error_message( WP_Error $error ) {
        $error_message = $error->get_error_message();

        if ( is_array( $error_message ) && isset( $error_message['details']['description'] ) ) {
            $error_message = $error_message['details']['description'];
        } elseif ( is_array( $error_message ) && isset( $error_message['message'] ) ) {
            $error_message = $error_message['message'];
        }
        return $error_message;
    }

    /**
     * Include module template.
     *
     * @since 3.5.0
     *
     * @param string $name template file name
     * @param array  $args
     *
     * @return void
     */
    public static function get_template( $name, $args = [] ) {
        $name = sanitize_text_field( wp_unslash( $name ) );
        dokan_get_template( "$name.php", $args, '', trailingslashit( DOKAN_RAZORPAY_TEMPLATE_PATH ) );
    }

    /**
     * Calculate the processing fee for a single vendor for an order.
     *
     * @since 3.5.0
     *
     * @param float     $order_processing_fee
     * @param \WC_ORDER $suborder
     * @param \WC_ORDER $order
     *
     * @return float
     */
    public static function calculate_processing_fee_for_suborder( $order_processing_fee, $suborder, $order ) {
        $razorpay_fee_for_vendor = ( $order_processing_fee * $suborder->get_total() ) / $order->get_total();
        return number_format( $razorpay_fee_for_vendor, 10 );
    }

    /**
     * Format balance to insert in database.
     *
     * @since 3.5.0
     *
     * @param int $amount
     *
     * @return string
     */
    public static function format_balance( $amount ) {
        return wc_format_decimal( $amount / 100, 2 );
    }

    /**
     * Format Order Total Amount value for processing.
     *
     * @see for https://razorpay.com/docs/api/route/#request-parameters-2
     *
     * @since 3.5.0
     *
     * @param int|float $amount
     *
     * @return int
     */
    public static function format_amount( $amount ) {
        $amount = floatval( $amount ) * 100;
        return absint( (string) $amount ); // must be converted to int
    }

    /**
     * Returns redirect URL post payment processing.
     *
     * @since 3.5.0
     *
     * @param int    $order_id
     * @param string $razorpay_order_id
     * @param bool   $is_cancelled
     *
     * @return string redirect URL
     */
    public static function get_redirect_url( $order_id, $razorpay_order_id, $is_cancelled = false ) {
        $order = wc_get_order( $order_id );

        $query = [
            'wc-api'            => static::get_gateway_id(),
            'order_key'         => $order->get_order_key(),
            'razorpay_order_id' => $razorpay_order_id,
        ];

        if ( $is_cancelled ) {
            $query['cancel_order'] = true;
        }

        return add_query_arg( $query, trailingslashit( get_home_url() ) );
    }

    /**
     * Get Customer Information.
     *
     * @since 3.5.0
     *
     * @param WC_Order $order
     *
     * @return array $args
     */
    public static function get_customer_info( $order ) {
        return [
            'name'    => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'email'   => $order->get_billing_email(),
            'contact' => $order->get_billing_phone(),
        ];
    }

    /**
     * Initialization of Razorpay API Instance.
     *
     * @since 3.5.0
     *
     * @return \Razorpay\Api\Api
     */
    public static function init_razorpay_api() {
        return new \Razorpay\Api\Api( static::get_key_id(), static::get_key_secret() );
    }

    /**
     * Redirect User to Return URL Page.
     *
     * @since 3.5.0
     *
     * @param WC_Order $order
     *
     * @return void
     */
    public static function redirect_user_to_return_url( $order ) {
        $redirected_url = dokan_pro()->module->razorpay->gateway_razorpay->get_return_url( $order );

        wp_safe_redirect( $redirected_url );
        exit;
    }

    /**
     * Get Supported Currencies for Dokan Razorpay.
     *
     * @see https://razorpay.com/docs/payments/payments/international-payments/
     * @see https://razorpay.com/docs/api/route/#transfer-entity
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_supported_currencies() {
        $supported_currencies = [
            'INR' => __( 'Indian rupee', 'dokan' ),
        ];

        return apply_filters( 'dokan_razorpay_supported_currencies', $supported_currencies );
    }

    /**
     * Get Razorpay business types for creating Linked account.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_razorpay_business_types() {
        return [
            'private_limited'       => __( 'Private Limited', 'dokan' ),
            'proprietorship'        => __( 'Proprietorship', 'dokan' ),
            'partnership'           => __( 'Partnership', 'dokan' ),
            'individual'            => __( 'Individual', 'dokan' ),
            'public_limited'        => __( 'Public Limited', 'dokan' ),
            'llp'                   => __( 'LLP', 'dokan' ),
            'trust'                 => __( 'Trust', 'dokan' ),
            'society'               => __( 'Society', 'dokan' ),
            'ngo'                   => __( 'NGO', 'dokan' ),
            'not_yet_registered'    => __( 'Not Yet Registered', 'dokan' ),
            'education_institution' => __( 'Education Institution', 'dokan' ),
            'other'                 => __( 'Other', 'dokan' ),
        ];
    }

    /**
     * Get Bank account types for creating Linked account.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_bank_account_types() {
        return [
            'Currnet' => __( 'Current', 'dokan' ),
            'Saving'  => __( 'Saving', 'dokan' ),
            'Other'   => __( 'Other', 'dokan' ),
        ];
    }

    /**
     * Check whether subscription module is enabled or not.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function has_vendor_subscription_module() {
        return function_exists( 'dokan_pro' ) && dokan_pro()->module->is_active( 'product_subscription' );
    }

    /**
     * Check if the order is a subscription order.
     *
     * @since 3.5.0
     *
     * @param WC_Product|int $product
     *
     * @return bool
     **/
    public static function is_vendor_subscription_product( $product ) {
        if ( is_int( $product ) ) {
            $product = wc_get_product( $product );
        }

        if ( ! $product instanceof \WC_Product ) {
            return false;
        }

        if ( ! self::has_vendor_subscription_module() ) {
            return false;
        }

        if ( 'product_pack' === $product->get_type() ) {
            return true;
        }

        return false;
    }

    /**
     * Get payment setup navigation url based on page.
     *
     * It'll handle vendor setup page link and vendor dashboard settings page link.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_payment_setup_navigation_url() {
        return wp_get_referer();
    }

    /**
     * Get disbursement balance date.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_balance_date() {
        $interval_days = 0;
        $disburse_mode = self::get_disbursement_mode();

        switch ( $disburse_mode ) {
            case 'DELAYED':
                // Add one day extra with the delay period to consider the processing
                $interval_days = (int) self::get_disbursement_delay_period() + 1;
                break;

            case 'ON_ORDER_COMPLETE':
                // Let's make a big assumption to avoid any risk
                $interval_days = 60;
                break;

            default:
                $interval_days = 0;
        }

        return empty( $interval_days ) ? dokan_current_datetime()->format( 'Y-m-d h:i:s' ) : dokan_current_datetime()->modify( "+ {$interval_days} days" )->format( 'Y-m-d h:i:s' );
    }
}
