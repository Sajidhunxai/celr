<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Processors;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WC_Order;
use WP_Error;
use Exception;
use WeDevs\Dokan\Cache;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Api\Charge;
use WeDevs\DokanPro\Modules\StripeExpress\Api\Transfer;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Api\SetupIntent;
use WeDevs\DokanPro\Modules\StripeExpress\Api\Transaction;
use WeDevs\DokanPro\Modules\StripeExpress\Api\CountrySpec;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Settings;
use WeDevs\DokanPro\Modules\StripeExpress\Support\UserMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Api\PaymentIntent;
use WeDevs\DokanPro\Modules\StripeExpress\Api\PaymentMethod;

/**
 * Class for processing payments.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Processors
 */
class Payment {

    /**
     * Disburse payments on demand.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return void
     */
    public static function disburse( WC_Order $order ) {
        if ( Order::lock_processing( $order->get_id(), 'disburse' ) ) {
            return;
        }

        // Charge id is stored in parent order, so we need to parse it from that order.
        $parent_order = $order->get_parent_id() ? wc_get_order( $order->get_parent_id() ) : $order;

        $intent = self::get_intent( $parent_order );
        if ( ! $intent ) {
            throw new DokanException( 'dokan_stripe_express_no_intent', __( 'No intent is found to process the order!', 'dokan' ) );
        }

        $charge_id = Order::get_charge_id( $parent_order, $intent );
        $intent = self::get_intent( $parent_order );
        if ( ! $intent ) {
            throw new DokanException( 'dokan_stripe_express_no_intent', __( 'No intent is found to process the order!', 'dokan' ) );
        }

        $charge_id = Order::get_charge_id( $parent_order, $intent );
        if ( ! $charge_id ) {
            throw new DokanException( 'dokan_stripe_express_no_charge', __( 'No charge id is found to process the order!', 'dokan' ) );
        }

        $all_orders = Order::get_all_orders_to_be_processed( $order );
        if ( ! $all_orders ) {
            throw new DokanException( 'dokan_stripe_express_no_orders', __( 'No orders found to be processed!', 'dokan' ) );
        }

        $all_withdraws              = [];
        $currency                   = $order->get_currency();
        $order_total                = $order->get_total();
        $stripe_fee                 = self::get_stripe_fee( $intent );
        $sellers_pay_processing_fee = Settings::sellers_pay_processing_fees();

        if ( $order->get_meta( 'has_sub_order' ) ) {
            OrderMeta::update_dokan_gateway_fee( $order, $stripe_fee );
            /* translators: 1) gateway title, 2) processing fee with currency */
            $order->add_order_note( sprintf( __( '[%1$s] Gateway processing fee: %2$s', 'dokan' ), Helper::get_gateway_title(), wc_price( $stripe_fee, [ 'currency' => $order->get_currency() ] ) ) );
        }

        foreach ( $all_orders as $sub_order ) {
            if ( ! $sub_order instanceof WC_Order ) {
                continue;
            }

            // transfer amount to vendor's connected account
            if ( ! empty( OrderMeta::get_transfer_id( $sub_order ) ) ) {
                continue;
            }

            $sub_order_id        = $sub_order->get_id();
            $vendor_id           = dokan_get_seller_id_by_order( $sub_order_id );
            $connected_vendor_id = UserMeta::get_stripe_account_id( $vendor_id );

            if ( empty( $connected_vendor_id ) ) {
                $sub_order->add_order_note( __( 'Vendor is not connected to Stripe. The payment transfer has been terminated.', 'dokan' ) );
                continue;
            }

            $vendor_raw_earning    = dokan()->commission->get_earning_by_order( $sub_order, 'seller' );
            $sub_order_total       = $sub_order->get_total();
            $stripe_fee_for_vendor = 0;

            if ( ! empty( $order_total ) && ! empty( $sub_order_total ) && ! empty( $stripe_fee ) ) {
                $stripe_fee_for_vendor = Order::get_fee_for_suborder( $stripe_fee, $sub_order, $parent_order );
                OrderMeta::update_stripe_fee( $sub_order, $stripe_fee_for_vendor );
            }

            if ( floatval( $sub_order_total ) <= 0 ) {
                /* translators: order number */
                $sub_order->add_order_note( sprintf( __( 'Order %s payment completed', 'dokan' ), $sub_order->get_order_number() ) );
                continue;
            }

            if ( $sellers_pay_processing_fee ) {
                $vendor_raw_earning = $vendor_raw_earning - $stripe_fee_for_vendor;
                OrderMeta::update_gateway_fee_paid_by( $sub_order, 'seller' );
            } else {
                OrderMeta::update_gateway_fee_paid_by( $sub_order, 'admin' );
            }

            OrderMeta::save( $sub_order );

            $vendor_earning = Helper::get_stripe_amount( $vendor_raw_earning );

            if ( $vendor_earning <= 0 ) {
                $sub_order->add_order_note(
                    sprintf(
                        /* translators: 1) balance amount 2) currency */
                        __( 'Transfer to the vendor stripe account has been terminated due to a negative balance: %1$s %2$s', 'dokan' ),
                        $vendor_raw_earning,
                        $currency
                    )
                );
                continue;
            }

            // get currency and symbol
            $currency        = $order->get_currency();
            $currency_symbol = html_entity_decode( get_woocommerce_currency_symbol( $order->get_currency() ) );

            // prepare extra metadata
            $application_fee = dokan()->commission->get_earning_by_order( $sub_order, 'admin' );
            $metadata        = [
                'stripe_processing_fee' => $currency_symbol . wc_format_decimal( $stripe_fee_for_vendor, 2 ),
                'application_fee'       => $currency_symbol . wc_format_decimal( $application_fee, 2 ),
            ];

            // get payment info
            $payment_info = self::generate_data( $parent_order, $sub_order, $metadata, 'transfer' );

            if ( empty( $payment_info['source_transaction'] ) ) {
                $payment_info['source_transaction'] = $charge_id;
            }

            try {
                $payment_info['amount']      = $vendor_earning;
                $payment_info['currency']    = $currency;
                $payment_info['destination'] = $connected_vendor_id;
                $transfer                    = Transfer::create( $payment_info );

                OrderMeta::update_transfer_id( $sub_order, $transfer->id );
                OrderMeta::save( $sub_order );

                $withdraw_data = [
                    'user_id'  => $vendor_id,
                    'amount'   => wc_format_decimal( $vendor_raw_earning, 2 ),
                    'order_id' => $sub_order_id,
                ];

                $all_withdraws[] = $withdraw_data;
                Withdraw::process_data( $withdraw_data );

                if ( $order->get_id() !== $sub_order_id ) {
                    $sub_order->add_order_note(
                        sprintf(
                            /* translators: 1) gateway title, 2) transferred amount with currency 3) vendor's stripe account id, 4) vendor id */
                            __( '[%1$s] Successfully transferred amount: %2$s to the account: %3$s of vendor: %4$s', 'dokan' ),
                            Helper::get_gateway_title(),
                            wc_price( $vendor_raw_earning, [ 'currency' => $currency ] ),
                            $connected_vendor_id,
                            $vendor_id
                        )
                    );
                    $sub_order->add_order_note(
                        sprintf(
                            /* translators: 1) order number, 2) gateway title, 3) charge id */
                            __( 'Order %1$s payment is completed via %2$s (Charge ID: %3$s)', 'dokan' ),
                            $sub_order->get_order_number(),
                            Helper::get_gateway_title(),
                            $charge_id
                        )
                    );
                }

                $order->add_order_note(
                    sprintf(
                        /* translators: 1) gateway title, 2) transferred amount with currency 3) vendor's stripe account id, 4) vendor id */
                        __( '[%1$s] Successfully transferred amount: %2$s to the account: %3$s of vendor: %4$s', 'dokan' ),
                        Helper::get_gateway_title(),
                        wc_price( $vendor_raw_earning, [ 'currency' => $currency ] ),
                        $connected_vendor_id,
                        $vendor_id
                    )
                );

                self::save_charge_data( $sub_order, $intent );
                OrderMeta::update_customer_id( $sub_order, $intent->customer );
                OrderMeta::update_payment_method_id( $sub_order, $intent->payment_method );
                OrderMeta::save( $sub_order );
            } catch ( DokanException $e ) {
                Helper::log(
                    sprintf(
                        'Could not transfer amount to connected vendor account. Order ID: %1$s. Amount: %2$s %3$s',
                        $sub_order->get_id(),
                        $vendor_raw_earning,
                        $currency
                    )
                );

                $sub_order->add_order_note(
                    sprintf(
                        /* translators: 1) gateway title, 2) error message */
                        __( '[%1$s] Transfer failed to vendor. Reason: %2$s', 'dokan' ),
                        Helper::get_gateway_title(),
                        $e->get_message()
                    )
                );

                continue;
            }
        }

        $order->add_order_note(
            sprintf(
                /* translators: 1) Gateway title, 2) Order number, 3) payment method type, 4) charge id */
                __( '[%1$s] Order %2$s payment is completed via %3$s. (Charge ID: %4$s)', 'dokan' ),
                Helper::get_gateway_title(),
                $order->get_order_number(),
                OrderMeta::get_payment_type( $order ),
                $charge_id
            )
        );

        OrderMeta::delete_awaiting_disbursement( $order );
        OrderMeta::update_withdraw_data( $order, $all_withdraws );
        OrderMeta::save( $order );
        dokan()->commission->calculate_gateway_fee( $order->get_id() );

        Order::unlock_processing( $order->get_id(), 'disburse' );
    }

