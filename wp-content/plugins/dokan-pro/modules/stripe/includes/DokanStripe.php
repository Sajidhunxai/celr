<?php

namespace WeDevs\DokanPro\Modules\Stripe;

use BadMethodCallException;
use WeDevs\DokanPro\Modules\Stripe\Transaction;
use WeDevs\DokanPro\Modules\Stripe\Factories\EventFactory;
use WeDevs\DokanPro\Modules\Stripe\Factories\StripeFactory;

defined( 'ABSPATH' ) || exit;

class DokanStripe {

    /**
     * Call the defined static methods
     *
     * @since 3.0.3
     *
     * @param string $name
     * @param array $args
     *
     * @return mix
     */
    public static function __callStatic( $name, $args ) {
        if ( ! in_array( $name, [ 'events', 'transfer', 'process' ] ) ) {
            throw new BadMethodCallException( sprintf( 'The %s method is not callable.', $name ), 422 );
        }

        if ( 'events' === $name ) {
            return new EventFactory();
        }

        if ( 'transfer' === $name ) {
            return new Transaction();
        }

        if ( 'process' === $name ) {
            $order = ! empty( $args[0] ) ? $args[0] : null;
            return new StripeFactory( $order );
        }
    }
}