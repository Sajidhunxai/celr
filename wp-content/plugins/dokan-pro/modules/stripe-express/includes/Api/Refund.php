<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Api;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use Exception;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Api;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;


/**
 * Refund API handler class
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Api
 */
class Refund extends Api {

    /**
     * Refund reasons that are suggested by Stripe.
     *
     * @since 3.6.1
     */
    const REASON_DUPLICATE        = 'duplicate';
    const REASON_FRAUD            = 'fraudulent';
    const REASON_CUSTOMER_REQUEST = 'requested_by_customer';

    /**
     * Retrieves a refund data.
     *
     * @since 3.6.1
     *
     * @param string $refund_id
     * @param array  $args
     *
     * @return \Stripe\Refund|false
     */
    public static function get( $refund_id, $args = [] ) {
        try {
            return static::api()->refunds->retrieve( $refund_id, $args );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not retrieve refund: %1$s. Error: %2$s', $refund_id, $e->getMessage() ), 'Refund', 'error' );
            return false;
        }
    }

    /**
     * Creates a refund.
     *
     * @since 3.6.1
     *
     * @param array $args
     *
     * @return \Stripe\Refund
     * @throws DokanException
     */
    public static function create( $args = [] ) {
        try {
            $defaults = [
                'reason' => self::REASON_CUSTOMER_REQUEST,
            ];

            $args = wp_parse_args( $args, $defaults );

            return static::api()->refunds->create( $args );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not create refund: %s', $e->getMessage() ), 'Refund', 'error' );
            Helper::log( 'Data: ' . print_r( $args, true ), 'Refund' );
            /* translators: error message */
            throw new DokanException( 'dokan-stripe-express-refund-error', sprintf( __( '%s', 'dokan' ), $e->getMessage() ) ); // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings
        }
    }
}
