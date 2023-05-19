<?php

// Restricting direct file access through url.
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// If AdminStoreSupportTicketController already exists then return.
if ( ! class_exists( 'StoreSupportHelper' ) ) :
    /**
     * StoreSupportHelper class
     * This class has all the static helper methods for store support.
     *
     * @since 3.5.0
     */
    class StoreSupportHelper {

        /**
         * Query for all support topics
         *
         * @since 3.5.0
         *
         * @param array $args
         *
         * @return array $query
         */
        public static function dokan_get_all_support_topics( $args = [] ) {
            global $post;

            $defaults = [
                'post_type'      => 'dokan_store_support',
                'posts_per_page' => 20,
                'offset'         => 0,
                'paged'          => 1,
                'orderby'        => 'ID',
                'order'          => 'DESC',
                'meta_key'       => 'store_id', //phpcs:ignore
            ];

            $args = wp_parse_args( $args, $defaults );

            // The Query
            $result = [];
            $the_query = new WP_Query( $args );

            if ( $the_query->have_posts() ) {
                while ( $the_query->have_posts() ) {
                    $the_query->the_post();
                    global $post;

                    $current_topic = $post;

                    $vendor_id   = get_post_meta( get_the_ID(), 'store_id', true );
                    $vendor      = dokan()->vendor->get( $vendor_id );
					if ( ! $vendor->is_vendor() ) {
						continue;
					}

                    $vendor_name = $vendor->get_shop_name();

                    $customer_id   = get_the_author_meta( 'ID' );
                    $customer = get_user_by( 'ID', $customer_id );
					if ( ! $customer   ) {
						$customer_name = __( '(no name)', 'dokan' );
					} else {
						$customer_name = $customer->display_name;
					}

                    $current_topic->vendor_name   = $vendor_name;
                    $current_topic->customer_name = $customer_name;
                    $current_topic->vendor_id     = $vendor_id;
                    $current_topic->store_url     = $vendor->get_shop_url();
                    $current_topic->ticket_date   = dokan_format_datetime( dokan_get_timestamp( $current_topic->post_date_gmt, true ) );
                    $current_topic->reading       = get_post_meta( get_the_ID(), 'reading', true ) ? 'yes' : 'no';

                    $result[] = $current_topic;
                }
            }
            wp_reset_postdata();

            return $result;
        }

        /**
         * Returns total support topics count
         *
         * @since 3.5.0
         *
         * @return int $topics
         */
        public static function dokan_get_total_support_topics_count() {
            $args = [
                'post_type'      => 'dokan_store_support',
                'posts_per_page' => -1,
                'meta_key'       => 'store_id', // phpcs:ignore
            ];

            $the_query = new WP_Query( $args );
            $topics_count = $the_query->found_posts;

            return $topics_count;
        }

        /**
         * Return all, opened and closed topics count
         *
         * @since 3.5.0
         *
         * @return array $result
         */
        public static function dokan_get_support_topics_status_count() {
            $all_topics = wp_count_posts( 'dokan_store_support' );

            $closed_topics_count = ! empty( $all_topics->closed ) ? $all_topics->closed : 0;
            $open_topics_count = ! empty( $all_topics->open ) ? $all_topics->open : 0;

            $result = [
                'all'           => $closed_topics_count + $open_topics_count,
                'closed_topics' => $closed_topics_count,
                'open_topics'   => $open_topics_count,
            ];

            return $result;
        }

        /**
         * Change status of topic from support list action
         *
         * @since 3.5.0
         *
         * @param int $support_ticket_topic_id
         * @param string $status open/closed
         *
         * @return int $support_ticket_topic_id
         */
        public static function dokan_change_topic_status( $support_ticket_topic_id, $status = 'open' ) {
            if ( empty( $support_ticket_topic_id ) || 0 === $support_ticket_topic_id ) {
                return false;
            }

            $validated_status = 'open' === $status ? $status : 'closed';

            $my_post = [
                'ID'          => $support_ticket_topic_id,
                'post_status' => $validated_status,
            ];
            wp_update_post( $my_post );

            return $support_ticket_topic_id;
        }

        /**
         * Get those customers who created support tickets.
         *
         * @since 3.5.0
         *
         * @param $searched_customer Searched customer name.
         *
         * @return array $results
         */
        public static function dokan_get_support_topic_created_customers( $searched_customer = '' ) {
            global $wpdb;

            $users = $wpdb->users;
            $posts = $wpdb->posts;
            $like  = '%' . $wpdb->esc_like( $searched_customer ) . '%';

            $customer_where_clause = $searched_customer !== '' ? "AND $users.display_name LIKE $like" : '';

            $sql = "SELECT $users.ID, $users.display_name
                    FROM $users
                    LEFT JOIN $posts ON $posts.post_author = $users.ID
                    WHERE $posts.post_type = 'dokan_store_support'
                    $customer_where_clause
                    GROUP BY $posts.post_author LIMIT %d";

            $results = $wpdb->get_results( $wpdb->prepare( $sql, 100 ) ); // phpcs:ignore

            return $results;
        }

        /**
         * Returns the count of unread support topics.
         *
         * @since 3.6.0
         *
         * @return int $topics_count
         */
        public static function get_unread_support_topic_count() {
            $args = [
                'post_type'      => 'dokan_store_support',
                'posts_per_page' => -1,
                'meta_key'       => 'store_id',
                'meta_query'     => array(
                    array(
                        'key'     => 'reading',
                        'compare' => 'NOT EXISTS',
                    ),
                ),
            ];

            $the_query = new WP_Query( $args );
            $topics_count = $the_query->found_posts;

            return absint( $topics_count );
        }

        // ===============Admin store support rest process/calculation methods================

        /**
         * Returns all support tickets
         *
         * @param array $args
         * @return array $result
         */
        public static function get_all_tickets( $args = [] ) {
            $args['offset']         = absint( $args['per_page'] ) * ( absint( $args['page'] ) - 1 );
            $args['posts_per_page'] = absint( $args['per_page'] );
            $args['paged ']         = absint( $args['page'] );

            $filters = isset( $args['filter'] ) ? $args['filter'] : [];

            if ( isset( $filters['customer_id'] ) && 0 !== absint( sanitize_text_field( $filters['customer_id'] ) ) ) {
                $args['author'] = absint( sanitize_text_field( $filters['customer_id'] ) );
            }

            if ( isset( $filters['from_date'] ) && '' !== $filters['from_date'] ) {
                $from_date = dokan_current_datetime()->modify( sanitize_text_field( $filters['from_date'] ) );

                $from_year  = $from_date->format( 'Y' );
                $from_month = $from_date->format( 'm' );
                $from_day   = $from_date->format( 'd' );

                $args['date_query']['after'] = array(
                    'year'  => $from_year,
                    'month' => $from_month,
                    'day'   => $from_day,
                );
                $args['date_query']['inclusive'] = true;
            }
            if ( isset( $filters['to_date'] ) && '' !== $filters['to_date'] ) {
                $to_date = dokan_current_datetime()->modify( sanitize_text_field( $filters['to_date'] ) );

                $to_year  = $to_date->format( 'Y' );
                $to_month = $to_date->format( 'm' );
                $to_day   = $to_date->format( 'd' );

                $args['date_query']['before'] = array(
                    'year'  => $to_year,
                    'month' => $to_month,
                    'day'   => $to_day,
                );
                $args['date_query']['inclusive'] = true;
            }

            // Unseting these arguments beacuse for quering these keys are not valid,
            // and we have already modified these keyed arguments to queriable arguments.
            unset(
                $args['per_page'],
                $args['page'],
                $args['search'],
                $args['filter']
            );

            $result = self::dokan_get_all_support_topics( $args );

            return $result;
        }

        /**
         * Returns true if fillterable/current loop vendor id and returnable/search topic vendor id is same
         *
         * @since 3.5.0
         *
         * @param array $filters
         * @param object $topic
         *
         * @return boolean
         */
        public static function filter_topics_by_vendor( $filters, $topic ) {
            if ( isset( $filters['vendor_id'] ) && 0 !== absint( sanitize_text_field( $filters['vendor_id'] ) && $topic->vendor_id !== $filters['vendor_id'] ) ) {
                return false;
            }

            return true;
        }

        /**
         * Returns single topic informaitons.
         *
         * @since 3.5.0
         *
         * @param array $request
         *
         * @return array $result
         */
        public static function get_single_topic( $request ) {
            $dokan_store_support = dokan_pro()->module->store_support;

            $topic_id  = absint( $request['id'] );
            $vendor_id = absint( $request['vendor_id'] );

            // Updating post meta, this means admin has opened the topic and has read the topic.
            update_post_meta( $topic_id, 'reading', 'read' );

            $topics = $dokan_store_support->get_single_topic( $topic_id, $vendor_id );

            $result = [];

            if ( $topics->have_posts() ) {
                $result['topic'] = $topics->posts[0];
            }

            if ( isset( $result['topic'] ) ) {
                $result['topic']->avatar_url         = get_avatar_url( $result['topic']->post_author );
                $result['topic']->post_author_name   = get_userdata( $result['topic']->post_author )->user_login;
                $result['topic']->post_date_formated = dokan_format_datetime( dokan_get_timestamp( $result['topic']->post_date_gmt, true ) );

                // Gather comments for a specific page/post
                $comments = get_comments(
                    [
                        'post_id' => $result['topic']->ID,
                        'status'  => 'approve', //Change this to the type of comments to be displayed
                        'orderby' => 'comment_ID',
                        'order'   => 'ASC',
                    ]
                );

                $result['comments'] = self::load_comments_extra_data( $comments, $vendor_id );
            } else {
	            $result['topic']    = [];
	            $result['comments'] = [];
            }

            $result['store_info']              = dokan_get_store_info( $vendor_id );
            $result['store_info']['store_url'] = dokan_get_store_url( $vendor_id );

            $custom_logo_id = get_theme_mod( 'custom_logo' );
            $logo           = wp_get_attachment_image_src( $custom_logo_id, 'full' );

            $result['site_image_url']      = has_custom_logo() ? $logo[0] : get_avatar_url( 0 );
            $result['site_title']          = get_bloginfo( 'name', 'display' );
            $result['unread_topics_count'] = self::get_unread_support_topic_count();

            $admin_global_settings = dokan_get_option( 'dokan_admin_email_notification', 'dokan_store_support_setting', 'off' );
            $result['dokan_admin_email_notification_global'] = $admin_global_settings;

            $topic_specific_setting = get_post_meta( $topic_id, 'dokan_admin_email_notification', true );

            // Email notification setting is 'off' if global setting is 'off' or global setting is 'on' and topic specific setting is 'off'
            $result['dokan_admin_email_notification'] = 'off' === $admin_global_settings || ( 'on' === $admin_global_settings && 'off' === $topic_specific_setting ) ? 'off' : 'on';

            return $result;
        }

        /**
         * Loads and injects extra data of comments that needed.
         *
         * @since 3.5.0
         *
         * @param array $comments
         * @param int $vendor_id
         *
         * @return array $comments
         */
        public static function load_comments_extra_data( $comments, $vendor_id ) {
            foreach ( $comments as $key => $value ) {
                $value->avatar_url = get_avatar_url( $value->user_id );

                if ( user_can( $value->user_id, 'manage_options' ) ) {
                    $value->comment_user_type = [
                        'type' => 'admin',
                        'text' => __( 'Admin', 'dokan' ),
                    ];
                } elseif ( absint( $vendor_id ) === absint( $value->user_id ) ) {
                    $value->comment_user_type = [
                        'type' => 'vendor',
                        'text' => __( 'Vendor', 'dokan' ),
                    ];
                } else {
                    $value->comment_user_type = [
                        'type' => 'customer',
                        'text' => __( 'Customer', 'dokan' ),
                    ];
                }
                $timestamp                    = dokan_get_timestamp( $value->comment_date_gmt, true );
                $value->comment_date_formated = dokan_format_datetime( $timestamp );
            }

            return $comments;
        }

        /**
         * Create a new comment replay for a ticket as vendor or admin.
         *
         * @since 3.5.0
         *
         * @param string  $topic_id
         * @param string  $replire_name
         * @param WP_User $replier
         * @param string  $replay
         *
         * @return int|false
         */
        public static function create_comment_replay( $topic_id, $replire_name, $replier, $replay ) {
            $args = [
                'comment_post_ID'      => $topic_id,
                'comment_parent'       => 0,
                'comment_author'       => $replire_name,
                'comment_author_email' => $replier->user_email,
                'comment_author_url'   => home_url(),
                'comment_author_IP'    => dokan_get_client_ip(),
                'comment_date'         => current_time( 'mysql' ),
	            'comment_date_gmt'     => get_gmt_from_date( current_time( 'mysql' ) ),
                'comment_content'      => $replay,
                'comment_agent'        => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
                'user_id'              => $replier->ID,
            ];
            $result = wp_insert_comment( $args );

            if ( ! $result ) {
                dokan_log( sprintf( 'Commment repling failed for topic id: %1$s', $topic_id ) );
                return false;
            }

            return $result;
        }
    }
endif;
