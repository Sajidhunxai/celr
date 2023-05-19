<?php

namespace WeDevs\DokanPro\Modules\StoreReviews;

use WeDevs\DokanPro\Modules\StoreReviews\Emails\Manager;

class Module {

    /**
     * Constructor for the Dokan_Store_Reviews class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
        define( 'DOKAN_SELLER_RATINGS_PLUGIN_VERSION', '1.1.0' );
        define( 'DOKAN_SELLER_RATINGS_DIR', dirname( __FILE__ ) );
        define( 'DOKAN_SELLER_RATINGS_PLUGIN_ASSEST', plugins_url( 'assets', __FILE__ ) );

        //hooks
        add_action( 'init', array( $this, 'register_dokan_store_review_type' ) );
        add_action( 'dokan_seller_rating_value', array( $this, 'replace_rating_value' ), 10, 2 );
        add_filter( 'dokan_seller_tab_reviews_list', array( $this, 'replace_ratings_list' ), 10 ,2 );

        $this->includes();
        $this->instances();

        // Loads frontend scripts and styles
        add_action( 'init', array( $this, 'register_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        add_filter( 'dokan_rest_api_class_map', array( $this, 'rest_api_class_map' ) );

    }

    /**
     * Register scripts
     *
     * @since 3.7.4
     */
    public function register_scripts() {
        list( $suffix, $version ) = dokan_get_script_suffix_and_version();

        wp_register_style( 'dsr-styles', plugins_url( 'assets/css/style' . $suffix . '.css', __FILE__ ), false, $version );
        wp_register_script( 'dsr-scripts', plugins_url( 'assets/js/script' . $suffix . '.js', __FILE__ ), array( 'jquery', 'dokan-magnific-popup' ), $version, true );
        wp_register_style( 'dsr-scripts', plugins_url( 'assets/css/script' . $suffix . '.css', __FILE__ ), false, $version );
    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     * @uses wp_enqueue_style
     */
    public function enqueue_scripts() {
        //only load the scripts on store page for optimization
        if ( dokan_is_store_page() ) {
            wp_enqueue_style( 'dokan-magnific-popup' );
            wp_enqueue_style( 'dsr-styles' );
            wp_enqueue_script( 'dsr-scripts' );
            wp_enqueue_style( 'dsr-scripts' );
        }

        if ( dokan_is_store_listing() ) {
            wp_enqueue_style( 'dsr-styles' );
        }
    }

    /**
     * Include files
     *
     * @return void
     */
    public function includes() {
        if ( is_admin() ) {
            require_once DOKAN_SELLER_RATINGS_DIR.'/classes/admin.php';
        }
        require_once DOKAN_SELLER_RATINGS_DIR . '/classes/Emails/Manager.php';
        require_once DOKAN_SELLER_RATINGS_DIR.'/classes/DSR_View.php';
        require_once DOKAN_SELLER_RATINGS_DIR.'/classes/DSR_SPMV.php';
        require_once DOKAN_SELLER_RATINGS_DIR . '/functions.php';
    }

    public function instances() {
        new \DSR_SPMV();
        new Manager();
    }

    /**
     * REST API classes Mapping
     *
     * @since 2.9.5
     *
     * @return void
     */
    public function rest_api_class_map( $class_map ) {
        $class_map[ DOKAN_SELLER_RATINGS_DIR.'/classes/api/class-store-reviews-controller.php'] = 'Dokan_REST_Store_Review_Controller';

        return $class_map;
    }

     /**
     * Register Custom Post type for Store Reviews
     *
     * @since 1.0
     *
     * @return void
     */
    public function register_dokan_store_review_type() {
        $labels = array(
            'name'               => __( 'Store Reviews', 'Post Type General Name', 'dokan' ),
            'singular_name'      => __( 'Store Review', 'Post Type Singular Name', 'dokan' ),
            'menu_name'          => __( 'Store Reviews', 'dokan' ),
            'name_admin_bar'     => __( 'Store Reviews', 'dokan' ),
            'parent_item_colon'  => __( 'Parent Item', 'dokan' ),
            'all_items'          => __( 'All Reviews', 'dokan' ),
            'add_new_item'       => __( 'Add New review', 'dokan' ),
            'add_new'            => __( 'Add New', 'dokan' ),
            'new_item'           => __( 'New review', 'dokan' ),
            'edit_item'          => __( 'Edit review', 'dokan' ),
            'update_item'        => __( 'Update review', 'dokan' ),
            'view_item'          => __( 'View review', 'dokan' ),
            'search_items'       => __( 'Search review', 'dokan' ),
            'not_found'          => __( 'Not found', 'dokan' ),
            'not_found_in_trash' => __( 'Not found in Trash', 'dokan' ),
        );

        $args   = array(
            'label'             => __( 'Store Reviews', 'dokan' ),
            'description'       => __( 'Store Reviews by customer', 'dokan' ),
            'labels'            => $labels,
            'supports'          => array( 'title', 'author', 'editor' ),
            'hierarchical'      => false,
            'public'            => false,
            'publicly_queryable' => true,
            'show_in_menu'      => false,
            'show_in_rest'      => true,
            'menu_position'     => 5,
            'show_in_admin_bar' => false,
            'rewrite'           => array( 'slug' => '' ),
            'can_export'        => true,
            'has_archive'       => true,
        );

        register_post_type( 'dokan_store_reviews', $args );
    }

    /**
     * Filter Dokan Core rating calculation value
     *
     * @since 1.0
     *
     * @param array $rating
     * @param int $store_id
     *
     * @return array calculated Rating
     */
    public function replace_rating_value( $rating, $store_id ) {
        $args = array(
            'post_type'      => 'dokan_store_reviews',
            'meta_key'       => 'store_id',
            'meta_value'     => $store_id,
            'post_status'    => 'publish',
        );

        $query = new \WP_Query( $args );

        $review_count = $query->post_count;

        if ( $review_count ) {

            $rating = 0;
            foreach ( $query->posts as $review ) {
                $rating += intval( get_post_meta( $review->ID, 'rating', true ) );
            }

            $rating = number_format( $rating / $review_count, 2 );
        } else {
            $rating = __( 'No Ratings found yet', 'dokan' );
        }

        return array(
            'rating' => $rating,
            'count'  => $review_count
        );
    }

    /**
     * Filter the Review list shown on review tab by default core
     *
     * @since 1.0
     *
     * @param string $review_list
     * @param int $store_id
     *
     * @return string Review List HTML
     */
    public function replace_ratings_list( $review_list, $store_id ) {
        $paged = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
        $args = array(
            'post_type'      => 'dokan_store_reviews',
            'meta_key'       => 'store_id',
            'meta_value'     => $store_id,
            'post_status'    => 'publish',
            'author__not_in' => array( get_current_user_id(), $store_id ),
            'paged'          => $paged,
            'posts_per_page' => 20,
        );

        $query = new \WP_Query( $args );
        $no_review_msg = apply_filters( 'dsr_no_review_found_msg', 'No Reviews found' );
        ob_start();

        \DSR_View::init()->render_review_list( $query->posts, $no_review_msg );

        wp_reset_postdata();

        return ob_get_clean();
    }
}
