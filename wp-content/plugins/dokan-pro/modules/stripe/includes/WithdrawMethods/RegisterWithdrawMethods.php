<?php

namespace WeDevs\DokanPro\Modules\Stripe\WithdrawMethods;

use Exception;
use WeDevs\DokanPro\Modules\Stripe\Auth;
use WeDevs\DokanPro\Modules\Stripe\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * @todo These methods should be refactored to put in different related classes
 */
class RegisterWithdrawMethods {

    /**
     * Constructor method
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function __construct() {
        add_filter( 'dokan_admin_notices', [ $this, 'admin_notices' ] );

        if ( ! Helper::is_ready() ) {
            return;
        }

        Helper::bootstrap_stripe();
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
        add_filter( 'dokan_withdraw_methods', [ $this, 'register_methods' ] );
        add_filter( 'dokan_get_processing_fee', [ $this, 'get_order_processing_fee' ], 10, 2 );
        add_filter( 'dokan_get_processing_gateway_fee', [ $this, 'get_processing_gateway_fee' ], 10, 3 );
        add_filter( 'dokan_orders_vendor_net_amount', [ $this, 'dokan_orders_vendor_net_amount' ], 10, 5 );
        add_filter( 'dokan_withdraw_method_settings_title', [ $this, 'get_heading' ], 10, 2 );
        add_action( 'template_redirect', [ $this, 'authorize_vendor' ] );
        add_action( 'template_redirect', [ $this, 'deauthorize_vendor' ] );
        add_filter( 'dokan_withdraw_method_icon', [ $this, 'get_icon' ], 10, 2 );
        add_filter( 'dokan_is_seller_connected_to_payment_method', [ $this, 'is_seller_connected' ], 10, 3 );
    }

    /**
     * Show admin notices
     *
     * @since 3.0.4
     *
     * @param array $notices
     *
     * @return array
     */
    public function admin_notices( $notices ) {
        if ( ! Helper::is_enabled() || ! Helper::get_secret_key() || ! Helper::get_client_id() ) {
            $mode = Helper::is_test_mode() ? __( 'Test', 'dokan' ) : __( 'Live', 'dokan' );
            $notice = sprintf(
            // translators: 1) test or live mode, 2) Stripe, 3) test or live mode
                __( 'Please insert %1$s %2$s credential to use %3$s Mode', 'dokan' ), $mode, '<strong>Stripe</strong>', $mode
            );
            $notices[] = [
                'type'        => 'alert',
                'title'       => __( 'Dokan Stripe Connect module is almost ready!', 'dokan' ),
                'description' => $notice,
                'priority'    => 10,
                'actions'     => [
                    [
                        'type'    => 'primary',
                        'text'    => __( 'Go to Settings', 'dokan' ),
                        'actions' => esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=dokan-stripe-connect' ) ),
                        'action'  => add_query_arg(
                            array(
                                'page'    => 'wc-settings',
                                'tab'     => 'checkout',
                                'section' => 'dokan-stripe-connect',
                            ), admin_url( 'admin.php' )
                        ),
                    ],
                ],
            ];
        }

        if ( ! is_ssl() && ! Helper::is_test_mode() ) {
            $notice = sprintf(
                /* translators: 1: Dokan Stripe Connect 2: SSL Mode */
                __( '%1$s requires %2$s', 'dokan' ),
                '<strong>Dokan Stripe Connect</strong>',
                '<strong>SSL</strong>'
            );
            $notices[] = [
                'type'        => 'alert',
                'description' => $notice,
                'priority'    => 10,
            ];
        }

        return $notices;
    }

    /**
     * Register methods
     *
     * @since 3.0.3
     *
     * @param array $methods
     *
     * @return array
     */
    public function register_methods( $methods ) {
        $methods['dokan-stripe-connect'] = [
            'title'    => __( 'Stripe', 'dokan' ),
            'callback' => [ $this, 'stripe_authorize_button' ],
        ];

        return $methods;
    }

