<?php
namespace WeDevs\DokanPro\Modules\TableRate;

use WeDevs\Dokan\Cache;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

/**
 * Admin notices class
 *
 * @since 3.7.21
 */
class Notices {
    /**
     * Class constructor
     *
     * @since 3.7.21
     */
    public function __construct() {
        add_action( 'dokan_admin_notices', [ $this, 'admin_notices' ] );
        add_filter( 'dokan_admin_notices', [ $this, 'maybe_display_api_key_error_message' ], 20, 1 );
        add_action( 'wp_ajax_dokan_distance_rate_shipping_api_key_error_notice', [ $this, 'close_api_key_error_notice_ajax' ] );

        add_action( 'dokan_before_saving_settings', [ $this, 'clear_notice_cache' ], 20, 3 );
    }

    /**
     * Display API key error message
     *
     * @since 3.7.21
     *
     * @param array $notices
     *
     * @return array
     */
    public function maybe_display_api_key_error_message( $notices ) {
        // if transient is set, return from here
        if ( false !== Cache::get_transient( 'distance_rate_shipping_api_key_error_notice' ) ) {
            return $notices;
        }

        $source = dokan_get_option( 'map_api_source', 'dokan_appearance', 'google_maps' );
        if ( 'google_maps' !== $source ) {
            return $notices;
        }

        // check if API key is set
        $gmap_api_key = trim( dokan_get_option( 'gmap_api_key', 'dokan_appearance', '' ) );
        if ( empty( $gmap_api_key ) ) {
            return $notices;
        }

        $address_1 = 'R9PG+W7 Dhaka';
        $address_2 = 'R9H7+HF Dhaka';
        // Get distance matrix api
        $api      = new DokanGoogleDistanceMatrixAPI( $gmap_api_key, false );
        $distance = $api->get_distance(
            $address_1,
            $address_2,
            false
        );

        if ( isset( $distance->status ) && 'OK' === $distance->status ) {
            Cache::set_transient( 'distance_rate_shipping_api_key_error_notice', true, '', 10 * DAY_IN_SECONDS );

            return $notices;
        }

        $message = sprintf(
            '<strong>%s:</strong> %s, <strong>%s:</strong> %s',
            __( 'Error Code', 'dokan' ),
            $distance->status,
            __( 'Error Message', 'dokan' ),
            $distance->error_message );

        $notices[] = [
            'type'              => 'warning',
            'title'             => __( 'Dokan Distance Rate Shipping API Error!', 'dokan' ),
            'description'       => $message,
            'show_close_button' => true,
            'priority'          => 10,
            'ajax_data'         => [
                'action'          => 'dokan_distance_rate_shipping_api_key_error_notice',
                'opt_in_security' => wp_create_nonce( 'dokan_distance_rate_shipping_api_key_error_notice' ),
            ],
            'actions'           => [
                [
                    'type'   => 'primary',
                    'text'   => __( 'Enable Distance Matrix API ', 'dokan' ),
                    'action' => 'https://console.cloud.google.com/apis/library/distance-matrix-backend.googleapis.com?pli=1&project=vaulted-splice-323312',
                    'target' => '_balnk',
                ],
            ],
        ];

        return $notices;
    }

    /**
     * Admin Notice ajax action
     *
     * @since 3.7.21
     *
     * @return void
     */
    public function close_api_key_error_notice_ajax() {
        check_ajax_referer( 'dokan_distance_rate_shipping_api_key_error_notice', 'opt_in_security' );
        Cache::set_transient( 'distance_rate_shipping_api_key_error_notice', true, '', 10 * DAY_IN_SECONDS );
        wp_send_json_success();
    }

    /**
     * Show admin notices
     *
     * @since 3.4.2
     * @since 3.7.21 moved this method from table-rate-shipping/module.php file
     *
     * @return void
     */
    public function admin_notices( $notices ) {
        $dokan_appearance = get_option( 'dokan_appearance', [] );

        if ( ! empty( $dokan_appearance['gmap_api_key'] ) ) {
            return $notices;
        }

        $notices[] = [
            'type'        => 'alert',
            'title'       => __( 'Dokan Table Rate Shipping module is almost ready!', 'dokan' ),
            // translators: %1$s: Distance rate label, %2$s: Google map api label, %3$s: Setting url
            'description' => sprintf( __( '%1$s shipping requires %2$s key. Please set your API Key in %3$s.', 'dokan' ), 'Dokan <strong>Distance Rate</strong>', '<strong>Google Map API</strong>', '<strong>Dokan Admin Settings > Appearance</strong>' ),
            'priority'    => 10,
            'actions'     => [
                [
                    'type'   => 'primary',
                    'text'   => __( 'Go to Settings', 'dokan' ),
                    'action'  => add_query_arg( array( 'page' => 'dokan#/settings' ), admin_url( 'admin.php' ) ),
                ],
            ],
        ];

        return $notices;
    }

    /**
     * Clear notice cache
     *
     * @since 3.7.21
     *
     * @param string $option_name
     * @param array $option_value
     * @param array $old_options
     *
     * @return void
     */
    public function clear_notice_cache( $option_name, $option_value, $old_options ) {
        if ( 'dokan_appearance' !== $option_name ) {
            return;
        }

        if ( empty( $option_value['gmap_api_key'] ) || empty( $old_options['gmap_api_key'] ) || $option_value['gmap_api_key'] !== $old_options['gmap_api_key'] ) {
            Cache::delete_transient( 'distance_rate_shipping_api_key_error_notice' );
        }
    }
}
