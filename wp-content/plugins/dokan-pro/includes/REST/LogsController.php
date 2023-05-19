<?php

namespace WeDevs\DokanPro\REST;

use WeDevs\DokanPro\Admin\ReportLogExporter;
use WP_REST_Server;
use WeDevs\Dokan\Abstracts\DokanRESTAdminController;

class LogsController extends DokanRESTAdminController {

    /**
     * Route name
     *
     * @var string
     */
    protected $base = 'logs';

    /**
     * Register all routes related with logs
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace, '/' . $this->base, [
				[
					'methods'             => WP_REST_Server::READABLE,
					'callback'            => [ $this, 'get_logs' ],
					'permission_callback' => [ $this, 'check_permission' ],
                    'args'                => array_merge(
                        $this->get_collection_params(),
                        $this->get_logs_params()
                    ),
				],
			]
        );

        register_rest_route(
            $this->namespace, '/' . $this->base . '/export', [
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'export_logs' ],
                    'permission_callback' => [ $this, 'check_permission' ],
                    'args'                => array_merge(
                        $this->get_collection_params(),
                        $this->get_logs_params()
                    ),
                ],
            ]
        );
    }

    /**
     * Retrieves the query params for the collections.
     *
     * @since 3.4.1
     *
     * @return array Query parameters for the collection.
     */
    public function get_logs_params() {
        return [
            'vendor_id' => [
                'description'       => 'Vendor IDs to filter form',
                'type'              => [ 'array', 'integer' ],
                'default'           => [],
                'validate_callback' => 'rest_validate_request_arg',
                'items'             => [
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
            'order_id' => [
                'description'       => 'Order IDs to filter form',
                'type'              => [ 'array', 'integer' ],
                'default'           => [],
                'validate_callback' => 'rest_validate_request_arg',
                'items'             => [
                    'type'              => 'integer',
                    'sanitize_callback' => 'absint',
                ],
            ],
            'order_status' => [
                'description' => 'Order status to filter form',
                'required'    => false,
                'type'        => 'string',
                'default'     => '',
            ],
            'orderby' => [
                'description' => 'Filter by column',
                'required'    => false,
                'type'        => 'string',
                'default'     => 'order_id',
            ],
            'order' => [
                'description' => 'Order by type',
                'required'    => false,
                'type'        => 'string',
                'enum'        => [ 'desc', 'asc' ],
                'default'     => 'desc',
            ],
            'return' => [
                'description' => 'How data will be returned',
                'type'        => 'string',
                'required'    => false,
                'enum'        => [ 'all', 'ids', 'count' ],
                'context'     => [ 'view' ],
                'default'     => 'all',
            ],
        ];
    }

    /**
     * Get all logs
     *
     * @since 2.9.4
     *
     * @return object
     */
    public function get_logs( $request ) {
        $params = wp_unslash( $request->get_params() );
        $items_count = dokan_pro()->reports->get_logs( array_merge( $params, [ 'return' => 'count' ] ) );

        if ( is_wp_error( $items_count ) ) {
            return $items_count->get_error_message();
        }

        if ( ! $items_count ) {
            wp_send_json_error( __( 'No logs found', 'dokan' ) );
        }

        $results  = dokan_pro()->reports->get_logs( $params );
        $logs     = $this->prepare_logs_data( $results );

        $response = rest_ensure_response( $logs );
        $response = $this->format_collection_response( $response, $request, $items_count );

        return $response;
    }

    /**
     * Export all logs, send a json response after writing chunk data in file
     *
     * @since 3.4.1
     *
     * @param $request
     */
    public function export_logs( $request ) {
        include_once DOKAN_PRO_INC . '/Admin/ReportLogExporter.php';

        $params = $request->get_params();
        $step   = isset( $params['page'] ) ? absint( $params['page'] ) : 1; // phpcs:ignore
        $logs   = $this->prepare_logs_data( dokan_pro()->reports->get_logs( $params ) );

        $exporter = new ReportLogExporter();
        $exporter->set_items( $logs );
        $exporter->set_page( $step );
        $exporter->set_limit( $params['per_page'] );
        $exporter->set_total_rows( dokan_pro()->reports->get_logs( array_merge( $params, [ 'return' => 'count' ] ) ) );
        $exporter->generate_file();

        if ( $exporter->get_percent_complete() >= 100 ) {
            wp_send_json_success(
                [
                    'step'       => 'done',
                    'percentage' => 100,
                    'url'        => add_query_arg(
                        [
                            'download-order-log-csv'  => wp_create_nonce( 'download-order-log-csv-nonce' ),
                        ], admin_url( 'admin.php' )
                    ),
                ], 200
            );
        } else {
            wp_send_json_success(
                [
                    'step'       => ++$step,
                    'percentage' => $exporter->get_percent_complete(),
                    'columns'    => $exporter->get_column_names(),
                ], 200
            );
        }

        exit();
    }

    /**
     * Prepare Log items for response
     *
     * @param mixed $results
     *
     * @return array
     */
    public function prepare_logs_data( $results ) {
        $logs     = [];
        $statuses = wc_get_order_statuses();

        foreach ( $results as $result ) {
            $order                   = wc_get_order( $result->order_id );
            $is_subscription_product = false;

            if ( ! $order ) {
                continue;
            }

            foreach ( $order->get_items() as $item ) {
                $product = $item->get_product();

                if ( $product && 'product_pack' === $product->get_type() ) {
                    $is_subscription_product = true;
                    break;
                }
            }

            $order_total    = $order->get_total();
            $has_refund     = $order->get_total_refunded() ? true : false;
            $total_shipping = $order->get_shipping_total() ? $order->get_shipping_total() : 0;

            $tax_totals = 0;
            if ( $order->get_tax_totals() ) :
                foreach ( $order->get_tax_totals() as $tax ) :
                    $tax_totals = $tax_totals + $tax->amount;
                endforeach;
            endif;

            /**
             * Payment gateway fee minus from admin commission earning
             * net amount is excluding gateway fee, so we need to deduct it from admin commission
             * otherwise admin commission will be including gateway fees
             */
            $is_subscription_product = apply_filters( 'dokan_log_exclude_commission', $is_subscription_product, $result );
            $processing_fee          = (float) $order->get_meta( 'dokan_gateway_fee' );
            $commission              = $is_subscription_product ? (float) $result->order_total : (float) $result->order_total - (float) $result->net_amount;

            if ( $processing_fee && $processing_fee > 0 ) {
                $commission = $commission - $processing_fee;
            }

            /**
             * In case of refund, we are not excluding gateway fee, in case of stripe full/partial refund net amount can be negative
             */
            if ( $commission < 0 ) {
                $commission = 0;
            }

            $dp = 2; // 2 decimal points

            $gateway_fee_paid_by = $order->get_meta( 'dokan_gateway_fee_paid_by', true );

            if ( ! empty( $processing_fee ) && empty( $gateway_fee_paid_by ) ) {
                /**
                 * @since 3.7.15 dokan_gateway_fee_paid_by meta key returns empty value if gateway fee is paid admin
                 */
                $gateway_fee_paid_by = $order->get_payment_method() === 'dokan-stripe-connect' ? 'admin' : 'seller';
            }

            $logs[] = [
                'order_id'             => $result->order_id,
                'vendor_id'            => $result->seller_id,
                'vendor_name'          => dokan()->vendor->get( $result->seller_id )->get_shop_name(),
                'previous_order_total' => wc_format_decimal( $order_total, $dp ),
                'order_total'          => wc_format_decimal( $result->order_total, $dp ),
                'vendor_earning'       => $is_subscription_product ? 0 : wc_format_decimal( $result->net_amount, $dp ),
                'commission'           => wc_format_decimal( $commission, $dp ),
                'dokan_gateway_fee'    => $processing_fee ? wc_format_decimal( $processing_fee, $dp ) : 0,
                'gateway_fee_paid_by'  => $gateway_fee_paid_by ? $gateway_fee_paid_by : '',
                'shipping_total'       => wc_format_decimal( $total_shipping, $dp ),
                'tax_total'            => wc_format_decimal( $tax_totals, $dp ),
                'status'               => $statuses[ $result->order_status ],
                'date'                 => $result->post_date,
                'has_refund'           => $has_refund,
            ];
        }

        return $logs;
    }
}
