<?php

namespace WeDevs\DokanPro\Modules\SPMV\Search;

use stdClass;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Dashboard.
 * Here we are implementing all the Product search and add to store
 * related logic.
 *
 * @since 3.5.2
 */
class Dashboard {

    /**
     * Constructor.
     *
     * @sience 3.5.2
     *
     * @return void
     */
    public function __construct() {
        if ( ! $this->is_spmv_enabled() ) {
            add_action( 'wp', [ $this, 'redirect_to_dashboard' ] );
            return;
        }

        add_action( 'dokan_load_custom_template', [ $this, 'load_search_template' ], 10, 1 );
        add_filter( 'dokan_dashboard_nav_active', [ $this, 'active_dashboard_nav_menu' ], 10, 3 );
        add_action( 'dokan_spmv_products_search_content_table_before', [ $this, 'add_filter_content' ], 15 );
        add_action( 'dokan_spmv_products_search_content_table_before', [ $this, 'add_count_content' ], 10 );
        add_action( 'dokan_new_product_before_product_area', [ $this, 'load_product_search_box_template' ] );
        add_action( 'dokan_spmv_products_search_box', [ $this, 'load_product_listing_search_box_template' ] );
        add_action( 'wp_footer', [ $this, 'load_add_category_modal' ] );

        if ( wp_doing_ajax() ) {
            add_action( 'wp_ajax_dokan_spmv_handle_product_clone_request', [ $this, 'handle_product_clone_request' ] );
        }
    }

