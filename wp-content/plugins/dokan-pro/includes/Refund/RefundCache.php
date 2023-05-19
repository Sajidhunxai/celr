<?php

namespace WeDevs\DokanPro\Refund;

use WeDevs\Dokan\Cache;
use WeDevs\Dokan\Order\OrderCache;

/**
 * Refund Cache class.
 *
 * Manage all caches for refund.
 *
 * @since 3.4.2
 *
 * @see \WeDevs\Dokan\Cache
 */
class RefundCache {

    public function __construct() {
        add_action( 'dokan_refund_request_created', [ $this, 'clear_refund_cache' ], 10 );
        add_action( 'dokan_refund_updated', [ $this, 'clear_refund_cache' ], 10 );
        add_action( 'dokan_pro_refund_deleted', [ $this, 'clear_refund_cache' ], 10 );
        add_action( 'dokan_pro_refund_cancelled', [ $this, 'clear_refund_cache' ], 10 );
        add_action( 'woocommerce_order_refunded', [ $this, 'after_woocommerce_order_refunded' ], 10 ); // As we call this hook, no need to call `dokan_pro_refund_approved` hook
    }

    /**
     * Clear Refund Related caches.
     *
     * @since 3.4.2
     *
     * @param Refund $refund
     *
     * @return void
     */
    public function clear_refund_cache( $refund ) {
        $order_id  = $refund->get_order_id();
        $seller_id = dokan_get_seller_id_by_order( $order_id );
        self::delete( $seller_id );
    }

    /**
     * Clear Refund caches after woocommerce order.
     *
     * @since 3.4.2
     *
     * @param int $order_id
     *
     * @return void
     */
    public function after_woocommerce_order_refunded( $order_id ) {
        $seller_id = dokan_get_seller_id_by_order( $order_id );
        self::delete( $seller_id );
    }

    /**
     * Delete refund caches.
     *
     * @since 3.4.2
     *
     * @param int $seller_id
     *
     * @return void
     */
    public static function delete( $seller_id ) {
        Cache::invalidate_group( 'refunds' );
        Cache::invalidate_group( "refund_{$seller_id}" );
        OrderCache::delete( $seller_id );
    }
}
