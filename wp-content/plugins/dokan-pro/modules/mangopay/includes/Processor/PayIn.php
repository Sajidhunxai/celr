<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Processor;

use WP_Error;
use Exception;
use MangoPay\Money;
use MangoPay\Refund;
use MangoPay\PayIn as MangoPayIn;
use MangoPay\RefundReasonDetails;
use MangoPay\PayInPaymentDetailsCard;
use MangoPay\PayInExecutionDetailsWeb;
use MangoPay\PayInExecutionDetailsDirect;
use MangoPay\PayInPaymentDetailsBankWire;
use MangoPay\PayInPaymentDetailsDirectDebit;
use WeDevs\DokanPro\Modules\MangoPay\Support\Meta;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;
use WeDevs\DokanPro\Modules\MangoPay\Support\Settings;
use WeDevs\DokanPro\Modules\MangoPay\Support\Processor;

/**
 * Class to process Mangopay Pay in.
 *
 * @since 3.5.0
 */
class PayIn extends Processor {

    /**
     * Get payin info
     *
     * @since 3.5.0
     *
     * @param int $transaction_id
     *
     * @return object|false
     */
    public static function get( $transaction_id ) {
        try {
            $pay_in = static::config()->mangopay_api->PayIns->Get( $transaction_id );
        } catch ( Exception $e ) {
            self::log( sprintf( 'Could not fetch data for transaction: %s. Message: %s', $transaction_id, $e->getMessage() ) );
            return false;
        }

        return $pay_in;
    }

    /**
     * Creates a payin
     *
     * @since 3.5.0
     *
     * @param object $pay_in
     *
     * @return object|WP_Error
     */
    public static function create( $pay_in ) {
        try {
            $response = static::config()->mangopay_api->PayIns->Create( $pay_in );
        } catch( Exception $e ) {
            self::log( sprintf( 'Could not create payin. Message: %s', $e->getMessage() ) );
            self::log( 'Object: ' . print_r( $pay_in, true ) );
            return new WP_Error(
                'mangopay-payin-create-error',
                sprintf( __( 'Payment unsuccessful. Error: %s', 'dokan' ), $e->getMessage() )
            );
        }

        return $response;
    }

    /**
     * Call the appropriate payin url creation method depending on the card type.
     *
     * @since 3.5.0
     *
     * @param int|string $wp_user_id
     * @param int|string $order_id
     * @param int|float  $amount
     * @param int|float  $fees
     * @param string 	 $return_url
     * @param string 	 $currency
     * @param string 	 $card_type
     * @param string 	 $template_url
     *
     * @return mixed
     */
    public static function default_card_transaction(
        $wp_user_id,
        $order_id,
        $amount,
        $fees,
        $return_url,
        $currency = 'EUR',
        $card_type = 'CB_VISA_MASTERCARD',
        $template_url = ''
    ) {
        if ( 'SOFORT' === $card_type || 'GIROPAY' === $card_type ) {
            return static::direct_debit_web_transaction( $wp_user_id, $order_id, $amount, $fees, $return_url, $card_type, $currency, $template_url );
        }

        return static::card_transaction( $wp_user_id, $order_id, $amount, $fees, $return_url, $card_type, $currency, $template_url );
    }

    /**
     * Generates url for card payin.
     *
     * @since 3.5.0
     *
     * @param int|string $wp_user_id
     * @param int|string $order_id
     * @param int|float  $amount
     * @param int|float  $fees
     * @param string 	 $return_url
     * @param string 	 $card_type
     * @param string 	 $currency
     * @param string 	 $template_url
     *
     * @return array
     */
    public static function card_transaction(
        $wp_user_id,
        $order_id,
        $amount,
        $fees,
        $return_url,
        $card_type = 'CB_VISA_MASTERCARD',
        $currency = 'EUR',
        $template_url = ''
    ) {
        $pay_in = self::prepare(
            'CARD',
            $wp_user_id,
            array(
                'currency' 	 => $currency,
                'amount'   	 => $amount,
                'fees'	   	 => $fees,
                'order_id' 	 => $order_id,
                'card_type'  => $card_type,
                'return_url' => $return_url,
            )
        );

        if ( is_wp_error( $pay_in ) ) {
            return $pay_in;
        }

        $pay_in = parent::process_browser_data( $pay_in, $card_type );
        $pay_in = parent::prepare_billing_shipping_data( $pay_in, $order_id );

        if ( $template_url ) {
            $pay_in->ExecutionDetails->TemplateURLOptions = array( 'PAYLINEV2' => $template_url );
        }

        $response = self::create( $pay_in );
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        // Return the redirect url and the transaction id
        return array(
            'redirect_url'	 => ! empty( $response->ExecutionDetails->SecureModeRedirectURL )
                                ? $response->ExecutionDetails->SecureModeRedirectURL
                                : $response->ExecutionDetails->RedirectURL,
            'transaction_id' => $response->Id
        );
    }

