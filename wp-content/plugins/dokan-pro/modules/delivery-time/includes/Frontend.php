<?php

namespace WeDevs\DokanPro\Modules\DeliveryTime;

use WeDevs\DokanPro\Modules\DeliveryTime\StorePickup\Helper as StorePickupHelper;

/**
 * Class Frontend
 *
 * @since 3.3.0
 *
 * @package WeDevs\DokanPro\Modules\DeliveryTime
 */
class Frontend {

    /**
     * Delivery time Frontend constructor
     *
     * @since 3.3.0
     */
    public function __construct() {
        // Hooks
        add_action( 'woocommerce_review_order_before_payment', [ $this, 'render_delivery_time_template' ], 10 );
        add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts' ], 20 );
        add_action( 'dokan_create_parent_order', [ $this, 'save_delivery_time_args' ], 20, 2 );
        add_action( 'dokan_checkout_update_order_meta', [ $this, 'save_delivery_time_args' ], 20, 2 );
        add_action( 'woocommerce_order_details_before_order_table_items', [ $this, 'render_delivery_time_wc_order_details' ], 20, 1 );
        add_action( 'wp_ajax_nopriv_dokan_get_delivery_time_slot', [ $this, 'get_vendor_delivery_time_slot' ] );
        add_action( 'wp_ajax_dokan_get_delivery_time_slot', [ $this, 'get_vendor_delivery_time_slot' ] );
        add_action( 'woocommerce_after_checkout_validation', [ $this, 'validate_delivery_time_slot_args' ], 20, 2 );

        add_filter( 'dokan_localized_args', [ $this, 'add_i18n_date_format_localized_variable' ] );
    }

    /**
     * Renders Delivery time box to checkout page
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function render_delivery_time_template() {
        $vendor_infos = $this->get_vendor_delivery_time_info();

        if ( empty( $vendor_infos ) ) {
            return;
        }

        dokan_get_template_part(
            'delivery-time-box', '', [
                'is_delivery_time' => true,
                'vendor_infos'     => $vendor_infos,
            ]
        );
    }

    /**
     * Loads scripts
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function load_scripts() {
        if ( is_checkout() ) {
            wp_enqueue_script( 'dokan-delivery-time-flatpickr-script' );
            wp_enqueue_script( 'dokan-delivery-time-main-script' );

            wp_enqueue_style( 'dokan-delivery-time-flatpickr-style' );
            wp_enqueue_style( 'dokan-delivery-time-vendor-style' );
        }

        if ( is_order_received_page() || is_view_order_page() ) {
            wp_enqueue_style( 'dokan-delivery-time-vendor-style' );
        }
    }

    /**
     * Gets vendor delivery time infos for customers
     *
     * @since 3.3.0
     *
     * @return array
     */
    public function get_vendor_delivery_time_info() {
        global $woocommerce;
        $items = $woocommerce->cart->get_cart();

        $vendor_infos = [];

        foreach ( $items as $item => $values ) {
            $vendor   = [];
            $_product = wc_get_product( $values['data']->get_id() );

            // Continue if the product is downloadable or virtual
            if ( $_product->is_downloadable() || $_product->is_virtual() ) {
                continue;
            }

            $_vendor    = dokan_get_vendor_by_product( $_product );
            $_vendor_id = (int) $_vendor->get_id();

            if ( isset( $vendor_infos[ $_vendor_id ] ) ) {
                continue;
            }

            $vendor_delivery_options = Helper::get_delivery_time_settings( $_vendor_id );

            $delivery_date_label = dokan_get_option( 'delivery_date_label', 'dokan_delivery_time', 'off' );
            $delivery_box_info   = dokan_get_option( 'delivery_box_info', 'dokan_delivery_time', 'off' );

            $is_delivery_time_active           = isset( $vendor_delivery_options['delivery_support'] ) && 'on' === $vendor_delivery_options['delivery_support'];
            $vendor['is_delivery_time_active'] = $is_delivery_time_active;

            $preorder_date = isset( $vendor_delivery_options['preorder_date'] ) ? $vendor_delivery_options['preorder_date'] : '';

            $delivery_box_info_message = str_replace( '%DAY%', $preorder_date, $delivery_box_info );

            $vendor_delivery_options['delivery_date_label']       = $delivery_date_label;
            $vendor_delivery_options['delivery_box_info_message'] = $delivery_box_info_message;

            $store_info = dokan_get_store_info( $_vendor_id );

            $current_date = dokan_current_datetime();
            $date         = strtolower( $current_date->format( 'Y-m-d' ) );
            $day          = strtolower( $current_date->format( 'l' ) );

            $vendor_order_per_slot              = (int) isset( $vendor_delivery_options['order_per_slot'][ $day ] ) ? $vendor_delivery_options['order_per_slot'][ $day ] : -1;
            $vendor_preorder_blocked_date_count = (int) ( isset( $vendor_delivery_options['preorder_date'] ) && $vendor_delivery_options['preorder_date'] > 0 ) ? $vendor_delivery_options['preorder_date'] : 0;
            $vendor_delivery_slots              = $is_delivery_time_active ? Helper::get_available_delivery_slots_by_date( $_vendor_id, $vendor_order_per_slot, $date ) : [];

            $vendor['store_name']              = $store_info['store_name'];
            $vendor['delivery_time_slots']     = $vendor_delivery_slots;
            $vendor['vendor_delivery_options'] = $vendor_delivery_options;
            $vendor['vendor_vacation_days']    = ( dokan_pro()->module->is_active( 'seller_vacation' ) && isset( $store_info['seller_vacation_schedules'] ) && isset( $store_info['setting_go_vacation'] ) && 'yes' === $store_info['setting_go_vacation'] ) ? $store_info['seller_vacation_schedules'] : [];

            $current_date                  = $current_date->modify( '+' . $vendor_preorder_blocked_date_count . ' day' );
            $vendor_preorder_block_date_to = strtolower( $current_date->format( 'Y-m-d' ) );

            $vendor['vendor_preorder_blocked_dates'] = [];

            if ( $vendor_preorder_blocked_date_count > 0 ) {
                $vendor['vendor_preorder_blocked_dates'] = [
                    [
                        'from' => $date,
                        'to'   => $vendor_preorder_block_date_to,
                    ],
                ];
            }

            $is_store_location_pickup_active    = StorePickupHelper::is_store_pickup_location_active( $_vendor_id );
            $vendor['is_store_location_active'] = $is_store_location_pickup_active;

            $vendor = apply_filters( 'dokan_vendor_delivery_time_info', $vendor, $_vendor );

            if ( ( isset( $vendor['is_store_location_active'] ) && $vendor['is_store_location_active'] ) || $is_delivery_time_active ) {
                $vendor_infos[ $_vendor_id ] = $vendor;
            }
        }

        return apply_filters( 'dokan_all_vendors_delivery_time_info', $vendor_infos );
    }

