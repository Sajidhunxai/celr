<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Processor;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use Exception;
use MangoPay\Sorting;
use MangoPay\KycLevel;
use MangoPay\Pagination;
use MangoPay\KycDocument;
use MangoPay\SortDirection;
use MangoPay\KycDocumentType;
use MangoPay\LegalPersonType;
use MangoPay\KycDocumentStatus;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;
use WeDevs\DokanPro\Modules\MangoPay\Support\Processor;
use MangoPay\KycDocumentRefusedReasonType as RefuseReason;

/**
 * Class to process KYC operations
 *
 * @since 3.5.0
 */
class Kyc extends Processor {

    // phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase

    /**
     * Retrieve info about an existing KYV document.
     *
     * @since 3.5.0
     *
     * @param int $kyc_document_id
     *
     * @return KycDocument|false
     */
    public static function get( $kyc_document_id ) {
        try {
            $response = static::config()->mangopay_api->KycDocuments->Get( $kyc_document_id );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not parse document: %s. Message: %s', $kyc_document_id, $e->getMessage() ), 'KYC', 'error' );
            return false;
        }

        return $response;
    }

    /**
     * Creates KYV document.
     *
     * @since 3.5.0
     *
     * @param int|string $account_id
     * @param string     $kyc_file_type
     *
     * @return KycDocument|\WP_Error
     */
    public static function create_document( $account_id, $kyc_file_type ) {
        try {
            $response = static::config()->mangopay_api->Users->CreateKycDocument( $account_id, $kyc_file_type );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not create document: %s. Message: %s', $kyc_file_type, $e->getMessage() ), 'KYC', 'error' );
            /* translators: error message */
            return new \WP_Error( 'dokan-mp-kyc-doc-create-error', sprintf( __( 'Could not create KYC document. Error: %s', 'dokan' ), $e->getMessage() ) );
        }

        return $response;
    }

    /**
     * Add page (file) to a document.
     *
     * @since 3.5.0
     *
     * @param int|string $account_id
     * @param int|string $kyc_document_id
     * @param resource   $file
     *
     * @return true|\WP_Error
     */
    public static function create_page( $account_id, $kyc_document_id, $file ) {
        try {
            $response = static::config()->mangopay_api->Users->CreateKycPageFromFile( $account_id, $kyc_document_id, $file );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not create pages for document: %s. Message: %s', $kyc_document_id, $e->getMessage() ), 'KYC', 'error' );
            /* translators: 1) document id, 2) error message */
            return new \WP_Error( 'dokan-mp-kyc-doc-create-error', sprintf( __( 'Could not create pages for document: %1$ss. Error: %2$s', 'dokan' ), $kyc_document_id, $e->getMessage() ) );
        }

        return $response;
    }

    /**
     * Updates a KYC document.
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param object     $kyc_document
     *
     * @return KycDocument|\WP_Error
     */
    public static function update( $user_id, $kyc_document ) {
        try {
            return static::config()->mangopay_api->Users->UpdateKycDocument( $user_id, $kyc_document );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not update document: %s. Message: %s', $kyc_document->Id, $e->getMessage() ), 'KYC', 'error' );
            /* translators: error message */
            return new \WP_Error( 'dokan-mp-kyc-doc-update-error', sprintf( __( 'Could not update document. Error: %s', 'dokan' ), $e->getMessage() ) );
        }
    }

    /**
     * Requests for validation of a KYC document.
     *
     * @since 3.7.4
     *
     * @param string $account_id
     * @param string $document_id
     *
     * @return KycDocument|\WP_Error
     */
    public static function ask_for_validation( $account_id, $document_id ) {
        try {
            $kyc_doc         = new KycDocument();
            $kyc_doc->Id     = $document_id;
            $kyc_doc->Status = KycDocumentStatus::ValidationAsked;

            return static::config()->mangopay_api->Users->UpdateKycDocument( $account_id, $kyc_doc );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not update document to ask for validation: %s. Message: %s', $document_id, $e->getMessage() ), 'KYC', 'error' );
            /* translators: error message */
            return new \WP_Error( 'dokan-mp-kyc-doc-update-error', sprintf( __( 'Could not ask for validation: %s', 'dokan' ), $e->getMessage() ) );
        }
    }

    /**
     * Filters KYC documents
     *
     * @since 3.5.0
     *
     * @param int|string                   $user_id    Mangopay ID of the user
     * @param \MangoPay\Pagination         $pagination (Optional) Including pagination rules
     * @param \MangoPay\Sorting            $sorting    (Optional) Including sorting rules
     * @param \MangoPay\FilterKycDocuments $filter     (Optional) Including filtering rules
     *
     * @return array
     */
    public static function filter( $user_id, $pagination = null, $sorting = null, $filter = null ) {
        try {
            if ( empty( $pagination ) || ! $pagination instanceof Pagination ) {
                $pagination               = new Pagination();
                $pagination->Page         = 1;
                $pagination->ItemsPerPage = 100; //100 is the maximum
            }

            if ( empty( $sorting ) || ! $sorting instanceof Sorting ) {
                $sorting = new Sorting();
            }
            $sorting->AddField( 'CreationDate', SortDirection::DESC );

            return static::config()->mangopay_api->Users->GetKycDocuments( $user_id, $pagination, $sorting, $filter );
        } catch ( Exception $e ) {
            Helper::log( sprintf( 'Could not list KYC documents. Error: %s', $e->getMessage() ), 'KYC', 'error' );
            return [];
        }
    }

