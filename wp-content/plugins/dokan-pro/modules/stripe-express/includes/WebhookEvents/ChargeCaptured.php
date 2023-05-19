<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Order;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\WebhookEvent;

/**
 * Class to handle `charge.captured` webhook.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents
 */
class ChargeCaptured extends WebhookEvent {

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

        if ( Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return;
        }

        if ( empty( OrderMeta::get_transaction_id( $order ) ) ) {
            return;
        }

        if ( OrderMeta::is_charge_captured( $order ) ) {
            return;
        }

        OrderMeta::update_charge_captured( $order );
        OrderMeta::update_transaction_id( $order, $charge->id );
        OrderMeta::save( $order );

        // Check and see if capture is partial.
        if ( 0 < $charge->amount_refunded ) {
            $partial_amount = $this->get_partial_amount_to_charge( $charge );
            $order->set_total( $partial_amount );
            $order->add_order_note(
                sprintf(
                    /* translators: 1) gateway title, 2) partial captured amount */
                    __( '[%1$s] This charge was partially captured via Stripe Dashboard in the amount of: %2$s', 'dokan' ),
                    Helper::get_gateway_title(),
                    $partial_amount
                )
            );
        } else {
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

    /**
     * Calculates the partial amount to charge.
     *
     * @since 3.6.1
     *
     * @param \Stripe\Charge $charge
     *
     * @return float
     */
    public function get_partial_amount_to_charge( $charge ) {
        $amount = ( $charge->amount - $charge->amount_refunded ) / 100;

        if ( in_array( strtolower( $charge->currency ), Helper::no_decimal_currencies(), true ) ) {
            $amount = $charge->amount - $charge->amount_refunded;
        }

        return $amount;
    }
}
