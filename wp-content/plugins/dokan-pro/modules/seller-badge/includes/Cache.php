<?php
namespace WeDevs\DokanPro\Modules\SellerBadge;

use WeDevs\Dokan\Cache as DokanCache;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

/**
 * Delete all cache related to seller badge
 *
 * @since 3.7.14
 */
class Cache {

    /**
     * Class constructor
     *
     * @since 3.7.14
     */
    public function __construct() {
        add_action( 'dokan_seller_badge_updated', [ $this, 'delete_cache' ], 10, 1 );
        add_action( 'dokan_seller_badge_created', [ $this, 'delete_cache' ], 10, 1 );
        add_action( 'dokan_seller_badge_deleted', [ $this, 'delete_cache' ], 10, 1 );
        add_action( 'dokan_seller_badge_update_acquired_badges', [ $this, 'acquired_badge_level_data' ], 10, 1 );
        add_action( 'dokan_seller_badge_delete_cache_async', [ $this, 'delete_cache' ], 10, 1 );
        add_action( 'dokan_seller_badge_badge_status_seen', [ $this, 'delete_cache' ], 10, 1 );
    }

    /**
     * Delete all object and transient cache
     *
     * @since 3.7.14
     *
     * @param int|array $badge_id
     *
     * @return void
     */
    public function delete_cache( $badge_id ) {
        $transient_key   = 'get_all_seller_badges';
        $transient_group = 'seller_badges';
        DokanCache::invalidate_group( $transient_group );
        DokanCache::invalidate_transient_group( $transient_group );
    }

    /**
     * Update acquired badge level hooks
     *
     * @since 3.7.14
     *
     * @param array $acquired_badge_level_data
     *
     * @return void
     */
    public function acquired_badge_level_data( $acquired_badge_level_data ) {
        $this->delete_cache( 0 );
    }
}
