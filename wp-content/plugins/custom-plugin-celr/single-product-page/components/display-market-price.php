<?php
    function display_market_lowest_price($atts)
    {
        $atts = shortcode_atts(
            array(
                'product_id' => get_the_ID(),
            ),
            $atts
        );

        // Get the product ID
        $product_id = intval($atts['product_id']);

        $product = wc_get_product($product_id);
        if ($product === false) {
            return; // No product found
        }

        $regular_price = $product->get_regular_price();

        // Assuming $vendors data is retrieved from your DB or a specific function.
        $vendors = get_post_meta($product_id, 'vendors', true); // Get existing vendors



        ob_start();
    ?>
        <div class="market-price-box" style="display:flex; gap:10px;padding-top:1f0px;">

            <table class="market price-box">
                <th>Market Price</th>
                <tr>
                    <td>
                        <?php
                        // Retrieve the selected year from the URL parameter
                        $selected_year = isset($_POST['pa_vintage']) ? sanitize_text_field($_POST['pa_vintage']) : '';

                        if ($product->is_type('variable')) {
                            $variations = $product->get_available_variations();
                            $selected_variation_price = null;

                            foreach ($variations as $variation) {
                                $variation_attributes = $variation['attributes'];
                                $variation_vintage = $variation_attributes['attribute_pa_vintage'];

                                if ($variation_vintage === $selected_year) {
                                    $selected_variation_price = $variation['display_price'];
                                }
                            
                
                            }

                            foreach ($variations as $variation) {
                                $variable_prices[] = $variation['display_price'];
                            }
            
                        

                            if ($selected_variation_price !== null) {
                                // Display the price of the selected variation
                                echo wc_price($selected_variation_price);
                            } else{
                                    // Get the minimum price among the matching variations
                                    $min_price = min($variable_prices);
                                    $max_price = max($variable_prices);
                    
                                    // Display the variable product price range
                                    echo wc_price($min_price);

                                } 
                            
                        } else {
                            // Display the regular price for non-variable products
                            echo wc_price($regular_price);
                        }
                        ?>

                    </td>
                </tr>
            </table>

            <table class="lowest price-box">

                <th>Lowest Price</th>

                <tbody>
                    <tr>
                        <td>
                            <?php
                            if (!empty($vendors)) {
                                $lowest_price = null;

                                foreach ($vendors as $vendor) {
                                    $price = $vendor['price'];

                                    if ($lowest_price === null || $price < $lowest_price) {
                                        $lowest_price = $price;
                                    }
                                }

                                if ($lowest_price !== null && $lowest_price < $regular_price) {
                                    // Display the lowest price in your desired format or HTML structure
                                    echo wc_price($lowest_price);
                                } else {
                                    echo wc_price($lowest_price);
                                }
                            } else {
                                echo wc_price($regular_price);
                            }

                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    <?php
        return ob_get_clean();
    }

    add_shortcode('display_market_lowest_price', 'display_market_lowest_price');



function add_vendor_price_button_shortcode($atts)
{
    $product_id = get_the_ID();

    $atts = shortcode_atts(
        array(
            'product_id' => $product_id, // Default product ID if not specified in the shortcode
        ),
        $atts
    );

    $product_id = $atts['product_id'];
    $permalink = add_query_arg('product_id', $product_id, get_permalink(get_page_by_path('add-vendor-price')));

    return esc_url($permalink);
}
add_shortcode('add_vendor_price_button', 'add_vendor_price_button_shortcode');

