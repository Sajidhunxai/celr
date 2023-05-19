<?php

namespace WeDevs\DokanPro\Modules\Stripe\Factories;

use WC_Order;
use WeDevs\Dokan\Exceptions\DokanException;

defined( 'ABSPATH' ) || exit;

class StripeFactory {

    /**
     * Payment methods holder
     *
     * @var array
     */
    private $ways  = [];

    /**
     * Order object holder
     *
     * @var null
     */
    private $order = null;

    /**
     * Constructor method
     *
     * @since 3.0.3
     *
     * @param \WC_Order
     *
     * @return Void
     */
    public function __construct( $order ) {
        if ( ! $order instanceof WC_Order ) {
            $order = wc_get_order( $order );
        }

        if ( ! $order ) {
            throw new DokanException( 'invalid_order', __( 'Invalid Order', 'dokan' ) );
        }

        $this->order = $order;
        $this->set_supported_ways();
    }

    /**
     * With method
     *
     * @since 3.0.3
     *
     * @param string $with
     *
     * @return \PaymentMethods instance
     */
    public function with( $with ) {
        if ( empty( $this->ways[ $with ] ) ) {
            throw new DokanException(
                'dokan_unsupported_gateway',
                __( 'This gateway is not supported yet', 'dokan' ),
                422
            );
        }

        $payment_method = $this->ways[ $with ];
        $payment_method = "\\WeDevs\\DokanPro\\Modules\\Stripe\\PaymentMethods\\{$payment_method}";

        if ( ! class_exists( $payment_method ) ) {
            throw new DokanException(
                'dokan_unsupported_gateway',
                __( 'This payment method is not supported yet', 'dokan' ),
                422
            );
        }

        return new $payment_method( $this->order );
    }

    /**
     * Get supported payment methods
     *
     * @since 3.0.3
     *
     * @return void
     */
    private function set_supported_ways() {
        $this->ways = apply_filters( 'dokan_stripe_set_supported_ways', [
            '3ds'     => 'Stripe3DSPayment',
            'non_3ds' => 'StripeNon3DSPayment'
        ] );
    }
}
