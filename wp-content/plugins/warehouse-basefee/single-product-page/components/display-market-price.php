<?php
function display_market_lowerest_price($atts)
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
                <td><?php echo wc_price($regular_price); ?></td>
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

add_shortcode('display_market_lowerest_price', 'display_market_lowerest_price');



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
?>