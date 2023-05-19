<?php

namespace WeDevs\DokanPro\Modules\Germanized\CustomFields;

use WeDevs\DokanPro\Modules\Germanized\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Dashboard
 * @package WeDevs\DokanPro\Modules\Germanized\CustomFields
 * @since 3.3.1
 */
class Dashboard {
    /**
     * Dashboard constructor.
     */
    public function __construct() {
        // add custom meta on vendor dashboard settings page
        add_action( 'dokan_settings_after_store_phone', [ $this, 'vendor_dashboard_custom_fields' ], 10, 2 );

        //save vendor dashboard custom fields data
        add_action( 'dokan_store_profile_saved', [ $this, 'save_vendor_dashboard_custom_fields' ], 10, 2 );
    }

    /**
     * Add custom meta on vendor dashboard settings page
     *
     * @param int $user_id
     * @param array $store_settings
     *
     * @since 3.3.1
     * @return void
     */
    public function vendor_dashboard_custom_fields( $user_id, $store_settings ) {
        $dokan_company_name      = get_user_meta( $user_id, 'dokan_company_name', true );
        $dokan_vat_number        = get_user_meta( $user_id, 'dokan_vat_number', true );
        $dokan_company_id_number = get_user_meta( $user_id, 'dokan_company_id_number', true );
        $bank_name               = get_user_meta( $user_id, 'dokan_bank_name', true );
        $bank_iban               = get_user_meta( $user_id, 'dokan_bank_iban', true );
        $enabled_fields          = Helper::is_fields_enabled_for_seller();

        $company_v_status = isset( $store_settings['dokan_verification']['info']['company_v_status'] ) ? $store_settings['dokan_verification']['info']['company_v_status'] : '';
        $verified = 'approved' === $company_v_status ? 'disabled' : '';
        ?>
        <?php if ( $enabled_fields['dokan_company_name'] ) : ?>
            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label" for="settings_dokan_company_name"><?php echo Helper::get_company_name_label(); ?></label>
                <div class="dokan-w5 dokan-text-left">
                    <input <?php echo ( $verified ); ?> id="settings_dokan_company_name" value="<?php echo esc_attr( $dokan_company_name ); ?>" name="settings_dokan_company_name" placeholder="<?php echo Helper::get_company_name_label(); ?>" class="dokan-form-control input-md" type="text">
                </div>
            </div>
        <?php endif; ?>

        <?php if ( $enabled_fields['dokan_company_id_number'] ) : ?>
            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label" for="settings_dokan_company_id_number"><?php echo Helper::get_company_id_label(); ?></label>
                <div class="dokan-w5 dokan-text-left">
                    <input <?php echo ( $verified ); ?> id="settings_dokan_company_id_number" value="<?php echo esc_attr( $dokan_company_id_number ); ?>" name="settings_dokan_company_id_number" placeholder="<?php echo Helper::get_company_id_label(); ?>" class="dokan-form-control input-md" type="text">
                </div>
            </div>
        <?php endif; ?>

        <?php if ( $enabled_fields['dokan_vat_number'] ) : ?>
            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label" for="setting_vat_number"><?php echo Helper::get_vat_number_label(); ?></label>
                <div class="dokan-w5 dokan-text-left">
                    <input <?php echo ( $verified ); ?> id="setting_vat_number" value="<?php echo esc_attr( $dokan_vat_number ); ?>" name="settings_dokan_vat_number" placeholder="<?php echo Helper::get_vat_number_label(); ?>" class="dokan-form-control input-md" type="text">
                </div>
            </div>
        <?php endif; ?>

        <?php if ( $enabled_fields['dokan_bank_name'] ) : ?>
            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label" for="setting_bank_name"><?php echo Helper::get_bank_name_label(); ?></label>
                <div class="dokan-w5 dokan-text-left">
                    <input <?php echo ( $verified ); ?> name="setting_bank_name" id="setting_bank_name" value="<?php echo esc_attr( $bank_name ); ?>" class="dokan-form-control" placeholder="<?php echo Helper::get_bank_name_label(); ?>" type="text">
                </div>
            </div>
        <?php endif; ?>

        <?php if ( $enabled_fields['dokan_bank_iban'] ) : ?>
            <div class="dokan-form-group">
                <label class="dokan-w3 dokan-control-label" for="setting_bank_iban"><?php echo Helper::get_bank_iban_label(); ?></label>
                <div class="dokan-w5 dokan-text-left">
                    <input <?php echo ( $verified ); ?> name="setting_bank_iban" id="setting_bank_iban" value="<?php echo esc_attr( $bank_iban ); ?>" class="dokan-form-control" placeholder="<?php echo Helper::get_bank_iban_label(); ?>" type="text">
                </div>
            </div>
        <?php endif; ?>
        <?php
    }

    /**
     * Save vendor dashboard custom fields data
     *
     * @param int $store_id
     * @param array $dokan_settings
     *
     * @since 3.3.1
     * @return void
     */
    public function save_vendor_dashboard_custom_fields( $store_id, $dokan_settings ) {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'dokan_store_settings_nonce' ) ) {
            return;
        }

        if ( ! dokan_is_user_seller( $store_id ) ) {
            return;
        }

        $enabled_fields = Helper::is_fields_enabled_for_seller();

        $company_v_status = isset( $dokan_settings['dokan_verification']['info']['company_v_status'] ) ? $dokan_settings['dokan_verification']['info']['company_v_status'] : '';
        if ( 'approved' === $company_v_status ) {
            return;
        }

        // store company name
        if ( $enabled_fields['dokan_company_name'] ) {
            $company_name = isset( $_POST['settings_dokan_company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['settings_dokan_company_name'] ) ) : '';
            update_user_meta( $store_id, 'dokan_company_name', $company_name );
            update_user_meta( $store_id, 'billing_company', $company_name );
        }

        // store vat number
        if ( $enabled_fields['dokan_vat_number'] ) {
            $vat_number = isset( $_POST['settings_dokan_vat_number'] ) ? sanitize_text_field( wp_unslash( $_POST['settings_dokan_vat_number'] ) ) : '';
            update_user_meta( $store_id, 'dokan_vat_number', $vat_number );
        }

        // store company id number
        if ( $enabled_fields['dokan_company_id_number'] ) {
            $company_id_number = isset( $_POST['settings_dokan_company_id_number'] ) ? sanitize_text_field( wp_unslash( $_POST['settings_dokan_company_id_number'] ) ) : '';
            update_user_meta( $store_id, 'dokan_company_id_number', $company_id_number );
        }

        // store bank name
        if ( $enabled_fields['dokan_bank_name'] ) {
            $bank_name = isset( $_POST['setting_bank_name'] ) ? sanitize_text_field( wp_unslash( $_POST['setting_bank_name'] ) ) : '';
            update_user_meta( $store_id, 'dokan_bank_name', $bank_name );
        }

        // store bank iban
        if ( $enabled_fields['dokan_bank_iban'] ) {
            $bank_iban = isset( $_POST['setting_bank_iban'] ) ? sanitize_text_field( wp_unslash( $_POST['setting_bank_iban'] ) ) : '';
            update_user_meta( $store_id, 'dokan_bank_iban', $bank_iban );
        }
    }
}
