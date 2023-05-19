<?php

namespace WeDevs\DokanPro\REST;

use DSR_View;
use WP_Error;
use WP_REST_Server;
use WeDevs\Dokan\Abstracts\DokanRESTController;

/**
 * Reviews API controller
 *
 * @package dokan
 * @since 2.8.0
 *
 * @author weDevs
 */
class ReviewsController extends DokanRESTController {

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
    protected $base = 'reviews';


    /**
     * Register all routes related with coupons
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace, '/' . $this->base, [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_reviews' ],
                    'permission_callback' => [ $this, 'get_reviews_permission_check' ],
                    'args'                => $this->get_collection_params(),
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/summary', [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_reviews_summary' ],
                    'permission_callback' => [ $this, 'check_reviews_summary_permission' ],
                    'args'                => $this->get_collection_params(),
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)', [
                'args' => [
                    'id' => [
                        'description' => __( 'Unique identifier for the object.', 'dokan' ),
                        'type'        => 'integer',
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'update_review_status' ],
                    'permission_callback' => [ $this, 'manage_reviews_permission_check' ],
                    'args'                => [
                        'status' => [
                            'description' => __( 'Review Status', 'dokan' ),
                            'required'    => true,
                            'type'        => 'string',
                        ],
                    ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/stores/(?P<id>[\d]+)/reviews', [
                'args' => [
                    'id' => [
                        'description'       => __( 'Unique identifier for the object.', 'dokan' ),
                        'type'              => 'integer',
                        'validate_callback' => [ $this, 'is_valid_store' ],
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_item' ],
                    'permission_callback' => [ $this, 'create_item_permissions_check' ],
                    'args'                => [
                        'title'   => [
                            'required'    => true,
                            'type'        => 'string',
                            'description' => __( 'Review title.', 'dokan' ),
                        ],
                        'content' => [
                            'required'    => true,
                            'type'        => 'string',
                            'description' => __( 'Review content.', 'dokan' ),
                        ],
                        'rating'  => [
                            'required'          => false,
                            'type'              => 'integer',
                            'description'       => __( 'Review rating', 'dokan' ),
                            'validate_callback' => [ $this, 'is_valid_rating' ],
                            'default'           => 0,
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Validate store
     *
     * @param mixed $value
     * @param \WP_REST_Request $request
     * @param string $param
     *
     * @since 2.9.5
     *
     * @return bool|\WP_Error
     */
    public function is_valid_store( $value, $request, $param ) {
        $store = dokan()->vendor->get( $value );

        if ( absint( $store->id ) ) {
            return true;
        }

        return new WP_Error( 'rest_dokan_store_review_invalid_store', __( 'Invalid store id. Store not exists.', 'dokan' ) );
    }

    /**
     * Get reviews permissions
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function get_reviews_permission_check() {
        return current_user_can( 'dokan_view_review_menu' );
    }

    /**
     * Get reviews permissions
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function manage_reviews_permission_check() {
        return current_user_can( 'dokan_manage_reviews' );
    }

    /**
     * Get reviews permissions
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function check_reviews_summary_permission() {
        return current_user_can( 'dokan_view_review_reports' );
    }

    /**
     * Get all reviews
     *
     * @since 2.8.0
     *
     * @return object
     */
    public function get_reviews( $request ) {
        $store_id     = dokan_get_current_user_id();
        $review_class = dokan_pro()->review;

        if ( empty( $store_id ) ) {
            return new WP_Error( 'no_store_found', __( 'No seller found', 'dokan' ), [ 'status' => 404 ] );
        }

        $limit  = $request['per_page'];
        $offset = ( $request['page'] - 1 ) * $request['per_page'];

        $status   = $this->get_status( $request );
        $comments = $review_class->comment_query( $store_id, 'product', $limit, $status, $offset );

        $count = $this->get_total_count( $status );

        $data = [];
        foreach ( $comments as $comment ) {
            $data[] = $this->prepare_item_for_response( $comment, $request );
        }

        $response = rest_ensure_response( $data );
        $response = $this->format_collection_response( $response, $request, $count );

        return $response;
    }

    /**
     * Create review permission callback
     *
     * @param \WP_REST_Request $request
     *
     * @since 2.9.5
     *
     * @return bool
     */
    public function create_item_permissions_check( $request ) {
        if ( ! dokan_pro()->module->is_active( 'store_reviews' ) ) {
            return false;
        }

        if ( current_user_can( 'dokan_manage_reviews' ) ) {
            return true;
        }

        $dsr_view = DSR_View::init();

        return $dsr_view->check_if_valid_customer( $request['id'], get_current_user_id() );
    }

    /**
     * Validate rating
     *
     * @param mixed $value
     * @param \WP_REST_Request $request
     * @param string $param
     *
     * @since 2.9.5
     *
     * @return bool|\WP_Error
     */
    public function is_valid_rating( $value, $request, $param ) {
        $rating = absint( $request['rating'] );

        if ( $rating >= 0 && $rating <= 5 ) {
            return true;
        }

        return false;
    }

