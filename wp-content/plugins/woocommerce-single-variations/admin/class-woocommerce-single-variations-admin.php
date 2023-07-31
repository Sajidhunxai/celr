<?php

/**
 * The admin-specific functionality of the plugin.
 *
 * @link       http://plugins.db-dzine.com
 * @since      1.0.0
 *
 * @package    WooCommerce_Single_Variations
 * @subpackage WooCommerce_Single_Variations/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WooCommerce_Single_Variations
 * @subpackage WooCommerce_Single_Variations/admin
 * @author     Daniel Barenkamp <support@db-dzine.com>
 */
class WooCommerce_Single_Variations_Admin extends WooCommerce_Single_Variations {

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
	 * Construct the Class
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https://welaunch.io
	 * @param   [type]                       $plugin_name [description]
	 * @param   [type]                       $version     [description]
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Load Redux
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https://welaunch.io
	 * @return  [type]                       [description]
	 */
	public function load_redux()
	{
	    if ( file_exists( plugin_dir_path( dirname( __FILE__ ) ) . 'admin/options-init.php' ) ) {
	        require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/options-init.php';
	    }
	}

    /**
     * Enqueue Admin Scripts
     * @author Daniel Barenkamp
     * @version 1.0.0
     * @since   1.0.0
     * @link    http://plugins.db-dzine.com
     * @return  boolean
     */
    public function enqueue_scripts()
    {
    	$forJS = array(
    		'ajax_url' => admin_url( 'admin-ajax.php' ),
    	);

        wp_enqueue_script($this->plugin_name . '-admin', plugin_dir_url(__FILE__).'js/woocommerce-single-variations-admin.js', array('jquery'), $this->version, true);
        wp_localize_script($this->plugin_name . '-admin', 'woocommerce_single_variations_options', $forJS);
    }

	/**
	 * Init 
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https:/welaunch.io
	 * @return  [type]             [description]
	 */
	public function init()
	{
		global $woocommerce_single_variations_options, $variations_saved_loop;

		$variations_saved_loop = 0;

		$this->options = $woocommerce_single_variations_options;

		if (!$this->get_option('enable')) {
			return false;
		}

		add_action('woocommerce_product_after_variable_attributes',array($this, 'add_variation_title_field'), 10, 3 ); 
		add_action('woocommerce_save_product_variation', array($this, 'save_product_variation'), 10, 2 );
		add_action('transition_post_status',array($this, 'new_variable_product_published'), 10, 3 ); 
	}

	/**
	 * Add variation title backend field
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https:/welaunch.io
	 * @param   [type]             $loop           [description]
	 * @param   [type]             $variation_data [description]
	 * @param   [type]             $variation      [description]
	 */
	public function add_variation_title_field($loop, $variation_data, $variation)
	{
		echo '<div class="form-field variation form-row variation" data-id="'.$loop.'">';

			$value = get_post_meta($variation->ID, 'variation_title',true);
			woocommerce_wp_text_input( array( 
	            'id'           => 'variation_title['.esc_attr($loop).']', 
	            'label'        => esc_html__('Variation Title', 'woocommerce-single-variations'),
	            'type'  => 'text',
				'value'	=> $value
	        ));
		echo '</div>';
	}

