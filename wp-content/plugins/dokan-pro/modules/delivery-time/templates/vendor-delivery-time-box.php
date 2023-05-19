<?php
/**
 * Dokan Vendor delivery time box template
 *
 * @since 3.3.0
 * @package DokanPro
 */

use \WeDevs\DokanPro\Modules\DeliveryTime\Helper;
use \WeDevs\DokanPro\Modules\DeliveryTime\StorePickup\Helper as StorePickupHelper;

$delivery_type            = ! empty( $delivery_type ) ? $delivery_type : '';
$location_count           = count( StorePickupHelper::get_vendor_store_pickup_locations( $vendor_id ) );
$store_locations          = StorePickupHelper::get_vendor_store_pickup_locations( $vendor_id );
$delivery_box_label       = __( 'Delivery Time', 'dokan' );
$delivery_date_label      = __( 'Delivery Date: ', 'dokan' );
$date_placeholder_label   = ! empty( $vendor_info['vendor_delivery_options']['delivery_date_label'] ) ? $vendor_info['vendor_delivery_options']['delivery_date_label'] : __( 'Select delivery date', 'dokan' );
$is_delivery_time_active  = ! empty( $vendor_info['vendor_delivery_options']['delivery_support'] ) && 'on' === $vendor_info['vendor_delivery_options']['delivery_support'];
$is_store_location_active = StorePickupHelper::is_store_pickup_location_active( $vendor_id );

// If delivery type store pickup then change delivery type strings.
if ( 'store-pickup' === $delivery_type ) {
    $delivery_box_label     = __( 'Store Pickup Time', 'dokan' );
    $delivery_date_label    = __( 'Pickup Date: ', 'dokan' );
    $date_placeholder_label = __( 'Select pickup date', 'dokan' );
}

$current_date = dokan_current_datetime();

// Delivery time
$delivery_time_date = get_post_meta( $order_id, 'dokan_delivery_time_date', true ) ? $current_date->modify( get_post_meta( $order_id, 'dokan_delivery_time_date', true ) )->format( 'F j, Y' ) : null;
$delivery_time_slot = get_post_meta( $order_id, 'dokan_delivery_time_slot', true ) ? get_post_meta( $order_id, 'dokan_delivery_time_slot', true ) : null;

$vendor_info = isset( $vendor_info ) ? $vendor_info : null;

wp_add_inline_script( 'dokan-delivery-time-vendor-script', 'let vendorInfo =' . wp_json_encode( $vendor_info ), 'before' );
?>

<div class="" style="width:100%">
    <div class="dokan-panel dokan-panel-default dokan-vendor-panel">
        <div class="dokan-panel-heading"><strong class="delivery-time-box-heading"><?php echo esc_html( $delivery_box_label ); ?></strong></div>
        <div class="dokan-panel-body general-details vendor-delivery-time-box">
            <div class="delivery-type-date-info">
                <span><?php echo esc_html( $delivery_date_label ); ?></span>
                <?php echo esc_html( Helper::get_formatted_delivery_date_time_string( $delivery_time_date, $delivery_time_slot ) ); ?>
            </div>

            <form action="" method="post">
                <input type="hidden" name="vendor_selected_current_delivery_date_slot" value="<?php echo esc_attr( \WeDevs\DokanPro\Modules\DeliveryTime\Helper::get_formatted_delivery_date_time_string( $delivery_time_date, $delivery_time_slot ) ); ?>">
                <input type="hidden" name="order_id" value="<?php echo esc_attr( $order_id ); ?>">

                <div id="vendor-delivery-type" >
                    <?php if ( $is_delivery_time_active ) : ?>
                        <input type="radio" id="delivery-type-delivery" class="delivery-type-delivery"
                            name="dokan_delivery_type_delivery" <?php checked( $delivery_type, 'delivery' ); ?> />
                        <label for="delivery-type-delivery" class="delivery-type-delivery">
                            <?php esc_html_e( 'Delivery', 'dokan' ); ?>
                        </label>
                    <?php endif; ?>
                    <?php if ( $is_store_location_active && $location_count > 0 ) : ?>
                        <input type="radio" id="delivery-type-pickup" class="delivery-type-pickup"
                            name="dokan_delivery_type_pickup" <?php checked( $delivery_type, 'store-pickup' ); ?> />
                        <label for="delivery-type-pickup" class="delivery-type-pickup">
                            <?php esc_html_e( 'Store Pickup', 'dokan' ); ?>
                        </label>
                    <?php endif; ?>
                    <input type="hidden" id="selected-delivery-type" value="<?php echo esc_attr( $delivery_type ); ?>" />
                </div>

                <input id="vendor-delivery-time-date-picker" data-vendor_id="<?php echo esc_attr( $vendor_id ); ?>"
                    data-nonce="<?php echo esc_attr( wp_create_nonce( 'dokan_delivery_time' ) ); ?>" class="delivery-time-date-picker"
                    name="dokan_delivery_date" type="text" placeholder="<?php echo esc_attr( $date_placeholder_label ); ?>"
                    value="<?php echo esc_attr( $delivery_time_date ); ?>" readonly="readonly" />
                <select class="delivery-time-slot-picker dokan-form-control" id="vendor-delivery-time-slot-picker" name="dokan_delivery_time_slot">
                    <option selected disabled><?php esc_html_e( 'Select time slot', 'dokan' ); ?></option>
                </select>

                <div class="store-pickup-select-options" style="display: <?php echo ( ( ! empty( $delivery_type ) && 'store-pickup' === $delivery_type ) ? 'block' : 'none' ); ?>;">
                    <select class="delivery-store-location-picker dokan-form-control" id="dokan-store-pickup-location" name="dokan-store-pickup-location">
                        <option value=""><?php esc_html_e( 'Select store location', 'dokan' ); ?></option>
                        <?php foreach ( $store_locations as $index => $location ) : ?>
                            <?php $store_location = StorePickupHelper::get_formatted_vendor_store_pickup_location( $location, ' ', $location['location_name'] ); ?>
                            <option data-value="<?php echo esc_attr( $store_location ); ?>" value="<?php echo esc_attr( $location['location_name'] . '-' . $index ); ?>">
                                <?php echo esc_html( $location['location_name'] ); ?>
                            </option>
                        <?php endforeach; ?>

                    </select>

                    <div class="store-address">
                        <span id="delivery-store-location-address"></span>
                    </div>
                </div>

                <?php wp_nonce_field( 'dokan_vendor_delivery_time_box_action', 'dokan_vendor_delivery_time_box_nonce' ); ?>
                <input type="submit" id="dokan_update_delivery_time" name="dokan_update_delivery_time" class="dokan-btn add_note btn btn-sm btn-theme" value="<?php esc_attr_e( 'Update', 'dokan' ); ?>">
            </form>
        </div>
    </div>
</div>
