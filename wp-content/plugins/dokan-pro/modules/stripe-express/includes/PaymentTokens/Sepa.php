<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\PaymentTokens;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WC_Payment_Token;

/**
 * Dokan Stripe SEPA Direct Debit Payment Token.
 *
 * Representation of a payment token for SEPA.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\PaymentTokens
 */
class Sepa extends WC_Payment_Token {

    /**
     * Stores payment type.
     *
     * @since 3.6.1
     *
     * @var string
     */
    protected $type = 'sepa';

    /**
     * Stores SEPA payment token data.
     *
     * @since 3.7.8
     *
     * @var array
     */
    protected $extra_data = [
        'last4'               => '',
        'payment_method_type' => 'sepa_debit',
    ];

    /**
     * Stores hook prefix.
     *
     * @since 3.6.1
     *
     * @var string
     */
    protected $hook_prefix = 'dokan_stripe_express_payment_token_sepa_get_';

    /**
     * Get type to display to user.
     *
     * @since 3.6.1
     *
     * @param string $deprecated Deprecated since WooCommerce 3.0
     *
     * @return string
     */
    public function get_display_name( $deprecated = '' ) {
        $display = sprintf(
            /* translators: last 4 digits of IBAN account */
            __( 'SEPA IBAN ending in %s', 'dokan' ),
            $this->get_last4()
        );

        return $display;
    }

    /**
     * Retrieves hook prefix
     *
     * @since 3.6.1
     *
     * @return string
     */
    protected function get_hook_prefix() {
        return $this->hook_prefix;
    }

    /**
     * Validates SEPA payment tokens.
     *
     * These fields are required by all SEPA payment tokens:
     * last4  - string Last 4 digits of the iBAN
     *
     * @since 3.6.1
     *
     * @return boolean True if the passed data is valid
     */
    public function validate() {
        if ( ! parent::validate() ) {
            return false;
        }

        if ( ! $this->get_last4( 'edit' ) ) {
            return false;
        }

        return true;
    }

    /**
     * Retrieves the last four digits.
     *
     * @since  3.6.1
     *
     * @param string $context What the value is for. Valid values are view and edit.
     *
     * @return string Last 4 digits
     */
    public function get_last4( $context = 'view' ) {
        return $this->get_prop( 'last4', $context );
    }

    /**
     * Sets the last four digits.
     *
     * @since 3.6.1
     *
     * @param string
     *
     * @return void
     */
    public function set_last4( $last4 ) {
        $this->set_prop( 'last4', $last4 );
    }

    /**
     * Sets Stripe payment method type.
     *
     * @since 3.6.1
     *
     * @param string $type Payment method type.
     *
     * @return void
     */
    public function set_payment_method_type( $type ) {
        $this->set_prop( 'payment_method_type', $type );
    }

    /**
     * Retrieves Stripe payment method type.
     *
     * @since 3.6.1
     *
     * @param string $context What the value is for. Valid values are view and edit.
     *
     * @return string $payment_method_type
     */
    public function get_payment_method_type( $context = 'view' ) {
        return $this->get_prop( 'payment_method_type', $context );
    }
}