    /**
     * Generate url for for direct debit web payment types (like Sofort & Giropay).
     *
     * @since 3.5.0
     *
     * @param int|string $wp_user_id
     * @param int|string $order_id
     * @param int|float  $amount
     * @param int|float  $fees
     * @param string 	 $return_url
     * @param string 	 $card_type
     * @param string 	 $currency
     * @param string 	 $template_url
     *
     * @return array|WP_Error
     */
    public static function direct_debit_web_transaction(
        $wp_user_id,
        $order_id,
        $amount,
        $fees,
        $return_url,
        $card_type = 'SOFORT',
        $currency = 'EUR',
        $template_url = ''
    ) {
        $pay_in = self::prepare(
            'DIRECT_DEBIT',
            $wp_user_id,
            array(
                'currency' 	 => $currency,
                'amount'   	 => $amount,
                'fees'	   	 => $fees,
                'order_id' 	 => $order_id,
                'card_type'  => $card_type,
                'return_url' => $return_url,
            )
        );

        if ( is_wp_error( $pay_in ) ) {
            return $pay_in;
        }

        // From 19/08/2021 exclude sofort and giropay from custom template
        if (
            $template_url &&
            'SOFORT' !== $card_type &&
            'GIROPAY' !== $card_type
        ) {
            $pay_in->ExecutionDetails->TemplateURLOptions = array( 'PAYLINEV2' => $template_url );
        }

        $response = self::create( $pay_in );
        if ( is_wp_error( $response ) ) {
            return $response;
        }

        // Return the redirect url and the transaction id
        return array(
            'redirect_url'   => $response->ExecutionDetails->RedirectURL,
            'transaction_id' => $response->Id
        );
    }

    /**
     * Generate URL for for card web payment types.
     *
     * @since 3.5.0
     *
     * @param int|string $wp_user_id
     * @param int|string $order_id
     * @param string 	 $card_id
     * @param int|float  $amount
     * @param int|float  $fees
     * @param string 	 $return_url
     * @param string 	 $currency
     *
     * @return array|WP_Error
     */
    public static function card_web_transaction(
        $wp_user_id,
        $order_id,
        $card_id,
        $amount,
        $fees,
        $return_url,
        $currency = 'EUR'
    ) {
        $pay_in = self::prepare(
            'CARD',
            $wp_user_id,
            array(
                'currency' 	 => $currency,
                'amount'   	 => $amount,
                'fees'	   	 => $fees,
                'exec_type'  => 'DIRECT',
                'order_id' 	 => $order_id,
                'card_id' 	 => $card_id,
                'return_url' => $return_url,
            )
        );

        if ( is_wp_error( $pay_in ) ) {
            return $pay_in;
        }

        $pay_in   = parent::prepare_billing_shipping_data( $pay_in, $order_id );
        $pay_in   = parent::process_browser_data( $pay_in );
        $response = self::create( $pay_in );

        self::log( print_r( $response, true ) );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        /** Return the RedirectUrl and the transaction_id **/
        return array(
            'transaction_id'   => $response->Id,
            'redirect_url'	   => $return_url,
            'data_transaction' => $response
        );
    }

