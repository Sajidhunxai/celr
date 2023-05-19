<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Order;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\WebhookEvent;

/**
 * Class to handle `charge.suceeded` webhook.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents
 */
class ChargeSucceeded extends WebhookEvent {

    /**
     * Handles the event.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function handle() {
        $charge = $this->get_payload();

        // The following payment methods are synchronous so does not need to be handled via webhook
        if (
            isset( $charge->payment_method_details->type ) &&
            'card' === $charge->payment_method_details->type
        ) {
            return;
        }

        $order = Order::get_order_by_charge_id( $charge->id );

        if ( ! $order ) {
            $this->log( 'Could not find order via charge ID: ' . $charge->id );
            return;
        }

        if ( ! $order->has_status( 'on-hold' ) ) {
            return;
        }

        /*
         * When "Issue an authorization on checkout,and capture later"
         * setting is enabled, Stripe API still sends a "charge.succeeded"
         * webhook but the payment has not been captured yet.
         * This ensures that the payment has been captured,
         * before completing the payment.
         */
        if ( ! $charge->captured ) {
            return;
        }

        OrderMeta::update_charge_captured( $order );
        OrderMeta::update_transaction_id( $order, $charge->id );
        OrderMeta::save( $order );

        $order->payment_complete( $charge->id );

        $order->add_order_note(
            sprintf(
                /* translators: 1) gateway title, 2) transaction id */
                __( '[%1$s] Charge complete (Charge ID: %2$s)', 'dokan' ),
                Helper::get_gateway_title(),
                $charge->id
            )
        );
    }
}
