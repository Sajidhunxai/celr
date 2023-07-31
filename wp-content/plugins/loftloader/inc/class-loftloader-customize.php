<?php
if ( ! class_exists( 'LoftLoader_Customize' ) ) {
	/**
	* Load the Loftloader lite customize related functions
	*
	* @author Loft.Ocean
	* @since 2.0.0
	*/
	class LoftLoader_Customize {
		public function __construct() {
			$this->load_default_settings();
			if ( loftloader_is_customize() ) {
				$this->load_customize_controls();
				add_action( 'customize_controls_init', array( $this, 'remove_sections' ), 1000 );
				add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_scripts' ), 9999 );
				add_action( 'customize_preview_init', array( $this, 'preview_scripts' ) );
			}
		}

		private function load_default_settings() {
			require_once LOFTLOADER_ROOT . 'inc/configs/default-settings.php';
		}

		public function load_customize_controls() {
			$config_dir = LOFTLOADER_ROOT . 'inc/configs/';
			require_once $config_dir . 'customize-main.php';
			require_once $config_dir . 'customize-range.php';
			require_once $config_dir . 'customize-background.php';
			require_once $config_dir . 'customize-loader.php';
			require_once $config_dir . 'customize-promo.php';
			require_once $config_dir . 'customize-more.php';
			require_once $config_dir . 'customize-advanced.php';
		}

		public function remove_sections() {
			global $wp_customize;
			foreach( $wp_customize->containers() as $id => $container ) {
				if ( $container instanceof WP_Customize_Panel ) {
					( strpos( $id, 'loftloader_' ) === false ) ? $wp_customize->remove_panel( $id ) : '';
				} else if ( $container instanceof WP_Customize_Section){
					( strpos( $id, 'loftloader_' ) === false ) ? $wp_customize->remove_section( $id ) : '';
				}
			}
		}

		public function customize_scripts() {
			global $wp_scripts, $wp_styles;
			$js_url = LOFTLOADER_URI . 'assets/js/customize.min.js';
			$js_dep = array('jquery', 'wp-color-picker', 'jquery-ui-slider', 'customize-controls', 'media-editor' );
			$ui_css = LOFTLOADER_URI . 'assets/css/jquery-ui.css';
			$loader_css = LOFTLOADER_URI . 'assets/css/loftloader-settings.min.css';

			wp_register_script( 'loftloader-lite-customize', $js_url, $js_dep, LOFTLOADER_ASSET_VERSION );
			wp_localize_script( 'loftloader-lite-customize', 'loftloader_lite_i18n', array( 'name' => esc_html__( 'LoftLoader Lite', 'loftloader' ) ) ); // Change the site title in string "You are customizing ..."
			wp_enqueue_script( 'loftloader-lite-customize' );

			wp_enqueue_style( 'loftloader-lite-ui', $ui_css, array(), LOFTLOADER_ASSET_VERSION );
			wp_enqueue_style( 'loftloader-lite-customize', $loader_css, array(), LOFTLOADER_ASSET_VERSION );

			foreach ( $wp_scripts->registered as $h => $o ) {
				if ( strpos( $o->src, 'wp-content/themes' ) !== false ) {
					wp_dequeue_script( $h );
				}
				if ( strpos( $o->src, 'wp-content/plugins/disable-blog/' ) !== false ) {
					wp_dequeue_script( $h );
				}
			};
			foreach ( $wp_styles->registered as $h => $o ) {
				if ( strpos($o->src, 'wp-content/themes') !== false ) {
					wp_dequeue_style( $h );
				}
			};
		}

		public function preview_scripts() {
			$js_url = LOFTLOADER_URI . 'assets/js/preview.min.js';
			wp_register_script( 'loftloader-lite-preview', $js_url, array( 'jquery', 'customize-preview' ), LOFTLOADER_ASSET_VERSION, true );
			wp_localize_script( 'loftloader-lite-preview', 'loftloader_lite', array( 'preview' => 'on' ) );
			wp_enqueue_script( 'loftloader-lite-preview' );
		}
	}

	new LoftLoader_Customize();
}

