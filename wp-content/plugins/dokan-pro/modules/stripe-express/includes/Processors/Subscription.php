<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Processors;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WP_Error;
use WC_Order;
use WC_Product;
use WeDevs\Dokan\Exceptions\DokanException;
use DokanPro\Modules\Subscription\SubscriptionPack;
use WeDevs\DokanPro\Modules\StripeExpress\Api\Coupon;
use WeDevs\DokanPro\Modules\StripeExpress\Api\Product;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Support\UserMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Support\ProductMeta;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\StripeExpress\Api\Subscription as SubscriptionApi;

/**
 * Class for processing Subscription.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Processors
 */
class Subscription {

    /**
     * Vendor subscription product type.
     *
     * @since 3.7.8
     *
     * @var string
     */
    const VENDOR_SUBSCRIPTION_PRODUCT_TYPE = 'product_pack';

    /**
     * Subscription billing reasons to be disallowed.
     *
     * @since 3.7.8
     *
     * @var array
     */
    const DISALLOWED_BILLING_REASONS = [ 'manual', 'upcoming', 'subscription_threshold' ];

    /**
     * Retrieves stripe subscription ID.
     *
     * @since 3.7.8
     *
     * @param string       $stripe_subscription_id (Optional)
     * @param int|string   $customer_id            (Optional)
     * @param int|WC_Order $order                  (Optional)
     *
     * @return \Stripe\Subscription|WP_Error
     */
    public static function get( $stripe_subscription_id = null, $customer_id = null, $order = null ) {
        if ( empty( $stripe_subscription_id ) ) {
            $stripe_subscription_id = self::parse_subscription_id( $customer_id, $order );
        }

        try {
            return SubscriptionApi::get(
                $stripe_subscription_id,
                [
                    'expand' => [
                        'latest_invoice.payment_intent',
                        'latest_invoice.payment_intent.charges.data',
                        'pending_setup_intent',
                    ],
                ]
            );
        } catch ( DokanException $e ) {
            return new WP_Error( 'stripe-subscription-retrieve-error', $e->get_message() );
        }
    }

    /**
     * Creates a subscription for a vendor.
     *
     * @since 3.7.8
     *
     * @param array $data
     *
     * @return \Stripe\Subscription|WP_Error
     */
    public static function create( $data ) {
        try {
            if ( empty( $data['customer'] ) ) {
                $customer_id        = ! empty( WC()->session ) ? WC()->session->get_customer_id() : get_current_user_id();
                $stripe_customer_id = UserMeta::get_stripe_customer_id( $customer_id );

                if ( empty( $stripe_customer_id ) ) {
                    $stripe_customer_id = Customer::set( $customer_id )->create();

                    if ( is_wp_error( $stripe_customer_id ) ) {
                        return $stripe_customer_id;
                    }
                }

                $data['customer'] = $stripe_customer_id;
            }

            $data['expand'] = [
                'latest_invoice.payment_intent',
                'pending_setup_intent',
            ];

            return SubscriptionApi::create( $data );
        } catch ( DokanException $e ) {
            return new WP_Error( 'stripe-subscription-create-error', $e->get_message() );
        }
    }

    /**
     * Updates a stripe subscription.
     *
     * @since 3.7.8
     *
     * @param array             $data                   Data to update the subscription.
     * @param string            $stripe_subscription_id (Optional) Stripe subscription id. If not provided, it will be fetched from the customer ID or the order.
     * @param int|string        $customer_id            (Optional) Customer ID. If not provided, it will be according the current user id.
     * @param int|WC_Order|null $order                  (Optional) The order.
     *
     * @return \Stripe\Subscription|WP_Error
     */
    public static function update( $data, $stripe_subscription_id = null, $customer_id = null, $order = null ) {
        unset( $data['items'] );

        if ( empty( $stripe_subscription_id ) ) {
            $stripe_subscription_id = self::parse_subscription_id( $customer_id, $order );
        }

        try {
            return SubscriptionApi::update( $stripe_subscription_id, $data );
        } catch ( DokanException $e ) {
            return new WP_Error( 'stripe-subscription-update-error', $e->get_message() );
        }
    }

