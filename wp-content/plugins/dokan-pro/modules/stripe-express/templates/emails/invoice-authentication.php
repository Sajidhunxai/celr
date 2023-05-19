<?php
    defined( 'ABSPATH' ) || exit; // Exit if called directly
    do_action( 'woocommerce_email_header', $email_heading, $email );
?>

<p>
    <?php
    /* translators: vendor name */
    echo esc_html( sprintf( __( 'Hello %s', 'dokan' ), $invoice->vendor_name ) );
    ?>
</p>
<p>
    <?php
    echo wp_kses_post(
        sprintf(
            /* translators: 1) opening anchor tag with invoice link, 2) closing anchor tag */
            __( 'Please pay the outstanding %1$ssubcription bill%2$s and confirm your payment method to avoid subscription cancellation.', 'dokan' ),
            sprintf( '<a href="%s" target="_blank">', $authorization_url ),
            '</a>'
        )
    );
    ?>
</p>

<?php do_action( 'woocommerce_email_footer', $email ); ?>
