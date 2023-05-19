<?php

namespace WeDevs\DokanPro\Modules\Razorpay\Order;

use Exception;
use WeDevs\DokanPro\Modules\Razorpay\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class OrderValidator.
 *
 * Order related validation will be added here.
 *
 * @package WeDevs\Dokan\Gateways\Razorpay
 *
 * @since 3.5.0
 */
class OrderValidator {
    /**
     * Verify Razorpay Order and Order Amount.
     *
     * @see https://razorpay.com/docs/api/orders/#fetch-an-order-with-id
     *
     * @since 3.5.0
     *
     * @param string $razorpay_order_id
     * @param int    $order_id
     *
     * @return bool
     */
    public static function verify_order_amount( $razorpay_order_id = null, $order_id ) {
        if ( empty( $razorpay_order_id ) || empty( $order_id ) ) {
            return false;
        }

        try {
            $api            = Helper::init_razorpay_api();
            $razorpay_order = $api->order->fetch( $razorpay_order_id );
        } catch ( Exception $e ) {
            return false;
        }

        $order_data = OrderManager::make_order_unit_data( $order_id );

        $razorpay_order_args = [
            'id'       => $razorpay_order_id,
            'amount'   => $order_data['amount'],
            'currency' => $order_data['currency'],
            'receipt'  => (string) $order_id,
        ];

        $order_keys = array_keys( $razorpay_order_args );

        foreach ( $order_keys as $key ) {
            if ( $razorpay_order_args[ $key ] !== $razorpay_order[ $key ] ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Verify Razorpay Order and Payment Signature.
     *
     * @see https://razorpay.com/docs/payment-gateway/web-integration/hosted/best-practices/#3-verify-signature-to-avoid-data-tampering
     *
     * @since 3.5.0
     *
     * @param int   $order_id
     * @param array $order_id
     *
     * @return bool
     */
    public static function verify_payment_signature( $order_id, $post_data = [] ) {
        try {
            $api = Helper::init_razorpay_api();

            $razorpay_order_id   = isset( $post_data['razorpay_order_id'] ) ? sanitize_text_field( wp_unslash( $post_data['razorpay_order_id'] ) ) : null;
            $razorpay_payment_id = isset( $post_data['razorpay_payment_id'] ) ? sanitize_text_field( wp_unslash( $post_data['razorpay_payment_id'] ) ) : null;
            $razorpay_signature  = isset( $post_data['razorpay_signature'] ) ? sanitize_text_field( wp_unslash( $post_data['razorpay_signature'] ) ) : null;

            if ( empty( $razorpay_order_id ) || empty( $razorpay_payment_id ) || empty( $razorpay_signature ) ) {
                throw new Exception( __( 'Invalid Razorpay payment data.', 'dokan' ) );
            }

            $args = [
                'razorpay_order_id'   => $razorpay_order_id,
                'razorpay_payment_id' => $razorpay_payment_id,
                'razorpay_signature'  => $razorpay_signature,
            ];

            $api->utility->verifyPaymentSignature( $args );
            return true;
        } catch ( \Razorpay\Api\Errors\SignatureVerificationError $e ) {
            dokan_log( '[Dokan Razorpay] Razorpay Payment Signature verification failed. Order ID: ' . $order_id . '. Error:' . $e->getMessage(), 'error' );
        } catch ( Exception $e ) {
            dokan_log( '[Dokan Razorpay] Razorpay Payment failed. Order ID: ' . $order_id . '. Error:' . $e->getMessage(), 'error' );
        }

        return false;
    }

    /**
     * Check If Order is able to transfer to the vendor.
     *
     * @since 3.5.0
     *
     * @param \WC_Order $order
     * @param string    $connected_vendor_id
     * @param float     $vendor_earning
     *
     * @return bool     true if order is able to transfer
     */
    public static function is_order_transferable( \WC_Order $order, $connected_vendor_id, $vendor_earning ) {
        // If no connected vendor account, return false
        if ( ! $connected_vendor_id ) {
            // old order note for reference: Vendor's payment will be transferred to admin account since the vendor had not connected to Razorpay.
            $order->add_order_note( sprintf( __( 'Vendor payment will be transferred to the admin account since the vendor had not connected to Razorpay.', 'dokan' ) ) );
            $order->save_meta_data();
            return false;
        }

        // If negative balance is found, return false
        if ( $vendor_earning < 1 ) {
            $order->add_order_note(
                sprintf(
                    /* translators: 1: Vendor Row Earning, 2: Currency */
                    __( 'Transfer to the vendor razorpay account skipped due to a negative balance: %1$d %2$s', 'dokan' ),
                    $vendor_earning,
                    $order->get_currency()
                )
            );
            $order->save_meta_data();
            return false;
        }

        return true;
    }
}
