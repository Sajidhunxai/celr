<?php
namespace WeDevs\DokanPro\Modules\Germanized\CustomFields;

use WeDevs\DokanPro\Modules\Germanized\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class UserProfile
 * @package WeDevs\DokanPro\Modules\Germanized\CustomFields
 * @since 3.3.1
 */
class UserProfile {
    /**
     * UserProfile constructor.
     */
    public function __construct() {
        // add custom meta on user profile
        add_action( 'dokan_user_profile_after_phone_number', [ $this, 'user_profile_custom_fields' ], 10, 2 );

        // save meta fields
        add_action( 'personal_options_update', [ $this, 'save_user_profile_custom_fields' ], 10, 1 );
        add_action( 'edit_user_profile_update', [ $this, 'save_user_profile_custom_fields' ], 10, 1 );
    }

    /**
     * Add custom meta on user profile
     *
     * @param array $store_settings
     * @param \WP_User $user
     *
     * @since 3.3.1
     * @return void
     */
    public function user_profile_custom_fields( $store_settings, $user ) {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        if ( ! user_can( $user, 'dokandar' ) ) {
            return;
        }
        $fields_enabled = Helper::is_fields_enabled_for_seller();
        ?>
        <?php if ( $fields_enabled['dokan_company_name'] ) : ?>
            <tr>
                <th><?php echo Helper::get_company_name_label(); ?></th>
                <td>
                    <input type="text" name="dokan_company_name" class="regular-text" value="<?php echo esc_attr( $store_settings['company_name'] ); ?>">
                </td>
            </tr>
        <?php endif; ?>

        <?php if ( $fields_enabled['dokan_company_id_number'] ) : ?>
            <tr>
                <th><?php echo Helper::get_company_id_label(); ?></th>
                <td>
                    <input type="text" name="dokan_company_id_number" class="regular-text" value="<?php echo esc_attr( $store_settings['company_id_number'] ); ?>">
                </td>
            </tr>
        <?php endif; ?>

        <?php if ( $fields_enabled['dokan_vat_number'] ) : ?>
            <tr>
                <th><?php echo Helper::get_vat_number_label(); ?></th>
                <td>
                    <input type="text" name="dokan_vat_number" class="regular-text" value="<?php echo esc_attr( $store_settings['vat_number'] ); ?>">
                </td>
            </tr>
        <?php endif; ?>

        <?php if ( $fields_enabled['dokan_bank_name'] ) : ?>
            <tr>
                <th><?php echo Helper::get_bank_name_label(); ?></th>
                <td>
                    <input type="text" name="dokan_bank_name" class="regular-text" value="<?php echo esc_attr( $store_settings['bank_name'] ); ?>">
                </td>
            </tr>
        <?php endif; ?>

        <?php if ( $fields_enabled['dokan_bank_iban'] ) : ?>
            <tr>
                <th><?php echo Helper::get_bank_iban_label(); ?></th>
                <td>
                    <input type="text" name="dokan_bank_iban" class="regular-text" value="<?php echo esc_attr( $store_settings['bank_iban'] ); ?>">
                </td>
            </tr>
        <?php endif; ?>
        <?php
    }

    /**
     * Save user data
     *
     * @param int $user_id
     *
     * @return void
     */
    public function save_user_profile_custom_fields( $user_id ) {
        if ( ! dokan_is_user_seller( $user_id ) || ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        if ( ! isset( $_POST['dokan_update_user_profile_info_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dokan_update_user_profile_info_nonce'] ) ), 'dokan_update_user_profile_info' ) ) {
            return;
        }

        // store company name
        $company_name = isset( $_POST['dokan_company_name'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan_company_name'] ) ) : '';
        update_user_meta( $user_id, 'dokan_company_name', $company_name );
        update_user_meta( $user_id, 'billing_company', $company_name );

        // store vat number
        $vat_number = isset( $_POST['dokan_vat_number'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan_vat_number'] ) ) : '';
        update_user_meta( $user_id, 'dokan_vat_number', $vat_number );

        // store company id number
        $company_id_number = isset( $_POST['dokan_company_id_number'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan_company_id_number'] ) ) : '';
        update_user_meta( $user_id, 'dokan_company_id_number', $company_id_number );

        // store bank name
        $bank_name = isset( $_POST['dokan_bank_name'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan_bank_name'] ) ) : '';
        update_user_meta( $user_id, 'dokan_bank_name', $bank_name );

        // store bank iban
        $bank_iban = isset( $_POST['dokan_bank_iban'] ) ? sanitize_text_field( wp_unslash( $_POST['dokan_bank_iban'] ) ) : '';
        update_user_meta( $user_id, 'dokan_bank_iban', $bank_iban );
    }
}
