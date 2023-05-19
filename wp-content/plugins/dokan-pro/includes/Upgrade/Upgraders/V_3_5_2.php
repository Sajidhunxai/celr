<?php

namespace WeDevs\DokanPro\Upgrade\Upgraders;

use WeDevs\DokanPro\Abstracts\DokanProUpgrader;

class V_3_5_2 extends DokanProUpgrader {

    /**
     * Updates Delivery time database table
     *
     * @since 3.5.2
     *
     * @return void
     */
    public static function update_delivery_time_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'dokan_delivery_time';

        // Search if dokan delivery time is exists.
        $has_delivery_table = $wpdb->get_var(
            $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) ) // phpcs:ignore Squiz.WhiteSpace.SuperfluousWhitespace.EndLine
        );

        if ( $has_delivery_table !== $table_name ) {
            return;
        }

        $existing_columns = $wpdb->get_col( "DESC `{$table_name}`", 0 ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        // If exists delivery type column then return.
        if ( in_array( 'delivery_type', $existing_columns, true ) ) {
            return;
        }

        $wpdb->query(
            "ALTER TABLE `{$wpdb->prefix}dokan_delivery_time` ADD COLUMN `delivery_type` varchar(25) DEFAULT 'delivery' AFTER `slot`;" // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
        );

        $wpdb->query(
            "ALTER TABLE `{$wpdb->prefix}dokan_delivery_time` ADD KEY `key_vendor_id_date_type` (`vendor_id`,`date`,`delivery_type`);" // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
        );
    }

    /**
     * Update booking relationships table.
     *
     * @since 3.5.2
     *
     * @return void
     */
    public static function update_booking_relationships_table() {
        if ( ! class_exists( 'WC_Bookings_Tools' ) ) {
            return;
        }

        global $wpdb;

        $table_name = $wpdb->prefix . 'wc_booking_relationships';

        // Search if wc_booking_relationships is exists.
        $has_booking_relationships = $wpdb->get_var(
            $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) ) // phpcs:ignore Squiz.WhiteSpace.SuperfluousWhitespace.EndLine
        );

        if ( $has_booking_relationships !== $table_name ) {
            return;
        }

        $product_ids = $wpdb->get_col( "SELECT product_id FROM {$wpdb->prefix}wc_booking_relationships" );

        foreach ( $product_ids as $pid ) {
            if ( ! wc_get_product( $pid ) ) {
                \WC_Bookings_Tools::unlink_resource( $pid );
            }
        }
    }
}
