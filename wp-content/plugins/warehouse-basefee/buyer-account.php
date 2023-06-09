<?php



// Auto-fill checkout fields with user data or create an account
function custom_auth_checkout_autofill($fields)
{
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        $fields['billing']['billing_first_name']['default'] = $current_user->user_firstname;
        $fields['billing']['billing_last_name']['default'] = $current_user->user_lastname;
        $fields['billing']['billing_email']['default'] = $current_user->user_email;
        $fields['billing']['billing_phone']['default'] = get_user_meta($current_user->ID, 'billing_phone', true);
        $fields['billing']['billing_address_1']['default'] = get_user_meta($current_user->ID, 'billing_address_1', true);
        // Add other default fields here

        // Retrieve and set additional user meta fields
        $fields['billing']['user_location'] = array(
            'type' => 'text',
            'label' => __('Location', 'woocommerce'),
            'required' => false,
            'class' => array('form-row-wide'),
            'priority' => 40,
            'default' => get_user_meta($current_user->ID, 'user_location', true)
        );
    } else {
        $fields['account']['account_username'] = array(
            'type' => 'text',
            'label' => __('Username', 'woocommerce'),
            'required' => true,
            'class' => array('form-row-wide'),
            'priority' => 10,
        );
        $fields['account']['account_password'] = array(
            'type' => 'password',
            'label' => __('Password', 'woocommerce'),
            'required' => true,
            'class' => array('form-row-wide'),
            'priority' => 20,
        );
        $fields['account']['account_password-2'] = array(
            'type' => 'password',
            'label' => __('Confirm Password', 'woocommerce'),
            'required' => true,
            'class' => array('form-row-wide'),
            'priority' => 30,
        );

        // Add default fields here
        $fields['billing']['billing_first_name'] = array(
            'type' => 'text',
            'label' => __('First Name', 'woocommerce'),
            'required' => true,
            'class' => array('form-row-first'),
            'priority' => 40,
        );
        $fields['billing']['billing_last_name'] = array(
            'type' => 'text',
            'label' => __('Last Name', 'woocommerce'),
            'required' => true,
            'class' => array('form-row-last'),
            'priority' => 50,
        );
        $fields['billing']['billing_email'] = array(
            'type' => 'email',
            'label' => __('Email', 'woocommerce'),
            'required' => true,
            'class' => array('form-row-wide'),
            'priority' => 60,
        );
        $fields['billing']['billing_phone'] = array(
            'type' => 'tel',
            'label' => __('Phone', 'woocommerce'),
            'required' => false,
            'class' => array('form-row-wide'),
            'priority' => 70,
        );
        $fields['billing']['billing_address_1'] = array(
            'type' => 'text',
            'label' => __('Address', 'woocommerce'),
            'required' => true,
            'class' => array('form-row-wide'),
            'priority' => 80,
        );
        // Add other default fields here
    }
    return $fields;
}
add_filter('woocommerce_checkout_fields', 'custom_auth_checkout_autofill');