    /**
     * This enables dokan vendors to connect their stripe account to the site stripe gateway account
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function stripe_authorize_button() {
        $vendor_id           = get_current_user_id();
        $key                 = get_user_meta( $vendor_id, '_stripe_connect_access_key', true );
        $connected_vendor_id = get_user_meta( $vendor_id, 'dokan_connected_vendor_id', true );
        $auth_url            = '#';
        $disconnect_url      = '#';

        Helper::bootstrap_stripe();

        if ( empty( $key ) && empty( $connected_vendor_id ) ) {
            $auth_url = Auth::get_vendor_authorize_url();
        } else {
            $disconnect_url = Auth::get_vendor_deauthorize_url();
        }

        Helper::get_template(
            'vendor-settings-payment',
            [
                'vendor_id'           => $vendor_id,
                'key'                 => $key,
                'connected_vendor_id' => $connected_vendor_id,
                'auth_url'            => $auth_url,
                'disconnect_url'      => $disconnect_url,
            ]
        );
    }

    /**
     * Authorize vendor
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function authorize_vendor() {
        if ( empty( $_GET['state'] ) || empty( $_GET['code'] ) ) {
            return;
        }

        $state = sanitize_text_field( wp_unslash( $_GET['state'] ) );
        $code  = sanitize_text_field( wp_unslash( $_GET['code'] ) );

        if ( false === strpos( $state, 'dokan-stripe-connect' ) ) {
            return;
        }

        $store_id = get_current_user_id();

        $nonce = str_replace( 'dokan-stripe-connect:', '', $state );

        if ( ! wp_verify_nonce( $nonce, 'dokan-stripe-vendor-authorize' ) ) {
            return;
        }

        try {
            Helper::bootstrap_stripe();
            $response = Auth::get_vendor_token( $code );
        } catch ( Exception $e ) {
            dokan_log(
                sprintf(
                    "[Stripe Connect] Unable to authorize vendor. \nException Message: %s\nError Trace:\n%s",
                    $e->getMessage(),
                    $e->getTraceAsString()
                ),
                'error'
            );

            wp_die(
                __( 'Unable to authorize your store. Please contact the site admin.', 'dokan' ),
                __( 'Authorization Error', 'dokan' )
            );
        }

        update_user_meta( $store_id, 'dokan_connected_vendor_id', $response->stripe_user_id );
        update_user_meta( $store_id, '_stripe_connect_access_key', $response->access_token );

        // Update stripe data and store progress bar
        $dokan_settings                      = get_user_meta( $store_id, 'dokan_profile_settings', true );
        $dokan_settings['payment']['stripe'] = 1;

        update_user_meta( $store_id, 'dokan_profile_settings', $dokan_settings );

        $dokan_settings['profile_completion'] = dokan_pro()->store_settings->calculate_profile_completeness_value( $dokan_settings );

        update_user_meta( $store_id, 'dokan_profile_settings', $dokan_settings );

        // delete announcement transient
        delete_transient( 'dokan_check_stripe_access_key_valid_' . $store_id );
        delete_transient( 'non_connected_sellers_notice_intervals_' . $store_id );
        wp_safe_redirect( dokan_get_navigation_url( 'settings/payment-manage-dokan-stripe-connect' ) );
        exit;
    }

    /**
     * Deauthorize vendor
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function deauthorize_vendor() {
        if ( ! isset( $_GET['action'] ) || 'dokan-disconnect-stripe' !== $_GET['action'] ) {
            return;
        }

        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'dokan-stripe-vendor-deauthorize' ) ) {
            return;
        }

        $vendor_id = get_current_user_id();

        if ( ! $vendor_id || ! dokan_is_user_seller( $vendor_id ) ) {
            return;
        }

        try {
            Helper::bootstrap_stripe();
            Auth::deauthorize(
                [
                    'stripe_user_id' => get_user_meta( $vendor_id, 'dokan_connected_vendor_id', true ),
                ]
            );
        } catch ( Exception $e ) {
            dokan_log(
                sprintf(
                    "[Stripe Connect] Unable to deauthorize vendor. \nException Message: %s\nError Trace:\n%s",
                    $e->getMessage(),
                    $e->getTraceAsString()
                ),
                'error'
            );
        }

        delete_user_meta( $vendor_id, '_stripe_connect_access_key' );
        delete_user_meta( $vendor_id, 'dokan_connected_vendor_id' );

        // Update store stripe data and progress bar
        $dokan_settings                      = get_user_meta( $vendor_id, 'dokan_profile_settings', true );
        $dokan_settings['payment']['stripe'] = 0;

        update_user_meta( $vendor_id, 'dokan_profile_settings', $dokan_settings );

        $dokan_settings['profile_completion'] = dokan_pro()->store_settings->calculate_profile_completeness_value( $dokan_settings );

        update_user_meta( $vendor_id, 'dokan_profile_settings', $dokan_settings );

        // delete announcement transient
        delete_transient( "dokan_check_stripe_access_key_valid_$vendor_id" );
        delete_transient( 'non_connected_sellers_notice_intervals_' . $vendor_id );

        wp_safe_redirect( dokan_get_navigation_url( 'settings/payment-manage-dokan-stripe-connect' ) );
        exit;
    }

    /**
     * Order processing fee for Stripe
     *
     * @since 3.1.0
     *
     * @param float     $processing_fee
     * @param \WC_Order $order
     *
     * @return float
     */
    public function get_order_processing_fee( $processing_fee, $order ) {
        if ( 'dokan-stripe-connect' === $order->get_payment_method() ) {
            $stripe_processing_fee = $order->get_meta( 'dokan_gateway_fee' );

            if ( ! $stripe_processing_fee ) {
                // During processing vendor payment we save stripe fee in parent order
                $stripe_processing_fee = $order->get_meta( 'dokan_gateway_stripe_fee' );
            }

            if ( $stripe_processing_fee ) {
                $processing_fee = $stripe_processing_fee;
            }
        }

        return $processing_fee;
    }

