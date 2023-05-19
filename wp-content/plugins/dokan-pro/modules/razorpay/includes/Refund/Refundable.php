<?php

namespace WeDevs\DokanPro\Modules\Razorpay\Refund;

use WP_Error;
use Exception;
use WeDevs\DokanPro\Modules\Razorpay\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Trait Refundable.
 *
 * @since 3.5.0
 */
trait Refundable {
    /**
     * Refund from Admin Razorpay account to customer.
     *
     * @see https://razorpay.com/docs/api/refunds/#create-a-normal-refund
     * @see https://razorpay.com/docs/api/route/#refund-payments-and-reverse-transfer-from-a-linked
     *
     * @since 3.5.0
     *
     * @param \WeDevs\DokanPro\Refund\Refund $refund
     * @param WC_Order $order
     * @param string  $payment_id
     *
     * @return object|\WP_Error
     */
    public function refund( $refund, \WC_Order $order, $payment_id ) {
        $order = $order->get_parent_id() ? wc_get_order( $order->get_parent_id() ) : $order;

        if ( ! $order ) {
            return new WP_Error( 'dokan_razorpay_refund_error', __( 'Invalid order', 'dokan' ) );
        }

        $refund_data = [
            'amount'  => absint( wc_format_decimal( $refund->get_refund_amount(), 2 ) * 100 ), // Razorpay requires amount in int.
            'receipt' => 'Refund Receipt No #' . $refund->get_id(), // This receipt must be different from the previous receipts.
            'notes'   => [
                'reason'              => ! empty( $refund->get_refund_reason() ) ? $refund->get_refund_reason() : __( 'Refund', 'dokan' ),
                'order_id'            => $order->get_id(),
                'source'              => Helper::get_gateway_id(),
                'currency'            => $order->get_currency(),
                'refund_from_website' => true,
            ],
        ];

        try {
            return $this->api->payment->fetch( $payment_id )->refund( $refund_data );
        } catch ( Exception $e ) {
            return new WP_Error( 'dokan_razorpay_refund_error', $e->getMessage() );
        }
    }

    /**
     * Reverse transfer from Vendor Razorpay account to Admin account.
     *
     * @see https://razorpay.com/docs/api/route/#refund-payments-and-reverse-transfer-from-a-linked
     *
     * @since 3.5.0
     *
     * @param \WeDevs\DokanPro\Refund\Refund $refund
     * @param array                          $args
     * @param float                          $vendor_refund
     *
     * @return bool|WP_Error
     */
    public function reverse_transfer( $refund, $args, $vendor_refund ) {
        /**
         * Here, charge is refunded from admin razorpay account. after that we need to reverse corresponding
         * amount from vendor razorpay account. This will reverse the transfer and will goes to admin account.
         */
        try {
            // process reverse transfer from individual vendor account.
            $this->api->transfer
                ->fetch( $args['transfer_id'] )
                ->reverse(
                    [
                        'amount' => Helper::format_amount( $vendor_refund ),
                    ]
                );
            return true;
        } catch ( Exception $e ) {
            $error_message = sprintf(
                /* translators: 1: Refund ID, 2: Transfer ID */
                __( 'Dokan Razorpay Error: Order refunded from admin Razorpay account but can not automatically reverse transferred amount from vendor Razorpay account. Manual reversal required. (Refund ID: %1$s, Razorpay Transfer ID: %2$s)', 'dokan' ),
                $refund->get_id(),
                $args['transfer_id']
            );

            return new WP_Error( 'dokan_razorpay_reverse_transfer_error', $error_message );
        }
    }
}
