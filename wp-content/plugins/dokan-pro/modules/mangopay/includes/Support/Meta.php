<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Support;

/**
 * Meta data handler class
 *
 * @since 3.5.0
 */
class Meta {

    /**
     * Retrieves order meta key.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function key( $base ) {
        return '_' . Helper::get_gateway_id() . '_' . $base;
    }

    /**
     * Retrieves meta key for mangopay account id
     *
     * @since 3.5.0
     *
     * @return void
     */
    public static function mangopay_meta_key() {
        $key = self::key( 'account_id' );

        if ( ! Config::get_instance()->is_production() ) {
            $key .= '_sandbox';
        }

        return $key;
    }

    /**
     * Retrieves mangopay account id of a user
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     *
     * @return string|false
     */
    public static function get_mangopay_account_id( $user_id ) {
        return get_user_meta( $user_id, self::mangopay_meta_key(), true );
    }

    /**
     * Updates mangopay account id of a user
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param int|string $account_id
     *
     * @return int|boolean
     */
    public static function update_mangopay_account_id( $user_id, $account_id ) {
        delete_user_meta( $user_id, self::mangopay_meta_key() . '_trash' );
        return update_user_meta( $user_id, self::mangopay_meta_key(), $account_id );
    }

    /**
     * Deletes mangopay account id of a user
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param boolean $force
     *
     * @return boolean
     */
    public static function delete_mangopay_account_id( $user_id, $force = true ) {
        if ( ! $force ) {
            $account_id = self::get_mangopay_account_id( $user_id );
            update_user_meta( $user_id, self::mangopay_meta_key() . '_trash', $account_id );
        }

        return delete_user_meta( $user_id, self::mangopay_meta_key() );
    }

    /**
     * Retrieves Mangopay account it that was previously trashed.
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     *
     * @return string|false
     */
    public static function get_trashed_mangopay_account_id( $user_id ) {
        return get_user_meta( $user_id, self::mangopay_meta_key() . '_trash', true );
    }

    /**
     * Retrieves meta key for regular KYC verified.
     *
     * @since 3.5.0
     *
     * @return string
     */
    private static function regular_kyc_validated_key() {
        $key = self::key( 'regular_kyc_verified' );

        if ( ! Config::get_instance()->is_production() ) {
            $key .= '_sandbox';
        }

        return $key;
    }

    /**
     * Checks whether a user is regular KYC verified.
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     *
     * @return string|false
     */
    public static function is_user_regular_kyc_verified( $user_id ) {
        return 'yes' === get_user_meta( $user_id, self::regular_kyc_validated_key(), true );
    }

    /**
     * Updates the regular kyc verification status
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param int|string $status
     *
     * @return int|boolean
     */
    public static function update_regular_kyc_status( $user_id, $status ) {
        $meta_key = self::regular_kyc_validated_key();

        if ( 'yes' !== $status ) {
            return delete_user_meta( $user_id, $meta_key );
        }

        return update_user_meta( $user_id, $meta_key, 'yes' );
    }

    /**
     * Retrieves meta key for active bank account id.
     *
     * @since 3.5.0
     *
     * @return string
     */
    private static function active_bank_acoount_key() {
        $key = self::key( 'active_bank_account' );

        if ( ! Config::get_instance()->is_production() ) {
            $key .= '_sandbox';
        }

        return $key;
    }

    /**
     * Retrieves Mnagopay active bank account id
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     *
     * @return string|false
     */
    public static function get_active_bank_account( $user_id ) {
        return get_user_meta( $user_id, self::active_bank_acoount_key(), true );
    }

    /**
     * Updates mangopay bank account id of a user
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param int|string $bank_account_id
     *
     * @return int|boolean
     */
    public static function update_active_bank_account( $user_id, $bank_account_id ) {
        return update_user_meta( $user_id, self::active_bank_acoount_key(), $bank_account_id );
    }

    /**
     * Retrieves meta key for bank account id.
     *
     * @since 3.5.0
     *
     * @param string $account_type
     *
     * @return string
     */
    private static function bank_acoount_id_key( $account_type = '' ) {
        $key = self::key( "bank_account_id_$account_type" );

        if ( ! Config::get_instance()->is_production() ) {
            $key .= '_sandbox';
        }

        return $key;
    }

    /**
     * Retrieves Mnagopay bank account id
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param string     $account_type
     *
     * @return string|false
     */
    public static function get_bank_account_id( $user_id, $account_type = '' ) {
        return get_user_meta( $user_id, self::bank_acoount_id_key( $account_type ), true );
    }

