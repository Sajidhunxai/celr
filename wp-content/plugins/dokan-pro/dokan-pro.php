<?php
/**
 * Plugin Name: Dokan Pro
 * Plugin URI: https://wedevs.com/dokan/
 * Description: An e-commerce marketplace plugin for WordPress. Powered by WooCommerce and weDevs.
 * Version: 3.7.21
 * Author: weDevs
 * Author URI: https://wedevs.com/
 * WC requires at least: 5.0.0
 * WC tested up to: 7.7.0
 * License: GPL2
 * TextDomain: dokan
 */

/**
 * Dokan Pro Feature Loader
 *
 * Load all pro functionality in this class
 * if pro folder exist then automatically load this class file
 *
 * @since 2.4
 *
 * @author weDevs <info@wedevs.com>
 */
class Dokan_Pro {

    /**
     * Plan type
     *
     * @var string
     */
    private $plan = 'dokan-professional';

    /**
     * Plugin version
     *
     * @var string
     */
    public $version = '3.7.21';

    /**
     * Database version key
     *
     * @since 3.0.0
     *
     * @var string
     */
    private $db_version_key = 'dokan_pro_version';

    /**
     * Holds various class instances
     *
     * @since 3.0.0
     *
     * @var array
     */
    private $container = [];

    /**
     * Initializes the WeDevs_Dokan() class
     *
     * Checks for an existing WeDevs_WeDevs_Dokan() instance
     * and if it doesn't find one, creates it.
     */
    public static function init() {
        static $instance = false;

        if ( ! $instance ) {
            $instance = new Dokan_Pro();
        }

        return $instance;
    }

    /**
     * Constructor for the Dokan_Pro class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @return void
     */
    public function __construct() {
        require_once __DIR__ . '/vendor/autoload.php';

        $this->define_constants();

        // Register admin notices to container and load notices
        $this->container['admin_notices'] = new \WeDevs\DokanPro\Admin\Notices\Manager();

        add_action( 'dokan_loaded', [ $this, 'init_plugin' ] );
        add_action( 'dokan_loaded', [ $this, 'init_updater' ] );

        register_activation_hook( __FILE__, [ $this, 'activate' ] );
        register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );

