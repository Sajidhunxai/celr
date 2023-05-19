<?php

namespace WeDevs\DokanPro\REST;

use WP_Error;
use WP_Query;
use WP_User_Query;
use WP_REST_Response;
use WP_REST_Server;
use WeDevs\Dokan\Abstracts\DokanRESTController;
use WeDevs\DokanPro\Admin\Announcement;
use WeDevs\Dokan\Cache;

class AnnouncementController extends DokanRESTController {

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
    protected $base = 'announcement';

    /**
     * Post type.
     *
     * @var string
     */
    protected $post_type = 'dokan_announcement';

    /**
     * Post type.
     *
     * @var string
     */
    protected $post_status = array( 'publish' );

    /**
     * Register all announcement route
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace, '/' . $this->base, array(
                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_announcements' ),
                    'args'                => array_merge(
                        $this->get_collection_params(), array(
                            'status' => array(
                                'type'        => 'string',
                                'description' => __( 'Announcement status', 'dokan' ),
                                'required'    => false,
                            ),
                        )
                    ),
                    'permission_callback' => array( $this, 'get_announcement_permissions_check' ),
                ),
                array(
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => array( $this, 'create_announcement' ),
                    'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                    'permission_callback' => array( $this, 'create_announcement_permissions_check' ),
                ),
            )
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/', array(
                'args' => array(
                    'id' => array(
                        'description' => __( 'Unique identifier for the object.', 'dokan' ),
                        'type'        => 'integer',
                    ),
                ),

                array(
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => array( $this, 'get_announcement' ),
                    'permission_callback' => array( $this, 'get_announcement_permissions_check' ),
                ),

                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'update_announcement' ),
                    'permission_callback' => array( $this, 'get_announcement_permissions_check' ),
                ),

                array(
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => array( $this, 'delete_announcement' ),
                    'permission_callback' => array( $this, 'get_announcement_permissions_check' ),
                ),

            )
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/restore', array(
                'args' => array(
                    'id' => array(
                        'description' => __( 'Unique identifier for the object.', 'dokan' ),
                        'type'        => 'integer',
                    ),
                ),

                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'restore_announcement' ),
                    'permission_callback' => array( $this, 'restore_announcement_permissions_check' ),
                ),
            )
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/batch', array(
                array(
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => array( $this, 'batch_items' ),
                    'permission_callback' => array( $this, 'batch_items_permissions_check' ),
                    'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
                ),
            )
        );
    }

    /**
     * Get single object
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function get_object( $id ) {
        return get_post( $id );
    }

    /**
     * Get all announcement
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function get_announcements( $request ) {
        $status = ( empty( $request['status'] ) || $request['status'] === 'all' ) ? array( 'publish', 'pending', 'draft', 'future' ) : $request['status'];
        $limit  = $request['per_page'];
        $offset = ( $request['page'] - 1 ) * $request['page'];

        $args = array(
            'post_type'      => $this->post_type,
            'posts_per_page' => $limit,
            'paged'          => $offset,
            'post_status'    => $status,
        );

        $cache_group = 'announcements';
        $cache_key   = 'announcements_' . md5( wp_json_encode( $args ) );
        $query       = Cache::get( $cache_key, $cache_group );

        if ( false === $query ) {
            $query = new WP_Query( $args );

            Cache::set( $cache_key, $query, $cache_group );
        }

        $data = array();
        if ( $query->posts ) {
            foreach ( $query->posts as $value ) {
                $resp   = $this->prepare_response_for_object( $value, $request );
                $data[] = $this->prepare_response_for_collection( $resp );
            }
        }

        $response       = rest_ensure_response( $data );
        $response_count = $query->found_posts;

        $count = wp_count_posts( 'dokan_announcement' );

        $response->header( 'X-Status-All', ( $count->pending + $count->publish + $count->draft + $count->future + $count->trash ) );
        $response->header( 'X-Status-Pending', $count->pending );
        $response->header( 'X-Status-Publish', $count->publish );
        $response->header( 'X-Status-Draft', $count->draft );
        $response->header( 'X-Status-Trash', $count->trash );
        $response->header( 'X-Status-Future', $count->future );

        $response = $this->format_collection_response( $response, $request, $response_count );
        return $response;
    }

    /**
     * Get single announcement object
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function get_announcement( $request ) {
        $announcement_id = $request['id'];

        if ( empty( $announcement_id ) ) {
            return new WP_Error( 'no_announcement_found', __( 'No announcement found', 'dokan' ), array( 'status' => 404 ) );
        }

        $data     = $this->prepare_response_for_object( $this->get_object( $announcement_id ), $request );
        $response = rest_ensure_response( $data );

        return $response;
    }

    /**
     * Create announcement
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function create_announcement( $request ) {
        $created_announcement = dokan_pro()->announcement->create_announcement( $request );

        if ( is_wp_error( $created_announcement ) ) {
            return new WP_Error( $created_announcement->get_error_code(), $created_announcement->get_error_message(), array( 'status' => 404 ) );
        }
        $data = $this->prepare_response_for_object( $this->get_object( $created_announcement ), $request );

        return rest_ensure_response( $data );
    }

    /**
     * Update announcement
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function update_announcement( $request ) {
        if ( empty( trim( $request['id'] ) ) ) {
            return new WP_Error( 'no_id', __( 'No announcement id found', 'dokan' ), array( 'status' => 404 ) );
        }

        if ( isset( $request['title'] ) && empty( trim( $request['title'] ) ) ) {
            return new WP_Error( 'no_title', __( 'Announcement title must be required', 'dokan' ), array( 'status' => 404 ) );
        }

        $status        = ! empty( $request['status'] ) ? $request['status'] : '';
        $post_date     = ! empty( $request['post_date'] ) ? $request['post_date'] : '';

        $data = array(
            'ID'           => $request['id'],
            'post_title'   => sanitize_text_field( $request['title'] ),
            'post_content' => wp_kses_post( $request['content'] ),
        );

        if ( $status ) {
            $data['post_status'] = $status;
            $data['post_date']   = $post_date;
        }

        // if announcement is 'schedueld', but want to publish it now
        // and set post_date_gmt to `0000-00-00 00:00:00`
        $post_date_gmt = ! empty( $request['post_date_gmt'] ) ? $request['post_date_gmt'] : '';

        if ( $post_date_gmt ) {
            $data['post_date_gmt'] = $post_date_gmt;
        }

        $post_id = wp_update_post( $data );

        update_post_meta( $post_id, '_announcement_type', $request['sender_type'] );
        update_post_meta( $post_id, '_announcement_selected_user', $request['sender_ids'] );

        /**
         * @var $announcement Announcement
         */
        $announcement = dokan_pro()->announcement;

