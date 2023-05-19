<?php

namespace WeDevs\Dokanpro\Modules\MangoPay\WebhookEvents;

use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Order;
use WeDevs\DokanPro\Modules\MangoPay\Processor\PayIn;
use WeDevs\DokanPro\Modules\MangoPay\Abstracts\WebhookEvent;

/**
 * Class to handle PayIn success webhook
 *
 * @since 3.5.0
 */
class PayInNormalSucceded extends WebhookEvent {

    /**
     * Class constructor
     *
     * @since 3.5.0
     *
     * @param string $event
     */
    public function __construct( $event ) {
        $this->set_event( $event );
    }

    /**
     * Handles the webhook event
     *
     * @since 3.5.0
     *
     * @param array $payload
     *
     * @return void
     */
    public function handle( $payload ) {
        $payin = PayIn::get( $payload['RessourceId'] );

        if ( ! $payin ) {
            Helper::warn_owner(
                sprintf(
                    __( 'MangoPay Payin not found for Resource ID: %1$s', 'dokan' ),
                    $payload['RessourceId']
                )
            );
            Helper::exit_with_404();
        }

        $order_id = PayIn::verify( $payin, $payload );
        if ( ! $order_id ) {
            return;
        }

        /*
        * Save the transaction ID as the WC order meta
        * this needs to be done before calling payment->complete()
        * to handle auto-completed orders such as downloadables and virtual products & bookings
        */
        Order::save_transaction( $order_id, $payin );

        // at last, validate this order
        Order::validate( $order_id );
    }
}