    /**
     * Get payment intent id of an order.
     *
     * @since 3.6.1
     * @since 3.7.8 Added two additional parameters `$data` and `$is_setup`
     *
     * @param WC_Order $order     Required if intent id is not provided, otherwise optional.
     * @param string   $intent_id (Optional) If not provided, the intent id will be retrieved from order.
     * @param array    $data      (Optional) Array of data to pass as arguments.
     * @param boolean  $is_setup  (Optional) Indicates if it is a setup intent.
     *
     * @return \Stripe\PaymentIntent|false
     */
    public static function get_intent( $order = null, $intent_id = null, $data = [], $is_setup = false ) {
        if ( empty( $intent_id ) && $order instanceof WC_Order ) {
            $intent_id = ! $is_setup ? OrderMeta::get_payment_intent( $order ) : OrderMeta::get_setup_intent( $order );
        }

        if ( empty( $intent_id ) ) {
            return false;
        }

        if ( ! $is_setup ) {
            $data = array_merge_recursive(
                [
                    'expand' => [
                        'charges.data',
                        'latest_charge',
                    ],
                ],
                $data
            );

            $intent = PaymentIntent::get( $intent_id, $data );
        } else {
            $intent = SetupIntent::get( $intent_id, $data );
        }

        if ( ! empty( $intent->last_payment_error ) ) {
            Helper::log( 'Error when processing payment: ' . $intent->last_payment_error->message );
            return false;
        }

        return $intent;
    }

