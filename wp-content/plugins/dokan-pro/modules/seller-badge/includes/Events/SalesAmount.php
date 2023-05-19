<?php

namespace WeDevs\DokanPro\Modules\SellerBadge\Events;

use WeDevs\Dokan\Vendor\Vendor;
use WeDevs\DokanPro\Modules\SellerBadge\Abstracts\BadgeEvents;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

/**
 * Class sales amount count badge
 *
 * @since   3.7.14
 *
 * @package WeDevs\DokanPro\Modules\SellerBadge\Events
 */
class SalesAmount extends BadgeEvents {

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
        add_action( 'woocommerce_order_status_changed', [ $this, 'process_hook' ], 10, 1 );
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
    public function process_hook( $order_id ) {
        if ( false === $this->set_badge_and_badge_level_data() ) {
            return;
        }

        // if badge status is draft, no need to update vendor badges
        if ( 'published' !== $this->badge_data['badge_status'] ) {
            return;
        }

        $vendor_id = dokan_get_seller_id_by_order( $order_id );
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

        $sales_amount = dokan_author_total_sales( $vendor_id );

        if ( empty( $sales_amount ) ) {
            return false;
        }

        return round( $sales_amount, 2 );
    }
}