    /**
     * Saves delivery time args for single and sub orders
     *
     * @since 3.3.0
     *
     * @param /WC_Order $order
     * @param int $vendor_id
     *
     * @return void
     */
    public function save_delivery_time_args( $order, $vendor_id ) {
        /**
         * We're skipping nonce verification here. Because, if the customer creates account from checkout page at the time of ordering, the nonce changes. So, the nonce fails to verify.
         *
         * @see https://github.com/weDevsOfficial/dokan-pro/issues/2144
         */
        if ( ! isset( $_POST['woocommerce-process-checkout-nonce'] ) ) {
            return;
        }

        $order = $order instanceof \WC_Order ? $order : wc_get_order( $order );

        $data = isset( $_POST['vendor_delivery_time'] ) ? wc_clean( wp_unslash( $_POST['vendor_delivery_time'] ) ) : []; // phpcs:ignore

        $delivery_date          = isset( $data[ $vendor_id ]['delivery_date'] ) ? sanitize_text_field( $data[ $vendor_id ]['delivery_date'] ) : '';
        $delivery_time_slot     = isset( $data[ $vendor_id ]['delivery_time_slot'] ) ? sanitize_text_field( $data[ $vendor_id ]['delivery_time_slot'] ) : '';
        $selected_delivery_type = isset( $data[ $vendor_id ]['selected_delivery_type'] ) ? sanitize_text_field( $data[ $vendor_id ]['selected_delivery_type'] ) : '';

        $data = apply_filters(
            'dokan_delivery_time_checkout_args', [
                'order'                  => $order,
                'vendor_id'              => $vendor_id,
                'delivery_date'          => $delivery_date,
                'delivery_time_slot'     => $delivery_time_slot,
                'selected_delivery_type' => $selected_delivery_type,
            ], $data, $vendor_id
        );

        Helper::save_delivery_time_date_slot( $data );
    }

