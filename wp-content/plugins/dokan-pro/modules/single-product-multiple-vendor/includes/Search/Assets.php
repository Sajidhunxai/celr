<?php

namespace WeDevs\DokanPro\Modules\SPMV\Search;

use WeDevs\Dokan\ProductCategory\Helper as CategoryHelper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Assets.
 *
 * @since 3.5.2
 */
class Assets {

    /**
     * Constructor.
     *
     * @since 3.5.2
     *
     * @return void
     */
    public function __construct() {
        add_action( 'init', [ $this, 'register' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue' ] );
    }

    /**
     * Register assets.
     *
     * @since 3.5.2
     *
     * @return void
     */
    public function register() {
        // Use minified libraries if SCRIPT_DEBUG is turned off
        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

        wp_register_style( 'dokan-spmv-search-style', DOKAN_SPMV_ASSETS_DIR . '/css/product-search' . $suffix . '.css', [], DOKAN_PRO_PLUGIN_VERSION );
        wp_register_script( 'dokan-spmv-search-js', DOKAN_SPMV_ASSETS_DIR . '/js/product-search' . $suffix . '.js', [ 'jquery' ], DOKAN_PRO_PLUGIN_VERSION, true );
    }

    /**
     * Enqueue assets.
     *
     * @since 3.5.2
     *
     * @return void
     */
    public function enqueue() {
        global $wp;

        if (
            isset( $wp->query_vars['products-search'] )
            || isset( $wp->query_vars['products'] )
            || isset( $wp->query_vars['new-product'] )
            || ( isset( $wp->query_vars['booking'] ) && 'new-product' === $wp->query_vars['booking'] )
            || isset( $wp->query_vars['new-auction-product'] )
        ) {
            wp_enqueue_style( 'dokan-spmv-search-style' );
            wp_enqueue_script( 'dokan-spmv-search-js' );
            wp_enqueue_style( 'dokan-timepicker' );
            wp_enqueue_style( 'dokan-date-range-picker' );
        }

        if ( dokan_is_seller_dashboard() && isset( $wp->query_vars['products-search'] ) ) { // phpcs:ignore
            CategoryHelper::enqueue_and_localize_dokan_multistep_category();
        }
    }
}
