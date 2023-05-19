<?php

namespace WeDevs\DokanPro\Modules\Stripe;

use Exception;
use Stripe\BalanceTransaction;
use Stripe\Refund as StripeRefund;
use Stripe\Transfer;

defined( 'ABSPATH' ) || exit;

class Refund {

    /**
     * Constructor method
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * Init all the hooks
     *
     * @since 3.0.3
     *
     * @return void
     */
    private function hooks() {
        // stripe non3ds refund
        add_action( 'dokan_refund_request_created', [ $this, 'process_refund' ] );
        add_filter( 'dokan_refund_approve_vendor_refund_amount', [ $this, 'vendor_refund_amount_non_3ds' ], 10, 3 );
        add_action( 'dokan_refund_approve_before_insert', [ $this, 'add_vendor_withdraw_entry_non_3ds' ], 10, 3 );

        // process 3ds refund
        add_action( 'dokan_refund_request_created', [ $this, 'process_3ds_refund' ] );
        add_filter( 'dokan_refund_approve_vendor_refund_amount', [ $this, 'vendor_refund_amount_3ds' ], 10, 3 );
        add_action( 'dokan_refund_approve_before_insert', [ $this, 'add_vendor_withdraw_entry_3ds' ], 10, 3 );
    }

    /**
     * Process refund request
     *
     * @param  int $refund_id
     * @param  array $data
     *
     * @return void
     */
    public function process_refund( $refund ) {
        // get code editor suggestion on refund object
        if ( ! $refund instanceof \WeDevs\DokanPro\Refund\Refund ) {
            return;
        }

        // check if refund is approvable
        if ( ! dokan_pro()->refund->is_approvable( $refund->get_order_id() ) ) {
            dokan_log( sprintf( 'Stripe Non3DS Refund: This refund is not allowed to approve, Refund ID: %1$s, Order ID: %2$s', $refund->get_id(), $refund->get_order_id() ) );
            return;
        }

        $order = wc_get_order( $refund->get_order_id() );

        // return if $order is not instance of WC_Order
        if ( ! $order instanceof \WC_Order ) {
            return;
        }

        // return if not paid with dokan stripe payment gateway
        if ( 'dokan-stripe-connect' !== $order->get_payment_method() ) {
            return;
        }

        // Get parent order id, because charge id is stored on parent order id
        $parent_order_id = $order->get_parent_id() ? $order->get_parent_id() : $order->get_id();

        // get intent id of the parent order
        $payment_intent_id = get_post_meta( $parent_order_id, 'dokan_stripe_intent_id', true );
        if ( ! empty( $payment_intent_id ) ) {
            // if payment is processed with stripe3ds, return from here
            return;
        }

        /*
         * Handle manual refund.
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

        $seller_id        = $refund->get_seller_id();
        $vendor_token     = get_user_meta( $seller_id, '_stripe_connect_access_key', true );
        $vendor_charge_id = $order->get_meta( "_dokan_stripe_charge_id_{$seller_id}" );

        // if vendor charge id is not found, meaning charge was captured from admin stripe account
        if ( ! $vendor_charge_id ) {
            /**
             * Todo: if vendor charge id is not found, possible that charge was made on admin stripe account (eg: non connected vendors)
             * Implement automatic refund from admin account and negative charge vendors so that admin trace
             */
            $order->add_order_note(
                sprintf(
                /* translators: 1) Refund ID, 2) Order ID */
                    __( 'Dokan Stripe Refund Error: Automatic refund is not possible for this order. Reason: No vendor charge id found. Refund id: %1$s, Order ID: %2$s', 'dokan' ),
                    $refund->get_id(), $refund->get_order_id()
                )
            );
            return;
        }

        /**
         * If admin has earning from an order, only then refund application fee
         *
         * @since 3.0.0
         *
         * @see https://stripe.com/docs/api/refunds/create#create_refund-refund_application_fee
         *
         * @var string
         */
        $application_fee = wc_format_decimal( dokan()->commission->get_earning_by_order( $order, 'admin' ), 2 ) - wc_format_decimal( $order->get_meta( 'dokan_gateway_fee', true ), 2 );

        Helper::bootstrap_stripe();

