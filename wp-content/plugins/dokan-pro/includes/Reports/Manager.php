<?php

namespace WeDevs\DokanPro\Reports;

class Manager {

    public function get_logs( $args = [] ) {
        $default = [
            'id'           => [],
            'order_id'     => [],
            'vendor_id'    => [],
            'start_date'   => '',
            'end_date'     => '',
            'order_status' => '',         // possible values are wc-processing, wc-completed etc.
            'orderby'      => 'order_id',
            'order'        => 'DESC',
            'return'       => 'all',     // possible values are all, ids, count
            'per_page'     => 20,
            'page'         => 1,
        ];

        $args = wp_parse_args( $args, $default );

        global $wpdb;

        $fields      = 'do.*, p.post_date';
        $join        = "LEFT JOIN $wpdb->posts p ON do.order_id = p.ID";
        $where       = '';
        $orderby     = '';
        $query_args  = [ 1, 1 ];

        if ( 'ids' === $args['return'] ) {
            $fields = 'do.id';
        } elseif ( 'count' === $args['return'] ) {
            $fields = 'COUNT(do.id) AS total';
        }

        // check if id filter is applied
        if ( ! $this->is_empty( $args['id'] ) ) {
            if ( is_array( $args['id'] ) ) {
                $order_ids = implode( "','", array_map( 'absint', $args['id'] ) );
                $where .= " AND do.id IN ('$order_ids')";
            } else {
                $where .= ' AND do.id = %d';
                $query_args[] = $args['id'];
            }
        }

        // check if vendor id filter is applied
        if ( ! $this->is_empty( $args['vendor_id'] ) ) {
            if ( is_array( $args['vendor_id'] ) ) {
                $vendor_id = implode( "','", array_map( 'absint', $args['vendor_id'] ) );
                $where .= " AND seller_id IN ('$vendor_id')";
            } else {
                $where .= ' AND seller_id = %d';
                $query_args[] = $args['vendor_id'];
            }
        }

        // check if order id filter is applied
        if ( ! $this->is_empty( $args['order_id'] ) ) {
            if ( is_array( $args['order_id'] ) ) {
                $order_ids = implode( "','", array_map( 'absint', $args['order_id'] ) );
                $where .= " AND do.order_id IN ('$order_ids')";
            } else {
                $where .= ' AND do.order_id = %d';
                $query_args[] = $args['order_id'];
            }
        }

        // check if status filter is applied
        if ( ! empty( $args['order_status'] ) ) {
            $where .= ' AND p.post_status = %s';
            $query_args[] = $args['order_status'];
        } else {
            $where .= ' AND p.post_status <> %s';
            $query_args[] = 'trash';
        }

        // check if date filter is applied
        if ( ! empty( $args['start_date'] ) ) {
            $where .= ' AND DATE(p.post_date) >= %s';
            $query_args[] = $args['start_date'];
        }

        if ( ! empty( $args['end_date'] ) ) {
            $where .= ' AND DATE(p.post_date) <= %s';
            $query_args[] = $args['end_date'];
        }

        $supported_order_by = [
            'order_id' => 'do.order_id',
        ];

        if ( ! empty( $args['orderby'] ) && array_key_exists( $args['orderby'], $supported_order_by ) ) {
            $orderby = 'ORDER BY ' . $supported_order_by[ $args['orderby'] ] . ' ';
            $orderby .= in_array( strtolower( $args['order'] ), [ 'asc', 'desc' ], true ) ? strtoupper( $args['order'] ) : 'DESC';
        }

        $sql = "SELECT $fields FROM {$wpdb->prefix}dokan_orders do $join WHERE %d=%d $where $orderby";

        if ( 'count' === $args['return'] ) {
            return $wpdb->get_var( $wpdb->prepare( $sql, $query_args) ); //phpcs:ignore
        } else {
            $limit  = absint( $args['per_page'] );
            $page   = absint( $args['page'] );
            $page   = $page ? $page : 1;
            $offset = ( $page - 1 ) * $limit;

            $limits       = 'LIMIT %d, %d';
            $query_args[] = $offset;
            $query_args[] = $limit;
            $sql = $wpdb->prepare( "$sql $limits", $query_args ); //phpcs:ignore

            if ( 'ids' === $args['return'] ) {
                return $wpdb->get_col( $sql ); //phpcs:ignore
            } else {
                return $wpdb->get_results( $sql ); //phpcs:ignore
            }
        }
    }

    /**
     * This will check if given var is empty or not.
     *
     * @param mixed $var
     *
     * @since 3.4.1
     *
     * @return bool
     */
    protected function is_empty( $var ) {
        if ( empty( $var ) ) {
            return true;
        }

        if ( isset( $var[0] ) && intval( $var[0] === 0 ) ) {
            return true;
        }

        return false;
    }
}
