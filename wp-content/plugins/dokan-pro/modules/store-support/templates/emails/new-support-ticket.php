<?php
/**
 * New Support Ticket email
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

$site_url = dokan_get_navigation_url( 'support' ) . $topic_id;

?>

<div style="margin-bottom: 40px;">
    <?php esc_html_e( 'Hi,', 'dokan' ); ?>

	<p><?php esc_html_e( 'A support request has been made by customer on your store ', 'dokan' ); ?> <?php echo $store_info['store_name']; ?></p>

	<p><?php esc_html_e( 'You can see it by going here :', 'dokan' ); ?> <a href="<?php echo esc_url( $site_url ); ?>"><?php echo esc_url( $site_url ); ?></a></p>

	---
	<p><?php esc_html_e( 'From', 'dokan' ); ?> <?php echo $store_info['store_name']; ?></p>
	<p><?php echo esc_url( home_url() ); ?></p>
</div>

<?php

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
