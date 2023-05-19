<?php

use WeDevs\Dokan\Cache;

/**
 * Returns the definitions for the reports and charts
 *
 * @since 1.0
 *
 * @return array
 */
function dokan_get_reports_charts() {
    $charts = [
        'title'  => __( 'Sales', 'dokan' ),
        'charts' => [
            'overview'        => [
                'title'       => __( 'Overview', 'dokan' ),
                'description' => '',
                'hide_title'  => true,
                'function'    => 'dokan_sales_overview',
                'permission'  => 'dokan_view_overview_report',
            ],
            'sales_by_day'    => [
                'title'       => __( 'Sales by day', 'dokan' ),
                'description' => '',
                'function'    => 'dokan_daily_sales',
                'permission'  => 'dokan_view_daily_sale_report',
            ],
            'top_sellers'     => [
                'title'       => __( 'Top selling', 'dokan' ),
                'description' => '',
                'function'    => 'dokan_top_sellers',
                'permission'  => 'dokan_view_top_selling_report',
            ],
            'top_earners'     => [
                'title'       => __( 'Top earning', 'dokan' ),
                'description' => '',
                'function'    => 'dokan_top_earners',
                'permission'  => 'dokan_view_top_earning_report',
            ],
            'sales_statement' => [
                'title'       => __( 'Statement', 'dokan' ),
                'description' => '',
                'function'    => 'dokan_seller_sales_statement',
                'permission'  => 'dokan_view_statement_report',
            ],
        ],
    ];

    return apply_filters( 'dokan_reports_charts', $charts );
}

/**
 * Seller sales statement
 *
 * @since
 *
 * @return
 */
function dokan_seller_sales_statement() {
    $start_date = dokan_current_datetime()->modify( 'first day of this month' )->format( 'Y-m-d' );
    $end_date   = dokan_current_datetime()->format( 'Y-m-d' );

    if ( isset( $_GET['dokan_report_filter'] ) && isset( $_GET['dokan_report_filter_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['dokan_report_filter_nonce'] ) ), 'dokan_report_filter' ) && isset( $_GET['start_date_alt'] ) && isset( $_GET['end_date_alt'] ) ) {
        $start_date = dokan_current_datetime()
            ->modify( sanitize_text_field( wp_unslash( $_GET['start_date_alt'] ) ) )
            ->format( 'Y-m-d' );
        $end_date   = dokan_current_datetime()
            ->modify( sanitize_text_field( wp_unslash( $_GET['end_date_alt'] ) ) )
            ->format( 'Y-m-d' );
    } ?>

    <?php
        global $wpdb;
        $vendor = dokan()->vendor->get( dokan_get_current_user_id() );
        $opening_balance = $vendor->get_balance( false, date( 'Y-m-d', strtotime( $start_date . ' -1 days' ) ) );
        $status = implode( "', '", dokan_withdraw_get_active_order_status() );

        $sql = "SELECT * from {$wpdb->prefix}dokan_vendor_balance WHERE vendor_id = %d AND DATE(balance_date) >= %s AND DATE(balance_date) <= %s AND ( ( trn_type = 'dokan_orders' AND status IN ('{$status}') ) OR trn_type IN ( 'dokan_withdraw', 'dokan_refund' ) ) ORDER BY balance_date";
        $statements = $wpdb->get_results( $wpdb->prepare( $sql, $vendor->id, $start_date, $end_date ) );
    ?>

    <form method="get" class="dokan-form-inline report-filter dokan-clearfix" action="" id="dokan-v-dashboard-reports">
        <div class="dokan-form-group">
            <label for="from"><?php _e( 'From:', 'dokan' ); ?></label> <input type="text" class="datepicker" name="start_date" id="from" readonly="readonly" value="<?php echo date_i18n( get_option( 'date_format' ), strtotime( $start_date ) ); ?>" />
        </div>

        <input type="hidden" name="start_date_alt" id="from_alt" value="<?php echo esc_attr( $start_date ); ?>">
        <input type="hidden" name="end_date_alt" id="to_alt" value="<?php echo esc_attr( $end_date ); ?>">
        <?php wp_nonce_field( 'dokan_report_filter', 'dokan_report_filter_nonce' ); ?>

        <div class="dokan-form-group">
            <label for="to"><?php esc_html_e( 'To:', 'dokan' ); ?></label>
            <input type="text" name="end_date" id="to" class="datepicker" readonly="readonly" value="<?php echo date_i18n( get_option( 'date_format' ), strtotime( $end_date ) ); ?>" />

            <input type="hidden" name="chart" value="sales_statement">
            <input type="submit" name="dokan_report_filter" class="dokan-btn dokan-btn-success dokan-btn-sm dokan-theme" value="<?php esc_attr_e( 'Show', 'dokan' ); ?>" />
        </div>
        <input type="submit" name="dokan_statement_export_all" class="dokan-btn dokan-right dokan-btn-sm dokan-btn-danger dokan-btn-theme" <?php echo empty( $statements ) ? 'disabled' : ''; ?> value="<?php esc_attr_e( 'Export All', 'dokan' ); ?>">
    </form>

    <table class="table table-striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Balance Date', 'dokan' ); ?></th>
                <th><?php esc_html_e( 'Trn Date', 'dokan' ); ?></th>
                <th><?php esc_html_e( 'ID', 'dokan' ); ?></th>
                <th><?php esc_html_e( 'Type', 'dokan' ); ?></th>
                <th><?php esc_html_e( 'Debit', 'dokan' ); ?></th>
                <th><?php esc_html_e( 'Credit', 'dokan' ); ?></th>
                <th><?php esc_html_e( 'Balance', 'dokan' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( $opening_balance ) { ?>
                <tr>
                    <td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $start_date ) ); ?></td>
                    <td><?php echo '--'; ?></td>
                    <td><?php echo '--'; ?></td>
                    <td><?php esc_html_e( 'Opening Balance', 'dokan' ); ?></td>
                    <td><?php echo '--'; ?></td>
                    <td><?php echo '--'; ?></td>
                    <td><?php echo wc_price( $opening_balance ); ?></td>
                </tr>
            <?php }

            if ( count( $statements ) ) {
                $total_debit  = 0;
                $total_credit = 0;
                $balance      = $opening_balance;

                foreach ( $statements as $statement ) {
                    $total_debit += $statement->debit;
                    $total_credit += $statement->credit;
                    $balance += $statement->debit - $statement->credit;

                    switch ( $statement->trn_type ) {
                        case 'dokan_orders':
                            $type = __( 'Order', 'dokan' );
                            $url  = wp_nonce_url( add_query_arg( [ 'order_id' => $statement->trn_id ], dokan_get_navigation_url( 'orders' ) ), 'dokan_view_order' );
                            break;

                        case 'dokan_withdraw':
                            $type = __( 'Withdraw', 'dokan' );
                            $url  = add_query_arg( [ 'type' => 'approved' ], dokan_get_navigation_url( 'withdraw' ) );
                            break;

                        case 'dokan_refund':
                            $type = __( 'Refund', 'dokan' );
                            $url  = wp_nonce_url( add_query_arg( [ 'order_id' => $statement->trn_id ], dokan_get_navigation_url( 'orders' ) ), 'dokan_view_order' );
                            break;
                        }
                        ?>
                        <tr>
                            <td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $statement->balance_date ) ); ?></td>
                            <td><?php echo date_i18n( get_option( 'date_format' ), strtotime( $statement->trn_date ) ); ?></td>
                            <td><a href="<?php echo $url; ?>">#<?php echo $statement->trn_id; ?></a></td>
                            <td><?php echo $type; ?></td>
                            <td><?php echo wc_price( $statement->debit ); ?></td>
                            <td><?php echo wc_price( $statement->credit ); ?></td>
                            <td><?php echo wc_price( $balance ); ?></td>
                        </tr>
                    <?php } ?>
                        <tr>
                            <td></td>
                            <td></td>
                            <td></td>
                            <td><b><?php _e( 'Total :', 'dokan' ); ?></b></td>
                            <td><b><?php echo wc_price( $total_debit ); ?></b></td>
                            <td><b><?php echo wc_price( $total_credit ); ?></b></td>
                            <td><b><?php echo wc_price( $balance ); ?></b></td>
                        </tr>
                    <?php
                } else {
                ?>
            <tr>
                <td colspan="6"><?php _e( 'No Result found!', 'dokan' ); ?></td>
            </tr>

            <?php } ?>

        </tbody>
    </table>

    <?php
}

