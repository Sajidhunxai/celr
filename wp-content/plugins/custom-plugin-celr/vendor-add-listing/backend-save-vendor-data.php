<?php
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

?>