    /**
     * Updates mangopay bank account id of a user
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param int|string $bank_account_id
     * @param string     $account_type
     *
     * @return int|boolean
     */
    public static function update_bank_account_id( $user_id, $bank_account_id, $account_type = '' ) {
        return update_user_meta( $user_id, self::bank_acoount_id_key( $account_type ), $bank_account_id );
    }

    /**
     * Retrieves mangopay user status
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     *
     * @return string|false
     */
    public static function get_user_status( $user_id ) {
        return get_user_meta( $user_id, self::key( 'user_status' ), true );
    }

    /**
     * Retrieves mangopay user status
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param string $status
     *
     * @return int|boolean
     */
    public static function update_user_status( $user_id, $status ) {
        return update_user_meta( $user_id, self::key( 'user_status' ), $status );
    }

    /**
     * Retrieves user business type
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     *
     * @return string|false
     */
    public static function get_user_business_type( $user_id ) {
        return get_user_meta( $user_id, self::key( 'user_business_type' ), true );
    }

    /**
     * Updates user business type
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param string $business_type
     *
     * @return int|boolean
     */
    public static function update_user_business_type( $user_id, $business_type ) {
        return update_user_meta( $user_id, self::key( 'user_business_type' ), $business_type );
    }

    /**
     * Retrieves user birthday
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     *
     * @return string|false
     */
    public static function get_user_birthday( $user_id ) {
        return get_user_meta( $user_id, self::key( 'user_birthday' ), true );
    }

    /**
     * Updates user birthday
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param string $birthday
     *
     * @return int|boolean
     */
    public static function update_user_birthday( $user_id, $birthday ) {
        return update_user_meta( $user_id, self::key( 'user_birthday' ), $birthday );
    }

    /**
     * Retrieves user nationality
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     *
     * @return string|false
     */
    public static function get_user_nationality( $user_id ) {
        return get_user_meta( $user_id, self::key( 'user_nationality' ), true );
    }

    /**
     * Updates user nationality
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param string $nationality
     *
     * @return int|boolean
     */
    public static function update_user_nationality( $user_id, $nationality ) {
        return update_user_meta( $user_id, self::key( 'user_nationality' ), $nationality );
    }

    /**
     * Retrieves failed payout data
     *
     * @since 3.5.0
     *
     * @return array|false
     */
    public static function get_failed_payouts() {
        return get_option( self::key( 'failed_payouts' ), array() );
    }

    /**
     * Updates failed payout data
     *
     * @since 3.5.0
     *
     * @param array $payout
     *
     * @return int|boolean
     */
    public static function update_failed_payouts( $payout ) {
        $failed_payouts = self::get_failed_payouts();

        if ( is_array( $failed_payouts ) ) {
            $failed_payouts[ $payout['order_id'] ] = $payout;
        } else {
            $failed_payouts = array(
                $payout['order_id'] => $payout,
            );
        }

        return update_option( self::key( 'failed_payouts' ), $failed_payouts );
    }

    /**
     * Removes a failed payout to mark it successful.
     *
     * @since 3.5.0
     *
     * @param array $payout
     *
     * @return int|boolean
     */
    public static function remove_failed_payout( $payout ) {
        $failed_payouts = self::get_failed_payouts();

        unset( $failed_payouts[ $payout['order_id'] ] );

        return update_option( self::key( 'failed_payouts' ), $failed_payouts );
    }

    /**
     * Retrieves number of payout attempts.
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     *
     * @return array|false
     */
    public static function get_payout_attempts( $order ) {
        if ( is_object( $order ) ) {
            return $order->get_meta( self::key( 'payout_attempts' ) );
        }

        return get_post_meta( $order, self::key( 'payout_attempts' ), true );
    }

    /**
     * Updates payout data
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     * @param int|string        $payout_attempts
     *
     * @return int|boolean
     */
    public static function update_payout_attempts( $order, $payout_attempts ) {
        if ( is_object( $order ) ) {
            return $order->update_meta_data( self::key( 'payout_attempts' ), $payout_attempts );
        }

        return update_post_meta( $order, self::key( 'payout_attempts' ), $payout_attempts );
    }

    /**
     * Retrieves number of payout attempts.
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     *
     * @return string|false
     */
    public static function get_last_payout_attempt( $order ) {
        if ( is_object( $order ) ) {
            return $order->get_meta( self::key( 'last_payout_attempt' ) );
        }

        return get_post_meta( $order, self::key( 'last_payout_attempt' ), true );
    }

