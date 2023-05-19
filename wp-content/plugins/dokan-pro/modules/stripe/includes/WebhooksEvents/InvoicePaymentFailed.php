<?php

namespace WeDevs\DokanPro\Modules\Stripe\WebhooksEvents;

use WeDevs\DokanPro\Modules\Stripe\Helper;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\Stripe\Interfaces\WebhookHandleable;

defined( 'ABSPATH' ) || exit;

class InvoicePaymentFailed implements WebhookHandleable {

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
        $invoice    = $this->event->data->object;
        $vendor_id  = Helper::get_vendor_id_by_subscription( $invoice->subscription );
        $product_id = get_user_meta( $vendor_id, 'product_package_id', true );
        $order_id   = get_user_meta( $vendor_id, 'product_order_id', true );

        if ( ! class_exists( SubscriptionHelper::class ) || ! SubscriptionHelper::is_subscription_product( $product_id ) ) {
            return;
        }

        // Terminate user to update product
        update_user_meta( $vendor_id, 'can_post_product', '0' );

        // Make sure this is final attempt
        if ( isset( $invoice->next_payment_attempt ) && $invoice->next_payment_attempt == null ) {
            SubscriptionHelper::delete_subscription_pack( $vendor_id, $order_id );
        }
    }
}
