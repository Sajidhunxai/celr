<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\PaymentRequest;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use Exception;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Traits\PaymentRequestUtils;

/**
 * Ajax handler class for Payment request options.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\PaymentRequest
 */
class Ajax {

    use PaymentRequestUtils;

    /**
     * Constructor for Ajax class.
     *
     * @since 3.7.8
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * Intiates AJAX hooks.
     *
     * @since 3.7.8
     *
     * @return void
     */
    private function hooks() {
        add_action( 'wc_ajax_dokan_stripe_express_log_errors', [ $this, 'log_errors' ] );
        add_action( 'wc_ajax_dokan_stripe_express_clear_cart', [ $this, 'clear_cart' ] );
        add_action( 'wc_ajax_dokan_stripe_express_add_to_cart', [ $this, 'add_to_cart' ] );
        add_action( 'wc_ajax_dokan_stripe_express_create_order', [ $this, 'create_order' ] );
        add_action( 'wc_ajax_dokan_stripe_express_get_cart_details', [ $this, 'get_cart_details' ] );
        add_action( 'wc_ajax_dokan_stripe_express_get_shipping_options', [ $this, 'get_shipping_options' ] );
        add_action( 'wc_ajax_dokan_stripe_express_update_shipping_method', [ $this, 'update_shipping_method' ] );
        add_action( 'wc_ajax_dokan_stripe_express_get_selected_product_data', [ $this, 'get_selected_product_data' ] );
    }

    /**
     * Log errors coming from Payment Request
     *
     * @since 3.7.8
     *
     * @requires void
     */
    public function log_errors() {
        check_ajax_referer( 'dokan-stripe-express-log-errors', 'security' );

        $errors = isset( $_POST['errors'] ) ? wc_clean( wp_unslash( $_POST['errors'] ) ) : '';

        Helper::log( $errors, 'Payment Request Button' );

        exit;
    }

    /**
     * Clears cart.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function clear_cart() {
        check_ajax_referer( 'dokan-stripe-express-clear-cart', 'security' );

        WC()->cart->empty_cart();
        exit;
    }

    /**
     * Adds the current product to the cart. Used on product detail page.
     *
     * @since 3.7.8
     *
     * @return mixed
     */
    public function add_to_cart() {
        check_ajax_referer( 'dokan-stripe-express-add-to-cart', 'security' );

        if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
            define( 'WOOCOMMERCE_CART', true );
        }

        WC()->shipping->reset_shipping();

        $product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
        $qty          = ! isset( $_POST['qty'] ) ? 1 : absint( $_POST['qty'] );
        $product      = wc_get_product( $product_id );
        $product_type = $product->get_type();

        // First empty the cart to prevent wrong calculation.
        WC()->cart->empty_cart();

        if ( ( 'variable' === $product_type || 'variable-subscription' === $product_type ) && isset( $_POST['attributes'] ) ) {
            $attributes = wc_clean( wp_unslash( $_POST['attributes'] ) );

            $data_store   = \WC_Data_Store::load( 'product' );
            $variation_id = $data_store->find_matching_product_variation( $product, $attributes );

            WC()->cart->add_to_cart( $product->get_id(), $qty, $variation_id, $attributes );
        }

        if ( 'simple' === $product_type || 'subscription' === $product_type ) {
            WC()->cart->add_to_cart( $product->get_id(), $qty );
        }

        WC()->cart->calculate_totals();

        $data           = [];
        $data          += $this->build_display_items();
        $data['result'] = 'success';

