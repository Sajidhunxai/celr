<?php
/**
 * Generate a custom multi-step registration form.
 */
function custom_multi_step_registration_form() {
    ob_start();

    $current_user = wp_get_current_user();
    $user_email = $current_user->user_email;
    $first_name = $current_user->first_name;
    $last_name = $current_user->last_name;

    $warehouse_location = get_user_meta($current_user->ID, 'warehouse_location', true);
    $account_number = get_user_meta($current_user->ID, 'account_number', true);
    $account_type = get_user_meta($current_user->ID, 'account_type', true);

    $warehouse_options = get_warehouse_options();

    if (!is_user_logged_in()) {
        if (isset($_POST['submit'])) {
            $new_user_data = array(
                'user_login' => sanitize_text_field($_POST['username']),
                'user_email' => sanitize_email($_POST['user_email']),
                'user_pass' => sanitize_text_field($_POST['user_password']),
                'first_name' => sanitize_text_field($_POST['first_name']),
                'last_name' => sanitize_text_field($_POST['last_name']),
                'warehouse_location' => sanitize_text_field($_POST['warehouse']),
                'account_number' => sanitize_text_field($_POST['account_number']),
                'account_type' => sanitize_text_field($_POST['account_type']),
                'interests' => isset($_POST['interests']) ? $_POST['interests'] : array(),
				'role' => 'seller', 
			);

            $new_user_id = wp_insert_user($new_user_data);

            if (!is_wp_error($new_user_id)) {
                send_verification_email($new_user_id);
                echo '<script>
                    alert("New account created successfully! Please check your email to verify your account.");
                    window.location.href = "' . esc_url(home_url('/login')) . '";
                </script>';
                wp_redirect(home_url('/login'));
                exit;
            } else {
                echo '<div class="notice notice-error"><p>Error creating account. Please try again.</p></div>';
            }
        }
    } else {
        echo 'You are already logged in. You cannot create a new account.';
		wp_redirect(home_url('/login'));
    }


    ?>
	
    <div class="multi-step-registration">
        <form id="multi-step-form" method="post">
            <input type="hidden" name="action" value="custom_multi_step_registration">
            <?php wp_nonce_field('custom_multi_step_nonce', 'custom_multi_step_nonce'); ?>
			
            <div class="step" id="step1">
                <h3>
				Create your account
				</h3>
				<div class="error-message" id="step1-error"></div>

                <label for="user_email">Email:</label>
                <input type="email" name="user_email" id="user_email" >
                
                <label for="confirm_user_email">Confirm Email:</label>
                <input type="email" name="confirm_user_email" id="confirm_user_email" >
                
                <label for="user_password">Password:</label>
                <input type="password" name="user_password" id="user_password" >
                
                <label for="confirm_user_password">Confirm Password:</label>
                <input type="password" name="confirm_user_password" id="confirm_user_password" >
                
                <input type="checkbox" name="agreement" id="agree_to_terms" >
                    I agree to the Celr <a href="<?php echo home_url('/terms-and-condition') ?>">terms and conditions</a>* <br>
                <input type="checkbox" name="keep_me_informed" id="keep_me_informed" checked>
                    Keep me informed with market updates, offers, and fine wine insights

            </div>
            
            <div class="step" id="step2" style="display:none;">
                <h3>
					My details
				</h3>
				<div class="error-message" id="step2-error"></div>
       
                <label for="title">Title:</label>
                <select style="width:50%;" name="title" id="title">
                    <option value="Mr.">Mr.</option>
                    <option value="Miss.">Miss.</option>
                </select>
               <div style="display:flex; gap: 10px;">
                <div style="width:50%;">
                <label for="first_name">First Name:</label>
                <input  type="text" name="first_name" id="first_name" >
                </div>
                <div style="width:50%;">
                <label for="last_name">Last Name:</label>
                <input type="text" name="last_name" id="last_name" >
                </div>
               </div>
                <label for="username">Username:</label>
                <input type="text" name="username" id="username" >
               
                <label for="account_type">Type of Account:</label>
                <select name="account_type" id="account_type" >
                    <option value="Private Collector" <?php selected($account_type, 'Private Collector'); ?>>Private Collector</option>
                    <option value="Govt Collector" <?php selected($account_type, 'Govt Collector'); ?>>Govt Collector</option>
                </select>
            
                <div>
                <Label for="interests">I am interested in:</Label>
                    <input type="checkbox" id="buying" name="buying" value="buying" checked>
                    <label for="buying" style="display: contents;"> Buying</label><br>
                    <input type="checkbox" id="selling" name="selling" value="selling" checked>
                    <label for="selling" style="display: contents;"> Selling</label><br>
                    <input type="checkbox" id="marketInsights" name="marketInsights" value="marketInsights" checked>
                    <label for="marketInsights" style="display: contents;">  Market Insights</label>
                </div>
            
            </div>
            <div class="step" id="step3" style="display:none;">
				 <h3>
					Warehouse details
				</h3>
								<div class="error-message" id="step2-error"></div>

                <label for="warehouse">Warehouse/Merchant:</label>
                <select name="warehouse" id="warehouse">
                    <?php foreach ($warehouse_options as $term_id => $term_name) : ?>
                        <option value="<?php echo esc_attr($term_id); ?>" <?php selected($warehouse_location, $term_id); ?>><?php echo esc_html($term_name); ?></option>
                    <?php endforeach; ?>
                </select>
              
                <label for="no-warehouse-account"> I don’t have a merchant or personal warehouse account                     
                   <input type="checkbox" id="no-warehouse-account" name="no-warehouse-account" value="no-warehouse-account">
                </label>
                <label for="account_number">Bonded warehouse account number <span style="float:right;"><a href=""></a>What is this?</span></label>
                <input type="text" name="account_number" id="account_number" />
            </div>

            <div class="step-navigation">
                <button type="button" class="next-step">Continue</button> 
                <button type="submit" name="submit" class="submit-step" style="display:none;">Create Account</button>

            </div>
        </form>
    </div>

    <?php
    return ob_get_clean();
}



