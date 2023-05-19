<?php

namespace WeDevs\DokanPro\REST;

use Exception;
use WP_Error;
use WP_REST_Controller;
use WP_REST_Server;
use WeDevs\DokanPro\Refund\Refund;
use WeDevs\DokanPro\Refund\Sanitizer;
use WeDevs\DokanPro\Refund\Validator;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\Dokan\Traits\RESTResponseError;

class RefundController extends WP_REST_Controller {

    use RESTResponseError;

    /**
     * API namespace
     *
     * @since 3.0.0
     *
     * @var string
     */
    protected $namespace = 'dokan/v1';

    /**
     * API base
     *
     * @since 3.0.0
     *
     * @var string
     */
    protected $rest_base = 'refunds';

    /**
     * Register REST routes
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->rest_base, [
            [
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => [ $this, 'get_items' ],
                'permission_callback' => [ $this, 'get_items_permissions_check' ],
                'args'                => array_merge( $this->get_collection_params(), [
                    'status' => [
                        'type'        => 'string',
                        'description' => __( 'Refund status', 'dokan' ),
                        'enum'        => ! empty( dokan_pro()->refund ) ? array_keys( dokan_pro()->refund->get_statuses() ) : [],
                        'required'    => false,
                    ],
                    'search' => [
                        'type'        => 'string',
                        'description' => __( 'Search by order id OR shop name', 'dokan' ),
                        'required'    => false,
                    ],
                    'orderby' => [
                        'type'        => 'string',
                        'description' => __( 'Order By', 'dokan' ),
                        'required'    => false,
                        'default'     => 'id',
                    ],
                    'order'   => [
                        'type'        => 'string',
                        'description' => __( 'Order', 'dokan' ),
                        'required'    => false,
                        'default'     => 'asc',
                    ],
                ] ),
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/approve', [
            'args' => [
                'id' => [
                    'description' => __( 'Unique identifier for the object.', 'dokan' ),
                    'type'        => 'integer',
                    'validate_callback' => [ Validator::class, 'validate_id' ],
                ],
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'approve_item' ],
                'permission_callback' => [ $this, 'update_item_permissions_check' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)/cancel', [
            'args' => [
                'id' => [
                    'description'       => __( 'Unique identifier for the object.', 'dokan' ),
                    'type'              => 'integer',
                    'validate_callback' => [ Validator::class, 'validate_id' ],
                ],
            ],
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'cancel_item' ],
                'permission_callback' => [ $this, 'update_item_permissions_check' ],
            ],
        ] );

        register_rest_route( $this->namespace, '/' . $this->rest_base . '/(?P<id>[\d]+)', [
            'args' => [
                'id' => [
                    'description'       => __( 'Unique identifier for the object.', 'dokan' ),
                    'type'              => 'integer',
                    'validate_callback' => [ Validator::class, 'validate_id' ],
                ],
            ],
            [
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => [ $this, 'delete_item' ],
                'permission_callback' => [ $this, 'delete_item_permissions_check' ],
            ],
        ] );

        $batch_items_schema = $this->get_public_batch_schema();
        register_rest_route( $this->namespace, '/' . $this->rest_base . '/batch', [
            [
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => [ $this, 'batch_items' ],
                'permission_callback' => [ $this, 'batch_items_permissions_check' ],
                'args'                => $batch_items_schema['properties'],
            ],
            'schema' => [ $this, 'get_public_batch_schema' ],
        ] );
    }

    /**
     * Get refunds permission callback
     *
     * @since 3.0.0
     *
     * @param \WP_REST_Request $request
     *
     * @return bool
     */
    public function get_items_permissions_check( $request ) {
        return current_user_can( 'manage_woocommerce' ) || current_user_can( 'dokandar' );
    }

    /**
     * Update refund permission callback
     *
     * @since 3.0.0
     *
     * @param \WP_REST_Request $request
     *
     * @return bool
     */
    public function update_item_permissions_check( $request ) {
        return current_user_can( 'manage_woocommerce' );
    }

    /**
     * Delete refund permission callback
     *
     * @since 3.0.0
     *
     * @param \WP_REST_Request $request
     *
     * @return bool
     */
    public function delete_item_permissions_check( $request ) {
        return current_user_can( 'manage_woocommerce' );
    }

    /**
     * Batch refunds permission callback
     *
     * @since 3.0.0
     *
     * @param \WP_REST_Request $request
     *
     * @return bool
     */
    public function batch_items_permissions_check() {
        return current_user_can( 'manage_woocommerce' );
    }

