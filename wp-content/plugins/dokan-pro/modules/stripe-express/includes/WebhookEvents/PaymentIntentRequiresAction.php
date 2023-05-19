<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Processors\Order;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\WebhookEvent;

/**
 * Class to handle `payment_intent.requires_action` webhook.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents
 */
class PaymentIntentRequiresAction extends WebhookEvent {

    /**
     * Handles the event.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function handle() {
        $intent = $this->get_payload();
        $order  = Order::get_order_by_intent_id( $intent->id );

        if ( ! $order ) {
            $this->log( 'Could not find order via intent ID: ' . $intent->id );
            return;
        }

        if ( ! $order->has_status( [ 'pending', 'failed' ] ) ) {
            return;
        }

        if ( Order::lock_processing( $order->get_id(), 'intent', $intent->id ) ) {
            return;
        }

        /**
         * Fires when payment intent requires further action.
         *
         * @since 3.7.8
         *
         * @param WC_Order              $order
         * @param \Stripe\PaymentIntent $intent
         */
        do_action( 'dokan_stripe_express_payment_intent_requires_action', $order, $intent );

        Order::unlock_processing( $order->get_id() );
    }
}
