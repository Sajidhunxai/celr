<?php
if ( ! class_exists('LoftLoader_Any_Page_Filter' ) ) {
	class LoftLoader_Any_Page_Filter {
		private $defaults = array();
		private $page_settings = array();
		private $page_enabled = false;
		private $is_customize = false;
		public function __construct() {
			add_filter( 'loftloader_get_loader_setting', array( $this, 'get_loader_setting' ), 10, 2 );
			add_filter( 'loftloader_loader_enabled', array( $this, 'loader_enabled' ) );
			add_filter( 'loftloader_loader_attributes', array( $this, 'data_attributes' ) );
			add_action( 'loftloader_settings', array( $this, 'loader_settings' ) );
		}
		/**
		* @description get the plugin settings
		*/
		public function loader_settings() {
			global $wp_customize, $loftloader_default_settings;
			$this->is_customize = isset( $wp_customize ) ? true : false;
			if ( $this->is_any_page_extension_enabled() ) {
				$page = $this->get_queried_object();
				if ( ( $atts = $this->get_loader_attributes( $page->ID ) ) !== false ) {
					if ( isset( $atts['loftloader_show_close_tip'] ) ) {
						$atts['loftloader_show_close_tip'] = base64_decode( $atts['loftloader_show_close_tip'] );
					}
					$this->page_settings = array_intersect_key( $atts, $loftloader_default_settings );
					$this->page_enabled = isset( $atts['loftloader_main_switch'] ) && ( $atts['loftloader_main_switch'] === 'on' );
				}
			}
		}
		/**
		* @description helper function to get shortcode attributes
		*/
		private function get_loader_attributes( $page_id ) {
			$loader = get_post_meta( $page_id, 'loftloader_page_shortcode', true );
			$loader = trim( $loader );
			if ( ! empty( $loader ) ) {
				$loader = substr( $loader, 1, -1 );
				return shortcode_parse_atts( $loader );
			}
			return false;
		}
		/**
		* Helper function to test whether show loftloader
		* @return boolean return true if loftloader enabled and display on current page, otherwise false
		*/
		public function loader_enabled() {
			return $this->page_enabled;
		}
		/**
		* Helper function get setting option
		*/
		public function get_loader_setting( $setting_value, $setting_id ) {
			return ( $this->page_enabled && !$this->is_customize && isset( $this->page_settings[ $setting_id ] ) ) ? $this->page_settings[ $setting_id ] : $setting_value;
		}

		/**
		* Help function to test if any page extension enabled on current page
		*/
		protected function is_any_page_extension_enabled() {
			$is_fromt_home_page = ( is_front_page() || is_home() ) && ( get_option('show_on_front', false ) == 'page' );
			$is_shop_page = $this->is_woocommerce_shop();
			return $is_fromt_home_page || $is_shop_page || is_page();
		}
		/**
		* Get queried page object
		*/
		protected function get_queried_object() {
			if ( $this->is_woocommerce_shop() ) {
				$page_id = wc_get_page_id( 'shop' );
				return get_page( $page_id );
			} else {
				return get_queried_object();
			}
		}
		/**
		* Condition function test if is woocommerce shop page
		*/
		protected function is_woocommerce_shop() {
			if ( function_exists( 'is_shop' ) ) {
				$page_id = wc_get_page_id( 'shop' );
				return ! empty( $page_id ) && ( $page_id !== -1 ) && is_shop();
			}
			return false;
		}
		/**
		* Add data to loader wrapper to identify the loader is from any page shortcode
		*/
		public function data_attributes( $attr ) {
			if ( $this->page_enabled ) {
				$attr .= ' data-any-page-extension="true"';
			}
			return $attr;
		}
	}
}
