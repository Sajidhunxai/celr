<?php

defined( 'ABSPATH' ) || exit; // Exit if called directly

echo '= ' . wp_kses_post( $email_heading ) . " =\n\n";

/* translators: vendor name */
echo esc_html( sprintf( __( 'Hello %s', 'dokan' ), $invoice->vendor_name ) ) . "\n\n";

echo wp_kses_post(
    sprintf(
        /* translators: 1) opening anchor tag with invoice link, 2) closing anchor tag */
        __( 'Please pay the outstanding %1$ssubcription bill%2$s and confirm your payment method to avoid subscription cancellation.', 'dokan' ),
        sprintf( '<a href="%s" target="_blank">', $authorization_url ),
        '</a>'
    )
);

echo "\n\n=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=-=\n\n";

echo wp_kses_post( wpautop( wptexturize( apply_filters( 'woocommerce_email_footer_text', get_option( 'woocommerce_email_footer_text' ) ) ) ) );