    /**
     * Create a new Payment Intent.
     *
     * @since 3.6.1
     *
     * @param WC_Order     $order The order being processed
     * @param array|object $data  The source that is used for the payment
     * @param boolean      $setup Flag to determine if is is a setup intent
     *
     * @return \Stripe\PaymentIntent|\Stripe\SetupIntent
     * @throws DokanException
     */
    public static function create_intent( WC_Order $order, $data, $setup = false ) {
        $payment_info = self::generate_data( $order );

        $request = [
            'amount'               => Helper::get_stripe_amount( $order->get_total() ),
            'currency'             => strtolower( $order->get_currency() ),
            'description'          => $payment_info['description'],
            'metadata'             => $payment_info['metadata'],
            'capture_method'       => 'automatic',
            'payment_method_types' => [ 'card' ],
        ];

        $request = wp_parse_args( $data, $request );

        try {
            $intent = ! $setup ? PaymentIntent::create( $request ) : SetupIntent::create( $request );
        } catch ( DokanException $e ) {
            throw new DokanException( 'unable_to_create_intent', $e->get_message() );
        }

        self::save_intent_data( $order, $intent );

        return $intent;
    }

    /**
     * Updates payment intent to be able to save payment method.
     *
     * @since 3.6.1
     *
     * @param string  $intent_id           The id of the intent to update.
     * @param int     $order_id            The id of the order if intent created from Order.
     * @param array   $data                The data to update the intent with.
     * @param boolean $save_payment_method True if saving the payment method.
     * @param string  $payment_type        The name of the selected payment type or empty string.
     *
     * @return \Stripe\PaymentIntent|\Stripe\SetupIntent
     * @throws DokanException  If the update intent call returns with an error.
     */
    public static function update_intent( $intent_id, $order_id, $data = [], $is_setup = false, $save_payment_method = false, $payment_type = '' ) {
        $order = wc_get_order( $order_id );

        if ( ! is_a( $order, 'WC_Order' ) ) {
            throw new DokanException( 'invalid_order', __( 'No valid order found!', 'dokan' ) );
        }

            // get payment info
        $payment_info = self::generate_data( $order );

        $request = [
            'description'          => $payment_info['description'],
            'metadata'             => $payment_info['metadata'],
            'payment_method_types' => [ 'card' ],
        ];

        if ( ! $is_setup ) {
            $request['amount']   = Helper::get_stripe_amount( $order->get_total(), strtolower( $order->get_currency() ) );
            $request['currency'] = strtolower( $order->get_currency() );
        }

        // Get user/customer for order.
        $customer_id = Order::get_stripe_customer_id_from_order( $order );
        if ( ! empty( $customer_id ) ) {
            $user_id = $order->get_customer_id();
            $request['customer'] = $customer_id;
        } else {
            $user        = Order::get_user_from_order( $order );
            $user_id     = $user->ID;
            $customer    = Customer::set( $user_id );
            $customer_id = $customer->get_id();

            /*
             * At this stage, it is possible for new customers
             * not being registered to Stripe. So we need to
             * make sure the customer is synchronized in the
             * Stripe end, and we have a Stripe customer ID
             * available to pass as the parameter.
             */
            if ( empty( $customer_id ) ) {
                $customer->set_id( 0 );
                $customer_id = $customer->update_or_create();
                if ( is_wp_error( $customer_id ) ) {
                    throw new DokanException( 'customer_create_error', $customer_id->get_error_message() );
                }
            }
            $request['customer'] = $customer_id;
        }

        if ( ! empty( $payment_type ) ) {
            // Only update the payment_method_types if we have a reference to the payment type the customer selected.
            $request['payment_method_types'] = [ $payment_type ];
            self::set_method_title( $order, $payment_type );
            OrderMeta::update_payment_type( $order, $payment_type );
            OrderMeta::save( $order );
        }

        if ( $save_payment_method && ! $is_setup ) {
            $request['setup_future_usage'] = 'off_session';
            $request['metadata']['save_payment_method'] = '1';
        }

        $request = wp_parse_args( (array) $data, $request );

        try {
            $intent = ! $is_setup
                ? PaymentIntent::update( $intent_id, $request )
                : SetupIntent::update( $intent_id, $request );
        } catch ( DokanException $e ) {
            if ( ! Helper::is_no_such_customer_error( $e->get_message() ) ) {
                throw new DokanException( 'intent_update_error', $e->get_message() );
            }

            $customer = Customer::set( $user_id );
            $customer->set_id( 0 );
            $customer = $customer->update_or_create();
            if ( is_wp_error( $customer ) ) {
                throw new DokanException( 'customer_create_error', $e->get_message() );
            }

            $request['customer'] = $customer;

            $intent = ! $is_setup
                ? PaymentIntent::update( $intent_id, $request )
                : SetupIntent::update( $intent_id, $request );
        }

        $order->update_status( 'pending', __( 'Awaiting payment.', 'dokan' ) );
        OrderMeta::update_intent( $order, $intent_id, $is_setup );
        OrderMeta::save( $order );

        return $intent;
    }

    /**
     * Confirms an intent if it is the `requires_confirmation` state.
     *
     * @since 3.6.1
     *
     * @param \Stripe\PaymentIntent $intent         The intent to confirm.
     * @param string                $payment_method The payment method.
     *
     * @return \Stripe\Paymentintent|WP_Error
     */
    public static function confirm_intent( $intent, $payment_method ) {
        if ( 'requires_confirmation' !== $intent->status ) {
            return $intent;
        }

        try {
            // Try to confirm the intent (if 3DS is not required).
            $confirmed_intent = $intent->confirm(
                [
                    'payment_method' => $payment_method,
                ]
            );
        } catch ( \Exception $e ) {
            return new WP_Error( 'unable_to_confirm_intent', $e->getMessage() );
        }

        return $confirmed_intent;
    }

