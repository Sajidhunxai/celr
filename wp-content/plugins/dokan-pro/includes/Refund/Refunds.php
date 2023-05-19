<?php

namespace WeDevs\DokanPro\Refund;

class Refunds {

    /**
     * Query arguments
     *
     * @var array
     */
    protected $args = [];

    /**
     * Refund results
     *
     * @var array
     */
    protected $refunds = [];

    /**
     * Total refund found
     *
     * @var null|int
     */
    protected $total = null;

    /**
     * Maximum number of pages
     *
     * @var null|int
     */
    protected $max_num_pages = null;

    /**
     * Class constructor
     *
     * @since 3.0.0
     *
     * @param array $args
     *
     * @return void
     */
    public function __construct( $args = [] ) {
        $defaults = [
            'limit'         => 10,
            'page'          => 1,
            'no_found_rows' => false,
        ];

        $this->args = wp_parse_args( $args, $defaults );
        $this->query();
    }

    /**
     * Get refunds
     *
     * @since 3.0.0
     *
     * @return array
     */
    public function get_refunds() {
        return $this->refunds;
    }

    /**
     * Query refunds
     *
     * @since 3.0.0
     *
     * @return \WeDevs\Dokan\Refund\Refunds
     */
    public function query() {
        global $wpdb;

        $args = $this->args;

        // @note: empty variables may use in future
        $fields = '*';
        $join = '';
        $where = '';
        $groupby = '';
        $orderby = '';
        $order = 'asc';
        $limits = '';
        $query_args = [ 1, 1 ];

        if ( isset( $args['ids'] ) && is_array( $args['ids'] ) ) {
            $ids = array_map( 'absint', $args['ids'] );
            $ids = array_filter( $ids );

            $placeholders = [];
            foreach ( $ids as $id ) {
                $placeholders[] = '%d';
                $query_args[] = $id;
            }

            $where .= ' and id in ( ' . implode( ',', $placeholders ) . ' )';
        }

        if ( isset( $args['order_id'] ) ) {
            $where       .= ' and order_id = %d';
            $query_args[] = $args['order_id'];
        }

        if ( isset( $args['seller_id'] ) ) {
            $where       .= ' and seller_id = %d';
            $query_args[] = $args['seller_id'];
        }

        if ( isset( $args['refund_amount'] ) ) {
            $where       .= ' and refund_amount = %s';
            $query_args[] = $args['refund_amount'];
        }

        if ( isset( $args['refund_reason'] ) ) {
            $where .= ' and refund_reason = %s';
            $query_args[] = $args['refund_reason'];
        }

        if ( isset( $args['date'] ) ) {
            $where .= ' and date = %s';
            $query_args[] = $args['date'];
        }

        if ( isset( $args['status'] ) ) {
            $where .= ' and status = %d';
            $query_args[] = $args['status'];
        }

        if ( isset( $args['search'] ) ) {
            $like = '%' . $wpdb->esc_like( $args['search'] ) . '%';
            $join = " LEFT JOIN {$wpdb->usermeta} um ON {$wpdb->dokan_refund}.seller_id = um.user_id AND um.meta_key = 'dokan_store_name'";
            $where .= ' AND (order_id = %d OR um.meta_value LIKE %s)';
            $query_args[] = is_numeric( $args['search'] ) ? absint( $args['search'] ) : 0;
            $query_args[] = $like;
            $groupby = "group by {$wpdb->dokan_refund}.id";
        }

        if ( isset( $args['order'] ) && in_array( strtolower( $args['order'] ), [ 'asc', 'desc' ], true ) ) {
            $order = sanitize_text_field( $args['order'] );
        }

        if ( ! empty( $args['orderby'] ) && in_array( $args['orderby'], [ 'id', 'order_id', 'seller_id', 'date' ], true ) ) {
            $orderby .= "order by {$wpdb->dokan_refund}." . sanitize_sql_orderby( $args['orderby'] ) . ' ' . $order;
        }

        if ( ! empty( $args['limit'] ) ) {
            $limit  = absint( $args['limit'] );
            $page   = absint( $args['page'] );
            $page   = $page ? $page : 1;
            $offset = ( $page - 1 ) * $limit;

            $limits       = 'LIMIT %d, %d';
            $query_args[] = $offset;
            $query_args[] = $limit;
        }

        $found_rows = '';
        if ( ! $args['no_found_rows'] && ! empty( $limits ) ) {
            $found_rows = 'SQL_CALC_FOUND_ROWS';
        }

        $refunds = $wpdb->get_results( $wpdb->prepare(
            "SELECT $found_rows $fields FROM {$wpdb->dokan_refund} $join WHERE %d=%d $where $groupby $orderby $limits",
            ...$query_args
        ), ARRAY_A );

        if ( ! empty( $refunds ) ) {
            foreach ( $refunds as $refund ) {
                $this->refunds[] = new Refund( $refund );
            }
        }

        return $this;
    }

    /**
     * Get total number of refunds
     *
     * @since 3.0.0
     *
     * @return int
     */
    public function get_total() {
        global $wpdb;

        if ( ! isset( $this->total ) ) {
            $this->total = absint( $wpdb->get_var( "SELECT FOUND_ROWS()" ) );
        }

        return $this->total;
    }

    /**
     * Get maximum number of pages
     *
     * @since 3.0.0
     *
     * @return int
     */
    public function get_maximum_num_pages() {
        $total = $this->get_total();

        if ( ! $this->max_num_pages && $total && ! empty( $this->args['limit'] ) ) {
            $limit = absint( $this->args['limit'] );
            $this->max_num_pages = ceil( $total / $limit );
        }

        return $this->max_num_pages;
    }
}
