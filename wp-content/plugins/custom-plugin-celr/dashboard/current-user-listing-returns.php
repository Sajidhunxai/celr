<?php
function calculate_vendor_profit_percentage_shortcode($atts)
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
    $total_vendor_purchase = 0;
    $result = 0;


    foreach ($orders as $order) {
        $order_object = new Automattic\WooCommerce\Admin\Overrides\Order($order->get_id());

        $items = $order->get_items();
        foreach ($items as $item_id => $item) {
            $vendor_names = $item->get_meta('Vendor Name');
            $vendor_price = $item->get_meta('Vendor Price');
            $vendor_purchase = $item->get_meta('Vendor Purchase');
            $quantity = $item->get_quantity();

            if ($vendor_names === $vendor_name) {

                $total_sales += $vendor_price * $quantity;
                $total_vendor_purchase +=  $vendor_purchase * $quantity;

                if ($total_vendor_purchase !== 0 && $total_vendor_purchase !== '') {
                    $profit_percentage = (($total_sales - $total_vendor_purchase) / $total_vendor_purchase) * 100;
                    $result = round($profit_percentage, 2);

                } else {
                    $result = 0;
                }                    
            }
        }
    }

    return '<table class="market price-box box-dashboard">
        <tbody>
            <tr>
                <th>RETURN %</th>
            </tr>
            <tr>
                <td>
                    <span>' . $result . '</span>
                </td>
            </tr>
        </tbody>
    </table>';
}

add_shortcode('vendor_profit_percentage', 'calculate_vendor_profit_percentage_shortcode');
?>
