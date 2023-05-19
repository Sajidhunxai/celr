<?php

namespace WeDevs\DokanPro\Install;

/**
* Dokan Pro Installer file
*/

class Installer {

    /**
     * Load automatically when class initiate
     *
     * @since 2.8.0
     */
    public function do_install() {
        $this->create_shipping_tables();
        $this->create_shipping_tracking_table();
        $this->maybe_activate_modules();
        $this->appsero_optin();
    }

    /**
     * Create Shipping Tables
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function create_shipping_tables() {
        global $wpdb;

        $sqls = [
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_shipping_zone_methods` (
              `instance_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `method_id` varchar(255) NOT NULL DEFAULT '',
              `zone_id` int(11) unsigned NOT NULL,
              `seller_id` int(11) NOT NULL,
              `is_enabled` tinyint(1) NOT NULL DEFAULT '1',
              `settings` longtext,
              PRIMARY KEY (`instance_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",

            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_shipping_zone_locations` (
              `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
              `seller_id` int(11) DEFAULT NULL,
              `zone_id` int(11) DEFAULT NULL,
              `location_code` varchar(255) DEFAULT NULL,
              `location_type` varchar(255) DEFAULT NULL,
              PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;",
        ];

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';

        foreach ( $sqls as $sql ) {
            dbDelta( $sql );
        }
    }

    /**
     * Add new table for shipping tracking
     *
     * @since 3.2.4
     *
     * @return void
     */
    public function create_shipping_tracking_table() {
        global $wpdb;

        $sql = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_shipping_tracking` (
               `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
               `order_id` bigint(20) unsigned NOT NULL,
               `seller_id` bigint(20) NOT NULL,
               `provider` text NULL,
               `provider_label` text NULL,
               `provider_url` text NULL,
               `number` text NULL,
               `date` varchar(200) NULL,
               `shipping_status` varchar(200) NULL,
               `status_label` varchar(200) NULL,
               `is_notify` varchar(20) NULL,
               `item_id` text NULL,
               `item_qty` text NULL,
               `last_update` timestamp NOT NULL,
               `status` int(1) NOT NULL,
              PRIMARY KEY (id),
              KEY `order_id` (`order_id`),
              KEY `order_shipping_status` (`order_id`,`shipping_status`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;";

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';

        dbDelta( $sql );
    }

    /**
     * Maybe Activate modules
     *
     * For the first time activation after installation,
     * activate all pro modules.
     *
     * @since 2.8.0
     * @since 3.0.0 Using module manager to activate all modules
     * @since 3.1.0 Activate only available modules instead of all modules
     * @since 3.3.1 reactivate active modules after dokan pro is activated
     *
     * @return void
     * */
    public function maybe_activate_modules() {
        global $wpdb;

        if ( ! function_exists( 'WC' ) || ! function_exists( 'dokan' ) ) {
            return;
        }

        $modules = ! empty( dokan_pro()->module ) ? dokan_pro()->module : new \WeDevs\DokanPro\Module();

        $installed_modules = get_option( $modules::ACTIVE_MODULES_DB_KEY );
        // if some modules are previously installed and active, reactivate them
        if ( is_array( $installed_modules ) && ! empty( $installed_modules ) ) {
            $modules->activate_modules( $installed_modules );
            return;
        } elseif ( is_array( $installed_modules ) ) {
            return; // user deliberately left all modules deactive, so ignore that
        }
        // this is fresh installation, activate all available modules
        $modules->activate_modules( $modules->get_available_modules() );
    }

    /**
     * Initialize the appsero SDK
     *
     * @since 3.1.1
     *
     * @return void
     */
    protected function appsero_optin() {
        if ( ! class_exists( '\Appsero\Client' ) ) {
            return;
        }

        $client = new \Appsero\Client( '8f0a1669-b8db-46eb-9fc4-02ac5bfe89e7', 'Dokan Pro', DOKAN_PRO_FILE );

        $insights = $client->insights();
        $insights->hide_notice()->init();
        $insights->optin();
    }
}