function custom_auth_create_account_shortcode() {
    if (!is_user_logged_in()) {
        return custom_multi_step_registration_form();
    } else {
	 $redirect_script = '
            <script>
                window.location.href = "' . esc_url(home_url('/login')) . '";
            </script>
        ';
     return $redirect_script;

    }
}
add_shortcode('custom_auth_create_account', 'custom_auth_create_account_shortcode');





function display_custom_user_fields($user) {
	$account_type = get_user_meta($user->ID, 'account_type', true);
    $user_interests = get_user_meta($user->ID, 'interests', true);
  $warehouse_location = get_user_meta($user->ID, 'warehouse_location', true);

    $warehouse_options = get_warehouse_options();
    ?>
    <h3>Custom User Fields</h3>
    <table class="form-table">
        <tr>
            <th><label for="account_number">Account Number</label></th>
            <td>
                <input type="text" name="account_number" id="account_number" value="<?php echo esc_attr(get_user_meta($user->ID, 'account_number', true)); ?>" class="regular-text">
            </td>
        </tr>
        <tr>
            <th><label for="account_type">Account Type</label></th>
            <td>
				<select name="account_type" id="account_type">
				<option value="Private Collector" <?php selected($account_type, 'Private Collector'); ?>>Private Collector</option>
				<option value="Govt Collector" <?php selected($account_type, 'Govt Collector'); ?>>Govt Collector</option>
			</select>
            </td>
        </tr>
		  <tr>
             <th><label for="interests">I am interested in:</label></th>
            <td>
                <label><input type="checkbox" name="interests[]" value="buying" <?php echo in_array('buying', $user_interests) ? 'checked' : ''; ?>> Buying</label><br>
                <label><input type="checkbox" name="interests[]" value="selling" <?php echo in_array('selling', $user_interests) ? 'checked' : ''; ?>> Selling</label><br>
                <label><input type="checkbox" name="interests[]" value="marketInsights" <?php echo in_array('marketInsights', $user_interests) ? 'checked' : ''; ?>> Market Insights</label><br>
            </td>
        </tr>
		 <tr>
            <th><label for="warehouse">Warehouse/Merchant</label></th>
            <td>
                <select name="warehouse" id="warehouse">
                    <?php foreach ($warehouse_options as $term_id => $term_name) : ?>
                        <option value="<?php echo esc_attr($term_id); ?>" <?php selected($warehouse_location, $term_id); ?>><?php echo esc_html($term_name); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'display_custom_user_fields');
