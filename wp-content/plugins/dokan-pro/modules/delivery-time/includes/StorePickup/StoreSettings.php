<?php


namespace WeDevs\DokanPro\Modules\DeliveryTime\StorePickup;

/**
 * Class StoreSettings
 *
 * @package WeDevs\DokanPro\Modules\DeliveryTime\StorePickup
 */
class StoreSettings {

    /**
     * StoreSettings constructor
     *
     * @since 3.3.7
     */
    public function __construct() {
        add_action( 'dokan_settings_after_store_phone', [ $this, 'render_store_location_template' ], 9, 2 );
        add_action( 'wp_enqueue_scripts', [ $this, 'load_scripts' ], 20 );
        add_action( 'wp_ajax_dokan_store_location_save_item', [ $this, 'store_location_save_item' ] );
        add_action( 'wp_ajax_dokan_store_location_delete_item', [ $this, 'store_location_delete_item' ] );
        add_action( 'dokan_store_profile_saved', [ $this, 'save_enable_vendor_store_pickup_location_args' ], 20, 1 );

        add_filter( 'dokan_store_profile_settings_args', [ $this, 'restore_vendor_default_address' ], 20, 2 );
    }

    /**
     * Renders add store location pickup template
     *
     * @since 3.3.7
     *
     * @param int $current_user
     * @param array $profile_info
     *
     * @return void
     */
    public function render_store_location_template( $current_user, $profile_info ) {
        $has_default_address = Helper::vendor_has_default_address( $profile_info );

        if ( ! $has_default_address ) {
            dokan_get_template_part(
                'store-pickup/seller-address-fields', '', [
                    'is_delivery_time'    => true,
                    'profile_info'        => $profile_info,
                    'vendor_id'           => $current_user,
                ]
            );
            return;
        }

        $is_address_verified = false;

        // Checking if vendor verification module is active and the vendor has already verified the address
        if ( dokan_pro()->module->is_active( 'vendor_verification' ) && isset( $profile_info['dokan_verification']['info']['store_address']['v_status'] ) && 'approved' === $profile_info['dokan_verification']['info']['store_address']['v_status'] ) {
            $is_address_verified = true;
        }

        dokan_get_template_part(
            'store-pickup/add-store-pickup', '', [
                'is_delivery_time'    => true,
                'profile_info'        => $profile_info,
                'vendor_id'           => $current_user,
                'is_address_verified' => $is_address_verified,
            ]
        );
    }

    /**
     * Loads scripts
     *
     * @since 3.3.7
     *
     * @return void
     */
    public function load_scripts() {
        global $wp;

        if ( isset( $wp->query_vars['settings'] ) && 'store' === (string) $wp->query_vars['settings'] ) {
            wp_enqueue_script( 'dokan-store-location-pickup-script' );
        }
    }

