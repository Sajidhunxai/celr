<?php

namespace WeDevs\DokanPro\Modules\Elementor\Tags;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\TagBase;

class StoreCoupons extends TagBase {
    /**
     * Tag name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-coupons-tag';
    }

    /**
     * Tag title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Coupons', 'dokan' );
    }

    /**
     * Render tag
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function render() {
        $coupons = [];

        if ( dokan_is_store_page() ) {
            $dokan_store   = dokan_pro()->store;
            $store         = dokan()->vendor->get( get_query_var( 'author' ) );

            if ( empty( $store->data ) ) {
                return;
            }

            $store_user    = $store->data;
            $store_info    = $store_user->get_shop_info();
            $store_coupons = $dokan_store->get_store_coupons( $store_user, $store_info );
            $marketplace   = $dokan_store->get_store_coupons( $store_user, $store_info, true );
            $store_coupons = array_merge( $store_coupons, $marketplace );

            if ( ! empty( $store_coupons ) ) {
                foreach ( $store_coupons as $i => $store_coupon ) {
                    $coupons[ $i ] = [
                        'coupon'       => $store_coupon['coupon'],
                        // translators: 1) coupon discount amount
                        'coupon_title' => sprintf( __( '%s Discount', 'dokan' ), $store_coupon['coupon_amount_formatted'] ),
                        'expiry_date'  => '',
                        'current_time' => $store_coupon['current_time'],
                    ];

                    if ( ! empty( $store_coupon['expiry_date'] ) ) {
                        $expiry_date = $store_coupon['expiry_date'];
                        $expiry_date = is_object( $expiry_date ) ? $expiry_date->getTimestamp() : $expiry_date;
                        // translators: 1) Coupon expiration time
                        $coupons[ $i ]['expiry_date'] = sprintf( __( 'Expiring in %s', 'dokan' ), human_time_diff( $store_coupon['current_time'], $expiry_date ) );
                    }
                }
            }
        } else {
            $coupon               = new \StdClass();
            $coupon->post_content = 'Coupon Description';
            $coupon->post_title   = 'HOLIDAY25';

            // translators: 1) coupon discount amount
            $coupon_title = sprintf( __( '%s Discount', 'dokan' ), '25%' );

            $current_time = time();
            $expiry_date  = $current_time + ( 7 * 24 * 60 * 60 ); // current_time + 7 days

            // translators: coupon expiration time
            $expiry_date = sprintf( __( 'Expiring in %s', 'dokan' ), human_time_diff( $current_time, $expiry_date ) );

            for ( $i = 0; $i < 8; $i++ ) {
                $coupons[] = [
                    'coupon'       => $coupon,
                    'coupon_title' => $coupon_title,
                    'expiry_date'  => $expiry_date,
                    'current_time' => $current_time,
                ];
            }
        }

        echo wp_json_encode( $coupons );
    }
}
