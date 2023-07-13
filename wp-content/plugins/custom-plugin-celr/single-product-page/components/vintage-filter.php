<?php
// Define the shortcode
// AJAX callback function to filter variations based on vintage
// Define the shortcode
// AJAX callback function to filter variations based on vintage
add_action('wp_ajax_filter_variations', 'filter_variations_callback');
add_action('wp_ajax_nopriv_filter_variations', 'filter_variations_callback');
function filter_variations_callback()
{

    // Get the product ID
    $product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;

    // Get the selected year from the request
    $selected_year = isset($_POST['pa_vintage']) ? sanitize_text_field($_POST['pa_vintage']) : '';
    // error_log('Received pa_vintage value: ' . $selected_year);
    // Get the selected year from the request
    $selected_year = isset($_POST['pa_vintage']) ? sanitize_text_field($_POST['pa_vintage']) : '';

    // If the selected_year is not set in the POST data, check if it exists in the URL parameter
    if (empty($selected_year) && isset($_GET['product_year'])) {
        $selected_year = sanitize_text_field($_GET['product_year']);
    }

    // Get the product object
    $product = wc_get_product($product_id);

    if ($product && $product->is_type('variable')) {
        // Get the variations
        $variations = $product->get_available_variations();

        // Filter the variations based on the selected year
        $filtered_variations = array_filter($variations, function ($variation) use ($selected_year) {
            $variation_attributes = $variation['attributes'];
            $variation_vintage = $variation_attributes['attribute_pa_vintage'];

            return $variation_vintage === $selected_year;
        });

        // Prepare the response data
        $response_data = array();

        if (!empty($filtered_variations)) {
            foreach ($filtered_variations as $variation) {
                // var_dump($variation);
                $variation_attributes = $variation['attributes'];
                $variation_vintage = $variation_attributes['attribute_pa_vintage'];
                $variation_price_html = $variation['display_price'];
                $variation_image_url = $variation['image']['url'];
                $variation_color = $variation_attributes['attribute_pa_color'];
                $variation_drinking_windows = $variation_attributes['attribute_pa_drinking_windows'];
                $variation_region = $variation_attributes['attribute_pa_region'];


                $lowest_price_shortcode = display_market_lowest_price(array('product_id' => $product_id));

                // Build the variation data
                $variation_data = array(
                    'vintage' => $variation_vintage,
                    'title' => $product->get_name() . ' ' . $variation_vintage, // Include the year in the title
                    'price' => $variation_price_html,
                    'image' => $variation_image_url,
                    'prices' => $lowest_price_shortcode,
                    'color' => $variation_color,
                    'drinking' => $variation_drinking_windows,
                    'region' => $variation_region,
                );

                // Add the variation data to the response
                $response_data[] = $variation_data;
            }
        }
    } else {
        // Invalid product ID or not a variable product
        $response_data['error'] = 'Invalid product.';
    }

    // Return the response as JSON
    wp_send_json($response_data);
}

