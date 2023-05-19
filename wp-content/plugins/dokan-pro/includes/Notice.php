<?php

namespace WeDevs\DokanPro;

use WeDevs\Dokan\Cache;
use WP_Query;

/**
 * Dokan Notice Class
 *
 * @since  2.1
 *
 * @author weDevs  <info@wedevs.com>
 */
class Notice {

    private $perpage = 10;
    private $total_query_result;
    public $notice_id;

    /**
     * Load automatically when class initiate
     *
     * @since 2.4
     *
     * @uses action hook
     * @uses filter hook
     */
    public function __construct() {
        add_action( 'dokan_load_custom_template', array( $this, 'load_announcement_template' ), 10 );
        add_action( 'dokan_announcement_content_area_header', array( $this, 'load_header_template' ) );
        add_action( 'dokan_announcement_content', array( $this, 'load_announcement_content' ), 10 );
        add_action( 'dokan_single_announcement_content', array( $this, 'load_single_announcement_content' ), 10 );
        add_filter( 'dokan_get_dashboard_nav', array( $this, 'add_announcement_page' ), 15 );
        add_filter( 'dokan_dashboard_nav_active', [ $this, 'active_announcement_nav_menu' ], 11, 3 );
    }

    /**
     * Render announcement template
     *
     * @since  2.2
     *
     * @param  array $query_vars
     *
     * @return void
     */
    public function load_announcement_template( $query_vars ) {
        if ( isset( $query_vars['announcement'] ) ) {
            dokan_get_template_part(
                'announcement/announcement', '', array(
                    'pro' => true,
                    'announcement' => $this,
                )
            );
            return;
        }
        if ( isset( $query_vars['single-announcement'] ) ) {
            dokan_get_template_part( 'announcement/single-announcement', '', array( 'pro' => true ) );
            return;
        }
    }

    /**
     * Render Announcement listing template header
     *
     * @since 2.2
     *
     * @return void
     */
    public function load_header_template() {
        dokan_get_template_part( 'announcement/header', '', array( 'pro' => true ) );
    }

    /**
     * Load announcement Content
     *
     * @since 2.4
     *
     * @return void
     */
    public function load_announcement_content() {
        $this->show_announcement_template();
    }

    /**
     * Load Single announcement content
     *
     * @since 2.4
     *
     * @return void
     */
    public function load_single_announcement_content() {
        $this->notice_id = get_query_var( 'single-announcement' );

        if ( is_numeric( $this->notice_id ) ) {
            $notice = $this->get_single_announcement( $this->notice_id );
        }

        if ( $notice ) {
            $notice_data = reset( $notice );
            if ( 'unread' === $notice_data->status ) {
                $this->update_notice_status( $this->notice_id, 'read' );
            }
            dokan_get_template_part(
                'announcement/single-notice', '', array(
                    'pro' => true,
                    'notice_data' => $notice_data,
                )
            );
        } else {
            dokan_get_template_part( 'announcement/no-announcement', '', array( 'pro' => true ) );
        }
    }

    /**
     * Show announcement template
     *
     * @since  2.1
     *
     * @return void
     */
    public function show_announcement_template() {
        $pagenum = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1; //phpcs:ignore

        $args = array(
            'post_type'      => 'dokan_announcement',
            'post_status'    => 'publish',
            'posts_per_page' => apply_filters( 'dokan_announcement_list_number', $this->perpage ),
            'orderby'        => 'post_date',
            'order'          => 'DESC',
            'paged'          => $pagenum,
        );

        $this->add_query_filter();

        $seller_id   = dokan_get_current_user_id();
        $cache_group = "seller_announcement_{$seller_id}";
        $cache_key   = 'get_announcement_' . md5( wp_json_encode( array_merge( $args, [ 'author' => $seller_id ] ) ) );

        $seller_posts_response = Cache::get( $cache_key, $cache_group );

        if ( false === $seller_posts_response ) {
            $seller_posts_response = new WP_Query( $args );

            Cache::set( $cache_key, $seller_posts_response, $cache_group );
        }

        $seller_posts = $seller_posts_response->posts;

        $this->remove_query_filter();

        dokan_get_template_part(
            'announcement/listing-announcement', '', array(
                'pro'     => true,
                'notices' => $seller_posts,
            )
        );

        wp_reset_postdata();
        $this->get_pagination( $seller_posts_response );
    }

    /**
     *  Add Query filter for select, join and where
     *  with dokan_announcement post type
     *
     *  @since  2.1
     */
    public function add_query_filter() {
        add_filter( 'posts_fields', array( $this, 'select_dokan_announcement_table' ), 10, 2 );
        add_filter( 'posts_join', array( $this, 'join_dokan_announcement_table' ) );
        add_filter( 'posts_where', array( $this, 'where_dokan_announcement_table' ), 10, 2 );
    }

