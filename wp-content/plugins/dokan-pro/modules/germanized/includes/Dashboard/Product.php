<?php
namespace WeDevs\DokanPro\Modules\Germanized\Dashboard;

use WC_Germanized_Meta_Box_Product_Data;
use WeDevs\DokanPro\Modules\Germanized\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Product
 * @package WeDevs\DokanPro\Modules\Germanized\Dashboard
 * @since 3.3.1
 */
class Product {

    /**
     * Product constructor.
     *
     * @since 3.3.1
     * @return void
     */
    public function __construct() {
        $this->add_actions();
    }

    /**
     * Call all actions and filters
     *
     * @since 3.3.1
     */
    public function add_actions() {
        add_action( 'dokan_product_edit_after_main', [ $this, 'load_simple_product_template' ], 10, 2 );
        add_action( 'dokan_product_after_variable_attributes', [ $this, 'load_variable_product_template' ], 10, 3 );

        //save product data
        add_action( 'dokan_product_updated', [ $this, 'save_simple_product_data' ], 99, 1 );
        add_action( 'woocommerce_save_product_variation', [ $this, 'save_variation_product_data' ], 99, 2 );
    }

    /**
     * This method will load template for simple product
     *
     * @since 3.3.1
     * @param \WC_Product $post
     * @param int $post_id
     * @return void
     */
    public function load_simple_product_template( $post, $post_id ) {
        dokan_get_template_part(
            'product-simple', '', [
                'is_germanized' => true,
                'post'          => $post,
                'post_id'       => $post_id,
            ]
        );
    }

    /**
     * This method will load template for variable product
     *
     * @since 3.3.1
     * @param $loop
     * @param $variation_data
     * @param $variation
     * @return void
     */
    public function load_variable_product_template( $loop, $variation_data, $variation ) {
        dokan_get_template_part(
            'product-variable', '', [
                'is_germanized'     => true,
                'loop'              => $loop,
                'variation_data'    => $variation_data,
                'variation'         => $variation,
            ]
        );
    }

    /**
     * This method will save simple product data
     *
     * @since 3.3.1
     * @param int $post_id
     * @return void
     */
    public function save_simple_product_data( $post_id ) {
        if ( ! isset( $_POST['dokan_edit_product_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['dokan_edit_product_nonce'] ), 'dokan_edit_product' ) ) {
            return;
        }

        $data['_ts_gtin'] = isset( $data['_ts_gtin'] ) ? wc_clean( wp_unslash( $_POST['_ts_gtin'] ) ) : '';
        $data['_ts_mpn']  = isset( $data['_ts_mpn'] ) ? wc_clean( wp_unslash( $_POST['_ts_mpn'] ) ) : '';

        Helper::save_simple_product_eu_data( $post_id, $data );
    }

    /**
     * This method will save variable product data
     *
     * @since 3.3.1
     * @param $variation_id
     * @param $i
     *
     * @return void
     */
    public function save_variation_product_data( $variation_id, $i ) {
        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_key( $_POST['security'] ), 'save-variations' ) ) {
            return;
        }

        $data = array(
            '_unit_product'             => '',
            '_unit_price_auto'          => '',
            '_unit_price_regular'       => '',
            '_sale_price_label'         => '',
            '_sale_price_regular_label' => '',
            '_unit_price_sale'          => '',
            '_parent_unit_product'      => '',
            '_parent_unit'              => '',
            '_parent_unit_base'         => '',
            '_mini_desc'                => '',
            '_service'                  => '',
            'delivery_time'             => '',
            '_min_age'                  => '',
        );

        foreach ( $data as $k => $v ) {
            $data_k     = 'variable' . ( substr( $k, 0, 1 ) === '_' ? '' : '_' ) . $k;
            $data[ $k ] = ( isset( $_POST[ $data_k ][ $i ] ) ? wc_clean( wp_unslash( $_POST[ $data_k ][ $i ] ) ) : null );
        }

        // Check if parent has unit_base + unit otherwise ignore data
        if ( empty( $data['_parent_unit'] ) || empty( $data['_parent_unit_base'] ) ) {
            $data['_unit_price_auto']    = '';
            $data['_unit_price_regular'] = '';
            $data['_unit_price_sale']    = '';
        }

        // If parent has no unit, delete unit_product as well
        if ( empty( $data['_parent_unit'] ) ) {
            $data['_unit_product'] = '';
        }

        $data['_sale_price_dates_from'] = isset( $_POST['variable_sale_price_dates_from'][ $i ] ) ? wc_clean( wp_unslash( $_POST['variable_sale_price_dates_from'][ $i ] ) ) : '';
        $data['_sale_price_dates_to']   = isset( $_POST['variable_sale_price_dates_to'][ $i ] ) ? wc_clean( wp_unslash( $_POST['variable_sale_price_dates_to'][ $i ] ) ) : '';
        $data['_sale_price']            = isset( $_POST['variable_sale_price'][ $i ] ) ? wc_clean( wp_unslash( $_POST['variable_sale_price'][ $i ] ) ) : '';

        // store trusted shop data
        $store_trusted_data = array(
            '_ts_gtin' => '',
            '_ts_mpn'  => '',
        );

        foreach ( $store_trusted_data as $k => $v ) {
            $data_k     = 'variable' . ( substr( $k, 0, 1 ) === '_' ? '' : '_' ) . $k;
            $store_trusted_data[ $k ] = ( isset( $_POST[ $data_k ][ $i ] ) ? wc_clean( wp_unslash( $_POST[ $data_k ][ $i ] ) ) : null );
        }

        Helper::save_variable_products_variations_eu_data( $variation_id, $data, $store_trusted_data );
    }
}
