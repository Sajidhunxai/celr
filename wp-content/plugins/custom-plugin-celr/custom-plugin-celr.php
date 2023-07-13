<?php
/*
Plugin Name: Custom Plugin Celr
Description: Custom Plugin Based on Requirements
Version: 1.0
Author: Sajid Hunxai
*/


function add_warehouse_page() {
    add_menu_page(
        'Warehouse Settings',
        'Custom Plugin',
        'manage_options',
        'custom_plugin_dashboard',
        'custom_plugin_dashboard_page'
    );
    
    // Add sub-menu items
    add_submenu_page(
        'custom_plugin_dashboard',
        'View Offers',
        'View Offers',
        'manage_options',
        'view-offers',
        'display_offer_page'
    );
    add_submenu_page(
        'custom_plugin_dashboard',
        'warehouse ',
        'warehouse',
        'manage_options',
        'warehouse-settings',
        'render_warehouse_page'

    );
    
    // Add more sub-menu items if needed
    // add_submenu_page(...);
    // add_submenu_page(...);
}

add_action( 'admin_menu', 'add_warehouse_page' );

function custom_plugin_dashboard_page(){
    echo "main Dashboard";
}
// Render the warehouse settings page
function render_warehouse_page() {
    if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    
    // Process the form submission
    if ( isset( $_POST['submit'] ) ) {
        save_warehouse_data();
    }

    // Display the warehouse form
    ?>
    <div class="wrap">
        <h1>Warehouse Settings</h1>
        <form method="post">
            <label for="location_name">Location Name:</label>
            <select id="location_name" name="location_name" required>
                <?php
                $attribute_slug = 'pa_location'; // Adjust the attribute slug according to your setup
                $terms = get_terms( array(
                    'taxonomy' => $attribute_slug,
                    'hide_empty' => false,
                ) );

                foreach ( $terms as $term ) {
                    echo '<option value="' . esc_attr( $term->name ) . '">' . esc_html( $term->name ) . '</option>';
                }
                ?>
            </select>
            <br>
            <label for="warehouse_fee">Warehouse Fee:</label>
            <input type="number" id="warehouse_fee" name="warehouse_fee" step="0.01" required>
            <br>
            <input type="submit" name="submit" value="Save">
        </form>

        <h2>Submitted Warehouse Data</h2>
        <table class="wp-list-table widefat striped">
            <thead>
                <tr>
                    <th>Location Name</th>
                    <th>Warehouse Fee</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $existing_warehouses = get_option( 'custom_warehouses', array() );

                foreach ( $existing_warehouses as $location_name => $warehouse_fee ) {
                    echo '<tr>';
                    echo '<td>' . esc_html( $location_name ) . '</td>';
                    echo '<td>' . esc_html( $warehouse_fee ) . '</td>';
                    echo '</tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
    <?php
}

// Save the warehouse data to the database
function save_warehouse_data() {
    $location_name = sanitize_text_field( $_POST['location_name'] );
    $warehouse_fee = floatval( $_POST['warehouse_fee'] );

    // Save the warehouse data to the database (you can modify this to fit your needs)
    $existing_warehouses = get_option( 'custom_warehouses', array() );
    $existing_warehouses[ $location_name ] = $warehouse_fee;

    update_option( 'custom_warehouses', $existing_warehouses );
}

// Get the location for a product
function get_product_location( $product_id ) {
    // Get the product object
    $product = wc_get_product( $product_id );

    // Check if the product has attributes
    if ( $product->is_type( 'variable' ) ) {
        // For variable products, retrieve the attribute value from the selected variation
        $variation_id = $product->get_variation_default_attributes();
        $variation = wc_get_product( reset( $variation_id ) );

        if ( $variation ) {
            $location = $variation->get_attribute( 'pa_location' );
        }
    } else {
        // For simple products, retrieve the attribute value directly
        $location = $product->get_attribute( 'pa_location' );
    }

    return $location;
}

// Calculate the fee amount based on the location
function calculate_location_fee( $fee, $product_id ) {
    $product_location = get_product_location( $product_id );

    if ( $product_location === $fee['location'] ) {
        return $fee['price'];
    }

    return 0;
}

// Apply the warehouse fee for each product
// Apply the warehouse fee for each product
function apply_location_fee( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }

    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
        return;
    }

    $custom_fees = get_custom_fees();

    foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
        $product_id = $cart_item['product_id'];
        $product_location = get_product_location( $product_id );

        foreach ( $custom_fees as $fee ) {
            if ( $product_location === $fee['location'] ) {
                $amount = $fee['price'];
                $cart->add_fee( 'Location Fee', $amount );
                break; // Apply only one fee per product
            }
        }
    }
}
add_action( 'woocommerce_cart_calculate_fees', 'apply_location_fee' );


