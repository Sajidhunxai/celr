<?php

namespace WeDevs\Dokanpro\Modules\MangoPay\WebhookEvents;

use WeDevs\DokanPro\Modules\MangoPay\Support\Meta;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;
use WeDevs\DokanPro\Modules\MangoPay\Processor\PayOut;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Webhook;
use WeDevs\DokanPro\Modules\MangoPay\Abstracts\WebhookEvent;

/**
 * Handles webhook for payout suceeded.
 *
 * @since 3.5.0
 */
class PayOutNormalSucceeded extends WebhookEvent {

    /**
     * Class constructor.
     *
     * @since 3.5.0
     *
     * @param string $event
     */
    public function __construct( $event ) {
        $this->set_event( $event );
    }

    /**
     * Handles the webhook event.
     *
     * @since 3.5.0
     *
     * @param array $payload
     *
     * @return void
     */
    public function handle( $payload ) {
        $transaction = PayOut::get( $payload['RessourceId'] );

        if ( ! $transaction ) {
            Webhook::log( sprintf( 'A %1$s webhook is discarded due to incorrect Resource ID: %2$s', $this->get_event(), $payload['RessourceId'] ) );
            return;
        }

        if ( ! preg_match( '/WC Order #(\d+)/', $transaction->Tag, $matches ) ) {
            return;
        }

        $order_id = $matches[1];
        $order    = wc_get_order( $order_id );
        if ( ! $order ) {
            Webhook::log( sprintf( 'A %s webhook is discarded due to incorrect Order: %s', $this->get_event(), $order_id ) );
            return;
        }

        if ( ! empty( Meta::get_payout_id( $order ) ) ) {
            return;
        }

        $order->add_order_note( sprintf( __( '[%1$s] Incoming webhook: %2$s. Resource ID: %3$s', 'dokan' ), Helper::get_gateway_title(), $this->get_event(), $payload['RessourceId'] ) );

        Meta::update_payout_id( $order, $transaction->Id );
        Meta::update_last_payout_attempt( $order, dokan_current_datetime()->getTimestamp() );
        Meta::update_payout_attempts( $order, ! empty( $payout['total_attempt'] ) ? (int) $payout['total_attempt'] + 1 : 1 );
        Meta::remove_failed_payout(
            array(
                'order_id' => $order->get_id(),
            )
        );
        $order->save_meta_data();
    }
}
