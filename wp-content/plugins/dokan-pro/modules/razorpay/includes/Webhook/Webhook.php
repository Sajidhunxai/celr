<?php

namespace WeDevs\DokanPro\Modules\Razorpay\Webhook;

use WP_Error;
use Exception;
use Razorpay\Api\Api;
use Razorpay\Api\Errors\BadRequestError;
use WeDevs\DokanPro\Modules\Razorpay\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Webhook.
 *
 * @package WeDevs\DokanPro\Modules\Razorpay
 *
 * @see https://razorpay.com/docs/payments/dashboard/settings/webhooks/
 * @see https://razorpay.com/docs/webhooks/
 *
 * @since 3.5.0
 */
class Webhook {
    /**
     * Razorpay API instance.
     *
     * @var \Razorpay\Api\Api
     */
    protected $api = null;

    /**
     * Webhook URL that needs to be registered with Razorpay.
     *
     * @since 3.5.0
     *
     * @var string
     */
    public $webhook_url = null;

    /**
     * Webhook Constructor.
     *
     * @since 3.5.0
     */
    public function __construct( Api $api ) {
        $this->api         = $api;
        $this->webhook_url = Helper::get_webhook_url();
    }

    /**
     * Get all webhooks list from razorpay.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public function get() {
        try {
            $response = $this->api->webhook->all();

            if ( ! empty( $response['items'] ) && count( $response['items'] ) > 0 ) {
                return $response['items'];
            }
        } catch ( Exception $e ) {
            dokan_log( __( '[Dokan Razorpay] Could not get webhook requests. Error: ', 'dokan' ) . $e->getMessage() );
        }

        return [];
    }

    /**
     * Create webhook on Razorpay.
     *
     * @since 3.5.0
     *
     * @return bool|\Razorpay\Api\Webhook
     */
    public function create() {
        try {
            $response = $this->get();

            if ( is_wp_error( $response ) ) {
                dokan_log( '[Dokan Razorpay] Webhook listing error: ' . Helper::get_error_message( $response ) );
                return false;
            }

            if ( count( $response ) ) {
                $hooks = wp_list_pluck( $response, 'url', 'id' );

                // check webhook already exists and webhook url and site url are same
                $existing_web_hook_id = Helper::get_webhook_id();
                if ( ! empty( $existing_web_hook_id ) &&
                    array_key_exists( $existing_web_hook_id, $hooks ) &&
                    false !== strpos( $hooks[ $existing_web_hook_id ], $this->webhook_url )
                ) {
                    // check we've all the required webhook events
                    if ( $this->get_webhook_events_from_response( $response, $existing_web_hook_id ) ) {
                        // update also status to active
                        $this->update_status( $existing_web_hook_id, true );
                        return true;
                    }
                }

                foreach ( $hooks as $hook_id => $hook_url ) {
                    // disable all dokan webhooks for current site,
                    // since they don't support delete yet.
                    if ( false !== strpos( $hook_url, $this->webhook_url ) ) {
                        $this->update_status( $hook_id, false );
                    }
                }
            }

            // Create Webhook on Razorpay.
            $response = $this->api->webhook->create(
                [
                    'url'         => $this->webhook_url,
                    'events'      => Helper::get_webhook_events( true ),
                    'secret'      => Helper::get_webhook_secret(),
                    'description' => __( 'This webhook is created by Dokan Pro.', 'dokan' ),
                ]
            );

            if ( is_wp_error( $response ) ) {
                delete_option( Helper::get_webhook_key() );
                dokan_log( '[Dokan Razorpay] Could not create webhook automatically: ' . print_r( $response, true ) );
                return false;
            }

            // store this webhook to database
            update_option( Helper::get_webhook_key(), $response['id'] );
        } catch ( BadRequestError $bad_request_error ) {
            dokan_log( __( '[Dokan Razorpay] Could not create webhook. Bad Request Error: ', 'dokan' ) . $bad_request_error->getMessage() );
            return false;
        } catch ( Exception $e ) {
            dokan_log( __( '[Dokan Razorpay] Could not create webhook. Error: ', 'dokan' ) . $e->getMessage() );
            return false;
        }

        return true;
    }

    /**
     * Update webhook status on Razorpay.
     *
     * Razorpay currently does not support deleting a webhook.
     * So we can use this to disable a webhook status or enable if needed.
     *
     * @since 3.5.0
     *
     * @param string $webhook_id
     * @param bool    $is_active
     *
     * @return bool|WP_Error
     */
    public function update_status( $webhook_id, $is_active = false ) {
        if ( empty( $webhook_id ) ) {
            return new WP_Error( 'dokan_razorpay_invalid_webhook_id', __( 'Invalid webhook id provided, Please check your input.', 'dokan' ) );
        }

        // Toggle webhook active status.
        try {
            $this->api->webhook->edit(
                [
                    'url'    => $this->webhook_url,
                    'active' => $is_active ? true : false,
                    'events' => Helper::get_webhook_events( true ),
                    'secret' => Helper::get_webhook_secret(),
                ],
                $webhook_id
            );
        } catch ( BadRequestError $bad_request_error ) {
            dokan_log( __( '[Dokan Razorpay] Could not change webhook status. Bad Request Error: ', 'dokan' ) . $bad_request_error->getMessage() );
            return false;
        } catch ( Exception $e ) {
            dokan_log( __( '[Dokan Razorpay] Could not change webhook status. Error: ', 'dokan' ) . $e->getMessage() );
            return false;
        }

        return true;
    }

    /**
     * Verify a webhook request.
     *
     * @see https://razorpay.com/docs/webhooks/validate-test/#validate-webhooks
     *
     * @since 3.5.0
     *
     * @param object $webhook_body
     * @param string $webhook_signature
     *
     * @return bool
     */
    public function verify( $webhook_body, $webhook_signature ) {
        $webhook_secret = Helper::get_webhook_secret();

        // If secret is not found, means - verification is not required and is valid.
        if ( empty( $webhook_secret ) ) {
            return true;
        }

        // Process to verify if secret is found.
        try {
            $this->api->utility->verifyWebhookSignature(
                $webhook_body,
                $webhook_signature,
                $webhook_secret
            );

            // If no error, it means signature is valid.
            return true;
        } catch ( Exception $e ) {
            dokan_log( __( 'Webhook event signature verification failed.', 'dokan' ) );
            return false;
        }
    }

    /**
     * Check if we are using required webhooks.
     *
     * @since 3.5.0
     *
     * @param array  $response
     * @param string $webhook_id
     *
     * @return bool
     */
    protected function get_webhook_events_from_response( $response, $webhook_id ) {
        if ( ! is_array( $response ) ) {
            return false;
        }

        $event_data = null;
        foreach ( $response as $event ) {
            if ( $event['id'] === $webhook_id ) {
                $event_data = $event;
                break;
            }
        }

        if ( null === $event_data || empty( $event_data['events'] ) ) {
            return false;
        }

        $events_available = true;
        $webhook_events   = array_keys( (array) $event_data['events'] );
        $required_events  = array_keys( Helper::get_webhook_events() );

        foreach ( $required_events as $event ) {
            if ( ! in_array( $event, $webhook_events, true ) ) {
                $events_available = false;
                break;
            }
        }

        return $events_available;
    }
}
