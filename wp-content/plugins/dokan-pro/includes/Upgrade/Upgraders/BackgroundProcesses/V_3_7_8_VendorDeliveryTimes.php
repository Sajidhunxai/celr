<?php

namespace WeDevs\DokanPro\Upgrade\Upgraders\BackgroundProcesses;

use WeDevs\Dokan\Abstracts\DokanBackgroundProcesses;

/**
 * Dokan vendor delivery times upgrader class.
 *
 * @since 3.7.8
 */
class V_3_7_8_VendorDeliveryTimes extends DokanBackgroundProcesses {

    /**
     * Action
     *
     * Override this action in your processor class
     *
     * @since 3.7.8
     *
     * @var string
     */
    protected $action = 'dokan_pro_bg_action_3_7_8';

    /**
     * Update vendors delivery time.
     *
     * @param array $vendors
     *
     * @since 3.7.8
     *
     * @return bool
     */
    public function task( $vendors ) {
        if ( empty( $vendors ) ) {
            return false;
        }

        foreach ( $vendors as $user_id ) {
            $vendor_delivery_settings = get_user_meta( $user_id, '_dokan_vendor_delivery_time_settings' );

            if ( empty( $vendor_delivery_settings[0] ) ) {
                return;
            }

            $vendor_delivery_settings                      = $vendor_delivery_settings[0];
            $vendor_delivery_settings['order_per_slot']    = ! empty( $vendor_delivery_settings['order_per_slot'] ) ?
                max( (array) $vendor_delivery_settings['order_per_slot'] ) : 0;
            $vendor_delivery_settings['delivery_support']  = ! empty( $vendor_delivery_settings['allow_vendor_delivery_time_option'] ) ?
                $vendor_delivery_settings['allow_vendor_delivery_time_option'] : 'on';
            $vendor_delivery_settings['time_slot_minutes'] = ! empty( $vendor_delivery_settings['time_slot_minutes'] ) ?
                max( (array) $vendor_delivery_settings['time_slot_minutes'] ) : 0;

            // Sets delivery time as array and (order_per_slot & time_slot_minutes) as index value.
            foreach ( dokan_get_translated_days() as $day => $value ) {
                if ( empty( $vendor_delivery_settings['delivery_day'][ $day ] ) ) {
                    $vendor_delivery_settings['delivery_day'][ $day ] = 0;
                    $vendor_delivery_settings['opening_time'][ $day ] = [];
                    $vendor_delivery_settings['closing_time'][ $day ] = [];

                    continue;
                }

                $vendor_delivery_settings['delivery_day'][ $day ] = 1;
                $vendor_delivery_settings['opening_time'][ $day ] = ! empty( $vendor_delivery_settings['opening_time'][ $day ] ) ?
                    (array) dokan_convert_date_format( $vendor_delivery_settings['opening_time'][ $day ], 'g:i A', 'g:i a' ) : [];
                $vendor_delivery_settings['closing_time'][ $day ] = ! empty( $vendor_delivery_settings['closing_time'][ $day ] ) ?
                    (array) dokan_convert_date_format( $vendor_delivery_settings['closing_time'][ $day ], 'g:i A', 'g:i a' ) : [];
            }

            update_user_meta( $user_id, '_dokan_vendor_delivery_time_settings', $vendor_delivery_settings );
        }

        return false;
    }
}
