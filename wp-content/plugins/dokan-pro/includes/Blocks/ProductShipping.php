<?php

namespace WeDevs\DokanPro\Blocks;

use WC_Product;
use WP_REST_Request;

defined( 'ABSPATH' ) || exit;

/**
 * Dokan Product Shipping Block data class.
 *
 * @author weDevs
 */
class ProductShipping {

    /**
     * Block section name.
     *
     * @since 3.7.13
     *
     * @var string
     */
    public $section;

    /**
     * Product instance.
     *
     * @since 3.7.13
     *
     * @var WC_Product|null
     */
    private $product;

    /**
     * WP Rest request instance.
     *
     * @since 3.7.13
     *
     * @var WP_Rest_Request|null
     */
    private $request;

    /**
     * Constructor class.
     *
     * @since 3.7.13
     */
    public function __construct() {
        $this->section = 'shipping_tax';
        $this->product = null;
        $this->request = null;

        // Configuration
        add_filter( 'dokan_get_product_block_configurations', [ $this, 'get_block_configurations' ] );

        // Set and Get
        add_filter( 'dokan_rest_get_product_block_data', [ $this, 'get_block_data' ], 10, 3 );
        add_action( 'dokan_rest_insert_product_object', [ $this, 'set_block_data' ], 10, 3 );
    }

    /**
     * Get product block configurations.
     *
     * @since 3.7.13
     *
     * @param $configuration array
     *
     * @return array
     */
    public function get_block_configurations( $configuration = [] ) {
        // Shipping related configurations.
        $dokan_shipping_option  = get_option( 'woocommerce_dokan_product_shipping_settings' );
        $is_shipping_disabled   = false;
        $dokan_shipping_enabled = ( isset( $dokan_shipping_option['enabled'] ) ) ? $dokan_shipping_option['enabled'] : 'yes';

        if ( 'sell_digital' === dokan_pro()->digital_product->get_selling_product_type() ) {
            $is_shipping_disabled = true;
        }

        $configuration[ $this->section ] = [
            'wc_shipping_enabled'    => get_option( 'woocommerce_calc_shipping' ) === 'yes' ? true : false,
            'wc_tax_enabled'         => get_option( 'woocommerce_calc_taxes' ) === 'yes' ? true : false,
            'is_shipping_disabled'   => $is_shipping_disabled,
            'dokan_shipping_enabled' => 'yes' === $dokan_shipping_enabled ? true : false,
            'store_shipping_enabled' => get_user_meta( get_current_user_id(), '_dps_shipping_enable', true ) === 'yes' ? true : false,
            'weight_placeholder'     => sprintf(
                // translators: 1) Woocommerce weight unit name
                __( 'weight (%s)', 'dokan' ), get_option( 'woocommerce_weight_unit' )
            ),
            'length_placeholder'     => sprintf(
                // translators: 1) Woocommerce length unit name
                __( 'length (%s)', 'dokan' ), get_option( 'woocommerce_dimension_unit' )
            ),
            'width_placeholder'      => sprintf(
                // translators: 1) Woocommerce width unit name
                __( 'width (%s)', 'dokan' ), get_option( 'woocommerce_dimension_unit' )
            ),
            'height_placeholder'     => sprintf(
                // translators: 1) Woocommerce height unit name
                __( 'height (%s)', 'dokan' ), get_option( 'woocommerce_dimension_unit' )
            ),
            'classes'                => $this->get_product_shipping_classes(),
            'processing_times'       => dokan_get_shipping_processing_times(),
            'tax_statuses'           => [
                'taxable'   => __( 'Taxable', 'dokan' ),
                'shipping'  => __( 'Shipping only', 'dokan' ),
                'none'      => _x( 'None', 'Tax status', 'dokan' ),
            ],
            'tax_classes'            => dokan_pro()->products->get_tax_class_option(),
        ];

        return $configuration;
    }

    /**
     * Get product shipping classes formatted for select2.
     *
     * @since 3.7.13
     *
     * @return array
     */
    private function get_product_shipping_classes() {
        $shipping = WC()->shipping->get_shipping_classes();
        $classes  = [];

        if ( ! empty( $shipping ) ) {
            foreach ( $shipping as $shipping ) {
                $classes[ $shipping->slug ] = $shipping->name;
            }
        }

        return $classes;
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

        $user_id                 = dokan_get_current_user_id();
        $_additional_price       = $product->get_meta( '_additional_price', true, $context );
        $_additional_qty         = $product->get_meta( '_additional_qty', true, $context );
        $_processing_time        = $product->get_meta( '_dps_processing_time', true, $context );

        $dps_shipping_type_price = get_user_meta( $user_id, '_dps_shipping_type_price', true );
        $dps_additional_qty      = get_user_meta( $user_id, '_dps_additional_qty', true );
        $dps_pt                  = get_user_meta( $user_id, '_dps_pt', true );

        $block[ $this->section ] = [
            '_disable_shipping' => 'yes' === $product->get_meta( '_disable_shipping' ),
            '_weight'           => $product->get_weight( $context ),
            '_length'           => $product->get_length( $context ),
            '_width'            => $product->get_width( $context ),
            '_height'           => $product->get_height( $context ),
            'shipping_class'    => $product->get_shipping_class(),

            // Shipping Override fields than the store default.
            '_overwrite_shipping'  => 'yes' === $product->get_meta( '_overwrite_shipping', true, $context ),
            '_additional_price'    => $_additional_price ? $_additional_price : $dps_shipping_type_price,
            '_additional_qty'      => $_additional_qty ? $_additional_qty : $dps_additional_qty,
            '_dps_processing_time' => $_processing_time ? $_processing_time : $dps_pt,

            // Tax
            '_tax_status' => $product->get_tax_status( $context ),
            '_tax_class'  => $product->get_tax_class( $context ),
        ];

        return $block;
    }

