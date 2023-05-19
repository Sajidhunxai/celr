<?php

namespace WeDevs\DokanPro\Modules\FollowStore;

use DokanFollowStoreRestController;

final class Module {

    /**
     * Module version
     *
     * @since 1.0.0
     *
     * @var string
     */
    public $version = '1.0.0';

    /**
     * Class constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->load_hooks();
        $this->instances();
    }

    /**
     * Module constants
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function define_constants() {
        define( 'DOKAN_FOLLOW_STORE_VERSION', $this->version );
        define( 'DOKAN_FOLLOW_STORE_FILE', __FILE__ );
        define( 'DOKAN_FOLLOW_STORE_PATH', dirname( DOKAN_FOLLOW_STORE_FILE ) );
        define( 'DOKAN_FOLLOW_STORE_INCLUDES', DOKAN_FOLLOW_STORE_PATH . '/includes' );
        define( 'DOKAN_FOLLOW_STORE_URL', plugins_url( '', DOKAN_FOLLOW_STORE_FILE ) );
        define( 'DOKAN_FOLLOW_STORE_ASSETS', DOKAN_FOLLOW_STORE_URL . '/assets' );
        define( 'DOKAN_FOLLOW_STORE_VIEWS', DOKAN_FOLLOW_STORE_PATH . '/views' );
    }

    /**
     * Include module related files
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function includes() {
        require_once DOKAN_FOLLOW_STORE_INCLUDES . '/functions.php';
        require_once DOKAN_FOLLOW_STORE_INCLUDES . '/class-dokan-follow-store-install.php';
        require_once DOKAN_FOLLOW_STORE_INCLUDES . '/class-dokan-follow-store-scripts.php';
        require_once DOKAN_FOLLOW_STORE_INCLUDES . '/class-dokan-follow-store-ajax.php';
        require_once DOKAN_FOLLOW_STORE_INCLUDES . '/class-dokan-follow-store-follow-button.php';
        require_once DOKAN_FOLLOW_STORE_INCLUDES . '/class-dokan-follow-store-my-account.php';
        require_once DOKAN_FOLLOW_STORE_INCLUDES . '/class-dokan-follow-store-vendor-dashboard.php';
        require_once DOKAN_FOLLOW_STORE_INCLUDES . '/class-dokan-follow-store-cron.php';
        require_once DOKAN_FOLLOW_STORE_INCLUDES . '/class-dokan-follow-store-email-loader.php';
        require_once DOKAN_FOLLOW_STORE_INCLUDES . '/FollowStoreCache.php';
    }

    /**
     * Create module related class instances
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function instances() {
        new \Dokan_Follow_Store_Install();
        new \Dokan_Follow_Store_Scripts();
        new \Dokan_Follow_Store_Ajax();
        new \Dokan_Follow_Store_Follow_Button();
        new \Dokan_Follow_Store_My_Account();
        new \Dokan_Follow_Store_Vendor_Dashboard();
        new \Dokan_Follow_Store_Cron();
        new \Dokan_Follow_Store_Email_Loader();
        new FollowStoreCache();
    }

    /**
     * Load hooks for this modules
     *
     * @since 3.2.1
     *
     * @return void
     */
    public function load_hooks() {
        // Activation and Deactivation hook
        add_action( 'dokan_activated_module_follow_store', [ $this, 'activate' ] );
        add_action( 'dokan_deactivated_module_follow_store', [ $this, 'deactivate' ] );

        // flush rewrite rules
        add_action( 'woocommerce_flush_rewrite_rules', [ $this, 'flush_rewrite_rules' ] );
        add_filter( 'dokan_rest_api_class_map', [ $this, 'rest_api_class_map' ] );
        add_action( 'plugins_loaded', [ $this, 'load_background_class' ] );
    }

    /**
     * Plugin activation hook
     *
     * @since 3.7.17
     *
     * @return void
     */
    public function activate() {
        $this->flush_rewrite_rules();
    }

    /**
     * Plugin deactivation hook
     *
     * @since 3.7.17
     *
     * @return void
     */
    public function deactivate() {
        $this->flush_rewrite_rules();
    }

    /**
     * Flush rewrite rules
     *
     * @since 3.7.17
     *
     * @return void
     */
    public function flush_rewrite_rules() {
        dokan()->rewrite->register_rule();
        flush_rewrite_rules( true );
    }

    public function rest_api_class_map( $class_map ) {
        $class_map[ DOKAN_FOLLOW_STORE_PATH . '/includes/class-dokan-follow-store-rest-controller.php' ] = DokanFollowStoreRestController::class;

        return $class_map;
    }

    public function load_background_class() {
        $processor_file = DOKAN_FOLLOW_STORE_INCLUDES . '/class-dokan-follow-store-send-updates.php';
        if ( ! class_exists( 'Dokan_Follow_Store_Send_Updates' ) ) {
            require_once $processor_file;
        }

        global $dokan_follow_store_updates_bg;
        $dokan_follow_store_updates_bg = new \Dokan_Follow_Store_Send_Updates();
    }
}