    /**
     * Verifies the intent after payment is requested.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     *
     * @return void
     */
    public static function verify_intent( $order ) {
        if ( $order->get_payment_method() !== Helper::get_gateway_id() ) {
            return;
        }

        $intent = self::get_intent( $order );
        if ( ! $intent ) {
            return;
        }

        /*
         * It is possible that the order has been modified
         * or locked while processing a webhook in the meantime.
         * This ensures we are reading the right status.
         */
        clean_post_cache( $order->get_id() );
        $order = wc_get_order( $order->get_id() );

        /**
         * Filter the list of order statuses that should be ignored when verifying an intent.
         *
         * @since 3.7.8
         *
         * @param array    $statuses The list of order statuses to ignore.
         * @param WC_Order $order The order object.
         */
        $allowed_statuses = apply_filters( 'dokan_stripe_express_allowed_payment_processing_statuses', [ 'pending', 'failed' ], $order );

        // If payment has already been completed, this process is not needed.
        if ( ! $order->has_status( $allowed_statuses ) ) {
            return;
        }

        if ( Order::lock_processing( $order->get_id(), 'intent', $intent->id ) ) {
            return;
        }

        if ( 'setup_intent' === $intent->object && 'succeeded' === $intent->status ) {
            WC()->cart->empty_cart();
            $order->payment_complete();

            do_action( 'dokan_stripe_express_payment_completed', $order, $intent );
        } elseif ( 'succeeded' === $intent->status || 'requires_capture' === $intent->status ) {
            // Proceed with the payment completion.
            $response = self::get_latest_charge_from_intent( $intent );
            if ( ! $response ) {
                $response = $intent;
            }

            self::process_response( $response, $order );
            Subscription::process_early_renewal_success( $order );
        } elseif ( 'requires_payment_method' === $intent->status ) {
            // `requires_payment_method` means that SCA got denied for the current payment method.
            Order::process_failed_sca_auth( $order, $intent );
            Subscription::process_early_renewal_failure( $order );
        }

        Order::unlock_processing( $order->get_id() );
    }

    /**
     * Update order and maybe save payment method for an order after an intent has been created and confirmed.
     *
     * @since 3.6.1
     *
     * @param \WC_Order $order               Order being processed.
     * @param string    $intent_id           Stripe setup/payment ID.
     * @param bool      $save_payment_method Boolean representing whether payment method for order should be saved.
     *
     * @return void
     */
    public static function process_confirmed_intent( $order, $intent_id, $save_payment_method ) {
        $payment_needed = Helper::is_payment_needed( $order->get_id() );

        // Get payment intent to confirm status.
        if ( $payment_needed ) {
            $intent = PaymentIntent::get(
                $intent_id,
                [
                    'expand' => [
                        'payment_method',
                        'charges.data',
                    ],
                ]
            );

            $error = isset( $intent->last_payment_error ) ? $intent->last_payment_error : false;
        } else {
            $intent = SetupIntent::get(
                $intent_id,
                [
                    'expand' => [
                        'payment_method',
                        'latest_attempt',
                    ],
                ]
            );

            $error = isset( $intent->last_setup_error ) ? $intent->last_setup_error : false;
        }

        if ( ! empty( $error ) ) {
            Helper::log( 'Error when processing payment: ' . $error->message );
            throw new DokanException(
                'dokan-stripe-express-payment-error',
                __( "We're not able to process this payment. Please try again later.", 'dokan' )
            );
        }

        self::process_payment_method( $order, $intent, $save_payment_method );
        self::save_intent_data( $order, $intent );
        self::save_charge_data( $order, $intent );
        OrderMeta::update_redirect_processed( $order );
        OrderMeta::save( $order );

        /**
         * Process payment when needed.
         *
         * @since 3.7.8
         *
         * @param WC_Order $order             The order being processed.
         * @param string   $payment_method_id The source of the payment.
         */
        do_action( 'dokan_stripe_express_process_payment', $order, $intent->payment_method->id );

        if ( Subscription::is_recurring_vendor_subscription_order( $order ) ) {
            return;
        }

        if ( ! $order->has_status( [ 'pending', 'failed' ] ) ) {
            return;
        }

        if ( Order::lock_processing( $order->get_id(), 'intent', $intent->id ) ) {
            return;
        }

        if ( $payment_needed ) {
            // Use the last charge within the intent to proceed or the original response in case of SEPA
            $response = self::get_latest_charge_from_intent( $intent );
            if ( ! $response ) {
                $response = $intent;
            }
            self::process_response( $response, $order );

            Order::unlock_processing( $order->get_id() );
        } else {
            $order->payment_complete();
            do_action( 'dokan_stripe_express_payment_completed', $order, $intent );
        }
    }

