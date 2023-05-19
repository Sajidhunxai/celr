<?php

namespace WeDevs\DokanPro\Modules\Razorpay\Order;

use WeDevs\Dokan\Cache;
use WP_Error;
use WC_Order_Query;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\Razorpay\Helper;
use WeDevs\DokanPro\Modules\Razorpay\BackgroundProcess\DelayDisburseFund;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class OrderController.
 *
 * @package WeDevs\Dokan\Gateways\Razorpay
 *
 * @since 3.5.0
 */
class OrderController {
    /**
     * Handle transfer related works for an order.
     */
    use Transferable;

    /**
     * Necessary Hooks Related to Order.
     *
     * @since 3.5.0
     */
    public function __construct() {
        // Capture Razorpay Payment
        add_action( 'woocommerce_api_' . Helper::get_gateway_id(), [ $this, 'capture_payment' ], 5 );

        add_action( 'woocommerce_order_status_changed', [ $this, 'order_status_changed' ], 10, 3 );
        add_action( 'dokan_razorpay_daily_schedule', [ $this, 'disburse_delayed_payment' ], 10 );
    }

    /**
     * Handles if Order status changed to completed.
     *
     * @since 3.5.0
     *
     * @param int    $order_id
     * @param string $old_status
     * @param string $new_status
     *
     * @return void
     */
    public function order_status_changed( $order_id, $old_status, $new_status ) {
        // check if order status is completedd
        if ( 'completed' !== $new_status ) {
            return;
        }

        // get order
        $order = wc_get_order( $order_id );

        // check if order is a valid WC_Order instance
        if ( ! $order ) {
            return;
        }

        // check payment gateway used was dokan razorpay
        if ( $order->get_payment_method() !== Helper::get_gateway_id() ) {
            return;
        }

        // check that payment disbursement method wasn't direct
        if ( 'ON_ORDER_COMPLETE' !== $order->get_meta( '_dokan_razorpay_payment_disbursement_mode' ) ) {
            return;
        }

        // check already disbursed or not
        if ( 'yes' === $order->get_meta( '_dokan_razorpay_payment_withdraw_balance_added' ) ) {
            return;
        }

        // finally call api to disburse fund to vendor
        OrderManager::_disburse_payment( $order );
    }

    /**
     * This method will add queue for to be disburse payments.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function disburse_delayed_payment() {
        $time_now = Helper::get_on_hold_until_time( true );

        add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', [ $this, 'handle_custom_query_var' ], 10, 2 );
        $query = new WC_Order_Query(
            [
                'dokan_razorpay_delayed_disbursement' => true,
                'date_created'  => '<=' . $time_now->getTimestamp(),
                'status'        => [ 'wc-processing', 'wc-completed' ],
                'type'          => 'shop_order',
                'limit'         => -1,
                'return'        => 'ids',
            ]
        );
        $orders = $query->get_orders();
        remove_filter( 'woocommerce_order_data_store_cpt_get_orders_query', [ $this, 'handle_custom_query_var' ], 10 );

        $bg_class = dokan_pro()->module->razorpay->delay_disburse_bg;
        if ( ! $bg_class instanceof DelayDisburseFund ) {
            return;
        }

        foreach ( $orders as $order_id ) {
            $bg_class->push_to_queue( [ 'order_id' => $order_id ] );
        }

        $bg_class->save()->dispatch();
    }

    /**
     * This method will add metadata param.
     *
     * @since 3.5.0
     *
     * @param $query
     * @param $query_vars
     *
     * @return mixed
     */
    public function handle_custom_query_var( $query, $query_vars ) {
        if ( ! empty( $query_vars['dokan_razorpay_delayed_disbursement'] ) ) {
            $query['meta_query'][] = [
                'key'       => '_dokan_razorpay_payment_disbursement_mode',
                'value'     => 'DELAYED',
                'compare'   => '=',
            ];
            $query['meta_query'][] = [
                'key'       => '_dokan_razorpay_payment_withdraw_balance_added',
                'value'     => 'yes',
                'compare'   => '!=',
            ];
        }

        return $query;
    }

