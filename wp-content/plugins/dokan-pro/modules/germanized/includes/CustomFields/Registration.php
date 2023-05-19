<?php

namespace WeDevs\DokanPro\Modules\Germanized\CustomFields;

use WeDevs\DokanPro\Modules\Germanized\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Registration
 *
 * @since   3.3.1
 *
 * @package WeDevs\DokanPro\Modules\Germanized\CustomFields
 */
class Registration {

    /**
     * Registration constructor.
     */
    public function __construct() {
        // check if admin enabled custom fields on registration form
        if ( ! Helper::is_enabled_on_registration_form() ) {
            return;
        }
        // add fields on registration form
        add_action( 'dokan_seller_registration_after_shopurl_field', [ $this, 'registration_form_custom_fields' ] );
        add_action( 'dokan_after_seller_migration_fields', [ $this, 'registration_form_custom_fields' ] );

        // after registration form is submitted
        add_filter( 'woocommerce_new_customer_data', [ $this, 'set_new_vendor_reg_data' ], 99, 1 );
        add_action( 'woocommerce_created_customer', [ $this, 'save_vendor_reg_custom_fields' ], 99, 2 );
    }

    /**
     * @since 3.3.1
     * @return void
     */
    public function registration_form_custom_fields() {
        $data           = $this->get_eu_complaiance_field_data();
        $fields_enabled = Helper::is_fields_enabled_for_seller();
        // check if fields are enabled
        ?>
        <?php if ( $fields_enabled['dokan_company_name'] ) : ?>
            <p class="form-row form-group">
                <label for="dokan-company-name"><?php echo Helper::get_company_name_label(); ?></label>
                <input type="text" class="input-text form-control" name="dokan_company_name" id="dokan-company-name" value="<?php //phpcs:ignore
                if ( ! empty( $data['dokan_company_name'] ) ) {
                    echo esc_attr( $data['dokan_company_name'] );
                }
                //phpcs:ignore ?>"/>
            </p>
        <?php endif; ?>

        <?php if ( $fields_enabled['dokan_company_id_number'] ) : ?>
            <p class="form-row form-group">
                <label for="dokan-company-id-number"><?php echo Helper::get_company_id_label(); ?></label>
                <input type="text" class="input-text form-control" name="dokan_company_id_number" id="dokan-company-id-number" value="<?php //phpcs:ignore
                if ( ! empty( $data['dokan_company_id_number'] ) ) {
                    echo esc_attr( $data['dokan_company_id_number'] );
                }
                //phpcs:ignore ?>"/>
            </p>
        <?php endif; ?>

        <?php if ( $fields_enabled['dokan_vat_number'] ) : ?>
            <p class="form-row form-group form-row-wide">
                <label for="dokan-vat-number"><?php echo Helper::get_vat_number_label(); ?></label>
                <input type="text" class="input-text form-control" name="dokan_vat_number" id="dokan-vat-number" value="<?php //phpcs:ignore
                if ( ! empty( $data['dokan_vat_number'] ) ) {
                    esc_attr( $data['dokan_vat_number'] );
                }
                //phpcs:ignore ?>"/>
            </p>
        <?php endif; ?>

        <?php if ( $fields_enabled['dokan_bank_name'] && $fields_enabled['dokan_bank_iban'] ) : ?>
            <div class="split-row name-field form-row-wide">
                <p class="form-row form-group">
                    <label for="dokan-bank-name"><?php echo Helper::get_bank_name_label(); ?></label>
                    <input type="text" class="input-text form-control" name="dokan_bank_name" id="dokan-bank-name" value="<?php //phpcs:ignore
                    if ( ! empty( $data['dokan_bank_name'] ) ) {
                        echo esc_attr( $data['dokan_bank_name'] );
                    }
                    //phpcs:ignore ?>"/>
                </p>

                <p class="form-row form-group">
                    <label for="dokan-bank-iban"><?php echo Helper::get_bank_iban_label(); ?></label>
                    <input type="text" class="input-text form-control" name="dokan_bank_iban" id="dokan-bank-iban" value="<?php //phpcs:ignore
                    if ( ! empty( $data['dokan_bank_iban'] ) ) {
                        echo esc_attr( $data['dokan_bank_iban'] );
                    }
                    //phpcs:ignore ?>"/>
                </p>
            </div>
        <?php elseif ( $fields_enabled['dokan_bank_name'] ) : ?>
            <p class="form-row form-group">
                <label for="dokan-bank-name"><?php echo Helper::get_bank_name_label(); ?></label>
                <input type="text" class="input-text form-control" name="dokan_bank_name" id="dokan-bank-name" value="<?php //phpcs:ignore
                if ( ! empty( $data['dokan_bank_name'] ) ) {
                    echo esc_attr( $data['dokan_bank_name'] );
                }
                //phpcs:ignore ?>"/>
            </p>
        <?php elseif ( $fields_enabled['dokan_bank_iban'] ) : ?>
            <p class="form-row form-group">
                <label for="dokan-bank-iban"><?php echo Helper::get_bank_iban_label(); ?></label>
                <input type="text" class="input-text form-control" name="dokan_bank_iban" id="dokan-bank-iban" value="<?php //phpcs:ignore
                if ( ! empty( $data['dokan_bank_iban'] ) ) {
                    echo esc_attr( $data['dokan_bank_iban'] );
                }
                //phpcs:ignore ?>"/>
            </p>
        <?php endif; ?>
        <?php
    }

