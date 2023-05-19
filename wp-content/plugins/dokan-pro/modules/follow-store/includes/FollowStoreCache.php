<?php

namespace WeDevs\DokanPro\Modules\FollowStore;

use WeDevs\Dokan\Cache;

/**
 * Follow Store Cache class.
 *
 * @since 3.4.2
 *
 * @see \WeDevs\Dokan\Cache
 */
class FollowStoreCache {

    /**
     * Constructor
     *
     * @since 3.4.2
     */
    public function __construct() {
        add_action( 'dokan_follow_store_toggle_status', [ $this, 'clear_cache' ], 20, 4 );
    }

    /**
     * Clear Cache for Follow Stores Module
     *
     * @since 3.4.2
     *
     * @param int    $vendor_id
     * @param int    $follower_id
     * @param string $status
     * @param string $current_time
     *
     * @return void
     */
    public function clear_cache( $vendor_id, $follower_id, $status, $current_time ) {
        $cache_group = "followers_{$vendor_id}";

        Cache::delete( 'get_followers', $cache_group );
    }
}
