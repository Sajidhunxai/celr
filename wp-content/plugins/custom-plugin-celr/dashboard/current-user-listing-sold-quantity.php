<?php

function calculate_vendor_sales_quantity($atts)
{
    $current_user = wp_get_current_user();
    $vendor_name = $current_user->user_login;

    $args = array(
        'post_type'      => 'shop_order',
        'post_status'    => 'any',
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

    $total_sales_quantity = 0;

    foreach ($orders as $order) {
        $items = $order->get_items();

        foreach ($items as $item_id => $item) {
            $vendor_names = $item->get_meta('Vendor Name');
            if ($vendor_names === $vendor_name) {
                $total_sales_quantity += $item->get_quantity();
            }
        }
    }

    return '<table class="market price-box box-dashboard">
        <tbody>
            <tr>
                <th>CASES SOLD</th>
            </tr>
            <tr>
                <td>
                    <span>' . $total_sales_quantity . '</span>
                </td>
            </tr>
        </tbody>
    </table>';
}
add_shortcode('vendor_sales_quantity', 'calculate_vendor_sales_quantity');
?>