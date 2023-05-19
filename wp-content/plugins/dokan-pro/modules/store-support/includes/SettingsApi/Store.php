<?php

namespace WeDevs\DokanPro\Modules\StoreSupport\SettingsApi;

defined( 'ABSPATH' ) || exit;

/**
 * Store support Vendor settings.
 *
 * @since 3.7.13
 */
class Store {

    /**
     * Constructor
     *
     * @since 3.7.13
     */
    public function __construct() {
        add_filter( 'dokan_pro_vendor_settings_api_support_card', [ $this, 'add_store_support_to_support_card' ] );
    }

    /**
     * Add support to Support card.
     *
     * @since 3.7.13
     *
     * @param array $support_card Support card data.
     *
     * @return array
     */
    public function add_store_support_to_support_card( array $support_card ): array {
        $support   = [];
        $support[] = [
            'id'        => 'show_support_btn',
            'title'     => __( 'Enable Support in store', 'dokan' ),
            'desc'      => __( 'Show support button in store', 'dokan' ),
            'info'      => [],
            'icon'      => '',
            'type'      => 'checkbox',
            'default'   => 'yes',
            'options'   => [
                'yes' => __( 'Yes', 'dokan' ),
                'no'  => __( 'No', 'dokan' ),
            ],
            'parent_id' => 'store',
            'tab'       => 'advance',
            'card'      => 'support_card',
        ];
        $support[] = [
            'id'        => 'show_support_btn_product',
            'title'     => __( 'Enable Support in Single Product', 'dokan' ),
            'desc'      => __( 'Show support button in single product page.', 'dokan' ),
            'info'      => [],
            'icon'      => '',
            'type'      => 'checkbox',
            'default'   => 'yes',
            'options'   => [
                'yes' => __( 'Yes', 'dokan' ),
                'no'  => __( 'No', 'dokan' ),
            ],
            'parent_id' => 'store',
            'tab'       => 'advance',
            'card'      => 'support_card',
        ];
        $support[] = [
            'id'        => 'support_btn_name',
            'title'     => __( 'Customize Support Button Text', 'dokan' ),
            'desc'      => __( 'Support Button text', 'dokan' ),
            'info'      => [],
            'icon'      => '',
            'type'      => 'text',
            'parent_id' => 'store',
            'tab'       => 'advance',
            'card'      => 'support_card',
        ];

        $support = apply_filters( 'dokan_pro_vendor_settings_api_store_support_section', $support );
        array_push( $support_card, ...$support );

        return $support_card;
    }
}
