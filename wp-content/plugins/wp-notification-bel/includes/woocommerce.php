<?php

defined('ABSPATH') || exit;
//add_action('woocommerce_order_status_completed', 'wnbell_order_completed');
//add_action('woocommerce_process_shop_order_meta', 'wnbell_order_updated');
add_action('woocommerce_order_status_changed', 'wnbell_order_updated', 10, 3);
function wnbell_order_updated($order_id, $old_status, $new_status)
{
    $enable_new_woocommerce = get_option('wnbell_options')['enable_new_woocommerce'];
    if (!isset($enable_new_woocommerce) || $enable_new_woocommerce == false) {
        return;
    }
    $order = new WC_Order($order_id);
    //$user = $order->get_user();
    $user_id = $order->get_user_id();
    if (!$user_id) {
        return;
    }
    $user_meta = array();
    $user_meta = get_user_meta($user_id, 'wnbell_woocommerce_updates', true);
    if (!$user_meta) {
        $user_meta = array();
    }
    $date = $order->get_date_modified()->date('Y-m-d H:i:s');
    $order_update = array('order_id' => $order_id, 'status' => $new_status, 'date' => $date, 'time' => time());
    array_unshift($user_meta, $order_update);
    if (sizeof($user_meta) > 20) {
        $removed = array_pop($user_meta);
    }
    update_user_meta($user_id, 'wnbell_woocommerce_updates', $user_meta);

    wnbell_update_user_count($user_id);

}
