<?php
function add_warehouse_page() {
    add_menu_page(
        'Warehouse Settings',
        'Custom Plugin',
        'manage_options',
        'custom_plugin_dashboard',
        'custom_plugin_dashboard_page'
    );
    
    // Add sub-menu items
    add_submenu_page(
        'custom_plugin_dashboard',
        'View Offers',
        'View Offers',
        'manage_options',
        'view-offers',
        'display_offer_page'
    );
    add_submenu_page(
        'custom_plugin_dashboard',
        'warehouse ',
        'warehouse',
        'manage_options',
        'warehouse-settings',
        'render_warehouse_page'

    );

}

add_action( 'admin_menu', 'add_warehouse_page' );


?>