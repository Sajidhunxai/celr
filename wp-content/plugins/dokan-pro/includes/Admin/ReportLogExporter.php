<?php

namespace WeDevs\DokanPro\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit();
}

/**
 * Include dependencies.
 */
if ( ! class_exists( 'WC_CSV_Batch_Exporter', false ) ) {
    include_once WC_ABSPATH . 'includes/export/abstract-wc-csv-batch-exporter.php';
}

/**
 * ReportLogExporter for Log Export
 *
 * @since 3.4.1
 *
 * @package dokan
 */
class ReportLogExporter extends \WC_CSV_Batch_Exporter {
    /**
     * Type of export used in filter names.
     *
     * @since 3.4.1
     *
     * @var string
     */
    protected $export_type = 'order';

    /**
     * Filename to export to.
     *
     * @since 3.4.1
     *
     * @var string
     */
    protected $filename = 'dokan-order-export.csv';

    /**
     * Query parameters
     *
     * @since 3.4.1
     *
     * @var array
     */
    protected $items = [];

    /**
     * Query parameters
     *
     * @since 3.4.1
     *
     * @var array
     */
    protected $total_rows = 0;

    /**
     * Decimal places
     *
     * @since 3.4.1
     *
     * @var int
     */
    protected $decimal_places = 2;

    /**
     * Get column names
     *
     * @since 3.4.1
     *
     * @return array
     */
    public function get_column_names() {
        return $this->column_names;
    }

    /**
     * Set items for export
     *
     * @since 3.4.1
     *
     * @param array $params
     */
    public function set_items( $items = [] ) {
        $this->items = $items;
    }

    /**
     * Set total rows
     *
     * @since 3.4.1
     *
     * @param array $params
     */
    public function set_total_rows( $total_rows ) {
        $this->total_rows = absint( $total_rows );
    }

    /**
     * Return an array of columns to export.
     *
     * @since 3.4.1
     *
     * @return array
     */
    public function get_default_column_names() {
        return apply_filters(
            'dokan_logs_export_columns', [
                'order_id'             => __( 'Order ID', 'dokan' ),
                'vendor_id'            => __( 'Vendor ID', 'dokan' ),
                'vendor_name'          => __( 'Vendor Name', 'dokan' ),
                'previous_order_total' => __( 'Previous Order Total', 'dokan' ),
                'order_total'          => __( 'Order Total', 'dokan' ),
                'vendor_earning'       => __( 'Vendor Earning', 'dokan' ),
                'commission'           => __( 'Commission', 'dokan' ),
                'dokan_gateway_fee'    => __( 'Gateway Fee', 'dokan' ),
                'gateway_fee_paid_by'  => __( 'Gateway Fee Paid By', 'dokan' ),
                'shipping_total'       => __( 'Shipping', 'dokan' ),
                'tax_total'            => __( 'Tax', 'dokan' ),
                'status'               => __( 'Status', 'dokan' ),
                'date'                 => __( 'Date', 'dokan' ),
            ]
        );
    }

    /**
     * Prepare formatted data to export
     *
     * @since 3.4.1
     */
    public function prepare_data_to_export() {
        $this->row_data = [];

        foreach ( $this->items as $item ) {
            $row = $this->generate_row_data( $item );
            if ( $row ) {
                $this->row_data[] = $row;
            }
        }
    }

    /**
     * Take an order and generate row data from it for export.
     *
     * @since 3.4.1
     *
     * @param $dokan_order
     *
     * @return array
     */
    protected function generate_row_data( $order_item ) {
        $columns = $this->get_column_names();
        $row     = [];

        foreach ( $columns as $column_id => $column_name ) {
            // Skip some columns if we're being selective.
            if ( ! $this->is_column_exporting( $column_id ) ) {
                continue;
            }

            $row[ $column_id ] = $this->get_column_value( $order_item, $column_id );
        }

        return $row;
    }

    /**
     * Get value from object by key
     *
     * @since 3.4.1
     *
     * @param $order_item
     * @param $key
     *
     * @return mixed
     */
    protected function get_column_value( $order_item, $key ) {
        return isset( $order_item[ $key ] ) ? $order_item[ $key ] : '';
    }

    /**
     * Get total % complete.
     *
     * @since 3.4.1
     * @return int
     */
    public function get_percent_complete() {
        return $this->total_rows ? absint( floor( ( $this->get_total_exported() / $this->total_rows ) * 100 ) ) : 100;
    }
}
