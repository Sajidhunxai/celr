<?php

/**
* Admin class for store reviews
*
* @since 1.0.0
*/
class DSR_Admin {

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'dokan_admin_menu', array( $this, 'load_store_review_menu' ) );
        add_filter( 'dokan-admin-routes', array( $this, 'vue_admin_routes' ) );
        add_action( 'dokan-vue-admin-scripts', array( $this, 'vue_admin_enqueue_scripts' ) );
        add_action( 'init', array( $this, 'register_scripts' ) );
    }

    /**
     * Initializes the DSR_Admin() class
     *
     * Checks for an existing DSR_Admin() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( !$instance ) {
            $instance = new DSR_Admin();
        }

        return $instance;
    }

    /**
     * Load store review menu
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_store_review_menu( $capability ) {
        if ( current_user_can( $capability ) ) {
            global $submenu;

            $title = esc_html__( 'Store Reviews', 'dokan' );
            $slug  = 'dokan';

            $submenu[ $slug ][] = [ $title, $capability, 'admin.php?page=' . $slug . '#/store-reviews' ];
        }
    }

    /**
     * Load store review routes
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function vue_admin_routes( $routes ) {
        $routes[] = [
            'path'      => '/store-reviews',
            'name'      => 'StoreReviews',
            'component' => 'StoreReviews'
        ];

        return $routes;
    }

    /**
     * Register Scripts
     *
     * @since 3.7.4
     */
    public function register_scripts() {
        list( $suffix, $version ) = dokan_get_script_suffix_and_version();

        wp_register_style( 'dsr-admin-css', DOKAN_SELLER_RATINGS_PLUGIN_ASSEST . '/css/admin' . $suffix . '.css', false, $version );
        wp_register_script( 'dsr-admin', DOKAN_SELLER_RATINGS_PLUGIN_ASSEST . '/js/admin' . $suffix . '.js', array( 'jquery', 'dokan-vue-vendor', 'dokan-vue-bootstrap' ), $version, true );
    }

    /**
     * Load admin vue scripts
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function vue_admin_enqueue_scripts() {
        wp_enqueue_style( 'dsr-admin-css' );
        wp_enqueue_script( 'dsr-admin' );
    }
}

$dsr_admin = DSR_Admin::init();
