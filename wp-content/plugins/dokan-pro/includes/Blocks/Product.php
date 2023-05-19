<?php

namespace WeDevs\DokanPro\Blocks;

use WC_Product;

defined( 'ABSPATH' ) || exit;

class Product {

    /**
     * Constructor class.
     *
     * @since 3.7.13
     */
    public function __construct() {
        add_filter( 'dokan_rest_get_product_block_data', [ $this, 'set_block_data' ], 10, 3 );
    }

    /**
     * Set product block data for Dokan-pro.
     *
     * @since 3.7.13
     *
     * @param array      $block
     * @param WC_Product $product
     * @param string     $context
     *
     * @return array
     */
    public function set_block_data( $block, $product, $context = 'view' ) {
        // External product type.
        $block['general']['external_url'] = $product->is_type( 'external' ) ? $product->get_product_url( $context ) : '';
        $block['general']['button_text'] = $product->is_type( 'external' ) ? $product->get_button_text( $context ) : '';

        // Linked section.
        $block['linked']['upsell_ids']       = $this->get_formatted_products( array_map( 'absint', $product->get_upsell_ids( $context ) ) );
        $block['linked']['cross_sell_ids']   = $this->get_formatted_products( array_map( 'absint', $product->get_cross_sell_ids( $context ) ) );
        $block['linked']['grouped_products'] = $product->is_type( 'grouped' ) ? $this->get_formatted_products( $product->get_children() ) : [];

        return $block;
    }

    /**
     * Get formatted products from IDS with name and id.
     *
     * @since 3.7.13
     *
     * @param array $product_ids
     *
     * @return array
     */
    private function get_formatted_products( $product_ids = [] ) {
        if ( ! is_array( $product_ids ) || ! count( $product_ids ) ) {
            return [];
        }

        $products = wc_get_products(
            [
				'include' => $product_ids,
			]
        );

        $formatted_products = [];
        foreach ( $products as $product ) {
            $formatted_products[] = [
                'label' => $product->get_name(),
                'value' => $product->get_id(),
            ];
        }

        return $formatted_products;
    }
}
