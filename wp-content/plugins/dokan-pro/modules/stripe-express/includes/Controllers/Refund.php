<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Controllers;

use Exception;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Api\Transfer;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Api\Refund as StripeRefund;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Order as OrderProcessor;

defined( 'ABSPATH' ) || exit; // Exit if called directly

/**
 * Ajax controller class for Refunds.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Controllers
 */
class Refund {

    /**
     * Class constructor.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function __construct() {
        add_action( 'init', [ $this, 'hooks' ] );
    }

    /**
     * Registers necessary hooks.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function hooks() {
        add_action( 'dokan_refund_request_created', [ $this, 'process_refund' ] );
        add_action( 'dokan_refund_approve_before_insert', [ $this, 'process_vendor_withdraw_entry' ], 10, 3 );
        add_filter( 'dokan_refund_approve_vendor_refund_amount', [ $this, 'process_vendor_refund_amount' ], 10, 3 );
        add_filter( 'dokan_excluded_gateways_from_auto_process_api_refund', [ $this, 'exclude_from_auto_process_api_refund' ] );
    }

    /**
     * Processes dokan refund request.
     *
     * @since 3.6.1
     *
     * @param object $refund
     *
     * @return void
     */
    public function process_refund( $refund ) {
        if ( ! $refund instanceof \WeDevs\DokanPro\Refund\Refund ) {
            return;
        }

        if ( ! Helper::is_gateway_ready() ) {
            return;
        }

        // Check if refund is approvable
        if ( ! dokan_pro()->refund->is_approvable( $refund->get_order_id() ) ) {
            return Helper::log(
                sprintf(
                    'This refund is not eligible to be approved. Refund ID: %2$s. Order ID: %3$s',
                    $refund->get_id(),
                    $refund->get_order_id()
                )
            );
        }

        $order = wc_get_order( $refund->get_order_id() );

        // Return if $order is not instance of WC_Order
        if ( ! $order instanceof \WC_Order ) {
            return;
        }

        // Retrieves parent order as intent id is stored for parent order
        $parent_order = $order->get_parent_id() ? wc_get_order( $order->get_parent_id() ) : $order;

        // get intent id of the parent order
        $payment_intent_id = OrderMeta::get_payment_intent( $parent_order );
        if ( empty( $payment_intent_id ) ) {
            return;
        }

        /*
         * Handles manual refund.
         * Here the order is being approved
         * only if it is a manual refund.
         */
        if ( $refund->is_manual() ) {
            $refund = $refund->approve();

            if ( is_wp_error( $refund ) ) {
                Helper::log(
                    sprintf( 'Could not approve refund for request. Message: %s', $refund->get_error_message() ),
                    'Refund',
                    'error'
                );
            }
            return;
        }

        try {
            $refund_data = [
                'payment_intent' => $payment_intent_id,
                'amount'         => Helper::get_stripe_amount( $refund->get_refund_amount() ),
                'metadata'       => [
                    'order_id' => $order->get_id(),
                ],
            ];

            // Process refund on Stripe end
            $stripe_refund = StripeRefund::create( $refund_data );
        } catch ( DokanException $e ) {
            $error_message = sprintf(
                /* translators: 1) gateway title, 2) refund id, 3) order id, 4) error message */
                __( '[%1$s] Automatic refund failed on Stripe end. Manual refund required. Refund ID: %2$d, Order ID: %3$d, Error Message: %4$s', 'dokan' ),
                Helper::get_gateway_title(),
                $refund->get_id(),
                $refund->get_order_id(),
                $e->get_message()
            );
            Helper::log( $error_message, 'Refund', 'error' );
            $order->add_order_note( $error_message );
            return;
        }

        OrderProcessor::refund( $order, $refund, $stripe_refund );
    }

    /**
     * Updates gateway fee after refund.
     *
     * @since 3.6.1
     *
     * @param \WC_Order $order
     * @param float      $gateway_fee_refunded
     *
     * @return void
     */
    private function update_gateway_fee( $order, $gateway_fee_refunded ) {
        $gateway_fee = wc_format_decimal( OrderMeta::get_dokan_gateway_fee( $order ), 2 );
        $gateway_fee = $gateway_fee - $gateway_fee_refunded;

        /*
         * If there is no remaining amount,
         * then it is full refund and we are updating the processing fee to 0.
         * Because seller has already paid the processing fee from his account.
         * If we keep this then it will deducted twice.
         */
        if ( $order->get_remaining_refund_amount() <= 0 ) {
            $gateway_fee = 0;
        }

        OrderMeta::update_dokan_gateway_fee( $order, $gateway_fee );
        OrderMeta::save( $order );
    }