// Shortcode for displaying the vintage filter
function year_filter_shortcode($atts)
{
    global $post;

    // Infer the product_id from the current post
    $product_id = $post->ID;

    // Get the product object
    $product = wc_get_product($product_id);
    if (empty($product->get_price())) {
        $product_id = $product->get_id();
        echo "Sorry, there is an issue with this product. If you are an admin, please <a href='" . get_edit_post_link($product_id) . "'>Click here</a> to edit the product.";
    } else {
        ob_start();
?>
        <div class="vintage-filter-container">
            <div id="filtered-variations"></div>



            <?php
            // Get the product ID from the shortcode attributes
            // $product_id = isset($atts['product_id']) ? intval($atts['product_id']) : 0;

            // Get the product object
            // $product = wc_get_product($product_id);

            if ($product->is_type('variable')) {
                // Get the vintage variations
                $variations = $product->get_variation_attributes()['pa_vintage'];

                $buttonCount = count($variations);

                echo '<div class="' . ($buttonCount > 5 ? 'main-filter-form vintage-filter-form' : 'vintage-filter-form') . '">';
                echo '<button class="vintage-button" data-vintage="">All</button>';


                foreach ($variations as $variation) {
                    echo '<button class="vintage-button" data-vintage="' . $variation . '">' . $variation . '</button>';
                }
            }

            ?>
        </div>
        </div>
        <script>
            (function($) {
                $(document).ready(function() {


                    var defaultHtml = '<div class="product-container">  <div class="product-image">';
                    defaultHtml += '<img src="' + '<?php echo $product->get_image_id() ? wp_get_attachment_image_src($product->get_image_id(), "full")[0] : wc_placeholder_img_src(); ?>' + '" alt="' + '<?php echo $product->get_name(); ?>' + '" /> </div>';
                    defaultHtml += '<div class="product-title">';
                    defaultHtml += '<h1>' + '<?php echo $product->get_name(); ?>' + '</h1>';
                    defaultHtml += '<?php do_shortcode('[display_market_lowest_price]'); ?>';


                    $.ajax({
                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                        method: 'POST',
                        data: {
                            action: 'get_market_lowest_price_shortcode', // Custom AJAX action
                            product_id: <?php echo $product->get_id(); ?>, // Pass any necessary data
                        },
                        success: function(response) {
                            defaultHtml += response; // Add the shortcode output to the HTML
                            defaultHtml += '<?php echo do_shortcode('[product_attributes]'); ?><div class="elementor-element elementor-element-84aa3b3 elementor-widget__width-initial elementor-widget elementor-widget-wd_button" data-id="84aa3b3" data-element_type="widget" data-widget_type="wd_button.default"><div class="elementor-widget-container"><div class="wd-button-wrapper text-left"><a class="btn btn-style-default btn-style-semi-round btn-size-default btn-color-primary btn-full-width btn-icon-pos-right" href="<?php echo  do_shortcode('[add_vendor_price_button]'); ?>"><span class="wd-btn-text" data-elementor-setting-key="text">SELL THIS WINE</span></a></div></div></div></div></div></div>';

                            $('#filtered-variations').html(defaultHtml); // Update the element with the modified HTML
                        },
                        error: function() {
                            // Handle any error that occurs during the AJAX request
                            console.log('Error occurred during AJAX request');
                        }
                    });


                    // Add the default content to the "All Years" button
                    $('#filtered-variations').html(defaultHtml);

                    // Handle the click event of the vintage buttons
                    $('.vintage-button').on('click', function() {
                        var selectedYear = $(this).data('vintage');
                        var productId = <?php echo $product_id; ?>;

                        // Create a new URL object
                        var currentUrl = new URL(window.location.href);

                        // Set or replace the product_year parameter
                        currentUrl.searchParams.set('product_year', selectedYear);

                        // Update the URL without reloading the page
                        history.pushState(null, null, currentUrl.toString());


                        // Make the AJAX request to filter variations
                        $.ajax({
                            url: '<?php echo admin_url('admin-ajax.php'); ?>',
                            type: 'POST',
                            data: {
                                action: 'filter_variations',
                                product_id: productId,
                                pa_vintage: selectedYear
                            },
                            beforeSend: function() {
                                // Show loading indicator or perform any pre-request tasks
                                var loadingHtml = '<div class="loading-indicator" style="height: ' + $('#filtered-variations').height() + 'px; background-color: transparent;"><svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" style="margin: auto; display: block;" width="200px" height="200px" viewBox="0 0 100 100" preserveAspectRatio="xMidYMid"><g><g transform="translate(50 50)"><g transform="scale(0.8)"><g transform="translate(-50 -58)">';
loadingHtml += '<!-- Replace the URL below with the location of your SVG file -->';
loadingHtml += '<image xlink:href="<?php echo plugin_dir_url(__FILE__); ?>../../assets/images/loading.svg" ></image>';
loadingHtml += '</g></g></g></g></svg></div>';
$('#filtered-variations').html(loadingHtml);

                            },
                            success: function(response) {
                                // Handle the response
                                if (response.error) {
                                    $('#filtered-variations').html('<p>' + response.error + '</p>');
                                } else {
                                    // Clear the previous results
                                    $('#filtered-variations').empty();

                                    if (response.length === 0) {
                                        $('#filtered-variations').html(defaultHtml);
                                    } else {
                                        // Loop through the variations and display them
                                        $.each(response, function(index, variation) {
                                            var variationHtml = '<div class="product-container">  <div class="product-image">';
                                            variationHtml += '<img src="' + variation.image + '" alt="' + variation.title + '" /> </div>';
                                            variationHtml += '<div class="product-title">';
                                            variationHtml += '<h1>' + variation.title + '</h1>';
                                            variationHtml += variation.prices;

                                            variationHtml += '<ul class="attributes-add-form"><li class="attribute-add-form-item"><b>Color:</b> <b>' + variation.color + '</b></li><li class="attribute-add-form-item"><b>Region:</b> <b>' + variation.region + '</b></li><li class="attribute-add-form-item"><b>Drinking_windows:</b> <b>' + variation.drinking + '</b></li></ul>';

                                            variationHtml += '<div class="elementor-element elementor-element-84aa3b3 elementor-widget__width-initial elementor-widget elementor-widget-wd_button" data-id="84aa3b3" data-element_type="widget" data-widget_type="wd_button.default"><div class="elementor-widget-container"><div class="wd-button-wrapper text-left"><a class="btn btn-style-default btn-style-semi-round btn-size-default btn-color-primary btn-full-width btn-icon-pos-right" href="<?php echo  do_shortcode('[add_vendor_price_button]'); ?>"><span class="wd-btn-text" data-elementor-setting-key="text">SELL THIS WINE</span></a></div></div></div></div></div>';


                                            $('#filtered-variations').append(variationHtml);
                                        });
                                    }
                                }
                            },
                            error: function(xhr, status, error) {
                                // Handle the error
                            },
                            complete: function() {
                                // Perform any post-request tasks

                            }
                        });

                    });

                    // Get the product_year parameter value from the URL
                    var urlParams = new URLSearchParams(window.location.search);
                    var productYear = urlParams.get('product_year');

                    if (productYear) {
                        // Trigger the click event on the button with the matching vintage
                        $('.vintage-button[data-vintage="' + productYear + '"]').click();
                    }

                    // Handle the click event of the vintage links within the filtered variations
                    $('#filtered-variations').on('click', '.vintage-link', function(e) {
                        e.preventDefault();

                        var selectedVintage = $(this).data('vintage');

                        // Perform any action you want with the selected vintage
                        console.log('Selected Vintage: ' + selectedVintage);
                    });
                    window.onload = function() {
                        // Get the product_year parameter value from the URL
                        var urlParams = new URLSearchParams(window.location.search);
                        var productYear = urlParams.get('product_year');

                        if (productYear) {
                            // Trigger the click event on the button with the matching vintage
                            $('.vintage-button[data-vintage="' + productYear + '"]').click();
                        }
                    }
                });
            })(jQuery);
        </script>
<?php
        return ob_get_clean();
    }
}
add_shortcode('year_filter', 'year_filter_shortcode');



