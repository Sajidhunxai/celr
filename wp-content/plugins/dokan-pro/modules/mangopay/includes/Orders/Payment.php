<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Orders;

use WeDevs\DokanPro\Modules\MangoPay\Support\Meta;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;
use WeDevs\DokanPro\Modules\MangoPay\Processor\Order;
use WeDevs\DokanPro\Modules\MangoPay\Processor\PayIn;
use WeDevs\DokanPro\Modules\MangoPay\Support\Settings;
use WeDevs\DokanPro\Modules\MangoPay\BackgroundProcess\DelayedDisbursement;
use WeDevs\DokanPro\Modules\MangoPay\BackgroundProcess\FailedPayoutsDisbursement;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Refunds handler class
 *
 * @since 3.5.0
 */
class Payment {

    // phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

    /**
     * Refund constructor.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * Registers all the hooks
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function hooks() {
        // Process order status after order payment completed
        add_action( 'template_redirect', array( $this, 'redirect_order' ), 1, 1 );
        // Do fund transfers when an order status changes
        add_action( 'woocommerce_order_status_changed', array( $this, 'handle_disbursement' ), 10, 3 );
        // When order received, on thankyou page, display bankwire references if necessary
        add_action( 'woocommerce_thankyou_' . Helper::get_gateway_id(), array( $this, 'display_bankwire_ref' ) );
        // Hook for schedule to maintain delayed payment
        add_action( 'dokan_mangopay_daily_schedule', array( $this, 'disburse_delayed_payment' ) );
        // Hook for schedule to maintain failed payouts
        add_action( 'dokan_mangopay_daily_schedule', array( $this, 'disburse_failed_payouts' ) );
    }

    /**
     * Performs payment disbursement
     * according to settings when
     * order status is changed.
     *
     * @since 3.5.0
     *
     * @param int    $order_id
     * @param string $old_status
     * @param string $new_status
     *
     * @return mixed
     */
    public function handle_disbursement( $order_id, $old_status, $new_status ) {
        // Check whether order status is `completed` or `processing`
        if ( 'completed' !== $new_status && 'processing' !== $new_status ) {
            return;
        }

        $disburse_mode = Settings::get_disbursement_mode();

        // check if both order status and disburse mode is completed
        if ( 'completed' === $new_status && 'ON_ORDER_COMPLETED' !== $disburse_mode ) {
            return;
        }

        // check if both order status and disburse mode is processing
        if ( 'processing' === $new_status && 'ON_ORDER_PROCESSING' !== $disburse_mode ) {
            return;
        }

        // get order
        $order = wc_get_order( $order_id );

        // check if order is a valid WC_Order instance
        if ( ! $order ) {
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
        if ( ! apply_filters( 'dokan_mangopay_disburse_payment', true, $order ) ) {
            return;
        }

        // check payment gateway used was mangopay
        if ( $order->get_payment_method() !== Helper::get_gateway_id() ) {
            return;
        }

        // check already disbursed or not
        if ( Meta::is_withdraw_balance_added( $order ) ) {
            return;
        }

        // finally call api to disburse fund to vendor
        Order::disburse_payment( $order_id );
    }

    /**
     * This method will add queue for payments
     * that needs to be disbursed.
     *
     * @since 3.5.0
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

        add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', array( $this, 'handle_custom_query_var' ), 10, 2 );
        $query = new \WC_Order_Query(
            array(
                'dokan_mangopay_delayed_disbursement' => true,
                'date_created'                        => '<=' . $time_now->getTimestamp(),
                'status'                              => [ 'wc-processing', 'wc-completed' ],
                'type'                                => 'shop_order',
                'limit'                               => -1,
                'return'                              => 'ids',
            )
        );
        $orders = $query->get_orders();
        remove_filter( 'woocommerce_order_data_store_cpt_get_orders_query', array( $this, 'handle_custom_query_var' ), 10 );

        $bg_class = dokan_pro()->module->mangopay->delay_disburse;
        if ( ! $bg_class instanceof DelayedDisbursement ) {
            return;
        }

        foreach ( $orders as $order_id ) {
            $bg_class->push_to_queue( array( 'order_id' => $order_id ) );
        }

        $bg_class->save()->dispatch();
    }

    /**
     * This method will add metadata param
     *
     * @param $query
     * @param $query_vars
     *
     * @since 3.5.0
     *
     * @return mixed
     */
    public function handle_custom_query_var( $query, $query_vars ) {
        if ( empty( $query_vars['dokan_mangopay_delayed_disbursement'] ) ) {
            return $query;
        }

        $query['meta_query'][] = array(
            'key'     => '_dokan_mangopay_disbursement_mode',
            'value'   => 'DELAYED',
            'compare' => '=',
        );

        $query['meta_query'][] = array(
            'key'     => '_dokan_mangopay_withdraw_balance_added',
            'value'   => 'yes',
            'compare' => '!=',
        );

        return $query;
    }


    /**
     * This method will add queue for payments
     * that needs to be disbursed regarding all
     * failed payouts.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function disburse_failed_payouts() {
        $bg_class = dokan_pro()->module->mangopay->disburse_failed_payouts;
        if ( ! $bg_class instanceof FailedPayoutsDisbursement ) {
            return;
        }

        $failed_payouts = Meta::get_failed_payouts();
        $current_time   = dokan_current_datetime()->getTimestamp();

        foreach ( $failed_payouts as $order_id => $payout ) {
            // We will try maximum 5 times
            if ( 5 <= (int) $payout['total_attempt'] ) {
                continue;
            }

            $payout_attempted_in_hours = ( $current_time - (int) $payout['last_attempt'] ) / 60 / 60;
            if ( 24 > $payout_attempted_in_hours ) {
                continue;
            }

            $order = wc_get_order( $order_id );
            if ( ! $order instanceof \WC_Order ) {
                continue;
            }

            $bg_class->push_to_queue( $payout );
        }

        $bg_class->save()->dispatch();
    }

    /**
     * Performs payin validation on order redirect.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function redirect_order() {
        if ( ! is_checkout() ) {
            return;
        }

        global $wp;
        if ( empty( $wp->query_vars['order-received'] ) ) {
            return;
        }

        if ( empty( $_SERVER['REQUEST_URI'] ) ) {
            return;
        }

        $order_id = basename( parse_url( intval( wp_unslash( $_SERVER['REQUEST_URI'] ) ), PHP_URL_PATH ) );
        $order    = wc_get_order( $order_id );

        if ( ! $order ) {
            return;
        }

        if ( Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return;
        }

        if ( 'card' !== Meta::get_payment_type( $order ) ) {
            return;
        }

        $payment_ref = Meta::get_payment_ref( $order );

        if ( empty( $payment_ref ) || empty( $payment_ref['transaction_id'] ) ) {
            return;
        }

        $transaction = Payin::get( $payment_ref['transaction_id'] );

        if ( empty( $transaction ) || ! is_object( $transaction ) ) {
            return;
        }

        if ( 'FAILED' === $transaction->Status ) {
            /* translators: error message */
            $message = sprintf( __( '%s', 'dokan' ), $transaction->ResultMessage ); // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings
            wc_add_notice( '<span class="cancelmessagealone">' . $message . '</span>', 'error' );
            $order->update_status( 'failed', $message );
            $redirect_url = $order->get_cancel_order_url_raw();
            wp_safe_redirect( $redirect_url );
        }
    }

    /**
     * Display bankwire ref at top of thankyou page
     * when new order received via bankwire
     *
     * @since 3.5.0
     *
     * @param int $order_id
     *
     * @return void
     */
    public function display_bankwire_ref( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        if ( 'bank_wire' !== Meta::get_payment_type( $order ) ) {
            return;
        }

        $payment_ref = Meta::get_payment_ref( $order );
        if ( ! is_object( $payment_ref ) || ! isset( $payment_ref->PaymentDetails ) ) {
            return;
        }

        $address = array( $payment_ref->PaymentDetails->BankAccount->OwnerAddress->AddressLine1 );

        if ( ! empty( $payment_ref->PaymentDetails->BankAccount->OwnerAddress->AddressLine2 ) ) {
            $address[] = $payment_ref->PaymentDetails->BankAccount->OwnerAddress->AddressLine2;
        }

        $address[] = $payment_ref->PaymentDetails->BankAccount->OwnerAddress->City;
        $address[] = $payment_ref->PaymentDetails->BankAccount->OwnerAddress->PostalCode;

        if ( ! empty( $payment_ref->PaymentDetails->BankAccount->OwnerAddress->Region ) ) {
            $address[] = $payment_ref->PaymentDetails->BankAccount->OwnerAddress->Region;
        }

        $address[] = $payment_ref->PaymentDetails->BankAccount->OwnerAddress->Country;

        Helper::get_template(
            'bankwire-reference',
            array(
                'amount'   => $payment_ref->PaymentDetails->DeclaredDebitedFunds->Amount / 100,
                'currency' => $payment_ref->PaymentDetails->DeclaredDebitedFunds->Currency,
                'owner'    => $payment_ref->PaymentDetails->BankAccount->OwnerName,
                'iban'     => $payment_ref->PaymentDetails->BankAccount->Details->IBAN,
                'bic'      => $payment_ref->PaymentDetails->BankAccount->Details->BIC,
                'wire_ref' => $payment_ref->PaymentDetails->WireReference,
                'address'  => implode( ', ', $address ),
            )
        );
    }

    // phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
}
