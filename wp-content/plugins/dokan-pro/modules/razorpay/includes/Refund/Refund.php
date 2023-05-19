<?php

namespace WeDevs\DokanPro\Modules\Razorpay\Refund;

use Exception;
use WeDevs\DokanPro\Modules\Razorpay\Helper;
use WeDevs\DokanPro\Modules\Razorpay\Order\OrderManager;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Refund.
 *
 * @since 3.5.0
 */
class Refund {

    /**
     * Refundable Trait
     *
     * Added refund logics of Razorpay payment gateway added here.
     */
    use Refundable;

    /**
    * Razorpay API
    *
    * @var \Razorpay\Api\Api
    */
    protected $api;

    /**
     * Refund constructor.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function __construct() {
        $this->init_hooks();
        $this->api = Helper::init_razorpay_api();
    }

    /**
     * Init all the hooks.
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function init_hooks() {
        add_action( 'dokan_refund_request_created', [ $this, 'process_refund' ] );
        add_filter( 'dokan_pro_exclude_auto_approve_api_refund_request', [ $this, 'exclude_auto_approve_api_refund' ], 10, 2 );
        add_filter( 'dokan_refund_approve_vendor_refund_amount', [ $this, 'get_vendor_refund_amount' ], 10, 3 );
        add_action( 'dokan_refund_approve_before_insert', [ $this, 'add_vendor_withdraw_entry' ], 10, 3 );
    }

    /**
     * This method will refund payments to seller.
     *
     * @since 3.5.0
     *
     * @param \WeDevs\DokanPro\Refund\Refund $refund
     *
     * @return void
     *
     * @throws Exception
     */
    public function process_refund( $refund ) {
        // get code editor suggestion on refund object
        if ( ! $refund instanceof \WeDevs\DokanPro\Refund\Refund ) {
            return;
        }

        $order = wc_get_order( $refund->get_order_id() );

        // return if $order is not instance of WC_Order
        if ( ! $order instanceof \WC_Order ) {
            return;
        }

        // return if not paid with dokan razorpay payment gateway
        if ( Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return;
        }

        // Get parent order id, because charge id is stored on parent order id
        $parent_order_id = $order->get_parent_id() ? $order->get_parent_id() : $order->get_id();

        // get intent id of the parent order
        $payment_intent_id = get_post_meta( $parent_order_id, '_dokan_razorpay_payment_capture_id', true );
        if ( empty( $payment_intent_id ) ) {
            return;
        }

        // check if refund is approvable
        if ( ! dokan_pro()->refund->is_approvable( $refund->get_order_id() ) ) {
            dokan_log(
                sprintf(
                    /* translators: 1: Gateway Name, 2: Refund Order ID, 3: Main Order ID */
                    __( '%1$s: This refund is not allowed to approve, Refund ID: %2$d, Order ID: %3$d', 'dokan' ),
                    Helper::get_gateway_title(),
                    $refund->get_id(),
                    $refund->get_order_id()
                )
            );
            return;
        }

        /*
         * Handle manual refund.
         *
         * Here, if method returns `string true`, that means this refund is for API Refund.
         * Otherwise handle manual refund.
         *
         * Here, we are just approving if it is Manual refund.
         */
        if ( $refund->is_manual() ) {
            $refund = $refund->approve();

            if ( is_wp_error( $refund ) ) {
                dokan_log( $refund->get_error_message(), 'error' );
            }
            return;
        }

        /**
         * We need to process refund from admin razorpay account and then
         * we need to reverse the transfer created on vendor account.
         */

        // Step 1: check if transfer id exists
        $transfer_id = $order->get_meta( '_dokan_razorpay_transfer_id', true );

        if ( empty( $transfer_id ) ) {
            // we can't automatically reverse vendor balance, so manual refund and approval is required
            $order->add_order_note( __( 'Dokan Razorpay Refund Error: Automatic refund is not possible for this order.', 'dokan' ) );
            return;
        }

        // step 2: process customer refund on razorpay end
        $razorpay_refund = $this->refund( $refund, $order, $payment_intent_id );

        if ( is_wp_error( $razorpay_refund ) ) {
            $error_message = sprintf(
                /* translators: 1: refund id 2: order id 3: error message */
                __( 'Dokan Razorpay Refund Error: Refund failed on Razorpay End. Manual Refund Required. Refund ID: %1$d, Order ID: %2$d, Error Message: %3$s', 'dokan' ),
                $refund->get_id(),
                $refund->get_order_id(),
                $razorpay_refund->get_error_message()
            );
            dokan_log( $error_message, 'error' );
            $order->add_order_note( $error_message );
            return;
        }

        $refund_message = sprintf(
            /* translators: 1: Refund amount 2: Refund transaction id 3: Refund message */
            __( 'Refunded from admin razorpay account: %1$s. Refund ID: %2$s. Reason - %3$s', 'dokan' ),
            wc_price( $refund->get_refund_amount(), [ 'currency' => $order->get_currency() ] ),
            $razorpay_refund->id,
            $refund->get_refund_reason()
        );
        $order->add_order_note( $refund_message );

        $args = [
            'dokan_razorpay' => true,
            'transfer_id'    => $transfer_id,
        ];

        // Store refund id as array, this will help track all partial refunds
        $refund_ids = OrderManager::get_refund_ids_by_order( $order );

        if ( ! in_array( $razorpay_refund->id, $refund_ids, true ) ) {
            $refund_ids[] = $razorpay_refund->id;
        }

        update_post_meta( $order->get_id(), '_dokan_razorpay_refund_id', $refund_ids );

        // store last refund debug id
        update_post_meta( $order->get_id(), '_dokan_razorpay_refund_debug_id', $razorpay_refund->id );

        // Step 3: Try to approve the refund.
        $refund = $refund->approve( $args );

        if ( is_wp_error( $refund ) ) {
            dokan_log( $refund->get_error_message(), 'error' );
        }
    }