    /**
     * Updates payout data
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     * @param int|string        $last_attempt
     *
     * @return int|boolean
     */
    public static function update_last_payout_attempt( $order, $payout_attempts ) {
        if ( is_object( $order ) ) {
            return $order->update_meta_data( self::key( 'last_payout_attempt' ), $payout_attempts );
        }

        return update_post_meta( $order, self::key( 'last_payout_attempt' ), $payout_attempts );
    }

    /**
     * Retrieves commission status
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     *
     * @return string|false
     */
    public static function get_commision_status( $order ) {
        if ( is_object( $order ) ) {
            return $order->get_meta( self::key( 'commission' ) );
        }

        return get_post_meta( $order, self::key( 'commission' ), true );
    }

    /**
     * Updates commission status
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     * @param string $status
     *
     * @return int|boolean
     */
    public static function update_commision_status( $order, $status ) {
        if ( is_object( $order ) ) {
            return $order->update_meta_data( self::key( 'commission' ), $status );
        }

        return update_post_meta( $order, self::key( 'commission' ), $status );
    }

    /**
     * Retrieves commission data
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     *
     * @return string|false
     */
    public static function get_commision( $order ) {
        if ( is_object( $order ) ) {
            return $order->get_meta( self::key( 'commission' ) );
        }

        return get_post_meta( $order, self::key( 'commission' ), true );
    }

    /**
     * Updates commission data
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     * @param string $commission
     *
     * @return int|boolean
     */
    public static function update_commision( $order, $commission ) {
        if ( is_object( $order ) ) {
            return $order->update_meta_data( self::key( 'commission' ), $commission );
        }

        return update_post_meta( $order, self::key( 'commission' ), $commission );
    }

    /**
     * Retrieves withdraw data for an order.
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     *
     * @return array|false
     */
    public static function get_withdraw_data( $order ) {
        if ( is_object( $order ) ) {
            return $order->get_meta( self::key( 'withdraw_data' ) );
        }

        return get_post_meta( $order, self::key( 'withdraw_data' ), true );
    }

    /**
     * Updates withdraw data for an order.
     *
     * @since 3.5.0
     *
     * @param int|string $order
     * @param array      $withdraw_data
     *
     * @return int|boolean
     */
    public static function update_withdraw_data( $order, $withdraw_data ) {
        if ( is_object( $order ) ) {
            return $order->update_meta_data( self::key( 'withdraw_data' ), $withdraw_data );
        }

        return update_post_meta( $order, self::key( 'withdraw_data' ), $withdraw_data );
    }

    /**
     * Retrieves Mangopay transaction id
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     *
     * @return string|false
     */
    public static function get_transaction_id( $order ) {
        if ( is_object( $order ) ) {
            return $order->get_meta( self::key( 'transaction_id' ) );
        }

        return get_post_meta( $order, self::key( 'transaction_id' ), true );
    }

    /**
     * Updates Mangopay transaction id
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     * @param string $transaction_id
     *
     * @return int|bool
     */
    public static function update_transaction_id( $order, $transaction_id ) {
        if ( is_object( $order ) ) {
            return $order->update_meta_data( self::key( 'transaction_id' ), $transaction_id );
        }

        return update_post_meta( $order, self::key( 'transaction_id' ), $transaction_id );
    }

    /**
     * Retrieves all Mangopay transaction ids of an order
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     *
     * @return array|false
     */
    public static function get_all_transaction_ids( $order ) {
        if ( is_object( $order ) ) {
            return $order->get_meta( self::key( 'transaction_ids' ) );
        }

        return (array) get_post_meta( $order, self::key( 'transaction_ids' ), true );
    }

    /**
     * Updates all Mangopay transaction ids of an order
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     * @param array $transaction_ids
     *
     * @return int|bool
     */
    public static function update_all_transaction_ids( $order, $transaction_ids ) {
        if ( is_object( $order ) ) {
            return $order->update_meta_data( self::key( 'transaction_ids' ), $transaction_ids );
        }

        return update_post_meta( $order, self::key( 'transaction_ids' ), $transaction_ids );
    }

    /**
     * Retrieves Mangopay successful transaction id
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     *
     * @return string|false
     */
    public static function get_succeeded_transaction_id( $order ) {
        if ( is_object( $order ) ) {
            return $order->get_meta( self::key( 'success_transaction_id' ) );
        }

        return get_post_meta( $order, self::key( 'success_transaction_id' ), true );
    }

