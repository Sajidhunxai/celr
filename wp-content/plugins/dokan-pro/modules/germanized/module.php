<?php

namespace WeDevs\DokanPro\Modules\Germanized;

use WeDevs\Dokan\Traits\ChainableContainer;
use WeDevs\DokanPro\Modules\Germanized\Admin\Settings;
use WeDevs\DokanPro\Modules\Germanized\CustomFields\Admin;
use WeDevs\DokanPro\Modules\Germanized\CustomFields\Billing;
use WeDevs\DokanPro\Modules\Germanized\CustomFields\Dashboard;
use WeDevs\DokanPro\Modules\Germanized\CustomFields\Invoice;
use WeDevs\DokanPro\Modules\Germanized\CustomFields\Registration;
use WeDevs\DokanPro\Modules\Germanized\CustomFields\SingleStore;
use WeDevs\DokanPro\Modules\Germanized\CustomFields\UserProfile;
use WeDevs\DokanPro\Modules\Germanized\Dashboard\Product;
use WeDevs\DokanPro\Modules\Germanized\Dashboard\WCPDF;
use WeDevs\DokanPro\Modules\Germanized\SettingsApi\Store;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

class Module {

    use ChainableContainer;

    /**
     * Load automatically when class initiate
     *
     * @since 3.3.1
     */
    public function __construct() {
        $this->define();
        $this->initiate();
        $this->hooks();
    }

    /**
     * Initiate all classes
     *
     * @since 3.7.13
     *
     * @return void
     */
    public function initiate() {
        new BlockData();
    }

    /**
     * Define Constants
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function define() {
        define( 'DOKAN_GERMANIZED_DIR', dirname( __FILE__ ) );
        define( 'DOKAN_GERMANIZED_INC_DIR', DOKAN_GERMANIZED_DIR . '/includes' );
        define( 'DOKAN_GERMANIZED_ASSETS_DIR', plugins_url( 'assets', __FILE__ ) );
    }

    /**
     * Get plugin path
     *
     * @since 3.3.1
     * @return string
     */
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
     * Init all hooks
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function hooks() {
        add_action( 'plugins_loaded', [ $this, 'set_controllers' ] );
        add_filter( 'dokan_set_template_path', [ $this, 'load_templates' ], 10, 3 );

        // load scripts
        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_scripts' ] );
        }
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_frontend_scripts' ] );
        add_action( 'init', [ $this, 'register_scripts' ] );
    }

    /**
     * Includes all necessary class a functions file
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function set_controllers() {
        if ( is_admin() ) {
            $this->container['settings'] = new Settings();
        }

        if ( Helper::is_germanized_enabled_for_vendors() && ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) ) {
            // load frontend templates
            $this->container['product'] = new Product();
        }

        if ( Helper::is_wpo_wcpdf_enabled_for_vendors() ) {
            $this->container['wpo_wcpdf'] = new WCPDF();
        }

        // load admin custom fields
        $this->container['cf_admin'] = new Admin();

        // load billing custom fields
        $this->container['cf_billing'] = new Billing();

        // load dashboard custom fields
        $this->container['cf_dashboard'] = new Dashboard();

        // load dokan invoice custom fields
        $this->container['cf_invoice'] = new Invoice();

        // load registration form custom metas
        $this->container['cf_registration'] = new Registration();

        // load single store page custom fields
        $this->container['cf_single_store'] = new SingleStore();

        // load user profile custom fields
        $this->container['cf_user_profile'] = new UserProfile();

        // load settings api store page fields.
        new Store();
    }

    /**
     * Set template path for Wholesale
     *
     * @since 3.3.1
     *
     * @return string
     */
    public function load_templates( $template_path, $template, $args ) {
        if ( isset( $args['is_germanized'] ) && $args['is_germanized'] ) {
            return $this->plugin_path() . '/templates';
        }

        return $template_path;
    }

    /**
     * Register scripts
     *
     * @since 3.7.4
     */
    public function register_scripts() {
        list( $suffix, $version ) = dokan_get_script_suffix_and_version();

        wp_register_script( 'dokan-germanized-admin', DOKAN_GERMANIZED_ASSETS_DIR . '/js/script-admin' . $suffix . '.js', array( 'dokan-vue-bootstrap' ), $version, true );
        wp_register_script( 'dokan-germanized', DOKAN_GERMANIZED_ASSETS_DIR . '/js/script-public' . $suffix . '.js', array( 'jquery' ), $version, true );
        wp_register_style( 'dokan-germanized', DOKAN_GERMANIZED_ASSETS_DIR . '/css/style-public' . $suffix . '.css', array(), $version, 'all' );
    }

    /**
     * Load scripts and styles
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function enqueue_frontend_scripts() {
        // check if germanized is enabled for vendors
        if ( ! Helper::is_germanized_enabled_for_vendors() ) {
            return;
        }

        // Use minified libraries if SCRIPT_DEBUG is turned off
        $suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
        $get     = wp_unslash( $_GET ); // phpcs:ignore CSRF ok.
        $product = null;

        if ( dokan_is_seller_dashboard() && isset( $get['product_id'] ) ) {
            $post_id = intval( $get['product_id'] );
            $product = wc_get_product( $post_id );
        }

        // load script only in product edit page
        if ( ! empty( $product ) ) {
            wp_enqueue_script( 'dokan-germanized' );
            wp_enqueue_style( 'dokan-germanized' );
        }

        if ( dokan_is_store_page() ) {
            wp_enqueue_style( 'dokan-germanized' );
        }
    }

    /**
     * Load scripts and styles
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function enqueue_admin_scripts( $hook ) {
        // load vue app inside the parent menu only
        if ( 'toplevel_page_dokan' === $hook ) {
            wp_enqueue_script( 'dokan-germanized-admin' );
        }
    }
}
