<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Support;

// Exit if called directly
defined( 'ABSPATH' ) || exit;

/**
 * Class for handling all settings of Stripe Express
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Support
 */
class Settings {

    /**
     * Retrieves option key for settings.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function key() {
        return 'woocommerce_' . Helper::get_gateway_id() . '_settings';
    }

    /**
     * Retrieves all stripe settings
     *
     * @since 3.6.1
     *
     * @param string $key
     *
     * @return mixed
     */
    public static function get( $key = '_all' ) {
        if ( empty( $key ) ) {
            return [];
        }

        $settings = get_option( self::key(), [] );

        if ( '_all' === $key ) {
            return $settings;
        }

        if ( isset( $settings[ $key ] ) ) {
            return $settings[ $key ];
        }

        return '';
    }

    /**
     * Updates stripe settings.
     *
     * @since 3.6.1
     *
     * @param array $settings
     *
     * @return boolean
     */
    public static function update( $settings ) {
        return update_option( self::key(), (array) $settings );
    }

    /**
     * Checks if test mode is enabled
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public static function is_test_mode() {
        return 'yes' === self::get( 'testmode' );
    }

    /**
     * Checks if debugging is enabled.
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public static function is_debug_mode() {
        return 'yes' === self::get( 'debug' );
    }

    /**
     * Retrieves Stripe publishable key.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function get_publishable_key() {
        $settings = self::get();
        $key      = isset( $settings['testmode'] ) && 'yes' !== $settings['testmode'] ? 'publishable_key' : 'test_publishable_key';

        return ! empty( $settings[ $key ] ) ? $settings[ $key ] : '';
    }

    /**
     * Retrieves Stripe secret key.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function get_secret_key() {
        $settings = self::get();
        $key      = isset( $settings['testmode'] ) && 'yes' !== $settings['testmode'] ? 'secret_key' : 'test_secret_key';

        return ! empty( $settings[ $key ] ) ? $settings[ $key ] : '';
    }

    /**
     * Retrieves webhook key
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function get_webhook_secret() {
        $settings = self::get();
        $key      = isset( $settings['testmode'] ) && 'yes' !== $settings['testmode'] ? 'webhook_key' : 'test_webhook_key';

        return ! empty( $settings[ $key ] ) ? $settings[ $key ] : '';
    }

    /**
     * Retrieves enabled payment methods.
     *
     * @since 3.6.1
     *
     * @return string[]
     */
    public static function get_enabled_payment_methods() {
        $enabled_methods = self::get( 'enabled_payment_methods' );
        return empty( $enabled_methods ) ? [ 'card' ] : (array) $enabled_methods;
    }

    /**
     * Checks if saved card is enabled
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public static function is_saved_cards_enabled() {
        return 'yes' === self::get( 'saved_cards' );
    }

    /**
     * Checks if gateway is enabled
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public static function is_gateway_enabled() {
        return 'yes' === self::get( 'enabled' );
    }

    /**
     * Retrieves gateway title for Stripe Express
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function get_gateway_title() {
        $title = self::get( 'title' );

        return ! empty( $title ) ? $title : __( 'Stripe Express', 'dokan' );
    }

    /**
     * Retrieves gateway description
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function get_gateway_description() {
        $description = self::get( 'description' );

        return ! empty( $description ) ? $description : __( 'Pay via Stripe Express', 'dokan' );
    }

    /**
     * Retrieves default platform country.
     *
     * @since 3.7.17
     *
     * @return string[] A list of two-letter ISO codes of restricted countries.
     */
    public static function get_restricted_countries() {
        return (array) self::get( 'restricted_countries' );
    }

    /**
     * Checks if cross border transfer is enabled
     *
     * @since 3.7.17
     *
     * @return boolean
     */
    public static function is_cross_border_transfer_enabled() {
        return 'yes' === self::get( 'cross_border_transfer' );
    }

    /**
     * Checks if sellers pay processing fees
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public static function sellers_pay_processing_fees() {
        return 'yes' === self::get( 'sellers_pay_processing_fee' );
    }

    /**
     * Retrieves payment disbursement mode
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function get_disbursement_mode() {
        return self::get( 'disburse_mode' );
    }

    /**
     * Retrieves payment disbursement delay period
     *
     * @since 3.6.1
     *
     * @return int
     */
    public static function get_disbursement_delay_period() {
        $delay_period = self::get( 'disbursement_delay_period' );
        return ! empty( $delay_period ) ? (int) $delay_period : 0;
    }

    /**
     * Checks if display notice on vendor dashboard for
     * non-connected sellers is enabled.
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public static function is_display_notice_on_vendor_dashboard_enabled() {
        return 'yes' === self::get( 'notice_on_vendor_dashboard' );
    }

    /**
     * Checks if send announcement to non-connected sellers is enabled
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public static function is_send_announcement_to_sellers_enabled() {
        return 'yes' === self::get( 'announcement_to_sellers' );
    }

    /**
     * Get interval period for sending announcement
     *
     * @since 3.6.1
     *
     * @return int
     */
    public static function get_announcement_interval() {
        $interval = self::get( 'notice_interval' );
        return empty( $interval ) ? 7 : (int) $interval;
    }

    /**
     * Checks if capture is enabled on checkout.
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public static function is_manual_capture_enabled() {
        return 'yes' === self::get( 'capture' );
    }

    /**
     * Retrieves statement descriptor for customer bank statement.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function get_statement_descriptor() {
        $descriptor = self::get( 'statement_descriptor' );
        if ( ! empty( $descriptor ) ) {
            return Helper::clean_statement_descriptor( $descriptor );
        }

        return '';
    }
}
