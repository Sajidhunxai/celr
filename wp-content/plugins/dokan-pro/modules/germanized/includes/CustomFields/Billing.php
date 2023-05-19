<?php
namespace WeDevs\DokanPro\Modules\Germanized\CustomFields;

use WeDevs\DokanPro\Modules\Germanized\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Billing
 *
 * @package WeDevs\DokanPro\Modules\Germanized\CustomFields
 *
 * @since 3.3.1
 */
class Billing {
    /**
     * Billing constructor.
     */
    public function __construct() {
        // add custom fields under customer billing details
        add_filter( 'woocommerce_billing_fields', [ $this, 'custom_billing_checkout_fields' ], 10, 1 );

        add_filter( 'woocommerce_admin_billing_fields', [ $this, 'cf_in_order_details_billing' ] );

        add_action( 'woocommerce_admin_order_data_after_order_details', [ $this, 'cf_field_styles' ] );

        add_filter( 'woocommerce_customer_meta_fields', [ $this, 'cf_in_user_profile' ] );
    }

    /**
     * Get active custom fields with the labels
     *
     * @since 3.5.2
     *
     * @return array
     */
    private function get_active_customer_fields_and_labels() {
        $customer_fields = Helper::get_customer_fields();

        $customer_fields_labels = [];

        foreach ( $customer_fields as $field ) {
            switch ( $field ) {
                case 'billing_dokan_company_id_number':
                    $customer_fields_labels[ $field ] = Helper::get_customer_company_id_label();
                    break;
                case 'billing_dokan_vat_number':
                    $customer_fields_labels[ $field ] = Helper::get_customer_vat_number_label();
                    break;
                case 'billing_dokan_bank_name':
                    $customer_fields_labels[ $field ] = Helper::get_customer_bank_name_label();
                    break;
                case 'billing_dokan_bank_iban':
                    $customer_fields_labels[ $field ] = Helper::get_customer_bank_iban_label();
                    break;
            }
        }

        return $customer_fields_labels;
    }

    /**
     * Add Customer Custom fields to customer profile billing fields
     *
     * @since 3.5.2
     *
     * @param $fields
     *
     * @return array
     */
    public function cf_in_user_profile( $fields ) {
        $customer_fields = $this->get_active_customer_fields_and_labels();

        foreach ( $customer_fields as $field => $label ) {
            $fields['billing']['fields'][ $field ] = [ 'label' => $label, 'description' => '' ];
        }

        return $fields;
    }

    /**
     * Add styles for form fields in Order Billing info
     *
     * @since 3.5.2
     *
     * @param $order
     */
    public function cf_field_styles( $order ) {
        $customer_fields_labels = $this->get_active_customer_fields_and_labels();
        ?>
        <style>
            ._billing_dokan_company_id_number_field, ._billing_dokan_vat_number_field {
                width: 100% !important;
            }
            <?php
            if ( isset( $customer_fields_labels['billing_dokan_bank_name'] ) && isset( $customer_fields_labels['billing_dokan_bank_iban'] ) ) {
                ?>
                ._billing_dokan_bank_iban_field {
                    float: right !important;
                    clear: right !important;
                }
                <?php
            } elseif ( isset( $customer_fields_labels['billing_dokan_bank_name'] ) || isset( $customer_fields_labels['billing_dokan_bank_iban'] ) ) {
                ?>
                ._billing_dokan_bank_name_field, ._billing_dokan_bank_iban_field {
                    width: 100% !important;
                }
                <?php
            }
            ?>
        </style>
        <?php
    }

    /**
     * Add Customer Custom fields to billing data
     *
     * @since 3.5.2
     *
     * @param array $billing_fields
     *
     * @return array
     */
    public function cf_in_order_details_billing( $billing_fields ) {
        $customer_fields = $this->get_active_customer_fields_and_labels();

        foreach ( $customer_fields as $field => $label ) {
            $unprefixed_field = substr( $field, 8 );

            $billing_fields[ $unprefixed_field ] = [
                'type'  => 'text',
                'label' => $label,
                'show'  => false,
            ];
        }

        return $billing_fields;
    }

    /**
     * Add custom fields under customer billing details
     *
     * @param array $fields
     *
     * @since 3.3.1
     * @return array
     */
    public function custom_billing_checkout_fields( $fields ) {
        $new_fields = [];
        // get enabled fields for customer
        $endabled_fileds = Helper::is_fields_enabled_for_customer();

        if ( $endabled_fileds['billing_dokan_company_id_number'] ) {
            $new_fields['billing_dokan_company_id_number'] = [
                'type'      => 'text',
                'label'     => Helper::get_customer_company_id_label(),
                'required'  => false,
                'class'     => [ 'form-row-wide' ],
            ];
        }

        if ( $endabled_fileds['billing_dokan_vat_number'] ) {
            $new_fields['billing_dokan_vat_number'] = [
                'label'     => Helper::get_customer_vat_number_label(),
                'required'  => false,
                'class'     => [ 'form-row-wide' ],
            ];
        }

        if ( $endabled_fileds['billing_dokan_bank_name'] ) {
            $class = $endabled_fileds['billing_dokan_bank_name'] && $endabled_fileds['billing_dokan_bank_iban'] ? 'form-row-first' : 'form-row-wide';
            $new_fields['billing_dokan_bank_name'] = [
                'label'     => Helper::get_customer_bank_name_label(),
                'required'  => false,
                'class'     => [ $class ],
            ];
        }

        if ( $endabled_fileds['billing_dokan_bank_iban'] ) {
            $class = $endabled_fileds['billing_dokan_bank_name'] && $endabled_fileds['billing_dokan_bank_iban'] ? 'form-row-last' : 'form-row-wide';
            $new_fields['billing_dokan_bank_iban'] = [
                'label'     => Helper::get_customer_bank_iban_label(),
                'required'  => false,
                'class'     => [ $class ],
            ];
        }

        $fields = Helper::array_insert_after( $fields, 'billing_company', $new_fields );

        return $fields;
    }
}
