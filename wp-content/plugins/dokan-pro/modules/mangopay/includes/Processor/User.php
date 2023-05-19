<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Processor;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WP_Error;
use Exception;
use MangoPay\Address;
use MangoPay\UserLegal;
use MangoPay\Pagination;
use MangoPay\UserNatural;
use WeDevs\DokanPro\Modules\MangoPay\Support\Meta;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;
use WeDevs\DokanPro\Modules\MangoPay\Support\Processor;

/**
 * Class for processing mangopay users
 *
 * @since 3.5.0
 */
class User extends Processor {

    /**
     * User type for customers.
     *
     * @var string
     *
     * @since 3.7.8
     */
    const PAYER_TYPE = 'PAYER';

    /**
     * User type for sellers.
     *
     * @var string
     *
     * @since 3.7.8
     */
    const OWNER_TYPE = 'OWNER';

    /**
     * Retrieves a mangopay user data
     *
     * @since 3.5.0
     *
     * @param int|string $mangopay_user_id
     *
     * @return UserNatural|UserLegal|false
     */
    public static function get( $mangopay_user_id ) {
        if ( empty( $mangopay_user_id ) ) {
            return false;
        }

        try {
            $user = static::config()->mangopay_api->Users->Get( $mangopay_user_id );
        } catch( Exception $e ) {
            self::log( sprintf( 'Could not fetch user for ID: %s. Message: %s', $mangopay_user_id, $e->getMessage() ) );
            return false;
        }

        return $user;
    }

