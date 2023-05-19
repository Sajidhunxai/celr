<?php

namespace WeDevs\DokanPro\Modules\ReportAbuse;

class AdminSettings {

    /**
     * Class constructor
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function __construct() {
        add_filter( 'dokan_settings_sections', [ $this, 'add_settings_section' ] );
        add_filter( 'dokan_settings_fields', [ $this, 'add_settings_fields' ] );
    }

    /**
     * Add admin settings section
     *
     * @since 1.0.0
     *
     * @param array $sections
     *
     * @return array
     */
    public function add_settings_section( $sections ) {
        $sections['dokan_report_abuse'] = [
            'id'                   => 'dokan_report_abuse',
            'title'                => __( 'Product Report Abuse', 'dokan' ),
            'icon_url'             => DOKAN_REPORT_ABUSE_ASSETS . '/images/report.svg',
            'description'          => __( 'Configure Product Abusal Reports', 'dokan' ),
            'document_link'        => 'https://wedevs.com/docs/dokan/modules/dokan-report-abuse/',
            'settings_title'       => __( 'Product Report Abuse Settings', 'dokan' ),
            'settings_description' => __( 'Configure your marketplace to ensure safety and honesty by allowing customers to report fraudulent products.', 'dokan' ),
        ];

        return $sections;
    }

    /**
     * Add admin settings fields
     *
     * @since 1.0.0
     *
     * @param array $settings_fields
     *
     * @return array
     */
    public function add_settings_fields( $settings_fields ) {
        $settings_fields['dokan_report_abuse'] = [
            'reported_by_logged_in_users_only' => [
                'name'    => 'reported_by_logged_in_users_only',
                'label'   => __( 'Reported by', 'dokan' ),
                'desc'    => __( 'Only logged-in users can report', 'dokan' ),
                'type'    => 'switcher',
                'default' => 'off',
                'tooltip' => __( 'Restrict Product Abuse feature for logged-In users only', 'dokan' ),
            ],

            'abuse_reasons' => [
                'name'    => 'abuse_reasons',
                'label'   => __( 'Reasons for Abuse Report', 'dokan' ),
                'type'    => 'repeatable',
                'desc'    => __( 'Add multiple customized reasons.', 'dokan' ),
                'tooltip' => __( 'Add multiple customized reasons.', 'dokan' ),
            ],
        ];

        return $settings_fields;
    }
}
