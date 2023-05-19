<?php

namespace WeDevs\DokanPro\Modules\Stripe\WebhooksEvents;

use WeDevs\DokanPro\Modules\Stripe\Helper;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\Stripe\Interfaces\WebhookHandleable;

defined( 'ABSPATH' ) || exit;

class SubscriptionUpdated implements WebhookHandleable {

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

        if ( 'active' !== $subscription->status ) {
            return;
        }

        $vendor_id    = Helper::get_vendor_id_by_subscription( $subscription->id );
        $period_start = dokan_current_datetime()->setTimestamp( $subscription->current_period_start )->format( 'Y-m-d H:i:s' );
        $product_id   = get_user_meta( $vendor_id, 'product_package_id', true );

        if ( ! class_exists( SubscriptionHelper::class ) || ! SubscriptionHelper::is_subscription_product( $product_id ) ) {
            return;
        }

        if ( ! empty( $subscription->cancel_at ) ) {
            update_user_meta( $vendor_id, 'product_pack_enddate', dokan_current_datetime()->setTimestamp( $subscription->cancel_at )->format( 'Y-m-d H:i:s' ) );
            update_user_meta( $vendor_id, 'dokan_has_active_cancelled_subscrption', true );
        } else {
            update_user_meta( $vendor_id, 'dokan_has_active_cancelled_subscrption', false );
            update_user_meta( $vendor_id, 'product_pack_startdate', $period_start );
            update_user_meta( $vendor_id, 'can_post_product', '1' );
            update_user_meta( $vendor_id, 'has_pending_subscription', false );

            $dokan_subscription = dokan()->subscription->get( $product_id );
            update_user_meta( $vendor_id, 'product_pack_enddate', $dokan_subscription->get_product_pack_end_date() );

            do_action( 'dokan_vendor_purchased_subscription', $vendor_id );
        }
    }
}