    /**
     * AJAX request to save location item
     *
     * @since 3.3.7
     *
     * @return void
     */
    public function store_location_save_item() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dokan_reviews' ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'dokan' ), 403 );
        }

        if ( ! isset( $_POST['action'] ) || wc_clean( wp_unslash( $_POST['action'] ) ) !== 'dokan_store_location_save_item' ) {
            wp_send_json_error( __( 'Something went wrong', 'dokan' ), 403 );
        }

        if ( ! isset( $_POST['data'] ) ) {
            wp_send_json_error( __( 'Please provide data', 'dokan' ), 403 );
        }

        $vendor_id = dokan_get_current_user_id();

        if ( ! dokan_is_user_seller( $vendor_id ) ) {
            wp_send_json_error( __( 'Only seller can do this action', 'dokan' ), 403 );
        }

        $index = isset( $_POST['index'] ) ? wc_clean( wp_unslash( $_POST['index'] ) ) : '';

        $profile_info = dokan_get_store_info( $vendor_id );

        // Preventing saving of default location if vendor has already verified the default address
        if ( '0' === $index && dokan_pro()->module->is_active( 'vendor_verification' ) && isset( $profile_info['dokan_verification']['info']['store_address']['v_status'] ) && 'approved' === $profile_info['dokan_verification']['info']['store_address']['v_status'] ) {
            wp_send_json_error( [ 'message' => __( 'Default location can not be edited as it has been already verified!', 'dokan' ) ], 403 );
        }

        $location = [
            'location_name' => isset( $_POST['data']['location_name'] ) ? wc_clean( wp_unslash( $_POST['data']['location_name'] ) ) : '',
            'street_1'      => isset( $_POST['data']['street_1'] ) ? wc_clean( wp_unslash( $_POST['data']['street_1'] ) ) : '',
            'street_2'      => isset( $_POST['data']['street_2'] ) ? wc_clean( wp_unslash( $_POST['data']['street_2'] ) ) : '',
            'city'          => isset( $_POST['data']['city'] ) ? wc_clean( wp_unslash( $_POST['data']['city'] ) ) : '',
            'zip'           => isset( $_POST['data']['zip'] ) ? wc_clean( wp_unslash( $_POST['data']['zip'] ) ) : '',
            'state'         => isset( $_POST['data']['state'] ) ? wc_clean( wp_unslash( $_POST['data']['state'] ) ) : '',
            'country'       => isset( $_POST['data']['country'] ) ? wc_clean( wp_unslash( $_POST['data']['country'] ) ) : '',
        ];

        $vendor_settings        = dokan_get_store_info( $vendor_id );
        $vendor_store_locations = Helper::get_vendor_store_pickup_locations( $vendor_id, true );

        if ( '0' === $index ) {
            // Default Location as passed index is 0
            $vendor_settings['address'] = $location;
            $vendor_settings['vendor_store_location_pickup']['default_location_name'] = $location['location_name'];
        } elseif ( empty( $index ) ) {
            // New Location
            $vendor_store_locations[] = $location;
        } elseif ( isset( $vendor_store_locations[ absint( $index ) - 1 ] ) ) {
            // Update Location
            $vendor_store_locations[ absint( $index ) - 1 ] = $location;
        }

        $vendor_settings['store_locations'] = array_values( $vendor_store_locations );

        $vendor_settings['vendor_store_location_pickup']['multiple_store_location'] = isset( $_POST['data']['is_multiple_enable'] ) ? wc_clean( wp_unslash( $_POST['data']['is_multiple_enable'] ) ) : 'no';

        update_user_meta( $vendor_id, 'dokan_profile_settings', $vendor_settings );

        wp_send_json_success( [ 'success' => true ], 201 );
    }

    /**
     * AJAX request to delete location item
     *
     * @since DOKAN_SINCE_PRO
     *
     * @return void
     */
    public function store_location_delete_item() {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dokan_reviews' ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'dokan' ), 403 );
        }

        if ( ! isset( $_POST['action'] ) || 'dokan_store_location_delete_item' !== wc_clean( wp_unslash( $_POST['action'] ) ) ) {
            wp_send_json_error( __( 'Something went wrong', 'dokan' ), 403 );
        }

        if ( ! isset( $_POST['index'] ) ) {
            wp_send_json_error( __( 'Please provide data', 'dokan' ), 403 );
        }

        $vendor_id = dokan_get_current_user_id();

        if ( ! dokan_is_user_seller( $vendor_id ) ) {
            wp_send_json_error( __( 'Only seller can do this action', 'dokan' ), 403 );
        }

        $index           = wc_clean( wp_unslash( $_POST['index'] ) );
        $vendor_settings = dokan_get_store_info( $vendor_id );

        if ( '0' === $index ) {
            wp_send_json_error( __( 'Default location can not be deleted', 'dokan' ), 403 );
        }

        $vendor_store_locations = Helper::get_vendor_store_pickup_locations( $vendor_id, true );

        if ( ! isset( $vendor_store_locations[ absint( $index ) - 1 ] ) ) {
            wp_send_json_error( __( 'No location found', 'dokan' ), 404 );
        }

        unset( $vendor_store_locations[ absint( $index ) - 1 ] );

        $vendor_settings['store_locations'] = array_values( $vendor_store_locations );

        update_user_meta( $vendor_id, 'dokan_profile_settings', $vendor_settings );

        wp_send_json_success( [ 'success' => true ], 200 );
    }

    /**
     * Restores vendor default address
     *
     * @since 3.3.7
     *
     * @param array $dokan_settings
     * @param int $store_id
     *
     * @return array
     */
    public function restore_vendor_default_address( $dokan_settings, $store_id ) {
        $vendor_settings     = dokan_get_store_info( $store_id );
        $has_default_address = Helper::vendor_has_default_address( $vendor_settings );

        if ( ! $has_default_address ) {
            return $dokan_settings;
        }

        // If no address has set for store yet, return the default settings.
        if ( empty( $vendor_settings['address'] ) ) {
            return $dokan_settings;
        }

        $dokan_settings['address']['location_name'] = isset( $vendor_settings['address']['location_name'] ) ? $vendor_settings['address']['location_name'] : '';
        $dokan_settings['address']['street_1']      = isset( $vendor_settings['address']['street_1'] ) ? $vendor_settings['address']['street_1'] : '';
        $dokan_settings['address']['street_2']      = isset( $vendor_settings['address']['street_2'] ) ? $vendor_settings['address']['street_2'] : '';
        $dokan_settings['address']['city']          = isset( $vendor_settings['address']['city'] ) ? $vendor_settings['address']['city'] : '';
        $dokan_settings['address']['zip']           = isset( $vendor_settings['address']['zip'] ) ? $vendor_settings['address']['zip'] : '';
        $dokan_settings['address']['country']       = isset( $vendor_settings['address']['country'] ) ? $vendor_settings['address']['country'] : '';
        $dokan_settings['address']['state']         = isset( $vendor_settings['address']['state'] ) ? $vendor_settings['address']['state'] : '';

        return $dokan_settings;
    }

    /**
     * Saves enable setting for vendor store pickup location
     *
     * @since 3.3.7
     *
     * @param int $vendor_id
     * @param array $profile_info
     *
     * @return void
     */
    public function save_enable_vendor_store_pickup_location_args( $vendor_id ) {
        if ( ! isset( $_POST['multiple-store-location'] ) ) { // phpcs:ignore
            return;
        }

        $profile_info = dokan_get_store_info( $vendor_id );

        $multiple_store_location = wc_clean( wp_unslash( $_POST['multiple-store-location'] ) ); // phpcs:ignore

        $profile_info['vendor_store_location_pickup']['multiple_store_location'] = $multiple_store_location;

        update_user_meta( $vendor_id, 'dokan_profile_settings', $profile_info );
    }
}