    /**
     * Creates a Mangopay user.
     *
     * Checks if wp_user already has associated mangopay account
     * if yes, it updates the user. else creates it.
     *
     * @since 3.5.0
     * @since 3.7.8 Added parameter `$is_buyer`
     *
     * @param string $wp_user_id The WP user ID
     * @param array  $data       Array of data for the user
     * @param bool   $is_buyer   Whether the user is a BUYER or OWNER. Default `true` (BUYER)
     *
     * @return int|\WP_Error
     */
    public static function create( $wp_user_id, $data = [], $is_buyer = true ) {
        $wp_userdata = get_userdata( $wp_user_id );

        if ( ! $wp_userdata ) {
            return new WP_Error( 'dokan-mangopay-user-create-error', __( 'No valid user found!', 'dokan' ) );
        }

        $data['email']      = $wp_userdata->user_email;
        $data['first_name'] = $wp_userdata->first_name;
        $data['last_name']  = $wp_userdata->last_name;

        if ( empty( $data['first_name'] ) || empty( $data['last_name'] ) ) {
            return new WP_Error(
                'dokan-mangopay-user-create-error',
                sprintf(
                    __( 'Both First name and Last name are required to sign up. Please complete your <a href="%s">profile</a> first.', 'dokan' ),
                    esc_url_raw( dokan_get_navigation_url( 'edit-account' ) )
                )
            );
        }

        if ( empty( $data['address1'] ) ) {
            $data['address1'] = get_user_meta( $wp_user_id, 'billing_address_1', true );
        } else {
            update_user_meta( $wp_user_id, 'billing_address_1', $data['address1'] );
        }

        if ( empty( $data['address2'] ) ) {
            $data['address2'] = get_user_meta( $wp_user_id, 'billing_address_2', true );
        } else {
            update_user_meta( $wp_user_id, 'billing_address_2', $data['address2'] );
        }

        if ( empty( $data['city'] ) ) {
            $data['city'] = get_user_meta( $wp_user_id, 'billing_city', true );
        } else {
            update_user_meta( $wp_user_id, 'billing_city', $data['city'] );
        }

        if ( empty( $data['postcode'] ) ) {
            $data['postcode'] = get_user_meta( $wp_user_id, 'billing_postcode', true );
        } else {
            update_user_meta( $wp_user_id, 'billing_postcode', $data['postcode'] );
        }

        if ( empty( $data['state'] ) ) {
            $data['state'] = get_user_meta( $wp_user_id, 'billing_state', true );
        } else {
            update_user_meta( $wp_user_id, 'billing_state', $data['state'] );
        }

        if ( empty( $data['birthday'] ) ) {
            $data['birthday'] = Meta::get_user_birthday( $wp_user_id );
        }

        if ( empty( $data['nationality'] ) ) {
            $data['nationality'] = Meta::get_user_nationality( $wp_user_id );
        }

        if ( empty( $data['country'] ) ) {
            $data['country'] = get_user_meta( $wp_user_id, 'billing_country', true );
            if ( empty( $data['country'] ) && ! empty( $data['nationality'] ) ) {
                $data['country'] = $data['nationality'];
            }
        } else {
            update_user_meta( $wp_user_id, 'billing_country', $data['country'] );
        }

        if ( empty( $data['status'] ) ) {
            $data['status'] = 'NATURAL';
        }

        $mp_user_id = Meta::get_mangopay_account_id( $wp_user_id );
        if ( ! empty( $mp_user_id ) ) {
            $mp_user = self::get( $mp_user_id );
            if ( $mp_user ) {
                return self::update( $wp_user_id, $data, false );
            }
        }

        if ( 'LEGAL' === $data['status'] ) {
            $user                                           = new UserLegal();
            $user->Name                                     = $data['company_name'];
            $user->LegalPersonType                          = $data['business_type'];
            $user->LegalRepresentativeFirstName             = $data['first_name'];
            $user->LegalRepresentativeLastName              = $data['last_name'];
            $user->LegalRepresentativeEmail                 = $data['email'];
            $user->LegalRepresentativeAddress               = new Address();
            $user->LegalRepresentativeAddress->AddressLine1 = ! empty( $data['address1'] ) ? $data['address1'] : '';
            $user->LegalRepresentativeAddress->AddressLine2 = ! empty( $data['address2'] ) ? $data['address2'] : '';
            $user->LegalRepresentativeAddress->City         = ! empty( $data['city'] ) ? $data['city'] : '';
            $user->LegalRepresentativeAddress->PostalCode   = ! empty( $data['postcode'] ) ? $data['postcode'] : '';
            $user->LegalRepresentativeAddress->Region       = ! empty( $data['state'] ) ? $data['state'] : '';
            $user->LegalRepresentativeAddress->Country      = ! empty( $data['country'] ) ? $data['country'] : '';

            if ( ! $is_buyer ) {
                $user->CompanyNumber                         = $data['company_number'];
                $user->LegalRepresentativeBirthday           = Helper::format_date( $data['birthday'] );
                $user->LegalRepresentativeNationality        = $data['nationality'];
                $user->LegalRepresentativeCountryOfResidence = $data['country'];
                $user->HeadquartersAddress                   = new Address();
                $user->HeadquartersAddress->AddressLine1     = $data['company_address1'];
                $user->HeadquartersAddress->AddressLine2     = $data['company_address2'];
                $user->HeadquartersAddress->Country          = $data['company_country'];
                $user->HeadquartersAddress->City             = $data['company_city'];
                $user->HeadquartersAddress->PostalCode       = $data['company_postcode'];
                $user->HeadquartersAddress->Region           = $data['company_state'];
            }
        } else {
            $user            = new UserNatural();
            $user->FirstName = $data['first_name'];
            $user->LastName  = $data['last_name'];

            if ( ! empty( $data['address1'] ) && ! empty( $data['city'] ) && ! empty( $data['country'] ) ) {
                $user->Address               = new Address();
                $user->Address->AddressLine1 = $data['address1'];
                $user->Address->AddressLine2 = $data['address2'];
                $user->Address->City         = $data['city'];
                $user->Address->PostalCode   = $data['postcode'];
                $user->Address->Region       = $data['state'];
                $user->Address->Country      = $data['country'];
            }

            if ( ! $is_buyer ) {
                $user->Birthday           = ! empty( $data['birthday'] ) ? Helper::format_date( $data['birthday'] ) : '';
                $user->Nationality        = $data['nationality'];
                $user->CountryOfResidence = $data['country'];
            }
        }

        $user->PersonType = $data['status'];
        $user->Email      = $data['email'];
        $user->Tag        = "wp_user_id:$wp_user_id";

        if ( ! $is_buyer ) {
            $user->UserCategory               = self::OWNER_TYPE;
            $user->TermsAndConditionsAccepted = ! empty( $data['terms'] );
        } else {
            $user->UserCategory = self::PAYER_TYPE;
        }

        try {
            $mango_user = static::config()->mangopay_api->Users->Create( $user );
        } catch ( Exception $e ) {
            self::log( sprintf( __( 'Could not create Mangopay user for ID: %s. Error: %s.', 'dokan' ), $wp_user_id, $e->getMessage() ) );
            self::log( 'Object: ' . print_r( $user, true ) );
            return new WP_Error( 'add-user-error', sprintf( __( 'Could not create Mangopay user. Error: %s', 'dokan' ), $e->getMessage() ) );
        }

        Meta::update_mangopay_account_id( $wp_user_id, $mango_user->Id );
        Meta::update_user_status( $wp_user_id, $mango_user->PersonType );

        if ( ! $is_buyer ) {
            Meta::update_user_birthday( $wp_user_id, $data['birthday'] );
            Meta::update_user_nationality( $wp_user_id, $data['nationality'] );
        }

        if ( ! empty( $mango_user->LegalPersonType ) ) {
            Meta::update_user_business_type( $wp_user_id, $mango_user->LegalPersonType );
        }

        // If new user has no wallet yet, create one
        Wallet::create( $mango_user->Id );

        return $mango_user->Id;
    }

