<?php

namespace WeDevs\DokanPro\Modules\SellerBadge\Events;

use WeDevs\Dokan\Vendor\Vendor;
use WeDevs\DokanPro\Modules\SellerBadge\Manager;
use WeDevs\DokanPro\Modules\SellerBadge\Abstracts\BadgeEvents;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

/**
 * Class vendor first product count badge
 *
 * @since   3.7.14
 *
 * @package WeDevs\DokanPro\Modules\SellerBadge\Events
 */
class ProductPublished extends BadgeEvents {

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
     * @return false|int
     */
    protected function get_current_data( $vendor_id ) {
        /**
         * @var Vendor $vendor
         */
        $vendor = dokan()->vendor->get( $vendor_id );
        if ( ! $vendor->get_id() ) {
            return false;
        }

        $query_args = [
            'author'         => $vendor_id,
            'return'         => 'ids',
            'post_status'    => 'publish',
            'posts_per_page' => -1,
        ];

        $count = dokan()->product->all( $query_args );

        if ( empty( $count->post_count ) ) {
            return false;
        }

        return round( $count->post_count, 2 );
    }
}
