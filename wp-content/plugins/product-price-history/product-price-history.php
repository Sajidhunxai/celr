<?php

/**
 * Plugin Name:       Product Price History
 * Description:       Price history tracker for WooCommerce products.
 * Version:           2.1.6
 * Author:            Devnet
 * Author URI:        https://devnet.hr
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       product-price-history
 * Domain Path:       /languages
 * WC tested up to:   7.8
 *
 */
use  Devnet\PPH\Includes\Activator ;
use  Devnet\PPH\Includes\Deactivator ;
use  Devnet\PPH\Includes\PPH_PLUGIN ;
use  Devnet\PPH\Includes\Uninstaller ;
// If this file is called directly, abort.
if ( !defined( 'WPINC' ) ) {
    exit;
}

if ( function_exists( 'pph_fs' ) ) {
    pph_fs()->set_basename( false, __FILE__ );
} else {
    
    if ( !function_exists( 'pph_fs' ) ) {
        // Create a helper function for easy SDK access.
        function pph_fs()
        {
            global  $pph_fs ;
            
            if ( !isset( $pph_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/fs/freemius/start.php';
                $pph_fs = fs_dynamic_init( [
                    'id'             => '12131',
                    'slug'           => 'product-price-history',
                    'premium_slug'   => 'product-price-history-pro',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_810eeff02bc575018794400239eb0',
                    'is_premium'     => false,
                    'has_addons'     => false,
                    'has_paid_plans' => true,
                    'trial'          => [
                    'days'               => 7,
                    'is_require_payment' => true,
                ],
                    'menu'           => [
                    'slug'    => 'product-price-history',
                    'contact' => false,
                ],
                    'is_live'        => true,
                ] );
            }
            
            return $pph_fs;
        }
        
        // Init Freemius.
        pph_fs();
        // Signal that SDK was initiated.
        do_action( 'pph_fs_loaded' );
    }
    
    /*
     * Show the contact submenu item only when the user have a valid non-expired license.
     *
     * @param $is_visible The filtered value. Whether the submenu item should be visible or not.
     * @param $menu_id    The ID of the submenu item.
     *
     * @return bool If true, the menu item should be visible.
     */
    if ( !function_exists( 'pph_is_submenu_visible' ) ) {
        function pph_is_submenu_visible( $is_visible, $menu_id )
        {
            if ( 'contact' != $menu_id ) {
                return $is_visible;
            }
            return pph_fs()->can_use_premium_code();
        }
    
    }
    /*
     * TODO: do uninstall logic.
     */
    if ( !function_exists( 'pph_fs_uninstall_cleanup' ) ) {
        function pph_fs_uninstall_cleanup()
        {
            require_once plugin_dir_path( __FILE__ ) . 'includes/uninstaller.php';
            Uninstaller::cleanup();
        }
    
    }
    /*
     * Run Freemius actions and filters.
     */
    
    if ( function_exists( 'pph_fs' ) ) {
        pph_fs()->add_filter(
            'is_submenu_visible',
            'pph_is_submenu_visible',
            10,
            2
        );
        pph_fs()->add_action( 'after_uninstall', 'pph_fs_uninstall_cleanup' );
    }
    
    /*
     * Currently plugin version.
     */
    define( 'PRODUCT_PRICE_HISTORY_VERSION', '2.1.6' );
    define( 'DEVNET_PPH_NAME', 'Product Price History' );
    define( 'DEVNET_PPH_SLUG', plugin_basename( __FILE__ ) );
    define( 'DEVNET_PPH_OPTIONS', [
        'general'      => get_option( 'devnet_pph_general' ),
        'chart'        => get_option( 'devnet_pph_chart' ),
        'lowest_price' => get_option( 'devnet_pph_lowest_price' ),
        'price_alerts' => get_option( 'devnet_pph_price_alerts' ),
    ] );
    /**
     * The code that runs during plugin activation.
     * This action is documented in includes/activator.php
     */
    function pph_activate_product_price_history()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/activator.php';
        Activator::activate();
    }
    
    /**
     * The code that runs during plugin deactivation.
     * This action is documented in includes/deactivator.php
     */
    function pph_deactivate_product_price_history()
    {
        require_once plugin_dir_path( __FILE__ ) . 'includes/deactivator.php';
        Deactivator::deactivate();
    }
    
    register_activation_hook( __FILE__, 'pph_activate_product_price_history' );
    register_deactivation_hook( __FILE__, 'pph_deactivate_product_price_history' );
    /**
     * The core plugin class that is used to define internationalization,
     * admin-specific hooks, and public-facing site hooks.
     */
    require plugin_dir_path( __FILE__ ) . 'includes/plugin.php';
    /**
     * Begins execution of the plugin.
     *
     * Since everything within the plugin is registered via hooks,
     * then kicking off the plugin from this point in the file does
     * not affect the page life cycle.
     *
     * @since    1.0.0
     */
    function run_devnet_pph()
    {
        $plugin = new PPH_Plugin();
        $plugin->run();
    }
    
    add_action( 'plugins_loaded', function () {
        // Go ahead only if WooCommerce is activated
        
        if ( is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
            run_devnet_pph();
        } else {
            add_action( 'admin_notices', function () {
                $class = 'notice notice-error';
                $message = esc_html__( 'The “Product Price History” plugin cannot run without WooCommerce. Please install and activate WooCommerce plugin.', 'product-price-history' );
                printf( '<div class="%1$s"><p>%2$s</p></div>', esc_attr( $class ), esc_html( $message ) );
            } );
            return;
        }
    
    } );
}
