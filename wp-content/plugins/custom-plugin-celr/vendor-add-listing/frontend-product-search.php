<?php


//product search
function product_search_shortcode()
{
    ob_start();
    ?>
    <form method="get" action="<?php echo esc_url(get_permalink()); ?>" class="product-search-form">
        <input type="text" name="product_search" id="product_search" placeholder="I’d like to sell...">
        <button type="submit">Search</button>
    </form>
    <div class="dropdown">
        <div id="product_search_results" class="dropdown-content"></div>
    </div>
    <?php
    wp_reset_query();

    return ob_get_clean();
}

add_shortcode('product_search', 'product_search_shortcode');

//product search and show product and send to form
function product_search_ajax_handler()
{
    if (isset($_GET['product_search'])) {
        $product_search = sanitize_text_field($_GET['product_search']);
        $products = new WP_Query(
            array(
                'post_type' => 'product',
                's' => $product_search,
                'posts_per_page' => -1,
            )
        );

        ob_start();

        if ($products->have_posts()) {
            while ($products->have_posts()) :
                $products->the_post();
                $product_id = get_the_ID();
    ?>
                <a href="<?php echo esc_url(add_query_arg('product_id', $product_id, get_permalink(get_page_by_path('add-vendor-price')))); ?>">
                    <?php echo get_the_title(); ?>
                </a>
    <?php
            endwhile;
        } else {
            echo 'No products found.';
        }

        wp_reset_postdata();
        $output = ob_get_clean();

        wp_send_json_success($output);
    }

    wp_send_json_error('Invalid request');
}

add_action('wp_ajax_product_search_ajax', 'product_search_ajax_handler');
add_action('wp_ajax_nopriv_product_search_ajax', 'product_search_ajax_handler');


?>