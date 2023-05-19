<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Api;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use Exception;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Api;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;

/**
 * Product API handler class
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Api
 */
class Product extends Api {

    /**
     * Retrieves a client.
     *
     * @since 3.7.8
     *
     * @param string $product_id
     * @param array  $args
     *
     * @return false|\Stripe\Product
     */
    public static function get( $product_id, $args = [] ) {
        try {
            return static::api()->products->retrieve( $product_id, $args );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not retrieve product: %s', $e->getMessage() ), 'Product' );
            return false;
        }
    }

    /**
     * Creates an express client.
     *
     * @since 3.7.8
     *
     * @param array $args
     *
     * @return \Stripe\Product
     * @throws DokanException
     */
    public static function create( $args = [] ) {
        try {
            return static::api()->products->create( $args );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not create product: %s', $e->getMessage() ), 'Product' );
            Helper::log( 'Data: ' . print_r( $args, true ), 'Product' );
            throw new DokanException( 'dokan-stripe-express-product-create-error', $e->getMessage() );
        }
    }

    /**
     * Updates an connected account.
     *
     * @since 3.7.8
     *
     * @param string $account_id
     * @param array  $data
     *
     * @return \Stripe\Product
     * @throws DokanException
     */
    public static function update( $account_id, array $data = [] ) {
        try {
            return static::api()->accounts->update( $account_id, $data );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not update account: %1$s. Error: %2$s', $account_id, $e->getMessage() ), 'Product' );
            Helper::log( 'Data: ' . print_r( $data, true ), 'Product' );
            throw new DokanException( 'dokan-stripe-express-account-create-error', $e->getMessage() );
        }
    }
}
