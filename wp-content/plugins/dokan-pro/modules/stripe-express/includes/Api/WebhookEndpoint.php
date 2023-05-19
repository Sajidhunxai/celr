<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Api;

use Exception;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Api;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Webhook Endpoint API handler class
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Api
 */
class WebhookEndpoint extends Api {

    /**
     * Retrieves a webhook endpoint.
     *
     * @since 3.6.1
     *
     * @param string $webhook_id
     *
     * @return \Stripe\WebhookEndpoint|false
     */
    public static function get( $webhook_id ) {
        try {
            return static::api()->webhookEndpoints->retrieve( $webhook_id );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not retrieve webhook: %s. Message: %s', $webhook_id, $e->getMessage() ) );
            return false;
        }
    }

    /**
     * Lists all webhook endpoints.
     *
     * @since 3.6.1
     *
     * @param array $args (Optional)
     *
     * @return \Stripe\WebhookEndpoint[]|false
     */
    public static function all( $args = [] ) {
        $data = [
            'limit' => 100, // Maximum limit
        ];

        $args = wp_parse_args( $args, $data );

        try {
            $endpoints = static::api()->webhookEndpoints->all( $args );
            return $endpoints->data;
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not retrieve all webhook endpoints. Message: %s', $e->getMessage() ) );
            return false;
        }
    }

    /**
     * Creates webhook endpoint.
     *
     * @since 3.6.1
     *
     * @param array $args (Optional)
     *
     * @return \Stripe\WebhookEndpoint
     * @throws DokanException
     */
    public static function create( $args = [] ) {
        try {
            return static::api()->webhookEndpoints->create( $args );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not create webhook: %s. Message: %s', implode( ', ', (array) $args['enabled_events'] ), $e->getMessage() ) );

            throw new DokanException(
                'dokan-stripe-express-webhook-create-error',
                sprintf(
                    /* translators: 1) webhook events, 2) error message */
                    __( 'Could not create webhook: %1$s. Message: %2$s', 'dokan' ),
                    implode( ', ', (array) $args['enabled_events'] ),
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Updates a webhook endpoint.
     *
     * @since 3.6.1
     *
     * @param string $webhook_id
     * @param array  $args       (Optional)
     *
     * @return \Stripe\WebhookEndpoint
     * @throws DokanException
     */
    public static function update( $webhook_id, $args = [] ) {
        try {
            return static::api()->webhookEndpoints->update( $webhook_id, $args );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not update webhook: %s. Message: %s', $webhook_id, $e->getMessage() ) );

            throw new DokanException(
                'dokan-stripe-express-webhook-update-error',
                sprintf(
                    /* translators: 1) webhook events, 2) error message */
                    __( 'Could not update webhook: %1$s. Message: %2$s', 'dokan' ),
                    $webhook_id,
                    $e->getMessage()
                )
            );
        }
    }

    /**
     * Deletes a webhook endpoint.
     *
     * @since 3.6.1
     *
     * @param string $webhook_id
     *
     * @return \Stripe\WebhookEndpoint
     * @throws DokanException
     */
    public static function delete( $webhook_id ) {
        try {
            return static::api()->webhookEndpoints->delete( $webhook_id );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not delete webhook: %s. Message: %s', $webhook_id, $e->getMessage() ) );

            throw new DokanException(
                'dokan-stripe-express-webhook-delete-error',
                /* translators: 1) webhook event, 2) error message */
                sprintf( __( 'Could not delete webhook: %1$s. Message: %2$s', 'dokan' ), $webhook_id, $e->getMessage() )
            );
        }
    }

    /**
     * Retrieves a webhook event object
     *
     * @since 3.6.1
     *
     * @param string $event_id
     * @param array  $args     (Optional)
     *
     * @return \Stripe\Event|false
     */
    public static function get_event( $event_id, $args = [] ) {
        try {
            return static::api()->events->retrieve( $event_id, $args );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not retrieve webhook: %s. Message: %s', $event_id, $e->getMessage() ) );
            return false;
        }
    }
}
