<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Support;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WC_Order;

/**
 * Order meta data handler class for Stripe gateway.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Support
 */
class OrderMeta {

    /**
     * Saves the order.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return void
     */
    public static function save( WC_Order $order ) {
        if ( is_callable( [ $order, 'save' ] ) ) {
            $order->save();
        }
    }

    /**
     * Retrieves meta key for charge captured flag.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function charge_captured_key() {
        return Helper::meta_key( 'charge_captured' );
    }

    /**
     * Updates the status of charge captured.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     * @param string   $is_captured
     *
     * @return void
     */
    public static function update_charge_captured( WC_Order $order, $is_captured = 'yes' ) {
        $order->update_meta_data( self::charge_captured_key(), $is_captured );
    }

    /**
     * Checks whether stripe charge is captured or not.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return boolean
     */
    public static function is_charge_captured( WC_Order $order ) {
        return 'yes' === $order->get_meta( self::charge_captured_key(), true );
    }

    /**
     * Retrieves transaction id key.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function transaction_id_key() {
        return Helper::meta_key( 'transaction_id' );
    }

    /**
     * Updates the transaction id of an order
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     * @param string   $trn_id
     *
     * @return void
     */
    public static function update_transaction_id( WC_Order $order, $trn_id ) {
        $order->set_transaction_id( $trn_id );
        $order->update_meta_data( self::transaction_id_key(), $trn_id );
    }

    /**
     * Retrieves transaction id of an order.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return boolean
     */
    public static function get_transaction_id( WC_Order $order ) {
        return $order->get_meta( self::transaction_id_key(), true );
    }

    /**
     * Retrieves transfer id key.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function transfer_id_key() {
        return Helper::meta_key( 'transfer_id' );
    }

    /**
     * Updates the transfer id of an order.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     * @param string   $trn_id
     *
     * @return void
     */
    public static function update_transfer_id( WC_Order $order, $trn_id ) {
        $order->update_meta_data( self::transfer_id_key(), $trn_id );
    }

    /**
     * Retrieves transfer id of an order.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return boolean
     */
    public static function get_transfer_id( WC_Order $order ) {
        return $order->get_meta( self::transfer_id_key(), true );
    }

    /**
     * Retrieves payment/setup intent id key.
     *
     * @since 3.6.1
     *
     * @param boolean $is_setup
     *
     * @return string
     */
    public static function intent_id_key( $is_setup = false ) {
        $intent_type = $is_setup ? 'setup' : 'payment';
        return Helper::meta_key( "{$intent_type}_intent_id" );
    }

    /**
     * Updates intent id of an order.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     * @param string   $intent_id
     * @param boolean  $is_setup
     *
     * @return void
     */
    public static function update_intent( WC_Order $order, $intent_id, $is_setup = false ) {
        if ( ! $is_setup ) {
            self::add_payment_intent( $order, $intent_id );
        } else {
            self::add_setup_intent( $order, $intent_id );
        }
    }

    /**
     * Adds payment intent id and order note to order if payment intent is not already saved.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     * @param string   $payment_intent_id
     *
     * @return void
     */
    public static function add_payment_intent( WC_Order $order, $payment_intent_id ) {
        $intent_key    = self::intent_id_key();
        $old_intent_id = $order->get_meta( $intent_key, true );

        if ( $old_intent_id === $payment_intent_id ) {
            return;
        }

        $order->add_order_note(
            sprintf(
                /* translators: $1%s payment intent ID */
                __( '[%1$s] Payment Intent ID: %2$s', 'dokan' ),
                Helper::get_gateway_title(), $payment_intent_id
            )
        );

        $order->update_meta_data( $intent_key, $payment_intent_id );
    }

    /**
     * Retrieves payment intent id.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return mixed
     */
    public static function get_payment_intent( WC_Order $order ) {
        return $order->get_meta( self::intent_id_key(), true );
    }

    /**
     * Deletes intent id of an order.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     *
     * @return void
     */
    public static function delete_payment_intent( WC_Order $order ) {
        $order->delete_meta_data( self::intent_id_key() );
    }

    /**
     * Adds setup intent to order.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     * @param string $intent_id
     *
     * @return void
     */
    public static function add_setup_intent( WC_Order $order, $intent_id ) {
        $order->update_meta_data( self::intent_id_key( true ), $intent_id );
    }

    /**
     * Retrieves setup intent id.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return string
     */
    public static function get_setup_intent( WC_Order $order ) {
        return $order->get_meta( self::intent_id_key( true ), true );
    }

