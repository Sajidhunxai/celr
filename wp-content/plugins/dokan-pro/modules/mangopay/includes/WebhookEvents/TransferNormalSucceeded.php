<?php

namespace WeDevs\Dokanpro\Modules\MangoPay\WebhookEvents;

use WeDevs\DokanPro\Modules\MangoPay\Processor\Webhook;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Transfer;
use WeDevs\DokanPro\Modules\MangoPay\Abstracts\WebhookEvent;

/**
 * Class to handle Transfer success webhook.
 *
 * @since 3.5.0
 */
class TransferNormalSucceeded extends WebhookEvent {

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
        $transfer = Transfer::get( $payload['RessourceId'] );

        if ( ! $transfer ) {
            Webhook::log( sprintf( 'A %s webhook is discarded due to incorrect Resource ID: %s', $this->get_event(), $payload['RessourceId'] ) );
            return;
        }
    }
}