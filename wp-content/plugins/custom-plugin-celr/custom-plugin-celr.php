<?php
/*
Plugin Name: Custom Plugin Celr
Description: Custom Plugin Based on Requirements
Version: 1.0
Author: Sajid Hunxai
*/


// buyer request
require_once('buyer-request/buyer-account.php');
require_once('buyer-request/buyer-offer.php');
require_once('buyer-request/buyer-request-database-create.php');

// single product page
require_once('single-product-page/components/display-product-attributes.php');
require_once('single-product-page/components/display-market-price.php');
require_once('single-product-page/components/display-vendor.php');
require_once('single-product-page/components/vintage-filter.php');
require_once('single-product-page/required-functions.php');

// adding search vendor list item
require_once('vendor-add-listing/vendor-meta-box.php');
require_once('vendor-add-listing/backend-display-vendor-data.php');
require_once('vendor-add-listing/backend-remove-vendor-data.php');
require_once('vendor-add-listing/backend-save-vendor-data.php');
require_once('vendor-add-listing/frontend-vendor-listing-form.php');
require_once('vendor-add-listing/frontend-product-search.php');
require_once('vendor-add-listing/frontend-vendor-save-data.php');

// dashboard files
require_once('dashboard/current-user-listing.php');
require_once('dashboard/current-user-sales.php');
require_once('dashboard/current-user-total-sales.php');
require_once('dashboard/current-user-listing-quantity.php');
require_once('dashboard/current-user-listing-returns.php');
require_once('dashboard/current-user-listing-sold-quantity.php');
require_once('dashboard/current-user-statistics.php');

// Backend Celr 
require_once('celr-backend/celr-menu-backend.php');
require_once('celr-backend/celr-product-location.php');
require_once('celr-backend/warehouse-page-backend.php');
require_once('celr-backend/celr-backend-dashboard-page.php');

//mailing
require_once('celr-mailing/celr-checkout-mail.php');
//vendor registration
require_once('vendor-registration/celr-vendor-registration.php');
//search
require_once('celr-search/search-result.php');
require_once('celr-search/main-celr-search.php');

function get_celr_dir() {
    return plugin_dir_url(__FILE__);
}

function custom_search_filter($query) {
    if ($query->is_search && !is_admin() ) {
        global $wpdb;

        // Customize this to match your taxonomy name
       

        // Perform a search for posts/pages with the search query
        $query->set('post_type', array('product', 'post'));
		

       
    }
    return $query;
}
add_filter('pre_get_posts','custom_search_filter');

function enqueue_product_search_scripts() {
    wp_enqueue_script(
        'product-search',
        plugin_dir_url(__FILE__) . 'assets/js/addListingProductSearch.js',
        array('jquery'),
        '1.0',
        true
    );

    // Localize the AJAX URL
    wp_localize_script(
        'product-search',
        'productSearchAjax',
        array('ajaxurl' => admin_url('admin-ajax.php'))
    );
}
add_action('wp_enqueue_scripts', 'enqueue_product_search_scripts');

function add_theme_scripts() {
    wp_enqueue_style( 'style', get_stylesheet_uri() );
    wp_enqueue_style( 'slider', plugin_dir_url(__FILE__) . 'assets/css/custom-plugin-celr.css', array(), '1.0', 'all' );
    
    wp_enqueue_script( 'script', plugin_dir_url(__FILE__) . 'assets/js/custom-plugin-celr.js', array( 'jquery' ), 1.0, true );
	wp_enqueue_script('multi-step-scripts', plugin_dir_url(__FILE__) . 'assets/js/vendor-registration.js', array('jquery'), '1.0', true);

    wp_localize_script('script', 'ajax_object', array( // Add this line
        'ajax_url' => admin_url('admin-ajax.php'),
    ));

    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'add_theme_scripts' );

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');




// Custom function to add "Hello" after the product title on the product archive page
// function add_hello_to_product_title( $title, $id = null ) {
//     if ( is_post_type_archive( 'product' ) && ! is_admin() ) {
//         $title .= ' Hello';
//     }
//     return $title;
// }
// add_filter( 'the_title', 'add_hello_to_product_title', 10, 2 );

// Add this code to your theme's functions.php file or a custom plugin



/**
 * Custom function to add a title prefix to product titles on archive product page.
 */
function custom_add_product_title_prefix( $title ) {
    if ( is_post_type_archive( 'product' ) && in_the_loop() ) {
        // Customize the title prefix as per your needs
        			$vintage_shortcode_result = do_shortcode('[show_vintage]');

        $title =   $title .' '. $vintage_shortcode_result ;
    }
    return $title;
}
add_filter( 'the_title', 'custom_add_product_title_prefix' );


/**
 * Custom function to add something after the product price on archive product page.
 */
function custom_add_something_after_price() {
    if ( is_post_type_archive( 'product' ) && in_the_loop() ) {
        // Output the extra content after the product price.
        $price_diff = do_shortcode('[price_difference]');

        echo '<div class="custom-extra-content">'.$price_diff.'​</div>';
    }
}
add_action( 'woocommerce_after_shop_loop_item', 'custom_add_something_after_price' );