    /**
     * Parse subscription id from the customer/order.
     *
     * @since 3.7.8
     *
     * @param int|string|null   $customer_id
     * @param int|WC_Order|null $order
     *
     * @return string|false
     */
    public static function parse_subscription_id( $customer_id = null, $order = null ) {
        if ( empty( $customer_id ) ) {
            $customer_id = get_current_user_id();
        }

        $stripe_subscription_id = UserMeta::get_stripe_subscription_id( $customer_id );
        if ( empty( $stripe_subscription_id ) && ! empty( $order ) ) {
            if ( is_int( $order ) ) {
                $order = wc_get_order( $order );
            }

            if ( ! $order instanceof WC_Order ) {
                return false;
            }
            $stripe_subscription_id = OrderMeta::get_stripe_subscription_id( $order );
        }

        return $stripe_subscription_id;
    }

    /**
     * Retrives the stripe product by woccommerce product id.
     *
     * @since 3.7.8
     *
     * @param int|string $product_id
     *
     * @return \Stripe\Product|WP_Error|false
     */
    public static function get_product( $product_id ) {
        $stripe_product_id = ProductMeta::set( $product_id )->get_stripe_product_id();
        if ( empty( $stripe_product_id ) ) {
            return false;
        }

        try {
            return Product::get( $stripe_product_id );
        } catch ( DokanException $e ) {
            return new WP_Error( 'dokan-stripe-express-product-not-found', $e->get_message() );
        }
    }

    /**
     * Creates a product in Stripe end.
     *
     * @since 3.7.8
     *
     * @param WC_Product|int|string $product Product object or ID.
     * @param array                 $data    Additional data to add as product data (Optional)
     *
     * @return \Stripe\Product\WP_Error
     */
    public static function create_product( $product, $data = [] ) {
        if ( ! is_a( $product, 'WC_Product' ) ) {
            $product = wc_get_product( $product );
        }

        if ( ! $product ) {
            return new WP_Error( 'invalid-product', __( 'Product not found', 'dokan' ) );
        }

        try {
            $data           = self::prepare_product_data( $product, $data );
            $stripe_product = Product::create( $data );

            ProductMeta::set( $product )->update_stripe_product_id( $stripe_product->id )->save();

            return $stripe_product;
        } catch ( DokanException $e ) {
            return new WP_Error( 'dokan-stripe-express-product-create-error', __( 'Product could not be created', 'dokan' ) );
        }
    }

    /**
     * Updates a product in Stripe end.
     *
     * @since 3.7.8
     *
     * @param WC_Product|int|string $product
     * @param array                 $data
     *
     * @return \Stripe\Product|WP_Error
     */
    public static function update_product( $product, $data ) {
        if ( ! is_a( $product, 'WC_Product' ) ) {
            $product = wc_get_product( $product );
        }

        if ( ! $product ) {
            return new WP_Error( 'product-not-found', __( 'Product not found', 'dokan' ) );
        }

        $stripe_product_id = ProductMeta::set( $product )->get_stripe_product_id();
        if ( ! $stripe_product_id ) {
            return new WP_Error( 'product-not-found', __( 'Product not found', 'dokan' ) );
        }

        try {
            $data = ! empty( $data ) ? (array) $data : [];
            $data['existing'] = true;
            $data = self::prepare_product_data( $product, $data );

            return Product::update( $stripe_product_id, $data );
        } catch ( DokanException $e ) {
            return new WP_Error( 'dokan-stripe-express-product-update-error', __( 'Product could not be updated', 'dokan' ) );
        }
    }

    /**
     * Processes coupon for an order.
     * If coupon or discount is applied, it will create
     * a coupon with the total discounted amount in the Stripe end
     * while creating a subscription.
     *
     * @since 3.7.8
     *
     * @param array    $data     (Optional)
     * @param float    $discount (Optional)
     * @param WC_Order $order    (Optional)
     *
     * @return string|false|WP_Error `Coupon ID` on successful creation of coupon on need, `false` if no discount available, `WP_Error` for any API error.
     */
    public static function process_discount( $data = [], $discount = 0, $order = false ) {
        $currency = get_woocommerce_currency();
        $source   = ! $order || ! $order instanceof WC_Order ? WC()->cart : $order;

        if ( empty( $discount ) ) {
            $discount = $source->get_discount_total();
        }

        if ( empty( $discount ) ) {
            return false;
        }

        $data = wp_parse_args(
            (array) $data,
            [
                'amount_off' => Helper::get_stripe_amount( $discount ),
                'currency'   => strtolower( $currency ),
            ]
        );

        try {
            $coupon = Coupon::create( $data );
        } catch ( DokanException $e ) {
            return new WP_Error( 'coupon-create-error', $e->get_message() );
        }

        return $coupon->id;
    }

