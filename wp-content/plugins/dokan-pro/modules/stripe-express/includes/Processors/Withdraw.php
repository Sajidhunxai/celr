<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Processors;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Settings;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;

/**
 * Class for processing vendor withdraws.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Processors
 */
class Withdraw {

    /**
     * Processes vendor withdraw data after processing orders.
     *
     * @since 3.6.1
     *
     * @param array $withdraw_data
     *
     * @return void
     */
    public static function process_data( $withdraw_data ) {
        $vendor_balance_inserted = static::add_vendor_balance( $withdraw_data );
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
     * Processes vendor's balance.
     *
     * @since 3.6.1
     *
     * @param array $withdraw
     *
     * @return true|\WP_Error
     */
    public static function add_vendor_balance( $withdraw ) {
        global $wpdb;

        $balance_date = self::get_modified_balance_date();

        //update debit amount in vendor table where trn_type is `dokan_orders`
        $updated = $wpdb->update(
            $wpdb->dokan_vendor_balance,
            [
                'balance_date' => $balance_date,
            ],
            [
                'vendor_id' => $withdraw['user_id'],
                'trn_id'    => $withdraw['order_id'],
                'trn_type'  => 'dokan_orders',
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

        // check for possible error
        if ( false === $updated ) {
            return new \WP_Error(
                'update_dokan_vendor_balance_error',
                sprintf( '[insert_into_vendor_balance] Error while updating vendor balance table data: %1$s', $wpdb->last_error )
            );
        }

        //insert withdraw amount as credit in dokan vendor balance table
        $inserted = $wpdb->insert(
            $wpdb->dokan_vendor_balance,
            [
                'vendor_id'    => $withdraw['user_id'],
                'trn_id'       => $withdraw['order_id'],
                'trn_type'     => 'dokan_withdraw',
                /* translators: gateway title */
                'perticulars'  => sprintf( __( 'Paid Via %s', 'dokan' ), Helper::get_gateway_title() ),
                'debit'        => 0,
                'credit'       => $withdraw['amount'],
                'status'       => 'approved',
                'trn_date'     => dokan_current_datetime()->format( 'Y-m-d H:i:s' ),
                'balance_date' => $balance_date,
            ],
            [
                '%d',
                '%d',
                '%s',
                '%s',
                '%f',
                '%f',
                '%s',
                '%s',
                '%s',
            ]
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
     * @since 3.6.1
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
        self::process_vendor_balance_threshold( $withdraw['order_id'], 0, $withdraw['user_id'], 'dokan_orders' );
        self::process_vendor_balance_threshold( $withdraw['order_id'], 0, $withdraw['user_id'], 'dokan_withdraw' );

        $withdraw_data = [
            'date'    => current_time( 'mysql' ),
            'status'  => 1,
            'method'  => Helper::get_gateway_id(),
            'ip'      => dokan_get_client_ip(),
            'notes'   => sprintf(
                /* translators: 1) order id, 2) gateway title */
                __( 'Order %1$d payment Auto paid via %2$s', 'dokan' ),
                $withdraw['order_id'],
                Helper::get_gateway_title()
            ),
            'details' => '',
        ];

        $withdraw_data = array_merge( $withdraw_data, $withdraw );

        $withdraw_data_inserted = dokan()->withdraw->insert_withdraw( $withdraw_data );
        if ( is_wp_error( $withdraw_data_inserted ) ) {
            return $withdraw_data_inserted;
        }

        $order = wc_get_order( $withdraw['order_id'] );
        OrderMeta::update_if_withdraw_balance_added( $order, 'yes' );
        OrderMeta::save( $order );
    }

    /**
     * Processes vendor withdraw threshold date.
     *
     * @since 3.6.1
     *
     * @param int    $order_id
     * @param int    $threshold_days
     * @param int    $vendor_id
     * @param string $transaction_type
     *
     * @return int|boolean
     */
    public static function process_vendor_balance_threshold(
        $order_id,
        $threshold_days = 0,
        $vendor_id = 0,
        $transaction_type = 'dokan_orders'
    ) {
        global $wpdb;

        $vendor_id    = empty( $vendor_id ) ? dokan_get_seller_id_by_order( $order_id ) : $vendor_id;
        $balance_date = empty( $threshold_days )
                    ? dokan_current_datetime()->format( 'Y-m-d H:i:s' )
                    : dokan_current_datetime()->modify( "+ {$threshold_days} days" )->format( 'Y-m-d H:i:s' );

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

    /**
     * Modifies balance date with threshold according to disbursement mode.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function get_modified_balance_date() {
        $disburse_mode = Settings::get_disbursement_mode();
        switch ( $disburse_mode ) {
            case 'DELAYED':
                // Add one day extra with the delay period to consider the processing
                $interval_days = (int) Settings::get_disbursement_delay_period() + 1;
                break;

            case 'ON_ORDER_COMPLETED':
                // Let's make a big assumption to avoid any risk
                $interval_days = 60;
                break;

            default:
                $interval_days = 0;
        }

        return empty( $interval_days )
            ? dokan_current_datetime()->format( 'Y-m-d H:i:s' )
            : dokan_current_datetime()->modify( "+ {$interval_days} days" )->format( 'Y-m-d H:i:s' );
    }
}
