<?php

namespace WeDevs\DokanPro\REST;

use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WeDevs\Dokan\Abstracts\DokanRESTController;
use WeDevs\DokanPro\Dashboard\ProfileProgress;

/**
 * Dashboard API controller
 *
 * @package dokan
 * @since 3.7.13
 *
 * @author weDevs
 */
class DashboardController extends DokanRESTController {

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
    protected $base = 'vendor-dashboard';

    /**
     * Register all routes related with dashboard.
     *
     * @since 3.7.13
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace, '/' . $this->base . '/profile-progressbar', [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_profile_progressbar' ],
                    'permission_callback' => [ $this, 'permission_check_to_get_profile_progressbar' ],
                    'args'                => [],
                ],
            ]
        );
    }

    /**
     * Check permission if current user can see profile progress bar data.
     *
     * Only seller will get the access to get profile progressbar.
     *
     * @since 3.7.13
     *
     * @return boolean
     */
    public function permission_check_to_get_profile_progressbar() {
        return dokan_is_user_seller( get_current_user_id() );
    }

    /**
     * Get profile progressbar data.
     *
     * @since 3.7.13
     *
     * @param WP_REST_Request $request
     *
     * @return WP_REST_Response
     */
    public function get_profile_progressbar( $request ) {
        $profile_progress = new ProfileProgress();

        return rest_ensure_response( $profile_progress->get( true ) );
    }
}
