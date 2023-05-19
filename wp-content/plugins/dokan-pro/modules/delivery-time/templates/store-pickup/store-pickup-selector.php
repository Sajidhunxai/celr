<?php
/**
 *  Dokan store location selector section
 *
 *  @since 3.3.7
 *
 *  @package dokan
 */

$vendor_id                    = isset( $vendor_id ) ? $vendor_id : 0;
$vendor_info                  = isset( $vendor_info ) ? $vendor_info : [];
$location_count               = isset( $location_count ) ? $location_count : 0;
$vendor_can_override_settings = isset( $vendor_can_override_settings ) && $vendor_can_override_settings;

$is_vendor_delivery_time_active  = ! empty( $vendor_info['is_delivery_time_active'] ) && $vendor_can_override_settings;
$is_vendor_store_location_active = ! empty( $vendor_info['is_store_location_active'] ) && $vendor_can_override_settings;

$is_admin_store_pickup_active  = ! empty( $admin_support_settings['store-pickup'] ) && ! $vendor_can_override_settings;
$is_admin_delivery_time_active = ! empty( $admin_support_settings['delivery'] ) && ! $vendor_can_override_settings;

$is_delivery_time_active     = $is_admin_delivery_time_active || $is_vendor_delivery_time_active;
$is_store_pickup_time_active = ( $is_admin_store_pickup_active || $is_vendor_store_location_active ) && ( $location_count > 0 );

$active_selector = $is_delivery_time_active ? 'delivery' : 'store-pickup';
?>

<?php if ( $is_delivery_time_active || $is_store_pickup_time_active ) : ?>
    <div class="dokan-store-location-selector">
        <div class="selector-wrapper">
            <?php if ( $is_delivery_time_active ) : ?>
                <div data-vendor_id="<?php echo esc_attr( $vendor_id ); ?>" data-selector="<?php echo esc_attr( 'delivery' ); ?>" id="dokan-delivery-selector-<?php echo esc_attr( $vendor_id ); ?>" class="selector mr-16 <?php echo 'delivery' === $active_selector ? esc_attr( 'active-selector' ) : ''; ?>">
                    <svg height="18" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                        <path d="M386.689 304.403c-35.587 0-64.538 28.951-64.538 64.538s28.951 64.538 64.538 64.538c35.593 0 64.538-28.951 64.538-64.538s-28.951-64.538-64.538-64.538zm0 96.807c-17.796 0-32.269-14.473-32.269-32.269s14.473-32.269 32.269-32.269 32.269 14.473 32.269 32.269c0 17.797-14.473 32.269-32.269 32.269zM166.185 304.403c-35.587 0-64.538 28.951-64.538 64.538s28.951 64.538 64.538 64.538 64.538-28.951 64.538-64.538-28.951-64.538-64.538-64.538zm0 96.807c-17.796 0-32.269-14.473-32.269-32.269s14.473-32.269 32.269-32.269c17.791 0 32.269 14.473 32.269 32.269 0 17.797-14.473 32.269-32.269 32.269zM430.15 119.675a16.143 16.143 0 00-14.419-8.885h-84.975v32.269h75.025l43.934 87.384 28.838-14.5-48.403-96.268z"/>
                        <path d="M216.202 353.345h122.084v32.269H216.202zM117.781 353.345H61.849c-8.912 0-16.134 7.223-16.134 16.134 0 8.912 7.223 16.134 16.134 16.134h55.933c8.912 0 16.134-7.223 16.134-16.134 0-8.912-7.223-16.134-16.135-16.134zM508.612 254.709l-31.736-40.874a16.112 16.112 0 00-12.741-6.239H346.891V94.655c0-8.912-7.223-16.134-16.134-16.134H61.849c-8.912 0-16.134 7.223-16.134 16.134s7.223 16.134 16.134 16.134h252.773V223.73c0 8.912 7.223 16.134 16.134 16.134h125.478l23.497 30.268v83.211h-44.639c-8.912 0-16.134 7.223-16.134 16.134 0 8.912 7.223 16.134 16.134 16.134h60.773c8.912 0 16.134-7.223 16.135-16.134V264.605c0-3.582-1.194-7.067-3.388-9.896zM116.706 271.597H42.487c-8.912 0-16.134 7.223-16.134 16.134 0 8.912 7.223 16.134 16.134 16.134h74.218c8.912 0 16.134-7.223 16.134-16.134.001-8.911-7.222-16.134-16.133-16.134zM153.815 208.134H16.134C7.223 208.134 0 215.357 0 224.269s7.223 16.134 16.134 16.134h137.681c8.912 0 16.134-7.223 16.134-16.134s-7.222-16.135-16.134-16.135z"/>
                        <path d="M180.168 144.672H42.487c-8.912 0-16.134 7.223-16.134 16.134 0 8.912 7.223 16.134 16.134 16.134h137.681c8.912 0 16.134-7.223 16.134-16.134.001-8.911-7.222-16.134-16.134-16.134z"/>
                    </svg>
                    <span><?php esc_html_e( 'Delivery', 'dokan' ); ?></span>
                    <div class="dokan-delivery-time-tooltip info-icon" data-tip="<?php esc_attr_e( 'Home delivery at your doorstep', 'dokan' ); ?>" tabindex="1">
                        <svg xmlns="http://www.w3.org/2000/svg" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                </div>
            <?php endif; ?>
            <?php if ( $is_store_pickup_time_active ) : ?>
            <div data-vendor_id="<?php echo esc_attr( $vendor_id ); ?>" data-selector="<?php echo esc_attr( 'store-pickup' ); ?>" id="dokan-store-pickup-selector-<?php echo esc_attr( $vendor_id ); ?>" class="selector <?php echo 'store-pickup' === $active_selector ? esc_attr( 'active-selector' ) : ''; ?>">
                <svg height="18" viewBox="0 0 512.456 512.456" xmlns="http://www.w3.org/2000/svg">
                    <path d="M446.125 305.557l-10.088-195.203h-64.368V35.309H259.952v75.046h-46.57V35.309H101.665v75.046H10.987L0 477.147h512.456v-171.59zm-38.578-165.203l8.539 165.203H309.907l-4.949-165.203h36.711v34.308h30v-34.308zm-65.878-75.045v45.046h-51.717V65.309zm-210.004 0h51.717v45.046h-51.717zM30.912 447.147l9.19-306.793h61.562v34.308h30v-34.308h51.717v34.308h30v-34.308h61.562l9.191 306.793zm283.237 0l-3.343-111.59h59.168v111.59zm168.307 0h-82.481v-111.59h82.481z"/>
                    <path d="M106.68 280.182h101.688v30H106.68z"/>
                </svg>
                <span><?php esc_html_e( 'Pickup', 'dokan' ); ?></span>
                <div class="dokan-delivery-time-tooltip info-icon" data-tip="<?php esc_attr_e( 'Pickup products from your preferred store location', 'dokan' ); ?>" tabindex="1">
                    <svg xmlns="http://www.w3.org/2000/svg" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
