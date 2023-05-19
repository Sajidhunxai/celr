<?php

namespace WeDevs\DokanPro\Blocks;

use WeDevs\Dokan\Product\ProductAttribute as ProductAttributeLite;
use WC_Product;

defined( 'ABSPATH' ) || exit;

/**
 * Dokan Product Attribute Block data class.
 *
 * @author weDevs
 */
class ProductAttribute {

    /**
     * Block section name.
     *
     * @since 3.7.13
     *
     * @var string
     */
    public $section;

    /**
     * Constructor class.
     *
     * @since 3.7.13
     */
    public function __construct() {
        $this->section = 'attributes';

        // Configuration
        add_filter( 'dokan_get_product_block_configurations', [ $this, 'get_block_configurations' ] );

        // Set and Get
        add_filter( 'dokan_rest_get_product_block_data', [ $this, 'get_block_data' ], 10, 3 );
    }

    /**
     * Get product block configurations.
     *
     * @since 3.7.13
     *
     * @param array $configuration
     *
     * @return array
     */
    public function get_block_configurations( $configuration = [] ) {
        $configuration[ $this->section ] = [
            'attribute_taxonomies' => $this->get_attribute_taxonomies(),
        ];

        return $configuration;
    }

    /**
     * Get attribute taxonomies.
     *
     * @since 3.7.13
     *
     * @param bool $formatted
     *
     * @return array
     */
    private function get_attribute_taxonomies( $formatted = true ) {
        $taxonomies = wc_get_attribute_taxonomies();
        $selects    = [];

        if ( ! $formatted ) {
            return $taxonomies;
        }

        foreach ( $taxonomies as $taxonomy ) {
            $selects[] = [
                'label' => $taxonomy->attribute_label,
                'value' => $taxonomy->attribute_name,
                'id'    => $taxonomy->attribute_id,
                'terms' => wp_list_pluck( get_terms( 'pa_' . $taxonomy->attribute_name ), 'name', 'term_id' ),
            ];
        }

        return $selects;
    }

    /**
     * Get product-shipping data.
     *
     * @since 3.7.13
     *
     * @param array      $block
     * @param WC_Product $product
     * @param string     $context
     *
     * @return array
     */
    public function get_block_data( array $block, $product, string $context ) {
        if ( ! $product instanceof WC_Product ) {
            return $block;
        }

        $product_attribute  = new ProductAttributeLite();
        $post_id            = $product->get_id();
        $_has_attribute     = $product->get_meta( '_has_attribute', true, $context );
        $_create_variations = $product->get_meta( '_create_variation', true, $context );

        $block[ $this->section ] = [
            '_has_attribute'      => 'yes' === $_has_attribute,
            '_create_variations'  => 'yes' === $_create_variations,
            '_product_attributes' => $product_attribute->get( $post_id ),
            '_default_attributes' => maybe_unserialize( $product->get_default_attributes( $context ) ),
        ];

        return $block;
    }
}
