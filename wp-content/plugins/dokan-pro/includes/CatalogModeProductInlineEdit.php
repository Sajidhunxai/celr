<?php

namespace WeDevs\DokanPro;

use WeDevs\Dokan\CatalogMode\Helper;

/**
 * ProductBulkEdit class
 *
 * @since   3.7.4
 *
 * @package WeDevs\DokanPro\CatalogMode\Dashboard
 */
class CatalogModeProductInlineEdit {
    /**
     * Class Constructor
     *
     * @since 3.7.4
     */
    public function __construct() {
        if ( ! class_exists( Helper::class ) || ! Helper::is_enabled_by_admin() ) {
            return;
        }
        add_action( 'dokan_quick_edit_before_column2_ends', [ $this, 'inline_product_catalog_fields' ], 10, 1 );
        add_action( 'dokan_product_quick_edit_updated', [ $this, 'save_inline_edit_catalog_mode_data' ], 10, 1 );
    }

    /**
     * Add bulk edit status.
     *
     * @since 3.7.4
     *
     * @param int $product_id previous status.
     *
     * @return void
     */
    public function inline_product_catalog_fields( $product_id ) {
        if ( ! current_user_can( 'dokan_edit_product' ) ) {
            return;
        }

        //load template
        dokan_get_template_part(
            'products/catalog-mode-inline-edit-content', '', [
                'pro'        => true,
                'product_id' => $product_id,
                'saved_data' => Helper::get_catalog_mode_data_by_product( $product_id ),
            ]
        );
    }

    /**
     * This method will enable/disable catalog mode feature called from product inline edit.
     *
     * @since 3.7.4
     *
     * @return void
     */
    public function save_inline_edit_catalog_mode_data( $product_id ) {
        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'product-inline-edit' ) ) {
            return;
        }

        if ( ! current_user_can( 'dokan_edit_product' ) ) {
            return;
        }

        if ( ! isset( $_POST['data']['hide_add_to_cart_button'] ) ) {
            return;
        }

        $catalog_mode_data['hide_add_to_cart_button'] = isset( $_POST['data']['hide_add_to_cart_button'] ) && wc_string_to_bool( sanitize_text_field( wp_unslash( $_POST['data']['hide_add_to_cart_button'] ) ) ) ? 'on' : 'off';
        $catalog_mode_data['hide_product_price']      = isset( $_POST['data']['hide_product_price'] ) && wc_string_to_bool( sanitize_text_field( wp_unslash( $_POST['data']['hide_product_price'] ) ) ) ? 'on' : 'off';
        // set hide price to off if add to cart button is off
        if ( 'off' === $catalog_mode_data['hide_add_to_cart_button'] ) {
            $catalog_mode_data['hide_product_price'] = 'off';
        }
        update_post_meta( $product_id, '_dokan_catalog_mode', $catalog_mode_data );
    }
}
