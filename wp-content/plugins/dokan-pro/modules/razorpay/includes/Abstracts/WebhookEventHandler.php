<?php

namespace WeDevs\DokanPro\Modules\Razorpay\Abstracts;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class WebhookHandler.
 *
 * @package WeDevs\Dokan\Gateways\Razorpay\Interfaces
 *
 * @since 3.5.0
 */
abstract class WebhookEventHandler {

    /**
     * Event holder.
     *
     * @var object
     */
    private $event;

    /**
     * Handle the event.
     *
     * @since 3.5.0
     *
     * @return void
     */
    abstract public function handle();

    /**
     * Set the event.
     *
     * @param $event
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function set_event( $event ) {
        $this->event = $event;
    }

    /**
     * Get the event.
     *
     * @since 3.5.0
     *
     * @return array|object
     */
    public function get_event() {
        return $this->event;
    }
}
