<?php

// Render the popup content
function custom_popup_render_popup_content($vendor_info, $vendor_transfer_fee,)
{
    // Access the values from the $vendor_info array
    $vendor_name = $vendor_info['vendor_name'];
    $vendor_price = $vendor_info['vendor_price'];
    $vendor_vintage = $vendor_info['vendor_vintage'];
    $vendor_quantity = $vendor_info['vendor_quantity'];
    $vendor_location = $vendor_info['vendor_location'];
    $vendor_uname = $vendor_info['vendor_name'];

    // Create the content using the retrieved values

    $user = get_user_by('login', $vendor_uname); // Replace $vendor_username with the actual username


    // Get the product ID
    $product_id = get_the_ID();

    // Get the product thumbnail URL
    $thumbnail_url = get_the_post_thumbnail_url($product_id, 'thumbnail');

    // Get the plugin directory URI
    $plugin_dir_uri = plugin_dir_url(__FILE__);

    $product_title = get_the_title($product_id);





    ob_start();

?>
    <div id="overlay-offer" class="pum pum-overlay pum-theme-16391 pum-theme-lightbox popmake-overlay " style="opacity: 1; display: none;">
        <div id="popup-container" class="" style="display: none;">
            <h2>Make an Offer</h2>

            <div class="offer-product-info">
                <?php
                // Output the thumbnail
                if ($thumbnail_url) {
                    echo '<img src="' . esc_url($thumbnail_url) . '" alt="Product Thumbnail" />';
                } else {
                    // Output a placeholder image if no thumbnail is available
                    echo '<img src="' . esc_url($plugin_dir_uri . 'images/placeholder.png') . '" alt="Product Placeholder" />';
                }
                echo '<div class="offer-product-title"><h3>' . esc_html($product_title) . '</h3> <h4> ' . $vendor_vintage . '</h4></div>';
                // echo '';
                // echo $vendor_price;
                $content = "Vendor Name: $vendor_name, Price: $vendor_price, Vintage: $vendor_vintage, Quantity: $vendor_quantity, Location: $vendor_location, Transfer Fee: $vendor_transfer_fee";

                if ($user) {
                    $vendor_email = $user->user_email;
                    // Now $vendor_email contains the email address associated with the user
                } else {
                    // User not found
                    echo 'usernotfound';
                }
                // echo $content;
                ?>

            </div>
            <div class="popup-content" class="offer-form">
                <form id="offer-form" method="post">
                    <?php wp_nonce_field(basename(__FILE__), 'vendor_meta_nonce'); ?>
                    <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>" />
                    <input type="hidden" name="vendor_name" value="<?php echo esc_attr($vendor_uname); ?>" />

                    <div id="vendor-repeater">
                        <div class="form-control">
                            <label for="price" required style="display: inline-block;">My offer </label>
                            <div style="position: relative;">
                                <input type="number" name="offer_price[]" placeholder="" max="1000" min="1" required />
                                <span style="position: absolute; top: 45%; transform: translateY(-50%); left: 20px;">£</span>
                            </div>
                        </div>
                        <div class="form-control">
                            <label for="vintage" required style="display: inline-block;">Availability:</label>
                            <div class="custom-select-wrapper">
                                <span style="position: absolute; top: 45%; transform: translateY(-50%); left: 20px;"><?php echo $vendor_quantity; ?> case(s)</span>
                            </div>
                        </div>

                        <div class="form-control">
                            <label for="vintage" required style="display: inline-block;">Location:</label>
                            <div class="custom-select-wrapper">
                                <span style="position: absolute; top: 45%; transform: translateY(-50%); left: 20px;"><?php echo $vendor_location; ?></span>
                            </div>
                        </div>
                        <div class="form-control">
                            <label for="vintage" required style="display: inline-block;">Transfer fee:</label>
                            <div class="custom-select-wrapper">
                                <span style="position: absolute; top: 45%; transform: translateY(-50%); left: 20px;"><?php echo $vendor_transfer_fee; ?> </span>
                            </div>
                        </div>
                        <div class="form-control">
                            <label for="vintage" required style="display: inline-block;">Total:</label>
                            <div class="custom-select-wrapper">
                                <span style="position: absolute; top: 45%; transform: translateY(-50%); left: 20px;"><?php echo $vendor_price + $vendor_transfer_fee; ?> </span>
                            </div>
                        </div>
                    </div>
                    <div class="offer-form-right">
                        <table class="market price-box">
                            <tbody>
                                <tr>
                                    <th>Asking Price</th>
                                </tr>
                                <tr>
                                    <td><span class="woocommerce-Price-amount amount"><bdi><span class="woocommerce-Price-currencySymbol">$</span><?php echo $vendor_price ?></bdi></span></td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="form-control">
                            <?php
                            echo '<select name="vendor_quantity" class="quantity">'; // Add name attribute to select field
                            for ($j = 1; $j <= $vendor_quantity; $j++) {
                                echo '<option value="' . $j . '">' . $j . ' case</option>';
                            }
                            echo '</select>';
                            ?>

                        </div>
                        <button class="offer-submit-btn" type="submit">Make Offer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
<?php
    return ob_get_clean();
}
add_action('wp_ajax_process_offer_form', 'process_offer_form');

