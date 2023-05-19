<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Checkout;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\MangoPay\Processor\User;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;

/**
 * CLass for managing checkout options
 *
 * @since 3.5.0
 */
class Manager {

    /**
     * Class constructor.
     *
     * @since 3.5.0
     */
    public function __construct() {
        if ( ! Helper::is_gateway_ready() ) {
            return;
        }

        $this->init_classes();
        $this->hooks();
    }

    /**
     * Instantiates necessary classes.
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function init_classes() {
        new Ajax();
    }

    /**
     * Registers required hooks.
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function hooks() {
        // Hooks to process extra fields on checkout page
        add_action( 'woocommerce_checkout_update_user_meta', array( $this, 'save_extra_register_fields' ), 10, 2 );
        // When billing address is changed by customer
        add_action( 'woocommerce_customer_save_address', array( $this, 'synchronize_account_data' ) );
    }

    /**
     * Save the extra register fields.
     * We need this to enforce mandatory/required fields
     * that we need for creating a mangopay user
     *
     * @param int   $customer_id ID of the current customer
     * @param array $data        Posted data from the checkout page
     *
     * @return void
     */
    public function save_extra_register_fields( $customer_id, $data ) {
        // Return if Mangopay is not the payment method
        if ( Helper::get_gateway_id() !== $data['payment_method'] ) {
            return;
        }

        if ( ! empty( $data['billing_first_name'] ) ) {
            update_user_meta( $customer_id, 'first_name', $data['billing_first_name'] );
        }

        if ( ! empty( $data['billing_last_name'] ) ) {
            update_user_meta( $customer_id, 'last_name', $data['billing_last_name'] );
        }

        User::create( $customer_id );
    }

    /**
     * Fires up when WC shop settings have been saved.
     *
     * @since 3.5.0
     *
     * @param int $wp_user_id
     *
     * @return void
     */
    public function synchronize_account_data( $wp_user_id ) {
        User::sync_account_data( $wp_user_id );
    }
}