        new WeDevs\DokanPro\Brands\Hooks();
    }

    /**
     * Magic getter to bypass referencing objects
     *
     * @since 3.0.0
     *
     * @param $prop
     *
     * @return mixed
     */
    public function __get( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return $this->container[ $prop ];
        }

        trigger_error( sprintf( 'Undefined property: %s', self::class . '::$' . $prop ) );
    }

    /**
     * Magic isset to check if it's exist
     *
     * @param $prop
     *
     * @return bool
     */
    public function __isset( $prop ) {
        if ( array_key_exists( $prop, $this->container ) ) {
            return true;
        }

        return false;
    }

    /**
     * Define all pro module constant
     *
     * @since  2.6
     *
     * @return void
     */
    public function define_constants() {
        define( 'DOKAN_PRO_PLUGIN_VERSION', $this->version );
        define( 'DOKAN_PRO_FILE', __FILE__ );
        define( 'DOKAN_PRO_DIR', dirname( DOKAN_PRO_FILE ) );
        define( 'DOKAN_PRO_TEMPLATE_DIR', DOKAN_PRO_DIR . '/templates' );
        define( 'DOKAN_PRO_INC', DOKAN_PRO_DIR . '/includes' );
        define( 'DOKAN_PRO_ADMIN_DIR', DOKAN_PRO_INC . '/Admin' );
        define( 'DOKAN_PRO_CLASS', DOKAN_PRO_DIR . '/classes' );
        define( 'DOKAN_PRO_PLUGIN_ASSEST', plugins_url( 'assets', DOKAN_PRO_FILE ) );
        define( 'DOKAN_PRO_MODULE_DIR', DOKAN_PRO_DIR . '/modules' );
        define( 'DOKAN_PRO_MODULE_URL', plugins_url( 'modules', DOKAN_PRO_FILE ) );
    }

    /**
     * Get Dokan db version key
     *
     * @since 2.8.0
     *
     * @return string
     */
    public function get_db_version_key() {
        return $this->db_version_key;
    }

    /**
     * Placeholder for activation function
     */
    public function activate() {
        $installer = new \WeDevs\DokanPro\Install\Installer();
        $installer->do_install();

        // check if WooCommerce is exists or not
        if ( function_exists( 'WC' ) && function_exists( 'dokan' ) ) {
            $this->flush_rewrite_rules();
        }
    }

    /**
     * Placeholder for deactivation function
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function deactivate() {
        \WeDevs\DokanPro\Withdraw\Manager::cancel_all_schedules();
    }

    /**
     * This method will flush rewrite rules for dokan pro
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function flush_rewrite_rules() {
        // we need to load this file here, because rewrite rules were written on this file
        if ( ! isset( $this->container['store'] ) ) {
            $this->container['store'] = new \WeDevs\DokanPro\Store();
        }
        if ( ! isset( $this->container['social_login'] ) ) {
            $this->container['social_login'] = new \WeDevs\DokanPro\SocialLogin();
        }
        //other rewrite related hooks
        add_filter( 'dokan_query_var_filter', [ $this, 'load_query_var' ], 10 ); // this hook wasn't called on class constractor
        add_filter( 'dokan_query_var_filter', array( $this->container['social_login'], 'register_support_queryvar' ) ); // this filter wasn't called on class constractor

        // flash rewrite rules
        dokan()->rewrite->register_rule();
        flush_rewrite_rules();
    }

    /**
     * Load all things
     *
     * @since 2.7.3
     *
     * @return void
     */
    public function init_plugin() {
        spl_autoload_register( [ $this, 'dokan_pro_autoload' ] );

        $this->includes();
        $this->load_actions();
        $this->load_filters();

        $modules = new \WeDevs\DokanPro\Module();

        $modules->load_active_modules();

        $this->container['module'] = $modules;
    }

    /**
     * Check whether dokan lite is installed
     *
     * @since 3.3.1
     *
     * @return bool
     */
    public function is_dokan_lite_installed() {
        $plugins = array_keys( get_plugins() );
        return in_array( 'dokan-lite/dokan.php', $plugins, true ) || in_array( 'dokan/dokan.php', $plugins, true );
    }

    /**
     * This method will return core dokan lite plugin file
     *
     * @since 3.3.1
     *
     * @return string
     */
    public function get_core_plugin_file() {
        $plugins = array_keys( get_plugins() );
        if ( in_array( 'dokan/dokan.php', $plugins, true ) ) {
            return 'dokan/dokan.php';
        }
        return 'dokan-lite/dokan.php';
    }

    /**
     * Load all includes file for pro
     *
     * @since 2.4
     *
     * @return void
     */
    public function includes() {
        require_once DOKAN_PRO_INC . '/functions.php';
        require_once DOKAN_PRO_INC . '/Coupons/functions.php';
        require_once DOKAN_PRO_INC . '/function-orders.php';
        require_once DOKAN_PRO_INC . '/functions-reports.php';
        require_once DOKAN_PRO_INC . '/functions-wc.php';
    }

    /**
     * Load all necessary Actions hooks
     *
     * @since 2.4
     *
     * @return void
     */
    public function load_actions() {
        // init the classes
        add_action( 'init', [ $this, 'localization_setup' ] );

        add_action( 'init', [ $this, 'init_classes' ], 10 );
        add_action( 'init', [ $this, 'init_shipping_class' ], 1 );
        add_action( 'init', [ $this, 'register_scripts' ], 10 );

        add_action( 'woocommerce_after_my_account', [ $this, 'dokan_account_migration_button' ] );

        add_action( 'dokan_enqueue_scripts', [ $this, 'enqueue_scripts' ], 11 );
        add_action( 'dokan_enqueue_admin_scripts', [ $this, 'admin_enqueue_scripts' ] );

        if ( function_exists( 'register_block_type' ) ) {
            new \WeDevs\DokanPro\BlockEditorBlockTypes();
        }

        // flush rewrite rules
        add_action( 'woocommerce_flush_rewrite_rules', [ $this, 'flush_rewrite_rules' ] );
    }

    /**
     * Load all Filters Hook
     *
     * @since 2.4
     *
     * @return void
     */
    public function load_filters() {
        add_filter( 'dokan_rest_api_class_map', [ $this, 'rest_api_class_map' ] );
        add_filter( 'dokan_is_pro_exists', [ $this, 'set_as_pro' ], 99 );
        add_filter( 'dokan_query_var_filter', [ $this, 'load_query_var' ], 10 );
        add_filter( 'woocommerce_locate_template', [ $this, 'dokan_registration_template' ] );
        add_action( 'init', [ $this, 'account_migration_endpoint' ] );
        add_action( 'woocommerce_account_account-migration_endpoint', [ $this, 'account_migration' ] );
        add_filter( 'dokan_set_template_path', [ $this, 'load_pro_templates' ], 10, 3 );
        add_filter( 'dokan_widgets', [ $this, 'register_widgets' ] );

        //Dokan Email filters for WC Email
        add_filter( 'woocommerce_email_classes', [ $this, 'load_dokan_emails' ], 36 );
        add_filter( 'dokan_email_list', [ $this, 'set_email_template_directory' ], 15 );
        add_filter( 'dokan_email_actions', [ $this, 'register_email_actions' ] );
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'dokan', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     * Instantiate all classes
     *
     * @since 2.4
     *
     * @return void
     */
    public function init_classes() {
        new WeDevs\DokanPro\Refund\Hooks();
        new WeDevs\DokanPro\Coupons\Hooks();
        new WeDevs\DokanPro\Coupons\AdminCoupons();
        new \WeDevs\DokanPro\Shipping\Hooks();
        new \WeDevs\DokanPro\Upgrade\Hooks();
        new \WeDevs\DokanPro\StoreCategory();
        new \WeDevs\DokanPro\StoreListsFilter();
        new \WeDevs\DokanPro\Blocks\Manager();

        // Initialize multiple store time settings.
        new \WeDevs\DokanPro\StoreTime\Settings();

        new \WeDevs\DokanPro\SettingsApi\Manager();

        if ( is_admin() ) {
            new \WeDevs\DokanPro\Admin\Admin();
            new \WeDevs\DokanPro\Admin\Pointers();
            new \WeDevs\DokanPro\Admin\Ajax();
            new \WeDevs\DokanPro\Admin\ShortcodesButton();
        }

        $this->container['announcement'] = new \WeDevs\DokanPro\Admin\Announcement();
        new \WeDevs\DokanPro\EmailVerification();

        // fix rewrite rules for dokan pro
        if ( ! isset( $this->container['social_login'] ) ) {
            $this->container['social_login'] = new \WeDevs\DokanPro\SocialLogin();
        }
        if ( ! isset( $this->container['store'] ) ) {
            $this->container['store'] = new \WeDevs\DokanPro\Store();
        }
        //load classes
        $this->container['store_seo']                = new \WeDevs\DokanPro\StoreSeo();
        $this->container['product_seo']              = new \WeDevs\DokanPro\ProductSeo();
        $this->container['product_bulk_edit']        = new \WeDevs\DokanPro\ProductBulkEdit();
        $this->container['store_share']              = new \WeDevs\DokanPro\StoreShare();
        $this->container['products']                 = new \WeDevs\DokanPro\Products();
        $this->container['review']                   = new \WeDevs\DokanPro\Review();
        $this->container['notice']                   = new \WeDevs\DokanPro\Notice();
        $this->container['refund']                   = new \WeDevs\DokanPro\Refund\Manager();
        $this->container['brands']                   = new \WeDevs\DokanPro\Brands\Manager();
        $this->container['coupon']                   = new \WeDevs\DokanPro\Coupons\Manager();
        $this->container['reports']                  = new \WeDevs\DokanPro\Reports\Manager();
        $this->container['digital_product']          = new \WeDevs\DokanPro\DigitalProduct();
        $this->container['shipment']                 = new \WeDevs\DokanPro\Shipping\ShippingStatus();
        $this->container['bg_sync_vendor_zone_data'] = new \WeDevs\DokanPro\BackgroundProcess\SyncVendorZoneData();
        $this->container['reverse_withdrawal']       = new \WeDevs\DokanPro\ReverseWithdrawal();
        $this->container['catalog_mode_inline_edit'] = new \WeDevs\DokanPro\CatalogModeProductInlineEdit();

        if ( is_user_logged_in() ) {
            new \WeDevs\DokanPro\Dashboard\Dashboard();
            new WeDevs\DokanPro\Reports();
            new WeDevs\DokanPro\CustomWithdrawMethod();

            $this->container['store_settings'] = new \WeDevs\DokanPro\Settings();
        }

        $this->container['withdraw'] = new WeDevs\DokanPro\Withdraw\Manager();

        $this->container = apply_filters( 'dokan_pro_get_class_container', $this->container );

        new \WeDevs\DokanPro\Assets();

        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {
            new \WeDevs\DokanPro\Ajax();
        }
    }

    /**
     * Initialize shipping class
     *
     * @since 3.3.7
     *
     * @retrun void
     */
    public function init_shipping_class() {
        new \WeDevs\DokanPro\Shipping\Hooks();
    }

    /**
     * Initialize the plugin updater
     *
     * @since 3.1.1
     *
     * @return void
     */
    public function init_updater() {
        new \WeDevs\DokanPro\Update();
    }

    /**
     * Register all scripts
     *
     * @since 2.6
     *
     * @return void
     */
    public function register_scripts() {
        list( $suffix, $version ) = dokan_get_script_suffix_and_version();

        wp_register_style( 'dokan-pro-style', DOKAN_PRO_PLUGIN_ASSEST . '/css/dokan-pro' . $suffix . '.css', false, $version, 'all' );
        wp_register_style( 'dokan_pro_admin_style', DOKAN_PRO_PLUGIN_ASSEST . '/css/dokan-pro-admin-style' . $suffix . '.css', [], $version, 'all' );

        // Register all js
        wp_register_script( 'serializejson', WC()->plugin_url() . '/assets/js/jquery-serializejson/jquery.serializejson' . $suffix . '.js', [ 'jquery' ], $version, true );
        wp_register_script( 'dokan-product-shipping', plugins_url( 'assets/js/dokan-single-product-shipping' . $suffix . '.js', __FILE__ ), false, $version, true );
        wp_register_script( 'jquery-blockui', WC()->plugin_url() . '/assets/js/jquery-blockui/jquery.blockUI.min.js', [ 'jquery' ], $version, true );
        wp_register_script( 'dokan-pro-script', DOKAN_PRO_PLUGIN_ASSEST . '/js/dokan-pro' . $suffix . '.js', [ 'jquery', 'dokan-script' ], $version, true );
        wp_register_script( 'dokan_pro_admin', DOKAN_PRO_PLUGIN_ASSEST . '/js/dokan-pro-admin' . $suffix . '.js', [ 'jquery', 'jquery-blockui' ], $version, true );
        wp_register_script( 'dokan_admin_coupon', DOKAN_PRO_PLUGIN_ASSEST . '/js/dokan-admin-coupon' . $suffix . '.js', [ 'jquery' ], $version, true );
    }

    /**
     * Register widgets
     *
     * @since 2.8
     *
     * @return void
     */
    public function register_widgets( $widgets ) {
        $widgets['best_seller']    = \WeDevs\DokanPro\Widgets\BestSeller::class;
        $widgets['feature_seller'] = \WeDevs\DokanPro\Widgets\FeatureSeller::class;

        return $widgets;
    }

    /**
     * Dokan Account Migration Button render
     *
     * @since 2.4
     *
     * @return void
     */
    public function dokan_account_migration_button() {
        $user = wp_get_current_user();

        dokan_get_template_part( 'global/account-migration-btn', '', [ 'pro' => true ] );
    }

    /**
     * Enqueue scripts
     *
     * @since 2.6
     *
     * @return void
     * */
    public function enqueue_scripts() {
        // Use minified libraries if SCRIPT_DEBUG is turned off
        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

        if (
            ( dokan_is_seller_dashboard() || ( get_query_var( 'edit' ) && is_singular( 'product' ) ) )
            || dokan_is_store_page()
            || dokan_is_store_review_page()
            || is_account_page()
            || dokan_is_store_listing()
            || apply_filters( 'dokan_forced_load_scripts', false )
            ) {
            // Load dokan pro styles
            wp_enqueue_style( 'dokan-pro-style' );

            // Load accounting scripts
            wp_enqueue_script( 'serializejson' );
            wp_enqueue_script( 'jquery-blockui' );

            //localize script for refund and dashboard image options
            $dokan_refund = dokan_get_refund_localize_data();
            wp_localize_script( 'dokan-script', 'dokan_refund', $dokan_refund );
            wp_enqueue_script( 'dokan-pro-script' );
        }

        // Load in Single product pages only
        if ( is_singular( 'product' ) && ! get_query_var( 'edit' ) ) {
            wp_enqueue_script( 'dokan-product-shipping' );
        }

        if ( get_query_var( 'account-migration' ) ) {
            wp_enqueue_script( 'dokan-vendor-registration' );
        }
    }

    /**
     * Admin scripts
     *
     * @since 2.6
     *
     * @return void
     * */
    public function admin_enqueue_scripts() {
        global $post_type;

        wp_enqueue_script( 'jquery-blockui' );
        wp_enqueue_script( 'dokan_pro_admin' );

        if ( 'shop_order' === $post_type || 'toplevel_page_dokan' === get_current_screen()->id ) {
            wp_enqueue_style( 'dokan_pro_admin_style' );
        }

        $dokan_refund = dokan_get_refund_localize_data();
        $dokan_admin  = apply_filters(
            'dokan_admin_localize_param', [
                'ajaxurl'                 => admin_url( 'admin-ajax.php' ),
                'nonce'                   => wp_create_nonce( 'dokan-admin-nonce' ),
                'activating'              => __( 'Activating', 'dokan' ),
                'deactivating'            => __( 'Deactivating', 'dokan' ),
                'combine_commission_desc' => __( 'Amount you will get from sales in both percentage and fixed fee', 'dokan' ),
                'default_commission_desc' => __( 'It will override the default commission admin gets from each sales', 'dokan' ),
            ]
        );

        wp_localize_script( 'dokan_slider_admin', 'dokan_refund', $dokan_refund );
        wp_localize_script( 'dokan_pro_admin', 'dokan_admin', $dokan_admin );
    }

    /**
     * Initialize pro rest api class
     *
     * @param array $class_map
     *
     * @return array
     */
    public function rest_api_class_map( $class_map ) {
        return \WeDevs\DokanPro\REST\Manager::register_rest_routes( $class_map );
    }

    /**
     * Set plugin in pro mode
     *
     * @since 2.6
     *
     * @param bool $is_pro
     *
     * @return bool
     */
    public function set_as_pro( $is_pro ) {
        return true;
    }

    /**
     * Load Pro rewrite query vars
     *
     * @since 2.4
     *
     * @param array $query_vars
     *
     * @return array
     */
    public function load_query_var( $query_vars ) {
        $query_vars[] = 'coupons';
        $query_vars[] = 'reports';
        $query_vars[] = 'reviews';
        $query_vars[] = 'announcement';
        $query_vars[] = 'single-announcement';
        $query_vars[] = 'dokan-registration';

        return $query_vars;
    }

    /**
     * Register account migration endpoint on my-account page
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function account_migration_endpoint() {
        add_rewrite_endpoint( 'account-migration', EP_PAGES );
    }

    /**
     * Load account migration template
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function account_migration() {
        dokan_get_template_part( 'global/update-account', '', [ 'pro' => true ] );
    }

    /**
     * @param type $file
     *
     * @return type
     */
    public function dokan_registration_template( $file ) {
        if ( get_query_var( 'dokan-registration' ) && dokan_is_user_customer( get_current_user_id() ) && basename( $file ) === 'my-account.php' ) {
            $file = dokan_locate_template( 'global/dokan-registration.php', '', DOKAN_PRO_DIR . '/templates/', true );
        }

        return $file;
    }

    /**
     * Load dokan pro templates
     *
     * @since 2.5.2
     *
     * @return void
     * */
    public function load_pro_templates( $template_path, $template, $args ) {
        if ( isset( $args['pro'] ) && $args['pro'] ) {
            return $this->plugin_path() . '/templates';
        }

        return $template_path;
    }

    /**
     * Add Dokan Email classes in WC Email
     *
     * @since 2.6.6
     *
     * @param array $wc_emails
     *
     * @return $wc_emails
     */
    public function load_dokan_emails( $wc_emails ) {
        $wc_emails['Dokan_Email_Announcement']           = new \WeDevs\DokanPro\Emails\Announcement();
        $wc_emails['Dokan_Email_Updated_Product']        = new \WeDevs\DokanPro\Emails\UpdatedProduct();
        $wc_emails['Dokan_Email_Refund_Request']         = new \WeDevs\DokanPro\Emails\RefundRequest();
        $wc_emails['Dokan_Email_Refund_Vendor']          = new \WeDevs\DokanPro\Emails\RefundVendor();
        $wc_emails['Dokan_Email_Canceled_Refund_Vendor'] = new \WeDevs\DokanPro\Emails\CanceledRefundVendor();
        $wc_emails['Dokan_Email_Vendor_Enable']          = new \WeDevs\DokanPro\Emails\VendorEnable();
        $wc_emails['Dokan_Email_Vendor_Disable']         = new \WeDevs\DokanPro\Emails\VendorDisable();
        $wc_emails['Dokan_Email_Shipping_Status']        = new \WeDevs\DokanPro\Emails\ShippingStatus();

        return $wc_emails;
    }

    /**
     * Set template override directory for Dokan Emails
     *
     * @since 2.6.6
     *
     * @param array $dokan_emails
     *
     * @return $dokan_emails
     */
    public function set_email_template_directory( $dokan_emails ) {
        $dokan_pro_emails = [
            'announcement.php',
            'product-updated-pending.php',
            'refund_request.php',
            'refund-seller-mail.php',
            'refund-canceled-seller-mail.php',
            'vendor-disabled.php',
            'vendor-enabled.php',
            'shipping-status.php',
        ];

        return array_merge( $dokan_pro_emails, $dokan_emails );
    }

    /**
     * Register Dokan Email actions for WC
     *
     * @since 2.6.6
     *
     * @param array $actions
     *
     * @return $actions
     */
    public function register_email_actions( $actions ) {
        $actions[] = 'dokan_vendor_enabled';
        $actions[] = 'dokan_vendor_disabled';
        $actions[] = 'dokan_after_announcement_saved';
        $actions[] = 'dokan_rma_requested';
        $actions[] = 'dokan_refund_requested';
        $actions[] = 'dokan_refund_processed_notification';
        $actions[] = 'dokan_edited_product_pending_notification';
        $actions[] = 'dokan_order_shipping_status_tracking_notify';

        return $actions;
    }

    /**
     * Get plan id
     *
     * @since 2.8.4
     *
     * @return void
     */
    public function get_plan() {
        return $this->plan;
    }

    /**
     * List of Dokan Pro plans
     *
     * @since 3.0.0
     *
     * @return array
     */
    public function get_dokan_pro_plans() {
        return [
            [
                'name'        => 'starter',
                'title'       => __( 'Starter', 'dokan' ),
                'price_index' => 1,
            ],
            [
                'name'        => 'professional',
                'title'       => __( 'Professional', 'dokan' ),
                'price_index' => 2,
            ],
            [
                'name'        => 'business',
                'title'       => __( 'Business', 'dokan' ),
                'price_index' => 3,
            ],
            [
                'name'        => 'enterprise',
                'title'       => __( 'Enterprise', 'dokan' ),
                'price_index' => 4,
            ],
        ];
    }

    /**
     * Get plugin path
     *
     * @since 2.5.2
     *
     * @return void
     * */
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
     * Required all class files inside Pro
     *
     * @since 2.4
     *
     * @param string $class
     *
     * @return void
     */
    public function dokan_pro_autoload( $class ) {
        if ( stripos( $class, 'Dokan_Pro_' ) !== false ) {
            $class_name = str_replace( [ 'Dokan_Pro_', '_' ], [ '', '-' ], $class );
            $file_path  = DOKAN_PRO_CLASS . '/' . strtolower( $class_name ) . '.php';

            if ( file_exists( $file_path ) ) {
                require_once $file_path;
            }
        }
    }
}

/**
 * Load pro plugin for dokan
 *
 * @since 2.5.3
 *
 * @return \Dokan_Pro
 * */
function dokan_pro() {
    return Dokan_Pro::init();
}

dokan_pro();
