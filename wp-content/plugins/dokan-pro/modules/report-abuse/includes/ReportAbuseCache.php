<?php

namespace WeDevs\DokanPro\Modules\ReportAbuse;

use WeDevs\Dokan\Cache;

/**
 * Abuse Report Cache class.
 *
 * Manage all of the cachings for abuse report.
 *
 * @since 3.4.2
 *
 * @see \WeDevs\Dokan\Cache
 */
class ReportAbuseCache {

    public function __construct() {
        add_action( 'dokan_report_abuse_created_report', [ $this, 'clear_abuse_report_cache' ], 10 );
        add_action( 'dokan_report_abuse_deleted_report', [ $this, 'clear_abuse_report_cache' ], 10 );
    }

    /**
     * Clear Abuse Reports cache
     *
     * @since 3.4.2
     *
     * @return void
     */
    public function clear_abuse_report_cache() {
        Cache::invalidate_group( 'abuse_reports' );
    }
}
