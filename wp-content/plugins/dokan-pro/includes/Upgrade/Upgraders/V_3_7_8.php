<?php

namespace WeDevs\DokanPro\Upgrade\Upgraders;

use WeDevs\DokanPro\Abstracts\DokanProUpgrader;
use WeDevs\DokanPro\Upgrade\Upgraders\BackgroundProcesses\V_3_7_8_VendorDeliveryTimes;

class V_3_7_8 extends DokanProUpgrader {

    /**
     * Updates admin delivery time settings data.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public static function update_admin_delivery_settings() {
        $delivery_time = get_option( 'dokan_delivery_time', [] );

        if ( empty( $delivery_time ) ) {
            return;
        }

        $time_slot_minutes = ! empty( $delivery_time['default_time_slot_minutes'] ) ? $delivery_time['default_time_slot_minutes'] : [];
        $opening_time      = ! empty( $delivery_time['opening_time'] ) ? $delivery_time['opening_time'] : '';
        $closing_time      = ! empty( $delivery_time['closing_time'] ) ? $delivery_time['closing_time'] : '';
        $order_per_slot    = ! empty( $delivery_time['default_order_per_slot'] ) ? $delivery_time['default_order_per_slot'] : [];
        $delivery_day      = ! empty( $delivery_time['delivery_day'] ) ? $delivery_time['delivery_day'] : [];

        // unset all unnecessary data.
        unset( $delivery_time['delivery_day'] );
        unset( $delivery_time['opening_time'] );
        unset( $delivery_time['closing_time'] );
        unset( $delivery_time['default_opening_time'] );
        unset( $delivery_time['default_closing_time'] );
        unset( $delivery_time['default_order_per_slot'] );
        unset( $delivery_time['default_time_slot_minutes'] );

        $max_order_per_slot    = 0;
        $max_time_slot_minutes = 0;

        foreach ( dokan_get_translated_days() as $day_key => $day ) {
            if ( empty( $delivery_day[ $day_key ] ) ) {
                $delivery_time[ 'delivery_day_' . $day_key ]['delivery_status'] = '';
                $delivery_time[ 'delivery_day_' . $day_key ]['opening_time']    = '';
                $delivery_time[ 'delivery_day_' . $day_key ]['closing_time']    = '';

                continue;
            }

            $delivery_time[ 'delivery_day_' . $day_key ]['delivery_status'] = $delivery_day[ $day_key ];
            $delivery_time[ 'delivery_day_' . $day_key ]['opening_time']    = $opening_time;
            $delivery_time[ 'delivery_day_' . $day_key ]['closing_time']    = $closing_time;

            if ( isset( $order_per_slot[ $day_key ] ) ) {
                $max_order_per_slot = max( $order_per_slot[ $day_key ], $max_order_per_slot );
            }

            if ( isset( $time_slot_minutes[ $day_key ] ) ) {
                $max_time_slot_minutes = max( $time_slot_minutes[ $day_key ], $max_time_slot_minutes );
            }
        }

        $delivery_time['delivery_support'] = [
            'delivery'     => 'delivery',
            'store-pickup' => 'store-pickup',
        ];

        $delivery_time['order_per_slot']    = ! empty( $max_order_per_slot ) ? $max_order_per_slot : 0;
        $delivery_time['time_slot_minutes'] = ! empty( $max_time_slot_minutes ) ? $max_time_slot_minutes : 30;

        update_option( 'dokan_delivery_time', $delivery_time );
    }

    /**
     * Updates usermeta database table column. Before on,
     * delivery time gets single data in usermeta. Now, we
     * are setting data as array for multiple delivery times.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public static function update_vendor_delivery_times() {
        $i         = 1;
        $vendors   = [];
        $processor = new V_3_7_8_VendorDeliveryTimes();

        while ( true ) {
            $args = [
                'paged'  => $i++,
                'number' => 10,
                'fields' => 'ID',
            ];

            $vendors = dokan()->vendor->all( $args );

            if ( empty( $vendors ) ) {
                break;
            }

            $processor->push_to_queue( $vendors );
        }

        $processor->dispatch_process();
    }
}