        $assigned_sellers   = ! empty( $request['sender_ids'] ) ? $request['sender_ids'] : array();
        $announcement_types = apply_filters( 'dokan_announcement_seller_types', [ 'all_seller', 'enabled_seller', 'disabled_seller', 'featured_seller' ] );

        if ( 'selected_seller' === $request['sender_type'] ) {
            $announcement->process_seller_announcement_data( $assigned_sellers, $post_id );
        } elseif ( in_array( $request['sender_type'], $announcement_types, true ) ) {
            $assigned_sellers = array();

            $args = [
                'role__in'   => [ 'seller', 'administrator' ],
            ];

            if ( 'enabled_seller' === $request['sender_type'] ) {
                $args['meta_query'][] = [
                    'key'     => 'dokan_enable_selling',
                    'value'   => 'yes',
                    'compare' => '=',
                ];
            }

            if ( 'disabled_seller' === $request['sender_type'] ) {
                $args['meta_query'][] = [
                    'key'     => 'dokan_enable_selling',
                    'value'   => 'no',
                    'compare' => '=',
                ];
            }

            if ( 'featured_seller' === $request['sender_type'] ) {
                $args['meta_query'][] = [
                    'key'     => 'dokan_feature_seller',
                    'value'   => 'yes',
                    'compare' => '=',
                ];
            }
            $users   = new WP_User_Query( $args );
            $sellers = $users->get_results();

            if ( $sellers ) {
                foreach ( $sellers as $user ) {
                    $assigned_sellers[] = $user->ID;
                }
            }

            $announcement->process_seller_announcement_data( $assigned_sellers, $post_id );
        }

        do_action( 'dokan_after_announcement_saved', $post_id, $assigned_sellers );

        $data = $this->prepare_response_for_object( $this->get_object( $post_id ), $request );

