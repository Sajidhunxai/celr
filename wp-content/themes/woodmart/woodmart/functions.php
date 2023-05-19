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
    ?>
    <div id="vendor-repeater">
        <?php if ($vendors) : ?>
            <?php foreach ($vendors as $vendor) : ?>
                <div class="vendor-row">
                    <input type="text" name="vendor_name[]" value="<?php echo esc_attr($vendor['name']); ?>" placeholder="Vendor Name" />
                    <input type="text" name="vendor_price[]" value="<?php echo esc_attr($vendor['price']); ?>" placeholder="Vendor Price" />
                    <input type="text" name="vendor_color[]" value="<?php echo esc_attr($vendor['color']); ?>" placeholder="Vendor Color" />
                    <button class="remove-vendor">Remove</button>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="vendor-row">
                <input type="text" name="vendor_name[]" placeholder="Vendor Name" />
                <input type="text" name="vendor_price[]" placeholder="Vendor Price" />
                <input type="text" name="vendor_color[]" placeholder="Vendor Color" />
                <button class="remove-vendor">Remove</button>
            </div>
        <?php endif; ?>
        <button id="add-vendor">Add Vendor</button>
    </div>
    <script>
        jQuery(function($) {
            $('#add-vendor').on('click', function(e) {
                e.preventDefault();
                var row = '<div class="vendor-row">' +
                    '<input type="text" name="vendor_name[]" placeholder="Vendor Name" />' +
                    '<input type="text" name="vendor_price[]" placeholder="Vendor Price" />' +
                    '<input type="text" name="vendor_color[]" placeholder="Vendor Color" />' +
                    '<button class="remove-vendor">Remove</button>' +
                    '</div>';
                $('#vendor-repeater').append(row);
            });

            $(document).on('click', '.remove-vendor', function(e) {
                e.preventDefault();
                $(this).closest('.vendor-row').remove();
            });
        });
    </script>
    <?php
}

// Save the repeater field data
function save_vendor_data_frontend() {
    if (isset($_POST['submit_vendor_form'])) {
        if (!wp_verify_nonce($_POST['vendor_form_nonce'], basename(__FILE__))) {
            wp_die('Security check failed.');
        }

        $product_id = intval($_POST['product_id']);

        $existing_vendors = get_post_meta($product_id, 'vendors', true); // Get existing vendors

        $vendors = array();
        $names = array_map('sanitize_text_field', $_POST['vendor_name']);
        $prices = array_map('sanitize_text_field', $_POST['vendor_price']);
        $colors = array_map('sanitize_text_field', $_POST['vendor_color']);

        for ($i = 0; $i < count($names); $i++) {
            if (!empty($names[$i]) && !empty($prices[$i]) && !empty($colors[$i])) {
                $vendors[] = array(
                    'name' => $names[$i],
                    'price' => $prices[$i],
                    'color' => $colors[$i],
                );
            }
        }

        // Merge existing vendors with new vendors
        if (is_array($existing_vendors)) {
            $vendors = array_merge($existing_vendors, $vendors);
        }

        update_post_meta($product_id, 'vendors', $vendors);
    }
}
add_action('init', 'save_vendor_data_frontend');






// Display the vendor fields on the product page
function display_vendors() {
    global $post;
    $vendors = get_post_meta($post->ID, 'vendors', true);

    if ($vendors) {
        echo '<ul>';
        foreach ($vendors as $vendor) {
            echo '<li>Vendor: ' . esc_html($vendor['name']) . ', Price: ' . esc_html($vendor['price']) . ', Color: ' . esc_html($vendor['color']) . '</li>';
        }
        echo '</ul>';
    }
}


// Register the shortcode with the product ID parameter
function vendor_form_shortcode($atts) {
    // Extract the product ID from the shortcode attributes
    $atts = shortcode_atts(array(
        'product_id' => 0,
    ), $atts);

    // Get the product ID
    $product_id = intval($atts['product_id']);

    ob_start();
    ?>
    <form method="post">
        <?php wp_nonce_field(basename(__FILE__), 'vendor_form_nonce'); ?>
        <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>" />

        <div id="vendor-repeater">
            <div class="vendor-row">
                <input type="text" name="vendor_name[]" placeholder="Vendor Name" />
                <input type="text" name="vendor_price[]" placeholder="Vendor Price" />
                <input type="text" name="vendor_color[]" placeholder="Vendor Color" />
                <button class="remove-vendor">Remove</button>
            </div>
        </div>

        <button id="add-vendor">Add Vendor</button>
        <input type="submit" name="submit_vendor_form" value="Submit" />
    </form>

    <script>
        jQuery(document).ready(function($) {
            $('#add-vendor').on('click', function(e) {
                e.preventDefault();
                var row = '<div class="vendor-row">' +
                    '<input type="text" name="vendor_name[]" placeholder="Vendor Name" />' +
                    '<input type="text" name="vendor_price[]" placeholder="Vendor Price" />' +
                    '<input type="text" name="vendor_color[]" placeholder="Vendor Color" />' +
                    '<button class="remove-vendor">Remove</button>' +
                    '</div>';
                $('#vendor-repeater').append(row);
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
add_shortcode('vendor_form', 'vendor_form_shortcode');
