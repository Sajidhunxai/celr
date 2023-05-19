<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Processors;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Api\Account;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Settings;

/**
 * Class for processing orders.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Processors
 */
class Balance {

    /**
     * Holds the Stripe balance data.
     *
     * @since 3.7.8
     *
     * @var \Stripe\Balance
     */
    private $balance;

    /**
     * Holds the currency.
     *
     * @since 3.7.8
     *
     * @var string
     */
    private $currency;

    /**
     * Class instance
     *
     * @since 3.7.8
     *
     * @var mixed
     */
    private static $instance = null;

    /**
     * Private constructor for singletone instance.
     *
     * @since 3.7.8
     *
     * @return void
     */
    private function __construct() {}

    /**
     * Sets required data.
     *
     * @since 3.7.8
     *
     * @return static
     */
    public static function set() {
        if ( ! static::$instance ) {
            static::$instance = new static();
        }
        static::$instance->set_data();

        return static::$instance;
    }

    /**
     * Sets balance data.
     *
     * @since 3.7.8
     *
     * @return void
     */
    private function set_data() {
        $this->currency = get_woocommerce_currency();
        $this->balance  = Account::get_balance();
        if ( ! $this->balance || ! $this->balance instanceof \Stripe\Balance ) {
            $this->balance = false;
        }
    }

    /**
     * Retrieves balance data.
     *
     * @since 3.7.8
     *
     * @return \Stripe\Balance|false
     */
    public function get() {
        return $this->balance;
    }

    /**
     * Checks whether balance is available in Stripe for the desired currency.
     *
     * @since 3.7.8
     *
     * @param string $currency (Optional)
     */
    public function is_available( $currency = null ) {
        return 0 < $this->get_available_balance( $currency );
    }

    /**
     * Checks if balance available in Stripe against a order.
     *
     * @since 3.7.8
     *
     * @param int|\WC_Order $order
     * @param string        $context (Optional) Default `seller`, `admin` otherwise
     *
     * @return boolean
     */
    public function is_available_for_order( $order, $context = 'seller' ) {
        if ( is_int( $order ) ) {
            $order = wc_get_order( $order );
        }

        if ( ! $order instanceof \WC_Order ) {
            return false;
        }

        $amount_to_be_available = $order->get_total();

        // The actual amount will be except the stripe processing fee if seller pays.
        if ( 'seller' === $context && Settings::sellers_pay_processing_fees() ) {
            $processing_fee = 0;
            $payment_intent = Payment::get_intent( $order );

            if ( $payment_intent instanceof \Stripe\PaymentIntent ) {
                $processing_fee = Payment::get_stripe_fee( $payment_intent );
            }

            $amount_to_be_available -= $processing_fee;
        }

        return $amount_to_be_available <= $this->get_available_balance( $order->get_currency() );
    }

    /**
     * Retrives available balance in Stripe for the desired currency.
     *
     * @since 3.7.8
     *
     * @param string  $currency      (Optional) Currency for which the available balance will be retrieved.
     * @param boolean $stripe_format (Optional) Determines whether the balance will be returned in Stripe format (i.e., 3220) or in standard format (i.e., 32.20).
     *
     * @return float
     */
    public function get_available_balance( $currency = null, $stripe_format = false ) {
        if ( ! $this->balance ) {
            return 0;
        }

        if ( empty( $currency ) ) {
            $currency = $this->currency;
        }

        /*
         * We need to find the available balance for the given currency.
         * As the available balance consists of an array of balances of
         * different currencies, we need to search for the given currency.
         *
         * The data format of `$available_balances` is like below:
         * [
         *     [
         *         'amount' => 55500,
         *         'currency' => 'usd',
         *         'source_types' => $card_object,
         *     ],
         *     [
         *         'amount' => 3300,
         *         'currency' => 'eur',
         *         'source_types' => $card_object,
         *     ],
         * ]
         */
        $available_balances = (array) $this->balance->available;
        $currency_cols      = array_column( $available_balances, 'currency' );
        $index              = array_search( strtolower( $currency ), $currency_cols, true );

        // A `false` index means the currency does not exist.
        if ( false === $index ) {
            return 0;
        }

        $balance = $available_balances[ $index ]->amount;
        if ( ! $stripe_format ) {
            $balance /= 100;
        }

        return $balance;
    }
}
