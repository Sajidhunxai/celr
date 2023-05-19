<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Orders;

use WeDevs\DokanPro\Modules\MangoPay\Support\Meta;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Order;
use WeDevs\DokanPro\Modules\MangoPay\Processor\PayIn;
use WeDevs\DokanPro\Modules\MangoPay\Support\Settings;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Transfer;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Refunds handler class
 *
 * @since 3.5.0
 */
class Refund {

    /**
     * Refund constructor.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * Registers all the hooks
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function hooks() {
        add_action( 'dokan_refund_request_created', array( $this, 'process_refund' ) );
        add_filter( 'dokan_refund_approve_vendor_refund_amount', array( $this, 'vendor_refund_amount' ), 10, 3 );
        add_action( 'dokan_refund_approve_before_insert', array( $this, 'add_vendor_withdraw_entry' ), 10, 3 );
        add_filter( 'dokan_excluded_gateways_from_auto_process_api_refund', array( $this, 'exclude_from_auto_process_api_refund' ) );
    }

    /**
     * Process refund request.
     *
     * @since 3.5.0
     *
     * @param object $refund
     *
     * @return void
     * @throws \Exception
     */
    public function process_refund( $refund ) {
        // Get code editor suggestion on refund object
        if ( ! $refund instanceof \WeDevs\DokanPro\Refund\Refund ) {
            return;
        }

        // Check if gateway is ready
        if ( ! Settings::is_gateway_enabled() ) {
            return;
        }

        // Check if refund is approvable
        if ( ! dokan_pro()->refund->is_approvable( $refund->get_order_id() ) ) {
            Helper::log( sprintf( '%1$s: This refund is not eligible to be approved. Refund ID: %2$s. Order ID: %3$s', Helper::get_gateway_title(), $refund->get_id(), $refund->get_order_id() ) );
            return;
        }

        $order = wc_get_order( $refund->get_order_id() );

        // Return if $order is not instance of WC_Order
        if ( ! $order instanceof \WC_Order ) {
            return;
        }

        // Return if not paid with dokan mangopay payment gateway
        if ( Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return;
        }

        /*
         * Handles manual refund.
         * Here the order is being approved only if it is a manual refund.
         */
        if ( $refund->is_manual() ) {
            $refund = $refund->approve();

            if ( is_wp_error( $refund ) ) {
                Helper::log( sprintf( 'Could not approve refund for request. Message: %s', $refund->get_error_message() ), 'Refund', 'error' );
            }
            return;
        }

        $parent_order = ! empty( $order->get_parent_id() ) ? wc_get_order( $order->get_parent_id() ) : $order;

        // Check if transaction id exists
        $transaction_id = Meta::get_transaction_id( $parent_order );
        if ( ! $transaction_id ) {
            return $order->add_order_note(
                sprintf(
                    /* translators: 1) Gateway Title 2) Refund ID 3) Order ID */
                    __( '[%1$s] Error: Automatic refund is not possible for this order. Reason: No MangoPay transaction id is found. Refund id: %2$s, Order ID: %3$s', 'dokan' ),
                    Helper::get_gateway_title(), $refund->get_id(), $refund->get_order_id()
                )
            );
        }

        // Check if the amount has already been transfered to vendor
        $transfer_id = Meta::get_transfer_id( $order->get_id() );
        if ( ! empty( $transfer_id ) ) {
            if ( $order->get_total() !== $refund->get_refund_amount() ) {
                $order->add_order_note(
                    sprintf(
                        // translators: 1) gateway title
                        __( '%1$s: Automatic refund is not possible for this order. Transfer refund does not allow partial refund.', 'dokan' ),
                        Helper::get_gateway_title()
                    )
                );

                return $refund->cancel();
            }

            $mangopay_refund = Transfer::refund(
                $order->get_id(),
                $transfer_id,
                $order->get_customer_id(),
                $refund->get_refund_reason()
            );
        } else {
            // If succeeded transaction id exists, use it as the transaction id
            $succedded_transaction_id = Meta::get_succeeded_transaction_id( $parent_order );
            if ( ! empty( $succedded_transaction_id ) ) {
                $transaction_id = $succedded_transaction_id;
            }

            // Attempt to refund
            $mangopay_refund = PayIn::refund(
                $order->get_id(),
                $transaction_id,
                $order->get_customer_id(),
                $refund->get_refund_amount(),
                $order->get_currency(),
                $refund->get_refund_reason()
            );
        }

        if ( ! isset( $mangopay_refund->Status ) || 'SUCCEEDED' !== $mangopay_refund->Status ) {
            Helper::log( 'Refund Failed: ' . print_r( $mangopay_refund, true ) );

            $order->add_order_note(
                sprintf(
                    // translators: 1) gateway title
                    __( '%1$s: Automatic refund failed.', 'dokan' ),
                    Helper::get_gateway_title()
                )
            );

            return $refund->cancel();
        }

        Order::process_refund( $order, $refund, $mangopay_refund );
    }

    /**
     * Withdraw entry for automatic refund as debit
     *
     * @param object $refund
     * @param array  $args
     * @param float  $vendor_refund
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function add_vendor_withdraw_entry( $refund, $args, $vendor_refund ) {
        $order = wc_get_order( $refund->get_order_id() );

        // return if $order is not instance of WC_Order
        if ( ! $order instanceof \WC_Order ) {
            return;
        }

        // return if not paid with dokan paypal marketplace payment gateway
        if ( Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return;
        }

        global $wpdb;

        $wpdb->insert(
            $wpdb->dokan_vendor_balance,
            array(
                'vendor_id'    => $refund->get_seller_id(),
                'trn_id'       => $refund->get_order_id(),
                'trn_type'     => 'dokan_refund',
                'perticulars'  => maybe_serialize( $args ),
                'debit'        => $vendor_refund,
                'credit'       => 0,
                'status'       => 'wc-completed', // @see: Dokan_Vendor->get_balance() method
                'trn_date'     => current_time( 'mysql' ),
                'balance_date' => current_time( 'mysql' ),
            ),
            array(
                '%d',
                '%d',
                '%s',
                '%s',
                '%f',
                '%f',
                '%s',
                '%s',
                '%s',
            )
        );
    }

    /**
     * Set vendor refund amount as Mangopay refund amount
     *
     * @param float  $amount
     * @param array  $args
     * @param object $refund
     *
     * @since 3.5.0
     *
     * @return float
     */
    public function vendor_refund_amount( $amount, $args, $refund ) {
        if ( ! empty( $args['dokan_mangopay'] ) && ! empty( $args['net_refund_amount'] ) ) {
            return wc_format_decimal( $args['net_refund_amount'] );
        }

        return $amount;
    }

    /**
     * Excludes Mangopay gateway from auto processing API refund request.
     *
     * @since 3.5.0
     *
     * @param array $gateways
     *
     * @return array
     */
    public function exclude_from_auto_process_api_refund( $gateways ) {
        $gateways[ Helper::get_gateway_id() ] = Helper::get_gateway_title();
        return $gateways;
    }
}
