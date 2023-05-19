<?php

use WeDevs\Dokan\Cache;

/**
 * Store Support Cache class.
 *
 * Manage all caching functionalities for Store Support module.
 *
 * @since 3.4.2
 *
 * @see \WeDevs\Dokan\Cache
 */
class DokanStoreSupportCache {

    public function __construct() {
        add_action( 'dss_new_ticket_created', [ $this, 'clear_cache_after_new_comment' ], 10, 2 );
        add_action( 'dss_new_comment_inserted', [ $this, 'clear_cache_after_new_comment' ], 10, 2 );
        add_action( 'dokan_support_topic_status_changed', [ $this, 'clear_cache_change_topic_status' ], 10, 2 );
    }

    /**
     * Clear Store Support caches.
     *
     * @since 3.4.2
     *
     * @return void
     */
    public function clear_cache_after_new_comment( $post_id, $seller_id ) {
        $customer_id = get_post_field( 'post_author', $post_id );
        Cache::invalidate_group( "store_support_{$seller_id}" );
        Cache::invalidate_group( "store_support_customer_{$customer_id}" );
    }

    /**
     * Clear Cache after change topic status.
     *
     * @since 3.4.2
     *
     * @param int    $post_id
     * @param string $status
     *
     * @return void
     */
    public function clear_cache_change_topic_status( $post_id, $status ) {
        // Get store id from post meta
        $seller_id = get_post_meta( $post_id, 'store_id', true );

        $this->clear_cache_after_new_comment( $post_id, $seller_id );
    }
}
