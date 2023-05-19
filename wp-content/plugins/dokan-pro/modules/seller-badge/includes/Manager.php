<?php

namespace WeDevs\DokanPro\Modules\SellerBadge;

use Exception;
use WeDevs\Dokan\Cache;
use WeDevs\DokanPro\Modules\SellerBadge\Models\BadgeEvent;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

/**
 * Seller Badge Manager
 *
 * @since 3.7.14
 */
class Manager {

    /**
     * @since 3.7.14
     *
     * @var string
     */
    private $badge_table;

    /**
     * @since 3.7.14
     *
     * @var string
     */
    private $badge_acquired_table;

    /**
     * @since 3.7.14
     *
     * @var string
     */
    private $badge_level_table;

    /**
     * Class constructor
     *
     * @since 3.7.14
     */
    public function __construct() {
        global $wpdb;
        $this->badge_table          = "{$wpdb->prefix}dokan_seller_badge";
        $this->badge_level_table    = "{$wpdb->prefix}dokan_seller_badge_level";
        $this->badge_acquired_table = "{$wpdb->prefix}dokan_seller_badge_acquired";
    }

    /**
     * Get badge table string
     *
     * @since 3.7.14
     *
     * @return string
     */
    public function get_badge_table() {
        return $this->badge_table;
    }

    /**
     * Get badge level table string
     *
     * @since 3.7.14
     *
     * @return string
     */
    public function get_badge_level_table() {
        return $this->badge_level_table;
    }

    /**
     * Get badge acquired table string
     *
     * @since 3.7.14
     *
     * @return string
     */
    public function get_badge_acquired_table() {
        return $this->badge_acquired_table;
    }