	/**
	 * Save Variation Data (title)
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https:/welaunch.io
	 * @param   [type]             $variation_id [description]
	 * @param   [type]             $i            [description]
	 * @return  [type]                           [description]
	 */
	public function save_product_variation($variation_id, $i)
	{
		global $variations_saved_loop;

		// Performance
		// woocommerce_save_product_variation get calles every time a variation gets saved
		// need to hook here, because otherwise it ovberwrites custom sorting again ...
		$variations_saved_loop++;
		if($variations_saved_loop != count($_POST['variable_post_id']) ) {
			return;
		}

		if(empty($variation_id)) {
			return;
		}

		if(!isset($_POST['product_id'])){
			return;
		}

		$product_id = absint($_POST['product_id']);
		$parent_product = wc_get_product( $product_id );
		if(!$parent_product) {
			return;
		}

		if(!isset($_POST['product_id']) || !isset($_POST['product-type']) || !isset($_POST['variable_post_id'])) {
			return;
		}

		if(!empty($_POST['variation_title'][$i])){
			update_post_meta($variation_id,'variation_title', sanitize_text_field($_POST['variation_title'][$i]));
		} else {
			update_post_meta($variation_id,'variation_title', '');
		}

		$variation_ids = $_POST['variable_post_id'];
		$variation_order = $_POST['variation_menu_order'];
		$variable_enabled = $_POST['variable_enabled'];

		$product_id = absint($_POST['product_id']);
		$parent_product = wc_get_product( $product_id );
		if(!$parent_product) {
			return;
		}

		$parent_product_order = $parent_product->get_menu_order();
		$parent_product_status = $parent_product->get_status();

		foreach ($variation_ids as $index => $variation_id) {

			$variation = new WC_Product_Variation($variation_id);
			if(!$variation) {
				continue;
			}

			if($parent_product_status !== "auto-draft" && $parent_product_status !== "draft") {
				// $variation->set_status( $parent_product->get_status() );
				if(isset($variable_enabled[$index]) && $variable_enabled[$index] == "on") {
					$variation->set_status( 'publish' );
				} else {
					$variation->set_status( 'private' );
				}
			} else {
				$variation->set_status( 'private' );
			}

			// $variation->set_menu_order($parent_product_order . $variation_order[$index]);
			if($this->get_option('variationMenuOrder')) {
				$variation->set_menu_order($parent_product_order);
			}
			$variation->save();

			delete_post_meta($variation_id, 'woocommerce_single_variations_updated');
		}

		$this->update_variations(false);

	}

	public function reset_variations()
	{
  		if(!current_user_can( 'administrator' )) {
  			return false;
  		}
		
		global $wpdb;
		
		$args = array(
		   	'post_type' => 'product_variation',
		   	'posts_per_page' => -1,
		   	'post_status'    => 'any',
	   	);

        $taxonomies = array(
            'product_cat',
            'product_tag'
        );
            
		$taxonomies = apply_filters( 'woocommerce_single_variations_taxonomies', $taxonomies );
		$attributes = wc_get_attribute_taxonomies();
        if(!empty($attributes)){
            foreach ($attributes as $attribute) {
                $taxonomies[] = 'pa_' . $attribute->attribute_name;
            }
        }

	   	$posts = get_posts($args);
		foreach ($posts as $post) {

			$variation_id = $post->ID;

            delete_post_meta($variation_id, 'woocommerce_single_variations_updated');
            wp_delete_object_term_relationships($variation_id, $taxonomies);
		}

        $sql = "
            DELETE 
            FROM {$wpdb->options}
            WHERE option_name like '_transient_woocommerce_single_variations_%'
            OR option_name like '_transient_timeout_woocommerce_single_variations_%'
        ";

        $wpdb->query($sql);

		wp_redirect(  get_admin_url().'admin.php?page=woocommerce_single_variations_options_options' );
		exit;
	}

