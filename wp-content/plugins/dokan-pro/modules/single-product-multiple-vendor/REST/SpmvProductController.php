<?php

namespace WeDevs\DokanPro\Modules\SPMV\REST;

use WP_REST_Controller;
use Dokan_SPMV_Product_Duplicator;
use WP_REST_Request;
use WP_REST_Server;
use WeDevs\DokanPro\Modules\SPMV\Search\Dashboard;

/**
* SPMV Product Variation controller
*
* @since 3.7.19
*
* @package dokan
*/
class SpmvProductController extends WP_REST_Controller {

    /**
     * Endpoint namespace.
     *
     * @var string
     */
    protected $namespace = 'dokan/v1';

    /**
     * Route name
     *
     * @var string
     */
    protected $base = 'spmv-product';

    /**
     * Register the routes for products.
     *
     * @since 3.7.19
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace, '/' . $this->base . '/settings', [
                [
                    'methods'   => WP_REST_Server::READABLE,
                    'callback'  => [ $this, 'get_settings' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                ],
                'schema' => [ $this, 'get_settings_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/search', [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_spmv_products' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                    'args'                => $this->spmv_collection_params(),
                ],
                'schema' => [ $this, 'get_search_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/add-to-store', [
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'add_product_to_store' ],
                    'permission_callback' => [ $this, 'permissions_check' ],
                    'args'                => [
                        'product_id'           => [
                            'type'              => 'integer',
                            'description'       => __( 'Product id', 'dokan' ),
                            'sanitize_callback' => 'absint',
                            'required'          => true,
                        ],
                    ],
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );
    }

    /**
	 * Retrieves the query params for the collections.
	 *
	 * @since 3.7.19
	 *
	 * @return array Query parameters for the collection.
	 */
    public function spmv_collection_params() {
        $params = $this->get_collection_params();

        $params['orderby'] = [
            'description'       => __( 'Sort by products.', 'dokan' ),
            'type'              => 'string',
            'sanitize_callback' => 'sanitize_text_field',
            'validate_callback' => 'rest_validate_request_arg',
            'default'           => '',
        ];

        return $params;
    }

    /**
     * Checks the permission for product spmv.
     *
     * @since 3.7.19
     *
     * @return bool
     */
    public function permissions_check() {
        if ( ! dokan_pro()->module->is_active( 'spmv' ) || dokan_get_option( 'enable_pricing', 'dokan_spmv', 'on' ) === 'off' ) {
            return false;
        }

        return is_user_logged_in() && current_user_can( 'dokan_edit_product' );
    }

    /**
     * Returns spmv settings.
     *
     * @since 3.7.19
     *
     * @return WP_REST_Response
     */
    public function get_settings() {
        $settings = [
            'isEnabled' => 'on' === dokan_get_option( 'enable_pricing', 'dokan_spmv', 'off' ),
        ];

        return rest_ensure_response( $settings );
    }

    /**
     * Get spmv products.
     *
     * @since 3.7.19
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function get_spmv_products( WP_REST_Request $request ) {
        $page     = $request->get_param( 'page' );
        $per_page = $request->get_param( 'per_page' );
        $search   = $request->get_param( 'search' );
        $orderby  = $request->get_param( 'orderby' );
        $type     = 'all';

        /**
         * We are setting orderby param because,
         * in Dashboard::dokan_spmv_get_products there is a woocommerce method that formatting orderby parameter from super gobal $_GET variable,
         * and returnig the formated variable.
         */
        $_GET['orderby'] = $orderby;

        $spmv_products = Dashboard::dokan_spmv_get_products(
            $page,
            $search,
            '',
            $per_page,
            $type
        );

        $data = [];

        foreach ( $spmv_products->products as $product ) {
            $data[] = $this->prepare_item_for_response( $product, $request );
        }

        $max_num_pages = $spmv_products->max_num_pages;
        $total         = $spmv_products->total;