    /**
     * Get refunds
     *
     * @since 3.0.0
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function get_items( $request ) {
        $args = [
            'paginate' => true,
            'page'     => $request['page'],
            'limit'    => $request['per_page'],
            'orderby'  => $request['orderby'],
            'order'    => $request['order'],
        ];

        if ( isset( $request['status'] ) ) {
            $args['status'] = dokan_pro()->refund->get_status_code( $request['status'] );
        }

        if ( ! empty( $request['orderby'] ) && $request['orderby'] === 'vendor' ) {
            $args['orderby'] = 'seller_id';
        }

        if ( ! empty( $request['search'] ) ) {
            $args['search'] = $request['search'];
        }

        $seller_id = null;

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            // Vendors can only see their own requests
            $seller_id = dokan_get_current_user_id();

            if ( empty( $seller_id ) ) {
                return new WP_Error( 'dokan_rest_refund_error_user', __( 'No vendor found', 'dokan' ), [ 'status' => 404 ] );
            }
        } else if ( isset( $request['seller_id'] ) ) {
            // Allow manager to filter request with user or vendor id
            $seller_id = $request['seller_id'];
        }

        if ( $seller_id ) {
            $args['seller_id'] = $seller_id;
        }

        if ( isset( $request['ids'] ) ) {
            $args['ids'] = $request['ids'];
        }

        $refunds = dokan_pro()->refund->all( $args );

        $data = [];
        foreach ( $refunds->refunds as $refund ) {
            $item   = $this->prepare_item_for_response( $refund, $request );
            $data[] = $this->prepare_response_for_collection( $item );
        }

        $response            = rest_ensure_response( $data );
        $refund_count        = dokan_get_refund_count( $seller_id );
        $api_refund_disabled = 'on' !== dokan_get_option( 'automatic_process_api_refund', 'dokan_selling', 'off' );

        $response->header( 'X-Status-Pending', $refund_count['pending'] );
        $response->header( 'X-Status-Completed', $refund_count['completed'] );
        $response->header( 'X-Status-Cancelled', $refund_count['cancelled'] );
        $response->header( 'X-API-Refund-Disabled', $api_refund_disabled );

        $response = $this->format_collection_response( $response, $request, $refunds->total );
        return $response;
    }

    /**
     * Approve a refund request
     *
     * @since 3.0.0
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function approve_item( $request ) {
        try {
            $refund = dokan_pro()->refund->get( $request['id'] );

            if ( 'pending' !== $refund->get_status_name() ) {
                throw new DokanException(
                    'dokan_pro_rest_refund_error_approve',
                    __( 'The refund request does not have pending status', 'dokan' )
                );
            }

            $refund->approve();

            $response = $this->prepare_item_for_response( $refund, $request );

            return rest_ensure_response( $response );
        } catch ( Exception $e ) {
            return $this->send_response_error( $e );
        }
    }

    /**
     * Cancel a refund request
     *
     * @since 3.0.0
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function cancel_item( $request ) {
        try {
            $refund = dokan_pro()->refund->get( $request['id'] );

            if ( 'pending' !== $refund->get_status_name() ) {
                throw new DokanException(
                    'dokan_pro_rest_refund_error_cancel',
                    __( 'The refund request does not have pending status', 'dokan' )
                );
            }

            $refund->cancel();

            $response = $this->prepare_item_for_response( $refund, $request );

            return rest_ensure_response( $response );
        } catch ( Exception $e ) {
            return $this->send_response_error( $e );
        }
    }

    /**
     * Delete a refund
     *
     * @since 3.0.0
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response|\WP_Error
     */
    public function delete_item( $request ) {
        try {
            $refund = dokan_pro()->refund->get( $request['id'] );

            $refund = $refund->delete();

            if ( is_wp_error( $refund ) ) {
                throw new DokanException( $refund );
            }

            $response = $this->prepare_item_for_response( $refund, $request );

            return rest_ensure_response( $response );

        } catch ( Exception $e ) {
            return $this->send_response_error( $e );
        }
    }

