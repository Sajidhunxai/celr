<?php
/**
 *  Dokan store location select options section
 *
 *  @since 3.3.7
 *
 *  @package dokan
 */

$vendor_id = isset( $vendor_id ) ? $vendor_id : 0;
?>

<div class="store-pickup-select-options">
    <select class="delivery-store-location-picker" data-vendor_id="<?php echo esc_attr( $vendor_id ); ?>" id="delivery-store-location-picker-<?php echo esc_attr( $vendor_id ); ?>" name="vendor_delivery_time[<?php echo esc_attr( $vendor_id ); ?>][store_pickup_location]">
        <option selected disabled><?php esc_html_e( 'Select store location', 'dokan' ); ?></option>
    </select>

    <div class="store-address vendor-info" style="display: none">
        <span id="delivery-store-location-address-<?php echo esc_attr( $vendor_id ); ?>"></span>
    </div>
</div>
