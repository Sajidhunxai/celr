<?php

namespace WeDevs\DokanPro\Modules\RMA;

use WC_Product;

defined( 'ABSPATH' ) || exit;

/**
 * RMA Module block data.
 *
 * @since 3.7.13
 */
class BlockData {

    use \Dokan_RMA_Common;

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
        $this->section = 'rma';

        // Get configuration
        add_filter( 'dokan_get_product_block_configurations', [ $this, 'get_product_block_configurations' ] );

        // Get and Set block
        add_filter( 'dokan_rest_get_product_block_data', [ $this, 'get_product_block_data' ], 10, 3 );
        add_action( 'dokan_rest_insert_product_object', [ $this, 'set_product_block_data' ], 10, 3 );
    }

    /**
     * Get RMA module product block configurations.
     *
     * @since 3.7.13
     *
     * @param $configuration array
     *
     * @return array
     */
    public function get_product_block_configurations( $configuration = [] ) {
        $configuration[ $this->section ] = [
            'warranty_types'            => dokan_rma_warranty_type(),
            'warranty_lengths'          => dokan_rma_warranty_length(),
            'warranty_refund_reasons'   => dokan_rma_refund_reasons(),
            'warranty_length_durations' => dokan_rma_warranty_length_duration(),
        ];

        return $configuration;
    }

    /**
     * Get order-min-max product data for Dokan-pro
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

        $post_id          = $product->get_id();
        $rma              = $this->get_settings( $post_id );
        $override_default = get_post_meta( $post_id, '_dokan_rma_override_product', true );
        $rma_reasons      = isset( $rma['reasons'] ) ? $rma['reasons'] : [];

        $block[ $this->section ] = [
            'dokan_rma_product_override' => $override_default === 'yes',
            'warranty_label'             => isset( $rma['label'] ) ? $rma['label'] : '',
            'warranty_type'              => isset( $rma['type'] ) ? $rma['type'] : '',
            'warranty_length'            => isset( $rma['length'] ) ? $rma['length'] : '',
            'warranty_length_value'      => isset( $rma['length_value'] ) ? $rma['length_value'] : '',
            'warranty_length_duration'   => isset( $rma['length_duration'] ) ? $rma['length_duration'] : '',
            'warranty_reason'            => $rma_reasons,
            'addon_settings'             => isset( $rma['addon_settings'] ) ? $rma['addon_settings'] : [],
            'warranty_policy'            => isset( $rma['policy'] ) ? $rma['policy'] : '',
        ];

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

        if ( ! isset( $request['dokan_rma_product_override'] ) ) {
            return;
        }

        $post_id          = $product->get_id();
        $override_default = $request['dokan_rma_product_override'];

        if ( ! empty( $override_default ) ) {
            $override_default = $override_default ? 'yes' : 'no';
            $request['dokan_rma_product_override'] = $override_default;
            update_post_meta( $post_id, '_dokan_rma_override_product', $override_default );
        }

        if ( 'yes' === $override_default ) {
            $request              = $this->process_addon_settings( $request );
            $product_rma_settings = $this->transform_rma_settings( $request );
            update_post_meta( $post_id, '_dokan_rma_settings', $product_rma_settings );
        } else {
            delete_post_meta( $post_id, '_dokan_rma_settings' );
        }
    }

    /**
     * Process addon settings.
     *
     * @since 3.7.13
     *
     * @param WP_REST_Request $request
     *
     * @return array
     */
    private function process_addon_settings( $request ) {
        if ( ! isset( $request['addon_settings'] ) && ! is_array( $request['addon_settings'] ) ) {
            return $request;
        }

        $price    = [];
        $length   = [];
        $duration = [];

        foreach ( $request['addon_settings'] as $addon_setting ) {
            array_push( $price, $addon_setting['price'] );
            array_push( $length, $addon_setting['length'] );
            array_push( $duration, $addon_setting['duration'] );
        }

        $request['warranty_addon_price']    = $price;
        $request['warranty_addon_length']   = $length;
        $request['warranty_addon_duration'] = $duration;

        return $request;
    }
}
