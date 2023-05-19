<?php

/**
* Admin class
*/
class Dokan_RMA_Admin {

    /**
     * Load automatically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_filter( 'dokan_settings_sections', array( $this, 'load_settings_section' ), 20 );
        add_filter( 'dokan_settings_fields', array( $this, 'load_settings_fields' ), 20 );
    }

    /**
     * Load admin settings section
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_settings_section( $section ) {
        $section[] = [
            'id'                   => 'dokan_rma',
            'title'                => __( 'RMA', 'dokan' ),
            'icon_url'             => DOKAN_RMA_ASSETS_DIR . '/images/rma.svg',
            'description'          => __( 'Manage Return & Warranty', 'dokan' ),
            'document_link'        => 'https://wedevs.com/docs/dokan/modules/vendor-rma/',
            'settings_title'       => __( 'RMA Settings', 'dokan' ),
            'settings_description' => __( 'You can configure your site settings to allow vendors to offer customized return and warranty facility on their sold products.', 'dokan' ),
        ];

        return $section;
    }

    /**
     * Load all settings fields
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_settings_fields( $fields ) {
        $fields['dokan_rma'] = [
            'rma_order_status' => [
                'name'    => 'rma_order_status',
                'label'   => __( 'Order Status', 'dokan' ),
                'type'    => 'select',
                'desc'    => __( 'On what order status customer can avail the return or warranty facility.', 'dokan' ),
                'default' => 'seller',
                'options' => wc_get_order_statuses(),
                'tooltip' => __( 'On what order status customer can avail the Return or Warranty facility.', 'dokan' ),
            ],
            'rma_enable_refund_request' => [
                'name'    => 'rma_enable_refund_request',
                'label'   => __( 'Enable Refund Requests', 'dokan' ),
                'type'    => 'switcher',
                'desc'    => __( 'Allow customers to request for refunds', 'dokan' ),
                'default' => 'off',
            ],
            'rma_enable_coupon_request' => [
                'name'    => 'rma_enable_coupon_request',
                'label'   => __( 'Enable Coupon Requests', 'dokan' ),
                'type'    => 'switcher',
                'desc'    => __( 'Allow customers to request for coupons as store credit', 'dokan' ),
                'default' => 'off',
            ],

            'rma_reasons' => [
                'name'    => 'rma_reasons',
                'label'   => __( 'Reasons for RMA', 'dokan' ),
                'type'    => 'repeatable',
                'desc'    => __( 'You can add one or more custom reasons from here.', 'dokan' ),
                'tooltip' => __( 'You can add one or more custom reasons from here.', 'dokan' ),

            ],

            'rma_policy' => [
                'name'    => 'rma_policy',
                'label'   => __( 'Refund Policy', 'dokan' ),
                'type'    => 'wpeditor',
                'desc'    => __( 'Refund policy for all stores. Vendor can overwrite this policy.', 'dokan' ),
                'tooltip' => __( 'Refund policy for all stores. Vendor can overwrite this policy.', 'dokan' ),
            ],
        ];

        return $fields;
    }
}