    /**
     * Creates a store review
     *
     * @param \WP_REST_Request $request
     *
     * @since 2.9.5
     *
     * @return \WP_Error|\WP_REST_Response
     */
    public function create_item( $request ) {
        $post_id = dsr_save_store_review(
            $request['id'], [
                'title'       => $request['title'],
                'content'     => $request['content'],
                'reviewer_id' => get_current_user_id(),
                'rating'      => $request['rating'],
            ]
        );

        if ( is_wp_error( $post_id ) ) {
            return $post_id;
        }

        $post = get_post( $post_id );

        $user          = get_user_by( 'id', $post->post_author );
        $user_gravatar = get_avatar_url( $user->user_email );

        $data = [
            'id'         => absint( $post->ID ),
            'author'     => [
                'id'     => $user->ID,
                'name'   => $user->user_login,
                'email'  => $user->user_email,
                'url'    => $user->user_url,
                'avatar' => $user_gravatar,
            ],
            'title'      => $post->post_title,
            'content'    => $post->post_content,
            'permalink'  => null,
            'product_id' => null,
            'approved'   => true,
            'date'       => mysql_to_rfc3339( $post->post_date ),
            'rating'     => absint( get_post_meta( $post->ID, 'rating', true ) ),
        ];

        return rest_ensure_response( $data );
    }

    /**
     * Manaage reviews
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function update_review_status( $request ) {
        $store_id     = dokan_get_current_user_id();
        $review_class = dokan_pro()->review;

        if ( empty( $store_id ) ) {
            return new WP_Error( 'no_store_found', __( 'No seller found', 'dokan' ), [ 'status' => 404 ] );
        }

        if ( empty( $request['id'] ) ) {
            return new WP_Error( 'no_reivew_found', __( 'No review id found', 'dokan' ), [ 'status' => 404 ] );
        }

        if ( empty( $request['status'] ) ) {
            return new WP_Error( 'no_reivew_status_found', __( 'No review status found for updating review', 'dokan' ), [ 'status' => 404 ] );
        }

        $status = $this->get_status( $request );

        $comment_id = $request['id'];

        if ( isset( $comment_id ) ) {
            wp_set_comment_status( $comment_id, $status );
        }

        $comment = get_comment( $comment_id );
        $data    = $this->prepare_item_for_response( $comment, $request );

        return rest_ensure_response( $data );
    }

    /**
     * Get review status
     *
     * @since 2.8.0
     *
     * @return mixed
     */
    public function get_status( $request ) {
        $status = isset( $request['status'] ) ? $request['status'] : '';

        switch ( $status ) {
            case 'hold':
                return '0';
            case 'spam':
                return 'spam';
            case 'trash':
                return 'trash';
            default:
                return '1';
        }
    }

    /**
     * Get total count of comment
     *
     * @param $status
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function get_total_count( $status ) {
        global $wpdb;
        $user_id = dokan_get_current_user_id();

        $total = $wpdb->get_var(
            "SELECT COUNT(*)
            FROM $wpdb->comments, $wpdb->posts
            WHERE   $wpdb->posts.post_author='$user_id' AND
            $wpdb->posts.post_status='publish' AND
            $wpdb->comments.comment_post_ID=$wpdb->posts.ID AND
            $wpdb->comments.comment_approved='$status' AND
            $wpdb->posts.post_type='product'"
        );

        return $total;
    }

    /**
     * Get review summary
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function get_reviews_summary( $request ) {
        $seller_id = dokan_get_current_user_id();

        $data = [
            'comment_counts' => dokan_count_comments( 'product', $seller_id ),
            'reviews_url'    => dokan_get_navigation_url( 'reviews' ),
        ];

        return rest_ensure_response( $data );
    }

    /**
     * Prepare a single product review output for response.
     *
     * @param WP_Comment $review Product review object.
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_item_for_response( $review, $request ) {
        $data = [
            'id'           => (int) $review->comment_ID,
            'date_created' => wc_rest_prepare_date_response( $review->comment_date ),
            'review'       => $review->comment_content,
            'rating'       => (int) get_comment_meta( $review->comment_ID, 'rating', true ),
            'name'         => $review->comment_author,
            'email'        => $review->comment_author_email,
            'verified'     => wc_review_is_from_verified_owner( $review->comment_ID ),
        ];

        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
        $data    = $this->add_additional_fields_to_object( $data, $request );
        $data    = $this->filter_response_by_context( $data, $context );

        return apply_filters( 'woocommerce_rest_prepare_product_review', $data, $review, $request );
    }

    /**
     * Get the Product Review's schema, conforming to JSON Schema.
     *
     * @return array
     */
    public function get_item_schema() {
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'product_review',
            'type'       => 'object',
            'properties' => [
                'id'               => [
                    'description' => __( 'Unique identifier for the resource.', 'dokan' ),
                    'type'        => 'integer',
                    'context'     => [ 'view', 'edit' ],
                    'readonly'    => true,
                ],
                'review'           => [
                    'description' => __( 'The content of the review.', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                ],
                'date_created'     => [
                    'description' => __( "The date the review was created, in the site's timezone.", 'dokan' ),
                    'type'        => 'date-time',
                    'context'     => [ 'view', 'edit' ],
                ],
                'date_created_gmt' => [
                    'description' => __( 'The date the review was created, as GMT.', 'dokan' ),
                    'type'        => 'date-time',
                    'context'     => [ 'view', 'edit' ],
                ],
                'rating'           => [
                    'description' => __( 'Review rating (0 to 5).', 'dokan' ),
                    'type'        => 'integer',
                    'context'     => [ 'view', 'edit' ],
                ],
                'name'             => [
                    'description' => __( 'Reviewer name.', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                ],
                'email'            => [
                    'description' => __( 'Reviewer email.', 'dokan' ),
                    'type'        => 'string',
                    'context'     => [ 'view', 'edit' ],
                ],
                'verified'         => [
                    'description' => __( 'Shows if the reviewer bought the product or not.', 'dokan' ),
                    'type'        => 'boolean',
                    'context'     => [ 'view', 'edit' ],
                    'readonly'    => true,
                ],
            ],
        ];

        return $this->add_additional_fields_schema( $schema );
    }

}
