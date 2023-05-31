<?php

/**
 *
 * The framework's functions and definitions
 */

define('WOODMART_THEME_DIR', get_template_directory_uri());
define('WOODMART_THEMEROOT', get_template_directory());
define('WOODMART_IMAGES', WOODMART_THEME_DIR . '/images');
define('WOODMART_SCRIPTS', WOODMART_THEME_DIR . '/js');
define('WOODMART_STYLES', WOODMART_THEME_DIR . '/css');
define('WOODMART_FRAMEWORK', '/inc');
define('WOODMART_DUMMY', WOODMART_THEME_DIR . '/inc/dummy-content');
define('WOODMART_CLASSES', WOODMART_THEMEROOT . '/inc/classes');
define('WOODMART_CONFIGS', WOODMART_THEMEROOT . '/inc/configs');
define('WOODMART_HEADER_BUILDER', WOODMART_THEME_DIR . '/inc/header-builder');
define('WOODMART_ASSETS', WOODMART_THEME_DIR . '/inc/admin/assets');
define('WOODMART_ASSETS_IMAGES', WOODMART_ASSETS . '/images');
define('WOODMART_API_URL', 'https://xtemos.com/licenses/api/');
define('WOODMART_DEMO_URL', 'https://woodmart.xtemos.com/');
define('WOODMART_PLUGINS_URL', WOODMART_DEMO_URL . 'plugins/');
define('WOODMART_DUMMY_URL', WOODMART_DEMO_URL . 'dummy-content-new/');
define('WOODMART_TOOLTIP_URL', WOODMART_DEMO_URL . 'theme-settings-tooltips/');
define('WOODMART_SLUG', 'woodmart');
define('WOODMART_CORE_VERSION', '1.0.38');
define('WOODMART_WPB_CSS_VERSION', '1.0.2');

if (!function_exists('woodmart_load_classes')) {
    function woodmart_load_classes()
    {
        $classes = array(
            'Singleton.php',
            'Api.php',
            'Googlefonts.php',
            'Config.php',
            'Layout.php',
            'License.php',
            'Notices.php',
            'Options.php',
            'Stylesstorage.php',
            'Theme.php',
            'Themesettingscss.php',
            'Vctemplates.php',
            'Wpbcssgenerator.php',
            'Registry.php',
            'Pagecssfiles.php',
        );

        foreach ($classes as $class) {
            require WOODMART_CLASSES . DIRECTORY_SEPARATOR . $class;
        }
    }
}

woodmart_load_classes();

new WOODMART_Theme();

define('WOODMART_VERSION', woodmart_get_theme_info('Version'));

// Register a custom meta box
function vendor_meta_box()
{
    add_meta_box('vendor_meta', 'Vendor Information', 'render_vendor_meta_box', 'product', 'normal', 'high');
}
add_action('add_meta_boxes', 'vendor_meta_box');

