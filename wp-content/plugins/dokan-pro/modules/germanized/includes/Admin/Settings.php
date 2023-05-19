<?php
namespace WeDevs\DokanPro\Modules\Germanized\Admin;

use WeDevs\DokanPro\Modules\Germanized\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
* Admin class
*/
class Settings {

    /**
     * Load automatically when class initiate
     *
     * @since 3.3.1
     */
    public function __construct() {
        add_filter( 'dokan_settings_sections', [ $this, 'load_settings_section' ], 20 );
        add_filter( 'dokan_settings_fields', [ $this, 'load_settings_fields' ], 20 );
    }

    /**
     * Load admin settings section
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function load_settings_section( $section ) {
        $section[] = [
            'id'                   => 'dokan_germanized',
            'title'                => __( 'EU Compliance Fields', 'dokan' ),
            'icon_url'             => DOKAN_GERMANIZED_ASSETS_DIR . '/images/compliance.svg',
            'description'          => __( 'Configure EU Compliance Fields', 'dokan' ),
            'document_link'        => 'https://wedevs.com/docs/dokan/modules/eu-compliance-fields/',
            'settings_title'       => __( 'EU Compliance Settings', 'dokan' ),
            'settings_description' => __( 'Comply with EU guidelines when selling products on your marketplace with this integration. Configure which fields to display or make mandatory on your site.', 'dokan' ),
        ];

        return $section;
    }

    /**
     * Load all settings fields
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function load_settings_fields( $fields ) {
        $fields['dokan_germanized'] = [
            'vendor_fields' => [
                'name'    => 'vendor_fields',
                'label'   => __( 'Vendor Extra Fields', 'dokan' ),
                'type'    => 'multicheck',
                'desc'    => __( 'Checked fields will be used as extra fields for vendors.', 'dokan' ),
                'tooltip' => __( 'Allow extra input fields for vendor info. Uncheck the fields that won\'t be available to the vendors.', 'dokan' ),
                'default' => [
                    'dokan_company_name'      => 'dokan_company_name',
                    'dokan_company_id_number' => 'dokan_company_id_number',
                    'dokan_vat_number'        => 'dokan_vat_number',
                    'dokan_bank_name'         => 'dokan_bank_name',
                    'dokan_bank_iban'         => 'dokan_bank_iban',
                ],
                'options' => [
                    'dokan_company_name'      => Helper::get_company_name_label(),
                    'dokan_company_id_number' => Helper::get_company_id_label(),
                    'dokan_vat_number'        => Helper::get_vat_number_label(),
                    'dokan_bank_name'         => Helper::get_bank_name_label(),
                    'dokan_bank_iban'         => Helper::get_bank_iban_label(),
                ],
            ],
            'vendor_registration' => [
                'name'    => 'vendor_registration',
                'label'   => __( 'Display in Vendor Registration Form?', 'dokan' ),
                'type'    => 'switcher',
                'desc'    => __( 'Display vendors extra fields under vendor registration form', 'dokan' ),
                'default' => 'on',
            ],
            'customer_fields' => [
                'name'    => 'customer_fields',
                'label'   => __( 'Customer Extra Fields', 'dokan' ),
                'type'    => 'multicheck',
                'desc'    => __( 'Checked fields will be used as extra fields for customers.', 'dokan' ),
                'tooltip' => __( 'Allow extra input fields for customer billing and shipping address.', 'dokan' ),
                'default' => [
                    'billing_dokan_company_id_number' => 'billing_dokan_company_id_number',
                    'billing_dokan_vat_number'        => 'billing_dokan_vat_number',
                    'billing_dokan_bank_name'         => 'billing_dokan_bank_name',
                    'billing_dokan_bank_iban'         => 'billing_dokan_bank_iban',
                ],
                'options' => [
                    'billing_dokan_company_id_number' => Helper::get_customer_company_id_label(),
                    'billing_dokan_vat_number'        => Helper::get_customer_vat_number_label(),
                    'billing_dokan_bank_name'         => Helper::get_customer_bank_name_label(),
                    'billing_dokan_bank_iban'         => Helper::get_customer_bank_iban_label(),
                ],
            ],
            'enabled_germanized' => [
                'name'    => 'enabled_germanized',
                'label'   => __( 'Enable Germanized Support For Vendors', 'dokan' ),
                'type'    => 'switcher',
                'desc'    => __( 'This will add a new section in vendor product edit page with fields provided by Germanized for WooCommerce plugin', 'dokan' ),
                'default' => 'off',
            ],
            'override_invoice_number' => [
                'name'    => 'override_invoice_number',
                'label'   => __( 'Vendor\'s Can Override Invoice Number', 'dokan' ),
                'type'    => 'switcher',
                'desc'    => __( 'If you enable this setting, each vendor will be able to customize invoice number for their orders.', 'dokan' ),
                'default' => 'off',
            ],
        ];

        return $fields;
    }
}
