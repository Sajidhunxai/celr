<?php

namespace WeDevs\DokanPro\Modules\OrderMinMax;

use WC_Product;

defined( 'ABSPATH' ) || exit;

/**
 * OrderMinMax Module block data.
 *
 * @since 3.7.13
 */
class BlockData {

    /**
     * Block Section name.
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
        $this->section = 'order_min_max';

        // Get configuration.
        add_filter( 'dokan_get_product_block_configurations', [ $this, 'get_product_block_configurations' ] );

        // Product block get and set.
        add_filter( 'dokan_rest_get_product_block_data', [ $this, 'get_product_block_data' ], 10, 3 );
        add_filter( 'dokan_rest_get_product_variable_block_data', [ $this, 'get_variable_product_block_data' ], 10, 3 );
        add_action( 'dokan_rest_insert_product_object', [ $this, 'set_product_block_data' ], 10, 3 );
    }

    /**
     * Get order-min-max module product block configurations.
     *
     * @since 3.7.13
     *
     * @param $configuration array
     *
     * @return array
     */
    public function get_product_block_configurations( $configuration = [] ) {
        $configuration[ $this->section ] = [
            'min_max_product_variation_wise_activation_action_nonce' => wp_create_nonce( 'min_max_product_variation_wise_activation_action' ),
        ];

        return $configuration;
    }

    /**
     * Get order-min-max product data for Dokan-pro.
     *
     * @since 3.7.13
     *
     * @param array      $block
     * @param WC_Product $product
     * @param string     $context
     *
     * @return array
     */
    public function get_product_block_data( array $block, $product, string $context ) {
        if ( ! $product instanceof WC_Product ) {
            return $block;
        }

        $meta = Helper::get_product_min_max_meta( $product );

        $block[ $this->section ] = [
            'product_wise_activation' => ( Helper::is_enabled_min_max_qty() || Helper::is_enabled_min_max_amount() ) && isset( $meta['product_wise_activation'] ) ? $meta['product_wise_activation'] : '',
            'min_quantity'            => isset( $meta['min_quantity'] ) ? $meta['min_quantity'] : '',
            'max_quantity'            => isset( $meta['max_quantity'] ) ? $meta['max_quantity'] : '',
            'min_amount'              => isset( $meta['min_amount'] ) ? $meta['min_amount'] : '',
            'max_amount'              => isset( $meta['max_amount'] ) ? $meta['max_amount'] : '',
            '_donot_count'            => isset( $meta['_donot_count'] ) ? $meta['_donot_count'] : '',
            'ignore_from_cat'         => isset( $meta['ignore_from_cat'] ) ? $meta['ignore_from_cat'] : '',
        ];

        return $block;
    }

    /**
     * Get order-min-max product data for Dokan-pro.
     *
     * @since 3.7.13
     *
     * @param array      $block
     * @param WC_Product $product
     * @param string     $context
     *
     * @return array
     */
    public function get_variable_product_block_data( array $block, $product, string $context ) {
        $block = $this->get_product_block_data( $block, $product, $context );

        $block[ $this->section ]['min_max_product_variation_wise_activation_action_nonce'] = wp_create_nonce( 'min_max_product_variation_wise_activation_action' );

        return $block;
    }

    /**
     * Save order-min-max data after REST-API insert or update.
     *
     * @since 3.7.13
     *
     * @param WC_Product      $product  Inserted object.
     * @param WP_REST_Request $request  Request object.
     * @param boolean         $creating True when creating object, false when updating.
     *
     * @return void
     */
    public function set_product_block_data( $product, $request, $creating = true ) {
        if ( ! $product instanceof WC_Product ) {
            return;
        }

        // Return here if order_min_max is disabled.
        if ( ! Helper::is_enabled_min_max_qty_or_amount() ) {
            return;
        }

        $meta['product_wise_activation'] = isset( $request['product_wise_activation'] ) && (bool) ( wc_clean( wp_unslash( $request['product_wise_activation'] ) ) ) ? 'yes' : 'no';
        $meta['min_quantity']            = isset( $request['min_quantity'] ) && $request['min_quantity'] > 0 ? absint( wp_unslash( $request['min_quantity'] ) ) : 0;
        $meta['max_quantity']            = isset( $request['max_quantity'] ) && $request['max_quantity'] > 0 ? absint( wp_unslash( $request['max_quantity'] ) ) : 0;
        $meta['min_amount']              = isset( $request['min_amount'] ) && $request['min_amount'] > 0 ? wc_format_decimal( sanitize_text_field( wp_unslash( $request['min_amount'] ) ) ) : 0;
        $meta['max_amount']              = isset( $request['max_amount'] ) && $request['max_amount'] > 0 ? wc_format_decimal( sanitize_text_field( wp_unslash( $request['max_amount'] ) ) ) : 0;
        $meta['_donot_count']            = isset( $request['_donot_count'] ) && (bool) ( wc_clean( wp_unslash( $request['_donot_count'] ) ) ) ? 'yes' : 'no';
        $meta['ignore_from_cat']         = isset( $request['ignore_from_cat'] ) && (bool) ( wc_clean( wp_unslash( $request['ignore_from_cat'] ) ) ) ? 'yes' : 'no';

        Helper::set_product_min_max_meta( $product, $meta );
    }
}