    /**
     * Retrieves payment/setup intent debug id key.
     *
     * @since 3.7.8
     *
     * @param boolean $is_setup
     *
     * @return string
     */
    public static function debug_intent_id_key( $is_setup = false ) {
        $intent_type = $is_setup ? 'setup' : 'payment';
        return Helper::meta_key( "{$intent_type}_intent_debug_id" );
    }

    /**
     * Updates debug payment intent to order.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     * @param string   $intent_id
     *
     * @return void
     */
    public static function update_debug_payment_intent( WC_Order $order, $intent_id ) {
        $order->update_meta_data( self::debug_intent_id_key(), $intent_id );
    }

    /**
     * Retrieves setup intent id.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     *
     * @return string
     */
    public static function get_debug_payment_intent( WC_Order $order ) {
        return $order->get_meta( self::debug_intent_id_key(), true );
    }

    /**
     * Updates debug setup intent to order.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     * @param string   $intent_id
     *
     * @return void
     */
    public static function update_debug_setup_intent( WC_Order $order, $intent_id ) {
        $order->update_meta_data( self::debug_intent_id_key( true ), $intent_id );
    }

    /**
     * Retrieves debug setup intent id.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     *
     * @return string
     */
    public static function get_debug_setup_intent( WC_Order $order ) {
        return $order->get_meta( self::debug_intent_id_key( true ), true );
    }

    /**
     * Retrieves source id key.
     *
     * @since 3.6.1
     *
     * @deprecated 3.7.8
     *
     * @return string
     */
    public static function source_id_key() {
        return Helper::meta_key( 'source_id' );
    }

    /**
     * Retrieves payment method id key.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function payment_method_id_key() {
        return Helper::meta_key( 'payment_method_id' );
    }

    /**
     * Retrieves stripe payment method id.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     *
     * @return mixed
     */
    public static function get_payment_method_id( WC_Order $order ) {
        $payment_method_id = $order->get_meta( self::payment_method_id_key(), true );

        if ( empty( $payment_method_id ) ) {
            /*
             * Previously the payment method was stored as source id.
             * As of 3.7.8 the Source will be completely replaced
             * by Payment Method.
             * So for backward compatibility, if payment method is not found,
             * we need to search for it in the source meta and update it if found.
             */
            $payment_method_id = $order->get_meta( self::source_id_key(), true );
            if ( ! empty( $payment_method_id ) ) {
                $order->delete_meta_data( self::source_id_key() );
                self::update_payment_method_id( $order, $payment_method_id );
                self::save( $order );
            }
        }

        return $payment_method_id;
    }

    /**
     * Updates stripe payment method id.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     * @param string   $payment_method_id
     *
     * @return void
     */
    public static function update_payment_method_id( WC_Order $order, $payment_method_id ) {
        $order->update_meta_data( self::payment_method_id_key(), $payment_method_id );
    }

    /**
     * Deletes payment method id of an order.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     *
     * @return void
     */
    public static function delete_payment_method_id( WC_Order $order ) {
        $order->delete_meta_data( self::payment_method_id_key() );
        // Delete source id as well to maintain the backward compatibility.
        $order->delete_meta_data( self::source_id_key() );
    }

    /**
     * Retrieves meta key for customer id.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function customer_id_key() {
        return Helper::meta_key( 'customer_id' );
    }

    /**
     * Retrieves stripe customer id.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function get_customer_id( WC_Order $order ) {
        return $order->get_meta( self::customer_id_key(), true );
    }

    /**
     * Updates stripe customer id.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function update_customer_id( WC_Order $order, $customer_id ) {
        $order->update_meta_data( self::customer_id_key(), $customer_id );
    }

    /**
     * Deletes stripe customer id.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return mixed
     */
    public static function delete_customer_id( WC_Order $order ) {
        $order->delete_meta_data( self::customer_id_key() );
    }

    /**
     * Retrieves meta key for payment type
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function payment_type_key() {
        return Helper::meta_key( 'payment_type' );
    }

    /**
     * Retrieves stripe payment type
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return mixed
     */
    public static function get_payment_type( WC_Order $order ) {
        $payment_type = $order->get_meta( self::payment_type_key(), true );
        if ( ! empty( $payment_type ) ) {
            return $payment_type;
        }

        if ( $order->get_parent_id() ) {
            return self::get_payment_type( wc_get_order( $order->get_parent_id() ) );
        }

        return 'card';
    }

