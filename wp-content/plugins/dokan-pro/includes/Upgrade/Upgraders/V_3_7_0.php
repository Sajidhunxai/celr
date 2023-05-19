<?php

namespace WeDevs\DokanPro\Upgrade\Upgraders;

use WeDevs\DokanPro\Abstracts\DokanProUpgrader;

class V_3_7_0 extends DokanProUpgrader {

    /**
     * Updates dokan color settings data.
     *
     * @since 3.7.0
     *
     * @return void
     */
    public static function update_dokan_color_settings() {
        $colors = get_option( 'dokan_colors', [] );

        if ( empty( $colors ) || ! empty( $colors['store_color_pallete'] ) ) {
            return;
        }

        $dokan_colors             = [];
        $colors['value']          = 'default';
        $colors['pallete_status'] = 'custom';

        $dokan_colors['store_color_pallete'] = $colors;
        update_option( 'dokan_colors', $dokan_colors );
    }

    /**
     * Updates dokan rma settings data.
     *
     * @since 3.7.0
     *
     * @return void
     */
    public static function update_dokan_rma_settings() {
        $dokan_rma         = get_option( 'dokan_rma', [] );
        $enable_rma_refund = ! empty( $dokan_rma['rma_enable_refund_request'] ) ? $dokan_rma['rma_enable_refund_request'] : 'no';
        $enable_rma_coupon = ! empty( $dokan_rma['rma_enable_coupon_request'] ) ? $dokan_rma['rma_enable_coupon_request'] : 'no';

        $dokan_rma['rma_enable_refund_request'] = ( $enable_rma_refund === 'no' ) ? 'off' : 'on';
        $dokan_rma['rma_enable_coupon_request'] = ( $enable_rma_coupon === 'no' ) ? 'off' : 'on';

        update_option( 'dokan_rma', $dokan_rma );
    }

    /**
     * Updates Distance rate rules database table
     *
     * @since 3.7.0
     *
     * @return void
     */
    public static function update_distance_rate_rules_table() {
        global $wpdb;

        $table_name = $wpdb->prefix . 'dokan_distance_rate_shipping';

        // Search if dokan distance rate shipping table is exists.
        $has_delivery_table = $wpdb->get_var(
            $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $table_name ) ) // phpcs:ignore Squiz.WhiteSpace.SuperfluousWhitespace.EndLine
        );

        if ( $has_delivery_table !== $table_name ) {
            return;
        }

        $existing_columns = $wpdb->get_col( "DESC `{$table_name}`", 0 ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $column_to_add    = 'rate_order';

        // If exists column then return.
        if ( in_array( $column_to_add, $existing_columns, true ) ) {
            return;
        }

        $wpdb->query(
            "ALTER TABLE `{$table_name}` ADD COLUMN `{$column_to_add}` int(11) DEFAULT 0 AFTER `rate_abort`;" // phpcs:ignore WordPress.DB.DirectDatabaseQuery.SchemaChange
        );
    }
}
