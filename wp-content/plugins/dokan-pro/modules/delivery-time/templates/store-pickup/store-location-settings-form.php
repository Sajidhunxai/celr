<?php
/**
 *  Dokan store location settings for Delivery Time settings form
 *
 *  @since 3.3.7
 *
 *  @package dokan
 */

$enable_store_pickup = isset( $enable_store_pickup ) ? $enable_store_pickup : 'no';
?>

<div class="dokan-form-group">
    <label>
        <input name="store-pickup" type="hidden" value="no">
        <input type="checkbox" name="store-pickup" id="enable-store-location-pickup" value="yes" <?php checked( 'yes', $enable_store_pickup ); ?>> <?php esc_html_e( 'Store Pickup', 'dokan' ); ?>
    </label>
</div>
