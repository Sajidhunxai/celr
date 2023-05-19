<?php

namespace WeDevs\DokanPro\Modules\Razorpay\Gateways;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class RegisterGateways.
 *
 * @package WeDevs\DokanPro\Modules\Razorpay\Gateways
 *
 * @since 3.5.0
 */
class RegisterGateways {
    /**
     * RegisterGateways constructor.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * Init all the hooks.
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function hooks() {
        add_filter( 'woocommerce_payment_gateways', [ $this, 'register_gateway' ] );
    }

    /**
     * Register payment gateway.
     *
     * @since 3.5.0
     *
     * @param array $gateways
     *
     * @return array
     */
    public function register_gateway( $gateways ) {
        $gateways[] = '\WeDevs\DokanPro\Modules\Razorpay\PaymentMethods\Razorpay';

        return $gateways;
    }
}