function vintage_filter_shortcode($atts)
{
    $product_id = get_the_ID();

    $vendors = get_post_meta($product_id, 'vendors', true);

    $vintages = array_unique(array_column($vendors, 'vintage'));

    // Check for vintage filter
    $vintage_filters = isset($_GET['vintage_filter']) ? $_GET['vintage_filter'] : [];

    // Output the filter form
    ob_start();
    echo '<form action="" method="get" id="vintage-filter-form" >';

    $buttonCount = count($vintages);

    echo '<div class="' . ($buttonCount > 5 ? 'main-filter-form vintage-filter-form' : 'vintage-filter-form') . '">';
    echo '<button type="submit" class="vintage-filter-button" name="vintage_filter[]" value="all">All</button>';

    foreach ($vintages as $vintage) {
        $active = (in_array($vintage, $vintage_filters)) ? ' active' : '';
        echo '<button type="submit" class="vintage-filter-button' . $active . '" name="vintage_filter[]" value="' . $vintage . '">' . $vintage . '</button>';
    }

    echo '</div>';

    // Button to select all vintages

    echo '</form>';

    // Add JavaScript to add active class to button on click
    echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
    echo '<script>
        $(".vintage-filter").click(function() {
            $(this).toggleClass("active");
        });
    </script>';

    return ob_get_clean();
}
add_shortcode('vintage_filter', 'vintage_filter_shortcode');


function add_product_year_query_var($vars)
{
    $vars[] = 'product_year';
    return $vars;
}
add_filter('query_vars', 'add_product_year_query_var');



add_action('wp_ajax_get_market_lowest_price_shortcode', 'get_market_lowest_price_shortcode');
add_action('wp_ajax_nopriv_get_market_lowest_price_shortcode', 'get_market_lowest_price_shortcode');
function get_market_lowest_price_shortcode()
{
    // Retrieve the necessary data from the AJAX request
    $product_id = $_POST['product_id'];

    // Construct the shortcode with the necessary attributes
    $shortcode = '[display_market_lowest_price product_id="' . $product_id . '"]';

    // Execute the shortcode and return the output
    $output = do_shortcode($shortcode);

    echo $output;
    wp_die();
}




?>