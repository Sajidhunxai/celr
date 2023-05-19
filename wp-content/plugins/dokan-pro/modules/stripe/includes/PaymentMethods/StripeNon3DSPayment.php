<?php

namespace WeDevs\DokanPro\Modules\Stripe\PaymentMethods;

use Exception;
use Stripe\Token;
use Stripe\Charge;
use Stripe\BalanceTransaction;
use WeDevs\DokanPro\Modules\Stripe\Customer;
use WeDevs\DokanPro\Modules\Stripe\Helper;
use WeDevs\DokanPro\Modules\Stripe\StripeConnect;
use WeDevs\DokanPro\Modules\Stripe\Interfaces\Payable;

class StripeNon3DSPayment extends StripeConnect implements Payable {

    /**
     * Hold all the data of order object.
     *
     * @var \WC_Order
     */
    protected $order = null;

    /**
     * Constructor method
     *
     * @since 3.0.3
     *
     * @param \WC_Order
     *
     * @return void
     */
    public function __construct( $order ) {
        $this->order = $order;
        parent::__construct();
    }

    /**
     * Pay for the order
     *
     * @return array
     * @since 3.0.3
     */
    public function pay() {
        $order         = $this->order;
        $customer_id   = get_current_user_id();

        try {
            $this->validate_minimum_order_amount( $order );

            $stripe_customer = ! empty( $_POST['dokan_stripe_customer_id'] ) ? wc_clean( wp_unslash( $_POST['dokan_stripe_customer_id'] ) ) : null; // phpcs:ignore WordPress.Security.NonceVerification

            $force_save_source = false;
            if ( Helper::is_subscription_order( $order ) || Helper::has_subscription( $order->get_id() ) ) {
                $force_save_source = true;
            }
            $prepared_source = $this->prepare_source( $customer_id, $force_save_source, $stripe_customer );
            $this->validate_source( $prepared_source );

            if ( Helper::is_subscription_order( $order ) ) {
                $product_pack = Helper::get_subscription_product_by_order( $order );
                $subscription = dokan()->subscription->get( $product_pack->get_id() );

                if ( ! $subscription->is_recurring() ) {
                    $currency   = strtolower( get_woocommerce_currency() );
                    /* translators: 1) site name, 2) Order number */
                    $order_desc = sprintf( __( '%1$s - Order %2$s', 'dokan' ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() );
                    $charge     = Charge::create(
                        [
                            'amount'      => Helper::get_stripe_amount( $order->get_total() ),
                            'currency'    => $currency,
                            'description' => $order_desc,
                            'customer'    => $prepared_source->customer,
                        ]
                    );

                    $order->add_order_note(
                        sprintf(
                        /* translators: 1) Order number, 2) Payment gateway title 3) stripe charge id */
                            __( 'Order %1$s payment completed via %2$s on Charge ID: %3$s', 'dokan' ),
                            $order->get_order_number(),
                            $this->get_title(),
                            $charge->id
                        )
                    );
                    $order->payment_complete();
                }

                do_action( 'dokan_process_subscription_order', $order, $prepared_source, $subscription->is_recurring() );
            } else {
                $this->process_seller_payment( $order, $prepared_source );
            }
        } catch ( Exception $e ) {
            /* translators: 1) Error message from stripe */
            $order->add_order_note( sprintf( __( 'Stripe Payment Error: %s', 'dokan' ), $e->getMessage() ) );
            update_post_meta( $order->get_id(), '_dwh_stripe_charge_error', $e->getMessage() );
            wc_add_notice( __( 'Error: ', 'dokan' ) . $e->getMessage(), 'error' );
            return array(
                'result'   => 'fail',
                'redirect' => '',
            );
        }

        $response = [
            'result'   => 'success',
            'redirect' => $this->get_return_url( $order ),
        ];

        return $response;
    }

    /**
     * Process Seller payment
     *
     * @return void
     * @throws \Stripe\Exception\ApiErrorException
     * @throws \WeDevs\Dokan\Exceptions\DokanException
     * @since 1.3.3
     */
    public function process_seller_payment( $order, $prepared_source ) {
        $currency      = strtolower( get_woocommerce_currency() );
        $charge_ids    = [];
        $all_withdraws = [];
        $all_orders    = $this->get_all_orders_to_be_processed( $order );

        if ( ! $all_orders ) {
            throw new Exception( __( 'No orders found to process!', 'dokan' ) );
        }

        // no matter what we need to store source to stripe customer object if customer used a new card to make payments
        $added_source_id = $this->add_source_to_customer( $prepared_source );

        foreach ( $all_orders as $tmp_order ) {
            $tmp_order_id = $tmp_order->get_id();
            $seller_id    = dokan_get_seller_id_by_order( $tmp_order_id );
            $dokan_order  = $this->get_dokan_order( $tmp_order_id, $seller_id );

            if ( ! $dokan_order ) {
                throw new Exception( __( 'Something went wrong and the order can not be processed!', 'dokan' ) );
            }

            $order_total     = (float) $dokan_order->order_total;
            $application_fee = $order_total - (float) $dokan_order->net_amount;
            $vendor_earning  = $order_total - $application_fee;

            if ( (float) $dokan_order->order_total === 0 ) {
                /* translators: 1) order number */
                $tmp_order->add_order_note( sprintf( __( 'Order %s payment completed', 'dokan' ), $tmp_order->get_order_number() ) );
                continue;
            }

            $access_token = get_user_meta( $seller_id, '_stripe_connect_access_key', true );

            if ( ! empty( $access_token ) ) {
                // if it's guest user, try to create a token
                try {
                    $token = Token::create( [ 'customer' => $prepared_source->customer ], $access_token );
                } catch ( Exception $exception ) {
                    if ( Helper::is_customer_without_source_error( $exception->getMessage() ) && $prepared_source->token_id ) {
                        throw new Exception( __( 'Saved payment method won\'t work for this purchase. Please provide a new card details and try again.', 'dokan' ) );
                    }
                    throw $exception;
                }
            } elseif ( Helper::allow_non_connected_sellers() ) {
                $token = false;
            } else {
                throw new Exception( __( 'Unable to process with Stripe gateway', 'dokan' ) );
            }

            if ( $order->get_id() === $tmp_order_id ) {
                /* translators: 1) site name 2) order number */
                $order_desc = sprintf( __( '%1$s - Order %2$s', 'dokan' ), esc_html( get_bloginfo( 'name' ) ), $order->get_order_number() );
            } else {
                /* translators: 1) site name 2) sub order number 3) parent order number */
                $order_desc = sprintf( __( '%1$s - Order %2$s, suborder of %3$s', 'dokan' ), esc_html( get_bloginfo( 'name' ) ), $tmp_order->get_order_number(), $order->get_order_number() );
            }

            if ( $token ) {
                $charge = Charge::create(
                    [
                        'amount'          => Helper::get_stripe_amount( $order_total ),
                        'currency'        => $currency,
                        'application_fee' => Helper::get_stripe_amount( $application_fee ),
                        'description'     => $order_desc,
                        'source'          => ! empty( $token->id ) ? $token->id : $prepared_source->source,
                    ], $access_token
                );
                $tmp_order->update_meta_data( '_dokan_charge_captured_account', 'seller' ); // this meta will determine which account is responsible for refund.
            } else {
                $charge = Charge::create(
                    [
                        'amount'      => Helper::get_stripe_amount( $order_total ),
                        'currency'    => $currency,
                        'description' => $order_desc,
                        'customer'    => $prepared_source->customer,
                    ]
                );

                $tmp_order->add_order_note( sprintf( __( 'Vendor payment transferred to admin account since the vendor had not connected to Stripe.', 'dokan' ) ) );
                $tmp_order->update_meta_data( '_dokan_charge_captured_account', 'admin' );  // this meta will determine which account is responsible for refund.
            }

            $charge_ids[ $seller_id ] = $charge->id;
            update_post_meta( $tmp_order_id, $this->stripe_meta_key . $seller_id, $charge->id );

            if ( $order->get_id() !== $tmp_order_id && $token ) {
                $tmp_order->add_order_note(
                    sprintf(
                    /* translators: 1) sub order number 2) payment gateway title 3) stripe charge id */
                        __( 'Order %1$s payment completed via %2$s on Charge ID: %3$s', 'dokan' ),
                        $tmp_order->get_order_number(),
                        $this->get_title(),
                        $charge->id
                    )
                );
            }

            if ( ! empty( $token ) && $charge->balance_transaction ) {
                $balance_transaction = BalanceTransaction::retrieve( $charge->balance_transaction, $access_token );
            } elseif ( $charge->balance_transaction ) {
                $balance_transaction = BalanceTransaction::retrieve( $charge->balance_transaction );
            }

            if ( $balance_transaction ) {
                $fee            = Helper::format_gateway_balance_fee( $balance_transaction );
                $vendor_earning = $vendor_earning - $fee;

                update_post_meta( $tmp_order_id, 'dokan_gateway_stripe_fee', $fee );
                $tmp_order->update_meta_data( 'dokan_gateway_fee_paid_by', 'seller' );
            }

            $tmp_order->save_meta_data();

            // Only process withdraw request once vendor get paid.
            if ( ! empty( $token ) ) {
                $withdraw_data = [
                    'user_id'  => $seller_id,
                    'amount'   => wc_format_decimal( $vendor_earning, 4 ),
                    'order_id' => $tmp_order_id,
                ];

                $all_withdraws[] = $withdraw_data;
            }
        }

        $order->add_order_note(
            sprintf(
            /* translators: 1) order number 2) payment gateway title 3) stripe charge ids */
                __( 'Order %1$s payment is completed via %2$s on (Charge ID: %3$s)', 'dokan' ),
                $order->get_order_number(),
                $this->get_title(),
                implode( ', ', $charge_ids )
            )
        );

        $order->save_meta_data();
        $order->payment_complete();
        $this->insert_into_vendor_balance( $all_withdraws );
        $this->process_seller_withdraws( $all_withdraws );

        foreach ( $charge_ids as $seller_id => $charge_id ) {
            $meta_key = $this->stripe_meta_key . $seller_id;
            update_post_meta( $order->get_id(), $meta_key, $charge_id );
        }

        if ( ! empty( $added_source_id ) ) {
            $this->delete_source_from_customer( $prepared_source->customer, $added_source_id );
        }
    }

    /**
     * This method will add a source to a stripe customer
     *
     * @since 3.2.4
     *
     * @param $prepared_source
     *
     * @throws \WeDevs\Dokan\Exceptions\DokanException
     *
     * @return null|string
     */
    private function add_source_to_customer( $prepared_source ) {
        if ( empty( $prepared_source->token_id ) && ! empty( $prepared_source->source ) && ! empty( $prepared_source->customer ) && ! $prepared_source->token_saved ) {
            $customer = new Customer();
            $customer->set_id( $prepared_source->customer );
            try {
                return $customer->add_source_only( $prepared_source->source );
            } catch ( Exception $exception ) {
                dokan_log( 'add_source_to_customer error: ' . print_r( $exception, true ) );
            }
        } elseif ( ! empty( $prepared_source->token_id ) && ! empty( $prepared_source->customer ) ) {
            $customer = new Customer();
            $customer->set_id( $prepared_source->customer );
            $customer->set_default_source( $prepared_source->source );
        }
        return null;
    }

    /**
     * This method will delete a source from a stripe customer
     *
     * @since 3.2.4
     *
     * @param $prepared_source
     */
    private function delete_source_from_customer( $customer_id, $added_source_id ) {
        $customer = new Customer();
        $customer->set_id( $customer_id );
        $customer->delete_source( $added_source_id );
    }
}
