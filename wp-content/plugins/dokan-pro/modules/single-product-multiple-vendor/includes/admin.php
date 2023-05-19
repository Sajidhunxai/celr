<?php

/**
* Admin class
*
* @package Dokan Pro
*/
class Dokan_SPMV_Admin {

    /**
     * Load automatically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        // settings section
        add_filter( 'dokan_settings_sections', array( $this, 'add_new_section_admin_panel' ) );
        add_filter( 'dokan_settings_fields', array( $this, 'add_new_setting_field_admin_panel' ), 12, 1 );
    }

    /**
     * Add new Section in admin dokan settings
     *
     * @param array  $sections
     *
     * @return array
     */
    public function add_new_section_admin_panel( $sections ) {
        $sections['dokan_spmv'] = [
            'id'                   => 'dokan_spmv',
            'title'                => __( 'Single Product MultiVendor', 'dokan' ),
            'icon_url'             => DOKAN_SPMV_ASSETS_DIR . '/images/spmv.svg',
            'description'          => __( 'Single Product MultiVendor Settings', 'dokan' ),
            'document_link'        => 'https://wedevs.com/docs/dokan/modules/single-product-multiple-vendor/',
            'settings_title'       => __( 'SPMV Settings', 'dokan' ),
            'settings_description' => __( 'You can configure your site to allow vendors to sell other vendor\'s products with desired customizations.', 'dokan' ),
        ];

        return $sections;
    }

    /**
     * Add new Settings field in admin settings area
     *
     * @param array  $settings_fields
     *
     * @return array
     */
    public function add_new_setting_field_admin_panel( $settings_fields ) {
        $settings_fields['dokan_spmv'] = array(
            'enable_pricing' => [
                'name'    => 'enable_pricing',
                'label'   => __( 'Enable Single Product Multiple Vendor', 'dokan' ),
                'desc'    => __( 'Enable Single Product Multiple Vendor functionality', 'dokan' ),
                'type'    => 'switcher',
                'tooltip' => __( 'Allow your vendors to sell other vendor\'s product.', 'dokan' ),
            ],

            'sell_item_btn' => [
                'name'    => 'sell_item_btn',
                'label'   => __( 'Sell Item Button Text', 'dokan' ),
                'desc'    => __( 'Change your sell this item button text', 'dokan' ),
                'type'    => 'text',
                'default' => __( 'Sell This Item', 'dokan' ),
            ],

            'available_vendor_list_title' => [
                'name'    => 'available_vendor_list_title',
                'label'   => __( 'Available Vendor Display Area Title', 'dokan' ),
                'desc'    => __( 'Set your heading for available vendor section in single product page', 'dokan' ),
                'type'    => 'text',
                'default' => __( 'Other Available Vendor', 'dokan' ),
            ],

            'available_vendor_list_position' => [
                'name'    => 'available_vendor_list_position',
                'label'   => __( 'Available Vendor Section Display Position', 'dokan' ),
                'desc'    => __( 'Set your displaying position for displaying available vendor section in single product page', 'dokan' ),
                'type'    => 'select',
                'options' => array(
                    'below_tabs'  => __( 'Above Single Product Tabs', 'dokan' ),
                    'inside_tabs' => __( 'Display inside Product Tabs', 'dokan' ),
                    'after_tabs'  => __( 'After Single Product Tabs', 'dokan' ),
                ),
                'default' => 'below_tabs',
                'tooltip' => __( 'Select where to display available vendor section in single product page.', 'dokan' ),
            ],

            'show_order' => [
                'name'    => 'show_order',
                'label'   => __( 'Show SPMV Products', 'dokan' ),
                'desc'    => __( 'Select option for shown products under SPMV concept. "Show all products" will show all duplicate products.', 'dokan' ),
                'type'    => 'select',
                'options' => wp_list_pluck( dokan_spmv_get_show_order_options(), 'label', 'name' ),
                'default' => 'show_all',
            ],
        );

        return $settings_fields;
    }

}
