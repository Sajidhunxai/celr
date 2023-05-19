<?php

namespace WeDevs\DokanPro\Modules\Razorpay\Webhook;

use Exception;
use WeDevs\DokanPro\Modules\Razorpay\Helper;
use WeDevs\DokanPro\Modules\Razorpay\Factories\EventFactory;
use WeDevs\DokanPro\Modules\Razorpay\Webhook\Webhook;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class WebhookHandler.
 *
 * @package WeDevs\DokanPro\Modules\Razorpay
 *
 * @see https://razorpay.com/docs/payments/dashboard/settings/webhooks/
 * @see https://razorpay.com/docs/webhooks/
 *
 * @since 3.5.0
 */
class WebhookHandler {
    /**
     * Razorpay API instance
     *
     * @var \Razorpay\Api\Api
     */
    protected $api = null;

    /**
     * Webhook class manage Razorpay webhooks.
     *
     * @var WeDevs\DokanPro\Modules\Razorpay\Webhook\Webhook
     */
    private $webhook = null;

    /**
     * WebhookHandler constructor.
     *
     * @since 3.5.0
     */
    public function __construct() {
        $this->api     = Helper::init_razorpay_api();
        $this->webhook = new Webhook( $this->api );
        $this->hooks();
    }

    /**
     * Init all the hooks.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function hooks() {
        add_action( 'woocommerce_api_' . Helper::get_gateway_id(), [ $this, 'handle_events' ] );
    }

    /**
     * Handle events which are coming from Razorpay.
     *
     * @since 3.5.0
     *
     * @return void
     * @throws \WeDevs\Dokan\Exceptions\DokanException
     */
    public function handle_events() {
        // if the gateway is disabled then we are not processing further execution
        if ( ! Helper::is_ready() ) {
            status_header( 200 );
            exit();
        }

        $request_body = file_get_contents( 'php://input' );
        $event        = json_decode( $request_body );

        if ( ! $event ) {
            status_header( 400 );
            exit();
        }

        // get request header
        $request_headers   = array_change_key_case( $this->get_request_headers(), CASE_UPPER );
        $webhook_signature = $request_headers['X-RAZORPAY-SIGNATURE'];

        // verify webhook event
        if ( ! $this->webhook->verify( $request_body, $webhook_signature ) ) {
            dokan_log( 'Dokan Razorpay: Incoming webhook failed validation: ' . print_r( $event, true ) );

            // webhook verification failed
            status_header( 400 );
            exit();
        }

        dokan_log( "[Dokan Razorpay] Webhook request body:\n" . print_r( $event, true ) );

        EventFactory::handle( $event );

        status_header( 200 );
        exit();
    }

    /**
     * Gets the incoming request headers. Some servers are not using
     * Apache and "getallheaders()" will not work so we may need to
     * build our own headers.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public function get_request_headers() {
        if ( ! function_exists( 'getallheaders' ) ) {
            $headers = [];

            foreach ( $_SERVER as $name => $value ) {
                if ( 'HTTP_' === substr( $name, 0, 5 ) ) {
                    $headers[ str_replace( ' ', '-', ucwords( strtolower( str_replace( '_', ' ', substr( $name, 5 ) ) ) ) ) ] = $value;
                }
            }

            return $headers;
        } else {
            return getallheaders();
        }
    }

    /**
     * Register webhook and remove old webhook endpoints from Razorpay.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public function register_webhook() {
        if ( ! Helper::is_api_ready() ) {
            return false;
        }

        // Create webhook
        return $this->webhook->create();
    }

    /**
     * Delete webhook on Razorpay end.
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public function deregister_webhook() {
        if ( ! Helper::is_api_ready() ) {
            return false;
        }

        try {
            $response = $this->webhook->get();
            if ( is_wp_error( $response ) ) {
                dokan_log( 'Dokan Razorpay listing webhook error: ' . print_r( $response, true ) );
                return false;
            }

            // Check if any webhook is registered there or not.
            if ( ! count( $response ) ) {
                return false;
            }

            $hooks = wp_list_pluck( $response, 'url', 'id' );
            foreach ( $hooks as $hook_id => $hook_url ) {
                // disable all dokan webhooks for the current site.
                if ( false !== strpos( $hook_url, $this->webhook->webhook_url ) ) {
                    $this->webhook->update_status( $hook_id, false );
                }
            }

            // delete database reference
            delete_option( Helper::get_webhook_key() );

            return true;
        } catch ( Exception $e ) {
            dokan_log( __( 'Could not delete webhook: ', 'dokan' ) . $e->getMessage() );
            return false;
        }

        return true;
    }
}
