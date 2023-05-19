<?php

namespace WeDevs\DokanPro\Modules\SellerBadge\Events;

use WeDevs\Dokan\Vendor\Vendor;
use WeDevs\DokanPro\Modules\SellerBadge\Abstracts\BadgeEvents;
use WeDevs\DokanPro\Modules\SellerBadge\Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

/**
 * Class sale only here count badge
 *
 * @since   3.7.14
 *
 * @package WeDevs\DokanPro\Modules\SellerBadge\Events
 */
class ExclusiveToPlatform extends BadgeEvents {

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
        add_action( 'dokan_new_vendor', [ $this, 'process_hook' ], 10, 1 );
        add_action( 'dokan_update_vendor', [ $this, 'process_hook' ], 10, 1 );
    }

    /**
     * Process hooks related to this badge
     *
     * @since 3.7.14
     *
     * @param int $vendor_id
     *
     * @return void
     */
    public function process_hook( $vendor_id ) {
        if ( false === $this->set_badge_and_badge_level_data() ) {
            return;
        }

        // if badge status is draft, no need to update vendor badges
        if ( 'published' !== $this->badge_data['badge_status'] ) {
            return;
        }

        $this->run( $vendor_id );
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

        $shop_info = $vendor->get_shop_info();
        if ( ! $shop_info ) {
            return false;
        }

        return ! empty( $shop_info['sale_only_here'] ) ? $shop_info['sale_only_here'] : false;
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

        // unpublish badge if previously acquired
        $this->unpublished_acquired_badge( $vendor_id );

        $current_data = $this->get_current_data( $vendor_id );
        if ( false === $current_data ) {
            return;
        }

        // since this badge doesn't have any levels, we can assume that first element is the only level
        $acquired_level = reset( $acquired_levels );

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
