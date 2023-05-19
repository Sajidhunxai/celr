<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Api;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use Exception;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Api;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;

/**
 * API handler class for paymrnt intent
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Api
 */
class PaymentIntent extends Api {

    /**
     * Creates a payment intent.
     *
     * @since 3.6.1
     *
     * @param array $args
     *
     * @return \Stripe\PaymentIntent
     * @throws DokanException
     */
    public static function create( $args ) {
        $args = wp_parse_args(
            $args,
            [
                'amount'               => 0,
                'currency'             => strtolower( get_woocommerce_currency() ),
                'payment_method_types' => [ 'card' ],
            ]
        );

        if ( (int) $args['amount'] <= 0 ) {
            throw new DokanException( 'dokan-stripe-express-payment-intent-error', __( 'Could not create payment intent. Error: Amount cannot be negative.', 'dokan' ) );
        }

        try {
            return self::api()->paymentIntents->create( $args );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not create payment intent. Error: %s', $e->getMessage() ), 'Payment Intent' );
            Helper::log( 'Data: ' . print_r( $args, true ) );
            /* translators: error message */
            throw new DokanException( 'dokan-stripe-express-payment-intent-error', sprintf( __( 'Could not create payment intent. Error: %s', 'dokan' ), $e->getMessage() ) );
        }
    }

    /**
     * Updates a payment intent.
     *
     * @since 3.6.1
     *
     * @param string $intent_id
     * @param array $data
     *
     * @return \Stripe\PaymentIntent
     * @throws DokanException
     */
    public static function update( $intent_id, $data ) {
        try {
            return self::api()->paymentIntents->update( $intent_id, $data );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not update payment intent: %1$s. Error: %2$s', $intent_id, $e->getMessage() ), 'Payment Intent' );
            Helper::log( 'Data: ' . print_r( $data, true ) );
            throw new DokanException( 'dokan-stripe-express-payment-intent-error', $e->getMessage() );
        }
    }

    /**
     * Retrieves a Payment intent.
     *
     * @since 3.6.1
     *
     * @param string $intent_id
     * @param array  $args      (optional)
     *
     * @return \Stripe\PaymentIntent|false
     */
    public static function get( $intent_id, $args = [] ) {
        try {
            return self::api()->paymentIntents->retrieve( $intent_id, $args );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not retrieve payment intent for id: %1$s. Error: %2$s', $intent_id, $e->getMessage() ) );
            return false;
        }
    }
}
