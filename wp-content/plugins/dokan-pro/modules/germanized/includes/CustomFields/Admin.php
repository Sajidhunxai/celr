<?php
namespace WeDevs\DokanPro\Modules\Germanized\CustomFields;

use WeDevs\Dokan\Vendor\Vendor;
use WeDevs\DokanPro\Modules\Germanized\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Admin
 * @package WeDevs\DokanPro\Modules\Germanized\CustomFields
 * @since 3.3.1
 */
class Admin {
    /**
     * Admin constructor.
     */
    public function __construct() {
        // store custom fields data during add/edit vendors
        add_filter( 'dokan_before_create_vendor', [ $this, 'update_vendor_custom_fields' ], 10, 2 );
        add_action( 'dokan_before_update_vendor', [ $this, 'update_vendor_custom_fields' ], 10, 2 );

        // populated custom fields data during creating vendor instance
        add_filter( 'dokan_vendor_shop_data', [ $this, 'populate_shop_data' ], 10, 2 );
        add_filter( 'dokan_vendor_to_array', [ $this, 'populate_shop_data' ], 10, 2 );

        // custom fields label for js
        add_filter( 'dokan_admin_localize_script', [ $this, 'add_localized_data' ], 10, 1 );
    }

    /**
     * Custom fields label to use in js files
     *
     * @param array $localized_data
     *
     * @since 3.3.1
     * @return array
     */
    public function add_localized_data( $localized_data ) {
        // vendor fields label
        $localized_data['dokan_cf_vendor_labels'] = [
            'company_name'        => Helper::get_company_name_label(),
            'company_id_number'   => Helper::get_company_id_label(),
            'vat_number'          => Helper::get_vat_number_label(),
            'bank_name'           => Helper::get_bank_name_label(),
            'bank_iban'           => Helper::get_bank_iban_label(),
        ];
        // vendor enabled fields
        $localized_data['dokan_cf_vendor_fields'] = Helper::get_seller_fields();
        return $localized_data;
    }

    /**
     * Populated custom fields data during creating vendor instance
     *
     * @param array $shop_info
     * @param Vendor $vendor
     * @since 3.3.1
     * @return array
     */
    public function populate_shop_data( $shop_info, $vendor ) {
        $shop_info['company_name']      = $vendor->get_meta( 'dokan_company_name', true );
        $shop_info['vat_number']        = $vendor->get_meta( 'dokan_vat_number', true );
        $shop_info['company_id_number'] = $vendor->get_meta( 'dokan_company_id_number', true );
        $shop_info['bank_name']         = $vendor->get_meta( 'dokan_bank_name', true );
        $shop_info['bank_iban']         = $vendor->get_meta( 'dokan_bank_iban', true );

        return $shop_info;
    }

    /**
     * Store custom fields data during add/edit vendors
     *
     * @param int $vendor
     * @param array $data
     *
     * @return Vendor
     * @since 3.3.1
     */
    public function update_vendor_custom_fields( $vendor_id, $data ) {
        // get vendor object
        $vendor = new Vendor( $vendor_id );
        $enabled_fields = Helper::is_fields_enabled_for_seller();

        // store company name
        if ( $enabled_fields['dokan_company_name'] ) {
            $company_name = isset( $data['company_name'] ) ? sanitize_text_field( $data['company_name'] ) : '';
            $vendor->update_meta( 'dokan_company_name', $company_name );
            $vendor->update_meta( 'billing_company', $company_name );
        }

        // store vat number
        if ( $enabled_fields['dokan_vat_number'] ) {
            $vat_number = isset( $data['vat_number'] ) ? sanitize_text_field( $data['vat_number'] ) : '';
            $vendor->update_meta( 'dokan_vat_number', $vat_number );
        }

        // store company id number
        if ( $enabled_fields['dokan_company_id_number'] ) {
            $company_id_number = isset( $data['company_id_number'] ) ? sanitize_text_field( $data['company_id_number'] ) : '';
            $vendor->update_meta( 'dokan_company_id_number', $company_id_number );
        }

        // store bank name
        if ( $enabled_fields['dokan_bank_name'] ) {
            $bank_name = isset( $data['bank_name'] ) ? sanitize_text_field( $data['bank_name'] ) : '';
            $vendor->update_meta( 'dokan_bank_name', $bank_name );
        }

        // store bank iban number
        if ( $enabled_fields['dokan_bank_iban'] ) {
            $bank_iban = isset( $data['bank_iban'] ) ? sanitize_text_field( $data['bank_iban'] ) : '';
            $vendor->update_meta( 'dokan_bank_iban', $bank_iban );
        }
    }
}
