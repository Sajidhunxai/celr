<?php

function add_vendor_info_to_cart_item_data($cart_item_data, $product_id, $variation_id)
{
    echo $cart_item_data;
    echo $product_id;
    echo $variation_id;
    
    if (isset($_GET['vendor_name'])) {
        $vendor_name = sanitize_text_field($_GET['vendor_name']);
        $cart_item_data['vendor_name'] = $vendor_name;
    }

    if (isset($_GET['price'])) {
        $price = sanitize_text_field($_GET['price']);
        $cart_item_data['vendor_price'] = $price;
    }

    if (isset($cart_item['warehouse_fee'])) {
        $warehouse_fee =  sanitize_text_field($_GET['warehouse_fee']);
        $cart_item_data['warehouse_fee'] = $warehouse_fee;
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'add_vendor_info_to_cart_item_data', 10, 3);

// Display vendor information in the cart
function display_vendor_info_in_cart($item_data, $cart_item)
{
    // echo $item_data;
    echo '<pre>';
print_r($cart_item);
echo '</pre>';
    

    if (isset($cart_item['vendor_name'])) {
        $item_data[] = array(
            'key'     => 'Vendor Name',
            'value'   => wc_clean($cart_item['vendor_name']),
            'display' => '',
        );
    }
    if (isset($cart_item['vendor_price'])) {
        $item_data[] = array(
            'key'     => 'Vendor Price',
            'value'   => wc_price(wc_clean($cart_item['vendor_price'])), // wc_price() to format the price
            'display' => '',
        );
    }
    if (isset($cart_item['warehouse_fee'])) {
        $item_data[] = array(
            'key'     => 'Warehouse Fee',
            'value'   => wc_price(wc_clean($cart_item['f'])), // wc_price() to format the fee
            'display' => '',
        );
    }
    return $item_data;
}
add_filter('woocommerce_get_item_data', 'display_vendor_info_in_cart', 10, 2);

// function display_warehouse_fee_at_checkout($item_quantity, $cart_item, $cart_item_key)
// {
//     if (isset($cart_item['warehouse_fee'])) {
//         $item_quantity .= sprintf('<div class="warehouse-fee">Warehouse Fee: %s</div>', wc_price(wc_clean($cart_item['warehouse_fee'])));
//     }

//     return $item_quantity;
// }

// add_filter('woocommerce_checkout_cart_item_quantity', 'display_warehouse_fee_at_checkout', 10, 3);


// Adjust the product price

function set_vendor_in_session()
{
    if (isset($_GET['vendor_name'])) {
        $vendor_name = sanitize_text_field($_GET['vendor_name']);
        WC()->session->set('selected_vendor', $vendor_name);
    }
}
add_action('init', 'set_vendor_in_session');

function adjust_cart_item_price($cart_item_data, $product_id)
{
    $vendor_name = WC()->session->get('selected_vendor');
    $current_user = wp_get_current_user();
    $warehouse_location = get_user_meta($current_user->ID, 'warehouse_location', true);
    $warehouse_options = get_warehouse_options();

    if ($vendor_name) {
        $vendors = get_post_meta($product_id, 'vendors', true);
        $warehouse_fees = get_option('custom_warehouses', array());

        foreach ($vendors as $vendor) {
            if ($vendor['name'] == $vendor_name && isset($warehouse_fees[$vendor['location']])) {
                $vendor_price = $vendor['price'];
                $warehouse_fee = $warehouse_fees[$vendor['location']];

                if ($warehouse_options[$warehouse_location] === $vendor['location']) {
                    $new_price = $vendor_price;
                } else {
                    $new_price = $vendor_price + $warehouse_fee;
                }

                $cart_item_data['new_price'] = $new_price;
                break;
            }
        }
    }
    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'adjust_cart_item_price', 10, 2);



function set_new_price($cart_object)
{
    if (is_admin() && !defined('DOING_AJAX'))
        return;

    if (did_action('woocommerce_before_calculate_totals') >= 2)
        return;

    // Iterate over each item in the cart
    foreach ($cart_object->get_cart() as $cart_item) {
        if (isset($cart_item['new_price'])) {
            $cart_item['data']->set_price($cart_item['new_price']);
        }
    }
}
add_action('woocommerce_before_calculate_totals', 'set_new_price', 10, 1);


function get_current_user_warehouse_name()
{
    $current_user = wp_get_current_user();
    $user_id = $current_user->ID;

    // Retrieve the warehouse location user meta value
    $warehouse_location = get_user_meta($user_id, 'warehouse_location', true);

    // Get the warehouse name based on the term ID
    $warehouse_name = '';
    if ($warehouse_location) {
        $term = get_term_by('term_id', $warehouse_location, 'warehouse_taxonomy');
        if ($term) {
            $warehouse_name = $term->name;
        }
    }

    return $warehouse_location;
}



// Display vendor information in the order
function display_vendor_info_in_order($item_id, $item, $order)
{
    $vendor_name = $item->get_meta('vendor_name');
    $vendor_price = $item->get_meta('vendor_price');
    $warehouse_fee = $item->get_meta('warehouse_fee');
    $warehouse_location = get_current_user_warehouse_name();

    if ($vendor_name) {
        printf('<p><strong>Vendor Name:</strong> %s</p>', $vendor_name);
    }
    if ($vendor_price) {
        printf('<p><strong>Vendor Price:</strong> %s</p>', wc_price($vendor_price));
    }
    if ($warehouse_fee) {
        printf('<p><strong>Warehouse Fee:</strong> %s</p>', wc_price($warehouse_fee));
    }
    if ($warehouse_location) {
        printf('<p><strong>Warehouse Location:</strong> %s</p>', $warehouse_location);
    }
}
add_action('woocommerce_order_item_meta_end', 'display_vendor_info_in_order', 10, 3);
