<?php

namespace Devnet\PPH\Includes;

class Activator
{

	/**
	 * @since    1.0.0
	 */
	public static function activate()
	{
		self::setup_db_table();
	}

	/**
	 * Create custom table.
	 *  
	 * @since     1.1.0
	 */
	private static function db_table_columns($table)
	{
		$columns['price_history'] = "product_id bigint(20) UNSIGNED NOT NULL,
			parent_product_id varchar(20) NULL,
			regular_price varchar(20) NULL,
			sale_price varchar(20) NULL,
			price DECIMAL(10,2) NOT NULL,
			currency varchar(3) NULL,
			product_type varchar(20) NULL,
			hidden TINYINT(1) UNSIGNED NULL,
			date_created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP";


		$columns['price_alerts'] = "product_id bigint(20) UNSIGNED NOT NULL,
			product_type varchar(20) NULL,
			price DECIMAL(10,2) NOT NULL,
			currency varchar(3) NULL,
			target_price DECIMAL(10,2) NULL,
			user_name VARCHAR(120) NULL,
			user_email VARCHAR(120) NOT NULL,			
			user_optin TINYINT(1) UNSIGNED NULL,			
			user_marketing TINYINT(1) UNSIGNED NULL,
			status varchar(20) NULL,
			notification_date datetime NULL,
			date_created datetime NOT NULL DEFAULT CURRENT_TIMESTAMP";

		return isset($columns[$table]) ? $columns[$table] : null;
	}

	/**
	 * Create custom table.
	 *  
	 * @since     1.0.0
	 */
	private static function setup_db_table()
	{
		global $wpdb;

		$tables = [
			'price_history',
			'price_alerts',
		];

		$charset_collate = $wpdb->get_charset_collate();

		require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

		foreach ($tables as $table) {
			$table_name = $wpdb->prefix . 'pph_' . $table;

			$columns = self::db_table_columns($table);

			if (!$columns) continue;

			$sql = "CREATE TABLE $table_name (
			  id bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,        
			  $columns,
			  PRIMARY KEY  (id),
			  INDEX (product_id)
			) $charset_collate;";

			if ($wpdb->get_var("SHOW TABLES LIKE '$table_name'") != $table_name) {
				dbDelta($sql);
			}
		}
	}
}