// Create a custom page for profile editing
function custom_auth_profile_edit_page()
{
    ob_start();

     // Fetch current user data
     $current_user = wp_get_current_user();
     $user_email = $current_user->user_email;
     $first_name = $current_user->first_name;
     $last_name = $current_user->last_name;
 
     $warehouse_location = get_user_meta($current_user->ID, 'warehouse_location', true);
     $account_number = get_user_meta($current_user->ID, 'account_number', true);
     $account_type = get_user_meta($current_user->ID, 'account_type', true);
     
     // Warehouse options
     $warehouse_options = get_warehouse_options();
    

    // Get checkout values
    $billing_country = WC()->checkout()->get_value('billing_country');
    $billing_address_1 = WC()->checkout()->get_value('billing_address_1');
    $billing_address_2 = WC()->checkout()->get_value('billing_address_2');
    $billing_city = WC()->checkout()->get_value('billing_city');
    $billing_state = WC()->checkout()->get_value('billing_state');
    $billing_postcode = WC()->checkout()->get_value('billing_postcode');


    // Handle form submission
    if (isset($_POST['submit'])) {
        $user_id = get_current_user_id();
        $user_data = array(
            'ID' => $user_id,
            'user_nicename' => sanitize_text_field($_POST['user_nicename']),
            // Add other fields as needed
        );

        wp_update_user($user_data);
        update_user_meta($user_id, 'first_name', sanitize_text_field($_POST['first_name']));
        update_user_meta($user_id, 'last_name', sanitize_text_field($_POST['last_name']));
        update_user_meta($user_id, 'warehouse_location', sanitize_text_field($_POST['warehouse']));
        update_user_meta($user_id, 'account_number', sanitize_text_field($_POST['account_number']));
        update_user_meta($user_id, 'account_type', sanitize_text_field($_POST['account_type']));
       
        // Fetch the updated user data
        $current_user = wp_get_current_user();
        $user_email = $current_user->user_email;
        $user_nicename = $current_user->user_nicename;
        $first_name = $current_user->first_name;
        $last_name = $current_user->last_name;
        $warehouse_location = get_user_meta($current_user->ID, 'warehouse_location', true);
        $account_number = get_user_meta($current_user->ID, 'account_number', true);
        $account_type = get_user_meta($current_user->ID, 'account_type', true);

        echo '<div class="notice notice-success"><p>Profile updated successfully!</p></div>';
    }

    // Handle email change
    if (isset($_POST['change_email'])) {
        echo '
        <form method="post">
            <label for="new_email">New Email:</label>
            <input type="email" name="new_email" id="new_email" value="' . esc_attr($user_email) . '" required>
            <button type="submit" name="update_email">Update Email</button>
        </form>';
        return ob_get_clean();
    }

    // Handle password change
    if (isset($_POST['change_password'])) {
        echo '
        <form method="post">
            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" id="new_password" required>
            <button type="submit" name="update_password">Update Password</button>
        </form>';
        return ob_get_clean();
    }
   
    
?>

<div class="buyer-account-details">
        <h2>Account details</h2>
        <p><strong>Current email address:</strong> <?php echo esc_html($user_email); ?></p>
        <button type="button" onclick="document.getElementById('change_email_form').style.display = 'block';">Change Email</button>
        <button type="button" onclick="document.getElementById('change_password_form').style.display = 'block';">Change Password</button>
    </div>

    <div id="change_email_form" style="display: none;">
        <form method="post">
            <label for="new_email">New Email:</label>
            <input type="email" name="new_email" id="new_email" value="<?php echo esc_attr($user_email); ?>" required>
            <button type="submit" name="update_email">Update Email</button>
        </form>
    </div>

    <div id="change_password_form" style="display: none;">
        <form method="post">
            <label for="new_password">New Password:</label>
            <input type="password" name="new_password" id="new_password" required>
            <button type="submit" name="update_password">Update Password</button>
        </form>
    </div>

    <form method="post" class="buyer-account-form">
        <div class="buyer-account-details">
            <h2>Your personal details</h2>
            <label for="title">Title:</label>
            <select name="title" id="title">
                <option value="Mr.">Mr.</option>
                <option value="Miss.">Miss.</option>
            </select>

            <label for="first_name">First Name:</label>
            <input type="text" name="first_name" id="first_name" value="<?php echo esc_attr($first_name); ?>" required>

            <label for="last_name">Last Name:</label>
            <input type="text" name="last_name" id="last_name" value="<?php echo esc_attr($last_name); ?>" required>

            <label for="account_type">Type of Account:</label>
            <select name="account_type" id="account_type">
                <option value="Private Collector" <?php selected($account_type, 'Private Collector'); ?>>Private Collector</option>
                <option value="Govt Collector" <?php selected($account_type, 'Govt Collector'); ?>>Govt Collector</option>
            </select>
        </div>
        <div class="buyer-account-details">
            <h2>Transfer details</h2>
            <label for="warehouse">Warehouse:</label>
            <select name="warehouse" id="warehouse">
                <?php foreach ($warehouse_options as $term_id => $term_name) : ?>
                    <option value="<?php echo esc_attr($term_id); ?>" <?php selected($warehouse_location, $term_id); ?>><?php echo esc_html($term_name); ?></option>
                <?php endforeach; ?>
            </select>

            <label for="account_number">Account number:</label>
            <input type="text" name="account_number" id="account_number" value="<?php echo esc_attr($account_number); ?>" required>
        </div>
        <div class="buyer-account-details">
            <h2>Billing address</h2>
            <?php
            $billing_country = WC()->checkout->get_value('billing_country');
            woocommerce_form_field('billing_country', array(
                'type' => 'country',
                'label' => 'Country:',
                'required' => true,
                'default' => $billing_country,
            ));
            ?>

            <label for="billing_address_1">Address Line 1:</label>
            <input type="text" name="billing_address_1" id="billing_address_1" value="<?php echo esc_attr($billing_address_1); ?>" required>

            <label for="billing_address_2">Address Line 2:</label>
            <input type="text" name="billing_address_2" id="billing_address_2" value="<?php echo esc_attr($billing_address_2); ?>">

            <label for="billing_city">City:</label>
            <input type="text" name="billing_city" id="billing_city" value="<?php echo esc_attr($billing_city); ?>" required>

            <label for="billing_state">State:</label>
            <input type="text" name="billing_state" id="billing_state" value="<?php echo esc_attr($billing_state); ?>" required>

            <label for="billing_postcode">Postal Code:</label>
            <input type="text" name="billing_postcode" id="billing_postcode" value="<?php echo esc_attr($billing_postcode); ?>" required>
        </div>
        <button type="submit" name="submit">Save Changes</button>
    </form>

<?php
    return ob_get_clean();
}
function get_warehouse_options()
{
    $options = array();

    // Get the "Warehouse" attribute
    $taxonomy = 'pa_location';
    $attribute = get_taxonomy($taxonomy);

    if ($attribute) {
        $terms = get_terms(array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        ));

        foreach ($terms as $term) {
            $options[$term->term_id] = $term->name;
        }
    }

    return $options;
}


