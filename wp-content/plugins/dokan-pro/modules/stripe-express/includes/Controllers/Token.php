<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Controllers;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Customer;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Token as TokenProcessor;

/**
 * Handles and process WC payment tokens API.
 * Seen in checkout page and my account->add payment method page.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Controllers
 */
class Token {

    /**
     * Class constructor.
     *
     * @since 3.6.1
     */
    public function __construct() {
        add_action( 'init', [ $this, 'hooks' ] );
    }

    /**
     * Registers all necessary hooks.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function hooks() {
        add_filter( 'woocommerce_get_customer_payment_tokens', [ $this, 'get_customer_payment_tokens' ], 10, 3 );
        add_filter( 'woocommerce_payment_methods_list_item', [ $this, 'get_account_saved_payment_methods_list_item_sepa' ], 10, 2 );
        add_filter( 'woocommerce_get_credit_card_type_label', [ $this, 'normalize_sepa_label' ] );
        add_action( 'woocommerce_payment_token_deleted', [ $this, 'payment_token_deleted' ], 10, 2 );
        add_action( 'woocommerce_payment_token_set_default', [ $this, 'payment_token_set_default' ] );
    }

    /**
     * Normalizes the SEPA IBAN label on My Account page.
     *
     * @since 3.6.1
     *
     * @param string $label
     *
     * @return string
     */
    public function normalize_sepa_label( $label ) {
        if ( 'sepa iban' === strtolower( $label ) ) {
            return 'SEPA IBAN';
        }

        return $label;
    }

    /**
     * Gets saved tokens from Stripe, if they don't already exist in WooCommerce.
     *
     * @since 3.6.1
     *
     * @param \WC_Payment_Token[]  $tokens     Array of tokens
     * @param string               $user_id    WC User ID
     * @param string               $gateway_id WC Gateway ID
     *
     * @return array
     */
    public function get_customer_payment_tokens( $tokens, $user_id, $gateway_id ) {
        /*
         * If Stripe express gateway is not available,
         * then we won't show any token related to it.
         */
        if ( ! empty( $tokens ) && ! Helper::is_gateway_ready() ) {
            $tokens = array_filter(
                $tokens, function( $token ) {
                    return Helper::get_gateway_id() !== $token->get_gateway_id();
                }
            );
        }

        /*
         * If the user is not logged in or the gateway is not Stripe Express,
         * we don't further intervene in the process.
         */
        if ( ( ! empty( $gateway_id ) && Helper::get_gateway_id() !== $gateway_id ) || ! is_user_logged_in() ) {
            return $tokens;
        }

        $reusable_payment_methods = Helper::get_enabled_reusable_payment_methods();
        $customer                 = Customer::set( $user_id );
        $remaining_tokens         = [];

        foreach ( $tokens as $token ) {
            if ( Helper::get_gateway_id() !== $token->get_gateway_id() ) {
                continue;
            }

            $payment_method_type = $this->get_payment_method_type_from_token( $token );
            if ( ! in_array( $payment_method_type, $reusable_payment_methods, true ) ) {
                // Remove saved token from list, if payment method is not enabled.
                unset( $tokens[ $token->get_id() ] );
            } else {
                /*
                 * Store relevant existing tokens here.
                 * We will use this list to check
                 * whether these methods still exist on Stripe's end.
                 */
                $remaining_tokens[ $token->get_token() ] = $token;
            }
        }

        /*
         * Maps the reusable payment methods to their respective retrievable types.
         * The retrievable type will indeed be used to create a new token in the WooCommerce end.
         */
        $retrievable_payment_method_types = [];
        $payment_methods                  = Helper::get_available_method_instances();
        foreach ( $reusable_payment_methods as $payment_method_type ) {
            $payment_method = $payment_methods[ $payment_method_type ];
            if ( ! in_array( $payment_method->get_retrievable_type(), $retrievable_payment_method_types, true ) ) {
                $retrievable_payment_method_types[] = $payment_method->get_retrievable_type();
            }
        }

        foreach ( $retrievable_payment_method_types as $payment_method_type ) {
            $customers_payment_methods = $customer->get_payment_methods( $payment_method_type );

            // Prevent unnecessary recursion, WC_Payment_Token::save() ends up calling 'get_customer_payment_tokens' in some cases.
            remove_action( 'woocommerce_get_customer_payment_tokens', [ $this, 'get_customer_payment_tokens' ], 10, 3 );
            foreach ( $customers_payment_methods as $method ) {
                if ( ! isset( $remaining_tokens[ $method->id ] ) ) {
                    $payment_method_type = $this->get_original_payment_method_type( $method );
                    if ( ! in_array( $payment_method_type, $reusable_payment_methods, true ) ) {
                        continue;
                    }

                    // Create new token for new payment method and add to list.
                    $payment_method             = $payment_methods[ $payment_method_type ];
                    $token                      = $payment_method->create_payment_token_for_user( $user_id, $method );
                    $tokens[ $token->get_id() ] = $token;
                } else {
                    /*
                     * Count that existing token for payment method is still present on Stripe.
                     * Remaining IDs in $remaining_tokens no longer exist with Stripe and will be eliminated.
                     */
                    unset( $remaining_tokens[ $method->id ] );
                }
            }
            add_action( 'woocommerce_get_customer_payment_tokens', [ $this, 'get_customer_payment_tokens' ], 10, 3 );
        }

        /*
         * Eliminate remaining payment methods no longer known by Stripe.
         * Prevent unnecessary recursion, when deleting tokens.
         */
        remove_action( 'woocommerce_payment_token_deleted', [ $this, 'payment_token_deleted' ], 10, 2 );
        foreach ( $remaining_tokens as $token ) {
            unset( $tokens[ $token->get_id() ] );
            $token->delete();
        }
        add_action( 'woocommerce_payment_token_deleted', [ $this, 'payment_token_deleted' ], 10, 2 );

        return $tokens;
    }