    /**
     * Sets vendor refund amount as Stripe refund amount.
     *
     * @since 3.6.1
     *
     * @param float   $amount
     * @param array  $args
     * @param object $refund
     *
     * @return float
     */
    public function process_vendor_refund_amount( $amount, $args, $refund ) {
        if ( empty( $args[ Helper::get_gateway_id() ] ) || empty( $args['transfer_id'] ) ) {
            return $amount;
        }

        $order = wc_get_order( $refund->get_order_id() );
        if ( ! $order instanceof \WC_Order ) {
            return $amount;
        }

        if ( Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return $amount;
        }

        if ( $amount <= 0 ) {
            $amount = $refund->get_refund_amount();
        }

        $gateway_fee_refunded = ! empty( $args['gateway_fee_refunded'] ) ? wc_format_decimal( $args['gateway_fee_refunded'] ) : 0;

        // Check gateway fee is refunded, if not we need to calculate this value manually
        if ( $gateway_fee_refunded === 0 ) {
            $order_total          = $order->get_total( 'edit' );
            $gateway_fee          = wc_format_decimal( OrderMeta::get_stripe_fee( $order ), 2 );
            $gateway_fee_refunded = $gateway_fee > 0 ? ( ( $gateway_fee / $order_total ) * $refund->get_refund_amount() ) : 0;
            $refund_amount        = $amount - $gateway_fee_refunded;
        } else {
            $refund_amount = $amount - $gateway_fee_refunded;
            $refund_amount = $refund_amount > 0 ? $refund_amount : 0;
        }

        // Check if balance transaction is greater than $refund_amount
        try {
            $stripe_transfer          = Transfer::get( $args['transfer_id'] );
            $total_retrievable_amount = ( $stripe_transfer->amount - $stripe_transfer->amount_reversed ) / 100;
            $total_retrievable_amount = $total_retrievable_amount > 0 ? $total_retrievable_amount : 0;
        } catch ( Exception $e ) {
            $total_retrievable_amount = 0;
        }

        // check if we are doing full refund, or this is the last amount refund for partial refund
        if (
            wc_format_decimal( $order->get_total_refunded(), 2 ) === wc_format_decimal( $order->get_total( 'edit' ), 2 ) ||
            $refund_amount > $total_retrievable_amount
        ) {
            $refund_amount = $total_retrievable_amount;
        }

        // Update gateway fees
        $this->update_gateway_fee( $order, $gateway_fee_refunded );

        return wc_format_decimal( $refund_amount, 2 );
    }

    /**
     * Withdraw entry for automatic refund as debit.
     *
     * @since 3.6.1
     *
     * @param object $refund
     * @param array  $args
     * @param float   $amount
     *
     * @return void
     */
    public function process_vendor_withdraw_entry( $refund, $args, $amount ) {
        $order = wc_get_order( $refund->get_order_id() );
        if ( ! $order instanceof \WC_Order ) {
            return;
        }

        if ( Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return;
        }

        if ( empty( $args[ Helper::get_gateway_id() ] ) || empty( $args['transfer_id'] ) ) {
            return;
        }

        try {
            $stripe_amount = Helper::get_stripe_amount( $amount );

            // An amount less than 1 cannot be reversed
            if ( $stripe_amount < 1 ) {
                throw new DokanException(
                    'invalid-reverse-transfer-amount',
                    __( 'The amount for reverse transfer must be greater than or equal to 1.', 'dokan' )
                );
            }

            $reverse_transfer = Transfer::reverse(
                $args['transfer_id'],
                [
                    'amount'   => $stripe_amount,
                    'metadata' => [
                        'order_id' => $order->get_id(),
                    ],
                ]
            );

            $order->add_order_note(
                sprintf(
                    /* translators: 1) gateway title, 2) stripe transfer id  */
                    __( '[%1$s] Reversed %2$s from vendor stripe account. ID: %3$s', 'dokan' ),
                    Helper::get_gateway_title(),
                    wc_price( $amount, [ 'currency' => $order->get_currency() ] ),
                    $reverse_transfer->id
                )
            );

            global $wpdb;

            $wpdb->insert(
                $wpdb->dokan_vendor_balance,
                [
                    'vendor_id'    => $refund->get_seller_id(),
                    'trn_id'       => $refund->get_order_id(),
                    'trn_type'     => 'dokan_refund',
                    'perticulars'  => maybe_serialize( $args ),
                    'debit'        => $amount,
                    'credit'       => 0,
                    'status'       => 'wc-completed', // @see: Dokan_Vendor->get_balance() method
                    'trn_date'     => current_time( 'mysql' ),
                    'balance_date' => current_time( 'mysql' ),
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
        } catch ( DokanException $e ) {
            $order->add_order_note(
                sprintf(
                    /* translators: 1) gateway title, 2) amount  */
                    __( '[%1$s] Could not reversed %2$s from vendor stripe account.', 'dokan' ),
                    Helper::get_gateway_title(),
                    wc_price( $refund->get_refund_amount(), [ 'currency' => $order->get_currency() ] )
                )
            );
        }
    }

    /**
     * Excludes Stripe Express gateway from auto processing API refund request.
     *
     * @since 3.6.1
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