    /**
     * Prepares metadata for product.
     *
     * @since 3.7.8
     *
     * @param WC_Product $product
     * @param array      $data    (Optional)
     *
     * @return array
     */
    public static function prepare_product_data( WC_Product $product, $data = [] ) {
        $product_data = [];

        if ( ! empty( $data['name'] ) ) {
            $product_data['name'] = $data['name'];
        }

        if ( ! empty( $data['description'] ) ) {
            $product_data['description'] = $data['description'];
        }

        if ( ! empty( $data['metadata'] ) ) {
            $product_data['metadata'] = $data['metadata'];
        }

        /*
         * If `existing` key is false or empty, that means we are creating a new product.
         * As `name` and `descrtiption` are required fields while cresting a product,
         * we need to make sure these data are not empty.
         * Also, though `metadata` is not required, we still need to add some metadata
         * so that we can maintain the mapping between product from both end.
         */
        if ( empty( $data['existing'] ) ) {
            $product_id = $product->get_id();

            if ( empty( $product_data['name'] ) ) {
                $product_title = $product->get_title();
                $product_data['name'] = ! empty( $product_title )
                    ? $product_title
                    /* translators: %s) product title */
                    : sprintf( __( 'Dokan product #%s', 'dokan' ), $product_id );
            }

            if ( empty( $product_data['description'] ) ) {
                $product_description = $product->get_description();
                $product_data['description'] = ! empty( $product_description )
                    ? $product_description
                    /* translators: %s) product description */
                    : sprintf( __( 'Dokan product #%s', 'dokan' ), $product_id );
            }

            if ( empty( $product_data['metadata']['type'] ) ) {
                $product_data['metadata']['type'] = ! empty( $data['type'] ) ? $data['type'] : $product->get_type();
            }

            if ( empty( $product_data['metadata']['product_id'] ) ) {
                $product_data['metadata']['product_id'] = $product_id;
            }
        }

        if ( isset( $data['active'] ) ) {
            $product_data['active'] = $data['active'];
        }

        if ( isset( $data['statement_descriptor'] ) ) {
            $product_data['statement_descriptor'] = Helper::clean_statement_descriptor( $data['statement_descriptor'] );
        }

        return $product_data;
    }

    /**
     * Checks if order is a subscription order.
     *
     * @since 3.7.8
     *
     * @param int|string $order_id
     *
     * @return boolean
     */
    public static function is_subscription_order( $order_id ) {
        return self::is_wc_subscription_order( $order_id )
            || self::is_recurring_vendor_subscription_order( $order_id );
    }

    /**
     * Checks if cart contains a subscription.
     *
     * @since 3.7.8
     *
     * @return boolean
     */
    public static function exists_in_cart() {
        return self::cart_contains_wc_subscription()
            || self::cart_contains_recurring_vendor_subscription();
    }

    /**
     * Checks if WooCOmmerce ubscription is enabled.
     *
     * @since 3.7.8
     *
     * @return boolean
     */
    public static function has_wc_subscription() {
        return class_exists( 'WC_Subscriptions' ) && version_compare( \WC_Subscriptions::$version, '2.2.0', '>=' );
    }

    /**
     * Checks if the order has subscription.
     *
     * @since 3.7.8
     *
     * @param int|string $order_id
     *
     * @return boolean
     */
    public static function is_wc_subscription_order( $order_id ) {
        return self::has_wc_subscription()
            && (
                self::order_contains_wc_subscription( $order_id )
                || self::order_contains_wc_subscription_renewal( $order_id )
                || wcs_is_subscription( $order_id )
            );
    }

    /**
     * Checks if the order contains subscription order.
     *
     * @since 3.7.8
     *
     * @param int $order_id
     *
     * @return boolean
     */
    public static function order_contains_wc_subscription( $order_id ) {
        return function_exists( 'wcs_order_contains_subscription' ) && wcs_order_contains_subscription( $order_id );
    }

    /**
     * Checks if the order contains subscription renewal.
     *
     * @since 3.7.8
     *
     * @param int $order_id
     *
     * @return boolean
     */
    public static function order_contains_wc_subscription_renewal( $order_id ) {
        return function_exists( 'wcs_order_contains_renewal' ) && wcs_order_contains_renewal( $order_id );
    }

