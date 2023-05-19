<?php

namespace WeDevs\DokanPro\Modules\Razorpay\Factories;

use BadMethodCallException;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\Razorpay\Helper;
use WeDevs\DokanPro\Modules\Razorpay\Abstracts\WebhookEventHandler;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class EventFactory.
 *
 * @package WeDevs\DokanPro\Modules\Razorpay\Factories
 *
 * @since 3.5.0
 */
class EventFactory {
    /**
     * Call the defined static methods.
     *
     * @since 3.5.0
     *
     * @param $name
     * @param $arguments
     *
     * @return mixed
     * @throws DokanException
     */
    public static function __callStatic( $name, $arguments ) {
        try {
            if ( 'handle' !== $name ) {
                throw new BadMethodCallException( sprintf( 'The %s method is not callable.', $name ), 422 );
            }

            if ( ! empty( $arguments[0] ) ) {
                $event                          = $arguments[0];
                $webhook_event_handler_instance = self::get( $event );

                if ( $webhook_event_handler_instance instanceof WebhookEventHandler ) {
                    return $webhook_event_handler_instance->$name();
                }

                do_action( 'dokan_razorpay_events', $event, $name );
            }
        } catch ( \Exception $e ) {
            dokan_log( '[Dokan Razorpay] Webhook Rendering Error: ' . $e->getMessage() );
        }
    }

    /**
     * Create required event class instance.
     *
     * @since 3.5.0
     *
     * @param $event
     *
     * @return WebhookEventHandler|void instance
     * @throws DokanException
     */
    public static function get( $event ) {
        $events = Helper::get_webhook_events();
        $class  = null;

        if ( ! array_key_exists( $event->event, $events ) ) {
            return;
        }

        $class = $events[ $event->event ];
        $class = "\\WeDevs\\DokanPro\\Modules\\Razorpay\\Webhook\\Events\\{$class}";

        if ( ! class_exists( $class ) ) {
            throw new DokanException(
                'dokan_razorpay_unsupported_event',
                /* translators: 1: Event name */
                sprintf( __( 'This %s is not supported yet', 'dokan' ), $class ),
                422
            );
        }

        return new $class( $event );
    }
}
