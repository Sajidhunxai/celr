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

    // if (isset($cart_item['vendor_price'])) {
    //     $item_data[] = array(
    //         'key'     => 'Vendor Price',
    //         'value'   => wc_price(wc_clean($cart_item['vendor_price'])), // wc_price() to format the price
    //         'display' => '',
    //     );
    // }
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




function get_orders_by_vendor_name($atts)
{
    $current_user = wp_get_current_user();
    $vendor_name = $current_user->user_login;
    $user_name = $current_user->user_login;

    $status_mapping = array(
        'processing' => array(
            'post_status' => 'processing',
            'status_label' => 'Transfer pending',
            'show_mark_complete_button' => true,
            'show_mark_processing_button' => true,
        ),
        'completed' => array(
            'post_status' => 'completed',
            'status_label' => 'Completed Orders',
            'show_mark_complete_button' => false,
            'show_mark_processing_button' => false,
        ),
        'on-hold' => array(
            'post_status' => 'on-hold',
            'status_label' => 'On Hold Orders',
            'show_mark_complete_button' => false,
            'show_mark_processing_button' => true,
        ),
    );

    $default_status = 'processing';
    $default_status_mapping = $status_mapping[$default_status];
    $limit = isset($atts['limit']) ? intval($atts['limit']) : -1;
    $args['posts_per_page'] = $limit;

    $post_status = isset($_GET['status']) ? $_GET['status'] : $default_status;
    $status_config = isset($status_mapping[$post_status]) ? $status_mapping[$post_status] : $default_status_mapping;

    extract($status_config);

    $args = array(
        'post_type'      => 'shop_order',
        'post_status'    => $post_status,
        'posts_per_page' => $limit, // Assign the limit value here
        'meta_query'     => array(
            array(
                'key'   => 'Vendor Name',
                'value' => $vendor_name,
            ),
        ),
    );

    if (isset($_GET['status'])) {
        $_SESSION['active_button'] = $_GET['status'];
    }

    $output = '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="get" class="vendor-sales">';
    $output .= '<div class="vendor-sales-btn"><input type="hidden" name="page_id" value="' . get_the_ID() . '" />';
    foreach ($status_mapping as $status_key => $status_data) {
        $active_class = isset($_SESSION['active_button']) && $_SESSION['active_button'] === $status_key ? ' active' : '';
        $output .= '<button class="order-button' . $active_class . '" type="submit" name="status" value="' . $status_key . '">' . $status_data['status_label'] . '</button>';
    }
    $output .= '</div></form>';

    $output .= '<script>
        document.addEventListener("DOMContentLoaded", function() {
            var buttons = document.getElementsByClassName("order-button");
            for (var i = 0; i < buttons.length; i++) {
                buttons[i].addEventListener("click", function() {
                    var current = document.getElementsByClassName("active");
                    if (current.length > 0) {
                        current[0].className = current[0].className.replace(" active", "");
                    }
                    this.className += " active";
                });
            }
        });
    </script>';

    $order_query = new WC_Order_Query($args);
    $orders = $order_query->get_orders();
    if ($limit !== -1) {
        $orders = array_slice($orders, 0, $limit);
        // var_dump($orders);

    }

    if ($orders) {
        $output .= '<div class="vendor-sales">';
        foreach ($orders as $order) {
            $order_object = new Automattic\WooCommerce\Admin\Overrides\Order($order->get_id());
            $status = $order_object->get_status();
            $buyer_first_name =  $order_object->get_billing_first_name();
            $buyer_last_name =  $order_object->get_billing_last_name();
            $buyer_address =  $order_object->get_billing_address_1();
            $buyer_address2 =  $order_object->get_billing_address_2();
            $order_date =  $order_object->get_date_created();
            $order_date_without_time = date('Y-m-d', strtotime($order_date));
            $deadline_date = date('Y-m-d', strtotime('+7 days', strtotime($order_date)));
    
            if ($status === $post_status){
                $order_id = $order->get_id();
                $order_title = $order->get_title();
                $buyer_id = $order->get_user_id();

                $items = $order->get_items();
                foreach ($items as $item_id => $item) {
                    $product = $item->get_product();
                    $vendor_names = $item->get_meta('Vendor Name');
                    $vendor_price = $item->get_meta('Vendor Price');
                    $transfer_fee = $item->get_meta('Transfer Fee');
                    $vendor_format = $item->get_meta('Product Format');
                    $warehouse_location = $item->get_meta('Warehouse Location');

                    if ($vendor_names === $vendor_name) {
                        $sku = $product ? $product->get_sku() : '';

                        $output .= '<div class="sales-items"><div class="vendor-sales-left">';
                        $output .= '<h4>Producer:</h4><span> ' . $item->get_name() . '</span>';
                        $output .= '<h4>Price sold at: </h4><span>£ ' . $vendor_price . '</span>';
                        $output .= '<h4>Format: </h4><span>' . $vendor_format . '</span>';
                        $output .= '<h4>Quantity: </h4><span>' . $item->get_quantity() . ' case(s) </span>';
                        $output .= '<h4>LWIN: </h4><span>' . $sku . '</span>';
                        $output .= '<h4>Buyer Account #:</h4><span> ' . $buyer_id . '</span>';
                        $output .= '<h4>Buyer warehouse:</h4><span> ' . $warehouse_location . '</span>';
                        // $output .= '<h4>Order Id: </h4><span>' . $order_id . '</span>';

                        $output .= '</div>';
                        $output .= '<div class="sales-page-btns">';


                        if ($show_mark_complete_button && $status !== 'completed' && $status !== 'on-hold') {
                            $output .= '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
                            $output .= '<input type="hidden" name="order_id" value="' . $order_id . '" />';
                            $output .= '<button class="order-button active"  type="submit" name="mark_complete" value="true">Mark as Complete</button>';
                            $output .= '</form>';
                        }

                        if ($show_mark_processing_button && $status !== 'processing') {
                            $output .= '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
                            $output .= '<input type="hidden" name="order_id" value="' . $order_id . '" />';
                            $output .= '<button class="order-button active" type="submit" name="mark_processing" value="true">Mark as Processing</button>';
                            $output .= '</form>';
                        }
                        $output .= '<button class="show-details-button order-button active" type="button" data-overlay-id="overlay-' . $order_id . '">View buyer details &nbsp;&nbsp;&nbsp;<i aria-hidden="true" class="fas fa-arrow-right" style="width: 20px;     transform: scaleX(1.6);"></i>			</button>';






                        $output .= '</div></div>';
                        // Hidden div to store remaining details
                        $output .= '<div id="overlay-' . $order_id . '" class="remaining-details-overlay" style="display: none;">';
                        $output .= '<div class="remaining-details">';
                        $output .= '<button class="close-button" type="button">&times;</button>'; // Close button
                        $output .= '<div class="offer-left">';
                        $output .= '<h4>Buyer Name: </h4><span>' . $buyer_first_name . ' ' . $buyer_last_name . '</span>';
                        $output .= '<h4>Shipping Paid: </h4><span>' . $transfer_fee . '</span>';
                        $output .= '<h4>Total cases: </h4><span>' .  $item->get_quantity()  . '</span>';
                        $output .= '<h4>Delivery deadline:</h4><span>' . $deadline_date . '</span>';
                        $output .= '<h4>Days to forfeit:</h4><span>7 days</span>';
                        $output .= '<h4>Buyer Account #:</h4><span> ' . $buyer_id . '</span>';
                        $output .= '<h4>Buyer warehouse:</h4><span> ' . $warehouse_location . '</span>';

                        $output .= '<h4>Buyer Address: </h4><span>' . $buyer_address . ' ' . $buyer_address2 . '</span>';
                        $output .= '<h4>Order Date:</h4><span>' . $order_date_without_time . '</span>';
                        $output .= '</div>';

                        if ($show_mark_complete_button && $status !== 'completed' && $status !== 'on-hold') {
                            $output .= '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
                            $output .= '<input type="hidden" name="order_id" value="' . $order_id . '" />';
                            $output .= '<button class="order-button active"  type="submit" name="mark_complete" value="true">Mark as Complete</button>';
                            $output .= '</form>';
                        }

                        if ($show_mark_processing_button && $status !== 'processing') {
                            $output .= '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
                            $output .= '<input type="hidden" name="order_id" value="' . $order_id . '" />';
                            $output .= '<button class="order-button active" type="submit" name="mark_processing" value="true">Mark as Processing</button>';
                            $output .= '</form>';
                        }
                        $output .= '<span style="color:red">Got a problem? Contact us.</span>';
                        $output .= '</div></div>';
                    }
                }
            }
        }
        $output .= '</div>';
    } else {
        $output .= 'No Sales Found';
    }

    if (isset($_POST['mark_complete']) && $_POST['mark_complete'] === 'true' && isset($_POST['order_id'])) {
        $order_id = $_POST['order_id'];
        $order = wc_get_order($order_id);
        if ($order) {
            $order->update_status('completed');
            $output .= '<p>Order marked as complete.</p>';
        } else {
            $output .= '<p>Failed to mark order as complete.</p>';
        }
    }

    if (isset($_POST['mark_processing']) && $_POST['mark_processing'] === 'true' && isset($_POST['order_id'])) {
        $order_id = $_POST['order_id'];
        $order = wc_get_order($order_id);
        if ($order) {
            $order->update_status('processing');
            $output .= '<p>Order marked as processing.</p>';
        } else {
            $output .= '<p>Failed to mark order as processing.</p>';
        }
    }


    $output .= '<script>
document.addEventListener("DOMContentLoaded", function() {
    var buttons = document.getElementsByClassName("show-details-button");
  
    for (var i = 0; i < buttons.length; i++) {
      buttons[i].addEventListener("click", function() {
        var overlayId = this.getAttribute("data-overlay-id");
        var overlay = document.getElementById(overlayId);
        if (overlay) {
          overlay.style.display = "block";
        }
      });
    }
  
    var overlays = document.getElementsByClassName("close-button");
  
    for (var j = 0; j < overlays.length; j++) {
      overlays[j].addEventListener("click", function() {
        var overlay = this.closest(".remaining-details-overlay");
        if (overlay) {
          overlay.style.display = "none";
        }
      });
    }
  });
  

</script>';
    return $output;
}
add_shortcode('vendor_sales', 'get_orders_by_vendor_name');


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
function get_vendor_listings_for_current_user_shortcode($atts) {
    $atts = shortcode_atts(array(
        'limit' => -1, // Default to display all listings (-1)
    ), $atts);

    ob_start();
    
    // Check if user is logged in
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $current_user_username = $current_user->user_login;

        $products = get_posts(array('post_type' => 'product', 'posts_per_page' => -1));
        $all_listings = array();

        echo "<div class='vendor-listing'>";

        foreach ($products as $product) {
            $product_id = $product->ID;
            $product_name = $product->post_title;
            $product_sku = get_post_meta($product_id, '_sku', true);
            $product_price = get_post_meta($product_id, '_regular_price', true);
            $vendors = get_post_meta($product_id, 'vendors', true);

            if ($vendors) {
                foreach ($vendors as $vendor) {
                    if ($vendor['name'] === $current_user_username) {
                        $all_listings[$product_id][] = array(
                            'name' => $product_name,
                            'sku' => $product_sku,
                            'price' => $product_price,
                            'vendor' => $vendor,
                        );
                    }
                }
            }
        }

        if (!empty($all_listings)) {
            foreach ($all_listings as $product_id => $listings) {
                $listing = $listings[0]; // Display the first listing only
                $popup_id = "popup-$product_id-{$listing['vendor']['name']}";
                $overlay_id = "overlay-$product_id-{$listing['vendor']['name']}";
                $edit_form_id = "edit-form-$product_id-{$listing['vendor']['name']}";

                echo "<div class='vendor-listing-main'>";
                echo "<div class='vendor-listing-left'>";
                echo "<h4>Producer:</h4> <span>{$listing['name']}</span>";
                echo "<h4>Listed price:</h4> <span>£ {$listing['vendor']['price']}</span>";
                echo "<h4>Vs. Market Price:</h4><span class='price-diff'>[price_difference product_id='$product_id']</span>";
                echo "<h4>Format:</h4><span> {$listing['vendor']['format']}</span>";
                echo "<h4>Quantity:</h4><span> {$listing['vendor']['quantity']} case(s)</span>";
                echo "<h4>LWIN:</h4><span>  {$listing['sku']}</span>";
                echo "<h4>Vendor Name:</h4><span> {$listing['vendor']['name']}</span>";
                echo "</div><div class='vendor-listing-right'>";
                
                // Edit Button and Form
                echo "<button class='order-button active' onclick='showEditForm(\"$popup_id\", \"$overlay_id\")'>Edit listing</button>";
                echo "<div id='$overlay_id' class='popup-overlay'></div>";
                echo "<div id='$popup_id' class='edit-popup' style='display: none;'>";
                echo "<button class='close-button' onclick='hideEditForm(\"$popup_id\", \"$overlay_id\")'>&times;</button>";
                echo "<form id='$edit_form_id' class='edit-form'>";
                echo "<input type='hidden' name='action' value='update_vendor_listing'>";
                echo "<input type='hidden' name='product_id' value='$product_id'>";
                echo "<input type='hidden' name='vendor_name' value='{$listing['vendor']['name']}'>";
                echo "<input type='text' name='vendor_price' placeholder='Enter new price' value='{$listing['vendor']['price']}'>";
                echo "<input type='text' name='vendor_format' placeholder='Enter format' value='{$listing['vendor']['format']}'>";
                echo "<input type='text' name='vendor_quantity' placeholder='Enter quantity' value='{$listing['vendor']['quantity']}'>";
                // Add other input fields as needed
                echo "<button class='order-button active' type='button' onclick='updateVendorListing(\"$product_id\", \"{$listing['vendor']['name']}\")'>Update listing</button>";
                echo "</form>";
                echo "</div>";
                echo "</div>";
                echo "</div>";
                echo "<script>
                    function showEditForm(popupId, overlayId) {
                        var popup = document.getElementById(popupId);
                        var overlay = document.getElementById(overlayId);
                        popup.style.display = 'block';
                        overlay.style.display = 'block';
                    }

                    function hideEditForm(popupId, overlayId) {
                        var popup = document.getElementById(popupId);
                        var overlay = document.getElementById(overlayId);
                        popup.style.display = 'none';
                        overlay.style.display = 'none';
                    }

                    function updateVendorListing(product_id, vendor_name) {
                        var form = document.getElementById('$edit_form_id');
                        var formData = new FormData(form);

                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', '" . admin_url('admin-ajax.php') . "');
                        xhr.onreadystatechange = function() {
                            if (xhr.readyState === 4 && xhr.status === 200) {
                                var response = JSON.parse(xhr.responseText);
                                if (response.success) {
                                    alert('Vendor listing updated successfully!');
                                    window.location.reload();
                                } else {
                                    alert('Failed to update vendor listing!');
                                }
                            }
                        };
                        xhr.send(formData);
                    }
                </script>";
                
                if ($atts['limit'] === '1') {
                    break; // Break the loop after displaying the desired listing
                }
            }
        }

        echo "</div>";
    } else {
        echo "<p>Please log in to view vendor listings.</p>";
    }

    return ob_get_clean();
}

