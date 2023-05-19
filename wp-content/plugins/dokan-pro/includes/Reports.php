<?php

namespace WeDevs\DokanPro;

use WeDevs\DokanPro\Admin\ReportLogExporter;

/**
 * Dokan Pro Report Class
 *
 * @since 2.4
 *
 * @package dokan
 */
class Reports {

    /**
     * Load automatically when class inistantiate
     *
     * @since 2.4
     *
     * @uses actions|filter hooks
     */
    public function __construct() {
        add_action( 'dokan_report_content_inside_before', array( $this, 'show_seller_enable_message' ) );
        add_filter( 'dokan_get_dashboard_nav', array( $this, 'add_reports_menu' ) );
        add_action( 'dokan_load_custom_template', array( $this, 'load_reports_template' ) );
        add_action( 'dokan_report_content_area_header', array( $this, 'report_header_render' ) );
        add_action( 'dokan_report_content', array( $this, 'render_review_content' ) );
        add_action( 'template_redirect', array( $this, 'handle_statement' ) );
        add_action( 'init', [ $this, 'download_log_export_file' ], 15 );
    }

    /**
     * Export statement
     *
     * @return void
     */
    public function handle_statement() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        if ( ! dokan_is_user_seller( get_current_user_id() ) ) {
            return;
        }