    /**
     * Updates successful Mangopay transaction id
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     * @param string $transaction_id
     *
     * @return int|bool
     */
    public static function update_succeeded_transaction_id( $order, $transaction_id ) {
        if ( is_object( $order ) ) {
            return $order->update_meta_data( self::key( 'success_transaction_id' ), $transaction_id );
        }

        return update_post_meta( $order, self::key( 'success_transaction_id' ), $transaction_id );
    }

    /**
     * Retrieves Mangopay transfers
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     *
     * @return array|false
     */
    public static function get_transfers( $order ) {
        if ( is_object( $order ) ) {
            return $order->get_meta( self::key( 'transfers' ) );
        }

        return get_post_meta( $order, self::key( 'transfers' ), true );
    }

    /**
     * Retrieves Mangopay transfers
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     * @param array $transfers
     *
     * @return int|boolean
     */
    public static function update_transfers( $order, $transfers ) {
        if ( is_object( $order ) ) {
            return $order->update_meta_data( self::key( 'transfers' ), $transfers );
        }

        return update_post_meta( $order, self::key( 'transfers' ), $transfers );
    }

    /**
     * Retrieves Mangopay refund ids
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     *
     * @return array|false
     */
    public static function get_refund_ids( $order ) {
        if ( is_object( $order ) ) {
            $refund_ids = $order->get_meta( self::key( 'refunds' ) );
        } else {
            $refund_ids = get_post_meta( $order, self::key( 'refunds' ), true );
        }

        if ( ! empty( $refund_ids ) ) {
            return (array) $refund_ids;
        }

        return array();
    }

    /**
     * Retrieves Mangopay refund ids
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     * @param string|array $refund_ids
     *
     * @return int|boolean
     */
    public static function update_refund_ids( $order, $refund_ids ) {
        if ( empty( $refund_ids ) ) {
            return false;
        }

        if ( is_object( $order ) ) {
            return $order->update_meta_data(
                self::key( 'refunds' ),
                array_merge(
                    self::get_refund_ids( $order ),
                    (array) $refund_ids
                )
            );
        }

        return update_post_meta(
            $order,
            self::key( 'refunds' ),
            array_merge(
                self::get_refund_ids( $order ),
                (array) $refund_ids
            )
        );
    }

    /**
     * Retrieves payout data
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     *
     * @return array|false
     */
    public static function get_payouts( $order ) {
        if ( is_object( $order ) ) {
            return $order->get_meta( self::key( 'payouts' ) );
        }

        return get_post_meta( $order, self::key( 'payouts' ), true );
    }

    /**
     * Updates payout data
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     * @param array $payouts
     *
     * @return int|boolean
     */
    public static function update_payouts( $order, $payouts ) {
        $succeeded_payouts = self::get_payouts( $order );

        if ( is_array( $succeeded_payouts ) ) {
            $succeeded_payouts[] = $payouts;
        } else {
            $succeeded_payouts = array( $payouts );
        }

        if ( is_object( $order ) ) {
            return $order->update_meta_data( self::key( 'payouts' ), $succeeded_payouts );
        }

        return update_post_meta( $order, self::key( 'payouts' ), $succeeded_payouts );
    }

    /**
     * Retrieves Mangopay payment reference
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     *
     * @return object|false
     */
    public static function get_payment_ref( $order ) {
        if ( is_object( $order ) ) {
            return $order->get_meta( self::key( 'payment_ref' ) );
        }

        return get_post_meta( $order, self::key( 'payment_ref' ), true );
    }

    /**
     * Updates Mangopay payment reference
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     * @param object $reference
     *
     * @return int|boolean
     */
    public static function update_payment_ref( $order, $reference ) {
        if ( is_object( $order ) ) {
            return $order->update_meta_data( self::key( 'payment_ref' ), $reference );
        }

        return update_post_meta( $order, self::key( 'payment_ref' ), $reference );
    }

    /**
     * Retrieves Mangopay payment type
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     *
     * @return string|false
     */
    public static function get_payment_type( $order ) {
        if ( is_object( $order ) ) {
            return $order->get_meta( self::key( 'payment_type' ) );
        }

        return get_post_meta( $order, self::key( 'payment_type' ), true );
    }

