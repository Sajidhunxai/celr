<?php

namespace WeDevs\DokanPro\Admin;

/**
 * Ajax handling for Dokan in Admin area
 *
 * @since 2.2
 *
 * @author weDevs <info@wedevs.com>
 */
class Ajax {

    /**
     * Load automatically all actions
     */
    public function __construct() {
        add_action( 'wp_ajax_regen_sync_table', array( $this, 'regen_sync_order_table' ) );
        add_action( 'wp_ajax_check_duplicate_suborders', array( $this, 'check_duplicate_suborders' ) );
        add_action( 'wp_ajax_rewrite_product_variations_author', [ $this, 'rewrite_product_variations_author' ] );
        add_action( 'wp_ajax_dokan_get_distance_btwn_address', [ $this, 'get_address_btwn_address' ] );
    }

    /**
     * Handle sync order table via ajax
     *
     * @return json success|error|data
     */
    public function regen_sync_order_table() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return wp_send_json_error( __( 'You don\'t have enough permission', 'dokan', '403' ) );
        }

        global $wpdb;

        $limit        = isset( $_POST['limit'] ) ? $_POST['limit'] : 0;
        $offset       = isset( $_POST['offset'] ) ? $_POST['offset'] : 0;
        $total_orders = isset( $_POST['total_orders'] ) ? $_POST['total_orders'] : 0;

        if ( $offset == 0 ) {
            $wpdb->query( 'TRUNCATE TABLE ' . $wpdb->dokan_orders );

            $total_orders = $wpdb->get_var( "SELECT count(ID)
                FROM $wpdb->posts
                WHERE post_type = 'shop_order'" );

            $parent_orders = $wpdb->get_var( "SELECT count(ID)
                FROM {$wpdb->posts} as p
                LEFT JOIN {$wpdb->postmeta} as m ON p.ID = m.post_id
                WHERE m.meta_key = 'has_sub_order' and p.post_type = 'shop_order' " );
            $total_orders = $total_orders - $parent_orders;
        }

        $sql = "SELECT ID FROM $wpdb->posts
                WHERE post_type = 'shop_order'
                LIMIT %d,%d";

        $orders = $wpdb->get_results( $wpdb->prepare($sql, $offset * $limit, $limit ) );

        if ( $orders ) {
            foreach ( $orders as $order) {
                dokan_sync_order_table( $order->ID );
            }

            $sql       = "SELECT * FROM " . $wpdb->dokan_orders;
            $generated = $wpdb->get_results( $sql );
            $done      = count( $generated );

            wp_send_json_success( array(
                'offset'       => $offset + 1,
                'total_orders' => $total_orders,
                'done'         => $done,
                'message'      => sprintf( __( '%d orders sync completed out of %d', 'dokan' ), $done, $total_orders )
            ) );
        } else {
            $dashboard_link = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=dokan' ), __( 'Go to Dashboard &rarr;', 'dokan' ) );
            wp_send_json_success( array(
                'offset'  => 0,
                'done'    => 'All',
                'message' => sprintf( __( 'All orders has been synchronized. %s', 'dokan' ), $dashboard_link )
            ) );
        }
    }

    /**
     * Remove duplicate sub-orders if found
     *
     * @since 2.4.4
     *
     * @return json success|error|data
     */
    public function check_duplicate_suborders(){

        if ( ! isset( $_POST['action'] ) || $_POST['action'] !== 'check_duplicate_suborders' ) {
            return wp_send_json_error( __( 'You don\'t have enough permission', 'dokan', '403' ) );
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return wp_send_json_error( __( 'You don\'t have enough permission', 'dokan', '403' ) );
        }

        if ( session_id() == '' ) {
            session_start();
        }

        global $wpdb;

        $limit        = isset( $_POST['limit'] ) ? $_POST['limit'] : 0;
        $offset       = isset( $_POST['offset'] ) ? $_POST['offset'] : 0;
        $prev_done    = isset( $_POST['done'] ) ? $_POST['done'] : 0;
        $total_orders = isset( $_POST['total_orders'] ) ? $_POST['total_orders'] : 0;

        if ( $offset == 0 ) {
            unset( $_SESSION['dokan_duplicate_order_ids'] );
            $total_orders = $wpdb->get_var( "SELECT count(ID) FROM $wpdb->posts AS p
                LEFT JOIN $wpdb->postmeta AS m ON p.ID = m.post_id
                WHERE post_type = 'shop_order' AND m.meta_key = 'has_sub_order'" );
        }

        $sql = "SELECT ID FROM $wpdb->posts AS p
        LEFT JOIN $wpdb->postmeta AS m ON p.ID = m.post_id
        WHERE post_type = 'shop_order' AND m.meta_key = 'has_sub_order'
        LIMIT %d,%d";

        $orders           = $wpdb->get_results( $wpdb->prepare( $sql, $offset * $limit, $limit ) );
        $duplicate_orders = isset( $_SESSION['dokan_duplicate_order_ids'] ) ? $_SESSION['dokan_duplicate_order_ids'] : array();

        if ( $orders ) {
            foreach ( $orders as $order ) {

                $sellers_count = count( dokan_get_sellers_by( $order->ID ) );
                $sub_order_ids = dokan_get_suborder_ids_by( $order->ID );

                if ( $sellers_count < count( $sub_order_ids ) ) {
                    $duplicate_orders = array_merge( array_slice( $sub_order_ids, $sellers_count ), $duplicate_orders );
                }
            }

            if ( count( $duplicate_orders ) ) {
                $_SESSION['dokan_duplicate_order_ids'] = $duplicate_orders;
            }

            $done = $prev_done + count($orders);

            wp_send_json_success( array(
                'offset'       => $offset + 1,
                'total_orders' => $total_orders,
                'done'         => $done,
                'message'      => sprintf( __( '%d orders checked out of %d', 'dokan' ), $done, $total_orders )
            ) );

        } else {

            if( count( $duplicate_orders ) ) {
               wp_send_json_success( array(
                    'offset'  => 0,
                    'done'    => 'All',
                    'message' => sprintf( __( 'All orders are checked and we found some duplicate orders', 'dokan' ) ),
                    'duplicate' => true
                ) );
            }

            $dashboard_link = sprintf( '<a href="%s">%s</a>', admin_url( 'admin.php?page=dokan' ), __( 'Go to Dashboard &rarr;', 'dokan' ) );

            wp_send_json_success( array(
                    'offset'  => 0,
                    'done'    => 'All',
                    'message' => sprintf( __( 'All orders are checked and no duplicate was found. %s', 'dokan' ), $dashboard_link )
            ) );
        }
    }

    /**
     * Rewrite product variations author via ajax.
     *
     * @since 3.7.13
     *
     * @return void
     */
    public function rewrite_product_variations_author() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'dokan_admin' ) ) {
            return wp_send_json_error( __( 'Nonce verification failed', 'dokan' ), 403 );
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return wp_send_json_error( __( 'You don\'t have enough permission', 'dokan' ), 403 );
        }

        $page         = ! empty( $_POST['page'] ) ? absint( wp_unslash( $_POST['page'] ) ) : 1;
        $bg_processor = dokan()->bg_process->rewrite_variable_products_author;

        $args = [
            'updating' => 'dokan_update_variable_product_variations_author_ids',
            'page'     => $page,
        ];

        $bg_processor->push_to_queue( $args )->save()->dispatch();

        wp_send_json_success(
            [
                'process' => 'running',
                'message' => __( 'Variable product variations author ids rewriting queued successfully', 'dokan' ),
            ]
        );
    }

    /**
     * Get distance between two address to check if Distance Matrix API is working or not
     *
     * @since 3.7.21
     *
     * @return void
     */
    public function get_address_btwn_address() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'dokan_admin' ) ) {
            wp_send_json_error( __( 'Nonce verification failed', 'dokan' ), 403 );
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( __( 'You don\'t have enough permission', 'dokan' ), 403 );
        }

        $address1 = isset( $_POST['address1'] ) ? sanitize_text_field( wp_unslash( $_POST['address1'] ) ) : '';
        $address2 = isset( $_POST['address2'] ) ? sanitize_text_field( wp_unslash( $_POST['address2'] ) ) : '';

        if ( empty( $address1 ) ) {
            wp_send_json_error( __( 'Address 1 is empty', 'dokan' ), 403 );
        }

        if ( empty( $address2 ) ) {
            wp_send_json_error( __( 'Address 2 is empty', 'dokan' ), 403 );
        }

        // check if API key is set
        $gmap_api_key = trim( dokan_get_option( 'gmap_api_key', 'dokan_appearance', '' ) );
        if ( empty( $gmap_api_key ) ) {
            wp_send_json_error( __( 'Google Map API key is not set', 'dokan' ), 403 );
        }

        $api = new \WeDevs\DokanPro\Modules\TableRate\DokanGoogleDistanceMatrixAPI( $gmap_api_key, false );
        $distance = $api->get_distance(
            $address1,
            $address2,
            false
        );

        if ( isset( $distance->status ) && 'OK' === $distance->status ) {
            $message = __( 'Distance Matrix API is enabled.', 'dokan' );
            wp_send_json_success( $message );
        }

        $message = sprintf(
            '<strong>%s:</strong> %s, <strong>%s:</strong> %s',
            __( 'Error Code', 'dokan' ),
            $distance->status,
            __( 'Error Message', 'dokan' ),
            $distance->error_message
        );

        wp_send_json_error( $message, 403 );
    }
}
