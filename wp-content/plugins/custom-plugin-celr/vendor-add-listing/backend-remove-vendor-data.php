<?php
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

?>