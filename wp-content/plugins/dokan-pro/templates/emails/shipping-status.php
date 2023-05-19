<?php
/**
 * Shipping tracking info for customers.
 *
 * An email sent to the customer(s) when shipment create or update by vendors if notify enable
 *
 * @class    ShippingStatus
 *
 * @version  3.2.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email );


$tracking_link = sprintf( '<a href="%s" target="_blank">%s</a>', $tracking_info->provider_url, __( 'Click Here to Track Your Order', 'dokan' ) );
$item_qty      = json_decode( $tracking_info->item_qty );
$line_items    = $order->get_items( 'line_item' );
$text_align    = is_rtl() ? 'right' : 'left';
?>

<?php /* translators: %s: Customer first name */ ?>
<p><?php printf( esc_html__( 'Hi %s,', 'dokan' ), esc_html( $order->get_billing_first_name() ) ); ?></p>

<p><?php esc_html_e( 'The following shipping status has been added to your order:', 'dokan' ); ?></p>

<blockquote><?php echo wpautop( wptexturize( make_clickable( $ship_info ) ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></blockquote>

<p><b><?php echo $tracking_link; ?></b></p>

<p><?php esc_html_e( 'As a reminder, here are your order details:', 'dokan' ); ?></p>

<div style="margin-bottom: 40px;">
    <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
        <thead>
            <tr>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Product', 'dokan' ); ?></th>
                <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Quantity', 'dokan' ); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if ( $item_qty ) : ?>   
                <?php foreach ( $item_qty as $item_id => $item ) : ?>
                    <?php
                    $item_details = new \WC_Order_Item_Product( $item_id );
                    $_product     = $item_details->get_product();
                    ?>
                    <tr>
                        <td class="td" style="text-align: left;vertical-align: middle;font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;color: #636363;border: 1px solid #e5e5e5;padding: 12px">
                            <?php if ( $_product ) : ?>
                                <a target="_blank" href="<?php echo esc_url( get_permalink( absint( $_product->get_id() ) ) ); ?>">
                                    <?php echo esc_html( $item_details['name'] ); ?>
                                </a>
                            <?php else : ?>
                                <?php echo esc_html( $item_details['name'] ); ?>
                            <?php endif; ?>
                        </td>
                        <td class="td" style="text-align: left;vertical-align: middle;font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;color: #636363;border: 1px solid #e5e5e5;padding: 12px">
                            <strong><?php echo esc_html( $item ); ?> (<?php esc_html_e( 'Qty', 'dokan' ); ?>)</strong>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
            <tr>
                <th class="td" scope="row" style="text-align: left;color: #636363;border: 1px solid #e5e5e5;vertical-align: middle;padding: 12px"><?php esc_attr_e( 'Payment Method', 'dokan' ); ?>:
                </th>

                <td class="td" style="text-align: left;color: #636363;border: 1px solid #e5e5e5;vertical-align: middle;padding: 12px">
                    <?php echo esc_attr( $order->get_payment_method_title() ); ?>
                </td>
            </tr>
        </tbody>
        <tfoot>
            <?php
            if ( $order->get_customer_note() ) {
                ?>
                <tr>
                    <th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Note:', 'dokan' ); ?></th>
                    <td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></td>
                </tr>
                <?php
            }
            ?>
        </tfoot>
    </table>
</div>

<?php

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
    echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
}

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
