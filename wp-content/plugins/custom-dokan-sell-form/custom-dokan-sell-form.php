<?php
/*
Plugin Name: Custom Dokan Sell Form
*/

// Enqueue custom JavaScript and CSS files
function custom_dokan_sell_form_scripts() {
    wp_enqueue_script( 'custom-dokan-sell-form', plugin_dir_url( __FILE__ ) . 'js/custom-dokan-sell-form.js', array( 'jquery' ), '1.0', true );
}
add_action( 'wp_enqueue_scripts', 'custom_dokan_sell_form_scripts' );

// Display the custom sell form
function custom_dokan_sell_form() {
    global $product;

    // Check if the user is a vendor
    if ( dokan_is_user_seller( get_current_user_id() ) ) {
        $product_id = get_the_ID();

        // Output your custom form HTML
        ?>
        <form id="custom-sell-form" method="post" action="<?php echo esc_url( add_query_arg( array( 'custom_sell_product_id' => $product_id ), dokan_get_navigation_url( 'products' ) ) ); ?>">
            <label for="custom_price">Price:</label>
            <input type="text" name="custom_price" id="custom_price" required>
            
            <!-- Add additional form fields for attributes or other information -->
            
            <input type="submit" value="Sell This Product">
        </form>
        <?php
    }
}
add_action( 'woocommerce_single_product_summary', 'custom_dokan_sell_form', 25 );

// Process the custom sell form submission
function process_custom_dokan_sell_form() {
    if ( isset( $_POST['custom_price'] ) && isset( $_GET['custom_sell_product_id'] ) ) {
        $product_id = intval( $_GET['custom_sell_product_id'] );
        $price = sanitize_text_field( $_POST['custom_price'] );
        
        // Process and sanitize the additional form fields
        
        // Update the product with the new price and other information
        update_post_meta( $product_id, '_regular_price', $price );
        update_post_meta( $product_id, '_price', $price );
        
        // Redirect to a success page or display a success message
        wp_redirect( dokan_get_navigation_url( 'products' ) );
        exit;
    }
}
add_action( 'init', 'process_custom_dokan_sell_form' );
