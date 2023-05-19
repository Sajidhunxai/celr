<?php

namespace WeDevs\DokanPro\Modules\Stripe\WebhooksEvents;

use WC_Order_Query;
use Stripe\Subscription;
use WeDevs\DokanPro\Modules\Stripe\Helper;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\Stripe\Interfaces\WebhookHandleable;
use DokanPro\Modules\Subscription\SubscriptionPack;

defined( 'ABSPATH' ) || exit;

/**
 * It does happen on subscription plan switching
 *
 * @since 3.0.3
 */
class InvoicePaymentSucceeded implements WebhookHandleable {

    /**
     * Event holder
     *
     * @var null
     */
    private $event = null;

    /**
     * Constructor method
     *
     * @since 3.0.3
     *
     * @param \Stripe\Event $event
     *
     * @return void
     */
    public function __construct( $event ) {
        $this->event = $event;
    }

    /**
     * Modify query params.
     *
     * @since 3.4.3
     *
     * @param array $query
     * @param array $query_vars
     *
     * @return array
     */
    public function handle_custom_query_var( $query, $query_vars ) {
        if ( ! empty( $query_vars['search_transaction'] ) ) {
            $query['meta_query'][] = [
                'key'       => '_dokan_stripe_payment_capture_id',
                'value'     => $query_vars['search_transaction'],
                'compare'   => '=',
            ];
        }

        return $query;
    }

