<?php

class Dokan_SPMV_Product_Visibility {

    protected $started_background_process = false;

    /**
     * Class constructor
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function __construct() {
        add_action( 'dokan_after_saving_settings', [ $this, 'after_saving_settings' ], 10, 2 );
        add_action( 'dokan_spmv_create_clone_product', [ $this, 'after_create_clone' ], 10, 3 );
        add_action( 'woocommerce_update_product', [ $this, 'woocommerce_update_product' ] );
        add_action( 'woocommerce_product_query', [ $this, 'add_query_filters' ] );
    }

    /**
     * Fires right save admin settings
     *
     * @since 2.9.11
     *
     * @param string $option_name
     * @param mixed  $option_value
     *
     * @return void
     */
    public function after_saving_settings( $option_name, $option_value ) {
        global $wpdb;

        if ( 'dokan_spmv' !== $option_name ) {
            return;
        }

        $updater_file = DOKAN_SPMV_INC_DIR . '/dokan-spmv-update-product-visibility.php';

        include_once $updater_file;
        $processor = new Dokan_SPMV_Update_Product_Visibility();

        $processor->cancel_process();

        $map_ids = $wpdb->get_col( "select map_id from {$wpdb->prefix}dokan_product_map group by map_id" );

        $item = [
            'map_ids' => $map_ids,
        ];

        $processor->push_to_queue( $item );
        $processor->save()->dispatch();

        $processes = get_option( 'dokan_background_processes', [] );
        $processes['Dokan_SPMV_Update_Product_Visibility'] = $updater_file;

        update_option( 'dokan_background_processes', $processes, 'no' );
    }

    /**
     * Fires after cloning a product
     *
     * @since 2.9.11
     *
     * @param int $cloned_product_id
     * @param int $product_id
     * @param int $map_id
     *
     * @return void
     */
    public function after_create_clone( $cloned_product_id, $product_id, $map_id ) {
        dokan_spmv_update_clone_visibilities( $map_id );
    }

    /**
     * Update visibility on product update
     *
     * @since 2.9.11
     *
     * @param int $product_id
     *
     * @return void
     */
    public function woocommerce_update_product( $product_id ) {
        global $wpdb;

        if ( ! $this->started_background_process ) {
            $map_id = $wpdb->get_var( $wpdb->prepare(
                "select map_id from {$wpdb->prefix}dokan_product_map where product_id = %d",
                $product_id
            ) );

            if ( $map_id ) {
                dokan_spmv_update_clone_visibilities( $map_id );
            }
        }

        $this->started_background_process = true;
    }

    /**
     * Filter WC product query
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function add_query_filters() {
        $show_order = dokan_get_option( 'show_order', 'dokan_spmv', 'show_all' );

        if ( 'show_all' !== $show_order ) {
            add_filter( 'posts_where_request', [ $this, 'filter_where_request' ], 10, 2 );
            add_filter( 'posts_join_request', [ $this, 'filter_join_request' ], 10, 2 );
        }
    }

    /**
     * Filter the where query
     *
     * @since 2.9.11
     *
     * @param string    $where
     * @param \WP_Query $wp_query
     *
     * @return string
     */
    public function filter_where_request( $where, $wp_query ) {
        global $wpdb;

        if ( 'product' === $wp_query->get( 'post_type' ) || 'product_cat' === $wp_query->get( 'taxonomy' ) ) {
            $where .= " AND ( {$wpdb->prefix}dokan_product_map.visibility = 1 OR {$wpdb->prefix}dokan_product_map.visibility IS NULL )";
        }

        return $where;
    }

    /**
     * Filter the join query
     *
     * @since 2.9.11
     *
     * @param string    $join
     * @param \WP_Query $wp_query
     *
     * @return string
     */
    public function filter_join_request( $join, $wp_query ) {
        global $wpdb;

        if ( 'product' === $wp_query->get( 'post_type' ) || 'product_cat' === $wp_query->get( 'taxonomy' ) ) {
            $table = "{$wpdb->prefix}dokan_product_map";
            $join .= " left join {$table} on {$wpdb->posts}.ID = {$table}.product_id";
        }

        return $join;
    }
}
