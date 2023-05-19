<?php

namespace WeDevs\DokanPro;

use WeDevs\Dokan\Cache;

class Review {

    private $limit = 15;
    private $pending;
    private $spam;
    private $trash;
    private $approved;
    private $post_type;

    /**
     * Load automatically when class inistantiate
     *
     * @since 2.4
     *
     * @uses actions|filter hooks
     */
    public function __construct() {
        add_filter( 'dokan_get_dashboard_nav', array( $this, 'add_review_menu' ) );
        add_action( 'dokan_load_custom_template', array( $this, 'load_review_template' ) );

        add_action( 'dokan_review_content_inside_before', array( $this, 'show_seller_enable_message' ) );
        add_action( 'dokan_review_content_area_header', array( $this, 'dokan_review_header_render' ), 10 );
        add_action( 'dokan_review_content', array( $this, 'dokan_review_content_render' ), 10 );
        add_action( 'dokan_review_content_status_filter', array( $this, 'dokan_review_status_filter' ), 10, 2);
        add_action( 'dokan_review_content_listing', [ $this, 'review_content_listing' ], 10, 1 );
        add_action( 'dokan_manage_reviews_form', [ $this, 'render_manage_reviews_form' ], 10, 1 );
        add_action( 'dokan_review_listing_table_body', array( $this, 'dokan_render_listing_table_body' ), 10 );
        add_action( 'dokan_review_content_inside_after', array( $this, 'dokan_render_listing_table_script_template' ), 10 );
        add_action( 'template_redirect', array( $this, 'handle_status' ), 10 );

        add_action( 'wp_ajax_dokan_comment_status', array( $this, 'ajax_comment_status' ) );
        add_action( 'wp_ajax_dokan_update_comment', array( $this, 'ajax_update_comment' ) );

        // We need to clear cache after comment is created.
        // No need to check for update status or comment, those are handled by other hooks.
        add_action( 'comment_post', [ $this, 'clear_review_cache' ], 10, 3 );
    }

    /**
     * Show Seller Enable Error Message
     *
     * @since 2.4
     *
     * @return void
     */
    public function show_seller_enable_message() {
        $user_id = get_current_user_id();

        if ( ! dokan_is_seller_enabled( $user_id ) ) {
            echo dokan_seller_not_enabled_notice();
        }
    }

    /**
     * Add Review menu
     *
     * @param array $urls
     *
     * @since 2.4
     *
     * @return array $urls
     */
    public function add_review_menu( $urls ) {

        $urls['reviews'] = array(
            'title'      => __( 'Reviews', 'dokan' ),
            'icon'       => '<i class="far fa-comments"></i>',
            'url'        => dokan_get_navigation_url( 'reviews' ),
            'pos'        => 65,
            'permission' => 'dokan_view_review_menu'
        );

        return $urls;
    }

    /**
     * Load Review template
     *
     * @since 2.4
     *
     * @param  array $query_vars
     *
     * @return void [require once template]
     */
    public function load_review_template( $query_vars ) {
        if ( isset( $query_vars['reviews'] ) ) {

            if ( ! current_user_can( 'dokan_view_review_menu' ) ) {
                dokan_get_template_part('global/dokan-error', '', array( 'deleted' => false, 'message' => __( 'You have no permission to view review page', 'dokan' ) ) );
                return;
            } else {
                dokan_get_template_part( 'review/reviews', '', array( 'pro'=>true ) );
                return;
            }
        }
    }

    /**
     * Render Review Template Header content
     *
     * @since 2.4
     *
     * @return void
     */
    public function dokan_review_header_render() {
        dokan_get_template_part( 'review/header', '', array( 'pro' => true ) );
    }

    /**
     * Render dokan review content
     *
     * @since  2.4
     *
     * @return void
     */
    public function dokan_review_content_render() {
        $this->reviews_view();
    }

    /**
     * Counting spam, pending, trash and save it private variable
     *
     * @since 2.4
     *
     * @global object $wpdb
     * @global object $current_user
     *
     * @param string  $post_type
     *
     * @return void
     */
    public function get_count( $post_type ) {
        global $wpdb;

        $counts = dokan_count_comments( $post_type, dokan_get_current_user_id() );

        $this->pending  = $counts->moderated;
        $this->spam     = $counts->spam;
        $this->trash    = $counts->trash;
        $this->approved = $counts->approved;
    }

