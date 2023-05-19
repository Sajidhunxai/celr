<?php

namespace WeDevs\DokanPro\Modules\Stripe\WebhooksEvents;

use WeDevs\DokanPro\Modules\Stripe\Helper;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\Stripe\Interfaces\WebhookHandleable;

defined( 'ABSPATH' ) || exit;

class SubscriptionDeleted implements WebhookHandleable {

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
     * Hanle the event
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function handle() {
        $subscription = $this->event->data->object;

        $vendor_id     = Helper::get_vendor_id_by_subscription( $subscription->id );
        $order_id      = get_user_meta( $vendor_id, 'product_order_id', true );
        $has_recurring = get_user_meta( $vendor_id, '_customer_recurring_subscription', true );
        $product_id    = get_user_meta( $vendor_id, 'product_package_id', true );

        if ( ! class_exists( SubscriptionHelper::class ) || ! SubscriptionHelper::is_subscription_product( $product_id ) ) {
            return;
        }

        // validate order
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            dokan_log( '[Dokan Stripe] Webhook: SubscriptionDeleted, Invalid Order id: ' . $order_id ); // maybe deleted order
            return;
        }

        $order->add_order_note( __( 'Subscription Cancelled.', 'dokan' ) );

        if ( $has_recurring ) {
            SubscriptionHelper::delete_subscription_pack( $vendor_id, $order_id );
            delete_user_meta( $vendor_id, '_stripe_subscription_id' );
        }
    }
}
