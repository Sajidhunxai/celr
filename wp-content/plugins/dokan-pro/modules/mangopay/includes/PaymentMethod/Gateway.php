<?php

namespace WeDevs\DokanPro\Modules\MangoPay\PaymentMethod;

use WC_Order;
use WC_Payment_Gateway;
use WC_Session_Handler;
use Automattic\WooCommerce\Utilities\NumberUtil;
use WeDevs\DokanPro\Modules\MangoPay\Support\Meta;
use WeDevs\DokanPro\Modules\MangoPay\Support\Config;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Card;
use WeDevs\DokanPro\Modules\MangoPay\Processor\PayIn;
use WeDevs\DokanPro\Modules\MangoPay\Processor\PayOut;
use WeDevs\DokanPro\Modules\MangoPay\Support\Settings;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Webhook;
use WeDevs\DokanPro\Modules\MangoPay\Processor\BankAccount;

// Exit if called directly
defined( 'ABSPATH' ) || exit;

/**
 * WooCommerce Payment Gateway class for MangoPay
 *
 * @since 3.5.0
 */
class Gateway extends WC_Payment_Gateway {

    /**
     * Class constructor
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function __construct() {
        // Load necessary fields info
        $this->init_fields();

        // Load the settings
        $this->init_form_fields();
        $this->init_settings();

        // Load necessary hooks
        $this->hooks();
    }

    /**
     * Initiates all required info for payment gateway
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function init_fields() {
        $this->id                 = Helper::get_gateway_id();
        $this->has_fields         = true;
        $this->method_title       = Helper::get_gateway_title();
        $this->method_description = Helper::get_gateway_description();
        $this->order_button_text  = Helper::get_order_button_text();
        $this->title              = $this->get_option( 'title' );
        $this->title              = empty( $this->title ) ? __( 'MangoPay', 'dokan' ) : $this->title;
        $this->sandbox_mode       = $this->get_option( 'sandbox_mode' );
        $this->client_id          = $this->get_option( 'client_id' );
        $this->api_key            = $this->get_option( 'api_key' );
        $this->sandbox_client_id  = $this->get_option( 'sandbox_client_id' );
        $this->sandbox_api_key    = $this->get_option( 'sandbox_api_key' );
        $this->debug              = $this->get_option( 'debug' );
        $this->enabled            = ! $this->is_valid_for_use() ? 'no' : $this->get_option( 'enabled' );
        $this->supports           = array( 'products', 'refunds' );
        $this->supported_locales  = helper::get_supported_locales();
        $this->saved_cards        = $this->get_option( 'saved_cards' );
        $this->icon               = apply_filters( 'woocommerce_dokan_mangopay_icon', DOKAN_MANGOPAY_ASSETS . 'images/mangopay.svg' );
    }

    /**
     * Initiates all necessary hooks
     *
     * @since 3.5.0
     *
     * @uses add_action() To add action hooks
     *
     * @return void
     */
    private function hooks() {
        add_action( "woocommerce_update_options_payment_gateways_{$this->id}", array( $this, 'process_admin_options' ) );
        add_filter( "woocommerce_settings_api_sanitized_fields_{$this->id}", array( $this, 'encrypt_passphrase' ) );
    }

    /**
     * Initiates form fields for admin settings
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function init_form_fields() {
        $this->form_fields = require DOKAN_MANGOPAY_TEMPLATE_PATH . 'admin-gateway-settings.php';
    }

    /**
     * Check whether the payment gateway can be enabled
     *
     * @since 3.5.0
     *
     * @uses get_woocommerce_currency() Retrieves active currency of WooCommerce
     * @uses Helper::get_supported_currencies() To check if WooCommerce active currency exists in this
     *
     * @return boolean
     */
    public function is_valid_for_use() {
        return in_array( get_woocommerce_currency(), Helper::get_supported_currencies(), true );
    }

    /**
     * Checks if the gateway is available for use
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public function is_available() {
        // Check if gateway is enabled
        if ( ! parent::is_available() ) {
            return false;
        }

        // This payment method can't be used for unsupported curencies
        if ( ! $this->is_valid_for_use() ) {
            return false;
        }

        // Mangopay is not available for guest checkout
        if ( ! is_user_logged_in() ) {
            return false;
        }

        if ( empty( WC()->cart ) ) {
            return true;
        }

        /**
         * Filter to manipulate cart validation necessity.
         * 
         * @since 3.7.17
         * 
         * @param bool $needs_validation
         */
        if ( ! apply_filters( 'dokan_mangopay_needs_cart_validation', true ) ) {
            return true;
        }

