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

defined( 'ABSPATH' ) || exit;

$ship_info     = '';
$tracking_link = '';

if ( $tracking_info ) {
	$ship_info = __( 'Shipping Provider: ', 'dokan' ) . $tracking_info->provider_label . '<br />' . __( 'Shipping number: ', 'dokan' ) . $tracking_info->number . '<br />' . __( 'Shipped date: ', 'dokan' ) . $tracking_info->date . '<br />' . __( 'Shipped status: ', 'dokan' ) . $tracking_info->status_label;

	$tracking_link = sprintf( '<a href="%s" target="_blank">%s</a>', $tracking_info->provider_url, __( 'Click Here to Track Your Order', 'dokan' ) );
}

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n";
echo esc_html( wp_strip_all_tags( $email_heading ) );
echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

/* translators: %s: Customer first name */
echo sprintf( esc_html__( 'Hi %s,', 'dokan' ), esc_html( $order->get_billing_first_name() ) ) . "\n\n";
echo esc_html__( 'The following shipping status has been added to your order:', 'dokan' ) . "\n\n";

echo "----------\n\n";

echo wptexturize( $ship_info ) . "\n\n"; // phpcs:ignore WordPress.XSS.EscapeOutput.OutputNotEscaped

echo $tracking_link;

echo "----------\n\n";

echo esc_html__( 'As a reminder, here are your order details:', 'dokan' ) . "\n\n";

/*
 * @hooked WC_Emails::order_details() Shows the order details table.
 * @hooked WC_Structured_Data::generate_order_data() Generates structured data.
 * @hooked WC_Structured_Data::output_structured_data() Outputs structured data.
 * @since 2.5.0
 */
do_action( 'woocommerce_email_order_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n----------------------------------------\n\n";

/*
 * @hooked WC_Emails::order_meta() Shows order meta data.
 */
do_action( 'woocommerce_email_order_meta', $order, $sent_to_admin, $plain_text, $email );

/*
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

echo "\n\n----------------------------------------\n\n";

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
	echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
	echo "\n\n----------------------------------------\n\n";
}

echo wp_kses_post( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) );
