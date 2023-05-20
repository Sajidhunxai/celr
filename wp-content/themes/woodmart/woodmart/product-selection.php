<?php
/*
Template Name: Product Selection
*/


get_header();


if (isset($_GET['product_id'])) {
    $product_id = intval($_GET['product_id']);
    echo do_shortcode('[vendor_form product_id=' . $product_id . ']');
} else {
    // Display product search form
    if (isset($_GET['product_search'])) {
        $product_search = sanitize_text_field($_GET['product_search']);
        $products = new WP_Query(array(
            'post_type' => 'product',
            's' => $product_search,
            'posts_per_page' => -1,
        ));

        if ($products->have_posts()) {
            while ($products->have_posts()) : $products->the_post();
                $product_id = get_the_ID();
                ?>
                <a href="<?php echo esc_url(add_query_arg('product_id', $product_id)); ?>">
                    <?php echo get_the_title(); ?>
                </a>
                <br>
                <?php
            endwhile;
        } else {
            echo 'No products found.';
        }

        wp_reset_postdata();
    } else {
        // Display the search form
        ?>
        <form method="get" action="<?php echo esc_url(get_permalink()); ?>">
            <input type="text" name="product_search" placeholder="Search Products" />
            <input type="submit" value="Search" />
        </form>
        <?php
    }
}
?>
<?php get_footer(); ?>
