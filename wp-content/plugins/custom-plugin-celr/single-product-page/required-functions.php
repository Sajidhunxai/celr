<?php

function add_vendor_info_to_cart_item_data($cart_item_data, $product_id, $variation_id)
{


    if (isset($_GET['vendor_name'])) {
        $vendor_name = sanitize_text_field($_GET['vendor_name']);
        $cart_item_data['vendor_name'] = $vendor_name;
    }

    if (isset($_GET['price'])) {
        $price = sanitize_text_field($_GET['price']);
        $cart_item_data['vendor_price'] = $price;
    }

    if (isset($_GET['warehouse_fee'])) {
        $warehouse_fee =  sanitize_text_field($_GET['warehouse_fee']);
        $cart_item_data['warehouse_fee'] = $warehouse_fee;
    }
    if (isset($_GET['vendor_format'])) {
        $vendor_format =  sanitize_text_field($_GET['vendor_format']);
        $cart_item_data['vendor_format'] = $vendor_format;
    }
    if (isset($_GET['vendor_purchase'])) {
        $vendor_format =  sanitize_text_field($_GET['vendor_purchase']);
        $cart_item_data['vendor_purchase'] = $vendor_format;
    }
    unset($item_data['quantity']);
    unset($item_data['price']);

    return $cart_item_data;
}
add_filter('woocommerce_add_cart_item_data', 'add_vendor_info_to_cart_item_data', 10, 3);

// Display vendor information in the cart
function display_vendor_info_in_cart($item_data, $cart_item)
{

    // echo $item_data;
    // echo '<pre>';
    // print_r($cart_item);
    // echo '</pre>';
    if (isset($cart_item['quantity'])) {
        $quantity = wc_clean($cart_item['quantity']);
        $display_text = $quantity . ' case(s)';

        $item_data[] = array(
            'key'     => 'Number of cases',
            'value'   => $quantity,
            'display' => $display_text,
        );
    }
    if (isset($cart_item['vendor_price'])) {
        $item_data[] = array(
            'key'     => 'Vendor Price',
            'value'   => wc_price(wc_clean($cart_item['vendor_price'])), // wc_price() to format the price
            'display' => '',
        );
        // print_r($item_data); 

    }
        // if (isset($cart_item['vendor_purchase'])) {
        //     $item_data[] = array(
        //         'key'     => 'Vendor Purchase',
        //         'value'   => wc_price(wc_clean($cart_item['vendor_purchase'])), // wc_price() to format the price
        //         'display' => false,
        //     );
        //     // print_r($item_data); 

        // }
    if (isset($cart_item['quantity'])) {
        $quantity = wc_clean($cart_item['quantity']);
        $display_text = $quantity . ' case(s)';

        $item_data[] = array(
            'key'     => 'Number of cases',
            'value'   => $quantity,
            'display' => $display_text,
        );
    }

    if (isset($cart_item['vendor_price'])) {
        $currentDate = new DateTime();
        $deadlineDate = $currentDate->modify('+7 days')->format('Y-m-d');

        $item_data[] = array(
            'key'     => 'Transfer deadline',
            'value'   => $deadlineDate,
            'display' => '',
        );
    }

 
    if (isset($cart_item['warehouse_location'])) {
        $item_data[] = array(
            'key'     => 'Warehouse Location',
            'value'   => wc_clean($cart_item['warehouse_location']),
            'display' => '',
        );
    }
    if (isset($cart_item['warehouse_fee'])) {
        $item_data[] = array(
            'key'     => 'Transfer Fee',
            'value'   => wc_price(wc_clean($cart_item['warehouse_fee'])), // wc_price() to format the fee
            'display' => '',
        );
    }
    if (isset($cart_item['product_format'])) {
        $item_data[] = array(
            'key'     => 'Product Format',
            'value'   => wc_clean($cart_item['product_format']),
            'display' => '',
        );
    }


    unset($item_data['quantity']);
    unset($item_data['price']);
    return $item_data;
}
add_filter('woocommerce_get_item_data', 'display_vendor_info_in_cart', 10, 2);

// Add vendor name as order item meta
function add_vendor_name_to_order_item_meta($item, $cart_item_key, $values, $order)
{
    if (isset($values['vendor_name'])) {
        $item->add_meta_data('Vendor Name', $values['vendor_name']);
        $item->add_meta_data('Vendor Price', $values['vendor_price']);
        $item->add_meta_data('Transfer Fee', $values['warehouse_fee']);
        $item->add_meta_data('Product Format', $values['product_format']);
        $item->add_meta_data('Warehouse Location', $values['warehouse_location']);
        $item->add_meta_data('Vendor Purchase', $values['vendor_purchase']);
        echo '<script>
        var elements = document.getElementsByClassName("wc-item-meta");
        for (var i = 0; i < elements.length; i++) {
            elements[i].style.display = "none";
        }
        </script>';

    }
}
add_action('woocommerce_checkout_create_order_line_item', 'add_vendor_name_to_order_item_meta', 10, 4);


function modify_product_name_display($product_name, $cart_item, $cart_item_key)
{
    $product = $cart_item['data'];
    $warehouse_fee = isset($cart_item['warehouse_fee']) ? wc_price(wc_clean($cart_item['warehouse_fee'])) : '';

    if ($product) {
        $product_name = $product->get_name();
        $product_name .= $warehouse_fee !== '' ? ' (Warehouse Fee: ' . $warehouse_fee . ')' : '';
    }

    return $product_name;
}
add_filter('woocommerce_checkout_cart_item_quantity', 'modify_product_name_display', 10, 3);



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
    $vendors = get_post_meta($product_id, 'vendors', true);
    $warehouse_fees = get_option('custom_warehouses', array());

    foreach ($vendors as $vendor) {
        $vendor_price = $vendor['price'];
        $warehouse_fee = $warehouse_fees[$vendor['location']];
        // var_dump($vendor);
        $new_price = $vendor_price + $warehouse_fee;

        $cart_item_data['new_price'] = $new_price;
        break;
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

    // print_r($cart_object);
    // Iterate over each item in the cart
    foreach ($cart_object->get_cart() as $cart_item) {
        if (isset($cart_item['price'])) {
            $cart_item['data']->set_price($cart_item['price']);
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



// // Display vendor information in the order
// function display_vendor_info_in_order($item_id, $item, $order)
// {
//     $vendor_name = $item->get_meta('vendor_name');
//     $vendor_price = $item->get_meta('vendor_price');
//     $warehouse_fee = $item->get_meta('warehouse_fee');
//     $warehouse_location = get_current_user_warehouse_name();

//     if ($vendor_name) {
//         printf('<p><strong>Vendor Name:</strong> %s</p>', $vendor_name);
//     }
//     if ($vendor_price) {
//         printf('<p><strong>Vendor Price:</strong> %s</p>', wc_price($vendor_price));
//     }
//     if ($warehouse_fee) {
//         printf('<p><strong>Warehouse Fee:</strong> %s</p>', wc_price($warehouse_fee));
//     }
//     if ($warehouse_location) {
//         printf('<p><strong>Warehouse Location:</strong> %s</p>', $warehouse_location);
//     }
// }
// add_action('woocommerce_order_item_meta_end', 'display_vendor_info_in_order', 10, 3);

