<?php 

// Register a custom meta box
function vendor_meta_box()
{
    add_meta_box('vendor_meta', 'Vendor Information', 'render_vendor_meta_box', 'product', 'normal', 'high');
}
add_action('add_meta_boxes', 'vendor_meta_box');

// Render the meta box content
// Function to render the vendor meta box
function render_vendor_meta_box($post)
{
    wp_nonce_field(basename(__FILE__), 'vendor_meta_nonce');
    $vendors = get_post_meta($post->ID, 'vendors', true);
    $current_user = wp_get_current_user();
    $user_name = $current_user->display_name;

    // Get the options for the attribute 'location'
    $location_attr = 'pa_location'; // Replace with the slug of the 'location' attribute
    $terms_locations = get_terms(
        array(
            'taxonomy' => $location_attr,
            'hide_empty' => false,
        )
    );
    $location_options = array();
    foreach ($terms_locations as $term) {
        $location_options[] = $term->name;
    }

    // Get the options for the attribute 'quantity' dynamically based on the current post
    $quantity_attr = 'pa_quantity';
    $terms_quantities = get_terms(
        array(
            'taxonomy' => $quantity_attr,
            'hide_empty' => false,
        )
    );
    $quantity_options = array();
    foreach ($terms_quantities as $term) {
        $quantity_options[] = $term->name;
    }

    // Get the options for the attribute 'vintage' dynamically based on the current post
    $quantity_attr = 'pa_vintage';
    $terms_vintages = get_terms(
        array(
            'taxonomy' => $quantity_attr,
            'hide_empty' => false,
        )
    );
    $vintages_options = array();
    foreach ($terms_vintages as $term) {
        $vintages_options[] = $term->name;
    }
    //get the options for 'format' dynamic based on the current post
    $format_attr = 'pa_format';
    $terms_formats = get_terms(
        array(
            'taxonomy' => $format_attr,
            'hide_empty' => false,
        )
    );
    $formats_options = array();
    foreach ($terms_formats as $term) {
        $formats_options[] = $term->name;
    }



    // Get the tags used for the product
    $product_tags = get_the_terms($post->ID, 'product_tag');
    $product_tag_names = array();
    if ($product_tags && !is_wp_error($product_tags)) {
        foreach ($product_tags as $tag) {
            $product_tag_names[] = $tag->name;
        }
    }

?>
    <div id="vendor-repeater">
        <?php if ($vendors) : ?>
            <?php foreach ($vendors as $vendor) : ?>
                <div class="vendor-row">
                    <input type="hidden" name="vendor_id[]" value="<?php echo esc_attr($vendor['id']); ?>" />
                    <input type="text" name="vendor_name[]" value="<?php echo esc_attr($vendor['name']); ?>" placeholder="Vendor Name" />
                    <input type="text" name="vendor_price[]" value="<?php echo esc_attr($vendor['price']); ?>" placeholder="Vendor Price" />
                    <select name="vendor_location[]">
                        <option value="">Select Location</option>
                        <?php foreach ($location_options as $option) : ?>
                            <option value="<?php echo esc_attr($option); ?>" <?php selected($vendor['location'], $option); ?>><?php echo esc_html($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="vendor_quantity[]">
                        <option value="">Select Quantity</option>
                        <?php foreach ($quantity_options as $option) : ?>
                            <option value="<?php echo esc_attr($option); ?>" <?php selected($vendor['quantity'], $option); ?>><?php echo esc_html($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="vendor_vintage[]">
                        <option value="">Select Vintage</option>
                        <?php foreach ($vintages_options as $option) : ?>
                            <option value="<?php echo esc_attr($option); ?>" <?php selected($vendor['vintage'], $option); ?>><?php echo esc_html($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <select name="vendor_format[]">
                        <option value="">Select Formats</option>
                        <?php foreach ($formats_options as $option) : ?>
                            <option value="<?php echo esc_attr($option); ?>" <?php selected($vendor['format'], $option); ?>><?php echo esc_html($option); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="vendor_purchase[]" value="<?php echo esc_attr($vendor['purchase']); ?>" placeholder="Vendor Purchase Price" />
                    <select name="vendor_tags[]">
                        <option value="">Select Tag</option>
                        <?php foreach ($product_tag_names as $tag) : ?>
                            <option value="<?php echo esc_attr($tag); ?>"><?php echo esc_html($tag); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button class="remove-vendor">Remove</button>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <div class="vendor-row">
                
                <button class="remove-vendor">Remove</button>
            </div>
        <?php endif; ?>
        <button id="add-vendor">Add Vendor</button>
        <button type="submit" name="submit_vendor_form">Submit</button> <!-- Added submit button -->
    </div>
    <script>
        jQuery(function($) {
            $('#add-vendor').on('click', function(e) {
                e.preventDefault();
                var row = '<div class="vendor-row">' +
                    '<input type="text" name="vendor_name[]" value="<?php echo esc_attr($user_name); ?>" placeholder="Vendor Name" />' +
                    '<input type="text" name="vendor_price[]" placeholder="Vendor Price" />' +
                    '<select name="vendor_location[]">' +
                    '<option value="">Select Location</option>' +
                    '<?php foreach ($location_options as $option) : ?>' +
                    '<option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>' +
                    '<?php endforeach; ?>' +
                    '</select>' +
                    '<select name="vendor_quantity[]">' +
                    '<option value="">Select Quantity</option>' +
                    '<?php foreach ($quantity_options as $option) : ?>' +
                    '<option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>' +
                    '<?php endforeach; ?>' +
                    '</select>' +
                    '<select name="vendor_vintage[]">' +
                    '<option value="">Select Vintage</option>' +
                    '<?php foreach ($vintages_options as $option) : ?>' +
                    '<option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>' +
                    '<?php endforeach; ?>' +
                    '</select>' +
                    '<select name="vendor_format[]">' +
                    '<option value="">Select Format</option>' +
                    '<?php foreach ($formats_options as $option) : ?>' +
                    '<option value="<?php echo esc_attr($option); ?>"><?php echo esc_html($option); ?></option>' +
                    '<?php endforeach; ?>' +
                    '</select>' +
                    '<select name="vendor_tags[]">' +
                    '<option value="">Select Tag</option>' +
                    '<input type="number" name="vendor_purchase[]" placeholder="Vendor Purchase Price" />' +
                    '<?php foreach ($product_tag_names as $tag) : ?>' +
                    '<option value="<?php echo esc_attr($tag); ?>"><?php echo esc_html($tag); ?></option>' +
                    '<?php endforeach; ?>' +
                    '</select>' +
                    '<button class="remove-vendor">Remove</button>' +
                    '</div>';
                $('#vendor-repeater').append(row);
            });

            $(document).on('click', '.remove-vendor', function(e) {
                e.preventDefault();
                var vendorRow = $(this).closest('.vendor-row');
                var vendorId = vendorRow.find('input[name="vendor_id[]"]').val();

                $.ajax({
                    type: 'POST',
                    url: ajaxurl, // Use the correct URL to the WordPress admin-ajax.php file
                    data: {
                        action: 'remove_vendor',
                        vendor_id: vendorId,
                    },
                    success: function(response) {
                        if (response.success) {
                            vendorRow.remove();
                        } else {
                            console.log(response.error);
                        }
                    },
                    error: function(jqXHR, textStatus, errorThrown) {
                        console.log(errorThrown);
                    }
                });
            });

            // Submit form on button click
            $('button[name="submit_vendor_form"]').on('click', function(e) {
                e.preventDefault();
                $('form#post').submit();
            });
        });
    </script>
    <?php
}




?>