// Retrieve fees from the WordPress admin
function get_custom_fees() {
    $custom_fees = array();
    $warehouses = get_option( 'custom_warehouses', array() );

    foreach ( $warehouses as $location_name => $warehouse_fee ) {
        $custom_fees[] = array(
            'id'        => sanitize_title( $location_name ),
            'name'      => $location_name,
            'location'  => $location_name,
            'price'     => $warehouse_fee
        );
    }

    return $custom_fees;
}

require_once('buyer-account.php');
require_once('buyer-offer.php');
require_once('single-product-page/components/display-product-attributes.php');
require_once('single-product-page/components/display-market-price.php');
require_once('single-product-page/components/display-vendor.php');
require_once('single-product-page/components/vintage-filter.php');
require_once('single-product-page/required-functions.php');
require_once('vendor-add-listing/product-search-handler.php');
require_once('dashboard/current-user-listing.php');

function enqueue_product_search_scripts() {
    wp_enqueue_script(
        'product-search',
        plugin_dir_url(__FILE__) . 'js/addListingProductSearch.js',
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
    wp_enqueue_style( 'slider', plugin_dir_url(__FILE__) . '/css/styles.css', array(), '1.1', 'all' );
    
    wp_enqueue_script( 'script', plugin_dir_url(__FILE__) . '/js/script.js', array( 'jquery' ), 1.1, true );
    wp_localize_script('script', 'ajax_object', array( // Add this line
        'ajax_url' => admin_url('admin-ajax.php'),
    ));

    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'add_theme_scripts' );


// add_action('rest_api_init', function () {
//     register_rest_route('myplugin/v1', '/offer/', array(
//       'methods' => 'POST',
//       'callback' => 'handle_offer_submission',
//     ));
//   });
  
//   // Handle form submission
//   function handle_offer_submission(WP_REST_Request $request) {
//     // Extract form data
//     $data = $request->get_params();
//     $product_id = sanitize_text_field($data['product_id']);
//     $vendor_name = sanitize_text_field($data['vendor_name']);
//     $offer_price = sanitize_text_field($data['offer_price']);
//     $vendor_quantity = sanitize_text_field($data['quantity']);
//     $vendor_location = sanitize_text_field($data['vendor_location']);
//     $vendor_transfer_fee = sanitize_text_field($data['vendor_transfer_fee']);
  
//     // Get vendor email
//     $user = get_user_by('login', $vendor_name);
//     if ($user) {
//       $vendor_email = $user->user_email;
//     } else {
//       return new WP_Error('user_not_found', 'No user found with the given username.', array('status' => 400));
//     }
  
//     // Validate the data (add your validation logic here)
//     // If the data is invalid, you can return a WP_Error object with an appropriate message
  
//     // Insert data into database
//     global $wpdb;
//     $table_name = $wpdb->prefix . 'offer_data';
//     $wpdb->insert(
//       $table_name,
//       array(
//         'product_id' => $product_id,
//         'vendor_name' => $vendor_name,
//         'offer_price' => $offer_price,
//         'vendor_quantity' => $vendor_quantity,
//         'vendor_location' => $vendor_location,
//         'vendor_transfer_fee' => $vendor_transfer_fee,
//       ),
//       array('%d', '%s', '%s', '%d', '%s', '%s')
//     );
  
//     // Send email
//     $subject = 'New Offer Submitted';
//     $message = "Dear $vendor_name,\n\nA new offer has been submitted for your product. Details are as follows:\n\nOffer Price: $offer_price\nQuantity: $vendor_quantity\nLocation: $vendor_location\nTransfer Fee: $vendor_transfer_fee\n\nThank you.";
//     $headers = array('Content-Type: text/html; charset=UTF-8');
//     wp_mail($vendor_email, $subject, $message, $headers);
  
//     // Return a success response
//     return new WP_REST_Response(array('message' => 'Form submitted successfully'), 200);
//   }

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

function create_offer_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'offer_data';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id mediumint(9) NOT NULL,
        product_id mediumint(9) NOT NULL,
        vendor_name varchar(255) NOT NULL,
        offer_price decimal(10,2) NOT NULL,
        vendor_quantity mediumint(9) NOT NULL,
        vendor_location varchar(255) NOT NULL,
        vendor_transfer_fee decimal(10,2) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    dbDelta($sql);
}

// Run the function when the plugin is activated
register_activation_hook(__FILE__, 'create_offer_table');
