<?php

namespace WeDevs\DokanPro\Modules\Razorpay;

use WeDevs\Dokan\Traits\ChainableContainer;
use WeDevs\DokanPro\Modules\Razorpay\Cart\CartHandler;
use WeDevs\DokanPro\Modules\Razorpay\Order\OrderController;
use WeDevs\DokanPro\Modules\Razorpay\Order\OrderManager;
use WeDevs\DokanPro\Modules\Razorpay\Refund\Refund;
use WeDevs\DokanPro\Modules\Razorpay\PaymentMethods\Razorpay;
use WeDevs\DokanPro\Modules\Razorpay\Gateways\RegisterGateways;
use WeDevs\DokanPro\Modules\Razorpay\WithdrawMethods\RegisterWithdrawMethods;
use WeDevs\DokanPro\Modules\Razorpay\BackgroundProcess\DelayDisburseFund;
use WeDevs\DokanPro\Modules\Razorpay\WithdrawMethods\VendorLinkedAccount;
// use WeDevs\DokanPro\Modules\Razorpay\Webhook\WebhookHandler;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Module.
 *
 * @package WeDevs\Dokan\Gateways
 *
 * @see https://razorpay.com/docs/api For Related API's
 *
 * @since 3.5.0
 */
class Module {

    use ChainableContainer;

    /**
     * @var string
     */
    private static $class_name;

    /**
     * Manager constructor.
     *
     * @since 3.5.0
     * @return void
     */
    public function __construct() {
        $this->define_constants();
        $this->set_controllers();

        // Activation and Deactivation hook for this module.
        add_action( 'dokan_activated_module_razorpay', [ $this, 'activate' ], 10, 1 );
        add_action( 'dokan_deactivated_module_razorpay', [ $this, 'deactivate' ], 10, 1 );

        // Set default template directory for this module.
        add_filter( 'dokan_set_template_path', [ $this, 'load_razorpay_templates' ], 10, 3 );
    }

    /**
    * Get plugin path.
    *
    * @since 3.5.0
    *
    * @return string
    **/
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    /**
     * Define module constants.
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function define_constants() {
        define( 'DOKAN_RAZORPAY_FILE', __FILE__ );
        define( 'DOKAN_RAZORPAY_PATH', dirname( DOKAN_RAZORPAY_FILE ) );
        define( 'DOKAN_RAZORPAY_ASSETS', plugin_dir_url( DOKAN_RAZORPAY_FILE ) . 'assets/' );
        define( 'DOKAN_RAZORPAY_TEMPLATE_PATH', dirname( DOKAN_RAZORPAY_FILE ) . '/templates/' );
    }

    /**
     * Set controllers.
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function set_controllers() {
        $this->container['order_manager']     = new OrderManager();
        $this->container['register_gateways'] = new RegisterGateways();
        $this->container['linked_account']    = new VendorLinkedAccount();
        $this->container['withdraw_methods']  = new RegisterWithdrawMethods();
        $this->container['gateway_razorpay']  = new Razorpay();
        $this->container['cart_handler']      = new CartHandler();
        $this->container['order_controller']  = new OrderController();
        $this->container['refund']            = new Refund();
        $this->container['delay_disburse_bg'] = new DelayDisburseFund();
        // $this->container['webhook']        = new WebhookHandler();
    }

    /**
    * Load Dokan Razorpay templates.
    *
    * @since 3.5.0
    *
    * @return string
    */
    public function load_razorpay_templates( $template_path, $template, $args ) {
        if ( isset( $args['is_razorpay'] ) && $args['is_razorpay'] ) {
            return $this->plugin_path() . '/templates';
        }

        return $template_path;
    }

    /**
     * Activate Module.
     *
     * @since 3.5.0
     */
    public function activate( $instance ) {
        // $instance->container['webhook']->register_webhook();

        if ( ! wp_next_scheduled( 'dokan_razorpay_daily_schedule' ) ) {
            wp_schedule_event( time(), 'twicedaily', 'dokan_razorpay_daily_schedule' );
        }
    }

    /**
     * De-activate Module.
     *
     * @since 3.5.0
     */
    public function deactivate( $instance ) {
        // $instance->container['webhook']->deregister_webhook();

        // clear scheduled task
        wp_clear_scheduled_hook( 'dokan_razorpay_daily_schedule' );
    }
}
