<?php
namespace WeDevs\DokanPro\Modules\Germanized\CustomFields;

use WeDevs\DokanPro\Modules\Germanized\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class SingleStore
 * @package WeDevs\DokanPro\Modules\Germanized\CustomFields
 * @since 3.3.1
 */
class SingleStore {
    /**
     * SingleStore constructor.
     */
    public function __construct() {
        // display custom fields on single store header section
        add_action( 'dokan_store_header_info_fields', [ $this, 'store_header_info_custom_fields' ], 10, 1 );

        // add customizer settings for single store header info fields
        add_action( 'dokan_store_customizer_after_vendor_info', [ $this, 'store_header_info_customizer_settings' ], 10, 1 );
    }

    /**
     * Add customizer settings for single store header info fields
     *
     * @param \WP_Customize_Manager $wp_customize
     *
     * @since 3.3.1
     * @return void
     */
    public function store_header_info_customizer_settings( $wp_customize ) {
        // get enabled fields
        $fields_enabled = Helper::is_fields_enabled_for_seller();

        // customizer settings to hide company name field
        if ( $fields_enabled['dokan_company_name'] ) {
            $wp_customize->add_setting(
                'dokan_appearance[hide_vendor_info][dokan_company_name]',
                [
                    'default'              => '',
                    'type'                 => 'option',
                    'capability'           => 'manage_options',
                    'sanitize_callback'    => [ Helper::class, 'bool_to_string' ],
                    'sanitize_js_callback' => [ Helper::class, 'empty_to_bool' ],
                ]
            );

            $wp_customize->add_control(
                'hide_dokan_company_name',
                [
                    'label'    => __( 'Hide ', 'dokan' ) . Helper::get_company_name_label(),
                    'section'  => 'dokan_store',
                    'settings' => 'dokan_appearance[hide_vendor_info][dokan_company_name]',
                    'type'     => 'checkbox',
                ]
            );
        }

        // customizer settings to hide company id field
        if ( $fields_enabled['dokan_company_id_number'] ) {
            $wp_customize->add_setting(
                'dokan_appearance[hide_vendor_info][dokan_company_id_number]',
                [
                    'default'              => '',
                    'type'                 => 'option',
                    'capability'           => 'manage_options',
                    'sanitize_callback'    => [ Helper::class, 'bool_to_string' ],
                    'sanitize_js_callback' => [ Helper::class, 'empty_to_bool' ],
                ]
            );

            $wp_customize->add_control(
                'hide_dokan_company_id_number',
                [
                    'label'    => __( 'Hide ', 'dokan' ) . Helper::get_company_id_label(),
                    'section'  => 'dokan_store',
                    'settings' => 'dokan_appearance[hide_vendor_info][dokan_company_id_number]',
                    'type'     => 'checkbox',
                ]
            );
        }

        // customizer settings to hide vat number field
        if ( $fields_enabled['dokan_vat_number'] ) {
            $wp_customize->add_setting(
                'dokan_appearance[hide_vendor_info][dokan_vat_number]',
                [
                    'default'              => '',
                    'type'                 => 'option',
                    'capability'           => 'manage_options',
                    'sanitize_callback'    => [ Helper::class, 'bool_to_string' ],
                    'sanitize_js_callback' => [ Helper::class, 'empty_to_bool' ],
                ]
            );

            $wp_customize->add_control(
                'hide_dokan_vat_number',
                [
                    'label'    => __( 'Hide ', 'dokan' ) . Helper::get_vat_number_label(),
                    'section'  => 'dokan_store',
                    'settings' => 'dokan_appearance[hide_vendor_info][dokan_vat_number]',
                    'type'     => 'checkbox',
                ]
            );
        }

        // customizer settings to hide bank name field
        if ( $fields_enabled['dokan_bank_name'] ) {
            $wp_customize->add_setting(
                'dokan_appearance[hide_vendor_info][dokan_bank_name]',
                [
                    'default'              => '',
                    'type'                 => 'option',
                    'capability'           => 'manage_options',
                    'sanitize_callback'    => [ Helper::class, 'bool_to_string' ],
                    'sanitize_js_callback' => [ Helper::class, 'empty_to_bool' ],
                ]
            );

            $wp_customize->add_control(
                'hide_dokan_bank_name',
                [
                    'label'    => __( 'Hide ', 'dokan' ) . Helper::get_bank_name_label(),
                    'section'  => 'dokan_store',
                    'settings' => 'dokan_appearance[hide_vendor_info][dokan_bank_name]',
                    'type'     => 'checkbox',
                ]
            );
        }

        // customizer settings to hide bank iban field
        if ( $fields_enabled['dokan_bank_iban'] ) {
            $wp_customize->add_setting(
                'dokan_appearance[hide_vendor_info][dokan_bank_iban]',
                [
                    'default'              => '',
                    'type'                 => 'option',
                    'capability'           => 'manage_options',
                    'sanitize_callback'    => [ Helper::class, 'bool_to_string' ],
                    'sanitize_js_callback' => [ Helper::class, 'empty_to_bool' ],
                ]
            );

            $wp_customize->add_control(
                'hide_dokan_bank_iban',
                [
                    'label'    => __( 'Hide ', 'dokan' ) . Helper::get_bank_iban_label(),
                    'section'  => 'dokan_store',
                    'settings' => 'dokan_appearance[hide_vendor_info][dokan_bank_iban]',
                    'type'     => 'checkbox',
                ]
            );
        }
    }

