<?php

namespace WeDevs\Dokanpro\Modules\MangoPay\WebhookEvents;

use WeDevs\DokanPro\Modules\MangoPay\Support\Meta;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Refund;
use WeDevs\DokanPro\Modules\MangoPay\Processor\PayOut;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Webhook;
use WeDevs\DokanPro\Modules\MangoPay\Abstracts\WebhookEvent;

/**
 * Class to handle Payout refund succeeded webhook.
 *
 * @since 3.5.0
 */
class PayOutRefundSucceeded extends WebhookEvent {

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
     * Handles the webhook.
     *
     * @since 3.5.0
     *
     * @param array $payload
     *
     * @return void
     */
    public function handle( $payload ) {
        $refund = Refund::get( $payload['RessourceId'] );

        if ( empty( $refund ) ) {
            Webhook::log( sprintf( 'A %s webhook is discarded due to incorrect Resource ID: %s', $this->get_event(), $payload['RessourceId'] ) );
            return;
        }

        $payout = PayOut::get( $refund->InitialTransactionId );
        if ( empty( $payout ) ) {
            return;
        }

        if ( ! preg_match( '/WC Order #(\d+)/', $payout->Tag, $matches ) ) {
            return;
        }

        $order_id = $matches[1];
        $order    = wc_get_order( $order_id );
        if ( ! $order ) {
            Webhook::log( sprintf( 'A %s webhook is discarded due to incorrect Order: %s', $this->get_event(), $order_id ) );
            return;
        }

        $order->add_order_note(
            sprintf(
                __( '[%1$s] Payout to the vendor: %2$s has failed. Amount: %3$s%4$s', 'dokan' ),
                Helper::get_gateway_title(),
                dokan_get_seller_id_by_order( $order_id ),
                get_woocommerce_currency_symbol( $refund->DebitedFunds->Currency ),
                $refund->DebitedFunds->Amount / 100
            )
        );

        $parent_order = false;
        if ( $order->get_parent_id() ) {
            $parent_order = wc_get_order( $order->get_parent_id() );
        }

        if ( $parent_order ) {
            $parent_order->add_order_note(
                sprintf(
                    __( '[%1$s] Payout to the vendor: %2$s has failed. Amount: %3$s%4$s', 'dokan' ),
                    Helper::get_gateway_title(),
                    dokan_get_seller_id_by_order( $order_id ),
                    get_woocommerce_currency_symbol( $refund->DebitedFunds->Currency ),
                    $refund->DebitedFunds->Amount / 100
                )
            );
        }

        Helper::warn_owner(
            __( 'Payout to your bank account has failed. Please update your bank account and other verification process if needed.', 'dokan' ),
            $order_id
        );

        $total_attemts = Meta::get_payout_attempts( $order );
        if ( empty( $total_attemts ) ) {
            $total_attemts = 0;
        }

        $total_attemts = (int) $total_attemts + 1;
        $last_attempt  = dokan_current_datetime()->getTimestamp();

        Meta::update_payout_id( $order, '' );
        Meta::update_last_payout_attempt( $order, $last_attempt );
        Meta::update_payout_attempts( $order, $total_attemts );
        Meta::update_failed_payouts(
            array(
                'order_id'     => $order_id,
                'vendor_id'    => dokan_get_seller_id_by_order( $order_id ),
                'total_attemt' => $total_attemts,
                'last_attempt' => $last_attempt,
                'currency'     => $refund->DebitedFunds->Currency,
                'withdraw'     => array(
                    'amount' => $refund->DebitedFunds->Amount / 100
                ),
            )
        );
        $order->save_meta_data();
    }
}