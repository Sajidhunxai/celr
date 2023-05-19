<?php

namespace WeDevs\DokanPro\Modules\Razorpay\Logs;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class RazorpayLog.
 *
 * @package WeDevs\Dokan\Gateways\Razorpay
 *
 * @since 3.5.0
 */
class RazorpayLog {
    /**
     * Log Razorpay error data with debug id.
     *
     * @since 3.5.0
     *
     * @param int      $id
     * @param WP_Error $error
     * @param string   $meta_key
     * @param string   $context
     *
     * @return void
     */
    public static function error( $id, $error, $meta_key, $context = 'post' ) {
        $error_data = $error->get_error_data();

        //store dokan razorpay debug id
        if ( isset( $error_data['dokan_razorpay_id'] ) ) {
            switch ( $context ) {
                case 'post':
                    update_post_meta( $id, "_dokan_razorpay_{$meta_key}_debug_id", $error_data['dokan_razorpay_id'] );
                    break;

                case 'user':
                    update_user_meta( $id, "_dokan_razorpay_{$meta_key}_debug_id", $error_data['dokan_razorpay_id'] );
                    break;
            }
        }

        dokan_log( "[Dokan Razorpay] $meta_key Error:\n" . print_r( $error, true ), 'error' );
    }
}
