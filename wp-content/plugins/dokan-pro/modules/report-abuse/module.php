<?php

namespace WeDevs\DokanPro\Modules\ReportAbuse;

final class Module {

    /**
     * Plugin constructor
     *
     * @since 2.9.8
     *
     * @return void
     */
    public function __construct() {
        $this->define_constants();
        $this->includes();
        $this->instances();

        add_action( 'dokan_activated_module_report_abuse', [ self::class, 'activate' ] );
    }

    /**
     * Module constants
     *
     * @since 2.9.8
     *
     * @return void
     */
    private function define_constants() {
        define( 'DOKAN_REPORT_ABUSE_FILE' , __FILE__ );
        define( 'DOKAN_REPORT_ABUSE_PATH' , dirname( DOKAN_REPORT_ABUSE_FILE ) );
        define( 'DOKAN_REPORT_ABUSE_INCLUDES' , DOKAN_REPORT_ABUSE_PATH . '/includes' );
        define( 'DOKAN_REPORT_ABUSE_URL' , plugins_url( '', DOKAN_REPORT_ABUSE_FILE ) );
        define( 'DOKAN_REPORT_ABUSE_ASSETS' , DOKAN_REPORT_ABUSE_URL . '/assets' );
        define( 'DOKAN_REPORT_ABUSE_VIEWS', DOKAN_REPORT_ABUSE_PATH . '/views' );
    }

    /**
     * Include module related files
     *
     * @since 2.9.8
     *
     * @return void
     */
    private function includes() {
        require_once DOKAN_REPORT_ABUSE_INCLUDES . '/functions.php';
        require_once DOKAN_REPORT_ABUSE_INCLUDES . '/ReportAbuseCache.php';
        require_once DOKAN_REPORT_ABUSE_INCLUDES . '/AdminSettings.php';
        require_once DOKAN_REPORT_ABUSE_INCLUDES . '/Ajax.php';
        require_once DOKAN_REPORT_ABUSE_INCLUDES . '/SingleProduct.php';
        require_once DOKAN_REPORT_ABUSE_INCLUDES . '/EmailLoader.php';
        require_once DOKAN_REPORT_ABUSE_INCLUDES . '/Admin.php';
        require_once DOKAN_REPORT_ABUSE_INCLUDES . '/Rest.php';
        require_once DOKAN_REPORT_ABUSE_INCLUDES . '/AdminSingleProduct.php';
    }

    /**
     * Create module related class instances
     *
     * @since 2.9.8
     *
     * @return void
     */
    private function instances() {
        new \WeDevs\DokanPro\Modules\ReportAbuse\ReportAbuseCache();
        new \WeDevs\DokanPro\Modules\ReportAbuse\AdminSettings();
        new \WeDevs\DokanPro\Modules\ReportAbuse\Ajax();
        new \WeDevs\DokanPro\Modules\ReportAbuse\SingleProduct();
        new \WeDevs\DokanPro\Modules\ReportAbuse\EmailLoader();
        new \WeDevs\DokanPro\Modules\ReportAbuse\Admin();
        new \WeDevs\DokanPro\Modules\ReportAbuse\Rest();
        new \WeDevs\DokanPro\Modules\ReportAbuse\AdminSingleProduct();
    }

    /**
     * Executes on module activation
     *
     * @since 2.9.8
     *
     * @return void
     */
    public static function activate() {
        $option = get_option( 'dokan_report_abuse', [] );

        if ( empty( $option['abuse_reasons'] ) ) {
            $option['abuse_reasons'] = [
                [
                    'id'    => 'report_as_spam',
                    'value' => esc_html__( 'This content is spam', 'dokan' ),
                ],
                [
                    'id'    => 'report_as_adult',
                    'value' => esc_html__( 'This content should marked as adult', 'dokan' ),
                ],
                [
                    'id'    => 'report_as_abusive',
                    'value' => esc_html__( 'This content is abusive', 'dokan' ),
                ],
                [
                    'id'    => 'report_as_violent',
                    'value' => esc_html__( 'This content is violent', 'dokan' ),
                ],
                [
                    'id'    => 'report_as_risk_of_hurting',
                    'value' => esc_html__( 'This content suggests the author might be risk of hurting themselves', 'dokan' ),
                ],
                [
                    'id'    => 'report_as_infringes_copyright',
                    'value' => esc_html__( 'This content infringes upon my copyright', 'dokan' ),
                ],
                [
                    'id'    => 'report_as_contains_private_info',
                    'value' => esc_html__( 'This content contains my private information', 'dokan' ),
                ],
                [
                    'id' => 'other',
                    'value' => esc_html__( 'Other', 'dokan' )
                ],
            ];

            update_option( 'dokan_report_abuse', $option, false );
        }

        self::create_tables();
    }

    /**
     * Create module related tables
     *
     * @since 2.9.8
     *
     * @return void
     */
    private static function create_tables() {
        global $wpdb;

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            if ( ! empty($wpdb->charset ) ) {
                $collate .= "AUTO_INCREMENT=1 DEFAULT CHARACTER SET $wpdb->charset";
            }

            if ( ! empty($wpdb->collate ) ) {
                $collate .= " AUTO_INCREMENT=1 COLLATE $wpdb->collate";
            }
        }

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $request_table = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_report_abuse_reports` (
          `id` BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
          `reason` VARCHAR(191) NOT NULL,
          `product_id` BIGINT(20) NOT NULL,
          `vendor_id` BIGINT(20) NOT NULL,
          `customer_id` BIGINT(20) DEFAULT NULL,
          `customer_name` VARCHAR(191) DEFAULT NULL,
          `customer_email` VARCHAR(100) DEFAULT NULL,
          `description` TEXT DEFAULT NULL,
          `reported_at` DATETIME NOT NULL,
          INDEX `reason` (`reason`),
          INDEX `product_id` (`product_id`),
          INDEX `vendor_id` (`vendor_id`)
        ) $collate";

        dbDelta( $request_table );
    }
}