    /**
     * Hanlde Ajax Comment Status
     *
     * @since 2.4
     *
     * @return josn
     */
    public function ajax_comment_status() {
        if ( ! wp_verify_nonce( $_POST['nonce'], 'dokan_reviews' ) && !is_user_logged_in() ) {
            wp_send_json_error();
        }

        if ( ! current_user_can( 'dokan_manage_reviews' ) ) {
            wp_send_json_error( __( 'You have no permission to manage this review', 'dokan' ) );
            return;
        }

        $comment_id  = $_POST['comment_id'];
        $action      = $_POST['comment_status'];
        $post_type   = $_POST['post_type'];

        $comment = get_comment( $comment_id );
        if ( empty( $comment ) ) {
            return;
        }

        // invalidate reviews
        $post_id   = $comment->comment_post_ID;
        $seller_id = get_post_field( 'post_author', $post_id );
        Cache::invalidate_group( "product_reviews_{$seller_id}" );

        if ( $action == 'delete' && isset( $comment_id ) ) {
            wp_delete_comment( $comment_id );
        }

        if ( isset( $comment_id ) && isset( $action ) ) {
            wp_set_comment_status( $comment_id, $action );
        }

        $counts = dokan_count_comments( $post_type, dokan_get_current_user_id() );

        ob_start();
        $this->render_row( $comment, $post_type  );
        $html = array(
            'pending'  => $counts->moderated,
            'spam'     => $counts->spam,
            'trash'    => $counts->trash,
            'approved' => $counts->approved,
            'content'  => ob_get_clean()
        );

        wp_send_json_success( $html );
    }

    /**
     * Reviews View
     *
     * Reviews Comments this shortcode activation function
     *
     * @since 2.4
     *
     * @return void
     */
    public function reviews_view() {

        global $wpdb, $current_user;

        if ( is_user_logged_in() ) {

            // initialize
            $this->post_type = 'product';
            $post_type       = 'product';

            $counts        = dokan_count_comments( $post_type, dokan_get_current_user_id() );

            $this->pending  = $counts->moderated;
            $this->spam     = $counts->spam;
            $this->trash    = $counts->trash;
            $this->approved = $counts->approved;

            dokan_get_template_part( 'review/content', '', array(
                'pro'       => true,
                'post_type' => $post_type,
                'counts'    => $counts
            ) );
        }
    }

    /**
     * Dokan Render Review Status filter
     *
     * @since 2.4
     *
     * @param  string $post_type
     * @param  object $counts
     *
     * @return void
     */
    public function dokan_review_status_filter( $post_type, $counts ) {
        $this->review_comments_menu( $post_type, $counts );
    }

