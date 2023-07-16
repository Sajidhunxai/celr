<?php
/*
Plugin Name: Custom Plugin Celr
Description: Custom Plugin Based on Requirements
Version: 1.0
Author: Sajid Hunxai
*/


// buyer request
require_once('buyer-request/buyer-account.php');
require_once('buyer-request/buyer-offer.php');
require_once('buyer-request/buyer-request-database-create.php');

// single product page
require_once('single-product-page/components/display-product-attributes.php');
require_once('single-product-page/components/display-market-price.php');
require_once('single-product-page/components/display-vendor.php');
require_once('single-product-page/components/vintage-filter.php');
require_once('single-product-page/required-functions.php');

// adding search vendor list item
require_once('vendor-add-listing/vendor-meta-box.php');
require_once('vendor-add-listing/backend-display-vendor-data.php');
require_once('vendor-add-listing/backend-remove-vendor-data.php');
require_once('vendor-add-listing/backend-save-vendor-data.php');
require_once('vendor-add-listing/frontend-vendor-listing-form.php');
require_once('vendor-add-listing/frontend-product-search.php');
require_once('vendor-add-listing/frontend-vendor-save-data.php');

// dashboard files
require_once('dashboard/current-user-listing.php');
require_once('dashboard/current-user-sales.php');
require_once('dashboard/current-user-total-sales.php');
require_once('dashboard/current-user-listing-quantity.php');
require_once('dashboard/current-user-listing-returns.php');
require_once('dashboard/current-user-listing-sold-quantity.php');
require_once('dashboard/current-user-statistics.php');

// Backend Celr 
require_once('celr-backend/celr-menu-backend.php');
require_once('celr-backend/celr-product-location.php');
require_once('celr-backend/warehouse-page-backend.php');
require_once('celr-backend/celr-backend-dashboard-page.php');

//mailing
require_once('celr-mailing/celr-checkout-mail.php');



function enqueue_product_search_scripts() {
    wp_enqueue_script(
        'product-search',
        plugin_dir_url(__FILE__) . 'assets/js/addListingProductSearch.js',
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

function add_theme_scripts() {
    wp_enqueue_style( 'style', get_stylesheet_uri() );
    wp_enqueue_style( 'slider', plugin_dir_url(__FILE__) . 'assets/css/custom-plugin-celr.css', array(), '1.0', 'all' );
    
    wp_enqueue_script( 'script', plugin_dir_url(__FILE__) . 'assets/js/custom-plugin-celr.js', array( 'jquery' ), 1.0, true );
    wp_localize_script('script', 'ajax_object', array( // Add this line
        'ajax_url' => admin_url('admin-ajax.php'),
    ));

    if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
        wp_enqueue_script( 'comment-reply' );
    }
}
add_action( 'wp_enqueue_scripts', 'add_theme_scripts' );

require_once(ABSPATH . 'wp-admin/includes/upgrade.php');


