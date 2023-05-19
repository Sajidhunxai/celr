<?php
namespace WeDevs\DokanPro\Modules\Germanized;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

use WC_Germanized_Meta_Box_Product_Data;

/**
 * Class Helper
 * @package WeDevs\DokanPro\Modules\Germanized
 * @since 3.3.1
 */
class Helper {

    /**
     * This method will check if Germanized For WooCommerce is installed and active
     *
     * @since 3.3.1
     *
     * @return bool
     */
    public static function is_germanized_installed() {
        return class_exists( 'WooCommerce_Germanized' );
    }

    /**
     * This method will check if Gernamized is installed and enabled for vendors
     *
     * @since 3.3.1
     *
     * @return bool
     */
    public static function is_germanized_enabled_for_vendors() {
        $enabled = dokan_get_option( 'enabled_germanized', 'dokan_germanized', 'off' );
        return ( static::is_germanized_installed() && 'on' === $enabled );
    }

    /**
     * This method will check if Gernamized is installed and enabled for vendors
     *
     * @since 3.3.1
     *
     * @return bool
     */
    public static function is_enabled_on_registration_form() {
        $enabled = dokan_get_option( 'vendor_registration', 'dokan_germanized', 'on' );
        return ( 'on' === $enabled );
    }

    /**
     * This method will check if WooCommerce PDF Invoices & Packing Slips Plugin is installed and active
     *
     * @since 3.3.1
     *
     * @return bool
     */
    public static function is_wpo_wcpdf_installed() {
        return class_exists( 'WPO_WCPDF' );
    }

    /**
     * This method will check if WooCommerce PDF Invoices & Packing Slips Plugin is installed and enabled for vendors
     *
     * @since 3.3.1
     *
     * @return bool
     */
    public static function is_wpo_wcpdf_enabled_for_vendors() {
        $enabled = dokan_get_option( 'override_invoice_number', 'dokan_germanized', 'off' );
        return ( static::is_wpo_wcpdf_installed() && 'on' === $enabled );
    }

    /**
     * Get enabled customer extra fields
     *
     * @since 3.3.1
     *
     * @return array
     */
    public static function get_customer_fields() {
        $default = [
            'billing_dokan_company_id_number'   => 'billing_dokan_company_id_number',
            'billing_dokan_vat_number'          => 'billing_dokan_vat_number',
            'billing_dokan_bank_name'           => 'billing_dokan_bank_name',
            'billing_dokan_bank_iban'           => 'billing_dokan_bank_iban',
        ];

        $fields = dokan_get_option( 'customer_fields', 'dokan_germanized', $default );
        return array_values( array_filter( $fields ) );
    }

    /**
     * This method will return true if custom fields is enabled for customer
     *
     * @since 3.3.1
     *
     * @return array
     */
    public static function is_fields_enabled_for_customer() {
        $fields_enabled = static::get_customer_fields();
        return [
            'billing_dokan_company_id_number'   => in_array( 'billing_dokan_company_id_number', $fields_enabled, true ),
            'billing_dokan_vat_number'          => in_array( 'billing_dokan_vat_number', $fields_enabled, true ),
            'billing_dokan_bank_name'           => in_array( 'billing_dokan_bank_name', $fields_enabled, true ),
            'billing_dokan_bank_iban'           => in_array( 'billing_dokan_bank_iban', $fields_enabled, true ),
        ];
    }

    /**
     * This method will return company name label
     *
     * @since 3.3.1
     *
     * @return string
     */
    public static function get_customer_company_id_label() {
        return apply_filters( 'dokan_customer_cf_company_id_number_label', esc_attr__( 'Company ID/EUID Number', 'dokan' ) );
    }

    /**
     * This method will return company name label
     *
     * @since 3.3.1
     *
     * @return string
     */
    public static function get_customer_vat_number_label() {
        return apply_filters( 'dokan_customer_cf_vat_number_label', esc_attr__( 'VAT/TAX Number', 'dokan' ) );
    }

    /**
     * This method will return company name label
     *
     * @since 3.3.1
     *
     * @return string
     */
    public static function get_customer_bank_name_label() {
        return apply_filters( 'dokan_customer_cf_bank_name_label', esc_attr__( 'Name of Bank', 'dokan' ) );
    }

    /**
     * This method will return company name label
     *
     * @since 3.3.1
     *
     * @return string
     */
    public static function get_customer_bank_iban_label() {
        return apply_filters( 'dokan_customer_cf_bank_iban_label', esc_attr__( 'Bank IBAN', 'dokan' ) );
    }

