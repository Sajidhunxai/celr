<?php

namespace WeDevs\DokanPro\Modules\Stripe;

use WC_Subscriptions;
use WC_Subscriptions_Cart;
use WC_Subscriptions_Product;

defined( 'ABSPATH' ) || exit;

class Validation {

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
        add_action( 'woocommerce_after_checkout_validation', [ $this, 'check_vendor_configure_stripe' ], 15, 2 );
        add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'cart_validation_with_multiple_products' ], 10, 2 );
    }

    /**
     * Cart validation with multiple subscription products
     *
     * @param $valid
     * @param $product_id
     *
     * @return mixed
     */
    public function cart_validation_with_multiple_products( $valid, $product_id ) {
        if ( ! class_exists( 'WC_Subscriptions' ) ) {
            return $valid;
        }

        $is_subscription            = WC_Subscriptions_Product::is_subscription( $product_id );
        $cart_contains_subscription = WC_Subscriptions_Cart::cart_contains_subscription();

        if ( $is_subscription && $cart_contains_subscription ) {

            WC_Subscriptions_Cart::remove_subscriptions_from_cart();

            wc_add_notice( __( 'A subscription has been removed from your cart. Due to payment gateway restrictions, different subscription products can not be purchased at the same time.', 'dokan' ), 'notice' );

        } elseif ( $is_subscription && ! $cart_contains_subscription && ! WC()->cart->is_empty() ) {

            WC()->cart->empty_cart();

            wc_add_notice( __( 'Products has been removed from your cart. Products and subscriptions can not be purchased at the same time.', 'dokan' ), 'notice' );

        } elseif ( $cart_contains_subscription ) {

            WC_Subscriptions_Cart::remove_subscriptions_from_cart();

            wc_add_notice( __( 'A subscription has been removed from your cart. Products and subscriptions can not be purchased at the same time.', 'dokan' ), 'notice' );

            // Redirect to cart page to remove subscription & notify shopper
            $add_to_cart_redirect = version_compare( WC_Subscriptions::$version, '4.0.0', '>=' ) ? 'WC_Subscriptions_Cart_Validator::add_to_cart_ajax_redirect' : 'WC_Subscriptions::redirect_ajax_add_to_cart';
            if ( version_compare( WC_VERSION, '3.0.8', '<' ) ) {
                add_filter( 'add_to_cart_fragments', $add_to_cart_redirect );
            } else {
                add_filter( 'woocommerce_add_to_cart_fragments', $add_to_cart_redirect );
            }
        }

        return $valid;
    }

    /**
     * Validate checkout if vendor has configured stripe account
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function check_vendor_configure_stripe( $data, $errors ) {
        if ( ! Helper::is_enabled() || Helper::allow_non_connected_sellers() ) {
            return;
        }

        if ( Helper::get_gateway_id() !== $data['payment_method'] ) {
            return;
        }

        foreach ( WC()->cart->get_cart() as $item ) {
            $product_id                                                          = $item['data']->get_id();
            $available_vendors[ get_post_field( 'post_author', $product_id ) ][] = $item['data'];
        }

        // if it's subscription product return early
        $subscription_product = wc_get_product( $product_id );

        if ( $subscription_product && 'product_pack' === $subscription_product->get_type() ) {
            return;
        }

        $vendor_names = [];

        foreach ( array_keys( $available_vendors ) as $vendor_id ) {
            $vendor       = dokan()->vendor->get( $vendor_id );
            $access_token = get_user_meta( $vendor_id, '_stripe_connect_access_key', true );

            if ( empty( $access_token ) ) {
                $vendor_products = [];

                foreach ( $available_vendors[$vendor_id] as $product ) {
                    $vendor_products[] = sprintf( '<a href="%s">%s</a>', $product->get_permalink(), $product->get_name() );
                }

                $vendor_names[$vendor_id] = [
                    'name'     => sprintf( '<a href="%s">%s</a>', esc_url( $vendor->get_shop_url() ), $vendor->get_shop_name() ),
                    'products' => implode( ', ', $vendor_products )
                ];
            }
        }

        foreach ( $vendor_names as $vendor_id => $data ) {
            $errors->add( 'stipe-not-configured', sprintf( __( '<strong>Error!</strong> You cannot complete your purchase until <strong>%s</strong> has enabled Stripe as a payment gateway. Please remove %s to continue.', 'dokan' ), $data['name'], $data['products'] ) );
        }
    }
}
