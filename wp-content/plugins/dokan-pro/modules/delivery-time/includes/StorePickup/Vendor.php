<?php

namespace WeDevs\DokanPro\Modules\DeliveryTime\StorePickup;

/**
 * Class Store Pickup Vendor
 *
 * @package WeDevs\DokanPro\Modules\DeliveryTime\StorePickup
 */
class Vendor {

    /**
     * Store location pickup Vendor constructor
     *
     * @since 3.3.7
     */
    public function __construct() {
        add_action( 'dokan_order_details_after_customer_info', [ $this, 'render_store_pickup_location_order_details' ], 20, 1 );
        add_action( 'dokan_delivery_time_settings_after_delivery_show_time_option', [ $this, 'render_store_location_enable_checkbox' ], 10, 1 );
        add_action( 'dokan_delivery_time_after_save_settings', [ $this, 'save_store_location_allow_setting' ], 10 );
        add_action( 'dokan_delivery_time_disabled_override', [ $this, 'save_store_location_allow_setting' ], 10 );
    }

    /**
     * Renders store pickup location order details
     *
     * @since 3.3.7
     *
     * @param /WC_Order $order
     *
     * @return void
     */
    public function render_store_pickup_location_order_details( $order ) {
        $pickup_location = $order->get_meta( 'dokan_store_pickup_location' );
        $order_id        = $order->get_id();
        $vendor_id       = (int) dokan_get_seller_id_by_order( $order_id );
        $date            = $order->get_meta( 'dokan_delivery_time_date' );
        $slot            = $order->get_meta( 'dokan_delivery_time_slot' );

        if ( empty( $pickup_location ) || empty( $date ) || empty( $slot ) ) {
            return;
        }

        dokan_get_template_part(
            'store-pickup/store-location-vendor-order-details', '', [
                'is_delivery_time' => true,
                'location'         => $pickup_location,
                'date'             => $date,
                'slot'             => $slot,
                'seller_id'        => $vendor_id,
                'order_id'         => $order_id,
            ]
        );
    }

    /**
     * Renders store location enable checkbox
     *
     * @since 3.3.7
     *
     * @param array $vendor_settings
     *
     * @return void
     */
    public function render_store_location_enable_checkbox( $vendor_settings ) {
        $vendor_id = dokan_get_current_user_id();

        if ( ! dokan_is_user_seller( $vendor_id ) ) {
            return;
        }

        $enable_store_pickup = Helper::is_store_pickup_location_active( $vendor_id, false );

        dokan_get_template_part(
            'store-pickup/store-location-settings-form', '', [
                'is_delivery_time'    => true,
                'enable_store_pickup' => $enable_store_pickup,
            ]
        );
    }

    /**
     * Save store location allow setting
     *
     * @since 3.3.7
     *
     * @return void
     */
    public function save_store_location_allow_setting() {
        if ( ! isset( $_POST['dokan_update_delivery_time_settings'] ) || ! isset( $_POST['dokan_delivery_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dokan_delivery_settings_nonce'] ) ), 'dokan_delivery_time_form_action' ) ) {
            return;
        }

        if ( ! isset( $_POST['store-pickup'] ) ) { // phpcs:ignore
            return;
        }

        $vendor_id = dokan_get_current_user_id();

        if ( ! dokan_is_user_seller( $vendor_id ) ) {
            return;
        }

        $enable_store_pickup_location = wc_clean( wp_unslash( $_POST['store-pickup'] ) );

        $profile_info = dokan_get_store_info( $vendor_id );

        $profile_info['vendor_store_location_pickup']['enable_store_pickup_location'] = $enable_store_pickup_location;

        update_user_meta( $vendor_id, 'dokan_profile_settings', $profile_info );
    }
}