    /**
     * Get the URL to upload a KYC Document for that user.
     *
     * @since 3.5.0
     *
     * @param string $mp_user_id
     *
     * @return string
     */
    public static function get_dashboard_url( $mp_user_id ) {
        return static::config()->get_dashboard_url() . "/User/$mp_user_id/Kyc";
    }

    /**
     * Tests if KYC validation is successful
     *
     * @since 3.5.0
     *
     * @param type $mp_user_id
     *
     * @return boolean|string
     */
    public static function is_valid( $mp_user_id ) {
        $user = User::get( $mp_user_id );
        if ( ! $user ) {
            return false;
        }

        // we are light or there is one not set we kill it
        if ( ! isset( $user->KYCLevel ) || KycLevel::Light === $user->KYCLevel ) {
            return false;
        }

        // Get required document types for a specific person type
        $required_docs = self::get_doc_types( $user );
        // get all documents of that user
        $submitted_docs = self::filter( $mp_user_id );

        // if we dont have the same count we have a problem
        foreach ( $required_docs as $doc_type => $doc ) {
            $found = false;
            foreach ( $submitted_docs as $doc ) {
                if ( $doc_type === $doc->Type && KycDocumentStatus::Validated === $doc->Status ) {
                    $found = true;
                    break;
                }
            }

            // If not found in the list of docs, we kick out
            if ( ! $found ) {
                return false;
            }
        }

        // If everything is fine we can return true
        return true;
    }

    /**
     * Retrieves required document types for a specific person type.
     *
     * @since 3.5.0
     *
     * @param \MangoPay\User $user
     *
     * @return array
     */
    public static function get_doc_types( \MangoPay\User $user ) {
        // Default for everyone
        $required_docs = [ KycDocumentType::IdentityProof => __( 'Identity Proof', 'dokan' ) ];

        // If not legal person type, we need no further execution
        if ( 'LEGAL' !== $user->PersonType ) {
            return $required_docs;
        }

        // Mandatory for all legal user
        $required_docs[ KycDocumentType::RegistrationProof ] = __( 'Registration Proof', 'dokan' );

        if ( empty( $user->LegalPersonType ) || LegalPersonType::Soletrader === $user->LegalPersonType ) {
            return $required_docs;
        }

        $required_docs[ KycDocumentType::ArticlesOfAssociation ] = __( 'Articles of Association', 'dokan' );

        if ( LegalPersonType::Business === $user->LegalPersonType && KycLevel::Regular !== $user->KYCLevel ) {
            $required_docs[ KycDocumentType::ShareholderDeclaration ] = __( 'Shareholder Declaration', 'dokan' );
        }

        return $required_docs;
    }

    /**
     * Retrieves refused reasons of KYC documents
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_refused_reasons() {
        return array(
            RefuseReason::DocumentUnreadable              => __( 'Document unreadable', 'dokan' ),
            RefuseReason::DocumentNotAccepted             => __( 'Document not acceptable', 'dokan' ),
            RefuseReason::DocumentHasExpired              => __( 'Document has expired', 'dokan' ),
            RefuseReason::DocumentIncomplete              => __( 'Document incomplete', 'dokan' ),
            RefuseReason::DocumentMissing                 => __( 'Document missing', 'dokan' ),
            RefuseReason::DocumentDoesNotMatchUserData    => __( 'Document does not match user data', 'dokan' ),
            RefuseReason::DocumentDoesNotMatchAccountData => __( 'Document does not match account data', 'dokan' ),
            RefuseReason::DocumentFalsified                => __( 'Document has been falsified', 'dokan' ),
            RefuseReason::SpecificCase                     => __( 'Specific case, please contact us', 'dokan' ),
            RefuseReason::UnderagePerson                  => __( 'Underage person', 'dokan' ),
            RefuseReason::TriggerPEPS                     => __( 'PEPS check triggered', 'dokan' ),
            RefuseReason::TriggerSanactionLists           => __( 'Sanction lists check triggered', 'dokan' ),
            RefuseReason::TriggerInterpol                 => __( 'Interpol check triggered', 'dokan' ),
            'OTHER'                                       => __( 'Other', 'dokan' ),
        );
    }

    /**
     * Retrieves KYC document statuses.
     *
     * @since 3.7.8
     *
     * @return array<string,string>
     */
    public static function get_doc_statuses() {
        return [
            KycDocumentStatus::Created         => __( 'Created', 'dokan' ),
            KycDocumentStatus::OutOfDate       => __( 'Out of Date', 'dokan' ),
            KycDocumentStatus::Refused         => __( 'Refused', 'dokan' ),
            KycDocumentStatus::Validated       => __( 'Validated', 'dokan' ),
            KycDocumentStatus::ValidationAsked => __( 'Validation Asked', 'dokan' ),
        ];
    }
    // phpcs:enable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase
}