    /**
     * Updates stripe payment type.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     * @param string   $payment_type
     *
     * @return void
     */
    public static function update_payment_type( WC_Order $order, $payment_type ) {
        $order->update_meta_data( self::payment_type_key(), $payment_type );
    }

    /**
     * Retrieves meta key for redirect processed flag.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function redirect_processed_key() {
        return Helper::meta_key( 'redirect_processed' );
    }

    /**
     * Check whether order redirect is processed.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return boolean
     */
    public static function is_redirect_processed( WC_Order $order ) {
        return $order->get_meta( self::redirect_processed_key(), true );
    }

    /**
     * Updates the flag if order redirect is processed.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     * @param string   $is_processed
     *
     * @return void
     */
    public static function update_redirect_processed( WC_Order $order, $is_processed = 'yes' ) {
        $order->update_meta_data( self::redirect_processed_key(), $is_processed );
    }

    /**
     * Retrieves disbursement mode key.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function disburse_mode_key() {
        return Helper::meta_key( 'disburse_mode' );
    }

    /**
     * Updates the disbursement mode of an order.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     * @param string   $disburse_mode
     *
     * @return void
     */
    public static function update_disburse_mode( WC_Order $order, $disburse_mode ) {
        $order->update_meta_data( self::disburse_mode_key(), $disburse_mode );
    }

    /**
     * Retrieves the disbursement mode of an order.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return void
     */
    public static function get_disburse_mode( WC_Order $order ) {
        return $order->get_meta( self::disburse_mode_key(), true );
    }

    /**
     * Retrieves meta key for stripe fee.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function stripe_fee_key() {
        return Helper::meta_key( 'fee' );
    }

    /**
     * Gets the Stripe fee for order.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return float
     */
    public static function get_stripe_fee( WC_Order $order ) {
        return wc_format_decimal( $order->get_meta( self::stripe_fee_key(), true ), 2 );
    }

    /**
     * Updates the Stripe fee for order.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     * @param float    $amount
     *
     * @return void
     */
    public static function update_stripe_fee( WC_Order $order, $amount = 0.0 ) {
        $order->update_meta_data( self::stripe_fee_key(), $amount );
    }

    /**
     * Deletes Stripe gateway fee.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     *
     * @return void
     */
    public static function delete_stripe_fee( WC_Order $order ) {
        $order->delete_meta_data( self::stripe_fee_key() );
    }

    /**
     * Retrives meta key for withdraw data.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function withdraw_data_key() {
        return Helper::meta_key( 'withdraw_data' );
    }

    /**
     * Retrieves withdraw data for a parent order.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return array
     */
    public static function get_withdraw_data( WC_Order $order ) {
        return $order->get_meta( self::withdraw_data_key(), true );
    }

    /**
     * Updates withdraw data for a parent order.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     * @param string   $withdraw_data
     *
     * @return void
     */
    public static function update_withdraw_data( WC_Order $order, $withdraw_data ) {
        $order->update_meta_data( self::withdraw_data_key(), $withdraw_data );
    }

    /**
     * Retrives meta key for withdraw balance added flag.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function withdraw_balance_added_key() {
        return Helper::meta_key( 'withdraw_balance_added' );
    }

    /**
     * Checks if withdraw balance added for an order.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return boolean
     */
    public static function is_withdraw_balance_added( WC_Order $order ) {
        return 'yes' === $order->get_meta( self::withdraw_balance_added_key(), true );
    }

    /**
     * Updates withdraw balance added flag.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     * @param string   $is_added
     *
     * @return void
     */
    public static function update_if_withdraw_balance_added( $order, $is_added = 'yes' ) {
        $order->update_meta_data( self::withdraw_balance_added_key(), $is_added );
    }

    /**
     * Retrives meta key for refund id.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function refund_id_key() {
        return Helper::meta_key( 'refund_ids' );
    }

    /**
     * Retrieves refund ids for an order.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return array
     */
    public static function get_refund_ids( WC_Order $order ) {
        return (array) $order->get_meta( self::refund_id_key(), true );
    }

    /**
     * Updates refund ids for an order.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     * @param string   $refund_id
     *
     * @return void
     */
    public static function update_refund_id( WC_Order $order, $refund_id ) {
        $refund_ids = self::get_refund_ids( $order );

        if ( is_array( $refund_ids ) ) {
            $refund_ids[] = $refund_id;
        } else {
            $refund_ids = [ $refund_id ];
        }

        $order->update_meta_data( self::refund_id_key(), $refund_ids );
    }

