<?php

namespace WeDevs\DokanPro\Modules\Razorpay\WithdrawMethods;

use WP_Error;
use WeDevs\DokanPro\Modules\Razorpay\Helper;
use WeDevs\DokanPro\Modules\Razorpay\Logs\RazorpayLog;
use WeDevs\DokanPro\Modules\Razorpay\Utilities\Processor;
use WeDevs\DokanPro\Modules\Razorpay\Requests\VendorConnectRequest;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class VendorLinkedAccount
 *
 * @package WeDevs\DokanPro\Modules\Razorpay
 *
 * @since 3.5.0
 */
class VendorLinkedAccount {
    /**
     * Instance of Processor Class.
     *
     * @var WeDevs\DokanPro\Modules\Razorpay\Utilities\Processor
     */
    private $processor;

    /**
     * VendorLinkedAccount constructor.
     *
     * @since 3.5.0
     */
    public function __construct() {
        // Instantiate Processor
        $this->processor = Processor::init();

        // Init Hooks
        $this->init_hooks();
    }

    /**
     * Initialize hooks.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function init_hooks() {
        add_action( 'init', [ $this, 'deauthorize_from_wc_setup' ], 10 );
        add_action( 'wp_ajax_dokan_razorpay_connect', [ $this, 'connect' ], 10 );
        add_action( 'template_redirect', [ $this, 'deauthorize' ], 10 );
    }

    /**
     * Handle razorpay connect process for vendor account.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function connect() {
        // Validate and sanitize data
        $data = VendorConnectRequest::handle( $_POST ); // phpcs:ignore WordPress.Security.NonceVerification.Missing

        if ( is_wp_error( $data ) ) {
            $errors = (array) $data->get_error_message();

            // Bail to first error if any error found
            $error_message = count( $errors ) ? $errors[0]['message'] : __( 'Please fill all necessary data.', 'dokan' );

            wp_send_json_error(
                [
                    'type'    => 'error',
                    'message' => $error_message,
                ]
            );
        }

        $user_id = dokan_get_current_user_id();

        // Create a linked account in razorpay for this vendor
        $response = $this->create_account( $data );

        if ( is_wp_error( $response ) ) {
            RazorpayLog::error( $user_id, $response, 'create_linked_account', 'user' );

            wp_send_json_error(
                [
                    'type'    => 'error',
                    'message' => $response->get_error_message(),
                ]
            );
        }

        // Now save datas to user meta.
        $url = $this->authorize( $user_id, $response );
        wp_send_json_success(
            [
                'type'    => 'success',
                'url'     => $url,
                'message' => __( 'Connected to Razorpay successfully.', 'dokan' ),
            ]
        );
    }

    /**
     * Handle razorpay authorization.
     *
     * @since 3.5.0
     *
     * @param int   $user_id
     * @param array $response
     *
     * @return string
     */
    public function authorize( $user_id, $response ) {
        if ( empty( $response['id'] ) ) {
            return;
        }

        // Update user meta data to authorization
        update_user_meta( $user_id, Helper::get_seller_enabled_for_received_payment_key(), 1 );
        update_user_meta( $user_id, Helper::get_seller_payments_receivable_key(), 1 );
        update_user_meta( $user_id, Helper::get_seller_account_id_key(), $response['id'] );
        update_user_meta( $user_id, Helper::get_seller_account_id_key_trashed(), $response['id'] );

        $this->update_seller_profile( true );

        // redirect to this page with success, if everythings ok.
        $url = add_query_arg(
            [
                'status'  => 'success',
                'message' => __( 'Razorpay account connected successfully.', 'dokan' ),
            ],
            Helper::get_payment_setup_navigation_url()
        );

        dokan_pro()->store_settings->save_store_data( $user_id );

        return $url;
    }

