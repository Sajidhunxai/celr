<?php

namespace WeDevs\DokanPro\Modules\Razorpay\BackgroundProcess;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Background_Process', false ) ) {
    include_once dirname( WC_PLUGIN_FILE ) . 'includes/abstracts/class-wc-background-process.php';
}

use WC_Background_Process;
use WeDevs\DokanPro\Modules\Razorpay\Helper;
use WeDevs\DokanPro\Modules\Razorpay\Order\OrderManager;

class DelayDisburseFund extends WC_Background_Process {
    /**
     * Initiate new background process.
     */
    public function __construct() {
        // Uses unique prefix per blog so each blog has separate queue.
        $this->prefix = 'wp_' . get_current_blog_id();
        $this->action = 'dokan_razorpay_sync_delay_disbursement';

        parent::__construct();
    }

    /**
     * Dispatch updater.
     *
     * Updater will still run via cron job if this fails for any reason.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function dispatch() {
        $dispatched = parent::dispatch();

        if ( is_wp_error( $dispatched ) ) {
            $dispatched = (object) $dispatched; // convert to object for removing warning

            // translators: 1: error message
            dokan_log( sprintf( 'Unable to dispatch Dokan Vendor Zone Data Sync: %s', $dispatched->get_error_message() ), 'error' );
        }
    }

    /**
     * Handle cron healthcheck.
     *
     * Restart the background process if not already running
     * and data exists in the queue.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function handle_cron_healthcheck() {
        if ( $this->is_process_running() ) {
            // Background process already running.
            return;
        }

        if ( $this->is_queue_empty() ) {
            // No data to process.
            $this->clear_scheduled_event();
            return;
        }

        $this->handle();
    }

    /**
     * Schedule fallback event.
     *
     * @since 3.5.0
     *
     * @return void
     */
    protected function schedule_event() {
        if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
            wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
        }
    }

    /**
     * Task.
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @since 3.5.0
     *
     * @param object $vendor Update callback function.
     *
     * @return string|bool
     */
    protected function task( $args ) {
        $order_id = isset( $args['order_id'] ) ? $args['order_id'] : 0;
        $order    = wc_get_order( $order_id );

        if ( ! $order ) {
            return false;
        }

        // check payment gateway used was dokan razorpay
        if ( $order->get_payment_method() !== Helper::get_gateway_id() ) {
            return false;
        }

        // check already disbursed or not
        if ( 'yes' === $order->get_meta( '_dokan_razorpay_payment_withdraw_balance_added' ) ) {
            return false;
        }

        // check that payment disbursement method wasn't direct
        if ( 'DELAYED' !== $order->get_meta( '_dokan_razorpay_payment_disbursement_mode' ) ) {
            return false;
        }

        // check order status is processing or completed
        if ( ! $order->has_status( [ 'processing', 'completed' ] ) ) {
            return false;
        }

        // finally call api to disburse fund to vendor
        OrderManager::_disburse_payment( $order );

        return false;
    }

    /**
     * Complete.
     *
     * Override if applicable, but ensure that the below actions are
     * performed, or, call parent::complete().
     *
     * @since 3.5.0
     *
     * @return void
     */
    protected function complete() {
        dokan_log( 'Task Delay Disbursement of vendor funds are completed.', 'info' );
        parent::complete();
    }
}
