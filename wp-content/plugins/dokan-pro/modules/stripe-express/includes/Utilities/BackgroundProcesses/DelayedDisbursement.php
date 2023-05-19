<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Utilities\BackgroundProcesses;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Background_Process', false ) ) {
    include_once dirname( WC_PLUGIN_FILE ) . 'includes/abstracts/class-wc-background-process.php';
}

use WC_Background_Process;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Payment;

/**
 * Class for handling delayed disbursement.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Utilities\BackgroundProcesses
 */
class DelayedDisbursement extends WC_Background_Process {

    /**
     * Class constructor
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function __construct() {
        // Uses unique prefix per blog so each blog has separate queue.
        $this->prefix = 'wp_' . get_current_blog_id();
        $this->action = 'dokan_stripe_express_sync_delay_disbursement';

        parent::__construct();
    }

    /**
     * Dispatches updater.
     *
     * Updater will still run via cron job
     * if this fails for any reason.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function dispatch() {
        $dispatched = parent::dispatch();

        if ( is_wp_error( $dispatched ) ) {
            Helper::log(
                sprintf( 'Unable to dispatch Dokan Stripe Express delay disbursement sync: %s', $dispatched->get_error_message() ),
                'error'
            );
        }
    }

    /**
     * Handles cron healthcheck
     *
     * Restart the background process if not
     * already running and data exists in the queue.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function handle_cron_healthcheck() {
        // Background process already running.
        if ( $this->is_process_running() ) {
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
     * @since 3.6.1
     *
     * @return void
     */
    protected function schedule_event() {
        if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
            wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
        }
    }

    /**
     * Handles the task.
     *
     * @since 3.6.1
     *
     * @param object $args
     *
     * @return string|bool
     */
    protected function task( $args ) {
        $order_id = isset( $args['order_id'] ) ? $args['order_id'] : 0;
        $order    = wc_get_order( $order_id );

        if ( ! $order ) {
            return false;
        }

        // check payment gateway used was stripe express
        if ( $order->get_payment_method() !== Helper::get_gateway_id() ) {
            return false;
        }

        // Don't proceed if it's a suborder
        if ( ! empty( $order->get_parent_id() ) ) {
            return false;
        }

        // check if transaction id exists
        if ( empty( $order->get_transaction_id() ) ) {
            return false;
        }

        // check already disbursed or not
        if ( OrderMeta::get_transfer_id( $order ) ) {
            return false;
        }

        // check that payment disbursement method wasn't direct
        if ( 'DELAYED' !== OrderMeta::get_disburse_mode( $order ) ) {
            return false;
        }

        // check order status is processing or completed
        if ( ! $order->has_status( [ 'processing', 'completed' ] ) ) {
            return false;
        }

        try {
            // finally call api to disburse fund to vendor
            Payment::disburse( $order );
        } catch ( DokanException $e ) {
            Helper::log( "Delayed disbursement task error: {$e->get_message()}" );
            return false;
        }

        return false;
    }

    /**
     * Completes the task.
     *
     * @since 3.6.1
     *
     * @return void
     */
    protected function complete() {
        Helper::log( 'Task Delay Disbursement of vendor funds are completed.', 'Payout', 'info' );
        parent::complete();
    }
}