    /**
     * Render Review Listing content
     *
     * @since 2.4
     * @since 3.7.13 Updated method name from 'dokan_review_content_listing' to 'review_content_listing'.
     *
     * @param string $post_type
     *
     * @return void
     */
    public function review_content_listing( $post_type ) {
        if ( ! current_user_can( 'dokan_manage_reviews' ) && ! current_user_can( 'dokan_view_reviews' ) ) {
            return;
        }

        $comment_status = 'all';
        if ( isset( $_POST['dokan_comment_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dokan_comment_nonce'] ) ), 'dokan_comment_nonce_action' ) ) {
            $comment_status = isset( $_POST['comment_status'] ) ? sanitize_text_field( wp_unslash( $_POST['comment_status'] ) ) : 'all';
        }

        dokan_get_template_part(
            'review/listing', '', [
                'pro'            => true,
                'post_type'      => $post_type,
                'comment_status' => $comment_status,
                'manage_review'  => dokan_get_option( 'seller_review_manage', 'dokan_selling', 'on' ),
            ]
        );

        echo $this->pagination( $post_type );
    }

    /**
     * Show manage reviews form.
     *
     * @since 3.7.13
     *
     * @param string $comment_status
     *
     * @return void
     */
    public function render_manage_reviews_form( $comment_status = 'all' ) {

        $comment_status = isset( $_GET['comment_status'] ) 
            ? sanitize_text_field( $_GET['comment_status'] ) 
            : $comment_status;

        if ( dokan_get_option( 'seller_review_manage', 'dokan_selling', 'on' ) !== 'on' ) {
            return;
        }

        if ( ! current_user_can( 'dokan_manage_reviews' ) ) {
            return;
        }

        dokan_get_template_part(
            'review/manage-reviews', '', [
                'pro'            => true,
                'comment_status' => $comment_status,
            ]
        );
    }

    /**
     * Render Reviews Edit underscores template
     *
     * @since 2,4
     *
     * @return void
     */
    public function dokan_render_listing_table_script_template() {
        dokan_get_template_part( 'review/tmpl-review-script', '', array(
            'pro' => true,
        ) );
    }

    /**
     * Pagination
     *
     * @since 2.4
     *
     * @param string  $post_type
     *
     * @return string
     */
    public function pagination( $post_type ) {
        global $wpdb, $current_user;

        $status = $this->page_status();
        $user_id = dokan_get_current_user_id();

        $total = $wpdb->get_var(
            "SELECT COUNT(*)
            FROM $wpdb->comments, $wpdb->posts
            WHERE   $wpdb->posts.post_author='$user_id' AND
            $wpdb->posts.post_status='publish' AND
            $wpdb->comments.comment_post_ID=$wpdb->posts.ID AND
            $wpdb->comments.comment_approved='$status' AND
            $wpdb->posts.post_type='$post_type'"
        );

        $pagenum      = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
        $num_of_pages = ceil( $total / $this->limit );
        $base_url = dokan_get_navigation_url( 'reviews' );

        $page_links = paginate_links( array(
                'base'      => $base_url. '%_%',
                'format'    => '?pagenum=%#%',
                'add_args'  => false,
                'prev_text' => __( '&laquo;', 'aag' ),
                'next_text' => __( '&raquo;', 'aag' ),
                'total'     => $num_of_pages,
                'type'      => 'array',
                'current'   => $pagenum
            ) );

        if ( $page_links ) {
            $pagination_links  = '<div class="pagination-wrap">';
            $pagination_links .= '<ul class="pagination"><li>';
            $pagination_links .= join( "</li>\n\t<li>", $page_links );
            $pagination_links .= "</li>\n</ul>\n";
            $pagination_links .= '</div>';

            return $pagination_links;
        }
    }

    /**
     * Review Pagination
     *
     * @since 2.4
     * @since 3.3.4 `$wp_query` used for pagination generation.
     *
     * @param int     $id
     * @param string  $post_type
     * @param int     $limit
     * @param string  $status
     *
     * @return string
     */
    public function review_pagination( $id, $post_type, $limit, $status ) {
        global $wpdb;
        $paged   = ( get_query_var( 'paged' ) ) ? get_query_var( 'paged' ) : 1;
        $pagenum = $paged ? $paged : 1;

        $total = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM $wpdb->posts
                        INNER JOIN $wpdb->postmeta ON ( $wpdb->posts.ID = $wpdb->postmeta.post_id )
                        WHERE $wpdb->posts.post_author NOT IN ( %d, %d )
                        AND ( $wpdb->postmeta.meta_key = %s AND $wpdb->postmeta.meta_value = %s )
                        AND $wpdb->posts.post_type = %s
                        AND $wpdb->posts.post_status = %s",
                get_current_user_id(),
                $id,
                'store_id',
                $id,
                'dokan_store_reviews',
                'publish'
            )
        );

        $page_links = paginate_links(
            array(
                'base'      => dokan_get_store_url( $id ) . 'reviews/%_%',
                'format'    => 'page/%#%',
                'prev_text' => '&laquo;',
                'next_text' => '&raquo;',
                'type'      => 'array',
                'current'   => $pagenum,
                'total'     => ceil( $total / $limit ),
            )
        );
        wp_reset_postdata();

        if ( $page_links ) {
            $pagination_links  = '<div class="pagination-wrap">';
            $pagination_links .= '<ul class="pagination"><li>';
            $pagination_links .= join( "</li>\n\t<li>", $page_links );
            $pagination_links .= "</li>\n</ul>\n";
            $pagination_links .= '</div>';

            return $pagination_links;
        }
    }

    /**
     * Review Pagination with query param
     *
     * @since 3.3.4
     *
     * @param int     $id
     * @param string  $post_type
     * @param int     $limit
     * @param string  $status
     *
     * @return string
     */
    public function review_pagination_with_query( $id, $post_type, $limit, $status, $pagenum = 1 ) {
        global $wpdb;

        $total = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT count(c.comment_ID)
            FROM $wpdb->comments as c, $wpdb->posts as p
            WHERE p.post_author=%s AND
                p.post_status=%s AND
                c.comment_post_ID=p.ID AND
                c.comment_approved=%s AND
                p.post_type=%s  ORDER BY c.comment_ID DESC",
                $id,
                'publish',
                $status,
                $post_type
            )
        );

        $num_of_pages = ceil( $total / $limit );

        $page_links = paginate_links(
            array(
                'base'      => dokan_get_store_url( $id ) . 'reviews/%_%',
                'format'    => '?pagenum=%#%',
                'prev_text' => __( '&laquo;', 'dokan' ),
                'next_text' => __( '&raquo;', 'dokan' ),
                'total'     => $num_of_pages,
                'type'      => 'array',
                'current'   => $pagenum,
            )
        );

        if ( $page_links ) {
            $pagination_links  = '<div class="pagination-wrap">';
            $pagination_links .= '<ul class="pagination"><li>';
            $pagination_links .= join( "</li>\n\t<li>", $page_links );
            $pagination_links .= "</li>\n</ul>\n";
            $pagination_links .= '</div>';

            return $pagination_links;
        }
    }

    /**
     * Render Page status
     *
     * Return current page status.
     * Is it panding, spam, trash or all
     *
     * @since 2.4
     *
     * @return string
     */
    public function page_status() {
        $status = isset( $_GET['comment_status'] ) ? $_GET['comment_status'] : '';

        if ( $status == 'hold' ) {
            return '0';
        } else if ( $status == 'spam' ) {
                return 'spam';
            } else if ( $status == 'trash' ) {
                return 'trash';
            } else {
            return '1';
        }
    }

    /**
     * Get Comment Status
     *
     * @since 2.4
     *
     * @param  string $status
     *
     * @return string
     */
    public function get_comment_status( $status ) {
        switch ( $status ) {
        case '1':
            return 'approved';

        case '0':
            return 'pending';

        default:
            return $status;
        }
    }

    /**
     * Return all comments by comments status
     *
     * @since 2.4
     *
     * @global object $current_user
     * @global object $wpdb
     *
     * @param string  $post_type
     *
     * @return string
     */
    public function dokan_render_listing_table_body( $post_type ) {
        $status    = $this->page_status();
        $limit     = $this->limit;
        $seller_id = dokan_get_current_user_id();

        $cache_group = "product_reviews_{$seller_id}";
        $cache_key   = "get_product_reviews_{$post_type}_{$limit}_{$status}";
        $comments    = Cache::get( $cache_key, $cache_group );

        if ( false === $comments ) {
            $comments = $this->comment_query( $seller_id, $post_type, $limit, $status );

            Cache::set( $cache_key, $comments, $cache_group );
        }

        dokan_get_template_part( 'review/listing-table-body', '', array(
            'pro'       => true,
            'comments'  => $comments,
            'post_type' => $post_type
        ) );
    }

    /**
     * Comment Query
     *
     * @since 2.4
     *
     * @param  integer $id
     * @param  string $post_type
     * @param  integer $limit
     * @param  string $status
     *
     * @return object
     */
    public function comment_query( $id, $post_type, $limit, $status, $offset = false ) {
        global $wpdb;

        $perpage = $limit;
        $paged   = isset( $_REQUEST['pagenum'] ) ? absint( $_REQUEST['pagenum'] ): get_query_var( 'paged', 1 );
        $pagenum = $paged ? $paged : 1;
        $offset  = ( $pagenum - 1 ) * $perpage;

        $comments = $wpdb->get_results(
            "SELECT c.comment_content, c.comment_ID, c.comment_author,
                c.comment_author_email, c.comment_author_url,
                p.post_title, c.user_id, c.comment_post_ID, c.comment_approved,
                c.comment_date
            FROM $wpdb->comments as c, $wpdb->posts as p
            WHERE p.post_author='$id' AND
                p.post_status='publish' AND
                c.comment_post_ID=p.ID AND
                c.comment_approved='$status' AND
                p.post_type='$post_type'  ORDER BY c.comment_ID DESC
            LIMIT $offset,$limit"
        );

        return $comments;
    }

    /**
     * Render Comments Row
     *
     * @since 2.4
     *
     * @param  object $comment
     * @param  string $post_type
     *
     * @return void
     */
    public function render_row( $comment, $post_type ) {
        $comment_date       = get_comment_date( '', $comment->comment_ID );
        $comment_author_img = get_avatar( $comment->comment_author_email, 32 );
        $eidt_post_url      = get_edit_post_link( $comment->comment_post_ID );
        $permalink          = get_comment_link( $comment );
        $comment_status     =  $this->get_comment_status( $comment->comment_approved );

        $page_status = $this->page_status();

        dokan_get_template_part( 'review/listing-table-tr', '', array(
            'pro'                => true,
            'comment'            => $comment,
            'comment_date'       => $comment_date,
            'comment_author_img' => $comment_author_img,
            'eidt_post_url'      => $eidt_post_url,
            'permalink'          => $permalink,
            'page_status'        => $page_status,
            'post_type'          => $post_type,
            'comment_status'     => $comment_status
        ) );
    }

    /**
     * Update Comment via Ajax
     *
     * @since 2.4
     *
     * @return josn
     */
    public function ajax_update_comment() {
        if ( ! $this->quick_edit ) {
            wp_send_json_error( __( 'You can not edit reviews!', 'dokan' ) );
        }

        if ( !wp_verify_nonce( $_POST['nonce'], 'dokan_reviews' ) ) {
            wp_send_json_error();
        }

        $comment_id = absint( $_POST['comment_id'] );
        $commentarr = array(
            'comment_ID'           => $comment_id,
            'comment_content'      => $_POST['content'],
            'comment_author'       => $_POST['author'],
            'comment_author_email' => $_POST['email'],
            'comment_author_url'   => $_POST['url'],
            'comment_approved'     => $_POST['status'],
        );

        wp_update_comment( $commentarr );

        $comment = get_comment( $comment_id );

        //invalidate reviews
        $post_id   = $comment->comment_post_ID;
        $seller_id = get_post_field( 'post_author', $post_id );
        Cache::invalidate_group( "product_reviews_{$seller_id}" );

        ob_start();
        $this->render_row( $comment, $_POST['post_type'] );
        $html = ob_get_clean();
        wp_send_json_success( $html );
    }

    /**
     * Process bulk action
     *
     * @since 2.4
     */
    public function handle_status() {
        if ( ! isset( $_POST['comt_stat_sub'] ) ) {
            return;
        }

        if ( ! wp_verify_nonce( $_POST['dokan_comment_nonce'], 'dokan_comment_nonce_action' ) || ! is_user_logged_in() ) {
            return;
        }

        if ( ! current_user_can( 'dokan_manage_reviews' ) ) {
            return;
        }

        $action = $_POST['comment_status'];

        if ( empty( $_POST['commentid'] ) ) {
            return;
        }

        foreach ( $_POST['commentid'] as $commentid ) {
            $comment = get_comment( $commentid );
            if ( ! $comment ) {
                continue;
            }

            //invalidate reviews
            $post_id   = $comment->comment_post_ID;
            $seller_id = get_post_field( 'post_author', $post_id );
            Cache::invalidate_group( "product_reviews_{$seller_id}" );

            if ( $action == 'delete' ) {
                wp_delete_comment( $commentid );
            } else {
                wp_set_comment_status( $commentid, $action );
            }
        }

        $current_status = isset( $_GET['comment_status'] ) ? $_GET['comment_status'] : '';
        $redirect_to = add_query_arg( array( 'comment_status' => $current_status ), dokan_get_navigation_url('reviews') );
        wp_redirect( $redirect_to );
    }

    /**
     * Show Comment filter menu
     *
     * @since 2.4
     *
     * @param string  $post_type
     *
     * @return void
     */
    public function review_comments_menu( $post_type, $counts ) {
        $url          = dokan_get_navigation_url( 'reviews' );
        $pending      = isset( $counts->moderated ) ? $counts->moderated : 0;
        $spam         = isset( $counts->spam ) ? $counts->spam : 0;
        $trash        = isset( $counts->trash ) ? $counts->trash : 0;
        $approved     = isset( $counts->approved ) ? $counts->approved : 0;
        $status_class = ( isset( $_GET['comment_status'] ) && ! empty( $_GET['comment_status'] ) ) ? $_GET['comment_status'] : 'approved';

        dokan_get_template_part( 'review/status-filter', '', array(
            'pro'          => true,
            'url'          => $url,
            'pending'      => $pending,
            'spam'         => $spam,
            'trash'        => $trash,
            'approved'     => $approved,
            'status_class' => $status_class
        ) );
    }

    /**
     * Count all, pending, spam, trash Comments
     *
     * @since 2.4
     *
     * @param string $post_type
     * @param string $status
     *
     * @return object
     */
    public function count_status( $post_type, $status ) {
        global $wpdb, $current_user;

        return $totalcomments = $wpdb->get_var(
            "SELECT count($wpdb->comments.comment_ID)
            FROM $wpdb->comments, $wpdb->posts
            WHERE $wpdb->posts.post_author=dokan_get_current_user_id() AND
            $wpdb->posts.post_status='publish' AND
            $wpdb->comments.comment_post_ID=wp_posts.ID AND
            $wpdb->comments.comment_approved='$status' AND
            $wpdb->posts.post_type='$post_type'"
        );
    }

    public function render_store_tab_comment_list( $comments, $store_id ) {

        ob_start();
        if ( count( $comments ) == 0 ) {
            echo '<span colspan="5">' . __( 'No Reviews Found', 'dokan' ) . '</span>';
        } else {
            foreach ( $comments as $single_comment ) {
                if ( $single_comment->comment_approved ) {
                    $GLOBALS['comment'] = $single_comment;
                    $comment_date       = get_comment_date( '', $single_comment->comment_ID );
                    $comment_author_img = get_avatar( $single_comment->comment_author_email, 180 );
                    $permalink          = get_comment_link( $single_comment );
                    ?>

                    <li <?php comment_class(); ?> itemtype="http://schema.org/Review" itemscope="" itemprop="reviews">
                        <div class="review_comment_container">
                            <div class="dokan-review-author-img"><?php echo $comment_author_img; ?></div>
                            <div class="comment-text">
                                <a href="<?php echo $permalink; ?>">
                                    <?php
                                    if ( get_option( 'woocommerce_enable_review_rating' ) == 'yes' ) :
                                        $rating = intval( get_comment_meta( $single_comment->comment_ID, 'rating', true ) );
                                    ?>
                                        <div class="dokan-rating">
                                            <div itemprop="reviewRating" itemscope itemtype="http://schema.org/Rating" class="star-rating" title="<?php echo sprintf( __( 'Rated %d out of 5', 'dokan' ), $rating ) ?>">
                                                <span style="width:<?php echo ( intval( get_comment_meta( $single_comment->comment_ID, 'rating', true ) ) / 5 ) * 100; ?>%"><strong itemprop="ratingValue"><?php echo $rating; ?></strong> <?php _e( 'out of 5', 'dokan' ); ?></span>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </a>
                                <p>
                                    <strong itemprop="author"><?php echo $single_comment->comment_author; ?></strong>
                                    <em class="verified"><?php echo $single_comment->user_id == 0 ? sprintf( '(%s)', __( 'Guest', 'dokan' ) ) : ''; ?></em>
                                    –
                                    <a href="<?php echo $permalink; ?>">
                                        <time datetime="<?php echo date( 'c', strtotime( $comment_date ) ); ?>" itemprop="datePublished"><?php echo $comment_date; ?></time>
                                    </a>
                                </p>
                                <div class="description" itemprop="description">
                                    <p><?php echo $single_comment->comment_content; ?></p>
                                </div>
                            </div>
                        </div>
                    </li>

                    <?php
                }
            }
        }

        $review_list = ob_get_clean();

        return apply_filters( 'dokan_seller_tab_reviews_list', $review_list, $store_id );
    }

    /**
     * Clear review cache, on create new review.
     *
     * @since 3.4.2
     *
     * @param int        $comment_id
     * @param int|string $comment_approved
     * @param array      $comment_data
     *
     * @return void
     */
    public function clear_review_cache( $comment_id, $comment_approved, $comment_data ) {
        // Check post type
        $post_type = get_post_type( $comment_data['comment_post_ID'] );

        // clear cache if it is a product
        if ( 'product' !== $post_type ) {
            return;
        }

        $seller_id = get_post_field( 'post_author', $comment_data['comment_post_ID'] );

        Cache::invalidate_group( "product_reviews_{$seller_id}" );
    }
}