    /**
     * Get enabled vendor extra fields
     *
     * @since 3.3.1
     *
     * @return array
     */
    public static function get_seller_fields() {
        $default = [
            'dokan_company_name'        => 'dokan_company_name',
            'dokan_company_id_number'   => 'dokan_company_id_number',
            'dokan_vat_number'          => 'dokan_vat_number',
            'dokan_bank_name'           => 'dokan_bank_name',
            'dokan_bank_iban'           => 'dokan_bank_iban',
        ];

        $fields = dokan_get_option( 'vendor_fields', 'dokan_germanized', $default );
        return array_values( array_filter( $fields ) );
    }

    /**
     * This method will return true if custom fields is enabled for vendors
     *
     * @since 3.3.1
     *
     * @return array
     */
    public static function is_fields_enabled_for_seller() {
        $fields_enabled = static::get_seller_fields();
        return [
            'dokan_company_name'        => in_array( 'dokan_company_name', $fields_enabled, true ),
            'dokan_company_id_number'   => in_array( 'dokan_company_id_number', $fields_enabled, true ),
            'dokan_vat_number'          => in_array( 'dokan_vat_number', $fields_enabled, true ),
            'dokan_bank_name'           => in_array( 'dokan_bank_name', $fields_enabled, true ),
            'dokan_bank_iban'           => in_array( 'dokan_bank_iban', $fields_enabled, true ),
        ];
    }

    /**
     * This method will return company name label
     *
     * @since 3.3.1
     *
     * @return string
     */
    public static function get_company_name_label() {
        return apply_filters( 'dokan_cf_company_name_label', esc_attr__( 'Company Name', 'dokan' ) );
    }

    /**
     * This method will return company name label
     *
     * @since 3.3.1
     *
     * @return string
     */
    public static function get_company_id_label() {
        return apply_filters( 'dokan_cf_company_id_number_label', esc_attr__( 'Company ID/EUID Number', 'dokan' ) );
    }

    /**
     * This method will return company name label
     *
     * @since 3.3.1
     *
     * @return string
     */
    public static function get_vat_number_label() {
        return apply_filters( 'dokan_cf_vat_number_label', esc_attr__( 'VAT/TAX Number', 'dokan' ) );
    }

    /**
     * This method will return company name label
     *
     * @since 3.3.1
     *
     * @return string
     */
    public static function get_bank_name_label() {
        return apply_filters( 'dokan_cf_bank_name_label', esc_attr__( 'Name of Bank', 'dokan' ) );
    }

    /**
     * This method will return company name label
     *
     * @since 3.3.1
     *
     * @return string
     */
    public static function get_bank_iban_label() {
        return apply_filters( 'dokan_cf_bank_iban_label', esc_attr__( 'Bank IBAN', 'dokan' ) );
    }

    /**
     * Returns mixed units array
     *
     * @since 3.3.1
     * @param string $taxonomy
     * @param string $key
     *
     * @since 3.3.1
     *
     * @return mixed units as array
     */
    public static function get_terms( $taxonomy, $key = 'name' ) {
        $list  = array();
        $terms = get_terms( $taxonomy, array( 'hide_empty' => false ) );

        if ( ! empty( $terms ) && ! is_wp_error( $terms ) ) {
            if ( $key === 'id' ) {
                foreach ( $terms as $term ) {
                    $list[ $term->term_id ] = $term->name;
                }
            } else {
                foreach ( $terms as $term ) {
                    $list[ $term->slug ] = $term->name;
                }
            }
        }

        return $list;
    }

    /**
     * Display help tips
     *
     * @param string $tip
     * @param bool $echo
     * @param bool $allow_html
     *
     * @since 3.3.1
     *
     * @return string|void
     */
    public static function display_help_tips( $tip, $echo = false, $allow_html = false ) {
        if ( $allow_html ) {
            $tip = wc_sanitize_tooltip( $tip );
        } else {
            $tip = esc_attr( $tip );
        }

        $message = '<i class="fas fa-question-circle tips" aria-hidden="true" data-title="' . $tip . '"></i>';
        if ( $echo ) {
            echo $message;
        } else {
            return $message;
        }
    }

    /**
     * Convert a boolean value to empty/string
     *
     * @param string $value
     *
     * @since 3.3.1
     *
     * @return bool
     */
    public static function bool_to_string( $value, $obj ) {
        $id_data = $obj->id_data();

        // assuming it's a multi dimentional array, like: dokan_appearance[hide_vendor_info][email]
        // here: keys[0] = hide_vendor_info, keys[1]: email
        $name = isset( $id_data['keys'][1] ) ? $id_data['keys'][1] : $id_data['keys'][0];

        return is_bool( $value ) && $value ? $name : '';
    }

