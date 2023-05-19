<?php

/**
 * Class AdminStoreSupportTicketController file
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once DOKAN_STORE_SUPPORT_INC_DIR . '/class-store-support-helper.php';
use WeDevs\Dokan\Abstracts\DokanRESTAdminController;

if ( ! class_exists( 'AdminStoreSupportTicketController' ) ) :
    /**
     * Dokan admin store suppoer rest controller class.
     *
     * @since 3.5.0
     *
     * @class AdminStoreSupportTicketController
     *
     * @extends DokanRESTAdminController
     */
    class AdminStoreSupportTicketController extends DokanRESTAdminController {

        /**
         * Route name
         *
         * @var string
         */
        protected $base = 'support-ticket';

        /**
         * Register all routes related with logs
         *
         * @since 3.5.0
         *
         * @return void
         */
        public function register_routes() {
            register_rest_route(
                $this->namespace, '/' . $this->base, [
                    [
                        'methods'             => WP_REST_Server::READABLE,
                        'callback'            => [ $this, 'get_all_tickets' ],
                        'permission_callback' => [ $this, 'check_permission' ],
                        'args'                => $this->get_collection_params(),
                    ],
                ]
            );

            register_rest_route(
                $this->namespace, '/' . $this->base . '/batch', [
                    [
                        'methods'             => WP_REST_Server::EDITABLE,
                        'callback'            => [ $this, 'batch_update' ],
                        'permission_callback' => [ $this, 'check_permission' ],
                    ],
                ]
            );

            register_rest_route(
                $this->namespace, '/' . $this->base . '/customers', [
                    [
                        'methods'             => WP_REST_Server::READABLE,
                        'callback'            => [ $this, 'get_customers' ],
                        'permission_callback' => [ $this, 'check_permission' ],
                    ],
                ]
            );

            register_rest_route(
                $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)', [
                    'args' => [
                        'id' => [
                            'description' => __( 'Unique identifier of topic id.', 'dokan' ),
                            'type'        => 'integer',
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                    ],
                    [
                        'methods'             => WP_REST_Server::READABLE,
                        'callback'            => [ $this, 'get_single_topic' ],
                        'permission_callback' => [ $this, 'check_permission' ],
                    ],
                ]
            );

            register_rest_route(
                $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)', [
                    'args' => [
                        'id' => [
                            'description' => __( 'Unique identifier if topic.', 'dokan' ),
                            'type'        => 'integer',
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                    ],
                    [
                        'methods'             => WP_REST_Server::CREATABLE,
                        'callback'            => [ $this, 'create_reply' ],
                        'args'                => [
                            'replay'    => [
                                'type'        => 'integer',
                                'description' => __( 'Topic comment or replay', 'dokan' ),
                                'required'    => true,
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                            'vendor_id'    => [
                                'type'        => 'integer',
                                'description' => __( 'Store id', 'dokan' ),
                                'required'    => true,
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                            'selected_user'    => [
                                'type'        => 'integer',
                                'description' => __( 'Store id', 'dokan' ),
                                'required'    => true,
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                        ],
                        'permission_callback' => [ $this, 'check_permission' ],
                    ],
                ]
            );

            register_rest_route(
                $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/status', [
                    'args' => [
                        'id' => [
                            'description' => __( 'Unique identifier if topic.', 'dokan' ),
                            'type'        => 'integer',
                        ],
                    ],
                    [
                        'methods'             => WP_REST_Server::CREATABLE,
                        'callback'            => [ $this, 'change_status' ],
                        'args'                => [
                            'status'    => [
                                'type'        => 'string',
                                'description' => __( 'Topic status', 'dokan' ),
                                'required'    => true,
                                'sanitize_callback' => 'sanitize_text_field',
                            ],
                        ],
                        'permission_callback' => [ $this, 'check_permission' ],
                    ],
                ]
            );

            register_rest_route(
                $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/comment', [
                    'args' => [
                        'id' => [
                            'description' => __( 'Unique identifier of comment.', 'dokan' ),
                            'type'        => 'integer',
                            'sanitize_callback' => 'sanitize_text_field',
                        ],
                    ],
                    [
                        'methods'             => WP_REST_Server::DELETABLE,
                        'callback'            => [ $this, 'delete_comment' ],
                        'permission_callback' => [ $this, 'check_permission' ],
                    ],
                ]
            );

            register_rest_route(
                $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/email-notification', [
                    'args' => [
                        'id' => [
                            'description'       => __( 'Unique identifier of topic id.', 'dokan' ),
                            'type'              => 'integer',
                            'sanitize_callback' => 'absint',
                        ],
                        'notification' => [
                            'description'       => __( 'Enable or disable notification for admin', 'dokan' ),
                            'type'              => 'boolean',
                            'sanitize_callback' => 'rest_sanitize_boolean',
                        ],
                    ],
                    [
                        'methods'             => WP_REST_Server::CREATABLE,
                        'callback'            => [ $this, 'enable_disable_email_notification' ],
                        'permission_callback' => [ $this, 'check_permission' ],
                    ],
                ]
            );
        }

        /**
         * Get all support tickets list
         *
         * @param \WP_REST_Request $request Request object.
         *
         * @since 3.5.0
         *
         * @return array $response
         */
        public function get_all_tickets( $request ) {
            global $wpdb;

            $args   = [];
            $params = $this->get_collection_params();

            // Preparing arguments.
            foreach ( $params as $key => $value ) {
                if ( ! isset( $request[ $key ] ) || ( 'post_status' === $key && 'all' === $request[ $key ] ) ) {
                    continue;
                }

                if ( 'search' === $key && ! empty( $request[ $key ] ) ) {
                    $index = is_numeric( $request[ $key ] ) ? 'p' : 's';
                    $args[ $index ] = $wpdb->esc_like( $request[ $key ] );
                }

                $args[ $key ] = $request[ $key ];
            }

            // Filterable argumenta for vendor wise filter.
            $filters = isset( $args['filter'] ) ? $args['filter'] : [];

            // All tickets.
            $result = StoreSupportHelper::get_all_tickets( $args );

            // After filter by vendor and preparing items data container.
            $prepared_result = [];

            // Preparing data and filtering by vendor if vendor id sent from frontend.
            foreach ( $result as $key => $value ) {
                if ( StoreSupportHelper::filter_topics_by_vendor( $filters, $value ) ) {
                    $suooprt_ticket_data = $this->prepare_item_for_response( $value, $request );
                    $prepared_result[]   = $this->prepare_response_for_collection( $suooprt_ticket_data );
                }
            }

            $response = rest_ensure_response( $prepared_result );

            $arguments = isset( $args['post_status'] ) ? [ $args['post_status'] ] : [];

            $response = $this->format_collection_response( $response, $request, StoreSupportHelper::dokan_get_total_support_topics_count( $arguments ) );

            return $response;
        }

        /**
         * Get single topic and its comments.
         *
         * @since 3.5.0
         *
         * @param \WP_REST_Request $request Request object.
         *
         * @return object $result
         */
        public function get_single_topic( $request ) {
            $result = StoreSupportHelper::get_single_topic( $request );

            return rest_ensure_response( $result );
        }

        /**
         * Delete a comment by its comment id.
         *
         * @since 3.5.0
         *
         * @param \WP_REST_Request $request Request object.
         *
         * @return int|boolean
         */
        public function delete_comment( $request ) {
            $comment_id = absint( $request['id'] );

            $result = wp_delete_comment( $comment_id, true );

            return rest_ensure_response( $result );
        }

        /**
         * Create a new comment or reply from admin dashboard.
         *
         * @since 3.5.0
         *
         * @param \WP_REST_Request $request Request object.
         *
         * @return boolean|int
         */
        public function create_reply( $request ) {
            $topic_id      = absint( sanitize_text_field( $request['id'] ) );
            $replay        = sanitize_textarea_field( $request['replay'] );
            $vendor_id     = absint( sanitize_text_field( $request['vendor_id'] ) );
            $selected_user = sanitize_text_field( $request['selected_user'] );
            $replier       = 'vendor' === $selected_user ? get_user_by( 'ID', $vendor_id ) : wp_get_current_user();
            $replire_name  = ( empty( $replier->first_name ) || empty( $replier->last_name ) ) ? $replier->user_login : $replier->first_name . ' ' . $replier->last_name;

            if ( ! in_array( $selected_user, [ 'admin', 'vendor' ], true ) ) {
                return;
            }

            return StoreSupportHelper::create_comment_replay( $topic_id, $replire_name, $replier, $replay );
        }

        /**
         * Change ticket staus form admin.
         *
         * @since 3.5.0
         *
         * @param \WP_REST_Request $request Request object.
         *
         * @return array
         */
        public function change_status( $request ) {
            $topic_id = absint( sanitize_text_field( $request['id'] ) );
            $status   = sanitize_text_field( $request['status'] );

            $my_post = array(
                'ID'          => $topic_id,
                'post_status' => $status,
            );

            // Update the post into the database
            $result = wp_update_post( $my_post );

            if ( is_wp_error( $result ) ) {
                wp_send_json_error(
                    [
                        'result'  => 'error',
                        'message' => __( 'Could not update status', 'dokan' ),
                    ]
                );
            }

            wp_send_json_success(
                [
                    'result'  => 'success',
                    /* translators: Ticket id */
                    'message' => sprintf( __( 'Ticket is %s', 'dokan' ), $status ),
                ]
            );
        }

        /**
         * Returns collection parameters
         *
         * @since 3.5.0
         *
         * @return array $collection
         */
        public function get_collection_params() {
            $collection = parent::get_collection_params();

            $collection['filter'] = [
                'description'       => __( 'Finter argumants', 'dokan' ),
                'type'              => 'object',
                'sanitize_callback' => 'wp_unslash',
                'validate_callback' => 'rest_validate_request_arg',
            ];
            $collection['orderby'] = [
                'description'       => __( 'Get results order by', 'dokan' ),
                'default'           => 'ID',
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ];
            $collection['order'] = [
                'description'       => __( 'Assendind or decending order', 'dokan' ),
                'default'           => 'DESC',
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ];
            $collection['post_status'] = [
                'description'       => __( 'Ticket status', 'dokan' ),
                'default'           => 'open',
                'type'              => 'string',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ];

            return $collection;
        }

        /**
         * Batch update for store support listing
         *
         * @since 3.5.0
         *
         * @param $request
         *
         * @since 3.5.0
         *
         * @return array|WP_Error
         */
        public function batch_update( $request ) {
            $params = $request->get_params();

            if ( empty( $params ) ) {
                return new \WP_Error( 'no_item_found', __( 'No items found for bulk updating', 'dokan' ), [ 'status' => 404 ] );
            }

            $response = [];

            foreach ( $params as $status => $value ) {
                switch ( $status ) {
                    case 'close':
                        foreach ( $value as $store_support_topic_id ) {
                            $response['closed'][] = StoreSupportHelper::dokan_change_topic_status( $store_support_topic_id, 'closed' );
                        }
                        break;
                }
            }

            return $response;
        }

        /**
         * Prepare a single support ticket output for response
         *
         * @since 3.5.0
         *
         * @param $suooprt_tickets
         * @param \WP_REST_Request $request Request object.
         * @param array $additional_fields (optional)
         *
         * @return \WP_REST_Response $response Response data.
         */
        public function prepare_item_for_response( $support_tickets, $request, $additional_fields = [] ) {
            $data = $support_tickets->to_array();

            $data_for_response = array_merge( $data, apply_filters( 'dokan_rest_support_tickets_additional_fields', $additional_fields, $support_tickets, $request ) );
            $response          = rest_ensure_response( $data_for_response );

            $response->add_links( $this->prepare_links( $data, $request ) );

            return apply_filters( 'dokan_rest_prepare_suooprt_tickets_item_for_response', $response );
        }

        /**
         * Prepare links for the request.
         *
         * @since 3.5.0
         *
         * @param \WC_Data $object Object data.
         * @param \WP_REST_Request $request Request object.
         *
         * @return array Links for the given post.
         */
        protected function prepare_links( $object, $request ) {
            $links = [
                'collection' => [
                    'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->base ) ),
                ],
            ];

            return $links;
        }

        /**
         * Format item's collection for response
         *
         * @since 3.5.0
         *
         * @param object $response
         * @param object $request
         * @param int $total_items
         *
         * @return object
         */
        public function format_collection_response( $response, $request, $total_items ) {
            // Store pagination values for headers then unset for count query.
            $per_page  = (int) ( ! empty( $request['per_page'] ) ? $request['per_page'] : 20 );
            $page      = (int) ( ! empty( $request['page'] ) ? $request['page'] : 1 );
            $max_pages = ceil( $total_items / $per_page );

            if ( current_user_can( 'manage_woocommerce' ) ) {
                $counts = StoreSupportHelper::dokan_get_support_topics_status_count();

                $response->header( 'X-Status-All', (int) $counts['all'] );
                $response->header( 'X-Status-Closed', (int) $counts['closed_topics'] );
                $response->header( 'X-Status-Open', (int) $counts['open_topics'] );
                $response->header( 'X-Ticket-Unread', StoreSupportHelper::get_unread_support_topic_count() );
            }

            $response->header( 'X-WP-Total', (int) $total_items );
            $response->header( 'X-WP-TotalPages', (int) $max_pages );

            if ( 0 === $total_items ) {
                return $response;
            }

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
         * Get customers.
         *
         * @since 3.5.0
         *
         * @param \WP_REST_Request $request Request object.
         *
         * @return array
         */
        public function get_customers( $request ) {
            $searched_customer = ! empty( $request['search'] ) ? sanitize_text_field( wp_unslash( $request['search'] ) ) : '';

            return StoreSupportHelper::dokan_get_support_topic_created_customers( $searched_customer );
        }

        /**
         * Enables or disable email notification for admin.
         *
         * @since 3.6.0
         *
         * @param object $request
         *
         * @return array $response
         */
        public function enable_disable_email_notification( $request ) {
            $topic_id     = $request['id'];
            $notification = $request['notification'];
            $enabled      = $notification ? __( 'enabled', 'dokan' ) : __( 'disabled', 'dokan' ); // true means admin wants to enable notification or disable notification.

            $global_settings = dokan_get_option( 'dokan_admin_email_notification', 'dokan_store_support_setting', 'off' );
            $type            = 'warn';
            $message         =  $notification ? __( 'Can not enable email notification, because global settings is off', 'dokan' ) : '';

            // If global setting is 'on' admin can any settings in specific settings if 'off' then topic specific settings can be set on.
            if ( 'on' === $global_settings || ( 'off' === $global_settings && ! $notification ) ) {
                update_post_meta( $topic_id, 'dokan_admin_email_notification', $notification ? 'on' : 'off' );

                /* translators: %s: enabled / disabled */
                $message = sprintf( __( 'Email notification %1$s successfully', 'dokan' ), $enabled );
                $type    = 'success';
            }

            $response = [
                'result'  => $notification,
                'message' => $message,
                'type'    => $type,
            ];

            return $response;
        }
    }
endif;
