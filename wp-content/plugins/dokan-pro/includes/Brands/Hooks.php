<?php

namespace WeDevs\DokanPro\Brands;

class Hooks {

    public function __construct() {
        add_action( 'yith_wcbr_init', [ $this, 'init' ], 11 );
    }

    /**
     * Load brand after Dokan Pro init classes
     *
     * @since 2.9.7
     *
     * @return void
     */
    public function init() {
        add_action( 'init', [ $this, 'load_dokan_brands' ] );
    }

    /**
     * Load functionalities
     *
     * @since 3.0.2
     *
     * @return void
     */
    public function load_dokan_brands() {
        dokan_pro()->brands->set_is_active( true );

        if ( class_exists( 'YITH_WCBR_Premium' ) ) {
            dokan_pro()->brands->set_is_premium_active( true );
        }

        dokan_pro()->brands->set_settings( [
            'mode' => dokan_get_option( 'product_brands_mode', 'dokan_selling', 'single' ),
        ] );

        add_filter( 'dokan_settings_fields', [ AdminSettings::class, 'add_admin_settings_fields' ], 11, 2 );
        add_action( 'dokan_new_product_after_product_tags', [ FormFields::class, 'new_product_form_field' ] );
        add_action( 'dokan_product_edit_after_product_tags', [ FormFields::class, 'product_edit_form_field' ], 10, 2 );
        add_action( 'dokan_new_product_added', [ FormFields::class, 'set_product_brands' ], 10, 2 );
        add_action( 'dokan_product_updated', [ FormFields::class, 'set_product_brands' ], 10, 2 );
        add_action( 'dokan_product_duplicate_after_save', [ $this, 'set_duplicate_product_brands' ], 10, 2 );
        add_action( 'dokan_spmv_create_clone_product', [ $this, 'set_spmv_duplicate_product_brands' ], 10, 3 );
    }

    /**
     * Set brand for duplicate products
     *
     * @param Object $duplicate
     * @param Object $product
     */
    public function set_duplicate_product_brands( $clone_product, $product ) {
        $brands_ids = [];
        $brands     = wp_get_object_terms( $product->get_id(), dokan_pro()->brands->get_taxonomy() );

        if ( count( $brands ) > 0 ) {
            foreach ( $brands as $brand ) {
                $brands_ids[] = $brand->term_id;
            }

            wp_set_object_terms( $clone_product->get_id(), $brands_ids, dokan_pro()->brands->get_taxonomy() );
        }
    }

    /**
     * Set brand for Single Product MultiVendor duplicate products
     *
     * @param Object $duplicate
     * @param Object $product
     */
    public function set_spmv_duplicate_product_brands( $clone_product_id, $product_id, $map_id ) {
        $brands_ids = [];
        $brands     = wp_get_object_terms( $product_id, dokan_pro()->brands->get_taxonomy() );

        if ( count( $brands ) > 0 ) {
            foreach ( $brands as $brand ) {
                $brands_ids[] = $brand->term_id;
            }

            wp_set_object_terms( $clone_product_id, $brands_ids, dokan_pro()->brands->get_taxonomy() );
        }
    }
}
