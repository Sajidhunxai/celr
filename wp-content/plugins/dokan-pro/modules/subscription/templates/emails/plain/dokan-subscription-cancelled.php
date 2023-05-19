<?php
/**
 * Subscription Cancelled Email
 *
 * An email is sent to admin when a subscription is get cancalled by the vendor
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! $vendor ) {
    return;
}

echo "= " . $email_heading . " =\n\n";
?>

<?php _e( 'Hello there,', 'dokan' );  echo " \n\n";?>

<?php echo sprintf( __( 'A subscription has been cancelled by %s', 'dokan' ), $vendor->get_store_name() ); echo " \n\n"; ?>

<?php _e( 'Subscription Details:', 'dokan' );  ?>

<?php echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n"; ?>

<?php
    if ( $subscription ) {
        printf( __( 'Subscription Details:', 'dokan' ) ); echo " \n";

        printf( '<p>%s</p>', sprintf( __( 'Subscription Pack: %s', 'dokan' ), $subscription->get_package_title() ) ); echo " \n";
        printf( '<p>%s</p>', sprintf( __( 'Price: %s' , 'dokan' ), wc_price( $subscription->get_price() ) ) ); echo " \n";
    }
?>

<?php

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
