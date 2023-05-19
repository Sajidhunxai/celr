<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Processors\Order;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Payment;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\WebhookEvent;

/**
 * Class to handle `payment_intent.succeeded` webhook.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents
 */
class PaymentIntentSucceeded extends WebhookEvent {

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

        // Process the payment method from intent
        $save_payment_method = OrderMeta::should_save_payment_method( $order );
        Payment::process_payment_method( $order, $intent, $save_payment_method );

        // Process valid response.
        $response = Payment::get_latest_charge_from_intent( $intent );
        if ( ! $response ) {
            $response = $intent;
        }

        Payment::process_response( $response, $order );
        Order::unlock_processing( $order->get_id() );
    }
}
