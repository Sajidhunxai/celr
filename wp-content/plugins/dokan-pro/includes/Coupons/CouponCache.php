<?php

namespace WeDevs\DokanPro\Coupons;

use WeDevs\Dokan\Cache;

/**
* Coupon Cache Class.
*
* Manage all of the caches related to coupons.
*
* @since 3.4.2
*
* @see \WeDevs\Dokan\Cache
*/
class CouponCache {

    /**
     * Manage Coupon Caches.
     *
     * We Manage the following coupon groups -
     * 1. seller_coupons_[seller_id] => Seller Wise Coupons
     *
     * @since 3.4.2
     */
    public function __construct() {
        // Handles Coupon caches created from Dokan.
        add_action( 'dokan_after_coupon_create', [ $this, 'reset_coupons_cache_after_modification' ], 10, 1 );
        add_action( 'dokan_after_coupon_delete', [ $this, 'reset_coupons_cache_after_modification' ], 10, 1 );

        // Handles Coupon caches created from WooCommerce
        add_action( 'woocommerce_new_coupon', [ $this, 'reset_coupons_cache_after_modification' ], 10, 1 );
        add_action( 'woocommerce_update_coupon', [ $this, 'reset_coupons_cache_after_modification' ], 10, 1 );

        // Handles Coupon caches deletion from WordPress.
        add_action( 'wp_trash_post', [ $this, 'reset_coupons_cache_after_modification' ], 10, 1 );
        add_action( 'untrashed_post', [ $this, 'after_restore_coupon' ], 10, 2 );
        add_action( 'delete_post', [ $this, 'before_delete_coupon' ], 10, 2 );
    }

    /**
     * Invalidate seller coupons cache.
     *
     * @since 3.4.2
     *
     * @param int $seller_id
     *
     * @return void
     */
    public function invalidate_seller_coupons( $seller_id ) {
        if ( ! $seller_id ) {
            return;
        }

        Cache::invalidate_group( "seller_coupons_{$seller_id}" );
    }

    /**
     * Reset coupon after create, edit, delete, trash.
     *
     * @since 3.4.2
     *
     * @param int $coupon_id
     *
     * @return void
     */
    public function reset_coupons_cache_after_modification( $coupon_id ) {
        if ( 'shop_coupon' !== get_post_type( $coupon_id ) ) {
            return;
        }

        $seller_id = get_post_field( 'post_author', $coupon_id );
        $this->invalidate_seller_coupons( $seller_id );
    }

    /**
     * Reset coupon after restore.
     *
     * @since 3.4.2
     *
     * @param int    $post_id         Post ID.
	 * @param string $previous_status The status of the post at the point where it was trashed.
     *
     * @return void
     */

    public function after_restore_coupon( $post_id, $status ) {
        if ( 'shop_coupon' !== get_post_type( $post_id ) ) {
            return;
        }

        $seller_id = get_post_field( 'post_author', $post_id );
        $this->invalidate_seller_coupons( $seller_id );
    }

    /**
     * Reset coupon caches before deleting a coupon.
     *
     * @since 3.4.2
     *
     * @param int      $post_id
     * @param \WP_Post $post
     *
     * @return void
     */
    public function before_delete_coupon( $post_id, $post ) {
        if ( 'shop_coupon' !== $post->post_type ) {
            return;
        }

        $this->invalidate_seller_coupons( $post->post_author );
    }
}
