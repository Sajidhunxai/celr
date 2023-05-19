<?php

namespace WeDevs\DokanPro\Modules\Razorpay\WithdrawMethods;

use WeDevs\DokanPro\Modules\Razorpay\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class RegisterWithdrawMethods
 *
 * @package WeDevs\DokanPro\Modules\Razorpay
 *
 * @since 3.5.0
 */
class RegisterWithdrawMethods {
    /**
     * RegisterWithdrawMethod constructor.
     *
     * @since 3.5.0
     */
    public function __construct() {
        // Register and Save Razorpay withdraw method
        add_filter( 'dokan_withdraw_methods', [ $this, 'register_method' ] );

        // Register styles & scripts
        add_action( 'init', [ $this, 'register_scripts' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

        // Enque script for vendor setup wizard
        add_action( 'dokan_setup_wizard_enqueue_scripts', [ $this, 'enqueue_scripts_in_seller_wizard' ] );

        // Send announcement
        add_action( 'dokan_dashboard_before_widgets', [ $this, 'send_announcement_to_non_connected_vendor' ], 10 );

        // Display notice
        add_action( 'dokan_dashboard_content_inside_before', [ $this, 'display_notice_on_vendor_dashboard' ] );

        add_filter( 'dokan_withdraw_method_icon', [ $this, 'get_method_icon' ], 10, 2 );
        add_filter( 'dokan_withdraw_method_settings_title', [ $this, 'get_heading' ], 10, 2 );
        add_filter( 'dokan_is_seller_connected_to_payment_method', [ $this, 'is_seller_connected' ], 10, 3 );
        add_filter( 'dokan_profile_completion_progress_for_payment_methods', [ $this, 'calculate_profile_progress' ] );
    }

    /**
     * Register Withdraw method.
     *
     * @since 3.5.0
     *
     * @param array $methods
     *
     * @return array
     */
    public function register_method( $methods ) {
        // check if admin provided all the required api keys
        if ( ! Helper::is_ready() ) {
            return $methods;
        }

        $methods['dokan_razorpay'] = [
            'title'    => __( 'Dokan Razorpay', 'dokan' ),
            'callback' => [ $this, 'razorpay_connect_button' ],
        ];

        return $methods;
    }

    /**
     * Register Scripts.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function enqueue_scripts() {
        global $wp;

        // Check if razorpay is enabled
        if ( ! Helper::is_ready() ) {
            return;
        }

        // Register scripts and styles for the payment page only
        if ( isset( $wp->query_vars['settings'] ) && in_array( $wp->query_vars['settings'], [ 'payment', 'payment-manage-' . Helper::get_gateway_id(), 'payment-manage-' . Helper::get_gateway_id() . '-edit' ], true ) ) {
            wp_enqueue_style( 'dokan-razorpay-vendor-register' );
            wp_enqueue_style( 'dokan-magnific-popup' );
            wp_enqueue_script( 'dokan-magnific-popup' );
        }
    }

    /**
     * Register scripts
     *
     * @since 3.7.4
     */
    public function register_scripts() {
        list( $suffix, $version ) = dokan_get_script_suffix_and_version();

        wp_register_style( 'dokan-razorpay-vendor-register', DOKAN_RAZORPAY_ASSETS . 'css/razorpay-vendor-register.css', [], $version );
    }

    /**
     * Enqueue Scripts in Vendor Setup Wizard Payment page.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function enqueue_scripts_in_seller_wizard() {
        // check if razorpay is enabled
        if ( ! Helper::is_ready() ) {
            return;
        }

        // check if page is dokan-seller-setup and step is payment
        if ( isset( $_GET['page'] ) && 'dokan-seller-setup' === $_GET['page'] && isset( $_GET['step'] ) && 'payment' === $_GET['step'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            wp_enqueue_style( 'dokan-style' );
            wp_enqueue_style( 'dokan-magnific-popup' );
            wp_enqueue_style( 'dokan-razorpay-vendor-register' );
        }
    }

    /**
     * This enables dokan vendors to connect their Razorpay account
     * to the site Razorpay gateway account.
     *
     * @since 3.5.0
     *
     * @global WP_User $current_user
     *
     * @param $store_settings
     *
     * @return void
     */
    public function razorpay_connect_button( $store_settings ) {
        global $current_user;

        $email = isset( $store_settings['payment']['dokan_razorpay']['email'] ) ? esc_attr( $store_settings['payment']['dokan_razorpay']['email'] ) : $current_user->user_email;

        $is_seller_enabled = Helper::is_seller_enable_for_receive_payment( get_current_user_id() );
        $merchant_id       = Helper::get_seller_account_id( get_current_user_id() );
        $nonce             = wp_create_nonce( 'dokan-razorpay-connect' );

        $disconnect_razorpay_url = wp_nonce_url(
            add_query_arg(
                [ 'action' => 'dokan-razorpay-disconnect' ],
                Helper::get_payment_setup_navigation_url()
            ),
            'dokan-razorpay-disconnect'
        );

        Helper::get_template(
            'vendor-settings-payment',
            [
                'email'                   => $email,
                'is_seller_enabled'       => $is_seller_enabled,
                'nonce'                   => $nonce,
                'merchant_id'             => $merchant_id,
                'ajax_url'                => admin_url( 'admin-ajax.php' ),
                'disconnect_url'          => $disconnect_razorpay_url,
            ]
        );
    }

    /**
     * Send announcement to vendors if their account is not connected with Razorpay.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function send_announcement_to_non_connected_vendor() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        if ( ! Helper::display_announcement_to_non_connected_sellers() ) {
            return;
        }

        if ( ! dokan_is_withdraw_method_enabled( Helper::get_gateway_id() ) ) {
            return;
        }

        // check razorpay payment gateway is enabled
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        if ( ! array_key_exists( Helper::get_gateway_id(), $available_gateways ) ) {
            return;
        }

        // check if razorpay is ready
        if ( ! Helper::is_ready() ) {
            return;
        }

        // get current user id
        $seller_id = dokan_get_current_user_id();

        // check if current user is vendor
        if ( ! dokan_is_user_seller( $seller_id ) ) {
            return;
        }

        // check if vendor is already connected with razorpay
        if ( Helper::is_seller_enable_for_receive_payment( $seller_id ) ) {
            return;
        }

        if ( false === get_transient( "dokan_razorpay_notice_intervals_$seller_id" ) ) {
            $announcement = new \WeDevs\DokanPro\Admin\Announcement();
            // sent announcement message
            $args = [
                'title'       => $this->connect_messsage(),
                'sender_type' => 'selected_seller',
                'sender_ids'  => [ $seller_id ],
                'status'      => 'publish',
            ];
            $notice = $announcement->create_announcement( $args );

            if ( is_wp_error( $notice ) ) {
                /* translators: 1: Seller ID, 2: Notice error message */
                dokan_log( sprintf( 'Error Creating Razorpay Connect Announcement For Seller %1$s, Error Message: %2$s', $seller_id, $notice->get_error_message() ) );
                return;
            }

            // notice is sent, now store transient
            set_transient( "dokan_razorpay_notice_intervals_$seller_id", 'sent', DAY_IN_SECONDS * Helper::non_connected_sellers_display_notice_intervals() );
        }
    }

    /**
     * Display notice to vendors if their account is not connected with Razorpay.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function display_notice_on_vendor_dashboard() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        // get current user id
        $seller_id = dokan_get_current_user_id();

        // check if current user is vendor
        if ( ! dokan_is_user_seller( $seller_id ) ) {
            return;
        }

        if ( ! Helper::display_notice_on_vendor_dashboard() ) {
            return;
        }

        // check razorpay payment gateway is enabled
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        if ( ! array_key_exists( Helper::get_gateway_id(), $available_gateways ) ) {
            return;
        }

        // check if razorpay is ready
        if ( ! Helper::is_ready() ) {
            return;
        }

        // check if vendor is already connected with Razorpay
        if ( Helper::is_seller_enable_for_receive_payment( $seller_id ) ) {
            return;
        }

        if ( ! dokan_is_withdraw_method_enabled( Helper::get_gateway_id() ) ) {
            return;
        }

        echo '<div class="dokan-alert dokan-alert-danger dokan-panel-alert">' . $this->connect_messsage() . '</div>';
    }

    /**
     * Get connect message if not connected.
     *
     * @since 3.5.0
     *
     * @return string
     */
    private function connect_messsage() {
        return wp_kses(
            sprintf(
            // Translators: %1$s is the link to the settings page, %2$s is anchor end tag.
                __( 'Your account is not connected with Razorpay. Connect your %1$s Razorpay%2$s account to receive automatic payouts.', 'dokan' ),
                sprintf( '<a href="%1$s">', dokan_get_navigation_url( 'settings/payment-manage-' . Helper::get_gateway_id() ) ),
                '</a>'
            ),
            [
                'a' => [
                    'href'   => true,
                    'target' => true,
                ],
            ]
        );
    }

    /**
     * Get payment method icon
     *
     * @since 3.5.6
     *
     * @param string $method_icon
     * @param string $method_key
     *
     * @return string
     */
    public function get_method_icon( $method_icon, $method_key ) {
        if ( Helper::get_gateway_id() === $method_key ) {
            $method_icon = DOKAN_RAZORPAY_ASSETS . '/images/razorpay-withdraw-method.svg';
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
        if ( false !== stripos( $slug, Helper::get_gateway_id() ) ) {
            $heading = __( 'Razorpay Settings', 'dokan' );
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
        if ( Helper::get_gateway_id() === $payment_method_id && Helper::is_seller_enable_for_receive_payment( $seller_id ) ) {
            $connected = true;
        }

        return $connected;
    }

    /**
     * Calculate Dokan profile completeness value
     *
     * @since 3.7.1
     *
     * @param array $progress_track_value
     *
     * @return array
     */
    public function calculate_profile_progress( $progress_track_value ) {
        if (
            ! Helper::is_seller_enable_for_receive_payment( dokan_get_current_user_id() ) ||
            ! isset( $progress_track_value['progress'] ) ||
            ! isset( $progress_track_value['current_payment_val'] ) ||
            $progress_track_value['current_payment_val'] <= 0
        ) {
            return $progress_track_value;
        }

        $progress_track_value['progress'] += $progress_track_value['current_payment_val'];
        $progress_track_value[ Helper::get_gateway_id() ] = $progress_track_value['current_payment_val'];
        $progress_track_value['current_payment_val'] = 0;

        return $progress_track_value;
    }
}
