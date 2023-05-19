<?php

namespace WeDevs\DokanPro\Admin;

use WeDevs\Dokan\Cache;

/**
 *  Dokan Announcement class for Admin
 *
 *  Announcement for seller
 *
 *  @since 2.1
 *
 *  @author weDevs <info@wedevs.com>
 */
class Announcement {

    private $post_type = 'dokan_announcement';

    protected $processor;

    /**
     *  Load automatically all actions
     */
    public function __construct() {
        $this->processor = new AnnouncementBackgroundProcess();

        add_action( 'init', array( $this, 'post_types' ), 20 );
    }

    /**
     * Trigger mail
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function trigger_mail( $post_id ) {
        $data = get_post( $post_id );

        if ( ! $data ) {
            return;
        }

        if ( 'publish' !== $data->post_status ) {
            return;
        }

        $sender_type = get_post_meta( $post_id, '_announcement_type', true );
        $vendor_ids  = [];

        $announcement_types = apply_filters( 'dokan_announcement_seller_types', [ 'all_seller', 'enabled_seller', 'disabled_seller', 'featured_seller' ] );

        if ( in_array( $sender_type, $announcement_types, true ) ) {
            $args = [
                'role__in'   => [ 'seller', 'administrator' ],
            ];

            if ( 'enabled_seller' === $sender_type ) {
                $args['meta_query'][] = [
                    'key'     => 'dokan_enable_selling',
                    'value'   => 'yes',
                    'compare' => '=',
                ];
            }

            if ( 'disabled_seller' === $sender_type ) {
                $args['meta_query'][] = [
                    'key'     => 'dokan_enable_selling',
                    'value'   => 'no',
                    'compare' => '=',
                ];
            }

            if ( 'featured_seller' === $sender_type ) {
                $args['meta_query'][] = [
                    'key'     => 'dokan_feature_seller',
                    'value'   => 'yes',
                    'compare' => '=',
                ];
            }

            $users   = new \WP_User_Query( $args );
            $vendors = $users->get_results();

            if ( $vendors ) {
                foreach ( $vendors as $vendor ) {
                    array_push( $vendor_ids, $vendor->ID );
                }
            }
        } else {
            $vendor_ids = get_post_meta( $post_id, '_announcement_selected_user', true );
        }

        $payload = [];

        foreach ( $vendor_ids as $vendor_id ) {
            $payload = array(
                'post_id'   => $post_id,
                'sender_id' => $vendor_id,
            );

            $this->processor->push_to_queue( $payload );
        }

        $this->processor->save()->dispatch();
    }

    /**
     * Register Announcement post type
     *
     * @since 2.1
     *
     * @return void
     */
    public function post_types() {
        register_post_type(
            $this->post_type, array(
                'label'           => __( 'Announcement', 'dokan' ),
                'description'     => '',
                'public'          => false,
                'show_ui'         => true,
                'show_in_menu'    => false,
                'capability_type' => 'post',
                'hierarchical'    => false,
                'rewrite'         => array( 'slug' => '' ),
                'query_var'       => false,
                'supports'        => array( 'title', 'editor' ),
                'labels'          => array(
                    'name'               => __( 'Announcement', 'dokan' ),
                    'singular_name'      => __( 'Announcement', 'dokan' ),
                    'menu_name'          => __( 'Dokan Announcement', 'dokan' ),
                    'add_new'            => __( 'Add Announcement', 'dokan' ),
                    'add_new_item'       => __( 'Add New Announcement', 'dokan' ),
                    'edit'               => __( 'Edit', 'dokan' ),
                    'edit_item'          => __( 'Edit Announcement', 'dokan' ),
                    'new_item'           => __( 'New Announcement', 'dokan' ),
                    'view'               => __( 'View Announcement', 'dokan' ),
                    'view_item'          => __( 'View Announcement', 'dokan' ),
                    'search_items'       => __( 'Search Announcement', 'dokan' ),
                    'not_found'          => __( 'No Announcement Found', 'dokan' ),
                    'not_found_in_trash' => __( 'No Announcement found in trash', 'dokan' ),
                    'parent'             => __( 'Parent Announcement', 'dokan' ),
                ),
            )
        );
    }