    /**
     * Approve, Pending and cancel bulk action
     * JSON data format for sending to API
     *     {
     *         "approved" : [
     *             "1", "9", "7"
     *         ],
     *         "pending" : [
     *             "2"
     *         ],
     *         "delete" : [
     *             "4"
     *         ],
     *         "cancelled" : [
     *             "5"
     *         ]
     *     }
     *
     * @since 2.8.0
     *
     * @return WP_Error|\WP_HTTP_Response|\WP_REST_Response
     */
    public function batch_items( $request ) {
        $success = [];
        $failed  = [];

        if ( ! empty( $request['completed'] ) && is_array( $request['completed'] ) ) {
            foreach ( $request['completed'] as $id ) {
                $refund = dokan_pro()->refund->get( $id );

                if ( ! $refund ) {
                    $failed['completed'][] = $id;
                } else {
                    $validate_request = dokan_pro()->refund->is_approvable( $refund->get_order_id() );

                    if ( ! $validate_request ) {
                        $failed['completed'][] = $id;
                    } else {
                        $refund = $refund->approve();

                        if ( is_wp_error( $refund ) ) {
                            $failed['completed'][] = $id;
                        } else {
                            $success['completed'][] = $id;
                        }
                    }
                }
            }
        }

        if ( ! empty( $request['cancelled'] ) && is_array( $request['cancelled'] ) ) {
            foreach ( $request['cancelled'] as $id ) {
                $refund = dokan_pro()->refund->get( $id );

                if ( ! $refund ) {
                    $failed['cancelled'][] = $id;
                } else {
                    $validate_request = dokan_pro()->refund->has_pending_request( $refund->get_order_id() );

                    if ( ! $validate_request ) {
                        $failed['cancelled'][] = $id;
                    } else {
                        $refund = $refund->cancel();

                        if ( is_wp_error( $refund ) ) {
                            $failed['cancelled'][] = $id;
                        } else {
                            $success['cancelled'][] = $id;
                        }
                    }
                }
            }
        }

        if ( ! empty( $request['delete'] ) && is_array( $request['delete'] ) ) {
            foreach ( $request['delete'] as $id ) {
                $refund = dokan_pro()->refund->get( $id );

                if ( ! $refund ) {
                    $failed['delete'][] = $id;
                } else {
                    if ( ! current_user_can( 'manage_options' ) && dokan_get_current_user_id() !== (int) $refund->get_seller_id() ) {
                        // current seller isn't owner of this refund
                        $failed['delete'][] = $id;
                        continue;
                    }

                    $refund = $refund->delete();

                    if ( is_wp_error( $refund ) ) {
                        $failed['delete'][] = $id;
                    } else {
                        $success['delete'][] = $id;
                    }
                }
            }
        }

        return rest_ensure_response( [
            'success' => $success,
            'failed'  => $failed,
        ] );
    }

