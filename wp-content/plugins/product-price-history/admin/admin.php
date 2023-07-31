<?php

namespace Devnet\PPH\Admin;

use  Devnet\PPH\Modules\Chart\Chart_Admin ;
use  Devnet\PPH\Includes\Helper ;
class PPH_Admin
{
    /**
     * The ID of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $plugin_name    The ID of this plugin.
     */
    private  $plugin_name ;
    /**
     * The version of this plugin.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $version    The current version of this plugin.
     */
    private  $version ;
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }
    
    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles( $hook )
    {
        $allowed_pages = [ 'toplevel_page_product-price-history', 'post.php', 'post-new.php' ];
        if ( !in_array( $hook, $allowed_pages ) ) {
            return;
        }
        wp_enqueue_style( 'wp-color-picker' );
        wp_enqueue_style(
            $this->plugin_name,
            plugin_dir_url( __DIR__ ) . 'assets/build/admin.css',
            [],
            $this->version,
            'all'
        );
    }
    
    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts( $hook )
    {
        $allowed_pages = [ 'toplevel_page_product-price-history', 'post.php', 'post-new.php' ];
        if ( !in_array( $hook, $allowed_pages ) ) {
            return;
        }
        wp_enqueue_media();
        wp_enqueue_script(
            'wp-color-picker-alpha',
            plugin_dir_url( __DIR__ ) . 'assets/color-picker/wp-color-picker-alpha.min.js',
            [ 'wp-color-picker' ],
            $this->version,
            true
        );
        $script_asset_path = plugin_dir_url( __DIR__ ) . 'assets/build/admin.asset.php';
        $script_info = ( file_exists( $script_asset_path ) ? include $script_asset_path : [
            'dependencies' => [ 'jquery' ],
            'version'      => $this->version,
        ] );
        wp_enqueue_script(
            $this->plugin_name,
            plugin_dir_url( __DIR__ ) . 'assets/build/admin.js',
            $script_info['dependencies'],
            $script_info['version'],
            true
        );
        $script_data = [
            'ajaxurl' => admin_url( 'admin-ajax.php' ),
            'nonce'   => wp_create_nonce( 'pph_ajax_nonce' ),
            'text'    => [
            '30_days'        => esc_html__( '30 days', 'product-price-history' ),
            '3_months'       => esc_html__( '3 months', 'product-price-history' ),
            '6_months'       => esc_html__( '6 months', 'product-price-history' ),
            '12_months'      => esc_html__( '12 months', 'product-price-history' ),
            'delete_confirm' => esc_html__( 'Are you sure you want to proceed? This action will irreversibly delete old data.', 'product-price-history' ),
            'delete_success' => esc_html__( '%s old entries has been successfully deleted.', 'product-price-history' ),
            'delete_none'    => esc_html__( 'No old data was found or deleted.', 'product-price-history' ),
        ],
        ];
        wp_localize_script( $this->plugin_name, 'devnet_pph_script', $script_data );
    }
    
    /**
     * Add admin menu page.
     *
     * @since    1.0.0
     */
    public function admin_menu()
    {
        $plugin_settings = new Settings();
        $menu_slug = 'product-price-history';
        add_menu_page(
            esc_html__( 'Product Price History', 'product-price-history' ),
            esc_html__( 'Price History', 'product-price-history' ),
            'edit_posts',
            $menu_slug,
            [ $plugin_settings, 'settings_page' ],
            'dashicons-chart-line',
            56
        );
        add_submenu_page(
            $menu_slug,
            esc_html__( 'Settings', 'product-price-history' ),
            esc_html__( 'Settings', 'product-price-history' ),
            'edit_posts',
            $menu_slug
        );
    }
    
    /**
     * Add plugin action link.
     *
     * @since    1.0.0
     */
    public function plugin_action_links( $links )
    {
        $custom_links = [];
        $custom_links[] = '<a href="' . esc_url( get_admin_url( null, 'admin.php?page=product-price-history' ) ) . '">' . esc_html__( 'Settings', 'product-price-history' ) . '</a>';
        return array_merge( $custom_links, $links );
    }
    
    /**
     * Declare that plugin is COT compatible.
     *
     * @since    1.0.0
     */
    public function cot_compatible()
    {
        
        if ( class_exists( '\\Automattic\\WooCommerce\\Utilities\\FeaturesUtil' ) ) {
            $plugin_file = plugin_dir_path( dirname( __FILE__ ) ) . 'product-price-history.php';
            \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', $plugin_file, true );
        }
    
    }
    
    /**
     * Ajax action for deleting old price history data.
     *  
     * @since     2.1.3
     */
    public function delete_old_data()
    {
        $nonce = $_POST['nonce'];
        if ( !wp_verify_nonce( $nonce, 'pph_ajax_nonce' ) ) {
            wp_send_json_error( 'Invalid nonce.' );
        }
        $older_than = ( isset( $_POST['args']['older_than'] ) ? sanitize_text_field( $_POST['args']['older_than'] ) : null );
        $deleted = Helper::delete_old_data( 'price_history', $older_than );
        wp_send_json( $deleted );
        wp_die();
    }

}