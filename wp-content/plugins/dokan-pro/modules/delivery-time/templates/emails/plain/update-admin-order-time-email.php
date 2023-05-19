<?php
/**
 * Update Order Delivery Time Email.
 *
 * An email sent to the vendor/customer when an order delivery time updated.
 *
 * @class   Dokan_Email_Admin_Update_Order_Delivery_Time
 * @version 3.7.8
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
echo '= ' . esc_html( wp_strip_all_tags( $email_heading ) ) . " =\n\n";
// translators: 1: Newline character.
echo sprintf( __( 'Hi, %1$s', 'dokan' ), " \n\n" );
// translators: 1: Delivery time updated by, 2: Newline character.
echo sprintf( __( 'Your order %1$s time has been updated by admin. %2$s', 'dokan' ), wp_strip_all_tags( $updated_delivery_type ), " \n\n" );
// translators: 1: Previous delivery date, 2: Previous delivery slot, 3: Previous delivery type, 4: Newline character.
echo sprintf( __( 'Previous delivery details : Date - %1$s, Slot - %2$s, Type - %3$s. %4$s', 'dokan' ), wp_strip_all_tags( $prev_delivery_date ), wp_strip_all_tags( $prev_delivery_slot ), wp_strip_all_tags( $prev_delivery_type ), " \n\n" );
// translators: 1: Updated delivery date, 2: Updated delivery slot, 3: Updated delivery type, 4: Newline character.
echo sprintf( __( 'Updated delivery details : Date - %1$s, Slot - %2$s, Type - %3$s. %4$s', 'dokan' ), wp_strip_all_tags( $updated_delivery_date ), wp_strip_all_tags( $updated_delivery_slot ), wp_strip_all_tags( $updated_delivery_type ), " \n\n" );

if ( 'store-pickup' === $updated_delivery_type ) :
    if ( 'store-pickup' === $prev_delivery_type ) :
        // translators: 1: Previous pickup location, 2: Newline character.
        echo sprintf( __( 'Previous pickup location : %1$s. %2$s', 'dokan' ), wp_strip_all_tags( $prev_pickup_location ), " \n\n" );
    endif;

    // translators: 1: Updated pickup location, 2: Newline character.
    echo sprintf( __( 'Updated pickup location : %1$s. %2$s', 'dokan' ), wp_strip_all_tags( $updated_pickup_location ), " \n\n" );
endif;

// translators: 1: Order page URL, 2: Newline character.
echo sprintf( __( 'You can view the order details by visiting this link. %1$s %2$s', 'dokan' ), esc_url( $order_link ), " \n" );

/**
 * Show user-defined additional content - this is set in each email's settings.
 */
if ( $additional_content ) {
    echo esc_html( wp_strip_all_tags( wptexturize( $additional_content ) ) );
    echo "\n\n----------------------------------------\n\n";
}

echo "\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) );