// Render the meta box content
// Function to render the vendor meta box
function render_vendor_meta_box($post)
{
    wp_nonce_field(basename(__FILE__), 'vendor_meta_nonce');
    $vendors = get_post_meta($post->ID, 'vendors', true);
    $current_user = wp_get_current_user();
    $user_name = $current_user->display_name;

    // Get the options for the attribute 'location'
    $location_attr = 'pa_location'; // Replace with the slug of the 'location' attribute
    $terms_locations = get_terms(
        array(
            'taxonomy' => $location_attr,
            'hide_empty' => false,
        )
    );
    $location_options = array();
    foreach ($terms_locations as $term) {
        $location_options[] = $term->name;
    }

    // Get the options for the attribute 'quantity' dynamically based on the current post
    $quantity_attr = 'pa_quantity';
    $terms_quantities = get_terms(
        array(
            'taxonomy' => $quantity_attr,
            'hide_empty' => false,
        )
    );
    $quantity_options = array();
    foreach ($terms_quantities as $term) {
        $quantity_options[] = $term->name;
    }

    // Get the options for the attribute 'vintage' dynamically based on the current post
    $quantity_attr = 'pa_vintage';
    $terms_vintages = get_terms(
        array(
            'taxonomy' => $quantity_attr,
            'hide_empty' => false,
        )
    );
    $vintages_options = array();
    foreach ($terms_vintages as $term) {
        $vintages_options[] = $term->name;
    }
    //get the options for 'format' dynamic based on the current post
    $format_attr = 'pa_format';
    $terms_formats = get_terms(
        array(
            'taxonomy' => $format_attr,
            'hide_empty' => false,
        )
    );
    $formats_options = array();
    foreach ($terms_formats as $term) {
        $formats_options[] = $term->name;
    }



    // Get the tags used for the product
    $product_tags = get_the_terms($post->ID, 'product_tag');
    $product_tag_names = array();
    if ($product_tags && !is_wp_error($product_tags)) {
        foreach ($product_tags as $tag) {
            $product_tag_names[] = $tag->name;
        }
    }

?>
    <div id="vendor-repeater">
        <?php if ($vendors) : ?>
            <?php foreach ($vendors as $vendor) : ?>
                <div class="vendor-row">
                    <input type="hidden" name="vendor_id[]" value="<?php echo esc_attr($vendor['id']); ?>" />
                    <input type="text" name="vendor_name[]" value="<?php echo esc_attr($vendor['name']); ?>" placeholder="Vendor Name" />
                    <input type="text" name="vendor_price[]" value="<?php echo esc_attr($vendor['price']); ?>" placeholder="Vendor Price" />
                    <select name="vendor_location[]">
                        <option value="">Select Location</option>
                        <?php foreach ($location_options as $option) : ?>
                            <option value="<?php echo esc_attr($option); ?>" <?php selected($vendor['location'], $option); ?>><?php echo esc_html($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="vendor_quantity[]">
                        <option value="">Select Quantity</option>
                        <?php foreach ($quantity_options as $option) : ?>
                            <option value="<?php echo esc_attr($option); ?>" <?php selected($vendor['quantity'], $option); ?>><?php echo esc_html($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="vendor_vintage[]">
                        <option value="">Select Vintage</option>
                        <?php foreach ($vintages_options as $option) : ?>
                            <option value="<?php echo esc_attr($option); ?>" <?php selected($vendor['vintage'], $option); ?>><?php echo esc_html($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="vendor_format[]">
                        <option value="">Select Formats</option>
                        <?php foreach ($formats_options as $option) : ?>
                            <option value="<?php echo esc_attr($option); ?>" <?php selected($vendor['format'], $option); ?>><?php echo esc_html($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="vendor_purchase[]" value="<?php echo esc_attr($vendor['purchase']); ?>" placeholder="Vendor Purchase Price" />
                    <select name="vendor_tags[]">
                        <option value="">Select Tag</option>
                        <?php foreach ($product_tag_names as $tag) : ?>
                            <option value="<?php echo esc_attr($tag); ?>"><?php echo esc_html($tag); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="remove-vendor">Remove</button>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="vendor-row">
                <input type="text" name="vendor_name[]" value="<?php echo esc_attr($user_name); ?>" placeholder="Vendor Name" />
                <input type="text" name="vendor_price[]" placeholder="Vendor Price" />
                <select name="vendor_location[]">
                    <option value="">Select Location</option>
                    <?php foreach ($location_options as $option) : ?>
                        <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="vendor_quantity[]">
                    <option value="">Select Quantity</option>
                    <?php foreach ($quantity_options as $option) : ?>
                        <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="vendor_vintage[]">
                    <option value="">Select Vintage</option>
                    <?php foreach ($vintages_options as $option) : ?>
                        <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                    <?php endforeach; ?>
                </select>
                <select name="vendor_format[]">
                    <option value="">Select Formats</option>
                    <?php foreach ($formats_options as $option) : ?>
                        <option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="number" name="vendor_purchase[]" placeholder="Vendor Purchase Price" />
                <select name="vendor_tags[]">
                    <option value="">Select Tag</option>
                    <?php foreach ($product_tag_names as $tag) : ?>
                        <option value="<?php echo esc_attr($tag); ?>"><?php echo esc_html($tag); ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="remove-vendor">Remove</button>
            </div>
        <?php endif; ?>
        <button id="add-vendor">Add Vendor</button>
        <button type="submit" name="submit_vendor_form">Submit</button> <!-- Added submit button -->
    </div>
    <script>
        jQuery(function($) {
            $('#add-vendor').on('click', function(e) {
                e.preventDefault();
                var row = '<div class="vendor-row">' +
                    '<input type="text" name="vendor_name[]" value="<?php echo esc_attr($user_name); ?>" placeholder="Vendor Name" />' +
                    '<input type="text" name="vendor_price[]" placeholder="Vendor Price" />' +
                    '<select name="vendor_location[]">' +
                    '<option value="">Select Location</option>' +
                    '<?php foreach ($location_options as $option) : ?>' +
                    '<option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>' +
                    '<?php endforeach; ?>' +
                    '</select>' +
                    '<select name="vendor_quantity[]">' +
                    '<option value="">Select Quantity</option>' +
                    '<?php foreach ($quantity_options as $option) : ?>' +
                    '<option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>' +
                    '<?php endforeach; ?>' +
                    '</select>' +
                    '<select name="vendor_vintage[]">' +
                    '<option value="">Select Vintage</option>' +
                    '<?php foreach ($vintages_options as $option) : ?>' +
                    '<option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>' +
                    '<?php endforeach; ?>' +
                    '</select>' +
                    '<select name="vendor_format[]">' +
                    '<option value="">Select Format</option>' +
                    '<?php foreach ($formats_options as $option) : ?>' +
                    '<option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>' +
                    '<?php endforeach; ?>' +
                    '</select>' +
                    '<select name="vendor_tags[]">' +
                    '<option value="">Select Tag</option>' +
                    '<input type="number" name="vendor_purchase[]" placeholder="Vendor Purchase Price" />' +
                    '<?php foreach ($product_tag_names as $tag) : ?>' +
                    '<option value="<?php echo esc_attr($tag); ?>"><?php echo esc_html($tag); ?></option>' +
                    '<?php endforeach; ?>' +
                    '</select>' +
                    '<button class="remove-vendor">Remove</button>' +
                    '</div>';
                $('#vendor-repeater').append(row);
            });

            $(document).on('click', '.remove-vendor', function(e) {
                e.preventDefault();
                var vendorRow = $(this).closest('.vendor-row');
                var vendorId = vendorRow.find('input[name="vendor_id[]"]').val();

                $.ajax({
                    type: 'POST',
                    url: ajaxurl, // Use the correct URL to the WordPress admin-ajax.php file
                    data: {
                        action: 'remove_vendor',
                        vendor_id: vendorId,
                    },
                    success: function(response) {
                        if (response.success) {
                            vendorRow.remove();
                        } else {
                            console.log(response.error);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log(errorThrown);
                    }
                });
            });

            // Submit form on button click
            $('button[name="submit_vendor_form"]').on('click', function(e) {
                e.preventDefault();
                $('form#post').submit();
            });
        });
    </script>
    <?php
}

// Function to save vendor data
function save_vendor_data($post_id)
{
    if (!isset($_POST['vendor_meta_nonce']) || !wp_verify_nonce($_POST['vendor_meta_nonce'], basename(__FILE__))) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (!isset($_POST['vendor_name']) || !isset($_POST['vendor_price']) || !isset($_POST['vendor_location']) || !isset($_POST['vendor_quantity']) || !isset($_POST['vendor_vintage']) || !isset($_POST['vendor_format']) || !isset($_POST['vendor_purchase']) || !isset($_POST['vendor_tags'])) {
        return;
    }

    $vendor_names = $_POST['vendor_name'];
    $vendor_prices = $_POST['vendor_price'];
    $vendor_locations = $_POST['vendor_location'];
    $vendor_quantities = $_POST['vendor_quantity'];
    $vendor_vintages = $_POST['vendor_vintage'];
    $vendor_formats = $_POST['vendor_format'];
    $vendor_purchases = $_POST['vendor_purchase'];

    $vendor_tags = $_POST['vendor_tags'];

    $vendors = array();
    $count = count($vendor_names);

    for ($i = 0; $i < $count; $i++) {
        $vendor = array(
            'id' => $i + 1,
            'name' => sanitize_text_field($vendor_names[$i]),
            'price' => sanitize_text_field($vendor_prices[$i]),
            'location' => sanitize_text_field($vendor_locations[$i]),
            'quantity' => sanitize_text_field($vendor_quantities[$i]),
            'vintage' => sanitize_text_field($vendor_vintages[$i]),
            'format' => sanitize_text_field($vendor_formats[$i]),
            'purchase' => sanitize_text_field($vendor_purchases[$i]),

            'tags' => sanitize_text_field($vendor_tags[$i]),
        );
        $vendors[] = $vendor;
    }

    update_post_meta($post_id, 'vendors', $vendors);
}
add_action('save_post', 'save_vendor_data');

// AJAX callback to remove vendor
function remove_vendor()
{
    $vendorId = isset($_POST['vendor_id']) ? intval($_POST['vendor_id']) : 0;
    if ($vendorId > 0) {
        // Remove the vendor from the 'wp_postmeta' table
        delete_post_meta($vendorId, 'vendors');
        wp_send_json_success();
    } else {
        wp_send_json_error(array('error' => 'Invalid vendor ID.'));
    }
}
add_action('wp_ajax_remove_vendor', 'remove_vendor');
add_action('wp_ajax_nopriv_remove_vendor', 'remove_vendor');

// Display the vendor fields on the product page
function display_vendors()
{
    global $post;
    $vendors = get_post_meta($post->ID, 'vendors', true);

    if ($vendors) {
        echo '<ul>';
        foreach ($vendors as $vendor) {
            echo '<li>Vendor: ' . esc_html($vendor['name']) . ', Price: ' . esc_html($vendor['price']) . ', Location: ' . esc_html($vendor['location']) . ', Quantity: ' . esc_html($vendor['quantity']);

            // Get the product tags
            $tags = get_the_tags($post->ID);
            if ($tags) {
                echo ', Tags: ';
                foreach ($tags as $tag) {
                    echo '<span>' . esc_html($tag->name) . '</span>';
                }
            }

            echo '</li>';
        }
        echo '</ul>';
    }
}


// Form shortcode
function vendor_form_shortcode($atts)
{
    if (is_user_logged_in()) {
        // User is logged in, show the form
        // Extract the product ID from the shortcode attributes
        $atts = shortcode_atts(
            array(
                'product_id' => 0,
            ),
            $atts
        );

        // Get the product ID
        $product_id = intval($atts['product_id']);
        $product_tag_names = array();

        // Get the product tags
        $product_tags = get_the_terms($product_id, 'product_tag');
        if ($product_tags && !is_wp_error($product_tags)) {
            foreach ($product_tags as $tag) {
                $product_tag_names[] = $tag->name;
            }
        }

        // Get the post data
        $post = get_post($product_id);
        $post_title = $post->post_title;
        $post_content = $post->post_content;
        $post_image = get_the_post_thumbnail_url($product_id, 'full');

        //get price and attributes
        $product_attributes = get_post_meta($product_id, 'product_attributes', true);

        $product_price = wc_get_product($product_id);
        $regular_price = $product_price->get_regular_price();
        //lowest price

        $vendors = get_post_meta($product_id, 'vendors', true); // Get existing vendors


        ob_start();
    ?>
        <div class="column-container">
            <div class="column large">
                <?php if ($post_image) : ?>
                    <img src="<?php echo esc_url($post_image); ?>" alt="<?php echo esc_html($post_title); ?>" width="400" />
                <?php else : ?>
                    <img src="<?php echo get_site_url() . '/wp-content/uploads/2023/05/image-44.png'; ?> " alt="Default Image" width="400" />
                <?php endif; ?>

            </div>

            <div class="column large">
                <h2>
                    <?php echo esc_html(strtoupper($post_title)); ?>
                </h2>
                <div id="vendor-repeater">
                    <form method="post">
                        <?php wp_nonce_field(basename(__FILE__), 'vendor_meta_nonce'); ?>
                        <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>" />

                        <div class="vendor-row">
                            <?php
                            $current_user = wp_get_current_user();
                            $user_name = $current_user->display_name;
                            ?>
                            <input type="hidden" name="vendor_name[]" placeholder="Vendor Name" value="<?php echo esc_attr($user_name); ?>" readonly />
                            <div class="form-control">
                                <label for="price" required style="display: inline-block;">My offer price<span class="required">*</span></label>
                                <div style="position: relative;">
                                    <input type="number" name="vendor_price[]" placeholder="" max="1000" min="1" required />
                                    <span style="position: absolute; top: 45%; transform: translateY(-50%); left: 20px;">£</span>
                                </div>
                            </div>
                            <div class="form-control">
                                <label for="vintage" required style="display: inline-block;">Vintage<span class="required">*</span></label>
                                <div class="custom-select-wrapper"> <!-- Add a custom select wrapper -->
                                    <select name="vendor_vintage[]" class="custom-select">
                                        <!-- Assign a class for custom select styling -->
                                        <?php
                                        $attribute_vintage = 'pa_vintage'; // Replace with your actual attribute slug
                                        $terms_vintages = get_terms(
                                            array(
                                                'taxonomy' => $attribute_vintage,
                                                'hide_empty' => false,
                                            )
                                        );
                                        foreach ($terms_vintages as $term_vintage) {
                                            echo '
                                                    <option value="' . esc_attr($term_vintage->name) . '">
                                                    ' . esc_html($term_vintage->name) . '
                                                    </option>
                                                    ';
                                        }
                                        ?>
                                    </select>

                                </div>
                            </div>
                            <div class="form-control">
                                <label for="fname" required style="display: inline-block;">Quantity<span class="required">*</span></label>
                                <div class="custom-select-wrapper"> <!-- Add a custom select wrapper -->
                                    <select name="vendor_quantity[]" class="custom-select">
                                        <!-- Assign a class for custom select styling -->
                                        <?php
                                        $attribute_quantity = 'pa_quantity'; // Replace with your actual attribute slug
                                        $terms_quantity = get_terms(
                                            array(
                                                'taxonomy' => $attribute_quantity,
                                                'hide_empty' => false,
                                            )
                                        );
                                        foreach ($terms_quantity as $term_quantity) {
                                            echo '
                                                    <option value="' . esc_attr($term_quantity->name) . '">
                                                    ' . esc_html($term_quantity->name) . '
                                                    </option>
                                                    ';
                                        }
                                        ?>
                                    </select>

                                </div>
                            </div>
                            <div class="form-control">
                                <label for="fname" required style="display: inline-block;">Format<span class="required">*</span></label>
                                <div class="custom-select-wrapper"> <!-- Add a custom select wrapper -->
                                    <select name="vendor_format[]" class="custom-select">
                                        <!-- Assign a class for custom select styling -->
                                        <?php
                                        $attribute_format = 'pa_format'; // Replace with your actual attribute slug
                                        $terms_formats = get_terms(
                                            array(
                                                'taxonomy' => $attribute_format,
                                                'hide_empty' => false,
                                            )
                                        );
                                        foreach ($terms_formats as $term_format) {
                                            echo '
                                                    <option value="' . esc_attr($term_format->name) . '">
                                                    ' . esc_html($term_format->name) . '
                                                    </option>
                                                    ';
                                        }
                                        ?>
                                    </select>

                                </div>
                            </div>
                            <div class="form-control">
                                <label for="fname" required style="display: inline-block;">Warehouse<span class="required">*</span></label>
                                <div class="custom-select-wrapper"> <!-- Add a custom select wrapper -->
                                    <select name="vendor_location[]" class="custom-select">
                                        <!-- Assign a class for custom select styling -->
                                        <?php
                                        $attribute_name = 'pa_location'; // Replace with your actual attribute slug
                                        $terms = get_terms(
                                            array(
                                                'taxonomy' => $attribute_name,
                                                'hide_empty' => false,
                                            )
                                        );
                                        foreach ($terms as $term) {
                                            echo '
                                                    <option value="' . esc_attr($term->name) . '">
                                                    ' . esc_html($term->name) . '
                                                    </option>
                                                    ';
                                        }
                                        ?>
                                    </select>
                                </div>
                            </div>

                            <div class="form-control">
                                <label for="purchase" required style="display: inline-block;">Purchase price</label>
                                <div style="position: relative;">
                                    <input type="number" name="vendor_purchase[]" placeholder="" max="1000" min="1" />
                                    <div class="tooltip-icon">i
                                        <div class="tooltip-box">
                                            <span class="tooltip-text">Please provide the price at which you made the purchase or the total amount paid.</span>
                                        </div>
                                    </div>
                                </div>

                            </div>

                        </div>

                </div>
            </div>

            <div class="column">
                <div class="market-price-box" style="display:flex; gap:10px;padding-top:40px;">

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
                                <td> <?php
                                        if (!empty($vendors)) {
                                            $lowest_price = null;

                                            foreach ($vendors as $vendor) {
                                                $price = $vendor['price'];

                                                if ($lowest_price === null || $price < $lowest_price) {
                                                    $lowest_price = $price;
                                                }
                                            }

                                            if ($lowest_price !== null) {
                                                // Display the lowest price in your desired format or HTML structure
                                                echo '$ ' . $lowest_price;
                                            } else {
                                                echo wc_price($regular_price);
                                            }
                                        } else {
                                            echo wc_price($regular_price);
                                        }

                                        ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <table>

                    <tr>
                        <th>Robert Parker:</th>
                        <td>98/100</td>
                    </tr>
                    <tr>
                        <th>Neil Martin:</th>
                        <td>96/100</td>
                    </tr>
                    <tr>
                        <th>Lisa Perotti:</th>
                        <td>97/100</td>
                    </tr>
                </table>
                <div class="vendor-submit-button">
                    <input type="submit" name="submit_vendor_form" value="LIST WINE" />

                </div>
                </form>
            </div>

        </div>
        <div class="form-product-description">
            <h3> Description </h3>
            <?php echo $post_content; ?>
            <?php

            // Check if the product object is valid
            if ($product_price) {
                // Get the product attributes
                $attributes = $product_price->get_attributes();

                // Check if there are attributes
                if (!empty($attributes)) {
                    // Output the attributes as a list
                    echo '<ul class="attributes-add-form">';
                    foreach ($attributes as $attribute) {
                        echo '<li class="attribute-add-form-item"> <b>' . str_replace('pa_', '', $attribute->get_name()) .
                            ': </b>' . esc_html(implode(', ', $attribute->get_options())) .

                            '</li>';
                    }
                    echo '</ul>';
                }
            }
            ?>

        </div>


        <script>
            function toggleTooltip() {
                var tooltipBox = document.querySelector('.tooltip-box');
                if (tooltipBox.style.display === 'none') {
                    tooltipBox.style.display = 'block';
                } else {
                    tooltipBox.style.display = 'none';
                }
            }
        </script>
    <?php
        return ob_get_clean();
    } else {
        // User is not logged in, show a popup with a login form
        ob_start();
    ?>

        <div id="vendor-form-container">
            <div id="vendor-form-blur">


            </div>
            <div id="vendor-form-content">
                <h2>Sign in to start selling</h2>
                <!-- <p>Please log in to access the form.</p> -->
                <?php wp_login_form(); ?>
            </div>
        </div>

        <script>
            jQuery(document).ready(function($) {
                // Show the form with blur effect
                $('#vendor-form-container').addClass('show');

                // Close the form when clicking outside the content area
                $('#vendor-form-container').on('click', function(e) {
                    if (!$(e.target).closest('#vendor-form-content').length) {
                        $(this).removeClass('show');
                    }
                });
            });
        </script>
    <?php
        return ob_get_clean();
    }
}


// Save form data to product
function save_vendor_data_frontend()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_vendor_form'])) {
        if (!wp_verify_nonce($_POST['vendor_meta_nonce'], basename(__FILE__))) {
            wp_die('Security check failed.');
        }

        $product_id = intval($_POST['product_id']);

        $existing_vendors = get_post_meta($product_id, 'vendors', true); // Get existing vendors

        $vendors = $existing_vendors ? $existing_vendors : array();
        $names = !empty($_POST['vendor_name']) ? array_map('sanitize_text_field', $_POST['vendor_name']) : array();
        $prices = !empty($_POST['vendor_price']) ? array_map('sanitize_text_field', $_POST['vendor_price']) : array();
        $locations = !empty($_POST['vendor_location']) ? array_map('sanitize_text_field', $_POST['vendor_location']) : array();
        $quantity = !empty($_POST['vendor_quantity']) ? array_map('sanitize_text_field', $_POST['vendor_quantity']) : array();
        $vintage = !empty($_POST['vendor_vintage']) ? array_map('sanitize_text_field', $_POST['vendor_vintage']) : array();
        $format = !empty($_POST['vendor_format']) ? array_map('sanitize_text_field', $_POST['vendor_format']) : array();
        $purchase = !empty($_POST['vendor_purchase']) ? array_map('sanitize_text_field', $_POST['vendor_purchase']) : array();
        $tags = !empty($_POST['vendor_tags']) ? array_map('sanitize_text_field', $_POST['vendor_tags']) : array();

        $current_user = wp_get_current_user();
        $user_name = $current_user->display_name;

        for ($i = 0; $i < count($names); $i++) {
            if (!empty($names[$i]) && !empty($prices[$i]) && !empty($locations[$i]) && !empty($quantity[$i]) && !empty($vintage[$i])) {
                $vendor_tags = isset($tags[$i]) ? $tags[$i] : array(); // Retrieve the selected tags
                $vendors[] = array(
                    'name' => ($names[$i] === $user_name) ? $user_name : $names[$i],
                    'price' => $prices[$i],
                    'location' => $locations[$i],
                    'quantity' => $quantity[$i],
                    'vintage' => $vintage[$i],
                    'format' => $format[$i],
                    'purchase' => $purchase[$i],
                    'tags' => $vendor_tags,
                );
            }
        }

        // Update post meta
        $result = update_post_meta($product_id, 'vendors', $vendors);

        if ($result) {
            // Output JavaScript alert
            echo '<script>alert("Vendor data saved!"); window.location.href = "' . home_url() . '";</script>';
            exit();
        } else {
            // Output JavaScript alert for failed data storage
            echo '<script>alert("Failed to save vendor data!"); window.history.back();</script>';
            exit();
        }
    }
}

add_shortcode('vendor_form', 'vendor_form_shortcode');
add_action('init', 'save_vendor_data_frontend');
//product search Enqueue js and jquery
function enqueue_product_search_scripts()
{
    wp_enqueue_script(
        'product-search',
        get_template_directory_uri() . '/custom.js',
        // Replace with the actual path to your JavaScript file
        array('jquery'),
        '1.0',
        true
    );
    wp_enqueue_style('form', get_template_directory_uri() . '/custom.css', false, '1.1', 'all');


    // Localize the AJAX URL
    wp_localize_script(
        'product-search',
        'productSearchAjax',
        array('ajaxurl' => admin_url('admin-ajax.php'))
    );
}
add_action('wp_enqueue_scripts', 'enqueue_product_search_scripts');



//product search
function product_search_shortcode()
{
    ob_start();
    ?>
    <form method="get" action="<?php echo esc_url(get_permalink()); ?>" class="product-search-form">
        <input type="text" name="product_search" id="product_search" placeholder="I’d like to sell...">
        <button type="submit">Search</button>
    </form>
    <div class="dropdown">
        <div id="product_search_results" class="dropdown-content"></div>
    </div>
    <?php
    wp_reset_query();

    return ob_get_clean();
}

add_shortcode('product_search', 'product_search_shortcode');

//product search and show product and send to form
function product_search_ajax_handler()
{
    if (isset($_GET['product_search'])) {
        $product_search = sanitize_text_field($_GET['product_search']);
        $products = new WP_Query(
            array(
                'post_type' => 'product',
                's' => $product_search,
                'posts_per_page' => -1,
            )
        );

        ob_start();

        if ($products->have_posts()) {
            while ($products->have_posts()) :
                $products->the_post();
                $product_id = get_the_ID();
    ?>
                <a href="<?php echo esc_url(add_query_arg('product_id', $product_id, get_permalink(get_page_by_path('add-vendor-price')))); ?>">
                    <?php echo get_the_title(); ?>
                </a>
<?php
            endwhile;
        } else {
            echo 'No products found.';
        }

        wp_reset_postdata();
        $output = ob_get_clean();

        wp_send_json_success($output);
    }

    wp_send_json_error('Invalid request');
}

add_action('wp_ajax_product_search_ajax', 'product_search_ajax_handler');
add_action('wp_ajax_nopriv_product_search_ajax', 'product_search_ajax_handler');
function add_vendor_info_to_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
    if( isset( $_GET['vendor_name'] ) ){
        $vendor_name = sanitize_text_field( $_GET['vendor_name'] );
        $cart_item_data['vendor_name'] = $vendor_name;
    }
    
    if( isset( $_GET['price'] ) ){
        $price = sanitize_text_field( $_GET['price'] );
        $cart_item_data['vendor_price'] = $price;
    }

    return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'add_vendor_info_to_cart_item_data', 10, 3 );


// Display vendor information in the cart
function display_vendor_info_in_cart( $item_data, $cart_item ) {
    if( isset( $cart_item['vendor_name'] ) ){
        $item_data[] = array(
            'key'     => 'Vendor Name',
            'value'   => wc_clean( $cart_item['vendor_name'] ),
            'display' => '',
        );
    }
    if( isset( $cart_item['vendor_price'] ) ){
        $item_data[] = array(
            'key'     => 'Vendor Price',
            'value'   => wc_price( wc_clean( $cart_item['vendor_price'] ) ), // wc_price() to format the price
            'display' => '',
        );
    }
    return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'display_vendor_info_in_cart', 10, 2 );

// Adjust the product price

function set_vendor_in_session() {
    if ( isset( $_GET['vendor_name'] ) ) {
        $vendor_name = sanitize_text_field( $_GET['vendor_name'] );
        WC()->session->set( 'selected_vendor', $vendor_name );
    }
}
add_action( 'init', 'set_vendor_in_session' );
function adjust_cart_item_price( $cart_item_data, $product_id ) {
    $vendor_name = WC()->session->get( 'selected_vendor' );
    $current_user = wp_get_current_user();
    $warehouse_location = get_user_meta( $current_user->ID, 'warehouse_location', true );
    $warehouse_options = get_warehouse_options();

    if ( $vendor_name ) {
        $vendors = get_post_meta( $product_id, 'vendors', true );
        $warehouse_fees = get_option( 'custom_warehouses', array() );

        foreach ( $vendors as $vendor ) {
            if ( $vendor['name'] == $vendor_name && isset( $warehouse_fees[ $vendor['location'] ] ) ) {
                $vendor_price = $vendor['price'];
                $warehouse_fee = $warehouse_fees[ $vendor['location'] ];

                if ( $warehouse_options[ $warehouse_location ] === $vendor['location'] ) {
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
add_filter( 'woocommerce_add_cart_item_data', 'adjust_cart_item_price', 10, 2 );



function set_new_price( $cart_object ) {
    if( is_admin() && ! defined( 'DOING_AJAX' ) )
        return;

    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
        return;

    // Iterate over each item in the cart
    foreach ( $cart_object->get_cart() as $cart_item ) {
        if( isset( $cart_item['new_price'] ) ) {
            $cart_item['data']->set_price( $cart_item['new_price'] );
        }
    }
}
add_action( 'woocommerce_before_calculate_totals', 'set_new_price', 10, 1 );

// function adjust_product_price( $price, $product ) {
//     if ( isset( $_GET['vendor_name'] ) ) {
//         $vendor_name = sanitize_text_field( $_GET['vendor_name'] );
//         $vendors = get_post_meta( $product->get_id(), 'vendors', true );

//         foreach ( $vendors as $vendor  ) {
//             if ( $vendor['name'] == $vendor_name ) {
//                 $vendor_price = $vendor['price'];
//                 return $vendor_price;
//             }
//         }
//     }

//     return $price;
// }

// add_filter( 'woocommerce_product_get_price', 'adjust_product_price', 10, 2 );
// add_filter( 'woocommerce_product_variation_get_price', 'adjust_product_price', 10, 2 ); // If you're using variations


// function adjust_cart_item_price( $cart ) {
//     if ( is_admin() && ! defined( 'DOING_AJAX' ) )
//         return;

//     if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 )
//         return;

//     // Iterate over each item in the cart
//     foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
//         $product = $cart_item['data'];

//         // Check if vendor name is set
//         if ( isset( $_GET['vendor_name'] ) ) {
//             $vendor_name = sanitize_text_field( $_GET['vendor_name'] );
//             $vendors = get_post_meta( $product->get_id(), 'vendors', true );
//             $warehouse_fees = get_option('custom_warehouses', array());

//             foreach ( $vendors as $vendor  ) {
//                 if ( $vendor['name'] == $vendor_name && isset( $warehouse_fees[ $vendor['location'] ] )) {
//                     $vendor_price = $vendor['price'];
//                     $product->set_price($vendor_price);
//                     break;
//                 }
//             }
//         }
//     }
// }
// add_action( 'woocommerce_before_calculate_totals', 'adjust_cart_item_price', 10, 1 );





function get_current_user_warehouse_name() {
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


function display_vendor_data($product_id) {
    // Retrieve the vendor data for the product
    $vendors = get_post_meta($product_id, 'vendors', true);
    $warehouse_fees = get_option('custom_warehouses', array());

    

    echo '<div class="single-product-main">';
    if ($vendors) {
        // Get unique location and format values from vendors
        $locations = array_unique(array_column($vendors, 'location'));
        $formats = array_unique(array_column($vendors, 'format'));

        // Check for location and format filter checkboxes
        $location_filters = isset($_GET['location_filter']) ? $_GET['location_filter'] : $locations;
        $format_filters = isset($_GET['format_filter']) ? $_GET['format_filter'] : $formats;

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

      

        // Add the JavaScript that will automatically submit the form when a checkbox is changed or sort option is modified
        echo '
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <script>
            $(".location-filter, .format-filter, #sort_by").change(function() {
                $("#vendor-filter-form").submit();
            });
        </script>
        ';

        // Filter vendors based on location and format
        $vendors = array_filter($vendors, function($vendor) use ($location_filters, $format_filters) {
            return in_array($vendor['location'], $location_filters) && in_array($vendor['format'], $format_filters);
        });

        // Sort vendors based on price
        usort($vendors, function($a, $b) use ($sort_by) {
            if ($sort_by == 'price_desc') {
                return $b['price'] - $a['price'];
            } else {
                return $a['price'] - $b['price'];
            }
        });
        echo '</div>';

        echo '<div class="vendor-offer-main">';
        echo '<div class="offer-header"> <div class="number-offers">'. count($vendors). ' Offers </div>';

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

           
                if($vendor){
                echo '<div class="offer-container">';
                // echo '<h3>Vendor Data</h3>';
                // echo '<p>Vendor Name: ' . $vendor['name'] . '</p>';
                echo '<div class="offer-left">';
                    echo '<p>Offer Price: </p> <span class="offer-price:"> £' . ($vendor['price'] ) . '</span>';
                    // echo '<p>Vintage: ' . $vendor['vintage'] . '</p>';
                    echo '<p>Availability: </p> <span> ' . $vendor['quantity'] . ' case(s)</span> ';
                
                    echo '<p>Location:  </p> <span>' . $vendor['location'] . '</span>';
                    // echo '<p>Purchase Price: ' . $vendor['purchase'] . '</p>';
                    echo '<p>Warehouse Fee :  </p>  <span> £ '; 
                     if($warehouse_options[$warehouse_location]  ===  $vendor['location']){
                        echo "0 (per case) </span>';";
                     }else{
                        echo  $warehouse_fee. ' (per case) </span>';
                     }
                     
                     
                echo '</div>';

                //Add to Cart button
                echo '<div class="offer-right">';
                echo '<button class="add-to-cart" data-product-id="'.$product_id.'" data-quantity="1" data-price="'.($vendor['price'] + $warehouse_fee).'" data-vendor-name="'.$vendor['name'].'">Make offer</button>';
                echo '<button class="add-to-cart" data-product-id="'.$product_id.'" data-quantity="1" data-price="'.($vendor['price'] + $warehouse_fee).'" data-vendor-name="'.$vendor['name'].'">Add to Cart</button>';
                echo '<select class="quantity">';
                for ($j = 1; $j <= $vendor['quantity']; $j++) {
                    echo '<option value="' . $j . '">' . $j . ' case</option>';
                }
                echo '</select>';
                echo '<p class="total-price">Total Price: £<span>';
                if($warehouse_options[$warehouse_location]  ===  $vendor['location']){
                    echo  ($vendor['price'] * $vendor['quantity']) . '</span></p>';
                 }else{
                    echo  ($vendor['price'] + $warehouse_fee * $vendor['quantity']) . '</span></p>';
                 }
                
                echo '</div>';

            echo '</div>';
            }
            // Show the warehouse fee

        }
        echo '</div>';

        echo '</div>';

        echo '
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $(".quantity").change(function() {
                var quantity = $(this).val();
                var price = $(this).parent().siblings().find(".offer-price").text();
                var totalPrice = quantity * price;

                $(this).parent().siblings().find(".offer-price").text(totalPrice.toFixed(2));
                $(this).parent().siblings().find(".add-to-cart").attr("data-quantity", quantity);
            });

            $(".add-to-cart").click(function() {
                var productId = $(this).data("product-id");
                var quantity = $(this).data("quantity");
                var price = $(this).data("price");
                var vendorName = $(this).data("vendor-name");

                var cartUrl = "?add-to-cart=" + productId + "&quantity=" + quantity + "&price=" + price + "&vendor_name=" + vendorName;

                window.location.href = cartUrl;
            });

            $(".location-filter, .format-filter, #sort_by").change(function() {
                $("#vendor-filter-form").submit();
            });
        });
    </script>
    ';
     
        // Pagination links
       // Pagination links
 // Pagination links
 if ($total_pages > 1) {
    echo '<div class="single-product-pagination">';
    
    $big = 999999999; // need an unlikely integer
    echo paginate_links(array(
        'base' => str_replace($big, '%#%', esc_url(get_pagenum_link($big))),
        'format' => '?paged=%#%',
        'current' => max(1, $current_page),
        'total' => $total_pages,
        'prev_text' => __('« Prev'),
        'next_text' => __('Next »'),
    ));

    echo '</div>';
}

    }
}
function shortcode_vendor_data() {
    // Extract attributes from the shortcode
    global $product;
    $id = $product->get_id();


    if ($id) {
        // Call the function with the product id from the shortcode attributes
        display_vendor_data($id);
    }
}
add_shortcode('vendor_data', 'shortcode_vendor_data');


function display_market_lowerest_price($atts) {
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



function add_vendor_price_button_shortcode($atts) {
    $product_id = get_the_ID();

    $atts = shortcode_atts(
        array(
            'product_id' => $product_id , // Default product ID if not specified in the shortcode
        ),
        $atts
    );

    $product_id = $atts['product_id'];
    $permalink = add_query_arg('product_id', $product_id, get_permalink(get_page_by_path('add-vendor-price')));

    return esc_url($permalink);
}
add_shortcode('add_vendor_price_button', 'add_vendor_price_button_shortcode');

// Custom function to display product attributes
function display_product_attributes($atts) {
    // Extract the product ID from the shortcode attributes
    $atts = shortcode_atts(
        array(
            'product_id' => 0,
        ),
        $atts
    );

    // Get the product ID
    $product_id = intval($atts['product_id']);
    $product = wc_get_product($product_id);

    // Check if the product object is valid
    if ($product) {
        // Get the product attributes
        $attributes = $product->get_attributes();

        // Check if there are attributes
        if (!empty($attributes)) {
            // Filter and display specific attributes
            $filtered_attributes = array_intersect_key($attributes, array_flip(['pa_color', 'pa_region', 'pa_drinking_windows']));
            
            // Output the attributes as a list
            echo '<ul class="attributes-add-form">';
            foreach ($filtered_attributes as $attribute) {
                // Get attribute terms using ids returned by get_options()
                $terms = array_map(function($term_id) {
                    $term = get_term($term_id);
                    return $term ? $term->name : '';  // if the term exists, return its name
                }, $attribute->get_options());
                $attribute_name = str_replace('pa_', '', $attribute->get_name());
                $attribute_name = ucwords(strtolower($attribute_name)); // Convert to sentence case
                
                echo '<li class="attribute-add-form-item"><b>' . $attribute_name . ':</b> '.
                    '<b>' . esc_html(implode(', ', $terms)) .
                    '</b></li>';
            }
            echo '</ul>';
        }
    }
}
add_shortcode('product_attributes', 'display_product_attributes');

