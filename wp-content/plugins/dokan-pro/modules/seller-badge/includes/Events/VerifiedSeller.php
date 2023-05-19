<?php

namespace WeDevs\DokanPro\Modules\SellerBadge\Events;

use WeDevs\Dokan\Vendor\Vendor;
use WeDevs\DokanPro\Modules\SellerBadge\Manager;
use WeDevs\DokanPro\Modules\SellerBadge\Abstracts\BadgeEvents;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

/**
 * Class verified seller count badge
 *
 * @since   3.7.14
 *
 * @package WeDevs\DokanPro\Modules\SellerBadge\Events
 */
class VerifiedSeller extends BadgeEvents {

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
        add_action( 'dokan_verification_status_change', [ $this, 'process_hook' ], 10, 1 );
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
     * @return false|string[]
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
        if ( ! $shop_info || empty( $shop_info['dokan_verification'] ) ) {
            return false;
        }

        // To make sure the array is not empty.
        $defaults = [
            'dokan_v_id_status'  => 'pending',
            'company_v_status'   => 'pending',
            'phone_verification' => 'pending',
            'social_profiles'    => 'pending',
            'store_address'      => [
                'v_status' => 'pending',
            ],
        ];

        return wp_parse_args( $shop_info['dokan_verification']['info'], $defaults );
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

        $current_data = $this->get_current_data( $vendor_id );
        if ( false === $current_data ) {
            return;
        }

        $acquired_levels = $this->get_acquired_level_data( $vendor_id );
        if ( empty( $acquired_levels ) ) {
            return;
        }

        foreach ( $acquired_levels as &$acquired_level ) {
            $acquired_level['acquired_status'] = 'draft';
            $acquired_level['acquired_data']   = $acquired_level['level_condition'];

            // more than, less than, equal to
            switch ( $acquired_level['level_condition'] ) {
                case 'id_verification':
                    // if level data is less than current earning, user got this level
                    if ( 'approved' === $current_data['dokan_v_id_status'] ) {
                        $acquired_level['acquired_status'] = 'published';
                    }
                    break;

                case 'company_verification':
                    if ( 'approved' === $current_data['company_v_status'] ) {
                        $acquired_level['acquired_status'] = 'published';
                    }
                    break;

                case 'address_verification':
                    if ( 'approved' === $current_data['store_address']['v_status'] ) {
                        $acquired_level['acquired_status'] = 'published';
                    }
                    break;

                case 'phone_verification':
                    if ( 'approved' === $current_data['phone_verification'] ) {
                        $acquired_level['acquired_status'] = 'published';
                    }
                    break;

                case 'social_profiles':
                    if ( 'approved' === $current_data['social_profiles'] ) {
                        $acquired_level['acquired_status'] = 'published';
                    }
                    break;
            }

            // user got this level
            if ( empty( $acquired_level['id'] ) && 'published' === $acquired_level['acquired_status'] ) {
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
}
