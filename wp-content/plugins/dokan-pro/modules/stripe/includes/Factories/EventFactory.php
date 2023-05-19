<?php

namespace WeDevs\DokanPro\Modules\Stripe\Factories;

use WeDevs\DokanPro\Modules\Stripe\Helper;
use WeDevs\Dokan\Exceptions\DokanException;

defined( 'ABSPATH' ) || exit;

class EventFactory {

    /**
     * Create required event class instance
     *
     * @since 3.0.3
     *
     * @param \Stripe\Event $event
     *
     * @return \WebhooksEvents instance
     */
    public function get( $event ) {
        $events = Helper::get_supported_webhook_events();
        $class  = null;

        if ( ! array_key_exists( $event->type, $events ) ) {
            return;
        }

        $class = $events[ $event->type ];
        $class = "\\WeDevs\\DokanPro\\Modules\\Stripe\\WebhooksEvents\\{$class}";

        if ( ! class_exists( $class ) ) {
            throw new DokanException(
                'dokan_unsupported_event',
                sprintf( __( 'This %s is not supported yet', 'dokan' ), $class ),
                422
            );
        }

        return new $class( $event );
    }
}
