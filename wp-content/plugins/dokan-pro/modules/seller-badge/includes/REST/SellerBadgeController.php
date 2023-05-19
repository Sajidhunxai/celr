<?php

namespace WeDevs\DokanPro\Modules\SellerBadge\REST;

use WP_Error;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;
use WeDevs\Dokan\Abstracts\DokanRESTController;
use WeDevs\DokanPro\Modules\SellerBadge\Helper;
use WeDevs\DokanPro\Modules\SellerBadge\Manager;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

/**
 * Seller badge api controller
 *
 * @since 3.7.14
 */
class SellerBadgeController extends DokanRESTController {

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
    protected $base = 'seller-badge';

    /**
     * Register all routes related with seller badge.
     *
     * @since 3.7.14
     *
     * @return void
     */

    public function register_routes() {
        register_rest_route(
            $this->namespace, '/' . $this->base, [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_items' ],
                    'args'                => $this->get_seller_badge_collection_params(),
                    'permission_callback' => [ $this, 'get_items_permissions_check' ],
                ],
                [
                    'methods'             => WP_REST_Server::CREATABLE,
                    'callback'            => [ $this, 'create_item' ],
                    'permission_callback' => [ $this, 'create_item_permissions_check' ],
                    'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/', [
                'args'   => [
                    'id' => [
                        'description'       => __( 'Unique identifier for the object.', 'dokan' ),
                        'type'              => 'integer',
                        'sanitize_callback' => 'absint',
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_item' ],
                    'permission_callback' => [ $this, 'get_item_permissions_check' ],
                    'args'                => $this->get_single_seller_badge_collection_params(),
                ],
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'update_item' ],
                    'args'                => $this->get_endpoint_args_for_item_schema( WP_REST_Server::CREATABLE ),
                    'permission_callback' => [ $this, 'update_item_permissions_check' ],
                ],
                [
                    'methods'             => WP_REST_Server::DELETABLE,
                    'callback'            => [ $this, 'delete_item' ],
                    'permission_callback' => [ $this, 'delete_item_permissions_check' ],
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/row-actions', [
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'row_actions' ],
                    'args'                => $this->get_bulk_actions_collection_params(),
                    'permission_callback' => [ $this, 'row_actions_permissions_check' ],
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/bulk-actions', [
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'bulk_actions' ],
                    'args'                => $this->get_bulk_actions_collection_params(),
                    'permission_callback' => [ $this, 'bulk_actions_permissions_check' ],
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/events', [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_events' ],
                    'permission_callback' => [ $this, 'get_events_permission_check' ],
                ],
                'schema' => [ $this, 'get_events_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/vendor-unseen-badges/', [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_vendor_unseen_badges' ],
                    'args'                => $this->get_single_seller_badge_collection_params(),
                    'permission_callback' => [ $this, 'get_vendor_unseen_badges_permissions_check' ],
                ],
                'schema' => [ $this, 'get_item_schema' ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/set-badge-as-seen/', [
                [
                    'methods'             => WP_REST_Server::EDITABLE,
                    'callback'            => [ $this, 'set_badge_as_seen' ],
                    'args'                => $this->get_badge_as_seen_collection_params(),
                    'permission_callback' => [ $this, 'get_set_badge_as_seen_permissions_check' ],
                ],
            ]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/verification-types', [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_vendor_verification_types' ],
                    'permission_callback' => [ $this, 'get_vendor_verification_types_permission_check' ],
                ],
                'schema' => [ $this, 'get_verification_types_schema' ],
            ]
        );
    }

    /**
     * Checks if a given request has access to get items.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return bool True if the request has read access, WP_Error object otherwise.
     */
    public function get_items_permissions_check( $request ) {
        return is_user_logged_in() && dokan_is_user_seller( dokan_get_current_user_id() );
    }

    /**
     * Get all seller badges.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function get_items( $request ) {
        $manager      = new Manager();
        $query_params = $request->get_params();
        $items        = [];

        if ( ! current_user_can( dokana_admin_menu_capability() ) || $request->get_param( 'is_frontend' ) ) {
            $query_params['vendor_id']    = dokan_get_current_user_id();
            $query_params['badge_status'] = 'published';
        }

        // get item count
        $count = $manager->get_badge_count( $query_params );

        if ( is_wp_error( $count ) ) {
            rest_ensure_request( $count );
        }

        // only run query if count value is greater than 0
        if ( $count->all > 0 ) {
            $items = $manager->get_seller_badges( $query_params );
            if ( is_wp_error( $items ) ) {
                return rest_ensure_response( $items );
            }
        }

        // check if got some results from database
        if ( ! empty( $items ) ) {
            $data = [];
            foreach ( $items as $item ) {
                $item   = $this->prepare_item_for_response( $item, $request );
                $data[] = $this->prepare_response_for_collection( $item );
            }
            $items = $data;
            unset( $data );
        }

        // get all status
        $filtered_count = $count->all;
        if ( $query_params['badge_status'] === 'published' ) {
            $filtered_count = $count->published;
        } elseif ( $query_params['badge_status'] === 'draft' ) {
            $filtered_count = $count->draft;
        }

        $response = rest_ensure_response( $items );
        $response->header( 'X-Status-All', $count->all );
        $response->header( 'X-Status-Published', $count->published );
        $response->header( 'X-Status-Draft', $count->draft );
        $response = $this->format_collection_response( $response, $request, $filtered_count );

        return $response;
    }

    /**
     * Checks if a given request has access to get a specific item.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return bool True if the request has read access for the item, false otherwise.
     */
    public function get_item_permissions_check( $request ) {
        return is_user_logged_in() && dokan_is_user_seller( dokan_get_current_user_id() );
    }

    /**
     * Retrieves one item from the collection.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_item( $request ) {
        $badge_id = absint( $request->get_param( 'id' ) );

        if ( empty( $badge_id ) ) {
            return new WP_Error( 'invalid_badge_id', __( 'Please provide a valid badge id', 'dokan' ), [ 'status' => 404 ] );
        }

        $args = [
            'badge_id'    => $badge_id,
            'with_levels' => true,
            'vendor_id'   => $request->get_param( 'vendor_id' ),
        ];

        if (
            ! current_user_can( dokana_admin_menu_capability() ) ||
            ( $request->get_param( 'is_frontend' ) && empty( $args['vendor_id'] ) )
        ) {
            $args['vendor_id']    = dokan_get_current_user_id();
            $args['badge_status'] = 'published';
        }

        $manager       = new Manager();
        $badge_details = $manager->get_badge( $args );
        if ( is_wp_error( $badge_details ) ) {
            return rest_ensure_response( $badge_details );
        }

        $acquired_badge_levels = [];
        if ( ! empty( $args['vendor_id'] ) ) {
            $acquired_badge_levels = $manager->get_vendor_acquired_levels_by_badge_id( $args['vendor_id'], $badge_id, 'published' );
            if ( is_wp_error( $acquired_badge_levels ) ) {
                $acquired_badge_levels = [];
            }
        }

        $response = $this->prepare_item_for_response( $badge_details, $request, $acquired_badge_levels );
        $response = $this->prepare_response_for_collection( $response );

        return rest_ensure_response( $response );
    }

    /**
     * Checks if a given request has access to update a specific item.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return bool True if the request has access to update the item, false otherwise.
     */
    public function create_item_permissions_check( $request ) {
        return current_user_can( dokana_admin_menu_capability() );
    }

    /**
     * Get all seller badge events.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function create_item( $request ) {
        //return rest_ensure_response( $request->get_params() );
        $manager = new Manager();
        // create badge
        $badge_data = $manager->create_seller_badge( $request->get_params() );

        return rest_ensure_response( $badge_data );
    }

    /**
     * Checks if a given request has access to delete a specific item.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return true True if the request has access to delete the item, false otherwise.
     */
    public function delete_item_permissions_check( $request ) {
        return current_user_can( dokana_admin_menu_capability() );
    }

    /**
     * Deletes one item from the collection.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function delete_item( $request ) {
        $badge_id = absint( $request->get_param( 'id' ) );

        if ( empty( $badge_id ) ) {
            return new WP_Error( 'invalid_badge_id', __( 'Please provide a valid badge id', 'dokan' ), [ 'status' => 404 ] );
        }

        $manager = new Manager();
        $deleted = $manager->delete_badges( $badge_id );
        if ( is_wp_error( $deleted ) ) {
            return $deleted;
        }

        return rest_ensure_response( $deleted );
    }

    /**
     * Checks if a given request has access to process bulk actions.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return bool True if the request has read access, false otherwise.
     */
    public function bulk_actions_permissions_check( $request ) {
        return current_user_can( dokana_admin_menu_capability() );
    }

    /**
     * Process bulk actions
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request
     *
     * @return bool|WP_Error
     */
    public function bulk_actions( $request ) {
        $action  = $request->get_param( 'action' );
        $ids     = $request->get_param( 'ids' );
        $manager = new Manager();
        $errors  = [];
        $data    = [];

        switch ( $action ) {
            case 'publish':
            case 'draft':
                foreach ( $ids as $badge_id ) {
                    $args     = [
                        'id'           => $badge_id,
                        'badge_status' => $action === 'publish' ? 'published' : 'draft',
                    ];
                    $response = $manager->update_seller_badge( $args );
                    if ( is_wp_error( $response ) ) {
                        $errors[] = $response;
                    } else {
                        $data[] = $response;
                    }
                }
                break;

            case 'delete':
                $data = $manager->delete_badges( $ids );
                if ( is_wp_error( $data ) ) {
                    return $data;
                }
                break;

            default:
                return new WP_Error( 'invalid_bulk_action', __( 'Please provide a valid action name.', 'dokan' ) );
        }

        if ( ! empty( $errors ) ) {
            $error_string = '';
            foreach ( $errors as $error ) {
                $error_string .= "<p>{$error->get_error_message()}</p>";
            }

            $data['error'] = new WP_Error( 'batch_update_error', $error_string );
        }

        return rest_ensure_response( $data );
    }

    /**
     * Checks if a given request has access to process bulk actions.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return bool True if the request has read access, false otherwise.
     */
    public function row_actions_permissions_check( $request ) {
        return current_user_can( dokana_admin_menu_capability() );
    }

    /**
     * Process bulk actions
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request
     *
     * @return bool|WP_Error
     */
    public function row_actions( $request ) {
        $action   = $request->get_param( 'action' );
        $badge_id = $request->get_param( 'ids' );
        $manager  = new Manager();
        // fix badge id
        if ( is_array( $badge_id ) ) {
            $badge_id = $badge_id[0];
        }

        switch ( $action ) {
            case 'publish':
            case 'draft':
                $args = [
                    'id'           => $badge_id,
                    'badge_status' => $action === 'publish' ? 'published' : 'draft',
                ];
                $data = $manager->update_seller_badge( $args );
                if ( is_wp_error( $data ) ) {
                    return $data;
                }
                break;

            case 'delete':
                $data = $manager->delete_badges( $badge_id );
                if ( is_wp_error( $data ) ) {
                    return $data;
                }
                break;

            default:
                return new WP_Error( 'invalid_bulk_action', __( 'Please provide a valid action name.', 'dokan' ) );
        }

        return rest_ensure_response( $data );
    }

    /**
     * Checks if a given request has access to update a specific item.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return bool True if the request has access to update the item, false otherwise.
     */
    public function get_events_permission_check( $request ) {
        return current_user_can( dokana_admin_menu_capability() );
    }

    /**
     * Get all seller badge events.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_events( $request ) {
        $manager      = new Manager();
        $badge_events = Helper::get_dokan_seller_badge_events( '', true );
        $data         = [];

        $created_seller_badges = $manager->get_all_seller_badges();
        if ( is_wp_error( $created_seller_badges ) ) {
            return $created_seller_badges;
        }
        // format seller_badges data
        $seller_badge_events = [];
        foreach ( $created_seller_badges as $badge ) {
            $seller_badge_events[ $badge->event_type ] = $badge;
        }

        foreach ( $badge_events as $event_id ) {
            $event = Helper::get_dokan_seller_badge_events( $event_id );
            if ( is_wp_error( $event ) ) {
                return $event;
            }

            if ( array_key_exists( $event_id, $seller_badge_events ) ) {
                $event
                    ->set_created( true )
                    ->set_status( $seller_badge_events[ $event_id ]->badge_status );
            }

            $data[] = $event->get_data();
        }

        return rest_ensure_response( $data );
    }

    /**
     * Checks if a given request has access to update a specific item.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return bool True if the request has access to update the item, false otherwise.
     */
    public function get_vendor_verification_types_permission_check( $request ) {
        return is_user_logged_in();
    }

    /**
     * Checks if a given request has access to update a specific item.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function get_vendor_verification_types( $request ) {
        $verification_types = [];

        $verification_types['id_verification'] = [
            'id'       => 'id_verification',
            'title'    => __( 'ID Verification', 'dokan' ),
            'disabled' => false,
        ];

        $verification_types['company_verification'] = [
            'id'       => 'company_verification',
            'title'    => __( 'Company Verification', 'dokan' ),
            'disabled' => false,
        ];

        $verification_types['address_verification'] = [
            'id'       => 'address_verification',
            'title'    => __( 'Address Verification', 'dokan' ),
            'disabled' => false,
        ];

        $verification_types['phone_verification'] = [
            'id'       => 'phone_verification',
            'title'    => __( 'Phone Verification', 'dokan' ),
            'disabled' => false,
        ];

        $verification_types['social_profiles'] = [
            'id'       => 'social_profiles',
            'title'    => __( 'Social Profiles', 'dokan' ),
            'disabled' => false,
        ];

        return rest_ensure_response( $verification_types );
    }

    /**
     * Checks if a given request has access to update a specific item.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return bool True if the request has access to update the item, false otherwise.
     */
    public function update_item_permissions_check( $request ) {
        return current_user_can( dokana_admin_menu_capability() );
    }

    /**
     * Updates one item from the collection.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
     */
    public function update_item( $request ) {
        $manager = new Manager();
        // create badge
        $badge_data = $manager->update_seller_badge( $request->get_params() );

        return rest_ensure_response( $badge_data );
    }

    /**
     * Checks if a given request has access to get items.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return bool True if the request has read access, WP_Error object otherwise.
     */
    public function get_vendor_unseen_badges_permissions_check( $request ) {
        return dokan_is_user_seller( dokan_get_current_user_id() );
    }

    /**
     * Get vendors unseen badges with levels.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function get_vendor_unseen_badges( $request ) {
        $vendor_id = $request->get_param( 'vendor_id' );
        if ( ! current_user_can( dokana_admin_menu_capability() ) || $request->get_param( 'is_frontend' ) ) {
            $vendor_id = dokan_get_current_user_id();
        }

        $manager = new Manager();
        $items   = $manager->get_unseen_badges_by_vendor( [ 'vendor_id' => $vendor_id ] );
        if ( is_wp_error( $items ) ) {
            return rest_ensure_response( $items );
        }

        // check if got some results from database
        if ( ! empty( $items ) ) {
            $data = [];
            foreach ( $items as $item ) {
                $item   = $this->prepare_item_for_response( $item, $request );
                $data[] = $this->prepare_response_for_collection( $item );
            }
            $items = $data;
            unset( $data );
        }

        $response = rest_ensure_response( $items );
        $response = $this->format_collection_response( $response, $request, count( $items ) );

        return $response;
    }

    /**
     * Checks if a given request has access to get items.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request Full details about the request.
     *
     * @return bool True if the request has read access, WP_Error object otherwise.
     */
    public function get_set_badge_as_seen_permissions_check( $request ) {
        return dokan_is_user_seller( dokan_get_current_user_id() );
    }

    /**
     * Get vendors unseen badges with levels.
     *
     * @since 3.7.14
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function set_badge_as_seen( $request ) {
        $vendor_id = $request->get_param( 'vendor_id' );

        if ( empty( $vendor_id ) ) {
            $vendor_id = get_current_user_id();
        }

        $manager = new Manager();
        $updated = $manager->set_badge_status_as_seen( $vendor_id );

        return rest_ensure_response( $updated );
    }

    /**
     * Prepare badge data for response
     *
     * @since 3.7.14
     *
     * @param object          $item
     * @param WP_REST_Request $request
     * @param object[]        $vendor_acquired_levels
     *
     * @return WP_Error|WP_REST_Response
     */
    public function prepare_item_for_response( $item, $request, $vendor_acquired_levels = [] ) {
        $event = Helper::get_dokan_seller_badge_events( $item->event_type );

        if ( empty( $item ) || ! is_object( $item ) ) {
            return new WP_Error( 'invalid_badge_data', __( 'Invalid badge data provided. Please check your api request data.', 'dokan' ) );
        }

        if ( is_wp_error( $event ) ) {
            return new WP_Error( 'invalid_event_id', __( 'Badge event id is invalid. Please check your api request.', 'dokan' ) );
        }

        // setting badge logo via model is important at this point, since this url can come from two different places
        $default_logo = $event->get_badge_logo();
        $event->set_badge_logo( $item->badge_logo );

        $data = [
            'id'                     => absint( $item->id ),
            'badge_name'             => $item->badge_name,
            'badge_logo'             => $event->get_formatted_badge_logo(),
            'badge_logo_raw'         => $event->get_badge_logo(),
            'default_logo'           => $default_logo,
            'formatted_default_logo' => DOKAN_SELLER_BADGE_ASSETS . '/images/badges/' . $default_logo,
            'event_type'             => $event->get_event_id(),
            'formatted_hover_text'   => $event->get_formatted_hover_text( $item ),
            'event'                  => $event->get_data(),
            'badge_status'           => $item->badge_status,
            'formatted_badge_status' => Helper::get_formatted_event_status( $item->badge_status ),
            'level_count'            => absint( $item->level_count ),
            'vendor_count'           => absint( $item->vendor_count ),
            'acquired_level_count'   => $this->get_acquired_level_count( $item, $request ),
            'created_by'             => absint( $item->created_by ),
            'created_at'             => dokan_format_date( $item->created_at ),
            'levels'                 => [],
        ];

        if ( ! empty( $item->levels ) ) {
            $formatted_levels = [];
            foreach ( $item->levels as $index => $level ) {
                $formatted_level = $this->prepare_response_for_badge_level( $level, $request, $index );
                if ( is_wp_error( $formatted_level ) ) {
                    return $formatted_level;
                }
                $formatted_levels[] = $formatted_level;
            }
            $data['levels'] = $formatted_levels;
        }

        $vendor_acquired_levels = empty( $vendor_acquired_levels ) && isset( $item->acquired ) ? $item->acquired : $vendor_acquired_levels;
        if ( ! empty( $vendor_acquired_levels ) ) {
            $formatted_levels = [];
            foreach ( $vendor_acquired_levels as $index => $level ) {
                $formatted_level = $this->prepare_response_for_acquired_badge_level( $level, $request, $index );
                if ( is_wp_error( $formatted_level ) ) {
                    return $formatted_level;
                }
                $formatted_levels[ $level->level_id ] = $formatted_level;
            }
            $data['acquired'] = $formatted_levels;
        }

        $response = rest_ensure_response( $data );
        $response->add_links( $this->prepare_links( $item, $request ) );

        return apply_filters( 'dokan_rest_prepare_seller_badge_object', $response, $item, $request );
    }

    /**
     * Prepare badge data for response
     *
     * @since 3.7.14
     *
     * @param object          $item
     * @param WP_REST_Request $request
     *
     * @return int
     */
    private function get_acquired_level_count( $item, $request ) {
        if ( 'years_active' !== $item->event_type ) {
            return absint( $item->acquired_level_count );
        }

        $request_params = $request->get_params();
        $vendor_id      = ! empty( $request_params['vendor_id'] ) ? $request_params['vendor_id'] : ( ! empty( $request_params['is_frontend'] ) ? get_current_user_id() : 0 );
        if ( ! $vendor_id ) {
            return absint( $item->acquired_level_count );
        }

        return absint( Helper::get_vendor_year_count( $vendor_id ) );
    }

    /**
     * Prepare data for level object.
     *
     * @since 3.7.14
     *
     * @param object          $level
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response[]
     */
    public function prepare_response_for_badge_level( $level, $request, $index ) {
        if ( empty( $level ) || ! is_object( $level ) ) {
            return new WP_Error( 'invalid_level_data', __( 'Invalid level data. Please check your request parameters.' ) );
        }

        $data = [
            'id'                  => absint( $level->id ),
            'badge_id'            => absint( $level->badge_id ),
            'level'               => absint( $level->level ),
            'level_condition'     => $level->level_condition,
            'formatted_condition' => Helper::get_condition_data( $level->level_condition ),
            'level_data'          => $level->level_data,
            'vendor_count'        => absint( $level->vendor_count ),
        ];

        return apply_filters( 'dokan_rest_prepare_seller_badge_object', $data, $level, $request );
    }

    /**
     * Prepare data for level object.
     *
     * @since 3.7.14
     *
     * @param object          $level
     * @param WP_REST_Request $request
     *
     * @return WP_Error|WP_REST_Response[]
     */
    public function prepare_response_for_acquired_badge_level( $level, $request, $index ) {
        if ( empty( $level ) || ! is_object( $level ) ) {
            return new WP_Error( 'invalid_level_data', __( 'Invalid level data. Please check your request parameters.' ) );
        }

        $data = [
            'id'                        => absint( $level->id ),
            'vendor_id'                 => absint( $level->vendor_id ),
            'level_id'                  => absint( $level->level_id ),
            'acquired_data'             => sanitize_text_field( $level->acquired_data ),
            'acquired_status'           => sanitize_text_field( $level->acquired_status ),
            'formatted_acquired_status' => Helper::get_formatted_event_status( $level->acquired_status ),
            'badge_seen'                => absint( $level->badge_seen ),
            'created_at'                => absint( $level->created_at ),
            'formatted_created_at'      => dokan_format_date( $level->created_at ),
        ];

        return apply_filters( 'dokan_rest_prepare_seller_badge_acquired_object', $data, $level, $request );
    }

    /**
     * Prepare links for the request.
     *
     * @param mixed           $object  Object data.
     * @param WP_REST_Request $request Request object.
     *
     * @return array                   Links for the given post.
     */
    protected function prepare_links( $object, $request ) {
        return [
            'self'       => [
                'href' => rest_url( sprintf( '/%s/%s/%d', $this->namespace, $this->base, $object->id ) ),
            ],
            'collection' => [
                'href' => rest_url( sprintf( '/%s/%s', $this->namespace, $this->base ) ),
            ],
        ];
    }

    /**
     * This method will verify per page item value, will be used only with rest api validate callback
     *
     * @since 3.7.14
     *
     * @param $value
     * @param $request WP_REST_Request
     * @param $key
     *
     * @return bool|WP_Error
     */
    public function validate_per_page( $value, $request, $key ) {
        $attributes = $request->get_attributes();

        if ( isset( $attributes['args'][ $key ] ) ) {
            $argument = $attributes['args'][ $key ];
            // Check to make sure our argument is an int.
            if ( 'integer' === $argument['type'] && ! is_numeric( $value ) ) {
                // translators: 1) argument name, 2) argument value
                return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%1$s is not of type %2$s', 'dokan' ), $key, 'integer' ), [ 'status' => 400 ] );
            }
        } else {
            // This code won't execute because we have specified this argument as required.
            // If we reused this validation callback and did not have required args then this would fire.
            // translators: 1) argument name
            return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( '%s was not registered as a request argument.', 'dokan' ), $key ), [ 'status' => 400 ] );
        }

        if ( - 1 === intval( $value ) || $value > 0 ) {
            return true;
        }

        // translators: 1) rest api endpoint key name
        return new WP_Error( 'rest_invalid_param', sprintf( esc_html__( 'Accepted value for %1$s is -1 or non-zero positive integer', 'dokan' ), $key ), [ 'status' => 400 ] );
    }

    /**
     * Get seller badge collection params.
     *
     * @since 3.7.14
     *
     * @return array
     */
    public function get_seller_badge_collection_params() {
        $context            = $this->get_context_param();
        $context['default'] = 'view';

        return [
            'context'      => $context,
            'badge_id'     => [
                'description'       => __( 'Get badge by badge id', 'dokan' ),
                'type'              => 'array',
                'default'           => [],
                'validate_callback' => 'rest_validate_request_arg',
                'items'             => [
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                    'minimum'           => 1,
                ],
            ],
            'vendor_id'    => [
                'description'       => __( 'Filter badge by vendor id', 'dokan' ),
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'minimum'           => 1,
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'badge_name'   => [
                'description'       => __( 'Filter by badge name', 'dokan' ),
                'type'              => 'string',
                'default'           => '',
                'sanitize_callback' => 'sanitize_text_field',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'event_type'   => [
                'description'       => __( 'Filter by badge event type', 'dokan' ),
                'type'              => 'string',
                'enum'              => Helper::get_dokan_seller_badge_events( '', true ),
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'badge_status' => [
                'description'       => __( 'Filter by badge status', 'dokan' ),
                'type'              => 'string',
                'enum'              => [ 'all', 'published', 'draft' ],
                'context'           => [ 'view' ],
                'default'           => 'all',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'order'        => [
                'description'       => __( 'Badge status', 'dokan' ),
                'type'              => 'string',
                'enum'              => [ 'asc', 'ASC', 'desc', 'DESC' ],
                'default'           => 'asc',
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'orderby'      => [
                'description'       => __( 'Badge status', 'dokan' ),
                'type'              => 'string',
                'enum'              => [ 'badge_id', 'badge_name', 'badge_status', 'badge_created', 'event_type', 'vendor_id', 'vendor_count', ],
                'validate_callback' => 'rest_validate_request_arg',
                'default'           => 'badge_id',
            ],
            'page'         => [
                'description'       => __( 'Current page of the collection.' ),
                'type'              => 'integer',
                'default'           => 1,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
                'minimum'           => 1,
            ],
            'per_page'     => [
                'description'       => __( 'Maximum number of items to be returned in result set.', 'dokan' ),
                'type'              => 'integer',
                'default'           => 10,
                'minimum'           => - 1,
                'maximum'           => 100,
                'validate_callback' => [ $this, 'validate_per_page' ],
            ],
            'return'       => [
                'description' => __( 'How data will be returned', 'dokan' ),
                'type'        => 'string',
                'enum'        => [ 'all', 'badge_count', 'badge' ],
                'default'     => 'all',
            ],
            'is_frontend'  => [
                'description' => __( 'Is this api called from frontend? If vendor_id param is set, this parameter is no use.', 'dokan' ),
                'type'        => 'boolean',
                'default'     => false,
            ],
        ];
    }

    /**
     * Get seller badge collection params.
     *
     * @since 3.7.14
     *
     * @return array
     */
    public function get_single_seller_badge_collection_params() {
        $context            = $this->get_context_param();
        $context['default'] = 'view';

        return [
            'context'     => $context,
            'vendor_id'   => [
                'description'       => __( 'Filter badge by vendor id', 'dokan' ),
                'type'              => 'integer',
                'sanitize_callback' => 'absint',
                'minimum'           => 1,
                'validate_callback' => 'rest_validate_request_arg',
            ],
            'is_frontend' => [
                'description' => __( 'Is this api called from frontend? If vendor_id param is set, this parameter is no use.', 'dokan' ),
                'type'        => 'boolean',
                'default'     => false,
            ],
        ];
    }

    /**
     * Get seller badge collection params.
     *
     * @since 3.7.14
     *
     * @return array
     */
    public function get_badge_as_seen_collection_params() {
        $context            = $this->get_context_param();
        $context['default'] = 'edit';

        return [
            'context'   => $context,
            'vendor_id' => [
                'description'       => __( 'Filter badge by vendor id', 'dokan' ),
                'type'              => 'integer',
                'default'           => get_current_user_id(),
                'minimum'           => 1,
                'sanitize_callback' => 'absint',
                'validate_callback' => 'rest_validate_request_arg',
            ],
        ];
    }

    /**
     * Schema for batch processing/bulk actions
     *
     * @since 3.7.14
     *
     * @return array
     */
    public function get_bulk_actions_collection_params() {
        return [
            'context' => $this->get_context_param(),
            'action'  => [
                'required'    => true,
                'description' => __( 'Batch action name to process', 'dokan' ),
                'type'        => 'string',
                'enum'        => [ 'publish', 'draft', 'delete' ],
                'context'     => [ 'edit' ],
            ],
            'ids'     => [
                'required'    => true,
                'description' => __( 'Batch action to carry on advertisement items', 'dokan' ),
                'type'        => 'array',
                'context'     => [ 'edit' ],
                'items'       => [
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                    'minimum'           => 1,
                ],
            ],
        ];
    }

    /**
     * Get the badge item schema, conforming to JSON Schema.
     *
     * @since 3.7.14
     *
     * @return array
     */
    public function get_item_schema() {
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => 'Seller Badge Schema',
            'type'       => 'object',
            'properties' => [
                'id'                     => [
                    'description'       => __( 'Unique identifier for the object', 'dokan' ),
                    'type'              => 'integer',
                    'context'           => [ 'view', 'edit' ],
                    'readonly'          => true,
                    'validate_callback' => 'rest_validate_request_arg',
                    'sanitize_callback' => 'absint',
                ],
                'badge_name'             => [
                    'description'       => __( 'Badge name', 'dokan' ),
                    'type'              => 'string',
                    'minLength'         => 2,
                    'default'           => '',
                    'context'           => [ 'view', 'edit' ],
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                ],
                'badge_logo'             => [
                    'description'       => __( 'Badge Logo', 'dokan' ),
                    'type'              => [ 'string' ],
                    'context'           => [ 'view' ],
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                ],
                'default_logo'           => [
                    'description'       => __( 'Default badge Logo', 'dokan' ),
                    'type'              => [ 'string' ],
                    'context'           => [ 'view' ],
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                ],
                'formatted_default_logo' => [
                    'description'       => __( 'Formatted default badge Logo', 'dokan' ),
                    'type'              => [ 'string' ],
                    'format'            => 'uri',
                    'context'           => [ 'view' ],
                    'sanitize_callback' => 'sanitize_url',
                    'validate_callback' => 'rest_validate_request_arg',
                ],
                'badge_logo_raw'         => [
                    'description'       => __( 'Badge Logo Raw, Attachment image id or default badge file name.', 'dokan' ),
                    'type'              => [ 'string', 'int' ],
                    'context'           => [ 'view', 'edit' ],
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                ],
                'event_type'             => [
                    'description'       => __( 'Badge event type', 'dokan' ),
                    'type'              => 'string',
                    'enum'              => Helper::get_dokan_seller_badge_events( '', true ),
                    'context'           => [ 'view', 'edit' ],
                    'required'          => true,
                    'validate_callback' => 'rest_validate_request_arg',
                ],
                'badge_status'           => [
                    'description'       => __( 'Badge status', 'dokan' ),
                    'type'              => 'string',
                    'enum'              => [ 'published', 'draft' ],
                    'context'           => [ 'view', 'edit' ],
                    'default'           => 'draft',
                    'validate_callback' => 'rest_validate_request_arg',
                ],
                'formatted_badge_status' => [
                    'description'       => __( 'Formatted Badge status', 'dokan' ),
                    'type'              => 'string',
                    'context'           => [ 'view' ],
                    'readonly'          => true,
                    'validate_callback' => 'rest_validate_request_arg',
                ],
                'formatted_hover_text'   => [
                    'description'       => __( 'Formatted Hover Text', 'dokan' ),
                    'type'              => 'string',
                    'context'           => [ 'view' ],
                    'readonly'          => true,
                    'validate_callback' => 'rest_validate_request_arg',
                ],
                'level_count'            => [
                    'description'       => __( 'Number of level available for this badge.', 'dokan' ),
                    'type'              => 'integer',
                    'context'           => [ 'view' ],
                    'readonly'          => true,
                    'validate_callback' => 'rest_validate_request_arg',
                    'sanitize_callback' => 'absint',
                ],
                'vendor_count'           => [
                    'description'       => __( 'Number of vendors acquired this badge.', 'dokan' ),
                    'type'              => 'integer',
                    'context'           => [ 'view' ],
                    'readonly'          => true,
                    'validate_callback' => 'rest_validate_request_arg',
                    'sanitize_callback' => 'absint',
                ],
                'acquired_level_count'   => [
                    'description'       => __( 'If vendor_id is set, number of level acquired for that vendor, otherwise total acquired levels by all vendors.', 'dokan' ),
                    'type'              => 'integer',
                    'context'           => [ 'view' ],
                    'readonly'          => true,
                    'validate_callback' => 'rest_validate_request_arg',
                    'sanitize_callback' => 'absint',
                ],
                'event'                  => [
                    'description' => __( 'Event data.', 'dokan' ),
                    'type'        => 'object',
                    'required'    => false,
                    'context'     => [ 'view' ],
                    'properties'  => [
                        'id'                  => [
                            'description'       => __( 'Unique identifier for the object', 'dokan' ),
                            'type'              => 'string',
                            'enum'              => Helper::get_dokan_seller_badge_events( '', true ),
                            'context'           => [ 'view' ],
                            'required'          => true,
                            'validate_callback' => 'rest_validate_request_arg',
                        ],
                        'title'               => [
                            'description'       => __( 'Event title', 'dokan' ),
                            'type'              => 'string',
                            'default'           => '',
                            'context'           => [ 'view' ],
                            'required'          => true,
                            'sanitize_callback' => 'sanitize_text_field',
                            'validate_callback' => 'rest_validate_request_arg',
                        ],
                        'description'         => [
                            'description'       => __( 'Event description', 'dokan' ),
                            'type'              => 'string',
                            'default'           => '',
                            'context'           => [ 'view' ],
                            'required'          => true,
                            'sanitize_callback' => 'sanitize_text_field',
                            'validate_callback' => 'rest_validate_request_arg',
                        ],
                        'condition_text'      => [
                            'description' => __( 'Event condition text', 'dokan' ),
                            'type'        => 'object',
                            'context'     => [ 'view' ],
                            'required'    => true,
                            'properties'  => [
                                'prefix' => [
                                    'description'       => __( 'Prefix', 'dokan' ),
                                    'type'              => 'string',
                                    'default'           => '',
                                    'context'           => [ 'view' ],
                                    'required'          => true,
                                    'sanitize_callback' => 'sanitize_text_field',
                                    'validate_callback' => 'rest_validate_request_arg',
                                ],
                                'suffix' => [
                                    'description'       => __( 'Suffix', 'dokan' ),
                                    'type'              => 'string',
                                    'default'           => '',
                                    'context'           => [ 'view' ],
                                    'required'          => true,
                                    'sanitize_callback' => 'sanitize_text_field',
                                    'validate_callback' => 'rest_validate_request_arg',
                                ],
                                'type'   => [
                                    'description'       => __( 'Suffix', 'dokan' ),
                                    'type'              => 'string',
                                    'default'           => '',
                                    'context'           => [ 'view' ],
                                    'required'          => true,
                                    'sanitize_callback' => 'sanitize_text_field',
                                    'validate_callback' => 'rest_validate_request_arg',
                                ],
                            ],
                        ],
                        'hover_text'          => [
                            'description'       => __( 'Event hover text', 'dokan' ),
                            'type'              => 'string',
                            'default'           => '',
                            'context'           => [ 'view' ],
                            'required'          => true,
                            'sanitize_callback' => 'sanitize_text_field',
                            'validate_callback' => 'rest_validate_request_arg',
                        ],
                        'group'               => [
                            'description' => __( 'Event group', 'dokan' ),
                            'type'        => 'object',
                            'context'     => [ 'view' ],
                            'required'    => true,
                            'properties'  => [
                                'key'   => [
                                    'description'       => __( 'Group Key', 'dokan' ),
                                    'type'              => 'string',
                                    'default'           => '',
                                    'context'           => [ 'view' ],
                                    'required'          => true,
                                    'sanitize_callback' => 'sanitize_text_field',
                                    'validate_callback' => 'rest_validate_request_arg',
                                ],
                                'title' => [
                                    'description'       => __( 'Group Title', 'dokan' ),
                                    'type'              => 'string',
                                    'default'           => '',
                                    'context'           => [ 'view' ],
                                    'required'          => true,
                                    'sanitize_callback' => 'sanitize_text_field',
                                    'validate_callback' => 'rest_validate_request_arg',
                                ],
                            ],
                        ],
                        'has_multiple_levels' => [
                            'description'       => __( 'Check if event supports multiple levels', 'dokan' ),
                            'type'              => 'boolean',
                            'default'           => false,
                            'context'           => [ 'view' ],
                            'required'          => true,
                            'sanitize_callback' => 'rest_sanitize_boolean',
                            'validate_callback' => 'rest_validate_request_arg',
                        ],
                        'badge_logo'          => [
                            'description'       => __( 'Event logo url', 'dokan' ),
                            'type'              => [ 'string' ],
                            'format'            => 'uri',
                            'minLength'         => 2,
                            'default'           => '',
                            'context'           => [ 'view' ],
                            'required'          => true,
                            'sanitize_callback' => 'esc_url_raw',
                            'validate_callback' => 'rest_validate_request_arg',
                        ],
                        'input_group_icon'    => [
                            'description' => __( 'Event icon css class', 'dokan' ),
                            'type'        => 'object',
                            'context'     => [ 'view' ],
                            'required'    => true,
                            'properties'  => [
                                'condition' => [
                                    'description'       => __( 'Level condition field input icon', 'dokan' ),
                                    'type'              => 'string',
                                    'default'           => '',
                                    'context'           => [ 'view' ],
                                    'required'          => true,
                                    'sanitize_callback' => 'sanitize_text_field',
                                    'validate_callback' => 'rest_validate_request_arg',
                                ],
                                'data'      => [
                                    'description'       => __( 'Level data input field input icon', 'dokan' ),
                                    'type'              => 'string',
                                    'default'           => '',
                                    'context'           => [ 'view' ],
                                    'required'          => true,
                                    'sanitize_callback' => 'sanitize_text_field',
                                    'validate_callback' => 'rest_validate_request_arg',
                                ],
                            ],
                        ],
                    ],
                ],
                'levels'                 => [
                    'description' => __( 'Badge level data.', 'dokan' ),
                    'type'        => 'array',
                    'context'     => [ 'view', 'edit' ],
                    'items'       => [
                        'type'       => 'object',
                        'properties' => [
                            'id'              => [
                                'description'       => __( 'Unique identifier for the object', 'dokan' ),
                                'type'              => 'integer',
                                'context'           => [ 'view', 'edit' ],
                                'readonly'          => true,
                                'validate_callback' => 'rest_validate_request_arg',
                                'sanitize_callback' => 'absint',
                            ],
                            'badge_id'        => [
                                'description'       => __( 'Badge id this level is associated with', 'dokan' ),
                                'type'              => 'integer',
                                'context'           => [ 'view', 'edit' ],
                                'validate_callback' => 'rest_validate_request_arg',
                                'sanitize_callback' => 'absint',
                            ],
                            'level'           => [
                                'description'       => __( 'level number', 'dokan' ),
                                'type'              => 'integer',
                                'context'           => [ 'view', 'edit' ],
                                'validate_callback' => 'rest_validate_request_arg',
                                'sanitize_callback' => 'absint',
                            ],
                            'level_condition' => [
                                'description'       => __( 'Applied level condition', 'dokan' ),
                                'type'              => 'string',
                                'default'           => '',
                                'context'           => [ 'view', 'edit' ],
                                'required'          => true,
                                'sanitize_callback' => 'sanitize_text_field',
                                'validate_callback' => 'rest_validate_request_arg',
                            ],
                            'level_data'      => [
                                'description'       => __( 'Level condition value', 'dokan' ),
                                'type'              => 'string',
                                'default'           => '',
                                'context'           => [ 'view', 'edit' ],
                                'sanitize_callback' => 'sanitize_text_field',
                                'validate_callback' => 'rest_validate_request_arg',
                            ],
                            'vendor_count'    => [
                                'description'       => __( 'No of vendors acquired this level', 'dokan' ),
                                'type'              => 'integer',
                                'context'           => [ 'view' ],
                                'readonly'          => true,
                                'validate_callback' => 'rest_validate_request_arg',
                                'sanitize_callback' => 'absint',
                            ],
                        ],
                    ],
                ],
                'acquired'               => [
                    'description' => __( 'Badge acquired data.', 'dokan' ),
                    'type'        => 'array',
                    'context'     => [ 'view', 'edit' ],
                    'items'       => [
                        'type'       => 'object',
                        'properties' => [
                            'id'                        => [
                                'description'       => __( 'Unique identifier for the object', 'dokan' ),
                                'type'              => 'integer',
                                'context'           => [ 'view' ],
                                'readonly'          => true,
                                'validate_callback' => 'rest_validate_request_arg',
                                'sanitize_callback' => 'absint',
                            ],
                            'vendor_id'                 => [
                                'description'       => __( 'Acquired vendor id', 'dokan' ),
                                'type'              => 'integer',
                                'context'           => [ 'view' ],
                                'validate_callback' => 'rest_validate_request_arg',
                                'sanitize_callback' => 'absint',
                            ],
                            'level_id'                  => [
                                'description'       => __( 'Acquired badge level id', 'dokan' ),
                                'type'              => 'integer',
                                'context'           => [ 'view' ],
                                'validate_callback' => 'rest_validate_request_arg',
                                'sanitize_callback' => 'absint',
                            ],
                            'acquired_data'             => [
                                'description'       => __( 'Value while acquiring this badge.', 'dokan' ),
                                'type'              => 'string',
                                'default'           => '',
                                'context'           => [ 'view' ],
                                'sanitize_callback' => 'sanitize_text_field',
                                'validate_callback' => 'rest_validate_request_arg',
                            ],
                            'acquired_status'           => [
                                'description'       => __( 'Acquired level status.', 'dokan' ),
                                'type'              => 'string',
                                'enum'              => [ 'published', 'draft' ],
                                'default'           => 'draft',
                                'context'           => [ 'view' ],
                                'sanitize_callback' => 'sanitize_text_field',
                                'validate_callback' => 'rest_validate_request_arg',
                            ],
                            'formatted_acquired_status' => [
                                'description'       => __( 'Formatted acquired level status.', 'dokan' ),
                                'type'              => 'string',
                                'enum'              => [ 'published', 'draft' ],
                                'default'           => 'draft',
                                'context'           => [ 'view' ],
                                'sanitize_callback' => 'sanitize_text_field',
                                'validate_callback' => 'rest_validate_request_arg',
                            ],
                            'badge_seen'                => [
                                'description'       => __( 'Badge level seen by vendor', 'dokan' ),
                                'type'              => 'integer',
                                'enum'              => [ 0, 1 ],
                                'context'           => [ 'view' ],
                                'validate_callback' => 'rest_validate_request_arg',
                                'sanitize_callback' => 'absint',
                            ],
                            'created_at'                => [
                                'description'       => __( 'Vendor level acquired date.', 'dokan' ),
                                'type'              => 'integer',
                                'context'           => [ 'view', 'edit' ],
                                'validate_callback' => 'rest_validate_request_arg',
                                'sanitize_callback' => 'absint',
                            ],
                            'formatted_created_at'      => [
                                'description'       => __( 'Formatted vendor level acquired date', 'dokan' ),
                                'type'              => 'string',
                                'default'           => '',
                                'context'           => [ 'view' ],
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

    /**
     * Get the badge event schema, conforming to JSON Schema
     *
     * @since 3.7.14
     *
     * @return array
     */
    public function get_events_schema() {
        $item_schema = $this->get_item_schema();
        $schema      = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => __( 'Badge event data.', 'dokan' ),
            'type'       => 'object',
            'properties' => $item_schema['properties']['event']['properties'],
        ];

        return $this->add_additional_fields_schema( $schema );
    }

    /**
     * Get the badge event schema, conforming to JSON Schema
     *
     * @since 3.7.14
     *
     * @return array
     */
    public function get_verification_types_schema() {
        $schema = [
            '$schema'    => 'http://json-schema.org/draft-04/schema#',
            'title'      => __( 'Vendor verification types.', 'dokan' ),
            'type'       => 'object',
            'properties' => [
                'id'    => [
                    'description'       => __( 'Unique identifier for the object', 'dokan' ),
                    'type'              => 'string',
                    'minLength'         => 2,
                    'context'           => [ 'view', ],
                    'readonly'          => true,
                    'validate_callback' => 'rest_validate_request_arg',
                    'sanitize_callback' => 'sanitize_text_field',
                ],
                'title' => [
                    'description'       => __( 'Verification type title', 'dokan' ),
                    'type'              => 'string',
                    'minLength'         => 2,
                    'default'           => '',
                    'context'           => [ 'view', ],
                    'required'          => true,
                    'sanitize_callback' => 'sanitize_text_field',
                    'validate_callback' => 'rest_validate_request_arg',
                ],
            ],
        ];

        return $this->add_additional_fields_schema( $schema );
    }
}
