<?php

namespace WeDevs\DokanPro\Modules\OrderMinMax\SettingsApi;

defined( 'ABSPATH' ) || exit;

/**
 * Vendor settings api for OrderMinMax
 *
 * @since 3.7.13
 */
class Store {

    /**
     * Constructor function
     *
     * @since 3.7.13
     */
    public function __construct() {
        add_filter( 'dokan_vendor_settings_api_store_details_tab', [ $this, 'add_min_max_card_to_vendor_settings_api' ] );
    }

    /**
     * Adds variation min max settings to the vendor dashboard.
     *
     * @since 3.7.13
     *
     * @param array $store_details_tab array of store details tab.
     *
     * @return array
     */
    public function add_min_max_card_to_vendor_settings_api( array $store_details_tab ): array {
        $order_min_max_fields    = [];
        $enable_min_max_quantity = dokan_get_option( 'enable_min_max_quantity', 'dokan_selling', 'off' );
        $enable_min_max_amount   = dokan_get_option( 'enable_min_max_amount', 'dokan_selling', 'off' );

        if ( 'on' === $enable_min_max_quantity ) {
            $min_max_quantities = [
                [
                    'id'        => 'enable_vendor_min_max_quantity',
                    'title'     => __( 'Enable Min/Max Product Quantities', 'dokan' ),
                    'desc'      => __( 'Activating this will set min and max quantities for selected products & category', 'dokan' ),
                    'icon'      => '',
                    'default'   => 'no',
                    'options'   => [
                        'yes' => __( 'Yes', 'dokan' ),
                        'no'  => __( 'No', 'dokan' ),
                    ],
                    'type'      => 'checkbox',
                    'parent_id' => 'order_min_max',
                ],
                [
                    'id'        => 'min_quantity_to_order',
                    'title'     => __( 'Minimum Quantities', 'dokan' ),
                    'desc'      => __( 'Minimum Quantities for Order', 'dokan' ),
                    'icon'      => '',
                    'type'      => 'number',
                    'increment' => 1,
                    'minimum'   => 0,
                    'parent_id' => 'order_min_max',
                ],
                [
                    'id'        => 'max_quantity_to_order',
                    'title'     => __( 'Maximum Quantities', 'dokan' ),
                    'desc'      => __( 'Maximum Quantities for Order', 'dokan' ),
                    'icon'      => '',
                    'type'      => 'number',
                    'increment' => 1,
                    'minimum'   => 0,
                    'parent_id' => 'order_min_max',
                ],
            ];
            array_push( $order_min_max_fields, ...$min_max_quantities );
        }

        if ( 'on' === $enable_min_max_amount ) {
            $min_max_amounts = [
                [
                    'id'        => 'enable_vendor_min_max_amount',
                    'title'     => __( 'Enable Min/Max Product Amount', 'dokan' ),
                    'desc'      => __( 'Activating this will set min and max amount for selected products & category', 'dokan' ),
                    'icon'      => '',
                    'default'   => 'no',
                    'options'   => [
                        'yes' => __( 'Yes', 'dokan' ),
                        'no'  => __( 'No', 'dokan' ),
                    ],
                    'type'      => 'checkbox',
                    'parent_id' => 'order_min_max',
                ],
                [
                    'id'        => 'min_amount_to_order',
                    'title'     => __( 'Minimum Amount', 'dokan' ),
                    'desc'      => __( 'Minimum Amount for Order', 'dokan' ),
                    'icon'      => '',
                    'type'      => 'number',
                    'increment' => 0.01,
                    'minimum'   => 0,
                    'mode'      => 'currency',
                    'parent_id' => 'order_min_max',
                ],
                [
                    'id'        => 'max_amount_to_order',
                    'title'     => __( 'Maximum Amount', 'dokan' ),
                    'desc'      => __( 'Maximum Amount for Order', 'dokan' ),
                    'icon'      => '',
                    'type'      => 'number',
                    'increment' => 0.01,
                    'minimum'   => 0,
                    'mode'      => 'currency',
                    'parent_id' => 'order_min_max',
                ],
            ];
            array_push( $order_min_max_fields, ...$min_max_amounts );
        }

        if ( 'on' === $enable_min_max_quantity || 'on' === $enable_min_max_amount ) {
            $minmax_card   = [];
            $minmax_card[] = [
                'id'        => 'min_max_quantities_card',
                'title'     => __( 'Define Min/Max Quantities & Amount', 'dokan' ),
                'desc'      => __( 'Set minimum or maximum order limit on specific items.', 'dokan' ),
                'info'      => [
                    [
                        'text' => __( 'Docs', 'dokan-lite' ),
                        'url'  => 'https://wedevs.com/docs/dokan/modules/how-to-enable-minimum-maximum-order-amount-for-dokan/',
                        'icon' => 'dokan-icon-doc',
                    ],
                ],
                'icon'      => 'dokan-icon-products',
                'type'      => 'card',
                'parent_id' => 'store',
                'tab'       => 'store_details',
                'editable'  => true,
            ];
            $min_max_product_category = [
                [
                    'id'          => 'vendor_min_max_products',
                    'title'       => __( 'Select Products', 'dokan' ),
                    'placeholder' => __( 'Search Products', 'dokan' ),
                    'desc'        => '',
                    'icon'        => '',
                    'type'        => 'combobox',
                    'multiple'    => true,
                    'parent_id'   => 'order_min_max',
                ],
                [
                    'id'          => 'vendor_min_max_product_cat',
                    'title'       => __( 'Select Category', 'dokan' ),
                    'placeholder' => __( 'Search Category', 'dokan' ),
                    'desc'        => '',
                    'icon'        => '',
                    'type'        => 'combobox',
                    'multiple'    => false,
                    'parent_id'   => 'order_min_max',
                ],
            ];

            array_push( $order_min_max_fields, ...$min_max_product_category );

            $minmax_card[] = [
                'id'        => 'order_min_max',
                'title'     => '',
                'desc'      => '',
                'info'      => [],
                'icon'      => '',
                'type'      => 'section',
                'parent_id' => 'store',
                'tab'       => 'store_details',
                'card'      => 'min_max_quantities_card',
                'editable'  => false,
                'fields'    => $order_min_max_fields,
            ];

            $minmax_card = apply_filters( 'dokan_pro_vendor_settings_api_min_max_quantities_amount_card', $minmax_card );
            array_push( $store_details_tab, ...$minmax_card );
        }

        return $store_details_tab;
    }
}