add_shortcode('vendor_listings_current_user', 'get_vendor_listings_for_current_user_shortcode');


function update_vendor_listing()
{
    $product_id = intval($_POST['product_id']);
    $vendor_name = sanitize_text_field($_POST['vendor_name']);
    $new_data = array(
        'vendor_price' => sanitize_text_field($_POST['vendor_price']),
        'vendor_format' => sanitize_text_field($_POST['vendor_format']),
        'vendor_quantity' => sanitize_text_field($_POST['vendor_quantity']),
        // Add other fields as needed
    );

    $result = false;
    $products = get_posts(array('post_type' => 'product', 'posts_per_page' => -1));
    foreach ($products as $product) {
        if ($product->ID == $product_id) {
            $vendors = get_post_meta($product_id, 'vendors', true);
            if ($vendors) {
                foreach ($vendors as &$vendor) {
                    if ($vendor['name'] === $vendor_name) {
                        $vendor['price'] = $new_data['vendor_price'];
                        $vendor['format'] = $new_data['vendor_format'];
                        $vendor['quantity'] = $new_data['vendor_quantity'];
                        // Update other fields as needed
                        break;
                    }
                }
            }
            $result = update_post_meta($product_id, 'vendors', $vendors);
            break;
        }
    }

    if ($result) {
        wp_send_json_success();
    } else {
        wp_send_json_error();
    }
}
add_action('wp_ajax_update_vendor_listing', 'update_vendor_listing');
add_action('wp_ajax_nopriv_update_vendor_listing', 'update_vendor_listing');


