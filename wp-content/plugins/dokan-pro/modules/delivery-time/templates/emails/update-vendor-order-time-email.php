<?php
/**
 * Order Delivery Time Updated Email.
 *
 * An email sent to the vendor/customer when an order delivery time updated.
 *
 * @class   Dokan_Email_Vendor_Update_Order_Delivery_Time
 * @version 3.7.8
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

do_action( 'woocommerce_email_header', $email_heading, $email );

    /* translators: %s: Customer first name */ ?>
    <p><?php printf( esc_html__( 'Hi %s,', 'dokan' ), esc_html( $order->get_billing_first_name() ) ); ?></p>

    <p>
        <?php
        echo sprintf(
            // translators: 1: Order Id, 2: Order Delivery Type, 3: Seller name, 4: Newline character.
            __( 'Your order id #%1$s %2$s time has been updated by %3$s %4$s', 'dokan' ),
            // translators: 1: Order Link, 2: Order Id.
            sprintf( '<a href="%1$s">%2$s</a>', esc_url( $order_link ), esc_html( $order_id ) ),
            esc_html( $prev_delivery_type ),
            esc_html( $seller_name ),
            " \n\n"
        );
        ?>
    </p>

    <p><?php esc_html_e( 'As a reminder, here are your order details:', 'dokan' ); ?></p>

    <div style="margin-bottom: 30px;">
        <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
            <thead>
                <tr>
                    <th class="td" scope="col"><?php esc_html_e( 'Product', 'dokan' ); ?></th>
                    <th class="td" scope="col"><?php esc_html_e( 'Quantity', 'dokan' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if ( $order->get_items() ) : ?>
                    <?php foreach ( $order->get_items() as $item_id => $item ) : ?>
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
                                <strong><?php echo esc_html( $item['quantity'] ); ?> (<?php esc_html_e( 'Qty', 'dokan' ); ?>)</strong>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
                <tr>
                    <th class="td" scope="row" style="text-align: left;color: #636363;border: 1px solid #e5e5e5;vertical-align: middle;padding: 12px"><?php esc_attr_e( 'Payment Method', 'dokan' ); ?>:
                    </th>

                    <td class="td" style="text-align: left;color: #636363;border: 1px solid #e5e5e5;vertical-align: middle;padding: 12px">
                        <?php echo esc_html( $order->get_payment_method_title() ); ?>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <?php if ( $order->get_customer_note() ) : ?>
                    <tr>
                        <th class="td" scope="row" colspan="2"><?php esc_html_e( 'Note:', 'dokan' ); ?></th>
                        <td class="td"><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></td>
                    </tr>
                <?php endif; ?>
            </tfoot>
        </table>
    </div>
    <?php
    /*
     * @hooked WC_Emails::order_meta() Shows order meta data.
     */
    do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );
    ?>
    <p>
        <?php
        // translators: 1: Delivery type.
        echo sprintf( __( 'and also here are your %1$s details:', 'dokan' ), esc_html( $updated_delivery_type ) );
        ?>
    </p>

    <div style="margin-bottom: 40px;">
        <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
            <thead>
                <tr>
                    <th class="td" scope="col"><?php esc_html_e( 'Previous Delivery Details', 'dokan' ); ?></th>
                    <th class="td" scope="col"><?php esc_html_e( 'Updated Delivery Details', 'dokan' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="td" style="text-align: left;vertical-align: middle;font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;color: #636363;border: 1px solid #e5e5e5;padding: 12px">
                        <p><strong><?php echo esc_html( 'Delivery Date: ' ); ?> </strong> ( <?php echo esc_html( $prev_delivery_date ); ?> ) </p>
                    </td>
                    <td class="td" style="text-align: left;vertical-align: middle;font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;color: #636363;border: 1px solid #e5e5e5;padding: 12px">
                        <p><strong><?php echo esc_html( 'Delivery Date: ' ); ?> </strong> ( <?php echo esc_html( $updated_delivery_date ); ?> ) </p>
                    </td>
                </tr>
                <tr>
                    <td class="td" style="text-align: left;vertical-align: middle;font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;color: #636363;border: 1px solid #e5e5e5;padding: 12px">
                        <p><strong><?php echo esc_html( 'Delivery Slot: ' ); ?> </strong> ( <?php echo esc_html( $prev_delivery_slot ); ?> ) </p>
                    </td>
                    <td class="td" style="text-align: left;vertical-align: middle;font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;color: #636363;border: 1px solid #e5e5e5;padding: 12px">
                        <p><strong><?php echo esc_html( 'Delivery Slot: ' ); ?> </strong> ( <?php echo esc_html( $updated_delivery_slot ); ?> ) </p>
                    </td>
                </tr>
                <tr>
                    <td class="td" style="text-align: left;vertical-align: middle;font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;color: #636363;border: 1px solid #e5e5e5;padding: 12px">
                        <p><strong><?php echo esc_html( 'Delivery Type: ' ); ?> </strong> ( <?php echo esc_html( $prev_delivery_type ); ?> ) </p>
                    </td>
                    <td class="td" style="text-align: left;vertical-align: middle;font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;color: #636363;border: 1px solid #e5e5e5;padding: 12px">
                        <p><strong><?php echo esc_html( 'Delivery Type: ' ); ?> </strong> ( <?php echo esc_html( $updated_delivery_type ); ?> ) </p>
                    </td>
                </tr>
                <?php if ( 'store-pickup' === $updated_delivery_type ) : ?>
                <tr>
                    <td class="td" style="text-align: left;vertical-align: middle;font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;color: #636363;border: 1px solid #e5e5e5;padding: 12px">
                        <p><strong><?php echo esc_html( 'Store Pickup Location: ' ); ?> </strong> ( <?php echo esc_html( 'store-pickup' === $prev_delivery_type ? $prev_pickup_location : $updated_pickup_location ); ?> ) </p>
                    </td>
                    <td class="td" style="text-align: left;vertical-align: middle;font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;color: #636363;border: 1px solid #e5e5e5;padding: 12px">
                        <p><strong><?php echo esc_html( 'Store Pickup Location: ' ); ?> </strong> ( <?php echo esc_html( $updated_pickup_location ); ?> ) </p>
                    </td>
                </tr>
                <?php endif; ?>
                <tr>
                    <th class="td" scope="row" style="text-align: left;color: #636363;border: 1px solid #e5e5e5;vertical-align: middle;padding: 12px"><?php esc_html_e( 'Payment Method :', 'dokan' ); ?></th>

                    <td class="td" style="text-align: left;color: #636363;border: 1px solid #e5e5e5;vertical-align: middle;padding: 12px">
                        <?php echo esc_html( $order->get_payment_method_title() ); ?>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <?php if ( $order->get_customer_note() ) : ?>
                    <tr>
                        <th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Note:', 'dokan' ); ?></th>
                        <td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></td>
                    </tr>
                <?php endif; ?>
            </tfoot>
        </table>
    </div>

    <p>
        <?php
        echo sprintf(
            // translators: 1: Opening anchor tag, 2: Closing anchor tag, 3: Newline character.
            __( 'You can view the order details by clicking %1$shere%2$s%3$s', 'dokan' ),
            // translators: %s: Order page URL.
            sprintf( '<a href="%s">', $order_link ),
            '</a>',
            " \n"
        );
        ?>
    </p>

    <p>
        <?php
        /**
         * Show user-defined additional content - this is set in each email's settings.
         */
        if ( $additional_content ) {
            echo wp_kses_post( wpautop( wptexturize( $additional_content ) ) );
        }
        ?>
    </p>
<?php

/*
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
