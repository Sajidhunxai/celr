<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Processors;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;

/**
 * Class for processing WooCommerce payment tokens.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Processors
 */
class Token {

    /**
     * Retrieves WooCommerce token by ID.
     *
     * @since 3.7.8
     *
     * @param string|int $token_id
     *
     * @return \WC_Payment_Token|NULL
     */
    public static function get( $token_id ) {
        add_filter( 'woocommerce_payment_token_class', [ __CLASS__, 'get_payment_token_class' ], 10, 2 );
        $token = \WC_Payment_Tokens::get( $token_id );
        remove_filter( 'woocommerce_payment_token_class', [ __CLASS__, 'get_payment_token_class' ], 10, 2 );
        return $token;
    }

    /**
     * Modifies the payment token class while adding/changing
     * payment method to add support for custom token types.
     *
     * @since 3.7.8
     *
     * @param string $token_class The default token class generated
     * @param string $token_type  Type of the token being processed
     *
     * @return string
     */
    public static function get_payment_token_class( $token_class, $token_type ) {
        switch ( $token_type ) {
            case 'CC':
                $token_class = '\WeDevs\DokanPro\Modules\StripeExpress\PaymentTokens\Card';
                break;

            case 'sepa':
            case 'ideal':
                $token_class = '\WeDevs\DokanPro\Modules\StripeExpress\PaymentTokens\Sepa';
                break;
        }

        return apply_filters( 'dokan_stripe_express_payment_token_class', $token_class, $token_type );
    }

    /**
     * Extract the payment token from the provided request.
     *
     * @since 3.7.8
     *
     * @return \WC_Payment_Token|NULL
     */
    public static function parse_from_request() {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $payment_method    = isset( $_POST['payment_method'] ) ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) ) : Helper::get_gateway_id();
        $token_request_key = "wc-$payment_method-payment-token";

        if ( ! isset( $_POST[ $token_request_key ] ) || 'new' === $_POST[ $token_request_key ] ) {
            return null;
        }

        $token = self::get( sanitize_text_field( wp_unslash( $_POST[ $token_request_key ] ) ) );
        // phpcs:enable WordPress.Security.NonceVerification.Missing

        // If the token doesn't belong to this gateway or the current user it's invalid.
        if ( ! $token || $payment_method !== $token->get_gateway_id() || $token->get_user_id() !== get_current_user_id() ) {
            return null;
        }

        return $token;
    }
}
