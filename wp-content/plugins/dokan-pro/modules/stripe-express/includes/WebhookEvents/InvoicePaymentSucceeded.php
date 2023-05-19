<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use DokanPro\Modules\Subscription\SubscriptionPack;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Order;
use WeDevs\DokanPro\Modules\StripeExpress\Support\UserMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Payment;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Subscription;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\WebhookEvent;

/**
 * Class to handle `invoice.payment_succeeded` webhook.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents
 */
class InvoicePaymentSucceeded extends WebhookEvent {

    /**
     * Handles the event.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function handle() {
        if ( ! Subscription::has_vendor_subscription_module() ) {
            return;
        }

        $invoice = $this->get_payload();
        if ( ! $invoice->paid ) {
            return;
        }

        if ( empty( $invoice->billing_reason ) || in_array( $invoice->billing_reason, Subscription::DISALLOWED_BILLING_REASONS, true ) ) {
            return;
        }

        $vendor_id  = Subscription::get_vendor_id_by_subscription( $invoice->subscription );
        $product_id = UserMeta::get_product_pack_id( $vendor_id );
        if ( ! $product_id || ! SubscriptionHelper::is_subscription_product( $product_id ) ) {
            return;
        }

        $order_id = UserMeta::get_product_order_id( $vendor_id );
        $order    = wc_get_order( $order_id );
        if ( ! $order ) {
            $this->log( sprintf( 'Invalid Order ID: %s', $order_id ) );
            return;
        }

        $subscription = new SubscriptionPack( $product_id, $vendor_id );
        if ( $subscription->has_active_cancelled_subscrption() && $subscription->reactivate_subscription() ) {
            $order->add_order_note( __( 'Subscription Reactivated.', 'dokan' ) );
            return;
        }

        if ( ! empty( $invoice->charge ) ) {
            OrderMeta::update_subscription_charge_id( $order, $invoice->charge );
            OrderMeta::save( $order );
        }

        switch ( $invoice->billing_reason ) {
            case 'subscription_create':
                try {
                    $subscription->activate_subscription( $order );

                    $stripe_subscription = Subscription::get( $invoice->subscription );

                    // If trial period exists, setup trial data and do not complete order yet.
                    if ( ! empty( $stripe_subscription->trial_end ) && $stripe_subscription->trial_end > time() ) {
                        SubscriptionHelper::activate_trial_subscription( $order, $subscription, $stripe_subscription->id );
                        break;
                    }

                    /* translators: 1) Stripe Invoice ID */
                    Order::add_note( $order, sprintf( __( 'Subscription activated. Invoice ID: %s', 'dokan' ), $invoice->id ) );
                    $order->payment_complete( $invoice->id );

                    SubscriptionHelper::delete_trial_meta_data( $vendor_id );
                } catch ( \Exception $e ) {
                    break;
                }
                break;

            case 'subscription':
                Order::add_note( $order, __( 'Subscription updated.', 'dokan' ) );
                break;

            case 'subscription_cycle':
                $processing_fee = Payment::get_gateway_fee_from_charge( $invoice->charge );
                $order_total    = (float) $invoice->amount_paid / 100;

                // Check if transaction already recorded
                add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', [ $this, 'handle_custom_query_var' ], 10, 2 );

                $query = new \WC_Order_Query(
                    [
                        'search_transaction' => $invoice->id,
                        'customer_id'        => $order->get_customer_id(),
                        'limit'              => 1,
                        'type'               => 'shop_order',
                        'orderby'            => 'date',
                        'order'              => 'DESC',
                        'return'             => 'ids',
                    ]
                );
                $orders = $query->get_orders();

                remove_filter( 'woocommerce_order_data_store_cpt_get_orders_query', [ $this, 'handle_custom_query_var' ], 10 );

                // Transaction is already recorded.
                if ( ! empty( $orders ) ) {
                    $order->payment_complete( $invoice->id );
                    return;
                }

                // Create new renewal order
                $renewal_order = SubscriptionHelper::create_renewal_order( $order, $order_total );
                if ( is_wp_error( $renewal_order ) ) {
                    $this->log( 'Create Renewal Order Failed. Error: ' . $renewal_order->get_error_message() );
                    return;
                }

                // Add renewal order number on order note
                $order->add_order_note(
                    sprintf(
                        /* translators: renewal order number with link */
                        __( 'Order %s created to record renewal.', 'dokan' ),
                        sprintf(
                            '<a href="%s">%s</a> ',
                            esc_url( SubscriptionHelper::get_edit_post_link( $renewal_order->get_id() ) ),
                            /* translators: renewal order number */
                            sprintf( _x( '#%s', 'hash before order number', 'dokan' ), $renewal_order->get_order_number() )
                        )
                    )
                );

                // Add order number on renewal order note
                $renewal_order->add_order_note(
                    sprintf(
                        /* translators: 1) subscription order number with link */
                        __( 'Order created to record renewal subscription for %s.', 'dokan' ),
                        sprintf(
                            '<a href="%s">%s</a> ',
                            esc_url( SubscriptionHelper::get_edit_post_link( $subscription->get_id() ) ),
                            /* translators: order number */
                            sprintf( _x( '#%s', 'hash before order number', 'dokan' ), $order->get_order_number() )
                        )
                    )
                );

                // Add less required metadatas
                OrderMeta::update_payment_capture_id( $renewal_order, $invoice->id );
                OrderMeta::update_stripe_fee( $renewal_order, $processing_fee );
                OrderMeta::update_dokan_gateway_fee( $renewal_order, $processing_fee );
                OrderMeta::update_gateway_fee_paid_by( $renewal_order, 'admin' );
                OrderMeta::update_shipping_fee_recipient( $renewal_order, 'admin' );
                OrderMeta::update_tax_fee_recipient( $renewal_order, 'admin' );
                OrderMeta::update_vendor_subscription_order( $renewal_order );
                OrderMeta::save( $renewal_order );

                /* translators: processing fee */
                Order::add_note( $renewal_order, sprintf( __( 'Processing fee: %s', 'dokan' ), $processing_fee ) );
                /* translators: transaction id */
                Order::add_note( $renewal_order, sprintf( __( 'Transaction ID: %s', 'dokan' ), $invoice->id ) );

                // Complete Payment for Subscription
                $renewal_order->payment_complete( $invoice->id );
                break;
        }
    }

    /**
     * Modifies query params according to need.
     *
     * @since 3.7.8
     *
     * @param array $query
     * @param array $query_vars
     *
     * @return array
     */
    public function handle_custom_query_var( $query, $query_vars ) {
        if ( ! empty( $query_vars['search_transaction'] ) ) {
            $query['meta_query'][] = [
                'key'       => OrderMeta::payment_capture_id_key(),
                'value'     => $query_vars['search_transaction'],
                'compare'   => '=',
            ];
        }

        return $query;
    }
}