    /**
     * Retrieves subscriptions from order.
     *
     * @since 3.7.8
     *
     * @param int|WC_Order $order
     *
     * @return \WC_Subscription[]
     */
    public static function get_wc_subscriptions_from_order( $order ) {
        if ( ! self::has_wc_subscription() ) {
            return [];
        }

        if ( ! is_object( $order ) ) {
            $order = wc_get_order( $order );
        }

        if ( ! $order instanceof WC_Order ) {
            return [];
        }

        if ( self::order_contains_wc_subscription( $order->get_id() ) ) {
            return wcs_get_subscriptions_for_order( $order );
        }

        if ( self::order_contains_wc_subscription_renewal( $order->get_id() ) ) {
            return wcs_get_subscriptions_for_renewal_order( $order );
        }

        return [];
    }

    /**
     * Checks if a product is a subscription.
     *
     * @since 3.7.8
     *
     * @param int|string $product_id
     *
     * @return boolean
     */
    public static function is_wc_subscription_product( $product_id ) {
        return self::has_wc_subscription()
            && class_exists( 'WC_Subscriptions_Product' )
            && \WC_Subscriptions_Product::is_subscription( $product_id );
    }

    /**
     * Checks if cart contains wc subscriptions.
     *
     * @since 3.7.8
     *
     * @return boolean
     */
    public static function cart_contains_wc_subscription() {
        if ( self::has_wc_subscription() ) {
            return \WC_Subscriptions_Cart::cart_contains_subscription() || self::cart_contains_renewal();
        }
        return false;
    }

    /**
     * Checks the cart to see if it contains a subscription product renewal.
     *
     * @since 3.7.8
     *
     * @return mixed The cart item containing the renewal as an array, else false.
     */
    public static function cart_contains_renewal() {
        if ( ! self::has_wc_subscription() || ! function_exists( 'wcs_cart_contains_renewal' ) ) {
            return false;
        }
        return wcs_cart_contains_renewal();
    }

    /**
     * Checks if a payment mathod needs to be changed for a subscription.
     *
     * @since 3.7.8
     *
     * @param int|string $order_id
     *
     * @return boolean
     */
    public static function subcription_payment_method_needs_change( $order_id ) {
        return (
            self::has_wc_subscription() &&
            self::is_wc_subscription_order( $order_id ) &&
            self::is_changing_payment_method()
        );
    }

