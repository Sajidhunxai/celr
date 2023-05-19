<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Factories;

use Exception;
use BadMethodCallException;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Webhook;
use WeDevs\DokanPro\Modules\MangoPay\Abstracts\WebhookEvent;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Class EventFactory
 *
 * @package WeDevs\DokanPro\Modules\PayPalMarketplace\Factories
 *
 * @since 3.5.0
 */
class EventFactory {

    /**
     * Call the defined static methods
     *
     * @param $method
     * @param $args
     *
     * @since 3.5.0
     *
     * @return mixed
     */
    public static function __callStatic( $method, $args ) {
        try {
            if ( 'handle' !== $method ) {
                throw new BadMethodCallException( sprintf( 'The %s method is not callable.', $method ), 422 );
            }

            if ( ! empty( $args[0] ) && ! empty( $args[1] ) ) {
                $event         = $args[0];
                $payload       = $args[1];
                $webhook_event = self::get( $event );

                if ( $webhook_event instanceof WebhookEvent ) {
                    return $webhook_event->$method( $payload );
                }

                do_action( 'dokan_mangopay_events', $event, $method );
            }
        } catch ( Exception $e ) {
            Helper::log( 'Rendering Error: ' . $e->getMessage(), 'Webhook' );
        }
    }

    /**
     * Create required event class instance
     *
     * @param $event
     *
     * @since 3.5.0
     *
     * @return WebhookEventHandler|void instance
     * @throws DokanException
     */
    public static function get( $event ) {
        $events = Webhook::get_supported_events();
        $class  = null;

        if ( ! array_key_exists( $event, $events ) ) {
            return;
        }

        $class = $events[ $event ];
        $class = "\\WeDevs\\DokanPro\\Modules\\MangoPay\\WebhookEvents\\{$class}";

        if ( ! class_exists( $class ) ) {
            throw new DokanException(
                'dokan_mangopay_unsupported_event',
                sprintf( 'This %s is not supported yet', $class ),
                422
            );
        }

        return new $class( $event );
    }
}