function calculate_price_difference($product_id)
{
    $product = wc_get_product($product_id);
    if ($product === false) {
        return 'No product found';
    }

    // Get the selected variation price if available
    $selected_variation_price = null;
    if ($product->is_type('variable')) {
        $variations = $product->get_available_variations();
        $selected_year = isset($_POST['pa_vintage']) ? sanitize_text_field($_POST['pa_vintage']) : '';

        foreach ($variations as $variation) {
            $variation_attributes = $variation['attributes'];
            $variation_vintage = $variation_attributes['attribute_pa_vintage'];

            if ($variation_vintage === $selected_year) {
                $selected_variation_price = $variation['display_price'];
                break;
            }
        }
    }

    // Get the vendors' prices
    $vendors = get_post_meta($product_id, 'vendors', true);
    if (empty($vendors)) {
        return '0% same';
    }

    // Find the lowest vendor price
    $lowest_price = null;
    foreach ($vendors as $vendor) {
        $price = $vendor['price'];

        if ($lowest_price === null || $price < $lowest_price) {
            $lowest_price = $price;
        }
    }

    // Compare the selected variation price with the lowest vendor price
    if ($selected_variation_price !== null) {
        if ($lowest_price !== null && $lowest_price < $selected_variation_price) {
            $difference = $selected_variation_price - $lowest_price;
            $percentage = round(($difference / $selected_variation_price) * 100, 2);
            return $percentage . '% below';
        } else if ($lowest_price !== null && $lowest_price > $selected_variation_price) {
            $difference = $lowest_price - $selected_variation_price;
            $percentage = round(($difference / $selected_variation_price) * 100, 2);
            return $percentage . '% above';
        } else {
            return '0% same';
        }
    }

    // If no selected variation price available, compare with the minimum variation price
    if ($product->is_type('variable')) {
        $variations = $product->get_available_variations();
        $variable_prices = array();

        foreach ($variations as $variation) {
            $variable_prices[] = $variation['display_price'];
        }

        $min_price = min($variable_prices);

        if ($lowest_price !== null && $lowest_price < $min_price) {
            $difference = $min_price - $lowest_price;
            $percentage = round(($difference / $min_price) * 100, 2);
            return $percentage . '% below';
        } else if ($lowest_price !== null && $lowest_price > $min_price) {
            $difference = $lowest_price - $min_price;
            $percentage = round(($difference / $min_price) * 100, 2);
            return $percentage . '% above';
        } else {
            return 'Same as vendor price';
        }
    }

    return 'No variation price available';
}

function display_price_difference($atts)
{
    $atts = shortcode_atts(
        array(
            'product_id' => get_the_ID(),
        ),
        $atts
    );

    $product_id = intval($atts['product_id']);
    $difference = calculate_price_difference($product_id);

    return '<span class="price-difference">' . $difference . '</span>';
}

add_shortcode('price_difference', 'display_price_difference');


function show_vintage_only($atts)
{
    $atts = shortcode_atts(
        array(
            'product_id' => get_the_ID(),
        ),
        $atts
    );

    $product_id = intval($atts['product_id']);
    $product = wc_get_product($product_id);

    if ($product === false) {
        return 'No product found';
    }

    // Check if the filter_vintage parameter is set in the URL
    $filter_vintage = isset($_GET['filter_vintage']) ? intval($_GET['filter_vintage']) : null;

    // Check if the product is variable type
    if ($product->is_type('variable')) {
        $variations = $product->get_available_variations();
        $vintage_values = array();

        foreach ($variations as $variation) {
            $variation_attributes = $variation['attributes'];
            $variation_vintage = $variation_attributes['attribute_pa_vintage'];

            if (!empty($variation_vintage) && is_numeric($variation_vintage)) {
                $vintage_values[] = intval($variation_vintage);
            }
        }

        // If filter_vintage parameter is set and exists in the variations, use its value
        if ($filter_vintage !== null && in_array($filter_vintage, $vintage_values)) {
            return $filter_vintage;
        }

        // Pick a random vintage year if there are values within the variations
        if (!empty($vintage_values)) {
            $allowed_years = array_unique($vintage_values);
            $random_vintage = $allowed_years[array_rand($allowed_years)];
            return $random_vintage;
        } else {
            return 'No vintage attribute available';
        }
    } else {
        // If the product is not variable type, check the single product's vintage attribute
        $vintage = $product->get_attribute('pa_vintage');

        if (!empty($vintage) && is_numeric($vintage)) {
            // If filter_vintage parameter is set and matches the product's vintage, use its value
            if ($filter_vintage !== null && intval($vintage) === $filter_vintage) {
                return $filter_vintage;
            }

            return $vintage;
        } else {
            return 'No vintage attribute available';
        }
    }
}

add_shortcode('show_vintage', 'show_vintage_only');




?>