    /**
     * Capture Razorpay Payment.
     *
     * Takes payment If the order is valid and razorpay payment is complete.
     *
     * @since 3.5.0
     *
     * @return void
     **/
    public function capture_payment() {
        // Check if Razorpay payment gateway is disabled
        if ( ! Helper::is_enabled() ) {
            return;
        }

        // Stop for webhook event here. It will be processed seperately.
        if ( isset( $_GET ) && 0 === count( $_GET ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            return;
        }

        // Validate the Order getting from request
        $order = dokan_pro()->module->razorpay->cart_handler->get_order_from_request();
        if ( ! $order instanceof \WC_Order ) {
            return;
        }

        // Return if payment gateway is not Razorpay
        if ( $order->get_payment_method() !== Helper::get_gateway_id() ) {
            return;
        }

        try {
            // Handle Nonce Verification
            if ( ! isset( $_POST['dokan_razorpay_checkout_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['dokan_razorpay_checkout_nonce'] ), 'dokan_razorpay_pay' ) ) {
                throw new DokanException( 'invalid-razorpay-payment-nonce', __( 'Nonce verification failed. Are you cheating?', 'dokan' ) );
            }

            // Check if payment is called from razorpay.
            $is_cancelled = isset( $_GET['cancel_order'] ) ? (bool) sanitize_text_field( wp_unslash( $_GET['cancel_order'] ) ) : false;
            if ( $is_cancelled ) {
                OrderManager::update_order_status( $order, true );
            }

            // If the order has already been paid, redirect user to return url
            if ( false === $order->needs_payment() ) {
                Helper::redirect_user_to_return_url( $order );
            }

            // Get razorpay payment from request and validate
            $razorpay_order_id   = isset( $_GET['razorpay_order_id'] ) ? sanitize_text_field( wp_unslash( $_GET['razorpay_order_id'] ) ) : null;
            $razorpay_payment_id = isset( $_GET['razorpay_payment_id'] ) ? sanitize_text_field( wp_unslash( $_GET['razorpay_payment_id'] ) ) : null;

            // Verify Payment Signature to ensure payment is from Razorpay
            $is_payment_verified = OrderValidator::verify_payment_signature( $order->get_id(), $_GET );
            if ( ! $is_payment_verified ) {
                throw new DokanException( 'invalid-razorpay-payment-signature', __( 'Invalid razorpay payment. Please try again.', 'dokan' ) );
            }

            // Fetch payment from razorpay
            $api              = Helper::init_razorpay_api();
            $razorpay_payment = $api->payment->fetch( $razorpay_payment_id );
            if ( empty( $razorpay_payment ) ) {
                throw new DokanException( 'missing-razorpay-payment', __( 'Invalid razorpay payment. Please try again.', 'dokan' ) );
            }

            // If payment is failed, update order status as failed and exit.
            if ( ! empty( $razorpay_payment ) && 'failed' === $razorpay_payment->status ) {
                throw new DokanException( 'razorpay-payment-failed', __( 'Payment failed. Please try again.', 'dokan' ) );
            }

            // Store captured payment id in order meta.
            $order->update_meta_data( '_dokan_razorpay_payment_capture_id', $razorpay_payment->id );
            $order->save_meta_data();

            /* translators: 1: Razorpay generated Order ID  */
            $order->add_order_note( sprintf( __( 'Razorpay Transaction ID: %s.', 'dokan' ), $razorpay_order_id ) );

            // Update Order status with razorpay payment id
            OrderManager::update_order_status( $order, false, $razorpay_payment->id );

            // Process vendor payments and do transfer related works.
            $this->process_vendor_payments( $order, $razorpay_payment );
        } catch ( \Exception $e ) {
            $order->update_status( 'failed', __( 'Payment failed.', 'dokan' ) );

            // translators: 1: error message
            dokan_log( sprintf( __( '[Dokan Razorpay] Invalid Payment: %s', 'dokan' ), $e->getMessage() ) );
        }

        // Finally redirect to return url
        Helper::redirect_user_to_return_url( $order );
    }

    /**
     * Process vendor payments.
     *
     * @since 3.5.0
     *
     * @param \WC_Order $order
     * @param object    $payment_data Razorpay payment data
     *
     * @return void
     * @throws DokanException
     */
    public function process_vendor_payments( $order, $razorpay_payment ) {
        // If it's come here, there must be a payment id.
        if ( empty( $razorpay_payment->id ) ) {
            throw new DokanException( 'missing-razorpay-payment', __( 'Invalid razorpay payment. Please try again.', 'dokan' ) );
        }

        $razorpay_payment_id = $razorpay_payment->id;
        $all_orders          = OrderManager::get_all_orders_to_be_processed( $order );

        if ( ! $all_orders ) {
            throw new DokanException( 'dokan_no_order_found', __( 'No orders found to be processed!', 'dokan' ) );
        }

        // Do transfer related works.
        $all_withdraws = [];
        $currency      = $razorpay_payment->currency;
        $order_total   = $order->get_total();
        $razorpay_fee  = wc_format_decimal( Helper::format_balance( $razorpay_payment->fee ), 2 );

        // Add gateway fee to order meta.
        $order->update_meta_data( 'dokan_razorpay_gateway_fee', $razorpay_fee );
        $order->update_meta_data( 'dokan_gateway_fee', $razorpay_fee );

        /* translators: 1: Gateway fee */
        $order->add_order_note( sprintf( __( 'Payment gateway processing fee %s', 'dokan' ), wc_price( $razorpay_fee, [ 'currency' => $currency ] ) ) );

        // Process all orders and make transfer one by one.
        foreach ( $all_orders as $tmp_order ) {
            if ( ! $tmp_order instanceof \WC_Order ) {
                continue;
            }

            $tmp_order_id        = $tmp_order->get_id();
            $vendor_id           = dokan_get_seller_id_by_order( $tmp_order_id );
            $vendor_raw_earning  = dokan()->commission->get_earning_by_order( $tmp_order, 'seller' );
            $connected_vendor_id = Helper::get_seller_account_id( $vendor_id );
            $tmp_order_total     = $tmp_order->get_total();

            if ( 0 === $tmp_order_total ) {
                /* translators: 1: Order Number */
                $tmp_order->add_order_note( sprintf( __( 'Order %s payment completed.', 'dokan' ), $tmp_order->get_order_number() ) );
                continue;
            }

            $razorpay_suborder_fee = Helper::calculate_processing_fee_for_suborder( $razorpay_fee, $tmp_order, $order );

            if ( Helper::seller_pays_the_processing_fee() && ! empty( $order_total ) && ! empty( $tmp_order_total ) && ! empty( $razorpay_fee ) ) {
                $vendor_raw_earning = $vendor_raw_earning - $razorpay_suborder_fee;
            }

            // Update vendor earning after calculating the gateway fee if seller pays processing fee.
            $vendor_earning = wc_format_decimal( $vendor_raw_earning, 2 );
            if ( false === OrderValidator::is_order_transferable( $tmp_order, $connected_vendor_id, $vendor_earning ) ) {
                continue;
            }

            // Update Gateway fee and some other meta data
            $tmp_order->update_meta_data( '_dokan_razorpay_payment_disbursement_mode', Helper::get_disbursement_mode() );
            $tmp_order->update_meta_data( '_dokan_razorpay_payment_charge_captured', 'yes' );
            $tmp_order->update_meta_data( '_dokan_razorpay_payment_processing_fee', wc_format_decimal( $razorpay_suborder_fee, 2 ) );
            $tmp_order->update_meta_data( 'dokan_gateway_fee', wc_format_decimal( $razorpay_suborder_fee, 2 ) );
            $tmp_order->update_meta_data( 'paid_with_dokan_razorpay', true );
            $tmp_order->update_meta_data( 'dokan_gateway_fee_paid_by', Helper::seller_pays_the_processing_fee() ? 'seller' : 'admin' );

            // Generate Razorpay transfer Data
            $transfers = $this->make_transfer_unit_data(
                [
                    'connected_vendor_id'     => $connected_vendor_id,
                    'vendor_earning'          => $vendor_earning,
                    'order'                   => $tmp_order,
                    'parent_order'            => $order,
                    'razorpay_fee_for_vendor' => Helper::seller_pays_the_processing_fee() ? $razorpay_suborder_fee : 0,
                ]
            );

            // Save the order meta data.
            $tmp_order->save_meta_data();

            // Transfer amount to vendor razorpay account.
            $is_transferred = $this->transfer( $razorpay_payment_id, $transfers, $tmp_order, $vendor_earning );
            if ( ! $is_transferred ) {
                continue;
            }

            global $wpdb;

            // Update Net amount in dokan orders.
            $updated = $wpdb->update(
                $wpdb->dokan_orders,
                [ 'net_amount' => (float) $vendor_earning ],
                [ 'order_id' => $tmp_order->get_id() ],
                [ '%f' ],
                [ '%d' ]
            );
            if ( false === $updated ) {
                return new WP_Error( 'update_dokan_order_error', sprintf( '[process_vendor_payments] Error while updating order table data: %1$s', $wpdb->last_error ) );
            }

            // Update vendor balance debit entry and vendor balance threshold date.
            $balance_date = Helper::get_balance_date();
            $wpdb->update(
                $wpdb->dokan_vendor_balance,
                [
                    'debit' => (float) $vendor_earning,
                    'balance_date' => $balance_date,
                ],
                [
                    'trn_id' => $tmp_order->get_id(),
                    'trn_type' => 'dokan_orders',
                ],
                [ '%f', '%s' ],
                [ '%d', '%s' ]
            );
            if ( false === $updated ) {
                return new WP_Error( 'update_dokan_vendor_balance_error', sprintf( '[process_vendor_payments] Error while updating vendor balance table data: %1$s', $wpdb->last_error ) );
            }

            // Insert a vendor withdraw credit entry with vendor balance threshold date.
            $inserted = $wpdb->insert(
                $wpdb->dokan_vendor_balance,
                [
                    'vendor_id'     => $vendor_id,
                    'trn_id'        => $tmp_order->get_id(),
                    'trn_type'      => 'dokan_withdraw',
                    'perticulars'   => 'Paid Via ' . Helper::get_gateway_title(),
                    'debit'         => 0,
                    'credit'        => (float) $vendor_earning,
                    'status'        => 'approved',
                    'trn_date'      => dokan_current_datetime()->format( 'Y-m-d h:i:s' ),
                    'balance_date'  => $balance_date,
                ],
                [
                    '%d',
					'%d',
					'%s',
					'%s',
					'%f',
					'%f',
					'%s',
					'%s',
					'%s',
                ]
            );

            if ( false === $inserted ) {
                return new WP_Error( 'insert_dokan_vendor_balance_error', sprintf( '[process_vendor_payments] Error while inserting vendor balance table credit entry data: %1$s', $wpdb->last_error ) );
            }

            // clear cache
            // remove cache for seller earning
            $cache_key = "get_earning_from_order_table_{$tmp_order->get_id()}_seller";
            Cache::delete( $cache_key );

            // remove cache for admin earning
            $cache_key = "get_earning_from_order_table_{$tmp_order->get_id()}_admin";
            Cache::delete( $cache_key );

            $withdraw_data = [
                'user_id'  => $vendor_id,
                'amount'   => $vendor_earning,
                'order_id' => $tmp_order_id,
            ];

            $all_withdraws[] = $withdraw_data;
        }

        $order->save_meta_data();

        // Finally Process vendor withdraws and update balance.
        OrderManager::handle_vendor_balance( $all_withdraws );
    }
}