    /**
     * Returns whether this user is changing the payment method for a subscription.
     *
     * @since 3.7.8
     *
     * @return bool
     */
    public static function is_changing_payment_method() {
        if ( isset( $_GET['change_payment_method'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            return wcs_is_subscription( wc_clean( wp_unslash( $_GET['change_payment_method'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
        }
        return false;
    }

    /**
     * Once an intent has been verified, perform some final actions for early renewals.
     *
     * @since 3.7.8
     *
     * @param WC_Order $renewal_order
     *
     * @return void
     */
    public static function process_early_renewal_success( $renewal_order ) {
        if ( self::has_wc_subscription() && isset( $_GET['early_renewal'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            wcs_update_dates_after_early_renewal( wcs_get_subscription( $renewal_order->get_meta( '_subscription_renewal' ) ), $renewal_order );
            wc_add_notice( __( 'Your early renewal order was successful.', 'dokan' ), 'success' );
        }
    }

    /**
     * During early renewals, instead of failing the renewal order, delete it and let Subscription redirect to the checkout.
     *
     * @since 3.7.8
     *
     * @param WC_Order $renewal_order
     *
     * @return void
     */
    public static function process_early_renewal_failure( $renewal_order ) {
        if ( self::has_wc_subscription() && isset( $_GET['early_renewal'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
            $renewal_order->delete( true );
            wc_add_notice( __( 'Payment authorization for the renewal order was unsuccessful, please try again.', 'dokan' ), 'error' );
            wp_safe_redirect( wcs_get_early_renewal_url( wcs_get_subscription( $renewal_order->get_meta( '_subscription_renewal' ) ) ) );
            exit;
        }
    }

    /**
     * Check whether subscription module is enabled or not
     *
     * @since 3.7.8
     *
     * @return bool
     */
    public static function has_vendor_subscription_module() {
        // It's not product_subscription, id for vendor subscription module is product_subscription
        return function_exists( 'dokan_pro' ) && dokan_pro()->module->is_active( 'product_subscription' );
    }

    /**
     * Checks if a order is a subscription order.
     *
     * @since 3.7.8
     *
     * @param WC_Order|int $order
     *
     * @return boolean
     */
    public static function is_vendor_subscription_order( $order ) {
        if ( ! self::has_vendor_subscription_module() ) {
            return false;
        }

        $product = SubscriptionHelper::get_vendor_subscription_product_by_order( $order );

        return ! empty( $product );
    }

    /**
     * Checks if an order is a recurring vendor subscription order.
     *
     * @since 3.7.8
     *
     * @param WC_Order|int $order
     *
     * @return boolean
     */
    public static function is_recurring_vendor_subscription_order( $order ) {
        if ( ! self::has_vendor_subscription_module() ) {
            return false;
        }

        if ( is_int( $order ) ) {
            $order = wc_get_order( $order );
        }

        if ( ! $order ) {
            return false;
        }

        $product = SubscriptionHelper::get_vendor_subscription_product_by_order( $order );

        if ( empty( $product ) ) {
            return false;
        }

        $dokan_subscription = new SubscriptionPack( $product->get_id(), $order->get_customer_id() );
        return $dokan_subscription->is_recurring();
    }

    /**
     * Checks if the product is a vendor subscription product.
     *
     * @since 3.7.8
     *
     * @param int $product_id
     *
     * @return bool
     */
    public static function is_vendor_subscription_product( $product_id ) {
        return self::has_vendor_subscription_module()
            && SubscriptionHelper::is_subscription_product( $product_id );
    }

    /**
     * Checks if the product is a recurring vendor subscription product.
     *
     * @since 3.7.8
     *
     * @param int $product_id
     *
     * @return bool
     */
    public static function is_recurring_vendor_subscription_product( $product_id ) {
        return self::has_vendor_subscription_module()
            && SubscriptionHelper::is_subscription_product( $product_id )
            && SubscriptionHelper::is_recurring_pack( $product_id );
    }

    /**
     * Verifies if Cart contains recurring vendor subscription product.
     *
     * @since 3.7.8
     *
     * @return boolean
     */
    public static function cart_contains_recurring_vendor_subscription() {
        if ( empty( WC()->cart ) ) {
            return false;
        }

        foreach ( WC()->cart->cart_contents as $item ) {
            $product_id = $item['data']->get_id();

            if ( self::is_recurring_vendor_subscription_product( $product_id ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Verifies if Cart contains vendor subscription product.
     *
     * @since 3.7.8
     *
     * @return boolean
     */
    public static function cart_contains_vendor_subscription() {
        if ( empty( WC()->cart ) ) {
            return false;
        }

        foreach ( WC()->cart->cart_contents as $item ) {
            $product_id = $item['data']->get_id();

            if ( self::is_vendor_subscription_product( $product_id ) ) {
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieves vendor id by stripe subscriptoin
     *
     * @since 3.7.8
     *
     * @param string|\Stripe\Subscription $subscription
     *
     * @return int|null
     */
    public static function get_vendor_id_by_subscription( $subscription ) {
        if ( is_string( $subscription ) ) {
            $subscription = self::get( $subscription );
        }

        if ( ! $subscription instanceof \Stripe\Subscription ) {
            return null;
        }

        global $wpdb;

        $vendor_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT `user_id`
                FROM $wpdb->usermeta
                WHERE `meta_value` = %s
                AND ( `meta_key` = %s OR `meta_key` = %s )",
                UserMeta::stripe_subscription_id_key(),
                UserMeta::stripe_debug_subscription_id_key(),
                $subscription->id
            )
        );

        if ( ! empty( $vendor_id ) ) {
            return absint( $vendor_id );
        }

        $order = self::get_order_by_subscription( $subscription );

        return $order ? $order->get_customer_id() : null;
    }

    /**
     * Retrieves vendor id by stripe subscriptoin
     *
     * @since 3.7.8
     *
     * @param string|\Stripe\Subscription $subscription
     *
     * @return WC_Order|false
     */
    public static function get_order_by_subscription( $subscription ) {
        if ( is_string( $subscription ) ) {
            $subscription = self::get( $subscription );
        }

        if ( ! $subscription instanceof \Stripe\Subscription ) {
            return false;
        }

        global $wpdb;

        $order_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT `post_id`
                FROM $wpdb->postmeta
                WHERE `meta_key` = %s
                AND `meta_value` = %s",
                OrderMeta::stripe_subscription_id_key(),
                $subscription->id
            )
        );

        if ( empty( $order_id ) && ! empty( $subscription->metadata['order_id'] ) ) {
            $order_id = $subscription->metadata['order_id'];
        }

        if ( empty( $order_id ) ) {
            return false;
        }

        $order = wc_get_order( absint( $order_id ) );
        if ( ! $order instanceof WC_Order ) {
            return false;
        }

        return $order;
    }
}
