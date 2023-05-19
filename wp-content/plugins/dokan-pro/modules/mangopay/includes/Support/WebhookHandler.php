<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Support;

defined( 'ABSPATH' ) || exit;

use WeDevs\DokanPro\Modules\MangoPay\Processor\Webhook;
use WeDevs\DokanPro\Modules\MangoPay\Factories\EventFactory;

/**
 * Webhook handler class
 *
 * @since 3.5.0
 */
class WebhookHandler {

    /**
     * Class constructor
     *
     * @since 3.5.0
     */
    function __construct() {
        $this->hooks();
    }

    /**
     * Registers required hooks
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function hooks() {
        $events = array_keys( Webhook::get_supported_events() );
        foreach ( $events as $event ) {
            add_action(
                'woocommerce_api_' . Webhook::generate_event_slug( $event ),
                array( $this, 'handle_request' )
            );
        }
    }

    /**
     * Handles incoming webhook request
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function handle_request() {
        Webhook::log( 'Handling request url received at: ' . current_time( 'Y-m-d H:i:s', 0 ), 'info' );

        $event_type = Webhook::get_suffix();
        $payload    = Webhook::get_payload();

        Webhook::log( "Event Type: $event_type . Payload: " .print_r( $payload, true ) );

        if ( ! Webhook::is_authentic( $event_type, $payload ) ) {
            Webhook::log( 'Incoming hook is not authentic' );
            Helper::exit_with_404();
        }

        EventFactory::handle( $event_type, $payload );

        // Send out our http response
        echo '200 (OK)';
        exit;
    }

    /**
     * Registers webhooks
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function register() {
        Webhook::register_all();
    }

    /**
     * Deregisters webhooks
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function deregister() {
        Webhook::deregister_all();
    }
}