<?php
/*
Plugin Name: Custom Warehouse Fees Plugin
Description: Adds location-based fees to WooCommerce products
Version: 1.0
Author: Your Name
*/

// Add a custom page to the WordPress admin
function add_warehouse_page() {
    add_menu_page(
        'Warehouse Settings',
        'Warehouse Settings',
        'manage_options',
        'warehouse-settings',
        'render_warehouse_page'
    );
}
add_action( 'admin_menu', 'add_warehouse_page' );

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

function add_theme_scripts() {
	wp_enqueue_style( 'style', get_stylesheet_uri() );

	wp_enqueue_style( 'slider', plugin_dir_url(__FILE__) . '/css/styles.css', array(), '1.1', 'all' );

	wp_enqueue_script( 'script', plugin_dir_url(__FILE__) . '/js/script.js', array( 'jquery' ), 1.1, true );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'add_theme_scripts' );