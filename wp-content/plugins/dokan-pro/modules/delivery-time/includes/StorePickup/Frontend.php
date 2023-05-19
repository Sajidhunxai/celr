<?php

namespace WeDevs\DokanPro\Modules\DeliveryTime\StorePickup;

/**
 * Class Store Pickup Frontend
 *
 * @package WeDevs\DokanPro\Modules\DeliveryTime\StorePickup
 */
class Frontend {

    /**
     * Store location pickup Frontend constructor
     *
     * @since 3.3.7
     */
    public function __construct() {
        add_action( 'dokan_after_delivery_box_info_message', [ $this, 'render_store_pickup_location_type_selector_section' ], 10, 2 );
        add_action( 'dokan_after_delivery_time_slot_picker', [ $this, 'render_store_pickup_location_select_option_section' ], 10, 2 );
        add_action( 'dokan_delivery_time_before_meta_save', [ $this, 'save_store_pickup_location_meta' ], 10, 2 );

        add_action( 'wp_ajax_dokan_store_location_get_items', [ $this, 'store_location_get_items' ] );
        add_action( 'wp_ajax_nopriv_dokan_store_location_get_items', [ $this, 'store_location_get_items' ] );

        add_action( 'woocommerce_order_details_before_order_table_items', [ $this, 'render_store_location_wc_order_details' ], 20, 1 );

        add_filter( 'dokan_delivery_time_checkout_args', [ $this, 'handle_store_location_checkout_args' ], 10, 3 );
    }

    /**
     * Renders store pickup location selector section
     *
     * @since 3.3.7
     *
     * @param int $vendor_id
     * @param array $vendor_info
     *
     * @return void
     */
    public function render_store_pickup_location_type_selector_section( $vendor_id, $vendor_info ) {
        // Getting count of locations & admin delivery settings.
        $location_count               = count( Helper::get_vendor_store_pickup_locations( $vendor_id ) );
        $admin_support_settings       = dokan_get_option( 'delivery_support', 'dokan_delivery_time', '' );
        $vendor_can_override_settings = dokan_get_option( 'allow_vendor_override_settings', 'dokan_delivery_time', 'off' );

        dokan_get_template_part(
            'store-pickup/store-pickup-selector', '', [
                'is_delivery_time'             => true,
                'vendor_id'                    => $vendor_id,
                'vendor_info'                  => $vendor_info,
                'location_count'               => $location_count,
                'admin_support_settings'       => $admin_support_settings,
                'vendor_can_override_settings' => $vendor_can_override_settings === 'on',
            ]
        );
    }

    /**
     * Renders store pickup location select option section
     *
     * @since 3.3.7
     *
     * @param int $vendor_id
     * @param array $vendor_info
     *
     * @return void
     */
    public function render_store_pickup_location_select_option_section( $vendor_id, $vendor_info ) {
        if ( ! Helper::is_store_pickup_location_active( $vendor_id ) ) {
            return;
        }

        dokan_get_template_part(
            'store-pickup/store-pickup-select-options', '', [
                'is_delivery_time' => true,
                'vendor_id'        => $vendor_id,
            ]
        );
    }

    /**
     * Ajax request for getting vendor store locations
     *
     * @since 3.3.7
     *
     * @return void
     */
    public function store_location_get_items() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'dokan_delivery_time' ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'dokan' ), 403 );
        }

        if ( ! isset( $_POST['action'] ) || 'dokan_store_location_get_items' !== wc_clean( wp_unslash( $_POST['action'] ) ) ) {
            wp_send_json_error( __( 'Something went wrong', 'dokan' ), 403 );
        }

        if ( ! isset( $_POST['vendor_id'] ) ) {
            wp_send_json_error( __( 'Vendor ID not found', 'dokan' ), 403 );
        }

        $vendor_id = absint( wp_unslash( $_POST['vendor_id'] ) );

        $vendor_store_locations = Helper::get_vendor_store_pickup_locations( $vendor_id, false, true );

        $formatted_locations = [];

        foreach ( $vendor_store_locations as $location_name => $location ) {
            $formatted_locations[ $location_name ] = Helper::get_formatted_vendor_store_pickup_location( $location, ' ', $location['location_name'] );
        }

        wp_send_json_success( [ 'vendor_store_locations' => $formatted_locations ], 200 );
    }

    /**
     * Store pickup location checkout args
     *
     * @since 3.3.7
     *
     * @param array $args
     * @param array $data
     * @param int $vendor_id
     *
     * @return array
     */
    public function handle_store_location_checkout_args( $args, $data, $vendor_id ) {
        $store_pickup_location = isset( $data[ $vendor_id ]['store_pickup_location'] ) ? wp_unslash( wc_clean( $data[ $vendor_id ]['store_pickup_location'] ) ) : '';

        $args['store_pickup_location'] = $store_pickup_location;

        return $args;
    }

    /**
     * Renders store location on WC order details page
     *
     * @since 3.3.7
     *
     * @param \WC_Order $order
     *
     * @return void
     */
    public function render_store_location_wc_order_details( $order ) {
        if ( ! $order ) {
            return;
        }

        // Getting store pickup date meta
        $vendor_delivery_date = $order->get_meta( 'dokan_delivery_time_date' );

        // Getting store pickup slot meta
        $delivery_time_slot = $order->get_meta( 'dokan_delivery_time_slot' );

        if ( empty( $vendor_delivery_date ) || empty( $delivery_time_slot ) ) {
            return;
        }

        // Getting store pickup location meta
        $store_location = $order->get_meta( 'dokan_store_pickup_location' );

        if ( empty( $store_location ) ) {
            return;
        }

        dokan_get_template_part(
            'store-pickup/store-location-order-details', '', [
                'is_delivery_time' => true,
                'location'         => $store_location,
                'order'            => $order,
                'date'             => $vendor_delivery_date,
                'slot'             => $delivery_time_slot,
            ]
        );
    }

    /**
     * Saves meta for store pickup location
     *
     * @since 3.3.7
     *
     * @param \WC_Order $order
     * @param array data
     *
     * @return void
     */
    public function save_store_pickup_location_meta( $order, $data ) {
        $store_pickup_location  = isset( $data['store_pickup_location'] ) ? $data['store_pickup_location'] : '';
        $selected_delivery_type = isset( $data['selected_delivery_type'] ) ? $data['selected_delivery_type'] : '';

        if ( empty( $store_pickup_location ) || empty( $selected_delivery_type ) ) {
            return;
        }

        // Adding order meta if the delivery type is store-pickup
        if ( 'store-pickup' === $selected_delivery_type ) {
            $order->update_meta_data( 'dokan_store_pickup_location', $store_pickup_location );
        }
    }
}
