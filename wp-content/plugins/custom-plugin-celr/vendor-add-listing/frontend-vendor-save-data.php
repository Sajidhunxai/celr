<?php
// Save form data to product


add_action('init', 'save_vendor_data_frontend');
function save_vendor_data_frontend()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_vendor_form'])) {
        if (!wp_verify_nonce($_POST['vendor_meta_nonce'], basename(__FILE__))) {
            wp_die('Security check failed.');
        }

        $product_id = intval($_POST['product_id']);

        $existing_vendors = get_post_meta($product_id, 'vendors', true); // Get existing vendors

        // Check if the current user is a vendor
        $current_user = wp_get_current_user();
        $is_vendor = in_array('vendor', $current_user->roles);

        if ($is_vendor) {
            // Check if the current vendor already has content for this product
            $current_vendor_username = $current_user->user_login;
            if (!empty($existing_vendors)) {
                foreach ($existing_vendors as $existing_vendor) {
                    if ($existing_vendor['name'] === $current_vendor_username) {
                        // Vendor already has content for this product
                        // You can display a message or prevent them from adding another content
                        echo '<script>alert("You have already added content for this product!"); window.history.back();</script>';
                        exit();
                    }
                }
            }
        }

        $vendors = $existing_vendors ? $existing_vendors : array();
        $names = !empty($_POST['vendor_name']) ? array_map('sanitize_text_field', $_POST['vendor_name']) : array();
        $prices = !empty($_POST['vendor_price']) ? array_map('sanitize_text_field', $_POST['vendor_price']) : array();
        $locations = !empty($_POST['vendor_location']) ? array_map('sanitize_text_field', $_POST['vendor_location']) : array();
        $quantity = !empty($_POST['vendor_quantity']) ? array_map('sanitize_text_field', $_POST['vendor_quantity']) : array();
        $vintage = !empty($_POST['vendor_vintage']) ? array_map('sanitize_text_field', $_POST['vendor_vintage']) : array();
        $format = !empty($_POST['vendor_format']) ? array_map('sanitize_text_field', $_POST['vendor_format']) : array();
        $purchase = !empty($_POST['vendor_purchase']) ? array_map('sanitize_text_field', $_POST['vendor_purchase']) : array();
        $tags = !empty($_POST['vendor_tags']) ? array_map('sanitize_text_field', $_POST['vendor_tags']) : array();


        for ($i = 0; $i < count($names); $i++) {
            if (!empty($names[$i]) && !empty($prices[$i]) && !empty($locations[$i]) && !empty($quantity[$i]) && !empty($vintage[$i])) {
                $vendor_tags = isset($tags[$i]) ? $tags[$i] : array(); // Retrieve the selected tags
                $vendors[] = array(
                    'name' =>  $current_user->user_login,
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
?>