<?php
namespace WeDevs\DokanPro\Modules\SellerBadge;

use WeDevs\Dokan\Traits\ChainableContainer;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

/**
 * Class for Seller Badge module integration.
 *
 * @since 3.7.14
 */
class Module {

    use ChainableContainer;

    /**
     * Cloning is forbidden.
     *
     * @since 3.7.14
     */
    public function __clone() {
        $message = ' Backtrace: ' . wp_debug_backtrace_summary();
        _doing_it_wrong( __METHOD__, $message . __( 'Cloning is forbidden.', 'dokan' ), DOKAN_PRO_PLUGIN_VERSION );
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 3.7.14
     */
    public function __wakeup() {
        $message = ' Backtrace: ' . wp_debug_backtrace_summary();
        _doing_it_wrong( __METHOD__, $message . __( 'Unserializing instances of this class is forbidden.', 'dokan' ), DOKAN_PRO_PLUGIN_VERSION );
    }

    /**
     * Class constructor.
     *
     * @since 3.7.14
     */
    public function __construct() {
        $this->define_constants();
        $this->initiate();

        add_filter( 'dokan_rest_api_class_map', [ $this, 'add_rest_controller' ] );
        add_filter( 'dokan_set_template_path', [ $this, 'load_templates' ], 20, 3 );

        // Activation and Deactivation hook
        add_action( 'dokan_activated_module_seller_badge', [ $this, 'activate' ] );
        add_action( 'dokan_deactivated_module_seller_badge', [ $this, 'deactivate' ] );

        // flush rewrite rules
        add_action( 'woocommerce_flush_rewrite_rules', [ $this, 'flush_rewrite_rules' ] );

        add_filter( 'dokan_widgets', [ $this, 'register_widgets' ] );

        add_action( 'init', [ $this, 'register_scripts' ] );
        add_action( 'init', [ $this, 'register_badge_events' ], 1 );
    }

    /**
     * Add module REST Controller
     *
     * @since 3.7.14
     *
     * @param array $class_map
     */
    public function add_rest_controller( $class_map ) {
        $class_map[ DOKAN_SELLER_BADGE_INCLUDES . '/REST/SellerBadgeController.php' ] = 'WeDevs\\DokanPro\\Modules\\SellerBadge\\REST\\SellerBadgeController';

        return $class_map;
    }

    /**
     * Module constants
     *
     * @since 3.7.14
     *
     * @return void
     */
    private function define_constants() {
        define( 'DOKAN_SELLER_BADGE_ASSETS_DIR', plugins_url( 'assets', __FILE__ ) );
        define( 'DOKAN_SELLER_BADGE_FILE', __FILE__ );
        define( 'DOKAN_SELLER_BADGE_PATH', dirname( DOKAN_SELLER_BADGE_FILE ) );
        define( 'DOKAN_SELLER_BADGE_INCLUDES', DOKAN_SELLER_BADGE_PATH . '/includes' );
        define( 'DOKAN_SELLER_BADGE_URL', plugins_url( '', DOKAN_SELLER_BADGE_FILE ) );
        define( 'DOKAN_SELLER_BADGE_ASSETS', DOKAN_SELLER_BADGE_URL . '/assets' );
        define( 'DOKAN_SELLER_BADGE_TEMPLATE_PATH', DOKAN_SELLER_BADGE_PATH . '/templates/' );
    }

    /**
     * Initiate all classes
     *
     * @since 3.7.14
     *
     * @return void
     */
    public function initiate() {
        $this->container['hooks'] = new Hooks();
        $this->container['cache'] = new Cache();
        $this->container['frontend_hooks'] = new Frontend\Hooks();

        if ( is_admin() ) {
            $this->container['admin_hooks'] = new Admin\Hooks();
        }
    }

    /**
     * Register Badge Event Hooks
     *
     * @since 3.7.14
     *
     * @return void
     */
    public function register_badge_events() {
        RegisterBadgeEvents::instance();
    }

    /**
     * Register seller badge scripts
     *
     * @since 3.7.14
     *
     * @return void
     */
    public function register_scripts() {
        // Use minified libraries if SCRIPT_DEBUG is turned off
        [ $suffix, $version ] = dokan_get_script_suffix_and_version();

        wp_register_script(
            'dokan-seller-badge-admin',
            DOKAN_SELLER_BADGE_ASSETS . '/js/dokan-seller-badge-admin' . $suffix . '.js',
            [ 'jquery', 'dokan-vue-vendor', 'dokan-vue-bootstrap' ],
            $version,
            true
        );

        wp_register_style(
            'dokan-seller-badge-admin',
            DOKAN_SELLER_BADGE_ASSETS . '/css/dokan-seller-badge-admin' . $suffix . '.css',
            [],
            $version,
            'all'
        );

        wp_register_script(
            'dokan-seller-badge-frontend',
            DOKAN_SELLER_BADGE_ASSETS . '/js/dokan-seller-badge-frontend' . $suffix . '.js',
            [ 'jquery', 'dokan-vue-vendor', 'dokan-vue-bootstrap' ],
            $version,
            true
        );

        wp_register_style(
            'dokan-seller-badge-frontend',
            DOKAN_SELLER_BADGE_ASSETS . '/css/dokan-seller-badge-frontend' . $suffix . '.css',
            [],
            $version,
            'all'
        );

        wp_localize_script(
            'dokan-seller-badge-admin',
            'DokanSellerBadgesAdmin',
            [
                'assetsUrl' => DOKAN_SELLER_BADGE_ASSETS,
            ]
        );
    }

    /**
     * Set template path for Request Quote
     *
     * @since 3.7.14
     *
     * @return string
     */
    public function load_templates( $template_path, $template, $args ) {
        if ( ( isset( $args['seller_badge_list_template'] ) && $args['seller_badge_list_template'] ) ) {
            return DOKAN_SELLER_BADGE_TEMPLATE_PATH;
        }

        return $template_path;
    }

    /**
     * Install all tables when module is activated.
     *
     * @since 3.7.14
     *
     * @return void
     */
    public function activate() {
        new Installer();
        $this->flush_rewrite_rules();
    }

    /**
     * Flush rewrite rules
     *
     * @since 3.7.14
     *
     * @return void
     */
    public function flush_rewrite_rules() {
        dokan()->rewrite->register_rule();
        flush_rewrite_rules( true );
    }

    /**
     * This method will be called during module deactivation
     *
     * @since 3.7.14
     */
    public function deactivate() {
        if ( function_exists( 'as_unschedule_all_actions' ) ) {
            $hook = 'dokan_seller_badge_daily_at_midnight_cron';
            as_unschedule_all_actions( $hook );
        }

        // flush rewrite rules
        $this->flush_rewrite_rules();
    }

    /**
     * Register widgets.
     *
     * @since 3.7.14
     *
     * @param array $widgets
     *
     * @return array
     */
    public function register_widgets( $widgets ) {
        $widgets['seller_badge'] = Widgets\SellerBadge::class;

        return $widgets;
    }
}
