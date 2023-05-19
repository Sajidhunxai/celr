<?php
/**
 * Plugin Name: Frontend Admin
 * Plugin URI: https://wordpress.org/plugins/acf-frontend-form-element/
 * Description: This awesome plugin allows you to easily display admin forms to the frontend of your site so your clients can easily edit content on their own from the frontend.
 * Version:     3.16.7
 * Author:      Shabti Kaplan
 * Author URI:  https://www.dynamiapps.com/
 * Text Domain: acf-frontend-form-element
 * Domain Path: /languages/
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'feap_fs' ) ) {
	function feap_fs() {
		return false;
	}
}

if ( ! class_exists( 'Front_End_Admin' ) ) {
	if ( ! defined( 'FEA_VERSION' ) ) {
		define( 'FEA_VERSION', '3.16.7.1' );
		define( 'FEA_PATH', __FILE__ );
		define( 'FEA_NAME', plugin_basename( __FILE__ ) );
		define( 'FEA_URL', plugin_dir_url( __FILE__ ) );
		define( 'FEA_DIR', __DIR__ );
		define( 'FEA_TITLE', 'Frontend Admin' );
		define( 'FEA_PREFIX', 'frontend_admin' );
		define( 'FEA_NS', 'acf-frontend-form-element' );
		define( 'FEA_PRO', 'https://www.dynamiapps.com/frontend-admin/#pricing' );
		define( 'FEA_PRE', 'fea' );
	}
	/**
	 * Main Frontend Admin Class
	 *
	 * The main class that initiates and runs the plugin.
	 *
	 * @since 1.0.0
	 */
	final class Front_End_Admin {


		/**
		 * Constructor
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 */
		public function __construct() {
			add_action( 'after_setup_theme', array( $this, 'init' ), 11 );
		}



		/**
		 * Initialize the plugin
		 *
		 * Load the plugin only after ACF is loaded.
		 * Checks for basic plugin requirements, if one check fail don't continue,
		 * If all checks have passed load the files required to run the plugin.
		 *
		 * Fired by `plugins_loaded` action hook.
		 *
		 * @since 1.0.0
		 *
		 * @access public
		 */
		public function init() {
			if ( did_action( 'front_end_admin_pro_loaded' ) ) {
				return;
			}

			global $fea_instance;

			if ( isset( $fea_instance ) ) {
				return;
			}

			include_once 'main/plugin.php';
			$fea_instance = new \Frontend_Admin\Plugin();

		}

	}
	new Front_End_Admin();

}

if ( ! function_exists( 'fea_instance' ) ) {
	function fea_instance() {
		global $fea_instance;

		if ( ! isset( $fea_instance ) ) {
			$fea_instance = new stdClass();
		}

		return $fea_instance;
	}
}