/**
 * Generate SQL query and fetch the report data based on the arguments passed
 *
 * This function was cloned from WC_Admin_Report class.
 *
 * @since 1.0
 *
 * @global WPDB $wpdb
 * @global WP_User $current_user
 *
 * @param array  $args
 * @param string $start_date
 * @param string $end_date
 *
 * @return obj
 */
function dokan_get_order_report_data( $args, $start_date, $end_date, $current_user = false ) {
    global $wpdb;

    if ( ! $current_user ) {
        $current_user = dokan_get_current_user_id();
    }

    $defaults = [
        'data'         => [],
        'where'        => [],
        'where_meta'   => [],
        'query_type'   => 'get_row',
        'group_by'     => '',
        'order_by'     => '',
        'limit'        => '',
        'filter_range' => false,
        'nocache'      => false,
        'debug'        => false,
    ];

    $args = wp_parse_args( $args, $defaults );

    extract( $args );

    if ( empty( $data ) ) {
        return false;
    }

    $select = [];

    foreach ( $data as $key => $value ) {
        $distinct = '';

        if ( isset( $value['distinct'] ) ) {
            $distinct = 'DISTINCT';
        }

        if ( $value['type'] == 'meta' ) {
            $get_key = "meta_{$key}.meta_value";
        } elseif ( $value['type'] == 'post_data' ) {
            $get_key = "posts.{$key}";
        } elseif ( $value['type'] == 'order_item_meta' ) {
            $get_key = "order_item_meta_{$key}.meta_value";
        } elseif ( $value['type'] == 'order_item' ) {
            $get_key = "order_items.{$key}";
        } elseif ( $value['type'] == 'dokan_orders' ) {
            $get_key = "do.{$key}";
        }

        if ( $value['function'] ) {
            $get = "{$value['function']}({$distinct} {$get_key})";
        } else {
            $get = "{$distinct} {$get_key}";
        }

        $select[] = "{$get} as {$value['name']}";
    }

    $query['select'] = 'SELECT ' . implode( ',', $select );
    $query['from']   = "FROM {$wpdb->posts} AS posts";

    // Joins
    $joins       = [];
    $joins['do'] = "LEFT JOIN {$wpdb->prefix}dokan_orders AS do ON posts.ID = do.order_id";

    foreach ( $data as $key => $value ) {
        if ( $value['type'] == 'meta' ) {
            $joins["meta_{$key}"] = "LEFT JOIN {$wpdb->postmeta} AS meta_{$key} ON posts.ID = meta_{$key}.post_id";
        } elseif ( $value['type'] == 'order_item_meta' ) {
            $joins['order_items']            = "LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_items.order_id";
            $joins["order_item_meta_{$key}"] = "LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta_{$key} ON order_items.order_item_id = order_item_meta_{$key}.order_item_id";
        } elseif ( $value['type'] == 'order_item' ) {
            $joins['order_items'] = "LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_items.order_id";
        }
    }

    if ( ! empty( $where_meta ) ) {
        foreach ( $where_meta as $value ) {
            if ( ! is_array( $value ) ) {
                continue;
            }

            $key = is_array( $value['meta_key'] ) ? $value['meta_key'][0] : $value['meta_key'];

            if ( isset( $value['type'] ) && $value['type'] == 'order_item_meta' ) {
                $joins['order_items']            = "LEFT JOIN {$wpdb->prefix}woocommerce_order_items AS order_items ON posts.ID = order_id";
                $joins["order_item_meta_{$key}"] = "LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS order_item_meta_{$key} ON order_items.order_item_id = order_item_meta_{$key}.order_item_id";
            } else {
                // If we have a where clause for meta, join the postmeta table
                $joins["meta_{$key}"] = "LEFT JOIN {$wpdb->postmeta} AS meta_{$key} ON posts.ID = meta_{$key}.post_id";
            }
        }
    }

    $query['join'] = implode( ' ', $joins );

    $query['where'] = "
        WHERE   posts.post_type     = 'shop_order'
        AND     posts.post_status   != 'trash'
        AND     do.seller_id = {$current_user}
        AND     do.order_status IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', [ 'wc-completed', 'wc-processing', 'wc-on-hold', 'wc-refunded', ] ) ) . "')
        AND     do.order_status NOT IN ('wc-cancelled','wc-failed')
        ";

    if ( $filter_range ) {
        $query['where'] .= "
            AND     DATE(post_date) >= '" . $start_date . "'
            AND     DATE(post_date) <= '" . $end_date . "'
        ";
    }

    foreach ( $data as $key => $value ) {
        if ( $value['type'] == 'meta' ) {
            $query['where'] .= " AND meta_{$key}.meta_key = '{$key}'";
        } elseif ( $value['type'] == 'order_item_meta' ) {
            $query['where'] .= " AND order_items.order_item_type = '{$value['order_item_type']}'";
            $query['where'] .= " AND order_item_meta_{$key}.meta_key = '{$key}'";
        }
    }

    if ( ! empty( $where_meta ) ) {
        $relation = isset( $where_meta['relation'] ) ? $where_meta['relation'] : 'AND';

        $query['where'] .= ' AND (';

        foreach ( $where_meta as $index => $value ) {
            if ( ! is_array( $value ) ) {
                continue;
            }

            $key = is_array( $value['meta_key'] ) ? $value['meta_key'][0] : $value['meta_key'];

            if ( strtolower( $value['operator'] ) == 'in' ) {
                if ( is_array( $value['meta_value'] ) ) {
                    $value['meta_value'] = implode( "','", $value['meta_value'] );
                }

                if ( ! empty( $value['meta_value'] ) ) {
                    $where_value = "IN ('{$value['meta_value']}')";
                }
            } else {
                $where_value = "{$value['operator']} '{$value['meta_value']}'";
            }

            if ( ! empty( $where_value ) ) {
                if ( $index > 0 ) {
                    $query['where'] .= ' ' . $relation;
                }

                if ( isset( $value['type'] ) && $value['type'] == 'order_item_meta' ) {
                    if ( is_array( $value['meta_key'] ) ) {
                        $query['where'] .= " ( order_item_meta_{$key}.meta_key   IN ('" . implode( "','", $value['meta_key'] ) . "')";
                    } else {
                        $query['where'] .= " ( order_item_meta_{$key}.meta_key   = '{$value['meta_key']}'";
                    }

                    $query['where'] .= " AND order_item_meta_{$key}.meta_value {$where_value} )";
                } else {
                    if ( is_array( $value['meta_key'] ) ) {
                        $query['where'] .= " ( meta_{$key}.meta_key   IN ('" . implode( "','", $value['meta_key'] ) . "')";
                    } else {
                        $query['where'] .= " ( meta_{$key}.meta_key   = '{$value['meta_key']}'";
                    }

                    $query['where'] .= " AND meta_{$key}.meta_value {$where_value} )";
                }
            }
        }

        $query['where'] .= ')';
    }

    if ( ! empty( $where ) ) {
        foreach ( $where as $value ) {
            if ( strtolower( $value['operator'] ) == 'in' ) {
                if ( is_array( $value['value'] ) ) {
                    $value['value'] = implode( "','", $value['value'] );
                }

                if ( ! empty( $value['value'] ) ) {
                    $where_value = "IN ('{$value['value']}')";
                }
            } else {
                $where_value = "{$value['operator']} '{$value['value']}'";
            }

            if ( ! empty( $where_value ) ) {
                $query['where'] .= " AND {$value['key']} {$where_value}";
            }
        }
    }

    if ( $group_by ) {
        $query['group_by'] = "GROUP BY {$group_by}";
    }

    if ( $order_by ) {
        $query['order_by'] = "ORDER BY {$order_by}";
    }

    if ( $limit ) {
        $query['limit'] = "LIMIT {$limit}";
    }

    $query      = apply_filters( 'dokan_reports_get_order_report_query', $query );
    $query      = implode( ' ', $query );
    $query_hash = md5( $query_type . $query );

    if ( $debug ) {
        printf( '<pre>%s</pre>', print_r( $query, true ) );
    }

    $cache_group = "report_data_seller_{$current_user}";
    $cache_key   = 'wc_report_' . $query_hash;

    $result = Cache::get_transient( $cache_key, $cache_group );
    if ( $debug || $nocache || ( false === $result ) ) {
        $result = apply_filters( 'dokan_reports_get_order_report_data', $wpdb->$query_type( $query ), $data );

        if ( $filter_range ) {
            if ( $end_date === dokan_current_datetime()->format( 'Y-m-d' ) ) {
                $expiration = 60 * 60 * 1; // 1 hour
            } else {
                $expiration = 60 * 60 * 24; // 24 hour
            }
        } else {
            $expiration = 60 * 60 * 24; // 24 hour
        }

        Cache::set_transient( $cache_key, $result, $cache_group, $expiration );
    }

    return $result;
}

