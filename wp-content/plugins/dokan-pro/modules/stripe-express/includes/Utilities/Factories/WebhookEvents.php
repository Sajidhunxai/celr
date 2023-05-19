<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Utilities\Factories;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use Exception;
use BadMethodCallException;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Webhook;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\WebhookEvent;

/**
 * Class WebhookEvents.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Utilities\Factories
 */
class WebhookEvents {

    /**
     * Calss the defined static methods.
     *
     * @since 3.6.1
     *
     * @param string $method
     * @param array  $args
     *
     * @return mixed
     * @throws BadMethodCallException
     */
    public static function __callStatic( $method, $args ) {
        try {
            if ( 'handle' !== $method ) {
                throw new BadMethodCallException( sprintf( 'The %s method is not callable.', $method ), 422 );
            }

            if ( ! empty( $args[0] ) ) {
                $event         = $args[0];
                $event_handler = self::construct_handler( $event );

                if ( $event_handler instanceof WebhookEvent ) {
                    return $event_handler->$method();
                }

                do_action( 'dokan_stripe_express_events', $event, $method );
            }
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Webhook Rendering Error: %s', $e->getMessage() ) );
        }
    }

    /**
     * Constructs required event class instance.
     *
     * @since 3.6.1
     *
     * @param \Stripe\Event $event
     *
     * @return WebhookEvent|void
     * @throws DokanException
     */
    public static function construct_handler( $event ) {
        $events = Webhook::get_supported_events();
        $class  = null;

        if ( ! array_key_exists( $event->type, $events ) ) {
            return;
        }

        $class = $events[ $event->type ];
        $class = "\\WeDevs\\DokanPro\\Modules\\StripeExpress\\WebhookEvents\\{$class}";

        if ( ! class_exists( $class ) ) {
            throw new DokanException(
                'dokan_stripe_express_unsupported_event',
                /* translators: class name */
                sprintf( __( 'This %s is not supported yet', 'dokan' ), $class ),
                422
            );
        }

        return new $class( $event );
    }
}
