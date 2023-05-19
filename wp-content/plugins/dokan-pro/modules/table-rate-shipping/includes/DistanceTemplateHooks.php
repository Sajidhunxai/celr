<?php

namespace WeDevs\DokanPro\Modules\TableRate;

/**
 * Table Rate Shipping Template Class
 */

class DistanceTemplateHooks {

    /**
     * Constructor for the Table Rate
     * Shipping Template class
     *
     * @since 3.4.2
     */
    public function __construct() {
        add_action( 'dokan_render_settings_content', [ $this, 'load_settings_content' ], 35 );
        add_action( 'dokan_distance_rate_shipping_setting_start', [ $this, 'load_settings_form_header' ], 10 );
        add_action( 'dokan_distance_rate_shipping_setting_form', [ $this, 'load_settings_form_general' ], 11 );
        add_action( 'dokan_distance_rate_shipping_setting_form', [ $this, 'load_settings_table_rate' ], 12 );
    }

    /**
     * Load Settings Content
     *
     * @since 3.4.2
     *
     * @param  array $query_vars
     *
     * @return void
     */
    public function load_settings_content( $query_vars ) {
        if ( ! isset( $query_vars['settings'] ) ) {
            return;
        }

        if ( 'distance-rate-shipping' !== $query_vars['settings'] ) {
            return;
        }

        if (
            ! current_user_can( 'dokan_view_store_shipping_menu' ) ||
            ! dokan_pro()->module->table_rate_shipping->get_instance()
        ) {
            return dokan_get_template_part(
                'global/dokan-error', '', array(
                    'deleted' => false,
                    'message' => __( 'You have no permission to view this page', 'dokan' ),
                )
            );
        }

        if ( 'disabled' === get_option( 'woocommerce_ship_to_countries' ) ) {
            return dokan_get_template_part(
                'global/dokan-error', '', array(
                    'deleted' => false,
                    'message' => __( 'Shipping functionality is currentlly disabled by site owner', 'dokan' ),
                )
            );
        }

        $dokan_appearance = get_option( 'dokan_appearance', [] );

        if ( empty( $dokan_appearance['gmap_api_key'] ) ) {
            return dokan_get_template_part(
                'global/dokan-error', '', array(
                    'deleted' => false,
                    'message' => __( 'Distance rate shipping requires Google map API key.', 'dokan' ),
                )
            );
        }

        dokan_get_template_part(
            'distance-rate/settings', '', [
                'is_table_rate_shipping' => true,
            ]
        );
    }

    /**
     * Load Settings Content
     *
     * @since 3.4.2
     *
     * @return void
     */
    public function load_settings_form_header() {
        if ( ! dokan_pro()->module->table_rate_shipping->get_instance() ) {
            return;
        }

        $this->get_messages();

        dokan_get_template_part(
            'header-settings', '', [
                'is_table_rate_shipping' => true,
                'zone_id'                => dokan_pro()->module->table_rate_shipping->get_zone(),
            ]
        );
    }

    /**
     * Render Update Message
     *
     * @return void
     */
    public function get_messages() {
        if ( isset( $_GET['message'] ) && 'distance_rate_saved' === $_GET['message'] ) { // phpcs:ignore
            dokan_get_template_part(
                'global/dokan-message',
                '',
                array(
                    'message' => __( 'Distance rates has been saved successfully!', 'dokan' ),
                )
            );
        }
    }