        return rest_ensure_response( $data );
    }

    /**
     * Delete announcement
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function delete_announcement( $request ) {
        $post = $this->get_object( $request['id'] );

        if ( is_wp_error( $post ) ) {
            return $post;
        }

        $id             = $post->ID;
        $force          = (bool) $request['force'];
        $supports_trash = ( EMPTY_TRASH_DAYS > 0 );
        $supports_trash = apply_filters( "dokan_rest_{$this->post_type}_trashable", $supports_trash, $post );

        // delete individual announcement cache
        Announcement::delete_announcement_cache( [], $id );

        // If we're forcing, then delete permanently.
        if ( $force ) {
            $previous = $this->prepare_response_for_object( $post, $request );
            $result = wp_delete_post( $id, true );
            $this->delete_announcement_data( $id );
            $response = new WP_REST_Response();
            $response->set_data(
                array(
                    'deleted' => true,
                    'previous' => $previous->get_data(),
                )
            );
        } else {
            // If we don't support trashing for this type, error out.
            if ( ! $supports_trash ) {
                /* translators: %s: force=true */
                return new WP_Error( 'rest_trash_not_supported', sprintf( __( "The post does not support trashing. Set '%s' to delete.", 'dokan' ), 'force=true' ), array( 'status' => 501 ) );
            }

            // Otherwise, only trash if we haven't already.
            if ( 'trash' === $post->post_status ) {
                return new WP_Error( 'rest_already_trashed', __( 'The post has already been deleted.', 'dokan' ), array( 'status' => 410 ) );
            }

            // (Note that internally this falls through to `wp_delete_post` if
            // the trash is disabled.)
            $result = wp_trash_post( $id );
            $post = get_post( $id );
            $response = $this->prepare_response_for_object( $post, $request );
        }

        if ( ! $result ) {
            return new WP_Error( 'dokan_rest_cannot_delete', __( 'The announcement cannot be deleted.', 'dokan' ), array( 'status' => 500 ) );
        }

        return $response;
    }

    /**
     * Restore announcement
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function restore_announcement( $request ) {
        $post = $this->get_object( $request['id'] );

        if ( is_wp_error( $post ) ) {
            return $post;
        }

        $post     = wp_untrash_post( $post->ID );
        $response = $this->prepare_response_for_object( $post, $request );

        // delete individual announcement cache
        Announcement::delete_announcement_cache( [], $post->ID );

        return $response;
    }

    /**
     * Trash, delete and restore bulk action
     *
     * JSON data format for sending to API
     *     {
     *         "trash" : [
     *             "1", "9", "7"
     *         ],
     *         "delete" : [
     *             "2"
     *         ],
     *         "restore" : [
     *             "4"
     *         ]
     *     }
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function batch_items( $request ) {
        global $wpdb;

        $params = $request->get_params();

        if ( empty( $params ) ) {
            return new WP_Error( 'no_item_found', __( 'No items found for bulk updating', 'dokan' ), array( 'status' => 404 ) );
        }

        $allowed_status = array( 'trash', 'delete', 'restore' );

        foreach ( $params as $status => $value ) {
            if ( in_array( $status, $allowed_status, true ) ) {
                if ( 'delete' === $status ) {
                    foreach ( $value as $announcement_id ) {
                        // delete individual announcement cache
                        Announcement::delete_announcement_cache( [], $announcement_id );
                        $result = wp_delete_post( $announcement_id, true );
                        $this->delete_announcement_data( $announcement_id );
                    }
                } elseif ( 'trash' === $status ) {
                    foreach ( $value as $announcement_id ) {
                        // delete individual announcement cache
                        Announcement::delete_announcement_cache( [], $announcement_id );
                        wp_trash_post( $announcement_id );
                    }
                } elseif ( 'restore' === $status ) {
                    foreach ( $value as $announcement_id ) {
                        // delete individual announcement cache
                        Announcement::delete_announcement_cache( [], $announcement_id );
                        wp_untrash_post( $announcement_id );
                    }
                }
            }
        }

        return true;
    }


    /**
     * Delete announcement relational table data
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function delete_announcement_data( $post_id ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'dokan_announcement';
        $sql = "DELETE FROM {$table_name} WHERE `post_id` = $post_id";

        $wpdb->query( $sql );
    }

    /**
     * Get announcement permissions check
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function get_announcement_permissions_check() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Get restore announcement permissions check
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function restore_announcement_permissions_check() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Create announcement permissions check
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function create_announcement_permissions_check() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Check permission for getting withdraw
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function batch_items_permissions_check() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Prepare data for response
     *
     * @since 2.8.0
     *
     * @return data
     */
    public function prepare_response_for_object( $object, $request ) {
        $data = array(
            'id'           => $object->ID,
            'title'        => $object->post_title,
            'content'      => $object->post_content,
            'status'       => $object->post_status,
            'created_at'   => mysql_to_rfc3339( $object->post_date ),
            'sender_type'  => get_post_meta( $object->ID, '_announcement_type', true ),
            'sender_ids'   => array(),
        );

        $sender_ids = get_post_meta( $object->ID, '_announcement_selected_user', true );

        if ( ! empty( $sender_ids ) ) {
            foreach ( $sender_ids as  $id ) {
                $vendor = dokan()->vendor->get( $id );
                $data['sender_ids'][] = array(
                    'id'        => $id,
                    'name'      => $vendor->get_shop_name() . '(' . $vendor->get_email() . ')',
                    'shop_name' => $vendor->get_shop_name(),
                );
            }
        }

        $response = rest_ensure_response( $data );
        $response->add_links( $this->prepare_links( $object, $request ) );

        return apply_filters( 'dokan_rest_prepare_announcement_object', $response, $object, $request );
    }

    /**
     * Prepare links for the request.
     *
     * @param WC_Data         $object  Object data.
     * @param WP_REST_Request $request Request object.
     *
     * @return array                   Links for the given post.
     */
    protected function prepare_links( $object, $request ) {
        $links = array(
            'self' => array(
                'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->base, $object->ID ) ),
            ),
            'collection' => array(
                'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->base ) ),
            ),
        );

        return $links;
    }
}
