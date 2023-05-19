<?php

namespace WeDevs\DokanPro\Modules\RMA;

use WeDevs\DokanPro\Modules\RMA\RmaCache;
use WeDevs\DokanPro\Modules\RMA\BlockData;

class Module {

    /**
     * Load automatically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        $this->define();

        $this->includes();

        $this->initiate();

        $this->hooks();

        add_action( 'dokan_activated_module_rma', [ $this, 'activate' ] );
    }

    /**
     * Hooks
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function define() {
        define( 'DOKAN_RMA_DIR', dirname( __FILE__ ) );
        define( 'DOKAN_RMA_INC_DIR', DOKAN_RMA_DIR . '/includes' );
        define( 'DOKAN_RMA_ASSETS_DIR', plugins_url( 'assets', __FILE__ ) );
    }

    /**
    * Get plugin path
    *
    * @since 1.5.1
    *
    * @return void
    **/
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
     * Includes all necessary class a functions file
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function includes() {
        if ( is_admin() ) {
            require_once DOKAN_RMA_INC_DIR . '/class-admin.php';
        }

        require_once DOKAN_RMA_INC_DIR . '/class-trait-rma.php';
        require_once DOKAN_RMA_INC_DIR . '/class-ajax.php';
        require_once DOKAN_RMA_INC_DIR . '/class-vendor.php';
        require_once DOKAN_RMA_INC_DIR . '/class-product.php';
        require_once DOKAN_RMA_INC_DIR . '/class-order.php';
        require_once DOKAN_RMA_INC_DIR . '/class-frontend.php';
        require_once DOKAN_RMA_INC_DIR . '/class-warranty-request.php';
        require_once DOKAN_RMA_INC_DIR . '/class-warranty-item.php';
        require_once DOKAN_RMA_INC_DIR . '/class-warranty-request-conversation.php';

        // Load all helper functions
        require_once DOKAN_RMA_INC_DIR . '/functions.php';
    }

    /**
     * Initiate all classes
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function initiate() {
        if ( is_admin() ) {
            new \Dokan_RMA_Admin();
        }

        new RmaCache();
        new BlockData();
        new \Dokan_RMA_Ajax();
        new \Dokan_RMA_Vendor();
        new \Dokan_RMA_Frontend();
        new \Dokan_RMA_Product();
        new \Dokan_RMA_Order();
    }

    /**
     * Init all hooks
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function hooks() {
        //tinysort.min.js
        add_action( 'init', [ $this, 'register_scripts' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts' ] );

        add_action( 'dokan_loaded', [ $this, 'load_emails' ], 20 );
        // dokan simple auciton email
        add_filter( 'dokan_email_list', array( $this, 'set_email_template_directory' ) );
        // flush rewrite rules
        add_action( 'woocommerce_flush_rewrite_rules', [ $this, 'flush_rewrite_rules' ] );
    }

    /**
     * Load emails
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_emails() {
        add_filter( 'woocommerce_email_classes', [ $this, 'load_rma_email_classes' ], 99 );
        add_filter( 'dokan_email_actions', [ $this, 'register_rma_email_actions' ] );
    }

    /**
     * Load all email class related with RMA
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function load_rma_email_classes( $wc_emails ) {
        $wc_emails['Dokan_Send_Coupon_Email']         = include DOKAN_RMA_INC_DIR . '/emails/class-dokan-rma-send-coupin-email.php';
        $wc_emails['Dokan_Rma_Send_Warranty_Request'] = include DOKAN_RMA_INC_DIR . '/emails/class-dokan-rma-send-warranty-request.php';

        return $wc_emails;
    }

    /**
     * Register all email actions
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function register_rma_email_actions( $actions ) {
        $actions[] = 'dokan_send_coupon_to_customer';
        $actions[] = 'dokan_rma_send_warranty_request';

        return $actions;
    }

    /**
     * Register Scripts
     *
     * @since 3.7.4
     */
    public function register_scripts() {
        list( $suffix, $version ) = dokan_get_script_suffix_and_version();

        wp_register_script( 'dokan-rma-script', DOKAN_RMA_ASSETS_DIR . '/js/scripts.js', array( 'jquery' ), $version, true );
        wp_register_style( 'dokan-rma-style', DOKAN_RMA_ASSETS_DIR . '/css/style.css', false, $version, 'all' );
    }