function pph_display_price_history_chart_shortcode() {
    // Check if the user is logged in
//     if (is_user_logged_in()) {
//                 ob_start();
//         // Replace 'Product_Price_History' and '2.1.6' with actual plugin name and version
//         $plugin_name = 'Product_Price_History';
//         $plugin_version = '2.1.6';
        
//         // Create an instance of the Chart_Public class
//         $chart_module = new \Devnet\PPH\Modules\Chart\Chart_Public($plugin_name, $plugin_version);

// 		   $chart_module->price_history_output();
// 		   return ob_get_clean();

		
//     } else {
        // User is not logged in, show custom HTML content
        return '<div class="hidden-price-chart-main-div">
  <div class="price-chart-main" >
    <div class="price-chart-box">
      <img
        src="http://celr.co.uk/wp-content/uploads/2023/05/Screenshot-2023-03-21-at-15.11-2.png"
        alt=""
      />
    </div>
  </div>
  <div class="price-chart-login-div">
      <div class="price-chart-login-div">
        <a class="btn btn-style-default btn-style-semi-round btn-size-default btn-color-alt btn-full-width btn-icon-pos-right" href="/registration" >
          <span class="wd-btn-text" data-elementor-setting-key="text">
            Sign in to see stats
          </span>
        </a>
      </div>
  </div>
</div>
';
    
}
add_shortcode('pph_price_history_chart', 'pph_display_price_history_chart_shortcode');


function custom_role_based_redirect_dynamic() {
    if (!is_user_logged_in()) {
        $allowed_roles = array('administrator', 'vendor'); // Define allowed roles
        $user = wp_get_current_user();

        if (!array_intersect($allowed_roles, $user->roles)) {
            $redirect_pages = array('my-dashboard', 'my-sales'); // Pages to redirect from
            $current_page = get_post_field('post_name', get_queried_object_id());

            if (in_array($current_page, $redirect_pages)) {
                wp_redirect(home_url('/login'));
                exit();
            }
        }
    }
}
add_action('template_redirect', 'custom_role_based_redirect_dynamic');

function redirect_login_page() {
    $login_url  = home_url( '/login' );
    $url = basename($_SERVER['REQUEST_URI']); // get requested URL
    isset( $_REQUEST['redirect_to'] ) ? ( $url   = "wp-login.php" ): 0; // if users ssend request to wp-admin
    if( $url  == "wp-login.php" && $_SERVER['REQUEST_METHOD'] == 'GET')  {
        wp_redirect( $login_url );
        exit;
    }
}
add_action('init','redirect_login_page');


// Add first name and last name fields to WooCommerce registration form
// Add custom fields to WooCommerce registration form
function custom_woocommerce_register_form() {
    ?>
    <p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
        <label for="first_name">First Name&nbsp;<span class="required">*</span></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="first_name" id="first_name" value="<?php if (!empty($_POST['first_name'])) echo esc_attr($_POST['first_name']); ?>" required>
    </p>
    <p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
        <label for="last_name">Last Name&nbsp;<span class="required">*</span></label>
        <input type="text" class="woocommerce-Input woocommerce-Input--text input-text" name="last_name" id="last_name" value="<?php if (!empty($_POST['last_name'])) echo esc_attr($_POST['last_name']); ?>" required>
    </p>
    <p class="woocommerce-FormRow woocommerce-FormRow--wide form-row form-row-wide">
        <label for="reg_password">Password&nbsp;<span class="required">*</span></label>
        <input type="password" class="woocommerce-Input woocommerce-Input--password input-text" name="reg_password" id="reg_password" required>
    </p>
    <!-- Add more fields if needed -->
    <?php
}
add_action('woocommerce_register_form', 'custom_woocommerce_register_form');

// Validate additional fields during WooCommerce registration
function custom_woocommerce_registration_errors($errors, $username, $email) {
    if (empty($_POST['first_name'])) {
        $errors->add('first_name_error', __('First name is required.', 'woocommerce'));
    }
    if (empty($_POST['last_name'])) {
        $errors->add('last_name_error', __('Last name is required.', 'woocommerce'));
    }
    if (isset($_POST['reg_password']) && empty($_POST['reg_password'])) {
        $errors->add('password_error', __('Password is required.', 'woocommerce'));
    }
    return $errors;
}
add_filter('woocommerce_registration_errors', 'custom_woocommerce_registration_errors', 10, 3);

// Save additional fields during WooCommerce registration
function custom_woocommerce_user_register($user_id) {
    if (!empty($_POST['first_name'])) {
        update_user_meta($user_id, 'first_name', sanitize_text_field($_POST['first_name']));
    }
    if (!empty($_POST['last_name'])) {
        update_user_meta($user_id, 'last_name', sanitize_text_field($_POST['last_name']));
    }
}
add_action('woocommerce_created_customer', 'custom_woocommerce_user_register');