    /**
     * Returns original type of payment method from Stripe payment method response,
     * after checking whether payment method is SEPA method generated from another type.
     *
     * @since 3.6.1
     *
     * @param \Stripe\PaymentMethod $payment_method Stripe payment method object.
     *
     * @return string Payment method type/ID
     */
    private function get_original_payment_method_type( $payment_method ) {
        if ( Helper::get_sepa_payment_method_type() === $payment_method->type ) {
            if ( ! is_null( $payment_method->sepa_debit->generated_from->charge ) ) {
                return $payment_method->sepa_debit->generated_from->charge->payment_method_details->type;
            }
            if ( ! is_null( $payment_method->sepa_debit->generated_from->setup_attempt ) ) {
                return $payment_method->sepa_debit->generated_from->setup_attempt->payment_method_details->type;
            }
        }
        return $payment_method->type;
    }

    /**
     * Returns original Stripe payment method type from payment token.
     *
     * @since 3.6.1
     *
     * @param \WeDevs\DokanPro\Modules\StripeExpress\PaymentTokens\Sepa|\WeDevs\DokanPro\Modules\StripeExpress\PaymentTokens\Card $payment_token WC Payment Token (CC or SEPA)
     *
     * @return string
     */
    private function get_payment_method_type_from_token( $payment_token ) {
        $type = $payment_token->get_type();
        if ( 'CC' === $type ) {
            return 'card';
        } elseif ( 'sepa' === $type ) {
            return $payment_token->get_payment_method_type();
        } else {
            return $type;
        }
    }

    /**
     * Controls the output for SEPA on the my account page.
     *
     * @since 3.6.1
     *
     * @param  array                $item          Individual list item from woocommerce_saved_payment_methods_list
     * @param  \WC_Payment_Token_CC $payment_token The payment token associated with this method entry
     *
     * @return array                           Filtered item
     */
    public function get_account_saved_payment_methods_list_item_sepa( $item, $payment_token ) {
        if ( 'sepa' === strtolower( $payment_token->get_type() ) ) {
            $item['method']['last4'] = $payment_token->get_last4();
            $item['method']['brand'] = esc_html__( 'SEPA IBAN', 'dokan' );
        }

        return $item;
    }

    /**
     * Delete token from Stripe.
     *
     * @since 3.6.1
     *
     * @param string            $token_id
     * @param \WC_Payment_Token $token
     *
     * @return void
     */
    public function payment_token_deleted( $token_id, $token ) {
        if ( Helper::get_gateway_id() === $token->get_gateway_id() && Helper::is_gateway_ready() ) {
            $customer = Customer::set( get_current_user_id() );
            $customer->detach_payment_method( $token->get_token() );
        }
    }

    /**
     * Set as default in Stripe.
     *
     * @since 3.6.1
     *
     * @param string $token_id
     *
     * @return void
     */
    public function payment_token_set_default( $token_id ) {
        if ( ! Helper::is_gateway_ready() ) {
            return;
        }

        $token = TokenProcessor::get( $token_id );
        if ( Helper::get_gateway_id() === $token->get_gateway_id() ) {
            $customer = Customer::set( get_current_user_id() );
            $customer->set_default_payment_method( $token->get_token() );
        }
    }
}
