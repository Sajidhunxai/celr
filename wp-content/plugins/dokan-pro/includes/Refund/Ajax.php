<?php

namespace WeDevs\DokanPro\Refund;

use Exception;
use WP_Error;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\Dokan\Traits\AjaxResponseError;

class Ajax {

    use AjaxResponseError;

    /**
     * Create refund request Ajax hook
     *
     * @since 3.0.0
     *
     * @param array $data
     *
     * @return array
     */
    public static function create_refund_request( $data ) {
        $map_params = [
            'line_item_qtys'         => 'item_qtys',
            'line_item_totals'       => 'item_totals',
            'line_item_tax_totals'   => 'item_tax_totals',
            'restock_refunded_items' => 'restock_items',
            'api_refund'             => 'method',
        ];

        foreach ( $map_params as $request_param => $refund_param ) {
            if ( isset( $data[ $request_param ] ) ) {
                $data[ $refund_param ] = $data[ $request_param ];
            }
        }

        $request = new Request( $data );

        $request->set_required(
            [
                'order_id'      => _x( 'Order ID', 'Refund request required parameter', 'dokan' ),
                'refund_amount' => _x( 'Refund amount', 'Refund request required parameter', 'dokan' ),
            ]
        );

        $request->sanitize();

        if ( $request->has_error() ) {
            throw new DokanException( $request->get_error() );
        }

        $request->validate();

        if ( $request->has_error() ) {
            throw new DokanException( $request->get_error() );
        }

        $refund = dokan_pro()->refund->create( $request->get_model()->get_data() );

        if ( is_wp_error( $refund ) ) {
            throw new DokanException( $refund );
        }

        return $refund;
    }

    /**
     * Insert refund request via ajax
     *
     * @since 2.4.11
     * @since 3.0.0 Refactor with new refund api
     *
     * @return void
     */
    public static function dokan_refund_request( $exception_handler = null ) {
        check_ajax_referer( 'order-item', 'security' );

        try {
            $post = wp_unslash( $_POST );

            $refund = self::create_refund_request( $post );

            $message = apply_filters( 'dokan_pro_refund_ajax_refund_request_message', __( 'Refund request submitted.', 'dokan' ) );

            do_action( 'dokan_refund_requested', $refund->get_order_id() );

            wp_send_json_success(
                [
                    'refund'  => $refund->get_data(),
                    'message' => $message,
                ], 201
            );
        } catch ( Exception $e ) {
            if ( ! $exception_handler ) {
                self::send_response_error( $e );
            } else {
                call_user_func( $exception_handler, $e );
            }
        }
    }

    /**
     * Intercept wc ajax request from wp-admin product edit page
     *
     * @since 2.4.11
     * @since 3.0.0 Refactor with new refund api
     *
     * @return void
     */
    public static function intercept_wc_ajax_request() {
        $removed = remove_action( 'wp_ajax_woocommerce_refund_line_items', [ 'WC_AJAX', 'refund_line_items' ], 10 );

        if ( ! $removed ) {
            return;
        }

        self::dokan_refund_request( [ self::class, 'wc_ajax_request_error_handler' ] );
    }

    /**
     * Exception handler for WC error response
     *
     * @since 3.0.0
     *
     * @param \Exception $e
     *
     * @return void
     */
    public static function wc_ajax_request_error_handler( Exception $e ) {
        $message = __( 'Something went wrong', 'dokan' );

        if ( $e instanceof DokanException ) {
            $error_code = $e->get_error_code();

            if ( $error_code instanceof WP_Error ) {
                $message = implode( ' ', $error_code->get_error_messages() );
            } else {
                $message = $e->get_message();
            }
        }

        wp_send_json_error(
            [
                'error' => $message,
            ]
        );
    }
}
