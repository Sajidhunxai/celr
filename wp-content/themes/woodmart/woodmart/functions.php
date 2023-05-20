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
    $(this).closest('.vendor-row').remove();
  });
    });

    // Submit form on button click
    jQuery(document).ready(function($) {
    $('button[name="submit_vendor_form"]').on('click', function(e) {
        e.preventDefault();
        $('form#post').submit();
    });
    });


      
    </script>
    <?php
}

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

    ob_start();
    ?>

    <div id="vendor-repeater">
        <form method="post">
            <?php wp_nonce_field(basename(__FILE__), 'vendor_meta_nonce'); ?>
            <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>" />

            <div class="vendor-row">
                <?php
                $current_user = wp_get_current_user();
                $user_name = $current_user->display_name;
                ?>
                <input type="text" name="vendor_name[]" placeholder="Vendor Name" value="<?php echo esc_attr($user_name); ?>" readonly />
                <input type="text" name="vendor_price[]" placeholder="Vendor Price" />
                <select name="vendor_location[]">
                    <?php
                    $attribute_name = 'pa_location'; // Replace with your actual attribute slug
                    $terms = get_terms(array(
                        'taxonomy' => $attribute_name,
                        'hide_empty' => false,
                    ));
                    foreach ($terms as $term) {
                        echo '<option value="' . esc_attr($term->name) . '">' . esc_html($term->name) . '</option>';
                    }
                    ?>
                </select>
                <select name="vendor_quantity[]">
                    <?php
                    $attribute_quantity = 'pa_quantity'; // Replace with your actual attribute slug
                    $terms_quantity = get_terms(array(
                        'taxonomy' => $attribute_quantity,
                        'hide_empty' => false,
                    ));
                    foreach ($terms_quantity as $term_quantity) {
                        echo '<option value="' . esc_attr($term_quantity->name) . '">' . esc_html($term_quantity->name) . '</option>';
                    }
                    ?>
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
}

// Save form data
// Save form data
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