        $response = rest_ensure_response( $data );
        return $this->format_collection_response( $response, $request, $total, $max_num_pages, $page );
    }

    /**
     * Format item's collection for response
     *
     * @param  WP_REST_Response $response
     * @param  WP_REST_Request  $request
     * @param  int              $total_items
     * @param  int              $page
     *
     * @return WP_REST_Response
     */
    public function format_collection_response( $response, $request, $total_items, $max_pages, $page ) {
        $response->header( 'X-WP-Total', $total_items );

        $response->header( 'X-WP-TotalPages', $max_pages );
        $base = add_query_arg( $request->get_query_params(), rest_url( sprintf( '/%s/%s', $this->namespace, $this->base ) ) );

        if ( $page > 1 ) {
            $prev_page = $page - 1;
            if ( $prev_page > $max_pages ) {
                $prev_page = $max_pages;
            }
            $prev_link = add_query_arg( 'page', $prev_page, $base );
            $response->link_header( 'prev', $prev_link );
        }
        if ( $max_pages > $page ) {
            $next_page = $page + 1;
            $next_link = add_query_arg( 'page', $next_page, $base );
            $response->link_header( 'next', $next_link );
        }

        return $response;
    }

    /**
     * Prepare spmv data for response
     *
     * @since 3.7.19
     *
     * @param WC_Product      $item
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response
     */
    public function prepare_item_for_response( $item, $request ) {
        $duplicator = new Dokan_SPMV_Product_Duplicator();
        $vendor     = dokan_get_vendor_by_product( $item );

        $product = [];
        $product['average_rating'] = $item->get_average_rating();
        $product['title']          = $item->get_title();
        $product['image']          = $item->get_image( 'thumbnail' );
        $product['permalink']      = $item->get_permalink();
        $product['review_count']   = $item->get_review_count();
        $product['type']           = $item->get_type();
        $product['id']             = $item->get_id();
        $product['price']          = $item->get_price();
        $product['price_html']     = wp_kses_post( $item->get_price_html() );;
        $product['category_list']  = wp_kses_post( wc_get_product_category_list( $item->get_id() ) );
        $product['vendor_name']    = $vendor->get_name();

        if ( dokan_get_current_user_id() === $vendor->get_id() ) {
            $product['action'] = 'self-product';
        } elseif ( $duplicator->check_already_cloned( $item->get_id(), dokan_get_current_user_id() ) ) {
            $product['action'] = 'already-cloned';
        } else {
            $product['action'] = 'add-to-store';
        }

        return apply_filters( 'dokan_rest_prepare_spmv_product_object', $product, $item, $request );
    }

    /**
     * Add product to current vendor store.
     *
     * @since 3.7.19
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function add_product_to_store( WP_REST_Request $request ) {
        $product_id         = $request->get_param( 'product_id' );
        $product_duplicator = new Dokan_SPMV_Product_Duplicator();

        $vendor_id = dokan_get_current_user_id();

        if ( ! dokan_spmv_can_vendor_create_new_product( $vendor_id ) ) {
            return new WP_Error( 'spmv_permission_denied', __( "You don't have permission to add this product.", "dokan" ) );
        }

        if ( $product_duplicator->check_already_cloned( $product_id, $vendor_id ) ) {
            return new WP_Error( 'spmv_product_added_to_store', __( "You have already cloned this product.", "dokan" ) );
        }

        $cloned_product = $product_duplicator->clone_product( $product_id, $vendor_id );

        if ( is_wp_error( $cloned_product ) ) {
            return $cloned_product;
        }

        return rest_ensure_response( [ 'status' => true, 'success_message' => __( 'Successfully added product to your store', 'dokan-pro' ) ] );
    }

    /**
     * Get spmv settings schema, conforming to JSON Schema
     *
     * @since 3.7.19
     *
     * @return array
     */
    public function get_settings_item_schema() {
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => __( 'Spmv settings Item Schema.', 'dokan' ),
            'type'       => 'object',
            'properties' => [
                'isEnabled' => [
                    'description'       => __( 'Is SPMV module is enabled.', 'dokan' ),
                    'type'              => 'boolean',
                    'context'           => [ 'view' ],
                    'required'          => false,
                    'sanitize_callback' => 'rest_sanitize_boolean',
                    'validate_callback' => 'rest_validate_request_arg',
                ],
            ],
        ];

        return $this->add_additional_fields_schema( $schema );
    }

    /**
     * Get spmv products item schema, conforming to JSON Schema
     *
     * @since 3.7.19
     *
     * @return array
     */
    public function get_search_item_schema() {
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => __( 'Search products item Schema.', 'dokan' ),
            'type'       => 'object',
            'properties' => [
                'max_num_pages' => [
                    'description'       => __( 'Maximum number of pages', 'dokan' ),
                    'type'              => 'integer',
                    'context'           => [ 'view', 'edit' ],
                    'sanitize_callback' => 'absint',
                    'validate_callback' => 'rest_validate_request_arg',
                ],
                'total' => [
                    'description'       => __( 'Total products items', 'dokan' ),
                    'type'              => 'integer',
                    'context'           => [ 'view', 'edit' ],
                    'sanitize_callback' => 'absint',
                    'validate_callback' => 'rest_validate_request_arg',
                ],
                'products' => [
                    'description' => __( 'status of the corresponding operation', 'dokan' ),
                    'type'        => 'array',
                    'context'     => [ 'view' ],
                    'items'       => [
                        'type'       => 'object',
                        'properties' => [
                            'id'                    => [
                                'description'       => __( 'Unique identifier for the object', 'dokan' ),
                                'type'              => 'integer',
                                'context'           => [ 'view', 'edit' ],
                                'readonly'          => true,
                                'validate_callback' => 'rest_validate_request_arg',
                                'sanitize_callback' => 'absint',
                            ],
                            'action'                => [
                                'description'       => __( 'Status of product, means if it is already added or can be added.', 'dokan' ),
                                'type'              => 'string',
                                'default'           => '',
                                'context'           => [ 'view' ],
                                'readonly'          => true,
                                'sanitize_callback' => 'sanitize_text_field',
                                'validate_callback' => 'rest_validate_request_arg',
                            ],
                            'average_rating'        => [
                                'description'       => __( 'Average rating of the product', 'dokan' ),
                                'type'              => 'string',
                                'default'           => '',
                                'context'           => [ 'view' ],
                                'readonly'          => true,
                                'sanitize_callback' => 'sanitize_text_field',
                                'validate_callback' => 'rest_validate_request_arg',
                            ],
                            'category_list'         => [
                                'description'       => __( 'Product category list', 'dokan' ),
                                'type'              => 'string',
                                'default'           => '',
                                'context'           => [ 'view' ],
                                'readonly'          => true,
                                'sanitize_callback' => 'sanitize_textarea_field',
                                'validate_callback' => 'rest_validate_request_arg',
                            ],
                            'image'                 => [
                                'description'       => __( 'Product image', 'dokan' ),
                                'type'              => 'string',
                                'default'           => '',
                                'context'           => [ 'view' ],
                                'readonly'          => true,
                                'sanitize_callback' => 'sanitize_textarea_field',
                                'validate_callback' => 'rest_validate_request_arg',
                            ],
                            'permalink'                 => [
                                'description'       => __( 'Product link', 'dokan' ),
                                'type'              => 'string',
                                'default'           => '',
                                'context'           => [ 'view' ],
                                'readonly'          => true,
                                'sanitize_callback' => 'sanitize_textarea_field',
                                'validate_callback' => 'rest_validate_request_arg',
                            ],
                            'price'                 => [
                                'description'       => __( 'Product link', 'dokan' ),
                                'type'              => 'string',
                                'default'           => '',
                                'context'           => [ 'view' ],
                                'readonly'          => true,
                                'sanitize_callback' => 'sanitize_text_field',
                                'validate_callback' => 'rest_validate_request_arg',
                            ],
                            'price_html'                 => [
                                'description'       => __( 'Product link', 'dokan' ),
                                'type'              => 'string',
                                'default'           => '',
                                'context'           => [ 'view' ],
                                'readonly'          => true,
                                'sanitize_callback' => 'sanitize_textarea_field',
                                'validate_callback' => 'rest_validate_request_arg',
                            ],
                            'review_count'          => [
                                'description'       => __( 'Product review count', 'dokan' ),
                                'type'              => 'integer',
                                'context'           => [ 'view', 'edit' ],
                                'readonly'          => true,
                                'validate_callback' => 'rest_validate_request_arg',
                                'sanitize_callback' => 'absint',
                            ],
                            'title'                 => [
                                'description'       => __( 'Product name', 'dokan' ),
                                'type'              => 'string',
                                'default'           => '',
                                'context'           => [ 'view' ],
                                'readonly'          => true,
                                'sanitize_callback' => 'sanitize_text_field',
                                'validate_callback' => 'rest_validate_request_arg',
                            ],
                            'type'                  => [
                                'description'       => __( 'Product type', 'dokan' ),
                                'type'              => 'string',
                                'default'           => '',
                                'context'           => [ 'view' ],
                                'readonly'          => true,
                                'sanitize_callback' => 'sanitize_text_field',
                                'validate_callback' => 'rest_validate_request_arg',
                            ],
                            'vendor_name'           => [
                                'description'       => __( 'Product vendor name', 'dokan' ),
                                'type'              => 'string',
                                'default'           => '',
                                'context'           => [ 'view' ],
                                'readonly'          => true,
                                'sanitize_callback' => 'sanitize_text_field',
                                'validate_callback' => 'rest_validate_request_arg',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        return $this->add_additional_fields_schema( $schema );
    }
}