    /**
     * Updates a mangopay user.
     *
     * @since 3.5.0
     * @since 3.7.8 Removed parameter `$mp_user_id` and added parameter `$is_buyer`
     *
     * @param string|int wp_user_id The WP user ID
     * @param array 	 $data      Array of data to update
     * @param bool       $is_buyer  Whether the user is a BUYER or OWNER. Default `true` (BUYER)
     *
     * @return object|\WP_Error
     */
    public static function update( $wp_user_id, $data, $is_buyer = true ) {
        $mp_user_id = Meta::get_mangopay_account_id( $wp_user_id );
        $user       = self::get( $mp_user_id );

        if ( ! $user ) {
            return new WP_Error( 'no-mangopay-user', __( 'No user found for the given id', 'dokan' ) );
        }

        $update_needed = false;
        switch ( $user->PersonType ) {
            case 'NATURAL':
                if ( ! empty( $data['first_name'] ) && $user->FirstName !== $data['first_name'] ) {
                    $user->FirstName = $data['first_name'];
                    $update_needed   = true;
                }

                if ( ! empty( $data['last_name'] ) && $user->LastName !== $data['last_name'] ) {
                    $user->LastName = $data['last_name'];
                    $update_needed  = true;
                }

                if ( ! empty( $data['address1'] ) && $user->Address->AddressLine1 !== $data['address1'] ) {
                    $user->Address->AddressLine1 = $data['address1'];
                    $update_needed               = true;
                }

                if ( ! empty( $data['address2'] ) && $user->Address->AddressLine2 !== $data['address2'] ) {
                    $user->Address->AddressLine2 = $data['address2'];
                    $update_needed               = true;
                }

                if ( ! empty( $data['city'] ) && $user->Address->City !== $data['city'] ) {
                    $user->Address->City = $data['city'];
                    $update_needed       = true;
                }

                if ( ! empty( $data['postcode'] ) && $user->Address->PostalCode !== $data['postcode'] ) {
                    $user->Address->PostalCode = $data['postcode'];
                    $update_needed             = true;
                }

                if ( ! empty( $data['country'] ) && ( $user->Address->Country !== $data['country'] || $user->CountryOfResidence !== $data['country'] ) ) {
                    $user->Address->Country   = $data['country'];
                    $user->CountryOfResidence = $data['country'];
                    $update_needed            = true;
                }

                if ( ! empty( $data['state'] ) && in_array( $user->Address->Country, array( 'US', 'MX', 'CA' ) ) && $user->Address->Region !== $data['state'] ) {
                    $user->Address->Region = $data['state'];
                    $update_needed         = true;
                }

                if ( ! empty( $data['email'] ) && $user->Email !== $data['email'] ) {
                    $user->Email   = $data['email'];
                    $update_needed = true;
                }

                break;

            default: // Business or Legal user
                if ( isset( $data['company_number'] ) && ! empty( trim( $data['company_number'] ) ) ) {
                    //remove spaces
                    $company_number = str_replace( ' ', '', $data['company_number'] );
                    if ( ! isset( $user->CompanyNumber ) || $user->CompanyNumber !== $company_number ) {
                        $user->CompanyNumber = $company_number;
                        $update_needed       = true;
                    }
                }

                if ( ! empty( $data['company_name'] ) && $user->Name !== $data['company_name'] ) {
                    $user->Name    = $data['company_name'];
                    $update_needed = true;
                }

                if ( ! empty( $data['company_address1'] ) && $user->HeadquartersAddress->AddressLine1 !== $data['company_address1'] ) {
                    $user->HeadquartersAddress->AddressLine1 = $data['company_address1'];
                    $update_needed                           = true;
                }

                if ( ! empty( $data['company_address2'] ) && $user->HeadquartersAddress->AddressLine2 !== $data['company_address2'] ) {
                    $user->HeadquartersAddress->AddressLine2 = $data['company_address2'];
                    $update_needed                           = true;
                }

                if ( ! empty( $data['company_city'] ) && $user->HeadquartersAddress->City !== $data['company_city'] ) {
                    $user->HeadquartersAddress->City = $data['company_city'];
                    $update_needed                   = true;
                }

                if ( ! empty( $data['company_postalcode'] ) && $user->HeadquartersAddress->PostalCode !== $data['company_postalcode'] ) {
                    $user->HeadquartersAddress->PostalCode = $data['company_postalcode'];
                    $update_needed                         = true;
                }

                if ( ! empty( $data['company_country'] ) && $user->HeadquartersAddress->Country !== $data['company_country'] ) {
                    $user->HeadquartersAddress->Country = $data['company_country'];
                    $update_needed                      = true;
                }

                if ( ! empty( $data['company_state'] ) && in_array( $user->HeadquartersAddress->Country, array( 'US', 'MX', 'CA' ) ) && $user->HeadquartersAddress->Region !== $data['company_state'] ) {
                    $user->HeadquartersAddress->Region = $data['company_state'];
                    $update_needed                     = true;
                }

                if ( ! empty( $data['first_name'] ) && $user->LegalRepresentativeFirstName !== $data['first_name'] ) {
                    $user->LegalRepresentativeFirstName = $data['first_name'];
                    $update_needed                      = true;
                }

                if ( ! empty( $data['last_name'] ) && $user->LegalRepresentativeLastName !== $data['last_name'] ) {
                    $user->LegalRepresentativeLastName = $data['last_name'];
                    $update_needed                     = true;
                }

                if ( ! empty( $data['birthday'] ) ) {
                    $timestamp = Helper::format_date( $data['birthday'] );
                    if ( $user->LegalRepresentativeBirthday !== $timestamp ) {
                        $user->LegalRepresentativeBirthday = $timestamp;
                        $update_needed                     = true;
                    }
                }

                if ( ! empty( $data['nationality'] ) && $user->LegalRepresentativeNationality !== $data['nationality'] ) {
                    $user->LegalRepresentativeNationality = $data['nationality'];
                    $update_needed                        = true;
                }

                if ( ! empty( $data['email'] ) && ( $user->LegalRepresentativeEmail !== $data['email'] || $user->Email !== $data['email'] ) ) {
                    $user->LegalRepresentativeEmail = $data['email'];
                    $user->Email                    = $data['email'];
                    $update_needed                  = true;
                }

                if ( ! empty( $data['country'] ) && ( $user->LegalRepresentativeCountryOfResidence !== $data['country'] || $user->LegalRepresentativeAddress->Country !== $data['country'] ) ) {
                    $user->LegalRepresentativeCountryOfResidence = $data['country'];
                    $user->LegalRepresentativeAddress->Country   = $data['country'];
                    $update_needed                               = true;
                }

                if ( ! empty( $data['state'] ) && in_array( $user->LegalRepresentativeCountryOfResidence, array( 'US', 'MX', 'CA' ) ) && $user->HeadquartersAddress->Region !== $data['state'] ) {
                    $user->LegalRepresentativeAddress->Region = $data['state'];
                    $update_needed                            = true;
                }

                if ( ! empty( $data['city'] ) && $user->LegalRepresentativeAddress->City !== $data['city'] ) {
                    $user->LegalRepresentativeAddress->City = $data['city'];
                    $update_needed                          = true;
                }

                if ( ! empty( $data['postcode'] ) && $user->LegalRepresentativeAddress->PostalCode !== $data['postcode'] ) {
                    $user->LegalRepresentativeAddress->PostalCode = $data['postcode'];
                    $update_needed                                = true;
                }

                if ( ! empty( $data['address1'] ) && $user->LegalRepresentativeAddress->AddressLine1 !== $data['address1'] ) {
                    $user->LegalRepresentativeAddress->AddressLine1 = $data['address1'];
                    $update_needed                                  = true;
                }

                if ( ! empty( $data['address2'] ) && $user->LegalRepresentativeAddress->AddressLine2 !== $data['address2'] ) {
                    $user->LegalRepresentativeAddress->AddressLine2 = $data['address2'];
                    $update_needed                                  = true;
                }

                if ( ! empty( $data['business_type'] ) && $user->LegalPersonType !== $data['business_type'] ) {
                    $user->LegalPersonType = $data['business_type'];
                    $update_needed         = true;
                }

                if ( ! empty( $data['terms'] ) ) {
                    $user->TermsAndConditionsAccepted = true;
                    $update_needed                    = true;
                }
        }

        if ( empty( $user->UserCategory ) || ! in_array( $user->UserCategory, [ self::PAYER_TYPE, self::OWNER_TYPE ], true ) ) {
            $user->UserCategory = $is_buyer ? self::PAYER_TYPE : self::OWNER_TYPE;
            $update_needed      = true;
        }

        if ( $update_needed ) {
            try {
                $updated_user = static::config()->mangopay_api->Users->Update( $user );
            } catch( Exception $e ) {
                self::log( sprintf( 'Could not update the user. Message: %s', $e->getMessage() ) );
                self::log( 'Object: ' . print_r( $user, true ) );
                self::log( 'Given Data: ' . print_r( $data, true ) );
                return new WP_Error( 'dokan-mangopay-user-update-error', sprintf( __( 'Could not update the user. Error: %s' ), $e->getMessage() ) );
            }

            if ( ! $is_buyer ) {
                if ( ! empty( $data['birthday'] ) ) {
                    Meta::update_user_birthday( $wp_user_id, $data['birthday'] );
                }

                if ( ! empty( $data['nationality'] ) ) {
                    Meta::update_user_nationality( $wp_user_id, $data['nationality'] );
                }
            }

            if ( ! empty( $updated_user->LegalPersonType ) ) {
                Meta::update_user_business_type( $wp_user_id, $updated_user->LegalPersonType );
            }

            return $updated_user->Id;
        }

        return $user->Id;
    }

