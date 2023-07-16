<?php
/*
Template Name: Vendor Template
*/

// Get the product ID from the query string parameter
get_header();

?>
<div class="site-content " role="main">

    <?php /* The loop */
    while (have_posts()) : the_post(); ?>
        <article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>

            <div class="entry-content">
                <?php echo woodmart_get_the_content(); ?>
                <?php wp_link_pages(array('before' => '<div class="page-links"><span class="page-links-title">' . esc_html__('Pages:', 'woodmart') . '</span>', 'after' => '</div>', 'link_before' => '<span>', 'link_after' => '</span>')); ?>
            </div>

            <?php woodmart_entry_meta(); ?>

        </article><!-- #post -->

        <?php
        // If comments are open or we have at least one comment, load up the comment template.
        if (woodmart_get_opt('page_comments') && (comments_open() || get_comments_number())) :
            comments_template();
        endif;
        ?>

    <?php endwhile; ?>

</div><!-- .site-content -->

<?php get_footer(); ?>