    /**
     * Save product-shipping data.
     *
     * @since 3.7.13
     *
     * @param WC_Product      $product  Inserted object.
     * @param WP_REST_Request $request  Request object.
     * @param boolean         $creating True when creating object, false when updating.
     *
     * @return void
     */
    public function set_block_data( $product, $request, $creating = true ) {
        if ( ! $product instanceof WC_Product ) {
            return;
        }

        $this->product = $product;
        $this->request = $request;

        // Set dimensions for variable and shippable products.
        $this->set_dimensions();

        // Set shipping disable or enable.
        $this->product->update_meta_data( '_disable_shipping', ( isset( $request['_disable_shipping'] ) && $request['_disable_shipping'] ) ? 'yes' : 'no' );

        // Override store's default shipping data for this product.
        $this->override_store_default();

        // Set tax status and class.
        $this->product->set_tax_class( isset( $request['_tax_class'] ) ? $request['_tax_class'] : '' );
        $this->product->set_tax_status( isset( $request['_tax_status'] ) ? $request['_tax_status'] : '' );

        $this->set_shipping_class();
        $this->product->save();
    }

    /**
     * Set product dimensions and return product instance.
     *
     * @since 3.7.13
     *
     * @param WC_Product      $product
     * @param WP_Rest_Request $request
     *
     * @return void
     */
    private function set_dimensions() {
        if ( $this->product->is_virtual() ) {
            $this->set_virtual_product_dimensions();
        }

        $this->set_shippable_product_dimensions();
    }

    /**
     * Set virtual product dimensions.
     *
     * @since 3.7.13
     *
     * @param WC_Product $product
     *
     * @return void
     */
    private function set_virtual_product_dimensions() {
        $this->product->set_weight( '' );
        $this->product->set_length( '' );
        $this->product->set_width( '' );
        $this->product->set_height( '' );
    }

    /**
     * Set shippable product dimensions.
     *
     * @since 3.7.13
     *
     * @return void
     */
    private function set_shippable_product_dimensions() {
        if ( isset( $this->request['_weight'] ) ) {
            $this->product->set_weight( ( '' === $this->request['_weight'] ) ? '' : wc_format_decimal( $this->request['_weight'] ) );
        }

        if ( isset( $this->request['_length'] ) ) {
            $this->product->set_length( ( '' === $this->request['_length'] ) ? '' : wc_format_decimal( $this->request['_length'] ) );
        }

        if ( isset( $this->request['_width'] ) ) {
            $this->product->set_width( ( '' === $this->request['_width'] ) ? '' : wc_format_decimal( $this->request['_width'] ) );
        }

        if ( isset( $this->request['_height'] ) ) {
            $this->product->set_height( ( '' === $this->request['_height'] ) ? '' : wc_format_decimal( $this->request['_height'] ) );
        }
    }

    /**
     * Override store's default shipping data for this product.
     *
     * @since 3.7.13
     *
     * @return void
     */
    private function override_store_default() {
        if ( isset( $this->request['_overwrite_shipping'] ) && ( 'yes' === $this->request['_overwrite_shipping'] || $this->request['_overwrite_shipping'] ) ) {
            $this->product->update_meta_data( '_overwrite_shipping', 'yes' );
        } else {
            $this->product->update_meta_data( '_overwrite_shipping', 'no' );
        }

        $this->product->update_meta_data( '_additional_price', isset( $this->request['_additional_price'] ) ? $this->request['_additional_price'] : '' );
        $this->product->update_meta_data( '_additional_qty', isset( $this->request['_additional_qty'] ) ? $this->request['_additional_qty'] : '' );
        $this->product->update_meta_data( '_dps_processing_time', isset( $this->request['_dps_processing_time'] ) ? $this->request['_dps_processing_time'] : '' );
    }

    /**
     * Set product shipping class.
     *
     * @since 3.7.13
     *
     * @return void
     */
    private function set_shipping_class() {
        $class_slug   = $this->request['shipping_class'];
        $product_type = $this->product->get_type();
        $term         = get_term_by( 'slug', $class_slug, 'product_shipping_class' );

        if ( empty( $term ) || is_wp_error( $term ) || 'external' !== $product_type ) {
            return;
        }

        $product_shipping_class = absint( $term->term_id );
        wp_set_object_terms( $this->product->get_id(), $product_shipping_class, 'product_shipping_class' );
    }
}
