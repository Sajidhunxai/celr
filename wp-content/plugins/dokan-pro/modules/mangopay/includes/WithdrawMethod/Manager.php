<?php

namespace WeDevs\DokanPro\Modules\MangoPay\WithdrawMethod;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Admin\Announcement;
use WeDevs\DokanPro\Modules\MangoPay\Support\Meta;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;
use WeDevs\DokanPro\Modules\MangoPay\Processor\User;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Wallet;
use WeDevs\DokanPro\Modules\MangoPay\Processor\PayOut;
use WeDevs\DokanPro\Modules\MangoPay\Support\Settings;
use WeDevs\DokanPro\Modules\MangoPay\Processor\BankAccount;

/**
 * Class to handle all hooks for MangoPay as withdraw method
 *
 * @since 3.5.0
 */
class Manager {

    /**
     * Class constructor
     *
     * @since 3.5.0
     */
    public function __construct() {
        $this->hooks();
        $this->init_classes();
    }

    /**
     * Registers all required hooks.
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function hooks() {
        // Register withdraw method
        add_filter( 'dokan_withdraw_methods', array( $this, 'register_mangopay_withdraw_method' ) );
        // Remove gateway from active withdraw method to prevent it from appearing at seller setup page
        add_filter( 'dokan_get_active_withdraw_methods', array( $this, 'remove_gateway_for_setup_page' ) );
        // Mamgopay paymenmt settings form
        add_action( 'dokan_mangopay_vendor_settings_bottom', array( $this, 'render_bank_account_form' ), 10, 2 );
        add_action( 'dokan_mangopay_bank_account_list', array( $this, 'render_bank_account_list' ) );
        add_action( 'dokan_mangopay_vendor_settings_bottom', array( $this, 'render_kyc_form' ), 11 );
        add_action( 'dokan_mangopay_vendor_settings_bottom', array( $this, 'render_wallets_info' ), 12 );
        // Process vendor settings for mangopay
        add_filter( 'dokan_store_profile_settings_args', array( $this, 'process_vendor_mangopay_settings' ), 10, 2 );
        // Send announcement
        add_action( 'dokan_dashboard_before_widgets', array( $this, 'send_announcement_to_non_connected_vendor' ), 10 );
        // Display notice
        add_action( 'dokan_dashboard_content_inside_before', array( $this, 'display_notice_on_vendor_dashboard' ) );

        add_filter( 'dokan_withdraw_method_settings_title', [ $this, 'get_heading' ], 10, 2 );
        add_filter( 'dokan_withdraw_method_icon', [ $this, 'get_icon' ], 10, 2 );
        add_filter( 'dokan_is_seller_connected_to_payment_method', [ $this, 'is_seller_connected' ], 10, 3 );
        add_filter( 'dokan_profile_completion_progress_for_payment_methods', [ $this, 'calculate_profile_progress' ] );
    }

    /**
     * Inistantiates required classes
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function init_classes() {
        if ( wp_doing_ajax() ) {
            new Ajax();
        }
    }

    /**
     * Register Mangopay as withdraw method
     *
     * @since 3.5.0
     *
     * @param array $methods
     *
     * @return array
     */
    public function register_mangopay_withdraw_method( $methods ) {
        if ( Helper::is_gateway_ready() ) {
            $methods[ Helper::get_gateway_id() ] = array(
                'title'    => Helper::get_gateway_title(),
                'callback' => array( $this, 'vendor_gateway_settings' ),
            );
        }

        return $methods;
    }

    /**
     * Removes gateway from active withdraw method
     * to prevent it from appearing at seller setup page.
     *
     * @since 3.5.0
     *
     * @param array $methods
     *
     * @return array
     */
    public function remove_gateway_for_setup_page( $methods ) {
        if ( ! empty( $_GET['page'] ) && 'dokan-seller-setup' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
            unset( $methods[ Helper::get_gateway_id() ] );
        }

        return $methods;
    }

    /**
     * Renders Mangopay form for registration as withdraw method
     *
     * @since 3.5.0
     *
     * @param array $store_settings
     *
     * @return void
     */
    public function vendor_gateway_settings( $store_settings ) {
        $user_id           = get_current_user_id();
        $mangopay_settings = [];
        $mangopay_user_id  = Meta::get_mangopay_account_id( $user_id );

        if ( ! empty( $store_settings['payment'] ) && ! empty( $store_settings['payment']['mangopay'] ) ) {
            $mangopay_settings = $store_settings['payment']['mangopay'];
        }

        wp_enqueue_style( 'dokan-mangopay-vendor' );
        wp_enqueue_script( 'dokan-mangopay-vendor' );

        Helper::get_template(
            'vendor-gateway-settings',
            array(
                'user_id'             => $user_id,
                'signup_fields'        => Helper::get_signup_fields( $user_id ),
                'is_seller_connected' => Helper::is_seller_connected( $user_id ),
                'is_payout_enabled'   => PayOut::is_user_eligible( $user_id ),
                'mp_user'             => User::get( $mangopay_user_id ),
                'payment_settings'    => $mangopay_settings,
            )
        );
    }