add_action('edit_user_profile', 'display_custom_user_fields');
function save_custom_user_fields($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return;
    }

    // Update account number
    if (isset($_POST['account_number'])) {
        $account_number = sanitize_text_field($_POST['account_number']);
        update_user_meta($user_id, 'account_number', $account_number);
    }

    // Update account type
    if (isset($_POST['account_type'])) {
        $account_type = sanitize_text_field($_POST['account_type']);
        update_user_meta($user_id, 'account_type', $account_type);
    }

    // Update interests
    if (isset($_POST['interests'])) {
        $interests = array_map('sanitize_text_field', $_POST['interests']);
        update_user_meta($user_id, 'interests', $interests);
    }
	//update warehouse
  if (isset($_POST['warehouse'])) {
        $warehouse_location = sanitize_text_field($_POST['warehouse']);
        update_user_meta($user_id, 'warehouse_location', $warehouse_location);
    }
}
add_action('personal_options_update', 'save_custom_user_fields');
add_action('edit_user_profile_update', 'save_custom_user_fields');

function send_verification_email($user_id) {
    $user = get_userdata($user_id);

    $subject = 'Please verify your email';
    
    $verification_link = add_query_arg(
        array('user_id' => $user_id),
        home_url('/my-dashboard/')
    );
    
$message = '
<!DOCTYPE html>
<html>
<head>

  <meta charset="utf-8">
  <meta http-equiv="x-ua-compatible" content="ie=edge">
  <title>Email Confirmation</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style type="text/css">
  @media screen {
    @font-face {
      font-style: normal;
      font-weight: 400;
    }
    @font-face {
      font-style: normal;
      font-weight: 700;

    }
  }
  body,
  table,
  td,
  a {
    -ms-text-size-adjust: 100%; /* 1 */
    -webkit-text-size-adjust: 100%; /* 2 */
  }
  /**
   * Remove extra space added to tables and cells in Outlook.
   */
  table,
  td {
    mso-table-rspace: 0pt;
    mso-table-lspace: 0pt;
  }
  /**
   * Better fluid images in Internet Explorer.
   */
  img {
    -ms-interpolation-mode: bicubic;
  }
 
  a[x-apple-data-detectors] {
    font-family: inherit !important;
    font-size: inherit !important;
    font-weight: inherit !important;
    line-height: inherit !important;
    color: inherit !important;
    text-decoration: none !important;
  }
  /**
   * Fix centering issues in Android 4.4.
   */
  div[style*="margin: 16px 0;"] {
    margin: 0 !important;
  }
  body {
    width: 100% !important;
    height: 100% !important;
    padding: 0 !important;
    margin: 0 !important;
  }
  /**
   * Collapse table borders to avoid space between cells.
   */
  table {
    border-collapse: collapse !important;
  }
  a {
    color: #1a82e2;
  }
  img {
    height: auto;
    line-height: 100%;
    text-decoration: none;
    border: 0;
    outline: none;
  }
  </style>

</head>
<body style="background-color: #e9ecef;">
  <div class="preheader" style="display: none; max-width: 0; max-height: 0; overflow: hidden; font-size: 1px; line-height: 1px; color: #fff; opacity: 0;">
    A preheader is the short summary text that follows the subject line when an email is viewed in the inbox.
  </div>

  <!-- start body -->
  <table border="0" cellpadding="0" cellspacing="0" width="100%">

    <!-- start logo -->
    <tr>
      <td align="center" bgcolor="#e9ecef">
        <!--[if (gte mso 9)|(IE)]>
        <table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
        <tr>
        <td align="center" valign="top" width="600">
        <![endif]-->
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
          <tr>
            <td align="center" valign="top" style="padding: 36px 24px;">
              <a href=" ' . esc_url(home_url('')) . '" target="_blank" style="display: inline-block;">
                <img src="' . esc_url(home_url('/wp-content/uploads/logo.png')) . ' " alt="Logo" border="0" width="48" style="display: block; width: 48px; max-width: 48px; min-width: 48px;">
              </a>
            </td>
          </tr>
        </table>
        <!--[if (gte mso 9)|(IE)]>
        </td>
        </tr>
        </table>
        <![endif]-->
      </td>
    </tr>
    <!-- end logo -->

    <!-- start hero -->
    <tr>
      <td align="center" bgcolor="#e9ecef">
        <!--[if (gte mso 9)|(IE)]>
        <table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
        <tr>
        <td align="center" valign="top" width="600">
        <![endif]-->
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">
          <tr>
            <td align="left" bgcolor="#ffffff" style="padding: 36px 24px 0; font-family: \'Source Sans Pro\', Helvetica, Arial, sans-serif; border-top: 3px solid #d4dadf;">
              <h1 style="margin: 0; font-size: 32px; font-weight: 700; letter-spacing: -1px; line-height: 48px;">Confirm Your Email Address</h1>
            </td>
          </tr>
        </table>
        <!--[if (gte mso 9)|(IE)]>
        </td>
        </tr>
        </table>
        <![endif]-->
      </td>
    </tr>
    <!-- end hero -->

    <!-- start copy block -->
    <tr>
      <td align="center" bgcolor="#e9ecef">
        <!--[if (gte mso 9)|(IE)]>
        <table align="center" border="0" cellpadding="0" cellspacing="0" width="600">
        <tr>
        <td align="center" valign="top" width="600">
        <![endif]-->
        <table border="0" cellpadding="0" cellspacing="0" width="100%" style="max-width: 600px;">

          <!-- start copy -->
          <tr>
            <td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: \'Source Sans Pro\', Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
              <p style="margin: 0;">Dear '. $user->display_name .' Tap the button below to confirm your email address. If you didn\'t create an account with <a href="#">Paste</a>, you can safely delete this email.</p>
            </td>
          </tr>
          <!-- end copy -->

          <!-- start button -->
          <tr>
            <td align="left" bgcolor="#ffffff">
              <table border="0" cellpadding="0" cellspacing="0" width="100%">
                <tr>
                  <td align="center" bgcolor="#ffffff" style="padding: 12px;">
                    <table border="0" cellpadding="0" cellspacing="0">
                      <tr>
                        <td align="center" bgcolor="#1a82e2" style="border-radius: 6px;">
                          <a href="'. $verification_link.'" target="_blank" style="display: inline-block; padding: 16px 36px; font-family: \'Source Sans Pro\', Helvetica, Arial, sans-serif; font-size: 16px; color: #ffffff; text-decoration: none; border-radius: 6px;">Verify Now</a>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>
            </td>
          </tr>
          <!-- end button -->

          <!-- start copy -->
          <tr>
            <td align="left" bgcolor="#ffffff" style="padding: 24px; , Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px;">
              <p style="margin: 0;">If that doesn\'t work, copy and paste the following link in your browser:</p>
              <p style="margin: 0;"><a href="https://blogdesire.com" target="_blank">'.$verification_link.'</a></p>
            </td>
          </tr>
          <!-- end copy -->

          <!-- start copy -->
          <tr>
            <td align="left" bgcolor="#ffffff" style="padding: 24px; font-family: \'Source Sans Pro\', Helvetica, Arial, sans-serif; font-size: 16px; line-height: 24px; border-bottom: 3px solid #d4dadf">
              <p style="margin: 0;">Cheers,<br>  ' . wp_title() . '</p>
            </td>
          </tr>
          <!-- end copy -->

        </table>
        <!--[if (gte mso 9)|(IE)]>
        </td>
        </tr>
        </table>
        <![endif]-->
      </td>
    </tr>
    <!-- end copy block -->

  </table>
  <!-- end body -->

</body>
</html>';
  
    $headers = array('Content-Type: text/html; charset=UTF-8');

    wp_mail($user->user_email, $subject, $message, $headers);
}


?>