<?php

defined( 'ABSPATH' ) || exit;

/**
 * Dokan Review Listing Template
 *
 * @since 2.4
 *
 * @package dokan
 */

    /**
     * Dokan_manage_reviews_form hook.
     *
     * @since 3.7.13
     */
    do_action( 'dokan_manage_reviews_form', $comment_status );
?>

    <table id="dokan-comments-table" class="dokan-table dokan-table-striped">
        <thead>
            <tr>
                <?php if ( 'on' === $manage_review && current_user_can( 'dokan_manage_reviews' ) ) { ?>
                    <th class="col-check"><input class="dokan-check-all" type="checkbox"></th>
                <?php } ?>
                <th class="col-author"><?php esc_html_e( 'Author', 'dokan' ); ?></th>
                <th class="col-content"><?php esc_html_e( 'Comment', 'dokan' ); ?></th>
                <th class="col-link"><?php esc_html_e( 'Link To', 'dokan' ); ?></th>
                <th class="col-link"><?php esc_html_e( 'Rating', 'dokan' ); ?></th>
            </tr>
        </thead>

        <tbody>

            <?php

            /**
             * Dokan_review_listing_table_body hook
             *
             * @hooked dokan_render_listing_table_body
             */
            do_action( 'dokan_review_listing_table_body', $post_type )
            ?>

        </tbody>
    </table>

</form>