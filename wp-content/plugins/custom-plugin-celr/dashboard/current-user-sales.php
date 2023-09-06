<?php

function get_orders_by_vendor_name($atts)
{
		
	
    $current_user = wp_get_current_user();
	if ($current_user) {

    $vendor_name = $current_user->user_login;
    $user_name = $current_user->user_login;

    $status_mapping = array(
        'processing' => array(
            'post_status' => 'processing',
            'status_label' => 'Transfer pending',
            'show_mark_complete_button' => true,
            'show_mark_processing_button' => true,
        ),
        'completed' => array(
            'post_status' => 'completed',
            'status_label' => 'Completed Orders',
            'show_mark_complete_button' => false,
            'show_mark_processing_button' => false,
        ),
        'on-hold' => array(
            'post_status' => 'on-hold',
            'status_label' => 'On Hold Orders',
            'show_mark_complete_button' => false,
            'show_mark_processing_button' => true,
        ),
    );

    $default_status = 'processing';
    $default_status_mapping = $status_mapping[$default_status];
    $limit = isset($atts['limit']) ? intval($atts['limit']) : -1;
    $args['posts_per_page'] = $limit;

    $post_status = isset($_GET['status']) ? $_GET['status'] : $default_status;
    $status_config = isset($status_mapping[$post_status]) ? $status_mapping[$post_status] : $default_status_mapping;

    extract($status_config);

    $args = array(
        'post_type'      => 'shop_order',
        'post_status'    => $post_status,
        'posts_per_page' => $limit, // Assign the limit value here
        'meta_query'     => array(
            array(
                'key'   => 'Vendor Name',
                'value' => $vendor_name,
            ),
        ),
    );

    if (isset($_GET['status'])) {
        $_SESSION['active_button'] = $_GET['status'];
    }

    $output = '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="get" class="vendor-sales">';
    $output .= '<div class="vendor-sales-btn"><input type="hidden" name="page_id" value="' . get_the_ID() . '" />';
    foreach ($status_mapping as $status_key => $status_data) {
        $active_class = isset($_SESSION['active_button']) && $_SESSION['active_button'] === $status_key ? ' active' : '';
        $output .= '<button class="order-button' . $active_class . '" type="submit" name="status" value="' . $status_key . '">' . $status_data['status_label'] . '</button>';
    }
    $output .= '</div></form>';

    $output .= '<script>
    document.addEventListener("DOMContentLoaded", function() {
        var buttons = document.getElementsByClassName("order-button");
        for (var i = 0; i < buttons.length; i++) {
            buttons[i].addEventListener("click", function() {
                var current = document.getElementsByClassName("active");
                if (current.length > 0) {
                    current[0].className = current[0].className.replace(" active", "");
                }
                this.classList.add("active");
            });
        }

        // Add "active" class to the initially selected button
        var selectedButton = document.querySelector(".order-button[value='.$post_status.']");
        if (selectedButton) {
            selectedButton.classList.add("active");
        }
    });
</script>';



    $order_query = new WC_Order_Query($args);
    $orders = $order_query->get_orders();
    if ($limit !== -1) {
        $orders = array_slice($orders, 0, $limit);

    }

    if ($orders > 0) {
        $output .= '<div class="vendor-sales">';
        foreach ($orders as $order) {
            $order_object = new Automattic\WooCommerce\Admin\Overrides\Order($order->get_id());
            $status = $order_object->get_status();
            $buyer_first_name =  $order_object->get_billing_first_name();
            $buyer_last_name =  $order_object->get_billing_last_name();
            $buyer_address =  $order_object->get_billing_address_1();
            $buyer_address2 =  $order_object->get_billing_address_2();
            $order_date =  $order_object->get_date_created();
            $order_date_without_time = date('Y-m-d', strtotime($order_date));
            $deadline_date = date('Y-m-d', strtotime('+7 days', strtotime($order_date)));

            if ($status === $post_status){
                $order_id = $order->get_id();
                $order_title = $order->get_title();
                $buyer_id = $order->get_user_id();

                $items = $order->get_items();
                foreach ($items as $item_id => $item) {
                    $product = $item->get_product();
                    $vendor_names = $item->get_meta('Vendor Name');
                    $vendor_price = $item->get_meta('Vendor Price');
                    $transfer_fee = $item->get_meta('Transfer Fee');
                    $vendor_format = $item->get_meta('Product Format');
                    $warehouse_location = $item->get_meta('Warehouse Location');

                    if ($vendor_names === $vendor_name) {

                        if($order){
                        $sku = $product ? $product->get_sku() : '';

                        $output .= '<div class="sales-items"><div class="vendor-sales-left">';
                        $output .= '<h4>Producer:</h4><span> ' . $item->get_name() . '</span>';
                        $output .= '<h4>Price sold at: </h4><span>£ ' . $vendor_price . '</span>';
                        $output .= '<h4>Format: </h4><span>' . $vendor_format . '</span>';
                        $output .= '<h4>Quantity: </h4><span>' . $item->get_quantity() . ' case(s) </span>';
                        $output .= '<h4>LWIN: </h4><span>' . $sku . '</span>';
                        $output .= '<h4>Buyer Account #:</h4><span> ' . $buyer_id . '</span>';
                        $output .= '<h4>Buyer warehouse:</h4><span> ' . $warehouse_location . '</span>';
                        // $output .= '<h4>Order Id: </h4><span>' . $order_id . '</span>';

                        $output .= '</div>';
                        $output .= '<div class="sales-page-btns">';


                        if ($show_mark_complete_button && $status !== 'completed' && $status !== 'on-hold') {
                            $output .= '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
                            $output .= '<input type="hidden" name="order_id" value="' . $order_id . '" />';
                            $output .= '<button class="order-button active"  type="submit" name="mark_complete" value="true">Mark as Complete</button>';
                            $output .= '</form>';
                        }

                        if ($show_mark_processing_button && $status !== 'processing') {
                            $output .= '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
                            $output .= '<input type="hidden" name="order_id" value="' . $order_id . '" />';
                            $output .= '<button class="order-button active" type="submit" name="mark_processing" value="true">Mark as Processing</button>';
                            $output .= '</form>';
                        }
                        $output .= '<button class="show-details-button order-button active" type="button" data-overlay-id="overlay-' . $order_id . '">View buyer details &nbsp;&nbsp;&nbsp;<i aria-hidden="true" class="fas fa-arrow-right" style="width: 20px;     transform: scaleX(1.6);"></i>			</button>';


                        $output .= '</div></div>';
                        // Hidden div to store remaining details
                        $output .= '<div id="overlay-' . $order_id . '" class="remaining-details-overlay" style="display: none;">';
                        $output .= '<div class="remaining-details">';
                        $output .= '<button class="close-button" type="button">&times;</button>'; // Close button
                        $output .= '<div class="offer-left">';
                        $output .= '<h4>Buyer Name: </h4><span>' . $buyer_first_name . ' ' . $buyer_last_name . '</span>';
                        $output .= '<h4>Shipping Paid: </h4><span>' . $transfer_fee . '</span>';
                        $output .= '<h4>Total cases: </h4><span>' .  $item->get_quantity()  . '</span>';
                        $output .= '<h4>Delivery deadline:</h4><span>' . $deadline_date . '</span>';
                        $output .= '<h4>Days to forfeit:</h4><span>7 days</span>';
                        $output .= '<h4>Buyer Account #:</h4><span> ' . $buyer_id . '</span>';
                        $output .= '<h4>Buyer warehouse:</h4><span> ' . $warehouse_location . '</span>';

                        $output .= '<h4>Buyer Address: </h4><span>' . $buyer_address . ' ' . $buyer_address2 . '</span>';
                        $output .= '<h4>Order Date:</h4><span>' . $order_date_without_time . '</span>';
                        $output .= '</div>';

                        if ($show_mark_complete_button && $status !== 'completed' && $status !== 'on-hold') {
                            $output .= '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
                            $output .= '<input type="hidden" name="order_id" value="' . $order_id . '" />';
                            $output .= '<button class="order-button active"  type="submit" name="mark_complete" value="true">Mark as Complete</button>';
                            $output .= '</form>';
                        }

                        if ($show_mark_processing_button && $status !== 'processing') {
                            $output .= '<form action="' . esc_url($_SERVER['REQUEST_URI']) . '" method="post">';
                            $output .= '<input type="hidden" name="order_id" value="' . $order_id . '" />';
                            $output .= '<button class="order-button active" type="submit" name="mark_processing" value="true">Mark as Processing</button>';
                            $output .= '</form>';
                        }
                        $output .= '<span style="color:red">Got a problem? Contact us.</span>';
                        $output .= '</div></div>';
                        }
                    }

                }
            }
        }
        $output .= '</div>';
    } else {
        $output .= 'No Sales Found';
    }

    if (isset($_POST['mark_complete']) && $_POST['mark_complete'] === 'true' && isset($_POST['order_id'])) {
        $order_id = $_POST['order_id'];
        $order = wc_get_order($order_id);
        if ($order) {
            $order->update_status('completed');
            $output .= '<p>Order marked as complete.</p>';
        } else {
            $output .= '<p>Failed to mark order as complete.</p>';
        }
    }

    if (isset($_POST['mark_processing']) && $_POST['mark_processing'] === 'true' && isset($_POST['order_id'])) {
        $order_id = $_POST['order_id'];
        $order = wc_get_order($order_id);
        if ($order) {
            $order->update_status('processing');
            $output .= '<p>Order marked as processing.</p>';
        } else {
            $output .= '<p>Failed to mark order as processing.</p>';
        }
    }

    return $output;
	}else{
		$redirect_script = '
            <script>
                window.location.href = "' . esc_url(home_url('/login')) . '";
            </script>
        ';
     return $redirect_script; 
	}
}
add_shortcode('vendor_sales', 'get_orders_by_vendor_name');
?>