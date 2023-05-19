<?php
global $wp_query;

$wp_query->rewind_posts();
?>
<?php if ( have_posts() ): ?>

    <div class="seller-items site-main woocommerce">

        <?php woocommerce_product_loop_start(); ?>

            <?php while ( have_posts() ) : the_post(); ?>

                <?php wc_get_template_part( 'content', 'product' ); ?>

            <?php endwhile; // end of the loop. ?>

        <?php woocommerce_product_loop_end(); ?>

    </div>

    <?php dokan_content_nav( 'nav-below' ); ?>

<?php else: ?>

    <p class="dokan-info"><?php esc_html_e( 'No products were found on this vendor!', 'dokan' ); ?></p>

<?php endif; ?>