        try {
            $stripe_refund = StripeRefund::create(
                [
                    'charge'                 => $vendor_charge_id,
                    'amount'                 => Helper::get_stripe_amount( $refund->get_refund_amount() ),
                    'reason'                 => __( 'requested_by_customer', 'dokan' ),
                    'refund_application_fee' => $application_fee > 0 ? true : false,
                ],
                $vendor_token
            );

            /* translators: 1) Stripe refund id */
            $order->add_order_note( sprintf( __( 'Refund Processed Via Seller Stripe Account ( Refund ID: %s )', 'dokan' ), $stripe_refund->id ) );

            $args = [
                'stripe_non_3ds'    => true,
                'refund_id'         => $stripe_refund->id,
                'refunded_account'  => 'seller',
            ];

            // get balance transaction for refund amount, we need to deduct gateway charge from vendor refund amount
            $balance_transaction            = BalanceTransaction::retrieve( $stripe_refund->balance_transaction, $vendor_token );
            $gateway_fee_refunded           = abs( Helper::format_gateway_balance_fee( $balance_transaction ) );
            $args['gateway_fee_refunded']   = ! empty( $gateway_fee_refunded ) ? $gateway_fee_refunded : 0;

            $refund = $refund->approve( $args );

            if ( is_wp_error( $refund ) ) {
                dokan_log( $refund->get_error_message(), 'error' );
            }
        } catch ( Exception $e ) {
            $error_message = sprintf(
                /* translators: 1) Refund ID, 2) Order ID */
                __( 'Dokan Stripe Refund Error: Automatic refund was not successful for this order. Manual Refund Required. Reason: %1$s Refund id: %2$s, Order ID: %3$s', 'dokan' ),
                $e->getMessage(), $refund->get_id(), $refund->get_order_id()
            );
            $order->add_order_note( $error_message );
            dokan_log( $error_message, 'error' );
        }
    }

    /**
     * Set vendor refund amount for 3ds mode
     *
     * @param float $vendor_refund
     * @param array $args
     * @param \WeDevs\DokanPro\Refund\Refund $refund
     *
     * @return float
     * @throws \Stripe\Exception\ApiErrorException
     * @since 3.3.2
     */
    public function vendor_refund_amount_non_3ds( $vendor_refund, $args, $refund ) {
        $order = wc_get_order( $refund->get_order_id() );

        // return if $order is not instance of WC_Order
        if ( ! $order instanceof \WC_Order ) {
            return $vendor_refund;
        }

        // return if not paid with dokan paypal marketplace payment gateway
        if ( Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return $vendor_refund;
        }

        if ( empty( $args['stripe_non_3ds'] ) || empty( $args['refund_id'] ) ) {
            return $vendor_refund;
        }

        // check gateway fee refunded amount
        $gateway_fee_refunded = ! empty( $args['gateway_fee_refunded'] ) ? wc_format_decimal( $args['gateway_fee_refunded'] ) : 0;
        // check gateway fee is refunded, if not we need to calculate this value manually
        if ( $gateway_fee_refunded === 0 ) {
            $order_total = $order->get_total( 'edit' );
            $gateway_fee = wc_format_decimal( $order->get_meta( 'dokan_gateway_stripe_fee', true ), 2 );
            $gateway_fee_refunded = $gateway_fee > 0 ? ( ( $gateway_fee / $order_total ) * $refund->get_refund_amount() ) : 0;
            $refund_amount = $vendor_refund - $gateway_fee_refunded;
        } else {
            $refund_amount = $vendor_refund - $gateway_fee_refunded;
        }

        $refund_amount = $refund_amount > 0 ? $refund_amount : 0; // making sure amount is not negative

        // update gateway fees
        $this->update_gateway_fee( $order, $gateway_fee_refunded );

        return $refund_amount;
    }

    /**
     * Withdraw entry for automatic refund as debit
     *
     * @param $refund \WeDevs\DokanPro\Refund\Refund
     * @param $args array
     * @param $vendor_refund float
     *
     * @since 3.3.2
     *
     * @return void
     */
    public function add_vendor_withdraw_entry_non_3ds( $refund, $args, $vendor_refund ) {
        $order = wc_get_order( $refund->get_order_id() );

        // return if $order is not instance of WC_Order
        if ( ! $order instanceof \WC_Order ) {
            return;
        }

        // return if not paid with dokan paypal marketplace payment gateway
        if ( Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return;
        }

        if ( empty( $args['stripe_non_3ds'] ) || empty( $args['refund_id'] ) ) {
            return;
        }

        global $wpdb;

        // now insert into database
        $wpdb->insert(
            $wpdb->dokan_vendor_balance,
            [
                'vendor_id'     => $refund->get_seller_id(),
                'trn_id'        => $refund->get_order_id(),
                'trn_type'      => 'dokan_refund',
                'perticulars'   => __( 'Refunded Via Stripe', 'dokan' ),
                'debit'         => $vendor_refund,
                'credit'        => 0,
                'status'        => 'wc-completed', // see: Dokan_Vendor->get_balance() method
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
     * This method will refund payments collected with stripe 3ds
     *
     * @param \WeDevs\DokanPro\Refund\Refund $refund
     * @throws Exception
     * @since 3.2.2
     */
    public function process_3ds_refund( $refund ) {
        // get code editor suggestion on refund object
        if ( ! $refund instanceof \WeDevs\DokanPro\Refund\Refund ) {
            return;
        }

        $order = wc_get_order( $refund->get_order_id() );

        // return if $order is not instance of WC_Order
        if ( ! $order instanceof \WC_Order ) {
            return;
        }

        // return if not paid with dokan stripe payment gateway
        if ( 'dokan-stripe-connect' !== $order->get_payment_method() ) {
            return;
        }

        // check if user paid with stripe3ds ( check if _stripe_intent_id exists ) we can use paid_with_dokan_3ds meta exists,
        // but parent order doesn't have this meta. so using _stripe_intent_id is safer for both single or multivendor orders

        // Get parent order id, because charge id is stored on parent order id
        $parent_order_id = $order->get_parent_id() ? $order->get_parent_id() : $order->get_id();

        // get intent id of the parent order
        $payment_intent_id = get_post_meta( $parent_order_id, 'dokan_stripe_intent_id', true );
        if ( empty( $payment_intent_id ) ) {
            return;
        }

        // check if refund is approvable
        if ( ! dokan_pro()->refund->is_approvable( $refund->get_order_id() ) ) {
            dokan_log( sprintf( 'Stripe 3ds Refund: This refund is not allowed to approve, Refund ID: %1$s, Order ID: %2$s', $refund->get_id(), $refund->get_order_id() ) );
            return;
        }

        /*
         * Handle manual refund.
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

        // now process refund.
        /**
         * Related Documentation:
         * https://stripe.com/docs/connect/charges-transfers
         * https://stripe.com/docs/api/refunds
         * https://stripe.com/docs/api/transfer_reversals/create?lang=php
         * We need to process refund from admin stripe account and then we need to reverse the transfer created
         * on vendor account.
         */

        Helper::bootstrap_stripe();

        // Step 1: check if transfer id exists
        $transfer_id = $order->get_meta( '_dokan_stripe_transfer_id' );

        if ( empty( $transfer_id ) ) {
            // we can't automatically reverse vendor balance, so manual refund and approval is required
            // add order note
            $order->add_order_note( __( 'Dokan Stripe 3ds Refund Error: Automatic refund is not possible for this order.', 'dokan' ) );
            return;
        }

        // step 2: process customer refund on stripe end
        try {
            $stripe_refund = StripeRefund::create(
                [
                    'payment_intent'    => $payment_intent_id,
                    'amount'            => Helper::get_stripe_amount( $refund->get_refund_amount() ),
                    'reason'            => 'requested_by_customer',
                ]
            );

            /* translators: 1) refund amount 2) refund currency 3) transaction id 4) refund message */
            $refund_message = sprintf( __( 'Refunded from admin stripe account: %1$s %2$s - Refund ID: %3$s - %4$s', 'dokan' ), $refund->get_refund_amount(), $order->get_currency(), $stripe_refund->id, $refund->get_refund_reason() );
            $order_note_id = $order->add_order_note( $refund_message );
        } catch ( Exception $e ) {
            $error_message = sprintf( 'Dokan Stripe 3ds Refund Error: Refund failed on Stripe End. Manual Refund Required. Refund ID: %1$s, Order ID: %2$s, Error Message: %3$s', $refund->get_id(), $refund->get_order_id(), $e->getMessage() );
            dokan_log( $error_message, 'error' );
            $order->add_order_note( $error_message );
            return;
        }

        $args = [
            'stripe_3ds'    => true,
            'transfer_id'   => $transfer_id,
        ];

        // get balance transaction for refund amount, we need to deduct gateway charge from vendor refund amount
        $gateway_fee_refunded           = abs( Helper::format_gateway_balance_fee( $stripe_refund->balance_transaction ) );
        $args['gateway_fee_refunded']   = ! empty( $gateway_fee_refunded ) ? $gateway_fee_refunded : 0;

        // Step 3: Try to approve the refund.
        $refund = $refund->approve( $args );

        if ( is_wp_error( $refund ) ) {
            dokan_log( $refund->get_error_message(), 'error' );
        }
    }

    /**
     * Set vendor refund amount for 3ds mode
     *
     * @param float $vendor_refund
     * @param array $args
     * @param \WeDevs\DokanPro\Refund\Refund $refund
     *
     * @return float
     * @since 3.3.2
     */
    public function vendor_refund_amount_3ds( $vendor_refund, $args, $refund ) {
        $order = wc_get_order( $refund->get_order_id() );

        // return if $order is not instance of WC_Order
        if ( ! $order instanceof \WC_Order ) {
            return $vendor_refund;
        }

        // return if not paid with dokan paypal marketplace payment gateway
        if ( Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return $vendor_refund;
        }

        if ( empty( $args['stripe_3ds'] ) || empty( $args['transfer_id'] ) ) {
            return $vendor_refund;
        }

        // check gateway fee refunded amount
        $gateway_fee_refunded = ! empty( $args['gateway_fee_refunded'] ) ? wc_format_decimal( $args['gateway_fee_refunded'] ) : 0;

        // check gateway fee is refunded, if not we need to calculate this value manually
        if ( $gateway_fee_refunded === 0 ) {
            $order_total = $order->get_total( 'edit' );
            $gateway_fee = wc_format_decimal( $order->get_meta( 'dokan_gateway_stripe_fee', true ), 2 );
            $gateway_fee_refunded = $gateway_fee > 0 ? ( ( $gateway_fee / $order_total ) * $refund->get_refund_amount() ) : 0;
            $refund_amount = $vendor_refund - $gateway_fee_refunded;
        } else {
            $refund_amount = $vendor_refund - $gateway_fee_refunded;
        }

        $refund_amount = $refund_amount > 0 ? $refund_amount : 0; // making sure amount is not negative

        // check if balance transaction is greater than $refund_amount
        try {
            $stripe_transfer = Transfer::retrieve( $args['transfer_id'] );
            $total_retrievable_amount = ( $stripe_transfer->amount - $stripe_transfer->amount_reversed ) / 100;
            $total_retrievable_amount = $total_retrievable_amount > 0 ? $total_retrievable_amount : 0; // making sure amount is not negative
        } catch ( Exception $e ) {
            $total_retrievable_amount = 0;
        }

        // check if we are doing full refund, or this is the last amount refund for partial refund
        if ( wc_format_decimal( $order->get_total_refunded(), 2 ) === wc_format_decimal( $order->get_total( 'edit' ), 2 ) || $refund_amount > $total_retrievable_amount ) {
            $refund_amount = $total_retrievable_amount;
        }

        // update gateway fees
        $this->update_gateway_fee( $order, $gateway_fee_refunded );

        return $refund_amount;
    }

    /**
     * Withdraw entry for automatic refund as debit
     *
     * @param $refund \WeDevs\DokanPro\Refund\Refund
     * @param $args array
     * @param $vendor_refund float
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function add_vendor_withdraw_entry_3ds( $refund, $args, $vendor_refund ) {
        $order = wc_get_order( $refund->get_order_id() );

        // return if $order is not instance of WC_Order
        if ( ! $order instanceof \WC_Order ) {
            return;
        }

        // return if not paid with dokan paypal marketplace payment gateway
        if ( Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return;
        }

        if ( empty( $args['stripe_3ds'] ) || empty( $args['transfer_id'] ) ) {
            return;
        }

        global $wpdb;

        /**
         * With stripe3ds refund, charge is refunded from admin stripe account. after that we need to reverse corresponding
         * amount from vendor stripe account. If reversal is successful, we need to add a debit entry in dokan vendor balance
         * table.
         */
        try {
            // now process transfer reverse
            $stripe_reverse_transfer = Transfer::createReversal(
                $args['transfer_id'],
                [
                    'amount' => Helper::get_stripe_amount( $vendor_refund ),
                ]
            );
            /* translators: 1) Stripe Transfer ID  */
            $success_message = sprintf( __( 'Amount reversed from vendor stripe account. Transfer ID: %1$s', 'dokan' ), $args['transfer_id'] );
            $order->add_order_note( $success_message );

            // now insert into database
            $wpdb->insert(
                $wpdb->dokan_vendor_balance,
                [
                    'vendor_id'     => $refund->get_seller_id(),
                    'trn_id'        => $refund->get_order_id(),
                    'trn_type'      => 'dokan_refund',
                    'perticulars'   => __( 'Refunded Via Stripe 3DS', 'dokan' ),
                    'debit'         => $vendor_refund,
                    'credit'        => 0,
                    'status'        => 'wc-completed', // see: Dokan_Vendor->get_balance() method
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
        } catch ( \Exception $e ) {
            $error_message = sprintf( 'Dokan Stripe 3ds Error: Order refunded from admin Stripe account but can not automatically reverse transferred amount from vendor Stripe account. Manual reversal required. (Refund ID: %1$s, Stripe Transfer ID: %2$s)', $refund->get_id(), $args['transfer_id'] );
            dokan_log( $error_message );
            $order->add_order_note( $error_message );
        }
    }

    /**
     * @param \WC_Order $order
     * @param float $gateway_fee_refunded
     *
     * @since 3.3.2
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
}
