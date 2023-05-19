<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Checkout;

use WeDevs\DokanPro\Modules\MangoPay\Processor\Card;
use WeDevs\DokanPro\Modules\MangoPay\Processor\User;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;

/**
 * Ajax handler class for checkout.
 *
 * @since 3.5.0
 */
class Ajax {

    /**
     * Class constructor.
     *
     * @since 3.5.0
     */
    public function __construct() {
        // Ajax actions for card registration and processing
        add_action( 'wp_ajax_dokan_mangopay_get_registered_cards', array( $this, 'get_registered_cards' ) );
        add_action( 'wp_ajax_dokan_mangopay_deactivate_card', array( $this, 'deactivate_card' ) );
        add_action( 'wp_ajax_dokan_mangopay_register_card', array( $this, 'register_card' ) );
        add_action( 'wp_ajax_dokan_mangopay_update_card', array( $this, 'update_card' ) );
    }

    /**
     * Create a registration record for a card.
     *
     * @since 3.5.0
     *
     * @return mixed
     */
    public function register_card() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dokan_mangopay_checkout_nonce' ) ) { // phpcs:ignore
            wp_send_json_error( __( 'Nonce verification failed!', 'dokan' ) );
        }

        $user_id        = ! empty( $_POST['user_id'] ) ? intval( wp_unslash( $_POST['user_id'] ) ) : get_current_user_id();
        $order_currency = ! empty( $_POST['order_currency'] ) ? sanitize_text_field( wp_unslash( $_POST['order_currency'] ) ) : get_woocommerce_currency();
        $card_type      = ! empty( $_POST['card_type'] ) ? sanitize_text_field( wp_unslash( $_POST['card_type'] ) ) : 'CB_VISA_MASTERCARD';
        $nickname       = ! empty( $_POST['preauth_ccnickname'] ) ? sanitize_text_field( wp_unslash( $_POST['preauth_ccnickname'] ) ) : '';
        $registration   = Card::register( $user_id, $order_currency, $card_type, $nickname );

        if ( ! $registration['success'] ) {
            wp_send_json_error( $registration['message'] );
        }

        wp_send_json_success(
            array(
                'CardRegistrationId'  => $registration['response']->Id,
                'PreregistrationData' => $registration['response']->PreregistrationData,
                'AccessKey'           => $registration['response']->AccessKey,
                'CardRegistrationURL' => $registration['response']->CardRegistrationURL,
                'UserId'              => $registration['response']->UserId
            )
        );
    }

    /**
     * Updates card registration after saving card data.
     *
     * @since 3.5.0
     *
     * @return mixed
     */
    public function update_card() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dokan_mangopay_checkout_nonce' ) ) { // phpcs:ignore
            wp_send_json_error( __( 'Nonce verification failed!', 'dokan' ) );
        }

        $card_id  = ! empty( $_POST['card_id'] ) ? sanitize_text_field( wp_unslash( $_POST['card_id'] ) ) : '';
        $reg_data = ! empty( $_POST['reg_data'] ) ? sanitize_text_field( wp_unslash( $_POST['reg_data'] ) ) : '';
        $card     = Card::update( $card_id, $reg_data );

        if ( ! $card['success'] ) {
            wp_send_json_error( $card['message'] );
        }

        wp_send_json_success( $card['response'] );
    }

    /**
     * Deactivates a registered card.
     *
     * @since 3.5.0
     *
     * @return mixed
     */
    public function deactivate_card() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dokan_mangopay_checkout_nonce' ) ) { // phpcs:ignore
            wp_send_json_error( __( 'Nonce verification failed!', 'dokan' ) );
        }

        $card_id = ! empty( $_POST['card_id'] ) ? sanitize_text_field( wp_unslash( $_POST['card_id'] ) ) : '';
        $result  = Card::deactivate( $card_id );

        if ( ! $result['success'] ) {
            wp_send_json_error( __( 'Failed to deactivate card', 'dokan' ) );
        }

        wp_send_json_success( __( 'Card deactivated', 'dokan' ) );
    }

    /**
     * Retrieves registered cards.
     *
     * @since 3.5.0
     *
     * @return mixed
     */
    public function get_registered_cards(){
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dokan_mangopay_checkout_nonce' ) ) { // phpcs:ignore
            wp_send_json_error( __( 'Nonce verification failed!', 'dokan' ) );
        }

        $user_id = ! empty( $_POST['user_id'] ) ? intval( wp_unslash( $_POST['user_id'] ) ) : get_current_user_id();

        if ( empty( $user_id ) ) {
            wp_send_json_error( '' );
        }

        $registered_cards = User::get_cards( $user_id );

        ob_start();
        Helper::get_template(
            'registered-cards',
            [ 'registered_cards' => $registered_cards, ]
        );
        wp_send_json_success( ob_get_clean() );
    }
}