function get_total_sales_by_vendor_name($atts)
{
    $current_user = wp_get_current_user();
    $vendor_name = $current_user->user_login;

    $status_mapping = array(
        'processing' => array(
            'post_status' => 'processing',
            'status_label' => 'Transfer pending',
        ),
        'completed' => array(
            'post_status' => 'completed',
            'status_label' => 'Completed Orders',
        ),
        'on-hold' => array(
            'post_status' => 'on-hold',
            'status_label' => 'On Hold Orders',
        ),
    );

    $args = array(
        'post_type'      => 'shop_order',
        'post_status'    => array('processing', 'completed', 'on-hold'),
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => 'Vendor Name',
                'value' => $vendor_name,
            ),
        ),
    );

    $order_query = new WC_Order_Query($args);
    $orders = $order_query->get_orders();

    $total_sales = 0;
    $total_vendor_price = 0;

    foreach ($orders as $order) {
        $order_object = new Automattic\WooCommerce\Admin\Overrides\Order($order->get_id());

        $items = $order->get_items();
        foreach ($items as $item_id => $item) {
            $vendor_names = $item->get_meta('Vendor Name');
            $vendor_price = $item->get_meta('Vendor Price');
            if ($vendor_names === $vendor_name) {
                $total_sales += $item->get_total();
                $total_vendor_price += $vendor_price;
                $result = wc_price($total_sales);
            }else{
                $result = '0';
            }
        }
    }

    $output = '<table class="market price-box box-dashboard">
        <tbody>
            <tr>
                <th>SALES</th>
            </tr>
            <tr>
                <td>
                    <span>' . $result . '</span>
                </td>
            </tr>
        </tbody>
    </table>';


    return $output;
}
add_shortcode('total_sales_by_vendor', 'get_total_sales_by_vendor_name');

