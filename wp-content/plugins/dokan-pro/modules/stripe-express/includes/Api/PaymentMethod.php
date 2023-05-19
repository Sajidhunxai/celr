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
class PaymentMethod extends Api {

    /**
     * Creates a payment method.
     *
     * @since 3.6.1
     *
     * @param array $args
     *
     * @return \Stripe\PaymentMethod
     * @throws DokanException
     */
    public static function create( $args ) {
        try {
            return static::api()->paymentMethods->create( $args );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not create payment method. Error: %s', $e->getMessage() ), 'Payment Method' );
            Helper::log( 'Data: ' . print_r( $args, true ), 'Payment Method' );
            throw new DokanException(
                'dokan-stripe-express-payment-method-error',
                /* translators: error message */
                sprintf( __( 'Could not create payment method. Error: %s', 'dokan' ), $e->getMessage() )
            );
        }
    }

    /**
     * Updates a setup intent.
     *
     * @since 3.6.1
     *
     * @param string $method_id
     * @param array $data
     *
     * @return \Stripe\PaymentMethod
     * @throws DokanException
     */
    public static function update( $method_id, $data ) {
        try {
            return static::api()->paymentMethods->update( $method_id, $data );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not update payment method: %1$s. Error: %2$s', $method_id, $e->getMessage() ), 'Payment Method' );
            Helper::log( 'setup Data: ' . print_r( $data, true ) );
            throw new DokanException( 'dokan-stripe-express-payment-method-error', $e->getMessage() );
        }
    }

    /**
     * Retrieves a payment method.
     *
     * @since 3.6.1
     * @since 3.7.8 Added additional `$args` parameter.
     *
     * @param string $method_id
     * @param array  $args      (Optional)
     *
     * @return \Stripe\PaymentMethod|false
     */
    public static function get( $method_id, $args = [] ) {
        try {
            return static::api()->paymentMethods->retrieve( $method_id, $args );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not retrieve payment method for id: %1$s. Error: %2$s', $method_id, $e->getMessage() ), 'Payment Method' );
            return false;
        }
    }

    /**
     * Retrieves all paymemnt methods of a customer.
     *
     * @since 3.6.1
     *
     * @param string $customer_id
     * @param array $args
     *
     * @return \Stripe\PaymentMethod[]
     */
    public static function get_by_customer( $customer_id, $args ) {
        $defaults = [
            'type' => 'card',
        ];

        $args = wp_parse_args( $args, $defaults );

        try {
            $response = static::api()->customers->allPaymentMethods( $customer_id, $args );
            if ( ! empty( $response->error ) ) {
                return [];
            }
            return $response->data;
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not retrieve payment method for customer: %1$s. Error: %2$s', $customer_id, $e->getMessage() ), 'Payment Method' );
            return [];
        }
    }

    /**
     * Attaches a payment method to a customer.
     *
     * @since 3.6.1
     *
     * @param string $payment_method_id
     * @param string $customer_id
     *
     * @return \Stripe\PaymentMethod
     * @throws DokanException
     */
    public static function attach( $payment_method_id, $customer_id ) {
        try {
            return static::api()->paymentMethods->attach( $payment_method_id, [ 'customer' => $customer_id ] );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not attach payment method: %1$s for customer: %2$s. Error: %3$s', $payment_method_id, $customer_id, $e->getMessage() ), 'Payment Method' );
            throw new DokanException(
                'dokan-stripe-express-payment-method-error',
                /* translators: 1) customer id 2) error message */
                sprintf( __( 'Could not attach payment method to customer: %1$s. Error: %2$s', 'dokan' ), $customer_id, $e->getMessage() )
            );
        }
    }

    /**
     * Detaches a payment method to a customer.
     *
     * @since 3.6.1
     *
     * @param string $payment_method_id
     *
     * @return \Stripe\PaymentMethod
     * @throws DokanException
     */
    public static function detach( $payment_method_id ) {
        try {
            return static::api()->paymentMethods->detach( $payment_method_id );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not detach payment method: %1$s. Error: %2$s', $payment_method_id, $e->getMessage() ), 'Payment Method' );
            throw new DokanException(
                'dokan-stripe-express-payment-method-error',
                /* translators: error message */
                sprintf( __( 'Could not detach payment method. Error: %s', 'dokan' ), $e->getMessage() )
            );
        }
    }
}