    /**
     * Processes the payment method from intent.
     *
     * @since 3.7.8
     *
     * @param WC_Order                                  $order
     * @param \Stripe\PaymentIntent|\Stripe\SetupIntent $intent
     * @param bool                                      $save_payment_method
     *
     * @return void
     */
    public static function process_payment_method( $order, $intent, $save_payment_method ) {
        list( $payment_method_type, $payment_method_details ) = self::get_method_data_from_intent( $intent );

        $payment_methods = Helper::get_available_method_instances();
        if ( ! isset( $payment_methods[ $payment_method_type ] ) ) {
            return;
        }

        $payment_method = $payment_methods[ $payment_method_type ];

        /*
         * To mask the iDeal payment method as SEPA Direct Debit
         * as iDeal cannot be used directly as reusable payment method.
         */
        if ( $save_payment_method && $payment_method->is_reusable() ) {
            if ( $payment_method->get_id() !== $payment_method->get_retrievable_type() ) {
                $payment_method_id     = $payment_method_details[ $payment_method_type ]->generated_sepa_debit;
                $stripe_payment_method = PaymentMethod::get( $payment_method_id );
            } else {
                if ( is_object( $intent->payment_method ) ) {
                    $stripe_payment_method = $intent->payment_method;
                } else {
                    $stripe_payment_method = PaymentMethod::get( $intent->payment_method );
                }
            }

            self::save_payment_method_data( $order, $stripe_payment_method );

            $user = Order::get_user_from_order( $order );
            do_action( 'dokan_stripe_express_add_payment_method', $user->ID, $stripe_payment_method );
        }

        self::set_method_title( $order, $payment_method_type );
    }

    /**
     * Extracts payment method data from intent.
     *
     * @since 3.6.1
     *
     * @param \Stripe\PaymentIntent|\Stripe\SetupIntent $intent
     *
     * @return array list {payment_method_type:string|null,payment_method_details:\Stripe\PaymentMethod|false}
     */
    public static function get_method_data_from_intent( $intent ) {
        $payment_method_type    = '';
        $payment_method_details = false;

        if ( 'payment_intent' === $intent->object ) {
            if ( ! empty( $intent->charges ) && 0 < $intent->charges->total_count ) {
                $charge                 = self::get_latest_charge_from_intent( $intent );
                $payment_method_details = $charge->payment_method_details;
                $payment_method_type    = ! empty( $payment_method_details ) ? $payment_method_details->type : '';
            }
        } elseif ( 'setup_intent' === $intent->object ) {
            if ( ! empty( $intent->latest_attempt->payment_method_details ) ) {
                $payment_method_details = $intent->latest_attempt->payment_method_details;
                $payment_method_type    = $payment_method_details->type;
            } elseif ( ! empty( $intent->payment_method ) ) {
                $payment_method_details = $intent->payment_method;
                $payment_method_type    = $payment_method_details->type;
            }
        }

        return [ $payment_method_type, $payment_method_details ];
    }

    /**
     * Set formatted readable payment method title for order,
     * using payment method details from accompanying charge.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order               WC Order being processed.
     * @param string   $payment_method_type Stripe payment method key.
     *
     * @return void
     */
    public static function set_method_title( $order, $payment_method_type ) {
        $payment_methods = Helper::get_available_method_instances();
        if ( ! isset( $payment_methods[ $payment_method_type ] ) ) {
            return;
        }

        $payment_method_title = $payment_methods[ $payment_method_type ]->get_label();
        $order->set_payment_method( Helper::get_gateway_id() );
        $order->set_payment_method_title( $payment_method_title );
        $order->save();
    }

    /**
     * Stores extra meta data for an order from a Stripe Response.
     *
     * @since 3.6.1
     *
     * @param object $response
     * @param WC_Order $order
     *
     * @return object
     * @throws DokanException
     */
    public static function process_response( $response, $order ) {
        $order_id = $order->get_id();
        $captured = ! empty( $response->captured ) ? 'yes' : 'no';

        // Store charge data.
        OrderMeta::update_charge_captured( $order, $captured );
        OrderMeta::save( $order );

        if ( 'yes' === $captured ) {
            switch ( $response->status ) {
                case 'succeeded':
                    OrderMeta::update_transaction_id( $order, $response->id );
                    OrderMeta::save( $order );

                    $order->add_order_note(
                        /* translators: 1) gateway title, 2) transaction id */
                        sprintf( __( '[%1$s] Charge complete (Charge ID: %2$s)', 'dokan' ), Helper::get_gateway_title(), $response->id )
                    );

                    $order->payment_complete( $response->id );

                    $intent = null;
                    if ( 'charge' === $response->object ) {
                        $intent = self::get_intent( $order, $response->payment_intent );
                    } elseif ( 'payment_intent' === $response->object ) {
                        $intent = $response;
                    }

                    do_action( 'dokan_stripe_express_payment_completed', $order, $intent );

                    break;

                /*
                 * Charge can be captured but in a pending state. Payment methods
                 * that are asynchronous may take couple days to clear. Webhook will
                 * take care of the status changes.
                 */
                case 'pending':
                    $order_stock_reduced = $order->get_meta( '_order_stock_reduced', true );

                    if ( ! $order_stock_reduced ) {
                        wc_reduce_stock_levels( $order_id );
                    }

                    OrderMeta::update_transaction_id( $order, $response->id );
                    OrderMeta::save( $order );

                    $order->update_status(
                        'on-hold',
                        /* translators: 1) gateway title, 2) transaction id */
                        sprintf( __( '[%1$s] Stripe charge awaiting payment: %2$ss.', 'dokan' ), Helper::get_gateway_title(), $response->id )
                    );
                    break;

                case 'failed':
                    $localized_message = __( 'Payment processing failed. Please retry.', 'dokan' );
                    $order->add_order_note( $localized_message );
                    throw new DokanException( print_r( $response, true ), $localized_message );
            }
        } else {
            OrderMeta::update_transaction_id( $order, $response->id );
            OrderMeta::save( $order );

            if ( $order->has_status( [ 'pending', 'failed' ] ) ) {
                wc_reduce_stock_levels( $order_id );
            }

            $order->update_status(
                'on-hold',
                sprintf(
                    /* translators: 1) gateway title, 2) charge id */
                    __( '[%1$s] Charge authorized (Charge ID: %2$s). Process order to take payment, or cancel to remove the pre-authorization. Attempting to refund the order in part or in full will release the authorization and cancel the payment.', 'dokan' ),
                    Helper::get_gateway_title(),
                    $response->id
                )
            );
        }

        do_action( 'dokan_stripe_express_process_response', $response, $order );

        return $response;
    }

