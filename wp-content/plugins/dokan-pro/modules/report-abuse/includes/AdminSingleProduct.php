<?php

namespace WeDevs\DokanPro\Modules\ReportAbuse;

class AdminSingleProduct {

    /**
     * Class constructor
     *
     * @since 2.9.8
     *
     * @return void
     */
    public function __construct() {
        add_action( 'init', [ self::class, 'register_scripts' ] );
        add_action( 'add_meta_boxes', [ self::class, 'add_abuse_report_meta_box' ] );
    }

    /**
     * Add metabox in product editing page
     *
     * @since 2.9.8
     *
     * @return void
     */
    public static function add_abuse_report_meta_box() {
        add_meta_box( 'dokan_report_abuse_reports', __( 'Abuse Reports', 'dokan' ), [ self::class, 'meta_box' ], 'product', 'normal', 'core' );
    }

    /**
     * Register scripts
     *
     * @since 3.7.4
     */
    public static function register_scripts() {
        list( $suffix, $version ) = dokan_get_script_suffix_and_version();

        wp_register_style( 'dokan-report-abuse-admin-single-product', DOKAN_REPORT_ABUSE_ASSETS . '/css/dokan-report-abuse-admin-single-product' . $suffix . '.css', [ 'dokan-fontawesome' ], $version );
        wp_register_script( 'dokan-report-abuse-admin-single-product', DOKAN_REPORT_ABUSE_ASSETS . '/js/dokan-report-abuse-admin-single-product' . $suffix . '.js', [ 'jquery' ], $version, true );
    }

    /**
     * Abuse Reports metabox
     *
     * @since 2.9.8
     *
     * @param \WP_Post $post
     *
     * @return void
     */
    public static function meta_box( $post ) {
        // Use minified libraries if SCRIPT_DEBUG is turned off
        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

        $reports = dokan_report_abuse_get_reports( [
            'product_id' => $post->ID,
        ] );

        dokan_report_abuse_template( 'report-abuse-admin-single-product', [
            'reports'     => $reports,
            'date_format' => get_option( 'date_format', 'F j, Y' ),
            'time_format' => get_option( 'time_format', 'g:i a' ),
        ] );

        wp_enqueue_style( 'dokan-report-abuse-admin-single-product' );
        wp_enqueue_script( 'dokan-report-abuse-admin-single-product' );

        wp_localize_script( 'dokan-report-abuse-admin-single-product', 'dokanReportAbuse', [
            'rest' => [
                'root'    => esc_url_raw( get_rest_url() ),
                'nonce'   => wp_create_nonce( 'wp_rest' ),
            ],
            'i18n' => [
                'delete'              => esc_html__( 'Delete', 'dokan' ),
                'deleting'            => esc_html__( 'Deleting', 'dokan' ),
                'deletedSuccessfully' => esc_html__( 'Report successfully deleted.', 'dokan' ),
                'confirmDelete'       => esc_html__( 'Are you sure you want to delete this report', 'dokan' ),
            ]
        ] );
    }
}
