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

$site_url = dokan_get_navigation_url( 'support' ) . $topic_id;

echo '= ' . $email_heading . ' =\n\n';

esc_html_e( 'Hi,', 'dokan' );

esc_html_e( 'A support request has been made by customer on your store: ', 'dokan' );
echo esc_html( $store_info['store_name'] . '\n\n' );

esc_html_e( 'You can see it by going here :', 'dokan' );
echo esc_url( $site_url ) . '\n\n';

echo '---\n\n';
esc_html_e( 'From ', 'dokan' );
echo esc_html( $store_info['store_name'] ) . '\n\n';
echo esc_url( home_url() ) . '\n\n';

echo '=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n';

echo '\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n';

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
