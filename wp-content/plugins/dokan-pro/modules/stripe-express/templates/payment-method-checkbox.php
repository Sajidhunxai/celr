<fieldset <?php echo $force_checked ? 'style="display:none;"' : ''; ?>>
    <p class="form-row woocommerce-SavedPaymentMethods-saveNew">
        <input id="<?php echo esc_attr( $id ); ?>"
            name="<?php echo esc_attr( $id ); ?>"
            type="checkbox"
            value="true"
            style="width:auto;"
            <?php echo $force_checked ? 'checked' : ''; ?>
        />
        <label for="<?php echo esc_attr( $id ); ?>" style="display:inline;">
            <?php
            echo esc_html(
                apply_filters(
                    'dokan_stripe_express_save_to_account_text',
                    __( 'Save payment information to my account for future purchases.', 'dokan' )
                )
            );
            ?>
        </label>
    </p>
</fieldset>