    /**
     * Retrives meta key for last refund id.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function last_refund_id_key() {
        return Helper::meta_key( 'last_refund_id' );
    }

    /**
     * Retrieves last refund id for an order.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return array
     */
    public static function get_last_refund_id( WC_Order $order ) {
        return (array) $order->get_meta( self::last_refund_id_key(), true );
    }

    /**
     * Updates last refund id for an order.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     * @param string   $refund_id
     *
     * @return void
     */
    public static function update_last_refund_id( WC_Order $order, $refund_id ) {
        $order->update_meta_data( self::last_refund_id_key(), $refund_id );
    }

    /**
     * Retrieves meta key for payment capture id.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function payment_capture_id_key() {
        return Helper::meta_key( 'payment_capture_id' );
    }

    /**
     * Retrievs payment capture id.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     *
     * @return string|false
     */
    public static function get_payment_capture_id( WC_Order $order ) {
        return $order->get_meta( self::payment_capture_id_key(), true );
    }

    /**
     * Updates payment capture id.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     * @param string   $payment_capture_id
     *
     * @return void
     */
    public static function update_payment_capture_id( WC_Order $order, $payment_capture_id ) {
        $order->update_meta_data( self::payment_capture_id_key(), $payment_capture_id );
    }

    /**
     * Retrives meta key for status final flag.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function status_final_key() {
        return Helper::meta_key( 'status_final' );
    }

    /**
     * Updates status final.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     *
     * @return void
     */
    public static function make_status_final( WC_Order $order ) {
        $order->update_meta_data( self::status_final_key(), true );
    }

    /**
     * Retrieves status final.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     *
     * @return string|false
     */
    public static function get_status_final( WC_Order $order ) {
        return $order->get_meta( self::status_final_key(), true );
    }

    /**
     * Deletes status final.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     *
     * @return void
     */
    public static function undo_status_final( WC_Order $order ) {
        $order->delete_meta_data( self::status_final_key() );
    }

    /**
     * Retrives meta key for status before hold.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function status_before_hold_key() {
        return Helper::meta_key( 'status_before_hold' );
    }

    /**
     * Updates status before hold.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     * @param string   $status
     *
     * @return void
     */
    public static function update_status_before_hold( WC_Order $order, $status ) {
        $order->update_meta_data( self::status_before_hold_key(), $status );
    }

    /**
     * Retrieves status before hold.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return string|false
     */
    public static function get_status_before_hold( WC_Order $order ) {
        return $order->get_meta( self::status_before_hold_key(), true );
    }

    /**
     * Retrieves meta key for vendor subscription order.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function vendor_subscription_order_key() {
        return Helper::meta_key( 'vendor_subscription_order' );
    }

    /**
     * Checks if order is a vendor subscription order.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     *
     * @return boolean
     */
    public static function is_vendor_subscription( WC_Order $order ) {
        return 'yes' === $order->get_meta( self::vendor_subscription_order_key(), true );
    }

    /**
     * Updates flag for vendor subscription order.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     * @param string   $status
     *
     * @return void
     */
    public static function update_vendor_subscription_order( WC_Order $order, $status = 'yes' ) {
        $order->update_meta_data( self::vendor_subscription_order_key(), $status );
    }

    /**
     * Retrives meta key for Stripe subscription id.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function stripe_subscription_id_key() {
        return Helper::meta_key( 'stripe_subscription_id' );
    }

    /**
     * Retrives Stripe subscription id.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     *
     * @return string|false
     */
    public static function get_stripe_subscription_id( WC_Order $order ) {
        return $order->get_meta( self::stripe_subscription_id_key(), true );
    }

    /**
     * Updates Stripe subscription id.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     * @param string   $subscription_id
     *
     * @return void
     */
    public static function update_stripe_subscription_id( WC_Order $order, $subscription_id ) {
        $order->update_meta_data( self::stripe_subscription_id_key(), $subscription_id );
    }

    /**
     * Retrieves meta key for subscription charge id.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function subscription_charge_id_key() {
        return Helper::meta_key( 'subscription_charge_id' );
    }

    /**
     * Retrieves subscription charge id.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     *
     * @return string|false
     */
    public static function get_subscription_charge_id( WC_Order $order ) {
        return $order->get_meta( self::subscription_charge_id_key(), true );
    }

