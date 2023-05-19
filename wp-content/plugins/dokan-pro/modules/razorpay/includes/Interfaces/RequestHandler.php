<?php

namespace WeDevs\DokanPro\Modules\Razorpay\Interfaces;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Interface RequestHandler.
 *
 * @since 3.5.0
 */
interface RequestHandler {
    /**
     * Handle the request.
     *
     * @since 3.5.0
     *
     * @param array $data
     *
     * @return WP_Error|mixed
     */
    public static function handle( $data = [] );
}
