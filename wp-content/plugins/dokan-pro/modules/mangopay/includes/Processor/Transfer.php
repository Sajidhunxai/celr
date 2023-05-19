<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Processor;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WP_Error;
use Exception;
use MangoPay\Money;
use MangoPay\Refund;
use MangoPay\RefundReasonDetails;
use MangoPay\Transfer as MangoTransfer;
use WeDevs\DokanPro\Modules\MangoPay\Support\Meta;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;
use WeDevs\DokanPro\Modules\MangoPay\Support\Processor;

/**
 * Class for handling wallet transfers
 *
 * @since 3.5.0
 */
class Transfer extends Processor {

    /**
     * Retrieves a transfer data.
     *
     * @since 3.5.0
     *
     * @param int|string $transfer_id
     *
     * @return object|false
     */
    public static function get( $transfer_id ) {
        try {
            return static::config()->mangopay_api->Transfers->Get( $transfer_id );
        } catch( Exception $e ) {
            Helper::log(
                sprintf(
                    'Could not parse transfer data for ID: %s. Message: %s',
                    $transfer_id, $e->getMessage()
                ),
                'Transfer'
            );
            return false;
        }
    }

    /**
     * Perform Mangopay wallet-to-wallet transfer with retained fees
     *
     * @since 3.5.0
     *
     * @see: https://github.com/Mangopay/mangopay2-php-sdk/blob/master/demos/workflow/scripts/transfer.php
     *
     * @param int|string $order_id
     * @param int|string $transaction_id
     * @param int|string $customer_id
     * @param int|string $vendor_id
     * @param int|float  $amount
     * @param int|float  $fees
     * @param string     $currency
     *
     * @return object
     */
    public static function create( $order_id, $transaction_id, $customer_id, $vendor_id, $amount, $fees, $currency ) {
        try {
            $mangopay_customer_id	= Meta::get_mangopay_account_id( $customer_id );
            if ( empty( $mangopay_customer_id ) ) {
                $mangopay_customer_id = User::create( $customer_id );
            }

            $mangopay_vendor_id = Meta::get_mangopay_account_id( $vendor_id );
            if ( empty( $mangopay_vendor_id ) ) {
                $mangopay_vendor_id = User::create( $vendor_id );
            }

            // Get the user wallet that was used for the transaction
            $transaction        = PayIn::get( $transaction_id );
            $customer_wallet_id = $transaction->CreditedWalletId;

            // Get the vendor wallet
            $vendor_wallet = Wallet::create( $mangopay_vendor_id );

            // Go for the transfer
            $transfer                         = new MangoTransfer();
            $transfer->AuthorId               = $mangopay_customer_id;
            $transfer->DebitedFunds           = new Money();
            $transfer->DebitedFunds->Currency = $currency;
            $transfer->DebitedFunds->Amount   = round( $amount * 100 );
            $transfer->Fees                   = new Money();
            $transfer->Fees->Currency         = $currency;
            $transfer->Fees->Amount           = round( $fees * 100 );
            $transfer->DebitedWalletID        = $customer_wallet_id;
            $transfer->CreditedWalletId       = $vendor_wallet->Id;
            $transfer->Tag                    = "WC Order #$order_id";

            $response = static::config()->mangopay_api->Transfers->Create( $transfer );
        } catch( Exception $e ) {
            Helper::log(
                sprintf(
                    'Could not process the wallet transfer of the amount: %s to the wallet: %s of the user: %s. Message: %s',
                    $amount, $vendor_wallet->Id, $vendor_id, $e->getMessage()
                ),
                'Transfer'
            );

            return new WP_Error(
                'dokan-mangopay-transfer-error',
                sprintf(
                    __( 'Could not process the wallet transfer of the amount: %s to the wallet: %s. Message: %s', 'dokan' ),
                    $amount, $vendor_wallet->Id, $vendor_id, $e->getMessage()
                )
            );
        }

        return $response;
    }

    /**
     * Refunds a transfer
     *
     * @since 3.5.0
     *
     * @param int|string $order_id
     * @param int|string $transfer_id
     * @param int|string $wp_user_id
     * @param string     $reason
     *
     * @return mixed
     */
    public static function refund( $order_id, $transfer_id, $wp_user_id, $reason = '' ) {
        try {
            $mp_user_id = Meta::get_mangopay_account_id( $wp_user_id );
            $mp_user_id = ! $mp_user_id ? $wp_user_id : $mp_user_id;
            $refund     = new Refund();
            $refund->AuthorId     = $mp_user_id;
            $refund->Tag          = "WC order #$order_id";
            $refund->RefundReason = new RefundReasonDetails();
            $refund->RefundReason->RefundReasonMessage = $reason;

            return static::config()->mangopay_api->Transfers->CreateRefund( $transfer_id, $refund );
        } catch( Exception $e ) {
            Helper::log(
                sprintf(
                    'Could not process the transfer refund for the transfer: %s. Message: %s',
                    $transfer_id, $e->getMessage()
                ),
                'Refund'
            );

            Helper::log(
                sprintf( 'Object: ' . print_r( $refund, true ) ),
                'Refund'
            );

            return new WP_Error(
                'dokan-mangopay-transfer-refund-error',
                sprintf(
                    __( 'Could not process the refund for the transfer: %s. Message: %s', 'dokan' ),
                    $transfer_id, $e->getMessage()
                ),
                'Refund'
            );
        }
    }
}
