<?php
/**
 * Dokan Distance Rate Shipping Template
 *
 * @since 3.4.2
 *
 * @package dokan
 */
?>
<?php do_action( 'dokan_distance_rate_shipping_setting_start' ); ?>

<form method="post" id="dokan-distance-rate-shipping-setting-form" action="" class="dokan-form-horizontal">

    <?php
    /**
     * Dokan Distance Rate Shipping Setting Form Hook
     *
     * @since 3.4.2
     */
    do_action( 'dokan_distance_rate_shipping_setting_form' );
    ?>

    <div class="dokan-form-group">
        <div class="dokan-w4 dokan-text-left">

            <?php wp_nonce_field( 'dokan_distance_rate_shipping_settings', 'dokan_distance_rate_shipping_settings_nonce' ); ?>
            <input type="submit" name="dokan_update_distance_rate_shipping_settings" class="dokan-btn dokan-btn-danger dokan-btn-theme" value="<?php esc_attr_e( 'Update Settings', 'dokan' ); ?>">
        </div>
    </div>
</form><!-- .distance-rate-shipping-setting-form -->

<?php do_action( 'dokan_distance_rate_shipping_setting_end' ); ?>
