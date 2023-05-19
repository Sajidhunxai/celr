<?php
/**
 *  Dokan store location vendor order details
 *
 *  @since 3.3.7
 *
 *  @package dokan
 */

use \WeDevs\DokanPro\Modules\DeliveryTime\Helper as MainHelper;
use \WeDevs\DokanPro\Modules\DeliveryTime\StorePickup\Helper;

$location       = isset( $location ) ? $location : '';
$date           = isset( $date ) ? $date : '';
$slot           = isset( $slot ) ? $slot : '';
$delivery_info  = MainHelper::get_order_delivery_info( $seller_id, $order_id );
$formatted_slot = MainHelper::get_formatted_delivery_slot_string( $slot );
?>

<div class="vendor-store-pickup-location" style="display: <?php echo 'store-pickup' === $delivery_info->delivery_type ? 'block' : 'none'; ?>;">
    <li>
        <span><?php esc_html_e( 'Store location pickup:', 'dokan' ); ?></span>
        <div><?php echo esc_html( Helper::get_formatted_date_store_location_string( $date, $location, $formatted_slot ) ); ?></div>
    </li>
</div>
