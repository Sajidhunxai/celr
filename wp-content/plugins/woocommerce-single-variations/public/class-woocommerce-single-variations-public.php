<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       http://plugins.db-dzine.com
 * @since      1.0.0
 *
 * @package    WooCommerce_Single_Variations
 * @subpackage WooCommerce_Single_Variations/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WooCommerce_Single_Variations
 * @subpackage WooCommerce_Single_Variations/public
 * @author     Daniel Barenkamp <support@db-dzine.com>
 */
class WooCommerce_Single_Variations_Public extends WooCommerce_Single_Variations {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	protected $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of this plugin.
	 */
	protected $version;

	/**
	 * options of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $options
	 */
	protected $options;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct($plugin_name, $version ) 
	{
		$this->plugin_name = $plugin_name;
		$this->version = $version;
		$this->is_related = false;
	}
	
	/**
	 * Enqueu Styles
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https://welaunch.io
	 * @return  [type]                       [description]
	 */
	public function enqueue_scripts() 
	{
		global $woocommerce_single_variations_options;

		$this->options = $woocommerce_single_variations_options;

		if(!$this->get_option('variationTitleEnabled')) {
			return;
		}

		if(!$this->get_option('variationTitleChangeOnSingleProductPages')) {
			return;
		}

		wp_enqueue_script( $this->plugin_name . '-public', plugin_dir_url( dirname( __FILE__ ) ) . 'public/js/woocommerce-single-variations-public.js', array( 'jquery'), $this->version, true );
		
		$forJS = array(
			'ajax_url' => admin_url('admin-ajax.php'),
			'titleSelector' => $this->get_option('variationTitleChangeOnSingleProductPagesSelector'),
 		);

    	$forJS = apply_filters('woocommerce_single_variations_settings', $forJS);
        wp_localize_script($this->plugin_name . '-public', 'woocommerce_single_variations_options', $forJS);
	}

    /**
     * Inits the Single Variations
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https://plugins.db-dzine.com
     * @return  [type]                       [description]
     */
    public function init()
    {
		global $woocommerce_single_variations_options;
		$this->options = $woocommerce_single_variations_options;

		if (!$this->get_option('enable')) {
			return false;
		}

		add_action('woocommerce_product_query', array($this, 'modify_product_query'), 10, 1);
		add_filter('woocommerce_shortcode_products_query', array($this, 'modify_shortcode_query'), 10, 1 );
		add_filter('woocommerce_subcategory_count_html', array($this, 'change_category_count'), 990, 2);

		add_filter('posts_clauses', array($this, 'maybe_hide_parent_products'), 10, 2);
		// add_action('woocommerce_before_shop_loop', array($this, 'change_wp_query'), 1, 1);


		add_filter('woocommerce_layered_nav_count', array($this,'layered_nav_count'), 10, 3);
		add_filter('the_title', array($this, 'variation_title' ), 10, 2 );
		
		add_filter('get_canonical_url', array($this, 'seo_change_canonical'), 90, 2 );
		add_filter('wpseo_accessible_post_types', array($this, 'seo_add_variations_to_sitemap'), 90, 1 );
		add_filter('wpseo_title', array($this, 'seo_change_meta_title'), 90, 1 );
		add_filter('wpseo_metadesc', array($this, 'seo_change_meta_desc'), 90, 1 );

		// Yoast Woo Plugin support
		global $yoast_woo_seo;
		if(!empty($yoast_woo_seo) && $this->get_option('seoSitemap')) {
			remove_filter('wpseo_sitemap_exclude_post_type', array($yoast_woo_seo, 'xml_sitemap_post_types'), 10, 2);
		}

		add_filter('post_type_link', array($this, 'remove_excluded_attribute_from_link'), 10, 4 );
		add_action('pre_get_posts', array($this, 'add_variations_to_search_results'), 50, 1 );

		add_action('woocommerce_product_variation_get_rating_counts', array($this, 'modify_variation_rating_counts'), 50, 2 );
		add_action('woocommerce_product_variation_get_average_rating', array($this, 'modify_variation_average_rating'), 50, 2 );
		add_action('woocommerce_product_variation_get_review_count', array($this, 'modify_variation_review_count'), 50, 2 );	

		add_filter('woocommerce_related_products', array($this, 'show_variations_in_related_products'), 50, 3 );

		add_shortcode( 'woocommerce_single_variations', array($this, 'shortcode'));
    }

	public function remove_excluded_attribute_from_link($post_link, $post, $leavename, $sample ) 
	{
		if(!$post) {
			return $post_link;
		}

		$id = $post->ID;		

		$post_type = get_post_type($id);
		if($post_type != "product_variation") {
			return $post_link;
		}

		if(!$this->get_option('excludedAttributesRemoveFromQueryString')) {
			return $post_link;
		}

		// https://dev.welaunch.io/plugins/woocommerce-single-variations/product/test-shoe-copy/" string(53) "?attribute_pa_color=brown-lether&attribute_pa_size=40

		$excludedAttributes = $this->get_option('excludedAttributes');
		if(empty($excludedAttributes)) {
			return $post_link;
		}

		foreach ($excludedAttributes as $excludedAttribute) {
			$removeAttr = 'attribute_' . $excludedAttribute;
			$post_link = preg_replace('~(\?|&)' . $removeAttr . '=[^&]*~','$1',$post_link);
		}

		$post_link = rtrim($post_link, '&');

		return $post_link;
	}

	public function add_variation_to_price_filter($product_types)
	{
		return array('product_variation', 'product');
	}