function dokan_get_total_refunded_amount( $start_date, $end_date, $seller_id = null ) {
    global $wpdb;

    if( $seller_id === null ) {
        $seller_id = dokan_get_current_user_id();
    }

    return $wpdb->get_var(
        $wpdb->prepare(
            "SELECT SUM(dr.refund_amount) FROM {$wpdb->posts} AS posts
                INNER JOIN $wpdb->dokan_refund AS dr ON posts.ID = dr.order_id
                WHERE posts.post_type = %s AND posts.post_status != %s
                    AND dr.status = %d AND seller_id = %d AND DATE(post_date) >= %s AND DATE(post_date) <= %s",
            'shop_order', 'trash', 1, $seller_id, $start_date, $end_date
        )
    );
}

/**
 * Generate sales overview report chart in report area
 *
 * @since 1.0
 *
 * @return void
 */
function dokan_sales_overview() {
    $start_date = dokan_current_datetime()->modify( 'first day of this month' )->format( 'Y-m-d' );
    $end_date   = dokan_current_datetime()->format( 'Y-m-d' );

    dokan_report_sales_overview( $start_date, $end_date, __( 'This month\'s sales', 'dokan' ) );
}

/**
 * Generate seller dashboard overview chart
 *
 * @since 1.0
 *
 * @return void
 */
