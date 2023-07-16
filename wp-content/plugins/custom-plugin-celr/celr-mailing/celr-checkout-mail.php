<?php
function send_custom_email($order_id)
{
    $order = wc_get_order($order_id);
    $vendor_name = '';
    $user_email = '';
    $custom_name = '';
    $address = '';
    $vendor_names = '';
    $vendor_price = '';
    $vendor_format = '';
    $warehouse_location = '';
    $quantity = '';
    $product_name = '';
    $lwin = '';
    $order_number = $order->get_order_number();

    foreach ($order->get_items() as $item) {
        $vendor_name = $item->get_meta('Vendor Name');
        if ($vendor_name) {
            break;
        }
    }

    if ($vendor_name) {
        $users = get_users(array('search' => $vendor_name));
        if (!empty($users)) {
            $user = $users[0];
            $user_email = $user->user_email;
        }

        // Get additional order information
        $custom_name = $order->get_billing_first_name();
        $address = $order->get_formatted_billing_address();

        // Get vendor information
        $vendor_names = $item->get_meta('Vendor Name');
        $vendor_price = $item->get_meta('Vendor Price');
        $transfer_fee = $item->get_meta('Transfer Fee');
        $vendor_format = $item->get_meta('Product Format');
        $warehouse_location = $item->get_meta('Warehouse Location');
        $quantity = $item->get_quantity();
        $product_name = $item->get_name();
        $lwin = $item->get_meta('lwin');
        $admin_email = get_option('admin_email'); // Get the default admin email from WordPress settings


        $message = "Dear <strong>$custom_name</strong>,<br><br>" .
        "You have received your order with No: <strong>$order_number</strong> for the producer: <strong>$product_name</strong> listed on our website (celr.co.uk)." .
        "We kindly request that you send the product to the client <strong>$warehouse_location</strong> Warehouse Location for delivery" .
        "The price of the product is <strong>$vendor_price</strong>, as indicated during the listing process" .
        " and format of the product is <strong>$vendor_format</strong>, and the quantity requested is <strong>$quantity</strong> case(s) with the LWIN <strong>$lwin</strong>.<br><br>" .
        "Best regards,<br>" .
        "<strong>Celr.co.uk</strong>";
       

        $headers = 'From: ' . get_bloginfo('name') . ' <' . $admin_email . '>' . "\r\n";
        $headers .= 'Reply-To: ' . $admin_email . "\r\n";
        $headers .= 'Content-Type: text/html; charset=UTF-8';

        $subject = 'Congratulation you received an Order From '. $custom_name;
        // Send the email
        wp_mail($user_email, $subject, $message, $headers);

        // Display the alert with the recipient email and additional information
        echo "<script>alert('Thanks you !! You will be notified once the Process is start');</script>";
    }
}


add_action('woocommerce_thankyou', 'send_custom_email');

?>