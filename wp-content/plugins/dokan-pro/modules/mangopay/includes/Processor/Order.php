<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Processor;

use WC_Order;
use Exception;
use WeDevs\DokanPro\Modules\MangoPay\Support\Meta;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;
use WeDevs\DokanPro\Modules\MangoPay\Support\Processor;

/**
 * Class for processing orders
 *
 * @since 3.5.0
 */
class Order extends Processor {

    /**
     * Switches the order to "Paid" status after validating.
     *
     * @since 3.5.0
     *
     * @param int|string $order_id
     *
     * @return void
     */
    public static function validate( $order_id ) {
        // Get wc order object
        $order = new \WC_Order( $order_id );
        if ( ! $order ) {
            return;
        }

        if ( ! static::config()->is_production() ) {
            $order->add_order_note( __( 'This order has been processed using the Mangopay Sandbox environment.', 'dokan' ) );
        }

        try {
            $order->payment_complete( $order_id );
        } catch ( Exception $e ) {
            Helper::log(
                sprintf(
                    '[%s] Payment complete unsuccessful. Error: %s',
                    current_time( 'Y-m-d H:i:s', 0 ),
                    $e->getMessage()
                )
            );

            Helper::warn_owner(
                sprintf(
                    __( 'Mangopay Payin webhook could not complete payment for order: %1$s. %2$s', 'dokan' ),
                    $order_id,
                    $e->getMessage()
                ),
                $order_id
            );

            return false;
        }
    }

    /**
     * Perform payment disbursement to vendors
     *
     * @since 3.5.0
     *
     * @param int $order_id
     *
     * @return mixed
     */
    public static function disburse_payment( $order_id ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return false;
        }

        // Check if mangopay transaction id exists
        $transaction_id = Meta::get_transaction_id( $order );
        if ( empty( $transaction_id ) ) {
            return false;
        }

        // Find the successful mangopay transaction id
        $mp_success_transaction_id = Meta::get_succeeded_transaction_id( $order );
        if ( ! empty( $mp_success_transaction_id ) ) {
            $transaction_id = $mp_success_transaction_id;
        }

        $transfer_data = self::process_transfer_data( $order, $transaction_id );
        if ( is_wp_error( $transfer_data ) ) {
            return false;
        }

        $mp_transfers = array();
        $mp_payouts   = array();
        $withdraws    = array();

        // Perform the wallet transfers
        foreach ( $transfer_data as $transfer ) {
            // Do not try to perform a wallet transfer when the amount is zero or negative
            if ( floatval( $transfer['amount'] ) <= 0 || floatval( $transfer['fees'] ) < 0 ) {
                continue;
            }

            $sub_order = wc_get_order( $transfer['order_id'] );
            if ( ! $sub_order instanceof WC_Order ) {
                continue;
            }

            if ( ! empty( Meta::get_payout_id( $sub_order ) ) ) {
                continue;
            }

            // Make the wallet transfer
            $transfer_result = Transfer::create(
                $transfer['order_id'],
                $transfer['transaction_id'],
                $transfer['customer_id'],
                $transfer['vendor_id'],
                $transfer['amount'],
                $transfer['fees'],
                $transfer['currency']
            );

            if ( is_wp_error( $transfer_result ) ) {
                continue;
            }

            $mp_transfers[] = $transfer_result;

            if ( 'SUCCEEDED' !== $transfer_result->Status ) {
                continue;
            }

            // Update transfer info
            Meta::update_transfer_id( $sub_order, $transfer_result->Id );
            Meta::update_commision_status( $sub_order, 'paid' );
            Meta::update_commision( $sub_order, $transfer['fees'] );
            $sub_order->save_meta_data();

            // Process vendor balance and withdraw
            self::process_vendor_withdraw( $transfer['withdraw'] );

            // Enter data in failed payouts to process it later in case payout fails
            $transfer['total_attempt'] = isset( $transfer['total_attempt'] ) ? (int) $transfer['total_attempt'] + 1 : 0;
            $transfer['last_attempt']  = dokan_current_datetime()->getTimestamp();
            Meta::update_failed_payouts( $transfer );

            $withdraws[]   = $transfer['withdraw'];
            $mp_account_id = Meta::get_mangopay_account_id( $transfer['vendor_id'] );
            if ( empty( $mp_account_id ) ) {
                continue;
            }

            $payout_result = PayOut::create(
                $transfer['vendor_id'],
                $transfer['order_id'],
                $transfer['currency'],
                $transfer['withdraw']['amount'],
                0
            );

            if ( is_wp_error( $payout_result ) ) {
                continue;
            }

            $mp_payouts[] = $payout_result;

            if (
                ! isset( $payout_result->Status ) ||
                ( 'SUCCEEDED' !== $payout_result->Status && 'CREATED' !== $payout_result->Status )
            ) {
                continue;
            }

            Meta::update_payout_id( $sub_order, $payout_result->Id );
            Meta::update_last_payout_attempt( $sub_order, dokan_current_datetime()->getTimestamp() );
            Meta::update_payout_attempts( $sub_order, isset( $transfer['total_attempt'] ) ? (int) $transfer['total_attempt'] + 1 : 1 );
            Meta::remove_failed_payout( $transfer );
            Meta::update_payouts( $sub_order, $transfer );
            $sub_order->save_meta_data();
        }