    /**
     * Handle the event.
     *
     * @since 3.0.3
     * @since 3.4.3 Added recurring payment system for subscription
     *
     * @return void
     */
    public function handle() {
        $invoice             = $this->event->data->object;
        $vendor_id           = Helper::get_vendor_id_by_subscription( $invoice->subscription );
        $subscription_stripe = Subscription::retrieve( $invoice->subscription );
        $period_start        = dokan_current_datetime()->setTimestamp( $subscription_stripe->current_period_start )->format( 'Y-m-d H:i:s' );
        $period_end          = dokan_current_datetime()->setTimestamp( $subscription_stripe->current_period_end )->format( 'Y-m-d H:i:s' );
        $order_id            = get_user_meta( $vendor_id, 'product_order_id', true );
        $product_id          = get_user_meta( $vendor_id, 'product_package_id', true );

        if ( ! class_exists( SubscriptionHelper::class ) || ! SubscriptionHelper::is_subscription_product( $product_id ) ) {
            return;
        }

        if ( ! $invoice->paid ) {
            return;
        }

        if ( empty( $invoice->billing_reason ) || in_array( $invoice->billing_reason, [ 'manual', 'upcoming', 'subscription_threshold' ], true ) ) {
            return;
        }

        // Get Subscription Order
        $subscription_order = wc_get_order( $order_id );
        if ( ! $subscription_order ) {
            dokan_log( '[Dokan Stripe] Webhook: InvoicePaymentSucceeded, Invalid Order id: ' . $order_id ); // maybe deleted order
            return;
        }

        // get subscription object
        $subscription = new SubscriptionPack( $product_id, $vendor_id );

        // check if we have active cancel subscription
        if ( $subscription->has_active_cancelled_subscrption() && $subscription->reactivate_subscription() ) {
            // update order status
            $subscription_order->add_order_note( __( 'Subscription Reactivated.', 'dokan' ) );
            return;
        }

        // get required data for webhook event
        $stripe_transaction_id = $invoice->id;

        if ( ! empty( $invoice->charge ) ) {
            update_post_meta( $order_id, '_stripe_subscription_charge_id', $invoice->charge );
        }

        if ( 'subscription_create' === $invoice->billing_reason ) {
            // activate subscription
            $subscription->activate_subscription( $subscription_order );

            // check trial exists
            if ( ! empty( $subscription_stripe->trial_end ) && $subscription_stripe->trial_end > time() ) {
                // setup trial data and do not complete order yet
                SubscriptionHelper::activate_trial_subscription( $subscription_order, $subscription, $subscription_stripe->id );
            } else {
                // translators: 1) Stripe Subscription ID
                $subscription_order->add_order_note( sprintf( __( 'Subscription activated.  Subscription ID: %s', 'dokan' ), $stripe_transaction_id ) );
                $subscription_order->payment_complete( $stripe_transaction_id );
                // Delete any trail metas for vendor
                SubscriptionHelper::delete_trial_meta_data( $vendor_id );
            }

            //indicate either a change to a subscription or a period advancement
        } elseif ( 'subscription' === $invoice->billing_reason ) {
            // update order status
            $subscription_order->add_order_note( __( 'Subscription Updated.', 'dokan' ) );

            // Manage renewal orders if billing reason is subscription cycle
        } elseif ( 'subscription_cycle' === $invoice->billing_reason ) {
            $renewal_order      = null;
            $stripe_currency    = strtolower( $invoice->currency );
            $processing_fee     = Helper::get_gateway_fee_from_charge_id( $invoice->charge );
            $stripe_order_total = (float) $invoice->amount_paid / 100;

            // check if transaction already recorded
            add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', [ $this, 'handle_custom_query_var' ], 10, 2 );
            $query = new WC_Order_Query(
                [
                    'search_transaction' => $stripe_transaction_id,
                    'customer_id'        => $subscription_order->get_customer_id(),
                    'limit'              => 1,
                    'type'               => 'shop_order',
                    'orderby'            => 'date',
                    'order'              => 'DESC',
                    'return'             => 'ids',
                ]
            );

            $orders = $query->get_orders();

            remove_filter( 'woocommerce_order_data_store_cpt_get_orders_query', [ $this, 'handle_custom_query_var' ], 10 );

            if ( ! empty( $orders ) ) {
                // transaction is already recorded
                $subscription_order->payment_complete( $stripe_transaction_id );

                return;
            }

            // create new renewal order
            $renewal_order = SubscriptionHelper::create_renewal_order( $subscription_order, $stripe_order_total );

            if ( is_wp_error( $renewal_order ) ) {
                dokan_log( '[Dokan Stripe] Create Renewal Order Failed. Error: ' . $renewal_order->get_error_message() );

                return;
            }

            // translators: %s: order number.
            $order_number = sprintf( _x( '#%s', 'hash before order number', 'dokan' ), $renewal_order->get_order_number() );

            // translators: %s: order number.
            $subscription_order_number = sprintf( _x( '#%s', 'hash before order number', 'dokan' ), $subscription_order->get_order_number() );

            // translators: placeholder is order ID
            $subscription_order->add_order_note( sprintf( __( 'Order %s created to record renewal.', 'dokan' ), sprintf( '<a href="%s">%s</a> ', esc_url( SubscriptionHelper::get_edit_post_link( $renewal_order->get_id() ) ), $order_number ) ) );

            // add order note on renewal order
            // translators: 1) subscription order number
            $renewal_order->add_order_note( sprintf( __( 'Order created to record renewal subscription for %s.', 'dokan' ), sprintf( '<a href="%s">%s</a> ', esc_url( SubscriptionHelper::get_edit_post_link( $subscription->get_id() ) ), $subscription_order_number ) ) );

            // set subscription order to renewal order
            $subscription_order = $renewal_order;

            // Add less required metadatas
            $subscription_order->update_meta_data( '_dokan_stripe_payment_capture_id', $stripe_transaction_id );
            $subscription_order->update_meta_data( '_dokan_stripe_payment_processing_fee', $processing_fee );
            $subscription_order->update_meta_data( '_dokan_stripe_payment_processing_currency', $stripe_currency );
            $subscription_order->update_meta_data( 'dokan_gateway_fee', $processing_fee );
            $subscription_order->update_meta_data( 'dokan_gateway_fee_paid_by', 'admin' );
            $subscription_order->update_meta_data( 'shipping_fee_recipient', 'admin' );
            $subscription_order->update_meta_data( 'tax_fee_recipient', 'admin' );
            $subscription_order->update_meta_data( '_dokan_vendor_subscription_order', 'yes' );

            $subscription_order->add_order_note(
                /* translators: %s: stripe processing fee */
                sprintf( __( 'Stripe processing fee is %s', 'dokan' ), $processing_fee )
            );

            // Finally save everything
            $subscription_order->save_meta_data();

            $test_mode = Helper::is_test_mode() ? __( 'Stripe Sandbox Transaction ID', 'dokan' ) : __( 'Stripe Transaction ID', 'dokan' );
            $subscription_order->add_order_note(
                /* translators: %1s: stripe mode text, %2s: stripe transaction id. */
                sprintf(
                    '%1$s: %2$s',
                    $test_mode,
                    $stripe_transaction_id
                )
            );

            // Complete Payment for Subscription
            $subscription_order->payment_complete( $stripe_transaction_id );
        }
    }
}
