<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Cart;

use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;

/**
 * Class for managing Cart
 *
 * @since 3.5.0
 */
class Manager {

    /**
     * Constructor for Cart manager.
     *
     * @since 3.5.0
     */
    public function __construct() {
        if ( ! Helper::is_gateway_ready() ) {
            return;
        }

        $this->hooks();
    }

    /**
     * Registers all required hooks.
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function hooks() {
        add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'validate_vendor_is_connected' ], 10, 2 );
    }

    /**
     * Validates whether or not add to cart is allowed.
     *
     * If MangoPay is only payment gateway available
     * and vendor is not connected with MangoPay,
     * restrict adding product to cart for that vendor.
     *
     * @since 3.5.0
     *
     * @param bool $passed
     * @param int $product_id
     *
     * @return bool
     */
    public function validate_vendor_is_connected( $passed, $product_id ) {
        // check if this is a vendor subscription product
        if ( Helper::is_vendor_subscription_product( $product_id ) ) {
            return $passed;
        }

        // check if dokan mangopay is only payment gateway available
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        if ( ! array_key_exists( Helper::get_gateway_id(), $available_gateways ) ) {
            return $passed;
        }

        // check if mangopay is ready
        if ( ! Helper::is_gateway_ready() ) {
            return $passed;
        }

        if ( count( $available_gateways ) > 1 ) {
            return $passed;
        }

        // get post author
        $seller_id = get_post_field( 'post_author', $product_id );

        // check if vendor is not connected with mangopay
        if ( ! Helper::is_seller_connected( $seller_id ) ) {
            $message = wp_kses(
                sprintf(
                    // translators: 1) Product title
                    __( '<strong>Error!</strong> Could not add product %1$s to cart, this product/vendor is not eligible to be paid with %2$s', 'dokan' ),
                    get_the_title( $product_id ), Helper::get_gateway_title()
                ),
                array(
                    'strong' => array(),
                )
            );

            wc_add_notice( $message, 'error' );
            return false;
        }

        return $passed;
    }
}
