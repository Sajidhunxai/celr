<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Controllers;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WC_Order;
use Exception;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Settings;
use WeDevs\DokanPro\Modules\StripeExpress\Api\PaymentMethod;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Payment;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Customer;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Subscription;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Order as OrderProcessor;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\BackgroundProcesses\DelayedDisbursement;

/**
 * Class for controlling payment intents.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Controllers
 */
class Order {

    /**
     * Constructor for Intent controller.
     *
     * @since 3.6.1
     */
    public function __construct() {
        add_action( 'init', [ $this, 'hooks' ] );
    }

    /**
     * Registers all hooks.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function hooks() {
        // Process order redirect
        add_action( 'wp', [ $this, 'maybe_process_order_redirect' ] );
        // Handle payment disbursement
        add_action( 'woocommerce_order_status_changed', [ $this, 'handle_payment_disbursement' ], 10, 3 );
        // Hook for schedule to maintain delayed payment
        add_action( 'dokan_stripe_express_daily_schedule', [ $this, 'disburse_delayed_payment' ] );
        // Modify processing fees and net amounts
        add_filter( 'dokan_get_processing_fee', [ $this, 'get_order_processing_fee' ], 10, 2 );
        add_filter( 'dokan_get_processing_gateway_fee', [ $this, 'get_processing_gateway_fee' ], 10, 3 );
        add_filter( 'dokan_orders_vendor_net_amount', [ $this, 'get_vendor_net_amount' ], 10, 5 );
    }

    /**
     * Processes order redirect if necessary.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function maybe_process_order_redirect() {
        if ( Helper::is_payment_methods_page() ) {
            if ( ! Helper::is_setup_intent_success_creation_redirection() ) {
                return;
            }

            if ( ! isset( $_GET['redirect_status'] ) || 'succeeded' !== $_GET['redirect_status'] ) {
                wc_add_notice( __( 'Failed to add payment method.', 'dokan' ), 'error', [ 'icon' => 'error' ] );
                return;
            }

            $setup_intent = false;
            if ( isset( $_GET['setup_intent'] ) ) {
                $setup_intent = Payment::get_intent(
                    null,
                    sanitize_text_field( wp_unslash( $_GET['setup_intent'] ) ),
                    [
                        'expand' => [
                            'payment_method',
                            'latest_attempt',
                        ],
                    ],
                    true
                );
            }

            if ( ! $setup_intent ) {
                return;
            }

            /*
             * The newly created payment method does not inherit the customers' billing info, so we manually
             * trigger an update; in case of failure we log the error and continue because the payment method's
             * billing info will be updated when the customer makes a purchase anyway.
             */
            try {
                $user_id           = get_current_user_id();
                $customer          = Customer::set( $user_id );
                $payment_method_id = $setup_intent->payment_method->id;

                if (
                    ! empty( $setup_intent->latest_attempt->payment_method_details ) &&
                    'ideal' === $setup_intent->latest_attempt->payment_method_details->type &&
                    ! empty(
                        $setup_intent->latest_attempt->payment_method_details
                            ->{$setup_intent->latest_attempt->payment_method_details->type}
                                ->generated_sepa_debit
                    )
                ) {
                    $payment_method_id = $setup_intent->latest_attempt->payment_method_details
                        ->{$setup_intent->latest_attempt->payment_method_details->type}
                            ->generated_sepa_debit;
                }

                // Payment method needs to be attached to a customer before updating
                $customer->attach_payment_method( $payment_method_id );
                $customer_data         = $customer->map_data( null, new \WC_Customer( $user_id ) );
                $payment_method_object = PaymentMethod::update(
                    $payment_method_id,
                    [
                        'billing_details' => [
                            'name'    => $customer_data['name'],
                            'email'   => $customer_data['email'],
                            'phone'   => $customer_data['phone'],
                            'address' => $customer_data['address'],
                        ],
                    ]
                );

                do_action( 'dokan_stripe_express_add_payment_method', $user_id, $payment_method_object );

                wc_add_notice( __( 'Payment method successfully added.', 'dokan' ) );
            } catch ( DokanException $e ) {
                wc_add_notice( __( 'Failed to add payment method.', 'dokan' ), 'error' );
                Helper::log( 'Error: ' . $e->get_message() );
            }