    /**
     * Saves payment method data top order meta
     *
     * @since 3.6.1
     * @since 3.7.8 Added action hook `dokan_stripe_express_save_payment_method_data`
     *
     * @param WC_Order              $order
     * @param \Stripe\PaymentMethod $payment_method
     *
     * @return void
     */
    public static function save_payment_method_data( $order, $payment_method ) {
        if ( ! $payment_method instanceof \Stripe\PaymentMethod ) {
            return;
        }

        if ( $payment_method->customer ) {
            OrderMeta::update_customer_id( $order, $payment_method->customer );
        }

        OrderMeta::update_payment_method_id( $order, $payment_method->id );
        OrderMeta::save( $order );

        /**
         * Hooks when payment method data are updated for an order.
         *
         * @since 3.7.8
         *
         * @param WC_Order              $order
         * @param \Stripe\PaymentMethod $payment_method
         */
        do_action( 'dokan_stripe_express_save_payment_method_data', $order, $payment_method );
    }

    /**
     * Saves payment/setup intent to order meta.
     *
     * @since 3.6.1
     *
     * @param WC_Order                                  $order
     * @param \Stripe\PaymentIntent|\Stripe\SetupIntent $intent
     *
     * @return void
     */
    public static function save_intent_data( WC_Order $order, $intent ) {
        if ( 'payment_intent' === $intent->object ) {
            OrderMeta::add_payment_intent( $order, $intent->id );
        } elseif ( 'setup_intent' === $intent->object ) {
            OrderMeta::add_setup_intent( $order, $intent->id );
        }

        OrderMeta::save( $order );
    }

    /**
     * Saves charge data for order.
     *
     * @since 3.6.1
     *
     * @param WC_Order              $order
     * @param \Stripe\PaymentIntent $intent
     *
     * @return void
     */
    public static function save_charge_data( WC_Order $order, $intent ) {
        Ordermeta::update_charge_captured( $order );

        $charge_id = Order::get_charge_id( $order, $intent );

        if ( $charge_id ) {
            OrderMeta::update_transaction_id( $order, $charge_id );
        }

        OrderMeta::save( $order );
    }

    /**
     * Retrieves stripe fee from intent.
     *
     * @since 3.6.1
     *
     * @param \Stripe\PaymentIntent $intent The stripe intent object
     * @param boolean               $raw    (Optional) Whether or not to format the value. By default, formatted value will be returned.
     *
     * @return string|float
     */
    public static function get_stripe_fee( $intent, $raw = false ) {
        $latest_charge_data  = self::get_latest_charge_from_intent( $intent );
        $balance_transaction = Transaction::get( $latest_charge_data->balance_transaction );

        return ! $raw
            ? Helper::format_balance_fee( $balance_transaction )
            : $balance_transaction->fee;
    }

    /**
     * Get payment source. This can be a new token/source or existing WC token.
     * If user is logged in and/or has WC account, create an account on Stripe.
     * This way we can attribute the payment to the user to better fight fraud.
     *
     * @since 3.6.1
     *
     * @param int      $user_id              ID of the WP user.
     * @param bool     $force_save_source    Should we force save payment source.
     * @param int|null $existing_customer_id ID of customer if already exists.
     *
     * @return object{customer:string|false,payment_method:string|null,payment_method_object:\Stripe\PaymentMethod|false,setup_future_usage:'off_session'|'on_session'}
     * @throws Exception
     */
    public static function prepare_source( $user_id, $force_save_source = false, $existing_customer_id = null ) {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        $customer = Customer::set( $user_id );

        if ( ! empty( $existing_customer_id ) ) {
            $customer->set_id( $existing_customer_id );
        }

        $payment_method_source = null;
        $payment_method_id     = '';
        $setup_future_usage    = false;
        $payment_method        = isset( $_POST['payment_method'] )
            ? sanitize_text_field( wp_unslash( $_POST['payment_method'] ) )
            : Helper::get_gateway_id();

        // New CC info was entered and we have a new source to process.
        if ( ! empty( $_POST['payment_method_source'] ) ) {
            $payment_method_source = PaymentMethod::get( sanitize_text_field( wp_unslash( $_POST['payment_method_source'] ) ) );
            $payment_method_id     = $payment_method_source ? $payment_method_source->id : '';

            /*
             * This is true if the user wants to store the card to their account.
             * Criteria to save to file is they are logged in, they opted
             * to save or product requirements and the source is actually reusable.
             * Either that or force_save_source is true.
             */
            if (
                $force_save_source ||
                ( $user_id && Settings::is_saved_cards_enabled() && Helper::is_saved_card( $payment_method ) )
            ) {
                $response = $customer->attach_payment_method( $payment_method_id );
                if ( is_wp_error( $response ) ) {
                    throw new Exception( $response->get_error_message() );
                }
                $setup_future_usage = true;
            }
        } else {
            $wc_token = Token::parse_from_request();
            if ( empty( $wc_token ) ) {
                WC()->session->set( 'refresh_totals', true );
                throw new Exception( __( 'Invalid payment method. Please input a new card number.', 'dokan' ) );
            }

            $payment_method_id = $wc_token->get_token();
        }

        $customer_id = $customer->get_id();
        if ( ! $customer_id ) {
            $created = $customer->create();
            if ( is_wp_error( $created ) ) {
                throw new Exception( $created->get_error_message() );
            }

            $customer->set_id( $created );
            $customer_id = $customer->get_id();
        } else {
            $updated = $customer->update();
            if ( is_wp_error( $updated ) ) {
                throw new Exception( $updated->get_error_message() );
            }
        }

        if ( empty( $payment_method_source ) ) {
            $payment_method_source = PaymentMethod::get( $payment_method_id );
        }

        return (object) [
            'customer'              => $customer_id,
            'payment_method'        => $payment_method_id,
            'payment_method_object' => $payment_method_source,
            'setup_future_usage'    => $setup_future_usage ? 'off_session' : 'on_session',
        ];
        // phpcs:enable WordPress.Security.NonceVerification.Missing
    }

