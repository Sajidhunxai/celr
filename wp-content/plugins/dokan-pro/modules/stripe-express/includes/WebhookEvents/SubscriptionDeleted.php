<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\UserMeta;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Subscription;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\WebhookEvent;

/**
 * Class to handle `customer.subscription.updated` webhook.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents
 */
class SubscriptionDeleted extends WebhookEvent {

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

        $subscription = $this->get_payload();
        $vendor_id    = Subscription::get_vendor_id_by_subscription( $subscription );
        $product_id   = UserMeta::get_product_pack_id( $vendor_id );

        if ( ! $product_id || ! SubscriptionHelper::is_subscription_product( $product_id ) ) {
            return;
        }

        $order_id = UserMeta::get_product_order_id( $vendor_id );
        $order    = wc_get_order( $order_id );
        if ( ! $order ) {
            $this->log( sprintf( 'Could not find the order id: %s', $order_id ) );
            return;
        }

        if ( UserMeta::has_customer_recurring_subscription( $vendor_id ) ) {
            SubscriptionHelper::delete_subscription_pack( $vendor_id, $order_id );
            UserMeta::delete_stripe_subscription_id( $vendor_id );
        }

        $order->add_order_note( __( 'Subscription Cancelled.', 'dokan' ) );
    }
}
