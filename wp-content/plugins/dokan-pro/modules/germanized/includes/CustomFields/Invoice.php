<?php
namespace WeDevs\DokanPro\Modules\Germanized\CustomFields;

use WeDevs\DokanPro\Modules\Germanized\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Invoice
 *
 * @package WeDevs\DokanPro\Modules\Germanized\CustomFields
 *
 * @since 3.3.1
 */
class Invoice {
    /**
     * Invoice constructor.
     */
    public function __construct() {
        // dokan invoice: add custom fields on vendor address
        add_filter( 'dokan_invoice_single_seller_address', [ $this, 'invoice_single_seller_address' ], 10, 3 );

        // dokan invoice: add custom fields on customer address
        add_action( 'wpo_wcpdf_before_billing_address', [ $this, 'invoice_customer_before_billing_address' ], 10, 2 );
        add_action( 'wpo_wcpdf_after_billing_address', [ $this, 'invoice_customer_after_billing_address' ], 10, 2 );
    }

    /**
     * Dokan Invoice: add custom fields after customer billing address
     *
     * @param string $type
     * @param \WC_Order $order
     *
     * @since 3.3.1
     * @return void
     */
    public function invoice_customer_after_billing_address( $type, $order ) {
        if ( ! is_object( $order ) && is_numeric( $order ) ) {
            $order = wc_get_order( $order );
        }

        if ( ! $order ) {
            return;
        }

        $fields_enabled = Helper::is_fields_enabled_for_customer();
        // get order id
        $order_id = $order->get_parent_id() ? $order->get_parent_id() : $order->get_id();
        // get data from order meta first, if order meta is empty get data from user meta
        $bank_name = get_post_meta( $order_id, '_billing_dokan_bank_name', true );
        $bank_iban = get_post_meta( $order_id, '_billing_dokan_bank_iban', true );

        // check if bank name exists for this customer
        if ( ! empty( $bank_name ) && $fields_enabled['billing_dokan_bank_name'] ) {
            echo '<br>' . Helper::get_bank_name_label() . ': ' . esc_html( $bank_name );
        }

        // check if vat number exists for this customer
        if ( ! empty( $bank_iban ) && $fields_enabled['billing_dokan_bank_iban'] ) {
            echo '<br>' . Helper::get_bank_iban_label() . ': ' . esc_html( $bank_iban );
        }
    }

    /**
     * Dokan Invoice: add custom fields before customer billing address
     *
     * @param string $type
     * @param \WC_Order $order
     *
     * @since 3.3.1
     * @return void
     */
    public function invoice_customer_before_billing_address( $type, $order ) {
        if ( ! is_object( $order ) && is_numeric( $order ) ) {
            $order = wc_get_order( $order );
        }

        if ( ! $order ) {
            return;
        }

        // get order id
        $order_id       = $order->get_parent_id() ? $order->get_parent_id() : $order->get_id();
        $printed        = false;
        $fields_enabled = Helper::is_fields_enabled_for_customer();

        // get company id
        $dokan_company_id_number = get_post_meta( $order_id, '_billing_dokan_company_id_number', true );
        // get vat number
        $dokan_vat_number = get_post_meta( $order_id, '_billing_dokan_vat_number', true );

        // check if company id exists for this customer
        if ( ! empty( $dokan_company_id_number ) && $fields_enabled['billing_dokan_company_id_number'] ) {
            $printed = true;
            echo '<br>' . Helper::get_company_id_label() . ': ' . esc_html( $dokan_company_id_number );
        }

        // check if vat number exists for this customer
        if ( ! empty( $dokan_vat_number ) && $fields_enabled['billing_dokan_vat_number'] ) {
            $printed = true;
            echo '<br>' . Helper::get_vat_number_label() . ': ' . esc_html( $dokan_vat_number );
        }

        if ( $printed ) {
            echo '<br>';
        }
    }

    /**
     * Dokan invoice: add custom fields on vendor address
     *
     * @param string $shop_address
     * @param int $vendor_id
     * @param int $order_id
     *
     * @since 3.3.1
     * @return string
     */
    public function invoice_single_seller_address( $shop_address, $vendor_id, $order_id ) {
        $dokan_company_name      = get_user_meta( $vendor_id, 'dokan_company_name', true );
        $dokan_company_id_number = get_user_meta( $vendor_id, 'dokan_company_id_number', true );
        $vat_number              = get_user_meta( $vendor_id, 'dokan_vat_number', true );
        $bank_name               = get_user_meta( $vendor_id, 'dokan_bank_name', true );
        $bank_iban               = get_user_meta( $vendor_id, 'dokan_bank_iban', true );

        // get enabled settings fields
        $fields_enabled = Helper::is_fields_enabled_for_seller();

        $address_top    = '';
        $address_bottom = '';

        // check if company name exists for this customer
        if ( ! empty( $dokan_company_name ) && $fields_enabled['dokan_company_name'] ) {
            $address_top = Helper::get_company_name_label() . ': ' . esc_html( $dokan_company_name );
        }

        // check if company id exists for this customer
        if ( ! empty( $dokan_company_id_number ) && $fields_enabled['dokan_company_id_number'] ) {
            $address_top .= '<br>' . Helper::get_company_id_label() . ': ' . esc_html( $dokan_company_id_number );
        }

        // check of vat id exists for this vendor
        if ( ! empty( $vat_number ) && $fields_enabled['dokan_vat_number'] ) {
            $address_top .= '<br>' . Helper::get_vat_number_label() . ': ' . esc_html( $vat_number );
        }

        // check if bank name exists for this customer
        if ( ! empty( $bank_name ) && $fields_enabled['dokan_bank_name'] ) {
            $address_bottom .= '<br>' . Helper::get_bank_name_label() . ': ' . esc_html( $bank_name );
        }

        // check if vat number exists for this customer
        if ( ! empty( $bank_iban ) && $fields_enabled['dokan_bank_iban'] ) {
            $address_bottom .= '<br>' . Helper::get_bank_iban_label() . ': ' . esc_html( $bank_iban );
        }

        if ( ! empty( $address_top ) ) {
            $shop_address = $address_top . '<br>' . $shop_address;
        }

        if ( ! empty( $address_bottom ) ) {
            $shop_address .= $address_bottom . '<br>';
        }

        return $shop_address;
    }
}

