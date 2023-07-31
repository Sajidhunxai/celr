<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       http://plugins.db-dzine.com
 * @since      1.0.0
 *
 * @package    WooCommerce_Single_Variations
 * @subpackage WooCommerce_Single_Variations/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    WooCommerce_Single_Variations
 * @subpackage WooCommerce_Single_Variations/includes
 * @author     Daniel Barenkamp <support@db-dzine.com>
 */
class WooCommerce_Single_Variations {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      WooCommerce_Single_Variations_Loader    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */

	public function __construct($version) 
	{
		$this->plugin_name = 'woocommerce-single-variations';
		$this->version = $version;

		$this->load_dependencies();
		$this->set_locale();
		$this->define_admin_hooks();
		$this->define_public_hooks();

	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - WooCommerce_Single_Variations_Loader. Orchestrates the hooks of the plugin.
	 * - WooCommerce_Single_Variations_i18n. Defines internationalization functionality.
	 * - WooCommerce_Single_Variations_Admin. Defines all hooks for the admin area.
	 * - WooCommerce_Single_Variations_Public. Defines all hooks for the public side of the site.
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
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-single-variations-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-woocommerce-single-variations-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-woocommerce-single-variations-admin.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-woocommerce-single-variations-public.php';

		$this->loader = new WooCommerce_Single_Variations_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the WooCommerce_Single_Variations_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() 
	{
		$this->plugin_i18n = new WooCommerce_Single_Variations_i18n();
		$this->loader->add_action( 'plugins_loaded', $this->plugin_i18n, 'load_plugin_textdomain' );
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
		$this->plugin_admin = new WooCommerce_Single_Variations_Admin( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'plugins_loaded', $this->plugin_admin, 'load_redux' );
		$this->loader->add_action( 'init', $this->plugin_admin, 'init' );

		$this->loader->add_action( 'admin_enqueue_scripts', $this->plugin_admin, 'enqueue_scripts', 20);

		$this->loader->add_action( 'woocommerce_after_product_ordering', $this->plugin_admin, 'reset_variation_menu_order', 10, 2);
 
 		if(isset($_GET['update-variations'])) {
            $this->loader->add_action( 'init', $this->plugin_admin, 'update_variations', 10 );
        }
        $this->loader->add_action( 'wp_ajax_init_single_variations', $this->plugin_admin, 'ajax_update_variations', 10 );		
        $this->loader->add_action( 'wp_ajax_reset_single_variations', $this->plugin_admin, 'ajax_reset_variations', 10 );		

 		if(isset($_GET['reset-variations'])) {
            $this->loader->add_action( 'init', $this->plugin_admin, 'reset_variations', 10 );
        }

 		if(isset($_GET['reset-variation-transients'])) {
            $this->loader->add_action( 'init', $this->plugin_admin, 'reset_transients', 10 );
        }

	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() 
	{
		$this->public = new WooCommerce_Single_Variations_Public( $this->get_plugin_name(), $this->get_version() );
		$this->loader->add_action( 'init', $this->public, 'init' );
		$this->loader->add_action( 'wp_enqueue_scripts', $this->public, 'enqueue_scripts');
		$this->loader->add_filter( 'woocommerce_post_class', $this->public, 'addVariationClasses', 10, 2);

		$this->loader->add_action( 'wp_ajax_nopriv_woocommerce_get_variation_title', $this->public, 'ajax_get_variation_title');
        $this->loader->add_action( 'wp_ajax_woocommerce_get_variation_title', $this->public, 'ajax_get_variation_title');
		
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
	 * Get Options
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https://plugins.db-dzine.com
	 * @param   [type]                       $option [description]
	 * @return  [type]                               [description]
	 */
    protected function get_option($option)
    {
    	if(!isset($this->options)) {
    		return false;
    	}

    	if(!is_array($this->options)) {
    		return false;
    	}
    	
    	if(!array_key_exists($option, $this->options))
    	{
    		return false;
    	}
    	return $this->options[$option];
    }

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @since     1.0.0
	 * @return    WooCommerce_Single_Variations_Loader    Orchestrates the hooks of the plugin.
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}

}
