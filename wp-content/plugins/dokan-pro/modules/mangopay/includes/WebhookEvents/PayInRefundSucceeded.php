<?php

namespace WeDevs\Dokanpro\Modules\MangoPay\WebhookEvents;

use WC_Order;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Order;
use WeDevs\DokanPro\Modules\MangoPay\Processor\PayIn;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Refund;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Webhook;
use WeDevs\DokanPro\Modules\MangoPay\Abstracts\WebhookEvent;

/**
 * CLass to handle PayIn refund webhook.
 *
 * @since 3.5.0
 */
class PayInRefundSucceeded extends WebhookEvent {

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
        $refund = Refund::get( $payload['RessourceId'] );

        if ( empty( $refund ) || 'PAYIN' !== $refund->InitialTransactionType || 'SUCCEEDED' !== $refund->Status ) {
            return;
        }

        preg_match_all( '/vendor#(\d+):(\d+[\.\d+]*)/i', $refund->Tag, $matches );
        if ( empty( $matches[1] ) ) {
            return;
        }

        $vendor_amount = array();
        for ( $i = 0; $i < count( $matches[1] ); ++$i ) {
            $vendor_amount[ $matches[1][ $i ] ]['amount'] = $matches[2][ $i ];
        }

        if ( empty( $vendor_amount ) ) {
            return;
        }

        $payin = PayIn::get( $refund->InitialTransactionId );
        if ( empty( $payin ) ) {
            return;
        }

        if ( ! preg_match( '/^WC Order #(\d+)$/', $payin->Tag, $matches ) ) {
            return;
        }

        $order_id = $matches[1];
        $order 	  = new WC_Order( $order_id );

        if ( ! $order ) {
            Helper::log( "Invalid order: $order_id" );
            return;
        }

        if ( 'refunded' === $order->get_status() ) {
            return;
        }

        $order->add_order_note( sprintf( __( '[%1$s] Incoming Webhook %2$s Resource Id: %3$s', 'dokan' ), Helper::get_gateway_title(), $this->get_event(), $payload['RessourceId'] ) );

        if ( $order->get_meta( 'has_sub_order' ) ) {
            $sub_orders = get_children(
                array(
                    'post_parent' => $order->get_id(),
                    'post_type'   => 'shop_order'
                )
            );

            foreach ( $sub_orders as $sub_order ) {
                $vendor_id = dokan_get_seller_id_by_order( $sub_order->ID );

                if ( array_key_exists( $vendor_id, $vendor_amount ) ) {
                    $vendor_amount[ $vendor_id ]['order_id'] = $sub_order->ID;
                }
            }
        } else {
            $vendor_id = dokan_get_seller_id_by_order( $order->get_id() );
            $vendor_amount[ $vendor_id ]['order_id'] = $order->get_id();
        }

        foreach ( $vendor_amount as $vendor_id => $va_refund ) {
            // prepare data for further process this request
            $refund->Fees->Amount          = 0;
            $refund->DebitedFunds->Amount  = $va_refund['amount'] * 100;
            $refund->CreditedFunds->Amount = $va_refund['amount'] * 100;

            $dokan_refund = Order::create_refund(
                array(
                    'order_id'      => $va_refund['order_id'],
                    'seller_id'     => $vendor_id,
                    'refund_amount' => $va_refund['amount'],
                    'refund_reason' => "{$refund->RefundReason->RefundReasonType}: " . sprintf( ! empty( $refund->RefundReason->RefundReasonMessage ) ? $refund->RefundReason->RefundReasonMessage : 'Refunded via Mangopay Dashboard' ),
                )
            );

            if ( is_wp_error( $dokan_refund ) ) {
                Webhook::log( 'Refund Error: ' . $dokan_refund->get_error_message() );
                continue;
            }

            // Try to approve the refund.
            Order::process_refund( $order, $dokan_refund, $refund );
        }
    }
}
