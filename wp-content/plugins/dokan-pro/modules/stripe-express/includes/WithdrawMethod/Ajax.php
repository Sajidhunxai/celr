<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\WithdrawMethod;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Processors\User;

/**
 * Class to handle AJAX actions
 * for Stripe Express withdraw method.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\WithdrawMethod
 */
class Ajax {

    /**
     * Class constructor
     *
     * @since 3.6.1
     */
    public function __construct() {
        if ( wp_doing_ajax() ) {
            $this->hooks();
        }
    }

    /**
     * Registers all required hooks.
     *
     * @since 3.6.1
     *
     * @return void
     */
    private function hooks() {
        add_action( 'wp_ajax_dokan_stripe_express_vendor_signup', [ $this, 'sign_up' ] );
        add_action( 'wp_ajax_dokan_stripe_express_vendor_disconnect', [ $this, 'disconnect_vendor' ] );
        add_action( 'wp_ajax_dokan_stripe_express_get_login_url', [ $this, 'get_login_url' ] );
        add_action( 'wp_ajax_dokan_stripe_express_cancel_onboarding', [ $this, 'cancel_onboarding' ] );
    }

    /**
     * Signs a vendor up.
     *
     * @since 3.6.1
     *
     * @return mixed
     */
    public function sign_up() {
        if (
            ! isset( $_POST['_wpnonce'] ) ||
            ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dokan_stripe_express_vendor_payment_settings' )
        ) {
            wp_send_json_error( __( 'Nonce verification failed!', 'dokan' ) );
        }

        if ( ! current_user_can( 'dokan_manage_withdraw' ) ) {
            wp_send_json_error( __( 'Permission denied!', 'dokan' ) );
        }

        $args = [
            'country'  => ! empty( $_POST['country'] ) ? sanitize_text_field( wp_unslash( $_POST['country'] ) ) : '',
            'url_args' => ! empty( $_POST['url_args'] ) ? sanitize_text_field( wp_unslash( $_POST['url_args'] ) ) : '',
        ];

        $response = User::onboard( get_current_user_id(), $args );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( $response->get_error_message() );
        }

        /**
         * Dokan hook to do additional action after this payment gateway is disconnected by seller
         *
         * @since 3.7.1
         */
        do_action( 'dokan_stripe_express_seller_activated', get_current_user_id() );

        wp_send_json_success( $response );
    }

    /**
     * Generates login url for stripe express dashboard.
     *
     * @since 3.6.1
     *
     * @return mixed
     */
    public function get_login_url() {
        if (
            ! isset( $_POST['_wpnonce'] ) ||
            ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dokan_stripe_express_vendor_payment_settings' )
        ) {
            wp_send_json_error( __( 'Nonce verification failed!', 'dokan' ) );
        }

        if ( ! current_user_can( 'dokan_manage_withdraw' ) ) {
            wp_send_json_error( __( 'Permission denied!', 'dokan' ) );
        }

        $response = User::set( get_current_user_id() )->get_stripe_login_url();

        if ( ! $response ) {
            wp_send_json_error( __( 'Something went wrong! Please try again later.', 'dokan' ) );
        }

        wp_send_json_success( [ 'url' => $response ] );
    }

    /**
     * Disconnects a vendor from stripe express.
     *
     * @since 3.6.1
     *
     * @return mixed
     */
    public function disconnect_vendor() {
        if (
            ! isset( $_POST['_wpnonce'] ) || // phpcs:ignore
            ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dokan_stripe_express_vendor_payment_settings' )
        ) {
            wp_send_json_error( __( 'Nonce verification failed!', 'dokan' ) );
        }

        if ( ! current_user_can( 'dokan_manage_withdraw' ) ) {
            wp_send_json_error( __( 'Permission denied!', 'dokan' ) );
        }

        $response = User::disconnect( get_current_user_id() );

        if ( ! $response ) {
            wp_send_json_error( __( 'Something went wrong! Please try again later.', 'dokan' ) );
        }

        wp_send_json_success( __( 'Account disconnected successfully', 'dokan' ) );
    }

    /**
     * Cancel ongoing vendor onboarding process.
     *
     * @since 3.7.21
     *
     * @return mixed
     */
    public function cancel_onboarding() {
        if (
            ! isset( $_POST['_wpnonce'] ) || // phpcs:ignore
            ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dokan_stripe_express_vendor_payment_settings' )
        ) {
            wp_send_json_error( __( 'Nonce verification failed!', 'dokan' ) );
        }

        if ( ! current_user_can( 'dokan_manage_withdraw' ) ) {
            wp_send_json_error( __( 'Permission denied!', 'dokan' ) );
        }

        $response = User::disconnect( get_current_user_id(), true );
        if ( ! $response ) {
            wp_send_json_error( __( 'Something went wrong! Please try again later.', 'dokan' ) );
        }

        wp_send_json_success( __( 'Onboarding process cancelled.', 'dokan' ) );
    }
}