function dokan_dashboard_sales_overview() {
    $start_date = dokan_current_datetime()->modify( 'first day of this month' )->format( 'Y-m-d' );
    $end_date   = dokan_current_datetime()->format( 'Y-m-d' );

    dokan_sales_overview_chart_data( $start_date, $end_date, 'day' );
}

/**
 * Generates daily sales report
 *
 * @since 1.0
 *
 * @global WPDB $wpdb
 */
function dokan_daily_sales() {
    global $wpdb;

    $start_date = dokan_current_datetime()->modify( 'first day of this month' )->format( 'Y-m-d' );
    $end_date   = dokan_current_datetime()->format( 'Y-m-d' );

    if ( isset( $_POST['dokan_report_filter'] ) && isset( $_POST['dokan_report_filter_daily_sales_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dokan_report_filter_daily_sales_nonce'] ) ), 'dokan_report_filter_daily_sales' ) && isset( $_POST['start_date_alt'] ) && isset( $_POST['end_date_alt'] ) ) {
        $start_date = dokan_current_datetime()
            ->modify( sanitize_text_field( wp_unslash( $_POST['start_date_alt'] ) ) )
            ->format( 'Y-m-d' );
        $end_date   = dokan_current_datetime()
            ->modify( sanitize_text_field( wp_unslash( $_POST['end_date_alt'] ) ) )
            ->format( 'Y-m-d' );
    }
    ?>

    <form method="post" class="dokan-form-inline report-filter dokan-clearfix" action="" id="dokan-v-dashboard-reports">
        <div class="dokan-form-group">
            <label for="from"><?php _e( 'From:', 'dokan' ); ?></label> <input type="text" class="datepicker" name="start_date" id="from" readonly="readonly" value="<?php echo date_i18n( get_option( 'date_format' ), strtotime( $start_date ) ); ?>" />
        </div>

        <input type="hidden" name="start_date_alt" id="from_alt" value="<?php echo esc_attr( $start_date ); ?>">
        <input type="hidden" name="end_date_alt" id="to_alt" value="<?php echo esc_attr( $end_date ); ?>">
        <?php wp_nonce_field( 'dokan_report_filter_daily_sales', 'dokan_report_filter_daily_sales_nonce' ); ?>

        <div class="dokan-form-group">
            <label for="to"><?php esc_html_e( 'To:', 'dokan' ); ?></label>
            <input type="text" name="end_date" id="to" class="datepicker" readonly="readonly" value="<?php echo date_i18n( get_option( 'date_format' ), strtotime( $end_date ) ); ?>" />

            <input type="submit" name="dokan_report_filter" class="dokan-btn dokan-btn-success dokan-btn-sm dokan-theme" value="<?php esc_attr_e( 'Show', 'dokan' ); ?>" />
        </div>
    </form>
    <?php
    dokan_report_sales_overview( $start_date, $end_date, __( 'Sales in this period', 'dokan' ) );
}

