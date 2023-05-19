<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Controllers;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\PaymentGateways\ApplePay;

/**
 * Class for managing Stripe gateway
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Controllers
 */
class Gateway {

    /**
     * Class constructor
     *
     * @since 3.6.1
     */
    public function __construct() {
        $this->init_classes();
        $this->hooks();
    }

    /**
     * Instantiates necessary classes.
     *
     * @since 3.6.1
     *
     * @return void
     */
    private function init_classes() {
        new ApplePay();
    }

    /**
     * Registers necessary hooks
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function hooks() {
        // Registers Stripe payment gateway
        add_filter( 'woocommerce_payment_gateways', [ $this, 'register_gateway' ] );
    }

    /**
     * Registers payment gateway
     *
     * @since 3.6.1
     *
     * @param array $gateways
     *
     * @return array
     */
    public function register_gateway( $gateways ) {
        $gateways[] = '\WeDevs\DokanPro\Modules\StripeExpress\PaymentGateways\Stripe';

        return $gateways;
    }
}
