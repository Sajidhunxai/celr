<?php

use WeDevs\Dokan\Abstracts\DokanRESTController;
use WeDevs\Dokan\Cache;

/**
* Announcement Controller class
*/
class Dokan_REST_Store_Review_Controller extends DokanRESTController {

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
    protected $base = 'store-reviews';

    /**
     * Post type.
     *
     * @var string
     */
    protected $post_type = 'dokan_store_reviews';

    /**
     * Register all announcement route
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route( $this->namespace, '/' . $this->base, array(
            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_items' ),
                'args'                => array_merge( $this->get_collection_params(),  array(
                    'vendor_id' => array(
                        'type'        => 'string',
                        'description' => __( 'Vendor ID', 'dokan' ),
                        'required'    => false,
                    ),
                ) ),
                'permission_callback' => '__return_true',
            ),
        ) );

        register_rest_route( $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/', array(
            'args' => array(
                'id' => array(
                    'description' => __( 'Unique identifier for the object.', 'dokan' ),
                    'type'        => 'integer',
                    'required'    => true,
                ),
            ),

            array(
                'methods'             => WP_REST_Server::READABLE,
                'callback'            => array( $this, 'get_item' ),
                'permission_callback' => '__return_true',
            ),

            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'update_review' ),
                'permission_callback' => array( $this, 'update_review_permissions_check' ),
            ),

            array(
                'methods'             => WP_REST_Server::DELETABLE,
                'callback'            => array( $this, 'delete_review' ),
                'args'                => array(
                    'force' => array(
                        'type'        => 'boolean',
                        'description' => __( 'Trash or permanenet delete', 'dokan-lite' ),
                        'required'    => false,
                    )
                ),
                'permission_callback' => array( $this, 'delete_review_permissions_check' ),
            ),

        ) );

        register_rest_route( $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/restore', array(
            'args' => array(
                'id' => array(
                    'description' => __( 'Unique identifier for the object.', 'dokan' ),
                    'type'        => 'integer',
                ),
            ),

            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'restore_reviews' ),
                'permission_callback' => array( $this, 'restore_review_permissions_check' ),
            ),
        ) );

        register_rest_route( $this->namespace, '/' . $this->base . '/batch', array(
            array(
                'methods'             => WP_REST_Server::EDITABLE,
                'callback'            => array( $this, 'batch_items' ),
                'permission_callback' => array( $this, 'batch_items_permissions_check' ),
                'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::EDITABLE ),
            ),
        ) );
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
    public function get_items( $request ) {
        $limit  = $request['per_page'];
        $offset = ( isset( $request['page'] ) && ! empty( $request['page'] ) ) ? $request['page'] : 1;
        $status = ( empty( $request['status'] ) || $request['status'] == 'all' ) ? array( 'publish', 'pending', 'draft', 'future' ) : $request['status'];

        $args = array(
            'post_type'      => $this->post_type,
            'posts_per_page' => $limit,
            'paged'          => $offset,
            'post_status'    => $status
        );

        if ( ! empty( $request['vendor_id'] ) ) {
            $args['meta_query'] = [
                [
                    'key'   => 'store_id',
                    'value' => $request['vendor_id'],
                ]
            ];
        }

        $cache_group = 'store_reviews';
        $cache_key   = 'store_reviews_' . md5( wp_json_encode( $args ) );;
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

        $response = rest_ensure_response( $data );
        $count    = wp_count_posts( $this->post_type );

        $response->header( 'X-Status-All', ( $count->publish + $count->trash ) );
        $response->header( 'X-Status-Trash', $count->trash );

        $response = $this->format_collection_response( $response, $request, $query->found_posts );
        return $response;
    }

    /**
     * Get single announcement object
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function get_item( $request ) {
        $store_review_id = $request['id'];

        if ( empty( $store_review_id ) ) {
            return new WP_Error( 'no_review_found', __( 'No review found', 'dokan' ), array( 'status' => 404 ) );
        }

        $store_review_object = $this->get_object( $store_review_id );

        if ( ! $store_review_object ) {
            return new WP_Error( 'no_review_found', __( 'No review found', 'dokan' ), array( 'status' => 404 ) );
        }

        $data     = $this->prepare_response_for_object( $store_review_object, $request );
        $response = rest_ensure_response( $data );

        return $response;
    }

    /**
     * Update announcement
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function update_review( $request ) {
        if ( empty( trim( $request['id'] ) ) ) {
            return new WP_Error( 'no_id', __( 'No review id found', 'dokan' ), array( 'status' => 404 ) );
        }

        if ( isset( $request['title'] ) && empty( trim( $request['title'] ) ) ) {
            return new WP_Error( 'no_title', __( 'Review title must be required', 'dokan' ), array( 'status' => 404 ) );
        }

        if ( isset( $request['content'] ) && empty( trim( $request['content'] ) ) ) {
            return new WP_Error( 'no_content', __( 'Review content must be required', 'dokan' ), array( 'status' => 404 ) );
        }

        $status    = ! empty( $request['status'] ) ? $request['status'] : '';
        $rating    = ! empty( $request['rating'] ) ? $request['rating']: '1';

        $data = array(
            'ID'           => $request['id'],
            'post_title'   => sanitize_text_field( $request['title'] ),
            'post_content' => sanitize_textarea_field( $request['content'] ),
        );

        if ( $status ) {
            $data['post_status'] = $status;
        }

        $post_id = wp_update_post( $data );

        update_post_meta( $post_id, 'rating', $request['rating'] );

        do_action( 'dokan_after_store_review_update', $post_id, $request );

        $data = $this->prepare_response_for_object( $this->get_object( $post_id ), $request );

        Cache::invalidate_group( 'store_reviews' );

        return rest_ensure_response( $data );
    }

    /**
     * Delete announcement
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function delete_review( $request ) {
        $post = $this->get_object( $request['id'] );

        if ( is_wp_error( $post ) ) {
            return $post;
        }

        $id    = $post->ID;
        $force = (bool) $request['force'];

        $supports_trash = ( EMPTY_TRASH_DAYS > 0 );

        $supports_trash = apply_filters( "dokan_rest_{$this->post_type}_trashable", $supports_trash, $post );

        // If we're forcing, then delete permanently.
        if ( $force ) {
            $previous = $this->prepare_response_for_object( $post, $request );
            $result = wp_delete_post( $id, true );
            $response = new WP_REST_Response();
            $response->set_data( array( 'deleted' => true, 'previous' => $previous->get_data() ) );
        } else {
            // If we don't support trashing for this type, error out.
            if ( ! $supports_trash ) {
                /* translators: %s: force=true */
                return new WP_Error( 'rest_trash_not_supported', sprintf( __( "The post does not support trashing. Set '%s' to delete.", "dokan" ), 'force=true' ), array( 'status' => 501 ) );
            }

            // Otherwise, only trash if we haven't already.
            if ( 'trash' === $post->post_status ) {
                return new WP_Error( 'rest_already_trashed', __( 'The post has already been deleted.', 'dokan' ), array( 'status' => 410 ) );
            }

            // (Note that internally this falls through to `wp_delete_post` if
            // the trash is disabled.)
            $result   = wp_trash_post( $id );
            $post     = get_post( $id );
            $response = $this->prepare_response_for_object( $post, $request );
        }

        if ( ! $result ) {
            return new WP_Error( 'dokan_rest_cannot_delete', __( 'The review cannot be deleted.', 'dokan' ), array( 'status' => 500 ) );
        }

        Cache::invalidate_group( 'store_reviews' );

        return $response;
    }

    /**
     * Restore announcement
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function restore_reviews( $request ) {
        $post = $this->get_object( $request['id'] );

        if ( is_wp_error( $post ) ) {
            return $post;
        }

        $post = wp_untrash_post( $post->ID );

        // Update the post status from `draft` to `publish` as by default `wp_untrash_post` makes post `draft`
        wp_update_post( [
            'ID'          => $post->ID,
            'post_status' => 'publish'
        ] );

        $response = $this->prepare_response_for_object( $post, $request );

        Cache::invalidate_group( 'store_reviews' );

        return $response;
    }

    /**
     * trash, delete and restore bulk action
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
            return new WP_Error( 'no_item_found', __( 'No items found for bulk updating', 'dokan-lite' ), array( 'status' => 404 ) );
        }

        $allowed_status = array( 'trash', 'delete', 'restore' );

        foreach ( $params as $status => $value ) {
            if ( in_array( $status, $allowed_status ) ) {
                if ( 'delete' === $status ) {
                    foreach ( $value as $store_review_id ) {
                        $result = wp_delete_post( $store_review_id, true );
                    }
                } else if ( 'trash' === $status ) {
                    foreach ( $value as $store_review_id ) {
                        wp_trash_post( $store_review_id );
                    }
                } else if ( 'restore' === $status ) {
                    foreach ( $value as $store_review_id ) {
                        wp_untrash_post( $store_review_id );
                    }
                }
            }
        }

        Cache::invalidate_group( 'store_reviews' );

        return true;
    }

    /**
     * Udpate review permission
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function update_review_permissions_check() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Delete review permissions
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function delete_review_permissions_check() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Restore review permission
     *
     * @since 2.8.2
     *
     * @return void
     */
    public function restore_review_permissions_check() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Batch item permissions
     *
     * @since 2.8.2
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
        $customer  = get_user_by( 'id', $object->post_author );
        $vendor_id = get_post_meta( $object->ID, 'store_id', true );
        $rating    = get_post_meta( $object->ID, 'rating', true );
        $vendor    = dokan()->vendor->get( $vendor_id );

        $data = array(
            'id'           => $object->ID,
            'title'        => $object->post_title,
            'content'      => $object->post_content,
            'status'       => $object->post_status,
            'created_at'   => mysql_to_rfc3339( $object->post_date ),
            'customer'     => [
                'id'           => $customer->ID,
                'first_name'   => $customer->first_name,
                'last_name'    => $customer->last_name,
                'email'        => $customer->user_email,
                'display_name' => $customer->display_name,
            ],
            'vendor'       => [
                'id'         => $vendor->get_id(),
                'first_name' => $vendor->get_first_name(),
                'last_name'  => $vendor->get_last_name(),
                'shop_name'  => $vendor->get_shop_name(),
                'shop_url'   => $vendor->get_shop_url(),
                'avatar'     => $vendor->get_avatar(),
                'banner'     => $vendor->get_banner(),
            ],
            'rating'       => intval( $rating )
        );

        $response      = rest_ensure_response( $data );
        $response->add_links( $this->prepare_links( $object, $request ) );

        return apply_filters( 'dokan_rest_prepare_store_review_object', $response, $object, $request );
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
