<?php

namespace WeDevs\DokanPro\Modules\SellerBadge\Abstracts;

use WeDevs\DokanPro\Modules\SellerBadge\Helper;
use WeDevs\DokanPro\Modules\SellerBadge\Manager;
use WeDevs\DokanPro\Modules\SellerBadge\Models\BadgeEvent;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

/**
 * Abstraction for badge events, all events will implement this class
 *
 * @since 3.7.14
 */
abstract class BadgeEvents {

    /**
     * @since 3.7.14
     *
     * @var string $event_type
     */
    protected $event_type;

    /**
     * @since 3.7.14
     *
     * @var BadgeEvent
     */
    protected $badge_event;

    /**
     * @since 3.7.14
     *
     * @var int $badge_id
     */
    protected $badge_id;

    /**
     * @since 3.7.14
     *
     * @var array $badge_data
     */
    protected $badge_data;

    /**
     * @since 3.7.14
     *
     * @var array $badge_level_data
     */
    protected $badge_level_data;

    /**
     * No of vendors to process at a time
     *
     * @since 3.7.14
     *
     * @var int $vendor_queue_limit
     */
    protected $vendor_queue_limit = 20;

    /**
     * Class constructor
     *
     * @since 3.7.14
     *
     * @param $event_type
     */
    public function __construct( $event_type ) {
        $this->event_type = sanitize_text_field( $event_type );

        $this->badge_event = Helper::get_dokan_seller_badge_events( $this->event_type );
        if ( is_wp_error( $this->badge_event ) ) {
            return;
        }

        // After created or updated a badge, we need to award the badge to the vendor through this hook.
        add_action( 'dokan_seller_badge_' . $this->event_type . '_created', [ $this, 'add_to_queue' ], 20, 2 );
        add_action( 'dokan_seller_badge_' . $this->event_type . '_updated', [ $this, 'add_to_queue' ], 20, 2 );
        add_action( 'dokan_seller_badge_' . $this->event_type . '_async', [ $this, 'process_background_task' ] );
    }

    /**
     * Get current compare data
     *
     * @since 3.7.14
     *
     * @param int $vendor_id
     *
     * @return mixed
     */
    protected abstract function get_current_data( $vendor_id );

    /**
     * Run the event job
     *
     * @since 3.7.14
     *
     * @param int $vendor_id single vendor id.
     *
     * @return void
     */
    protected function run( $vendor_id ) {
        $manager = new Manager();

        if ( ! is_numeric( $vendor_id ) ) {
            return;
        }

        if ( ! dokan_is_user_seller( $vendor_id ) ) {
            return;
        }

        $current_data = $this->get_current_data( $vendor_id );
        if ( false === $current_data ) {
            return;
        }

        $acquired_levels = $this->get_acquired_level_data( $vendor_id );
        if ( empty( $acquired_levels ) ) {
            return;
        }

        foreach ( $acquired_levels as &$acquired_level ) {
            $level_data                        = is_numeric( $acquired_level['level_data'] ) ? round( $acquired_level['level_data'], 2 ) : 0;
            $old_status                        = $acquired_level['acquired_status'];
            $acquired_level['acquired_status'] = 'draft';

            if ( $current_data > $level_data ) {
                $acquired_level['acquired_status'] = 'published';
                $acquired_level['acquired_data']   = $current_data;
            }

            // user got this level
            if ( 'draft' === $old_status && 'published' === $acquired_level['acquired_status'] ) {
                // this is the first time user getting this level
                $acquired_level['badge_seen'] = 0;
                $acquired_level['created_at'] = time();
            }
        }

        // now save acquired badge data
        $inserted = $manager->update_vendor_acquired_badge_levels_data( $acquired_levels );
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
     * This method can be called directly or will be call after a badge has been added/updated.
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

        // check if level data has been changed while update
        if ( empty( $this->badge_data['updated_levels'] ) ) {
            return;
        }

        $vendors = [];
        $i       = 1;
        while ( null !== $vendors ) {
            $args = [
                'status' => 'all',
                'paged'  => $i ++,
                'number' => 10,
                'fields' => 'ID',
            ];

            $vendors = dokan()->vendor->all( $args );
            if ( ! empty( $vendors ) ) {
                // add task to queue to process in background.
                WC()->queue()->add( 'dokan_seller_badge_' . $this->event_type . '_async', [ $vendors ] );
            } else {
                $vendors = null;
                $data = [
                    'badge_id' => $this->badge_id,
                    'event_type' => $this->event_type,
                ];
                // delete seller cache
                WC()->queue()->add( 'dokan_seller_badge_delete_cache_async', [ $data ] );
            }
        }
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
        // before calling run method, make sure we got badge_level and badge_data via query
        // this will save some execution instead of calling it from run() method
        if ( false === $this->set_badge_and_badge_level_data() ) {
            return;
        }

        foreach ( $vendors as $vendor_id ) {
            $this->run( $vendor_id );
        }
    }

