<?php

namespace Devnet\PPH\Includes;

use  Devnet\PPH\Admin\PPH_Admin ;
use  Devnet\PPH\Admin\Edit_Product ;
use  Devnet\PPH\Modules\Chart\Chart_Admin ;
use  Devnet\PPH\Modules\Chart\Edit_Product as ChartEditProduct ;
use  Devnet\PPH\Modules\Chart\Chart_Public ;
use  Devnet\PPH\Modules\LowestPrice\LP_Admin ;
use  Devnet\PPH\Modules\LowestPrice\LP_Public ;
use  Devnet\PPH\Modules\PriceAlerts\Notifier ;
use  Devnet\PPH\Modules\PriceAlerts\PA_Admin ;
use  Devnet\PPH\Modules\PriceAlerts\PA_Public ;
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 */
class PPH_Plugin
{
    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      Loader    $loader    Maintains and registers all hooks for the plugin.
     */
    protected  $loader ;
    /**
     * The unique identifier of this plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $plugin_name    The string used to uniquely identify this plugin.
     */
    protected  $plugin_name ;
    /**
     * The current version of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      string    $version    The current version of the plugin.
     */
    protected  $version ;
    /**
     * Define the core functionality of the plugin.
     *
     * Set the plugin name and the plugin version that can be used throughout the plugin.
     * Load the dependencies, define the locale, and set the hooks for the admin area and
     * the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function __construct()
    {
        
        if ( defined( 'PRODUCT_PRICE_HISTORY_VERSION' ) ) {
            $this->version = PRODUCT_PRICE_HISTORY_VERSION;
        } else {
            $this->version = '1.0.0';
        }
        
        $this->plugin_name = 'product-price-history';
        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_chart_module_hooks();
        $this->define_lowest_price_module_hooks();
    }
    
    /**
     * Load the required dependencies for this plugin.
     *
     * Create an instance of the loader which will be used to register the hooks
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function load_dependencies()
    {
        /**
         * The class responsible for orchestrating the actions and filters of the
         * core plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/loader.php';
        /**
         * The class responsible for defining internationalization functionality
         * of the plugin.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/i18n.php';
        /**
         * The class responsible for defining various database helper functions.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/helper.php';
        /**
         * The class wrapper for handling WordPress Settings API.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings-api.php';
        /**
         * The class responsible for defining all actions that occur in the settings panel.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/settings.php';
        /**
         * The class responsible for defining all actions that occur in the admin area.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/admin.php';
        /**
         * The class responsible for defining all actions that occur in edit-product.
         */
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/edit-product.php';
        /**
         * 
         * MODULES
         * 
         */
        // Chart
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'modules/chart/helper.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'modules/chart/admin.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'modules/chart/edit-product.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'modules/chart/public.php';
        // Lowest Price
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'modules/lowest-price/helper.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'modules/lowest-price/admin.php';
        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'modules/lowest-price/public.php';
        $this->loader = new Loader();
    }
    
    /**
     * Define the locale for this plugin for internationalization.
     *
     * Uses the i18n class in order to set the domain and to register the hook
     * with WordPress.
     *
     * @since    1.0.0
     * @access   private
     */
    private function set_locale()
    {
        $plugin_i18n = new i18n();
        $this->loader->add_action( 'init', $plugin_i18n, 'load_plugin_textdomain' );
    }
    
    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks()
    {
        $plugin_admin = new PPH_Admin( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
        $this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );
        $this->loader->add_action(
            'admin_menu',
            $plugin_admin,
            'admin_menu',
            100
        );
        $this->loader->add_filter( 'plugin_action_links_' . DEVNET_PPH_SLUG, $plugin_admin, 'plugin_action_links' );
        $this->loader->add_action( 'before_woocommerce_init', $plugin_admin, 'cot_compatible' );
        $this->loader->add_action( 'wp_ajax_pph_delete_old_data', $plugin_admin, 'delete_old_data' );
        $edit_product = new Edit_Product();
        $this->loader->add_filter(
            'woocommerce_product_data_tabs',
            $edit_product,
            'product_data_tab',
            99,
            1
        );
        $this->loader->add_action( 'woocommerce_product_data_panels', $edit_product, 'product_data_panels' );
        $this->loader->add_action(
            'woocommerce_product_after_variable_attributes',
            $edit_product,
            'variation_panel',
            10,
            3
        );
        $this->loader->add_action(
            'woocommerce_after_product_object_save',
            $edit_product,
            'update_product',
            10,
            2
        );
        $this->loader->add_action( 'wp_ajax_update_pph_db_row', $edit_product, 'update_pph_db_row' );
        $this->loader->add_action(
            'after_delete_post',
            $edit_product,
            'remove_deleted_products_from_pph_table',
            99,
            2
        );
    }
    
    /**
     * Register all of the hooks related to the Chart module.
     *
     * @since    2.0.0
     * @access   private
     */
    private function define_chart_module_hooks()
    {
        $chart_admin = new Chart_Admin( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_filter( 'pph_settings_sections', $chart_admin, 'settings_section' );
        $this->loader->add_filter( 'pph_settings_fields', $chart_admin, 'settings_fields' );
        $edit_product = new ChartEditProduct();
        $this->loader->add_action( 'pph_before_editable_table', $edit_product, 'pph_hide_chart' );
        $this->loader->add_action(
            'woocommerce_process_product_meta',
            $edit_product,
            'save_fields',
            10,
            2
        );
        $chart_public = new Chart_Public( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_action( 'wp_enqueue_scripts', $chart_public, 'enqueue_styles' );
        $this->loader->add_action( 'wp_enqueue_scripts', $chart_public, 'enqueue_scripts' );
        $chart_position = DEVNET_PPH_OPTIONS['chart']['position'] ?? 'woocommerce_product_meta_start';
        if ( 'custom' !== $chart_position ) {
            $this->loader->add_action( $chart_position, $chart_public, 'price_history_output' );
        }
        $this->loader->add_action( 'wp_ajax_pph_variation', $chart_public, 'get_variation_price_history' );
        $this->loader->add_action( 'wp_ajax_nopriv_pph_variation', $chart_public, 'get_variation_price_history' );
    }
    
    /**
     * Register all of the hooks related to the Lowest Price module.
     *
     * @since    2.0.0
     * @access   private
     */
    private function define_lowest_price_module_hooks()
    {
        $lp_admin = new LP_Admin( $this->get_plugin_name(), $this->get_version() );
        $this->loader->add_filter( 'pph_settings_sections', $lp_admin, 'settings_section' );
        $this->loader->add_filter( 'pph_settings_fields', $lp_admin, 'settings_fields' );
        $lp_public = new LP_Public( $this->get_plugin_name(), $this->get_version() );
        //$this->loader->add_action('wp_enqueue_scripts', $lp_public, 'enqueue_scripts');
        $this->loader->add_filter(
            'woocommerce_get_price_html',
            $lp_public,
            'price_html',
            10,
            2
        );
    }
    
    /**
     * Run the loader to execute all of the hooks with WordPress.
     *
     * @since    1.0.0
     */
    public function run()
    {
        $this->loader->run();
    }
    
    /**
     * The name of the plugin used to uniquely identify it within the context of
     * WordPress and to define internationalization functionality.
     *
     * @since     1.0.0
     * @return    string    The name of the plugin.
     */
    public function get_plugin_name()
    {
        return $this->plugin_name;
    }
    
    /**
     * The reference to the class that orchestrates the hooks with the plugin.
     *
     * @since     1.0.0
     * @return    Loader    Orchestrates the hooks of the plugin.
     */
    public function get_loader()
    {
        return $this->loader;
    }
    
    /**
     * Retrieve the version number of the plugin.
     *
     * @since     1.0.0
     * @return    string    The version number of the plugin.
     */
    public function get_version()
    {
        return $this->version;
    }

}