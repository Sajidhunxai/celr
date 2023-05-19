<?php

namespace WeDevs\DokanPro\Modules\Stripe;

use Exception;
use Stripe\Event;
use Stripe\Stripe;
use Stripe\WebhookEndpoint;
use WeDevs\Dokan\Exceptions\DokanException;

defined( 'ABSPATH' ) || exit;

class WebhookHandler {

    /**
     * Constructor method
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function __construct() {
        Helper::bootstrap_stripe();
        $this->hooks();
    }

    /**
     * Init all the hooks
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function hooks() {
        add_action( 'woocommerce_api_dokan_stripe', [ $this, 'handle_events' ] );
    }

    /**
     * Register webhook and remove old webhook endpoints from stripe
     *
     * @since 3.0.3
     * @since 3.2.0 updated register webhook logic
     *
     * @return void
     */
    public function register_webhook() {
        try {
            // delete all webhooks if already created, this is useful for old users
            $this->deregister_webhook();

            // create required webhook
            $response = WebhookEndpoint::create(
                [
                    'api_version'    => '2020-08-27',
                    'url'            => home_url( 'wc-api/dokan_stripe' ),
                    'enabled_events' => $this->get_events(),
                    'description'    => __( 'This webhook is created by Dokan Pro.', 'dokan' ),
                ]
            );
        } catch ( Exception $e ) {
            dokan_log( __( 'Could not create webhook: ', 'dokan' ) . $e->getMessage() );
            return;
        }
    }

    /**
     * Register webhook and remove old `webhook=dokan` endpoint from stripe
     *
     * @since 3.2.0
     *
     * @return void
     */
    public function deregister_webhook() {
        try {
            $site_url = str_replace( [ 'http://', 'https://' ], '', home_url( '/' ) );
            foreach ( WebhookEndpoint::all() as $hook ) {
                // remove all dokan webhooks
                if ( false !== strpos( $hook->url, 'webhook=dokan' ) || false !== strpos( $hook->url, $site_url . 'wc-api/dokan_stripe' ) ) {
                    $event = WebhookEndpoint::retrieve( $hook->id );
                    $event->delete();
                }
            }
        } catch ( Exception $e ) {
            dokan_log( __( 'Could not delete webhook: ', 'dokan' ) . $e->getMessage() );
            return;
        }
    }

    /**
     * Get all the webhook events
     *
     * @since 3.0.3
     *
     * @return array
     */
    public function get_events() {
        return apply_filters(
            'dokan_get_webhook_events',
            [
                'charge.dispute.closed',
                'charge.dispute.created',
                'customer.subscription.deleted',
                'customer.subscription.trial_will_end',
                'customer.subscription.updated',
                'invoice.payment_action_required',
                'invoice.payment_failed',
                'invoice.payment_succeeded',
            ]
        );
    }

    /**
     * Handle events which are comming from stripe
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function handle_events() {
        if ( 'POST' !== $_SERVER['REQUEST_METHOD'] ) {
            return;
        }

        $request_body = file_get_contents( 'php://input' );
        $event        = json_decode( $request_body );

        if ( empty( $event->id ) ) {
            return;
        }

        //dokan_log( "[Stripe Connect] Webhook request body:\n" . print_r( $event, true ) );

        try {
            if ( ! empty( $event->account ) ) {
                $users = get_users( [
                    'meta_key'     => 'dokan_connected_vendor_id',
                    'meta_value'   => $event->account,
                    'meta_compare' => '=',
                ] );

                if ( empty( $users ) ) {
                    // We've handled the deauth cleanup in RegisterWithdrawMethods::deauthorize_vendor
                    if ( 'account.application.deauthorized' === $event->type ) {
                        status_header( 200 );
                        exit;
                    }

                    throw new DokanException(
                        'dokan_stripe_connect_error_account_not_found',
                        sprintf(
                            __( 'No user found for Stripe account id %s.', 'dokan' ),
                            $event->account
                        )
                    );
                }

                $vendor            = $users[0];
                $vendor_id         = $vendor->ID;
                $vendor_access_key = get_user_meta( $vendor_id, '_stripe_connect_access_key', true );

                if ( empty( $vendor_access_key ) ) {
                    throw new DokanException(
                        'dokan_stripe_connect_error_vendor_access_key_not_found',
                        sprintf(
                            __( 'Stripe connect access key not found for vendor id %d.', 'dokan' ),
                            $vendor_id
                        )
                    );
                }

                Stripe::setApiKey( $vendor_access_key );
            }

            $event = Event::retrieve( $event->id );

            //dokan_log( "[Stripe Connect] Webhook retrieved event:\n" . print_r( $event, true ) );

            if ( array_key_exists( $event->type, Helper::get_supported_webhook_events() ) ) {
                DokanStripe::events()->get( $event )->handle();
            }

            status_header( 200 );
            exit;
        } catch ( Exception $e ) {
            dokan_log( 'Webhook Processing Error (Event ): ' . $e->getMessage(), 'error' );
            exit;
        }
    }
}