/**
 * Sales overview factory function
 *
 * @since 1.0
 *
 * @global type $woocommerce
 * @global WPDB $wpdb
 * @global type $wp_locale
 * @global WP_User $current_user
 *
 * @param string $start_date
 * @param string $end_date
 * @param string $heading
 */
function dokan_report_sales_overview( $start_date, $end_date, $heading = '' ) {
    global $woocommerce, $wpdb, $wp_locale;

    $total_sales = $total_orders = $order_items = $discount_total = $shipping_total = 0;

    $order_totals = dokan_get_order_report_data( [
        'data' => [
            '_order_total' => [
                'type'     => 'meta',
                'function' => 'SUM',
                'name'     => 'total_sales',
            ],
            '_order_shipping' => [
                'type'     => 'meta',
                'function' => 'SUM',
                'name'     => 'total_shipping',
            ],
            'ID' => [
                'type'     => 'post_data',
                'function' => 'COUNT',
                'name'     => 'total_orders',
            ],
        ],
        'filter_range' => true,
//         'debug' => true
    ], $start_date, $end_date );

    $total_sales    = $order_totals->total_sales;
    $total_shipping = $order_totals->total_shipping;
    $total_orders   = absint( $order_totals->total_orders );
    $total_items    = absint( dokan_get_order_report_data( [
        'data' => [
            '_qty' => [
                'type'            => 'order_item_meta',
                'order_item_type' => 'line_item',
                'function'        => 'SUM',
                'name'            => 'order_item_qty',
            ],
        ],
        'query_type'   => 'get_var',
        'filter_range' => true,
    ], $start_date, $end_date ) );

    // Get discount amounts in range
    $total_coupons = dokan_get_order_report_data( [
        'data' => [
            'discount_amount' => [
                'type'            => 'order_item_meta',
                'order_item_type' => 'coupon',
                'function'        => 'SUM',
                'name'            => 'discount_amount',
            ],
        ],
        'where' => [
            [
                'key'      => 'order_item_type',
                'value'    => 'coupon',
                'operator' => '=',
            ],
        ],
        'query_type'   => 'get_var',
        'filter_range' => true,
    ], $start_date, $end_date );

    // Get refunded amount in range
    $total_refunded = dokan_get_total_refunded_amount( $start_date, $end_date );

    $num_of_days   = (int) date( 'd' );
    $average_sales = $total_sales / $num_of_days;

    $legend = apply_filters( 'dokan-seller-dashboard-reports-left-sidebar', [
        'sales_in_this_period' => [
            'title' => sprintf( __( '%s sales in this period', 'dokan' ), '<strong>' . wc_price( $total_sales ) . '</strong>' ),
        ],
        'net_sales_in_this_period' => [
            'title' => sprintf( __( '%s net sales', 'dokan' ), '<strong>' . wc_price( $total_sales - $total_refunded ) . '</strong>' ),
        ],
        'average_daily_sales' => [
            'title' => sprintf( __( '%s average daily sales', 'dokan' ), '<strong>' . wc_price( $average_sales ) . '</strong>' ),
        ],
        'orders_placed' => [
            'title' => sprintf( __( '%s orders placed', 'dokan' ), '<strong>' . $total_orders . '</strong>' ),
        ],
        'items_purchased' => [
            'title' => sprintf( __( '%s items purchased', 'dokan' ), '<strong>' . $total_items . '</strong>' ),
        ],
        'charged_for_shipping' => [
            'title' => sprintf( __( '%s charged for shipping', 'dokan' ), '<strong>' . wc_price( $total_shipping ) . '</strong>' ),
        ],
        'worth_of_coupons_used' => [
            'title' => sprintf( __( '%s worth of coupons used', 'dokan' ), '<strong>' . wc_price( $total_coupons ) . '</strong>' ),
        ],
    ] ); ?>
    <div id="poststuff" class="dokan-reports-wrap">
        <div class="dokan-reports-sidebar report-left dokan-left">
            <ul class="chart-legend">
                <?php foreach ( $legend as $item ) {
                    printf( '<li>%s</li>', $item['title'] );
                } ?>
            </ul>
        </div>

        <div class="dokan-reports-main report-right dokan-right">
            <div class="postbox">
                <h3><span><?php echo $heading; ?></span></h3>

                <?php dokan_sales_overview_chart_data( $start_date, $end_date, 'day' ); ?>
            </div>
        </div>
    </div>
    <?php
}

/**
 * Prepares chart data for sales overview
 *
 * @since 1.0
 *
 * @global WP_Locale $wp_locale
 *
 * @param string $start_date
 * @param string $end_date
 * @param string $group_by
 */
