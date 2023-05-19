<?php
/**
 * Display the related orders for a subscription or order
 *
 * @var object $post The primitive post object that is being displayed (as an order or subscription)
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

?>
<style>
    .dokan_vendor_subscriptions_related_orders {
        margin: 0;
        overflow: auto;
    }

    .dokan_vendor_subscriptions_related_orders table {
        width: 100%;
        background: #fff;
        border-collapse: collapse;
    }

    .dokan_vendor_subscriptions_related_orders table thead th {
        background: #f8f8f8;
        padding: 8px;
        font-size: 11px;
        text-align: left;
        color: #555;
        -webkit-touch-callout: none;
        -webkit-user-select: none;
        -khtml-user-select: none;
        -moz-user-select: none;
        -ms-user-select: none;
        user-select: none;
    }

    .dokan_vendor_subscriptions_related_orders table thead th:last-child {
        padding-right: 12px;
    }

    .dokan_vendor_subscriptions_related_orders table thead th:first-child {
        padding-left: 12px;
    }

    .dokan_vendor_subscriptions_related_orders table thead th:last-of-type,
    .dokan_vendor_subscriptions_related_orders table td:last-of-type {
        text-align: right;
    }

    .dokan_vendor_subscriptions_related_orders table tbody th,
    .dokan_vendor_subscriptions_related_orders table td {
        padding: 8px;
        text-align: left;
        line-height: 26px;
        vertical-align: top;
        border-bottom: 1px dotted #ececec;
    }

    .dokan_vendor_subscriptions_related_orders table tbody th:last-child,
    .dokan_vendor_subscriptions_related_orders table td:last-child {
        padding-right: 12px;
    }

    .dokan_vendor_subscriptions_related_orders table tbody th:first-child,
    .dokan_vendor_subscriptions_related_orders table td:first-child {
        padding-left: 12px;
    }

    .dokan_vendor_subscriptions_related_orders table tbody tr:last-child td {
        border-bottom: none;
    }
</style>
<div class="dokan_vendor_subscriptions_related_orders">
	<table>
		<thead>
			<tr>
				<th><?php esc_html_e( 'Order Number', 'dokan' ); ?></th>
				<th><?php esc_html_e( 'Relationship', 'dokan' ); ?></th>
				<th><?php esc_html_e( 'Date', 'dokan' ); ?></th>
				<th><?php esc_html_e( 'Status', 'dokan' ); ?></th>
				<th><?php echo esc_html_x( 'Total', 'table heading', 'dokan' ); ?></th>
			</tr>
		</thead>
		<tbody>
			<?php do_action( 'dokan_vendor_subscription_related_orders_meta_box_rows', $post ); ?>
		</tbody>
	</table>
</div>
