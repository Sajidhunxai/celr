<?php

namespace WeDevs\DokanPro;

defined( 'ABSPATH' ) || exit;

/**
 * Store Lists Filter Pro Class
 *
 * @since 3.0.0
 */
class StoreListsFilter {

    /**
     * WP_User_Query holder
     *
     * @var object
     */
    private $query;

    /**
     * Orderby holder
     *
     * @var string
     */
    private $orderby;

    /**
     * Constructor method
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * Init hooks
     *
     * @since  3.0.0
     *
     * @return void
     */
    public function hooks() {
        add_action( 'dokan_before_store_lists_filter_apply_button', [ $this, 'category_area' ] );
        add_action( 'dokan_after_store_lists_filter_category', [ $this, 'featured_store' ] );
        add_filter( 'dokan_seller_listing_args', [ $this, 'store_args' ], 10, 2 );
        add_filter( 'dokan_store_lists_sort_by_options', [ $this, 'add_sort_by_options' ] );
        add_action( 'pre_get_users', [ $this, 'get_filtered_stores' ] );
        add_action( 'dokan_before_filter_user_query', [ $this, 'filter_user_query' ], 10, 2 );
    }

    /**
     * Category area template
     *
     * @since  3.0.0
     *
     * @param  array $stores
     *
     * @return void
     */
    public function category_area( $stores ) {
        dokan_get_template_part( 'store-lists/category-area', '', [
            'pro'        => true,
            'stores'     => $stores,
            'categories' => $this->get_categories()
        ] );
    }

    /**
     * Featured store template
     *
     * @since  3.0.0
     *
     * @param  array $stores
     * @return void
     */
    public function featured_store( $stores ) {
        dokan_get_template_part( 'store-lists/featured', '', [
            'pro'         => true,
            'stores'      => $stores,
        ] );

        dokan_get_template_part( 'store-lists/open-now', '', [
            'pro'    => true,
            'stores' => $stores
        ] );
    }

    /**
     * Get store categories
     *
     * @since  3.0.0
     *
     * @return array | null on failure
     */
    public function get_categories() {
        if ( ! dokan_is_store_categories_feature_on() ) {
            return;
        }

        $categories = get_terms( [
            'taxonomy'   => 'store_category',
            'hide_empty' => false,
        ] );

        $categories = array_map( function( $category ) {
            return [
                'name' => $category->name,
                'slug' => $category->slug
            ];
        }, $categories );

        return apply_filters( 'dokan_get_store_categories', $categories );
    }

    public function store_args( $args, $request ) {
        if ( ! empty( $request['featured'] ) && 'yes' === $request['featured'] ) {
            $args['featured'] = 'yes';
        }

        if ( ! empty( $request['open_now'] ) && 'yes' === $request['open_now'] ) {
            $args['open_now'] = 'yes';
        }

        if ( ! empty( $request['rating'] ) ) {
            $args['rating'] = intval( $request['rating'] );
        }

        return $args;
    }

    /**
     * Add sort by options
     *
     * @since  3.0.0
     *
     * @param array
     */
    public function add_sort_by_options( $options ) {
        $options['top_rated']     = __( 'Top Rated', 'dokan' );
        $options['most_reviewed'] = __( 'Most Reviewed', 'dokan' );

        return $options;
    }

    /**
     * Get filtered stores
     *
     * @since  3.0.0
     *
     * @param  WP_User_Query $query
     *
     * @return void
     */
    public function get_filtered_stores( $query ) {
        $is_open = ! empty( $query->query_vars['open_now'] ) && 'yes' === $query->query_vars['open_now'] ? true : false;
        $rating  = ! empty( $query->query_vars['rating'] ) ? $query->query_vars['rating'] : 0;

        if ( ! $is_open && empty( $rating ) ) {
            return $query;
        }

        $all_stores = get_users( apply_filters( 'dokan_pre_get_all_stores_query', [
            'role__in'      => [ 'seller', 'administrator' ],
            'number'        => -1,
            'orderby'       => 'registered',
            'order'         => 'ASC',
            'status'        => 'approved',
            'fields'        => 'ids',
            'no_found_rows' => true,
            'meta_query'    => [
                [
                    'key'     => 'dokan_enable_selling',
                    'value'   => 'yes',
                    'compare' => '='
                ]
            ]
        ] ) );

        $store_to_exclude = [];

        foreach ( $all_stores as $store ) {
            if ( $is_open && ! dokan_is_store_open( $store ) ) {
                array_push( $store_to_exclude, $store );
                continue;
            }

            if ( $rating ) {
                $vendor         = dokan()->vendor->get( $store );
                $vendor_ratings = $vendor->get_rating();
                $vendor_rating  = ! empty( $vendor_ratings['rating'] ) ? $vendor_ratings['rating'] : 0;

                if ( $vendor->get_id() > 0 && $vendor_rating < $rating ) {
                    array_push( $store_to_exclude, $store );
                }
            }
        }

        $query->set( 'exclude', $store_to_exclude );
    }

    /**
     * Filter user query
     *
     * @since  3.0.0
     *
     * @param  WP_User_Query $query
     * @param  string $orderby
     *
     * @return void
     */
    public function filter_user_query( $query, $orderby ) {
        $this->query   = $query;
        $this->orderby = $orderby;

        $this->filter_query_from();
        $this->filter_query_orderby();
    }

    /**
     * Filter query from clause
     *
     * @since  3.0.0
     *
     * @return void
     */
    private function filter_query_from() {
        global $wpdb;

        // based on number of store reviews
        if ( 'most_reviewed' === $this->orderby ) {
            $this->query->query_from .= " LEFT JOIN (
                    SELECT count(post.ID) AS review_count, meta.meta_value AS seller_id
                    FROM {$wpdb->posts} AS post
                    INNER JOIN {$wpdb->postmeta} AS meta ON post.ID = meta.post_id
                    WHERE post.post_type = 'dokan_store_reviews'
                    AND meta.meta_key = 'store_id'
                    GROUP BY seller_id
                    ) as review
                    ON ({$wpdb->users}.ID = review.seller_id)";
        }

        // based on store reviews total
        if ( 'top_rated' === $this->orderby ) {
            $this->query->query_from .= " LEFT JOIN (
                    SELECT store_id, sum(rating) AS rating
                    FROM
                        (SELECT p.ID,
                            sum( if( m.meta_key = 'store_id', m.meta_value, 0 ) ) AS store_id,
                            sum( if( m.meta_key = 'rating', m.meta_value, 0 ) ) AS rating
                        FROM {$wpdb->postmeta} AS m
                        LEFT JOIN {$wpdb->posts} AS p ON p.ID = m.post_id
                        WHERE p.post_type = 'dokan_store_reviews'
                        GROUP BY p.ID) AS vt
                    GROUP BY store_id
                    ORDER BY rating) as rating
                    ON ({$wpdb->users}.ID = rating.store_id)";
        }
    }

    /**
     * Filter orderby query
     *
     * @since  3.0.0
     *
     * @return void
     */
    private function filter_query_orderby() {
        if ( 'most_reviewed' === $this->orderby ) {
            $this->query->query_orderby = 'ORDER BY review_count DESC';
        }

        if ( 'top_rated' === $this->orderby ) {
            $this->query->query_orderby = 'ORDER BY rating DESC';
        }
    }
}