<?php
/**
 * My Account page
 *
 * This template can be overridden by copying it to yourtheme/dokan/store-support/support-tickets.php.
 *
 * @package Dokan/Templates
 */

defined( 'ABSPATH' ) || exit;

$dss      = dokan_pro()->module->store_support;
$topic_id = get_query_var( 'support-tickets' );
$topic    = is_numeric( $topic_id ) ? $dss->get_single_topic_by_customer( $topic_id, dokan_get_current_user_id() ) : ''; ?>

<div class="dokan-support-customer-listing dokan-support-topic-wrapper">
    <?php
        if ( empty( $topic ) || isset( $_GET['ticket_status'] ) ) {
            $dss->support_topic_status_list( false );
            $dss->print_support_topics_by_customer( dokan_get_current_user_id() );
        } else {
            $dss->print_single_topic( $topic );
        }
    ?>
</div>