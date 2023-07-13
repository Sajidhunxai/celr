<?php
function display_vendor_data($product_id)
{
    // Retrieve the vendor data for the product
    $vendors = get_post_meta($product_id, 'vendors', true);
    $warehouse_fees = get_option('custom_warehouses', array());

    // Check if the plugin file is loaded




    echo '<div class="single-product-main" id="single-product-main">';
    if ($vendors) {
        // Get unique location and format values from vendors
        $locations = array_unique(array_column($vendors, 'location'));
        $formats = array_unique(array_column($vendors, 'format'));

        // Check for location and format filter checkboxes
        $location_filters = isset($_GET['location_filter']) ? $_GET['location_filter'] : $locations;
        $format_filters = isset($_GET['format_filter']) ? $_GET['format_filter'] : $formats;
        $vintages = array_unique(array_column($vendors, 'vintage'));
        $vintage_filters = isset($_GET['vintage_filter']) ? $_GET['vintage_filter'] : $vintages;

        if (in_array('all', $vintage_filters)) {
            $vintage_filters = $vintages;
        }
        // Check for sort option
        $sort_by = isset($_GET['sort_by']) ? $_GET['sort_by'] : 'price_asc';

        // Pagination
        $limit = 2; // Number of vendors per page
        $total_vendors = count($vendors);
        $total_pages = ceil($total_vendors / $limit);
        $current_page = get_query_var('paged') ? get_query_var('paged') : 1;
        $offset = ($current_page - 1) * $limit;
        echo '<div class="vendor-filters-container">';

        // Display the filter form
        echo '<form action="" method="get" id="vendor-filter-form" class="vendor-filter-form">';
        echo '<div class="vendor-filter">';

        echo '<label for="location_filter">WAREHOUSE LOCATION</label><br/>';
        // Generate checkbox for each unique location
        foreach ($locations as $location) {
            $checked = (in_array($location, $location_filters)) ? 'checked' : '';
            echo '<input type="checkbox" class="location-filter" name="location_filter[]" value="' . $location . '" ' . $checked . '>' . $location . '<br/>';
        }
        echo '</div>';
        echo '<div class="vendor-filter">';

        echo '<label for="format_filter">CASE SIZE</label><br/>';

        // Generate checkbox for each unique format
        foreach ($formats as $format) {
            $checked = (in_array($format, $format_filters)) ? 'checked' : '';
            echo '<input type="checkbox" class="format-filter" name="format_filter[]" value="' . $format . '" ' . $checked . '>' . $format . '<br/>';
        }
        echo '</div>';



        // Add the JavaScript that will automatically submit the form when a checkbox is    d or sort option is modified
        echo '
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(".location-filter, .format-filter, #sort_by").change(function() {
                $("#vendor-filter-form").submit();
            });
        </script>
        ';

        $vendors = array_filter($vendors, function ($vendor) use ($location_filters, $format_filters, $vintage_filters) {
            return in_array($vendor['location'], $location_filters)
                && in_array($vendor['format'], $format_filters)
                && in_array($vendor['vintage'], $vintage_filters);
        });


        // Sort vendors based on price
        usort($vendors, function ($a, $b) use ($sort_by) {
            if ($sort_by == 'price_desc') {
                return $b['price'] - $a['price'];
            } else {
                return $a['price'] - $b['price'];
            }
        });
        echo '</div>';

        echo '<div class="vendor-offer-main">';
        echo '<div class="offer-header"> <div class="number-offers">' . count($vendors) . ' Offers </div>';

        echo '<div class="vendor-price-sort"> ';

        echo '<label for="sort_by">Sort by:</label>';
        echo '<select id="sort_by" name="sort_by">
                        <option value="price_asc" ' . ($sort_by == 'price_asc' ? 'selected' : '') . '>Price: Low to High</option>
                        <option value="price_desc" ' . ($sort_by == 'price_desc' ? 'selected' : '') . '>Price: High to Low</option>
                    </select><br/>';
        echo '</div></div>';

        echo '</form>';
        // Loop through each vendor
        // Loop through vendors for the current page
        for ($i = $offset; $i < min($offset + $limit, $total_vendors); $i++) {
            $vendor = $vendors[$i];

            // Fetch the warehouse fee for the vendor's location
            $warehouse_fee = isset($warehouse_fees[$vendor['location']]) ? $warehouse_fees[$vendor['location']] : 0;

            // Display the vendor data
            $current_user = wp_get_current_user();
            $warehouse_location = get_user_meta($current_user->ID, 'warehouse_location', true);
            $warehouse_options = get_warehouse_options();


            if ($vendor) {
                echo '<div class="offer-container">';
                // echo '<h3>Vendor Data</h3>';
                // echo '<p>Vendor Name: ' . $vendor['name'] . '</p>';
                echo '<div class="offer-left">';
                echo '<p>Offer Price: </p> <span class="offer-price:"> £' . ($vendor['price']) . '</span>';
                // echo '<p>Vintage: ' . $vendor['vintage'] . '</p>';
                echo '<p>Availability: </p> <span> ' . $vendor['quantity'] . ' case(s)</span> ';

                echo '<p>Location:  </p> <span>' . $vendor['location'] . '</span>';
                // echo '<p>Purchase Price: ' . $vendor['purchase'] . '</p>';
                echo '<p>Transfer Fee :  </p>  <span> £ ';
                if ($warehouse_options[$warehouse_location]  ===  $vendor['location']) {
                    echo "0 (per case) </span>';";
                } else {
                    echo  $warehouse_fee . ' (per case) </span>';
                }


                echo '</div>';
                if (function_exists('custom_popup_render_popup_content')) {
                    // Call the function and pass the vendor name
              
                    $vendor_transfer_fee = $warehouse_fee;

                    $vendor_info = array(
                        'vendor_name' => $vendor['name'],
                        'vendor_price' => $vendor['price'],
                        'vendor_vintage' => $vendor['vintage'],
                        'vendor_quantity' => $vendor['quantity'],
                        'vendor_location' => $vendor['location'],
                        'vendor_email' => $vendor['email'],

                        'vendor_transfer_fee' => $warehouse_fee,

                    );
                    $content = custom_popup_render_popup_content($vendor_info, $vendor_transfer_fee,);

                    // Output the content
                    echo $content;
                }
                //Add offer  button
                echo '<div class="offer-right">';
                // echo '<button class="make-offer" data-product-id="' . $product_id . '" data-vendor-id="' . $vendor['id'] . '">Make an Offer</button>';
                echo '<button class="make-offer" data-product-id="' . $product_id  . '" data-vendor-name="' . $vendor['name'] . '" onclick="openPopup(' . $product_id . ', \'' . $vendor['name'] . '\')">Make Offer</button>';

                echo '<script>
                    jQuery(document).ready(function($) {
                        $(".make-offer").on("click", function() {
                            var productId = $(this).data("product-id");
                            var vendorName = $(this).data("vendor-name");

                            openPopup(productId, vendorName);
                        });
                    });
                </script>';
                // echo '<button class="add-to-cart" data-product-id="' . $product_id . '" data-quantity="1" data-price="' . ($vendor['price'] + $warehouse_fee) . '" data-vendor-name="' . $vendor['name'] . '">Make offer</button>';
                // echo '<button class="add-to-cart"  data-product-id="' . $product_id . '" data-quantity="1" data-price="' . ($vendor['price'] + $warehouse_fee) . '" data-vendor-name="' . $vendor['name']. '">Add to Cart</button>';
                echo '<button class="add-to-cart" data-product-id="' . $product_id . '" data-variation-id="' . $vendor['variation_id'] . '" data-quantity="1" data-price="' . ($vendor['price'] + $warehouse_fee) . '">Add to Cart</button>';

                echo '<select id="quantity" class="quantity">';

                for ($j = 1; $j <= $vendor['quantity']; $j++) {
                    echo '<option value="' . $j . '">' . $j . ' case</option>';
                }
                echo '</select>';
                echo '<p class="total-price">Total Price: £<span>';
                if ($warehouse_options[$warehouse_location]  ===  $vendor['location']) {
                    echo $vendor['price'] . '</span></p>';
                } else {
                    echo ($vendor['price'] + $warehouse_fee) . '</span></p>';
                }
                // echo $warehouse_fee;
                echo '</div>';

                echo '</div>';
            }
            // Show the warehouse fee

        }
        echo '</div>';

        echo '</div>';
        echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
        echo '<script>
        $(document).ready(function() {
            function addToCart(data) {
                var totalPrice = data.price * data.quantity;
                console.log(totalPrice);
                var transferFee = ' . json_encode($warehouse_options[$warehouse_location] === $vendor['location']) . ' ? 0 : data.warehouseFee;
            
                var cartUrl = "?add-to-cart=" + data.productId + "&quantity=" + data.quantity + "&price=" + totalPrice + "&vendor_name=" + data.vendorName + "&warehouse_fee=" + transferFee;
            
                window.location.href = cartUrl;
            }
            
            // // Usage:
            // $(".add-to-cart").click(function() {
            //     var data = {
            //         productId: $(this).data("product-id"),
            //         quantity: parseInt($(this).data("quantity")),
            //         price: '.$vendor['price'].',
            //         vendorName: $(this).data("vendor-name"),
            //         warehouseFee: ' . $warehouse_fee . '
            //     };
            
            //     addToCart(data);
            // });

            $(".add-to-cart").click(function(e) {
                e.preventDefault();
                var productId = $(this).data("product-id");
                var variationId = $(this).data("variation-id");
                var quantity = $(this).data("quantity");
                var price = $(this).data("price");
                
                console.log(price)
                var data = {
                    action: "add_to_cart",
                    vendor_name: productId,
                    variation_id: variationId,
                    quantity: quantity,
                    price: price
                };

                $.post("' . admin_url('admin-ajax.php') . '", data, function(response) {
                    window.location.href = "' . wc_get_cart_url() . '";
                });
            });
                    
            $(".quantity").change(function() {
                var quantity = parseInt($(this).val());
                var price = parseFloat($(this).parent().siblings().find(".offer-price").text());
                var totalPrice;
               
                if (' . json_encode($warehouse_options[$warehouse_location] === $vendor['location']) . ') {
                    totalPrice = ' . $vendor['price'] . ' * quantity;
                } else {
                    totalPrice = (' . $vendor['price'] . ' + ' . $warehouse_fee . ') * quantity;
                }
        
                $(".total-price span").text(totalPrice.toString());
            
                $(this).parent().siblings().find(".offer-price").text(totalPrice.toFixed(2));
                $(".add-to-cart").attr("data-quantity", quantity);

            });
           

          
            
        
            $(".location-filter, .format-filter, #sort_by").change(function() {
                $("#vendor-filter-form").submit();
            });
        
            
        });
        </script>';



        // Pagination links
        if ($total_pages > 1) {
            echo '<div class="single-product-pagination">';

            if ($current_page > 1) {
                echo '<a href="' . esc_url(get_pagenum_link($current_page - 1)) . '">' . __('« Prev') . '</a>';
            }

            if ($current_page < $total_pages) {
                echo '<a href="' . esc_url(get_pagenum_link($current_page + 1)) . '">' . __('Next »') . '</a>';
            }

            echo '</div>';
        }
    }
}
function shortcode_vendor_data()
{
    // Extract attributes from the shortcode
    global $product;
    $id = $product->get_id();


    if ($id) {
        // Call the function with the product id from the shortcode attributes
        display_vendor_data($id);
    }
}
add_shortcode('vendor_data', 'shortcode_vendor_data');

add_action('wp_ajax_add_to_cart', 'ajax_add_to_cart');
add_action('wp_ajax_nopriv_add_to_cart', 'ajax_add_to_cart');
function ajax_add_to_cart() {
    if (isset($_POST['vendor_name']) && isset($_POST['variation_id']) && isset($_POST['quantity']) && isset($_POST['price'])) {
        $vendor_name = sanitize_text_field($_POST['vendor_name']);
        $variation_id = intval($_POST['variation_id']);
        $quantity = intval($_POST['quantity']);
        $price = floatval($_POST['price']);

        $cart_item_data = array(
            'price' => $price,
        );

        WC()->cart->add_to_cart($vendor_name, $quantity, $variation_id, array(), $cart_item_data);

        die();
    }
}
?>