    /**
     * Calculate gateway fee for a suborder
     *
     * @since 3.1.0
     *
     * @param float     $gateway_fee
     * @param \WC_Order $suborder
     * @param \WC_Order $order
     *
     * @return float|int
     */
    public function get_processing_gateway_fee( $gateway_fee, $suborder, $order ) {
        if ( 'dokan-stripe-connect' === $order->get_payment_method() ) {
            $order_processing_fee = dokan()->commission->get_processing_fee( $order );
            $gateway_fee          = Helper::calculate_processing_fee_for_suborder( $order_processing_fee, $suborder, $order );
        }

        return $gateway_fee;
    }

    /**
     * Vendor net earning for a order
     *
     * @since 3.1.0
     *
     * @param float     $net_amount
     * @param float     $vendor_earning
     * @param float     $gateway_fee
     * @param \WC_Order $suborder
     * @param \WC_Order $order
     *
     * @return float
     */
    public function dokan_orders_vendor_net_amount( $net_amount, $vendor_earning, $gateway_fee, $suborder, $order ) {
        if (
            'dokan-stripe-connect' === $order->get_payment_method()
            && 'seller' !== $suborder->get_meta( 'dokan_gateway_fee_paid_by', true )
        ) {
            return $vendor_earning;
        }

        return $net_amount;
    }

    /**
     * Get the Withdrawal method icon
     *
     * @since 3.5.6
     *
     * @param string $method_icon
     * @param string $method_key
     *
     * @return string
     */
    public function get_icon( $method_icon, $method_key ) {
        if ( in_array( $method_key, [ 'stripe', 'dokan-stripe-connect' ], true ) ) {
            $method_icon = DOKAN_STRIPE_ASSETS . 'images/stripe-withdraw-method.svg';
        }

        return $method_icon;
    }

    /**
     * Get the heading for this payment's settings page
     *
     * @since 3.5.6
     *
     * @param string $heading
     * @param string $slug
     *
     * @return string
     */
    public function get_heading( $heading, $slug ) {
        if ( false !== strpos( $slug, 'dokan-stripe-connect' ) ) {
            $heading = __( 'Stripe Settings', 'dokan' );
        }

        return $heading;
    }

    /**
     * Get if a seller is connected to this payment method
     *
     * @since 3.6.1
     *
     * @param bool $connected
     * @param string $payment_method_id
     * @param int $seller_id
     *
     * @return bool
     */
    public function is_seller_connected( $connected, $payment_method_id, $seller_id ) {
        if ( Helper::get_gateway_id() === $payment_method_id && Helper::is_seller_connected( $seller_id ) ) {
            $connected = true;
        }

        return $connected;
    }
}
