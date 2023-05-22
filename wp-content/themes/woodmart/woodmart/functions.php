<?php
/**
 *
 * The framework's functions and definitions
 */

define( 'WOODMART_THEME_DIR', get_template_directory_uri() );
define( 'WOODMART_THEMEROOT', get_template_directory() );
define( 'WOODMART_IMAGES', WOODMART_THEME_DIR . '/images' );
define( 'WOODMART_SCRIPTS', WOODMART_THEME_DIR . '/js' );
define( 'WOODMART_STYLES', WOODMART_THEME_DIR . '/css' );
define( 'WOODMART_FRAMEWORK', '/inc' );
define( 'WOODMART_DUMMY', WOODMART_THEME_DIR . '/inc/dummy-content' );
define( 'WOODMART_CLASSES', WOODMART_THEMEROOT . '/inc/classes' );
define( 'WOODMART_CONFIGS', WOODMART_THEMEROOT . '/inc/configs' );
define( 'WOODMART_HEADER_BUILDER', WOODMART_THEME_DIR . '/inc/header-builder' );
define( 'WOODMART_ASSETS', WOODMART_THEME_DIR . '/inc/admin/assets' );
define( 'WOODMART_ASSETS_IMAGES', WOODMART_ASSETS . '/images' );
define( 'WOODMART_API_URL', 'https://xtemos.com/licenses/api/' );
define( 'WOODMART_DEMO_URL', 'https://woodmart.xtemos.com/' );
define( 'WOODMART_PLUGINS_URL', WOODMART_DEMO_URL . 'plugins/' );
define( 'WOODMART_DUMMY_URL', WOODMART_DEMO_URL . 'dummy-content-new/' );
define( 'WOODMART_TOOLTIP_URL', WOODMART_DEMO_URL . 'theme-settings-tooltips/' );
define( 'WOODMART_SLUG', 'woodmart' );
define( 'WOODMART_CORE_VERSION', '1.0.38' );
define( 'WOODMART_WPB_CSS_VERSION', '1.0.2' );

if ( ! function_exists( 'woodmart_load_classes' ) ) {
	function woodmart_load_classes() {
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

		foreach ( $classes as $class ) {
			require WOODMART_CLASSES . DIRECTORY_SEPARATOR . $class;
		}
	}
}

woodmart_load_classes();

new WOODMART_Theme();

define( 'WOODMART_VERSION', woodmart_get_theme_info( 'Version' ) );

// Register a custom meta box
function vendor_meta_box() {
    add_meta_box('vendor_meta', 'Vendor Information', 'render_vendor_meta_box', 'product', 'normal', 'high');
}
add_action('add_meta_boxes', 'vendor_meta_box');

// Render the meta box content
// Function to render the vendor meta box
function render_vendor_meta_box($post) {
    wp_nonce_field(basename(__FILE__), 'vendor_meta_nonce');
    $vendors = get_post_meta($post->ID, 'vendors', true);
    $current_user = wp_get_current_user();
    $user_name = $current_user->display_name;

    // Get the options for the attribute 'location'
    $location_attr = 'pa_location'; // Replace with the slug of the 'location' attribute
    $terms_locations = get_terms(array(
        'taxonomy' => $location_attr,
        'hide_empty' => false,
    ));
    $location_options = array();
    foreach ($terms_locations as $term) {
        $location_options[] = $term->name;
    }

    // Get the options for the attribute 'quantity' dynamically based on the current post
    $quantity_attr = 'pa_quantity'; // Replace with the slug of the 'quantity' attribute
    $terms_quantities = get_terms(array(
        'taxonomy' => $quantity_attr,
        'hide_empty' => false,
    ));
    $quantity_options = array();
    foreach ($terms_quantities as $term) {
        $quantity_options[] = $term->name;
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
                    '<select name="vendor_tags[]">' +
                    '<option value="">Select Tag</option>' +
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
function save_vendor_data($post_id) {
    if (!isset($_POST['vendor_meta_nonce']) || !wp_verify_nonce($_POST['vendor_meta_nonce'], basename(__FILE__))) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    if (!isset($_POST['vendor_name']) || !isset($_POST['vendor_price']) || !isset($_POST['vendor_location']) || !isset($_POST['vendor_quantity']) || !isset($_POST['vendor_tags'])) {
        return;
    }

    $vendor_names = $_POST['vendor_name'];
    $vendor_prices = $_POST['vendor_price'];
    $vendor_locations = $_POST['vendor_location'];
    $vendor_quantities = $_POST['vendor_quantity'];
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
            'tags' => sanitize_text_field($vendor_tags[$i]),
        );
        $vendors[] = $vendor;
    }

    update_post_meta($post_id, 'vendors', $vendors);
}
add_action('save_post', 'save_vendor_data');

