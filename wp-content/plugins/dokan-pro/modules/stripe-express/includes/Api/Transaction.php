<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Api;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use Exception;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Api;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;

/**
 * API handler class for balance transaction
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Api
 */
class Transaction extends Api {

    /**
     * Retrieves a balance transaction.
     *
     * @since 3.6.1
     *
     * @param string $id
     *
     * @return \Stripe\BalanceTransaction|false
     */
    public static function get( $id ) {
        try {
            return static::api()->balanceTransactions->retrieve( $id );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not retrieve balance transaction for id: %1$s. Error: %2$s', $id, $e->getMessage() ), 'Balance Transaction' );
            return false;
        }
    }
}
