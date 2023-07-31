<?php
/*
Plugin Name: LoftLoader
Plugin URI: http://www.loftocean.com/
Description: An easy to use plugin to add an animated preloader to your website with fully customisations.
Version: 2.4.0
Requires PHP: 5.6
Requires at least: 5.0
Author: Loft.Ocean
Author URI: http://www.loftocean.com/
Text Domain: loftloader
License: GPLv2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/


/**
 * LoftLoader main file
 *
 * @package   LoftLoader
 * @link	  http://www.loftocean.com/
 * @author	  Suihai Huang from Loft Ocean Team
 */

// Not allowed by directly accessing.
if ( ! defined( 'ABSPATH' ) ) {
	die( esc_html__( 'Access not allowed!', 'loftloader' ) );
}

if ( ! class_exists( 'LoftLoader' ) ) {
	/**
	 * Define the constant used in this plugin
	 */
	define( 'LOFTLOADER_ROOT', dirname( __FILE__ ) . '/' );
	define( 'LOFTLOADER_NAME', plugin_basename( __FILE__  ) );
	define( 'LOFTLOADER_URI',  plugin_dir_url( __FILE__ ) );
	define( 'LOFTLOADER_ASSET_VERSION', '2022112601' );

	class LoftLoader {
		public function __construct() {
			load_plugin_textdomain( 'loftloader' );

			$this->load_upgrade();
			$this->load_customize();

			add_action( 'wp', array( $this, 'load_front' ) );
			add_action( 'admin_menu', array( $this, 'admin_menu' ) );
			add_filter( 'plugin_action_links_' . LOFTLOADER_NAME, array( $this, 'plugin_action_links' ) );
		}
		/**
		* For LoftLoader customize, load the customize related functions
		*/
		function load_upgrade() {
			require_once LOFTLOADER_ROOT . 'inc/class-loftloader-upgrade.php';
		}
		/**
		* For LoftLoader upgrade, load the upgrade related functions
		*/
		function load_customize() {
			require_once LOFTLOADER_ROOT . 'inc/class-loftloader-customize.php';
		}

		/**
		* For LoftLoader front, load the front end related functions
		*/
		function load_front() {
			require_once LOFTLOADER_ROOT . 'inc/class-loftloader-front.php';
		}

		/**
		* Add new setting link to loftloader
		*/
		function plugin_action_links( $links ) {
			$customize_url = $this->get_customize_uri();
			$action_links = array(
				'settings' => '<a href="' . $customize_url . '" title="' . esc_attr__('View LoftLoader Settings', 'loftloader') . '">' . esc_html__('Settings', 'loftloader') . '</a>'
			);
			return array_merge( $action_links, $links );
		}

		/**
		* Add an admin menu for loftloader
		*/
		function admin_menu() {
			global $submenu;
			$customize_url = $this->get_customize_uri();
			$submenu['options-general.php'][] = array( esc_html__( 'LoftLoader Lite', 'loftloader' ), 'manage_options', $customize_url, 'hide-if-no-customize' );
		}

		/**
		* Helper function to get loftloader customize url
		* @return url loftloader customize uri
		*/
		function get_customize_uri() {
			$return_url = '';
			if ( ! empty( $_SERVER['REQUEST_URI'] ) ) {
				$return_url = urlencode( sanitize_text_field( wp_unslash( $_SERVER['REQUEST_URI'] ) ) );
			}
			return add_query_arg( array('return' => $return_url, 'plugin' => 'loftloader-lite' ), 'customize.php' );
		}
	}

	// Init loftloader lite
	add_action( 'after_setup_theme', 'loftloader_init' );
	function loftloader_init() {
		if ( ! class_exists( 'LoftLoader_Pro' ) ) {
			new LoftLoader();
		}
	}

	add_action( 'plugins_loaded', 'loftloader_any_page' );
	function loftloader_any_page() {
		if ( ! class_exists( 'LoftLoader_Pro' ) ) {
			$enable_any_page = get_option( 'loftloader_enable_any_page', '' );
			if ( $enable_any_page === 'on' ) {
				require_once LOFTLOADER_ROOT . 'inc/any-page/class-loftloader-any-page.php';
			}
		}
	}

	// Remove widget panels
	add_filter( 'customize_loaded_components', 'loftloader_remove_widget_panels', 1000 );
	function loftloader_remove_widget_panels( $components ) {
		if ( ! class_exists( 'LoftLoader_Pro' ) && ( isset( $_GET['plugin'] ) && ( $_GET['plugin'] === 'loftloader-lite' ) ) ) {
			foreach ( $components as $i => $c ) {
				if ( false !== $i ) {
					unset( $components[ $i ] );
				}
			}
		}
		return $components;
	}

	/**
	* Helper function to test on loftloader customize page
	*
	* @return boolean
	*/
	function loftloader_is_customize() {
		global $wp_customize;
		return ( isset($_GET['plugin'] ) && ( $_GET['plugin'] === 'loftloader-lite') ) || ( isset( $wp_customize ) && $wp_customize->is_preview() && ! is_admin() ) || defined( 'DOING_AJAX' );
	}
}

/**
 * Deletes saved data for the plugin unless setting to preserve
 * settings is enabled
 *
 * @since  2.0 custom tables, custom images, and image directory deleted
 * @since  1.0
 */
function loftloader_plugin_uninstall() {
	if ( ! current_user_can( 'activate_plugins' ) ) {
		return;
	}

	if ( 'on' != get_option( 'loftloader_remove_settings', '' ) ) {
		return;
	}

	global $wpdb;
	$table_name = esc_sql( $wpdb->prefix . "postmeta" );
	$result = $wpdb->query("
		DELETE
		FROM $table_name
		WHERE meta_key = 'loftloader_page_shortcode';"
	);

	delete_option( 'loftloader_lite_version' );
	delete_option( 'loftloader_main_switch' );
	delete_option( 'loftloader_show_range'  );
	delete_option( 'loftloader_bg_color' );
	delete_option( 'loftloader_bg_opacity' );
	delete_option( 'loftloader_bg_animation' );
	delete_option( 'loftloader_loader_type' );
	delete_option( 'loftloader_loader_color' );
	delete_option( 'loftloader_custom_img' );
	delete_option( 'loftloader_img_width' );
	delete_option( 'loftloader_show_close_timer' );
	delete_option( 'loftloader_show_close_tip' );
	delete_option( 'loftloader_max_load_time' );
	delete_option( 'loftloader_inline_js' );
	delete_option( 'loftloader_enable_any_page' );
	delete_option( 'loftloader_remove_settings' );
}

register_deactivation_hook( __FILE__, 'loftloader_plugin_uninstall' );
