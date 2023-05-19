<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Traits\WebhookUtils;


/**
 * Class WebhookEvent
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts
 */
abstract class WebhookEvent {

    use WebhookUtils;

    /**
     * Event holder.
     *
     * @since 3.6.1
     *
     * @var \Stripe\Event
     */
    private $event;

    /**
     * Handles the event.
     *
     * @since 3.6.1
     * @since 3.7.8 Removed `$payload` parameter.
     *
     * @return void
     */
    abstract protected function handle();

    /**
     * Class constructor.
     *
     * @since 3.7.8
     *
     * @param \Stripe\Event $event Stripe event object.
     */
    public function __construct( $event ) {
        $this->set( $event );
    }

    /**
     * Sets the event.
     *
     * @since 3.6.1
     *
     * @param \Stripe\Event $event
     *
     * @return void
     */
    protected function set( $event ) {
        $this->event = $event;
    }

    /**
     * Retrieves the event.
     *
     * @since 3.6.1
     *
     * @return \Stripe\Event
     */
    protected function get() {
        return $this->event;
    }

    /**
     * Retrives the payload of the event.
     *
     * @since 3.7.8
     *
     * @return \Stripe\StripeObject
     */
    protected function get_payload() {
        return $this->event->data->object;
    }

    /**
     * Logs debugging data.
     *
     * @since 3.7.8
     *
     * @param string $message The message to be logged.
     * @param string $level   (Optional) The log level. Default `debug`.
     *
     * @return void
     */
    protected function log( $message, $level = 'debug' ) {
        Helper::log( sprintf( '%1$s :: %2$s', get_class( $this ), $message ), 'Webhook', $level );
    }
}
