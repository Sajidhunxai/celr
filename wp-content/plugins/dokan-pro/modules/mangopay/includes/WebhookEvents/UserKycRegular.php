<?php

namespace WeDevs\Dokanpro\Modules\MangoPay\WebhookEvents;

use WeDevs\DokanPro\Modules\MangoPay\Support\Meta;
use WeDevs\DokanPro\Modules\MangoPay\Abstracts\WebhookEvent;
use WeDevs\DokanPro\Modules\MangoPay\Processor\User;

/**
 * Class to handle KYC verification webhook.
 *
 * @since 3.5.0
 */
class UserKycRegular extends WebhookEvent {

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
        $user = User::get( $payload['RessourceId'] );
        if ( ! $user ) {
            return false;
        }

        if ( ! preg_match( '/^wp_user_id:(\d+)$/', $user->Tag, $matches ) ) {
            return false;
        }

        $wp_user_id = $matches[1];
        Meta::update_regular_kyc_status( $wp_user_id, 'yes' );
    }
}
