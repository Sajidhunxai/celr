<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Api;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\Api;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;

/**
 * API handler class for charges.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Api
 */
class Charge extends Api {

    /**
     * Retrieves charge data from Stripe.
     *
     * @since 3.7.8
     *
     * @param string $charge_id
     * @param array  $args
     *
     * @return \Stripe\Charge|false
     */
    public static function get( $charge_id, $args = [] ) {
        try {
            return static::api()->charges->retrieve( $charge_id, $args );
        } catch ( \Exception $e ) {
            Helper::log( sprintf( 'Could not retrieve charge for ID: %1$s. Error: %2$s', $charge_id, $e->getMessage() ), 'Charge' );
            return false;
        }
    }
}