    /**
     * Retrieves gateway fee from charge.
     *
     * @since 3.7.8
     *
     * @param string $charge_id
     *
     * @return string
     */
    public static function get_gateway_fee_from_charge( $charge_id ) {
        $charge = Charge::get( $charge_id, [ 'expand' => [ 'balance_transaction' ] ] );
        if ( ! isset( $charge->balance_transaction ) ) {
            return 0;
        }

        return Helper::format_balance_fee( $charge->balance_transaction );
    }

    /**
     * Given a response from Stripe, check if it's a card error where
     * authentication is required to complete the payment.
     *
     * @since 3.7.8
     *
     * @param \Stripe\PaymentIntent|\Stripe\SetupIntent The response from Stripe.
     *
     * @return boolean Whether or not it's a 'authentication_required' error
     */
    public static function is_authentication_required( $intent ) {
        if ( ! empty( $intent->error ) ) {
            return 'authentication_required' === $intent->error->code;
        }
        if ( ! empty( $intent->last_payment_error ) ) {
            return 'authentication_required' === $intent->last_payment_error->code;
        }
        if ( ! empty( $intent->status ) ) {
            return 'requires_action' === $intent->status || 'requires_confirmation' === $intent->status;
        }
        return false;
    }

    /**
     * Generate extra information for orders to send with stripe.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order                     The Order object.
     * @param WC_Order $sub_order      (Optional) The Sub Order object if available.
     * @param array    $extra_metadata (Optional) Extra metadata to attach.
     * @param string   $api_type       (Optional) The Stripe API type to use. Ex: 'payment_intent', 'setup_intent', 'transfer', 'charge', etc.
     *
     * @return array
     */
    public static function generate_data( WC_Order $order, WC_Order $sub_order = null, $extra_metadata = [], $api_type = 'payment_intent' ) {
        $post_data = [
            'transfer_group' => apply_filters(
                'dokan_stripe_express_transfer_group',
                sprintf( 'Dokan Order#%d', $order->get_id() ),
                $order,
                $sub_order
            ),
        ];

        if ( 'transfer' !== $api_type ) {
            $statement_descriptor = Settings::get_statement_descriptor();
            if ( ! empty( $statement_descriptor ) ) {
                $post_data['statement_descriptor'] = $statement_descriptor;
            }

            if ( method_exists( $order, 'get_shipping_postcode' ) && ! empty( $order->get_shipping_postcode() ) ) {
                $post_data['shipping'] = [
                    'name'    => trim( $order->get_shipping_first_name() . ' ' . $order->get_shipping_last_name() ),
                    'address' => [
                        'line1'       => $order->get_shipping_address_1(),
                        'line2'       => $order->get_shipping_address_2(),
                        'city'        => $order->get_shipping_city(),
                        'country'     => $order->get_shipping_country(),
                        'postal_code' => $order->get_shipping_postcode(),
                        'state'       => $order->get_shipping_state(),
                    ],
                ];
            }
        } else {
            $post_data['source_transaction'] = $order->get_transaction_id();
        }

        $metadata = [
            'customer_name'  => sanitize_text_field( $order->get_billing_first_name() ) . ' ' . sanitize_text_field( $order->get_billing_last_name() ),
            'customer_email' => sanitize_email( $order->get_billing_email() ),
            'order_id'       => $order->get_id(),
            'site_url'       => esc_url( get_site_url() ),
            'payment_type'   => 'single',
        ];

        if ( Subscription::is_subscription_order( $order->get_id() ) ) {
            $metadata['payment_type'] = 'recurring';
        }

        if ( is_array( $extra_metadata ) && ! empty( $extra_metadata ) ) {
            $metadata += $extra_metadata;
        }

        if ( ! is_null( $sub_order ) && $sub_order->get_id() !== $order->get_id() ) {
            $post_data['description'] = sprintf(
                /* translators: 1) blog name 2) order number 3) sub order number */
                __( '%1$1s - Order %2$2s, suborder of %3$3s', 'dokan' ),
                Helper::get_blogname(),
                $sub_order->get_order_number(),
                $order->get_order_number()
            );

            // Fix sub order metadata
            $metadata['order_id']        = $sub_order->get_id();
            $metadata['parent_order_id'] = $order->get_id();
        } else {
            $post_data['description'] = sprintf(
                /* translators: 1) blog name 2) order number */
                __( '%1$s - Order %2$s', 'dokan' ),
                Helper::get_blogname(),
                $order->get_order_number()
            );
        }

        $post_data['metadata'] = apply_filters( 'dokan_stripe_express_payment_metadata', $metadata, $order, $sub_order );

        return apply_filters( 'dokan_stripe_express_generate_payment_info', $post_data, $order, $sub_order );
    }

