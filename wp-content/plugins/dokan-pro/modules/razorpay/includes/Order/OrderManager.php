<?php

namespace WeDevs\DokanPro\Modules\Razorpay\Order;

use WP_Error;
use WeDevs\Dokan\Cache;
use Razorpay\Api\Errors\BadRequestError;
use WeDevs\DokanPro\Modules\Razorpay\Helper;
use WeDevs\DokanPro\Modules\Razorpay\Cart\CartHandler;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class OrderManager.
 *
 * @package WeDevs\Dokan\Gateways\Razorpay
 *
 * @since 3.5.0
 */
class OrderManager {
    /**
     * Generate Order Unit data for razorpay order.
     *
     * @since 3.5.0
     *
     * @see https://razorpay.com/docs/api/orders/
     *
     * @param int $order_id
     *
     * @return array
     */
    public static function make_order_unit_data( $order_id ) {
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return [];
        }

        $subtotal       = $order->get_subtotal();
        $tax_total      = static::get_tax_amount( $order );
        $shipping_total = wc_format_decimal( $order->get_shipping_total(), 2 );
        $platform_fee   = static::get_total_admin_commission( $order );

        // if tax fee recipient is 'admin' then it will added with platform fee
        if ( 'admin' === get_post_meta( $order->get_id(), 'tax_fee_recipient', true ) ) {
            $subtotal  += $tax_total;
            $tax_total  = 0.00;
        }

        // if shipping fee recipient is 'admin' then it will added with platform fee
        if ( 'admin' === get_post_meta( $order->get_id(), 'shipping_fee_recipient', true ) ) {
            $subtotal       += $shipping_total;
            $shipping_total = 0.00;
        }

        // calculate discounts
        $total_discount = $order->get_total_discount() + static::get_minimum_order_discount( $order );

        // Prepare Order Data
        $data = [
            'receipt'         => $order->get_id(),
            'amount'          => Helper::format_amount( $order->get_total() ),
            'currency'        => $order->get_currency(),
            'payment_capture' => 1, // This means, once the payment is successful, the amount will be captured from the customer's account.
            'app_offer'       => $total_discount > 0 ? 1 : 0,
            'notes'           => [ // Max 15 notes of 256 characters per note.
                'dokan_order_id' => $order->get_id(),
                'total_discount' => wc_format_decimal( $total_discount, 2 ),
                'item_total'     => wc_format_decimal( $subtotal, 2 ),
                'shipping_total' => wc_format_decimal( $shipping_total, 2 ),
                'tax_total'      => wc_format_decimal( $tax_total, 2 ),
                'platform_fee'   => wc_format_decimal( $platform_fee, 2 ),
            ],
        ];

