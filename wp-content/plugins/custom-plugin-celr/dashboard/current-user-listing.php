<?php


add_shortcode('current_user_listing', 'current_user_listing_shortcode');

// Shortcode callback function
function current_user_listing_shortcode($atts) {
    // Get the current user ID
    $current_user = wp_get_current_user();
    $current_username = $current_user->user_login;


    // Retrieve all products
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
    );
    $products = new WP_Query($args);

    // Check if there are any products
    if ($products->have_posts()) {
        $output = '<div class="vendor-my-listing">';
        
        // Loop through each product
        while ($products->have_posts()) {
            $products->the_post();
            $product_id = get_the_ID();
            $product_name = get_the_title();

            // Retrieve the vendor data for the product
            $vendors = get_post_meta($product_id, 'vendors', true);

              // Filter vendors based on the current username
              $vendors = array_filter($vendors, function ($vendor) use ($current_username) {
                return $vendor['name'] === $current_username;
            });
            if (!empty($vendors)) {
                // Display the vendor data
                foreach ($vendors as $vendor) {
                
                    $output .= '<div class="my-listing">';

                    $output .= '<div>';
                    $output .= '<p>Producer: ' . $product_name . '</p>';
                        $output .= '<p>Vendor: ' . $vendor['name'] . '</p>';
                        $output .= '<p>Listed price: ' . $vendor['price'] . '</p>';
                        $output .= '<p>Format:  ' . $vendor['format'] . '</p>';
                        $output .= '<p>Quantity: ' . $vendor['quantity'] . '</p>';
                        $output .= '<p>Location: ' . $vendor['location'] . '</p>';
                        $sku = get_post_meta($product_id, '_sku', true);
                        $output .= '<p>LWIN: ' . $sku . '</p>';
                        $product = wc_get_product($product_id);
                        $color = $product->get_attribute('LWIN'); 
                        $output .= '<p>LWIN: ' . $color . '</p>';
                    $output .= '</div><div>';
                    $output .= '<button>Edit Listing</button>';
                    $output .= '</div>';
                    $output .= '</div>';

                }
            }
        }

        $output .= '</div>';
        wp_reset_postdata();
    } else {
        $output = '<p>No products found.</p>';
    }

    return $output;
}

?>