if ( class_exists( 'WP_Customize_Setting' ) ) {
	/**
	* LoftLoader related customization api classes
	*
	* @since 2.0.0
	*/

	// LoftLoader base section class, changed the json function to modify the customize action text
	class LoftLoader_Customize_Section extends WP_Customize_Section {
		public function json() {
			$array = parent::json();
			$array['customizeAction'] = esc_html__( 'Setting', 'loftloader' );
			return $array;
		}
		/**
		* render function for LoftLoader Switch section
		*/
		protected function render() {
			if ( 'loftloader_switch' === $this->type ) :
				$switch = $this->manager->get_setting('loftloader_main_switch')->value();
				$classes = 'accordion-section control-section control-section-' . $this->type; ?>

				<li
					id="accordion-section-<?php echo esc_attr( $this->id ); ?>"
					class="accordion-section control-section control-section-<?php echo esc_attr( $this->type ); ?>"
				>
					<h3 class="accordion-section-title" tabindex="0">
						<?php echo esc_html( $this->title ); ?>
						<span class="screen-reader-text"><?php esc_html_e( 'Press return or enter to open this section', 'loftloader' ); ?></span>
						<input type="checkbox" name="loftloader-main-switch" value="on" <?php checked( $switch, 'on' ); ?> />
					</h3>
					<ul class="accordion-section-content">
						<li class="customize-section-description-container">
							<div class="customize-section-title">
								<button class="customize-section-back" tabindex="-1">
									<span class="screen-reader-text"><?php esc_html_e( 'Back', 'loftloader' ); ?></span>
								</button>
								<h3>
									<span class="customize-action"><?php esc_html_e( 'Setting', 'loftloader' ); ?></span>
									<?php echo wp_kses_post( $this->title ); ?>
								</h3>
							</div> <?php
							if ( ! empty( $this->description ) ) : ?>
								<div class="description customize-section-description"><?php echo wp_kses_post( $this->description ); ?></div> <?php
							endif; ?>
						</li>
					</ul>
				</li> <?php
			else :
				parent::render();
			endif;
		}
	}

	// LoftLoader base customize control class: add class properties as displaying dependency.
	class LoftLoader_Customize_Control extends WP_Customize_Control {
		public $filter = false;
		public $text = '';
		public $parent_setting_id = '';
		public $show_filter = array();
		public $img = '';
		public $href = '';
		public $note_below = '';
		public function active_callback() {
			if ( $this->filter && ( $this->manager->get_setting($this->parent_setting_id ) instanceof WP_Customize_Setting ) && ! empty( $this->show_filter ) ) {
				$parent_setting_value = $this->manager->get_setting( $this->parent_setting_id )->value();
				return in_array( $parent_setting_value, $this->show_filter ) ? true : false;
			}
			return true;
		}
		public function render_content() {
			switch ( $this->type ) {
				case 'loftloader-ad':
					if ( ! empty( $this->label ) ) : ?>
						<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span> <?php
					endif;
					if ( ! empty( $this->img ) ) : ?>
						<a href="<?php echo esc_url( $this->href ); ?>" target="_blank">
							<img src="<?php echo esc_url( $this->img ); ?>" >
						</a> <?php
					endif; ?>
					<div class="customize-control-notifications-container"></div> <?php
					break;
				case 'loftloader-any-page':
					if ( ! empty( $this->label ) ) : ?>
						<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span> <?php
					endif;
					if ( ! empty( $this->description ) ) : ?>
						<span class="description customize-control-description"><?php echo wp_kses_post( $this->description ); ?></span> <?php
					endif; ?>
					<input
						type="button"
						<?php $this->link(); ?>
						class="button button-primary loftloader-any-page-generate"
						value="<?php esc_attr_e( 'Generate', 'loftloader' ); ?>"
					/><br/><br/>
					<textarea class="loftloader-any-page-shortcode" cols="30" rows="4"></textarea>
					<div class="customize-control-notifications-container"></div> <?php
					break;
				case 'check': ?>
					<label> <?php
						if ( ! empty( $this->label ) ) : ?>
							<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span> <?php
						endif; ?>
							<input class="loftlader-checkbox" type="checkbox" value="on" name="<?php echo esc_attr( $this->id ); ?>" <?php checked( 'on', $this->value() ); ?> />
							<input style="display:none;" type="checkbox" value="on" <?php $this->link(); ?> <?php checked( 'on', $this->value() ); ?> />
					</label> <?php
					if ( ! empty( $this->description ) ) : ?>
						<span class="description customize-control-description"><?php echo wp_kses_post( $this->description ); ?></span> <?php
					endif;
					break;
				default:
					parent::render_content();
					if ( ! empty( $this->text ) ) {
						echo esc_html( $this->text ) . '<br/>';
					}
					if ( ! empty( $this->note_below ) ) : ?>
						<span class="description"><?php echo esc_html( $this->note_below ); ?></span><?php
					endif;
			}
		}
	}
	// Modify the color control class to add the display dependency logic.
	class LoftLoader_Customize_Color_Control extends WP_Customize_Color_Control {
		public $filter = false;
		public $parent_setting_id = '';
		public $show_filter = array();
		public function active_callback() {
			if ( $this->filter && ( $this->manager->get_setting( $this->parent_setting_id ) instanceof WP_Customize_Setting ) && ! empty( $this->show_filter ) ) {
				$parent_setting_value = $this->manager->get_setting( $this->parent_setting_id )->value();
				return in_array( $parent_setting_value, $this->show_filter ) ? true : false;
			}
			return true;
		}
	}
	// Modify the image control class to add the display dependency logic.
	class LoftLoader_Customize_Image_Control extends WP_Customize_Image_Control {
		public $filter = false;
		public $parent_setting_id = '';
		public $show_filter = array();
		public function active_callback() {
			if ( $this->filter && ( $this->manager->get_setting($this->parent_setting_id ) instanceof WP_Customize_Setting ) && ! empty( $this->show_filter ) ) {
				$parent_setting_value = $this->manager->get_setting( $this->parent_setting_id )->value();
				return in_array( $parent_setting_value, $this->show_filter ) ? true : false;
			}
			return true;
		}
	}
	// Add new slider control class with jqueryui slider function
	class LoftLoader_Customize_Slider_Control extends LoftLoader_Customize_Control {
		public $input_class = '';
		public $after_text = '%';
		public function render_content() {
			if ( empty( $this->input_attrs ) ) {
				return;
			} ?>

			<label class="amount opacity"> <?php
			if ( ! empty( $this->label ) ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span> <?php
			endif; ?>
				<span class="<?php echo esc_attr( $this->input_class ); ?>">
					<input readonly="readonly" type="text" <?php $this->link(); ?> value="<?php echo esc_attr( $this->value() ); ?>" >
					<?php echo wp_kses_post( $this->after_text ); ?>
				</span>
			</label>
			<div
				class="ui-slider loader-ui-slider"
				data-value="<?php echo esc_attr( $this->manager->get_setting($this->id)->value() ); ?>"
				<?php $this->input_attrs(); ?>
			>
			</div>
			<div class="customize-control-notifications-container"></div> <?php
		}
	}
	// Add new radio type control class for loader animation choices.
	class LoftLoader_Customize_Animation_Types_Control extends WP_Customize_Control {
		public function render_content() {
			if ( empty( $this->choices ) )
				return;

			if ( ! empty( $this->label ) ) : ?>
				<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
			<?php endif; ?>
			<button class="customize-more-toggle" aria-expanded="false">
				<span class="screen-reader-text"><?php esc_html_e('More info', 'loftloader'); ?></span>
			</button> <?php
			if ( ! empty( $this->description ) ) : ?>
				<span class="description customize-control-description" style="display: none;"><?php echo wp_kses_post( $this->description ); ?></span>
			<?php endif; ?>

			<div id="loftloader_option_animation">
			<?php
				$name = '_customize-radio-' . $this->id;
				foreach ( $this->choices as $value => $attrs ) :
					$attr = '';
					if ( ! empty( $attrs['attr'] ) ) {
						foreach ( (array)$attrs['attr'] as $attr_name => $attr_value ) {
							$attr .= ' ' . $attr_name . '="' . $attr_value . '"';
						}
					}
					$item_id = sanitize_title( $this->id . '-' . $value );
				?>
				<label for="<?php echo esc_attr( $item_id ); ?>" title="<?php echo esc_attr($attrs['label']); ?>">
					<input
						id="<?php echo esc_attr( $item_id ); ?>"
						class="loftloader-radiobtn <?php echo esc_attr( $value ); ?>"
						type="radio"
						value="<?php echo esc_attr( $value ); ?>"
						name="<?php echo esc_attr( $name ); ?>"
						<?php $this->link(); ?>
						<?php checked( $this->value(), $value ); ?>
						<?php echo wp_kses_post( $attr ); ?>
					/>
					<span></span>
				</label>
				<?php endforeach; ?>
			</div>
			<?php
		}
	}
	// Add new number type control class with text after the element.
	class LoftLoader_Customize_Number_Text_Control extends LoftLoader_Customize_Control {
		public $after_text = '';
		public $input_class = '';
		public $input_wrap_class = '';
		public function render_content() { ?>
			<label>
				<?php if ( ! empty( $this->label ) ) : ?>
					<span class="customize-control-title"><?php echo esc_html( $this->label ); ?></span>
				<?php endif;
				if ( ! empty( $this->description ) ) : ?>
					<span class="description customize-control-description"><?php echo wp_kses_post( $this->description ); ?></span>
				<?php endif; ?>
				<span class="<?php echo esc_attr($this->input_wrap_class); ?>">
					<input
						class="<?php echo esc_attr( $this->input_class ); ?>"
						type="<?php echo esc_attr( $this->type ); ?>"
						<?php $this->input_attrs(); ?>
						value="<?php echo esc_attr( $this->value() ); ?>"
						<?php $this->link(); ?>
					/>
					<?php echo esc_attr( $this->after_text ); ?>
				</span>
			</label>
			<?php
		}
	}

	if ( ! function_exists( 'loftloader_sanitize_checkbox' ) ) {
		/**
		* Check the switch checkbox value
		*
		* @param string the value from user
		* @return mix if set return string 'on', otherwise return false
		*/
		function loftloader_sanitize_checkbox( $input ) {
			return empty( $input ) ? 'off' : 'on';
		}
	}

	if ( ! function_exists( 'loftloader_sanitize_choice' ) ) {
		/**
		* Check the value is one of the choices from customize control
		*
		* @param string the value from user
		* @param object customize setting object
		* @return string the value from user or the default setting value
		*/

		function loftloader_sanitize_choice( $input, $setting ) {
			$choices = $setting->manager->get_control( $setting->id )->choices;
			$choices = array_keys( $choices );
			return in_array( $input, $choices ) ? $input : $setting->default;
		}
	}

	if ( ! function_exists( 'loftloader_sanitize_number' ) ) {
		/**
		* Check the value is float with 1 decimal
		*
		* @param string the value from user
		* @param object customize setting object
		* @return string the value from user or the default setting value
		*/

		function loftloader_sanitize_number( $value) {
			if ( ! empty( $value ) ) {
				$value = floatval( $value );
				return number_format( $value, 1, '.', '' );
			}
			return 0;
		}
	}
}
