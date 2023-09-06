<?php

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
                echo "<h2>{$listing['name']}</h2>";
                echo "<form id='edit-form-{$product_id}-{$listing['vendor']['name']}' class='edit-form'>";
               
                echo "<div class='edit-form-field'><input type='hidden' name='action' value='update_vendor_listing'>
                <input type='hidden' name='product_id' value='{$product_id}'>
                <input type='hidden' name='vendor_name' value='{$listing['vendor']['name']}'>

               <h4>Price: </h4><input type='text' name='vendor_price' placeholder='Enter new price' value='{$listing['vendor']['price']}'>
               <h4>Format:</h4><input type='text' name='vendor_format' placeholder='Enter format' value='{$listing['vendor']['format']}'>
               <h4>Quantity:</h4><input type='text' name='vendor_quantity' placeholder='Enter quantity' value='{$listing['vendor']['quantity']}'>
               
               
               </div>";
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
                        var form = document.getElementById('edit-form-' + product_id + '-' + vendor_name);
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
                                    alert('Failed to update vendor listing !' + response.error);
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
        }else{

            echo '<div class="no-listing">
            <h2>No Product Found</h2>
            <p>You did not have added any product.</p>
            </div>
            ';
        }
      
        echo "</div>";
    } else {
        $redirect_script = '
            <script>
                window.location.href = "' . esc_url(home_url('/login')) . '";
            </script>
        ';
     return $redirect_script;   
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