    /**
     * Exclude dokan razorpay from auto approve api refund.
     *
     * @since 3.5.0
     *
     * @param array                  $payment_gateways
     * @param WeDevs\DokanPro\Refund $refund
     *
     * @return array
     */
    public function exclude_auto_approve_api_refund( $payment_gateways, $refund ) {
        $payment_gateways[] = Helper::get_gateway_id();

        return $payment_gateways;
    }

    /**
     * Recalculate gateway fee after a refund.
     *
     * @since 3.5.0
     *
     * @param \WC_Order $order
     * @param float     $gateway_fee_refunded
     *
     * @return void
     */
    private function update_gateway_fee( $order, $gateway_fee_refunded ) {
        $gateway_fee = wc_format_decimal( $order->get_meta( 'dokan_gateway_fee', true ), 2 );
        $gateway_fee = $gateway_fee - $gateway_fee_refunded;

        /*
         * If there is no remaining amount then its full refund and we are updating the processing fee to 0.
         * because seller is already paid the processing fee from his account. if we keep this then it will deducted twice.
         */
        if ( $order->get_remaining_refund_amount() <= 0 ) {
            $gateway_fee = 0;
        }

        $order->update_meta_data( 'dokan_gateway_fee', $gateway_fee );
        $order->save_meta_data();
    }