        /*
         * This payment method can't be used if a Vendor does not have
         * a MangoPay account. So we need to traverse all the cart items
         * to check if any vendor doesn't have a MangoPay account.
         */
        foreach ( WC()->cart->cart_contents as $item ) {
            // Get vendor id from product id
            $vendor_id = dokan_get_vendor_by_product( $item['data']->get_id(), true );
            if ( ! $vendor_id ) {
                return false;
            }

            /*
             * If any vendor is not registered for a MangoPay account,
             * the gateway is not available for checkout.
             */
            if ( empty( Meta::get_mangopay_account_id( $vendor_id ) ) ) {
                return false;
            }

            // Check if the vendor has payout eligibility
            if ( ! PayOut::is_user_eligible( $vendor_id ) ) {
                return false;
            }
        }

        return true;
    }

    /**
     * Output the admin options table.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function admin_options() {
        parent::admin_options();
        wp_enqueue_script( 'dokan-mangopay-admin' );
    }

    /**
     * Processes the admin options.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function process_admin_options() {
        parent::process_admin_options();
        if ( $this->enabled ) {
            //if gateway is enabled, automatically create webhook for this site
            Webhook::register_all();
        } else {
            //if gateway is disabled, delete created webhook for this site
            Webhook::deregister_all();
        }
    }

    /**
     * Checks whether an order is refundable through Mangopay.
     *
     * @since 3.5.0
     *
     * @param object $order
     *
     * @return boolean
     */
    public function can_refund_order( $order ) {
        // Check initial requirements
        if ( ! parent::can_refund_order( $order ) ) {
            return false;
        }

        // Check whether order is processed or completed
        if ( ! in_array( $order->get_status(), array( 'processing', 'completed' ), true ) ) {
            return false;
        }

        // Check if it is the parent order
        if ( $order->get_meta( 'has_sub_order' ) ) {
            return false;
        }

        // Check whether the order amount has been paid out to vendors
        if ( ! empty( Meta::get_payout_id( $order ) ) ) {
            return false;
        }

        if ( ! empty( $order->get_parent_id() ) ) {
            $order = wc_get_order( $order->get_parent_id() );
        }

        // Check whether transaction id exists
        if ( empty( Meta::get_transaction_id( $order ) ) ) {
            return false;
        }

        // bank wire transaction does not support refund
        if ( 'bank_wire' === Meta::get_payment_type( $order ) ) {
            return false;
        }

        return true;
    }

    /**
     * Display MangoPay payment related fields
     *
     * @since 3.5.0
     *
     * @return array
     */
    public function payment_fields() {
        wp_enqueue_script( 'dokan-mangopay-kit' );
        wp_enqueue_script( 'dokan-mangopay-checkout' );
        wp_enqueue_style( 'dokan-mangopay-checkout' );

        $mp_user_id            = '';
        $available_card_types  = Helper::get_available_card_types();
        $selected_credit_cards = Settings::get_selected_credit_cards();
        $user_id               = get_current_user_id();
        $credit_card_activated = false;
        $selected_debit_cards  = Settings::get_selected_direct_pay_methods();
        $selected_card         = ! empty( $_POST['dokan_mangopay_card_type'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan_mangopay_card_type'] ) ) : '';

        if ( $user_id ) {
            $mp_user_id = Meta::get_mangopay_account_id( $user_id );
        }

        // Check if at least one credit card is activated
        foreach ( array_keys( $available_card_types ) as $card_type ) {
            if ( 'BANK_WIRE' !== $card_type && in_array( $card_type, $selected_credit_cards, true ) ) {
                $credit_card_activated = true;
            }
        }

        Helper::get_template(
            'payment-fields',
            array(
                'selected_card'             => $selected_card,
                'selected_debit_cards'      => $selected_debit_cards,
                'selected_credit_cards'     => $selected_credit_cards,
                'credit_card_activated'     => $credit_card_activated,
                'debit_card_enabled'        => ! empty( $selected_debit_cards ),
                'credit_card_enabled'       => ! empty( $selected_credit_cards ),
                'available_direct_payments' => Helper::get_available_direct_payment_types(),
                'available_card_types'      => $available_card_types,
                'mangopay_url'              => Settings::is_test_mode() ? 'https://api.sandbox.mangopay.com' : 'https://api.mangopay.com',
                'months'                    => Helper::get_months_dropdown(),
                'years'                     => Helper::get_years_dropdown(),
                'saved_cards_enabled'       => $this->saved_cards,
                'client_id'                 => Settings::get_client_id(),
                'mangopay_account_id'       => $mp_user_id,
            )
        );
    }

    /**
     * Redirects to MangoPay payment form to process the payment
     *
     * @since 3.5.0
     *
     * @param int|string $order_id
     *
     * @return mixed
     */
    public function process_payment( $order_id ) {
        $user_id = get_current_user_id();

        if ( ! $user_id ) {
            $user_id = ( new WC_Session_Handler() )->generate_customer_id();
        }

        $order = wc_get_order( $order_id );
        if ( ! $order instanceof WC_Order ) {
            return wc_add_notice( __( 'Payment error: No valid order found.', 'dokan' ), 'error' );
        }

        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $return_url   = $this->get_return_url( $order );
        $payment      = false;
        $payment_type = ! empty( $_POST['dokan_mangopay_payment_type'] )
                        ? sanitize_text_field( wp_unslash( $_POST['dokan_mangopay_payment_type'] ) )
                        : '';

        $method_selection = 'card_default';

        switch ( strtolower( $payment_type ) ) {
            case 'card':
                $card_type = ! empty( $_POST['dokan_mangopay_card_type'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan_mangopay_card_type'] ) ) : '';
                break;

            case 'directdebitweb':
                $card_type = ! empty( $_POST['dokan_mangopay_directdebitweb_type'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan_mangopay_directdebitweb_type'] ) ) : '';
                break;

            case 'bank_wire':
                $card_type        = ! empty( $_POST['dokan_mangopay_card_type'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan_mangopay_card_type'] ) ) : '';
                $method_selection = 'bank_wire';
                break;

            case 'registeredcard':
                $method_selection = 'registered_card';
                break;

            default:
                $card_type = 'CB_VISA_MASTERCARD';
                break;
        }

        switch ( $method_selection ) {
            case 'card_default':
                $payment = PayIn::default_card_transaction(
                    $user_id,
                    $order_id,
                    round( $order->get_total() * 100 ),
                    0,
                    $return_url,
                    $order->get_currency(),
                    $card_type
                );
                break;

            case 'registered_card':
                if ( empty( $_POST['registered_card_selected'] ) ) {
                    break;
                }

                $payment = PayIn::card_web_transaction(
                    $user_id,
                    $order_id,
                    sanitize_text_field( wp_unslash( $_POST['registered_card_selected'] ) ),
                    NumberUtil::round( $order->get_total() * 100 ),
                    0,
                    $return_url,
                    $order->get_currency()
                );
                break;

            case 'bank_wire':
                return $this->process_bank_wire( $order_id );
        }
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        if ( false === $payment ) {
            return wc_add_notice( __( 'Payment error: Could not create the MangoPay payment.', 'dokan' ), 'error' );
        }

        if ( is_wp_error( $payment ) ) {
            /* translators: error message */
            return wc_add_notice( sprintf( __( '%s', 'dokan' ), $payment->get_error_message() ), 'error' ); // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings
        }

        // Save metadata for card payment
        Card::save_metadata( $order, $payment );
        $order->save_meta_data();

        return array(
            'result'   => 'success',
            'redirect' => $payment['redirect_url'],
        );
    }

    /**
     * Process Direct Bank Wire payment types
     *
     * @since 3.5.0
     *
     * @param int|string $order_id
     *
     * @return array
     */
    private function process_bank_wire( $order_id ) {
        $order   = wc_get_order( $order_id );
        $user_id = get_current_user_id();

        if ( empty( $user_id ) ) {
            $user_id = ( new WC_Session_Handler() )->generate_customer_id();
        }

        $bankwire_ref = PayIn::bankwire_transaction(
            $user_id,
            $order_id,
            round( $order->get_total() * 100 ),
            0,
            $order->get_currency()
        );

        if ( is_wp_error( $bankwire_ref ) ) {
            /* translators: error message */
            return wc_add_notice( sprintf( __( 'Payment error: %s', 'dokan' ), $bankwire_ref->get_error_message() ), 'error' );
        }

        BankAccount::save_metadata( $order, $bankwire_ref );

        return array(
            'result'   => 'success',
            'redirect' => $this->get_return_url( $order ),
        );
    }

    /**
     * Encrypts api passphrase before saving
     *
     * @since 3.5.0
     *
     * @param array $settings
     *
     * @return array
     */
    public function encrypt_passphrase( $settings ) {
        $config = Config::get_instance();

        if ( ! empty( $settings['sandbox_api_key'] ) ) {
            $settings['sandbox_api_key'] = $config->encrypt( $settings['sandbox_api_key'] );
        }

        if ( ! empty( $settings['api_key'] ) ) {
            $settings['api_key'] = $config->encrypt( $settings['api_key'] );
        }

        return $settings;
    }
}