    /**
     * Retrieves latest charge from payment intent.
     *
     * While passing the payment intent object, make sure the `charges.data`
     * and/or `latest_charge` were expanded inside the object.
     * For example, the intent object was retrieved with the following arguments:
     * [
     *     expand => [
     *         'latest_charge',
     *         'charges.data',
     *     ]
     * ]
     *
     * @since 3.7.12
     *
     * @param \Stripe\PaymentIntent $intent The payment intent object.
     *
     * @return \Stripe\Charge|false
     */
    public static function get_latest_charge_from_intent( $intent ) {
        if ( ! empty( $intent->latest_charge ) ) {
            $latest_charge = $intent->latest_charge;

            if ( is_string( $latest_charge ) ) {
                $latest_charge = Charge::get( $latest_charge );
            }

            if ( ! $latest_charge instanceof \Stripe\Charge ) {
                return false;
            }

            return $latest_charge;
        }

        if ( ! empty( $intent->charges->data ) ) {
            return end( $intent->charges->data );
        }

        return false;
    }

    /**
     * Retrieves supported transfer countries based on the marketplace country.
     * Currently only the EU countries are supported for each other.
     *
     * @since 3.7.17
     *
     * @param string $country_code (Optional) The two-letter ISO code of the country of the marketplace.
     *
     * @return string[] List of two-letter ISO codes of the supported transfer countries.
     */
    public static function get_supported_transfer_countries( $country_code = null ) {
        try {
            if ( empty( $country_code ) ) {
                $country_code = User::get_platform_country();
            }

            // Apply the feature for EU countries only
            if ( ! in_array( $country_code, Helper::get_european_countries(), true ) ) {
                return [];
            }

            $cache_key     = "stripe_express_get_specs_for_$country_code";
            $cache_group   = 'stripe_express_country_specs';
            $country_specs = Cache::get_transient( $cache_key, $cache_group );

            if ( false === $country_specs ) {
                $country_specs = CountrySpec::get( $country_code );
                Cache::set_transient( $cache_key, $country_specs, $cache_group );
            }

            if ( ! isset( $country_specs->supported_transfer_countries ) ) {
                return [];
            }

            return $country_specs->supported_transfer_countries;
        } catch ( DokanException $e ) {
            return [];
        }
    }

    /**
     * Create the level 3 data array to send to Stripe when making a purchase.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order The order that is being paid for.
     *
     * @return array          The level 3 data to send to Stripe.
     */
    public static function generate_level3_data( WC_Order $order ) {
        // Get the order items. Don't need their keys, only their values.
        // Order item IDs are used as keys in the original order items array.
        $order_items = array_values( $order->get_items( [ 'line_item', 'fee' ] ) );
        $currency    = $order->get_currency();

        $stripe_line_items = array_map(
            function( $item ) use ( $currency ) {
                if ( is_a( $item, 'WC_Order_Item_Product' ) ) {
                    $product_id = $item->get_variation_id()
                        ? $item->get_variation_id()
                        : $item->get_product_id();
                    $subtotal   = $item->get_subtotal();
                } else {
                    $product_id = substr( sanitize_title( $item->get_name() ), 0, 12 );
                    $subtotal   = $item->get_total();
                }
                $product_description = substr( $item->get_name(), 0, 26 );
                $quantity            = $item->get_quantity();
                $unit_cost           = Helper::get_stripe_amount( ( $subtotal / $quantity ), $currency );
                $tax_amount          = Helper::get_stripe_amount( $item->get_total_tax(), $currency );
                $discount_amount     = Helper::get_stripe_amount( $subtotal - $item->get_total(), $currency );

                return (object) [
                    'product_code'        => (string) $product_id, // Up to 12 characters that uniquely identify the product.
                    'product_description' => $product_description, // Up to 26 characters long describing the product.
                    'unit_cost'           => $unit_cost, // Cost of the product, in cents, as a non-negative integer.
                    'quantity'            => $quantity, // The number of items of this type sold, as a non-negative integer.
                    'tax_amount'          => $tax_amount, // The amount of tax this item had added to it, in cents, as a non-negative integer.
                    'discount_amount'     => $discount_amount, // The amount an item was discounted—if there was a sale,for example, as a non-negative integer.
                ];
            },
            $order_items
        );

        $level3_data = [
            'merchant_reference' => $order->get_id(), // An alphanumeric string of up to  characters in length. This unique value is assigned by the merchant to identify the order. Also known as an “Order ID”.
            'shipping_amount'    => Helper::get_stripe_amount( (float) $order->get_shipping_total() + (float) $order->get_shipping_tax(), $currency ), // The shipping cost, in cents, as a non-negative integer.
            'line_items'         => $stripe_line_items,
        ];

        // The customer’s U.S. shipping ZIP code.
        $shipping_address_zip = $order->get_shipping_postcode();
        if ( Helper::is_valid_zip_code( $shipping_address_zip ) ) {
            $level3_data['shipping_address_zip'] = $shipping_address_zip;
        }

        // The merchant’s U.S. shipping ZIP code.
        $store_postcode = get_option( 'woocommerce_store_postcode' );
        if ( Helper::is_valid_zip_code( $store_postcode ) ) {
            $level3_data['shipping_from_zip'] = $store_postcode;
        }

        return $level3_data;
    }
}