function calculate_vendor_quantity($atts)
{
    $current_user = wp_get_current_user();
    $current_user_username = $current_user->user_login;

    $products = get_posts(array('post_type' => 'product', 'posts_per_page' => -1));
    $total_vendor_quantity = 0;

    foreach ($products as $product) {
        $product_id = $product->ID;
        $vendors = get_post_meta($product_id, 'vendors', true);
        if ($vendors) {
            foreach ($vendors as $vendor) {
                if ($vendor['name'] === $current_user_username) {
                    $total_vendor_quantity += intval($vendor['quantity']);
                    $result = $total_vendor_quantity;
                }
            }
        }
    }
    return '<table class="market price-box box-dashboard">
        <tbody>
            <tr>
                <th>CASES LIVE</th>
            </tr>
            <tr>
                <td>
                    <span>' . $result . '</span>
                </td>
            </tr>
        </tbody>
    </table>';
}
add_shortcode('vendor_quantity', 'calculate_vendor_quantity');

function get_vendor_total_sales($atts)
{
    $current_user = wp_get_current_user();
    $vendor_name = $current_user->user_login;

    $status_mapping = array(
        'processing' => array(
            'post_status' => 'processing',
            'status_label' => 'Transfer pending',
        ),
        'completed' => array(
            'post_status' => 'completed',
            'status_label' => 'Completed Orders',
        ),
        'on-hold' => array(
            'post_status' => 'on-hold',
            'status_label' => 'On Hold Orders',
        ),
    );

    $args = array(
        'post_type'      => 'shop_order',
        'post_status'    => array('processing', 'completed', 'on-hold'),
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => 'Vendor Name',
                'value' => $vendor_name,
            ),
        ),
    );

    $order_query = new WC_Order_Query($args);
    $orders = $order_query->get_orders();

    $total_sales = 0.0; // Initialize as float
    $total_vendor_price = 0.0; // Initialize as float

    foreach ($orders as $order) {
        $order_object = new Automattic\WooCommerce\Admin\Overrides\Order($order->get_id());

        $items = $order->get_items();
        foreach ($items as $item_id => $item) {
            $vendor_names = $item->get_meta('Vendor Name');
            $vendor_price = $item->get_meta('Vendor Price');
            if ($vendor_names === $vendor_name) {
                $total_sales += floatval($item->get_total()); // Convert to float
                $total_vendor_price += floatval($vendor_price); // Convert to float
            }
        }
    }

    return $total_sales;
}