	/**
	 * Sets category & tags for variations
	 * Important because otherwise categories will not show single variations
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https:/welaunch.io
	 * @param   [type]             $variation_id [description]
	 * @param   [type]             $i            [description]
	 * @return  [type]                           [description]
	 */
	public function update_variations($redirect = true, $update_all = false)
	{

		$args = array(
		   	'post_type' => 'product_variation',
		   	'posts_per_page' => -1,
		   	'post_status' => 'any',
			
	   	);

	   	if(!$update_all) {
	   		$args['meta_query'] = array(
		        array(
		            'key'     => 'woocommerce_single_variations_updated',
		            'compare' => 'NOT EXISTS'
        		),		   	
    		);
	   	}

		$excludedAttributes = $this->get_option('excludedAttributes');

		$posts = get_posts($args);
		foreach ($posts as $post) {

			$variation_id = $post->ID;
			$parent_product_id = wp_get_post_parent_id( $variation_id );
	        if( !$parent_product_id ) {
	        	continue;
        	} 

        	$parent_product = wc_get_product( $parent_product_id );
			if(!$parent_product) {
				continue;
			}

			if(!$update_all) {
				$checkUpdated = get_post_meta($variation_id, 'woocommerce_single_variations_updated', true);
				if($checkUpdated) {
					continue;
				}
			}

			update_post_meta($variation_id, 'woocommerce_single_variations_updated', true);

            $taxonomies = array(
                'product_cat',
                'product_tag'
            );

            $taxonomies = apply_filters( 'woocommerce_single_variations_taxonomies', $taxonomies );

            foreach( $taxonomies as $taxonomy ) {
                $terms = (array) wp_get_post_terms( $parent_product_id, $taxonomy, array("fields" => "ids") );
                wp_set_post_terms( $variation_id, $terms, $taxonomy );

            }

			$variation = new WC_Product_Variation($variation_id);
			if(!$variation) {
				return;
			}
					

            $attributes = $variation->get_variation_attributes();
            if(!empty($attributes)){
                foreach ($attributes as $key => $term) {
                    $attr_tax = urldecode( str_replace('attribute_', '', $key) );
                    // if(in_array($attr_tax, $excludedAttributes)){
                    // 	continue;
                    // }

                    wp_set_post_terms($variation_id, $term, $attr_tax);
                }
            }

            $parent_product_status = $parent_product->get_status();
            if($parent_product_status !== "auto-draft" && $parent_product_status !== "draft") {
            	// $variation->set_status( $parent_product->get_status() );
            } else {
				$variation->set_status( 'private' );
			}

            $dateCreated = $parent_product->get_date_created();

            $variation->set_date_created($dateCreated);
            $variation->save();

			$parent_attributes = $parent_product->get_attributes();
            if(!empty($parent_attributes)){
                foreach ($parent_attributes as $parent_attribute) {
                	if($parent_attribute->get_variation() == true) {
                		continue;
                	}

                    $attr_tax = $parent_attribute->get_taxonomy();
                    // if(in_array($attr_tax, $excludedAttributes)){
                    // 	continue;
                    // }
                    
                    $terms = (array) $parent_attribute->get_terms();
                    if(!empty($terms)) {
                    	$tmp = array();
                    	foreach ($terms as $term) {
                			$tmp[] = $term->term_id;
                    	}

                    	wp_set_post_terms($variation_id, $tmp, $attr_tax);
                    }
                    
                }
            }
		}

		if($redirect) {
			wp_redirect(  get_admin_url().'admin.php?page=woocommerce_single_variations_options_options' );
			exit;
		}
	}

