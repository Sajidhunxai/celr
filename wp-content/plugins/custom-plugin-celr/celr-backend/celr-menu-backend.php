<?php
function add_warehouse_page() {
    add_menu_page(
        'Warehouse Settings',
        'Celr Manage',
        'manage_options',
        'custom_plugin_dashboard_celr',
        'custom_plugin_dashboard_page',
        'dashicons-store', // Icon for the menu item
        7 // Menu position
    );
    
    // Add sub-menu items
    add_submenu_page(
        'custom_plugin_dashboard_celr',
        'View Offers',
        'View Offers',
        'manage_options',
        'view-offers',
        'display_offer_page'
    );
    add_submenu_page(
        'custom_plugin_dashboard_celr',
        'Warehouse',
        'Warehouse',
        'manage_options',
        'warehouse-settings',
        'render_warehouse_page'

    );

}

add_action( 'admin_menu', 'add_warehouse_page' );


?>