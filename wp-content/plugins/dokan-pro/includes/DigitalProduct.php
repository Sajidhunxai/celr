<?php

namespace WeDevs\DokanPro;

/**
 * Digital Porduct class
 *
 * @since 3.2.3
 *
 * @package dokan
 */
class DigitalProduct {

    /**
     * Load autometically when class initiate
     *
     * @since 3.2.3
     *
     * @uses actions
     * @uses filters
     */
    public function __construct() {
        $this->init_hooks();
    }

    /**
     * Init hooks and filters
     *
     * @return void
     */
    public function init_hooks() {
        add_filter( 'dokan_settings_general_site_options', [ $this, 'add_admin_setting_digital_mode' ], 9 );
        add_action( 'dokan_admin_setup_wizard_step_store_after', [ $this, 'admin_wizard_store_setup_field' ] );
        add_action( 'dokan_admin_setup_wizard_save_step_store', [ $this, 'after_admin_wizard_store_field_save' ] );
        add_filter( 'dokan_get_dashboard_settings_nav', [ $this, 'remove_shipping_settings_menu' ], 99 );
    }

    /**
     * Add vendor store options in general settings
     *
     * @since 3.2.3
     *
     * @param array $settings_fields
     *
     * @return array $settings_fields
     */
    public function add_admin_setting_digital_mode( $settings_fields ) {
        $settings_fields['global_digital_mode'] = array(
            'name'    => 'global_digital_mode',
            'label'   => __( 'Selling Product Types', 'dokan' ),
            'desc'    => __( 'Select a type for vendors what type of product they can sell only', 'dokan' ),
            'type'    => 'radio',
            'default' => 'sell_both',
            'tooltip' => __( 'Select the type of products vendor can sell.', 'dokan' ),
            'options' => apply_filters(
                'dokan_digital_product_types',
                [
                    'sell_physical' => __( 'Physical', 'dokan' ),
                    'sell_digital'  => __( 'Digital', 'dokan' ),
                    'sell_both'     => __( 'Both', 'dokan' ),
                ]
            ),
        );

        return $settings_fields;
    }

    /**
     * Get dokan selling product type
     *
     * @since 3.2.3
     *
     * @return string
     */
    public function get_selling_product_type() {
        return dokan_get_option( 'global_digital_mode', 'dokan_general', 'sell_both' );
    }

    /**
     * Add store digitial product option template
     *
     * @since 3.2.3
     *
     * @return void
     */
    public function admin_wizard_store_setup_field( $wizard ) {
        $args = array(
            'pro'          => true,
            'label'        => __( 'Selling Product Types', 'dokan' ),
            'digital_mode' => $this->get_selling_product_type(),
            'plans' => apply_filters(
                'dokan_digital_product_types',
                [
                    'sell_physical' => __( 'Physical', 'dokan' ),
                    'sell_digital'  => __( 'Digital', 'dokan' ),
                    'sell_both'     => __( 'Both', 'dokan' ),
                ]
            ),
        );

        dokan_get_template_part( 'settings/seller-wizard-digital-product-settings', '', $args );
    }

    /**
     * Set store categories after wizard settings is saved
     *
     * @since 3.2.3
     *
     * @param \WeDevs\Dokan\Vendor\SetupWizard $wizard
     *
     * @return void
     */
    public function after_admin_wizard_store_field_save( $wizard ) {
        check_admin_referer( 'dokan-setup' );

        $get_postdata  = wp_unslash( $_POST ); // phpcs:ignore
        $dokan_general = get_option( 'dokan_general', array() );

        $dokan_general['global_digital_mode'] = ! empty( $get_postdata['dokan_digital_product'] ) ? sanitize_text_field( $get_postdata['dokan_digital_product'] ) : 'sell_both';

        update_option( 'dokan_general', $dokan_general );
    }

    /**
     * Remove shipping menu when digital mode only
     *
     * @since 3.2.3
     *
     * @param  array $sub_settins
     *
     * @return array
     */
    public function remove_shipping_settings_menu( $sub_settins ) {
        if ( 'sell_digital' === $this->get_selling_product_type() ) {
            unset( $sub_settins['shipping'] );
        }

        return $sub_settins;
    }
}
