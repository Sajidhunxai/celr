<?php

namespace WeDevs\DokanPro\Modules\RankMath;

defined( 'ABSPATH' ) || exit;

use RankMath\Admin\Metabox\Post_Screen;

/**
 * Class for post screen handler
 *
 * @since 3.4.0
 */
class PostScreen extends Post_Screen {

    /**
     * Class constructor
     *
     * @since 3.4.0
     */
    public function __construct() {

        /**
         * At a point the function `get_sample_permalink`
         * is being used by a private method of the parent class.
         *
         * But as the `get_sample_permalink` function works
         * only within the admin panel, we need to require
         * the file that contains the function as we need to use
         * the function on the frontend for Vendor Dashboard.
         *
         * Note that we cannot override the method that is using
         * the function as the method is private in the parent class.
         */
        if ( ! function_exists( 'get_sample_permalink' ) ) {
            require_once ABSPATH . 'wp-admin/includes/post.php';
        }

        parent::__construct();
    }

    /**
     * Retrieves object id
     *
     * @since 3.4.0
     * @since 3.7.13 Added logics for handle from the new
     *                        Vendor Dashboard Product edit page.
     *
     * @return int
     */
    public function get_object_id() {
        $product_id = ! empty( $_GET['product_id'] ) ? absint( wp_unslash( $_GET['product_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

        if ( ! empty( $product_id ) ) {
            return $product_id;
        }

        /**
         * Fetch `dokan_rank_math_edit_post_id` from user meta
         *
         * As, we're storing current edit post id when user goes in
         * Product edit page and updates this value through API.
         *
         * Then also reload the page and fetch from this object id.
         */
        $user_id = dokan_get_current_user_id();
        return (int) get_user_meta( $user_id, 'dokan_rank_math_edit_post_id', true );
    }
}