    /**
     * Updates charge id for a subscription.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     * @param string   $charge_id
     *
     * @return void
     */
    public static function update_subscription_charge_id( WC_Order $order, $charge_id ) {
        $order->update_meta_data( self::subscription_charge_id_key(), $charge_id );
    }


    /**
     * Retrieves meta key for coupon id.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function coupon_id_key() {
        return Helper::meta_key( 'coupon_id' );
    }

    /**
     * Retrieves coupon id for Stripe.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     *
     * @return string|false
     */
    public static function get_stripe_coupon_id( WC_Order $order ) {
        return $order->get_meta( self::coupon_id_key(), true );
    }

    /**
     * Updates Stripe coupon id for order.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     * @param string   $coupon_id
     *
     * @return void
     */
    public static function update_stripe_coupon_id( WC_Order $order, $coupon_id ) {
        $order->update_meta_data( self::coupon_id_key(), $coupon_id );
    }

    /**
     * Retrieves meta key for awaiting disbursement flag.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function awaiting_disbursement_key() {
        return Helper::meta_key( 'awaiting_disbursement' );
    }

    /**
     * Retrieves meta data of awaiting disbursement flag.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     *
     * @return string|false
     */
    public static function has_awaiting_disbursement( WC_Order $order ) {
        return 'yes' === $order->get_meta( self::awaiting_disbursement_key(), true );
    }

    /**
     * Updates meta data of awaiting disbursement flag.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     * @param string   $is_awaiting
     *
     * @return void
     */
    public static function update_awaiting_disbursement( WC_Order $order, $is_awaiting = 'yes' ) {
        $order->update_meta_data( self::awaiting_disbursement_key(), $is_awaiting );
    }

    /**
     * Deletes awaiting disbursement flag.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     *
     * @return void
     */
    public static function delete_awaiting_disbursement( WC_Order $order ) {
        $order->delete_meta_data( self::awaiting_disbursement_key() );
    }

    /**
     * Retrieves meta key for save payment method flag.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function save_payment_method_key() {
        return Helper::meta_key( 'save_payment_method' );
    }

    /**
     * Indicates whether or not payment method should be saved.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     *
     * @return boolean
     */
    public static function should_save_payment_method( WC_Order $order ) {
        return 'yes' === $order->get_meta( self::save_payment_method_key(), true );
    }

    /**
     * Updates flag of whether or not payment method should be saved.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     *
     * @return void
     */
    public static function update_save_payment_method( WC_Order $order, $save = 'yes' ) {
        $order->update_meta_data( self::save_payment_method_key(), $save );
    }

    //** Default Dokan meta keys are below. No need to add prefix for these. **//

    /**
     * Updates gateway fee for dokan.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     * @param float|string $fee
     *
     * @return void
     */
    public static function update_dokan_gateway_fee( WC_Order $order, $fee ) {
        $order->update_meta_data( 'dokan_gateway_fee', $fee );
    }

    /**
     * Updates gateway fee for dokan.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return string|false
     */
    public static function get_dokan_gateway_fee( WC_Order $order ) {
        return $order->get_meta( 'dokan_gateway_fee', true );
    }

    /**
     * Updates who paid the gateway fee for dokan.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     * @param string   $paid_by 'seller' or 'admin'
     *
     * @return void
     */
    public static function update_gateway_fee_paid_by( WC_Order $order, $paid_by = 'seller' ) {
        $paid_by = 'seller' === $paid_by ? $paid_by : 'admin';
        $order->update_meta_data( 'dokan_gateway_fee_paid_by', $paid_by );
    }

    /**
     * Retrieves who paid the gateway fee for dokan.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return string|false
     */
    public static function get_gateway_fee_paid_by( WC_Order $order ) {
        return $order->get_meta( 'dokan_gateway_fee_paid_by', true );
    }

    /**
     * Updates meta data of tax fee recipient.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     * @param string   $recipient
     *
     * @return void
     */
    public static function update_tax_fee_recipient( WC_Order $order, $recipient = 'admin' ) {
        $order->update_meta_data( 'tax_fee_recipient', $recipient );
    }

    /**
     * Updates meta data of shipping fee recipient.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     * @param string   $recipient
     *
     * @return void
     */
    public static function update_shipping_fee_recipient( WC_Order $order, $recipient = 'admin' ) {
        $order->update_meta_data( 'shipping_fee_recipient', $recipient );
    }
}
