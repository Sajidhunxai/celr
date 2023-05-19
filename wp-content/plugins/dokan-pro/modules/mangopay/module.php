<?php

namespace WeDevs\DokanPro\Modules\MangoPay;

use WeDevs\Dokan\Traits\ChainableContainer;

defined( 'ABSPATH' ) || exit;

/**
 * Main class for MangoPay module
 *
 * @since 3.5.0
 */
class Module {

    use ChainableContainer;

    /**
     * Class constructor
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function __construct() {
        $this->constants();
        $this->controllers();
        $this->hooks();
    }

    /**
     * Define module constants
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function constants() {
        define( 'DOKAN_MANGOPAY_FILE', __FILE__ );
        define( 'DOKAN_MANGOPAY_PATH', dirname( DOKAN_MANGOPAY_FILE ) );
        define( 'DOKAN_MANGOPAY_ASSETS', plugin_dir_url( DOKAN_MANGOPAY_FILE ) . 'assets/' );
        define( 'DOKAN_MANGOPAY_TEMPLATE_PATH', dirname( DOKAN_MANGOPAY_FILE ) . '/templates/' );
    }

    /**
     * Sets all controllers
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function controllers() {
        $this->container['admin']                   = new Admin\Manager();
        $this->container['shortcode']               = new Support\Shortcode();
        $this->container['frontend']                = new Frontend\Manager();
        $this->container['webhook']                 = new Support\WebhookHandler();
        $this->container['payment_method']          = new PaymentMethod\Manager();
        $this->container['withdraw_method']         = new WithdrawMethod\Manager();
        $this->container['orders']                  = new Orders\Manager();
        $this->container['cart']                    = new Cart\Manager();
        $this->container['checkout']                = new Checkout\Manager();
        $this->container['delay_disburse']          = new BackgroundProcess\DelayedDisbursement();
        $this->container['disburse_failed_payouts'] = new BackgroundProcess\FailedPayoutsDisbursement();
    }

    /**
     * Registers required hooks.
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function hooks() {
        // Activation and Deactivation hook
        add_action( 'dokan_activated_module_mangopay', array( $this, 'activate' ) );
        add_action( 'dokan_deactivated_module_mangopay', array( $this, 'deactivate' ) );
    }

    /**
     * Performs actions upon module activation
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function activate( $instance ) {
        $this->container['webhook']->register();

        if ( ! wp_next_scheduled( 'dokan_mangopay_daily_schedule' ) ) {
            wp_schedule_event( time(), 'daily', 'dokan_mangopay_daily_schedule' );
        }
    }

    /**
     * Performs actions upon module deactivation
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function deactivate( $instance ) {
        $this->container['webhook']->deregister();

        // clear scheduled task
        wp_clear_scheduled_hook( 'dokan_mangopay_daily_schedule' );
    }
}