    /**
     * Withdraw entry for automatic refund as debit.
     *
     * @since 3.5.0
     *
     * @param \WeDevs\DokanPro\Refund\Refund $refund
     * @param array $args
     * @param float $vendor_refund
     *
     * @return void
     */
    public function add_vendor_withdraw_entry( $refund, $args, $vendor_refund ) {
        // Check if a transfer is made for this order.
        if ( empty( $args['transfer_id'] ) ) {
            return;
        }

        $order = wc_get_order( $refund->get_order_id() );

        // return if $order is not instance of WC_Order
        if ( ! $order instanceof \WC_Order ) {
            return;
        }

        // return if not paid with dokan razorpay payment gateway
        if ( Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return;
        }

        // Process reverse transfer from vendor account to admin account.
        // If reversal is successful, we need to add a debit entry in dokan vendor balance table.
        $reversed = $this->reverse_transfer( $refund, $args, $vendor_refund );
        if ( is_wp_error( $reversed ) ) {
            // translators: 1: Error Message
            dokan_log( sprintf( __( '[Dokan Razorpay] Merchant Reverse Transfer Amount: %s', 'dokan' ), $reversed->get_error_message() ), 'error' );
            $order->add_order_note( $reversed->get_error_message() );
            return;
        }

        /* translators: 1: Razorpay Transfer ID  */
        $success_message = sprintf( __( 'Amount reversed from vendor razorpay account. Transfer ID: %s', 'dokan' ), $args['transfer_id'] );
        $order->add_order_note( $success_message );

        global $wpdb;
        // Reversed from Vendor account. Now process it into vendor balance.
        $wpdb->insert(
            $wpdb->dokan_vendor_balance,
            [
                'vendor_id'     => $refund->get_seller_id(),
                'trn_id'        => $refund->get_order_id(),
                'trn_type'      => 'dokan_refund',
                /* translators: 1: Gateway Title */
                'perticulars'   => sprintf( __( 'Refunded Via %s', 'dokan' ), Helper::get_gateway_title() ),
                'debit'         => $vendor_refund,
                'credit'        => 0,
                'status'        => 'wc-completed', // @see: Dokan_Vendor->get_balance() method
                'trn_date'      => current_time( 'mysql' ),
                'balance_date'  => current_time( 'mysql' ),
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
    }

    /**
     * Get vendor refund amount as razorpay refund amount.
     *
     * @since 3.5.0
     *
     * @param float                          $vendor_refund
     * @param array                          $args
     * @param \WeDevs\DokanPro\Refund\Refund $refund
     *
     * @return float
     */
    public function get_vendor_refund_amount( $vendor_refund, $args, $refund ) {
        // Check if it's from dokan_razorpay gateway and has transfer.
        if ( empty( $args['dokan_razorpay'] ) || empty( $args['transfer_id'] ) ) {
            return $vendor_refund;
        }

        $order = wc_get_order( $refund->get_order_id() );

        // return if $order is not instance of WC_Order
        if ( ! $order instanceof \WC_Order ) {
            return $vendor_refund;
        }

        // return if not paid with dokan razorpay payment gateway
        if ( Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return $vendor_refund;
        }

        // check gateway fee refunded amount
        $gateway_fee          = wc_format_decimal( $order->get_meta( '_dokan_razorpay_payment_processing_fee', true ), 2 );
        $gateway_fee_refunded = wc_format_decimal( ( $gateway_fee * $refund->get_refund_amount() ) / $order->get_total(), 2 );

        // check gateway fee is refunded, if not we need to calculate this value manually
        $refund_amount = $vendor_refund - $gateway_fee_refunded;
        $refund_amount = $refund_amount > 0 ? $refund_amount : 0; // making sure amount is not negative

        // check if balance transaction is greater than $refund_amount
        try {
            $razorpay_transfer        = $this->api->transfer->fetch( $args['transfer_id'] );
            $total_retrievable_amount = ( $razorpay_transfer->amount - $razorpay_transfer->amount_reversed ) / 100;
            $total_retrievable_amount = $total_retrievable_amount > 0 ? $total_retrievable_amount : 0; // making sure amount is not negative
        } catch ( Exception $e ) {
            $total_retrievable_amount = 0;
        }

        // check if we are doing full refund, or this is the last amount refund for partial refund
        if ( wc_format_decimal( $order->get_total_refunded(), 2 ) === wc_format_decimal( $order->get_total( 'edit' ), 2 ) || $refund_amount > $total_retrievable_amount ) {
            $refund_amount = $total_retrievable_amount;
        }

        // Deduct refunded amount from gateway fee and Update gateway fee.
        $this->update_gateway_fee( $order, $gateway_fee_refunded );

        return $refund_amount;
    }
}
