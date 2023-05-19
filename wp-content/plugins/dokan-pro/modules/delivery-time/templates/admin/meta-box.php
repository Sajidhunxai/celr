<?php
/**
 * Dokan WC order details page delivery time meta box
 *
 * @since 3.3.0
 * @package DokanPro
 */

use \WeDevs\DokanPro\Modules\DeliveryTime\Helper;
use \WeDevs\DokanPro\Modules\DeliveryTime\StorePickup\Helper as StorePickupHelper;

$vendor_info             = ! empty( $vendor_info ) ? $vendor_info : [];
$vendor_id               = ! empty( $vendor_info['vendor_id'] ) ? $vendor_info['vendor_id'] : '';
$store_location          = ! empty( $vendor_info['store_location'] ) ? $vendor_info['store_location'] : '';
$delivery_type           = ! empty( $delivery_type ) ? $delivery_type : '';
$current_delivery_label  = __( 'Current delivery time:', 'dokan' );
$date_picker_placeholder = __( 'Select delivery date', 'dokan' );
$date_picker_label       = __( 'Delivery date:', 'dokan' );
$slot_picker_label       = __( 'Delivery time slot:', 'dokan' );
$store_locations         = StorePickupHelper::get_vendor_store_pickup_locations( $vendor_id );
$location_count          = count( StorePickupHelper::get_vendor_store_pickup_locations( $vendor_id ) );
// check if delivery time and location pickup is active for this vendor
$is_delivery_time_active  = ! empty( $vendor_info['vendor_delivery_options']['delivery_support'] ) && 'on' === $vendor_info['vendor_delivery_options']['delivery_support'];
$is_store_location_active = StorePickupHelper::is_store_pickup_location_active( $vendor_id );

// If delivery type store pickup then change delivery type strings.
if ( 'store-pickup' === $delivery_type ) {
    $current_delivery_label  = __( 'Current pickup time:', 'dokan' );
    $date_picker_placeholder = __( 'Select store pickup date', 'dokan' );
    $date_picker_label       = __( 'Pickup date:', 'dokan' );
    $slot_picker_label       = __( 'Pickup time slot:', 'dokan' );
}
?>