// AJAX callback to remove vendor
function remove_vendor() {
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
function display_vendors() {
    global $post;
    $vendors = get_post_meta($post->ID, 'vendors', true);

    if ($vendors) {
        echo '<ul>';
        foreach ($vendors as $vendor) {
            echo '<li>Vendor: ' . esc_html($vendor['name']) . ', Price: ' . esc_html($vendor['price']) . ', Location: ' . esc_html($vendor['location']) .', Quantity: ' . esc_html($vendor['quantity']);

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
function vendor_form_shortcode($atts) {
    if (is_user_logged_in()) {
        // User is logged in, show the form
        // Extract the product ID from the shortcode attributes
        $atts = shortcode_atts(array(
            'product_id' => 0,
        ), $atts);

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
                    <img src="<?php echo get_site_url() .  '/wp-content/uploads/2023/05/image-44.png' ; ?> " alt="Default Image" width="400" />
                <?php endif; ?>

                </div>
                <div class="column">
                    <h3><?php echo esc_html($post_title); ?></h3>
                    <div id="vendor-repeater">
                    <form method="post">
                        <?php wp_nonce_field(basename(__FILE__), 'vendor_meta_nonce'); ?>
                        <input
                        type="hidden"
                        name="product_id"
                        value="<?php echo esc_attr($product_id); ?>"
                        />

                        <div class="vendor-row">
                        <?php
                                $current_user = wp_get_current_user();
                                $user_name = $current_user->display_name; ?>
                        <input
                            type="text"
                            name="vendor_name[]"
                            placeholder="Vendor Name"
                            value="<?php echo esc_attr($user_name); ?>"
                            readonly
                        />
                        <input type="text" name="vendor_price[]" placeholder="Vendor Price" />
                        <select name="vendor_location[]">
                            <?php
                                    $attribute_name = 'pa_location'; // Replace with your actual attribute slug
                                    $terms = get_terms(array(
                                        'taxonomy' =>
                            $attribute_name, 'hide_empty' => false, )); foreach ($terms as
                            $term) { echo '
                            <option value="' . esc_attr($term->name) . '">
                            ' . esc_html($term->name) . '
                            </option>
                            '; } ?>
                        </select>
                        <select name="vendor_quantity[]">
                            <?php
                                    $attribute_quantity = 'pa_quantity'; // Replace with your actual attribute slug
                                    $terms_quantity = get_terms(array(
                                        'taxonomy' =>
                            $attribute_quantity, 'hide_empty' => false, )); foreach
                            ($terms_quantity as $term_quantity) { echo '
                            <option value="' . esc_attr($term_quantity->name) . '">
                            ' . esc_html($term_quantity->name) . '
                            </option>
                            '; } ?>
                        </select>

                        <!-- Display product tags -->
                        <select name="vendor_tags[]" class="product-tags">
                            <option value="">Select Tag</option>
                        </select>

                        <button class="remove-vendor">Remove</button>
                        </div>

                        <button id="add-vendor">Add Vendor</button>
                        <input type="submit" name="submit_vendor_form" value="Submit" />
                    </form>
                    </div>
                </div>
                <div class="column">
                    <div class="market-price-box" style="display:flex">
                        <div class="box">
                            <h3>Market Price</h3>
                            <div class="price"><?php echo wc_price($regular_price); ?></div>
                        </div>

                        <div class="box">
                        <h3>Lowest Price</h3>
                    <div class="price">
                    <?php 
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
                    
                    ?>
                    </div>
                    </div>
                    </div>
                </div>
            </div>

     
        <script>
            var productTags = <?php echo json_encode($product_tag_names); ?>;

            jQuery(document).ready(function($) {
                // Populate product tags select field
                var productTagsSelect = $('.product-tags');
                $.each(productTags, function(index, tag) {
                    productTagsSelect.append($('<option>', {
                        value: tag,
                        text: tag
                    }));
                });

                $('#add-vendor').on('click', function(e) {
                    e.preventDefault();
                    var row = '<div class="vendor-row">' +
                        '<input type="text" name="vendor_name[]" placeholder="Vendor Name" value="<?php echo esc_attr($user_name); ?>" readonly />' +
                        '<input type="text" name="vendor_price[]" placeholder="Vendor Price" />' +
                        '<select name="vendor_location[]">' +
                        '<?php
                        foreach ($terms as $term) {
                            echo '<option value="' . esc_attr($term->name) . '">' . esc_html($term->name) . '</option>';
                        }
                        ?>' +
                        '</select>' +
                        '<select name="vendor_quantity[]">' +
                        '<?php
                        foreach ($terms_quantity as $term_quantity) {
                            echo '<option value="' . esc_attr($term_quantity->name) . '">' . esc_html($term_quantity->name) . '</option>';
                        }
                        ?>' +
                        '</select>' +
                        '<select name="vendor_tags[]" class="product-tags">' +
                        '<option value="">Select Tag</option>' +
                        '</select>' +
                        '<button class="remove-vendor">Remove</button>' +
                        '</div>';
                    $('#vendor-repeater').append(row);

                    // Repopulate product tags select field
                    var productTagsSelect = $('.product-tags');
                    $.each(productTags, function(index, tag) {
                        productTagsSelect.append($('<option>', {
                            value: tag,
                            text: tag
                        }));
                    });
                });

                $(document).on('click', '.remove-vendor', function(e) {
                    e.preventDefault();
                    $(this).closest('.vendor-row').remove();
                });
            });
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
function save_vendor_data_frontend() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_vendor_form'])) {
        if (!wp_verify_nonce($_POST['vendor_meta_nonce'], basename(__FILE__))) {
            wp_die('Security check failed.');
        }

        $product_id = intval($_POST['product_id']);

        $existing_vendors = get_post_meta($product_id, 'vendors', true); // Get existing vendors

        // Check if there are existing vendors for the current product
        if (!empty($existing_vendors)) {
            // Output JavaScript alert and prevent saving new post
            echo '<script>alert("Vendor data already exists for this product. Please update the existing data!"); window.history.back();</script>';
            exit();
        }

        $vendors = array();
        $names = !empty($_POST['vendor_name']) ? array_map('sanitize_text_field', $_POST['vendor_name']) : array();
        $prices = !empty($_POST['vendor_price']) ? array_map('sanitize_text_field', $_POST['vendor_price']) : array();
        $locations = !empty($_POST['vendor_location']) ? array_map('sanitize_text_field', $_POST['vendor_location']) : array();
        $quantity = !empty($_POST['vendor_quantity']) ? array_map('sanitize_text_field', $_POST['vendor_quantity']) : array();
        $tags = !empty($_POST['vendor_tags']) ? array_map('sanitize_text_field', $_POST['vendor_tags']) : array();

        $current_user = wp_get_current_user();
        $user_name = $current_user->display_name;

        for ($i = 0; $i < count($names); $i++) {
            if (!empty($names[$i]) && !empty($prices[$i]) && !empty($locations[$i]) && !empty($quantity[$i])) {
                $vendor_tags = isset($tags[$i]) ? $tags[$i] : array(); // Retrieve the selected tags
                $vendors[] = array(
                    'name' => ($names[$i] === $user_name) ? $user_name : $names[$i],
                    'price' => $prices[$i],
                    'location' => $locations[$i],
                    'quantity' => $quantity[$i],
                    'tags' => $vendor_tags,
                );
            }
        }

        // Update post meta
        $result = update_post_meta($product_id, 'vendors', $vendors);

        if ($result) {
            // Output JavaScript alert
            echo '<script>alert("Vendor data saved!"); window.location.href = "'.home_url().'";</script>';
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



//product search
function product_search_shortcode() {
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
function product_search_ajax_handler() {
    if (isset($_GET['product_search'])) {
        $product_search = sanitize_text_field($_GET['product_search']);
        $products = new WP_Query(array(
            'post_type' => 'product',
            's' => $product_search,
            'posts_per_page' => -1,
        ));

        ob_start();

        if ($products->have_posts()) {
            while ($products->have_posts()) : $products->the_post();
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

//product search Enqueue js and jquery
function enqueue_product_search_scripts() {
    wp_enqueue_script(
        'product-search',
        get_template_directory_uri() . '/custom.js', // Replace with the actual path to your JavaScript file
        array('jquery'),
        '1.0',
        true
    );

    // Localize the AJAX URL
    wp_localize_script(
        'product-search',
        'productSearchAjax',
        array('ajaxurl' => admin_url('admin-ajax.php'))
    );
}
add_action('wp_enqueue_scripts', 'enqueue_product_search_scripts');

