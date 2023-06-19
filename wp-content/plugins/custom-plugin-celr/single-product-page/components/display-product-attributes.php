<?php

function display_product_attributes($atts)
{
    // Extract the product ID from the shortcode attributes
    $atts = shortcode_atts(
        array(
            'product_id' => 0,
        ),
        $atts
    );

    // Get the product ID
    $product_id = intval($atts['product_id']);
    $product = wc_get_product($product_id);

    // Check if the product object is valid
    if ($product) {
        // Get the product attributes
        $attributes = $product->get_attributes();

        // Check if there are attributes
        if (!empty($attributes)) {
            // Filter and display specific attributes
            $filtered_attributes = array_intersect_key($attributes, array_flip(['pa_color', 'pa_region', 'pa_drinking_windows']));

            // Output the attributes as a list
            echo '<ul class="attributes-add-form">';
            foreach ($filtered_attributes as $attribute) {
                // Get attribute terms using ids returned by get_options()
                $terms = array_map(function ($term_id) {
                    $term = get_term($term_id);
                    return $term ? $term->name : '';  // if the term exists, return its name
                }, $attribute->get_options());
                $attribute_name = str_replace('pa_', '', $attribute->get_name());
                $attribute_name = ucwords(strtolower($attribute_name)); // Convert to sentence case

                echo '<li class="attribute-add-form-item"><b>' . $attribute_name . ':</b> ' .
                    '<b>' . esc_html(implode(', ', $terms)) .
                    '</b></li>';
            }
            echo '</ul>';
        }
    }
}
add_shortcode('product_attributes', 'display_product_attributes');

?>