    /**
     * Get the URL to access a User's Mangopay dashboard page.
     *
     * @since 3.5.0
     *
     * @param int $mp_user_id
     *
     * @return string
     */
    public static function get_dashboard_url( $mp_user_id ) {
        return static::config()->get_dashboard_url() . "/User/$mp_user_id/Details";
    }

    /**
     * Get the URL to access a User's Mangopay transactions page.
     *
     * @since 3.5.0
     *
     * @param int $mp_user_id
     *
     * @return string
     */
    public static function get_transaction_url( $mp_user_id ) {
        return static::config()->get_dashboard_url() . "/User/$mp_user_id/Transactions";
    }

    /**
     * Get the URL to access a Wallet's MP Transactions page.
     *
     * @since 3.5.0
     *
     * @param int $mp_user_id
     * @param int $mp_wallet_id
     *
     * @return string
     */
    public static function get_wallet_transaction_url( $mp_user_id, $mp_wallet_id ) {
        return static::config()->get_dashboard_url() . "/User/$mp_user_id/Wallets/$mp_wallet_id";
    }

    /**
     * Get document by mp doc id.
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param int|string $kyc_doc_id
     *
     * @return object
     */
    public static function get_kyc( $user_id,$kyc_doc_id ) {
        return static::config()->mangopay_api->Users->GetKycDocument( $user_id, $kyc_doc_id );
    }

