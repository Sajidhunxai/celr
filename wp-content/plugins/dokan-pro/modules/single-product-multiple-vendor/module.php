<?php

namespace WeDevs\DokanPro\Modules\SPMV;

use WeDevs\DokanPro\Modules\SPMV\Search\Assets;
use WeDevs\DokanPro\Modules\SPMV\Search\Dashboard;

class Module {

    /**
     * Load automatically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->define();

        $this->includes();

        $this->initiate();

        $this->hooks();
    }

    /**
     * Hooks
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function define() {
        define( 'DOKAN_SPMV_DIR', dirname( __FILE__ ) );
        define( 'DOKAN_SPMV_INC_DIR', DOKAN_SPMV_DIR . '/includes' );
        define( 'DOKAN_SPMV_ASSETS_DIR', plugins_url( 'assets', __FILE__ ) );
        define( 'DOKAN_SPMV_VIEWS', DOKAN_SPMV_DIR . '/views' );
    }

    /**
     * Includes all necessary class a functions file
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function includes() {
        require_once DOKAN_SPMV_INC_DIR . '/functions.php';
        require_once DOKAN_SPMV_INC_DIR . '/product-duplicator.php';

        if ( is_admin() ) {
            require_once DOKAN_SPMV_INC_DIR . '/admin.php';
            require_once DOKAN_SPMV_INC_DIR . '/products-admin.php';
        }

        require_once DOKAN_SPMV_INC_DIR . '/products.php';
        require_once DOKAN_SPMV_INC_DIR . '/product-visibility.php';

        require_once DOKAN_SPMV_INC_DIR . '/Search/Assets.php';
        require_once DOKAN_SPMV_INC_DIR . '/Search/Dashboard.php';
    }

    /**
     * Initiate all classes
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function initiate() {
        if ( is_admin() ) {
            new \Dokan_SPMV_Admin();
        }

        new \Dokan_SPMV_Products();
        new Assets();
        new Dashboard();

        $enable_option = dokan_get_option( 'enable_pricing', 'dokan_spmv', 'off' );
        if ( 'off' === $enable_option ) {
            return;
        }

        if ( is_admin() ) {
            new \Dokan_SPMV_Products_Admin();
        }

        new \Dokan_SPMV_Product_Visibility();
    }

    /**
     * Init all hooks
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function hooks() {
        add_action( 'dokan_activated_module_spmv', [ $this, 'activate' ] );
        add_filter( 'dokan_query_var_filter', [ $this, 'add_search_endpoints' ] );
        add_action( 'woocommerce_flush_rewrite_rules', [ $this, 'flush_rewrite_rules' ] );
        add_action( 'dokan_product_duplicate_after_save', [ $this, 'update_duplicate_product_spmv' ], 10, 2 );
        add_filter( 'dokan_rest_api_class_map', [ $this, 'rest_api_class_map' ] );
    }

    /**
     * Create Mapping table for product and vendor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function activate() {
        global $wpdb;

        $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_product_map` (
                `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
                `map_id` bigint(20) DEFAULT NULL,
                `product_id` bigint(20) DEFAULT NULL,
                `seller_id` bigint(20) DEFAULT NULL,
                `is_trash` tinyint(4) NOT NULL DEFAULT '0',
                `visibility` tinyint(1) DEFAULT '1',
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;";

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $sql );

        $this->flush_rewrite_rules();
    }

    /**
     * Update duplicate product if exists multi vendor
     *
     * @since 3.1.2
     *
     * @param array $clone_product
     * @param array $product
     *
     * @return void
     */
    public function update_duplicate_product_spmv( $clone_product, $product ) {
        if ( ! isset( $clone_product ) ) {
            return;
        }

        $map_id = get_post_meta( $clone_product->get_id(), '_has_multi_vendor', true );

        if ( $map_id ) {
            update_post_meta( $clone_product->get_id(), '_has_multi_vendor', '' );
        }
    }

    /**
     * Add search endpoint to vendor dashboard.
     *
     * @sience 3.5.2
     *
     * @param array $query_var
     *
     * @return array
     */
    public function add_search_endpoints( $query_var ) {
        $query_var[] = 'products-search';

        return $query_var;
    }

    /**
     * Flush rewrite rules
     *
     * @since 3.5.2
     *
     * @return void
     */
    public function flush_rewrite_rules() {
        dokan()->rewrite->register_rule();
        flush_rewrite_rules( true );
    }

    /**
     * REST API classes Mapping
     *
     * @since 3.7.19
     *
     * @return array
     */
    public function rest_api_class_map( $class_map ) {
        $class_map[ DOKAN_SPMV_DIR . '/REST/SpmvProductController.php' ] = '\WeDevs\DokanPro\Modules\SPMV\REST\SpmvProductController';

        return $class_map;
    }
}