    /**
     * Generates Wire Reference and Bank Account data for a bank wire payment.
     *
     * @since 3.5.0
     *
     * @param int|string $wp_user_id
     * @param int|string $order_id
     * @param int|float  $amount
     * @param int|float  $fees
     * @param string 	 $currency
     *
     * @return object|WP_Error
     */
    public static function bankwire_transaction( $wp_user_id, $order_id, $amount, $fees, $currency = 'EUR' ) {
        $pay_in = self::prepare(
            'BANK_WIRE',
            $wp_user_id,
            array(
                'currency' => $currency,
                'amount'   => $amount,
                'fees'	   => $fees,
                'order_id' => $order_id,
            )
        );

        if ( is_wp_error( $pay_in ) ) {
            return $pay_in;
        }

        return self::create( $pay_in );
    }

    /**
     * Processes card payin refund.
     *
     * @since 3.5.0
     *
     * @see: https://github.com/Mangopay/mangopay2-php-sdk/blob/master/demos/workflow/scripts/refund-payin.php
     *
     * @param int|string $order_id
     * @param int|string $transaction_id
     * @param int|string $wp_user_id
     * @param int|float  $amount
     * @param string 	 $currency
     * @param string 	 $reason
     *
     * @return object|WP_Error
     */
    public static function refund( $order_id, $transaction_id, $wp_user_id, $amount, $currency, $reason = '' ) {
        try {
            $mp_user_id						= Meta::get_mangopay_account_id( $wp_user_id );
            $vendor_id                      = dokan_get_seller_id_by_order( $order_id );
            $refund							= new Refund();
            $refund->AuthorId				= $mp_user_id;
            $refund->DebitedFunds			= new Money();
            $refund->DebitedFunds->Currency	= $currency;
            $refund->DebitedFunds->Amount	= $amount * 100;
            $refund->Fees					= new Money();
            $refund->Fees->Currency			= $currency;
            $refund->Fees->Amount			= 0;
            $refund->Tag					= "WC Order #$order_id, Dokan vendor#$vendor_id:$amount";
            $refund->RefundReason           = new RefundReasonDetails();
            $refund->RefundReasonMessage    = $reason;

            return static::config()->mangopay_api->PayIns->CreateRefund( $transaction_id, $refund );
        } catch( Exception $e ) {
            self::log( sprintf( 'Could not create payin refund. Message: %s', $e->getMessage() ) );
            return new WP_Error(
                'mangopay-payin-refund-error',
                sprintf( __( 'Could not create Payin Refund. Error: %s', 'dokan' ), $e->getMessage() )
            );
        }
    }

