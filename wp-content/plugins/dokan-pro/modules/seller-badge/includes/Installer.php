<?php

namespace WeDevs\DokanPro\Modules\SellerBadge;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

/**
 * Dokan Seller Badge Installer Class
 *
 * @since 3.7.14
 */
class Installer {

    /**
     * Install Seller Badge Module
     *
     * @since 3.7.14
     */
    public function __construct() {
        $this->create_tables();
        $this->create_capabilities();
        $this->daily_schedule_cron();
    }

    /**
     * Create all the table for the module
     *
     * @since 3.7.14
     *
     * @return void
     */
    public function create_tables() {
        global $wpdb;

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $collate = $wpdb->get_charset_collate();

        $table['dokan_seller_badge'] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_seller_badge` (
                  `id` bigint unsigned AUTO_INCREMENT,
                  `badge_name` text NOT NULL,
                  `badge_logo` text NOT NULL,
                  `event_type` varchar(256) NOT NULL,
                  `badge_status` varchar (25) DEFAULT 'draft' COMMENT 'available status: published, draft',
                  `level_count` tinyint(4) unsigned NOT NULL DEFAULT 1,
                  `created_by` bigint NOT NULL,
                  `created_at` int(11) UNSIGNED NOT NULL DEFAULT 0,
                  PRIMARY KEY  (`id`)
                ) ENGINE=InnoDB {$collate}";

        $table['dokan_seller_badge_level'] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_seller_badge_level` (
                  `id` bigint unsigned AUTO_INCREMENT,
                  `badge_id` bigint NOT NULL,
                  `level` tinyint(4) unsigned NOT NULL DEFAULT 1,
                  `level_condition` varchar(256) NULL ,
                  `level_data` varchar(256) NULL ,
                  PRIMARY KEY  (`id`),
                  KEY badge_id (badge_id)
                ) ENGINE=InnoDB {$collate}";

        $table['dokan_seller_badge_acquired'] = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_seller_badge_acquired` (
                  `id` bigint unsigned AUTO_INCREMENT,
                  `vendor_id` bigint unsigned NOT NULL,
                  `level_id` bigint NOT NULL,
                  `acquired_data` varchar(250) DEFAULT '',
                  `acquired_status` varchar(250) DEFAULT 'draft',
                  `badge_seen` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0:not seen, 1:seen',
                  `created_at` int(11) UNSIGNED NOT NULL DEFAULT 0,
                  PRIMARY KEY  (`id`),
                  UNIQUE KEY `vendor_level` (`vendor_id`,`level_id`),
                  KEY vendor_id (vendor_id),
                  KEY level_id (level_id)
                ) ENGINE=InnoDB {$collate}";

        foreach ( $table as $key => $sql ) {
            dbDelta( $sql );
        }
    }

    /**
     * Create capabilities for vendor.
     *
     * @since 3.7.14
     *
     * @return void
     */
    public function create_capabilities() {
        global $wp_roles;

        if ( ! class_exists( 'WP_Roles' ) ) {
            return;
        }

        if ( ! isset( $wp_roles ) ) {
            $wp_roles = new \WP_Roles(); //phpcs:ignore
        }

        $wp_roles->add_cap( 'seller', 'dokan_view_badge_menu' );
        $wp_roles->add_cap( 'administrator', 'dokan_view_badge_menu' );
        $wp_roles->add_cap( 'shop_manager', 'dokan_view_badge_menu' );
    }

    /**
     * Schedule cron for daily midnight
     *
     * @since 3.7.14
     *
     * @return bool
     */
    public function daily_schedule_cron() {
        $queue = WC()->queue();
        $hook = 'dokan_seller_badge_daily_at_midnight_cron';
        if ( null === $queue->get_next( $hook ) ) {
            $queue->cancel_all( $hook );
            // schedule cron at midnight local time
            $timestamp = dokan_current_datetime()->modify( 'midnight' )->getTimestamp();
            $queue->schedule_recurring( $timestamp, DAY_IN_SECONDS, $hook );
        }

        return true;
    }
}