    /**
     * Load search template.
     *
     * @since 3.5.2
     *
     * @param array $query_vars
     *
     * @return void
     */
    public function load_search_template( $query_vars ) {
        if ( ! isset( $query_vars['products-search'] ) ) {
            return;
        }

        $paged             = ( isset( $_GET['pagenum'] ) ) ? absint( wp_unslash( $_GET['pagenum'] ) ) : 1; // phpcs:ignore.
        $search_word       = ( isset( $_GET['search'] ) ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : ''; // phpcs:ignore.
        $products_per_page = apply_filters( 'loop_shop_per_page', wc_get_default_products_per_row() * wc_get_default_product_rows_per_page() );
        $search_args       = [
            'meta_key'   => '_price', //phpcs:ignore.
            'status'     => 'publish',
            's'          => $search_word,
            'limit'      => $products_per_page,
            'page'       => $paged,
            'paginate'   => true,
            'return'     => 'all',
            'visibility' => 'visible',
            'orderby'    => $ordering['orderby'],
            'order'      => $ordering['order'],
        ];

        if ( ! empty( $this->get_product_type_for_search() ) && 'all' !== $this->get_product_type_for_search() ) {
            $search_args['type'] = $this->get_product_type_for_search();
        } else {
            $search_args['type'] = array_diff(
                array_keys( wc_get_product_types() ),
                [
                    'auction',
                    'booking',
                    'grouped',
                ]
            );
        }

        $this->remove_auction_query_restrictions();
        $search_results = $this->dokan_spmv_get_products( $paged, $search_word, '',  $products_per_page, $this->get_product_type_for_search()  );
        $this->reset_auction_query_restrictions();

        wc_set_loop_prop( 'current_page', $paged );
        wc_set_loop_prop( 'is_paginated', wc_string_to_bool( true ) );
        wc_set_loop_prop( 'page_template', get_page_template_slug() );
        wc_set_loop_prop( 'per_page', $products_per_page );
        wc_set_loop_prop( 'total', $search_results->total );
        wc_set_loop_prop( 'total_pages', $search_results->max_num_pages );

        $args = [
            'search_results' => $search_results,
            'paged'          => $paged,
            'search_word'    => $search_word,

        ];
        dokan_spmv_get_template( 'search/result', $args );
    }

    /**
     * Set Products menu as active.
     *
     * @since 3.5.2
     *
     * @param string $active_menu
     * @param $request
     * @param array $active
     *
     * @return string
     */
    public function active_dashboard_nav_menu( $active_menu, $request, $active ) {
        if ( 'products-search' !== $active_menu ) {
            return $active_menu;
        }

        return ( ! empty( $this->get_product_type_for_search() ) ) ? $this->get_product_type_for_search() : 'products';
    }

    /**
     * Add woocommerce search filter
     *
     * @since 3.5.2
     *
     * @return void
     */
    public function add_filter_content() {
        dokan_spmv_get_template( 'search/filter', [] );
    }

    /**
     * Add woocommerce search page count content
     *
     * @since 3.5.2
     *
     * @return void
     */
    public function add_count_content() {
        dokan_spmv_get_template( 'search/count', [] );
    }

    /**
     * Search box template to display before new product form.
     *
     * @since 3.5.2
     *
     * @return void
     */
    public function load_product_search_box_template() {
        $type = $this->get_product_type_for_add_new();

        $args = [
            'action' => dokan_get_navigation_url( 'products-search' ),
            'type'   => $type,
        ];
        dokan_spmv_get_template( 'search/box', $args );
    }

    /**
     * Search box template to display before new product form.
     *
     * @since 3.5.2
     *
     * @return void
     */
    public function load_product_listing_search_box_template() {
        $search = isset( $_GET['search'] ) ? sanitize_text_field( wp_unslash( $_GET['search'] ) ) : ''; // phpcs:ignore.
        $type   = $this->get_product_type_for_search();
        $action = dokan_get_navigation_url( 'products-search' );

        dokan_spmv_get_template(
            'search/box-listing',
            [
                'search' => $search,
                'action' => $action,
                'type'   => $type,
            ]
        );
    }

    /**
     * Handle Product clone ajax request.
     *
     * @since 3.5.2
     *
     * @return void
     */
    public function handle_product_clone_request() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['nonce'] ), 'dokan_spmv_product_clone_from_search' ) ) {
            wp_send_json_error( esc_html__( 'Are you cheating?', 'dokan' ) );
        }

        if ( ! current_user_can( 'dokan_edit_product' ) ) {
            wp_send_json_error( esc_html__( 'You have no permission to do this action', 'dokan' ) );
        }

        $vendor_id = dokan_get_current_user_id();
        if ( ! dokan_spmv_can_vendor_create_new_product( $vendor_id ) ) {
            wp_send_json_error( esc_html__( 'You have no permission to create new product.', 'dokan' ) );
        }

        $product_id = isset( $_POST['product_id'] ) ? absint( wp_unslash( $_POST['product_id'] ) ) : 0;
        if ( ! $product_id ) {
            wp_send_json_error( esc_html__( 'Product id is required to clone product.', 'dokan' ) );
        }

        $product_duplicator = new \Dokan_SPMV_Product_Duplicator();

        if ( $product_duplicator->check_already_cloned( $product_id, $vendor_id ) ) {
            wp_send_json_error( esc_html__( 'You have already cloned this product.', 'dokan' ) );
        }

        $cloned_product = $product_duplicator->clone_product( $product_id, $vendor_id );
        if ( is_wp_error( $cloned_product ) ) {
            wp_send_json_error( esc_html( $cloned_product->get_error_message() ) );
        }

        wp_send_json_success(
            [
                'message' => __( 'Product added to store successfully!', 'dokan' ),
                'url'     => esc_url_raw( dokan_edit_product_url( $cloned_product ) ),
            ]
        );
    }

    /**
     * Check if SPMV is enabled.
     *
     * @since 3.5.2
     *
     * @return bool
     */
    private function is_spmv_enabled() {
        return 'on' === dokan_get_option( 'enable_pricing', 'dokan_spmv', 'off' );
    }

    /**
     * Check if Auction module is active.
     *
     * @since 3.5.2
     *
     * @return bool
     */
    private function is_auction_enabled() {
        return dokan_pro()->module->is_active( 'auction' );
    }

    /**
     * Check if Booking module is active.
     *
     * @since 3.5.2
     *
     * @return bool
     */
    private function is_booking_enabled() {
        return dokan_pro()->module->is_active( 'booking' );
    }

    /**
     * Get product type to perform search.
     *
     * @since 3.5.2
     *
     * @return string
     */
    private function get_product_type_for_search( $product_type = '' ) {
        if ( ! empty( $product_type ) ) {
            $product_type = sanitize_key( wp_unslash( $product_type ) );
        } else {
            $product_type = ! empty( $_GET['type'] ) ? sanitize_key( wp_unslash( $_GET['type'] ) ) : ''; // phpcs:ignore.
        }

        if ( $this->is_auction_enabled() && 'auction' === $product_type ) {
            return 'auction';
        }

        if ( $this->is_booking_enabled() && 'booking' === $product_type ) {
            return 'booking';
        }

        return '';
    }

    /**
     * Get product type to display search box on new product form.
     *
     * @since 3.5.2
     *
     * @return string
     */
    private function get_product_type_for_add_new() {
        global $wp;

        if ( $this->is_booking_enabled() && isset( $wp->query_vars['booking'] ) && 'new-product' === $wp->query_vars['booking'] ) {
            return 'booking';
        }

        if ( $this->is_auction_enabled() && isset( $wp->query_vars['new-auction-product'] ) ) {
            return 'auction';
        }

        return 'all';
    }

    /**
     * Remove auction product restriction.
     *
     * @since 3.5.2
     *
     * @return void
     */
    private function remove_auction_query_restrictions() {
        if ( ! class_exists( 'WooCommerce_simple_auction' ) ) {
            return;
        }

        global $woocommerce_auctions;

        if ( ! isset( $woocommerce_auctions ) ) {
            return;
        }

        remove_action( 'woocommerce_product_query', [ $woocommerce_auctions, 'remove_auctions_from_woocommerce_product_query' ], 2 );
        remove_action( 'woocommerce_product_query', [ $woocommerce_auctions, 'pre_get_posts' ], 99 );
        remove_action( 'pre_get_posts', [ $woocommerce_auctions, 'auction_arhive_pre_get_posts' ], 10 );
        remove_action( 'pre_get_posts', [ $woocommerce_auctions, 'query_auction_archive' ], 1 );
    }

    /**
     * Reset auction product restriction.
     *
     * @since 3.5.2
     *
     * @return void
     */
    private function reset_auction_query_restrictions() {
        if ( ! class_exists( 'WooCommerce_simple_auction' ) ) {
            return;
        }

        global $woocommerce_auctions;

        if ( ! isset( $woocommerce_auctions ) ) {
            return;
        }

        add_action( 'woocommerce_product_query', [ $woocommerce_auctions, 'remove_auctions_from_woocommerce_product_query' ], 2 );
        add_action( 'woocommerce_product_query', [ $woocommerce_auctions, 'pre_get_posts' ], 99 );
        add_action( 'pre_get_posts', [ $woocommerce_auctions, 'auction_arhive_pre_get_posts' ], 10 );
        add_action( 'pre_get_posts', [ $woocommerce_auctions, 'query_auction_archive' ], 1 );
    }

    /**
     * Redirect to vendor dashboard.
     *
     * @since 3.5.2
     *
     * @return void
     */
    public function redirect_to_dashboard() {
        $current_url = strtok( home_url( esc_url_raw( wp_unslash( $_SERVER['REQUEST_URI'] ) ) ), '?' );

        if ( dokan_get_navigation_url( 'products-search' ) === $current_url ) {
            wp_safe_redirect( dokan_get_navigation_url( '/' ) );
            exit;
        }
    }

    /**
     * Returns new category select ui html elements.
     *
     * @since 3.7.6
     *
     * @return void
     */
    public function load_add_category_modal() {
        /**
         * Checking if dokan dashboard and SPMV search product page.
         * Because without those page we don't need to load category modal.
         */
        global $wp;
        if ( dokan_is_seller_dashboard() && isset( $wp->query_vars['products-search'] ) ) {
            dokan_get_template_part( 'products/dokan-category-ui', '', array() );
        }
    }

    /**
     * Returns spmv products
     *
     * @since 3.7.19
     *
     * @param integer $paged
     * @param string  $search_word
     * @param string  $ordering
     * @param integer $products_per_page
     * @param string  $product_type
     *
     * @return array|stdClass
     */
    public static function dokan_spmv_get_products( int $paged = 1, string $search_word = '', string $ordering = '', int $products_per_page = 10, string $product_type = 'all' ) {
        $ordering          = WC()->query->get_catalog_ordering_args( $ordering );
        $search_args       = [
            'meta_key' => '_price', //phpcs:ignore.
            'status'   => 'publish',
            's'        => $search_word,
            'limit'    => $products_per_page,
            'page'     => $paged,
            'paginate' => true,
            'orderby'  => $ordering['orderby'],
            'order'    => $ordering['order'],
        ];

        if ( ! empty( $product_type ) && 'all' !== $product_type ) {
            $search_args['type'] = self::get_product_type_for_search( $product_type );
        } else {
            $search_args['type'] = array_diff(
                array_keys( wc_get_product_types() ),
                [
                    'auction',
                    'booking',
                ]
            );
        }

        return wc_get_products( $search_args );
    }
}
