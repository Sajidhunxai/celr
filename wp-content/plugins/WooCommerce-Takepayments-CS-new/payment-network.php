<?php
/*
Plugin Name: Takepayments CS Payment Gateway
Description: Provides the Takepayments CS Payment Gateway for WooCommerce
Plugin URI: https://producthub.takepayments.com/
Description: Provides the Takepayments gateway for CS accounts
Version: 2.0.7
Provider URI: http://www.Takepayments.com/
License: GPL2
*/

/**
 * Initialise WC Payment Network Gateway
 **/
add_action('plugins_loaded', 'init_wc_payment_network', 0);

/**
 * Initialise WC PaymentNetwork Gateway
 **/
if(function_exists('setup_module_database_tables')) {
    register_activation_hook( __FILE__, 'setup_module_database_tables' );
}

/**
 * Delete PaymentNetwork Gateway
 **/
if(function_exists('delete_plugin_database_table')) {
    register_uninstall_hook(__FILE__, 'delete_plugin_database_table');
}


function init_wc_payment_network() {

    if (!class_exists('WC_Payment_Gateway')) {
        return;
    }

    add_filter('plugin_action_links', 'add_wc_takepayments_action_plugin', 10, 5);

    include('includes/class-wc-takepayments-gateway.php');

    require_once __DIR__.'/vendor/autoload.php';

    add_filter('woocommerce_payment_gateways', 'add_takepayments_payment_gateway' );

}

function add_wc_takepayments_action_plugin($actions, $plugin_file)
{
    static $plugin;

    if (!isset($plugin))
    {
        $plugin = plugin_basename(__FILE__);
    }

    if ($plugin == $plugin_file)
    {
        $configs = include(dirname(__FILE__) . '/config.php');

        $section = str_replace(' ', '', strtolower($configs['gateway_title']));

        $actions = array_merge(array('settings' => '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section='.$section) . '">' . __('Settings', 'General') . '</a>'), $actions);
    }

    return $actions;
}

function add_takepayments_payment_gateway($methods) {
    $methods[] = 'WC_Payment_Network';
    return $methods;
}


function setup_module_database_tables() {
    $module_prefix = 'takepayments_';
    global $wpdb;
    global $jal_db_version;

    //Wallet table name.
    $wallet_table_name = $wpdb->prefix . 'woocommerce_' . $module_prefix . 'wallets';

    if ($wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $wallet_table_name)) ===  null) {
        $table_name = $wallet_table_name;

        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
			id BIGINT(9) NOT NULL AUTO_INCREMENT,
			merchants_id INT NOT NULL,
			users_id BIGINT NOT NULL,
			wallets_id BIGINT NOT NULL,
			PRIMARY KEY  (id)
		) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        add_option('jal_db_version', $jal_db_version);
    }
}

function delete_plugin_database_table() {
    $module_prefix = 'takepayments_';
    global $wpdb;
    $wpdb->show_errors();
    $table_name = $wpdb->prefix . 'woocommerce_' . $module_prefix . 'wallets';
    $sql = "DROP TABLE IF EXISTS $table_name";
    $wpdb->query($sql);
    //delete_option("my_plugin_db_version");
    //error_log('Logging SQL table drop');
}
