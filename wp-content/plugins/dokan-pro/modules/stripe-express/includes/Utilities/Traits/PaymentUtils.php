<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Utilities\Traits;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use Exception;
use WeDevs\Dokan\Exceptions\DokanException;
use Stripe\PaymentMethod as StripePaymentMethod;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Api\PaymentMethod;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Payment;

/**
 * Trait to manage payment utilities.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Utilities\Traits
 */
trait PaymentUtils {

    /**
     * Validates that the order meets the minimum order amount
     * set by Stripe.
     *
     * @since 3.6.1
     *
     * @param int|string $order_id
     *
     * @return void
     * @throws DokanException
     */
    public function validate_minimum_order_amount( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            throw new DokanException(
                'dokan-not-valid-order',
                /* translators: order id */
                sprintf( __( 'The order %s is not valid', 'dokan' ), $order_id )
            );
        }

        $minimum_amount = $this->get_minimum_amount();
        if ( $order->get_total() < $minimum_amount ) {
            throw new DokanException(
                'dokan-minimum-order-validation-failed',
                sprintf(
                    /* translators: order id */
                    __( 'Sorry, the minimum allowed order total is %1$s to use this payment method.', 'dokan' ),
                    wc_price( $minimum_amount )
                )
            );
        }
    }

    /**
     * Retrieves minimum amount for an order based on the currency.
     *
     * @since 3.6.1
     *
     * @param string $currency
     *
     * @return float
     */
    public function get_minimum_amount( $currency = '' ) {
        if ( empty( $currency ) ) {
            $currency = get_woocommerce_currency();
        }

        switch ( $currency ) {
            case 'GBP':
                $minimum_amount = 0.30;
                break;
            case 'AUD':
            case 'BRL':
            case 'CAD':
            case 'CHF':
            case 'EUR':
            case 'INR':
            case 'NZD':
            case 'SGD':
            case 'USD':
                $minimum_amount = 0.50;
                break;
            case 'BGN':
                $minimum_amount = 1.00;
                break;
            case 'AED':
            case 'MYR':
            case 'PLN':
            case 'RON':
                $minimum_amount = 2.00;
                break;
            case 'DKK':
                $minimum_amount = 2.50;
                break;
            case 'NOK':
            case 'SEK':
                $minimum_amount = 3.00;
                break;
            case 'HKD':
                $minimum_amount = 4.00;
                break;
            case 'MXN':
                $minimum_amount = 10.00;
                break;
            case 'CZK':
                $minimum_amount = 15.00;
                break;
            case 'JPY':
                $minimum_amount = 50.00;
                break;
            case 'HUF':
                $minimum_amount = 175.00;
                break;
            default:
                $minimum_amount = 0.50;
        }

        return apply_filters( 'dokan_stripe_express_minimum_order_amount', $minimum_amount, $currency );
    }

    /**
     * Checks if request is the original to prevent double processing
     * on WC side. The original-request header and request-id header
     * needs to be the same to mean its the original request.
     *
     * @since 3.6.1
     *
     * @param array $headers
     *
     * @return boolean
     */
    public function is_original_request( $headers ) {
        if ( $headers['original-request'] === $headers['request-id'] ) {
            return true;
        }

        return false;
    }

    /**
     * Adds a token to current user from a setup intent id.
     *
     * @since 3.7.8
     *
     * @param string $setup_intent_id ID of the setup intent.
     * @param int    $user_id         User to add token to.
     *
     * @return \WC_Payment_Token|false
     */
    public function create_token_from_setup_intent( $setup_intent_id, $user_id ) {
        try {
            $setup_intent = Payment::get_intent(
                null,
                $setup_intent_id,
                [
                    'expand' => [
                        'payment_method',
                        'latest_attempt',
                    ],
                ],
                true
            );

            if ( ! empty( $setup_intent->last_payment_error ) ) {
                throw new Exception( __( 'We\'re not able to add this payment method. Please try again later.', 'dokan' ) );
            }

            list( $payment_method_type, $payment_method_details ) = Payment::get_method_data_from_intent( $setup_intent );

            $payment_method = $this->payment_methods[ $payment_method_type ];

            /*
             * To mask the iDeal payment method as SEPA Direct Debit
             * as iDeal cannot be used directly as reusable payment method.
             */
            if ( $payment_method->get_id() !== $payment_method->get_retrievable_type() ) {
                $payment_method_id     = $payment_method_details[ $payment_method_type ]->generated_sepa_debit;
                $stripe_payment_method = PaymentMethod::get( $payment_method_id );
            } else {
                $stripe_payment_method = $setup_intent->payment_method;
            }

            if ( ! $stripe_payment_method instanceof StripePaymentMethod ) {
                throw new Exception( __( 'We\'re not able to add this payment method. Please try again later.', 'dokan' ) );
            }

            return $payment_method->create_payment_token_for_user( $user_id, $stripe_payment_method );
        } catch ( Exception $e ) {
            wc_add_notice( $e->getMessage(), 'error', [ 'icon' => 'error' ] );
            Helper::log( 'Error when adding payment method: ' . $e->getMessage() );
            return false;
        }
    }

    /**
     * Gets payment method settings to pass to client scripts.
     *
     * @since 3.7.8
     *
     * @return array<string,bool>
     */
    public function get_enabled_payment_method_config() {
        $settings                 = [];
        $enabled_payment_methods  = Helper::get_enabled_payment_methods_at_checkout();
        $payment_method_instances = Helper::get_available_method_instances();

        foreach ( $enabled_payment_methods as $payment_method ) {
            $method_instance    = $payment_method_instances[ $payment_method ];
            $is_method_reusable = $method_instance->is_reusable();
            $settings[ $payment_method ] = [
                'isReusable' => $is_method_reusable,
            ];

            /*
             * In some cases the payment method will be indicated by
             * the retrievable type instead of the payment method id.
             * For example, the iDeal payment method is indicated by
             * the type 'sepa_debit' instead of 'ideal in most cases.
             */
            if ( $method_instance->get_retrievable_type() !== $method_instance->get_id() ) {
                $settings[ $method_instance->get_retrievable_type() ] = [
                    'isReusable' => $is_method_reusable,
                ];
            }
        }

        return $settings;
    }

    /**
     * Retries the payment process once an error occured.
     *
     * @since 3.7.8
     *
     * @param \Stripe\PaymentIntent $intent            The Payment Intent response from the Stripe API.
     * @param WC_Order              $order             An order that is being paid for.
     * @param bool                  $retry             A flag that indicates whether another retry should be attempted.
     * @param bool                  $force_save_source Force save the payment source.
     * @param mixed                 $previous_error    Any error message from previous request.
     * @param bool                  $use_order_source  Whether to use the source, which should already be attached to the order.
     *
     * @return array|void
     * @throws DokanException If the payment is not accepted.
     */
    public function retry_after_error( $intent, $order, $retry, $force_save_source = false, $previous_error = false, $use_order_source = false ) {
        if ( ! $retry ) {
            $localized_message = __( 'Sorry, we are unable to process your payment at this time. Please retry later.', 'dokan' );
            $order->add_order_note( $localized_message );
            throw new DokanException( print_r( $intent, true ), $localized_message ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.
        }

        // Don't do anymore retries after this.
        if ( 5 <= $this->retry_interval ) {
            return $this->process_payment_with_saved_payment_method( $order->get_id(), false, $intent->id );
        }

        sleep( $this->retry_interval );
        $this->retry_interval++;

        return $this->process_payment_with_saved_payment_method( $order->get_id(), true, $intent->id );
    }

    /**
     * Renders gateway description if available.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function maybe_show_description() {
        $description = $this->get_description();

        if ( $this->testmode ) {
            $description = ( ! empty( $description ) ? "$description<br>" : '' ) . $this->testmode_description();
        }

        echo apply_filters( 'dokan_stripe_express_description', wpautop( wp_kses_post( trim( $description ) ) ), $this->id );
    }
}