    /**
     * Load scripts
     *
     * @since 1.0.0
     *
     * @return void
    */
    public function load_scripts() {
        global $wp, $post;

        $post_id = 0;

        if ( isset( $post->ID ) && $post->ID && 'product' == $post->post_type ) {
            $post_id = $post->ID;
        }

        if ( isset( $_GET['product_id'] ) ) {
            $post_id = intval( $_GET['product_id'] );
        }

        if ( ( isset( $wp->query_vars['settings'] ) && 'rma' === (string) $wp->query_vars['settings'] )
            || ( isset( $_GET['action'] ) && $_GET['action'] == 'edit' && ! empty( $_GET['product_id'] ) ) ) { //phpcs:ignore
            wp_enqueue_script( 'dokan-rma-script' );
            wp_enqueue_style( 'dokan-rma-style' );
        }

        if ( is_account_page() && ( isset( $wp->query_vars['request-warranty'] ) || isset( $wp->query_vars['view-rma-requests'] ) ) ) {
            wp_enqueue_style( 'dokan-rma-style' );
        }

        if ( isset( $wp->query_vars['return-request'] ) ) {
            wp_enqueue_style( 'dokan-rma-style' );
            wp_enqueue_script( 'dokan-rma-script' );

            wp_localize_script(
                'dokan-rma-script', 'DokanRMA', [
                    'ajaxurl' => admin_url( 'admin-ajax.php' ),
                    'nonce'   => wp_create_nonce( 'dokan_rma_nonce' ),
                ]
            );

            wp_enqueue_style( 'dokan-magnific-popup' );
            wp_enqueue_script( 'dokan-magnific-popup' );
        }

        if ( is_account_page() ) {
            $custom_css = '
            body.woocommerce-account ul li.woocommerce-MyAccount-navigation-link--rma-requests a:before{
                font-family: "Font Awesome\ 5 Free";
                font-weight: 900;
                content: "\f0e2"
            }';
            wp_add_inline_style( 'woocommerce-layout', $custom_css );
        }

        if ( isset( $wp->query_vars['settings'] ) && 'rma' === $wp->query_vars['settings'] ) {
            wp_enqueue_script( 'dokan-tooltip' );
            wp_enqueue_script( 'dokan-form-validate' );
        }
    }

    /**
     * Create Mapping table for product and vendor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function activate() {
        global $wp_roles;

        if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {
            $wp_roles = new \WP_Roles(); //phpcs:ignore
        }

        $wp_roles->add_cap( 'seller', 'dokan_view_store_rma_menu' );
        $wp_roles->add_cap( 'administrator', 'dokan_view_store_rma_menu' );
        $wp_roles->add_cap( 'shop_manager', 'dokan_view_store_rma_menu' );

        $wp_roles->add_cap( 'seller', 'dokan_view_store_rma_settings_menu' );
        $wp_roles->add_cap( 'administrator', 'dokan_view_store_rma_settings_menu' );
        $wp_roles->add_cap( 'shop_manager', 'dokan_view_store_rma_settings_menu' );

        $this->create_tables();

        // flash rewrite rules
        $this->flush_rewrite_rules();
    }

    /**
     * Flush rewrite rules
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function flush_rewrite_rules() {
        dokan()->rewrite->register_rule();
        flush_rewrite_rules();
    }

    /**
     * Set Proper template directory.
     *
     * @param array $template_array
     *
     * @return array
     */
    public function set_email_template_directory( $template_array ) {
        array_push( $template_array, 'send-coupon.php', 'send-warranty-request.php' );
        return $template_array;
    }

    /**
     * Create all tables related with RMA
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function create_tables() {
        global $wpdb;

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $request_table = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_rma_request` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `order_id` int(11) NOT NULL,
          `vendor_id` int(11) NOT NULL,
          `customer_id` int(11) NOT NULL,
          `type` varchar(25) NOT NULL DEFAULT '',
          `status` varchar(25) NOT NULL DEFAULT '',
          `reasons` text NOT NULL,
          `details` longtext,
          `note` longtext,
          `created_at` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

        $request_product_map = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_rma_request_product` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `request_id` int(11) NOT NULL,
          `product_id` int(11) NOT NULL,
          `quantity` int(11) NOT NULL,
          `item_id` int(11) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

        $conversation_table = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_rma_conversations` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `request_id` int(11) NOT NULL,
          `from` int(11) NOT NULL,
          `to` int(11) NOT NULL,
          `message` longtext,
          `created_at` datetime NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;";

        dbDelta( $request_table );
        dbDelta( $request_product_map );
        dbDelta( $conversation_table );
    }
}
