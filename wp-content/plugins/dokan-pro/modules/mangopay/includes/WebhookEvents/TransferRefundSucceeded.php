<?php

namespace WeDevs\Dokanpro\Modules\MangoPay\WebhookEvents;

use WeDevs\DokanPro\Modules\MangoPay\Support\Meta;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Order;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Refund;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Webhook;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Transfer;
use WeDevs\DokanPro\Modules\MangoPay\Abstracts\WebhookEvent;

/**
 * Class to handle Transfer refund webhook.
 *
 * @since 3.5.0
 */
class TransferRefundSucceeded extends WebhookEvent {

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
     * Handles the transfer refund webhook.
     *
     * @since 3.5.0
     *
     * @param array $payload
     *
     * @return void
     */
    public function handle( $payload ) {
        $refund = Refund::get( $payload['RessourceId'] );

        if ( empty( $refund ) || 'TRANSFER' !== $refund->InitialTransactionType || 'SUCCEEDED' !== $refund->Status ) {
            return;
        }

        $transfer = Transfer::get( $refund->InitialTransactionId );
        if ( ! $transfer ) {
            Webhook::log( sprintf( 'A %1$s webhook is discarded due to incorrect Resource ID: %2$s', $this->get_event(), $payload['RessourceId'] ) );
            return;
        }

        if ( ! preg_match( '/WC Order #(\d+)/', $transfer->Tag, $matches ) ) {
            return;
        }

        $order_id = $matches[1];
        $order    = wc_get_order( $order_id );
        if ( ! $order ) {
            Webhook::log( sprintf( 'A %s webhook is discarded due to incorrect Order: %d', $this->get_event(), $order_id ) );
            return;
        }

        if ( 'refunded' === $order->get_status() ) {
            return;
        }

        if ( empty( Meta::get_transfer_id( $order ) ) ) {
            return;
        }

        // insert new refund request
        $dokan_refund = Order::create_refund(
            array(
                'order_id'      => $order->get_id(),
                'seller_id'     => dokan_get_seller_id_by_order( $order->get_id() ),
                'refund_amount' => ( (float) $refund->DebitedFunds->Amount - (float) $refund->Fees->Amount ) / 100,
                'refund_reason' => "{$refund->RefundReason->RefundReasonType}: " . sprintf( ! empty( $refund->RefundReason->RefundReasonMessage ) ? $refund->RefundReason->RefundReasonMessage : 'Refunded via Dokan Mangopay' ),
            )
        );

        if ( is_wp_error( $dokan_refund ) ) {
            Webhook::log( 'Refund Error: ' . $dokan_refund->get_error_message() );
            return;
        }

        $order->add_order_note(
            /* translators: %s: mangopay refund id */
            sprintf( __( 'Refund Processed Via MangoPay Dashboard ( Refund ID: %s )', 'dokan' ), $payload['RessourceId'] )
        );

        Order::process_refund( $order, $dokan_refund, $refund );
    }
}