    /**
     * Processes payin data before creating
     *
     * @since 3.5.0
     *
     * @param string 	 $payment_type
     * @param int|string $user_id
     * @param array 	 $data
     *
     * @return object|false
     */
    public static function prepare( $payment_type, $user_id, $data ) {
        $user_id = User::sync_account_data( $user_id );

        if ( ! $user_id ) {
            return new WP_Error( 'dokan-mangopay-no-valid-user', __( 'No valid user found.', 'dokan' ) );
        }

        $wallet = Wallet::create( $user_id );
        if ( ! $wallet ) {
            return new WP_Error( 'dokan-mangopay-no-wallet', __( 'No valid Mangopay wallet found.', 'dokan' ) );
        }

        $defaults = array(
            'tag'        => null,
            'order_id'   => null,
            'currency'   => 'EUR',
            'amount'     => 0,
            'fees'       => 0,
            'card_id'    => null,
            'card_type'  => null,
            'return_url' => null,
            'locale'     => Helper::get_locale(),
            'exec_type'  => 'WEB',
        );

        $data = wp_parse_args( $data, $defaults );

        if ( ! isset( $data['tag'] ) ) {
            $data['tag'] = ! empty( $data['order_id'] ) ? "WC Order #{$data['order_id']}" : 'WC Order';
        }

        /**
         * Filter to modify pay-in data in case needed.
         *
         * @since 3.7.17
         *
         * @param array<string,mixed> $data
         */
        $data = apply_filters( 'dokan_mangopay_payin_data', $data );

        $pay_in 				  = new MangoPayIn();
        $pay_in->CreditedWalletId = $wallet->Id;
        $pay_in->AuthorId		  = $user_id;
        $pay_in->PaymentType	  = $payment_type;
        $pay_in->Tag 			  = $data['tag'];

        if ( 'BANK_WIRE' !== $payment_type ) {
            $pay_in->DebitedFunds 			= new Money();
            $pay_in->DebitedFunds->Currency	= $data['currency'];
            $pay_in->DebitedFunds->Amount	= $data['amount'];
            $pay_in->Fees 					= new Money();
            $pay_in->Fees->Currency 		= $data['currency'];
            $pay_in->Fees->Amount 			= $data['fees'];
            $pay_in->ExecutionType 			= $data['exec_type'];
        }

        switch ( $payment_type ) {
            case 'BANK_WIRE':
                $pay_in->ExecutionDetails 								= new PayInExecutionDetailsDirect();
                $pay_in->PaymentDetails 								= new PayInPaymentDetailsBankWire();
                $pay_in->PaymentDetails->DeclaredDebitedFunds 			= new Money();
                $pay_in->PaymentDetails->DeclaredDebitedFunds->Currency	= $data['currency'];
                $pay_in->PaymentDetails->DeclaredDebitedFunds->Amount	= $data['amount'];
                $pay_in->PaymentDetails->DeclaredFees 					= new Money();
                $pay_in->PaymentDetails->DeclaredFees->Currency			= $data['currency'];
                $pay_in->PaymentDetails->DeclaredFees->Amount			= $data['fees'];
                break;

            case 'CARD':
                $pay_in->PaymentDetails = new PayInPaymentDetailsCard();

                if ( 'WEB' === $data['exec_type'] ) {
                    $pay_in->PaymentDetails->CardType    = $data['card_type'];
                    $pay_in->ExecutionDetails 			 = new PayInExecutionDetailsWeb();
                    $pay_in->ExecutionDetails->ReturnURL = $data['return_url'];
                    $pay_in->ExecutionDetails->Culture	 = $data['locale'];
                } else {
                    $pay_in->PaymentDetails->CardId				   = $data['card_id'];
                    $pay_in->ExecutionDetails 					   = new PayInExecutionDetailsDirect();
                    $pay_in->ExecutionDetails->SecureModeReturnURL = $data['return_url'];
                }

                if ( in_array( $data['card_type'], Helper::get_3ds_supported_cards(), true ) ) {
                    $pay_in->SecureMode                            = 'FORCE';
                    $pay_in->Requested3DSVersion                   = 'V2_1';
                    $pay_in->ExecutionDetails->SecureModeReturnURL = $data['return_url'];
                }
                break;

            case 'DIRECT_DEBIT':
                $pay_in->PaymentDetails 			  	 = new PayInPaymentDetailsDirectDebit();
                $pay_in->PaymentDetails->DirectDebitType = $data['card_type'];
                $pay_in->ExecutionDetails 			 	 = new PayInExecutionDetailsWeb();
                $pay_in->ExecutionDetails->ReturnURL 	 = $data['return_url'];
                $pay_in->ExecutionDetails->Culture	 	 = $data['locale'];
                break;
        }

        return $pay_in;
    }

