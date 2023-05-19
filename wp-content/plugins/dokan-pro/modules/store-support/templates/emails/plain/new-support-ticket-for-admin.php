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
 * @author Dokan
 *
 * @version 3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$store_support_url = admin_url( 'admin.php?page=dokan' ) . '#/admin-store-support?page_type=single&topic=' . $topic_id . '&vendor_id=' . $store_id;

echo '= ' . $email_heading . ' =\n\n';

esc_html_e( 'Hi,', 'dokan' );

esc_html_e( 'A new support ticket is created in store: ', 'dokan' );
echo esc_html( $store_info['store_name'] . '\n\n' );

esc_html_e( 'Ticket URL :', 'dokan' );
echo esc_url( $store_support_url ) . '\n\n';

echo '---\n\n';
esc_html_e( 'From ', 'dokan' );
echo esc_html( $store_info['store_name'] ) . '\n\n';

echo esc_url( home_url() ) . '\n\n';

echo '=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n';

echo '\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n';

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