	public function change_price_filter_query($sql, $meta_query_sql, $tax_query_sql )
	{
		global $wpdb;

		$variationsSQL = "SELECT DISTINCT post_parent FROM {$wpdb->posts} WHERE {$wpdb->posts}.post_status ='publish' and  {$wpdb->posts}.post_type='product_variation'";
		$variations = $wpdb->get_results( $variationsSQL, 'ARRAY_A');
		$excludedPosts = array();
		if(!empty($variations)) {
			foreach ($variations as $variation) {
				$excludedPosts[] = $variation['post_parent'];
			}
		}

		if(!empty($excludedPosts)) {
			$sql .= ' AND product_id NOT IN(' . rtrim( implode(',', $excludedPosts), ',') . ') ';
		}
		// get variable products and remove them
		// $sql .= ' AND {$wpdb->posts}.post_parent != 0 ';
		// var_dump($sql);
		return $sql;
	}

    /**
     * Modify the Product Query and add product_variation type
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    https:/welaunch.io
     * @param   [type]             $q [description]
     * @return  [type]                [description]
     */
	public function modify_product_query($query) 
	{

		$currentCategory = get_queried_object();
		
		if($this->get_option('hideOnShopPage')) {
			$currentURL = preg_replace('/\?.*/', '', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
			$shopPostId = (int) get_option('woocommerce_shop_page_id');
			$shopURL = get_permalink($shopPostId);
			if($shopPostId > 0 && $currentURL == $shopURL) {
				return;
			}
		}

		$currentCategoryID = 0;
		
		if(isset($currentCategory->term_id)) {
			$currentCategoryID = $currentCategory->term_id;
		}

		$excludeProductCategories = $this->get_option('excludeProductCategories');
		if(!empty($excludeProductCategories) && in_array($currentCategoryID, $excludeProductCategories)) {
			return;
		}

		$filteredAttributes = WC_Query::get_layered_nav_chosen_attributes();
		$doNotShowVariationsOnFilter = $this->get_option('doNotShowVariationsOnFilter');
		if($doNotShowVariationsOnFilter && !empty($filteredAttributes)) {
			return;
		}

		if($this->get_option('onlyShowWhenFiltered') && empty($filteredAttributes)) {
			return;
		}
		
		$original_post_types = (array) $query->get('post_type');
		if(!empty($original_post_types)) {
   			$query->set('post_type', array_merge( $original_post_types, array('product','product_variation') ) );
   		} else {
   			$query->set('post_type', array('product','product_variation'));	
   		}

		$query->set('post_status', array('publish'));
		$query->set('single_variations_filter', 'yes');

		$excludeVariableProducts = $this->get_option('excludeVariableProducts');
		if(!empty($excludeVariableProducts)){
			$post_parent__not_in = (array) $query->get('post_parent__not_in');
			$query->set('post_parent__not_in', array_merge($post_parent__not_in, $excludeVariableProducts) );
		}

		$excludeVariationProducts = $this->get_option('excludeVariationProducts');
		if(!empty($excludeVariationProducts)){
            $post__not_in = (array) $query->get('post__not_in');
            $query->set('post__not_in', array_merge( $post__not_in, $excludeVariationProducts) );
		}

		$excludedNotOnFilter = $this->get_option('excludedNotOnFilter');
		$excludedAttributes = $this->get_option('excludedAttributes');

		if($excludedNotOnFilter && !empty($filteredAttributes) && !empty($excludedAttributes) ) {
			$excludedAttributeKeys = array_flip($excludedAttributes);
			$checkInFilter = array_intersect_key($filteredAttributes, $excludedAttributeKeys);
			if(!empty($checkInFilter)) {
				$excludedAttributes = array();
			}
		}

		$excludedAttributes = apply_filters('woocommerce_single_variations_excluded_attributes', $excludedAttributes);
		if(!empty($excludedAttributes)) {

			$excludedAttributeProducts = false;
			if($this->get_option('excludedAttributesCaching')) {

				if($this->get_option('excludedAttributesCachingCategory') && !empty($currentCategoryID)) {
				    $transient_name = 'woocommerce_single_variations_excluded_attribute_products_' . $currentCategoryID;
				} else {
				    $transient_name = 'woocommerce_single_variations_excluded_attribute_products';
				}
				$excludedAttributeProducts = get_transient( $transient_name );
		    }

		    if ( false === $excludedAttributeProducts ) { 

    			$attribute_tax_query = array(
					'relation' => $this->get_option('excludedAttributesRelation'),
				);
				foreach ($excludedAttributes as $excludedAttribute) {
				   	$attribute_tax_query[] = array(
				        'taxonomy'        =>  $excludedAttribute,
				        'field'           => 'id',
				        'terms'           =>  get_terms( $excludedAttribute, [ 'fields' => 'ids'  ] ),
				        'operator'        => 'IN',
				    );
			    }

			    $tax_query = array(
				    'relation' => 'AND'
				);

			    if($this->get_option('excludedAttributesCachingCategory')) {
				    if(isset($query->tax_query) && isset($query->tax_query->queries) && !empty($query->tax_query->queries)) {
				    	$tax_query = array_merge($tax_query, $query->tax_query->queries);
				    }
			    }

			    $tax_query[] = array(
			        'taxonomy'        =>  'product_type',
			        'field'           => 'slug',
			        'terms'           =>  'variable'
			    );

			    $tax_query[] = $attribute_tax_query;

				$excludedAttributeProductsQuery = array(
				   	'post_type'      => array('product'),
				   	// use title
				   	'orderby'		=> 'menu_order',
				   	'order'			=> 'ASC',
				   	'post_status'    => 'publish',
				   	'posts_per_page' => -1,
				   	'tax_query'      => $tax_query,
				);


				$products = new WP_Query( $excludedAttributeProductsQuery );	

				if(!empty($products->posts)) {

					$excludedAttributeProducts = array();

					$alreadySetAttributes = array();
					$firstVariationProducts = array();

					foreach ($products->posts as $parentProductID) {

						$parentProductID = $parentProductID->ID;
						$parent_product = wc_get_product($parentProductID);
						if(!$parent_product) {
							continue;
						}

						$variation_ids = $parent_product->get_children();
						if(empty($variation_ids)) {
							continue;
						}

						// Exclude all size products (with color)
						$excludedAttributeProducts = array_merge($excludedAttributeProducts, $variation_ids);	
						if($this->get_option('excludedAttributesKeepFirstVariation')) {

							// Loop trough variations of a product
							$foundOneAttributeProduct = false;
							foreach ($variation_ids as $variation_id) {

								$variationProduct = wc_get_product($variation_id);

								if(!$variationProduct) {
									continue;
								}

								// Always Exclude out of stock
								if($this->get_option('excludedAttributesKeepOnStock') && !$variationProduct->is_in_stock()) {
									$excludedAttributeProducts[] = $variation_id;
									continue;
								}

								// Price filter support
								if(isset($_GET['min_price']) && isset($_GET['max_price'])) {
									$variationProductPrice = $variationProduct->get_price();
									if(empty($variationProductPrice) || $variationProductPrice <= $_GET['min_price'] || $variationProductPrice >= $_GET['max_price'] ) {
										$excludedAttributeProducts[] = $variation_id;
										continue;
									}
								}

								// Magic ... 
								$variationProductAttributes = $variationProduct->get_attributes();
								if(empty($variationProductAttributes)) {
									continue;
								}
									
								$count = 0;
								foreach ($variationProductAttributes as $variationProductAttributeKey => $variationProductAttributeValue) {
									
									if(in_array($variationProductAttributeKey, $excludedAttributes)) {
										if($this->get_option('excludedAttributesKeepOneAttributeProducts') && count($variationProductAttributes) == 1) {
											if(!$foundOneAttributeProduct) {
												$firstVariationProducts[] = $variation_id;
												$foundOneAttributeProduct = true;
											} else {
												continue;	
											}
											
										} else {
											continue;
										}
									}

									if( isset($alreadySetAttributes[$parentProductID . $variationProductAttributeKey]) && 
										in_array($variationProductAttributeValue, $alreadySetAttributes[$parentProductID . $variationProductAttributeKey])
									) {
										continue;
									}

									if(isset($alreadySetAttributes[$parentProductID . $variationProductAttributeKey])) {
										$alreadySetAttributes[$parentProductID . $variationProductAttributeKey][] = $variationProductAttributeValue;
									} else {
										$alreadySetAttributes[$parentProductID . $variationProductAttributeKey] = array($variationProductAttributeValue);
									}
									
									$firstVariationProducts[] = $variation_id;
								}
							}
						}
					}
				}

				if(!empty($firstVariationProducts)) {
					$excludedAttributeProducts = array_diff($excludedAttributeProducts, $firstVariationProducts);
				}

				if($this->get_option('excludedAttributesCaching')) {
					set_transient( $transient_name, $excludedAttributeProducts, constant( $this->get_option('excludedAttributesCachingExpiration') ));
		    	}
			}

			$post__not_in = (array) $query->get('post__not_in');
			if(!empty($excludedAttributeProducts)) {
       			$query->set('post__not_in', array_merge( $post__not_in, $excludedAttributeProducts) );
       		}
   		}

		$includeVariationProducts = $this->get_option('includeVariationProducts');
		if(!empty($includeVariationProducts)) {
			$post__not_in = (array) $query->get('post__not_in');
       		$query->set('post__not_in', array_diff( $post__not_in, $includeVariationProducts) );
		}

	}

	/**
	 * Modify Shortcode Query
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https:/welaunch.io
	 * @param   [type]             $query_args [description]
	 * @return  [type]                         [description]
	 */
	public function modify_shortcode_query($query_args)
	{

		if($this->get_option('hideOnShopPage')) {
			$currentURL = preg_replace('/\?.*/', '', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]");
			$shopPostId = (int) get_option('woocommerce_shop_page_id');
			$shopURL = get_permalink($shopPostId);
			if($shopPostId > 0 && $currentURL == $shopURL) {
				return $query_args;
			}
		}

		if(!empty($query_args['post_type'])) {
			$original_post_types = (array) $query_args['post_type'];
		}

		if($this->get_option('shortcodeOnlyShowVariations')) {
			$query_args['post_type'] = array_merge($original_post_types, array('product_variation') );
			$query_args['post_type'] = array_diff($query_args['post_type'], array('product') );
		} else {
			$query_args['post_type'] = array_merge($original_post_types, array('product','product_variation') );

			$allVariableProductsQuery = array(
			   	'post_type'      => array('product'),
			   	'order'			=> 'ASC',
			   	'post_status'    => 'publish',
			   	'posts_per_page' => -1,
			   	'tax_query'      => array(
			   		array(
				        'taxonomy'        =>  'product_type',
				        'field'           =>  'slug',
				        'terms'           =>  'variable'
			    	)
		    	)
			);

			

			$allVariableProducts = new WP_Query( $allVariableProductsQuery );	
			$allVariableProductsIds = get_transient( 'woocommerce_single_variations_all_variable_product_ids' );		
			if(!empty($allVariableProducts->posts) && $allVariableProductsIds == false) {      		
				$allVariableProductsIds = array_column( (array) $allVariableProducts->posts, 'ID');
				set_transient( 'woocommerce_single_variations_all_variable_product_ids', $allVariableProductsIds, constant( $this->get_option('excludedAttributesCachingExpiration') ));
			}
			
			if(!$allVariableProductsIds) {
				$allVariableProductsIds = array();
			}

			$post__not_in = isset($query_args['post__not_in']) ? (array) $query_args['post__not_in'] : array();
			$query_args['post__not_in'] = array_merge( $post__not_in, $allVariableProductsIds);
		}

		$query_args['post_status'] = array('publish');

		$excludeVariableProducts = $this->get_option('excludeVariableProducts');
		if(!empty($excludeVariableProducts)){
			$post_parent__not_in = isset($query_args['post_parent__not_in']) ? (array) $query_args['post_parent__not_in'] : array();
			$query_args['post_parent__not_in'] = array_merge($post_parent__not_in, $excludeVariableProducts);
		}

		$excludeVariationProducts = $this->get_option('excludeVariationProducts');
		if(!empty($excludeVariationProducts)){
            $post__not_in = isset($query_args['post__not_in']) ? (array) $query_args['post__not_in'] : array();
            $query_args['post__not_in'] = array_merge( $post__not_in, $excludeVariationProducts);
		}

		$excludedAttributes = $this->get_option('excludedAttributes');
		$excludedAttributes = apply_filters('woocommerce_single_variations_excluded_attributes', $excludedAttributes);
		
		if(!empty($excludedAttributes)) {

			$excludedAttributeProducts = false;
			if($this->get_option('excludedAttributesCaching')) {
    			$transient_name = 'woocommerce_single_variations_excluded_attribute_products';
				$excludedAttributeProducts = get_transient( $transient_name );
			}

		    if ( false === $excludedAttributeProducts ) { 

    			$attribute_tax_query = array(
					'relation' => $this->get_option('excludedAttributesRelation'),
				);
				foreach ($excludedAttributes as $excludedAttribute) {
				   	$attribute_tax_query[] = array(
				        'taxonomy'        =>  $excludedAttribute,
				        'field'           => 'id',
				        'terms'           =>  get_terms( $excludedAttribute, [ 'fields' => 'ids'  ] ),
				        'operator'        => 'IN',
				    );
			    }

			    $tax_query = array(
				    'relation' => 'AND'
				);

			    $tax_query[] = array(
			        'taxonomy'        =>  'product_type',
			        'field'           => 'slug',
			        'terms'           =>  'variable'
			    );

			    $tax_query[] = $attribute_tax_query;

				$excludedAttributeProductsQuery = array(
				   	'post_type'      => array('product'),
				   	// use title
				   	'orderby'		=> 'menu_order',
				   	'order'			=> 'ASC',
				   	'post_status'    => 'publish',
				   	'posts_per_page' => -1,
				   	'tax_query'      => $tax_query,
				   	'suppress_filters' => false,
				);

				$products = new WP_Query( $excludedAttributeProductsQuery );	
				if(!empty($products->posts)) {
					$excludedAttributeProducts = array();

					$alreadySetAttributes = array();
					$firstVariationProducts = array();

					foreach ($products->posts as $parentProductID) {

						$parentProductID = $parentProductID->ID;
						$parent_product = wc_get_product($parentProductID);
						if(!$parent_product) {
							continue;
						}

						$variation_ids = $parent_product->get_children();
						if(empty($variation_ids)) {
							continue;
						}

						// Exclude all size products (with color)
						$excludedAttributeProducts = array_merge($excludedAttributeProducts, $variation_ids);					
						if($this->get_option('excludedAttributesKeepFirstVariation')) {

							// Loop trough variations of a product
							$foundOneAttributeProduct = false;
							foreach ($variation_ids as $variation_id) {

								$variationProduct = wc_get_product($variation_id);
								
								// Always Exclude out of stock
								if($this->get_option('excludedAttributesKeepOnStock') && !$variationProduct->is_in_stock()) {
									$excludedAttributeProducts[] = $variation_id;
									continue;
								}

								// Price filter support
								if(isset($_GET['min_price']) && isset($_GET['max_price'])) {
									$variationProductPrice = $variationProduct->get_price();
									if(empty($variationProductPrice) || $variationProductPrice <= $_GET['min_price'] || $variationProductPrice >= $_GET['max_price'] ) {
										$excludedAttributeProducts[] = $variation_id;
										continue;
									}
								}

								// Magic ... 
								$variationProductAttributes = $variationProduct->get_attributes();
								if(empty($variationProductAttributes)) {
									continue;
								}
									
								$count = 0;
								foreach ($variationProductAttributes as $variationProductAttributeKey => $variationProductAttributeValue) {
									
									if(in_array($variationProductAttributeKey, $excludedAttributes)) {
										if($this->get_option('excludedAttributesKeepOneAttributeProducts') && count($variationProductAttributes) == 1) {
											if(!$foundOneAttributeProduct) {
												$firstVariationProducts[] = $variation_id;
												$foundOneAttributeProduct = true;
											} else {
												continue;	
											}
											
										} else {
											continue;
										}
									}

									if( isset($alreadySetAttributes[$parentProductID . $variationProductAttributeKey]) && 
										in_array($variationProductAttributeValue, $alreadySetAttributes[$parentProductID . $variationProductAttributeKey])
									) {
										continue;
									}

									if(isset($alreadySetAttributes[$parentProductID . $variationProductAttributeKey])) {
										$alreadySetAttributes[$parentProductID . $variationProductAttributeKey][] = $variationProductAttributeValue;
									} else {
										$alreadySetAttributes[$parentProductID . $variationProductAttributeKey] = array($variationProductAttributeValue);
									}
									
									$firstVariationProducts[] = $variation_id;
								}
							}
						}
					}
				}
				
				if(!empty($firstVariationProducts)) {
					$excludedAttributeProducts = array_diff($excludedAttributeProducts, $firstVariationProducts);
				}

				if($this->get_option('excludedAttributesCaching')) {
					set_transient( $transient_name, $excludedAttributeProducts, constant( $this->get_option('excludedAttributesCachingExpiration') ));
		    	}
			}

            $post__not_in = isset($query_args['post__not_in']) ? (array) $query_args['post__not_in'] : array();
            $query_args['post__not_in'] = array_merge( $post__not_in, $excludedAttributeProducts);
		}

		$includeVariationProducts = $this->get_option('includeVariationProducts');
		if(!empty($includeVariationProducts)) {
            $post__not_in = isset($query_args['post__not_in']) ? (array) $query_args['post__not_in'] : array();
            $query_args['post__not_in'] = array_diff( $post__not_in, $includeVariationProducts);
		}

		if(!empty($query_args['post__not_in']) && !empty($query_args['post__in'])) {
			$query_args['post__in'] = array_diff($query_args['post__in'], $query_args['post__not_in']);
		}
		
		return $query_args;
    }

    public function change_wp_query($args)
    {
		if ( $GLOBALS['wp_query']->get( 'wc_query' ) ) {
			
		}
    }

	/**
	 * Maybe hide parent products and only show main ones
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https:/welaunch.io
	 * @param   [type]             $clauses [description]
	 * @param   [type]             $query   [description]
	 * @return  [type]                      [description]
	 */
	public function maybe_hide_parent_products($clauses, $query) 
	{
		global $wpdb;

		if(isset($query->query_vars['single_variations_filter']) && 
			$query->query_vars['single_variations_filter'] == 'yes' && 
			$this->get_option('hideParentProducts'))
		{
			$clauses['where'] .= " 
			AND  0 = (select count(*) as totalpart 
			FROM {$wpdb->posts} as oc_posttb 
			WHERE oc_posttb.post_parent = {$wpdb->posts}.ID 
			AND oc_posttb.post_type = 'product_variation' 
			";

			$excludeVariableProducts = $this->get_option('excludeVariableProducts');
			if(!empty($excludeVariableProducts)) {
				$clauses['where'] .= " 	AND {$wpdb->posts}.ID NOT IN ( " . join(',', array_filter($excludeVariableProducts)) . " )";
			}

			$clauses['where'] .= ") ";
		}
		
		return $clauses;
	}

	/**
	 * Modify the category count
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https:/welaunch.io
	 * @param   [type]             $html     [description]
	 * @param   [type]             $category [description]
	 * @return  [type]                       [description]
	 */
	public function category_count($html, $category)
	{
		$excludeVariableProducts = $this->get_option('excludeVariableProducts');
		$excludeVariationProducts = $this->get_option('excludeVariationProducts');

	    $the_query = new WP_Query(array(
	        'post_type' => array('product','product_variation'),
			'post_status' => 'publish',
			'post_parent__not_in' => $excludeVariableProducts,
			'post__not_in' => $excludeVariationProducts,
	        'tax_query' => array(
	            array(
	                'taxonomy' => 'product_cat',
	                'field' => 'id',
	                'terms' => $category->term_id
	            )
	        ),
	    ));

	    $count = $the_query->found_posts;
		wp_reset_postdata();

	    $html='<mark class="count">('.esc_html($count).')</mark>';
	    return $html;
	}

	/**
	 * Modify layerd nav count
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https:/welaunch.io
	 * @param   [type]             $html  [description]
	 * @param   [type]             $count [description]
	 * @param   [type]             $term  [description]
	 * @return  [type]                    [description]
	 */
	public function layered_nav_count($html, $count, $term)
	{
		if(!$this->get_option('changeCount')) {
			return '<span class="count">(' . absint($count ) . ')</span>';
		}

		$excludeVariableProducts = $this->get_option('excludeVariableProducts');
		$excludeVariationProducts = $this->get_option('excludeVariationProducts');
		$excludeProductCategories = $this->get_option('excludeProductCategories');
		$excludedAttributes = $this->get_option('excludedAttributes');

		if(!is_array($excludedAttributes)) {
			$excludedAttributes = array();
		}

		if(isset($term->taxonomy) && in_array($term->taxonomy, $excludedAttributes)) {
			return '<span class="count">(' . 1 . ')</span>';
		}

		$queryArgs = array(
            'post_type' => array('product','product_variation'),
			'post_status' => 'publish',
			'post_parent__not_in' => $excludeVariableProducts,
			'post__not_in' => $excludeVariationProducts,
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => $term->taxonomy,
                    'field' => 'id',
                    'terms' => $term->term_id
                ),
				array(
					'taxonomy' => 'product_cat',
					'field'    => 'ids',
					'terms'    => $excludeProductCategories,
					'operator' => 'NOT IN',
				)
            ),
        );

		$productCategoryQueried = get_queried_object();
		if(isset($productCategoryQueried->taxonomy) && $productCategoryQueried->taxonomy == "product_cat") {
			$queryArgs['tax_query'][] = array(
				'taxonomy' => 'product_cat',
				'field'    => 'ids',
				'terms'    => $productCategoryQueried->term_id,
				'operator' => 'IN',
			);
		}

		$query = new WP_Query($queryArgs);


	    $count = $query->post_count;
		if($this->get_option('hideParentProducts')) {
			
			$asd = array();
			foreach ($query->posts as $post) {
				if($post->post_parent) {
					$asd[$post->post_parent] = 1;
				}

			}
			$count = $count - count($asd);
		}

	    return '<span class="count">(' . absint($count ) . ')</span>';
	}

