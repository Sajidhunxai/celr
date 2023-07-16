<?php

function vendor_sales_statistics($atts) {
    $total_sales = do_shortcode('[total_sales_by_vendor]');
    $vendor_quantity = do_shortcode('[vendor_quantity]');
    $vendor_profit_percentage = do_shortcode('[vendor_profit_percentage]');
    $vendor_sales_quantity = do_shortcode('[vendor_sales_quantity]');

    $output = '<div class="box-price-dashboard">
        ' .$total_sales .
          $vendor_quantity .
        $vendor_profit_percentage. 
          $vendor_sales_quantity .'
    </div>';

    return $output;
}

add_shortcode('vendor_sales_statistics', 'vendor_sales_statistics');



function get_all_vendor_listings_shortcode($atts)
{
    ob_start();

    $products = get_posts(array('post_type' => 'product', 'posts_per_page' => -1));
    $all_listings = array();

    foreach ($products as $product) {
        $vendors = get_post_meta($product->ID, 'vendors', true);
        if ($vendors) {
            $all_listings[$product->ID] = $vendors;
        }
    }

    foreach ($all_listings as $product_id => $vendors) {
        echo "<h3>Product ID: $product_id</h3>";
        foreach ($vendors as $vendor) {
            echo "<p>Vendor Name: {$vendor['name']}</p>";
            echo "<p>Price: {$vendor['price']}</p>";
            // Output other vendor information as needed
        }
    }

    return ob_get_clean();
}
add_shortcode('vendor_listings', 'get_all_vendor_listings_shortcode');



?>