    /**
     * Refund REST request item schema
     *
     * @since 3.0.0
     *
     * @return array
     */
    public function get_item_schema() {
        $refund = new Refund();

        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'Refund',
            'type'       => 'object',
            'properties' => [
                'id' => [
                    'description' => __( 'Unique identifier for the object.', 'dokan' ),
                    'type'        => 'integer',
                    'context'     => [ 'view' ],
                    'readonly'    => true,
                ],
                'order_id' => [
                    'description' => __( 'Order ID', 'dokan' ),
                    'type'        => 'integer',
                    'context'     => [ 'view', 'edit' ],
                    'default'     => $refund->get_order_id(),
                ],
                'seller_id' => [
                    'description' => __( 'Vendor ID', 'dokan' ),
                    'type'        => 'integer',
                    'context'     => [ 'view' ],
                ],
                'amount' => [
                    'description' => __( 'The amount requested for refund. Should always be numeric', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'default'     => $refund->get_refund_amount(),
                ],
                'reason' => [
                    'description' => __( 'Refund Reason', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'default'     => $refund->get_refund_reason(),
                ],
                'item_qty' => [
                    'description' => __( 'Item Quantity', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'default'     => $refund->get_item_qtys(),
                ],
                'item_totals' => [
                    'description' => __( 'Items Total Amount', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'default'     => $refund->get_item_totals(),
                ],
                'tax_total' => [
                    'description' => __( 'Tax Total', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'default'     => $refund->get_item_tax_totals(),
                ],
                'restock' => [
                    'description' => __( 'Restock Items', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                    'default'     => $refund->get_restock_items(),
                ],
                'date' => [
                    'description' => __( 'The date the Refund request has beed created in the site\'s timezone.', 'dokan' ),
                    'type'        => 'date-time',
                    'context'     => [ 'view' ],
                ],
                'status' => [
                    'description' => __( 'Refund status', 'dokan' ),
                    'type'        => 'string',
                    'enum'        => ! empty( dokan_pro()->refund ) ? array_keys( dokan_pro()->refund->get_statuses() ) : [],
                    'context'     => [ 'view', 'edit' ],
                ],
                'method' => [
                    'description' => __( 'Refund Method', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                ],
            ],
        ];

        return $this->add_additional_fields_schema( $schema );
    }

    /**
     * Schema for batch processing
     *
     * @since 3.0.0
     *
     * @return array
     */
    public function get_public_batch_schema() {
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'batch',
            'type'       => 'object',
            'properties' => [
                'completed'  => [
                    'required'    => false,
                    'description' => __( 'List of refund IDs to be completed', 'dokan-lite' ),
                    'type'        => 'array',
                    'context'     => [ 'edit' ],
                    'default'     => [],
                    'items'       => [
                        'type' => 'integer',
                    ],
                ],
                'cancelled' => [
                    'required'    => false,
                    'description' => __( 'List of refund IDs to be cancelled', 'dokan-lite' ),
                    'type'        => 'array',
                    'context'     => [ 'edit' ],
                    'default'     => [],
                    'items'       => [
                        'type' => 'integer',
                    ],
                ],
            ],
        ];

        return $schema;
    }

    /**
     * Prepare refund for response
     *
     * @since 3.0.0
     *
     * @param \WeDevs\DokanPro\Refund\Refund $refund
     * @param \WP_REST_Request               $request
     *
     * @return \WP_REST_Response
     */
    public function prepare_item_for_response( $refund, $request ) {
        $vendor = dokan()->vendor->get( $refund->get_seller_id() );

        $data = [
            'id'            => Sanitizer::sanitize_id( $refund->get_id() ),
            'order_id'      => Sanitizer::sanitize_order_id( $refund->get_order_id() ),
            'vendor'        => $vendor->to_array(),
            'amount'        => Sanitizer::sanitize_refund_amount( $refund->get_refund_amount() ),
            'reason'        => Sanitizer::sanitize_refund_reason( $refund->get_refund_reason() ),
            'item_qtys'     => Sanitizer::sanitize_item_qtys( $refund->get_item_qtys() ),
            'item_totals'   => Sanitizer::sanitize_item_totals( $refund->get_item_totals() ),
            'tax_totals'    => Sanitizer::sanitize_item_tax_totals( $refund->get_item_tax_totals() ),
            'restock_items' => Sanitizer::sanitize_restock_items( $refund->get_restock_items() ),
            'created'       => mysql_to_rfc3339( Sanitizer::sanitize_date( $refund->get_date() ) ),
            'status'        => $refund->get_status_name(),
            'method'        => get_post_meta( $refund->get_order_id(), '_payment_method_title', true ),
            'type'          => $refund->get_method(),
        ];

        $response = rest_ensure_response( $data );
        $response->add_links( $this->prepare_links( $refund, $request ) );

        return apply_filters( 'dokan_rest_prepare_refund_object', $response, $refund, $request );
    }

    /**
     * Format collection response
     *
     * @since 3.0.0
     *
     * @param \WP_REST_Response $response
     * @param \WP_REST_Request  $request
     * @param int               $total_items
     *
     * @return \WP_REST_Response
     */
    public function format_collection_response( $response, $request, $total_items ) {
        if ( $total_items === 0 ) {
            return $response;
        }

        // Store pagation values for headers then unset for count query.
        $per_page = (int) ( ! empty( $request['per_page'] ) ? $request['per_page'] : 20 );
        $page     = (int) ( ! empty( $request['page'] ) ? $request['page'] : 1 );

        $response->header( 'X-WP-Total', (int) $total_items );

        $max_pages = ceil( $total_items / $per_page );

        $response->header( 'X-WP-TotalPages', (int) $max_pages );
        $base = add_query_arg( $request->get_query_params(), rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ) );

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
     * Prepare links for the request.
     *
     * @since 3.0.0
     *
     * @param \WeDevs\DokanPro\Refund\Refund         $object  Object data.
     * @param \WP_REST_Request                       $request Request object.
     *
     * @return array Links for the given post.
     */
    protected function prepare_links( $refund, $request ) {
        $links = [
            'self' => [
                'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->rest_base, $refund->get_id() ) ),
            ],
            'collection' => [
                'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->rest_base ) ),
            ],
        ];

        return $links;
    }
}
