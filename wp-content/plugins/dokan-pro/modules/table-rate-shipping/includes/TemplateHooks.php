<?php

namespace WeDevs\DokanPro\Modules\TableRate;

/**
 * Table Rate Shipping Template Class
 */

class TemplateHooks {

    /**
     * Constructor for the Table Rate
     * Shipping Template class
     *
     * @since 3.4.0
     */
    public function __construct() {
        add_action( 'dokan_render_settings_content', [ $this, 'load_settings_content' ], 35 );
        add_filter( 'dokan_dashboard_nav_active', [ $this, 'filter_nav_active' ], 10, 1 );
        add_filter( 'dokan_dashboard_settings_helper_text', [ $this, 'load_settings_helper_text' ], 10, 2 );
        add_filter( 'dokan_dashboard_settings_heading_title', [ $this, 'load_settings_header' ], 10, 2 );
        add_action( 'dokan_table_rate_shipping_setting_start', [ $this, 'load_settings_form_header' ], 10 );
        add_action( 'dokan_table_rate_shipping_setting_form', [ $this, 'load_settings_form_general' ], 11 );
        add_action( 'dokan_table_rate_shipping_setting_form', [ $this, 'load_settings_table_rate' ], 12 );
    }

    /**
     * Load Settings Content
     *
     * @since 3.4.0
     *
     * @param  array $query_vars
     *
     * @return void
     */
    public function load_settings_content( $query_vars ) {
        if ( ! isset( $query_vars['settings'] ) || 'table-rate-shipping' !== $query_vars['settings'] ) {
            return;
        }

        if ( ! current_user_can( 'dokan_view_store_shipping_menu' ) || ! dokan_pro()->module->table_rate_shipping->get_instance() ) {
            dokan_get_template_part(
                'global/dokan-error', '', array(
                    'deleted' => false,
                    'message' => __( 'You have no permission to view this page', 'dokan' ),
                )
            );
        } else {
            $disable_woo_shipping = get_option( 'woocommerce_ship_to_countries' );

            if ( 'disabled' === $disable_woo_shipping ) {
                dokan_get_template_part(
                    'global/dokan-error', '', array(
                        'deleted' => false,
                        'message' => __( 'Shipping functionality is currentlly disabled by site owner', 'dokan' ),
                    )
                );
            } else {
                dokan_get_template_part(
                    'settings', '', [
                        'is_table_rate_shipping' => true,
                    ]
                );
            }
        }
    }

    /**
     * Finlter nva active for table rate shipping
     *
     * @since 3.4.0
     *
     * @param string $active_menu
     *
     * @return string $active_menu
     */
    public function filter_nav_active( $active_menu ) {
        global $wp;

        if ( ! isset( $wp->query_vars['settings'] ) || ( ! in_array( $wp->query_vars['settings'], [ 'table-rate-shipping', 'distance-rate-shipping' ], true )  ) ) {
            return $active_menu;
        }

        if ( preg_match( '/table-rate-shipping|distance-rate-shipping/i', $active_menu ) ) {
            $menu = explode( '/', $active_menu );
            return $menu[0] . '/shipping';
        }

        return $active_menu;
    }

    /**
     * Load Settings page helper
     *
     * @since 3.4.0
     *
     * @param  string $help_text
     * @param  array $query_vars
     *
     * @return string
     */
    public function load_settings_helper_text( $help_text, $query_vars ) {
        $dokan_shipping_option = get_option( 'woocommerce_dokan_product_shipping_settings' );
        $enable_shipping       = ( isset( $dokan_shipping_option['enabled'] ) ) ? $dokan_shipping_option['enabled'] : 'yes';

        if ( 'yes' === $enable_shipping && ( in_array( $query_vars, [ 'table-rate-shipping', 'distance-rate-shipping' ], true ) ) ) {
            $help_text = sprintf(
                '<p>%s</p>',
                __( 'A shipping zone is a geographic region where a certain set of shipping methods are offered. We will match a customer to a single zone using their shipping address and present the shipping methods within that zone to them.', 'dokan' ),
                __( 'If you want to use the previous shipping system then', 'dokan' ),
                esc_url( dokan_get_navigation_url( 'settings/regular-shipping' ) ),
                __( 'Click Here', 'dokan' )
            );
        }

        return $help_text;
    }

    /**
     * Load Settings Header
     *
     * @since 3.4.0
     *
     * @param string $header
     * @param array $query_vars
     *
     * @return string
     */
    public function load_settings_header( $header, $query_vars ) {
        if ( in_array( $query_vars, [ 'table-rate-shipping', 'distance-rate-shipping' ], true ) ) {
            $settings_url = dokan_get_navigation_url( 'settings/shipping' ) . '#/settings';
            $header       = sprintf( '%s <span style="position:absolute; right:0px;"><a href="%s" class="dokan-btn dokan-btn-default"><i class="fas fa-cog"></i> %s</a></span>', __( 'Shipping Settings', 'dokan' ), $settings_url, __( 'Click here to add Shipping Policies', 'dokan' ) );
        }

        return $header;
    }