// Create a shortcode to display the profile editing page
function custom_auth_profile_edit_shortcode()
{
    if (is_user_logged_in()) {
        return custom_auth_profile_edit_page();
    } else {
        return 'You must be logged in to edit your profile.';
    }
}
add_shortcode('custom_auth_profile_edit', 'custom_auth_profile_edit_shortcode');

// Create an account for the user during checkout
function custom_auth_create_account($order_id)
{
    $order = wc_get_order($order_id);
    $user_id = $order->get_user_id();

    if ($user_id !== 0) {
        // User already exists, update their profile
        $user_data = array(
            'ID' => $user_id,
            'user_email' => $order->get_billing_email(),
            // Add other fields as needed
        );
        wp_update_user($user_data);

        return;
    }

    $username = sanitize_user($_POST['account_username']);
    $password = $_POST['account_password'];
    $email = sanitize_email($order->get_billing_email());
    $first_name = $order->get_billing_first_name();
    $last_name = $order->get_billing_last_name();

    // Create a new user account
    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        // Handle account creation error
        return;
    }

    // Set user data
    wp_update_user(
        array(
            'ID' => $user_id,
            'first_name' => $first_name,
            'last_name' => $last_name,
        )
    );

    // Update additional fields
    update_user_meta($user_id, 'user_location', $order->get_billing_city());

    // Assign the user to the order
    $order->set_customer_id($user_id);
    // $order->set_customer_email($email);
    $order->update_meta_data('_customer_user', $user_id);
    $order->save();

    // Log the user in after account creation
    wp_set_current_user($user_id);
    wp_set_auth_cookie($user_id, true);
    do_action('wp_login', $username);

    return;
}
add_action('woocommerce_checkout_order_processed', 'custom_auth_create_account');

function customer_warehouse_location_shortcode() {
    $current_user = wp_get_current_user();
    $warehouse_location = get_user_meta($current_user->ID, 'warehouse_location', true);
    $warehouse_options = get_warehouse_options();

    if (!empty($warehouse_location) && isset($warehouse_options[$warehouse_location])) {
        $location_name = $warehouse_options[$warehouse_location];
        return '<div class="change-my-location"><h4> My warehouse: <strong>' . $location_name .'</strong></h4> <a href="'. home_url('/buyer-account') . '">  Change this</a><div>';
    } else {
        return '<div class="change-my-location"><h4> Add Location to your Profile to Calculate Transfer Fee <strong> <a href=" '. home_url('/buyer-account') . '">Click Here</a></h4> <div>';

    }
}

add_shortcode('customer_warehouse_location', 'customer_warehouse_location_shortcode');



?>