	/**
	 * Sets category & tags for variations via AJAX
	 * Important because otherwise categories will not show single variations
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https:/welaunch.io
	 * @param   [type]             $variation_id [description]
	 * @param   [type]             $i            [description]
	 * @return  [type]                           [description]
	 */
	public function ajax_update_variations($redirect = true)
	{

		// Idea
		// 1. Get all variable 
		// 2. get their variations (children)
		// 3. no
		// $query_args = array(
		// 	'post_type' => 'product',
		// 	'posts_per_page' => -1,
		// 	'tax_query' => array(
		// 		array(
		// 			'taxonomy' => 'product_type',
		// 			'field' => 'slug',
		// 			'terms' => 'variable',
		// 		),
		// 	),
		// );
		// $query = new WP_Query($query_args);

		$loop = (int) $_POST['loop'];

		$response = array(
			'updated' => 0,
			'already_updated' => 0,
		);

		$args = array(
		   	'post_type' => 'product_variation',
		   	'post_status'    => 'any',
		   	'posts_per_page' => 50,
		   	'offset' => $loop * 50,
	   	);

		$excludedAttributes = $this->get_option('excludedAttributes');

		$posts = get_posts($args);
	
		foreach ($posts as $post) {

			$variation_id = $post->ID;
			$parent_product_id = wp_get_post_parent_id( $variation_id );
	        if( !$parent_product_id ) {
	        	continue;
        	} 

        	$parent_product = wc_get_product( $parent_product_id );
			if(!$parent_product) {
				continue;
			}

			$checkUpdated = get_post_meta($variation_id, 'woocommerce_single_variations_updated', true);
			if($checkUpdated) {
				$response['already_updated']++;
				continue;
			}

			update_post_meta($variation_id, 'woocommerce_single_variations_updated', true);
			$response['updated']++;

            $taxonomies = array(
                'product_cat',
                'product_tag'
            );

            $taxonomies = apply_filters( 'woocommerce_single_variations_taxonomies', $taxonomies );

            foreach( $taxonomies as $taxonomy ) {
                $terms = (array) wp_get_post_terms( $parent_product_id, $taxonomy, array("fields" => "ids") );
                wp_set_post_terms( $variation_id, $terms, $taxonomy );

            }

			$variation = new WC_Product_Variation($variation_id);
			if(!$variation) {
				return;
			}
			
            $parent_product_status = $parent_product->get_status();
            if($parent_product_status !== "auto-draft" && $parent_product_status !== "draft") {
				$variation->set_status( $parent_product->get_status() );
			} else {
				$variation->set_status( 'private' );
			}

			$parent_product_order = $parent_product->get_menu_order();
			$parent_product_order_length = strlen($parent_product_order);
			$variation_menu_order = $variation->get_menu_order();
			// $variation->set_menu_order($parent_product_order . substr($variation_menu_order, $parent_product_order_length) );
			if($this->get_option('variationMenuOrder')) {
				$variation->set_menu_order($parent_product_order );
			}

            $attributes = $variation->get_variation_attributes();
            if(!empty($attributes)){
                foreach ($attributes as $key => $term) {
                    $attr_tax = urldecode( str_replace('attribute_', '', $key) );
                    // if(in_array($attr_tax, $excludedAttributes)){
                    // 	continue;
                    // }

                    wp_set_post_terms($variation_id, $term, $attr_tax);
                }
            }

            $dateCreated = $parent_product->get_date_created();

            $variation->set_date_created($dateCreated);
            $variation->save();

			$parent_attributes = $parent_product->get_attributes();
            if(!empty($parent_attributes)){
                foreach ($parent_attributes as $parent_attribute) {
                	if($parent_attribute->get_variation() == true) {
                		continue;
                	}

                    $attr_tax = $parent_attribute->get_taxonomy();
   
                    $terms = (array) $parent_attribute->get_terms();
                    if(!empty($terms)) {
                    	$tmp = array();
                    	foreach ($terms as $term) {
                			$tmp[] = $term->term_id;
                    	}

                    	wp_set_post_terms($variation_id, $tmp, $attr_tax);
                    }
                    
                }
            }
		}


		wp_die( json_encode($response) );
		exit;
		
	}

	/**
	 * Sets category & tags for variations via AJAX
	 * Important because otherwise categories will not show single variations
	 * @author Daniel Barenkamp
	 * @version 1.0.0
	 * @since   1.0.0
	 * @link    https:/welaunch.io
	 * @param   [type]             $variation_id [description]
	 * @param   [type]             $i            [description]
	 * @return  [type]                           [description]
	 */
	public function ajax_reset_variations($redirect = true)
	{
		global $wpdb;

		$loop = (int) $_POST['loop'];

		$response = array(
			'updated' => 0,
			'already_updated' => 0,
		);

		$args = array(
		   	'post_type' => 'product_variation',
		   	'post_status'    => 'any',
		   	'posts_per_page' => 50,
		   	'offset' => $loop * 50,
	   	);

      	$taxonomies = array(
            'product_cat',
            'product_tag'
        );
            
		$taxonomies = apply_filters( 'woocommerce_single_variations_taxonomies', $taxonomies );
		$attributes = wc_get_attribute_taxonomies();
        if(!empty($attributes)){
            foreach ($attributes as $attribute) {
                $taxonomies[] = 'pa_' . $attribute->attribute_name;
            }
        }

	   	$posts = get_posts($args);
		foreach ($posts as $post) {

			$variation_id = $post->ID;

			$response['updated']++;

            delete_post_meta($variation_id, 'woocommerce_single_variations_updated');
            wp_delete_object_term_relationships($variation_id, $taxonomies);
		}

        $sql = "
            DELETE 
            FROM {$wpdb->options}
            WHERE option_name like '_transient_woocommerce_single_variations_%'
            OR option_name like '_transient_timeout_woocommerce_single_variations_%'
        ";

        $wpdb->query($sql);

		wp_die( json_encode($response) );
		exit;
		
	}


