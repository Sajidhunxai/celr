<?php

namespace WeDevs\DokanPro\Modules\Germanized\SettingsApi;

defined( 'ABSPATH' ) || exit;

/**
 * Settings on Store page.
 *
 * @since 3.7.13
 */
class Store {

    /**
     * Constructor function.
     *
     * @return void
     */
    public function __construct() {
        add_filter( 'dokan_vendor_settings_api_product_section_fields', [ $this, 'add_store_page_product_section_fields' ] );
    }

    /**
     * Add store page product section fields.
     *
     * @since 3.7.13
     *
     * @param array $fields array of section field.
     *
     * @return array
     */
    public function add_store_page_product_section_fields( array $fields ): array {
        $customizer_settings = dokan_get_option( 'product_sections', 'dokan_appearance', [] );

        if ( isset( $customizer_settings['advertised'] ) && 'off' === $customizer_settings['advertised'] ) {
            array_unshift(
                $fields,
                [
                    'id'        => 'featured',
                    'title'     => __( 'Show advertised products section', 'dokan' ),
                    'desc'      => __( 'Show advertised products section', 'dokan' ),
                    'icon'      => '',
                    'type'      => 'checkbox',
                    'default'   => 'yes',
                    'options'   => [
                        'yes' => __( 'Yes', 'dokan' ),
                        'no'  => __( 'No', 'dokan' ),
                    ],
                    'parent_id' => 'product_sections',
                ]
            );
        }
        return $fields;
    }
}
