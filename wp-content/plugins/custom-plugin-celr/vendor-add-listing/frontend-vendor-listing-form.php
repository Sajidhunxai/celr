<?php 
function user_has_submitted_vendor_data($user_id){
    $vendor_data = get_user_meta($user_id, 'vendor', true);
    return !empty($vendor_data);
}



//vendor-form
add_shortcode('vendor_form', 'vendor_form_shortcode');
function vendor_form_shortcode($atts)
{
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;

        // Check if the user has the 'vendor' or 'administrator' role
        if (in_array('author', $user_roles) || in_array('administrator', $user_roles)|| in_array('seller', $user_roles) || in_array('vendor', $user_roles)) {

            // User is logged in, show the form

            $atts = shortcode_atts(
                array(
                    'product_id' => 0,
                ),
                $atts
            );

            // Get the product ID
            $product_id = intval($atts['product_id']);

            // Get the product tags
            $product_tags = get_the_terms($product_id, 'product_tag');
            if ($product_tags && !is_wp_error($product_tags)) {
                foreach ($product_tags as $tag) {
                    $product_tag_names[] = $tag->name;
                }
            }

            // Get the post data
            $post = get_post($product_id);
            $post_title = $post->post_title;
            $post_content = $post->post_content;
            $post_image = get_the_post_thumbnail_url($product_id, 'full');

            //get price and attributes
            $product_attributes = get_post_meta($product_id, 'product_attributes', true);

            $product_price = wc_get_product($product_id);
            $regular_price = $product_price->get_regular_price();
            //lowest price


            ob_start();

    ?>
            <div>
                <div class="column-container">
                    <div class="column large">
                        <?php if ($post_image) : ?>
                            <img src="<?php echo esc_url($post_image); ?>" alt="<?php echo esc_html($post_title); ?>" width="400" />
                        <?php else : ?>
                            <img src="<?php echo get_site_url() . '/wp-content/uploads/2023/05/image-44.png'; ?> " alt="Default Image" width="400" />
                        <?php endif; ?>

                    </div>

                    <div class="column large">
                        <h2 id="add-product-title">
                            <?php echo esc_html(strtoupper($post_title)); ?>
                        </h2>
                        <div id="vendor-repeater">
                        <?php 
                       $current_user = wp_get_current_user();
                       $user_name = $current_user->user_login;
                       
                       $existing_vendors = get_post_meta($product_id, 'vendors', true);
                       
                       $has_submitted_data = false;
                       if (!empty($existing_vendors)) {
                           foreach ($existing_vendors as $existing_vendor) {
                               if ($existing_vendor['name'] === $user_name) {
                                   $has_submitted_data = true;
                                   break;
                               }
                           }
                       }
                       
                       if($has_submitted_data) {
                           echo ' You have already submitted your price for this product. You cannot submit again.</div></div>';
                           echo '<div class="column">';
                           echo '</div>';
                           ?>
                           
                             
                        <?php
                       } else {
                        ?>  
                        <form method="post">
                                <?php wp_nonce_field(basename(__FILE__), 'vendor_meta_nonce'); ?>
                                <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>" />

                                <div class="vendor-row">
                                    <?php
                                    $current_user = wp_get_current_user();
                                    $user_name = $current_user->user_login;
                                    
                                    ?>

                                    <input type="hidden" name="vendor_name[]" placeholder="Vendor Name" value="<?php echo esc_attr($user_name); ?>" readonly />

                                    <div class="form-control">
                                        <label for="price" required style="display: inline-block;">My offer price<span class="required">*</span></label>
                                        <div style="position: relative;">
                                            <input type="number" name="vendor_price[]" placeholder="" max="1000" min="1" required />
                                            <span style="position: absolute; top: 45%; transform: translateY(-50%); left: 20px;">£</span>
                                        </div>
                                    </div>
                                    <div class="form-control">
                                        <label for="vintage" required style="display: inline-block;">Vintage<span class="required">*</span></label>
                                        <div class="custom-select-wrapper"> <!-- Add a custom select wrapper -->
                                            <select name="vendor_vintage[]" class="custom-select">
                                                <!-- Assign a class for custom select styling -->
                                                <?php
                                            
                                            
                                                $attribute_vintage = 'pa_vintage'; // Replace with your actual attribute slug
                                            
                                                $terms_vintages = $product_price->get_attribute($attribute_vintage);
                                            
                                                if (!empty($terms_vintages)) {
                                                    $terms = explode(', ', $terms_vintages);
                                            
                                                    foreach ($terms as $term) {
                                                        echo '
                                                            <option value="' . esc_attr($term) . '">
                                                                ' . esc_html($term) . '
                                                            </option>
                                                        ';
                                                    }
                                                }
                                            
                                            
                                                ?>
                                            </select>

                                        </div>
                                    </div>
                                    <div class="form-control">
                                        <label for="fname" required style="display: inline-block;">Quantity<span class="required">*</span></label>
                                        <div class="custom-select-wrapper"> <!-- Add a custom select wrapper -->
                                            <select name="vendor_quantity[]" class="custom-select">
                                                <!-- Assign a class for custom select styling -->
                                                <?php
                                                $attribute_quantity = 'pa_quantity'; // Replace with your actual attribute slug
                                                $terms_quantity = get_terms(
                                                    array(
                                                        'taxonomy' => $attribute_quantity,
                                                        'hide_empty' => false,
                                                    )
                                                );
                                                foreach ($terms_quantity as $term_quantity) {
                                                    echo '
                                                    <option value="' . esc_attr($term_quantity->name) . '">
                                                    ' . esc_html($term_quantity->name) . '
                                                    </option>
                                                    ';
                                                }
                                                
                                                ?>
                                            </select>

                                        </div>
                                    </div>
                                    <div class="form-control">
                                        <label for="fname" required style="display: inline-block;">Format<span class="required">*</span></label>
                                        <div class="custom-select-wrapper"> <!-- Add a custom select wrapper -->
                                            <select name="vendor_format[]" class="custom-select">
                                                <!-- Assign a class for custom select styling -->
                                                <?php
                                                $attribute_format = 'pa_format'; // Replace with your actual attribute slug
                                              
                                                $terms_format = $product_price->get_attribute($attribute_format);
                                            
                                                if (!empty($terms_format)) {
                                                    $terms = explode(', ', $terms_format);
                                            
                                                    foreach ($terms as $term) {
                                                        echo '
                                                            <option value="' . esc_attr($term) . '">
                                                                ' . esc_html($term) . '
                                                            </option>
                                                        ';
                                                    }
                                                }
                                            
                                                ?>
                                            </select>

                                        </div>
                                    </div>
                                    <div class="form-control">
                                        <label for="fname" required style="display: inline-block;">Warehouse<span class="required">*</span></label>
                                        <div class="custom-select-wrapper"> <!-- Add a custom select wrapper -->
                                            <select name="vendor_location[]" class="custom-select">
                                                <!-- Assign a class for custom select styling -->
                                                <?php
                                                $attribute_name = 'pa_location'; // Replace with your actual attribute slug
                                                $terms = get_terms(
                                                    array(
                                                        'taxonomy' => $attribute_name,
                                                        'hide_empty' => false,
                                                    )
                                                );
                                                foreach ($terms as $term) {
                                                    echo '
                                                    <option value="' . esc_attr($term->name) . '">
                                                    ' . esc_html($term->name) . '
                                                    </option>
                                                    ';
                                                }
                                                ?>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="form-control">
                                        <label for="purchase" required style="display: inline-block;">Purchase price</label>
                                        <div style="position: relative;">
                                            <input type="number" name="vendor_purchase[]" placeholder="" max="1000" min="1" />
                                            <div class="tooltip-icon">i
                                                <div class="tooltip-box">
                                                    <span class="tooltip-text">Please provide the price at which you made the purchase or the total amount paid.</span>
                                                </div>
                                            </div>
                                        </div>

                                    </div>

                                </div>

                        </div>
                    </div>

                    <div class="column">
                       
                        <div id="price-variations" style="width: 89%;"></div>

                        <script>
                            (function($) {
                                $(document).ready(function() {

                                    $.ajax({
                                        url: '<?php echo admin_url('admin-ajax.php'); ?>',
                                        method: 'POST',
                                        data: {
                                            action: 'get_market_lowest_price_shortcode', // Custom AJAX action
                                            product_id: <?php echo $product_price->get_id(); ?>, // Pass any necessary data
                                        },
                                        success: function(response) {
                                            const  defaultHtml = response ; // Add the shortcode output to the HTML
                                            $('#price-variations').html(defaultHtml); // Update the element with the modified HTML
                                        },
                                        error: function() {
                                            // Handle any error that occurs during the AJAX request
                                            console.log('Error occurred during AJAX request');
                                        }
                                    });
                                
                                });
                            })(jQuery);
                        </script>
                        <table>

                            <tr>
                                <th>Robert Parker:</th>
                                <td>98/100</td>
                            </tr>
                            <tr>
                                <th>Neil Martin:</th>
                                <td>96/100</td>
                            </tr>
                            <tr>
                                <th>Lisa Perotti:</th>
                                <td>97/100</td>
                            </tr>
                        </table>
                        <div class="vendor-submit-button">
                            <input type="submit" name="submit_vendor_form" value="LIST WINE" />

                        </div>
                        </form>
                  
                    <?php
                    }
                    
                    ?>
  </div>
                </div>
                </div>
                <div class="form-product-description">
                    <h3> Description </h3>
                    <?php echo $post_content; ?>
                    <?php

if ($product_price) {
    // Get the product variations
    $variations = $product_price->get_available_variations();
    
    // Check if there are variations
    if (!empty($variations)) {
        // Extract the unique "pa_vintage" values
        $vintages = array();
        
        foreach ($variations as $variation) {
            $attributes = $variation['attributes'];
            if (isset($attributes['attribute_pa_vintage'])) {
                $vintages[] = $attributes['attribute_pa_vintage'];
            }
        }
        
        $vintages = array_unique($vintages);
        
        $buttonCount = count($vintages);

        // Output the vintage buttons
        echo '    <div class="vintage-filter-container">        <div id="vintage-buttons" class="' . ($buttonCount > 5 ? 'main-filter-form vintage-filter-form' : 'vintage-filter-form') . '">';
        echo '<button class="vintage-button" onclick="updateAttributes(\'\')">All</button>';
        
        foreach ($vintages as $vintage) {
            echo '<button class="vintage-button" onclick="updateAttributes(\'' . esc_attr($vintage) . '\')">' . esc_html($vintage) . '</button>';
        }
        
        echo '</div></div>';
        
        // Output the default attributes and title
        echo '<ul id="attributes-container" class="attributes-add-form">';
        $defaultVariation = $variations[0];
        $defaultAttributes = $defaultVariation['attributes'];
        
        foreach ($defaultAttributes as $attribute_name => $attribute_value) {
            if (strpos($attribute_name, 'attribute_') === 0) {
                echo '<li class="attribute-add-form-item"><b>' . str_replace('attribute_', '', $attribute_name) . ': </b>' . esc_html($attribute_value) . '</li>';
            }
        }
        
        echo '</ul>';
        
        // Output the JavaScript function to update attributes
        echo '<script>
            var originalTitle = document.getElementById("add-product-title").innerHTML;
            var vintageNode = null;
            
            function updateAttributes(selectedVintage) {
                var attributesContainer = document.getElementById("attributes-container");
                var title = document.getElementById("add-product-title");
        
                // Clear previous attributes
                attributesContainer.innerHTML = "";
        
                // Remove the previously added vintage text node if it exists
                if (vintageNode) {
                    title.removeChild(vintageNode);
                    vintageNode = null;
                }
        
                // Filter variations by selected vintage
                var filteredVariations = ' . json_encode($variations) . '.filter(function(variation) {
                    return variation.attributes.attribute_pa_vintage === selectedVintage;
                });
        
                // Output the attributes for the selected vintage or all variations
                if (filteredVariations.length > 0) {
                    var variationAttributes = filteredVariations[0].attributes;
        
                    for (var attributeName in variationAttributes) {
                        var attributeValue = variationAttributes[attributeName];
                        var attributeNameWithoutPrefix = attributeName.replace("attribute_", "");
        
                        var listItem = document.createElement("li");
                        listItem.className = "attribute-add-form-item";
                        listItem.innerHTML = "<b>" + attributeNameWithoutPrefix + ": </b>" + attributeValue;
        
                        attributesContainer.appendChild(listItem);
                    }
        
                    // Update the title by appending the selected vintage to the original title
                    if (selectedVintage) {
                        vintageNode = document.createTextNode(" - " + selectedVintage);
                        title.appendChild(vintageNode);
                    }
                }
            }
        </script>';
    }
}

                    
                    
                    ?>

                </div>
            </div>

            <script>
                function toggleTooltip() {
                    var tooltipBox = document.querySelector('.tooltip-box');
                    if (tooltipBox.style.display === 'none') {
                        tooltipBox.style.display = 'block';
                    } else {
                        tooltipBox.style.display = 'none';
                    }
                }
            </script>
        <?php
            return ob_get_clean();
        }
        // Example error message:
        $error_message = 'You do not have permission to access this page.';
        return '<p>' . esc_html($error_message) . '</p>';
    } else {
        // User is not logged in, show a popup with a login form
        ob_start();
        ?>

        <div id="vendor-form-container">
            <div id="vendor-form-blur">


            </div>
            <div id="vendor-form-content">
                <h2>Sign in to start selling</h2>
                <!-- <p>Please log in to access the form.</p> -->
                <?php wp_login_form(); ?>
            </div>
        </div>

        <script>
            jQuery(document).ready(function($) {
                // Show the form with blur effect
                $('#vendor-form-container').addClass('show');

                // Close the form when clicking outside the content area
                $('#vendor-form-container').on('click', function(e) {
                    if (!$(e.target).closest('#vendor-form-content').length) {
                        $(this).removeClass('show');
                    }
                });
            });
        </script>
    <?php
        return ob_get_clean();
    }
}

?>