	public function reset_variation_menu_order($sorting_id, $menu_orders)
	{
		if(!isset($_POST['id']) || empty($_POST['id'])) {
			return;
		}

		// Sort current product
		$product_id = absint($_POST['id']);
		$parent_product = wc_get_product( $product_id );
		if(!$parent_product) {
			return;
		}

		if(!$parent_product->is_type('variable')) {
			return;
		}

		$variation_ids = $parent_product->get_children();
		if(empty($variation_ids)) {
			return;
		}

		$parent_product_order = $parent_product->get_menu_order();
		$parent_product_order_length = strlen($parent_product_order);
		foreach ($variation_ids as $variation_id) {

			$variation = new WC_Product_Variation($variation_id);
			if(!$variation) {
				continue;
			}
			$variation_menu_order = $variation->get_menu_order();

			// $variation->set_menu_order($parent_product_order . substr($variation_menu_order, $parent_product_order_length) );

			if($this->get_option('variationMenuOrder')) {
				$variation->set_menu_order($parent_product_order );
			}

			$variation->save();

		}

		// Sort next Product
		$product_id = absint($_POST['nextid']);
		$parent_product = wc_get_product( $product_id );
		if(!$parent_product) {
			return;
		}

		if(!$parent_product->is_type('variable')) {
			return;
		}

		$variation_ids = $parent_product->get_children();
		if(empty($variation_ids)) {
			return;
		}

		$parent_product_order = $parent_product->get_menu_order();
		$parent_product_order_length = strlen($parent_product_order);
		foreach ($variation_ids as $variation_id) {

			$variation = new WC_Product_Variation($variation_id);
			if(!$variation) {
				continue;
			}
			$variation_menu_order = $variation->get_menu_order();

			// $variation->set_menu_order($parent_product_order . substr($variation_menu_order, $parent_product_order_length) );
			if($this->get_option('variationMenuOrder')) {
				$variation->set_menu_order($parent_product_order );
			}
			$variation->save();

		}	
	}

 	public function new_variable_product_published($new_status, $old_status, $post) 
 	{

 		if(!in_array( $post->post_type, array( 'product') ) ) {
 			return;
 		}

 		if(!isset($post->ID) || empty($post->ID)) {
 			return;
 		}

 		// if( $old_status != 'publish' && $new_status == 'publish' ) {

			$product_id = $post->ID;

			$parent_product = wc_get_product( $product_id );
			if(!$parent_product) {
				return;
			}

			if(!$parent_product->is_type('variable')) {
				return;
			}

			$variation_ids = $parent_product->get_children();
			if(empty($variation_ids)) {
				return;
			}

			foreach ($variation_ids as $index => $variation_id) {

				$variation = new WC_Product_Variation($variation_id);
				if(!$variation) {
					continue;
				}

				delete_post_meta($variation_id, 'woocommerce_single_variations_updated');
			}

			$this->update_variations(false);
		// }

  	}

  	public function reset_transients()
  	{
  		if(!current_user_can( 'administrator' )) {
  			return false;
  		}

        global $wpdb;

        $sql = "
            DELETE 
            FROM {$wpdb->options}
            WHERE option_name like '_transient_woocommerce_single_variations_%'
            OR option_name like '_transient_timeout_woocommerce_single_variations_%'
        ";

        $wpdb->query($sql);

		wp_redirect(  get_admin_url().'admin.php?page=woocommerce_single_variations_options_options' );
		exit;
  	}
}	