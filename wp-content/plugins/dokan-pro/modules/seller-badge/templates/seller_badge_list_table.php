<?php
/**
 * @since 3.7.14
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}
?>
<?php do_action( 'dokan_dashboard_wrap_start' ); ?>

<div class="dokan-dashboard-wrap">

    <?php
    /**
     *  Adding dokan_dashboard_content_before hook
     *  Adding dokan_seller_badge_content_before hook
     *
     * @hooked get_dashboard_side_navigation
     *
     * @since  3.7.14
     */
    do_action( 'dokan_dashboard_content_before' );
    do_action( 'dokan_seller_badge_content_before' );
    ?>

    <div class="dokan-dashboard-content dokan-seller-badge-content">
        <?php
        /**
         *  Adding dokan_seller_badge_content_inside_before hook
         *
         * @since 3.7.14
         */
        do_action( 'dokan_seller_badge_content_inside_before' );
        ?>
        <article class="dokan-seller-badge-area">
            <?php
            /**
             * Adding dokan_seller_badge_content_area_header hook
             *
             * @since 3.7.14
             */
            do_action( 'dokan_seller_badge_content_area_header' );
            ?>
            <div class="entry-content">
                <?php
                /**
                 * Adding dokan_seller_badge_content hook
                 *
                 * @since 3.7.14
                 */
                do_action( 'dokan_seller_badge_content' );
                ?>

            </div><!-- .entry-content -->

        </article> <!-- .dokan-seller-badge-area -->

        <?php

        /**
         *  Adding dokan_seller_badge_content_inside_after hook
         *
         * @since 3.7.14
         */
        do_action( 'dokan_seller_badge_content_inside_after' );
        ?>
    </div><!-- .dokan-dashboard-content -->

    <?php
    /**
     *  Adding dokan_dashboard_content_after hook
     *  dokan_seller_badge_content_after hook
     *
     * @since 3.7.14
     */
    do_action( 'dokan_dashboard_content_after' );
    do_action( 'dokan_seller_badge_content_after' );
    ?>
</div><!-- .dokan-dashboard-wrap -->

<?php do_action( 'dokan_dashboard_wrap_end' ); ?>
