<?php
$dss              = dokan_pro()->module->store_support;
$topic_id         = get_query_var( 'support' );
$base_ticket_date = ! empty( $ticket_start_date ) && ! empty( $ticket_end_date ) ? dokan_format_date( $ticket_start_date ) . ' - ' . dokan_format_date( $ticket_end_date ) : '';

if ( ! empty( $topic_id ) && is_numeric( $topic_id ) ) {
    $topic = $dss->get_single_topic( $topic_id, dokan_get_current_user_id() );
}
?>

<?php do_action( 'dokan_dashboard_wrap_start' ); ?>

<div class="dokan-dashboard-wrap">

    <?php
        /**
         *  The dokan_dashboard_content_before hook
         *  The dokan_dashboard_support_content_before
         *
         *  @hooked get_dashboard_side_navigation
         *
         *  @since 2.4
         */
        do_action( 'dokan_dashboard_content_before' );
        do_action( 'dokan_dashboard_support_content_before' );
    ?>

    <div class="dokan-dashboard-content dokan-support-listing dokan-support-topic-wrapper">

        <?php
        if ( empty( $topic ) ) {
            ?>

        <header class="dokan-dashboard-header">
            <?php $ticket_title = __( 'Support Tickets', 'dokan' ); ?>
            <h1 class="entry-title"><?php echo apply_filters( 'dss_vendor_support_title', $ticket_title ); ?></h1>
        </header><!-- .dokan-dashboard-header -->

            <?php do_action( 'dokan_support_ticket_listing_filter_form_before', $args ); ?>

        <form action="" method="get" class="dokan-form-inline dokan-w12 dokan-store-support-ticket-search-form">
            <?php do_action( 'dokan_support_ticket_listing_filter_form_start', $args ); ?>

            <div class="dokan-form-group">
                <select name="customer_id" id="dokan-search-support-customers" class="dokan-form-control" data-allow_clear="true" data-placeholder="<?php esc_attr_e( 'Customer Name', 'dokan' ); ?>">
                    <?php if ( ! empty( $customer_id ) ) { ?>
                        <option value="<?php echo esc_attr( $customer_id ); ?>" selected="selected"><?php echo esc_html( $customer_name ); ?></option>
                    <?php } else { ?>
                        <option value="" selected="selected"><?php esc_html_e( 'Select an option', 'dokan' ); ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="dokan-form-group">
                <input type="text" class="dokan-form-control" id="support_ticket_date_filter" placeholder="<?php esc_attr_e( 'Select Date Range', 'dokan' ); ?>" value="<?php echo esc_attr( $base_ticket_date ); ?>" autocomplete="off">
                <input type="hidden" name="ticket_start_date" id="support_ticket_start_date_filter_alt" value="<?php echo esc_attr( $ticket_start_date ); ?>" />
                <input type="hidden" name="ticket_end_date" id="support_ticket_end_date_filter_alt" value="<?php echo esc_attr( $ticket_end_date ); ?>" />
            </div>

            <div class="dokan-form-group">
                <input type="text" class="dokan-form-control" name="ticket_keyword" id="dokan-support-ticket-search-input" placeholder="<?php esc_attr_e( 'Ticket ID or Keyword', 'dokan' ); ?>" value="<?php echo esc_attr( $ticket_keyword ); ?>">
            </div>

            <input type="hidden" name="ticket_status" value="<?php echo esc_attr( $ticket_status ); ?>">

            <?php wp_nonce_field( 'dokan-support-listing-search', 'dokan-support-listing-search-nonce', false ); ?>

            <input type="submit" value="<?php esc_attr_e( 'Search', 'dokan' ); ?>" class="dokan-btn">

            <?php do_action( 'dokan_support_ticket_listing_filter_from_end', $args ); ?>
        </form>

            <?php
            do_action( 'dokan_support_ticket_listing_filter_form_after', $args );

            $dss->support_topic_status_list();
            $dss->print_support_topics( $args );
        } else {
            $dss->print_single_topic( $topic );
        }
        ?>

    </div><!-- .dokan-dashboard-content -->

    <?php

        /**
         *  The dokan_dashboard_content_after hook
         *  The dokan_dashboard_support_content_after hook
         *
         *  @since 2.4
         */
        do_action( 'dokan_dashboard_content_after' );
        do_action( 'dokan_dashboard_support_content_after' );
    ?>

</div><!-- .dokan-dashboard-wrap -->

<?php do_action( 'dokan_dashboard_wrap_end' ); ?>