    /**
     * Get all registered cards for this user ID.
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     *
     * @return array
     */
    public static function get_cards( $user_id ) {
        try {
            $mp_user_id               = Meta::get_mangopay_account_id( $user_id );
            $pagination               = new Pagination();
            $pagination->Page         = 1;
            $pagination->ItemsPerPage = 100;

            //get cards (page 1 limited to 100 first)
            $cards = static::config()->mangopay_api->Users->GetCards( $mp_user_id, $pagination );
            foreach ( $cards as $key => $card ) {
                if ( $card->Active === NULL || $card->Active === false ) {
                    unset( $cards[ $key ] );
                }
            }
        } catch( Exception $e ) {
            $cards = array();
        }

        return $cards;
    }

    /**
     * Synchronizes users account data
     *
     * @since 3.5.0
     *
     * @param int|string $wp_user_id
     *
     * @return void
     */
    public static function sync_account_data( $wp_user_id ) {
        $wp_userdata 			 = get_userdata( $wp_user_id );
        $user_data 				 = array();
        $user_data['user_email'] = $wp_userdata->user_email;

        // For first and last name, we take the billing info if available
        $user_data['first_name'] = get_user_meta( $wp_user_id, 'billing_first_name', true );
        if ( empty( $user_data['first_name'] ) ) {
            $user_data['first_name'] = ! empty( $_POST['first_name'] )
                                    ? sanitize_text_field( wp_unslash( $_POST['first_name'] ) )
                                    : get_user_meta( $wp_user_id, 'first_name', true );
        }

        $user_data['last_name'] = get_user_meta( $wp_user_id, 'billing_last_name', true );
        if ( empty( $user_data['last_name'] ) ) {
            $user_data['last_name'] = ! empty( $_POST['last_name'] )
                                    ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) )
                                    : get_user_meta( $wp_user_id, 'last_name', true );
        }

        $user_data['address1'] = get_user_meta( $wp_user_id, 'billing_address_1', true );
        $user_data['address2'] = get_user_meta( $wp_user_id, 'billing_address_2', true );
        $user_data['city']     = get_user_meta( $wp_user_id, 'billing_city', true );
        $user_data['postcode'] = get_user_meta( $wp_user_id, 'billing_postcode', true );
        $user_data['country']  = get_user_meta( $wp_user_id, 'billing_country', true );
        $user_data['state']    = get_user_meta( $wp_user_id, 'billing_country', true );

        if ( isset( $_POST['billing_state'] ) ) {
            $user_data['state'] = get_user_meta( $wp_user_id, 'billing_state', true );
        }

        if ( empty( $user_data['status'] ) ) {
            $user_data['status'] = 'NATURAL';
        }

        return self::create( $wp_user_id, $user_data );
    }

    /**
     * Logs user related debugging info.
     *
     * @since 3.5.0
     *
     * @param string $message
     * @param string $level
     *
     * @return void
     */
    public static function log( $message, $level = 'debug' ) {
        Helper::log( $message, 'User', $level );
    }
}
