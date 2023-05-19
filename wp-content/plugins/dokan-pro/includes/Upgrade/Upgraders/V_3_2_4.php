<?php

namespace WeDevs\DokanPro\Upgrade\Upgraders;

use WeDevs\DokanPro\Abstracts\DokanProUpgrader;
use WC_Product_Query;

class V_3_2_4 extends DokanProUpgrader {

    /**
     * Create dokan shipping tracking table
     *
     * @since 3.2.3
     *
     * @return void
     */
    public static function create_dokan_shipping_tracking_table() {
        global $wpdb;

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';

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

        dbDelta( $sql );
    }
}
