<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Processor;

use WP_Error;
use Exception;
use MangoPay\Pagination;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;
use WeDevs\DokanPro\Modules\MangoPay\Support\Meta;
use WeDevs\DokanPro\Modules\MangoPay\Support\Processor;

/**
 * Class to process UBO.
 *
 * @since 3.5.0
 */
class Ubo extends Processor {

    /**
     * Retrieves UBO declarations of a user.
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param object 	 $pagination
     * @param object 	 $sorting
     *
     * @return array|false
     */
    public static function get_declarations( $user_id, $pagination = null, $sorting = null ) {
        try {
            if ( empty( $pagination ) ) {
                $pagination 			  = new Pagination();
                $pagination->Page 		  = 1;
                $pagination->ItemsPerPage = 100; //100 is the maximum
            }

            $declarations = static::config()->mangopay_api->UboDeclarations->GetAll( $user_id, $pagination, $sorting );
        } catch( Exception $e ) {
            Helper::log( sprintf( 'Could not fetch declarations for user: %s. Message: %s', $user_id, $e->getMessage() ), 'UBO' );
            return false;
        }

        return $declarations;
    }

    /**
     * Retrieves data of a single declaration.
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param int|string $ubo_id
     *
     * @return object|false
     */
    public static function get_declaration( $user_id, $ubo_id ) {
        try {
            $declaration = static::config()->mangopay_api->UboDeclarations->Get( $user_id, $ubo_id );
        } catch( Exception $e ) {
            Helper::log( sprintf( 'Could not fetch declaration for id: %s. Message: %s', $ubo_id, $e->getMessage() ), 'UBO' );
            return false;
        }

        return $declaration;
    }

    /**
     * Retrieves element of a UBO declaration
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param int|string $declaration_id
     * @param int|string $ubo_id
     *
     * @return object|false
     */
    public static function get_element( $user_id, $declaration_id, $ubo_id ) {
        try {
            $element = static::config()->mangopay_api->Uboelements->GetUbo( $user_id, $declaration_id, $ubo_id );
        } catch( Exception $e ) {
            Helper::log( sprintf( 'Could not fetch element for declaration: %s. Message: %s', $declaration_id, $e->getMessage() ), 'UBO' );
            return false;
        }

        return $element;
    }

    /**
     * Creates a UBO declaration
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     *
     * @return object|WP_Error
     */
    public static function create_declaration( $user_id ) {
        try {
            $response = static::config()->mangopay_api->UboDeclarations->Create( $user_id );
        } catch( Exception $e ) {
            Helper::log( sprintf( 'Could not create declaration for user: %s. Message: %s', $user_id, $e->getMessage() ), 'UBO' );
            return new WP_Error( 'dokan-mangopay-ubo-error', sprintf( __( 'Could not create declaration for user: %s. Error: %s', 'dokan' ), $user_id, $e->getMessage() ) );
        }

        return $response;
    }

    /**
     * Creates an element for UBO declaration.
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param int|string $declaration_id
     * @param int|string $ubo_id
     *
     * @return object|WP_Error
     */
    public static function create_element( $user_id, $declaration_id, $ubo_id ) {
        try {
            $response = static::config()->mangopay_api->UboDeclarations->CreateUbo( $user_id, $declaration_id, $ubo_id );
        } catch( Exception $e ) {
            Helper::log( sprintf( 'Could not create element for declaration: %s. Message: %s', $declaration_id, $e->getMessage() ), 'UBO' );
            return new WP_Error( 'dokan-mangopay-ubo-error', sprintf( __( 'Could not create element for declaration: %s. Error: %s', 'dokan' ), $user_id, $e->getMessage() ) );
        }

        return $response;
    }

    /**
     * Updates an UBO element.
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param int|string $declaration_id
     * @param int|string $ubo_id
     *
     * @return object|WP_Error
     */
    public static function update_element( $user_id, $declaration_id, $ubo_id ) {
        try {
            $response = static::config()->mangopay_api->UboDeclarations->UpdateUbo( $user_id, $declaration_id, $ubo_id );
        } catch( Exception $e ) {
            Helper::log( sprintf( 'Could not update element for declaration: %s. Message: %s', $declaration_id, $e->getMessage() ), 'UBO' );
            return new WP_Error( 'dokan-mangopay-ubo-error', sprintf( __( 'Could not update element for declaration: %s. Error: %s', 'dokan' ), $user_id, $e->getMessage() ) );
        }

        return $response;
    }

    /**
     * Requests for a UBO validation.
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param int|string $declaration_id
     *
     * @return object|WP_Error
     */
    public static function ask_for_validation( $user_id, $declaration_id ) {
        try {
            $response = static::config()->mangopay_api->UboDeclarations->SubmitForValidation( $user_id, $declaration_id ) ;
        } catch( Exception $e ) {
            Helper::log( sprintf( 'Could not submit the request for declaration: %s. Message: %s', $declaration_id, $e->getMessage() ), 'UBO' );
            return new WP_Error( 'dokan-mangopay-ubo-error', sprintf( __( 'Could not submit the request for declaration: %s. Error: %s', 'dokan' ), $user_id, $e->getMessage() ) );
        }

        return $response;
    }

    /**
     * CHecks if a user's UBO is verified.
     *
     * @since 3.5.0
     *
     * @param int|string $wp_user_id
     *
     * @return boolean
     */
    public static function is_user_eligible( $wp_user_id ) {
        $account_id = Meta::get_mangopay_account_id( $wp_user_id );
        if ( ! $account_id ) {
            //account is missing
            return false;
        }

        // Get declaration and check if there is a validated
        $ubos = static::get_declarations( $account_id );
        if ( ! $ubos ) {
            return false;
        }

        foreach ( $ubos as $ubo ) {
            if ( 'VALIDATED' === $ubo->Status ) {
                //means one is validated, we can skip rest of the check
                return true;
            }
        }

        return false;
    }

    /**
     * Retrieves status details of documents.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_status_details() {
        return array(
            'REFUSED'                                  => __( 'Refused', 'dokan' ),
            'VALIDATION_ASKED'                         => __( 'Validation asked', 'dokan' ),
            'CREATED'                                  => __( 'Created', 'dokan' ),
            'VALIDATED'                                => __( 'Validated', 'dokan' ),
            'INCOMPLETE'                               => __( 'Incomplete', 'dokan' ),
            'WRONG_UBO_INFORMATION'                    => __( 'Wrong UBO information', 'dokan' ),
            'MISSING_UBO'                              => __( 'Missing UBO', 'dokan' ),
            'UBO_IDENTITY_NEEDED'                      => __( 'UBO identity needed', 'dokan' ),
            'DOCUMENTS_NEEDED'                         => __( 'Documents needed', 'dokan' ),
            'SHAREHOLDERS_DECLARATION_NEEDED'          => __( 'Shareholders declaration needed', 'dokan' ),
            'ORGANIZATION_CHART_NEEDED'                => __( 'Organization chart needed', 'dokan' ),
            'SPECIFIC_CASE'                            => __( 'Specific case', 'dokan' ),
            'DECLARATION_DO_NOT_MATCH_UBO_INFORMATION' => __( 'Declaration do not match UBO information', 'dokan' ),
        );
    }
}