        if ( ! empty( $withdraws ) ) {
            Meta::update_withdraw_data( $order, $withdraws );
        }

        if ( ! empty( $mp_transfers ) ) {
            Meta::update_transfers( $order, $mp_transfers );
        }

        $order->save_meta_data();
    }

    /**
     * Retrieves all wallet transfers that need to be performed in an order batch
     *
     * @since 3.5.0
     *
     * @param object $order
     * @param int    $transaction_id
     *
     * @return array
     */
    public static function process_transfer_data( $order, $transaction_id ) {
        $has_suborder = $order->get_meta( 'has_sub_order' );
        $all_orders   = array();

        if ( $has_suborder ) {
            $sub_orders = get_children(
                array(
                    'post_parent' => $order->get_id(),
                    'post_type'   => 'shop_order'
                )
             );

            foreach ( $sub_orders as $sub_order ) {
                $all_orders[] = wc_get_order( $sub_order->ID );
            }
        } else {
            $all_orders[] = $order;
        }

        if ( empty( $all_orders ) ) {
            return new \WP_Error( 'dokan-mangopay-transfer-list-parse-error', __( 'Something went wrong while processing the order', 'dokan' ) );
        }

        $transfer_data    = array();
        $list_to_transfer = array();
        $withdraw_data    = array();

        // Seems like we have some orders to process
        foreach ( $all_orders as $item ) {
            $item_id   = $item->get_id();
            $seller_id = dokan_get_seller_id_by_order( $item_id );

            if ( $has_suborder ) {
                $transaction_id = Meta::get_transaction_id( $item->get_parent_id() );
            }

            $vendor_earning = dokan()->commission->get_earning_by_order( $item, 'seller' );

            // transfer data
            $transfer_data['order_id']       = $item_id;
            $transfer_data['transaction_id'] = $transaction_id;
            $transfer_data['customer_id']    = $item->get_customer_id();
            $transfer_data['amount']         = $item->get_total();
            $transfer_data['fees']           = $item->get_total() - $vendor_earning;
            $transfer_data['currency']       = $item->get_currency();
            $transfer_data['vendor_id']      = $seller_id;
            // withdraw data
            $withdraw_data['user_id']  = $seller_id;
            $withdraw_data['amount']   = $vendor_earning;
            $withdraw_data['order_id'] = $item_id;
            $transfer_data['withdraw'] = $withdraw_data;
            $list_to_transfer[]        = $transfer_data;
        }

        return $list_to_transfer;
    }

    /**
     * Processes vendor withdraw balance after processing orders.
     *
     * @since 3.5.0
     *
     * @param array $withdraw_data
     *
     * @return void
     */
    public static function process_vendor_withdraw( $withdraw_data ) {
        $vendor_balance_inserted = static::process_vendor_balance( $withdraw_data );
        if ( is_wp_error( $vendor_balance_inserted ) ) {
            Helper::log(
                "Process Vendor Balance Error:\n" . $vendor_balance_inserted->get_error_message() . ', withdraw data: ' . print_r( $withdraw_data, true ),
                'Withdraw',
                'error'
            );
            return false;
        }

        //insert into withdraw table
        $withdraw_data_inserted = static::process_vendor_withdraw_balance( $withdraw_data );
        if ( is_wp_error( $withdraw_data_inserted ) ) {
            Helper::log(
                "Process Seller Withdraw Error:\n" . $withdraw_data_inserted->get_error_message() . ', withdraw data: ' . print_r( $withdraw_data, true ),
                'Withdraw',
                'error'
            );
            return false;
        }

        return true;
    }

    /**
     * Processes vendor's withdraw balance.
     *
     * @since 3.5.0
     *
     * @param array $withdraw
     *
     * @return true|\WP_Error
     */
    public static function process_vendor_balance( $withdraw ) {
        global $wpdb;

        $balance_date = Helper::get_modified_balance_date();

        //update debit amount in vendor table where trn_type is `dokan_orders`
        $updated = $wpdb->update(
            $wpdb->dokan_vendor_balance,
            array(
                'balance_date' => $balance_date,
            ),
            array(
                'vendor_id' => $withdraw['user_id'],
                'trn_id'    => $withdraw['order_id'],
                'trn_type'  => 'dokan_orders',
            ),
            array( '%s' ),
            array( '%d', '%d', '%s' )
        );

        // check for possible error
        if ( false === $updated ) {
            return new \WP_Error( 'update_dokan_vendor_balance_error', sprintf( '[insert_into_vendor_balance] Error while updating vendor balance table data: %1$s', $wpdb->last_error ) );
        }

        //insert withdraw amount as credit in dokan vendor balance table
        $inserted = $wpdb->insert(
            $wpdb->dokan_vendor_balance,
            array(
                'vendor_id'    => $withdraw['user_id'],
                'trn_id'       => $withdraw['order_id'],
                'trn_type'     => 'dokan_withdraw',
                'perticulars'  => 'Paid Via ' . Helper::get_gateway_title(),
                'debit'        => 0,
                'credit'       => $withdraw['amount'],
                'status'       => 'approved',
                'trn_date'     => dokan_current_datetime()->format( 'Y-m-d H:i:s' ),
                'balance_date' => $balance_date,
            ),
            array(
                '%d',
                '%d',
                '%s',
                '%s',
                '%f',
                '%f',
                '%s',
                '%s',
                '%s',
            )
        );

        // check for possible error
        if ( false === $inserted ) {
            return new \WP_Error( 'update_dokan_vendor_balance_error', sprintf( '[insert_vendor_withdraw_balance] Error while inserting into vendor balance table data: %1$s', $wpdb->last_error ) );
        }

        return true;
    }

    /**
     * Processes vendor's withdraw balance
     *
     * @since 3.5.0
     *
     * @param array $withdraw
     *
     * @return true|\WP_Error
     */
    public static function process_vendor_withdraw_balance( $withdraw ) {
        if ( empty( $withdraw ) ) {
            return;
        }

        // Reconcile withdraw balance date that was previously tempered when payment was completed.
        self::process_withdraw_threshold( $withdraw['order_id'], 0, $withdraw['user_id'], 'dokan_orders' );
        self::process_withdraw_threshold( $withdraw['order_id'], 0, $withdraw['user_id'], 'dokan_withdraw' );

        $withdraw_data = array(
            'date'    => current_time( 'mysql' ),
            'status'  => 1,
            'method'  => Helper::get_gateway_id(),
            'ip'      => dokan_get_client_ip(),
            'notes'   => sprintf(
                __( 'Order %d payment Auto paid via MangoPay', 'dokan' ),
                $withdraw['order_id']
            ),
            'details' => '',
        );

        $withdraw_data  = array_merge( $withdraw_data, $withdraw );

        $withdraw_data_inserted = dokan()->withdraw->insert_withdraw( $withdraw_data );
        if ( is_wp_error( $withdraw_data_inserted ) ) {
            return $withdraw_data_inserted;
        }

        Meta::update_withdraw_balance( $withdraw['order_id'], 'yes' );
    }

    /**
	 * Creates a refund.
	 *
     * @param array $args
     *
     * @since 3.5.0
     *
     * @return \WeDevs\DokanPro\Refund\Refund|\WP_Error
     */
    public static function create_refund( $args = array() ) {
        global $wpdb;

        $default_args = [
            'order_id'        => 0,
            'seller_id'       => 0,
            'refund_amount'   => 0,
            'refund_reason'   => '',
            'item_qtys'       => [],
            'item_totals'     => [],
            'item_tax_totals' => [],
            'restock_items'   => null,
            'date'            => current_time( 'mysql' ),
            'status'          => 0,
            'method'          => 'false',
        ];

        $args = wp_parse_args( $args, $default_args );

        $inserted = $wpdb->insert(
            $wpdb->dokan_refund,
            $args,
            array( '%d', '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' )
        );

        if ( $inserted !== 1 ) {
            return new \WP_Error( 'dokan_refund_create_error', __( 'Could not create new refund', 'dokan' ) );
        }

        $refund = dokan_pro()->refund->get( $wpdb->insert_id );

        return $refund;
    }

    /**
     * Processes vendor withdraw threshold date.
     *
     * @since 3.5.0
     *
     * @param int    $order_id
     * @param int    $threshold_days
     * @param int    $vendor_id
     * @param string $transaction_type
     *
     * @return int|boolean
     */
    public static function process_withdraw_threshold( $order_id, $threshold_days = 0, $vendor_id = 0, $transaction_type = 'dokan_orders' ) {
        global $wpdb;

        $vendor_id    = empty( $vendor_id ) ? dokan_get_seller_id_by_order( $order_id ) : $vendor_id;
        $balance_date = empty( $threshold_days )
                    ? dokan_current_datetime()->format( 'Y-m-d H:i:s' )
                    : dokan_current_datetime()->modify( "+ {$threshold_days} days" )->format( 'Y-m-d H:i:s' );

        // Update threshold balance date
        return $wpdb->update(
            $wpdb->dokan_vendor_balance,
            array(
                'balance_date' => $balance_date,
            ),
            array(
                'vendor_id' => $vendor_id,
                'trn_id'    => $order_id,
                'trn_type'  => $transaction_type,
            ),
            array(
                '%s',
            ),
            array(
                '%d',
                '%d',
                '%s',
            )
        );
    }

    /**
     * Processes a refund.
     *
     * @since 3.5.0
     *
     * @param \WC_Order                      $order
     * @param \WeDevs\DokanPro\Refund\Refund $dokan_refund
     * @param \MangoPay\Refund               $mangopay_refund
     *
     * @return boolean
     */
    public static function process_refund( $order, $dokan_refund, $mangopay_refund ) {
        // Fees amount in Mangopay refund object is 'Negative'
        $admin_commission = 0 === $mangopay_refund->Fees->Amount ? 0 : -1 * (float) $mangopay_refund->Fees->Amount / 100;
        $total_refund     = (float) ( $mangopay_refund->DebitedFunds->Amount - $mangopay_refund->Fees->Amount ) / 100;
        $vendor_amount    = (float) $mangopay_refund->DebitedFunds->Amount / 100;

        // prepare data for further process this request
        $args = array(
            'dokan_mangopay'       => true,
            'total_refund_amount'  => $total_refund,
            'reversed_admin_fee'   => $admin_commission,
            'reversed_gateway_fee' => 0,
            'net_refund_amount'    => $vendor_amount,
            'refund_id'            => $mangopay_refund->Id,
        );

        // Try to approve the refund.
        $dokan_refund = $dokan_refund->approve( $args );
        if ( is_wp_error( $dokan_refund ) ) {
            Helper::log( $dokan_refund->get_error_message(), 'error' );
            $order->add_order_note(
                sprintf(
                    // translators: 1) gateway title, 2) order id, 3) error message
                    __( '%1$s: Automatic refund failed for order: %2$s. Note: %3$s', 'dokan' ),
                    Helper::get_gateway_title(),
                    $order->get_id(),
                    $dokan_refund->get_error_message()
                )
            );

            return false;
        }

        $order->add_order_note(
            sprintf(
                // translators: 1) refund currency 2) refund amount 3) gateway title 4) refund id
                __( 'Refunded %1$s%2$s via %3$s - Refund ID: %4$s', 'dokan' ),
                get_woocommerce_currency_symbol( $mangopay_refund->DebitedFunds->Currency ),
                $total_refund,
                Helper::get_gateway_title(),
                $mangopay_refund->Id
            )
        );

        //store refund id as array, this will help track all partial refunds
        if ( $order->get_parent_id() ) {
            Meta::update_refund_ids( $order->get_parent_id(), $mangopay_refund->Id );
        } else {
            Meta::update_refund_ids( $order, $mangopay_refund->Id );
            $order->save_meta_data();
        }

        return true;
    }

    /**
     * Saves transaction history
     *
     * @since 3.5.0
     *
     * @param int|string|object $order_id
     * @param int|string $transaction_id
     *
     * @return void
     */
    public static function save_transaction_history( $order, $transaction_id ) {
        $transaction_ids = Meta::get_all_transaction_ids( $order );

        // Update the history of transaction ids for this order
        if ( is_array( $transaction_ids ) ) {
            $transaction_ids[] = $transaction_id;
        } else {
            $transaction_ids = array( $transaction_id );
        }

        Meta::update_all_transaction_ids( $order, $transaction_ids );
    }

    /**
     * Saves transaction data in the order meta
     *
     * @since 3.5.0
     *
     * @param int|string $order_id
     * @param object     $transaction
     *
     * @return void
     */
    public static function save_transaction( $order_id, $transaction ) {
        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return;
        }

        // Record order meta values for transaction
        Meta::update_transaction_id( $order, $transaction->Id );
        Meta::update_succeeded_transaction_id( $order, $transaction->Id );
        self::save_transaction_history( $order, $transaction->Id );

        /* translators: %1$s: Gateway title, %2$s: Transaction id */
        $order->add_order_note( sprintf( __( '%1$s: Transaction ID: %2$s', 'dokan' ), Helper::get_gateway_title(), $transaction->Id ) );

        $payment_method = '';
        if ( 'CARD' === $transaction->PaymentType ) {
            $payment_method = $transaction->PaymentDetails->CardType;
        } elseif ( 'DIRECT_DEBIT' === $transaction->PaymentType ) {
            $payment_method = $transaction->PaymentDetails->DirectDebitType;
        }

        if ( empty( $payment_method ) ) {
            $payment_method = $transaction->PaymentType;
        }

        /* translators: %1$s: Gateway title, %2$s: Payment method */
        $order->add_order_note( sprintf( __( '%1$s: Order paid via %2$s', 'dokan' ), Helper::get_gateway_title(), $payment_method ) );

        if ( $order->get_meta( 'has_sub_order' ) ) {
            $sub_orders = get_children(
                array(
                    'post_parent' => $order->get_id(),
                    'post_type'   => 'shop_order'
                )
             );

            foreach ( $sub_orders as $sub_order ) {
                $order = wc_get_order( $sub_order->ID );
                /* translators: %1$s: Gateway title, %2$s: Transaction id */
                $order->add_order_note( sprintf( __( '%1$s: Transaction ID: %2$s', 'dokan' ), Helper::get_gateway_title(), $transaction->Id ) );
                /* translators: %1$s: Gateway title, %2$s: Payment method */
                $order->add_order_note( sprintf( __( '%1$s: Order paid via %2$s', 'dokan' ), Helper::get_gateway_title(), $payment_method ) );
            }
        }
    }
}