    /**
     * Display custom fields on single store header section
     *
     * @param $store_id
     *
     * @since 3.3.1
     * @return void
     */
    public function store_header_info_custom_fields( $store_id ) {
        $dokan_company_name      = get_user_meta( $store_id, 'dokan_company_name', true );
        $dokan_vat_number        = get_user_meta( $store_id, 'dokan_vat_number', true );
        $dokan_company_id_number = get_user_meta( $store_id, 'dokan_company_id_number', true );
        $bank_name               = get_user_meta( $store_id, 'dokan_bank_name', true );
        $bank_iban               = get_user_meta( $store_id, 'dokan_bank_iban', true );
        $fields_enabled          = Helper::is_fields_enabled_for_seller();
        ?>
        <?php if ( $fields_enabled['dokan_company_name'] && ! empty( $dokan_company_name ) && ! dokan_is_vendor_info_hidden( 'dokan_company_name' ) ) : ?>
            <li class="dokan-company-name">
                <i class="fas fa-info"></i>
                <?php echo esc_html( $dokan_company_name ); ?>
            </li>
        <?php endif; ?>

        <?php if ( $fields_enabled['dokan_company_id_number'] && ! empty( $dokan_company_id_number ) && ! dokan_is_vendor_info_hidden( 'dokan_company_id_number' ) ) : ?>
            <li class="dokan-company-id-number">
                <i class="fas fa-info"></i>
                <?php echo esc_html( $dokan_company_id_number ); ?>
            </li>
        <?php endif; ?>

        <?php if ( $fields_enabled['dokan_vat_number'] && ! empty( $dokan_vat_number ) && ! dokan_is_vendor_info_hidden( 'dokan_vat_number' ) ) : ?>
            <li class="dokan-vat-number">
                <i class="fas fa-info"></i>
                <?php echo esc_html( $dokan_vat_number ); ?>
            </li>
        <?php endif; ?>

        <?php if ( $fields_enabled['dokan_bank_name'] && ! empty( $bank_name ) && ! dokan_is_vendor_info_hidden( 'dokan_bank_name' ) ) : ?>
            <li class="dokan-bank-name">
                <i class="fas fa-university"></i>
                <?php echo esc_html( $bank_name ); ?>
            </li>
        <?php endif; ?>

        <?php if ( $fields_enabled['dokan_bank_iban'] && ! empty( $bank_iban ) && ! dokan_is_vendor_info_hidden( 'dokan_bank_iban' ) ) : ?>
            <li class="dokan-bank-iban">
                <i class="fas fa-university"></i>
                <?php echo esc_html( $bank_iban ); ?>
            </li>
        <?php endif; ?>

        <?php
    }
}
