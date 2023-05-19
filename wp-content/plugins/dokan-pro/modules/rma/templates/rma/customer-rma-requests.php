<?php
/**
 * Customer RMA request list template
 *
 * @package dokan
 *
 * @since 1.0.0
 */
?>
<header>
    <h2><?php _e( 'All Requests', 'dokan' ); ?></h2>
</header>

<br>

<?php wc_print_notices(); ?>

<table class="shop_table my_account_orders table table-striped">

    <thead>
        <tr>
            <th class="rma-order-id"><span class="nobr"><?php _e( 'Order ID', 'dokan' ); ?></span></th>
            <th class="rma-vendor"><span class="nobr"><?php _e( 'Vendor', 'dokan' ); ?></span></th>
            <th class="rma-details"><span class="nobr"><?php _e( 'Type', 'dokan' ); ?></span></th>
            <th class="rma-status"><span class="nobr"><?php _e( 'Status', 'dokan' ); ?></span></th>
            <th></th>
        </tr>
    </thead>
    <tbody>
    <?php
    if ( ! empty( $requests ) ) {
        foreach ( $requests as $request ) {
            $order = wc_get_order( $request['order_id'] );
            if ( $order ):
                ?>
                    <tr class="order">
                        <td class="order-number">
                            <?php

                                echo sprintf( '<a href="%1$s">#%2$s</a> on <a href="%3$s">%4$s #%5$s</a>', esc_url( wc_get_account_endpoint_url( 'view-rma-requests' ) ) . $request['id'], $request['id'], $order->get_view_order_url(), __( 'Order', 'dokan' ), $order->get_order_number() );
                            ?>
                        </td>
                        <td class="rma-vendor">
                            <a href="<?php echo $request['vendor']['store_url']; ?>"><?php echo $request['vendor']['store_name']; ?></a>
                        </td>
                        <td class="rma-type">
                            <?php echo dokan_warranty_request_type( $request['type'] ); ?>
                        </td>
                        <td class="rma-status" style="text-align:left; white-space:nowrap;">
                            <?php echo dokan_warranty_request_status( $request['status'] ); ?>
                        </td>
                        <td>
                            <a href="<?php echo esc_url( wc_get_account_endpoint_url( 'view-rma-requests' ) ) . $request['id']; ?>" class="woocommerce-button button view"><?php _e( 'View', 'dokan' ) ?></a>
                        </td>
                    </tr>
                <?php
            endif;
        }
    } else {
        ?>
        <tr>
            <td colspan="5"><?php _e( 'No request found', 'dokan' ); ?></td>
        </tr>
        <?php
    }
    ?>
    </tbody>
</table>

<?php echo $pagination_html; ?>