function dokan_sales_overview_chart_data( $start_date, $end_date, $group_by ) {
    global $wp_locale;

    $start_date_to_time = dokan_current_datetime()->modify( $start_date )->getTimestamp();
    $end_date_to_time   = dokan_current_datetime()->modify( $end_date )->getTimestamp();
    $chart_interval     = dokan_get_interval_between_dates( $start_date_to_time, $end_date_to_time, $group_by );

    if ( $group_by == 'day' ) {
        $group_by_query       = 'YEAR(post_date), MONTH(post_date), DAY(post_date)';
        $barwidth             = 60 * 60 * 24 * 1000;
    } else {
        $group_by_query       = 'YEAR(post_date), MONTH(post_date)';
        $barwidth             = 60 * 60 * 24 * 7 * 4 * 1000;
    }

    // Get orders and dates in range - we want the SUM of order totals, COUNT of order items, COUNT of orders, and the date
    $orders = dokan_get_order_report_data( [
        'data' => [
            '_order_total' => [
                'type'     => 'meta',
                'function' => 'SUM',
                'name'     => 'total_sales',
            ],
            'ID' => [
                'type'     => 'post_data',
                'function' => 'COUNT',
                'name'     => 'total_orders',
                'distinct' => true,
            ],
            'post_date' => [
                'type'     => 'post_data',
                'function' => '',
                'name'     => 'post_date',
            ],
        ],
        'group_by'     => $group_by_query,
        'order_by'     => 'post_date ASC',
        'query_type'   => 'get_results',
        'filter_range' => true,
        'debug'        => false,
    ], $start_date, $end_date );

    // Prepare data for report
    $order_counts      = dokan_prepare_chart_data( $orders, 'post_date', 'total_orders', $chart_interval, $start_date_to_time, $group_by );
    $order_amounts     = dokan_prepare_chart_data( $orders, 'post_date', 'total_sales', $chart_interval, $start_date_to_time, $group_by );

    // Encode in json format
    $chart_data = wp_json_encode(
        [
            'order_counts'  => array_values( $order_counts ),
            'order_amounts' => array_values( $order_amounts ),
        ]
    );

    $chart_colours = [
        'order_counts'  => '#3498db',
        'order_amounts' => '#1abc9c',
    ];
    ?>
    <div class="chart-container">
        <div class="chart-placeholder main" style="width: 100%; height: 350px;"></div>
    </div>

    <script type="text/javascript">
        jQuery(function($) {

            var order_data = JSON.parse( '<?php echo $chart_data; ?>' );
            var isRtl = '<?php echo is_rtl() ? '1' : '0'; ?>'

            var series = [
                {
                    label: "<?php echo esc_js( __( 'Sales total', 'dokan' ) ); ?>",
                    data: order_data.order_amounts,
                    shadowSize: 0,
                    hoverable: true,
                    points: { show: true, radius: 5, lineWidth: 1, fillColor: '#fff', fill: true },
                    lines: { show: true, lineWidth: 2, fill: false },
                    shadowSize: 0,
                    prepend_tooltip: "<?php echo get_woocommerce_currency_symbol(); ?>"
                },
                {
                    label: "<?php echo esc_js( __( 'Number of orders', 'dokan' ) ); ?>",
                    data: order_data.order_counts,
                    shadowSize: 0,
                    hoverable: true,
                    points: { show: true, radius: 5, lineWidth: 2, fillColor: '#fff', fill: true },
                    lines: { show: true, lineWidth: 3, fill: false },
                    shadowSize: 0,
                    append_tooltip: " <?php echo __( 'sales', 'dokan' ); ?>"
                },
            ];

            var main_chart = jQuery.plot(
                jQuery('.chart-placeholder.main'),
                series,
                {
                    legend: {
                        show: true,
                        position: 'nw'
                    },
                    series: {
                        lines: { show: true, lineWidth: 4, fill: false },
                        points: { show: true }
                    },
                    grid: {
                        borderColor: '#eee',
                        color: '#aaa',
                        borderWidth: 1,
                        hoverable: true,
                        show: true,
                        aboveData: false,
                    },
                    xaxis: {
                        color: '#aaa',
                        position: "bottom",
                        tickColor: 'transparent',
                        mode: "time",
                        timeformat: "<?php echo ( $group_by == 'day' ) ? '%d %b' : '%b'; ?>",
                        monthNames: <?php echo json_encode( array_values( $wp_locale->month_abbrev ) ); ?>,
                        tickLength: 1,
                        minTickSize: [1, "<?php echo $group_by; ?>"],
                        font: {
                            color: "#aaa"
                        },
                        transform: function (v) { return ( isRtl == '1' ) ? -v : v; },
                        inverseTransform: function (v) { return ( isRtl == '1' ) ? -v : v; }
                    },
                    yaxes: [
                        {
                            position: ( isRtl == '1' ) ? "right" : "left",
                            min: 0,
                            minTickSize: 1,
                            tickDecimals: 0,
                            color: '#d4d9dc',
                            font: { color: "#aaa" }
                        },
                        {
                            position: ( isRtl == '1' ) ? "right" : "left",
                            min: 0,
                            tickDecimals: 2,
                            alignTicksWithAxis: 1,
                            color: 'transparent',
                            font: { color: "#aaa" }
                        }
                    ],
                    colors: ["<?php echo $chart_colours['order_counts']; ?>", "<?php echo $chart_colours['order_amounts']; ?>"]
                }
            );

            jQuery('.chart-placeholder').resize();
        });

    </script>
    <?php
}

