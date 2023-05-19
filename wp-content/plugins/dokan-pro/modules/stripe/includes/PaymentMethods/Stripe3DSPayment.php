<?php

namespace WeDevs\DokanPro\Modules\Stripe\PaymentMethods;

use Exception;
use Stripe\PaymentIntent;
use WeDevs\DokanPro\Modules\Stripe\Helper;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\Stripe\StripeConnect;
use WeDevs\DokanPro\Modules\Stripe\Interfaces\Payable;

class Stripe3DSPayment extends StripeConnect implements Payable {

    /**
     * Hold the order order object
     *
     * @var \WC_Order
     */
    protected $order = null;

    /**
     * @var bool|null
     */
    protected $force_save_source = null;

    /**
     * @var bool
     */
    protected $use_order_source = false;

    /**
     * Constructor method
     *
     * @since 3.0.3
     *
     * @param \WC_Order
     * @param $force_save_source bool|null
     * @param $use_order_source bool
     *
     * @return void
     */
    public function __construct( $order, $force_save_source = null, $use_order_source = false ) {
        $this->order                = $order;
        $this->force_save_source    = $force_save_source;
        $this->use_order_source     = $use_order_source;

        parent::__construct();
    }

    /**
     * Pay for the order
     *
     * @since 3.0.3
     *
     * @return array
     */
    public function pay() {
        try {
            $order              = $this->order;
            $stripe_customer_id = null;
            $this->force_save_source = ( null === $this->force_save_source ) ? false : $this->force_save_source;

            // check if this is a subscription product
            if ( Helper::has_subscription( $order->get_id() ) ) {
                $this->force_save_source = true;
                if ( Helper::is_subs_change_payment() ) {
                    return $this->change_subs_payment_method( $order->get_id() );
                }
            }

            // If it's a recurring subscription order.
            if ( Helper::is_subscription_order( $order ) ) {
                $order_items = $order->get_items();
                $product_pack_item = reset( $order_items );
                $product_pack = wc_get_product( $product_pack_item->get_product_id() );
                $dokan_subscription = dokan()->subscription->get( $product_pack->get_id() );

                if ( $dokan_subscription->is_recurring() ) {
                    update_user_meta( get_current_user_id(), 'product_order_id', $order->get_id() );
                    $order->add_order_note( sprintf( __( 'Order payment is completed via stripe with 3d secure', 'dokan' ) ) );
                    $order->payment_complete();

                    return [
                        'result' => 'success',
                        'redirect' => $this->get_return_url( $order ),
                    ];
                }
            }

            $this->validate_minimum_order_amount( $order );

            // Check whether there is an existing intent.
            $intent = $this->get_intent_from_order( $order );

            if ( ! empty( $intent->customer ) ) {
                $stripe_customer_id = $intent->customer;
            }

            // For some payments the source should already be present in the order.
            if ( $this->use_order_source ) {
                $prepared_source = $this->prepare_order_source( $order );
            } else {
                $prepared_source = $this->prepare_source( get_current_user_id(), $this->force_save_source, $stripe_customer_id );
            }

            $this->validate_source( $prepared_source );

            if ( $this->force_save_source ) {
                $this->save_source_to_order( $order, $prepared_source );
            }

            if ( $intent ) {
                try {
                    $intent = $this->update_existing_intent( $intent, $order, $prepared_source );
                } catch ( DokanException $e ) {
                    dokan_log( __( 'Error: updating payment intent error: ', 'dokan' ) . $e->get_message() );
                    $intent = $this->create_intent( $order, $prepared_source );
                }
            } else {
                $intent = $this->create_intent( $order, $prepared_source );
            }

            return $this->process_intent_status( $intent, $prepared_source, $order );
        } catch ( DokanException $e ) {
            $order->add_order_note( sprintf( __( 'Stripe Payment Error: %s', 'dokan' ), $e->getMessage() ) );
            wc_add_notice( __( 'Error: ', 'dokan' ) . $e->getMessage(), 'error' );
            dokan_log( 'Stripe 3ds Payment Error: Order ID: ' . $order->get_id() . ', Error Message: ' . $e->getMessage() . ', Response Data: ' . $e->get_error_code() );
            do_action( 'dokan_gateway_stripe_process_payment_error', $e, $order );

            /* translators: error message */
            $order->update_status( 'failed' );

            return array(
                'result'   => 'fail',
                'redirect' => '',
            );
        } catch ( Exception $e ) {
            $order->add_order_note( sprintf( __( 'Stripe Payment Error: %s', 'dokan' ), $e->getMessage() ) );
            wc_add_notice( __( 'Error: ', 'dokan' ) . $e->getMessage(), 'error' );

            do_action( 'dokan_gateway_stripe_process_payment_error', $e, $order );

            /* translators: error message */
            $order->update_status( 'failed' );

            return array(
                'result'   => 'fail',
                'redirect' => '',
            );
        }
    }