    /**
     * Inject custom fields to WooCommerce for new vendor registraion
     *
     * @since 3.3.1
     *
     * @param array $data
     *
     * @return array
     */
    public function set_new_vendor_reg_data( $data ) {
        $user_data = $this->get_eu_complaiance_field_data();

        $data['role'] = $user_data['role'];

        if ( $user_data['role'] !== 'seller' ) {
            return $data;
        }

        if ( isset( $user_data['dokan_company_name'] ) ) {
            $data['dokan_company_name'] = $user_data['dokan_company_name'];
        }
        if ( isset( $user_data['dokan_company_id_number'] ) ) {
            $data['dokan_company_id_number'] = $user_data['dokan_company_id_number'];
        }
        if ( isset( $user_data['dokan_vat_number'] ) ) {
            $data['dokan_vat_number'] = $user_data['dokan_vat_number'];
        }
        if ( isset( $user_data['dokan_bank_name'] ) ) {
            $data['dokan_bank_name'] = $user_data['dokan_bank_name'];
        }
        if ( isset( $user_data['dokan_bank_iban'] ) ) {
            $data['dokan_bank_iban'] = $user_data['dokan_bank_iban'];
        }

        return $data;
    }

    /**
     * Adds default dokan store settings when a new vendor registers
     *
     * @param int   $user_id
     * @param array $data
     *
     * @return void
     */
    public function save_vendor_reg_custom_fields( $user_id, $data ) {
        if ( ! isset( $data['role'] ) || $data['role'] !== 'seller' ) {
            return;
        }

        // store company name
        if ( isset( $data['dokan_company_name'] ) ) {
            update_user_meta( $user_id, 'dokan_company_name', $data['dokan_company_name'] );
        }
        // store vat number
        if ( isset( $data['dokan_vat_number'] ) ) {
            update_user_meta( $user_id, 'dokan_vat_number', $data['dokan_vat_number'] );
        }
        // store company id number
        if ( isset( $data['dokan_company_id_number'] ) ) {
            update_user_meta( $user_id, 'dokan_company_id_number', $data['dokan_company_id_number'] );
        }
        // store bank name
        if ( isset( $data['dokan_bank_name'] ) ) {
            update_user_meta( $user_id, 'dokan_bank_name', $data['dokan_bank_name'] );
        }
        // store bank iban
        if ( isset( $data['dokan_bank_iban'] ) ) {
            update_user_meta( $user_id, 'dokan_bank_iban', $data['dokan_bank_iban'] );
        }
    }

    /**
     * Get user posted data
     *
     * @since 3.7.6
     *
     * @return string[]
     */
    private function get_eu_complaiance_field_data() {
        $data = [
            'dokan_company_name'      => '',
            'dokan_company_id_number' => '',
            'dokan_vat_number'        => '',
            'dokan_bank_name'         => '',
            'dokan_bank_iban'         => '',
            'role'                    => '',
        ];

        // check if user submitted data
        $nonce_value = isset( $_POST['_wpnonce'] ) ? sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ) : ''; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        $nonce_value = isset( $_POST['woocommerce-register-nonce'] ) ? sanitize_key( wp_unslash( $_POST['woocommerce-register-nonce'] ) ) : $nonce_value; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized

        if ( wp_verify_nonce( $nonce_value, 'woocommerce-register' ) ) {
            $allowed_roles = apply_filters( 'dokan_register_user_role', [ 'customer', 'seller' ] );
            $role          = ( isset( $_POST['role'] ) && in_array( $_POST['role'], $allowed_roles, true ) ) ? sanitize_text_field( wp_unslash( $_POST['role'] ) ) : 'customer';

            $data = [
                'dokan_company_name'      => isset( $_POST['dokan_company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan_company_name'] ) ) : '',
                'dokan_company_id_number' => isset( $_POST['dokan_company_id_number'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan_company_id_number'] ) ) : '',
                'dokan_vat_number'        => isset( $_POST['dokan_vat_number'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan_vat_number'] ) ) : '',
                'dokan_bank_name'         => isset( $_POST['dokan_bank_name'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan_bank_name'] ) ) : '',
                'dokan_bank_iban'         => isset( $_POST['dokan_bank_iban'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan_bank_iban'] ) ) : '',
                'role'                    => $role,
            ];
        }

        return $data;
    }
}
