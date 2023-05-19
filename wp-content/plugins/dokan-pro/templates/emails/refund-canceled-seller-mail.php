<?php
/**
 * Refund Canceled Email.
 *
 * An email sent to the vendor when a refund request get canceled.
 *
 * @class   Dokan_Email_Refund_Canceled_Vendor
 * @version 3.3.6
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
do_action( 'woocommerce_email_header', $email_heading, $email );
?>
<p>
    <?php
    // translators: 1: Seller name, 2: Newline character.
    echo sprintf( __( 'Hi %1$s, %2$s', 'dokan' ), $seller_name, '<br>' );
    ?>
</p>
<p>
    <?php
    // translators: 1: Refund Request Status, 2: Newline character.
    echo sprintf( __( 'Your refund request is %1$s. %2$s', 'dokan' ), $status, '<br>' );
    ?>
</p>
<hr>
<p>
    <?php
    // translators: 1: Order ID, 2: Newline character.
    echo sprintf( __( 'Order ID : %1$s %2$s', 'dokan' ), $order_id, '<br>' );
    ?>
    <?php
    // translators: 1: Refund amount, 2: Newline character.
    echo sprintf( __( 'Refund Amount : %1$s %2$s', 'dokan' ), $amount, '<br>' );
    ?>
    <?php
    // translators: 1: Refund reason, 2: Newline character.
    echo sprintf( __( 'Refund Reason : %1$s %2$s', 'dokan' ), $reason, '<br>' );
    ?>
</p>
<p>
    <?php
    // translators: 1: Order page URL, 2: Newline character.
    echo sprintf( __( 'You can view the order details by clicking <a href="%1$s">here</a>%2$s', 'dokan' ), $order_link, " \n" );

    ?>
</p>

<?php
do_action( 'woocommerce_email_footer', $email );
