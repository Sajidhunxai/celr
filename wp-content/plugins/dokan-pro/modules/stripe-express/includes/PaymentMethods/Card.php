<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\PaymentMethods;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\PaymentMethod;
use WeDevs\DokanPro\Modules\StripeExpress\PaymentTokens\Card as PaymentTokenCC;

/**
 * Payment method handler class for Stripe Credit/Debit Cards.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\PaymentMethods
 */
class Card extends PaymentMethod {

    /**
     * Stores Stripe ID.
     *
     * @since 3.6.1
     *
     * @var string
     */
    const STRIPE_ID = 'card';

    /**
     * Strores label for the method.
     *
     * @since 3.6.1
     *
     * @var string
     */
    const LABEL = 'Credit/Debit Card';

    /**
     * Class constructor.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function __construct() {
        parent::__construct();
        $this->stripe_id   = self::STRIPE_ID;
        $this->title       = apply_filters( 'dokan_stripe_express_payment_method_title', __( 'Pay with credit/debit card', 'dokan' ), self::STRIPE_ID );
        $this->is_reusable = true;
        $this->label       = Helper::get_method_label( self::STRIPE_ID );
        $this->description = __(
            'Let your customers pay with major credit and debit cards without leaving your store.',
            'dokan'
        );
    }

    /**
     * Retrieves payment method title.
     *
     * @since 3.6.1
     *
     * @param array $payment_details Optional payment details from charge object.
     *
     * @return string
     */
    public function get_title( $payment_details = false ) {
        if ( ! $payment_details ) {
            return $this->title;
        }

        $details       = isset( $payment_details[ $this->stripe_id ] ) ? $payment_details[ $this->stripe_id ] : '';
        $funding_types = [
            'credit'  => __( 'credit', 'dokan' ),
            'debit'   => __( 'debit', 'dokan' ),
            'prepaid' => __( 'prepaid', 'dokan' ),
            'unknown' => __( 'unknown', 'dokan' ),
        ];

        if ( empty( $details ) || ! isset( $funding_types[ $details->funding ] ) || ! isset( $details->network ) ) {
            return $this->title;
        }

        return sprintf(
            // Translators: %1$s card brand, %2$s card funding (prepaid, credit, etc.).
            __( '%1$s %2$s card', 'dokan' ),
            ucfirst( $details->network ),
            $funding_types[ $details->funding ]
        );
    }

    /**
     * Returns string representing payment method type
     * to query to retrieve saved payment methods from Stripe.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public function get_retrievable_type() {
        return $this->get_id();
    }

    /**
     * Create and return WC payment token for user.
     *
     * @since 3.6.1
     *
     * @param int|string            $user_id        WP User ID
     * @param \Stripe\PaymentMethod $payment_method Stripe payment method object
     *
     * @return PaymentTokenCC
     */
    public function create_payment_token_for_user( $user_id, $payment_method ) {
        $token = new PaymentTokenCC();
        $token->set_expiry_month( $payment_method->card->exp_month );
        $token->set_expiry_year( $payment_method->card->exp_year );
        $token->set_card_type( strtolower( $payment_method->card->brand ) );
        $token->set_last4( $payment_method->card->last4 );
        $token->set_gateway_id( Helper::get_gateway_id() );
        $token->set_token( $payment_method->id );
        $token->set_user_id( $user_id );
        $token->save();
        return $token;
    }

    /**
     * The Credit Card method allows automatic capture.
     *
     * @since 3.6.1
     *
     * @return bool
     */
    public function requires_automatic_capture() {
        return false;
    }
}
