<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Api;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use Exception;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Api;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;

/**
 * Country specifications API handler class
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Api
 */
class CountrySpec extends Api {

    /**
     * Retrieves a client.
     *
     * @since 3.6.1
     *
     * @param string              $id
     * @param array<string,mixed> $args
     *
     * @return \Stripe\CountrySpec
     * @throws DokanException
     */
    public static function get( $id, array $args = [] ) {
        try {
            return static::api()->countrySpecs->retrieve( $id, $args );
        } catch ( Exception $e ) {
            /* translators: error message */
            $message = sprintf( __( 'Could not retrieve country specifications: %s', 'dokan' ), $e->getMessage() );
            Helper::log( $message, 'Account' );

            throw new DokanException( 'country-specs-retrieve-error', $message );
        }
    }
}
