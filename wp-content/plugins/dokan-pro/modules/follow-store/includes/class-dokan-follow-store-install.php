<?php

class Dokan_Follow_Store_Install {

    /**
     * Class constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        add_action( 'dokan_activated_module_follow_store', array( $this, 'activate' ) );
        add_action( 'dokan_deactivated_module_follow_store', array( $this, 'deactivate' ) );
    }

    /**
     * Fires after module activated
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function activate() {
        $this->queue_flash_rewrite_rules();
        $this->create_tables();
    }

    /**
     * Fires after module deactivated
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function deactivate() {
        $this->queue_flash_rewrite_rules();
        Dokan_Follow_Store_Cron::unschedule_event();

        global $dokan_follow_store_updates_bg;
        $dokan_follow_store_updates_bg->cancel_process();
    }

    /**
     * Add option to flush rewrite rules
     *
     * Process handled by WC_Post_Types::maybe_flush_rewrite_rules()
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function queue_flash_rewrite_rules() {
        update_option( 'woocommerce_queue_flush_rewrite_rules', 'yes' );
    }

    /**
     * Create module tables
     *
     * @since 1.0.0
     *
     * @return void
     */
    private function create_tables() {
        global $wpdb;

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            if ( ! empty($wpdb->charset ) ) {
                $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
            }

            if ( ! empty($wpdb->collate ) ) {
                $collate .= " COLLATE $wpdb->collate";
            }
        }

        $table_schema = array(
            "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_follow_store_followers` (
              `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
              `vendor_id` bigint(20) unsigned NOT NULL,
              `follower_id` bigint(20) unsigned NOT NULL,
              `followed_at` datetime NOT NULL,
              `unfollowed_at` datetime DEFAULT NULL,
              PRIMARY KEY (`id`),
              UNIQUE KEY `dokan_follow_store_followers_vendor_id_follower_id_unique` (`vendor_id`,`follower_id`),
              KEY `dokan_follow_store_followers_vendor_id` (`vendor_id`),
              KEY `dokan_follow_store_followers_follower_id` (`follower_id`)
            ) $collate;",
        );

        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        foreach ( $table_schema as $table ) {
            dbDelta( $table );
        }
    }
}
