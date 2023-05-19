<?php

namespace WeDevs\DokanPro\Modules\SellerBadge;

use WeDevs\Dokan\Vendor\Vendor;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

/**
 * Class for Hooks integration.
 *
 * @since 3.7.14
 */
class Hooks {

    /**
     * Class constructor
     *
     * @since 3.7.14
     *
     * @return void
     */
    public function __construct() {
        // store custom fields data during add/edit vendors
        add_action( 'dokan_before_create_vendor', [ $this, 'update_vendor_custom_fields' ], 10, 2 );
        add_action( 'dokan_before_update_vendor', [ $this, 'update_vendor_custom_fields' ], 10, 2 );

        // populated custom fields data during creating vendor instance
        add_filter( 'dokan_vendor_shop_data', [ $this, 'populate_shop_data' ], 10, 2 );
        add_filter( 'dokan_vendor_to_array', [ $this, 'populate_shop_data' ], 10, 2 );

        add_filter( 'dokan_rest_api_store_collection_params', [ $this, 'add_params_to_store_collection' ] );
        add_filter( 'dokan_rest_get_stores_args', [ $this, 'rest_get_stores_args' ], 10, 2 );
    }

    /**
     * Add params to store collection.
     *
     * @since 3.7.14
     *
     * @param array $args
     *
     * @return array
     */
    public function add_params_to_store_collection( $args ) {
        $args['badge_id'] = array(
            'description'       => __( 'Badge id.', 'dokan' ),
            'type'              => 'integer',
            'require'           => false,
            'sanitize_callback' => 'absint',
            'validate_callback' => 'rest_validate_request_arg',
        );

        return $args;
    }

    /**
     * Get dokan stores args
     *
     * @since 3.7.14
     *
     * @param array $args
     * @param \WP_REST_Request $request
     *
     *
     * @return array
     */
    public function rest_get_stores_args( $args, $request ) {
        if ( ! empty( $request ['badge_id'] ) ) {
            $manager = new Manager();
            $vendor_ids = $manager->get_acquired_vendors_by_badge_id( $request['badge_id'], $request['per_page'] );
            if ( is_wp_error( $vendor_ids ) || empty( $vendor_ids ) ) {
                $args['include'] = [0];
            } else {
                $args['include'] = $vendor_ids;
            }
        }

        return $args;
    }

    /**
     * Store custom fields data during add/edit vendors
     *
     * @since 3.7.14
     *
     * @param array $data
     *
     * @param int   $vendor_id
     *
     * @return void
     */
    public function update_vendor_custom_fields( $vendor_id, $data ) {
        // get vendor object
        $vendor         = new Vendor( $vendor_id );
        $sale_only_here = isset( $data['sale_only_here'] ) ? wc_string_to_bool( $data['sale_only_here'] ) : false;
        $vendor->update_meta( 'sale_only_here', $sale_only_here );
    }

    /**
     * Populated custom fields data during creating vendor instance
     *
     * @since 3.7.14
     *
     * @param Vendor $vendor
     *
     * @param array  $shop_info
     *
     * @return array
     */
    public function populate_shop_data( $shop_info, $vendor ) {
        $shop_info['sale_only_here'] = wc_string_to_bool( $vendor->get_meta( 'sale_only_here', true ) );

        return $shop_info;
    }

}
