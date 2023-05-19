<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Processor;

use Exception;
use MangoPay\Address;
use MangoPay\BankAccount as MangoBankAccount;
use WeDevs\DokanPro\Modules\MangoPay\Support\Meta;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;
use WeDevs\DokanPro\Modules\MangoPay\Support\Processor;

/**
 * Bank accounts handler class
 *
 * @since 3.5.0
 */
class BankAccount extends Processor {

    /**
     * Retrieves a specific bank account data
     *
     * @since 3.5.0
     *
     * @param int|string $mp_user_id
     * @param int|string $existing_account_id
     *
     * @return object
     */
    public static function get( $mp_user_id, $existing_account_id ) {
        try {
            $bank_account = static::config()->mangopay_api->Users->GetBankAccount( $mp_user_id, $existing_account_id );
        } catch( Exception $e ) {
            return false;
        }

        return $bank_account;
    }

    /**
     * Retrieves all bank accounts for a user.
     *
     * @since 3.5.0
     *
     * @param int|string $mp_user_id
     *
     * @return array
     */
    public static function all( $mp_user_id ) {
        try {
            return static::config()->mangopay_api->Users->GetBankAccounts( $mp_user_id );
        } catch( Exception $e ) {
            Helper::log( $e->getMessage(), 'Bank Account' );
            return array();
        }
    }

    /**
     * Register a user's bank account in Mangopay profile
     *
     * @since 3.5.0
     *
     * @see: https://github.com/Mangopay/mangopay2-php-sdk/blob/master/demos/workflow/scripts/bankaccount.php
     *
     * @param int|string $mp_user_id
     * @param int|string $wp_user_id
     * @param array      $account_data
     *
     * @return int|\WP_Error
     */
    public static function create( $mp_user_id, $wp_user_id, $account_data ) {
        $mp_user = User::get( $mp_user_id );
        if ( ! $mp_user ) {
            return new \WP_Error( 'dokan-mangopay-bank-account-create-error', __( 'No Mangopay user found. Please sign up first.', 'dokan' ) );
        }

        if ( 'GB' === $account_data['type'] ) {
            if ( 'LEGAL' === $mp_user->PersonType ) {
                $first_name = $mp_user->LegalRepresentativeFirstName;
                $last_name  = $mp_user->LegalRepresentativeLastName;
            } else {
                $first_name = $mp_user->FirstName;
                $last_name  = $mp_user->LastName;
            }

            if (
                $first_name !== $account_data['name'] &&
                $last_name !== $account_data['name'] &&
                "$first_name $last_name" !== $account_data['name']
            ) {
                return new \WP_Error(
                    'dokan-mangopay-bank-account-create-error',
                    __( 'For payments to GB accounts, Account holder\'s name must match either First Name and Last Name of the Mangopay user account.', 'dokan' )
                );
            }
        }

        $bank_account             = new MangoBankAccount();
        $bank_account->Type       = $account_data['type'];
        $bank_account->UserId     = $mp_user_id;
        $detail_class_name        = "MangoPay\BankAccountDetails{$account_data['type']}";
        $bank_account->Details    = new $detail_class_name();
        $account_types            = Helper::get_bank_account_types_fields();
        $errors                   = array();
        // If there is an existing bank account, fetch it first to get the redacted info we did not store
        $existing_bank_account_id = Meta::get_bank_account_id( $wp_user_id, $account_data['type'] );
        $existing_bank_account    = false;
        if ( ! empty( $existing_bank_account_id ) ) {
            $existing_bank_account = self::get( $mp_user_id, $existing_bank_account_id );
        }

        /*
         * It is necessary because if $existing_bank_account_id is empty or invalid,
         * the 'self::get()' method will return all bank accounts of the user.
         */
        if ( ! is_object( $existing_bank_account ) || ! $existing_bank_account instanceof MangoBankAccount ) {
            $existing_bank_account = false;
        }

        foreach ( $account_types[ $account_data['type'] ] as $field_name => $field_data ) {
            if (
                ! empty( $existing_bank_account ) &&
                ! empty( $field_data['unique'] ) &&
                $account_data[ $account_data['type'] ][ $field_name ] === $existing_bank_account->Details->{$field_data['property']}
            ) {
                return new \WP_Error( 'dokan-mangopay-bank-account-create-error', __( 'Bank account already exists', 'dokan' ) );
            }

            if ( isset( $account_data[ $account_data['type'] ][ $field_name ] ) ) {
                $bank_account->Details->{$field_data['property']} = $account_data[ $account_data['type'] ][ $field_name ];
            } elseif ( ! empty( $field_data['required'] ) ) {
                $errors[] = sprintf( __( '%s is required', 'dokan' ), $field_data['label'] );
            }
        }

        $common_fields = Helper::get_bank_account_common_fields();
        foreach ( $common_fields as $name => $data ) {
            if ( 'state' === $name && ! in_array( $account_data['country'], array( 'US', 'MX', 'CA' ) ) ) {
                continue;
            }

            if ( ! empty( $data['required'] ) && empty( $account_data[ $name ] ) ) {
                $errors[] = sprintf( __( '%s is required', 'dokan' ), $data['label'] );
            }
        }

        if ( ! empty( $errors ) ) {
            return new \WP_Error( 'dokan-mangopay-bank-account-create-error', implode( '<br>', $errors ) );
        }

        $bank_account->OwnerName                  = $account_data['name'];
        $bank_account->OwnerAddress               = new Address();
        $bank_account->OwnerAddress->AddressLine1 = $account_data['address1'];
        $bank_account->OwnerAddress->AddressLine2 = $account_data['address2'];
        $bank_account->OwnerAddress->City         = $account_data['city'];
        $bank_account->OwnerAddress->Country 	  = $account_data['country'];
        $bank_account->OwnerAddress->PostalCode   = $account_data['postcode'];
        $bank_account->OwnerAddress->Region 	  = empty( $account_data['state'] ) ? $account_data['city'] : $account_data['state'];
        $bank_account->Tag                        = 'wp_user_id:' . $wp_user_id;
        $bank_account->Active                     = true;

        try {
            $bank_account = static::config()->mangopay_api->Users->CreateBankAccount( $mp_user_id, $bank_account );

            if ( ! empty( $existing_bank_account ) ) {
                $existing_bank_account->Active = false;
                static::config()->mangopay_api->Users->UpdateBankAccount( $mp_user_id, $existing_bank_account );
            }
        } catch( Exception $e ) {
            Helper::log( 'Could not create bank account. Message: ' . $e->getMessage(), 'Bank Account', 'error' );
            Helper::log( print_r( $bank_account, true ), 'Bank Account' );
            return new \WP_Error( 'dokan-mangopay-bank-account-create-error', sprintf( __( 'Could not create bank account. Error: %s', 'dokan' ), $e->getMessage() ) );
        }

        Meta::update_bank_account_id( $wp_user_id, $bank_account->Id, $bank_account->Type );
        Meta::update_active_bank_account( $wp_user_id, $bank_account->Id );

        return $bank_account->Id;
    }

    /**
     * Saves metadata for payment through bank
     *
     * @since 3.5.0
     *
     * @param object $order
     * @param object $payment_data
     *
     * @return void
     */
    public static function save_metadata( $order, $payment_data ) {
        static::update_metadata( $order, $payment_data, 'bank_wire' );
    }
}
