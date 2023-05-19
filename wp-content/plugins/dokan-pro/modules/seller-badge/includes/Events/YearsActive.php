<?php

namespace WeDevs\DokanPro\Modules\SellerBadge\Events;

use WeDevs\DokanPro\Modules\SellerBadge\Abstracts\BadgeEvents;
use WeDevs\DokanPro\Modules\SellerBadge\Helper;
use WeDevs\DokanPro\Modules\SellerBadge\Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

/**
 * Class number of years count badge
 *
 * @since   3.7.14
 *
 * @package WeDevs\DokanPro\Modules\SellerBadge\Events
 */
class YearsActive extends BadgeEvents {

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
        add_action( 'dokan_seller_badge_daily_at_midnight_cron', [ $this, 'add_to_queue' ] );
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
        $year_count = Helper::get_vendor_year_count( $vendor_id );
        if ( empty( $year_count ) ) {
            return false;
        }

        return round( $year_count, 2 );
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
        $acquired_level['acquired_data'] = $current_data;
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
