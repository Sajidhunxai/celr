<?php
function calculate_vendor_quantity($atts)
{
    $current_user = wp_get_current_user();
    $current_user_username = $current_user->user_login;

    $products = get_posts(array('post_type' => 'product', 'posts_per_page' => -1));
    $total_vendor_quantity = 0;

    foreach ($products as $product) {
        $product_id = $product->ID;
        $vendors = get_post_meta($product_id, 'vendors', true);
        if ($vendors) {
            foreach ($vendors as $vendor) {
                if ($vendor['name'] === $current_user_username && $vendor['quantity'] > 0 ) {
                    $total_vendor_quantity += intval($vendor['quantity']);
                    $result = $total_vendor_quantity;
                }
            }
        }else{
            $result = 0;
        }
    }
    return '<table class="market price-box box-dashboard">
        <tbody>
            <tr>
                <th>CASES LIVE</th>
            </tr>
            <tr>
                <td>
                    <span>' . $result . '</span>
                </td>
            </tr>
        </tbody>
    </table>';
}
add_shortcode('vendor_quantity', 'calculate_vendor_quantity');

?>