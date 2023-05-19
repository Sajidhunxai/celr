<?php

namespace WeDevs\DokanPro\SettingsApi;

defined( 'ABSPATH' ) || exit;

/**
 * Store settings page.
 *
 * @since 3.7.13
 */
class Store {

    /**
     * Constructor.
     *
     * @since 3.7.13
     *
     * @return void
     */
    public function __construct() {
        add_filter( 'dokan_vendor_settings_api_general_tab', [ $this, 'add_biography_card_api' ] );
        add_filter( 'dokan_vendor_settings_api_business_info_card', [ $this, 'add_store_category_to_business_info_card' ] );
        add_filter( 'dokan_vendor_settings_api_advanced_tab', [ $this, 'add_support_card_to_vendor_settings_api' ] );
        add_filter( 'dokan_vendor_settings_api_advanced_tab', [ $this, 'add_storewide_discount_card_to_vendor_settings_api' ], 9 );
    }


    /**
     * Add biography card api to vendor settings
     *
     * @since 3.7.13
     *
     * @param array $settings array of settings.
     *
     * @return array
     */
    public function add_biography_card_api( array $settings ): array {
        $biography   = [];
        $biography[] = [
            'id'        => 'biography_card',
            'title'     => __( 'About Your Store', 'dokan' ),
            'desc'      => __( 'Give visitors detailed information about what your store is all about', 'dokan' ),
            'info'      => [],
            'icon'      => 'dokan-icon-doc-2',
            'type'      => 'card',
            'parent_id' => 'store',
            'tab'       => 'general',
            'editable'  => true,
        ];
        $biography[] = [
            'id'          => 'vendor_biography',
            'title'       => '',
            'desc'        => '',
            'placeholder' => __( 'Write about your business, product offerings and more', 'dokan' ),
            'info'        => [],
            'icon'        => '',
            'type'        => 'textarea',
            'parent_id'   => 'store',
            'tab'         => 'general',
            'card'        => 'biography_card',
        ];

        $biography = apply_filters( 'dokan_pro_vendor_settings_api_biography_card', $biography );
        array_push( $settings, ...$biography );
        return $settings;
    }

    /**
     * Add store category to business info card.
     *
     * @since 3.7.13
     *
     * @param array $business_info_card array of settings.
     *
     * @return array
     */
    public function add_store_category_to_business_info_card( array $business_info_card ): array {
        $category_type        = dokan_get_option( 'store_category_type', 'dokan_general', 'none' );
        $business_info_card[] = [
            'id'          => 'categories',
            'title'       => __( 'Category', 'dokan' ),
            'desc'        => '',
            'info'        => [],
            'icon'        => '',
            'placeholder' => __( 'Select Your Store Categories', 'dokan' ),
            'type'        => 'select',
            'multiple'    => 'multiple' === $category_type,
            'parent_id'   => 'store',
            'tab'         => 'general',
            'card'        => 'business_info',
            'options'     => get_terms(
                [
                    'taxonomy'   => 'store_category',
                    'hide_empty' => false,
                ]
            ),
        ];

        return $business_info_card;
    }

    /**
     * Add support card to advance tab.
     *
     * @since 3.7.13
     *
     * @param array $advance_tab Advance tab data.
     *
     * @return array
     */
    public function add_support_card_to_vendor_settings_api( array $advance_tab ): array {
        if ( ! dokan_pro()->module->is_active( 'live_chat' ) && ! dokan_pro()->module->is_active( 'store_support' ) ) {
            return $advance_tab;
        }

        $support_card   = [];
        $support_card[] = [
            'id'        => 'support_card',
            'title'     => __( 'Display Support Option', 'dokan' ),
            'desc'      => __( 'Choose where to display support button for customers to utilize', 'dokan' ),
            'info'      => [
                [
                    'text' => __( 'Docs', 'dokan' ),
                    'url'  => 'https://wedevs.com/docs/dokan/modules/how-to-install-and-use-store-support/',
                    'icon' => 'dokan-icon-doc',
                ],
            ],
            'icon'      => 'dokan-icon-headphone',
            'type'      => 'card',
            'parent_id' => 'store',
            'tab'       => 'advance',
            'editable'  => true,
        ];

        $support_card = apply_filters( 'dokan_pro_vendor_settings_api_support_card', $support_card );
        array_push( $advance_tab, ...$support_card );

        return $advance_tab;
    }


    /**
     * Add storewide discount to advance tab.
     *
     * @since 3.7.13
     *
     * @param array $advance_tab Advance tab data.
     *
     * @return array
     */
    public function add_storewide_discount_card_to_vendor_settings_api( array $advance_tab ): array {
        $is_enable_op_discount = dokan_get_option( 'discount_edit', 'dokan_selling', [] );
        if ( empty( array_filter( $is_enable_op_discount ) ) ) {
            return $advance_tab;
        }

        $storewide_discount_card   = [];
        $storewide_discount_card[] = [
            'id'        => 'storewide_discount_card',
            'title'     => __( 'Storewide Discount', 'dokan' ),
            'desc'      => __( 'Offer customers store wide discount if the order total is above a minimum unit amount', 'dokan' ),
            'info'      => [
                [
                    'text' => __( 'Docs', 'dokan' ),
                    'url'  => 'https://wedevs.com/docs/dokan/tutorials/how-to-enable-store-wide-bulk-discount/',
                    'icon' => 'dokan-icon-doc',
                ],
            ],
            'icon'      => 'dokan-icon-discount',
            'type'      => 'card',
            'parent_id' => 'store',
            'tab'       => 'advance',
            'editable'  => false,
        ];
        $storewide_discount_card[] = [
            'id'        => 'show_min_order_discount',
            'title'     => __( 'Discount', 'dokan' ),
            'desc'      => __( 'Enable storewide discount', 'dokan' ),
            'info'      => [],
            'icon'      => '',
            'type'      => 'checkbox',
            'default'   => 'no',
            'options'   => [
                'yes' => __( 'Enable storewide discount', 'dokan' ),
                'no'  => __( 'Disable storewide discount', 'dokan' ),
            ],
            'parent_id' => 'store',
            'tab'       => 'advance',
            'card'      => 'storewide_discount_card',
        ];
        $storewide_discount_card[] = [
            'id'        => 'setting_minimum_order_amount',
            'title'     => __( 'Minimum Order Amount', 'dokan' ),
            'desc'      => __( 'Minimum Order Amount to apply the discount.', 'dokan' ),
            'info'      => [],
            'icon'      => '',
            'type'      => 'text',
            'parent_id' => 'store',
            'tab'       => 'advance',
            'card'      => 'storewide_discount_card',
        ];
        $storewide_discount_card[] = [
            'id'        => 'setting_order_percentage',
            'title'     => __( 'Discount Percentage', 'dokan' ),
            'desc'      => '',
            'info'      => [],
            'icon'      => '',
            'type'      => 'text',
            'parent_id' => 'store',
            'tab'       => 'advance',
            'card'      => 'storewide_discount_card',
        ];

        $storewide_discount_card = apply_filters( 'dokan_pro_vendor_settings_api_storewide_discount_card', $storewide_discount_card );
        array_push( $advance_tab, ...$storewide_discount_card );

        return $advance_tab;
    }
}
