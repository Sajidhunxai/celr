<?php
/**
 * New Review Email.
 *
 * An email sent to the vendor and admin when a new review is created by customer.
 *
 * @since 3.5.5
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
    <div>
        <table cellspacing='0'>
            <tr>
                <th class='store-name'><?php esc_html_e( 'Store Name', 'dokan' ); ?></th>
                <td class="store-name"><?php echo esc_html( $store_name ); ?> </td>
            </tr>
            <tr>
                <th class='store-name'><?php esc_html_e( 'Reviewed by', 'dokan' ); ?></th>
                <td class="store-name"><?php echo esc_html( $reviewer_name ); ?> </td>
            </tr>
            <tr>
                <th class="quote-date"><?php esc_html_e( 'Rating', 'dokan' ); ?></th>
                <td class="quote-date">
                    <p class='dokan-stars'>
                        <?php
                        for ( $i = 0; $i < $rating; $i++ ) {
                            printf( '<i class="star-%1$s dashicons dashicons-star-filled" data-rating="%2$s"></i>', $i, $i );
                        }
                        ?>
                    </p>
                </td>
            </tr>
            <tr>
                <th class='store-name'><?php esc_html_e( 'Title', 'dokan' ); ?></th>
                <td class="store-name"><?php echo esc_html( $post_title ); ?> </td>
            </tr>
            <tr>
                <th class='store-name'><?php esc_html_e( 'Details', 'dokan' ); ?></th>
                <td class="store-name"><?php echo wp_kses_post( $post_details ); ?> </td>
            </tr>
        </table>
    </div>

<?php

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
