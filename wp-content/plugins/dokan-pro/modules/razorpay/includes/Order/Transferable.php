<?php

namespace WeDevs\DokanPro\Modules\Razorpay\Order;

use WeDevs\DokanPro\Modules\Razorpay\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Trait Transferable.
 *
 * Handles Tansfer Related works.
 *
 * @since 3.5.0
 */
trait Transferable {
    /**
     * Generate Transfer Unit Data to transfer amount to vendors.
     *
     * @see https://razorpay.com/docs/api/route/#create-transfers-from-payments
     *
     * @since 3.5.0
     *
     * @param array $data
     *
     * @return array
     */
    public function make_transfer_unit_data( $data = [] ) {
        $defaults = [
            'connected_vendor_id'     => '',
            'vendor_earning'          => 0,
            'order'                   => null,
            'parent_order'            => null,
            'razorpay_fee_for_vendor' => 0,
        ];

        $data         = wp_parse_args( $data, $defaults );
        $order        = $data['order'];
        $parent_order = $data['parent_order'];
        $currency     = $order->get_currency();

        if ( ! $order instanceof \WC_Order || ! $parent_order instanceof \WC_Order ) {
            return [];
        }

        // Get the admin commission fee for this order.
        $application_fee = dokan()->commission->get_earning_by_order( $order, 'admin' );

        $transfer = [
            'account'             => $data['connected_vendor_id'],
            'amount'              => Helper::format_amount( $data['vendor_earning'] ),
            'currency'            => $currency,
            // 15 max notes and per note max of 256 characters.
            'notes'               => [
                'razorpay_processing_fee' => wc_format_decimal( $data['razorpay_fee_for_vendor'], 2 ),
                'application_fee'         => wc_format_decimal( $application_fee, 2 ),
                'dokan_order_id'          => $order->get_id(),
            ],
            // `linked_account_notes` will be visible on sub-merchant's transaction detail.
            // Only `key` of parent notes can be added here.
            'linked_account_notes' => [
                'razorpay_processing_fee',
                'application_fee',
                'dokan_order_id',
            ],
            'on_hold'              => boolval( 'INSTANT' !== Helper::get_disbursement_mode() ),
        ];

        // We'll keep `on_hold_until` time to null=unlimited time. When the time is reached
        // or order is completed, then we'll update the on_hold status to false.

        // Process transfer data as per the API.
        $transfers['transfers'][0] = $transfer;

        return $transfers;
    }

    /**
     * Transfer amount to vendor Razorpay account from a payment.
     *
     * @see https://razorpay.com/docs/api/route/#create-transfers-from-payments
     *
     * @since 3.5.0
     *
     * @param string    $razorpay_payment_id
     * @param array     $transfer_data
     * @param \WC_Order $order
     * @param float     $amount
     *
     * @return bool
     */
    public function transfer( $razorpay_payment_id, $transfer_data, \WC_Order $order, $amount ) {
        $api = Helper::init_razorpay_api();

        try {
            // Create transfer from razorpay.
            $response_data = $api->payment->fetch( $razorpay_payment_id )->transfer( $transfer_data );

            if ( ! isset( $response_data->items ) ) {
                throw new \Exception( __( 'Razorpay Transfer is invalid.', 'dokan' ), 400 );
            }

            if ( ! count( $response_data->items ) ) {
                throw new \Exception( __( 'Razorpay Transfer is invalid. No transfer found.', 'dokan' ), 400 );
            }

            $transfer    = $response_data->items[0];
            $transfer_id = $transfer->id;
            $on_hold     = $transfer->on_hold ? true : false;

            $order->update_meta_data( '_dokan_razorpay_transfer_id', $transfer_id );
            $order->save_meta_data();

            // Add gateway fee note if this is not a parent order.
            // For parent order this has already been added.
            if ( ! empty( $order->get_parent_id() ) ) {
                $processing_fee = ! empty( $transfer_data['transfers'][0]['notes']['razorpay_processing_fee'] ) ? $transfer_data['transfers'][0]['notes']['razorpay_processing_fee'] : 0;
                /* translators: 1: Gateway fee */
                $order->add_order_note( sprintf( __( 'Payment gateway processing fee %s', 'dokan' ), wc_price( $processing_fee, [ 'currency' => $order->get_currency() ] ) ) );
            }

            // Add transfer_id note to this order.
            $order->add_order_note(
                sprintf(
                    /* translators: 1: Vendor Earning Amount, 2: Transfer ID */
                    __( 'Transferred successfully to the vendor account. Transferred amount: %1$s. Transfer ID: %2$s. Transfer Status: %3$s', 'dokan' ),
                    wc_price( $amount, [ 'currency' => $order->get_currency() ] ),
                    $transfer_id,
                    $on_hold ? __( 'Delayed', 'dokan' ) : __( 'Completed', 'dokan' )
                )
            );

            return true;
        } catch ( \Exception $e ) {
            dokan_log( '[Dokan Razorpay] Could not transfer amount to connected vendor account. Order ID: ' . $order->get_id() . ', Amount tried to transfer: ' . $amount . ' ' . $order->get_currency() );
            /* translators: 1: Error Message */
            $order->add_order_note( sprintf( __( 'Transfer failed to vendor account (%s)', 'dokan' ), $e->getMessage() ) );
            $order->add_order_note( __( 'Vendor payment will be transferred to the admin account since the transfer to the vendor razorpay account had failed.', 'dokan' ) );
            return false;
        }
    }
}