        return $data;
    }

    /**
     * Get tax amount.
     *
     * @since 3.5.0
     *
     * @param $order
     *
     * @return float
     */
    public static function get_tax_amount( \WC_Order $order ) {
        $tax_amount = 0;
        foreach ( $order->get_taxes() as $tax ) {
            $tax_amount += $tax['tax_total'] + $tax['shipping_tax_total'];
        }

        return wc_format_decimal( $tax_amount, 2 );
    }

    /**
     * Get Minimum Order Discount.
     *
     * @since 3.5.0
     *
     * @param \WC_Order $order
     *
     * @return float
     */
    public static function get_minimum_order_discount( \WC_Order $order ) {
        // check discount exists on order meta
        $discount = $order->get_meta( 'dokan_order_discount' );
        if ( $discount ) {
            return floatval( $discount );
        }
        return 0;
    }

    /**
     * Handle vendor balance and withdraw request.
     *
     * @since 3.5.0
     *
     * @param array $all_withdraws
     *
     * @return bool
     */
    public static function handle_vendor_balance( $all_withdraws ) {
        foreach ( $all_withdraws as $withdraw_data ) {
            static::insert_vendor_withdraw_balance( $withdraw_data );
        }

        return true;
    }

    /**
     * This method will entry vendors withdraw and balance entries.
     *
     * @since 3.5.0
     *
     * @param array $withdraw
     * @param bool  $insert_now
     *
     * @return WP_Error|bool true on success, WP_Error instance otherwise
     */
    public static function insert_vendor_withdraw_balance( &$withdraw, $insert_now = false ) {
        // check if order id exists
        if ( ! $withdraw['order_id'] ) {
            return new WP_Error( 'insert_vendor_withdraw_balance_error', sprintf( '[insert_vendor_withdraw_balance] Invalid order id. data: %1$s', print_r( $withdraw, true ) ) );
        }

        // check disbursement mode
        if ( false === $insert_now && get_post_meta( $withdraw['order_id'], '_dokan_razorpay_payment_disbursement_mode', true ) !== 'INSTANT' ) {
            // don't insert withdraw balance, store withdraw data as order meta
            update_post_meta( $withdraw['order_id'], '_dokan_razorpay_payment_withdraw_data', $withdraw );
            update_post_meta( $withdraw['order_id'], '_dokan_razorpay_payment_withdraw_balance_added', 'no' );
            return true;
        }

        // check if withdraw data is already inserted
        if ( 'yes' === get_post_meta( $withdraw['order_id'], '_dokan_razorpay_payment_withdraw_balance_added', true ) ) {
            return true;
        }

        // Check if vendor is connected or not
        $connected_vendor_id = Helper::get_seller_account_id( $withdraw['user_id'] );

        if ( ! $connected_vendor_id ) {
            return;
        }

        // Withdraw balance
        $withdraw_data_inserted = static::process_seller_withdraws( $withdraw );

        if ( false === $withdraw_data_inserted ) {
            return $withdraw_data_inserted;
        }

        // Update order meta that payment and withdraw balance is added
        update_post_meta( $withdraw['order_id'], '_dokan_razorpay_payment_withdraw_balance_added', 'yes' );

        return true;
    }

    /**
     * Automatically process withdrawal for sellers per order.
     *
     * @since 3.5.0
     *
     * @param array $all_withdraws
     *
     * @return void
     */
    protected static function process_seller_withdraws( $withdraw ) {
        if ( empty( $withdraw ) ) {
            return;
        }

        // Reconcile withdraw and dokan order balance date that was previously tempered when payment was creating.
        self::process_withdraw_balance_threshold( $withdraw['order_id'], dokan_current_datetime()->format( 'Y-m-d h:i:s' ), $withdraw['user_id'], 'dokan_orders' );
        self::process_withdraw_balance_threshold( $withdraw['order_id'], dokan_current_datetime()->format( 'Y-m-d h:i:s' ), $withdraw['user_id'], 'dokan_withdraw' );

        $ip = dokan_get_client_ip();
        /* translators: 1: Order ID, 2: Gateway Name */
        $note = sprintf( __( 'Order %1$d payment Auto paid via %2$s', 'dokan' ), $withdraw['order_id'], Helper::get_gateway_title() );

        $data = [
            'date'    => current_time( 'mysql' ),
            'status'  => 1,
            'method'  => Helper::get_gateway_id(),
            'notes'   => $note,
            'details' => $note,
            'ip'      => $ip,
        ];

        $data = array_merge( $data, $withdraw );

        return dokan()->withdraw->insert_withdraw( $data );
    }

    /**
     * Get Order wise Refund.
     *
     * @since 3.5.0
     *
     * @param \WC_Order|int $order
     *
     * @return array
     */
    public static function get_refund_ids_by_order( $order ) {
        if ( is_numeric( $order ) ) {
            $order = wc_get_order( $order );
        }

        if ( ! $order instanceof \WC_Order ) {
            return [];
        }

        $refund_ids = $order->get_meta( '_dokan_razorpay_refund_id' );
        if ( $refund_ids === '' ) {
            $refund_ids = [];
        } elseif ( ! is_array( $refund_ids ) && $refund_ids !== '' ) {
            $refund_ids = (array) $refund_ids;
        }

        return $refund_ids;
    }

    /**
     * Disburse delayed payment.
     *
     * If comes here, it means order could be in delayed or on_order_complete mode
     *
     * @see https://razorpay.com/docs/api/route/#modify-settlement-hold-for-transfers
     *
     * @since 3.5.0
     *
     * @param \WC_Order $order
     *
     * @return void
     */
    public static function _disburse_payment( $order ) { //phpcs:ignore PSR2.Methods.MethodDeclaration.Underscore
        // check if this is a valid WC_Order object
        if ( ! $order instanceof \WC_Order ) {
            return;
        }

        // Get withdraw data from order meta
        $withdraw_data = $order->get_meta( '_dokan_razorpay_payment_withdraw_data' );

        try {
            // Update `on_hold` status to false on razorpay-end, as we're making the disbursement now
            $transfer_id = $order->get_meta( '_dokan_razorpay_transfer_id' );
            if ( empty( $transfer_id ) ) {
                return;
            }

            $api = Helper::init_razorpay_api();
            $api->transfer->fetch( $transfer_id )->edit(
                [
                    'on_hold' => false,
                ]
            );

            $response = static::insert_vendor_withdraw_balance( $withdraw_data, true );

            if ( is_wp_error( $response ) ) {
                $order->add_order_note(
                    /* translators: 1: Payment Gateway Title, 2: Error message from gateway */
                    sprintf( __( '[%1$s] Inserting into vendor balance failed. Error Message: %2$s', 'dokan' ), Helper::get_gateway_title(), Helper::get_error_message( $response ) )
                );
            } else {
                $order->add_order_note(
                    /* translators: 1: Payment Gateway Title */
                    sprintf( __( '[%s] Successfully disbursed fund to the vendor.', 'dokan' ), Helper::get_gateway_title() )
                );
            }
        } catch ( BadRequestError $bad_request_error ) {
            dokan_log( 'Dokan Razorpay Disburse Payment: ' . $bad_request_error->getMessage(), 'error' );
        } catch ( \Exception $e ) {
            dokan_log( 'Dokan Razorpay Disburse Payment: ' . $e->getMessage(), 'error' );
        }
    }

    /**
     * Create an order in razorpay-end.
     *
     * Find the associated Razorpay Order from the session and verify that is is still correct.
     * If not found (or incorrect), create a new Razorpay Order.
     *
     * @see https://razorpay.com/docs/api/orders/#create-an-order
     *
     * @since 3.5.0
     *
     * @param int $order_id Order Id
     *
     * @return int|WP_Error Razorpay Order Id or WP_Error
     */
    public static function create_razorpay_order( $order_id ) {
        $order = wc_get_order( $order_id );

        // Return if no-order found.
        if ( ! $order ) {
            return new WP_Error( 'dokan_invalid_order', __( 'Order not found', 'dokan' ) );
        }

        $razorpay_order_id = get_post_meta( $order_id, '_dokan_razorpay_intent_id', true );
        $is_create_order   = false;
        $razorpay_order    = null;

        // Check validations if razorpay order exists and meets the order amount requirement.
        if ( empty( $razorpay_order_id ) || ( false === OrderValidator::verify_order_amount( $razorpay_order_id, $order_id ) ) ) {
            $is_create_order = true;
        }

        // If no need to create order, return the razorpay order id.
        if ( ! $is_create_order ) {
            return $razorpay_order_id;
        }

        try {
            $data = static::make_order_unit_data( $order_id );
            $api  = Helper::init_razorpay_api();

            // Create razorpay order intent in razorpay-end.
            $razorpay_order = $api->order->create( $data );
        } catch ( BadRequestError $e ) {
            // For the bad request errors, it's safe to show the message to the customer.
            dokan_log( __( '[Dokan Razorpay] create_razorpay_order error: ', 'dokan' ) . $e->getMessage(), 'error' );
            return new WP_Error( 'bad_request', $e->getMessage() );
        } catch ( \Exception $e ) {
            // For any other exceptions, we make sure that the error message
            // does not propagate to the front-end.
            dokan_log( __( '[Dokan Razorpay] create_razorpay_order error: ', 'dokan' ) . $e->getMessage(), 'error' );
            return new WP_Error( 'create_razorpay_order_error', __( 'Razorpay order could not be created. Please try again.', 'dokan' ) );
        }

        // Get Razorpay Order ID if there is no error
        $razorpay_order_id = $razorpay_order['id'];

        // Save this order intent ID in order meta.
        self::save_razorpay_intent_to_order( $order, $razorpay_order );

        return $razorpay_order_id;
    }

    /**
     * Save the razorpay intent to the order.
     *
     * @since 3.5.0
     *
     * @param \WC_Order $order
     * @param object    $razorpay_order
     *
     * @return void
     */
    protected static function save_razorpay_intent_to_order( \WC_Order $order, $razorpay_order ) {
        if ( ! isset( $razorpay_order['id'] ) ) {
            return;
        }

        if ( ! $order instanceof \WC_Order ) {
            return;
        }

        $order->update_meta_data( '_dokan_razorpay_intent_id', $razorpay_order['id'] );

        if ( is_callable( [ $order, 'save' ] ) ) {
            $order->save();
        }
    }

    /**
     * Get all Razorpay Order from a given order.
     *
     * This will get all of the suborders also.
     *
     * @since 3.5.0
     *
     * @param \WC_Order $order_id
     */
    public static function get_all_orders_to_be_processed( \WC_Order $order ) {
        $has_suborder = $order->get_meta( 'has_sub_order' );
        $all_orders   = [];

        if ( $has_suborder ) {
            $sub_order_ids = get_children(
                [
                    'post_parent' => $order->get_id(),
                    'post_type'   => 'shop_order',
                    'fields'      => 'ids',
                ]
            );

            foreach ( $sub_order_ids as $sub_order_id ) {
                $sub_order    = wc_get_order( $sub_order_id );
                $all_orders[] = $sub_order;
            }
        } else {
            $all_orders[] = $order;
        }

        return $all_orders;
    }

    /**
     * Update Order status & Notes.
     *
     * @since 3.5.0
     *
     * @param \WC_Order $order
     * @param bool      $is_cancelled, by default false
     * @param string    $razorpay_payment_id
     *
     * @return void
     */
    public static function update_order_status( &$order, $is_cancelled = false, $razorpay_payment_id = null ) {
        if ( ! is_a( $order, 'WC_Abstract_Order' ) ) {
            return;
        }

        // Handle if Order status is cancelled.
        if ( $is_cancelled ) {
            $order->update_status( 'cancelled', __( 'Order cancelled by the customer.', 'dokan' ) );

            CartHandler::maybe_clear_cart();

            wp_safe_redirect( $order->get_cancel_order_url() );
            exit;
        }

        // Handle successfull order.
        if ( $order->needs_payment() && ! empty( $razorpay_payment_id ) ) {
            $order->payment_complete( $razorpay_payment_id );

            $order->add_order_note(
                sprintf(
                    /* translators: 1: Order Number, 2: Gateway Title, 3: Razorpay Payment ID */
                    __( 'Order %1$s payment is completed via %2$s. (Payment ID: %3$s)', 'dokan' ),
                    $order->get_order_number(),
                    Helper::get_gateway_title(),
                    $razorpay_payment_id
                )
            );

            CartHandler::maybe_clear_cart();
        }
    }

    /**
     * Get total admin commission for a given order.
     *
     * @since 3.5.0
     *
     * @param \WC_Order $order
     *
     * @return float
     */
    protected static function get_total_admin_commission( \WC_Order $order ) {
        if ( ! $order->get_meta( 'has_sub_order' ) ) {
            return wc_format_decimal( dokan()->commission->get_earning_by_order( $order, 'admin' ), 2 );
        }

        $sub_order_ids = get_children(
            [
                'post_parent' => $order->get_id(),
                'post_type'   => 'shop_order',
                'fields'      => 'ids',
            ]
        );

        $total_commission = 0;
        foreach ( $sub_order_ids as $sub_order_id ) {
            $total_commission += wc_format_decimal( dokan()->commission->get_earning_by_order( $sub_order_id, 'admin' ), 2 );
        }

        return $total_commission;
    }

    /**
     * Processes vendor withdraw & balance threshold date.
     *
     * @since 3.5.0
     *
     * @param int    $order_id
     * @param string $balance_date
     * @param int    $vendor_id
     * @param string $transaction_type
     *
     * @return int|boolean
     */
    public static function process_withdraw_balance_threshold( $order_id, $balance_date, $vendor_id, $transaction_type = 'dokan_orders' ) {
        global $wpdb;

        // Update threshold balance date
        return $wpdb->update(
            $wpdb->dokan_vendor_balance,
            [
                'balance_date' => $balance_date,
            ],
            [
                'vendor_id' => $vendor_id,
                'trn_id'    => $order_id,
                'trn_type'  => $transaction_type,
            ],
            [
                '%s',
            ],
            [
                '%d',
                '%d',
                '%s',
            ]
        );
    }
}