/**
 * Output the top sellers chart.
 *
 * @return void
 */
function dokan_top_sellers() {
    global $start_date, $end_date, $woocommerce, $wpdb;
    $current_user = dokan_get_current_user_id();

    $start_date = dokan_current_datetime()->modify( 'first day of this month' )->format( 'Y-m-d' );
    $end_date   = dokan_current_datetime()->format( 'Y-m-d' );

    if ( isset( $_POST['dokan_report_filter_top_seller'] ) && isset( $_POST['dokan_report_filter_top_seller_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dokan_report_filter_top_seller_nonce'] ) ), 'dokan_report_filter_top_seller' ) && isset( $_POST['start_date_alt'] ) && isset( $_POST['end_date_alt'] ) ) {
        $start_date = dokan_current_datetime()
            ->modify( sanitize_text_field( wp_unslash( $_POST['start_date_alt'] ) ) )
            ->format( 'Y-m-d' );
        $end_date   = dokan_current_datetime()
            ->modify( sanitize_text_field( wp_unslash( $_POST['end_date_alt'] ) ) )
            ->format( 'Y-m-d' );
    }

    // Get order ids and dates in range
    $order_items = apply_filters( 'woocommerce_reports_top_sellers_order_items', $wpdb->get_results( "
        SELECT order_item_meta_2.meta_value as product_id, SUM( order_item_meta.meta_value ) as item_quantity FROM {$wpdb->prefix}woocommerce_order_items as order_items

        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta_2 ON order_items.order_item_id = order_item_meta_2.order_item_id
        LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
        LEFT JOIN {$wpdb->prefix}dokan_orders AS do ON posts.ID = do.order_id

        WHERE   posts.post_type     = 'shop_order'
        AND     posts.post_status   != 'trash'
        AND     do.seller_id = {$current_user}
        AND     do.order_status IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', [ 'wc-completed', 'wc-processing', 'wc-on-hold' ] ) ) . "')
        AND     post_date > '" . date( 'Y-m-d', strtotime( $start_date ) ) . "'
        AND     post_date < '" . date( 'Y-m-d', strtotime( '+1 day', strtotime( $end_date ) ) ) . "'
        AND     order_items.order_item_type = 'line_item'
        AND     order_item_meta.meta_key = '_qty'
        AND     order_item_meta_2.meta_key = '_product_id'
        GROUP BY order_item_meta_2.meta_value
    " ), $start_date, $end_date );

    $found_products = [];

    if ( $order_items ) {
        foreach ( $order_items as $order_item ) {
            $found_products[ $order_item->product_id ] = $order_item->item_quantity;
        }
    }

    asort( $found_products );
    $found_products = array_reverse( $found_products, true );
    $found_products = array_slice( $found_products, 0, 25, true );
    reset( $found_products );
    ?>
    <form method="post" action="" class="report-filter dokan-form-inline dokan-clearfix" id="dokan-v-dashboard-reports">
        <div class="dokan-form-group">
            <label for="from"><?php esc_html_e( 'From:', 'dokan' ); ?></label>
            <input type="text" class="datepicker" name="start_date" id="from" readonly="readonly" value="<?php echo date_i18n( get_option( 'date_format' ), strtotime( $start_date ) ); ?>" />
        </div>

        <input type="hidden" name="start_date_alt" id="from_alt" value="<?php echo esc_attr( $start_date ); ?>">
        <input type="hidden" name="end_date_alt" id="to_alt" value="<?php echo esc_attr( $end_date ); ?>">
        <?php wp_nonce_field( 'dokan_report_filter_top_seller', 'dokan_report_filter_top_seller_nonce' ); ?>

        <div class="dokan-form-group">
            <label for="to"><?php esc_html_e( 'To:', 'dokan' ); ?></label>
            <input type="text" class="datepicker" name="end_date" id="to" readonly="readonly" value="<?php echo date_i18n( get_option( 'date_format' ), strtotime( $end_date ) ); ?>" />
            <input type="submit" name="dokan_report_filter_top_seller" class="dokan-btn dokan-btn-success dokan-btn-sm dokan-theme" value="<?php esc_attr_e( 'Show', 'dokan' ); ?>" />
        </div>

    </form>


    <table class="table table-striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Product', 'dokan' ); ?></th>
                <th><?php esc_html_e( 'Sales', 'dokan' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
                $max_sales = current( $found_products );

    foreach ( $found_products as $product_id => $sales ) {
        $width         = $sales > 0 ? ( $sales / $max_sales ) * 100 : 0;
        $product_title = get_the_title( $product_id );

        if ( $product_title ) {
            $product_name = '<a href="' . get_permalink( $product_id ) . '">' . __( $product_title ) . '</a>';
        } else {
            $product_name = __( 'Product does not exist', 'dokan' );
        }

        echo '<tr><th style="width: 60%;">' . $product_name . '</th><td style="width: 1%;"><span>' . esc_html( $sales ) . '</span></td></tr>';
    } ?>
        </tbody>
    </table>
    <?php
}

/**
 * Output the top earners chart.
 *
 * @return void
 */
function dokan_top_earners() {
    global $wpdb;
    $current_user          = dokan_get_current_user_id();
    $withdraw_order_status = dokan_get_option( 'withdraw_order_status', 'dokan_withdraw', [ 'wc-completed' ] );

    $start_date = dokan_current_datetime()->modify( 'first day of this month' )->format( 'Y-m-d' );
    $end_date   = dokan_current_datetime()->format( 'Y-m-d' );

    if ( isset( $_POST['dokan_report_filter_top_earners'] ) && isset( $_POST['dokan_report_filter_top_earners_nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dokan_report_filter_top_earners_nonce'] ) ), 'dokan_report_filter_top_earners' ) && isset( $_POST['start_date_alt'] ) && isset( $_POST['end_date_alt'] ) ) {
        $start_date = dokan_current_datetime()
            ->modify( sanitize_text_field( wp_unslash( $_POST['start_date_alt'] ) ) )
            ->format( 'Y-m-d' );
        $end_date   = dokan_current_datetime()
            ->modify( sanitize_text_field( wp_unslash( $_POST['end_date_alt'] ) ) )
            ->format( 'Y-m-d' );
    }

    // Get order ids and dates in range
    $order_items = apply_filters( 'woocommerce_reports_top_earners_order_items', $wpdb->get_results( "
        SELECT order_item_meta_2.meta_value as product_id, SUM( order_item_meta.meta_value ) as line_total,SUM( do.net_amount ) as total_earning FROM {$wpdb->prefix}woocommerce_order_items as order_items

        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta_2 ON order_items.order_item_id = order_item_meta_2.order_item_id
        LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
        LEFT JOIN {$wpdb->prefix}dokan_orders AS do ON posts.ID = do.order_id

        WHERE   posts.post_type     = 'shop_order'
        AND     posts.post_status   != 'trash'
        AND     do.seller_id = {$current_user}
        AND     do.order_status           IN ('" . implode( "','", apply_filters( 'woocommerce_reports_order_statuses', $withdraw_order_status ) ) . "')
        AND     post_date > '" . date( 'Y-m-d', strtotime( $start_date ) ) . "'
        AND     post_date < '" . date( 'Y-m-d', strtotime( '+1 day', strtotime( $end_date ) ) ) . "'
        AND     order_items.order_item_type = 'line_item'
        AND     order_item_meta.meta_key = '_line_total'
        AND     order_item_meta_2.meta_key = '_product_id'
        GROUP BY order_item_meta_2.meta_value
    " ), $start_date, $end_date );

    $found_products = [];
    $total_earnings = [];

    if ( $order_items ) {
        foreach ( $order_items as $order_item ) {
            $found_products[ $order_item->product_id ] = $order_item->line_total;
            $total_earnings[ $order_item->product_id ] = $order_item->total_earning;
        }
    }

    asort( $found_products );
    $found_products = array_reverse( $found_products, true );
    $found_products = array_slice( $found_products, 0, 25, true );
    reset( $found_products ); ?>
    <form method="post" action="" class="report-filter dokan-form-inline dokan-clearfix" id="dokan-v-dashboard-reports">
        <div class="dokan-form-group">
            <label for="from"><?php _e( 'From:', 'dokan' ); ?></label>
            <input type="text" class="datepicker" name="start_date" id="from" readonly="readonly" value="<?php echo date_i18n( get_option( 'date_format' ), strtotime( $start_date ) ); ?>" />
        </div>
        <input type="hidden" name="start_date_alt" id="from_alt" value="<?php echo esc_attr( $start_date ); ?>">
        <input type="hidden" name="end_date_alt" id="to_alt" value="<?php echo esc_attr( $end_date ); ?>">
        <?php wp_nonce_field( 'dokan_report_filter_top_earners', 'dokan_report_filter_top_earners_nonce' ); ?>

        <div class="dokan-form-group">
            <label for="to"><?php esc_html_e( 'To:', 'dokan' ); ?></label>
            <input type="text" class="datepicker" name="end_date" id="to" readonly="readonly" value="<?php echo date_i18n( get_option( 'date_format' ), strtotime( $end_date ) ); ?>" />
            <input type="submit" name="dokan_report_filter_top_earners" class="dokan-btn dokan-btn-success dokan-btn-sm dokan-theme" value="<?php esc_attr_e( 'Show', 'dokan' ); ?>" />
        </div>

    </form>

    <table class="table table-striped">
        <thead>
            <tr>
                <th><?php esc_html_e( 'Product', 'dokan' ); ?></th>
                <th><?php esc_html_e( 'Sales', 'dokan' ); ?></th>
                <th><?php esc_html_e( 'Earning', 'dokan' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php
            $max_sales = current( $found_products );

            foreach ( $found_products as $product_id => $sales ) {
                $width = $sales > 0 ? ( round( $sales ) / round( $max_sales ) ) * 100 : 0;

                $product_title = get_the_title( $product_id );

                if ( $product_title ) {
                    $product_name = '<a href="' . get_permalink( $product_id ) . '">' . __( $product_title ) . '</a>';
                } else {
                    $product_name = __( 'Product no longer exists', 'dokan' );
                }

                echo '<tr>
                    <th style="width: 60%;">' . $product_name . '</th>
                    <td style="width: 20%;"><span>' . wc_price( $sales ) . '</span></td>
                    <td style="width: 20%;"><span>' . wc_price( $total_earnings[ $product_id ] ) . '</span></td>
                </tr>';
            } ?>
        </tbody>
    </table>
    <?php
}
