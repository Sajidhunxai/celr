<?php

namespace WeDevs\DokanPro\Dashboard;

use WeDevs\Dokan\Cache;
use WeDevs\Dokan\Dashboard\Templates\Dashboard as DokanDashboard;

/**
 * Dashboard Template Class.
 *
 * A template for frontend dashboard rendering items
 *
 * @since 2.4
 *
 * @author weDevs <info@wedevs.com>
 */
class Dashboard extends DokanDashboard {

    /**
     * Constructor for the WeDevs_Dokan class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses add_action()
     */
    public function __construct() {
        $this->user_id = dokan_get_current_user_id();

        add_action( 'dokan_dashboard_before_widgets', array( $this, 'show_profile_progressbar' ), 10 );
        add_action( 'dokan_dashboard_left_widgets', array( $this, 'get_review_widget' ), 16 );
        add_action( 'dokan_dashboard_right_widgets', array( $this, 'get_announcement_widget' ), 12 );
    }

    /**
     * Show Profile progressbar
     *
     * @return void
     */
    public function show_profile_progressbar() {
        if ( current_user_can( 'dokan_view_overview_menu' ) ) {
            echo dokan_get_profile_progressbar();
        }
    }

    /**
     * Get Review Widget
     *
     * @return void
     */
    public function get_review_widget() {
        if ( ! current_user_can( 'dokan_view_overview_menu' ) ) {
            return;
        }

        if ( ! current_user_can( 'dokan_view_review_reports' ) ) {
            return;
        }

        dokan_get_template_part(
            'dashboard/review-widget', '', array(
				'pro'            => true,
				'comment_counts' => $this->get_comment_counts(),
				'reviews_url'    => dokan_get_navigation_url( 'reviews' ),
			)
        );
    }

    /**
     * Get announcement widget
     *
     * @return void
     */
    public function get_announcement_widget() {
        if ( ! current_user_can( 'dokan_view_overview_menu' ) ) {
            return;
        }

        if ( ! current_user_can( 'dokan_view_announcement' ) ) {
            return;
        }

        $template_notice = dokan_pro()->notice;

        $args = array(
            'post_type'      => 'dokan_announcement',
            'post_status'    => 'publish',
            'posts_per_page' => apply_filters( 'dokan_dashboard_widget_announcement_list_number', 3 ),
            'orderby'        => 'post_date',
            'order'          => 'DESC',
        );

        $template_notice->add_query_filter();

        $seller_id   = dokan_get_current_user_id();
        $cache_group = "seller_announcement_{$seller_id}";
        $cache_key   = 'get_announcement_' . md5( wp_json_encode( array_merge( $args, [ 'author' => $seller_id ] ) ) );
        $posts_data  = Cache::get( $cache_key, $cache_group );

        if ( false === $posts_data ) {
            $posts_data = new \WP_Query( $args );

            Cache::set( $cache_key, $posts_data, $cache_group );
        }

        $seller_posts = $posts_data->posts;

        $template_notice->remove_query_filter();

        dokan_get_template_part(
            'dashboard/announcement-widget', '', array(
				'pro'              => true,
				'notices'          => $seller_posts,
				'announcement_url' => dokan_get_navigation_url( 'announcement' ),
			)
        );
    }
}
