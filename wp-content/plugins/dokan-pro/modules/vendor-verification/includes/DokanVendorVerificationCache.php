<?php

use WeDevs\Dokan\Cache;

/**
 * Vendor Verification Cache class.
 *
 * Manage all caches related to vendor verifications.
 *
 * @since 3.4.2
 *
 * @see \WeDevs\Dokan\Cache
 */
class DokanVendorVerificationCache {

    public function __construct() {
        add_action( 'dokan_after_address_verification_added', [ $this, 'clear_vendor_verification_cache' ], 10 );
        add_action( 'dokan_verification_updated', [ $this, 'clear_vendor_verification_cache' ], 10 );
        add_action( 'dokan_verification_status_change', [ $this, 'clear_vendor_verification_cache' ], 10 );
        add_action( 'dokan_id_verification_cancelled', [ $this, 'clear_vendor_verification_cache' ], 10 );
        add_action( 'dokan_address_verification_cancel', [ $this, 'clear_vendor_verification_cache' ], 10 );
        add_action( 'dokan_company_verification_submitted', [ $this, 'clear_vendor_verification_cache' ], 10 );
        add_action( 'dokan_company_verification_cancelled', [ $this, 'clear_vendor_verification_cache' ], 10 );
    }

    /**
     * Clear Vendor Verification caches.
     *
     * @since 3.4.2
     *
     * @param int $seller_id
     *
     * @return void
     */
    public function clear_vendor_verification_cache( $seller_id = null ) {
        Cache::invalidate_group( 'verifications' );
    }
}