    /**
     * Convert an empty value to boolean
     *
     * @param string $value
     *
     * @since 3.3.1
     *
     * @return bool
     */
    public static function empty_to_bool( $value ) {
        return empty( $value ) ? false : true;
    }

    /**
     * Insert a value or key/value pair after a specific key in an array.  If key doesn't exist, value is appended
     * to the end of the array.
     *
     * @param array $array
     * @param string $key
     * @param array $new
     *
     * @since 3.3.1
     *
     * @return array
     */
    public static function array_insert_after( array $array, $key, array $new ) {
        $keys   = array_keys( $array );
        $index  = array_search( $key, $keys, true );
        $pos    = false === $index ? count( $array ) : $index + 1;

        return array_slice( $array, 0, $pos, true ) + $new + array_slice( $array, $pos, count( $array ) - 1, true );
    }

    /**
     * This method will return last order date for a vendor
     *
     * @param int $vendor_id
     * @param int $current_order_id
     *
     * @since 3.3.1
     *
     * @return string|null Database query result (as string), or null on failure.
     */
    public static function get_vendor_last_order_date( $vendor_id, $current_order_id ) {
        global $wpdb;

        return $wpdb->get_var(
            $wpdb->prepare(
                "SELECT p.post_date FROM {$wpdb->postmeta} as m left join {$wpdb->posts} as p on m.post_id = p.ID
                where m.meta_key = '_dokan_vendor_id' AND m.meta_value = %d AND m.post_id != %d order by m.post_id DESC limit 1",
                [ $vendor_id, $current_order_id ]
            )
        );
    }

    /**
     * This method will save simple product Eu compliance fields data.
     *
     * @since 3.7.13
     *
     * @param int   $post_id
     * @param array $data
     *
     * @return void
     */
    public static function save_simple_product_eu_data( $post_id, $data ) {
        $product = WC_Germanized_Meta_Box_Product_Data::save( $post_id );

        if ( isset( $data['_ts_gtin'] ) ) {
            $product = wc_ts_set_crud_data( $product, '_ts_gtin', wc_clean( wp_unslash( $data['_ts_gtin'] ) ) );
        }

        if ( isset( $data['_ts_mpn'] ) ) {
            $product = wc_ts_set_crud_data( $product, '_ts_mpn', wc_clean( wp_unslash( $data['_ts_mpn'] ) ) );
        }

        if ( is_object( $product ) ) {
            $product->save();
        }
    }

    /**
     * Saves eu-fields for variable products variations.
     *
     * Call this after sanitizing and formatting the values.
     *
     * @since 3.7.13
     *
     * @param integer $variation_id Product variation id.
     * @param array   $data {
     *     Eu-fields data for variation products.
     *
     *     @type integer $_unit_product             Product Units.
     *     @type string  $_unit_price_auto          Calculation.
     *     @type integer $_unit_price_regular       Regular Unit Price.
     *     @type string  $_sale_price_label         Sale Label.
     *     @type string  $_sale_price_regular_label Sale Regular Label.
     *     @type integer $_unit_price_sale          Sale Unit Price.
     *     @type integer $_parent_unit_product
     *     @type string  $_parent_unit
     *     @type integer $_parent_unit_base
     *     @type string  $_mini_desc                Optional Mini Description.
     *     @type string  $_service
     *     @type string  $delivery_time             Delivery Time.
     *     @type integer $_min_age                  Minimum Age .
     *     @type string  $_sale_price_dates_from
     *     @type string  $_sale_price_dates_to
     *     @type string  $_sale_price
     * }
     * @param array   $store_trusted_data {
     *     Trusted store data.
     *
     *     @type string $_ts_gtin GITN
     *     @type string $_ts_mpn  MPN
     * }
     *
     * @return void
     */
    public static function save_variable_products_variations_eu_data( $variation_id, $data, $store_trusted_data ) {
        // get the product
        $product = wc_get_product( $variation_id );
        if ( ! $product ) {
            return;
        }

        $product_parent       = wc_get_product( $product->get_parent_id() );
        $data['product-type'] = $product_parent->get_type();


        WC_Germanized_Meta_Box_Product_Data::save_product_data( $product, $data, true );

        foreach ( $store_trusted_data as $key => $value ) {
            $germanized_product = wc_ts_set_crud_data( $product, $key, $value );
        }

        if ( is_object( $germanized_product ) ) {
            $germanized_product->save();
        }
    }
}