    /**
     * Set badge and badge level data
     *
     * @since 3.7.14
     *
     * @param array $badge_data
     *
     * @return bool
     */
    protected function set_badge_and_badge_level_data( $badge_data = [] ) {
        $stored_badge_data = $this->get_badge_data_by_event_type();
        if ( is_wp_error( $stored_badge_data ) ) {
            dokan_log( sprintf(
                'Dokan Seller Badge: Invalid badge data. Badge Event: %s', $this->event_type
            ) );

            return false;
        }
        $badge_data = wp_parse_args( $badge_data, (array) $stored_badge_data );

        if ( ! isset( $badge_data['updated_levels'] ) ) {
            $badge_data['updated_levels'] = 10; //short-circuit updated_levels so that hook runs as expected
        }

        // set badge id
        $this->badge_id   = $badge_data['id'];
        $this->badge_data = $badge_data;

        $badge_levels = $this->get_badge_levels( $this->badge_data['id'] );
        if ( is_wp_error( $badge_levels ) ) {
            dokan_log( sprintf(
                "Dokan Seller Badge: No badge level data found with given badge id.\n\rFile: %s \n\rBadge ID: %d \n\rError: %s",
                __FILE__, $this->badge_id, $badge_levels->get_error_message()
            ) );

            return false;
        }

        if ( empty( $badge_levels ) ) {
            dokan_log( sprintf(
                "Dokan Seller Badge: No badge level data found.\n\rFile: %s \n\rBadge Event: %s \n\rLevel Data: %s",
                __FILE__, $this->badge_event, print_r( $badge_levels, 1 )
            ) );

            return false;
        }

        $this->badge_level_data = $badge_levels;

        return true;
    }

    /**
     * This method will get badge data from database via event_type
     *
     * @since 3.7.14
     *
     * @return Object|\WP_Error
     */
    protected function get_badge_data_by_event_type() {
        $manager = new Manager();

        return $manager->get_badge_data_by_event_type( $this->event_type );
    }

    /**
     * Get badge levels by badge id
     *
     * @since 3.7.14
     *
     * @param int $badge_id
     *
     * @return object[]|\WP_Error
     */
    protected function get_badge_levels( $badge_id ) {
        $manager = new Manager();

        return $manager->get_badge_levels( [ 'badge_id' => $badge_id ] );
    }

    /**
     * This method will merge badge level data with seller acquired badge level data
     *
     * @since 3.7.14
     *
     * @param int $vendor_id
     *
     * @return array
     */
    protected function get_acquired_level_data( $vendor_id ) {
        //get manager class instance
        $manager = new Manager();
        // get acquired data for this vendor
        $vendor_acquired_levels = $manager->get_vendor_acquired_levels_by_badge_id( $vendor_id, $this->badge_data['id'] );
        if ( is_wp_error( $vendor_acquired_levels ) ) {
            $vendor_acquired_levels = [];
        }

        $acquired_levels = [];
        foreach ( $vendor_acquired_levels as $acquired_level_data ) {
            $acquired_levels[ $acquired_level_data->level_id ] = (array) $acquired_level_data;
        }

        foreach ( $this->badge_level_data as $level_data ) {
            if ( ! array_key_exists( $level_data->id, $acquired_levels ) ) {
                $acquired_levels[ $level_data->id ] = [
                    'id'              => 0,
                    'vendor_id'       => $vendor_id,
                    'level_id'        => $level_data->id,
                    'acquired_data'   => '',
                    'acquired_status' => 'draft',
                    'badge_seen'      => 0,
                    'created_at'      => time(),
                ];
            }

            // assign level data
            $acquired_levels[ $level_data->id ]['level_id']        = $level_data->id;
            $acquired_levels[ $level_data->id ]['level']           = $level_data->level;
            $acquired_levels[ $level_data->id ]['level_data']      = $level_data->level_data;
            $acquired_levels[ $level_data->id ]['level_condition'] = $level_data->level_condition;
        }

        // now sort level data based on level
        if ( count( $acquired_levels ) > 1 ) {
            uasort( $acquired_levels, function ( $a, $b ) {
                $item1 = intval( $a['level'] );
                $item2 = intval( $b['level'] );
                if ( $item1 === $item2 ) {
                    return 0;
                }

                return $item1 < $item2 ? - 1 : 1;
            } );
        }

        return $acquired_levels;
    }

    /**
     * Unpublish acquired badge for vendor
     *
     * @since 3.7.14
     *
     * @param $vendor_id
     *
     * @return void
     */
    protected function unpublished_acquired_badge( $vendor_id ) {
        $acquired_levels = $this->get_acquired_level_data( $vendor_id );
        if ( empty( $acquired_levels ) ) {
            return;
        }

        $manager = new Manager();
        // since this badge doesn't have any levels, we can assume that first element is the only level
        $acquired_level = reset( $acquired_levels );

        // user got this level
        $acquired_level['acquired_status'] = 'draft';

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
