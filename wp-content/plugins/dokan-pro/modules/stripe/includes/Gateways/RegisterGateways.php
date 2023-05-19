<?php

namespace WeDevs\DokanPro\Modules\Stripe\Gateways;

defined( 'ABSPATH' ) || exit;

class RegisterGateways {

    /**
     * Constructor method
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * Init all the hooks
     *
     * @since 3.0.3
     *
     * @return void
     */
    private function hooks() {
        add_filter( 'woocommerce_payment_gateways', [ $this, 'register_gateway' ] );
    }

    /**
     * Register payment gateway
     *
     * @since 3.0.3
     *
     * @param array $gateways
     *
     * @return array
     */
    public function register_gateway( $gateways ) {
        $gateways[] = '\WeDevs\DokanPro\Modules\Stripe\StripeConnect';

        return $gateways;
    }
}