    /**
     * Checks if the payment is okay and conveys all requirements
     *
     * @since 3.5.0
     *
     * @param object $transaction
     * @param array  $payload
     *
     * @return int|false
     */
    public static function verify( $transaction, $payload ) {
        // Check for the Order ID first because we need it to warn the right vendor of possible failures
        if ( ! preg_match( '/^WC Order #(\d+)$/', $transaction->Tag, $matches ) ) {
            Helper::warn_owner(
                sprintf(
                    __( 'MangoPay Payin with Webhook %1$s does not contain a WooCommerce Order ID reference for Resource ID: %2$s', 'dokan' ),
                    $payload['EventType'], $payload['RessourceId']
                )
            );

            return false;
        }

        $order_id = $matches[1];
        $order 	  = wc_get_order( $order_id );

        if ( ! $order ) {
            return false;
        }

        // If we already have a mangopay successfull transaction id we already processed the order
        $transaction_id = Meta::get_succeeded_transaction_id( $order );

        // If succeeded transaction exists or order status is processed, exit right away
        if ( $transaction_id || 'processing' === $order->get_status() || 'completed' === $order->get_status() ) {
            $order->add_order_note( sprintf(
                __( 'A Payin webhook with RessourceId %s has been recieved and discarded as the order has been already processsed.', 'dokan' ),
                $payload['RessourceId']
            ) );
            echo '200 (OK)';
            exit;
        }

        $order->add_order_note( sprintf( __( '[%1$s] Incoming Webhook Resource Id: %2$s', 'dokan' ), Helper::get_gateway_title(), $payload['RessourceId'] ) );

        $payment_type = Meta::get_payment_type( $order );

        // Check that this order was paid by bankwire **/
        if ( 'card' !== $payment_type && 'bank_wire' !== $payment_type ) {
            Helper::warn_owner(
                sprintf(
                    __( 'MangoPay Payin Resource ID: %1$s references WooCommerce Order ID: %2$s which was not paid by bank wire', 'dokan' ),
                    $payload['RessourceId'],
                    $order_id
                ),
                $order_id
            );

            return false;
        }

        // Check that the payin ID matches at least one of the transaction_ids of this order
        $transaction_ids = Meta::get_all_transaction_ids( $order );

        if ( ! empty( $transaction_ids ) && is_array( $transaction_ids ) ) {
            $transaction_ids[] = $transaction_id;
        } else {
            $transaction_ids = array( $transaction_id );
        }

        $payment_ref = Meta::get_payment_ref( $order );
        if ( $payment_ref ) {
            $transaction_ids[] = $payment_ref['transaction_id'];
        }

        if ( ! in_array( $transaction->Id, $transaction_ids ) ) {
            self::log( sprintf( 'Error (%s): Payin ID doesn\'t match WC order Payin ID', $payment_type ) );

            Helper::warn_owner(
                sprintf(
                    __( 'MangoPay Payin Resource ID: %1$s references wrong WooCommerce Order ID: %2$s', 'dokan' ),
                    $payload['RessourceId'],
                    $order_id
                ),
                $order_id
            );

            return false;
        }

        // Check if the transaction is successful
        if ( 'SUCCEEDED' !== $transaction->Status ) {
            self::log( "Transaction Error: $transaction->ResultMessage" );

            // Send an e-mail warning for failed bankwire payins only
            if ( 'bank_wire' === $payment_type ) {
                Helper::warn_owner(
                    sprintf(
                        __( 'MangoPay Payin did not succeed for Resource ID: %1$s concerning WooCommerce Order ID: %2$s with response: %3$s', 'dokan' ),
                        $payload['RessourceId'],
                        $order_id,
                        $transaction->ResultMessage
                    ),
                    $order_id
                );

                return false;
            }

            // For card payments we just add an order note
            $order->add_order_note( sprintf( __( '[%1$s] transaction note: %2$s', 'dokan' ), Helper::get_gateway_title(), $transaction->ResultMessage ) );

            return false;
        }

        // If the payin was made using card, we don't need further checking
        if ( 'card' === $payment_type ) {
            return $order_id;
        }

        // Check that declared/debited funds match
        if ( $transaction->DebitedFunds->Amount !== $transaction->PaymentDetails->DeclaredDebitedFunds->Amount ) {
            Helper::warn_owner(
                sprintf(
                    __( 'MangoPay Payin debited funds do not match declared for Resource ID: %1$s', 'dokan' ),
                    $payload['RessourceId'],
                    $order_id
                ),
                $order_id
            );

            self::log( 'Transaction: ' . print_r( $transaction, true ) );

            return false;
        }

        // Check that declared/fees match
        if ( $transaction->Fees->Amount !== $transaction->PaymentDetails->DeclaredFees->Amount ) {
            Helper::warn_owner(
                sprintf(
                    __( 'MANGOPAY Payin fees do not match declared for Resource ID: %1$s', 'dokan' ),
                    $payload['RessourceId'],
                    $order_id
                ),
                $order_id
            );

            self::log( 'Transaction: ' . print_r( $transaction, true ) );

            return false;
        }

        return $order_id;
    }

    /**
     * Logs debug info
     *
     * @since 3.5.0
     *
     * @param mixed  $message
     * @param string $level
     *
     * @return void
     */
    public static function log( $message, $level = 'error' ) {
        Helper::log( $message, 'PayIn', $level );
    }
}
