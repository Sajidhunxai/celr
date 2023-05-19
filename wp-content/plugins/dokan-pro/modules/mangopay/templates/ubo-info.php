<?php // phpcs:disable WordPress.NamingConventions.ValidVariableName.UsedPropertyNotSnakeCase ?>

<?php if ( count( $ubo_declarations ) > 0 ) : ?>
<table class="ubo_table_list_declarations has-background">
    <thead class="ubo_thead_list_declarations">
        <tr>
            <td></td>
            <td><?php esc_html_e( 'Created At', 'dokan' ); ?></td>
            <td><?php esc_html_e( 'Status', 'dokan' ); ?></td>
        </tr>
    </thead>
    <tbody class="ubo-element-list-body">
    <?php foreach ( $ubo_declarations as $ubo_declaration ) : ?>
        <tr>
            <td>
                <?php if ( count( $ubo_declaration->Ubos ) > 0 ) : ?>
                <button type="button"
                    id="show_ubo_elements_button_<?php echo esc_attr( $ubo_declaration->Id ); ?>"
                    data-id="<?php echo esc_attr( $ubo_declaration->Id ); ?>"
                    data-value="show">
                    <span class="dashicons dashicons-insert"></span>
                </button>

                <input type="hidden"
                    id="ubo_declaration_ubo_count_<?php echo esc_attr( $ubo_declaration->Id ); ?>"
                    value="<?php echo esc_attr( count( $ubo_declaration->Ubos ) ); ?>">
                <?php endif; ?>
            </td>
            <td>
                <?php echo ! empty( $ubo_declaration->CreationDate ) ? esc_html( $ubo_declaration->CreationDate ) : '-'; ?>
            </td>
            <td>
            <?php
            echo wp_kses(
                $ubo_declaration->OutputStatus, array(
                    'br'   => array(),
                    'span' => array(),
                )
            );
            ?>
            </td>
        </tr>
        <?php if ( count( $ubo_declaration->Ubos ) > 0 ) : ?>
        <tr class="ubo_tr_list_elements hidden" id="tr_ubo_<?php echo esc_attr( $ubo_declaration->Id ); ?>">
            <td></td>
            <td colspan="4">
                <table class="ubo_table_list_elements has-background">
                    <thead class="ubo_thead_list_elements">
                        <tr>
                            <td><?php esc_html_e( 'First Name', 'dokan' ); ?></td>
                            <td><?php esc_html_e( 'Last Name', 'dokan' ); ?></td>
                            <td><?php esc_html_e( 'Status', 'dokan' ); ?></td>
                            <td></td>
                        </tr>
                    </thead>
                    <tbody class="ubo-element-list-body">
                        <?php foreach ( $ubo_declaration->Ubos as $ubo_element ) : ?>
                        <tr>
                            <td><?php echo esc_html( $ubo_element->FirstName ); ?></td>
                            <td><?php echo esc_html( $ubo_element->LastName ); ?></td>
                            <td><?php echo esc_html( ! empty( $ubo_element->isActive ) ? __( 'Active', 'dokan' ) : __( 'Inactive', 'dokan' ) ); ?></td>
                            <td>
                                <?php if ( 'CREATED' === $ubo_declaration->Status || 'INCOMPLETE' === $ubo_declaration->Status ) : ?>
                                    <button type="button"
                                        id="uboelementbutton_<?php echo esc_attr( $ubo_element->Id ); ?>"
                                        data-uboelementid="<?php echo esc_attr( $ubo_element->Id ); ?>"
                                        data-uboelement="<?php echo esc_attr( wp_json_encode( $ubo_element ) ); ?>">
                                        <span class="dashicons dashicons-edit-large"></span>
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </td>
        </tr>
        <?php endif; ?>
    <?php endforeach; ?>
    </tbody>
</table>
<?php endif; ?>

<div id="ubo_create_error_div" class="ubo_create_errormessage" style="display:none;"></div>

<?php if ( $show_create_button ) : ?>
    <div id="ubo_create_div" class="ubo_create_div">
        <input type="button"
            class="btn btn-inverse btn-small"
            id="ubo_create_declaration_button"
            value="<?php esc_html_e( 'Create an UBO declaration', 'dokan' ); ?>">
    </div>
<?php endif; ?>

<div class="ubo_buttons_div">
    <?php if ( $created_ubo_button ) : ?>
    <div id="ubo_create_div">
        <input type="button"
            class="btn btn-inverse btn-small dokan-mp-btn-submit"
            style="float:none;"
            id="ubo_add_button"
            value="<?php esc_attr_e( 'Add UBO', 'dokan' ); ?>">
    </div>
    <?php endif; ?>
    <?php if ( $ubo_exists ) : ?>
    <div id="ubo_askvalidation_div">
        <input type="button"
            class="btn btn-inverse btn-small dokan-mp-btn-submit"
            style="float:none;"
            id="ubo_askvalidation_button"
            value="<?php esc_attr_e( 'Ask for validation', 'dokan' ); ?>">
    </div>
    <?php endif; ?>
<div>

<input type="hidden" id="ubo_mp_id" value="<?php echo esc_attr( $existing_account_id ); ?>">
<input type="hidden" id="ubo_declaration_id" value="<?php echo esc_attr( $created_ubo_id ); ?>">
<input type="hidden" id="ubo_element_id">

<div id="ubo_list_errors" class="woocommerce" style="display:none;">
    <ul id="ubo_element_errors" class="woocommerce-error" role="alert">
        <?php foreach ( $fields as $key => $value ) : ?>
        <li id="dokan_mp_<?php echo esc_attr( $key ); ?>_error" style="display:none;">
            <?php
                // translators: input label
                printf( esc_html__( 'Error: %s is required', 'dokan' ), $value['label'] );
            ?>
        </li>
        <?php endforeach; ?>
    </ul>
</div>

<div class="ubo-form-fields ubo_table_form" id="form_add_ubo_element" style="display:none;">
    <?php
    foreach ( $fields as $field_name => $field_data ) :
        woocommerce_form_field( $field_name, $field_data );
    endforeach;
    ?>

    <input type="button"
        class="btn btn-inverse btn-small"
        type="button"
        id="add_button_ubo_element"
        value="<?php esc_attr_e( 'Send UBO', 'dokan' ); ?>">
    <input type="button"
        class="btn btn-inverse btn-small"
        style="display:none;"
        id="update_button_ubo_element"
        value="<?php esc_attr_e( 'Update UBO', 'dokan' ); ?>" >
    &nbsp;
    <input type="button"
        class="btn btn-inverse btn-small"
        id="cancel_button_ubo_element"
        value="<?php esc_attr_e( 'Cancel', 'dokan' ); ?>" >
</div>
