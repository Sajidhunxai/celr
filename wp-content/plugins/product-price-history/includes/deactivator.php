<?php

namespace Devnet\PPH\Includes;

class Deactivator
{

	/**	
	 * @since    1.0.0
	 */
	public static function deactivate()
	{
		// Unschedule cron task
		$timestamp = wp_next_scheduled('pph_price_alerts_schedule');
		wp_unschedule_event($timestamp, 'pph_price_alerts_schedule');
	}
}
