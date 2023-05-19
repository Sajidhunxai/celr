<?php

namespace WeDevs\DokanPro\Upgrade\Upgraders;

use WeDevs\DokanPro\Abstracts\DokanProUpgrader;
use WeDevs\DokanPro\Modules\PayPalMarketplace\Helper;

class V_3_3_7 extends DokanProUpgrader {

    /**
     * Update PayPal Marketplace Webhooks
     *
     * @since 3.3.7
     *
     * @return void
     */
    public static function update_paypal_marketplace_webhooks() {
        if ( dokan_pro()->module->is_active( 'paypal_marketplace' ) ) {
            if ( Helper::is_ready() ) {
                /**
                 * @var $instance \WeDevs\DokanPro\Modules\PayPalMarketplace\WebhookHandler
                 */
                $instance = dokan_pro()->module->paypal_marketplace->webhook;
                $instance->register_webhook();
            }
        }
    }

    /**
     * Give default dokan_export_product capability
     * to vendor_staff to export the order list
     *
     * @since 3.3.7
     *
     * @return void
     */
    public static function add_dokan_export_product_capability_to_staff() {
        // get all staffs, vendors and admins
        $users = get_users(
            [
                'role__in' => [ 'vendor_staff', 'seller', 'administrator' ],
            ]
        );

        // adding capability to each user
        foreach ( $users as $user ) {
            $user->add_cap( 'dokan_export_order' );
        }
    }

    /**
     * Updates Delivery time database table
     *
     * @since 3.3.7
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

        $existing_columns = $wpdb->get_col( "DESC `{$wpdb->prefix}dokan_delivery_time`", 0 ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared

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
}
