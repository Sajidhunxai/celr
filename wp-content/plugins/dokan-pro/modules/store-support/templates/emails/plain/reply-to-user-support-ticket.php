<?php
/**
 * New Support Ticket email (plain text)
 *
 * This template can be overridden by copying it to yourtheme/dokan/emails/plain/admin-new-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author      Dokan
 * 
 * @version     3.3.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

echo '= ' . $email_heading . " =\n\n";

esc_html_e( 'Hi,', 'dokan' );

esc_html_e( 'A support request has been made by customer on your store: ', 'dokan' ) . esc_html( $email_data['ticket_title'] ) . "\n\n";

esc_html_e( 'See the details by going here: ', 'dokan' ) . esc_url( $email_data['account_ticket_url'] ) . "\n\n";

echo '---' . "\n\n";
esc_html_e( 'From ', 'dokan' ) . esc_html( $email_data['store_name'] ) . "\n\n";
echo esc_url( home_url() );

echo "=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