    /**
     * Get all seller badges.
     *
     * @since 3.7.14
     *
     * @param $args
     *
     * @return array|object|null|WP_Error
     */
    private function all( $args ) {
        $defaults = [
            'badge_id'              => [],
            'badge_name'            => '',
            'event_type'            => [],
            'badge_status'          => 'all', // all, published, draft. default status is all
            'acquired_badge_status' => 'all', // all, published, draft
            'vendor_id'             => [],
            'order'                 => 'ASC',
            'orderby'               => 'badge_id',
            'return'                => 'all', // possible values: all, badge_count, badge_levels, acquired_vendors_by_badge, vendor_acquired_badges, vendor_acquired_badge_levels, vendor_unseen_badges
            'per_page'              => 20,
            'page'                  => 1,
        ];
        // parse args
        $args = wp_parse_args( $args, $defaults );

        global $wpdb;

        $fields      = '';
        $from        = "$this->badge_table AS sb";
        $join        = '';
        $where       = '';
        $inner_where = '';
        $groupby     = '';
        $orderby     = '';
        $limits      = '';
        $query_args  = [ 1, 1 ];
        $status      = '';

        // set which fields to return
        if ( 'badge_count' === $args['return'] ) {
            $fields  = 'sb.badge_status, count(sb.id) AS badge_count';
            $groupby = 'GROUP BY sb.badge_status';
            // get badge table data
        } elseif ( 'badge_levels' === $args['return'] ) {
            // get badge levels with vendor count
            $fields      = ' bla.*';
            $inner_where = '';

            // vendor id filtering
            if ( ! $this->is_empty( $args['vendor_id'] ) ) {
                $vendor_ids  = implode( ",", array_filter( array_map( 'absint', (array) $args['vendor_id'] ) ) );
                $inner_where .= " AND ba.vendor_id IN ($vendor_ids)";
            }

            // acquired badge status filter
            if ( ! empty( $args['acquired_badge_status'] ) && 'all' !== $args['acquired_badge_status'] ) {
                $acquired_badge_status = sanitize_text_field( $args['acquired_badge_status'] );
                $inner_where           .= " AND ba.acquired_status = '$acquired_badge_status'";
            }

            $from = " (
                SELECT bl.*, coalesce(badge_acquired.total_vendor,0) AS vendor_count
                    FROM (
                        SELECT ba.level_id, COUNT(ba.vendor_id) AS total_vendor
                        FROM {$this->badge_acquired_table} AS ba
                        WHERE 1=1 $inner_where
                        GROUP BY ba.level_id
                    ) AS badge_acquired
                    RIGHT JOIN {$this->badge_level_table} AS bl
                    ON bl.id=badge_acquired.level_id
            ) AS bla";

            $join = " RIGHT JOIN {$this->badge_table} AS sb ON bla.badge_id=sb.id";

            $orderby = ' ORDER BY bla.badge_id ASC, bla.level ASC';
        } elseif ( 'vendor_unseen_badges' === $args['return'] ) {
            $inner_where = '';
            if ( ! $this->is_empty( $args['vendor_id'] ) ) {
                $vendor_ids  = implode( ",", array_filter( array_map( 'absint', (array) $args['vendor_id'] ) ) );
                $inner_where .= " AND ba.vendor_id IN ($vendor_ids)";
            }

            // acquired badge status filter
            if ( ! empty( $args['acquired_badge_status'] ) && 'all' !== $args['acquired_badge_status'] ) {
                $acquired_badge_status = sanitize_text_field( $args['acquired_badge_status'] );
                $inner_where           .= " AND ba.acquired_status = '$acquired_badge_status'";
            }

            $fields  = ' sb.*, bla.*';
            $from    = " (
            SELECT
                bl.id AS level_id, bl.badge_id, bl.level, bl.level_condition, bl.level_data,
                ba.id AS badge_acquired_id, ba.acquired_data, ba.created_at AS acquired_date
            FROM {$this->badge_level_table} AS bl
            LEFT JOIN {$this->badge_acquired_table} AS ba ON bl.id = ba.level_id
            WHERE ba.badge_seen = 0 $inner_where
            ) AS bla";
            $join    = " LEFT JOIN {$this->badge_table} AS sb ON sb.id = bla.badge_id";
            $orderby = ' ORDER BY bla.badge_id ASC, bla.level ASC';
        } elseif ( 'acquired_vendors_by_badge' === $args['return'] ) {
            $fields  = ' DISTINCT( acquired.vendor_id ) as vendor_id';
            $join    .= " RIGHT JOIN $this->badge_level_table AS level ON level.badge_id = sb.id";
            $join    .= " RIGHT JOIN $this->badge_acquired_table AS acquired ON acquired.level_id = level.id";
            $where   .= " AND acquired.acquired_status='published'";
            $orderby = ' ORDER BY sb.id ASC';
        } elseif ( 'vendor_acquired_badges' === $args['return'] ) {
            $fields       = ' sb.*, acquired.acquired_levels AS acquired_levels';
            $from         = " ( SELECT count( bl.id ) AS acquired_levels, bl.badge_id
                    FROM {$this->badge_level_table} AS bl
                    LEFT JOIN {$this->badge_acquired_table} AS ba ON bl.id = ba.level_id
                    WHERE ba.vendor_id = %d AND ba.acquired_status = 'published'
                    GROUP BY bl.badge_id
                ) AS acquired ";
            $join         = " LEFT JOIN {$this->badge_table} AS sb ON sb.id = acquired.badge_id";
            $orderby      = ' ORDER BY sb.id ASC';
            $query_args[] = $args['vendor_id'];
        } elseif ( 'vendor_acquired_badge_levels' === $args['return'] ) {
            $fields = ' acquired.*';
            $join   .= " RIGHT JOIN $this->badge_level_table AS level ON level.badge_id = sb.id";
            $join   .= " RIGHT JOIN $this->badge_acquired_table AS acquired ON acquired.level_id = level.id";

            // acquired badge status filter
            if ( ! $this->is_empty( $args['acquired_badge_status'] ) && 'all' !== $args['acquired_badge_status'] ) {
                $where        .= " AND acquired.acquired_status = %s";
                $query_args[] = sanitize_text_field( $args['acquired_badge_status'] );
            }
            // filter by vendor id
            if ( ! $this->is_empty( $args['vendor_id'] ) ) {
                $vendor_ids = implode( ",", array_filter( array_map( 'absint', (array) $args['vendor_id'] ) ) );
                $where      .= " AND acquired.vendor_id IN ($vendor_ids)";
            }
            $orderby = ' ORDER BY level.level ASC, level.id ASC';
        } else {
            // get badge data with acquired vendor count
            // vendor id filtering
            if ( ! $this->is_empty( $args['vendor_id'] ) ) {
                $vendor_ids  = implode( ",", array_filter( array_map( 'absint', (array) $args['vendor_id'] ) ) );
                $inner_where .= " AND ba.vendor_id IN ($vendor_ids)";
            }

            // acquired badge status filter
            if ( ! empty( $args['acquired_badge_status'] ) && 'all' !== $args['acquired_badge_status'] ) {
                $acquired_badge_status = sanitize_text_field( $args['acquired_badge_status'] );
                $inner_where           .= " AND ba.acquired_status = '$acquired_badge_status'";
            }

            $fields = 'sb.*, coalesce(vc.total_vendors,0) AS vendor_count, coalesce(vc.acquired_level_count,0) AS acquired_level_count, coalesce(acquired_data, 0) AS acquired_data';

            $from = " (
                SELECT badge_id, count(vendor_id) AS total_vendors, acquired_level_count, acquired_data
                FROM (
                    SELECT bl.badge_id, ba.vendor_id, count(ba.level_id) as acquired_level_count, MAX(ba.acquired_data) as acquired_data
                    FROM {$this->badge_level_table} AS bl
                    INNER JOIN {$this->badge_acquired_table} AS ba ON ba.level_id = bl.id
                    WHERE 1=1 $inner_where
                    GROUP BY bl.badge_id, ba.vendor_id
                ) AS vendor_count
                GROUP BY badge_id
                ) AS vc";

            $join = "RIGHT JOIN {$this->badge_table} AS sb ON sb.id = vc.badge_id";
        }

        // badge id filtering
        if ( ! $this->is_empty( $args['badge_id'] ) ) {
            $badge_ids = implode( ",", array_filter( array_map( 'absint', (array) $args['badge_id'] ) ) );
            $where     .= " AND sb.id IN ($badge_ids)";
        }

        // badge name filtering
        if ( ! $this->is_empty( $args['badge_name'] ) ) {
            $where        .= ' AND sb.badge_name LIKE %s';
            $query_args[] = '%' . $wpdb->esc_like( sanitize_text_field( $args['badge_name'] ) ) . '%';
        }

        // badge status filtering
        if ( 'badge_count' !== $args['return'] && ! $this->is_empty( $args['badge_status'] ) && 'all' !== $args['badge_status'] ) {
            $where        .= ' AND sb.badge_status = %s';
            $query_args[] = sanitize_text_field( $args['badge_status'] );
        }

        // event type filtering
        if ( ! $this->is_empty( $args['event_type'] ) && in_array( $args['event_type'], Helper::get_dokan_seller_badge_events( '', true ), true ) ) {
            $where        .= ' AND sb.event_type = %s';
            $query_args[] = sanitize_text_field( $args['event_type'] );
        }

        // fix order by parameters
        $available_order_by_param = [
            'badge_id'      => 'sb.id',
            'badge_name'    => 'sb.badge_name',
            'badge_status'  => 'sb.badge_status',
            'badge_created' => 'sb.created_at',
            'event_type'    => 'sb.event_type',
        ];

        // sort by vendor_count
        if ( ! in_array( $args['return'], [ 'badge_count', 'vendor_acquired_badges' ], true ) ) {
            $available_order_by_param['vendor_count'] = 'vendor_count';
        }

        // badge level filtering
        if ( in_array( $args['return'], [ 'vendor_acquired_badges' ], true ) ) {
            $available_order_by_param['level_id'] = 'level.id';
            $available_order_by_param['level']    = 'level.level';
        }

        // order by parameter
        if ( empty( $orderby ) && ! $this->is_empty( $args['orderby'] ) && array_key_exists( $args['orderby'], $available_order_by_param ) ) {
            $order   = in_array( strtolower( $args['order'] ), [ 'asc', 'desc' ], true ) ? strtoupper( $args['order'] ) : 'ASC';
            $orderby = "ORDER BY {$available_order_by_param[ $args['orderby'] ]} {$order}"; //no need for prepare, we've already whitelisted the parameters

            //second order by in case of similar value on first order by field
            if ( 'badge_id' !== $args['orderby'] ) {
                $orderby .= ", sb.id {$order}";
            }
        }

        // pagination parameters
        if ( ! $this->is_empty( $args['per_page'] ) && - 1 !== intval( $args['per_page'] ) ) {
            $limit  = absint( $args['per_page'] );
            $page   = absint( $args['page'] );
            $page   = $page ? $page : 1;
            $offset = ( $page - 1 ) * $limit;

            $limits       = 'LIMIT %d, %d';
            $query_args[] = $offset;
            $query_args[] = $limit;
        }

        // caching parameters
        $cache_group = 'seller_badges';
        $cache_key   = 'get_badges_' . md5( wp_json_encode( $args ) );
        $results     = Cache::get( $cache_key, $cache_group );

        if ( false !== $results ) {
            return $results;
        }

        if ( 'badge_count' === $args['return'] ) {
            // @codingStandardsIgnoreStart
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT $fields FROM $from $join WHERE %d=%d $where $groupby",
                    $query_args
                )
            );
            // @codingStandardsIgnoreEnd

            if ( ! empty( $wpdb->last_error ) ) {
                // translators: 1) query error
                return new WP_Error( 'get_seller_badges_db_error', sprintf( __( 'Database Error: %s', 'dokan' ), $wpdb->last_error ) );
            }

            $counts = [
                'all'       => 0,
                'published' => 0,
                'draft'     => 0,
            ];

            foreach ( $results as $row ) {
                $counts[ $row->badge_status ] = (int) $row->badge_count;
                $counts['all']                += $counts[ $row->badge_status ];
            }

            $results = (object) $counts;

            Cache::set( $cache_key, $results, $cache_group );
        } else {
            // @codingStandardsIgnoreStart
            $results = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT $fields FROM $from $join WHERE %d=%d $where $groupby $orderby $limits",
                    $query_args
                )
            );
            // @codingStandardsIgnoreEnd
            if ( ! empty( $wpdb->last_error ) ) {
                // translators: 1) query error
                return new WP_Error( 'get_seller_badges_db_error', sprintf( __( 'Database Error: %s', 'dokan' ), $wpdb->last_error ) );
            }

            if ( ! empty( $results ) ) {
                Cache::set( $cache_key, $results, $cache_group );
            }
        }

        return $results;
    }

    /**
     * Get seller badge count
     *
     * @since 3.7.14
     *
     * @param array $args
     *
     * @return object|WP_Error
     */
    public function get_badge_count( $args = [] ) {
        $args['return']   = 'badge_count';
        $args['per_page'] = - 1;

        return $this->all( $args );
    }

    /**
     * Get a single badge details
     *
     * @since 3.7.14
     *
     * @param array $args
     *
     * @return object|WP_Error
     */
    public function get_badge( $args = [] ) {
        $defaults = [
            'badge_id'              => 0,
            'with_levels'           => false,
            'vendor_id'             => 0,
            'acquired_badge_status' => 'published',
            'return'                => 'all',
        ];
        $args     = wp_parse_args( $args, $defaults );

        if ( empty( $args['badge_id'] ) ) {
            return new WP_Error( 'invalid_badge_id', __( 'Please provide a valid badge id.', 'dokan' ) );
        }

        // get badge data
        $badge_data = $this->all( $args );
        if ( is_wp_error( $badge_data ) ) {
            return $badge_data;
        }

        if ( empty( $badge_data ) ) {
            // translators: 1) badge id
            return new WP_Error( 'invalid-badge-id', sprintf( __( 'No badge found with given badge id, badge id: %d', 'dokan' ), $args['badge_id'] ) );
        }

        $badge_data = $badge_data[0];

        if ( $args['vendor_id'] && 'years_active' === $badge_data->event_type ) {
            $badge_data->acquired_level_count = Helper::get_vendor_year_count( $args['vendor_id'] );
        }

        if ( false === $args['with_levels'] ) {
            return $badge_data;
        }

        // get badge level
        $badge_levels = $this->get_badge_levels( $args );
        if ( is_wp_error( $badge_levels ) ) {
            return $badge_levels;
        }

        $badge_data->levels = $badge_levels;

        return $badge_data;
    }

    /**
     * Get all seller badge with acquired vendor badge count
     *
     * @since 3.7.14
     *
     * @param array $args
     *
     * @return object[]|WP_Error
     */
    public function get_seller_badges( $args = [] ) {
        $args['return']                = 'all';
        $args['acquired_badge_status'] = 'published';

        return $this->all( $args );
    }

    /**
     * This method will return all available seller badges from database.
     *
     * @since 3.7.14
     *
     * @return WP_Error|Object[]
     */
    public function get_all_seller_badges() {
        $transient_key   = 'get_all_seller_badges';
        $transient_group = 'seller_badges';
        $seller_badges   = Cache::get_transient( $transient_key, $transient_group );
        if ( false !== $seller_badges ) {
            return $seller_badges;
        }

        $args          = [
            'per_page'     => - 1,
            'badge_status' => 'all',
            'return'       => 'all',
        ];
        $seller_badges = $this->all( $args );

        if ( is_wp_error( $seller_badges ) ) {
            return $seller_badges;
        }

        if ( ! empty( $seller_badges ) ) {
            Cache::set_transient( $transient_key, $seller_badges, $transient_group, MONTH_IN_SECONDS );
        }

        return $seller_badges;
    }

    /**
     * Get unseen badges for a vendor
     *
     * @since 3.7.14
     *
     * @param array $args
     *
     * @return object[]|WP_Error
     */
    public function get_unseen_badges_by_vendor( $args = [] ) {
        $defaults = [
            'vendor_id'             => [],
            'badge_id'              => [],
            'badge_name'            => '',
            'event_type'            => [],
            'badge_status'          => 'published',
            'acquired_badge_status' => 'published',
            'per_page'              => - 1,
        ];

        $args                          = wp_parse_args( $args, $defaults );
        $args['badge_status']          = 'published';
        $args['acquired_badge_status'] = 'published';
        $args['per_page']              = - 1;
        $args['return']                = 'vendor_unseen_badges';

        // vendor id can't be empty
        if ( empty( $args['vendor_id'] ) ) {
            return new WP_Error( 'invalid-vendor-id', __( 'Please provide a valid vendor id.', 'dokan' ) );
        }

        $badges = $this->all( $args );
        if ( is_wp_error( $badges ) ) {
            return $badges;
        }

        $formated_badges = [];
        foreach ( $badges as $badge ) {
            if ( ! array_key_exists( $badge->badge_id, $formated_badges ) ) {
                $temp_badge = [
                    'id'                   => $badge->badge_id,
                    'badge_name'           => $badge->badge_name,
                    'badge_logo'           => $badge->badge_logo,
                    'event_type'           => $badge->event_type,
                    'badge_status'         => $badge->badge_status,
                    'level_count'          => $badge->level_count,
                    'vendor_count'         => 0,
                    'acquired_level_count' => 0,
                    'created_by'           => $badge->created_by,
                    'created_at'           => $badge->created_at,
                    'levels'               => [],
                ];

                // store badge data
                $formated_badges[ $badge->badge_id ] = (object) $temp_badge;
            }

            // update level
            $temp_level = [
                'id'              => $badge->level_id,
                'badge_id'        => $badge->badge_id,
                'level'           => $badge->level,
                'level_condition' => $badge->level_condition,
                'level_data'      => $badge->level_data,
                'vendor_count'    => 0,
            ];

            // update level acquired
            $temp_acquied_level = [
                'id'              => $badge->badge_acquired_id,
                'vendor_id'       => $args['vendor_id'],
                'level_id'        => $badge->level_id,
                'acquired_data'   => $badge->acquired_data,
                'acquired_status' => 'published',
                'badge_seen'      => 0,
                'created_at'      => $badge->acquired_date,
            ];

            $formated_badges[ $badge->badge_id ]->levels[]   = (object) $temp_level;
            $formated_badges[ $badge->badge_id ]->acquired[] = (object) $temp_acquied_level;
        }

        return $formated_badges;
    }

    /**
     * This method will return badge data with given badge key.
     *
     * @since 3.7.14
     *
     * @param string $event_type
     *
     * @return Object|WP_Error
     */
    public function get_badge_data_by_event_type( $event_type ) {
        $all_badges = $this->get_all_seller_badges();
        if ( is_wp_error( $all_badges ) ) {
            return $all_badges;
        }

        foreach ( $all_badges as $badge_data ) {
            if ( $badge_data->event_type === $event_type ) {
                return $badge_data;
            }
        }

        return new WP_Error( 'no-badge-data', __( 'No badge data found with given event type.', 'dokan' ) );
    }

    /**
     * Get badge level for a single badge
     *
     * @since 3.7.14
     *
     * @param array $args
     *
     * @return object[]|WP_Error
     */
    public function get_badge_levels( $args = [] ) {
        $defaults = [
            'badge_id'              => 0,
            'vendor_id'             => 0,
            'acquired_badge_status' => 'published',
        ];
        $args     = wp_parse_args( $args, $defaults );

        if ( empty( $args['badge_id'] ) ) {
            return new WP_Error( 'invalid_badge_id', __( 'Please provide a valid badge id.', 'dokan' ) );
        }

        $data = [
            'badge_id'              => $args['badge_id'],
            'vendor_id'             => $args['vendor_id'],
            'acquired_badge_status' => $args['acquired_badge_status'],
            'per_page'              => - 1,
            'return'                => 'badge_levels',
        ];

        // fix level count
        $levels = $this->all( $data );
        $length = count( $levels );
        $current_value = $levels[ $length - 1 ]->vendor_count;
        for ( $i = $length - 2; $i >= 0; $i-- ) {
            if ( ! $levels[ $i ]->vendor_count ) {
                continue;
            }
            $levels[ $i ]->vendor_count = $levels[ $i ]->vendor_count - $current_value;
            $current_value += $levels[ $i ]->vendor_count;
        }

        return $levels;
    }

    /**
     * Get acquired vendors by badge id
     *
     * @since 3.7.14
     *
     * @param int $badge_id
     * @param int $per_page
     *
     * @return int[]|WP_Error
     */
    public function get_acquired_vendors_by_badge_id( $badge_id = 0, $per_page = - 1 ) {
        if ( empty( $badge_id ) ) {
            return new WP_Error( 'invalid_badge_id', __( 'Please provide a valid badge id.', 'dokan' ) );
        }

        $args = [
            'badge_id'     => $badge_id,
            'per_page'     => $per_page,
            'return'       => 'acquired_vendors_by_badge',
        ];

        $acquired_vendor_ids = $this->all( $args );

        if ( is_wp_error( $acquired_vendor_ids ) ) {
            return $acquired_vendor_ids;
        }

        if ( empty( $acquired_vendor_ids ) ) {
            return [];
        }

        return wp_list_pluck( $acquired_vendor_ids, 'vendor_id' );
    }

    /**
     * Get vendor acquired badges
     *
     * @since 3.7.14
     *
     * @param $vendor_id
     *
     * @return object[]|WP_Error
     */
    public function get_vendor_acquired_badges( $vendor_id ) {
        if ( empty( $vendor_id ) || ! is_numeric( $vendor_id ) ) {
            return new WP_Error( 'invalid-vendor-id', __( 'Please provide a valid vendor id.', 'dokan' ) );
        }

        $args = [
            'badge_status' => 'published',
            'vendor_id'    => $vendor_id,
            'per_page'     => - 1,
            'return'       => 'vendor_acquired_badges',
        ];

        return $this->all( $args );
    }

    /**
     * This method will return acquired levels for a specific badge
     *
     * @since 3.7.14
     *
     * @param int    $vendor_id
     * @param int    $badge_id
     * @param string $acquired_badge_status
     *
     * @return object[]|WP_Error
     */
    public function get_vendor_acquired_levels_by_badge_id( $vendor_id, $badge_id, $acquired_badge_status = 'all' ) {
        // validate required data first
        if ( empty( $vendor_id ) || empty( $badge_id ) ) {
            return new WP_Error( 'invalid-data', __( 'Please provide valid badge id and vendor id.', 'dokan' ) );
        }

        $args = [
            'vendor_id'             => $vendor_id,
            'badge_id'              => $badge_id,
            'acquired_badge_status' => $acquired_badge_status,
            'per_page'              => - 1,
            'return'                => 'vendor_acquired_badge_levels',
        ];

        return $this->all( $args );
    }

    /**
     * Create Seller Badge Data
     *
     * @since 3.7.14
     *
     * @param array $args
     *
     * @return WP_Error|array
     */
    public function create_seller_badge( $args = [] ) {
        global $wpdb;
        //insert or update badge data
        $defaults = [
            'id'           => 0,
            'badge_name'   => '',
            'badge_logo'   => '',
            'event_type'   => '',
            'badge_status' => '',
            'created_by'   => get_current_user_id(),
            'created_at'   => dokan_current_datetime()->getTimestamp(),
            'levels'       => [],
        ];

        $args   = wp_parse_args( $args, $defaults );
        $data   = [];
        $format = [];

        // validate data before insert
        if ( empty( $args['badge_name'] ) ) {
            return new WP_Error( 'invalid-badge-name', __( 'Please provide a valid badge name.', 'dokan' ) );
        } else {
            $data['badge_name'] = sanitize_text_field( $args['badge_name'] );
            $format[]           = '%s';
        }

        // validate event type
        if ( empty( $args['event_type'] ) || ! in_array( $args['event_type'], Helper::get_dokan_seller_badge_events( '', true ), true ) ) {
            return new WP_Error( 'invalid-badge-event', __( 'Please provide a valid badge event type.', 'dokan' ) );
        } else {
            $data['event_type'] = sanitize_text_field( $args['event_type'] );
            $format[]           = '%s';
        }

        // get event object
        $badge_event = Helper::get_dokan_seller_badge_events( $data['event_type'] );
        if ( is_wp_error( $badge_event ) ) {
            return $badge_event;
        }

        // check event exists in dabatase
        $event_exists = $this->get_seller_badges( [
            'event_type' => $args['event_type'],
            'per_page'   => 1,
        ] );

        if ( is_wp_error( $event_exists ) ) {
            return $event_exists;
        }

        if ( ! empty( $event_exists ) ) {
            return new WP_Error( 'invalid-event-type', __( 'Provided event type already exists in database. Please select another event type.', 'dokan' ) );
        }

        if ( is_numeric( $args['badge_logo_raw'] ) ) {
            $data['badge_logo'] = intval( $args['badge_logo_raw'] );
            $format[]           = '%d';
        } else {
            // store only logo filename
            $event = Helper::get_dokan_seller_badge_events( $args['event_type'] );
            if ( is_wp_error( $event ) ) {
                return $event;
            }
            $data['badge_logo'] = $event->get_badge_logo();
            $format[]           = '%s';
        }

        if ( ! empty( $args['badge_status'] ) && array_key_exists( $args['badge_status'], Helper::get_formatted_event_status() ) ) {
            $data['badge_status'] = sanitize_text_field( $args['badge_status'] );
            $format[]             = '%s';
        } else {
            $data['badge_status'] = 'draft';
            $format[]             = '%s';
        }

        if ( is_array( $args['levels'] ) && ! empty( $args['levels'] ) ) {
            $data['level_count'] = count( $args['levels'] );
            $format[]            = '%d';
        }

        $data['created_by'] = absint( $args['created_by'] );
        $data['created_at'] = absint( $args['created_at'] );
        $format[]           = '%d';
        $format[]           = '%d';

        try {
            $wpdb->query( 'START TRANSACTION' );

            // insert row
            $wpdb->insert( $this->badge_table, $data, $format );
            if ( $wpdb->last_error ) {
                throw new Exception(
                /* translators: error message */
                    sprintf( __( 'Could not update badge data. Error: %s', 'dokan' ), $wpdb->last_error )
                );
            }

            $return_data = [ 'id' => $wpdb->insert_id, 'action' => 'insert' ];

            // now insert/update badge level data if available
            $badge_level_data = $this->update_badge_level_data( $return_data['id'], $args['levels'] );
            if ( is_wp_error( $badge_level_data ) ) {
                throw new Exception( $badge_level_data->get_error_message() );
            }
            $data['updated_levels'] = $badge_level_data;
            $data['id']             = $return_data['id'];

            // commit transaction
            $wpdb->query( 'COMMIT' );

            // call responsible hooks
            do_action( 'dokan_seller_badge_created', $return_data['id'], $data );
            do_action( 'dokan_seller_badge_' . $data['event_type'] . '_created', $return_data['id'], $data );

            return $return_data;
        } catch ( Exception $e ) {
            $wpdb->query( 'ROLLBACK' );

            return new WP_Error(
                'update-badge-data-error',
                $e->getMessage()
            );
        }
    }

    /**
     * Insert or update a single badge data
     *
     * @since 3.7.14
     *
     * @param array $args
     *
     * @return array|WP_Error
     */
    public function update_seller_badge( $args = [] ) {
        global $wpdb;
        //insert or update badge data
        $defaults = [
            'id'           => 0,
            'badge_name'   => '',
            'badge_logo'   => '',
            'event_type'   => '',
            'badge_status' => '',
            'created_by'   => get_current_user_id(),
            'created_at'   => dokan_current_datetime()->getTimestamp(),
            'levels'       => [],
        ];

        $args         = wp_parse_args( $args, $defaults );
        $data         = [];
        $format       = [];
        $where        = [];
        $where_format = [];

        // validate if we've got a valid badge id
        $badge_data = $this->get_badge( [ 'badge_id' => $args['id'] ] );
        if ( is_wp_error( $badge_data ) ) {
            return $badge_data;
        }

        // get badge event
        $badge_event = Helper::get_dokan_seller_badge_events( $badge_data->event_type );
        if ( is_wp_error( $badge_event ) ) {
            return $badge_event;
        }

        // check if same event type data exists on database
        // todo: move this check under get_seller_badges() method with exclude param
        $badge_exists = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT 1 FROM {$this->badge_table} WHERE event_type = %s AND id != %d",
                [ $badge_data->event_type, $args['id'] ]
            )
        );

        if ( ! empty( $badge_exists ) ) {
            return new WP_Error( 'invalid_event_type', __( 'Badge exists with given event type, please select a different event type.', 'dokan' ) );
        }

        if ( ! empty( $args['badge_name'] ) ) {
            $data['badge_name'] = sanitize_text_field( $args['badge_name'] );
            $format[]           = '%s';
        }

        if ( ! empty( $args['badge_logo_raw'] ) ) {
            if ( is_numeric( $args['badge_logo_raw'] ) ) {
                $data['badge_logo'] = intval( $args['badge_logo_raw'] );
                $format[]           = '%d';
            } else {
                $data['badge_logo'] = sanitize_file_name( $args['badge_logo_raw'] );
                $format[]           = '%s';
            }
        }

        if ( ! empty( $args['badge_status'] ) && array_key_exists( $args['badge_status'], Helper::get_formatted_event_status() ) ) {
            $data['badge_status'] = sanitize_text_field( $args['badge_status'] );
            $format[]             = '%s';
        }

        if ( is_array( $args['levels'] ) && ! empty( $args['levels'] ) ) {
            $data['level_count'] = count( $args['levels'] );
            $format[]            = '%d';
        }

        $where['id']    = absint( $args['id'] );
        $where_format[] = '%d';

        try {
            $wpdb->query( 'START TRANSACTION' );

            // update row
            $wpdb->update( $this->badge_table, $data, $where, $format, $where_format );
            if ( $wpdb->last_error ) {
                throw new Exception(
                /* translators: error message */
                    sprintf( __( 'Could not update badge data. Error: %s', 'dokan' ), $wpdb->last_error )
                );
            }
            $return_data = [ 'id' => absint( $badge_data->id ), 'action' => 'update' ];

            // now insert/update badge level data if available
            $badge_level_data = 0;
            if ( ! empty( $args['levels'] ) ) {
                $badge_level_data = $this->update_badge_level_data( $return_data['id'], $args['levels'] );
                if ( is_wp_error( $badge_level_data ) ) {
                    throw new Exception( $badge_level_data->get_error_message() );
                }
            }

            // what if status is changed from draft to published
            if ( 'published' === $data['badge_status'] && 'draft' === $badge_data->badge_status ) {
                $badge_level_data += 10; // short circuit so that background process can run
            }

            $data['updated_levels'] = $badge_level_data;
            $data['id']             = $return_data['id'];

            // commit transaction
            $wpdb->query( 'COMMIT' );

            // call required hooks
            do_action( 'dokan_seller_badge_updated', $badge_data->id, $data );
            do_action( 'dokan_seller_badge_' . $badge_data->event_type . '_updated', $badge_data->id, $data );

            return $return_data;
        } catch ( Exception $e ) {
            $wpdb->query( 'ROLLBACK' );

            return new WP_Error(
                'update-badge-data-error',
                $e->getMessage()
            );
        }
    }

    /**
     * Insert or update badge level data
     *
     * @since 3.7.14
     *
     * @param int   $badge_id
     * @param array $badge_level_data
     *
     * @return WP_Error|int Error on failure, effected row count on success
     */
    public function update_badge_level_data( $badge_id, $badge_level_data = [] ) {
        global $wpdb;

        if ( empty( $badge_id ) ) {
            return new WP_Error( 'invalid-badge-id', __( 'Please provide valid badge id.', 'dokan' ) );
        }

        if ( empty( $badge_level_data ) ) {
            return new WP_Error( 'invalid-level-data', __( 'Please provide valid badge level data.', 'dokan' ) );
        }

        $badge_data = $this->get_badge( [ 'badge_id' => $badge_id ] );
        if ( is_wp_error( $badge_data ) ) {
            return $badge_data;
        }

        // get badge event
        $badge_event = Helper::get_dokan_seller_badge_events( $badge_data->event_type );

        switch ( $badge_data->event_type ) {
            case 'featured_products':
            case 'featured_seller':
            case 'exclusive_to_platform':
            case 'years_active':
                // no need to validate this badge
                $saved_level_data        = reset( $badge_level_data );
                $badge_level             = [
                    'level'           => 1,
                    'level_data'      => '',
                    'level_condition' => '',
                ];
                $badge_level['id']       = isset( $saved_level_data['id'] ) ? $saved_level_data['id'] : 0;
                $badge_level['badge_id'] = isset( $saved_level_data['badge_id'] ) ? $saved_level_data['badge_id'] : 0;
                $badge_level_data        = [
                    $badge_level,
                ];
                break;
            case 'verified_seller':
                // validate only condition, not data
                // this will make sure no empty data is provided
                $badge_level_data = array_filter( $badge_level_data, function ( $badge_level ) {
                    return ! empty( $badge_level['level_condition'] );
                } );

                if ( empty( $badge_level_data ) ) {
                    return new WP_Error( 'invalid-level-data', __( 'Please provide valid badge level data', 'dokan' ) );
                }
                break;
            case 'customer_review':
                // validate level data only
                // this will make sure no empty data is provided
                $badge_level_data = array_filter( $badge_level_data, function ( $badge_level ) {
                    return ! empty( $badge_level['level_data'] );
                } );

                if ( empty( $badge_level_data ) ) {
                    return new WP_Error( 'invalid-level-data', __( 'Please provide valid badge level data', 'dokan' ) );
                }
                break;
            default:
                // this will make sure no empty data is provided
                $badge_level_data = array_filter( $badge_level_data, function ( $badge_level ) {
                    return ! empty( $badge_level['level_condition'] ) && ! empty( $badge_level['level_data'] );
                } );

                if ( empty( $badge_level_data ) ) {
                    return new WP_Error( 'invalid-level-data', __( 'Please provide valid badge level data', 'dokan' ) );
                }
        }

        // sort badge level data, this will make sure we are getting sorted level data
        usort( $badge_level_data, function ( $a, $b ) {
            $item1 = intval( $a['level_data'] );
            $item2 = intval( $b['level_data'] );
            if ( $item1 === $item2 ) {
                return 0;
            }

            return $item1 < $item2 ? - 1 : 1;
        } );

        // update level
        $i = 1;
        foreach ( $badge_level_data as &$level_data ) {
            $level_data['level'] = $i++;
        }

        // get all level id
        $level_ids = array_filter( wp_list_pluck( $badge_level_data, 'id' ) );

        // delete level ids if not exists
        $deleted = 0;
        if ( ! empty( $level_ids ) ) {
            $level_ids = implode( ",", array_filter( array_map( 'absint', $level_ids ) ) );
            // get unwanted level ids
            $unwanted_level_ids = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT id FROM $this->badge_level_table WHERE id NOT IN ( $level_ids ) AND badge_id = %d",
                    $badge_id
                )
            );

            if ( ! empty( $unwanted_level_ids ) ) {
                $unwanted_level_ids = implode( ",", array_filter( array_map( 'absint', $unwanted_level_ids ) ) );
                $deleted            = $wpdb->query(
                    "DELETE FROM $this->badge_level_table WHERE id IN ($unwanted_level_ids)"
                );

                if ( false === $deleted ) {
                    return new WP_Error( 'delete-badge-level-error', sprintf( __( 'Database query error: %s', 'dokan' ), $wpdb->last_error ) );
                }

                // delete vendor badge acquired data
                $acquired_level_deleted = $wpdb->query(
                    "DELETE FROM $this->badge_acquired_table WHERE level_id IN ($unwanted_level_ids)"
                );

                if ( false === $acquired_level_deleted ) {
                    return new WP_Error( 'delete-acquired-badge-level-error', sprintf( __( 'Database query error: %s', 'dokan' ), $wpdb->last_error ) );
                }
            }
        }

        // insert data
        $insert_or_update_query = "INSERT INTO `{$this->badge_level_table}` (id, badge_id, level, level_condition, level_data) VALUES  ";
        $validated_data         = [];
        foreach ( $badge_level_data as $badge_level ) {
            if ( ! is_array( $badge_level ) ) {
                return new WP_Error( 'invalid-level-data', __( 'Please provide valid badge level data', 'dokan' ) );
            }

            $insert_or_update_query .= ' ( %d, %d, %s, %s, %s ),';

            $validated_data[] = $badge_level['id'] ? absint( $badge_level['id'] ) : 0;
            $validated_data[] = absint( $badge_id );
            $validated_data[] = absint( $badge_level['level'] );
            $validated_data[] = $badge_level['level_condition'] ?: '';
            $validated_data[] = $badge_level['level_data'] ? sanitize_text_field( $badge_level['level_data'] ) : '';
        }
        $insert_or_update_query = rtrim( $insert_or_update_query, ',' );

        $insert_or_update_query .= "  ON DUPLICATE KEY UPDATE badge_id=values(badge_id), level=values(level), level_condition=values(level_condition), level_data=values(level_data)";
        $insert_or_update_query = $wpdb->prepare( $insert_or_update_query, $validated_data );

        $inserted = $wpdb->query( $insert_or_update_query );

        if ( false === $inserted ) {
            // translators: 1) database query error
            return new WP_Error( 'insert-badge-level-failed', sprintf( __( 'Error during updating badge level data: %s', 'dokan' ), $wpdb->last_error ) );
        }

        $total_updated = $deleted + $inserted;
        // set all badge status as draft if we updated badge levels
        if ( $total_updated > 0 && $badge_event->has_multiple_levels() ) {
            $updated = $this->remove_all_acquired_data_for_a_badge( $badge_id );
            if ( false === $updated ) {
                // translators: 1) database query error
                return new WP_Error( 'insert-badge-level-failed', sprintf( __( 'Error during updating badge acquired data: %s', 'dokan' ), $wpdb->last_error ) );
            }
        }

        return $deleted + $inserted;
    }

    /**
     * Insert or update vendor acquired badge levels data
     *
     * @since 3.7.14
     *
     * @param array[] $acquired_badge_level_data
     *
     * @return void|WP_Error
     */
    public function update_vendor_acquired_badge_levels_data( $acquired_badge_level_data = [] ) {
        global $wpdb;
        // insert data
        $insert_or_update_query = "INSERT INTO `{$this->badge_acquired_table}` (id, vendor_id, level_id, acquired_data, acquired_status, badge_seen, created_at) VALUES  ";
        $validated_data         = [];
        foreach ( $acquired_badge_level_data as $badge_level ) {
            if ( ! is_array( $badge_level ) ) {
                return new WP_Error( 'invalid-level-data', __( 'Please provide valid badge level data', 'dokan' ) );
            }

            $insert_or_update_query .= ' ( %d, %d, %d, %s, %s, %d, %d ),';

            $validated_data[] = $badge_level['id'] ? absint( $badge_level['id'] ) : 0;
            $validated_data[] = $badge_level['vendor_id'] ? absint( $badge_level['vendor_id'] ) : 0;
            $validated_data[] = $badge_level['level_id'] ? absint( $badge_level['level_id'] ) : 0;
            $validated_data[] = $badge_level['acquired_data'] ? sanitize_text_field( $badge_level['acquired_data'] ) : '';
            $validated_data[] = $badge_level['acquired_status'] ? sanitize_text_field( $badge_level['acquired_status'] ) : '';
            $validated_data[] = $badge_level['badge_seen'] ? absint( $badge_level['badge_seen'] ) : 0;
            $validated_data[] = $badge_level['created_at'] ? absint( $badge_level['created_at'] ) : 0;
        }
        $insert_or_update_query = rtrim( $insert_or_update_query, ',' );

        $insert_or_update_query .= "  ON DUPLICATE KEY UPDATE vendor_id=values(vendor_id), level_id=values(level_id), acquired_data=values(acquired_data), acquired_status=values(acquired_status), badge_seen=values(badge_seen), created_at=values(created_at)";
        $insert_or_update_query = $wpdb->prepare( $insert_or_update_query, $validated_data );

        $inserted = $wpdb->query( $insert_or_update_query );
        if ( false === $inserted ) {
            // translators: 1) database query error
            return new WP_Error( 'insert-vendor-acquired-badge-level-failed', sprintf( __( 'Error during updating vendor acquired badge level data: %s', 'dokan' ), $wpdb->last_error ) );
        }

        do_action( 'dokan_seller_badge_update_acquired_badges', $acquired_badge_level_data );
    }

    /**
     * Delete Single or multiple Badge Data
     *
     * @since 3.7.14
     *
     * @param int|array $badge_id
     *
     * @return WP_Error|array array if delete is successful otherwise WP_Error
     */
    public function delete_badges( $badge_id ) {
        // check for permission
        if ( ! current_user_can( 'manage_options' ) ) {
            return new WP_Error( 'invalid_permission', __( 'You do not have permission to delete badges.', 'dokan' ) );
        }

        // sanitize badge ids
        $badge_ids = array_filter( array_map( 'absint', (array) $badge_id ) );
        if ( empty( $badge_ids ) ) {
            return new WP_Error( 'invalid_badge_id', __( 'Please provide a valid badge id.', 'dokan' ) );
        }

        $badge_ids = implode( ',', $badge_ids );

        global $wpdb;

        try {
            $wpdb->query( 'START TRANSACTION' );
            // delete vendor acquired badge data
            $wpdb->query(
                "DELETE acquired FROM $this->badge_acquired_table AS acquired
                LEFT JOIN  $this->badge_level_table AS level ON level.id = acquired.level_id
                WHERE level.badge_id IN ( $badge_ids )"
            );

            if ( $wpdb->last_error ) {
                throw new Exception( $wpdb->last_error );
            }

            // delete badge level data
            $wpdb->query(
                "DELETE FROM $this->badge_level_table WHERE badge_id IN ($badge_ids)"
            );

            if ( $wpdb->last_error ) {
                throw new Exception( $wpdb->last_error );
            }

            // delete badge data
            $wpdb->query(
                "DELETE FROM $this->badge_table WHERE id IN ($badge_ids)"
            );

            if ( $wpdb->last_error ) {
                throw new Exception( $wpdb->last_error );
            }

            // commit transaction
            $wpdb->query( 'COMMIT' );

            do_action( 'dokan_seller_badge_deleted', $badge_id );

            return [ 'deleted' => true ];

        } catch ( Exception $e ) {
            $wpdb->query( 'ROLLBACK' );

            return new WP_Error(
                'delete_badge_error',
                /* translators: error message */
                sprintf( __( 'Could not delete badge data. Error: %s', 'dokan' ), $e->getMessage() )
            );
        }
    }

    /**
     * Update badge seen status.
     *
     * @since 3.7.14
     *
     * @param int   $vendor_id
     * @param array $badge_id
     *
     * @return WP_Error|bool
     */
    public function set_badge_status_as_seen( $vendor_id ) {
        global $wpdb;

        if ( empty( $vendor_id ) ) {
            return new WP_Error( 'invalid-vendor-id', __( 'Please provide a valid vendor id.', 'dokan' ) );
        }

        $updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$this->badge_acquired_table} AS ba
                SET ba.badge_seen = 1
                WHERE ba.vendor_id = %d AND ba.acquired_status = %s",
                [ $vendor_id, 'published' ]
            )
        );

        if ( false === $updated ) {
            // translators: 1) database query error
            return new WP_Error( 'update-badge-seen-status-failed', sprintf( __( 'Error during updating vendor acquired badge level data: %s', 'dokan' ), $wpdb->last_error ) );
        }

        // need this to delete cache
        do_action( 'dokan_seller_badge_badge_status_seen', $vendor_id );

        return true;
    }

    /**
     * Set acquired badge status to draft for a specific badge
     *
     * @since 3.7.14
     *
     * @param int $badge_id
     *
     * @return bool|WP_Error
     */
    public function remove_all_acquired_data_for_a_badge( $badge_id ) {
        global $wpdb;

        $updated = $wpdb->query(
            $wpdb->prepare(
                "UPDATE {$this->badge_acquired_table} AS ba
                LEFT JOIN {$this->badge_level_table} AS bl ON bl.id = ba.level_id
                LEFT JOIN {$this->badge_table} AS sb ON sb.id = bl.badge_id
                SET ba.acquired_status = %s
                WHERE sb.id = %d",
                [ 'draft', $badge_id ]
            )
        );

        if ( false === $updated ) {
            // translators: 1) database query error
            return new WP_Error( 'update-badge-acquired-data-error', sprintf( __( 'Error during updating vendor acquired badge level data: %s', 'dokan' ), $wpdb->last_error ) );
        }

        return true;
    }

    /**
     * Check if a variable is empty, if zero is set as default value, this method will return true
     *
     * @since 3.7.14
     *
     * @param mixed $var
     *
     * @return bool
     */
    protected function is_empty( $var ) {
        if ( empty( $var ) ) {
            return true;
        }

        // if var is an array, check if it's empty or not
        if ( is_array( $var ) && isset( $var[0] ) && intval( $var[0] ) === 0 ) {
            return true;
        }

        return false;
    }
}
