<?php
/**
 *  Dokan store location WC order details
 *
 *  @since 3.3.7
 *
 *  @package dokan
 */

use \WeDevs\DokanPro\Modules\DeliveryTime\Helper as DeliveryHelper;
use \WeDevs\DokanPro\Modules\DeliveryTime\StorePickup\Helper;

$location = isset( $location ) ? $location : '';
$date     = isset( $date ) ? $date : '';
$slot     = isset( $slot ) ? $slot : '';

$order_id      = ! empty( $order ) ? $order->get_id() : 0;
$vendor_id     = (int) dokan_get_seller_id_by_order( $order_id );
$delivery_info = DeliveryHelper::get_order_delivery_info( $vendor_id, $order_id );

$delivery_type   = ! empty( $delivery_info->delivery_type ) ? $delivery_info->delivery_type : 'delivery';
$is_store_pickup = $delivery_type === 'store-pickup';
$details_heading = $is_store_pickup ? __( 'Store location pickup: ', 'dokan' ) : __( 'Delivery Date: ', 'dokan' );
?>

<div id="dokan-store-location-order-details">
    <div class="main">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none"height="20" viewBox="0 0 24 24" stroke="#333333" style="align-self: flex-start; margin: 2.5px 4px 0 0;" >
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <div>
            <span style="margin: 0 8px 0 4px;"><strong><?php echo esc_html( $details_heading ); ?></strong></span>
            <?php if ( $is_store_pickup ) : ?>
                <span><?php echo esc_html( Helper::get_formatted_date_store_location_string( $date, $location, $slot ) ); ?></span>
            <?php else : ?>
                <span><?php echo esc_html( DeliveryHelper::get_formatted_delivery_date_time_string( $date, $slot ) ); ?></span>
            <?php endif; ?>
        </div>
    </div>
</div>
