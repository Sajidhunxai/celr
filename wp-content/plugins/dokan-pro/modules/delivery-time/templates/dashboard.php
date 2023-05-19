<?php
/**
 *  Dokan Delivery time dashboard Template
 *
 *  Load delivery_time_dashboard related template
 *
 *  @since 3.3.0
 *
 *  @package dokan
 */


$nonce = wp_create_nonce( 'dokan_vendor_get_calendar_nonce' );

$selected_filter = isset( $_GET['delivery_type_filter'] ) ? wc_clean( wp_unslash( $_GET['delivery_type_filter'] ) ) : 'all'; // phpcs:ignore

wp_add_inline_script( 'dokan-delivery-time-vendor-script', 'let dokan_delivery_time_calendar_nonce =' . wp_json_encode( $nonce ), 'before' );
?>

<?php do_action( 'dokan_dashboard_wrap_start' ); ?>

    <div class="dokan-dashboard-wrap">

        <?php

        /**
         *  Hook dokan_dashboard_content_before
         *
         *  @hooked get_dashboard_side_navigation
         *
         *  @since 3.3.0
         */
        do_action( 'dokan_dashboard_content_before' );
        do_action( 'dokan_delivery_time_dashboard_content_before' );

        ?>

        <div class="dokan-dashboard-content">
            <header class="dokan-dashboard-header">
                <h1 class="entry-title">
                    <?php esc_html_e( 'Delivery Time & Store Pickup', 'dokan' ); ?>
                </h1>
            </header>
            <div>
                <div class="dokan-delivery-type-wrapper">
                    <form action="" method="get" class="dokan-form-group">
                        <select class="dokan-form-control" id="delivery-type-filter" style="display: block;" name="delivery_type_filter">
                            <option <?php selected( $selected_filter, 'all' ); ?> value="all"><?php esc_html_e( 'All Events', 'dokan' ); ?></option>
                            <option <?php selected( $selected_filter, 'delivery' ); ?> value="delivery"><?php esc_html_e( 'Delivery', 'dokan' ); ?></option>
                            <option <?php selected( $selected_filter, 'store-pickup' ); ?> value="store-pickup"><?php esc_html_e( 'Store Pickup', 'dokan' ); ?></option>
                        </select>
                        <button class="dokan-btn dokan-btn-sm" type="submit"><?php esc_html_e( 'Filter', 'dokan' ); ?></button>
                    </form>
                </div>
                <div id='delivery-time-calendar'></div>
            </div>
        </div>

    </div><!-- .dokan-dashboard-wrap -->

<?php do_action( 'dokan_dashboard_wrap_end' ); ?>
