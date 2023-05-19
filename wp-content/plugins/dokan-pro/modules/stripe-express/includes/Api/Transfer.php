<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Api;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use Exception;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Api;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;


/**
 * Transfer API handler class
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Api
 */
class Transfer extends Api {

    /**
     * Retrieves a transfer data.
     *
     * @since 3.6.1
     *
     * @param int|string $transfer_id
     * @param array      $args
     *
     * @return \Stripe\Transfer|false
     */
    public static function get( $transfer_id, $args = [] ) {
        try {
            return static::api()->transfers->retrieve( $transfer_id, $args );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not retrieve transfer: %1$s. Error: %2$s', $transfer_id, $e->getMessage() ), 'Transfer', 'error' );
            return false;
        }
    }

    /**
     * Creates a transfer.
     *
     * @since 3.6.1
     *
     * @param array $args
     *
     * @return \Stripe\Transfer
     * @throws DokanException
     */
    public static function create( $args = [] ) {
        try {
            $defaults = [
                'currency' => strtolower( get_woocommerce_currency() ),
            ];

            $args = wp_parse_args( $args, $defaults );
            return static::api()->transfers->create( $args );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not create transfer: %s', $e->getMessage() ), 'Transfer', 'error' );
            Helper::log( 'Data: ' . print_r( $args, true ), 'Transfer' );
            /* translators: error message */
            throw new DokanException( 'dokan-stripe-express-transfer-error', sprintf( __( '%s', 'dokan' ), $e->getMessage() ) ); // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings
        }
    }

    /**
     * Reverses a transfer.
     *
     * @since 3.6.1
     *
     * @param string $transfer_id
     * @param array  $args
     *
     * @return \Stripe\Transfer
     * @throws DokanException
     */
    public static function reverse( $transfer_id, $args ) {
        try {
            return static::api()->transfers->createReversal( $transfer_id, $args );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not reverse transfer: %1$s. Reason: %2$s', $transfer_id, $e->getMessage() ), 'Transfer', 'error' );
            Helper::log( 'Data: ' . print_r( $args, true ), 'Transfer' );
            /* translators: error message */
            throw new DokanException( 'dokan-stripe-express-transfer-error', sprintf( __( '%s', 'dokan' ), $e->getMessage() ) ); // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings
        }
    }
}