    /**
     * Load Settings Content
     *
     * @since 3.4.2
     *
     * @return void
     */
    public function load_settings_form_general() {
        $instance_id = dokan_pro()->module->table_rate_shipping->get_instance();

        if ( ! $instance_id ) {
            return;
        }

        $method_info = dokan_pro()->module->table_rate_shipping->get_shipping_method( $instance_id );

        if ( empty( $method_info ) || ! isset( $method_info['settings'] ) ) {
            return;
        }

        $settings         = $method_info['settings'];
        $get_address      = $this->get_shipping_address_string( $settings, $instance_id );
        $dokan_appearance = get_option( 'dokan_appearance', [] );

        // Table rate main scripts load
        wp_enqueue_script( 'dokan-shipping-table-rate-rows' );

        dokan_get_template_part(
            'distance-rate/general-settings', '', [
                'is_table_rate_shipping'       => true,
                'title'                        => isset( $method_info['title'] ) ? $method_info['title'] : '',
                'tax_status'                   => isset( $settings['tax_status'] ) ? $settings['tax_status'] : '',
                'distance_rate_mode'           => isset( $settings['distance_rate_mode'] ) ? $settings['distance_rate_mode'] : '',
                'distance_rate_avoid'          => isset( $settings['distance_rate_avoid'] ) ? $settings['distance_rate_avoid'] : '',
                'distance_rate_unit'           => isset( $settings['distance_rate_unit'] ) ? $settings['distance_rate_unit'] : '',
                'distance_rate_show_distance'  => isset( $settings['distance_rate_show_distance'] ) ? $settings['distance_rate_show_distance'] : '',
                'distance_rate_show_duration'  => isset( $settings['distance_rate_show_duration'] ) ? $settings['distance_rate_show_duration'] : '',
                'distance_rate_address_1'      => isset( $settings['distance_rate_address_1'] ) ? $settings['distance_rate_address_1'] : '',
                'distance_rate_address_2'      => isset( $settings['distance_rate_address_2'] ) ? $settings['distance_rate_address_2'] : '',
                'distance_rate_city'           => isset( $settings['distance_rate_city'] ) ? $settings['distance_rate_city'] : '',
                'distance_rate_postal_code'    => isset( $settings['distance_rate_postal_code'] ) ? $settings['distance_rate_postal_code'] : '',
                'distance_rate_state_province' => isset( $settings['distance_rate_state_province'] ) ? $settings['distance_rate_state_province'] : '',
                'distance_rate_country'        => isset( $settings['distance_rate_country'] ) ? $settings['distance_rate_country'] : '',
                'get_address'                  => $get_address,
                'gmap_api_key'                 => $dokan_appearance['gmap_api_key'],
            ]
        );
    }

    /**
     * Get the shipping from address as string.
     *
     * @since 3.4.2
     *
     * @param array $settings
     * @param int   $instance_id
     *
     * @return string
     */
    public function get_shipping_address_string( $settings, $instance_id ) {
        $address = array();

        if ( ! empty( $settings['distance_rate_address_1'] ) ) {
            $address['address_1'] = $settings['distance_rate_address_1'];
        }

        if ( ! empty( $settings['distance_rate_address_2'] ) ) {
            $address['address_2'] = $settings['distance_rate_address_2'];
        }

        if ( ! empty( $settings['distance_rate_city'] ) ) {
            $address['city'] = $settings['distance_rate_city'];
        }

        if ( ! empty( $settings['distance_rate_postal_code'] ) ) {
            $address['postcode'] = $settings['distance_rate_postal_code'];
        }

        if ( ! empty( $settings['distance_rate_state_province'] ) ) {
            $address['state'] = $settings['distance_rate_state_province'];
        }

        if ( ! empty( $settings['distance_rate_country'] ) ) {
            $address['country'] = $settings['distance_rate_country'];
        }

        return implode( ', ', apply_filters( 'dokan_distance_rate_shipping_' . $instance_id . '_get_shipping_address_string', $address ) );
    }

    /**
     * Load Settings Content
     *
     * @since 3.4.2
     *
     * @return void
     */
    public function load_settings_table_rate() {
        $instance_id = dokan_pro()->module->table_rate_shipping->get_instance();

        if ( ! $instance_id ) {
            return;
        }

        $method_info      = dokan_pro()->module->table_rate_shipping->get_shipping_method( $instance_id );
        $shipping_rates   = dokan_pro()->module->table_rate_shipping->get_normalized_shipping_distance_rates( $instance_id );
        $normalized_rates = function_exists( 'wc_esc_json' ) ? wc_esc_json( wp_json_encode( $shipping_rates ) ) : _wp_specialchars( wp_json_encode( $shipping_rates ), ENT_QUOTES, 'UTF-8', true );

        dokan_get_template_part(
            'distance-rate/rows-settings', '', [
                'is_table_rate_shipping' => true,
                'instance_id'            => $instance_id,
                'normalized_rates'       => $normalized_rates,
            ]
        );
    }
}