            return;
        }

        if (
            ! is_order_received_page() ||
            empty( $_GET['wc_payment_method'] ) ||
            Helper::get_gateway_id() !== sanitize_text_field( wp_unslash( $_GET['wc_payment_method'] ) ) ||
            ! isset( $_GET['_wpnonce'] ) ||
            ! wp_verify_nonce( sanitize_key( wp_unslash( $_GET['_wpnonce'] ) ), 'dokan_stripe_express_process_redirect_order' ) ||
            empty( $_GET['order_id'] )
        ) {
            return;
        }

        $order = wc_get_order( absint( $_GET['order_id'] ) );
        if ( ! $order ) {
            return;
        }

        if ( ! empty( $_GET['payment_intent_client_secret'] ) ) {
            $intent_id = isset( $_GET['payment_intent'] ) ? sanitize_text_field( wp_unslash( $_GET['payment_intent'] ) ) : '';
            OrderMeta::update_debug_payment_intent( $order, $intent_id );
            OrderMeta::save( $order );
        } elseif ( ! empty( $_GET['setup_intent_client_secret'] ) ) {
            $intent_id = isset( $_GET['setup_intent'] ) ? sanitize_text_field( wp_unslash( $_GET['setup_intent'] ) ) : '';
            OrderMeta::update_debug_setup_intent( $order, $intent_id );
            OrderMeta::save( $order );
        } else {
            return;
        }

        if ( empty( $intent_id ) ) {
            return;
        }

        if ( apply_filters( 'dokan_stripe_express_should_not_process_order_redirect', $order->has_status( [ 'processing', 'completed', 'on-hold' ] ), $order ) ) {
            return;
        }

        $save_payment_method = isset( $_GET['save_payment_method'] ) && 'yes' === sanitize_text_field( wp_unslash( $_GET['save_payment_method'] ) );

        $this->process_order_redirect( $order, $intent_id, $save_payment_method );
    }

    /**
     * Processes order redirect after payment.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     * @param string   $intent_id
     * @param boolean  $save_payment_method
     *
     * @return void
     */
    public function process_order_redirect( $order, $intent_id, $save_payment_method ) {
        if ( ! $order instanceof WC_Order ) {
            return;
        }

        if ( OrderMeta::is_redirect_processed( $order ) ) {
            return;
        }

        Helper::log( "Begin processing redirect payment for order {$order->get_id()} for the amount of {$order->get_total()}" );

        try {
            Payment::process_confirmed_intent( $order, $intent_id, $save_payment_method );
        } catch ( Exception $e ) {
            Helper::log( 'Error: ' . $e->getMessage() );

            /* translators: localized exception message */
            $order->update_status( 'failed', sprintf( __( 'Payment failed: %s', 'dokan' ), $e->getMessage() ) );

            wc_add_notice( $e->getMessage(), 'error' );
            wp_safe_redirect( wc_get_checkout_url() );
            exit;
        }
    }

    /**
     * Handles payment disbursement on order status changed.
     *
     * @since 3.6.1
     *
     * @param int    $order_id
     * @param string $old_status
     * @param string $new_status
     *
     * @return void
     */
    public function handle_payment_disbursement( $order_id, $old_status, $new_status ) {
        // Check whether order status is `completed` or `processing`
        if ( 'completed' !== $new_status && 'processing' !== $new_status ) {
            return;
        }

        // get order
        $order = wc_get_order( $order_id );

        // check if order is a valid WC_Order instance
        if ( ! $order ) {
            return;
        }

        // check if the used payment method was Stripe Express
        if ( $order->get_payment_method() !== Helper::get_gateway_id() ) {
            return;
        }

        /**
         * Filter to modify if payment disbursement is not needed.
         *
         * @since 3.7.17
         *
         * @param bool $disburse_payment
         * @param WC_Order $order
         */
        if ( ! apply_filters( 'dokan_stripe_express_disburse_payment', true, $order ) ) {
            return;
        }

        // Disbursement is not needed for vendor subscription orders.
        if ( Subscription::is_vendor_subscription_order( $order ) ) {
            return;
        }

        if ( ! Helper::is_payment_needed( $order->get_id() ) ) {
            return;
        }

        $disburse_mode = Settings::get_disbursement_mode();

        /*
         * If the disbursement mode isn't updated previously,
         * update it according to the current settings.
         */
        if ( 'processing' !== $old_status && 'completed' !== $old_status ) {
            OrderMeta::update_disburse_mode( $order, $disburse_mode );
            OrderMeta::save( $order );
        }

        /*
         * In case of delayed disbursement mode,
         * we don't need to process it here.
         *
         * @see $this->disburse_delayed_payment()
         */
        if ( 'DELAYED' === $disburse_mode ) {
            return;
        }

        // check if both order status and disburse mode is processing
        if ( 'processing' === $new_status && 'ON_ORDER_PROCESSING' !== $disburse_mode ) {
            return;
        }

        Payment::disburse( $order );
    }

    /**
     * Disburses delayed payment.
     *
     * Adds order to queue for payments
     * that needs to be disbursed.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function disburse_delayed_payment() {
        $time_now       = dokan_current_datetime()->setTime( 23, 59, 59 );
        $interval_days  = Settings::get_disbursement_delay_period();

        if ( $interval_days > 0 ) {
            $interval       = new \DateInterval( "P{$interval_days}D" );
            $time_now       = $time_now->sub( $interval );
        }

        add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', [ $this, 'add_order_query_vars_for_delayed_disbursement' ], 10, 2 );
        $query = new \WC_Order_Query(
            [
                'dokan_stripe_express_delayed_disbursement' => true,
                'date_created'                              => '<=' . $time_now->getTimestamp(),
                'status'                                    => [ 'wc-processing', 'wc-completed' ],
                'type'                                      => 'shop_order',
                'limit'                                     => -1,
                'return'                                    => 'ids',
            ]
        );
        $orders = $query->get_orders();
        remove_filter( 'woocommerce_order_data_store_cpt_get_orders_query', [ $this, 'add_order_query_vars_for_delayed_disbursement' ], 10 );

        $bg_process = dokan_pro()->module->stripe_express->delay_disburse;
        if ( ! $bg_process instanceof DelayedDisbursement ) {
            return;
        }

        foreach ( $orders as $order_id ) {
            $bg_process->push_to_queue(
                [
                    'order_id' => $order_id,
                ]
            );
        }

        $bg_process->save()->dispatch();
    }

    /**
     * Adds metadata param for
     * orders with delayed disbursements.
     *
     * @param $query
     * @param $query_vars
     *
     * @since 3.6.1
     *
     * @return mixed
     */
    public function add_order_query_vars_for_delayed_disbursement( $query, $query_vars ) {
        if ( empty( $query_vars['dokan_stripe_express_delayed_disbursement'] ) ) {
            return $query;
        }

        $query['meta_query'][] = [
            'key'     => OrderMeta::disburse_mode_key(),
            'value'   => 'DELAYED',
            'compare' => '=',
        ];

        $query['meta_query'][] = [
            'key'     => OrderMeta::transfer_id_key(),
            'compare' => 'NOT EXISTS',
        ];

        $query['meta_query'][] = [
            'key'     => '_payment_method',
            'value'   => Helper::get_gateway_id(),
            'compare' => '=',
        ];

        return $query;
    }

    /**
     * Retrieves order processing fees for stripe.
     *
     * @since 3.6.1
     *
     * @param float     $processing_fee
     * @param \WC_Order $order
     *
     * @return float
     */
    public function get_order_processing_fee( $processing_fee, $order ) {
        if ( Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return $processing_fee;
        }

        if ( ! Helper::is_payment_needed( $order->get_id() ) ) {
            return 0;
        }

        $stripe_processing_fee = OrderMeta::get_dokan_gateway_fee( $order );

        if ( ! $stripe_processing_fee ) {
            // During processing vendor payment we save stripe fee in parent order
            $stripe_processing_fee = OrderMeta::get_stripe_fee( $order );
        }

        return $stripe_processing_fee > 0 ? $stripe_processing_fee : $processing_fee;
    }

    /**
     * Calculates gateway fee for a suborder.
     *
     * @since 3.6.1
     *
     * @param float     $gateway_fee
     * @param \WC_Order $suborder
     * @param \WC_Order $order
     *
     * @return float|int
     */
    public function get_processing_gateway_fee( $gateway_fee, $suborder, $order ) {
        if ( Helper::get_gateway_id() === $order->get_payment_method() ) {
            $order_processing_fee = dokan()->commission->get_processing_fee( $order );
            $gateway_fee          = OrderProcessor::get_fee_for_suborder( $order_processing_fee, $suborder, $order );
        }

        return wc_format_decimal( $gateway_fee, 2 );
    }

    /**
     * Retrieves net earning of a vendor.
     *
     * @since 3.6.1
     *
     * @param float     $net_amount
     * @param float     $vendor_earning
     * @param float     $gateway_fee
     * @param \WC_Order $suborder
     * @param \WC_Order $order
     *
     * @return void
     */
    public function get_vendor_net_amount( $net_amount, $vendor_earning, $gateway_fee, $suborder, $order ) {
        if (
            Helper::get_gateway_id() === $order->get_payment_method() &&
            'seller' !== OrderMeta::get_gateway_fee_paid_by( $suborder )
        ) {
            $net_amount = $vendor_earning;
        }

        return wc_format_decimal( $net_amount, 2 );
    }
}
