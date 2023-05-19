<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Api;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Api;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;

/**
 * Coupon API handler class.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Api
 */
class Coupon extends Api {

    /**
     * Retrieves stripe coupon.
     *
     * @since 3.7.8
     *
     * @param string $coupon_id
     * @param array  $args
     *
     * @return \Stripe\Coupon|false
     */
    public static function get( $coupon_id, $args = [] ) {
        try {
            return static::api()->coupons->retrieve( $coupon_id, $args );
        } catch ( \Exception $e ) {
            Helper::log( sprintf( 'Could not retrieve coupon for id: %1$s. Error: %2$s', $coupon_id, $e->getMessage() ), 'Coupon' );
            return false;
        }
    }

    /**
     * Creates a Stripe coupon.
     *
     * @since 3.7.8
     *
     * @param array $args
     *
     * @return \Stripe\Coupon
     * @throws DokanException
     */
    public static function create( $args ) {
        try {
            return static::api()->coupons->create( $args );
        } catch ( \Exception $e ) {
            /* translators: error message */
            $message = sprintf( __( 'Could not create coupon. Error: %s', 'dokan' ), $e->getMessage() );
            Helper::log( $message, 'Coupon' );
            Helper::log( 'Data: ' . print_r( $args, true ), 'Coupon' );

            throw new DokanException( 'coupon-create-error', $message );
        }
    }
}
