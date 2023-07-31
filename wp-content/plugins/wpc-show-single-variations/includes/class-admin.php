<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Woosv_Admin' ) ) {
	class Woosv_Admin {
		protected static $instance = null;

		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function admin_enqueue_scripts( $hook ) {
			wp_enqueue_style( 'woosv-backend', WOOSV_URI . 'assets/css/backend.css', [ 'woocommerce_admin_styles' ], WOOSV_VERSION );
			wp_enqueue_script( 'woosv-backend', WOOSV_URI . 'assets/js/backend.js', [
				'jquery',
				'wc-enhanced-select',
			], WOOSV_VERSION );
		}

		public function admin_menu() {
			add_submenu_page( 'wpclever', esc_html__( 'WPC Show Single Variations for WooCommerce', 'wpc-show-single-variations' ),
				esc_html__( 'Show Single Variations', 'wpc-show-single-variations' ),
				'manage_options',
				'wpclever-woosv',
				[ $this, 'setting_page_content' ] );
		}

		public function register_settings() {
			register_setting( 'woosv_settings', 'woosv_enable' );
			register_setting( 'woosv_settings', 'woosv_hide_parent' );
			register_setting( 'woosv_settings', 'woosv_hide_parent_exclude' );
		}

		public function setting_page_content() {
			$active_tab          = isset( $_GET['tab'] ) ? sanitize_key( $_GET['tab'] ) : 'settings';
			$enable              = get_option( 'woosv_enable' );
			$hide_parent         = get_option( 'woosv_hide_parent' );
			$hide_parent_exclude = get_option( 'woosv_hide_parent_exclude' );
			?>
			<div class="wpclever_settings_page wrap">
				<h1 class="wpclever_settings_page_title"><?php echo esc_html__( 'WPC Show Single Variations', 'wpc-show-single-variations' ) . ' ' . WOOSV_VERSION; ?></h1>
				<div class="wpclever_settings_page_desc about-text">
					<p>
						<?php printf( esc_html__( 'Thank you for using our plugin! If you are satisfied, please reward it a full five-star %s rating.', 'wpc-show-single-variations' ), '<span style="color:#ffb900">&#9733;&#9733;&#9733;&#9733;&#9733;</span>' ); ?>
						<br/>
						<a href="<?php echo esc_url( WOOSV_REVIEWS ); ?>" target="_blank"><?php esc_html_e( 'Reviews', 'wpc-show-single-variations' ); ?></a> |
						<a href="<?php echo esc_url( WOOSV_CHANGELOG ); ?>" target="_blank"><?php esc_html_e( 'Changelog', 'wpc-show-single-variations' ); ?></a> |
						<a href="<?php echo esc_url( WOOSV_DISCUSSION ); ?>" target="_blank"><?php esc_html_e( 'Discussion', 'wpc-show-single-variations' ); ?></a>
					</p>
				</div>
				<div class="wpclever_settings_page_nav">
					<h2 class="nav-tab-wrapper">
						<a href="<?php echo admin_url( 'admin.php?page=wpclever-woosv&tab=settings' ); ?>" class="<?php echo $active_tab === 'settings' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>">
							<?php esc_html_e( 'Settings', 'wpc-show-single-variations' ); ?>
						</a>
						<a href="<?php echo admin_url( 'admin.php?page=wpclever-woosv&tab=tools' ); ?>" class="<?php echo $active_tab === 'tools' ? 'nav-tab nav-tab-active' : 'nav-tab'; ?>">
							<?php esc_html_e( 'Tools', 'wpc-show-single-variations' ); ?>
						</a>
						<a href="<?php echo esc_url( admin_url( 'admin.php?page=wpclever-kit' ) ); ?>" class="nav-tab">
							<?php esc_html_e( 'Essential Kit', 'wpc-show-single-variations' ); ?>
						</a>
					</h2>
				</div>
				<div class="wpclever_settings_page_content">
					<?php if ( $active_tab === 'settings' ) { ?>
						<form method="post" action="options.php">
							<table class="form-table">
								<tr>
									<th><?php esc_html_e( 'Enable', 'wpc-show-single-variations' ); ?></th>
									<td>
										<input type="checkbox" class="field-control" id="woosv_enable" name="woosv_enable" value="yes" <?php echo( ( $enable === 'yes' ) ? 'checked' : '' ); ?>/>
										<label for="woosv_enable"><?php esc_html_e( 'Enable show single variations on archive page. You also can enable/disable at variation basis.', 'wpc-show-single-variations' ); ?></label>
									</td>
								</tr>
								<tr>
									<th><?php esc_html_e( 'Hide variable', 'wpc-show-single-variations' ); ?></th>
									<td>
										<input type="checkbox" class="field-control" id="woosv_hide_parent" name="woosv_hide_parent" value="yes" <?php echo( ( $hide_parent === 'yes' ) ? 'checked' : '' ); ?>/>
										<label for="woosv_hide_parent"><?php esc_html_e( 'Hide variable products on archive page.', 'wpc-show-single-variations' ); ?></label>
									</td>
								</tr>
								<tr>
									<th><?php esc_html_e( 'Exclude from hide variable', 'wpc-show-single-variations' ); ?></th>
									<td>
										<input type="hidden" name="woosv_hide_parent_exclude" value="<?php echo esc_attr( $hide_parent_exclude ); ?>"/>
										<select class="wc-product-search woosv-product-search" multiple="multiple" data-placeholder="<?php esc_attr_e( 'Search for a product&hellip;', 'wpc-show-single-variations' ); ?>" data-action="woocommerce_json_search_products_and_variations" data-exclude_type="variation,simple">
											<?php
											$_product_ids = explode( ',', $hide_parent_exclude );

											foreach ( $_product_ids as $_product_id ) {
												$_product = wc_get_product( $_product_id );

												if ( $_product ) {
													echo '<option value="' . esc_attr( $_product_id ) . '" selected="selected">' . wp_kses_post( $_product->get_formatted_name() ) . '</option>';
												}
											}
											?>
										</select>
										<span class="description"><?php esc_html_e( 'Choose variable products that will not be hide.', 'wpc-show-single-variations' ); ?></span>
									</td>
								</tr>
								<tr class="heading">
									<th colspan="2"><?php esc_html_e( 'Suggestion', 'wpc-show-single-variations' ); ?></th>
								</tr>
								<tr>
									<td colspan="2">
										Wanna save your precious time working on variations? Try our brand-new free plugin
										<a href="https://wordpress.org/plugins/wpc-variation-bulk-editor/" target="_blank">WPC Variation Bulk Editor</a> and
										<a href="https://wordpress.org/plugins/wpc-variation-duplicator/" target="_blank">WPC Variation Duplicator</a>.
									</td>
								</tr>
								<tr class="submit">
									<th colspan="2">
										<?php settings_fields( 'woosv_settings' ); ?><?php submit_button(); ?>
									</th>
								</tr>
							</table>
						</form>
					<?php } elseif ( $active_tab === 'tools' ) { ?>
						<table class="form-table">
							<tr>
								<th><?php esc_html_e( 'Re-init variations', 'wpc-show-single-variations' ); ?></th>
								<td>
									<?php
									$num   = absint( isset( $_GET['num'] ) ? $_GET['num'] : 50 );
									$paged = absint( isset( $_GET['paged'] ) ? $_GET['paged'] : 1 );

									if ( isset( $_GET['act'] ) && ( $_GET['act'] === 'init' ) ) {
										$args = [
											'post_type'      => 'product_variation',
											'posts_per_page' => $num,
											'paged'          => $paged
										];

										$posts = get_posts( $args );

										if ( ! empty( $posts ) ) {
											foreach ( $posts as $post ) {
												$variation_id   = $post->ID;
												$parent_id      = wp_get_post_parent_id( $variation_id );
												$parent_product = wc_get_product( $parent_id );

												if ( ! $parent_product ) {
													continue;
												}

												// taxonomies
												$taxonomies = apply_filters( 'woosv_init_taxonomies', [
													'product_cat',
													'product_tag',
													'wpc-brand'
												] );

												foreach ( $taxonomies as $taxonomy ) {
													$terms = (array) wp_get_post_terms( $parent_id, $taxonomy, [ "fields" => "ids" ] );
													wp_set_post_terms( $variation_id, $terms, $taxonomy );
												}

												$variation = new WC_Product_Variation( $variation_id );

												if ( ! $variation ) {
													return;
												}

												$variation->set_menu_order( $parent_product->get_menu_order() );
												$variation->save();

												// attributes
												$attributes = $variation->get_variation_attributes();

												if ( ! empty( $attributes ) ) {
													foreach ( $attributes as $key => $term ) {
														$attr_tax = str_replace( 'attribute_', '', $key );
														wp_set_post_terms( $variation_id, $term, $attr_tax );
													}
												}

												// parent attributes
												$parent_attributes = $parent_product->get_attributes();

												if ( ! empty( $parent_attributes ) ) {
													foreach ( $parent_attributes as $parent_attribute ) {
														if ( $parent_attribute->get_variation() ) {
															continue;
														}

														$attr_tax = $parent_attribute->get_taxonomy();
														$terms    = (array) $parent_attribute->get_terms();

														if ( ! empty( $terms ) ) {
															$tmp = [];

															foreach ( $terms as $term ) {
																$tmp[] = $term->term_id;
															}

															wp_set_post_terms( $variation_id, $tmp, $attr_tax );
														}
													}
												}
											}

											echo '<span style="color: #2271b1; font-weight: 700">' . esc_html__( 'Refreshing...', 'wpc-show-single-variations' ) . '</span>';
											echo '<p class="description">' . esc_html__( 'Please wait until it has finished!', 'wpc-show-single-variations' ) . '</p>';
											?>
											<script type="text/javascript">
                                              (function($) {
                                                $(function() {
                                                  setTimeout(function() {
                                                    window.location.href = '<?php echo admin_url( 'admin.php?page=wpclever-woosv&tab=tools&act=init&num=' . $num . '&paged=' . ( $paged + 1 ) ); ?>';
                                                  }, 1000);
                                                });
                                              })(jQuery);
											</script>
											<?php
										} else {
											echo '<span style="color: #2271b1; font-weight: 700">' . esc_html__( 'Finished!', 'wpc-show-single-variations' ) . '</span>';
										}
									} else {
										echo '<a class="button btn" href="' . admin_url( 'admin.php?page=wpclever-woosv&tab=tools&act=init' ) . '">' . esc_html__( 'Re-init variations', 'wpc-show-single-variations' ) . '</a>';
										echo '<p class="description">' . esc_html__( 'Re-init variations\' data to make it works with product category/tag. This process may take a while.', 'wpc-show-single-variations' ) . '</p>';
									}
									?>
								</td>
							</tr>
						</table>
					<?php } ?>
				</div>
			</div>
			<?php
		}

		public function action_links( $links, $file ) {
			if ( WOOSV_BASE === $file ) {
				$settings = '<a href="' . admin_url( 'admin.php?page=wpclever-woosv&tab=settings' ) . '">' . esc_html__( 'Settings', 'wpc-show-single-variations' ) . '</a>';
				array_unshift( $links, $settings );
			}

			return (array) $links;
		}

		public function row_meta( $links, $file ) {
			if ( WOOSV_BASE === $file ) {
				$row_meta = [
					'support' => '<a href="' . esc_url( WOOSV_DISCUSSION ) . '" target="_blank">' . esc_html__( 'Community support', 'wpc-show-single-variations' ) . '</a>',
				];

				return array_merge( $links, $row_meta );
			}

			return (array) $links;
		}

		public function add_fields( $loop, $variation_data, $variation ) {
			echo '<div class="form-row form-row-full woosv-variation-settings">';
			echo '<label>' . esc_html__( 'WPC Show Single Variations', 'wpc-show-single-variations' ) . ' <a href="' . admin_url( 'admin.php?page=wpclever-woosv' ) . '" target="_blank">Default settings</a></label>';
			echo '<div class="woosv-variation-wrap">';

			woocommerce_wp_select( [
				'id'      => 'woosv_enable_' . $variation->ID,
				'label'   => esc_html__( 'Enable', 'wpc-show-single-variations' ),
				'name'    => 'woosv_enable[' . $variation->ID . ']',
				'value'   => get_post_meta( $variation->ID, 'woosv_enable', true ) ?: 'default',
				'options' => [
					'default' => esc_html__( 'Default', 'wpc-show-single-variations' ),
					'enable'  => esc_html__( 'Enable', 'wpc-show-single-variations' ),
					'disable' => esc_html__( 'Disable', 'wpc-show-single-variations' ),
					'reverse' => esc_html__( 'Reverse', 'wpc-show-single-variations' )
				]
			] );

			woocommerce_wp_text_input( [
				'id'    => 'woosv_name_' . $variation->ID,
				'label' => esc_html__( 'Custom name', 'wpc-show-single-variations' ),
				'name'  => 'woosv_name[' . $variation->ID . ']',
				'value' => get_post_meta( $variation->ID, 'woosv_name', true ) ?: '',
			] );

			echo '</div></div>';
		}

		public function save_fields( $post_id ) {
			if ( isset( $_POST['woosv_enable'][ $post_id ] ) ) {
				update_post_meta( $post_id, 'woosv_enable', sanitize_key( $_POST['woosv_enable'][ $post_id ] ) );
			}

			if ( isset( $_POST['woosv_name'][ $post_id ] ) ) {
				update_post_meta( $post_id, 'woosv_name', sanitize_text_field( $_POST['woosv_name'][ $post_id ] ) );
			}
		}

		public function duplicate_variation( $old_variation_id, $new_variation_id ) {
			if ( $enable = get_post_meta( $old_variation_id, 'woosv_enable', true ) ) {
				update_post_meta( $new_variation_id, 'woosv_enable', $enable );
			}

			if ( $name = get_post_meta( $old_variation_id, 'woosv_name', true ) ) {
				update_post_meta( $new_variation_id, 'woosv_name', $name );
			}
		}

		public function bulk_update_variation( $variation_id, $fields ) {
			if ( ! empty( $fields['woosv_enable'] ) && ( $fields['woosv_enable'] !== 'wpcvb_no_change' ) ) {
				update_post_meta( $variation_id, 'woosv_enable', sanitize_key( $fields['woosv_enable'] ) );
			}

			if ( ! empty( $fields['woosv_name'] ) ) {
				update_post_meta( $variation_id, 'woosv_name', sanitize_text_field( $fields['woosv_name'] ) );
			}
		}
	}
}
