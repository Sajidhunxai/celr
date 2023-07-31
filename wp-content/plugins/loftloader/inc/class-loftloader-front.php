<?php
/**
 * Main class for front display
 *
 * @package   LoftLoader
 * @link	  http://www.loftocean.com/
 * @author	  Suihai Huang from Loft Ocean Team

 * @since version 1.0
 */

if ( ! class_exists( 'LoftLoader_Front' ) ) {
	class LoftLoader_Front {
		protected $defaults;
		protected $site_header_loaded = false;
		protected $site_footer_loaded = false;
		protected $type; // Get the loader settings
		public function __construct() {
			$this->get_settings();
			$this->init_cache();
			if ( $this->loader_enabled() ) {
				add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
				add_action( 'wp_head', array( $this, 'loader_custom_styles' ), 100 );
				add_action( 'wp_footer', array( $this, 'load_inline_js' ), 99 );
				add_filter( 'loftloader_modify_html', array( $this, 'show_loader_html' ) );
				add_filter( 'body_class', array( $this, 'body_class' ) );
			}
		}
		/**
		* Init cache for outputing
		*/
		public function init_cache() {
			// Only for front view
			if ( ! is_admin() ) {
				add_action( 'template_redirect', array( $this, 'start_cache' ), 2 );
			}
		}
		/**
		* Start cache for outputing
		*/
		public function start_cache() {
			if ( ! wp_doing_ajax() ) {
				// Start cache the output with callback function
				ob_start( array( $this, 'modify_html' ) );
			}
		}
		/**
		* Will be called when flush cache
		*
		* @param string cached string
		* @return string modified cached string
		*/
		public function modify_html( $html ) {
			if ( $this->site_header_loaded && $this->site_footer_loaded ) {
				return apply_filters( 'loftloader_modify_html', $html );
			} else {
				return $html;
			}
		}
		/**
		* @description get the plugin settings
		*/
		public function get_settings() {
			global $loftloader_default_settings;
			$this->defaults = $loftloader_default_settings;
			do_action( 'loftloader_settings' );
			$this->type = esc_attr( $this->get_loader_setting( 'loftloader_loader_type' ) );
			add_action( 'wp_head', array( $this, 'set_header_loaded' ) );
			add_action( 'wp_footer', array( $this, 'set_footer_loaded' ) );
		}
		/**
		 * @description enqueue the scripts and styles for front end
		 */
		public function enqueue_scripts() {
			$loadJSStyle = $this->get_loader_setting( 'loftloader_inline_js' );
			if ( ! is_customize_preview() && ( 'inline' !== $loadJSStyle ) ) {
				wp_enqueue_script( 'loftloader-lite-front-main', LOFTLOADER_URI . 'assets/js/loftloader.min.js', array(), LOFTLOADER_ASSET_VERSION, true );
			}
			wp_enqueue_style('loftloader-lite-animation', LOFTLOADER_URI . 'assets/css/loftloader.min.css', array(), LOFTLOADER_ASSET_VERSION);
		}
		/**
		* Load inline JavaScript code if set
		*/
		public function load_inline_js() {
			$loadJSStyle = $this->get_loader_setting( 'loftloader_inline_js' );
			if ( ( 'inline' === $loadJSStyle ) && ! is_customize_preview() ) { ?>
				<script type="text/javascript">
					( function() {
						function loftloader_finished() {
							document.body.classList.add( 'loaded' );
						}
						var loader = document.getElementById( 'loftloader-wrapper' );
						if ( loader ) {
							window.addEventListener( 'load', function( e ) {
								loftloader_finished();
							} );
							if ( loader.dataset && loader.dataset.showCloseTime ) {
								var showCloseTime = parseInt( loader.dataset.showCloseTime, 10 ), maxLoadTime = false,
									closeBtn = loader.getElementsByClassName( 'loader-close-button' );
								if ( showCloseTime && closeBtn.length ) {
									setTimeout( function() {
										closeBtn[0].style.display = '';
									}, showCloseTime );
									closeBtn[0].addEventListener( 'click', function( e ) {
										loftloader_finished();
									} );
								}
							}
							if ( loader.dataset.maxLoadTime ) {
								maxLoadTime = loader.dataset.maxLoadTime;
								maxLoadTime = parseInt( maxLoadTime, 10 );
								if ( maxLoadTime ) {
									setTimeout( function() {
										loftloader_finished();
									}, maxLoadTime );
								}
							}
						}
					} ) ();
				</script> <?php
			}
		}
		/**
		 * @description custom css for front end
		 */
		public function loader_custom_styles() {
			$color = esc_attr( $this->get_loader_setting( 'loftloader_loader_color' ) );
			$bgColor = esc_attr( $this->get_loader_setting( 'loftloader_bg_color' ) );
			$bgOpacity = intval( $this->get_loader_setting('loftloader_bg_opacity' ) ) / 100;

			$styles  = $this->generate_style(
				'loftloader-lite-custom-bg-color',
				'#loftloader-wrapper .loader-section {' . PHP_EOL . "\t" . 'background: ' . $bgColor . ';' . PHP_EOL . '}' . PHP_EOL
			);
			$styles .= $this->generate_style(
				'loftloader-lite-custom-bg-opacity',
				'#loftloader-wrapper .loader-section {' . PHP_EOL . "\t" . 'opacity: ' . $bgOpacity . ';' . PHP_EOL . '}' . PHP_EOL
			);
			$css = '';
			switch ( $this->type ) {
				case 'sun':
					$css = '#loftloader-wrapper.pl-sun #loader {' . PHP_EOL . "\t" . 'color: ' . $color . ';' . PHP_EOL . '}' . PHP_EOL;
					break;
				case 'circles':
					$css = '#loftloader-wrapper.pl-circles #loader {' . PHP_EOL . "\t" . 'color: ' . $color . ';' . PHP_EOL . '}' . PHP_EOL;
					break;
				case 'wave':
					$css = '#loftloader-wrapper.pl-wave #loader {' . PHP_EOL . "\t" . 'color: ' . $color . ';' . PHP_EOL . '}' . PHP_EOL;
					break;
				case 'square':
					$css = '#loftloader-wrapper.pl-square #loader span {' . PHP_EOL . "\t" . 'border: 4px solid ' . $color . ';' . PHP_EOL . '}' . PHP_EOL;
					break;
				case 'frame':
					$css = '#loftloader-wrapper.pl-frame #loader {' . PHP_EOL . "\t" . 'color: ' . $color . ';' . PHP_EOL . '}' . PHP_EOL;
					break;
				case 'imgloading':
					$width = absint($this->get_loader_setting('loftloader_img_width'));
					$image = esc_url($this->get_loader_setting('loftloader_custom_img'));
					$css  = empty($width) ? '' : '#loftloader-wrapper.pl-imgloading #loader {' . PHP_EOL . "\t" . 'width: ' . $width . 'px;' . PHP_EOL . '}' . PHP_EOL;
					$css .= '#loftloader-wrapper.pl-imgloading #loader span {' . PHP_EOL . "\t" . 'background-size: cover;' . PHP_EOL . "\t" . 'background-image: url(' . $image . ');' . PHP_EOL . '}' . PHP_EOL;
					break;
				case 'beating':
					$css = '#loftloader-wrapper.pl-beating #loader {' . PHP_EOL . "\t" . 'color: ' . $color . ';' . PHP_EOL . '}' . PHP_EOL;
					break;
			}
			$styles .= $this->generate_style( 'loftloader-lite-custom-loader', $css );
			echo wp_kses( $styles, array(
				'style' => array( 'type' => array(), 'id' => array(), 'media' => array() )
			) );
		}
		/**
		 * @description loftloader html
		 */
		public function show_loader_html( $origin ) {
			if ( ! empty( $origin ) ) {
				$regexp ='/(<body[^>]*>)/i';
				$split = preg_split( $regexp, $origin, 3, PREG_SPLIT_DELIM_CAPTURE );
				if ( is_array( $split ) && ( 3 <= count( $split ) ) ) {
					$image  = esc_url($this->get_loader_setting('loftloader_custom_img'));
					$ending = esc_attr($this->get_loader_setting('loftloader_bg_animation'));
					$wrap_class = array( 'pl-' . $this->type );

					$html  = '<div id="loftloader-wrapper" class="' . implode( ' ', $wrap_class ) . '"' . $this->loader_attributes() . '>';
					switch( $ending ) {
						case 'fade':
							$html .= '<div class="loader-section section-fade"></div>';
							break;
						case 'up':
							$html .= '<div class="loader-section section-slide-up"></div>';
							break;
						case 'split-v':
							$html .= '<div class="loader-section section-up"></div>';
							$html .= '<div class="loader-section section-down"></div>';
							break;
						case 'no-animation':
							$html .= '<div class="loader-section end-no-animation"></div>';
							break;
						default:
							$html .= '<div class="loader-section section-left">';
							$html .= '</div><div class="loader-section section-right"></div>';
					}

					$html .= '<div class="loader-inner"><div id="loader">';

					if ( ! empty( $image ) ) {
						// <!-- Only  image loading need the span with background -->
						if ( in_array( $this->type, array( 'imgloading' ) ) ) {
							$html .= $this->get_loader_type_loading_bg_image( $image );
						}
						if ( in_array( $this->type, array( 'frame', 'imgloading' ) ) ) {
							$html .= $this->get_loader_image( $image, $this->type );
						}
					}
					$html .= in_array( $this->type, array( 'imgloading' ) ) ? '' : '<span></span>';
					$html .= '</div></div>';

					if ( ! is_customize_preview() ) {
						$close_description = $this->get_loader_setting( 'loftloader_show_close_tip' );
						$html .= sprintf(
							'<div class="loader-close-button" style="display: none;"><span class="screen-reader-text">%s</span>%s</div>',
							esc_html__('Close', 'loftloader'),
							empty($close_description) ? '' : sprintf('<span class="close-des">%s</span>', $close_description)
						);
					}
					$html .= '</div>';

					return $split[0] . $split[1] . $html . implode( '', array_slice( $split, 2 ) );
				}
			}
			return $origin;
		}
		/**
		* Background image for loader type loading with custom image
		*
		* @param url image url
		* @return string html
		*/
		private function get_loader_type_loading_bg_image( $image ) {
			return sprintf(
				'<div class="imgloading-container"><span style="background-image: url(%s);"></span></div>',
				esc_url( $image )
			);
		}
		/**
		* Helper function to add manual loader settings
		*/
		private function loader_attributes() {
			$attrs = '';
			$show_close_time = $this->get_loader_setting( 'loftloader_show_close_timer' );
			$show_close_time = number_format( $show_close_time, 0, '.', '' );
			$attrs .= sprintf( ' data-show-close-time="%s"', esc_js( esc_attr( $show_close_time * 1000 ) ) );

			$max_load_time = $this->get_loader_setting( 'loftloader_max_load_time' );
			$max_load_time = number_format( $max_load_time, 1, '.', '' );
			if ( ! empty( $max_load_time ) ) {
				$attrs .= sprintf( ' data-max-load-time="%s"', esc_js( esc_attr( $max_load_time * 1000 ) ) );
			}

			return apply_filters( 'loftloader_loader_attributes', $attrs );
		}
		/**
		* Helper function to test whether show loftloader
		* @return boolean return true if loftloader enabled and display on current page, otherwise false
		*/
		private function loader_enabled() {
			if ( $this->test_builder() ) {
				return false;
			} else {
				if ( ( $this->get_loader_setting( 'loftloader_main_switch' ) === 'on' ) ) {
					$range = $this->get_loader_setting( 'loftloader_show_range' );
					if ( ( $range === 'sitewide' ) || ( ( $range === 'homepage' ) && is_front_page() ) ) {
						return true;
					} else {
						return false;
					}
				} else {
					return apply_filters( 'loftloader_loader_enabled', false );
				}
			}
		}
		/**
		* Helper function get setting option
		*/
		private function get_loader_setting( $setting_id ) {
			return apply_filters( 'loftloader_get_loader_setting', get_option( $setting_id, $this->defaults[ $setting_id ] ), $setting_id );
		}
		/**
		* Helper function generate styles
		*/
		private function generate_style( $id, $style ) {
			return '<style id="' . $id . '">' . $style . '</style>';
		}
		/**
		* Make the head loaded flag
		*/
		public function set_header_loaded() {
			$this->site_header_loaded = true;
		}
		/**
		* Make the footer loaded flag
		*/
		public function set_footer_loaded() {
			$this->site_footer_loaded = true;
		}
		/**
		* Not show loader in builder and theme customizer
		*/
		protected function test_builder() {
			if ( defined( 'ELEMENTOR_PATH' ) && isset( $_GET['elementor-preview'] ) && ! empty( sanitize_text_field( wp_unslash( $_GET['elementor-preview'] ) ) ) ) {
				return true;
			} else if ( class_exists( 'FLBuilderLoader' ) && isset( $_GET['fl_builder'] ) ) {
				return true;
			} else if ( defined( 'WPB_VC_VERSION' ) && ( ! empty( $_GET['vc_editable'] ) ) ) {
				return true;
			} else if ( is_customize_preview() && ( empty( $_GET['plugin'] ) || ( 'loftloader-lite' != sanitize_text_field( wp_unslash( $_GET['plugin'] ) ) ) ) ) {
				return true;
			}
			return false;
		}
		/**
		* Identify from loftloader lite
		*/
		public function body_class( $class ) {
			array_push( $class, 'loftloader-lite-enabled' );
			return $class;
		}
		/**
		* Get loader image
		*/
		protected function get_loader_image( $img, $type ) {
			if ( empty( $img ) ) return '';

			$width = 80;
			$height = 80;
			$is_frame = ( 'frame' == $type );

			$pid = attachment_url_to_postid( $img );
			$has_valid_image_attrs = false;
			$image_attrs = array();
			if ( empty( $pid ) ) {
				$info = getimagesize( $img );
				if ( $has_valid_image_attrs = ( ! empty( $info[1] ) ) && ( $info[0] > 1 ) ) {
					$image_attrs = array( 'width' => $info[0], 'height' => $info[1] );
				}
			} else {
				$image = wp_get_attachment_image_src( $pid, 'full' );
				if ( $has_valid_image_attrs = ( $image[1] > 1 ) ) {
					$image_attrs = array( 'width' => $image[1], 'height' => $image[2] );
				}
			}
			if ( $is_frame ) {
				if ( $has_valid_image_attrs ) {
					$width = $image_attrs['width'];
					$height = $image_attrs['height'];
				}
			} else {
				$width = intval( $this->get_loader_setting( 'loftloader_img_width' ) );
				$width = ( $width > 0 ) ? $width : 76;
				$height = $has_valid_image_attrs ? ( $image_attrs['height'] / $image_attrs['width'] * $width ) : $width;
			}
			return sprintf(
					'<img width="%3$s" height="%4$s" data-no-lazy="1" class="skip-lazy" alt="%1$s" src="%2$s">',
					esc_attr__( 'loader image', 'loftloader' ),
					esc_url( $img ),
					esc_attr( $width ),
					esc_attr( intval( $height ) )
				);
		}
	}
	new LoftLoader_Front();
}
