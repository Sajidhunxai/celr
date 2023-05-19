<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Controllers;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Subscription;

/**
 * Cart controller class.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Controllers
 */
class Cart {

    /**
     * Class constructor.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function __construct() {
        add_action( 'init', [ $this, 'hooks' ] );
    }

    /**
     * Registers all necessary hooks.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function hooks() {
        add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'validate_add_to_cart' ], 10, 2 );
    }

    /**
     * Validates if a product is eligible to be added to cart.
     *
     * If Stripe express is only payment gateway available
     * and vendor is not connected with Stripe,
     * restrict adding product to cart for that vendor.
     *
     * @since 3.6.1
     *
     * @param boolean $passed
     * @param int     $product_id
     *
     * @return boolean
     */
    public function validate_add_to_cart( $passed, $product_id ) {
        if ( ! $passed ) {
            return $passed;
        }
        // If it is a vendor subscription product then pass
        if ( Subscription::is_vendor_subscription_product( $product_id ) ) {
            return $passed;
        }

        // Check if dokan stripe express is only payment gateway available
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        if ( ! array_key_exists( Helper::get_gateway_id(), $available_gateways ) ) {
            return $passed;
        }

        if ( count( $available_gateways ) > 1 ) {
            return $passed;
        }

        // Check if stripe express is ready
        if ( ! Helper::is_gateway_ready() ) {
            return $passed;
        }

        // Get seller id
        $seller_id = dokan_get_vendor_by_product( $product_id, true );

        // check if vendor is not connected with Stripe Express
        if ( ! Helper::is_seller_connected( $seller_id ) ) {
            wc_add_notice(
                wp_kses(
                    sprintf(
                        // translators: 1) opening strong tag, 2) closing strong tag, 3) product title, 4) gateway title
                        __( '%1$sError!%2$s Could not add product %3$s to cart, this product/vendor is not eligible to be paid with %4$s which is the only available payment method available.', 'dokan' ),
                        '<strong>',
                        '</strong>',
                        get_the_title( $product_id ),
                        Helper::get_gateway_title()
                    ),
                    [
                        'strong' => [],
                    ]
                ),
                'error'
            );
            return false;
        }

        return $passed;
    }
}
