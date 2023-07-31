<?php

function display_product_attributes($atts)
{
    global $product;

    // Check if a product is currently being displayed
    if (!$product) {
        return ''; // No product found, return empty string
    }

    // Get the product attributes
    $attributes = $product->get_attributes();

    // Check if there are attributes
    if (!empty($attributes)) {
        // Filter and display specific attributes
        $filtered_attributes = array_intersect_key($attributes, array_flip(['pa_color', 'pa_region', 'pa_drinking_windows']));

        // Output the attributes as a list
        $output = '<ul class="attributes-add-form">';
        foreach ($filtered_attributes as $attribute) {
            // Get attribute terms using ids returned by get_options()
            $terms = array_map(function ($term_id) {
                $term = get_term($term_id);
                return $term ? $term->name : '';  // if the term exists, return its name
            }, $attribute->get_options());
            $attribute_name = str_replace(['pa_', '_'], ['', ' '], $attribute->get_name());
            $attribute_name = ucwords(strtolower($attribute_name)); // Convert to sentence case

            $output .= '<li class="attribute-add-form-item"><b>' . $attribute_name . ':</b> ' .
                '<b>' . esc_html(implode(', ', $terms)) .
                '</b></li>';
        }
        $output .= '</ul>';

        return $output;
    }

    return ''; // No attributes found, return empty string
}
add_shortcode('product_attributes', 'display_product_attributes');

?>