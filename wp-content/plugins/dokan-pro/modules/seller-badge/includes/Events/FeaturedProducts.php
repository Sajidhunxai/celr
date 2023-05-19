<?php

namespace WeDevs\DokanPro\Modules\SellerBadge\Events;

use WeDevs\Dokan\Vendor\Vendor;
use WeDevs\DokanPro\Modules\SellerBadge\Abstracts\BadgeEvents;
use WeDevs\DokanPro\Modules\SellerBadge\Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

/**
 * Class Feature product count badge
 *
 * @since   3.7.14
 *
 * @package WeDevs\DokanPro\Modules\SellerBadge\Events
 */
class FeaturedProducts extends BadgeEvents {

    /**
     * Class constructor
     *
     * @since 3.7.14
     *
     * @param string $event_type
     */
    public function __construct( $event_type ) {
        parent::__construct( $event_type );
        // return in case of error
        if ( is_wp_error( $this->badge_event ) ) {
            return;
        }
        add_action( 'save_post_product', [ $this, 'process_hook' ], 10, 1 );
    }

    /**
     * Process hooks related to this badge
     *
     * @since 3.7.14
     *
     * @param int $post_id
     *
     * @return void
     */
    public function process_hook( $post_id ) {
        if ( false === $this->set_badge_and_badge_level_data() ) {
            return;
        }

        // if badge status is draft, no need to update vendor badges
        if ( 'published' !== $this->badge_data['badge_status'] ) {
            return;
        }

        $vendor_id = dokan_get_vendor_by_product( $post_id, true );
        if ( $vendor_id ) {
            $this->run( $vendor_id );
        }
    }

    /**
     * Get current compare data
     *
     * @since 3.7.14
     *
     * @param int $vendor_id
     *
     * @return false|float
     */
    protected function get_current_data( $vendor_id ) {
        /**
         * @var Vendor $vendor
         */
        $vendor = dokan()->vendor->get( $vendor_id );
        if ( ! $vendor->get_id() ) {
            return false;
        }

        $featured_products = dokan_get_featured_products( -1, $vendor_id );

        if ( empty( $featured_products->post_count ) ) {
            return false;
        }

        return round( $featured_products->post_count, 2 );
    }

    /**
     * Run the event job
     *
     * @since 3.7.14
     *
     * @param int $vendor_id single vendor id.
     *
     * @return void
     */
    public function run( $vendor_id ) {
        $manager = new Manager();

        if ( ! is_numeric( $vendor_id ) ) {
            return;
        }

        if ( ! dokan_is_user_seller( $vendor_id ) ) {
            return;
        }

        $acquired_levels = $this->get_acquired_level_data( $vendor_id );
        if ( empty( $acquired_levels ) ) {
            return;
        }

        // since this badge doesn't have any levels, we can assume that first element is the only level
        $acquired_level = reset( $acquired_levels );

        // check if acquired level is already published, in that case return from here
        if ( 'published' ===  $acquired_level['acquired_status'] ) {
            return;
        }

        $current_data = $this->get_current_data( $vendor_id );
        if ( false === $current_data ) {
            return;
        }

        // user got this level
        $acquired_level['acquired_status'] = 'published';
        if ( empty( $acquired_level['badge_seen'] ) ) {
            $acquired_level['created_at'] = time();
        }

        // now save acquired badge data
        $inserted = $manager->update_vendor_acquired_badge_levels_data( [ $acquired_level ] );
        if ( is_wp_error( $inserted ) ) {
            dokan_log(
                sprintf(
                    'Dokan Vendor Badge: update acquired badge level failed. \n\rFile: %s \n\rLine: %s \n\rError: %s,',
                    __FILE__, __LINE__, $inserted->get_error_message()
                )
            );
        }
    }
}
