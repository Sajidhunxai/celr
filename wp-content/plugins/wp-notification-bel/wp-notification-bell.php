<?php

/**
 * Plugin Name: WP Notification Bell
 * Plugin URI: https://wpsimpleplugins.wordpress.com/documentation/
 * Description: On-site notification system.
 * Version: 1.3.27
 * Author: SPlugins
 * Author URI: https://wpsimpleplugins.wordpress.com/documentation/
 * License: GPLv2
 * WC tested up to: 7.8.2
 **/
if ( !defined( 'ABSPATH' ) ) {
    exit;
}

if ( function_exists( 'wnb_fs' ) ) {
    wnb_fs()->set_basename( false, __FILE__ );
} else {
    // DO NOT REMOVE THIS IF, IT IS ESSENTIAL FOR THE `function_exists` CALL ABOVE TO PROPERLY WORK.
    
    if ( !function_exists( 'wnb_fs' ) ) {
        // Create a helper function for easy SDK access.
        function wnb_fs()
        {
            global  $wnb_fs ;
            
            if ( !isset( $wnb_fs ) ) {
                // Include Freemius SDK.
                require_once dirname( __FILE__ ) . '/freemius/start.php';
                $wnb_fs = fs_dynamic_init( array(
                    'id'             => '7859',
                    'slug'           => 'wp-notification-bell',
                    'type'           => 'plugin',
                    'public_key'     => 'pk_055097af503c7ea0df64c89ce788a',
                    'is_premium'     => false,
                    'has_addons'     => true,
                    'has_paid_plans' => true,
                    'menu'           => array(
                    'slug'    => 'edit.php?post_type=wnbell_notifications',
                    'contact' => false,
                    'support' => false,
                ),
                    'anonymous_mode' => true,
                    'is_live'        => true,
                ) );
            }
            
            return $wnb_fs;
        }
        
        // Init Freemius.
        wnb_fs();
        // Signal that SDK was initiated.
        do_action( 'wnb_fs_loaded' );
    }
    
    require plugin_dir_path( __FILE__ ) . 'includes/logger.php';
    //if (is_admin()) {
    require plugin_dir_path( __FILE__ ) . 'admin/admin.php';
    require plugin_dir_path( __FILE__ ) . 'admin/admin_cpt.php';
    require plugin_dir_path( __FILE__ ) . 'admin/admin_page.php';
    //}
    wp_mkdir_p( WNBELL_LOG_DIR );
    add_action( 'init', 'wnbell_plugin_init' );
    global  $wnbell_db_version ;
    $wnbell_db_version = 3;
    if ( !defined( 'WNBELL_INTERVAL' ) ) {
        define( 'WNBELL_INTERVAL', 600000 );
    }
    require plugin_dir_path( __FILE__ ) . 'includes/activation.php';
    register_activation_hook( __FILE__, 'wnbell_install' );
    add_action( 'plugins_loaded', 'wnbell_update_db_check' );
    require plugin_dir_path( __FILE__ ) . 'includes/helpers.php';
    require plugin_dir_path( __FILE__ ) . 'includes/outputs.php';
    require plugin_dir_path( __FILE__ ) . 'includes/updates.php';
    require plugin_dir_path( __FILE__ ) . 'includes/shortcode.php';
    require plugin_dir_path( __FILE__ ) . 'includes/menu_bell.php';
    require plugin_dir_path( __FILE__ ) . 'includes/visitor_shortcode.php';
    require plugin_dir_path( __FILE__ ) . 'includes/visitor_menu_bell.php';
    require plugin_dir_path( __FILE__ ) . 'includes/floating_icon.php';
    require plugin_dir_path( __FILE__ ) . 'includes/comments.php';
    require plugin_dir_path( __FILE__ ) . 'includes/bbpress.php';
    require plugin_dir_path( __FILE__ ) . 'includes/buddypress.php';
    require plugin_dir_path( __FILE__ ) . 'includes/woocommerce.php';
    add_action( 'before_woocommerce_init', function () {
        if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
        }
    } );
    function wnbell_custom_is_submenu_visible( $is_visible, $menu_id )
    {
        if ( 'contact' != $menu_id ) {
            return $is_visible;
        }
        return wnb_fs()->can_use_premium_code();
    }
    
    wnb_fs()->add_filter(
        'is_submenu_visible',
        'wnbell_custom_is_submenu_visible',
        10,
        2
    );
    // register_activation_hook(__FILE__, 'wnbell_activate');
    function wnbell_plugin_init()
    {
        register_post_type( 'wnbell_notifications', array(
            'labels'             => array(
            'name'               => __( 'Notification Bell', 'wp-notification-bell' ),
            'singular_name'      => __( 'Notification', 'wp-notification-bell' ),
            'add_new'            => __( 'Add New', 'wp-notification-bell' ),
            'add_new_item'       => __( 'Add New Notification', 'wp-notification-bell' ),
            'edit'               => __( 'Edit', 'wp-notification-bell' ),
            'edit_item'          => __( 'Edit Notification', 'wp-notification-bell' ),
            'new_item'           => __( 'New Notification', 'wp-notification-bell' ),
            'all_items'          => __( 'All Notifications', 'wp-notification-bell' ),
            'view'               => __( 'View', 'wp-notification-bell' ),
            'view_item'          => __( 'View Notification', 'wp-notification-bell' ),
            'search_items'       => __( 'Search Notifications', 'wp-notification-bell' ),
            'not_found'          => __( 'No Notifications found', 'wp-notification-bell' ),
            'not_found_in_trash' => __( 'No Notifications found in Trash', 'wp-notification-bell' ),
            'parent'             => 'Parent Notification',
        ),
            'public'             => true,
            'publicly_queryable' => false,
            'menu_position'      => 20,
            'supports'           => array( 'thumbnail' ),
            'taxonomies'         => array( '' ),
            'menu_icon'          => 'dashicons-bell',
            'has_archive'        => true,
        ) );
        load_plugin_textdomain( 'wp-notification-bell', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
    }
    
    wnb_fs()->add_action( 'after_uninstall', 'wnb_fs_uninstall_cleanup' );
    function wnb_fs_uninstall_cleanup()
    {
        wnbell_delete_plugin();
    }
    
    function wnbell_delete_plugin()
    {
        global  $wpdb ;
        delete_option( 'wnbell_options' );
        delete_option( 'wnbell_change_metakeys_complete' );
        delete_option( 'wnbell_settings' );
        delete_option( 'wnbell_db_version' );
        delete_option( 'wnbell_notif_options' );
        $posts = get_posts( array(
            'numberposts' => -1,
            'post_type'   => 'wnbell_notifications',
            'post_status' => 'any',
        ) );
        foreach ( $posts as $post ) {
            wp_delete_post( $post->ID, true );
        }
        $meta_type = 'user';
        $user_id = 0;
        // This will be ignored, since we are deleting for all users.
        $meta_value = '';
        // Also ignored. The meta will be deleted regardless of value.
        $delete_all = true;
        $meta_key = 'wnbell_unseen';
        delete_metadata(
            $meta_type,
            $user_id,
            $meta_key,
            $meta_value,
            $delete_all
        );
        $meta_key = 'wnbell_seen_notification_post';
        delete_metadata(
            $meta_type,
            $user_id,
            $meta_key,
            $meta_value,
            $delete_all
        );
        $meta_key = 'wnbell_unseen_comments';
        delete_metadata(
            $meta_type,
            $user_id,
            $meta_key,
            $meta_value,
            $delete_all
        );
        $meta_key = 'wnbell_seen_comments_ids';
        delete_metadata(
            $meta_type,
            $user_id,
            $meta_key,
            $meta_value,
            $delete_all
        );
        $meta_key = 'wnbell_unseen_bbpress_replies';
        delete_metadata(
            $meta_type,
            $user_id,
            $meta_key,
            $meta_value,
            $delete_all
        );
        $meta_key = 'wnbell_seen_bbp_ids';
        delete_metadata(
            $meta_type,
            $user_id,
            $meta_key,
            $meta_value,
            $delete_all
        );
        $meta_key = 'wnbell_recipient_role';
        delete_metadata(
            $meta_type,
            $user_id,
            $meta_key,
            $meta_value,
            $delete_all
        );
        $meta_key = 'wnbell_recipient_username';
        delete_metadata(
            $meta_type,
            $user_id,
            $meta_key,
            $meta_value,
            $delete_all
        );
        $meta_key = 'wnbell_woocommerce_updates';
        delete_metadata(
            $meta_type,
            $user_id,
            $meta_key,
            $meta_value,
            $delete_all
        );
        $meta_key = 'wnbell_seen_woocommerce_ids';
        delete_metadata(
            $meta_type,
            $user_id,
            $meta_key,
            $meta_value,
            $delete_all
        );
        //global $wpdb;
        if ( is_multisite() ) {
            
            if ( !empty($_GET['networkwide']) ) {
                // Get blog list and cycle through all blogs
                $start_blog = $wpdb->blogid;
                $blog_list = $wpdb->get_col( 'SELECT blog_id FROM ' . $wpdb->blogs );
                foreach ( $blog_list as $blog ) {
                    switch_to_blog( $blog );
                    // Call function to delete table with prefix
                    wnbell_drop_table( $wpdb->get_blog_prefix() );
                }
                switch_to_blog( $start_blog );
                return;
            }
        
        }
        wnbell_drop_table( $wpdb->prefix );
    }
    
    function wnbell_drop_table( $prefix )
    {
        global  $wpdb ;
        $wpdb->query( 'DROP TABLE IF EXISTS ' . $prefix . 'wnbell_recipients_role' );
        $wpdb->query( 'DROP TABLE IF EXISTS ' . $prefix . 'wnbell_recipients' );
    }

}
