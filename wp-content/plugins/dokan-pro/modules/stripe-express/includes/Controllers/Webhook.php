<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Controllers;

defined( 'ABSPATH' ) || exit;

use Exception;
use Stripe\Event;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Settings;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Factories\WebhookEvents;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Webhook as WebhookProcessor;

/**
 * Class to handle Webhook operations.
 *
 * @since 3.6.1
 *
 * @package \WeDevs\DokanPro\Modules\StripeExpress\Controllers
 */
class Webhook {

    /**
     * Webhook secret key.
     *
     * @since 3.6.1
     *
     * @var string
     */
    protected $webhook_secret;

    /**
     * Class constructor.
     *
     * @since 3.6.1
     */
    public function __construct() {
        $this->webhook_secret = Settings::get_webhook_secret();
        add_action( 'init', [ $this, 'hooks' ] );

        /*
         * Get/set the time we began monitoring the health of webhooks by fetching it.
         * This should be roughly the same as the activation time of the version of the
         * plugin when this code first appears.
         */
        WebhookProcessor::get_monitoring_began_time();
    }

    /**
     * Registers all hooks.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function hooks() {
        add_action( 'woocommerce_api_' . WebhookProcessor::prefix(), [ $this, 'handle_events' ] );
    }

    /**
     * Handle events which are comming from stripe
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function handle_events() {
        if ( ! Helper::is_gateway_ready() ) {
            return;
        }

        if ( ! isset( $_SERVER['REQUEST_METHOD'] ) || 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
            return;
        }

        $request_body    = file_get_contents( 'php://input' );
        $request_headers = array_change_key_case( $this->get_request_headers(), CASE_UPPER );

        $this->validate_request( $request_headers, $request_body );

        try {
            $event = Event::constructFrom(
                json_decode( $request_body, true )
            );

            WebhookEvents::handle( $event );
            WebhookProcessor::set_last_success_time( $event->created );
            status_header( 200 );
            exit;
        } catch ( Exception $e ) {
            Helper::log( 'Webhook Processing Error (Event ): ' . $e->getMessage(), 'Webhook', 'error' );
            exit;
        }
    }

    /**
     * Register webhook and remove old webhook endpoints from stripe
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public function register() {
        return WebhookProcessor::create();
    }

    /**
     * Deregisters all webhooks.
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public function deregister() {
        return WebhookProcessor::delete();
    }

    /**
     * Validates the request to ensure authenticity.
     *
     * @since 3.6.1
     *
     * @param array $request_headers
     * @param array $request_body
     *
     * @return void
     */
    public function validate_request( $request_headers, $request_body ) {
        $validation_status = $this->verify_request_status( $request_headers, $request_body );
        if ( WebhookProcessor::STATUS_VALIDATION_SUCCEEDED !== $validation_status ) {
            Helper::log( 'Incoming webhook failed validation: ' . print_r( $request_body, true ), 'Webhook' );
            WebhookProcessor::set_last_failure_time( time() );
            WebhookProcessor::set_last_error( $validation_status );

            /*
             * A webhook endpoint must return a 2xx HTTP status code
             * to prevent future webhook delivery failures.
             * @see https://stripe.com/docs/webhooks/build#acknowledge-events-immediately
             */
            status_header( 204 );
            exit;
        }
    }

    /**
     * Gets the incoming request headers. Some servers are not using
     * Apache and "getallheaders()" will not work so we may need to
     * build our own headers.
     *
     * @since 3.6.1
     *
     * @return array
     */
    public function get_request_headers() {
        if ( function_exists( 'getallheaders' ) ) {
            return getallheaders();
        }

        $headers = [];

        foreach ( $_SERVER as $name => $value ) {
            if ( 'HTTP_' === substr( $name, 0, 5 ) ) {
                $headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
            }
        }

        return $headers;
    }

    /**
     * Verify the incoming webhook notification to make sure it is legit.
     *
     * @since 3.6.1
     *
     * @param array $request_headers The request headers from Stripe.
     * @param array $request_body    The request body from Stripe.
     *
     * @return string The validation result
     */
    public function verify_request_status( $request_headers, $request_body ) {
        if ( empty( $request_headers ) ) {
            return WebhookProcessor::STATUS_EMPTY_HEADERS;
        }

        if ( empty( $request_body ) ) {
            return WebhookProcessor::STATUS_EMPTY_BODY;
        }

        if ( empty( $this->webhook_secret ) ) {
            return $this->validate_request_user_agent( $request_headers );
        }

        // Check for a valid signature.
        $signature_format = '/^t=(?P<timestamp>\d+)(?P<signatures>(,v\d+=[a-z0-9]+){1,2})$/';
        if ( empty( $request_headers['STRIPE-SIGNATURE'] ) || ! preg_match( $signature_format, $request_headers['STRIPE-SIGNATURE'], $matches ) ) {
            return WebhookProcessor::STATUS_SIGNATURE_INVALID;
        }

        // Verify the timestamp.
        $timestamp = intval( $matches['timestamp'] );
        if ( abs( $timestamp - time() ) > 5 * MINUTE_IN_SECONDS ) {
            return WebhookProcessor::STATUS_TIMESTAMP_OUT_OF_RANGE;
        }

        // Generate the expected signature.
        $signed_payload     = $timestamp . '.' . $request_body;
        $expected_signature = hash_hmac( 'sha256', $signed_payload, $this->webhook_secret );

        // Check if the expected signature is present.
        if ( ! preg_match( '/,v\d+=' . preg_quote( $expected_signature, '/' ) . '/', $matches['signatures'] ) ) {
            return WebhookProcessor::STATUS_SIGNATURE_MISMATCH;
        }

        return WebhookProcessor::STATUS_VALIDATION_SUCCEEDED;
    }

    /**
     * Verify User Agent of the incoming webhook notification. Used as fallback for the cases when webhook secret is missing.
     *
     * @since 3.6.1
     *
     * @param array $request_headers The request headers from Stripe.
     *
     * @return string The validation result
     */
    private function validate_request_user_agent( $request_headers ) {
        $is_user_agent_valid = apply_filters(
            'dokan_stripe_express_webhook_is_user_agent_valid',
            empty( $request_headers['USER-AGENT'] ) || preg_match( '/Stripe/', $request_headers['USER-AGENT'] ),
            $request_headers
        );

        return $is_user_agent_valid ? WebhookProcessor::STATUS_VALIDATION_SUCCEEDED : WebhookProcessor::STATUS_USER_AGENT_INVALID;
    }
}