    /**
     * Renders delivery time details on wc order details page
     *
     * @since 3.3.0
     *
     * @param \WC_Order $order
     *
     * @return void
     */
    public function render_delivery_time_wc_order_details( $order ) {
        if ( ! $order ) {
            return;
        }

        // Getting delivery date meta
        $vendor_delivery_date = $order->get_meta( 'dokan_delivery_time_date' );

        if ( ! $vendor_delivery_date ) {
            return;
        }

        $current_date       = dokan_current_datetime();
        $current_date       = $current_date->modify( $vendor_delivery_date );
        $delivery_time_date = $current_date->format( 'F j, Y' );

        $delivery_time_slot = $order->get_meta( 'dokan_delivery_time_slot' );

        if ( ! $delivery_time_slot ) {
            return;
        }

        $store_location = $order->get_meta( 'dokan_store_pickup_location' );

        if ( ! empty( $store_location ) ) {
            return;
        }

        dokan_get_template_part(
            'delivery-time-order-details', '', [
                'order'                   => $order,
                'is_delivery_time'        => true,
                'delivery_time_date_slot' => [
                    'date' => $delivery_time_date,
                    'slot' => $delivery_time_slot,
                ],
            ]
        );
    }

    /**
     * Gets vendor delivery time slot from ajax request
     *
     * @since 3.3.0
     */
    public function get_vendor_delivery_time_slot() {
        if ( ! isset( $_POST['action'] ) || wc_clean( wp_unslash( $_POST['action'] ) ) !== 'dokan_get_delivery_time_slot' ) {
            wp_send_json_error( __( 'Something went wrong', 'dokan' ), '403' );
        }

        if ( ! isset( $_POST['nonce'] ) && ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'dokan_delivery_time' ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'dokan' ) );
        }

        $vendor_id = ! empty( $_POST['vendor_id'] ) ? sanitize_text_field( wp_unslash( $_POST['vendor_id'] ) ) : '';
        $date      = ! empty( $_POST['date'] ) ? sanitize_text_field( wp_unslash( $_POST['date'] ) ) : '';

        if ( empty( $vendor_id ) || empty( $date ) || ! strtotime( $date ) ) {
            wp_send_json_error( [ 'message' => __( 'No date or vendor id found.', 'dokan' ) ], 400 );
        }

        $delivery_time_slots = Helper::get_current_date_active_delivery_time_slots( $vendor_id, $date );
        wp_send_json_success( [ 'vendor_delivery_slots' => $delivery_time_slots ], 201 );
    }

    /**
     * Validates delivery time slot args from wc checkout
     *
     * @since 3.3.0
     *
     * @param array $wc_data
     * @param object $errors
     *
     * @return void
     */
    public function validate_delivery_time_slot_args( $wc_data, $errors ) {
        $is_time_selection_required = dokan_get_option( 'selection_required', 'dokan_delivery_time', 'on' );
        $posted_data                = isset( $_POST['vendor_delivery_time'] ) ? wp_unslash( wc_clean( $_POST['vendor_delivery_time'] ) ) : []; //phpcs:ignore

        foreach ( $posted_data as $data ) {
            if ( 'on' === $is_time_selection_required && ! empty( $data['selected_delivery_type'] ) && ( ! empty( $data['vendor_id'] ) && empty( $data['delivery_date'] ) ) ) {
                /* translators: %1$s selected delivery type name, %2$s: store name */
                $errors->add( 'dokan_delivery_date_required_error', sprintf( __( 'Please make sure you have selected the %1$s date for %2$s.', 'dokan' ), StorePickupHelper::get_formatted_delivery_type( $data['selected_delivery_type'] ), $data['store_name'] ) );
            }

            if ( ! empty( $data['selected_delivery_type'] ) && ( ! empty( $data['vendor_id'] ) && ! empty( $data['delivery_date'] ) ) && empty( $data['delivery_time_slot'] ) ) {
                /* translators: %s: store name */
                $errors->add( 'dokan_delivery_time_slot_error', sprintf( __( 'Please make sure you have selected the delivery time slot for %1$s.', 'dokan' ), $data['store_name'] ) );
            }

            if ( ( ! empty( $data['selected_delivery_type'] ) && 'store-pickup' === $data['selected_delivery_type'] ) && ( ! empty( $data['vendor_id'] ) && ! empty( $data['delivery_date'] ) ) && empty( $data['store_pickup_location'] ) ) {
                /* translators: %s: store name */
                $errors->add( 'dokan_store_pickup_location_error', sprintf( __( 'Please make sure you have selected the store pickup location for %1$s.', 'dokan' ), $data['store_name'] ) );
            }
        }
    }

    /**
     * Add i18n variable to frontend
     *
     * @since 3.3.0
     *
     * @param array $args
     *
     * @return array
     */
    public function add_i18n_date_format_localized_variable( $args ) {
        $args['i18n_date_format'] = wc_date_format();
        return $args;
    }
}