	public function change_category_count($hmtl, $category)
	{
		if(!$this->get_option('changeCount')) {
			return ' <span class="count">(' . absint($category->count ) . ')</span>';
		}

		$originalCount = $category->count;

		$excludeVariableProducts = $this->get_option('excludeVariableProducts');
		$excludeVariationProducts = $this->get_option('excludeVariationProducts');
		$excludeProductCategories = $this->get_option('excludeProductCategories');

		if(!empty($excludeProductCategories) && in_array($category->term_id, $excludeProductCategories)) {
			$query2 = new WP_Query(array(
                'post_type' => array('product'),
				'post_status' => 'publish',
				'post_parent__not_in' => $excludeVariableProducts,
				'post__not_in' => $excludeVariationProducts,
                'posts_per_page' => -1,
                'tax_query' => array(
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'ids',
						'terms'    => array_values( $excludeProductCategories ),
					)
                ),
            ));
			
			return '<mark class="count">(' . esc_html( $query2->post_count ) . ')</mark>';
		}

		$query = new WP_Query(array(
            'post_type' => array('product','product_variation'),
			'post_status' => 'publish',
			'post_parent__not_in' => $excludeVariableProducts,
			'post__not_in' => $excludeVariationProducts,
            'posts_per_page' => -1,
            'tax_query' => array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'id',
                    'terms' => $category->term_id
                ),
				// array(
				// 	'taxonomy' => 'product_cat',
				// 	'field'    => 'ids',
				// 	'terms'    => $excludeProductCategories,
				// 	'operator' => 'NOT IN',
				// )
            ),
        ));
	    $count = $query->post_count;

		if($this->get_option('hideParentProducts')) {

			$variableProducts = wc_get_products( array( 'status' => 'publish', 'limit' => -1, 'type' => 'variable', 'category' => array( $category->name ) ) );
			if($variableProducts) {
				$count = $count - count($variableProducts);
			}
		}

		return '<mark class="count">(' . esc_html( $count ) . ')</mark>';
	}

	public function ajax_get_variation_title()
	{
		$response = array(
			'status' => false,
			'title' => '',
		);

		$variation_id = (int) $_POST['variation_id'];
		$variation = wc_get_product($variation_id);
		if(!$variation) {
			echo json_encode($response);
			die();
		}

		$parentProduct = wc_get_product($variation->get_parent_id());

		$title = $this->variation_title($parentProduct->get_name(), $variation_id);

		if(!empty($title)) {
			$response['status'] = true;
			$response['title'] = $title;
		}

		echo json_encode($response);
		die();
	}

	/**
	 * Display single Variation Title
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https:/welaunch.io
	 * @param   [type]             $title [description]
	 * @param   [type]             $id    [description]
	 * @return  [type]                    [description]
	 */
	public function variation_title($title, $id)
	{
		if(empty($id)) {
			return $title;
		}

		if($id < 1){
			return $title;
		}

		if(!$this->get_option('variationTitleEnabled')) {
			return $title;
		}

		$post_type = get_post_type($id);
		if($post_type == "product_variation") {
			$title = $this->changeVariationTitle($title, $id);
		}

		if(is_product() && !empty($_GET) && $post_type == "product" && !$this->is_related) {

			if($this->get_option('variationTitleEnabledOnSingleProductPages')) {
				$title = $this->changeSingleProductPageTitle($title, $id);
			}
		}

		return $title;
	}

	public function changeSingleProductPageTitle($title, $id)
	{
		global $product;

		$variationID = (new \WC_Product_Data_Store_CPT())->find_matching_product_variation(
		    new \WC_Product($id),
		    $_GET
		);

		if(!$variationID) {
			return $title;
		}

		// Check custom Title
		$customVariationTitle = get_post_meta($variationID, 'variation_title', true);
		if(!empty($customVariationTitle)) {
			return $customVariationTitle;
		}

		$attributes = array();
		$titleTemplate = $this->get_option('variationTitleTemplate');
		$attributesTemplate = $this->get_option('variationTitleAttributesTemplate'); // {attributes_name} {attribute_values}
		$attributeNamesAppendix = $this->get_option('variationTitleAttributeNamesAppendix'); // and
		$excludedTitleAttributes = $this->get_option('excludedTitleAttributes');

		if(!$product) {
			$product = wc_get_product($id);
		}

		if($product && is_object($product)) {
			$title = $product->get_name();
		}

		foreach ($_GET as $param => $value) {

			$checkAttribute = substr($param, 0, 10);
			if($checkAttribute != "attribute_") {
	
				continue;
			}

			$attribute = urldecode( substr($param, 10) );
			if(is_array($excludedTitleAttributes) && in_array($attribute, $excludedTitleAttributes)) {
				continue;
			}

			$variationAttributeValue = "";
			if ( taxonomy_exists( $attribute ) ) {

				$attribute_name = wc_attribute_label($attribute);

				$term = get_term_by( 'slug', $value, $attribute );
				if ( ! is_wp_error( $term ) && ! empty( $term->name ) ) {
					$variationAttributeValue = $term->name;
				}

				$variationAttributeValue = urldecode($variationAttributeValue);

			} else {
				if(is_object($product)) {
					$customAttributes = $product->get_attributes();
					if(isset($customAttributes[$attribute]) && !empty($customAttributes[$attribute])) {
						$attribute_name = $customAttributes[$attribute]->get_name();
					}
					$variationAttributeValue = urldecode($value);
				}
			}

			$variationAttributeValue = trim($variationAttributeValue);
			if(empty($variationAttributeValue)) {
				continue;
			}

			$search = array(
				'{attributes_name}',
				'{attribute_values}'
			);

			$replace = array(
				$attribute_name,
				$variationAttributeValue
			);

			$attributes[] = str_replace($search, $replace, $attributesTemplate);
		}
	

		if(empty($attributes)) {
			return $title;
		}

		$attributes = implode($attributeNamesAppendix, $attributes);

		$search = array(
			'{title}',
			'{attributes}'

		);
		$replace = array(
			$title,
			$attributes
		);

		$title = str_replace($search, $replace, $titleTemplate);
		$title = str_replace('&#8211;', '', $title);
		$title = str_replace('  ', ' ', $title);

		return $title;
	}

	public function changeVariationTitle($title, $id) 
	{
		// Check custom Title
		$customVariationTitle = get_post_meta($id, 'variation_title', true);
		if(!empty($customVariationTitle)) {
			return $customVariationTitle;
		}

		$attributes = array();
		$titleTemplate = $this->get_option('variationTitleTemplate');
		$attributesTemplate = $this->get_option('variationTitleAttributesTemplate'); // {attributes_name} {attribute_values}
		$attributeNamesAppendix = $this->get_option('variationTitleAttributeNamesAppendix'); // and
		$excludedTitleAttributes = $this->get_option('excludedTitleAttributes');

		$variationProduct = wc_get_product($id);
		$variationAttributes = $variationProduct->get_attributes();
		$product = wc_get_product($variationProduct->get_parent_id());

		$originalTitle = $title;
		
		$splitted = false;
		// $splittedTitle = explode('&#8211;', $originalTitle);
		
		// if(!empty($splittedTitle[0]) && !empty($splittedTitle[1])) {
		// 	$splitted = true;
		// 	$title = $splittedTitle[1];
		// }
		if($product && is_object($product)) {
			$title = $product->get_name();
		}

		foreach ($variationAttributes as $variationAttributeKey => $variationAttributeValue) {

			$variationAttributeKey = urldecode($variationAttributeKey);
			$variationAttributeValue = urldecode($variationAttributeValue);

			if ( taxonomy_exists( $variationAttributeKey ) ) {

				$attribute_name = wc_attribute_label($variationAttributeKey);

				$term = get_term_by( 'slug', $variationAttributeValue, $variationAttributeKey );
				if ( ! is_wp_error( $term ) && ! empty( $term->name ) ) {
					$variationAttributeValue = $term->name;
				}

				$variationAttributeValue = urldecode($variationAttributeValue);

			} else {
				if(is_object($product)) {
					$customAttributes = $product->get_attributes();

					if(isset($customAttributes[$variationAttributeKey]) && !empty($customAttributes[$variationAttributeKey])) {
						$attribute_name = $customAttributes[$variationAttributeKey]->get_name();
					}
					$variationAttributeValue = urldecode($variationAttributeValue);
				}
			}

			$variationAttributeValue = trim($variationAttributeValue);
			if(empty($variationAttributeValue)) {
				continue;
			}

			if(is_array($excludedTitleAttributes) && in_array($variationAttributeKey, $excludedTitleAttributes)) { 
				continue;
			}

			$search = array(
				'{attributes_name}',
				'{attribute_values}'
			);

			$replace = array(
				$attribute_name,
				$variationAttributeValue,
			);

			$attributes[] = str_replace($search, $replace, $attributesTemplate);
		}

		if(empty($attributes)) {
			return $title;
		}

		$attributes = implode($attributeNamesAppendix, $attributes);

		$search = array(
			'{title}',
			'{attributes}'

		);
		$replace = array(
			$title,
			$attributes
		);

		$title = str_replace($search, $replace, $titleTemplate);
		$title = str_replace('&#8211;', '', $title);
		$title = str_replace('  ', ' ', $title);

		return $title;
	}

	public function add_variations_to_search_results( $query ) {

		$showVariationsInSearch = $this->get_option('showVariationsInSearch');
		if(!$showVariationsInSearch) {
			return;
		}

		if(is_admin()) {
			return;
		}

	    if ( $query->is_main_query() && $query->is_search() ) {
			$post_types = (array) $query->get('post_type');
			$query->set('post_types', array_merge($post_types, array('product_variation') ));

			$tax_query = array (
				array(
				    'taxonomy' => 'product_type',
				    'terms' => array( 'variable' ),
				    'field' => 'slug',
				    'operator' => 'NOT IN',
				)
			);
        	$query->set( 'tax_query', $tax_query );
	    }
	}

	public function addVariationClasses($classes, $product )
	{
		if(!$product) {
			return $classes;
		}

		if(is_single()) {
			return $classes;
		}

		$excludedAttributes = $this->get_option('excludedAttributes');

		$attributes = $product->get_attributes();
		foreach ($attributes as $attribute_key => $attribute_value) {

			// if(in_array($attribute_key, $excludedAttributes)) {
			// 	$classes[] = 'hidden';
			// }
			$classes[] = 'woocommerce-single-variations-attribute-' . $attribute_key;
		}

		return $classes;
	}


	public function modify_variation_rating_counts($value, $variation)
	{
   		if(!$this->get_option('variationRatings')) {
   			return $value;
   		}

		if(!empty($value)) {
			return $value;
		}

		if(!$variation) {
			return $value;
		}

		$parent_product = wc_get_product($variation->get_parent_id());
		if(!$parent_product) {
			return $value;
		}

		$parent_value = $parent_product->get_rating_counts();
		if(!empty($parent_value)) {
			$value = $parent_value;
		}

		return $value;
	}

	public function modify_variation_average_rating($value, $variation)
	{
   		if(!$this->get_option('variationRatings')) {
   			return $value;
   		}

		if(!empty($value)) {
			return $value;
		}

		if(!$variation) {
			return $value;
		}

		$parent_product = wc_get_product($variation->get_parent_id());
		if(!$parent_product) {
			return $value;
		}

		$parent_value = $parent_product->get_average_rating();
		if(!empty($parent_value)) {
			$value = $parent_value;
		}
		
		return $value;
	}

	public function modify_variation_review_count($value, $variation)
	{
   		if(!$this->get_option('variationRatings')) {
   			return $value;
   		}

		if(!empty($value)) {
			return $value;
		}

		if(!$variation) {
			return $value;
		}

		$parent_product = wc_get_product($variation->get_parent_id());
		if(!$parent_product) {
			return $value;
		}

		$parent_value = $parent_product->get_review_count();
		if(!empty($parent_value)) {
			$value = $parent_value;
		}

		return $value;
	}

	public function shortcode($atts)
	{
		$html = "";

		$products_in = "";
		$categories_in = "";

		$args = shortcode_atts( array(
	        'products' => '',
	        'categories' => '',
	        'order' => 'DESC',
	        'orderby' => 'menu_order',
	    ), $atts );

		if(!empty($args['products'])) {
	    	$products_in = explode(',', $args['products']);
	    	$products_in = $products_in;
	    } elseif(!empty($args['categories'])) {
	    	$categories_in = explode(',', $args['categories']);
	    	$categories_in = $categories_in;
	    }

	    $order = $args['order'];
	    $orderby = $args['orderby'];

	    $args = array(
		    'post_type'             => 'product_variation',
		    'post_status'           => 'publish',
		    'ignore_sticky_posts'   => 1,
		    'posts_per_page'        => -1,
		    'post__in'				=> $products_in,
		    'order'					=> $order,
		    'orderby'				=> $orderby,
		);

		if(!empty($categories_in)) {
			$args['tax_query'] = array(
		        array(
		            'taxonomy'      => 'product_cat',
		            'field' 		=> 'term_id', //This is optional, as it defaults to 'term_id'
		            'terms'         => $categories_in,
		            'operator'      => 'IN' // Possible values are 'IN', 'NOT IN', 'AND'.
		        ),
	        );
		}

	    $products = new WP_Query($args);
	    
	    if(!isset($products->posts) || empty($products->posts)) {
	    	return __('No products found', 'woocommerce-single-variations');
	    }

	    $products = $products->posts;
		$original_post = $GLOBALS['post'];

		ob_start();

		woocommerce_product_loop_start();

		foreach ( $products as $product ) {
			$GLOBALS['post'] = get_post( $product->ID ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			setup_postdata( $GLOBALS['post'] );
			wc_get_template_part( 'content', 'product' );
		}

		$GLOBALS['post'] = $original_post; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
		woocommerce_product_loop_end();

		$output = ob_get_contents();
		ob_end_clean();

		return $output;

	}

	public function seo_change_canonical( $canonical_url, $post ) 
	{

		if(!$this->get_option('seoCanonical')) {
			return $canonical_url;
		}

		$post_type = get_post_type($post->ID);

		if(is_product() && !empty($_GET) && $post_type == "product") {

			$variationID = (new \WC_Product_Data_Store_CPT())->find_matching_product_variation(
			    new \WC_Product($post->ID),
			    $_GET
			);

			if($variationID) {

				$variation = wc_get_product($variationID);
				if($variation) {
					$canonical_url = $variation->get_permalink();
				}
			}
		}

	    return $canonical_url;
	}

	public function seo_add_variations_to_sitemap($post_types)
	{
		if(!$this->get_option('seoSitemap')) {
			return $post_types;
		}

		$post_types[] = 'product_variation';
		return $post_types;
	}

	public function seo_change_meta_title($title) 
	{
		if(!$this->get_option('seoMetaTitle')) {
			return $title;
		}

		if(is_admin()) {
			return $title;
		}

		global $post;

		if(!$post) {
			return $title;
		}

		$id = $post->ID;
		$post_type = get_post_type($id);

		if(is_product() && !empty($_GET) && $post_type == "product") {

			$title = $post->post_title;
			if($this->get_option('variationTitleEnabledOnSingleProductPages')) {
				$title = $this->changeSingleProductPageTitle($title, $id);
			}
			$title .= ' %%sep%% %%sitename%%';
		}

	    return wpseo_replace_vars( $title, $post );
	}

	public function seo_change_meta_desc($desc) 
	{
		if(!$this->get_option('seoMetaDesc')) {
			return $desc;
		}

		if(is_admin()) {
			return $desc;
		}

		global $post;
		if(!$post) {
			return $desc;
		}

		$id = $post->ID;
		$post_type = get_post_type($id);

		if(is_product() && !empty($_GET) && $post_type == "product") {

			$variationID = (new \WC_Product_Data_Store_CPT())->find_matching_product_variation(
			    new \WC_Product($post->ID),
			    $_GET
			);

			if($variationID) {

				$variation = wc_get_product($variationID);
				if($variation) {
					$desc = $variation->get_description();
				}
			}
		}

	    return wpseo_replace_vars( $desc, $post );
	}

	public function show_variations_in_related_products($related_post_ids, $product_id, $args)
	{
		if(empty($related_post_ids)) {
			return $related_post_ids;
		}

		if(!$this->get_option('showVariationsInRelated')) {
			return $related_post_ids;
		}

		$this->is_related = true;

		foreach ($related_post_ids as $related_post_key => $related_post_id) {
			$related_product = wc_get_product($related_post_id);
			if(!$related_product) {
				continue;
			}

			if(!$related_product->is_type('variable')) {
				continue;
			}

			unset($related_post_ids[$related_post_key]);

			$related_product_variation_ids = $related_product->get_children();

			if(!empty($related_product_variation_ids)) {
				$related_post_ids = array_merge( $related_post_ids, $related_product_variation_ids);
			}
		}

		$related_post_ids = array_unique($related_post_ids);

		return $related_post_ids;
	}
}