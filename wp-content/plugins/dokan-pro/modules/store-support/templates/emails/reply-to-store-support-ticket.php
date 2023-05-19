<?php
/**
 * A New Reply to Store on Your Ticket
 *
 * This template can be overridden by copying it to yourtheme/dokan/emails/admin-new-order.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author  Dokan
 *
 * @version 3.3.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$text_align = is_rtl() ? 'right' : 'left';

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<div style="margin-bottom: 40px;">
    <?php esc_html_e( 'Hi,', 'dokan' ); ?>

    <?php /* translators: %s is replaced with "comment_author" */ ?>
	<p><?php echo esc_html( sprintf( __( '%s has replied to conversation: ', 'dokan' ), $email_data['comment']->comment_author ) ); ?> <b>#<?php echo esc_html( $email_data['ticket_id'] ); ?></b></p>

	<p><b><?php esc_html_e( 'Ticket URL: ', 'dokan' ); ?></b> <a href="<?php echo esc_url( $email_data['ticket_url'] ); ?>"><?php echo esc_url( $email_data['ticket_url'] ); ?></a></p>

	---
	<p><?php esc_html_e( 'From', 'dokan' ); ?> <?php echo esc_html( $email_data['store_name'] ); ?></p>
	<p><?php echo esc_url( home_url() ); ?></p>
</div>

<?php

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