    /**
     * Renders bank account form for MangoPay
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     *
     * @return void
     */
    public function render_bank_account_list( $user_id ) {
        $mp_user_id = Meta::get_mangopay_account_id( $user_id );

        if ( empty( $mp_user_id ) ) {
            return;
        }

        Helper::get_template(
            'bank-account-list',
            array(
                'user_id'        => $user_id,
                'bank_accounts'  => BankAccount::all( $mp_user_id ),
                'active_account' => Meta::get_active_bank_account( $user_id ),
            )
        );
    }

    /**
     * Renders bank account form for MangoPay
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param array $payment_settings
     *
     * @return void
     */
    public function render_bank_account_form( $user_id, $payment_settings ) {
        Helper::get_template(
            'bank-account-form',
            array(
                'user_id'          => $user_id,
                'account_types'    => Helper::get_bank_account_types(),
                'account_fields'   => Helper::get_bank_account_types_fields(),
                'common_fields'    => Helper::get_bank_account_common_fields(),
                'account_settings' => ! empty( $payment_settings['bank_account'] ) ? $payment_settings['bank_account'] : array(),
            )
        );
    }

    /**
     * Renders vendor's wallets info for MangoPay.
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     *
     * @return void
     */
    public function render_wallets_info( $user_id ) {
        $args = array(
            'no_wallet' => true,
        );

        $mp_user_id = Meta::get_mangopay_account_id( $user_id );

        if ( empty( $mp_user_id ) ) {
            return Helper::get_template( 'wallets-info', $args );
        }

        $wallets = Wallet::get( $mp_user_id );
        if ( empty( $wallets ) ) {
            $wallet_created = Wallet::create( $mp_user_id );
        }

        if ( ! empty( $wallet_created ) ) {
            $wallets = Wallet::get( $mp_user_id );
        }

        if ( empty( $wallets ) ) {
            return Helper::get_template( 'wallets-info', $args );
        }

        $args['no_wallet'] = false;
        $args['wallets']   = $wallets;

        Helper::get_template( 'wallets-info', $args );
    }

    /**
     * Renders KYC form
     *
     * @since 3.5.0
     *
     * @param int|string $vendor_id
     *
     * @return void
     */
    public function render_kyc_form( $vendor_id ) {
        // If user has the mp user id only then show the kyc form
        if ( ! empty( Meta::get_mangopay_account_id( $vendor_id ) ) ) {
            echo do_shortcode( '[dokan_mangopay_kyc_user_info]' );
            echo do_shortcode( '[dokan_mangopay_kyc_upload_form]' );
        }
    }

    /**
     * Processes mangopay payment settings for vendors
     *
     * @since 3.5.0
     *
     * @param array $settings
     * @param int   $vendor_id
     *
     * @return array
     */
    public function process_vendor_mangopay_settings( $settings, $vendor_id ) {
        if ( empty( $_POST['settings']['mangopay'] ) ) {
            return $settings;
        }

        $mangopay_data = wc_clean( wp_unslash( $_POST['settings']['mangopay'] ) );
        $mp_user_id    = Meta::get_mangopay_account_id( $vendor_id );

        if ( ! empty( $mangopay_data['vendor'] ) && ! empty( $mp_user_id ) ) {
            $mp_user = User::update(
                $vendor_id,
                array_merge(
                    array(
                        'birthday' => $mangopay_data['vendor']['birthday'],
                        'staus'    => $mangopay_data['vendor']['status'],
                    ),
                    $mangopay_data['vendor']
                ),
                false
            );

            if ( ! empty( $mangopay_data['vendor']['terms'] ) ) {
                $mangopay_data['vendor']['terms'] = '1';
            }
        }

        $account_types = Helper::get_bank_account_types_fields();
        $account_type  = array();

        if ( ! empty( $account_types[ $mangopay_data['bank_account']['type'] ] ) ) {
            $account_type = $mangopay_data['bank_account']['type'];
            $fields       = $account_types[ $account_type ];
        }

        $existing_bank_account_id = Meta::get_bank_account_id( $vendor_id );

        // Record redacted bank account data in vendor's usermeta
        foreach ( $fields as $field => $data ) {
            if (
                empty( $existing_bank_account_id ) ||
                ! isset( $mangopay_data['bank_account'][ $account_type ][ $field ] ) ||
                preg_match( '/\*\*/', $mangopay_data['bank_account'][ $account_type ][ $field ] ) ||
                empty( $data['redact'] )
            ) {
                continue;
            }

            list( $obf_start, $obf_end ) = explode( ',', $data['redact'] );
            $strlen = strlen( $mangopay_data['bank_account'][ $account_type ][ $field ] );

            /*
             * if its <=5 characters, lets just redact the whole thing
             * @see: https://github.com/Mangopay/wordpress-plugin/issues/12
             */
            if ( $strlen <= 5 ) {
                $mangopay_data['bank_account'][ $account_type ][ $field ] = str_repeat( '*', $strlen );
            } else {
                $obf_center = $strlen - $obf_start - $obf_end;
                if ( $obf_center < 2 ) {
                    $obf_center = 2;
                }

                $mangopay_data['bank_account'][ $account_type ][ $field ] = substr(
                    $mangopay_data['bank_account'][ $account_type ][ $field ],
                    0,
                    $obf_start
                ) . str_repeat(
                    '*',
                    $obf_center
                ) . substr(
                    $mangopay_data['bank_account'][ $account_type ][ $field ],
                    - $obf_end,
                    $obf_end
                );
            }
        }

        $settings['payment']['mangopay'] = $mangopay_data;
        return $settings;
    }