    /**
     * Proce seller announcement data
     *
     * @since  2.1
     *
     * @param  array $announcement_seller
     * @param  integer $post_id
     *
     * @return void
     */
    public function process_seller_announcement_data( $announcement_seller, $post_id ) {
        // delete old cache
        self::delete_announcement_cache( $announcement_seller );

        $inserted_seller_id = $this->get_assign_seller( $post_id );

        if ( ! empty( $inserted_seller_id ) ) {
            foreach ( $inserted_seller_id as $key => $value ) {
                $db[] = $value['user_id'];
            }
        } else {
            $db = array();
        }

        $sellers         = $announcement_seller;
        $existing_seller = $new_seller = $del_seller = array(); // phpcs:ignore

        foreach ( $sellers as $seller ) {
            if ( in_array( $seller, $db, true ) ) {
                $existing_seller[] = $seller;
            } else {
                $new_seller[] = $seller;
            }
        }

        $del_seller = array_diff( $db, $existing_seller );

        if ( $del_seller ) {
            $this->delete_assign_seller( $del_seller, $post_id );
        }

        if ( $new_seller ) {
            $this->insert_assign_seller( $new_seller, $post_id );
        }
    }

    /**
     * Get assign seller
     *
     * @since  2.1
     *
     * @param  integer $post_id
     *
     * @return array
     */
    public function get_assign_seller( $post_id ) {
        global $wpdb;

        $results = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT `user_id` FROM {$wpdb->prefix}dokan_announcement WHERE `post_id`= %d", $post_id
            ), ARRAY_A
        );

        if ( $results ) {
            return $results;
        } else {
            return array();
        }
    }

    /**
     * Insert assing seller
     *
     * @since 2.1
     *
     * @param  array $seller_array
     * @param  integer $post_id
     *
     * @return void
     */
    public function insert_assign_seller( $seller_array, $post_id ) {
        global $wpdb;

        $values     = '';
        $table_name = $wpdb->prefix . 'dokan_announcement';
        $i          = 0;

        foreach ( $seller_array as $key => $seller_id ) {
            $sep    = ( $i === 0 ) ? '' : ',';
            $values .= sprintf( "%s ( %d, %d, '%s')", $sep, $seller_id, $post_id, 'unread' );

            $i++;
        }

        $sql = "INSERT INTO {$table_name} (`user_id`, `post_id`, `status` ) VALUES $values";
        $wpdb->query( $sql ); // phpcs:ignore
    }

    /**
     * Delete assign seller
     *
     * @since  2.1
     *
     * @param  array $seller_array
     * @param  integer $post_id
     *
     * @return void
     */
    public function delete_assign_seller( $seller_array, $post_id ) {
        if ( ! is_array( $seller_array ) ) {
            return;
        }

        global $wpdb;

        $table_name = $wpdb->prefix . 'dokan_announcement';
        $values     = '';
        $i          = 0;

        foreach ( $seller_array as $key => $seller_id ) {
            $sep    = ( $i === 0 ) ? '' : ',';
            $values .= sprintf( '%s( %d, %d )', $sep, $seller_id, $post_id );

            $i++;
        }

        $sql = "DELETE FROM {$table_name} WHERE (`user_id`, `post_id` ) IN ($values)";

        if ( $values ) {
            $wpdb->query( $sql ); // phpcs:ignore
        }
    }

    /**
     * @since 3.2.2
     * @param $request
     * @return WP_Error|\WP_Error|\WP_REST_Response
     */
    public function create_announcement( $request ) {
        if ( empty( trim( $request['title'] ) ) ) {
            return new \WP_Error( 'no_title', __( 'Announcement title must be required', 'dokan' ) );
        }

        $status    = ! empty( $request['status'] ) ? $request['status'] : 'pending';
        $post_date = ! empty( $request['post_date'] ) ? $request['post_date'] : '';

        $data = array(
            'post_title'   => sanitize_text_field( $request['title'] ),
            'post_content' => ! empty( $request['content'] ) ? wp_kses_post( $request['content'] ) : '',
            'post_status'  => $status,
            'post_type'    => 'dokan_announcement',
            'post_author'  => isset( $request['author'] ) ? intval( $request['author'] ) : get_current_user_id(),
            'post_date'    => $post_date,
        );

        $post_id = wp_insert_post( $data );

        if ( is_wp_error( $post_id ) ) {
            return new \WP_Error( $post_id->get_error_message() );
        }

        update_post_meta( $post_id, '_announcement_type', $request['sender_type'] );
        update_post_meta( $post_id, '_announcement_selected_user', $request['sender_ids'] );

        $assigned_sellers   = ! empty( $request['sender_ids'] ) ? $request['sender_ids'] : array();
        $announcement_types = apply_filters( 'dokan_announcement_seller_types', [ 'all_seller', 'enabled_seller', 'disabled_seller', 'featured_seller' ] );

        if ( 'selected_seller' === $request['sender_type'] ) {
            $this->process_seller_announcement_data( $assigned_sellers, $post_id );
        } elseif ( in_array( $request['sender_type'], $announcement_types, true ) ) {
            $assigned_sellers = array();

            $args = [
                'role__in'   => [ 'seller', 'administrator' ],
                'fields'     => 'ID',
            ];

            switch ( $request['sender_type'] ) {
                case 'enabled_seller':
                    $args['meta_query'][] = [
                        'key'     => 'dokan_enable_selling',
                        'value'   => 'yes',
                        'compare' => '=',
                    ];
                    break;

                case 'disabled_seller':
                    $args['meta_query'][] = [
                        'key'     => 'dokan_enable_selling',
                        'value'   => 'no',
                        'compare' => '=',
                    ];
                    break;

                case 'featured_seller':
                    $args['meta_query'][] = [
                        'key'     => 'dokan_feature_seller',
                        'value'   => 'yes',
                        'compare' => '=',
                    ];
                    break;
            }

            $users   = new \WP_User_Query( $args );
            if ( $users->get_total() ) {
                $assigned_sellers = $users->get_results();

                // Remove excluded sellers ids
                if ( ! empty( $request['exclude_sellers_ids'] ) && is_array( $request['exclude_sellers_ids'] ) ) {
                    $assigned_sellers = array_diff( $assigned_sellers, $request['exclude_sellers_ids'] );
                }
            }

            $this->process_seller_announcement_data( $assigned_sellers, $post_id );
        }

        do_action( 'dokan_after_announcement_saved', $post_id, $assigned_sellers );

        return $post_id;
    }

    /**
     * Delete individual seller announcement cache.
     *
     * @since 3.4.2
     *
     * @param array|int $seller_ids
     * @param int $post_id
     *
     * @return void
     */
    public static function delete_announcement_cache( $seller_ids, $post_id = null ) {
        if ( is_array( $seller_ids ) ) {
            foreach ( $seller_ids as $seller_id ) {
                Cache::invalidate_group( "seller_announcement_{$seller_id}" );
            }
        } elseif ( is_numeric( $seller_ids ) ) {
            Cache::invalidate_group( "seller_announcement_{$seller_ids}" );
        } elseif ( is_numeric( $post_id ) ) {
            $seller_ids = dokan_pro()->announcement->get_assign_seller( $post_id );
            foreach ( $seller_ids as $seller_id ) {
                Cache::invalidate_group( "seller_announcement_{$seller_id['user_id']}" );
            }
        }
        // remove main cache group
        Cache::invalidate_group( 'announcements' );
    }
}