        wp_send_json( $data );
    }

    /**
     * Create order. Security is handled by WC.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function create_order() {
        if ( WC()->cart->is_empty() ) {
            wp_send_json_error( __( 'Empty cart', 'dokan' ) );
        }

        if ( ! defined( 'WOOCOMMERCE_CHECKOUT' ) ) {
            define( 'WOOCOMMERCE_CHECKOUT', true );
        }

        // Normalizes billing and shipping state values.
        $this->normalize_state();

        // In case the state is required, but is missing, add a more descriptive error notice.
        $this->validate_state();

        WC()->checkout()->process_checkout();

        die( 0 );
    }

    /**
     * Get cart details.
     *
     * @since 3.7.8
     *
     * @return mixed
     */
    public function get_cart_details() {
        check_ajax_referer( 'dokan-stripe-express-payment-request', 'security' );

        if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
            define( 'WOOCOMMERCE_CART', true );
        }

        WC()->cart->calculate_totals();

        $currency = get_woocommerce_currency();

        // Set mandatory payment details.
        $data = [
            'shipping_required' => WC()->cart->needs_shipping(),
            'order_data'        => [
                'currency'     => strtolower( $currency ),
                'country_code' => substr( get_option( 'woocommerce_default_country' ), 0, 2 ),
            ],
        ];

        $data['order_data'] += $this->build_display_items();

        wp_send_json( $data );
    }

    /**
     * Get shipping options.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function get_shipping_options() {
        check_ajax_referer( 'dokan-stripe-express-payment-request-shipping', 'security' );

        $shipping_address = [
            'country'   => isset( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '',
            'state'     => isset( $_POST['state'] ) ? sanitize_text_field( wp_unslash( $_POST['state'] ) ) : '',
            'postcode'  => isset( $_POST['postcode'] ) ? sanitize_text_field( wp_unslash( $_POST['postcode'] ) ) : '',
            'city'      => isset( $_POST['city'] ) ? sanitize_text_field( wp_unslash( $_POST['city'] ) ) : '',
            'address'   => isset( $_POST['address'] ) ? sanitize_text_field( wp_unslash( $_POST['address'] ) ) : '',
            'address_2' => isset( $_POST['address_2'] ) ? sanitize_text_field( wp_unslash( $_POST['address_2'] ) ) : '',
        ];

        $should_show_itemized_view = ! isset( $_POST['is_product_page'] )
            ? true
            : filter_var( wp_unslash( $_POST['is_product_page'] ), FILTER_VALIDATE_BOOLEAN );

        $data = $this->process_shipping_options( $shipping_address, $should_show_itemized_view );
        wp_send_json( $data );
    }

    /**
     * Update shipping method.
     *
     * @since 3.7.8
     *
     * @return mixed
     */
    public function update_shipping_method() {
        check_ajax_referer( 'dokan-stripe-express-update-shipping-method', 'security' );

        if ( ! defined( 'WOOCOMMERCE_CART' ) ) {
            define( 'WOOCOMMERCE_CART', true );
        }

        $shipping_methods = filter_input( INPUT_POST, 'shipping_method', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
        $this->modify_shipping_method( $shipping_methods );

        WC()->cart->calculate_totals();

        $should_show_itemized_view = ! isset( $_POST['is_product_page'] )
            ? true
            : filter_var( wp_unslash( $_POST['is_product_page'] ), FILTER_VALIDATE_BOOLEAN );

        $data           = [];
        $data          += $this->build_display_items( $should_show_itemized_view );
        $data['result'] = 'success';

        wp_send_json( $data );
    }

    /**
     * Gets the selected product data.
     *
     * @since 3.7.8
     *
     * @return mixed
     */
    public function get_selected_product_data() {
        check_ajax_referer( 'dokan-stripe-express-get-selected-product-data', 'security' );

        try {
            $product_id   = isset( $_POST['product_id'] ) ? absint( $_POST['product_id'] ) : 0;
            $qty          = ! isset( $_POST['qty'] ) ? 1 : apply_filters( 'woocommerce_add_to_cart_quantity', absint( $_POST['qty'] ), $product_id );
            $addon_value  = isset( $_POST['addon_value'] ) ? max( floatval( $_POST['addon_value'] ), 0 ) : 0;
            $product      = wc_get_product( $product_id );
            $variation_id = null;

            if ( ! is_a( $product, 'WC_Product' ) ) {
                /* translators: %d is the product Id */
                throw new Exception( sprintf( __( 'Product with the ID (%d) cannot be found.', 'dokan' ), $product_id ) );
            }

            if ( 'variable' === $product->get_type() && isset( $_POST['attributes'] ) ) {
                $attributes = wc_clean( wp_unslash( $_POST['attributes'] ) );

                $data_store   = \WC_Data_Store::load( 'product' );
                $variation_id = $data_store->find_matching_product_variation( $product, $attributes );

                if ( ! empty( $variation_id ) ) {
                    $product = wc_get_product( $variation_id );
                }
            }

            // Force quantity to 1 if sold individually and check for existing item in cart.
            if ( $product->is_sold_individually() ) {
                $qty = apply_filters( 'dokan_stripe_express_payment_request_add_to_cart_sold_individually_quantity', 1, $qty, $product_id, $variation_id );
            }

            if ( ! $product->has_enough_stock( $qty ) ) {
                /* translators: 1: product name 2: quantity in stock */
                throw new Exception( sprintf( __( 'You cannot add that amount of "%1$s"; to the cart because there is not enough stock (%2$s remaining).', 'dokan' ), $product->get_name(), wc_format_stock_quantity_for_display( $product->get_stock_quantity(), $product ) ) );
            }

            $total = $qty * $this->get_product_price( $product ) + $addon_value;

            $quantity_label = 1 < $qty ? ' (x' . $qty . ')' : '';

            $data  = [];
            $items = [];

            $items[] = [
                'label'  => $product->get_name() . $quantity_label,
                'amount' => Helper::get_stripe_amount( $total ),
            ];

            if ( wc_tax_enabled() ) {
                $items[] = [
                    'label'   => __( 'Tax', 'dokan' ),
                    'amount'  => 0,
                    'pending' => true,
                ];
            }

            if ( wc_shipping_enabled() && $product->needs_shipping() ) {
                $items[] = [
                    'label'   => __( 'Shipping', 'dokan' ),
                    'amount'  => 0,
                    'pending' => true,
                ];

                $data['shippingOptions'] = [
                    'id'     => 'pending',
                    'label'  => __( 'Pending', 'dokan' ),
                    'detail' => '',
                    'amount' => 0,
                ];
            }

            $data['displayItems'] = $items;
            $data['total']        = [
                'label'   => $this->get_total_label(),
                'amount'  => Helper::get_stripe_amount( $total ),
                'pending' => true,
            ];

            $data['requestShipping'] = ( wc_shipping_enabled() && $product->needs_shipping() );
            $data['currency']        = strtolower( get_woocommerce_currency() );
            $data['country_code']    = substr( get_option( 'woocommerce_default_country' ), 0, 2 );

            wp_send_json( $data );
        } catch ( Exception $e ) {
            wp_send_json( [ 'error' => wp_strip_all_tags( $e->getMessage() ) ] );
        }
    }
}
