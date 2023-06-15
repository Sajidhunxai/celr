<?php

function vintage_filter_shortcode($atts)
{
    $product_id = get_the_ID();

    $vendors = get_post_meta($product_id, 'vendors', true);
    $vintages = array_unique(array_column($vendors, 'vintage'));

    // Check for vintage filter
    $vintage_filters = isset($_GET['vintage_filter']) ? $_GET['vintage_filter'] : [];

    // Output the filter form
    ob_start();
    echo '<form action="" method="get" id="vintage-filter-form" class="vintage-filter-form">';

    // Generate button for each unique vintage
    echo '<button type="submit" class="vintage-filter-button" name="vintage_filter[]" value="all">All</button>';

    foreach ($vintages as $vintage) {
        $active = (in_array($vintage, $vintage_filters)) ? ' active' : '';
        echo '<button type="submit" class="vintage-filter-button' . $active . '" name="vintage_filter[]" value="' . $vintage . '">' . $vintage . '</button>';
    }

    // Button to select all vintages

    echo '</form>';

    // Add JavaScript to add active class to button on click
    echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';
    echo '<script>
        $(".vintage-filter").click(function() {
            $(this).toggleClass("active");
        });
    </script>';

    return ob_get_clean();
}
add_shortcode('vintage_filter', 'vintage_filter_shortcode');

?>