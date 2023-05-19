<?php

/**
* Product addon override from Vendor product
*
* @package dokan|Product Addon Moduel
*/
class Dokan_Product_Addon_Vendor_Product {

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'dokan_product_edit_after_main', [ $this, 'add_addons_section' ], 15, 2 );
        add_action( 'dokan_product_updated', [ $this, 'save_addon_options' ], 35, 2 );
        add_action( 'dokan_update_auction_product', [ $this, 'save_addon_options' ], 35, 2 );
    }

    /**
     * Initializes the Dokan_Product_Addon_Vendor_Product() class
     *
     * Checks for an existing Dokan_Product_Addon_Vendor_Product() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Dokan_Product_Addon_Vendor_Product();
        }

        return $instance;
    }

    /**
     * Product add on sections
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_addons_section( $post, $post_id ) {
        dokan_get_template_part( 'product-addon/html-product-addons', '', array(
            'is_product_addon' => true,
            'post'             => $post,
            'post_id'          => $post_id
        ) );
    }

    /**
     * Save product add on options
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function save_addon_options( $post_id, $postdata ) {
        if ( ! dokan_is_user_seller( get_current_user_id() ) ) {
            return;
        }

        if ( empty( $post_id ) ) {
            return;
        }

        $product = wc_get_product( $post_id );
        $product_addons = dokan_pa_get_posted_product_addons( $postdata );
        $product_addons_exclude_global = isset( $postdata['_product_addons_exclude_global'] ) ? 1 : 0;
        $product->update_meta_data( '_product_addons', $product_addons );
        $product->update_meta_data( '_product_addons_exclude_global', $product_addons_exclude_global );
        $product->save();
    }
}