<div id="dokan-admin-delivery-time">
    <div class="order_data_column_container">
        <div class="header">
            <div id="dokan-delivery-type-time" style="padding-bottom: 10px;">
                <span>
                    <strong>
                        <?php
                        /* translators: %s: Delivery label */
                        printf( esc_html( '%s' ), $current_delivery_label );
                        ?>
                    </strong>
                </span>
                <span><?php echo esc_html( Helper::get_formatted_delivery_date_time_string( $vendor_info['vendor_selected_delivery_date'], $vendor_info['vendor_selected_delivery_slot'] ) ); ?></span>
            </div>
            <div id="dokan-store-pickup-location" style="display: <?php echo ( 'store-pickup' === $delivery_type && ! empty( $store_location ) ) ? 'block' : 'none'; ?>;">
                <strong><?php esc_html_e( 'Store location: ', 'dokan' ); ?></strong>
                <span><?php echo esc_html( $store_location ); ?></span>
                <hr style="margin-top: 15px; border-bottom: 0; border-color: #c3c4c7;" />
            </div>
            <input name="vendor_selected_current_delivery_date_slot" type="hidden"
                value="<?php echo esc_attr( Helper::get_formatted_delivery_date_time_string( $vendor_info['vendor_selected_delivery_date'], $vendor_info['vendor_selected_delivery_slot'] ) ); ?>" />
        </div>
        <div class="order_data_column">
            <p class="admin-delivery-type">
                <?php if ( $is_delivery_time_active ) : ?>
                    <input type="radio" id="delivery-type-delivery" class="delivery-type-delivery"
                        name="dokan_delivery_type_delivery" <?php checked( $delivery_type, 'delivery' ); ?> />
                    <label for="delivery-type-delivery" class="delivery-type-delivery" style="padding: 0 30px 0 7px;">
                        <?php esc_html_e( 'Delivery', 'dokan' ); ?>
                    </label>
                <?php endif; ?>
                <?php if ( $is_store_location_active && $location_count > 0 ) : ?>
                    <input type="radio" id="delivery-type-pickup" class="delivery-type-pickup"
                        name="dokan_delivery_type_pickup" <?php checked( $delivery_type, 'store-pickup' ); ?> />
                    <label for="delivery-type-pickup" class="delivery-type-pickup" style="padding-left: 5px;">
                        <?php esc_html_e( 'Store Pickup', 'dokan' ); ?>
                    </label>
                <?php endif; ?>
                <input type="hidden" id="selected-delivery-type" value="<?php echo esc_attr( $delivery_type ); ?>" />
            </p>
            <p class="form-field form-field-wide">
                <label for="dokan-delivery-admin-date-picker">
                    <?php
                    /* translators: %s: date picker label */
                    printf( esc_html( '%s' ), $date_picker_label );
                    ?>
                </label>
                <input type="text" id="dokan-delivery-admin-date-picker" class="date-picker"
                    data-vendor_id="<?php echo esc_attr( $vendor_info['vendor_id'] ); ?>"
                    data-nonce="<?php echo esc_attr( wp_create_nonce( 'dokan_delivery_time' ) ); ?>"
                    placeholder="<?php printf( esc_attr( '%s' ), $date_picker_placeholder ); ?>"
                    maxlength="10" readonly />
                <input type="hidden" id="dokan_delivery_date_input" name="dokan_delivery_date" value="" />
            </p>
            <ul class="form-field form-field-wide">
                <li class="wide" >
                    <label for="dokan-delivery-admin-time-slot-picker">
                        <?php
                        /* translators: %s: slot picker label */
                        printf( esc_html( '%s' ), $slot_picker_label );
                        ?>
                    </label>
                    <select id="dokan-delivery-admin-time-slot-picker" style="width: 100%;" name="dokan_delivery_time_slot">
                        <option value=""><?php esc_html_e( 'Select time slot', 'dokan' ); ?></option>
                    </select>
                    <p style="display: none; color: #b32d2e; margin: 4px 0; font-size: 11px;" class="dokan-error">
                        <?php
                        echo sprintf(
                            /* translators: %s: Delivery type. */
                            esc_html__( '%s time slot can\'t be empty', 'dokan' ),
                            ucfirst( $delivery_type )
                        );
                        ?>
                    </p>
                </li>

                <li class="wide store-pickup-select-options" style="display: <?php echo ( ( ! empty( $delivery_type ) && 'store-pickup' === $delivery_type ) ? 'block' : 'none' ); ?>;">
                    <label for="dokan-admin-store-location-picker"><?php esc_html_e( 'Pickup location:', 'dokan' ); ?></label>
                    <select id="dokan-admin-store-location-picker" style="width: 100%;" name="dokan_store_pickup_location">
                        <option value=""><?php esc_html_e( 'Select store location', 'dokan' ); ?></option>

                        <?php foreach ( $store_locations as $index => $location ) : ?>
                            <?php $store_location = StorePickupHelper::get_formatted_vendor_store_pickup_location( $location, ' ', $location['location_name'] ); ?>
                            <option data-value="<?php echo esc_attr( $store_location ); ?>" value="<?php echo esc_attr( $location['location_name'] . '-' . $index ); ?>">
                                <?php echo esc_html( $location['location_name'] ); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p style="display: none; color: #b32d2e; margin: 4px 0; font-size: 11px;" class="dokan-error">
                        <?php esc_html_e( 'Pickup location can\'t be empty', 'dokan' ); ?>
                    </p>
                    <hr style="margin-top: 18px; border-bottom: 0; border-color: #c3c4c7; display: none;" />
                    <p class="store-address" style="display: none"></p>
                </li>
            </ul>
            <?php wp_nonce_field( 'dokan_delivery_admin_time_box_action', 'dokan_delivery_admin_time_box_nonce' ); ?>
        </div>
    </div>
</div>
