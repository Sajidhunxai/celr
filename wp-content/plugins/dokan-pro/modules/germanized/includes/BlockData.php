<?php

namespace WeDevs\DokanPro\Modules\Germanized;

use WC_Product;
use \WeDevs\DokanPro\Modules\Germanized\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Eu compliance/Germanized Module block data.
 *
 * @since 3.7.13
 */
class BlockData {

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
        $this->section = 'eu-compliance-fields';

        // If can not find the woocommerce grmanized plugin we don't need to load our code.
        if ( ! function_exists('WC_germanized') ) {
            return;
        }

        // Get configuration
        add_filter( 'dokan_get_product_block_configurations', [ $this, 'get_product_block_configurations' ] );

        // Get and Set block
        add_filter( 'dokan_rest_get_product_block_data', [ $this, 'get_product_block_data' ], 10, 3 );
        add_action( 'dokan_rest_insert_product_object', [ $this, 'set_product_block_data' ], 10, 3 );

        // Get and Set for variable product variations.
        add_filter( 'dokan_rest_get_product_variable_block_data', [ $this, 'get_eu_fields_data_for_variable_products' ], 10, 3 );
        add_action( 'dokan_ajax_save_product_variations', [$this, 'save_eu_fields_data_for_product_variations'] );
    }

    /**
     * Get eu-compliance fields module product block configurations.
     *
     * @since 3.7.13
     *
     * @param array $configuration
     *
     * @return array
     */
    public function get_product_block_configurations( $configuration = [] ) {
        $configuration[ $this->section ] = [
            'priceLabels'         => array_merge( array( '-1' => __( '-- Select Price --', 'dokan' ) ), function_exists( 'WC_germanized' ) ? WC_germanized()->price_labels->get_labels() : [] ),
            'units'               => array_merge( array( '-1' => __( '-- Select Price Label --', 'dokan' ) ), function_exists( 'WC_germanized' ) ? WC_germanized()->units->get_units() : [] ),
            'ageSelect'           => function_exists( 'wc_gzd_get_age_verification_min_ages_select' ) ? wc_gzd_get_age_verification_min_ages_select() : array( '-1' => _x( 'None', 'age', 'dokan' ) ),
            'deliveryTimes'       => array( '-1' => __( 'Select Delivery Time', 'dokan' ) ) + Helper::get_terms( 'product_delivery_time', 'id' ),
            'trustedShopsEnabled' => function_exists( 'WC_trusted_shops' ) && WC_trusted_shops()->trusted_shops->is_enabled(),
            'isGermanizedPro'     => function_exists( 'WC_trusted_shops' ) && WC_germanized()->is_pro(),
            'enabled_germanized'  => dokan_get_option( 'enabled_germanized', 'dokan_germanized', 'off' ),
        ];

        return $configuration;
    }

    /**
     * Get eu compliance product data for Dokan-pro.
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
        if ( ! $product instanceof WC_Product || ! function_exists( 'WC_germanized' ) ) {
            return $block;
        }

        $gzd_product          = wc_gzd_get_product( $product );
        $product_delivery_time = $gzd_product->get_delivery_time();
        $delivery_time         = is_object( $product_delivery_time ) ? $product_delivery_time->term_id : '';

        $block[ $this->section ] = [
            '_sale_price_label'         => $product->get_meta( '_sale_price_label', true, $context ),
            '_sale_price_regular_label' => $product->get_meta( '_sale_price_regular_label', true, $context ),
            '_unit'                     => $product->get_meta( '_unit', true, $context ),
            '_min_age'                  => $product->get_meta( '_min_age', true, $context ),
            '_unit_product'             => $product->get_meta( '_unit_product', true, $context ),
            '_unit_base'                => $product->get_meta( '_unit_base', true, $context ),
            '_delivery_time'            => $delivery_time,
            '_free_shipping'            => $product->get_meta( '_free_shipping', true, $context ),
            '_unit_price_auto'          => $product->get_meta( '_unit_price_auto', true, $context ),
            '_unit_price_regular'       => $product->get_meta( '_unit_price_regular', true, $context ),
            '_unit_price_sale'          => $product->get_meta( '_unit_price_sale', true, $context ),
            '_ts_gtin'                  => $product->get_meta( '_ts_gtin', true, $context ),
            '_ts_mpn'                   => $product->get_meta( '_ts_mpn', true, $context ),
            '_mini_desc'                => $product->get_meta( '_mini_desc', true, $context ),
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

        $this->process_eu_data_for_germanized_plugin( $request );

        Helper::save_simple_product_eu_data( $product->get_id(), $request );
    }

    /**
     * Assigning the request values into the global post so that germanized plugin can save the eu fields data.
     *
     * @since 3.7.13
     *
     * @param WP_REST_Request $request
     *
     * @return void
     */
    private function process_eu_data_for_germanized_plugin( $request ) {
        $_POST['_sale_price_label']         = $request['_sale_price_label'];
        $_POST['_sale_price_regular_label'] = $request['_sale_price_regular_label'];
        $_POST['_unit']                     = $request['_unit'];
        $_POST['_min_age']                  = $request['_min_age'];
        $_POST['_unit_product']             = $request['_unit_product'];
        $_POST['_unit_base']                = $request['_unit_base'];
        $_POST['delivery_time']             = $request['_delivery_time'];
        $_POST['_unit_price_auto']          = $request['_unit_price_auto'];
        $_POST['_unit_price_regular']       = $request['_unit_price_regular'];
        $_POST['_unit_price_sale']          = $request['_unit_price_sale'];
        $_POST['_sale_price']               = $request['_unit_price_sale'];
        $_POST['_ts_gtin']                  = $request['_ts_gtin'];
        $_POST['_ts_mpn']                   = $request['_ts_mpn'];
        $_POST['_mini_desc']                = $request['_mini_desc'];

        if ( ! empty( $request['_free_shipping'] ) ) {
            $_POST['_free_shipping'] = 'yes';
        }
    }

    /**
     * Returns Eu compliance fields data of variable products
     *
     * @since 3.7.13
     *
     * @param array                 $data
     * @param WC_Product|null|false $product
     * @param string                $context
     *
     * @return array $data
     */
    public function get_eu_fields_data_for_variable_products( $data, $_product, $context ) {
        $gzd_product           = wc_gzd_get_product( $_product );
        $product_delivery_time = $gzd_product->get_delivery_time( 'edit' );
        $delivery_time         = is_object( $product_delivery_time ) ? $product_delivery_time->term_id : '';

        // get trusted source fields
        $variation_meta   = get_post_meta( $_product->get_id() );
        $variation_data   = array();
        $variation_fields = array(
            '_ts_gtin' => '',
            '_ts_mpn'  => '',
        );

        foreach ( $variation_fields as $field => $value ) {
            $variation_data[ $field ] = isset( $variation_meta[ $field ][0] ) ? maybe_unserialize( $variation_meta[ $field ][0] ) : $value;
        }

        $data[$this->section] = [
            '_sale_price_label'         => $gzd_product->get_sale_price_label( 'edit' ),
            '_sale_price_regular_label' => $gzd_product->get_sale_price_regular_label( 'edit' ),
            '_unit'                     => $gzd_product->get_unit('edit'),
            '_min_age'                  => $gzd_product->get_min_age( 'edit' ),
            '_unit_product'             => $gzd_product->get_unit_product( 'edit' ),
            '_unit_base'                => $gzd_product->get_unit_base('edit'),
            '_delivery_time'            => $delivery_time,
            '_free_shipping'            => $gzd_product->get_free_shipping('edit'),
            '_unit_price_auto'          => $gzd_product->get_unit_price_auto('edit'),
            '_unit_price_regular'       => $gzd_product->get_unit_price_regular('edit'),
            '_unit_price_sale'          => $gzd_product->get_unit_price_sale('edit'),
            '_ts_gtin'                  => $variation_fields['_ts_gtin'],
            '_ts_mpn'                   => $variation_fields['_ts_mpn'],
            '_mini_desc'                => $gzd_product->get_mini_desc('edit'),
        ];

        return $data;
    }

    /**
     * Saves eu-compliance fields data for variation products.
     *
     * @since 3.7.13
     *
     * @param integer $product_id
     *
     * @return void
     */
    public function save_eu_fields_data_for_product_variations( $product_id ) {
        // Checking for nonce security.
        check_ajax_referer( 'save-variations', 'security' );

        // Check permissions again and make sure we have what we need
        if ( ! current_user_can( 'dokandar' ) || empty( $_POST ) || empty( $_POST['product_id'] ) ) {
            die( -1 );
        }

        $variation_id = ! empty( $_POST['id'] ) ? absint( wp_unslash( $_POST['id'] ) ) : '';
        $current_loop = ! empty( $_POST['currentLoop'] ) ? absint( wp_unslash( $_POST['currentLoop'] ) ) : '';

        if ( empty( $variation_id ) || empty( $current_loop ) ) {
            return;
        }

        $data['_unit_product']             = ! empty( $_POST['variable_unit_product'][ $current_loop ] ) ? sanitize_text_field( wp_unslash( $_POST['variable_unit_product'][ $current_loop ] ) ) : '';
        $data['_unit_price_auto']          = ! empty( $_POST['variable_unit_price_auto'][ $current_loop ] ) ? sanitize_text_field( wp_unslash( $_POST['variable_unit_price_auto'][ $current_loop ] ) ) : '';
        $data['_unit_price_regular']       = ! empty( $_POST['variable_unit_price_regular'][ $current_loop ] ) ? sanitize_text_field( wp_unslash( $_POST['variable_unit_price_regular'][ $current_loop ] ) ) : '';
        $data['_sale_price_label']         = ! empty( $_POST['variable_sale_price_label'][ $current_loop ] ) ? sanitize_text_field( wp_unslash( $_POST['variable_sale_price_label'][ $current_loop ] ) ) : '';
        $data['_sale_price_regular_label'] = ! empty( $_POST['variable_sale_price_regular_label'][ $current_loop ] ) ? sanitize_text_field( wp_unslash( $_POST['variable_sale_price_regular_label'][ $current_loop ] ) ) : '';
        $data['_unit_price_sale']          = ! empty( $_POST['variable_unit_price_sale'][ $current_loop ] ) ? sanitize_text_field( wp_unslash( $_POST['variable_unit_price_sale'][ $current_loop ] ) ) : '';
        $data['_sale_price']               = ! empty( $_POST['variable_unit_price_sale'][ $current_loop ] ) ? sanitize_text_field( wp_unslash( $_POST['variable_unit_price_sale'][ $current_loop ] ) ) : '';
        $data['_parent_unit']              = ! empty( $_POST['variable_parent_unit'][ $current_loop ] ) ? sanitize_text_field( wp_unslash( $_POST['variable_parent_unit'][ $current_loop ] ) ) : '';
        $data['_parent_unit_base']         = ! empty( $_POST['variable_parent_unit_base'][ $current_loop ] ) ? sanitize_text_field( wp_unslash( $_POST['variable_parent_unit_base'][ $current_loop ] ) ) : '';
        $data['_unit_base']                = ! empty( $_POST['variable_unit_base'][ $current_loop ] ) ? sanitize_text_field( wp_unslash( $_POST['variable_unit_base'][ $current_loop ] ) ) : '';
        $data['_unit']                     = ! empty( $_POST['variable_unit'][ $current_loop ] ) ? sanitize_text_field( wp_unslash( $_POST['variable_unit'][ $current_loop ] ) ) : '';
        $data['_mini_desc']                = ! empty( $_POST['variable_mini_desc'][ $current_loop ] ) ? sanitize_text_field( wp_unslash( $_POST['variable_mini_desc'][ $current_loop ] ) ) : '';
        $data['_service']                  = ! empty( $_POST['variable_service'][ $current_loop ] ) ? sanitize_text_field( wp_unslash( $_POST['variable_service'][ $current_loop ] ) ) : '';
        $data['delivery_time']             = ! empty( $_POST['variable_delivery_time'][ $current_loop ] ) ? sanitize_text_field( wp_unslash( $_POST['variable_delivery_time'][ $current_loop ] ) ) : '';
        $data['_min_age']                  = ! empty( $_POST['variable_min_age'][ $current_loop ] ) ? sanitize_text_field( wp_unslash( $_POST['variable_min_age'][ $current_loop ] ) ) : '';

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

        // store trusted shop data
        $store_trusted_data['_ts_gtin'] = ! empty( $_POST['variable_ts_gtin'][ $current_loop ] ) ? sanitize_text_field( wp_unslash( $_POST['variable_ts_gtin'][ $current_loop ] ) ) : '';
        $store_trusted_data['_ts_mpn']  = ! empty( $_POST['variable_ts_mpn'][ $current_loop ] ) ? sanitize_text_field( wp_unslash( $_POST['variable_ts_mpn'][ $current_loop ] ) ) : '';

        Helper::save_variable_products_variations_eu_data( $variation_id, $data, $store_trusted_data );
    }
}