    /**
     * Deauthorize vendor's Razorpay account.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function deauthorize() {
        if ( ! isset( $_GET['action'] ) || 'dokan-razorpay-disconnect' !== sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) {
            return;
        }

        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'dokan-razorpay-disconnect' ) ) {
            return;
        }

        $vendor_id = get_current_user_id();

        if ( ! $vendor_id || ! dokan_is_user_seller( $vendor_id ) ) {
            return;
        }

        // delete user metas
        $delete_metas = [
            Helper::get_seller_account_id_key(),
            Helper::get_seller_enabled_for_received_payment_key(),
            Helper::get_seller_payments_receivable_key(),
        ];

        foreach ( $delete_metas as $meta_key ) {
            delete_user_meta( $vendor_id, $meta_key );
        }

        $this->update_seller_profile( false );

        $url = add_query_arg(
            [
                'status'  => 'success',
                'message' => __( 'Razorpay account disconnected successfully.', 'dokan' ),
            ],
            Helper::get_payment_setup_navigation_url()
        );

        dokan_pro()->store_settings->save_store_data( $vendor_id );

        wp_safe_redirect( $url );
        exit;
    }

    /**
     * De-authorize vendor's Razorpay account from Vendor setup wizard section.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function deauthorize_from_wc_setup() {
        if ( ! empty( $_REQUEST['page'] ) && 'dokan-seller-setup' === sanitize_text_field( wp_unslash( $_REQUEST['page'] ) ) && ! empty( $_REQUEST['step'] ) && 'payment' === sanitize_text_field( wp_unslash( $_REQUEST['step'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            // check if it's for disconnect request
            if ( ! empty( $_REQUEST['action'] ) && 'dokan-razorpay-disconnect' === sanitize_text_field( wp_unslash( $_REQUEST['action'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                $this->deauthorize();
            }
        }
    }

    /**
     * Create a linked account in razorpay for route transfer.
     *
     * @see https://razorpay.com/docs/route/api/account-apis-beta/#create-an-account
     *
     * @since 3.5.0
     *
     * @param array $data Data come here must be sanitized before come.
     *
     * @return object|WP_Error
     */
    public function create_account( $data ) {
        $created_account = null;

        // Check if existing user account is already linked with razorpay.
        if ( ! empty( $data['razorpay_existing_user'] ) ) {
            if ( ! empty( $data['razorpay_account_id'] ) ) {
                $created_account = $this->get_account( $data['razorpay_account_id'] );
            }

            if ( ! empty( $created_account ) ) {
                return $created_account;
            }

            return new WP_Error(
                'no_razorpay_account_found',
                __( 'Sorry ! No Razorpay account found for the account ID. Please create a new account or give a valid razorpay account.', 'dokan' )
            );
        }

        // Create a new account.
        $linked_account_data = [
            'name'                => ! empty( $data['razorpay_account_name'] ) ? $data['razorpay_account_name'] : '',
            'email'               => ! empty( $data['razorpay_account_email'] ) ? $data['razorpay_account_email'] : '',
            'tnc_accepted'        => true,
            'account_details' => [
                'business_name'    => ! empty( $data['razorpay_business_name'] ) ? $data['razorpay_business_name'] : '',
                'business_type'    => ! empty( $data['razorpay_business_type'] ) ? $data['razorpay_business_type'] : '',
            ],
            'bank_account'    => [
                'ifsc_code'        => ! empty( $data['razorpay_ifsc_code'] ) ? $data['razorpay_ifsc_code'] : '',
                'beneficiary_name' => ! empty( $data['razorpay_beneficiary_name'] ) ? $data['razorpay_beneficiary_name'] : '',
                'account_type'     => ! empty( $data['razorpay_account_type'] ) ? $data['razorpay_account_type'] : '',
                'account_number'   => ! empty( $data['razorpay_account_number'] ) ? $data['razorpay_account_number'] : '',
            ],
        ];

        $url      = $this->processor->make_razorpay_url( 'v1/beta/accounts/' );
        $response = $this->processor->make_request(
            [
                'url'  => $url,
                'data' => wp_json_encode( $linked_account_data ),
            ]
        );

        if ( ! is_wp_error( $response ) ) {
            return $response; // newly created account.
        }

        // Get the first error message and return.
        $error_message = __( 'Something went wrong in Razorpay connect.', 'dokan' );
        $errors        = $response->get_error_message();

        if ( ! empty( $errors['error'] ) ) {
            $razorpay_error_message = ! empty( $errors['error']['description'] ) ? $errors['error']['description'] : '';

            // If error message says - The requested URL was not found on the server.
            // That means - Admin doesn't activated Route Transfer from Razorpay Dashboard yet.
            if ( ! empty( $razorpay_error_message ) && false !== strpos( $razorpay_error_message, 'The requested URL was not found on the server.' ) ) {
                $error_message = __( 'Route transfer is not being activated in Razorpay Dashboard yet. Please communicate with Admin to proceed.', 'dokan' );
                return new WP_Error( 'dokan_razorpay_create_linked_account_error', $error_message );
            }

            // If razorpay error message is email already exists.
            if ( ! empty( $razorpay_error_message ) && false !== strpos( $razorpay_error_message, 'The email has already been taken.' ) ) {
                return new WP_Error( 'dokan_razorpay_create_linked_account_error', __( 'Razorpay Error: Razorpay account already exists. Please create a new account with another email address. Then connect with the new email address here. Reason: Razorpay doesn\'t support the same email. Or just click I\'ve already an account option if you\'ve already connected once.', 'dokan' ) );
            }

            $error_message = __( 'Razorpay Error: ', 'dokan' ) . ! empty( $razorpay_error_message ) ? $razorpay_error_message : $error_message;
            return new WP_Error( 'dokan_razorpay_create_linked_account_error', $error_message );
        }

        return new WP_Error( 'dokan_razorpay_create_linked_account_error', $response );
    }

    /**
     * Get Single razorpay linked account.
     *
     * @see https://razorpay.com/docs/route/api/account-apis-beta/#fetch-account-by-id
     *
     * @since 3.5.0
     *
     * @param string $account_id
     *
     * @return object|WP_Error
     */
    private function get_account( $account_id ) {
        $url      = $this->processor->make_razorpay_url( "v1/beta/accounts/{$account_id}" );
        $response = $this->processor->make_request(
            [
                'url'    => $url,
                'method' => 'GET',
            ]
        );

        if ( is_wp_error( $response ) ) {
            return null;
        }

        return $response;
    }

    /**
     * Update the seller profile to mimic the authorization status
     *
     * @since 3.5.6
     *
     * @param $authorized
     */
    private function update_seller_profile( $authorized ) {
        $store_id       = get_current_user_id();
        $dokan_settings = get_user_meta( $store_id, 'dokan_profile_settings', true );

        if ( $authorized ) {
            $dokan_settings['payment'][ Helper::get_gateway_id() ] = 1;
        } else {
            $dokan_settings['payment'][ Helper::get_gateway_id() ] = 0;
        }

        update_user_meta( $store_id, 'dokan_profile_settings', $dokan_settings );
    }
}
