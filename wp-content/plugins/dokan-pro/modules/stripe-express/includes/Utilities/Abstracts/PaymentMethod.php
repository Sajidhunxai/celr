<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Settings;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Subscription;
use WeDevs\DokanPro\Modules\StripeExpress\PaymentTokens\Sepa as PaymentTokenSepa;

/**
 * Extendable abstract class for payment methods.
 *
 * Handles general functionality for payment methods.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts
 */
abstract class PaymentMethod {

    /**
     * Stripe key for payment method.
     *
     * @since 3.6.1
     *
     * @var string
     */
    protected $stripe_id;

    /**
     * Display title.
     *
     * @since 3.6.1
     *
     * @var string
     */
    protected $title;

    /**
     * Method label.
     *
     * @since 3.6.1
     *
     * @var string
     */
    protected $label;

    /**
     * Method description.
     *
     * @since 3.6.1
     *
     * @var string
     */
    protected $description;

    /**
     * Identify if the method is reusable.
     *
     * @since 3.6.1
     *
     * @var boolean
     */
    protected $is_reusable;

    /**
     * Array of currencies supported by this method.
     *
     * @since 3.6.1
     *
     * @var array
     */
    protected $supported_currencies;

    /**
     * Identify if the method can refund an order.
     *
     * @since 3.6.1
     *
     * @var boolean
     */
    protected $can_refund = true;

    /**
     * Identify if the method is enabled.
     *
     * @since 3.6.1
     *
     * @var bool
     */
    protected $enabled;

    /**
     * List of supported countries.
     *
     * @since 3.6.1
     *
     * @var array
     */
    protected $supported_countries;

    /**
     * Class constructor.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function __construct() {
        $enabled_payment_methods = Settings::get_enabled_payment_methods();
        $this->enabled = in_array( $this->get_id(), $enabled_payment_methods, true );
    }

    /**
     * Retrieves payment method ID.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public function get_id() {
        return $this->stripe_id;
    }

    /**
     * Retrieves true if the method is enabled.
     *
     * @since 3.6.1
     *
     * @return bool
     */
    public function is_enabled() {
        return $this->enabled;
    }

    /**
     * Retrieves payment method title.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public function get_title() {
        return $this->title;
    }

    /**
     * Retrieves payment method label.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public function get_label() {
        return $this->label;
    }

    /**
     * Retrieves payment method description.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public function get_description() {
        return $this->description;
    }

    /**
     * Checks whether payment method
     * can be used at checkout or not.
     *
     * @since 3.6.1
     *
     * @param int|null $order_id
     *
     * @return boolean
     */
    public function is_enabled_at_checkout( $order_id = null ) {
        // Check currency compatibility.
        $currencies = $this->get_supported_currencies();
        if ( ! empty( $currencies ) && ! in_array( get_woocommerce_currency(), $currencies, true ) ) {
            return false;
        }

        // If cart or order contains subscription, enable payment method if it's reusable.
        if ( Subscription::exists_in_cart() ) {
            return $this->is_reusable();
        }

        if ( ! empty( $order_id ) && Subscription::is_subscription_order( $order_id ) ) {
            return $this->is_reusable();
        }

        return true;
    }

    /**
     * Validates if a payment method is available on a given country.
     *
     * @since 3.6.1
     *
     * @param string $country a two-letter country code
     *
     * @return boolean
     */
    public function is_allowed_on_country( $country ) {
        if ( ! empty( $this->supported_countries ) ) {
            return in_array( $country, $this->supported_countries, true );
        }

        return true;
    }

    /**
     * Returns boolean dependent on whether payment method
     * will support saved payments/subscription payments.
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public function is_reusable() {
        return $this->is_reusable;
    }

    /**
     * Returns string representing payment method type
     * to query to retrieve saved payment methods from Stripe.
     *
     * @since 3.6.1
     *
     * @return string|null
     */
    public function get_retrievable_type() {
        return $this->is_reusable() ? Helper::get_sepa_payment_method_type() : null;
    }

    /**
     * Create new WC payment token and add to user.
     *
     * @since 3.6.1
     *
     * @param int|string            $user_id        WP User ID
     * @param \Stripe\PaymentMethod $payment_method Stripe payment method object
     *
     * @return PaymentTokenSepa
     */
    public function create_payment_token_for_user( $user_id, $payment_method ) {
        $token = new PaymentTokenSepa();
        $token->set_last4( $payment_method->sepa_debit->last4 );
        $token->set_gateway_id( Helper::get_gateway_id() );
        $token->set_token( $payment_method->id );
        $token->set_payment_method_type( $this->get_id() );
        $token->set_user_id( $user_id );
        $token->save();
        return $token;
    }

    /**
     * Returns the currencies this method supports.
     *
     * @since 3.6.1
     *
     * @return array|null
     */
    public function get_supported_currencies() {
        return apply_filters(
            "dokan_stripe_express_{$this->stripe_id}_supported_currencies",
            $this->supported_currencies
        );
    }

    /**
     * Returns whether the payment method requires automatic capture.
     * By default all the payment methods require automatic capture, except for "card".
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public function requires_automatic_capture() {
        return true;
    }

    /**
     * Checks if payment method allows refund via stripe.
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public function can_refund_via_stripe() {
        return $this->can_refund;
    }

    /**
     * Returns the HTML for the subtext messaging.
     *
     * @since 3.6.1
     *
     * @param string $stripe_method_status (optional) Status of this payment method based on the Stripe's account capabilities
     * @return string
     */
    public function get_subtext_messages( $stripe_method_status ) {
        // can be either a `currency` or `activation` messaging, to be displayed
        $messages = [];

        if ( ! empty( $stripe_method_status ) && 'active' !== $stripe_method_status ) {
            $text            = __( 'Pending activation', 'dokan' );
            $tooltip_content = sprintf(
                /* translators: %1: Payment method name */
                esc_attr__( '%1$s won\'t be visible to your customers until you provide the required information. Follow the instructions Stripe has sent to your e-mail address.', 'dokan' ),
                $this->get_label()
            );
            $messages[] = $text . '<span class="tips" data-tip="' . $tooltip_content . '"><span class="woocommerce-help-tip" style="margin-top: 0;"></span></span>';
        }

        $currencies = $this->get_supported_currencies();
        if ( ! empty( $currencies ) && ! in_array( get_woocommerce_currency(), $currencies, true ) ) {
            /* translators: %s: List of comma-separated currencies. */
            $tooltip_content = sprintf( esc_attr__( 'In order to be used at checkout, the payment method requires the store currency to be set to one of: %s', 'dokan' ), implode( ', ', $currencies ) );
            $text            = __( 'Requires currency', 'dokan' );

            $messages[] = $text . '<span class="tips" data-tip="' . $tooltip_content . '"><span class="woocommerce-help-tip" style="margin-top: 0;"></span></span>';
        }

        return count( $messages ) > 0 ? join( '&nbsp;–&nbsp;', $messages ) : '';
    }
}
