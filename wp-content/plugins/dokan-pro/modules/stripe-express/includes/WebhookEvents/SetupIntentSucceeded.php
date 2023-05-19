<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Processors\Order;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\WebhookEvent;

/**
 * Class to handle `setup_intent.succeeded` webhook.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents
 */
class SetupIntentSucceeded extends WebhookEvent {

    /**
     * Handles the event.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function handle() {
        $intent = $this->get_payload();
        $order  = Order::get_order_by_intent_id( $intent->id, true );

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

        $order->payment_complete();

        do_action( 'dokan_stripe_express_payment_completed', $order, $intent );

        Order::unlock_processing( $order->get_id() );
    }
}
