<?php

namespace WeDevs\DokanPro\Modules\SellerBadge\Events;

use WeDevs\DokanPro\Modules\SellerBadge\Helper;
use WeDevs\DokanPro\Modules\SellerBadge\Manager;
use WeDevs\DokanPro\Modules\SellerBadge\Abstracts\BadgeEvents;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

/**
 * Class trending products count badge
 *
 * @since 3.7.14
 *
 * @package WeDevs\DokanPro\Modules\SellerBadge\Events
 */
class TrendingProduct extends BadgeEvents {

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
     * @param string[] $args
     *
     * @return object[]
     */
    protected function get_current_data( $args ) {
        global $wpdb;

        $default = [
            'first_date' => '',
            'last_date'  => '',
            'limit'      => 10,
        ];
        $args    = wp_parse_args( $args, $default );
        $data    = [
            'first_date' => $args['first_date'],
            'last_date'  => $args['last_date'],
            'limit'      => $args['limit'],
        ];

        if ( empty( $data['first_date'] ) || empty( $data['last_date'] ) ) {
            return $wpdb->get_results(
            // phpcs:ignore
                $wpdb->prepare(
                    "SELECT product_id, sum(product_qty) as product_qty FROM {$wpdb->prefix}wc_order_product_lookup group by product_id order by product_qty desc limit %d",
                    $data['limit']
                )
            );
        }

        return $wpdb->get_results(
        // phpcs:ignore
            $wpdb->prepare(
                "SELECT product_id, sum(product_qty) as product_qty FROM {$wpdb->prefix}wc_order_product_lookup where date_created >= %s and date_created <= %s group by product_id order by product_qty desc limit %d",
                $data
            )
        );
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

        // user got this level
        $acquired_level['acquired_status'] = 'published';
        if ( empty( $acquired_level['id'] ) ) {
            // this is the first time user getting this level
            $acquired_level['badge_seen'] = 0;
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

    /**
     * Add task to queue.
     *
     * @since 3.7.14
     *
     * @return void
     */
    public function add_to_queue( $badge_id = 0, $badge_data = [] ) {
        if ( false === $this->set_badge_and_badge_level_data( $badge_data ) ) {
            return;
        }

        // if badge status is draft, no need to update vendor badges
        if ( 'published' !== $this->badge_data['badge_status'] ) {
            return;
        }

        $manager = new Manager();
        // there is only one badge level for this badge
        $badge_level_data = reset( $this->badge_level_data );
        $option_key       = "dokan_seller_badge_trending_product_{$badge_level_data->level_condition}";
        $now              = dokan_current_datetime()->setTimezone( new \DateTimeZone( 'UTC' ) )->setTime( 0, 0, 0 );
        $start_date       = $now;

        if ( ! empty( $this->badge_data['updated_levels'] ) ) {
            // no new update was made
            delete_option( $option_key );
        }

        if ( 'lifetime' === $badge_level_data->level_condition ) {
            $args = [
                'first_date' => '',
                'last_date'  => '',
                'limit'      => $badge_level_data->level_data,
            ];
        } else {
            $start_date   = $now->modify( 'first day of last month' )->setTime( 0, 0, 0 );
            $end_date     = $start_date->modify( 'last day of this month' )->setTime( 23, 59, 59 );
            $last_checked = (int) get_option( $option_key, 0 );
            if ( 'week' === $badge_level_data->level_condition ) {
                // weekly trending product
                $week_day   = Helper::get_week_start_day();
                $start_date = $now->modify( "$week_day last week" )->setTime( 0, 0, 0 );
                $end_date   = $start_date->modify( '+6 days' )->setTime( 23, 59, 59 );
            }

            if ( $start_date->getTimestamp() === $last_checked ) {
                return;
            }

            $args = [
                'first_date' => $start_date->format( 'Y-m-d H:i:s' ),
                'last_date'  => $end_date->format( 'Y-m-d H:i:s' ),
                'limit'      => $badge_level_data->level_data,
            ];
        }

        // remove all trending badges for all vendors
        $manager->remove_all_acquired_data_for_a_badge( $this->badge_id );

        $best_selling_products = $this->get_current_data( $args );
        if ( empty( $best_selling_products ) ) {
            return;
        }

        $vendor_ids = [];
        foreach ( $best_selling_products as $best_selling_product ) {
            $vendor_ids[] = dokan_get_vendor_by_product( $best_selling_product->product_id, true );
        }

        $vendor_ids = array_unique( $vendor_ids );

        foreach ( $vendor_ids as $vendor_id ) {
            $this->run( $vendor_id );
        }

        update_option( $option_key, $start_date->getTimestamp() );
    }

    /**
     * Process the background task.
     *
     * @since 3.7.14
     *
     * @param int[] $vendors
     *
     * @return void
     */
    public function process_background_task( $vendors ) {
        // do nothing
    }
}
