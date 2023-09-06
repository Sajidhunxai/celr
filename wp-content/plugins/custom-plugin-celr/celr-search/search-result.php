<?php
function custom_search_results_shortcode()
{
    // Get the search query from the URL parameter 'celr_search'
    $search_query = isset($_GET['celr_search']) ? sanitize_text_field($_GET['celr_search']) : '';
    $plugin_path = get_celr_dir();

    ob_start();
?>
		<div class="search-page-title">
			<h1>YOU SEARCHED FOR <br>
				 <?php if ($search_query): ?>
    <span>'<?php echo esc_html($search_query); ?>'</span>
  <?php else: ?>
    <span>'?'</span>
  <?php endif; ?>
			</h1>
		</div>
    <div class="custom-search-results">
		
        <div class=" container">
            <!-- Display product results -->
            <div class="search-result-cards row">

                <?php
                $product_args = array(
                    'post_type' => 'product', // Adjust the post type as needed
                    's' => $search_query,
                    'posts_per_page' => 12, // Number of products per page
                    'paged' => get_query_var('paged') ?: 1, // Use the current page number or default to 1
          
                );

                $product_query = new WP_Query($product_args);

                if ($product_query->have_posts()) : ?>
                    <?php while ($product_query->have_posts()) : $product_query->the_post(); ?>
                        <div class="search-card col-4">
                            <a href="<?php the_permalink(); ?>">
                                <div class="search-card-image">
                                    <?php // Display product image 
                                    if (has_post_thumbnail()) {
                                        the_post_thumbnail('large');
                                    } else {
                                        // Display a default image if no category thumbnail is available
                                        echo '<img src="' . esc_url(home_url('/wp-content/uploads/2023/05/Ellipse-15.png')) . '" alt="Celr Logo">';
                                    }
                                    ?>
                                </div>

                                <div class="search-card-content">
                                <div class="card-content-right">
 
                                    <p class="search-card-title"><?php the_title(); ?></p>
                                        <?php

                                        $post_type = get_post_type(get_the_ID());

                                        if ($post_type == 'product') {
                                            echo '<p>' . do_shortcode('[show_vintage]') . '</p>';
                                            echo '<p>' . do_shortcode('[display_price_variation]') . '</p>';
                                            echo '<p class="price-diff">' . do_shortcode('[price_difference]') . '</p>';
                                        }

                                        ?>

                                </div>
                                <div class="card-content-left">
                                    <div class="card-arrow-icon">
                                        <img src="<?php echo $plugin_path . '/assets/images/arrow.svg' ;?>" alt="Custom Arrow">
                                    </div>
                                </div>
                                </div>

                            </a>
                        </div>
                    <?php endwhile; 
                 
                    ?>
                    
                <?php endif;
                wp_reset_postdata(); ?>

                <!-- Display category results -->
                <?php
                $category_args = array(
                    'taxonomy' => 'product_cat',
                    'name__like' => $search_query,
                    'hide_empty' => false,
                    'number' => 6, // Number of categories per page
                    'paged' => get_query_var('paged') ?: 1, // Use the current page number or default to 1
    
                );

                $category_query = get_terms($category_args);

                if ($category_query) : ?>
                    <?php foreach ($category_query as $category) : ?>
                        <div class="search-card col-4">
                            <a href="<?php echo esc_url(get_term_link($category)); ?>">
                                <div class="search-card-image">
                                    <?php
                                    $category_thumbnail_id = get_term_meta($category->term_id, 'thumbnail_id', true);

                                    if (!empty($category_thumbnail_id)) {
                                        echo wp_get_attachment_image($category_thumbnail_id, 'large');
                                    } else {
                                        // Display a default image if no category thumbnail is available
                                        echo '<img src="' . esc_url(home_url('/wp-content/uploads/2023/05/Ellipse-15.png')) . '" alt="Celr Logo">';
                                    }
                                    ?>
                                </div>
                                <div class="search-card-content">
									                                    <p class="search-card-title"><?php echo esc_html($category->name); ?></p>

                                </div>


                            </a>
                        </div>
                    <?php endforeach; 
                     // Pagination for category results
                        // echo '<div class="pagination">';
                        // echo paginate_links(array(
                        //     'total' => ceil(count($category_query) / 12), // Calculate total pages
                        // ));
                        // echo '</div>';
                               // Pagination for product results
                    echo '<div class="search-page-pagination pagination">';
                    echo paginate_links(array(
                        'total' => $product_query->max_num_pages,
						 'prev_text' => '', 
   						 'next_text' => '', 
                    ));
                    echo '</div>';
                    ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php

    return ob_get_clean();
}
add_shortcode('custom_search_results', 'custom_search_results_shortcode');
?>