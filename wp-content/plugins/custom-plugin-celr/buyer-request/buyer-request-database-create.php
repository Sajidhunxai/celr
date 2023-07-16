<?php 
function create_offer_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'offer_data';

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        user_id mediumint(9) NOT NULL,
        product_id mediumint(9) NOT NULL,
        vendor_name varchar(255) NOT NULL,
        offer_price decimal(10,2) NOT NULL,
        vendor_quantity mediumint(9) NOT NULL,
        vendor_location varchar(255) NOT NULL,
        vendor_transfer_fee decimal(10,2) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    dbDelta($sql);
}

// Run the function when the plugin is activated
register_activation_hook(__FILE__, 'create_offer_table');

function delete_offer_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'offer_data';

    $wpdb->query("DROP TABLE IF EXISTS $table_name");
}
register_deactivation_hook(__FILE__, 'delete_offer_table');

?>