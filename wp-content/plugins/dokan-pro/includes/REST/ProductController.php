<?php

namespace WeDevs\DokanPro\REST;

use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WeDevs\Dokan\REST\ProductControllerV2;

/**
* Product Variation controller
*
* @since 3.7.14
*
* @package dokan
*/
class ProductController extends ProductControllerV2 {

    /**
     * Register the routes for products.
     *
     * @since 3.7.14
     *
     * @return void
     */
    public function register_routes() {
        parent::register_routes();

        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/duplicate', [
                'args' => [
                    'id' => [
                        'description' => __( 'Unique identifier for the object.', 'dokan' ),
                        'type'        => 'integer',
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'duplicate_product' ],
                    'permission_callback' => [ $this, 'duplicate_product_permissions_check' ],
                    'args'                => [],
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );
    }

    /**
     * Checks the permission for product duplication.
     *
     * @since 3.7.14
     *
     * @return bool
     */
    public function duplicate_product_permissions_check() {
        if ( dokan_get_option( 'vendor_duplicate_product', 'dokan_selling', 'on' ) === 'off' ) {
            return false;
        }

        if ( ! dokan_is_user_seller( dokan_get_current_user_id() ) ) {
            return false;
        }

        if ( ! apply_filters( 'dokan_vendor_can_duplicate_product', true ) ) {
            return false;
        }

        return true;
    }

    /**
     * Create a duplicate copy of a product.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function duplicate_product( \WP_REST_Request $request ) {
        $product_id = $request['id'];
        $duplicate_product = dokan_pro()->products->duplicate_product( $product_id );

        if ( is_wp_error( $duplicate_product ) ) {
            return rest_ensure_response( $duplicate_product );
        }

        return $this->prepare_data_for_response( $duplicate_product, $request );
    }
}
