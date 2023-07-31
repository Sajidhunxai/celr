<?php

namespace Devnet\PPH\Includes;

class Uninstaller
{

    /**	
     * @since    1.2.0
     */
    public static function cleanup()
    {
        $options = get_option('devnet_pph_general');

        $delete_plugin_data = $options['delete_plugin_data'] ?? false;

        if ($delete_plugin_data) {

            self::delete_options();
            self::remove_tables();
        }
    }

    /**	
     * @since    1.2.0
     */
    public static function delete_options()
    {

        delete_option('devnet_pph_general');
        delete_option('devnet_pph_chart');
        delete_option('devnet_pph_price_alerts');
        delete_option('devnet_pph_lowest_price');
    }

    /**	
     * @since    1.2.0
     */
    public static function remove_tables()
    {
        global $wpdb;

        $tables = [
            'pph_price_history',
            'pph_price_alerts',
        ];

        foreach ($tables as $table) {
            $table_name = $wpdb->prefix . $table;
            $sql = "DROP TABLE IF EXISTS $table_name";
            $wpdb->query($sql);
        }
    }
}
