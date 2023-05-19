<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Support;

/**
 * Class for handling all settings of Dokan MangoPay
 *
 * @since 3.5.0
 */
class Settings {

    /**
     * Retrieves all mangopay settings
     *
     * @since 3.5.0
     *
     * @param string $key
     *
     * @return mixed
     */
    public static function get( $key = '_all' ) {
        if ( empty( $key ) ) {
            return array();
        }

        $settings = get_option( 'woocommerce_' . Helper::get_gateway_id() . '_settings', array() );

        if ( '_all' === $key ) {
            return $settings;
        }

        if ( isset( $settings[ $key ] ) ) {
            return $settings[ $key ];
        }

        return '';
    }

    /**
     * Checks if sandbox is enabled
     *
     * @since 3.5.0
     *
     * @return boolean
     */
    public static function is_test_mode() {
        return 'yes' === self::get( 'sandbox_mode' );
    }

    /**
     * Retrieves mangopay client id
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_client_id() {
        $settings = self::get();
        $key      = isset( $settings['sandbox_mode'] ) && 'yes' !== $settings['sandbox_mode'] ? 'client_id' : 'sandbox_client_id';

        return ! empty( $settings[ $key ] ) ? $settings[ $key ] : '';
    }

    /**
     * Retrieves mangopay api key
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_api_key() {
        $settings = self::get();
        $key      = isset( $settings['sandbox_mode'] ) && 'yes' !== $settings['sandbox_mode'] ? 'api_key' : 'sandbox_api_key';

        return ! empty( $settings[ $key ] ) ? $settings[ $key ] : '';
    }

    /**
     * Retrieves webhook key
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_webhook_key() {
        return self::get( 'webhook_key' );
    }

    /**
     * Checks if card registration is enabled
     *
     * @since 3.5.0
     *
     * @return boolean
     */
    public static function is_saved_cards_enabled() {
        return 'yes' === self::get( 'saved_cards' );
    }

    /**
     * Checks if mangopay gateway is enabled
     *
     * @since 3.5.0
     *
     * @return boolean
     */
    public static function is_gateway_enabled() {
        return 'yes' === self::get( 'enabled' );
    }

    /**
     * Retrieves mangopay gateway title
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_gateway_title() {
        $title = self::get( 'title' );

        return ! empty( $title ) ? $title : __( 'MangoPay', 'dokan' );
    }

    /**
     * Retrieves mangopay gateway description
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_gateway_description() {
        $description = self::get( 'description' );

        return ! empty( $description ) ? $description : __( 'Pay via MangoPay', 'dokan' );
    }

    /**
     * Checkis if instant payout is enabled
     *
     * @since 3.5.0
     *
     * @return boolean
     */
    public static function is_instant_payout_enabled() {
        return 'yes' === self::get( 'instant_payout' );
    }

    /**
     * Retrieves payment disbursement mode
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_disbursement_mode() {
        return self::get( 'disburse_mode' );
    }

    /**
     * Retrieves payment disbursement delay period
     *
     * @since 3.5.0
     *
     * @return int
     */
    public static function get_disbursement_delay_period() {
        $delay_period = self::get( 'disbursement_delay_period' );
        return ! empty( $delay_period ) ? (int) $delay_period : 0;
    }

    /**
     * Retrieves selected credit cards for payment
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_selected_credit_cards() {
        $credit_cards = self::get( 'cards' );

        if ( empty( $credit_cards ) ) {
            return array();
        }

        return (array) $credit_cards;
    }

    /**
     * Retrieves selected direct pay methods
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_selected_direct_pay_methods() {
        $direct_methods = self::get( 'direct_pay' );

        if ( empty( $direct_methods ) ) {
            return array();
        }

        return (array) $direct_methods;
    }

    /**
     * Retrieves default vendor status
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_default_vendor_status() {
        return self::get( 'default_vendor_status' );
    }

    /**
     * Retrieves default business status
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_default_business_type() {
        return self::get( 'default_business_type' );
    }

    /**
     * Checks if display notice on vendor dashboard for
     * non-connected sellers is enabled.
     *
     * @since 3.5.0
     *
     * @return boolean
     */
    public static function is_display_notice_on_vendor_dashboard_enabled() {
        return 'yes' === self::get( 'notice_on_vendor_dashboard' );
    }

    /**
     * Checks if send announcement to non-connected sellers is enabled
     *
     * @since 3.5.0
     *
     * @return boolean
     */
    public static function is_send_announcement_to_sellers_enabled() {
        return 'yes' === self::get( 'announcement_to_sellers' );
    }

    /**
     * Get interval period for sending announcement
     *
     * @since 3.5.0
     *
     * @return int
     */
    public static function get_announcement_interval() {
        $interval = self::get( 'notice_interval' );
        return empty( $interval ) ? 7 : (int) $interval;
    }
}
