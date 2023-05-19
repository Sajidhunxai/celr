<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Api;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use Exception;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Api;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;

/**
 * Subscription API handler class
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Api
 */
class Subscription extends Api {

    /**
     * Retrieves a subscription.
     *
     * @since 3.7.8
     *
     * @param string $subscription_id The subscription ID.
     * @param array  $args            (Optional)
     *
     * @return \Stripe\Subscription
     * @throws DokanException
     */
    public static function get( $subscription_id, $args = [] ) {
        try {
            return static::api()->subscriptions->retrieve( $subscription_id, $args );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not retrieve subscription (%s): %s', $subscription_id, $e->getMessage() ), 'Subscription' );
            throw new DokanException( 'dokan-stripe-express-subscription-retrieve-error', $e->getMessage() );
        }
    }

    /**
     * Creates a subscription.
     *
     * @since 3.7.8
     *
     * @param array $data
     *
     * @return \Stripe\Subscription
     * @throws DokanException
     */
    public static function create( $data ) {
        try {
            return static::api()->subscriptions->create( $data );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not create subscription: %s', $e->getMessage() ), 'Subscription' );
            Helper::log( 'Data: ' . print_r( $data, true ), 'Subscription' );
            throw new DokanException( 'dokan-stripe-express-subscription-create-error', $e->getMessage() );
        }
    }

    /**
     * Creates a subscription.
     *
     * @since 3.7.8
     *
     * @param string $subscription_id
     * @param array  $data
     *
     * @return \Stripe\Subscription
     * @throws DokanException
     */
    public static function update( $subscription_id, $data ) {
        try {
            return static::api()->subscriptions->update( $subscription_id, $data );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not update subscription (%s): %s', $subscription_id, $e->getMessage() ), 'Subscription' );
            Helper::log( 'Data: ' . print_r( $data, true ), 'Subscription' );
            throw new DokanException( 'dokan-stripe-express-subscription-update-error', $e->getMessage() );
        }
    }
}