function calculate_vendor_profit_percentage($atts)
{
    $total_sales = get_vendor_total_sales($atts);
    $total_vendor_purchase = 0;

    // Retrieve the total vendor purchase from your existing logic
    $current_user = wp_get_current_user();
    $current_user_username = $current_user->user_login;

    $products = get_posts(array('post_type' => 'product', 'posts_per_page' => -1));

    foreach ($products as $product) {
        $product_id = $product->ID;
        $vendors = get_post_meta($product_id, 'vendors', true);
        if ($vendors) {
            foreach ($vendors as $vendor) {
                if ($vendor['name'] === $current_user_username) {
                    $vendor_purchase = isset($vendor['purchase']) ? floatval($vendor['purchase']) : 0;
                   
                    $total_vendor_purchase += $vendor_purchase;

                }
                
            }
        }
    }
    if ($vendor_purchase > 0 && $total_sales > 0) {
        $profit_percentage = (($total_sales - $total_vendor_purchase) / $total_vendor_purchase) * 100;
        $result = round($profit_percentage, 2);
    } else {
        $result = 0;
    }

    return '<table class="market price-box box-dashboard">
        <tbody>
            <tr>
                <th>RETURN %</th>
            </tr>
            <tr>
                <td>
                    <span>' . $result . '</span>
                </td>
            </tr>
        </tbody>
    </table>';
}
add_shortcode('vendor_profit_percentage', 'calculate_vendor_profit_percentage');



function calculate_vendor_sales_quantity($atts)
{
    $current_user = wp_get_current_user();
    $vendor_name = $current_user->user_login;

    $args = array(
        'post_type'      => 'shop_order',
        'post_status'    => 'any',
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => 'Vendor Name',
                'value' => $vendor_name,
            ),
        ),
    );

    $order_query = new WC_Order_Query($args);
    $orders = $order_query->get_orders();

    $total_sales_quantity = 0;

    foreach ($orders as $order) {
        $items = $order->get_items();

        foreach ($items as $item_id => $item) {
            $vendor_names = $item->get_meta('Vendor Name');
            if ($vendor_names === $vendor_name) {
                $total_sales_quantity += $item->get_quantity();
            }
        }
    }

    return '<table class="market price-box box-dashboard">
        <tbody>
            <tr>
                <th>CASES SOLD</th>
            </tr>
            <tr>
                <td>
                    <span>' . $total_sales_quantity . '</span>
                </td>
            </tr>
        </tbody>
    </table>';
}
add_shortcode('vendor_sales_quantity', 'calculate_vendor_sales_quantity');

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

