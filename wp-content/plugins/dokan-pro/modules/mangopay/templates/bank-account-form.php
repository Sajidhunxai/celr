<div class="dokan-mangopay-container hidden" id="dokan-mangopay-bank-account" data-user="<?php echo esc_attr( $user_id ); ?>">
    <div class="dokan-mp-bank-accounts">
        <?php do_action( 'dokan_mangopay_bank_account_list', $user_id ); ?>
    </div>

    <button type="submit"
        class="button dokan-mp-bank-account dokan-mp-btn-submit"
        id="dokan-mp-bank-account-add-new">
        <?php esc_attr_e( 'Add New', 'dokan' ); ?>
    </button>

    <div class="bank-account-fields hidden" id="dokan-mp-bank-account-fields">
        <div id="dokan-mangopay-bank-account-notice" class="dokan-alert"></div>

        <div>
        <?php
            woocommerce_form_field(
                'settings[mangopay][bank_account][type]',
                array(
                    'id'       => "dokan-mangopay-vendor-acccount-type",
                    'type'     => 'select',
                    'label'    => __( 'Account Type', 'dokan' ),
                    'required' => true,
                    'class'    => array( 'wc-enhanced-select' ),
                    'options'  => $account_types,
                ),
                isset( $account_settings['type'] ) ? $account_settings['type'] : ''
            );
        ?>
        </div>

        <?php foreach( $account_fields as $type => $fields ) : ?>
        <div class="dokan_mangopay_vendor_account_fields <?php echo esc_attr( $type ) . '_fields';?> bank-account-common-fields">
            <?php foreach( $fields as $field_name => $field_data ) :
                woocommerce_form_field(
                    "settings[mangopay][bank_account][$type][$field_name]",
                    array(
                        'id'                => "dokan-mangopay-vendor-acccount-$type-$field_name",
                        'type'              => isset( $field_data['format'] )      ? $field_data['format']      : 'text',
                        'label'             => isset( $field_data['label'] )       ? $field_data['label']       : '',
                        'required'          => isset( $field_data['required'] )    ? $field_data['required']    : false,
                        'placeholder'       => isset( $field_data['placeholder'] ) ? $field_data['placeholder'] : '',
                        'class'             => isset( $field_data['class'] )       ? $field_data['class']       : array(),
                        'options'           => isset( $field_data['options'] )     ? $field_data['options']     : array(),
                        'custom_attributes' => isset( $field_data['custom'] )      ? $field_data['custom']      : array(),
                    ),
                    isset( $account_settings[ $type ] ) && isset( $account_settings[ $type ][ $field_name ] ) ? $account_settings[ $type ][ $field_name ] : ''
                );
            endforeach; ?>
        </div>
        <?php endforeach; ?>

        <div>
            <?php foreach ( $common_fields as $key => $fields ) :
                woocommerce_form_field(
                    "settings[mangopay][bank_account][$key]",
                    $fields,
                    isset( $account_settings[ $key ] ) ? $account_settings[ $key ] : ''
                );
            endforeach; ?>
        </div>

        <input type="submit"
            value="<?php esc_attr_e( 'Submit', 'dokan' ); ?>"
            class="dokan-mp-bank-account dokan-mp-btn-submit"
            id="dokan-mp-bank-account-create">

        <input type="submit"
            value="<?php esc_attr_e( 'Submitting', 'dokan' ); ?>..."
            class="dokan-mp-bank-account"
            id="dokan-mp-bank-account-creating dokan-mp-btn-submit"
            disabled="disabled"
            style="display: none;">

        <input type="submit"
            value="<?php esc_attr_e( 'Cancel', 'dokan' ); ?>"
            class="dokan-mp-bank-account"
            id="dokan-mp-bank-account-cancel">
    </div>
</div>
