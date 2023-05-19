<?php

namespace WeDevs\DokanPro\Modules\Stripe;

use WC_Payment_Tokens;
use WC_Payment_Token_CC;

defined( 'ABSPATH' ) || exit;

/**
 * Handles and process WC payment tokens API.
 * Seen in checkout page and my account->add payment method page.
 *
 * @since 3.0.3
 */
class PaymentTokens {

    /**
     * Gateway id holder
     *
     * @var string
     */
    public $gateway_id = '';

    public function __construct() {
        $this->gateway_id = Helper::get_gateway_id();

        add_filter( 'woocommerce_get_customer_payment_tokens', [ $this, 'get_customer_payment_tokens' ], 10, 3 );
        add_action( 'woocommerce_payment_token_deleted', [ $this, 'payment_token_deleted' ], 10, 2 );
        add_action( 'woocommerce_payment_token_set_default', [ $this, 'payment_token_set_default' ] );
    }

    /**
     * Checks if customer has saved payment methods.
     *
     * @since 3.0.3
     *
     * @param int $customer_id
     *
     * @return bool
     */
    public static function customer_has_saved_methods( $customer_id ) {
        $gateway = 'dokan-stripe-connect';

        if ( empty( $customer_id ) ) {
            return false;
        }

        return (bool) WC_Payment_Tokens::get_customer_tokens( $customer_id, $gateway );
    }

    /**
     * Gets saved tokens from API if they don't already exist in WooCommerce.
     *
     * @since 3.0.3
     *
     * @param array $tokens
     * @param int $customer_id
     * @param int $gateway_id
     *
     * @return array
     */
    public function get_customer_payment_tokens( $tokens, $customer_id, $gateway_id ) {
        if ( is_user_logged_in() && class_exists( 'WC_Payment_Token_CC' ) ) {
            $stored_tokens = [];
            $stripe_stored_tokens = [];

            if ( $this->gateway_id !== $gateway_id ) {
                return $tokens;
            }

            $stripe_customer = new Customer( $customer_id );
            $stripe_sources  = $stripe_customer->get_sources();

            // get all stripe sources
            foreach ( $stripe_sources as $source ) {
                $stripe_stored_tokens[] = $source->id;
            }

            // delete from local token reference if source doesn't exists on stripe end.
            foreach ( $tokens as $token ) {
                if ( ! in_array( $token->get_token(), $stripe_stored_tokens, true ) ) {
                    $token->delete( true );
                    continue;
                }

                $stored_tokens[] = $token->get_token();
            }

            foreach ( $stripe_sources as $source ) {
                if ( isset( $source->type ) && 'card' === $source->type ) {
                    if ( ! in_array( $source->id, $stored_tokens, true ) ) {
                        $token = new WC_Payment_Token_CC();
                        $token->set_token( $source->id );
                        $token->set_gateway_id( $this->gateway_id );

                        if ( 'source' === $source->object && 'card' === $source->type ) {
                            $token->set_card_type( strtolower( $source->card->brand ) );
                            $token->set_last4( $source->card->last4 );
                            $token->set_expiry_month( $source->card->exp_month );
                            $token->set_expiry_year( $source->card->exp_year );
                        }

                        $token->set_user_id( $customer_id );
                        $token->save();
                        $tokens[ $token->get_id() ] = $token;
                    }
                } else {
                    if ( ! in_array( $source->id, $stored_tokens, true ) && 'card' === $source->object ) {
                        $token = new WC_Payment_Token_CC();
                        $token->set_token( $source->id );
                        $token->set_gateway_id( $this->gateway_id );
                        $token->set_card_type( strtolower( $source->brand ) );
                        $token->set_last4( $source->last4 );
                        $token->set_expiry_month( $source->exp_month );
                        $token->set_expiry_year( $source->exp_year );
                        $token->set_user_id( $customer_id );
                        $token->save();
                        $tokens[ $token->get_id() ] = $token;
                    }
                }
            }
        }

        return $tokens;
    }

    /**
     * Delete token from Stripe.
     *
     * @since 3.0.3
     *
     * @param string $token_id
     * @param string $token
     *
     * @return void
     */
    public function payment_token_deleted( $token_id, $token ) {
        if ( $this->gateway_id === $token->get_gateway_id() ) {
            $stripe_customer = new Customer( get_current_user_id() );
            $stripe_customer->delete_source( $token->get_token() );
        }
    }

    /**
     * Set as default in Stripe.
     *
     * @since 3.0.3
     *
     * @param string $token_id
     *
     * @return void
     */
    public function payment_token_set_default( $token_id ) {
        $token = WC_Payment_Tokens::get( $token_id );

        if ( $this->gateway_id === $token->get_gateway_id() ) {
            $stripe_customer = new Customer( get_current_user_id() );
            $stripe_customer->set_default_source( $token->get_token() );
        }
    }
}
