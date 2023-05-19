<?php
/**
 * Shows an shipment product item
 *
 * @var object $item The item being displayed
 * @var int $item_id The id of the item being displayed
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>
<tr class="item <?php echo apply_filters( 'woocommerce_admin_html_order_item_class', ( ! empty( $class ) ? $class : '' ), $item ); ?>" data-shipping_order_item_id="<?php echo esc_attr( $item_id ); ?>">
    <td class="order_item_select">
        <label class="dokan-control-label" for="shipment_order_item_select_<?php echo esc_attr( $item_id ); ?>">
            <input type="checkbox" name="shipment_order_item_select" id="shipment_order_item_select_<?php echo esc_attr( $item_id ); ?>" class="shipment_order_item_select" data-order_item_id="<?php echo esc_attr( $item_id ); ?>" value="<?php echo esc_attr( $item_id ); ?>">
        </label>

    </td>
    <td class="thumb">
        <?php if ( $_product ) : ?>
            <a href="<?php echo esc_url( get_permalink( absint( dokan_get_prop( $_product, 'id' ) ) ) ); ?>" class="tips"><?php echo $_product->get_image( 'shop_thumbnail', array( 'title' => '' ) ); ?></a>
        <?php else : ?>
            <?php echo wc_placeholder_img( 'shop_thumbnail' ); ?>
        <?php endif; ?>
    </td>
    <td class="name" data-sort-value="<?php echo esc_attr( $item['name'] ); ?>">
        <?php if ( $_product ) : ?>
            <a target="_blank" href="<?php echo esc_url( get_permalink( absint( dokan_get_prop( $_product, 'id' ) ) ) ); ?>">
                <?php echo esc_html( $item['name'] ); ?>
            </a>
        <?php else : ?>
            <?php echo esc_html( $item['name'] ); ?>
        <?php endif; ?>

        <input type="hidden" class="shipping_order_item_id" name="shipping_order_item_id[]" value="<?php echo esc_attr( $item_id ); ?>" />
    </td>

    <td class="quantity" width="1%">
        <div class="shipping-tracking dokan-hide shipping_order_item_qty_<?php echo esc_attr( $item_id ); ?>">
            <?php
            $is_shiptted = dokan_pro()->shipment->get_status_order_item_shipped( $order_id, $item_id, $item['qty'], 1 );
            $item_qty    = $is_shiptted ? $is_shiptted : $item['qty'];
            ?>
            <input style="width:60px" type="number" step="<?php echo apply_filters( 'woocommerce_quantity_input_step', '1', $_product ); ?>" min="1" max="<?php echo esc_attr( $item_qty ); ?>" autocomplete="off" name="shipping_order_item_qty[<?php echo absint( $item_id ); ?>]" value="<?php echo esc_attr( $item_qty ); ?>" placeholder="0" size="4" class="shipping_order_item_qty" />
        </div>
    </td>
</tr>