function process_offer_form()
{
    // Check if the form is submitted
    if (isset($_POST['product_id'])) {
        // Retrieve form data
        $product_id = intval($_POST['product_id']);
        $vendor_name = sanitize_text_field($_POST['vendor_name']);
        $offer_price = sanitize_text_field($_POST['offer_price'][0]); // Assuming you want to store only the first value in the array
        $vendor_quantity = sanitize_text_field($_POST['vendor_quantity']);
        $vendor_location = sanitize_text_field($_POST['vendor_location']);
        $vendor_transfer_fee = sanitize_text_field($_POST['vendor_transfer_fee']);

        $user = get_user_by('login', $vendor_name);

        if ($user) {
            $vendor_email = $user->user_email;
        }

        // Get the current user ID
        $user_id = get_current_user_id();

        // Validate the data (add your validation logic here)
        $errors = array();
        if (empty($offer_price)) {
            $errors[] = 'Offer price is required.';
        }

        // Add more validation rules if needed

        // If there are no validation errors, proceed to store the data and send the email
        if (empty($errors)) {
            // Insert data into the WordPress database
            global $wpdb;
            $table_name = $wpdb->prefix . 'offer_data'; // Replace 'offer_data' with your table name

            $insert_result = $wpdb->insert(
                $table_name,
                array(
                    'user_id' => $user_id,
                    'product_id' => $product_id,
                    'vendor_name' => $vendor_name,
                    'offer_price' => $offer_price,
                    'vendor_quantity' => $vendor_quantity,
                    'vendor_location' => $vendor_location,
                    'vendor_transfer_fee' => $vendor_transfer_fee
                ),
                array('%d', '%d', '%s', '%s', '%d', '%s', '%s')
            );

            // Check if insert was successful
            if ($insert_result === false) {
                error_log("Error inserting into DB: " . $wpdb->last_error);
                wp_send_json_error($wpdb->last_error);
                exit;
            }

            // Send email to the vendor
            $to = $vendor_email;
            $subject = 'New Offer Submitted';
            $message = 'Dear ' . $vendor_name . ',\n\nA new offer has been submitted for your product. Details are as follows:\n\nOffer Price: ' . $offer_price . '\nQuantity: ' . $vendor_quantity . '\nLocation: ' . $vendor_location . '\nTransfer Fee: ' . $vendor_transfer_fee . '\n\nThank you.';
            $headers = array('Content-Type: text/html; charset=UTF-8');

            wp_mail($to, $subject, $message, $headers);
            wp_send_json_success();

            // Redirect or display success message
            // For example, redirect to a thank-you page
            wp_redirect('thank-you-page');
            exit;
        } else {
            // Display validation errors to the user
            foreach ($errors as $error) {
                echo $error . '<br>';
            }
        }
    }
}

