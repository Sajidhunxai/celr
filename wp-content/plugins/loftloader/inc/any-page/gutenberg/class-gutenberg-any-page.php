<?php
/**
* Gutenberg Manager class
*/

if ( ! class_exists( 'LoftLoader_Gutenberg_Any_Page' ) ) {
	class LoftLoader_Gutenberg_Any_Page {
		/**
		* Array of post meta name list 
		*/
		public $page_meta_list = array(
			'loftloader_page_shortcode' => 'string'
		);
		/**
		* Construct function
		*/
		public function __construct() {
			add_action( 'enqueue_block_editor_assets', array( $this, 'enqueue_editor_assets' ) );
			$this->register_metas();
		}
		/**
		* Enqueue editor assets
		*/
		public function enqueue_editor_assets() {
			switch ( $this->get_current_post_type() ) {
				case 'page':
					wp_enqueue_script(
						'loftloader-gutenberg-any-page-script',
						LOFTLOADER_URI. 'inc/any-page/gutenberg/plugin.js',
						array( 'wp-blocks', 'wp-element', 'wp-i18n' ), 
						LOFTLOADER_ASSET_VERSION, 
						true
					);
					break;
			}
		}
		/**
		* Register metas for gutenberg
		*/
		public function register_metas() {
			foreach( $this->page_meta_list as $id => $type ) {
				register_meta( 'post', $id, array(
					'auth_callback' => array( $this, 'permission_check' ),
					'show_in_rest' 	=> true,
					'single' 		=> true,
					'type' 			=> $type
				) );
			}
		}
		/**
		* Check permission for meta registration
		*/
		public function permission_check( $arg1, $meta_name, $post_id ) {
			return current_user_can( 'edit_post', $post_id );
		}
		/**
		* Get current post type
		* @return mix post type string or boolean false
		*/
		protected function get_current_post_type() {
			global $post;
			if ( is_admin() && ! empty( $post ) && ! empty( $post->post_type ) ) {	
				return $post->post_type;
			} else {
				return false;
			}
		}
	}
	function loftloader_init_gutenberg_any_page() {
		new LoftLoader_Gutenberg_Any_Page();
	}
	add_action( 'init', 'loftloader_init_gutenberg_any_page' );
}