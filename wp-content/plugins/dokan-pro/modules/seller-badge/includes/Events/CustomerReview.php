<?php

namespace WeDevs\DokanPro\Modules\SellerBadge\Events;

use WeDevs\DokanPro\Modules\SellerBadge\Abstracts\BadgeEvents;
use WP_Comment;
use WP_Comment_Query;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

/**
 * Class CustomerReview. This class will handle the customer review logic.
 *
 * @since   3.7.14
 *
 * @package WeDevs\DokanPro\Modules\SellerBadge\Events
 */
class CustomerReview extends BadgeEvents {

    /**
     * Class constructor
     *
     * @since 3.7.14
     *
     * @param string $event_type
     */
    public function __construct( $event_type ) {
        parent::__construct( $event_type );
        // return in case of error
        if ( is_wp_error( $this->badge_event ) ) {
            return;
        }
        add_action( 'comment_post', [ $this, 'process_hook' ], 99, 2 );
        add_action( 'wp_set_comment_status', [ $this, 'process_hook' ], 99, 2 );
    }

    /**
     * Process hooks related to this badge
     *
     * @since 3.7.14
     *
     * @param int        $comment_id
     * @param int|string $comment_approved
     *
     * @return void
     */
    public function process_hook( $comment_id, $comment_approved ) {
        $review = get_comment( $comment_id );

        // Bail silently if this is not a review, or a reply to a review.
        if ( ! $this->is_review_or_reply( $review ) ) {
            return;
        }

        if ( ! in_array( $comment_approved, [ 1, '1', 'approve' ], true ) ) {
            return;
        }

        if ( false === $this->set_badge_and_badge_level_data() ) {
            return;
        }

        // if badge status is draft, no need to update vendor badges
        if ( 'published' !== $this->badge_data['badge_status'] ) {
            return;
        }

        $vendor_id = dokan_get_vendor_by_product( $review->comment_post_ID, true );
        if ( $vendor_id ) {
            $this->run( $vendor_id );
        }
    }

    /**
     * Determines if the object is a review or a reply to a review.
     *
     * @since 3.7.14
     *
     * @param WP_Comment|mixed $object Object to check.
     *
     * @return bool
     */
    protected function is_review_or_reply( $object ) {
        return $object instanceof WP_Comment && in_array( $object->comment_type, [ 'review', 'comment' ], true ) && get_post_type( $object->comment_post_ID ) === 'product';
    }

    /**
     * Get current compare data
     *
     * @since 3.7.14
     *
     * @param int $vendor_id
     *
     * @return int
     */
    protected function get_current_data( $vendor_id ) {
        $args = [
            'post_type'        => [ 'product' ],
            'post_author__in'  => [ $vendor_id ],
            'count'            => true,
            'meta_key'         => 'rating',
            'meta_value'       => '5',
            'meta_compare_key' => '=',
            'meta_type_key'    => 'NUMERIC',
        ];

        $comments_query = new WP_Comment_Query( $args );
        $comment_count  = $comments_query->get_comments();

        if ( empty( $comments_query ) ) {
            return false;
        }

        return round( $comment_count, 2 );
    }
}
