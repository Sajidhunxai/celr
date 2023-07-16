<?php
function get_total_sales_by_vendor_name($atts)
{
    $current_user = wp_get_current_user();
    $vendor_name = $current_user->user_login;

    $args = array(
        'post_type'      => 'shop_order',
        'post_status'    => array('processing', 'completed', 'on-hold'),
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => 'Vendor Name',
                'value' => $vendor_name,
            ),
        ),
    );

    $order_query = new WC_Order_Query($args);
    $orders = $order_query->get_orders();

    $total_sales = 0;
    $total_vendor_price = 0;
    $result =0;

    foreach ($orders as $order) {
        $order_object = new Automattic\WooCommerce\Admin\Overrides\Order($order->get_id());

        $items = $order->get_items();
        foreach ($items as $item_id => $item) {
            $vendor_names = $item->get_meta('Vendor Name');
            $vendor_price = $item->get_meta('Vendor Price');

            $quantity = $item->get_quantity();
            // echo $quantity;
 
                if ($vendor_names === $vendor_name) {
                $total_sales += $vendor_price * $quantity;
                
                $result = wc_price($total_sales);
                }
        }
    }

    $output = '<table class="market price-box box-dashboard">
        <tbody>
            <tr>
                <th>SALES</th>
            </tr>
            <tr>
                <td>
                    <span>' . $result . '</span>
                </td>
            </tr>
        </tbody>
    </table>';


    return $output;
}
add_shortcode('total_sales_by_vendor', 'get_total_sales_by_vendor_name');



function get_vendor_total_sales($atts)
{
    $current_user = wp_get_current_user();
    $vendor_name = $current_user->user_login;

    $status_mapping = array(
        'processing' => array(
            'post_status' => 'processing',
            'status_label' => 'Transfer pending',
        ),
        'completed' => array(
            'post_status' => 'completed',
            'status_label' => 'Completed Orders',
        ),
        'on-hold' => array(
            'post_status' => 'on-hold',
            'status_label' => 'On Hold Orders',
        ),
    );

    $args = array(
        'post_type'      => 'shop_order',
        'post_status'    => array('processing', 'completed', 'on-hold'),
        'posts_per_page' => -1,
        'meta_query'     => array(
            array(
                'key'   => 'Vendor Name',
                'value' => $vendor_name,
            ),
        ),
    );

    $order_query = new WC_Order_Query($args);
    $orders = $order_query->get_orders();

    $total_sales = 0.0; // Initialize as float
    $total_vendor_price = 0.0; // Initialize as float

    foreach ($orders as $order) {
        $order_object = new Automattic\WooCommerce\Admin\Overrides\Order($order->get_id());

        $items = $order->get_items();
        foreach ($items as $item_id => $item) {
            $vendor_names = $item->get_meta('Vendor Name');
            $vendor_price = $item->get_meta('Vendor Price');
            if ($vendor_names === $vendor_name) {
                $total_sales += floatval($item->get_total()); // Convert to float
                $total_vendor_price += floatval($vendor_price); // Convert to float
            }
        }
    }

    return $total_sales;
}
?>