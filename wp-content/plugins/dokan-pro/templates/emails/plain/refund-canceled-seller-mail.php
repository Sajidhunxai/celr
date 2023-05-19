<?php
/**
 * Refund Canceled Email.
 *
 * An email sent to the vendor when a refund request get canceled.
 *
 * @class   Dokan_Email_Refund_Canceled_Vendor
 * @version 3.3.6
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
echo '= ' . esc_html( wp_strip_all_tags( $email_heading ) ) . " =\n\n";
// translators: 1: Seller name, 2: Newline character.
echo sprintf( __( 'Hi %1$s, %2$s', 'dokan' ), $seller_name, " \n\n" );
// translators: 1: Refund Request Status, 2: Newline character.
echo sprintf( __( 'Your refund request is %1$s. %2$s', 'dokan' ), $status, " \n\n" );
// translators: 1: Order ID, 2: Newline character.
echo sprintf( __( 'Order ID : %1$s %2$s', 'dokan' ), $order_id, " \n" );
// translators: 1: Refund amount, 2: Newline character.
echo sprintf( __( 'Refund Amount : %1$s %2$s', 'dokan' ), $amount, " \n" );
// translators: 1: Refund reason, 2: Newline character.
echo sprintf( __( 'Refund Reason : %1$s %2$s', 'dokan' ), $reason, " \n\n" );
// translators: 1: Order page URL, 2: Newline character.
echo sprintf( __( 'You can view the order details by visiting this link. %1$s %2$s', 'dokan' ), $order_link, " \n" );


echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
