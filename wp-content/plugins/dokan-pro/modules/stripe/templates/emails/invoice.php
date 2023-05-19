<?php
defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

<p><?php echo esc_html( sprintf( __( 'Hello %s', 'dokan' ), $invoice->vendor_name ) ); ?></p>
<p>
    <?php
        echo wp_kses_post(
                sprintf(
                    __( 'Please pay the outstanding <a href="%s" target="_blank" >subcription bill</a> and confirm your payment method to avoid subscription cancellation.', 'dokan' ),
                    esc_url( $invoice->invoice_url )
                )
            );
    ?>
</p>

<?php do_action( 'woocommerce_email_footer', $email );