    /**
     * Updates Mangopay payment type
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     * @param string $pay_type
     *
     * @return int|boolean
     */
    public static function update_payment_type( $order, $pay_type ) {
        if ( is_object( $order ) ) {
            return $order->update_meta_data( self::key( 'payment_type' ), $pay_type );
        }

        return update_post_meta( $order, self::key( 'payment_type' ), $pay_type );
    }

    /**
     * Retrieves Mangopay payment disbursement mode
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     *
     * @return string|false
     */
    public static function get_payment_disburse_mode( $order ) {
        if ( is_object( $order ) ) {
            return $order->get_meta( self::key( 'payment_disbursement_mode' ) );
        }

        return get_post_meta( $order, self::key( 'payment_disbursement_mode' ), true );
    }

    /**
     * Updates Mangopay payment disbursement mode
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     * @param string $disburse_mode
     *
     * @return int|boolean
     */
    public static function update_payment_disburse_mode( $order, $disburse_mode ) {
        if ( is_object( $order ) ) {
            return $order->update_meta_data( self::key( 'payment_disbursement_mode' ), $disburse_mode );
        }

        return update_post_meta( $order, self::key( 'payment_disbursement_mode' ), $disburse_mode );
    }

    /**
     * Checks if withdraw balance added for an order
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     *
     * @return boolean
     */
    public static function is_withdraw_balance_added( $order ) {
        $is_added = is_object( $order )
            ? $order->get_meta( self::key( 'withdraw_balance_added' ) )
            : get_post_meta( $order, self::key( 'withdraw_balance_added' ), true );

        return 'yes' === $is_added;
    }

    /**
     * Updates Mangopay withdraw balance added flag
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     * @param string $is_added
     *
     * @return int|boolean
     */
    public static function update_withdraw_balance( $order, $is_added ) {
        if ( is_object( $order ) ) {
            return $order->update_meta_data( self::key( 'withdraw_balance_added' ), $is_added );
        }

        return update_post_meta( $order, self::key( 'withdraw_balance_added' ), $is_added );
    }

    /**
     * Retrieves transfer id of an order.
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     *
     * @return string|false
     */
    public static function get_transfer_id( $order ) {
        if ( is_object( $order ) ) {
            return $order->get_meta( self::key( 'transfer_id' ) );
        }

        return get_post_meta( $order, self::key( 'transfer_id' ), true );
    }

    /**
     * Updates transfer id of an order.
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     * @param int|string $transfer_id
     *
     * @return int|boolean
     */
    public static function update_transfer_id( $order, $transfer_id ) {
        if ( is_object( $order ) ) {
            return $order->update_meta_data( self::key( 'transfer_id' ), $transfer_id );
        }

        return update_post_meta( $order, self::key( 'transfer_id' ), $transfer_id );
    }

    /**
     * Retrieves payout id of an order.
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     *
     * @return string|false
     */
    public static function get_payout_id( $order ) {
        if ( is_object( $order ) ) {
            return $order->get_meta( self::key( 'payout_id' ) );
        }

        return get_post_meta( $order, self::key( 'payout_id' ), true );
    }

    /**
     * Updates payout id of an order.
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     * @param int|string $transfer_id
     *
     * @return int|boolean
     */
    public static function update_payout_id( $order, $payout_id ) {
        if ( is_object( $order ) ) {
            return $order->update_meta_data( self::key( 'payout_id' ), $payout_id );
        }

        return update_post_meta( $order, self::key( 'payout_id' ), $payout_id );
    }

    /**
     * Updates flag if withdraw threshold is processed.
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     * @param int|string        $is_added
     *
     * @return int|boolean
     */
    public static function update_withdraw_threshold_processed( $order, $is_added = 'yes' ) {
        $is_added = 'yes' === $is_added ? 'yes' : 'no';

        if ( is_object( $order ) ) {
            return $order->update_meta_data( self::key( 'withdraw_threshold_processed' ), $is_added );
        }

        return update_post_meta( $order, self::key( 'withdraw_threshold_processed' ), $is_added );
    }

    /**
     * Checks whether withdraw threshold is processed.
     *
     * @since 3.5.0
     *
     * @param int|string|object $order
     *
     * @return boolean
     */
    public static function is_withdraw_threshold_processed( $order ) {
        if ( is_object( $order ) ) {
            return 'yes' === $order->get_meta( self::key( 'withdraw_threshold_processed' ) );
        }

        return 'yes' === get_post_meta( $order, self::key( 'withdraw_threshold_processed' ), true );
    }
}