    /**
     * Remove query filters
     *
     * @since  2.1
     *
     * @return void
     */
    public function remove_query_filter() {
        remove_filter( 'posts_fields', array( $this, 'select_dokan_announcement_table' ), 10, 2 );
        remove_filter( 'posts_join', array( $this, 'join_dokan_announcement_table' ) );
        remove_filter( 'posts_where', array( $this, 'where_dokan_announcement_table' ), 10, 2 );
    }

    /**
     * Render pagination for seller announcement
     *
     * @since  2.1
     *
     * @param  object $query
     *
     * @return void
     */
    public function get_pagination( $query ) {
        $pagenum  = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1; //phpcs:ignore
        $base_url = dokan_get_navigation_url( 'announcement' );

        if ( $query->max_num_pages > 1 ) {
            echo '<div class="pagination-wrap">';
            $page_links = paginate_links(
                array(
                    'current'   => $pagenum,
                    'total'     => $query->max_num_pages,
                    'base'      => $base_url . '%_%',
                    'format'    => '?pagenum=%#%',
                    'add_args'  => false,
                    'type'      => 'array',
                    'prev_text' => __( '&laquo; Previous', 'dokan' ),
                    'next_text' => __( 'Next &raquo;', 'dokan' ),
                )
            );

            echo '<ul class="pagination"><li>';
            echo join( "</li>\n\t<li>", $page_links );
            echo "</li>\n</ul>\n";
            echo '</div>';
        }
    }

    /**
     * Get single announcement
     *
     * @since  2.1
     *
     * @param  integer $notice_id
     *
     * @return object
     */
    public function get_single_announcement( $notice_id ) {
        $args = array(
            'p'         => $notice_id,
            'post_type' => 'dokan_announcement',
        );

        $this->add_query_filter();

        $query = new WP_Query( $args );
        $notice = (array) $query->posts;

        $this->remove_query_filter();
        return $notice;
    }

    /**
     * Update notice status in dokan_announcement table
     *
     * @since  2.1
     *
     * @param  integer $notice_id
     * @param  string $status
     *
     * @return void
     */
    public function update_notice_status( $notice_id, $status ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'dokan_announcement';

        $wpdb->update(
            $table_name,
            array(
                'status' => $status,
            ),
            array(
                'post_id' => $notice_id,
                'user_id' => dokan_get_current_user_id(),
            )
        );
    }

    /**
     * Select query filter
     *
     * @since  2.1
     *
     * @param  string $fields
     * @param  object $query
     *
     * @return string
     */
    public function select_dokan_announcement_table( $fields, $query ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'dokan_announcement';
        $fields .= ' ,da.id, da.status';

        return $fields;
    }

    /**
     * Join query filter
     *
     * @since  2.1
     *
     * @param  string $join
     *
     * @return string
     */
    public function join_dokan_announcement_table( $join ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'dokan_announcement';
        $join .= " LEFT JOIN $table_name AS da ON $wpdb->posts.ID = da.post_id";

        return $join;
    }

    /**
     * Where query filter
     *
     * @since  2.1
     *
     * @param  integer $where
     * @param  object $query
     *
     * @return string
     */
    public function where_dokan_announcement_table( $where, $query ) {
        global $wpdb;

        $table_name = $wpdb->prefix . 'dokan_announcement';
        $current_user_id = dokan_get_current_user_id();

        $where .= " AND da.user_id = $current_user_id AND ( da.status = 'read' OR da.status = 'unread' )";

        return $where;
    }

    /**
     * Add announcement page in seller dashboard
     *
     * @param array $urls
     *
     * @return array $urls
     */
    public function add_announcement_page( $urls ) {
        if ( current_user_can( 'dokandar' ) ) {
            $urls['announcement'] = array(
                'title' => __( 'Announcements', 'dokan' ),
                'icon'  => '<i class="fas fa-bell"></i>',
                'url'   => dokan_get_navigation_url( 'announcement' ),
                'pos'   => 181,
            );
        }

        return $urls;
    }

    /**
     * Set announcement menu as active.
     *
     * @since 3.7.18
     *
     * @param string $active_menu Currently active menu slug.
     * @param string $request_uri Request URI.
     * @param array  $query_vars Currently active query vars.
     *
     * @return string
     */
    public function active_announcement_nav_menu( string $active_menu, $request_uri, array $query_vars ): string {
        if ( ! in_array( 'single-announcement', $query_vars, true ) ) {
            return $active_menu;
        }
        return 'announcement';
    }
}