    /**
     * Load Settings Content
     *
     * @since 3.4.0
     *
     * @return void
     */
    public function load_settings_form_header() {
        $zone_id     = dokan_pro()->module->table_rate_shipping->get_zone();
        $instance_id = dokan_pro()->module->table_rate_shipping->get_instance();

        if ( ! $instance_id ) {
            return;
        }

        $this->get_messages();

        dokan_get_template_part(
            'header-settings', '', [
                'is_table_rate_shipping' => true,
                'zone_id'                => $zone_id,
            ]
        );
    }

    /**
     * Render Update Message
     *
     * @return void
     */
    public function get_messages() {
        if ( isset( $_GET['message'] ) && 'table_rate_saved' === $_GET['message'] ) { // phpcs:ignore
            dokan_get_template_part(
                'global/dokan-message',
                '',
                array(
                    'message' => __(
                        'Table rates has been saved successfully!', 'dokan'
                    ),
                )
            );
        }
    }

    /**
     * Load Settings Content
     *
     * @since 3.4.0
     *
     * @return void
     */
    public function load_settings_form_general() {
        $instance_id = dokan_pro()->module->table_rate_shipping->get_instance();

        if ( ! $instance_id ) {
            return;
        }

        $method_info = dokan_pro()->module->table_rate_shipping->get_shipping_method( $instance_id );

        if ( empty( $method_info ) ) {
            return;
        }

        $settings = $method_info['settings'];

        // Table rate main scripts load
        wp_enqueue_script( 'dokan-shipping-table-rate-rows' );

        dokan_get_template_part(
            'general-settings', '', [
                'is_table_rate_shipping' => true,
                'title'                  => isset( $method_info['title'] ) ? $method_info['title'] : '',
                'tax_status'             => isset( $settings['tax_status'] ) ? $settings['tax_status'] : '',
                'prices_include_tax'     => isset( $settings['prices_include_tax'] ) ? $settings['prices_include_tax'] : get_option( 'woocommerce_prices_include_tax' ),
                'order_handling_fee'     => isset( $settings['order_handling_fee'] ) ? $settings['order_handling_fee'] : '',
                'max_shipping_cost'      => isset( $settings['max_shipping_cost'] ) ? $settings['max_shipping_cost'] : '',
                'calculation_type'       => isset( $settings['calculation_type'] ) ? $settings['calculation_type'] : '',
                'handling_fee'           => isset( $settings['handling_fee'] ) ? $settings['handling_fee'] : '',
                'min_cost'               => isset( $settings['min_cost'] ) ? $settings['min_cost'] : '',
                'max_cost'               => isset( $settings['max_cost'] ) ? $settings['max_cost'] : '',
            ]
        );
    }

    /**
     * Load Settings Content
     *
     * @since 3.4.0
     *
     * @return void
     */
    public function load_settings_table_rate() {
        $instance_id = dokan_pro()->module->table_rate_shipping->get_instance();

        if ( ! $instance_id ) {
            return;
        }

        $method_info      = dokan_pro()->module->table_rate_shipping->get_shipping_method( $instance_id );
        $class_priorities = isset( $method_info['settings']['classes_priorities'] ) ? $method_info['settings']['classes_priorities'] : array();
        $default_priority = isset( $method_info['settings']['default_priority'] ) ? $method_info['settings']['default_priority'] : 10;

        if ( function_exists( 'WCML\functions\getSitePress' ) ) { // wpml compatibility, will move this code to dokan wpml plugin in future
            $sitepress = \WCML\functions\getSitePress();
            $sitepress->switch_lang( $sitepress->get_default_language() );
            $shipping_classes = get_terms( 'product_shipping_class', 'hide_empty=0' );
            $classes          = WC()->shipping->get_shipping_classes(); // for shipping priority
            $sitepress->switch_lang();
        } else {
            $shipping_classes = get_terms( 'product_shipping_class', 'hide_empty=0' );
            $classes          = WC()->shipping->get_shipping_classes(); // for shipping priority
        }

        $shipping_rates   = dokan_pro()->module->table_rate_shipping->get_normalized_shipping_rates( $instance_id );
        $normalized_rates = function_exists( 'wc_esc_json' ) ? wc_esc_json( wp_json_encode( $shipping_rates ) ) : _wp_specialchars( wp_json_encode( $shipping_rates ), ENT_QUOTES, 'UTF-8', true );

        dokan_get_template_part(
            'rows-classes-settings', '', [
                'is_table_rate_shipping' => true,
                'instance_id'            => $instance_id,
                'classes'                => $classes,
                'default_priority'       => $default_priority,
                'class_priorities'       => $class_priorities,
                'shipping_classes'       => $shipping_classes,
                'normalized_rates'       => $normalized_rates,
            ]
        );
    }
}
