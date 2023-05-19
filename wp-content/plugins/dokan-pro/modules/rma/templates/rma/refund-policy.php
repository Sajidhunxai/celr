<?php
/**
 * Dokan Refund Policy Single Product tab Template
 *
 * @since 2.9.16
 *
 * @package dokan
 */
?>

<?php

do_action( 'dokan_before_refund_policy' );

printf( '%s', apply_filters( 'the_content', $policy ) );

do_action( 'dokan_after_refund_policy' );