    /**
     * Sends announcement to vendors if their account is not connected with MnagoPay
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function send_announcement_to_non_connected_vendor() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        $seller_id = dokan_get_current_user_id();

        if ( ! dokan_is_user_seller( $seller_id ) ) {
            return;
        }

        if ( ! Settings::is_send_announcement_to_sellers_enabled() ) {
            return;
        }

        if ( ! dokan_is_withdraw_method_enabled( Helper::get_gateway_id() ) ) {
            return;
        }

        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        if ( ! array_key_exists( Helper::get_gateway_id(), $available_gateways ) ) {
            return;
        }

        if ( ! Helper::is_gateway_ready() ) {
            return;
        }

        if ( Helper::is_seller_connected( $seller_id ) ) {
            return;
        }

        if ( false === get_transient( "dokan_mangopay_notice_intervals_$seller_id" ) ) {
            $announcement = new Announcement();
            // sent announcement message
            $args = array(
                'title'         => $this->notice_to_connect(),
                'sender_type'   => 'selected_seller',
                'sender_ids'    => array( $seller_id ),
                'status'        => 'publish',
            );

            $notice = $announcement->create_announcement( $args );

            if ( is_wp_error( $notice ) ) {
                Helper::log(
                    sprintf(
                        'Error creating announcement for non-connected seller %1$s. Error Message: %2$s',
                        $seller_id,
                        $notice->get_error_message()
                    )
                );
                return;
            }

            // Notice is sent, now store transient
            set_transient( "dokan_mangopay_notice_intervals_$seller_id", 'sent', DAY_IN_SECONDS * Settings::get_announcement_interval() );
        }
    }

    /**
     * Display notice to vendors if their account is not connected with Mangopay
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function display_notice_on_vendor_dashboard() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        // Geet current user id
        $seller_id = dokan_get_current_user_id();

        // Check if current user is vendor
        if ( ! dokan_is_user_seller( $seller_id ) ) {
            return;
        }

        if ( ! dokan_is_withdraw_method_enabled( Helper::get_gateway_id() ) ) {
            return;
        }

        // Check if notice on vendor dashboard is enabled
        if ( ! Settings::is_display_notice_on_vendor_dashboard_enabled() ) {
            return;
        }

        // Check if Mangopay payment gateway is enabled
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        if ( ! array_key_exists( Helper::get_gateway_id(), $available_gateways ) ) {
            return;
        }

        // Check if mangopay is ready
        if ( ! Helper::is_gateway_ready() ) {
            return;
        }

        // Check if vendor is already connected with mangopay
        if ( Helper::is_seller_connected( $seller_id ) ) {
            return;
        }

        echo '<div class="dokan-alert dokan-alert-danger dokan-panel-alert">' . $this->notice_to_connect() . '</div>';
    }

    /**
     * Retrieves notice for non-connected sellers
     *
     * @since 3.5.0
     *
     * @return string
     */
    private function notice_to_connect() {
        return wp_kses(
            sprintf(
                // Translators: %1$s is the link to the settings page, %2$s is anchor end tag.
                __( 'Your account is not connected with MangoPay. Connect your %1$s MangoPay%2$s account to receive automatic payouts.', 'dokan' ),
                sprintf( '<a href="%1$s">', dokan_get_navigation_url( 'settings/payment-manage-' . Helper::get_gateway_id() ) ),
                '</a>'
            ),
            array(
                'a' => array(
                    'href'   => true,
                    'target' => true,
                ),
            )
        );
    }

    /**
     * Get the Payment method icon
     *
     * @since 3.5.6
     *
     * @param string $method_icon
     * @param string $method_key
     *
     * @return string
     */
    public function get_icon( $method_icon, $method_key ) {
        if ( Helper::get_gateway_id() === $method_key ) {
            $method_icon = DOKAN_MANGOPAY_ASSETS . '/images/mangopay-withdraw-method.svg';
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
            $heading = __( 'MangoPay Settings', 'dokan' );
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
            ! Helper::is_seller_connected( dokan_get_current_user_id() ) ||
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
