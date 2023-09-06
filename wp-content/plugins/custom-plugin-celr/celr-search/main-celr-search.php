<?php
function celr_enqueue_product_search_scripts() {
    $plugin_path = get_celr_dir();

    wp_enqueue_script(
        'celr-search',
        $plugin_path . 'assets/js/celrMainSearch.js',
        array('jquery'),
        '1.0',
        true
    );

    // Localize the AJAX URL
    wp_localize_script(
        'celr-search',
        'celrSearchAjax',
        array('ajaxurl' => admin_url('admin-ajax.php'))
    );
}
add_action('wp_enqueue_scripts', 'celr_enqueue_product_search_scripts');

//product search
function celr_search_shortcode()
{
    ob_start();
    ?>
    <form method="get" action="<?php echo home_url('/search-result') ?> " class="celr-search-form">
        <input type="text" name="celr_search" id="celr_search" placeholder="I’m looking for...">
        <button type="submit">s</button>
    </form>
    <div class="search-dropdown dropdown">
        <div id="celr_search_results" class="dropdown-content" style="display:none;">
            </div>
    </div>
    <?php
    wp_reset_query();

    return ob_get_clean();
}
add_shortcode('celr_search', 'celr_search_shortcode');

function celr_search_ajax_handler()
{
    if (isset($_GET['celr_search'])) {
        $product_search = sanitize_text_field($_GET['celr_search']);


        // Search for products
        $product_args = array(
            'post_type' => 'product',
            's' => $product_search,
            'posts_per_page' => -1,
        );

        $products = new WP_Query($product_args);

        // Search for categories
        $category_args = array(
            'taxonomy' => 'product_cat',
            'name__like' => $product_search,
            'hide_empty' => false,

        );

        $categories = get_terms($category_args);

        ob_start();

        if ($products->have_posts() || !empty($categories)) {
            while ($products->have_posts()) :
                $products->the_post();
                $product_id = get_the_ID();

                // Display product title here
                ?>
                <a href="<?php echo get_permalink($product_id); ?>">
                    <?php echo get_the_title(); ?>
                </a>
                <?php
            endwhile;

            // Display matching categories
            if (!empty($categories)) {
                foreach ($categories as $category) {
                    ?>
                    <div class="category-item">
                        <a href="<?php echo esc_url(get_term_link($category)); ?>">
                            <?php echo $category->name; ?>
                        </a>
                    </div>
                    <?php
                }
            }
        } else {
            echo 'No products or categories found.';
        }

        wp_reset_postdata();
        $output = ob_get_clean();

        wp_send_json_success($output);

    }

    wp_send_json_error('Invalid request');
}

add_action('wp_ajax_celr_search_ajax', 'celr_search_ajax_handler');
add_action('wp_ajax_nopriv_celr_search_ajax', 'celr_search_ajax_handler');


function my_query_by_post_types( $query ) {
	$query->set( 'post_type', [ 'product_cat', 'custom-post-type2' ] );
}
add_action( 'elementor/query/{$query_id}', 'my_query_by_post_types' );
?>