// Register REST API endpoint
add_action('admin_menu', 'myplugin_admin_menu');

function myplugin_admin_menu()
{
    add_menu_page('View Offers', 'View Offers', 'manage_options', 'view-offers', 'display_offer_page', 'dashicons-cart', 6);
}
function display_offer_page()
{
    global $wpdb;
    $table_name = $wpdb->prefix . 'offer_data';

    $results = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
    echo '<h2>Offer Data</h2>';
    if ($results) {
        echo '<table class="wp-list-table widefat fixed striped table-view-list posts">';
        echo '<thead>';
        echo '<tr>';
        echo '<th>Offer ID</th>';
        echo '<th>User ID</th>';
        echo '<th>Product ID</th>';
        echo '<th>Vendor Name</th>';
        echo '<th>Offer Price</th>';
        echo '<th>Vendor Quantity</th>';
        echo '<th>Vendor Location</th>';
        echo '<th>Vendor Transfer Fee</th>';
        echo '<th>Actions</th>'; // Add Actions column
        echo '</tr>';
        echo '</thead>';
    } else {
        echo "No offers Found";
    }
    foreach ($results as $row) {
        echo '<tr>';
        echo '<td>' . esc_html($row['id']) . '</td>';
        echo '<td>' . esc_html($row['user_id']) . '</td>';
        echo '<td>' . esc_html($row['product_id']) . '</td>';
        echo '<td>' . esc_html($row['vendor_name']) . '</td>';
        echo '<td>' . esc_html($row['offer_price']) . '</td>';
        echo '<td>' . esc_html($row['vendor_quantity']) . '</td>';
        echo '<td>' . esc_html($row['vendor_location']) . '</td>';
        echo '<td>' . esc_html($row['vendor_transfer_fee']) . '</td>';
        echo '<td>'; // Start Actions column
        echo '<button class="delete-button" data-id="' . esc_attr($row['id']) . '">Delete</button>';
        echo '</td>'; // End Actions column
        echo '</tr>';
    }

    echo '</table>';



    // AJAX script
?>
    <script>
        jQuery(document).ready(function($) {
            // Delete button click handler
            $(".delete-button").click(function() {
                var offerId = $(this).data("id");
                if (confirm("Are you sure you want to delete this offers?")) {
                    deleteOffer(offerId);
                }
            });


            // Function to delete a single offer
            function deleteOffer(offerId) {
                $.ajax({
                    type: "POST",
                    url: "<?php echo admin_url('admin-ajax.php'); ?>",
                    data: {
                        action: "delete_offer",
                        offer_id: offerId,
                        nonce: "<?php echo wp_create_nonce('offer_actions_nonce'); ?>"
                    },
                    success: function(response) {
                        // Handle success response
                        alert(response.data);
                        // You can reload the table or update the UI as needed
                        location.reload(); // Refresh the page

                    },
                    error: function(xhr, ajaxOptions, thrownError) {
                        // Handle error response
                        alert("Error deleting offer: " + thrownError);
                        location.reload(); // Refresh the page

                    }
                });
            }
        });
    </script>
<?php
}

// Add AJAX action hook for deleting an offer
add_action('wp_ajax_delete_offer', 'delete_offer_callback');
function delete_offer_callback()
{
    // Check the AJAX nonce for security
    check_ajax_referer('offer_actions_nonce', 'nonce');

    // Get the offer ID from the AJAX request
    $offer_id = isset($_POST['offer_id']) ? intval($_POST['offer_id']) : 0;

    // Perform the offer deletion logic here
    global $wpdb;
    $table_name = $wpdb->prefix . 'offer_data';

    // Delete the offer from the database
    $result = $wpdb->delete($table_name, array('id' => $offer_id));

    if ($result === false) {
        // Error occurred while deleting the offer
        wp_send_json_error('Error deleting offer.');
    } else {
        // Offer deleted successfully
        wp_send_json_success('Offer deleted successfully!');
    }
}
