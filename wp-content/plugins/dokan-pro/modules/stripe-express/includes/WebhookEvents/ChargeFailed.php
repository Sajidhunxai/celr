<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Order;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\WebhookEvent;

/**
 * Class to handle `charge.failed` webhook.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents
 */
class ChargeFailed extends WebhookEvent {

    /**
     * Handles the event.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function handle() {
        $charge = $this->get_payload();
        $order  = Order::get_order_by_charge_id( $charge->id );

        if ( ! $order ) {
            $this->log( 'Could not find order via charge ID: ' . $charge->id );
            return;
        }

        // If order status is already in failed status don't continue.
        if ( $order->has_status( 'failed' ) ) {
            return;
        }

        /* translators: gateway title */
        $message = sprintf( __( '[%s] Payment failed to clear', 'dokan' ), Helper::get_gateway_title() );

        if ( empty( OrderMeta::get_status_final( $order ) ) ) {
            $order->update_status( 'failed', $message );
        } else {
            $order->add_order_note( $message );
        }
    }
}
