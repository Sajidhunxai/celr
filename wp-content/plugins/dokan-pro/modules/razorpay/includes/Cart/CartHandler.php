<?php

namespace WeDevs\DokanPro\Modules\Razorpay\Cart;

use WP_Error;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\Razorpay\Helper;
use WeDevs\DokanPro\Modules\Razorpay\Order\OrderManager;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class CartHandler.
 *
 * @package WeDevs\DokanPro\Modules\Razorpay\Cart
 *
 * @since 3.5.0
 */
class CartHandler {
    /**
     * CartHandler constructor.
     *
     * @since 3.5.0
     */
    public function __construct() {
        // Register scripts
        add_action( 'init', [ $this, 'register_scripts' ] );

        // Checkout and Cart Validation
        add_action( 'woocommerce_after_checkout_validation', [ $this, 'after_checkout_validation' ], 15, 2 );
        add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'validate_vendor_is_connected' ], 10, 2 );
        add_filter( 'woocommerce_available_payment_gateways', [ $this, 'checkout_filter_gateway' ], 1 );

        // Show Razorpay form after checkout in receipt page
        add_action( 'woocommerce_receipt_' . Helper::get_gateway_id(), [ $this, 'generate_razorpay_form' ] );
    }

    /**
     * Register scripts to load razorpay checkout js.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function register_scripts() {
        // Check if our payment gateway is disabled
        if ( ! Helper::is_enabled() ) {
            return;
        }

        $version                    = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? time() : DOKAN_PRO_PLUGIN_VERSION;
        $razorpay_checkout_js       = 'https://checkout.razorpay.com/v1/checkout.js';
        $dokan_razorpay_checkout_js = DOKAN_RAZORPAY_ASSETS . 'js/razorpay-checkout.js';

        wp_register_script( 'dokan_razorpay_checkout', $razorpay_checkout_js, [ 'jquery' ], time(), true ); // Don't cache this script
        wp_register_script( 'dokan_razorpay_wc_script', $dokan_razorpay_checkout_js, [ 'jquery' ], $version, true );
    }

    /**
     * Enqueue and Register Checkout Page Script.
     *
     * @since 3.5.0
     *
     * @param array $checkout_data
     *
     * @return void
     */
    private function enqueue_scripts() {
        // Check if not in checkout pay page
        if ( ! is_checkout_pay_page() ) {
            return;
        }

        wp_enqueue_script( 'dokan_razorpay_checkout' );
        wp_enqueue_script( 'dokan_razorpay_wc_script' );
    }

    /**
     * Localize scripts.
     *
     * @since 3.5.0
     *
     * @param array $checkout_data
     *
     * @return void
     */
    private function localized_scripts( $checkout_data ) {
        wp_localize_script(
            'dokan_razorpay_wc_script',
            'dokan_razorpay_checkout_data',
            $checkout_data
        );
    }

    /**
     * Display Razorpay Payment form on the checkout page after order receipt.
     *
     * @since 3.5.0
     *
     * @param int|\WC_Order $order
     *
     * @return string
     */
    public function generate_razorpay_form( $order_id ) {
        // Check if not in checkout pay page
        if ( ! is_checkout_pay_page() ) {
            return;
        }

        // Check if our payment gateway is disabled
        if ( ! Helper::is_enabled() ) {
            return;
        }

        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return new WP_Error(
                'dokan_rest_invalid_order', __( 'Invalid order', 'dokan' ), [
                    'status' => 404,
                ]
            );
        }

        // Check if payment gateway is razorpay
        if ( Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return;
        }

        try {
            $razorpay_order_id = OrderManager::create_razorpay_order( $order_id );
            if ( is_wp_error( $razorpay_order_id ) ) {
                // translators: 1: error message
                throw new \Exception( sprintf( __( 'Invalid Order Data. %s', 'dokan' ), $razorpay_order_id->get_error_message() ) );
            }

            $checkout_args = [
                'order_id'     => $razorpay_order_id,
                'key'          => Helper::get_key_id(),
                'name'         => get_bloginfo( 'name' ),
                'currency'     => get_woocommerce_currency(),
                /* translators: 1: Blog Name, 2: Order id  */
                'description'  => sprintf( __( '%1$s - Order %2$s', 'dokan' ), wp_specialchars_decode( get_bloginfo( 'name' ), ENT_QUOTES ), $order->get_order_number() ),
                'notes'        => [
                    'dokan_order_id' => $order_id,
                ],
                'prefill'      => Helper::get_customer_info( $order ),
                '_'            => [
                    'integration'                => Helper::get_gateway_id(),
                    'integration_version'        => dokan_pro()->version,
                    'integration_parent_version' => dokan()->version,
                ],
            ];

            return $this->generate_order_form( $checkout_args );
        } catch ( \Exception $e ) {
            ?>
            <div class="dokan-alert dokan-alert-danger">
                <?php echo esc_html( $e->getMessage() ); ?>
            </div>
            <?php
        }
    }

    /**
     * Generates Razorpay order form in Order Reciept Page.
     *
     * @since 3.5.0
     *
     * @param array $checkout_args
     *
     * @return void
     **/
    private function generate_order_form( $args ) {
        $order_id          = $args['notes']['dokan_order_id'];
        $razorpay_order_id = $args['order_id'];

        $order = wc_get_order( $order_id );
        if ( empty( $order ) ) {
            return;
        }

        $redirect_url       = Helper::get_redirect_url( $order_id, $razorpay_order_id );
        $args['cancel_url'] = Helper::get_redirect_url( $order_id, $razorpay_order_id, true );

        $this->enqueue_scripts();
        $this->localized_scripts( $args );
        ?>
            <p class="dokan-razorpay-thank-you"><?php esc_html_e( 'Thank you for your order, please click the button below to pay with Razorpay.', 'dokan' ); ?></p>
            <form id="dokan_razorpay_form" action="<?php echo esc_url( $redirect_url ); ?>" method="POST">
                <?php wp_nonce_field( 'dokan_razorpay_pay', 'dokan_razorpay_checkout_nonce' ); ?>
            </form>

            <p class="woocommerce-info woocommerce-message dokan-razorpay-success dokan-hide">
                <?php esc_html_e( 'Please wait while we are processing your payment.', 'dokan' ); ?>
                &nbsp;&nbsp;
                <span class="dokan-spinner dokan-razorpay-connect-spinner"></span>
            </p>

            <p class="dokan-razorpay-pay-buttons">
                <button id="dokan-razorpay-btn">
                    <?php esc_html_e( 'Pay Now', 'dokan' ); ?>
                </button>
                <button id="dokan-razorpay-btn-cancel" onclick="location.href='<?php echo esc_url( $args['cancel_url'] ); ?>'">
                    <?php esc_html_e( 'Cancel', 'dokan' ); ?>
                </button>
            </p>
        <?php
    }

    /**
     * Validation after checkout.
     *
     * @see https://razorpay.com/docs/api/route/#request-parameters
     *
     * @since 3.5.0
     *
     * @param array $data
     * @param array $errors
     *
     * @return void
     */
    public function after_checkout_validation( $data, $errors ) {
        if ( Helper::get_gateway_id() !== $data['payment_method'] ) {
            return;
        }

        if ( ! is_object( WC()->cart ) ) {
            return;
        }

        $available_vendors = [];
        foreach ( WC()->cart->get_cart() as $item ) {
            $product_id = $item['data']->get_id();

            $available_vendors[ get_post_field( 'post_author', $product_id ) ][] = $item['data'];
        }

        foreach ( array_keys( $available_vendors ) as $vendor_id ) {
            if ( ! Helper::is_seller_enable_for_receive_payment( $vendor_id ) ) {
                $vendor_products = [];
                foreach ( $available_vendors[ $vendor_id ] as $product ) {
                    $vendor_products[] = sprintf( '<a href="%s">%s</a>', $product->get_permalink(), $product->get_name() );
                }

                $errors->add(
                    'razorpay-not-configured',
                    wp_kses(
                        sprintf(
                            /* translators: 1: Vendor products */
                            __( '<strong>Error!</strong> Remove product %s and continue checkout, this product/vendor is not eligible to be paid with Razorpay', 'dokan' ),
                            implode( ', ', $vendor_products )
                        ),
                        [
                            'strong' => [],
                        ]
                    )
                );
            }
        }
    }

    /**
     * If Dokan-Razorpay is only payment gateway available and vendor is not connected with Razorpay,
     * product can not be added to cart for that vendor
     *
     * @since 3.5.0
     *
     * @param bool $passed
     * @param int $product_id
     *
     * @return bool
     */
    public function validate_vendor_is_connected( $passed, $product_id ) {
        // check if dokan razorpay is only payment gateway available
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();

        if ( ! array_key_exists( Helper::get_gateway_id(), $available_gateways ) ) {
            return $passed;
        }

        // check if razorpay is ready
        if ( ! Helper::is_ready() ) {
            return $passed;
        }

        if ( count( $available_gateways ) > 1 ) {
            return $passed;
        }

        // If product is subscription product, we're not adding this product to cart
        // It's a temporary check. After adding subscription feature, we'll remove this.
        if ( Helper::is_vendor_subscription_product( $product_id ) ) {
            $message = wp_kses(
                sprintf(
                    /* translators: 1: Product title, 2: Gateway title */
                    __( '<strong>Error!</strong> Could not add product %1$s to cart, Subscription product is not eligible to be paid with %2$s yet.', 'dokan' ),
                    get_the_title( $product_id ),
                    Helper::get_gateway_title()
                ),
                [
                    'strong' => [],
                ]
            );

            wc_add_notice( $message, 'error' );
            return false;
        }

        // get post author
        $seller_id = get_post_field( 'post_author', $product_id );

        // check if vendor is already connected with Razorpay
        if ( ! Helper::is_seller_enable_for_receive_payment( $seller_id ) ) {
            $message = wp_kses(
                sprintf(
                    /* translators: 1: Product title, 2: Gateway title */
                    __( '<strong>Error!</strong> Could not add product %1$s to cart, this product/vendor is not eligible to be paid with %2$s', 'dokan' ),
                    get_the_title( $product_id ), Helper::get_gateway_title()
                ),
                [
                    'strong' => [],
                ]
            );

            wc_add_notice( $message, 'error' );
            return false;
        }

        return $passed;
    }

    /**
     * Hide Razorpay payment gateway for subscription product.
     *
     * It's a temporary check. After adding subscription feature, we'll remove this.
     *
     * @since 3.5.0
     *
     * @param array $gateways
     *
     * @return array
     */
    public function checkout_filter_gateway( $gateways ) {
        if ( ! Helper::is_ready() ) {
            return $gateways;
        }

        if ( ! isset( $gateways[ Helper::get_gateway_id() ] ) ) {
            return $gateways;
        }

        if ( empty( WC()->cart->cart_contents ) ) {
            return $gateways;
        }

        // If we find any subscription product in cart, we're not showing razorpay gateway
        foreach ( WC()->cart->cart_contents as $values ) {
            if ( Helper::is_vendor_subscription_product( $values['data']->get_id() ) ) {
                unset( $gateways[ Helper::get_gateway_id() ] );
                break;
            }
        }

        return $gateways;
    }

    /**
     * Loads the order from the current request.
     *
     * @since 3.5.0
     *
     * @return \WC_Order
     * @throws DokanException An exception if there is no order key or the order does not exist.
     */
    public function get_order_from_request() {
        // Check for an order key in the request.
        $order_key = isset( $_GET['order_key'] ) ? sanitize_key( $_GET['order_key'] ) : null; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
        if ( empty( $order_key ) ) {
            throw new DokanException( 'missing-order-key', __( 'Invalid order key. Please try again.', 'dokan' ) );
        }

        // Get the order ID from the key.
        $order_id = wc_get_order_id_by_order_key( $order_key );
        if ( empty( $order_id ) ) {
            throw new DokanException( 'invalid-order-id', __( 'Invalid order id. Please try again.', 'dokan' ) );
        }

        // Get the order.
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            throw new DokanException( 'invalid-order', __( 'Invalid order. Please try again.', 'dokan' ) );
        }

        return $order;
    }

    /**
     * Clear Customer Woocommerce Cart if exists.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public static function maybe_clear_cart() {
        if ( isset( WC()->cart ) ) {
            WC()->cart->empty_cart();
        }
    }
}