    /**
     * Process intent status
     *
     * @since 3.0.3
     *
     * @param \PaymentIntent $intent
     * @param string $prepared_source
     * @param \WC_Order $order
     *
     * @return array
     */
    public function process_intent_status( $intent, $prepared_source, $order ) {
        if ( 'requires_confirmation' === $intent->status ) {
            $intent = $this->confirm_intent( $intent, $prepared_source );
        }

        if ( 'requires_action' === $intent->status ) {
            $this->unlock_order_payment( $order );

            if ( is_wc_endpoint_url( 'order-pay' ) ) {
                $redirect_url = add_query_arg( 'dokan-stripe-confirmation', 1, $order->get_checkout_payment_url( false ) );

                return [
                    'result'   => 'success',
                    'redirect' => $redirect_url,
                ];
            } else {
                /**
                 * This URL contains only a hash, which will be sent to `checkout.js` where it will be set like this:
                 * `window.location = result.redirect`
                 * Once this redirect is sent to JS, the `onHashChange` function will execute `handleCardPayment`.
                 */
                return [
                    'result'                => 'success',
                    'redirect'              => $this->get_return_url( $order ),
                    'payment_intent_secret' => $intent->client_secret,
                ];
            }
        }

        if ( 'succeeded' === $intent->status ) {
            $order->payment_complete();
            do_action( 'dokan_stripe_payment_completed', $order, $intent );

            return [
                'result'   => 'success',
                'redirect' => $this->get_return_url( $order ),
            ];
        }

        return [
            'result'   => 'fail',
            'redirect' => '',
        ];
    }

    /**
     * Create a new PaymentIntent
     *
     * @param \WC_Order $order
     * @param object $prepared_source The source that is used for the payment
     *
     * @param null $amount
     * @return object
     * @throws DokanException
     * @since 3.0.3
     */
    public function create_intent( $order, $prepared_source, $amount = null ) {
        // get payment info
        $payment_info = Helper::generate_payment_info( $order );

        $request = [
            'source'               => $prepared_source->source,
            'amount'               => $amount ? Helper::get_stripe_amount( $amount ) : Helper::get_stripe_amount( $order->get_total() ),
            'currency'             => strtolower( $order->get_currency() ),
            'description'          => $payment_info['description'],
            'metadata'             => $payment_info['metadata'],
            'setup_future_usage'   => $prepared_source->setup_future_usage,
            'capture_method'       => 'automatic',
            'payment_method_types' => [
                'card',
            ],
        ];

        if ( ! empty( $payment_info['transfer_group'] ) ) {
            $request['transfer_group'] = $payment_info['transfer_group'];
        }

        if ( $prepared_source->customer ) {
            $request['customer'] = $prepared_source->customer;
        }

        if ( isset( $prepared_source->setup_future_usage ) ) {
            $request['setup_future_usage'] = $prepared_source->setup_future_usage;
        }

        try {
            $intent = PaymentIntent::create( $request );
        } catch ( Exception $e ) {
            throw new DokanException( 'unable_to_create_payment_intent', $e->getMessage() );
        }

        $this->save_intent_to_order( $order, $intent );

        return $intent;
    }

    /**
     * Saves intent to order.
     *
     * @since 3.0.3
     *
     * @param \WC_Order $order For to which the source applies.
     * @param \Stripe\Paymentintent $intent Payment intent information.
     *
     * @return void
     */
    public function save_intent_to_order( $order, $intent ) {
        $order->update_meta_data( 'dokan_stripe_intent_id', $intent->id );
        $order->update_meta_data( '_stripe_customer_id', $intent->customer );
        $order->update_meta_data( '_stripe_source_id', $intent->source );
        $order->update_meta_data( '_stripe_intent_id', $intent->id );
        $order->update_meta_data( '_stripe_charge_captured', 'yes' );

        if ( isset( $intent->charges->first()->id ) ) {
            $order->update_meta_data( '_transaction_id', $intent->charges->first()->id );
        }

        if ( is_callable( [ $order, 'save' ] ) ) {
            $order->save();
        }
    }

    /**
     * Updates an existing intent with updated amount, source, and customer.
     *
     * @since 3.0.3
     *
     * @param object   $intent          The existing intent object.
     * @param WC_Order $order           The order.
     * @param object   $prepared_source Currently selected source.
     *
     * @return object                   An updated intent.
     */
    public function update_existing_intent( $intent, $order, $prepared_source ) {
        $request = [];

        if ( $prepared_source->source !== $intent->source ) {
            $request['source'] = $prepared_source->source;
        }

        $new_amount = Helper::get_stripe_amount( $order->get_total() );

        if ( $intent->amount !== $new_amount ) {
            $request['amount'] = $new_amount;
        }

        if ( $prepared_source->customer && $intent->customer !== $prepared_source->customer ) {
            $request['customer'] = $prepared_source->customer;
        }

        if ( empty( $request ) ) {
            return $intent;
        }

        try {
            $intent = PaymentIntent::update(
                $intent->id,
                $request
            );
        } catch ( Exception $e ) {
            throw new DokanException( 'payment_intent_error', $e->getMessage() );
        }

        return $intent;
    }

    /**
     * Confirms an intent if it is the `requires_confirmation` state.
     *
     * @param object $intent The intent to confirm.
     * @param object $prepared_source The source that is being charged.
     *
     * @return \Stripe\Paymentintent
     * @throws DokanException
     * @since 3.0.3
     */
    public function confirm_intent( $intent, $prepared_source ) {
        if ( 'requires_confirmation' !== $intent->status ) {
            return $intent;
        }

        // Try to confirm the intent (if 3DS is not required).
        $confirm_request = [
            'source' => $prepared_source->source,
        ];

        try {
            $confirmed_intent = $intent->confirm( $confirm_request );
        } catch ( Exception $e ) {
            throw new DokanException( 'unable_to_confirm_intent', $e->getMessage() );
        }

        return $confirmed_intent;
    }
}
