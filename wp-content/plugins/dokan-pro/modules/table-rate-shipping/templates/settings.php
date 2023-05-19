<?php
/**
 * Dokan Table Rate Shipping Template
 *
 * @since 3.4.0
 *
 * @package dokan
 */
?>
<?php do_action( 'dokan_table_rate_shipping_setting_start' ); ?>

<form method="post" id="dokan-table-rate-shipping-setting-form" action="" class="dokan-form-horizontal">

    <?php
    /**
     * Dokan Table Rate Shipping Setting Form Hook
     *
     * @since 3.4.0
     */
    do_action( 'dokan_table_rate_shipping_setting_form' );
    ?>

    <div class="dokan-form-group">
        <div class="dokan-w4 dokan-text-left">

            <?php wp_nonce_field( 'dokan_table_rate_shipping_settings', 'dokan_table_rate_shipping_settings_nonce' ); ?>
            <input type="submit" name="dokan_update_table_rate_shipping_settings" class="dokan-btn dokan-btn-danger dokan-btn-theme" value="<?php esc_attr_e( 'Update Settings', 'dokan' ); ?>">
        </div>
    </div>
</form><!-- .table-rate-shipping-setting-form -->

<?php do_action( 'dokan_table_rate_shipping_setting_end' ); ?>
