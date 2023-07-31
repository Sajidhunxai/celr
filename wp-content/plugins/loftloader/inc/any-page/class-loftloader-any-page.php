<?php
if ( ! class_exists( 'LoftLoader_Any_Page' ) && !class_exists('LoftLoader_Any_Page_Filter')){
	class LoftLoader_Any_Page{
		public function __construct(){
			add_action('add_meta_boxes', array($this, 'register_meta_boxes'));
			add_action('save_post', array($this, 'save_meta'), 10, 3);

			if ( ! is_admin() ) {
				$this->alter_loftloader();
			}

			if ( function_exists( 'register_block_type' ) ) {
				$this->load_gutenberg_panel();
			}
		}
		// Load gutenberg file
		protected function load_gutenberg_panel() {
			require_once LOFTLOADER_ROOT . 'inc/any-page/gutenberg/class-gutenberg-any-page.php';
		}
		// Register loftloader shortcode meta box
		public function register_meta_boxes(){
			add_meta_box(
				'loftloader_any_page_meta', 
				esc_html__( 'LoftLoader Any Page Shortcode', 'loftloader' ), 
				array( $this, 'metabox_callback' ), 
				'page', 
				'advanced',
				'high',
				array(
					'__block_editor_compatible_meta_box' => true,
					'__back_compat_meta_box' => true
				)
			);
		}
		// Show meta box html
		public function metabox_callback( $post ) {
			$shortcode = get_post_meta($post->ID, 'loftloader_page_shortcode', true); ?>
			<textarea name="loftloader_page_shortcode" style="width: 100%;" rows="4"><?php echo esc_textarea( str_replace('/\\"/g', '\\\\"', $shortcode ) ); ?></textarea>
			<input type="hidden" name="loftloader_any_page_nonce" value="<?php echo esc_attr( wp_create_nonce( 'loftloader_any_page_nonce' ) ); ?>" /> <?php
		}
		// Save loftloader shortcode meta
		public function save_meta($post_id, $post, $update){
			if ( empty( $update ) || ! in_array( $post->post_type, array( 'page' ) ) || empty( $_REQUEST['loftloader_any_page_nonce'] ) || ! empty( $_REQUEST['loftloader_gutenberg_enabled'] ) ) {
				return $post_id;
			} 
			if ( current_user_can( 'edit_post', $post_id ) ) {
				$shortcode = '';
				if ( ! empty( $_REQUEST['loftloader_page_shortcode'] ) ) {
					$shortcode = sanitize_text_field( wp_unslash( $_REQUEST['loftloader_page_shortcode'] ) );
				}
				update_post_meta( $post_id, 'loftloader_page_shortcode', $shortcode );
			}
			return $_post_id;
		}

		// Initial LoftLoader Pro Shortcode actions
		private function alter_loftloader() {
			require_once LOFTLOADER_ROOT . 'inc/any-page/class-any-page-filter.php';
			new LoftLoader_Any_Page_Filter();
		}
	}
	new LoftLoader_Any_Page();
}