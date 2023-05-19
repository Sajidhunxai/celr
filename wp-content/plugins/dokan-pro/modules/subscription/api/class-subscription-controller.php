<?php

use DokanPro\Modules\Subscription\Helper;
use DokanPro\Modules\Subscription\SubscriptionPack;
use WeDevs\Dokan\Abstracts\DokanRESTController;

/**
 * Subscription API controller
 *
 * @since 2.8.0
 *
 * @package dokan
 */
class Dokan_REST_Subscription_Controller extends DokanRESTController {

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
    protected $base = 'subscription';



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
                    'callback'            => [ $this, 'get_subscription' ],
                    'permission_callback' => [ $this, 'check_permission' ],
                    'args'                => array_merge(
                        $this->get_collection_params(), array(
                            'vendor_id' => array(
                                'description'       => __( 'Vendor ID', 'dokan' ),
                                'type'              => 'integer',
                                'default'           => null,
                                'sanitize_callback' => 'absint',
                                'validate_callback' => 'rest_validate_request_arg',
                                'required'          => false,
                            ),
                            'pack_id' => array(
                                'description'       => __( 'Package ID', 'dokan' ),
                                'type'              => 'integer',
                                'default'           => null,
                                'sanitize_callback' => 'absint',
                                'validate_callback' => 'rest_validate_request_arg',
                                'required'          => false,
                            ),
                        )
                    ),
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/packages', [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_subscription_packages' ],
                    'permission_callback' => [ $this, 'check_permission' ],
                    'args'                => $this->get_collection_params(),
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/nonrecurring-packages', [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_nonrecurring_subscription_packages' ],
                    'permission_callback' => [ $this, 'check_permission' ],
                    'args'                => $this->get_collection_params(),
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/vendor/(?P<id>[\d]+)', [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_active_subscription_for_vendor' ],
                    'permission_callback' => [ $this, 'check_permission' ],
                    'args'                => $this->get_collection_params(),
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/', [
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'update_subscription' ],
                    'permission_callback' => [ $this, 'check_permission' ],
                    'args'                => $this->get_collection_params(),
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/batch', [
                [
                    'methods'  => WP_REST_Server::EDITABLE,
                    'callback' => [ $this, 'batch_update' ],
                    'permission_callback' => [ $this, 'check_permission' ],
                ],
            ]
        );
    }

    /**
     * Check permission
     *
     * @since 2.9.3
     *
     * @return boolean
     */
    public function check_permission() {
        return current_user_can( 'manage_options' );
    }

    /**
     * Get all subscription
     *
     * @since 2.9.3
     *
     * @return object
     */
    public function get_subscription( $request ) {
        $params = $request->get_params();

        $args = apply_filters(
            'dokan_get_subscription_args', [
                'role__in'   => [ 'seller', 'administrator' ],
                'meta_query' => [
                    [
                        'key'   => 'can_post_product',
                        'value' => '1',
                    ],
                    [
                        'key'   => 'dokan_enable_selling',
                        'value' => 'yes',
                    ],
                ],
                'meta_key'   => 'product_pack_startdate',
                'orderby'    => 'meta_value, ID',
                'order'      => 'DESC',
            ]
        );

        if ( ! empty( $params['per_page'] ) ) {
            $args['number'] = $params['per_page'];
        }

        if ( ! empty( $params['offset'] ) ) {
            $args['offset'] = $params['offset'];
        }

        if ( ! empty( $params['orderby'] ) && $params['orderby'] === 'start_date' ) {
            $args['orderby']  = 'meta_value, ID';
            $args['meta_key'] = 'product_pack_startdate';
        } elseif ( ! empty( $params['orderby'] ) ) {
            unset( $args['meta_key'] );
            $args['orderby'] = $params['orderby'];
        }

        if ( ! empty( $params['order'] ) ) {
            $args['order'] = $params['order'];
        }

        if ( ! empty( $params['search'] ) ) {
            $args['meta_query'][] = [
                'key'     => 'dokan_store_name',
                'value'   => $params['search'],
                'compare' => 'LIKE',
            ];
        }

        if ( ! empty( $params['paged'] ) ) {
            $args['paged'] = $params['paged'];
        }

        if ( ! empty( $params['vendor_id'] ) ) {
            $args['include'] = (array) $params['vendor_id'];
        }

        if ( ! empty( $params['pack_id'] ) ) {
            $args['meta_query'][] = [
                'key'   => 'product_package_id',
                'value' => $params['pack_id'],
            ];
        }

        $user_query  = new WP_User_Query( $args );
        $users       = $user_query->get_results();
        $total_users = $user_query->get_total();

        if ( ! $users ) {
            return new WP_Error( 'no_subscription', __( 'No subscription found.', 'dokan' ), [ 'status' => 200 ] );
        }

        $data = [];

        foreach ( $users as $user ) {
            $temp_data = $this->prepare_item_for_response( $user, $request );
            if ( ! is_wp_error( $temp_data ) ) {
                $data[] = $temp_data;
            }
        }

        $response = rest_ensure_response( $data );

        return $this->format_collection_response( $response, $request, $total_users );
    }

    /**
     * Get subscription packages
     *
     * Give paginated results and search by subscription title
     *
     * @since 3.3.7
     *
     * @return object|WP_Error
     */
    public function get_subscription_packages( $request ) {
        global $wpdb;
        $params = $request->get_params();

        $args = [
            'posts_per_page' => $params['per_page'],
            'offset'         => ( $params['page'] - 1 ) * $params['per_page'],
            'post_status'    => [ 'publish', 'draft' ],
        ];

        if ( ! empty( $params['search'] ) ) {
            $args['s']              = $wpdb->esc_like( $params['search'] );
            $args['search_columns'] = [ 'post_title' ];
        }

        $query                 = ( new SubscriptionPack() )->all( $args );
        $total_packages        = $query->found_posts;
        $packages              = array();

        if ( ! $total_packages ) {
            return new WP_Error( 'no_subscription_pack', __( 'No subscription package found.', 'dokan' ), [ 'status' => 200 ] );
        }

        foreach ( $query->get_posts() as $package ) {
            array_push(
                $packages,
                array(
                    'id' => $package->ID,
                    'title' => $package->post_title,
                )
            );
        }

        $response = rest_ensure_response( $packages );

        return $this->format_collection_response( $response, $request, $total_packages );
    }

    /**
     * Get nonrecurring subscription packages
     *
     * @since 3.3.1
     *
     * @return WP_REST_Response|WP_Error
     */
    public function get_nonrecurring_subscription_packages( $request ) {
        $params                = $request->get_params();
        $subscription_packages = ( new SubscriptionPack() )->get_nonrecurring_packages();
        $total_packages        = count( $subscription_packages );
        $packages              = array();

        if ( ! $total_packages ) {
            return new WP_Error( 'no_subscription_pack', __( 'No subscription package found.', 'dokan' ), [ 'status' => 200 ] );
        }

        foreach ( $subscription_packages as $package ) {
            array_push(
                $packages,
                array(
                    'name'  => $package->ID,
                    'label' => $package->post_title,
                )
            );
        }

        $response = rest_ensure_response( $packages );
        $response = $this->format_collection_response( $response, $request, $total_packages );

        return $response;
    }

    /**
     * Get currently activated subscription for a vendor.
     *
     * @since 3.3.1
     *
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_HTTP_Response|WP_REST_Response
     */
    public function get_active_subscription_for_vendor( $request ) {
        $vendor_id           = (int) $request->get_param( 'id' );
        $users_assigned_pack = get_user_meta( $vendor_id, 'product_package_id', true );

        if ( ! $users_assigned_pack ) {
            $response = array(
                'name'  => 0,
                'label' => __( '-- Select a package --', 'dokan' ),
            );
        } else {
            $response = array(
                'name'  => $users_assigned_pack,
                'label' => get_the_title( $users_assigned_pack ),
            );
        }

        return rest_ensure_response( $response );
    }

    /**
     * Update subscription
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response
     */
    public function update_subscription( $request ) {
        $user_id            = (int) $request->get_param( 'id' );
        $action             = $request->get_param( 'action' );
        $status             = get_terms( 'shop_order_status' );
        $cancel_immediately = dokan_validate_boolean( $request->get_param( 'immediately' ) );

        if ( is_wp_error( $status ) ) {
            register_taxonomy( 'shop_order_status', array( 'shop_order' ), array( 'rewrite' => false ) );
        }

        $order_id = get_user_meta( $user_id, 'product_order_id', true );
        $vendor   = dokan()->vendor->get( $user_id )->subscription;
        $user     = new \WP_User( $user_id );

        if ( ! $order_id || ! $vendor ) {
            return new WP_Error( 'no_subscription', __( 'No subscription is found to be updated.', 'dokan' ), [ 'status' => 200 ] );
        }

        if ( 'activate' === $action ) {
            if ( $vendor->has_recurring_pack() && $vendor->has_active_cancelled_subscrption() ) {
                Helper::log( 'Subscription re-activattion check: Admin has re-activate Subscription of User #' . $user_id . ' on order #' . $order_id );
                do_action( 'dps_activate_recurring_subscription', $order_id, $user_id );
            }

            if ( ! $vendor->has_recurring_pack() ) {
                Helper::log( 'Subscription re-activattion check: Admin has re-activate Subscription of User #' . $user_id . ' on order #' . $order_id );
                do_action( 'dps_activate_non_recurring_subscription', $order_id, $user_id );
            }
        }

        if ( 'cancel' === $action ) {
            if ( $vendor->has_recurring_pack() ) {
                Helper::log( 'Subscription cancellation check: Admin has canceled Subscription of User #' . $user_id . ' on order #' . $order_id );
                do_action( 'dps_cancel_recurring_subscription', $order_id, $user_id, $cancel_immediately );
            } elseif ( ! $vendor->has_recurring_pack() ) {
                Helper::log( 'Subscription cancellation check: Admin has canceled Subscription of User #' . $user_id . ' on order #' . $order_id );
                do_action( 'dps_cancel_non_recurring_subscription', $order_id, $user_id, $cancel_immediately );
            }
        }

        $response = $this->prepare_item_for_response( $user, $request );
        $response = rest_ensure_response( $response );

        return $response;
    }

    /**
     * Batch update subscription
     *
     * @param \WP_REST_Request $request
     *
     * @return \WP_REST_Response
     */
    public function batch_update( $request ) {
        $action   = ! empty( $request['action'] ) ? $request['action'] : '';
        $user_ids = ! empty( $request['user_ids'] ) ? $request['user_ids'] : '';

        if ( ! $user_ids ) {
            return new WP_Error( 'no_subscription', __( 'No subscription is found to be updated.', 'dokan' ), [ 'status' => 200 ] );
        }

        $status = get_terms( 'shop_order_status' );

        if ( is_wp_error( $status ) ) {
            register_taxonomy( 'shop_order_status', array( 'shop_order' ), array( 'rewrite' => false ) );
        }

        foreach ( $user_ids as $user_id ) {
            $order_id = get_user_meta( $user_id, 'product_order_id', true );

            if ( ! $order_id ) {
                return new WP_Error( 'no_subscription', __( 'No subscription is found to be updated.', 'dokan' ), [ 'status' => 200 ] );
            }

            $vendor = dokan()->vendor->get( $user_id )->subscription;

            if ( 'activate' === $action ) {
                if ( $vendor->has_recurring_pack() && $vendor->has_active_cancelled_subscrption() ) {
                    Helper::log( 'Subscription activation check: Admin has activated Subscription of User #' . $user_id . ' on order #' . $order_id );
                    do_action( 'dps_activate_recurring_subscription', $order_id, $user_id );
                }
            }

            if ( 'cancel' === $action ) {
                if ( $vendor->has_recurring_pack() && ! $vendor->has_active_cancelled_subscrption() ) {
                    $cancel_immediately = false;
                    Helper::log( 'Subscription cancellation check: Admin has canceled Subscription of User #' . $user_id . ' on order #' . $order_id );
                    do_action( 'dps_cancel_recurring_subscription', $order_id, $user_id, $cancel_immediately );
                } elseif ( ! $vendor->has_recurring_pack() ) {
                    $cancel_immediately = true;
                    Helper::log( 'Subscription cancellation check: Admin has canceled Subscription of User #' . $user_id . ' on order #' . $order_id );
                    do_action( 'dps_cancel_non_recurring_subscription', $order_id, $user_id, $cancel_immediately );
                }
            }
        }

        $response = rest_ensure_response( $user_ids );

        return $response;
    }

    /**
     * Prepare a single sinle subscription output for response.
     *
     * @param Object $user
     * @param WP_REST_Request $request Request object.
     *
     * @return WP_REST_Response $response Response data.
     */
    public function prepare_item_for_response( $user, $request ) {
        /**
         * @var SubscriptionPack $subscription
         * @var \WeDevs\Dokan\Vendor\Vendor $seller
         */
        $seller       = dokan()->vendor->get( $user->ID );
        $subscription = $seller->subscription;

        if ( ! $subscription ) {
            return new WP_Error( 'no_subscription', __( 'No subscription is found to be deleted.', 'dokan' ), [ 'status' => 200 ] );
        }

        $end_date   = $subscription->get_pack_end_date();
        $order_id   = get_user_meta( $user->ID, 'product_order_id', true );

        $data = [
            'id'                       => $user->ID,
            'store_name'               => $seller->get_shop_name(),
            'order_link'               => get_edit_post_link( $order_id, false ),
            'order_id'                 => $order_id,
            'subscription_id'          => $subscription->get_id(),
            'subscription_title'       => $subscription->get_package_title(),
            'is_on_trial'              => $subscription->is_on_trial(),
            'subscription_trial_until' => $subscription->get_trial_end_date(),
            'start_date'               => dokan_format_date( $subscription->get_pack_start_date() ),
            'end_date'                 => 'unlimited' === $end_date ? __( 'Unlimited', 'dokan' ) : dokan_format_date( $end_date ),
            'current_date'             => dokan_format_date(),
            'status'                   => $subscription->has_subscription(),
            'is_recurring'             => $subscription->has_recurring_pack(),
            'has_active_cancelled_sub' => $subscription->has_active_cancelled_subscrption(),
        ];

        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';
        $data    = $this->add_additional_fields_to_object( $data, $request );
        $data    = $this->filter_response_by_context( $data, $context );

        return apply_filters( 'dokan_rest_prepare_subscription', $data, $user, $request );
    }
}
