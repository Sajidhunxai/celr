<?php
/**
 * Dokan Delivery time wc order details
 *
 * @since 3.3.0
 * @package DokanPro
 */

use \WeDevs\DokanPro\Modules\DeliveryTime\Helper;
use WeDevs\DokanPro\Modules\DeliveryTime\StorePickup\Helper as StorePickupHelper;

$delivery_time_date_slot = isset( $delivery_time_date_slot ) ? $delivery_time_date_slot : [];

$order_id      = ! empty( $order ) ? $order->get_id() : 0;
$location      = ! empty( $order ) ? $order->get_meta( 'dokan_store_pickup_location' ) : '';
$vendor_id     = ! empty( $vendor_id ) ? $vendor_id : 0;

$delivery_type    = ! empty( $delivery_type ) ? $delivery_type : 'delivery';
$is_delivery_type = $delivery_type === 'delivery';
$details_heading  = ! $is_delivery_type ? __( 'Store location pickup: ', 'dokan' ) : __( 'Delivery Date: ', 'dokan' );

if ( empty( $delivery_time_date_slot['date'] ) ) {
    return;
}
?>

<div id="dokan-delivery-time-slot-order-details">
    <svg xmlns="http://www.w3.org/2000/svg" fill="none" height="20" viewBox="0 0 24 24" stroke="#333333">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
    </svg>
    <div class="main">
        <span style="margin-right: 8px;"><strong><?php echo esc_html( $details_heading ); ?></strong></span>

        <?php if ( $is_delivery_type ) : ?>
            <span><?php echo esc_html( Helper::get_formatted_delivery_date_time_string( $delivery_time_date_slot['date'], $delivery_time_date_slot['slot'] ) ); ?></span>
        <?php else : ?>
            <span><?php echo esc_html( StorePickupHelper::get_formatted_date_store_location_string( $delivery_time_date_slot['date'], $location, $delivery_time_date_slot['slot'] ) ); ?></span>
        <?php endif; ?>
    </div>
</div>
