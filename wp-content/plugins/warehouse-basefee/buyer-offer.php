<?php

// Render the popup content
function custom_popup_render_popup_content($vendor_name, $vendor_price) {
    $product_id = get_the_ID();

    ob_start();
    ?>
    <div id="popup-container" class="" style="display: none;">
        <div class="popup-content">
            <h2>Make an Offer</h2>
            <form id="offer-form" method="post">
                <?php wp_nonce_field(basename(__FILE__), 'vendor_meta_nonce'); ?>
                <input type="hidden" name="product_id" value="<?php echo esc_attr($product_id); ?>" />

                <div id="vendor-repeater">
                    <!-- Form fields and inputs go here -->
                    <!-- Replace the existing form HTML with your desired form structure -->

                    <!-- Example form fields -->
                    <div class="form-control">
                        <label for="price" required style="display: inline-block;">My offer price for <?php echo esc_html($vendor_price); ?> <?php echo esc_html($vendor_name); ?><span class="required">*</span></label>
                        <div style="position: relative;">
                            <input type="number" name="vendor_price[]" placeholder="" max="1000" min="1" required />
                            <span style="position: absolute; top: 45%; transform: translateY(-50%); left: 20px;">£</span>
                        </div>
                    </div>
                    <div class="form-control">
                        <label for="vintage" required style="display: inline-block;">Vintage for <?php echo esc_html($vendor_name); ?><span class="required">*</span></label>
                        <div class="custom-select-wrapper">
                            <!-- Add your custom select input for the vintage -->
                        </div>
                    </div>
                    <!-- Add more form fields as needed -->

                    <button type="submit">Submit Offer</button>
                </div>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}

function send_vendor_email() {
    // Check if the form has been submitted
    if (isset($_POST['vendor_meta_nonce']) && wp_verify_nonce($_POST['vendor_meta_nonce'], basename(__FILE__))) {

        // Get the form data
        $product_id = $_POST['product_id'];
        $vendor_names = $_POST['vendor_name'];
        $vendor_prices = $_POST['vendor_price'];
        $vendor_vintages = $_POST['vendor_vintage'];
        $vendor_quantities = $_POST['vendor_quantity'];
        $vendor_formats = $_POST['vendor_format'];
        $vendor_locations = $_POST['vendor_location'];
        $vendor_purchases = $_POST['vendor_purchase'];

        // Construct the email message
        $subject = 'New vendor offer for product ID: ' . $product_id;
        $message = '';

        for ($i = 0; $i < count($vendor_names); $i++) {
            $message .= "Vendor Name: " . $vendor_names[$i] . "\n";
            $message .= "Offer Price: " . $vendor_prices[$i] . "\n";
            $message .= "Vintage: " . $vendor_vintages[$i] . "\n";
            $message .= "Quantity: " . $vendor_quantities[$i] . "\n";
            $message .= "Format: " . $vendor_formats[$i] . "\n";
            $message .= "Warehouse: " . $vendor_locations[$i] . "\n";
            $message .= "Purchase Price: " . $vendor_purchases[$i] . "\n\n";
        }

        // Set the recipient email address
        $to = 'vendor@example.com'; // Replace with the actual vendor email address

        // Set additional headers
        $headers = array(
            'Content-Type: text/plain; charset=UTF-8',
            'From: Your Website <noreply@example.com>'
        );

        // Send the email
        $sent = wp_mail($to, $subject, $message, $headers);

        // Check if the email was sent successfully
        if ($sent) {
            // Email sent successfully, you can perform any additional actions here
            // For example, display a success message or redirect the user
            echo 'Email sent successfully!';
        } else {
            // Email failed to send, handle the error
            echo 'Failed to send email.';
        }
    }
}

// Hook the form submission function to the form's action
add_action('init', 'send_vendor_email');