        if ( ! isset( $_GET['dokan_report_filter_nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['dokan_report_filter_nonce'] ) ), 'dokan_report_filter' ) ) {
            return;
        }

        if ( isset( $_GET['dokan_statement_export_all'] ) ) {
            $start_date = dokan_current_datetime()->modify( 'first day of this month' )->format( 'Y-m-d' );
            $end_date   = dokan_current_datetime()->format( 'Y-m-d' );

            if ( isset( $_GET['start_date_alt'] ) ) {
                $start_date = dokan_current_datetime()
                    ->modify( sanitize_text_field( wp_unslash( $_GET['start_date_alt'] ) ) )
                    ->format( 'Y-m-d' );
            }

            if ( isset( $_GET['end_date_alt'] ) ) {
                $end_date = dokan_current_datetime()
                    ->modify( sanitize_text_field( wp_unslash( $_GET['end_date_alt'] ) ) )
                    ->format( 'Y-m-d' );
            }

            $filename = 'Statement-' . dokan_current_datetime()->format( 'Y-m-d' );
            header( 'Content-Type: application/csv; charset=' . get_option( 'blog_charset' ) );
            header( "Content-Disposition: attachment; filename={$filename}.csv" );
            $currency = get_woocommerce_currency_symbol();
            $headers  = array(
                'date'         => __( 'Trn Date', 'dokan' ),
                'balance_date' => __( 'Balance Date', 'dokan' ),
                'id'           => __( 'ID', 'dokan' ),
                'type'         => __( 'Type', 'dokan' ),
                'debit'        => __( 'Debit', 'dokan' ),
                'credit'       => __( 'Credit', 'dokan' ),
                'balance'      => __( 'Balance', 'dokan' ),
            );

            foreach ( (array) $headers as $label ) {
                echo $label . ', ';
            }

            echo "\r\n";

            //calculate opening balance
            global $wpdb;
            $vendor = dokan()->vendor->get( dokan_get_current_user_id() );
            $opening_balance = $vendor->get_balance( false, dokan_current_datetime()->modify( $start_date . ' -1 days' )->format( 'Y-m-d' ) );
            $status = implode( "', '", dokan_withdraw_get_active_order_status() );

            $sql = "SELECT * from {$wpdb->prefix}dokan_vendor_balance WHERE vendor_id = %d AND DATE(balance_date) >= %s AND DATE(balance_date) <= %s AND ( ( trn_type = 'dokan_orders' AND status IN ('{$status}') ) OR trn_type IN ( 'dokan_withdraw', 'dokan_refund' ) ) ORDER BY balance_date";

            $statements = $wpdb->get_results( $wpdb->prepare( $sql, $vendor->id, $start_date, $end_date ) ); // phpcs:ignore

            echo $start_date . ', ';
            echo '--, ';
            echo '#--, ';
            echo 'Opening Balance, ';
            echo '--, ';
            echo '--, ';
            echo $opening_balance . ', ';
            echo "\r\n";

            $balance = $opening_balance;
            foreach ( $statements as $key => $statement ) {
                $balance += $statement->debit - $statement->credit;

                switch ( $statement->trn_type ) {
                    case 'dokan_orders':
                        $type = __( 'Order', 'dokan' );
                        break;

                    case 'dokan_withdraw':
                        $type = __( 'Withdraw', 'dokan' );
                        break;

                    case 'dokan_refund':
                        $type = __( 'Refund', 'dokan' );
                        break;
                }

                echo dokan_current_datetime()->modify( $statement->trn_date )->format( 'Y-m-d' ) . ', ';
                echo dokan_current_datetime()->modify( $statement->balance_date )->format( 'Y-m-d' ) . ', ';
                echo '#' . $statement->trn_id . ', ';
                echo $type . ', ';
                echo $statement->debit . ', ';
                echo $statement->credit . ', ';
                echo $balance . ', ';

                echo "\r\n";
            }

            exit();
        }
    }

    /**
     * Show Seller Enable Error Message
     *
     * @since 2.4
     *
     * @return void
     */
    public function show_seller_enable_message() {
        $user_id = get_current_user_id();

        if ( ! dokan_is_seller_enabled( $user_id ) ) {
            echo dokan_seller_not_enabled_notice();
        }
    }

    /**
     * Add Report Menu
     *
     * @since 2.4
     *
     * @param array $urls
     *
     * @return array
     */
    public function add_reports_menu( $urls ) {
        $urls['reports'] = array(
            'title' => __( 'Reports', 'dokan' ),
            'icon'  => '<i class="fas fa-chart-line"></i>',
            'url'   => dokan_get_navigation_url( 'reports' ),
            'pos'   => 60,
            'permission' => 'dokan_view_report_menu',
        );

        return $urls;
    }

    /**
     * Load Report Main Template
     *
     * @since 2.4
     *
     * @param  array $query_vars
     *
     * @return void
     */
    public function load_reports_template( $query_vars ) {
        if ( isset( $query_vars['reports'] ) ) {
            if ( ! current_user_can( 'dokan_view_review_menu' ) ) {
                dokan_get_template_part(
                    'global/dokan-error',
                    '',
                    [
                        'deleted' => false,
                        'message' => __( 'You have no permission to view review page', 'dokan' ),
                    ]
                );
                return;
            } else {
                dokan_get_template_part( 'report/reports', '', array( 'pro' => true ) );
                return;
            }
        }
    }

    /**
     * Render Report Header Template
     *
     * @since 2.4
     *
     * @return void
     */
    public function report_header_render() {
        dokan_get_template_part( 'report/header', '', array( 'pro' => true ) );
    }

    /**
     * Render Review Content
     *
     * @return void
     */
    public function render_review_content() {
        global $woocommerce;

        $charts  = dokan_get_reports_charts();
        $link    = dokan_get_navigation_url( 'reports' );
        $current = isset( $_GET['chart'] ) ? sanitize_text_field( wp_unslash( $_GET['chart'] ) ) : 'overview'; // phpcs:ignore

        dokan_get_template_part(
            'report/content', '', [
                'pro'     => true,
                'charts'  => $charts,
                'link'    => $link,
                'current' => $current,
            ]
        );
    }

    /**
     * Download exported log file
     *
     * @since 3.4.1
     */
    public function download_log_export_file() {
        if ( ! isset( $_GET['download-order-log-csv'] ) || ! wp_verify_nonce( $_GET['download-order-log-csv'], 'download-order-log-csv-nonce' ) ) { // phpcs:ignore
            return;
        }

        // export logs
        include_once DOKAN_PRO_INC . '/Admin/ReportLogExporter.php';
        $exporter = new ReportLogExporter();
        $exporter->export();
    }
}
