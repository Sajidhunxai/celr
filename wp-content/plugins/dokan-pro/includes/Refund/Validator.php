<?php

namespace WeDevs\DokanPro\Refund;

use WP_Error;

class Validator {

    /**
     * Validate id
     *
     * @since 3.0.0
     *
     * @param int $id
     *
     * @return bool
     */
    public static function validate_id( $id ) {
        if ( $id ) {
            $refund = dokan_pro()->refund->get( $id );

            if ( ! $refund ) {
                return new WP_Error( 'dokan_pro_refund_error_id', __( 'Invalid refund id.', 'dokan' ) );
            }
        }

        return true;
    }

    /**
     * Validate order_id
     *
     * @since 3.0.0
     *
     * @param int $id
     *
     * @return bool
     */
    public static function validate_order_id( $order_id ) {
        $order = wc_get_order( $order_id );

        if ( $order ) {
            return true;
        }

        return new WP_Error( 'dokan_pro_refund_error_order_id', __( 'Invalid order id.', 'dokan' ) );
    }

    /**
     * Validate refund_amount
     *
     * @since 3.0.0
     *
     * @param string                                          $id
     * @param \WeDevs\DokanPro\Refund\Request\WP_REST_Request $request
     *
     * @return bool
     */
    public static function validate_refund_amount( $refund_amount, $request ) {
        $refund_amount = Sanitizer::sanitize_refund_amount( $refund_amount );

        if ( $refund_amount <= 0 ) {
            return new WP_Error( 'dokan_pro_refund_error_refund_amount', __( 'Refund amount must be greater than zero.', 'dokan' ) );
        }

        $order = wc_get_order( $request['order_id'] );

        $max_allowed_refund = wc_format_decimal( $order->get_total() - $order->get_total_refunded(), wc_get_rounding_precision() );

        if ( $refund_amount > $max_allowed_refund ) {
            return new WP_Error( 'dokan_pro_refund_error_refund_amount', sprintf( __( 'Maximum allowed amount is %f.', 'dokan' ), $max_allowed_refund ) );
        }

        return true;
    }

    /**
     * Validate item_qtys
     *
     * @since 3.0.0
     *
     * @param array                                           $item_qtys
     * @param \WeDevs\DokanPro\Refund\Request\WP_REST_Request $request
     *
     * @return bool
     */
    public static function validate_item_qtys( $item_qtys, $request ) {
        $item_qtys = Sanitizer::sanitize_item_qtys( $item_qtys );

        if ( ! $item_qtys ) {
            return true;
        }

        $order            = wc_get_order( $request['order_id'] );
        $order_line_items = $order->get_items();

        foreach ( $item_qtys as $item_id => $item_qty ) {
            if ( ! isset( $order_line_items[ $item_id ] ) ) {
                return new WP_Error( 'dokan_pro_refund_error_item_qtys', sprintf( __( 'Invalid line item id %d', 'dokan' ), $item_id ) );
            }

            $order_line_item = $order_line_items[ $item_id ];

            if ( ! $item_qty ) {
                return new WP_Error( 'dokan_pro_refund_error_item_qtys', sprintf( __( 'Invalid line item quantity for item %s', 'dokan' ), $order_line_item->get_name() ) );
            }

            $order_line_item_qty = $order_line_item->get_quantity();

            if ( $item_qty > $order_line_item_qty ) {
                return new WP_Error( 'dokan_pro_refund_error_item_qtys', sprintf( __( 'Line item quantity must not exceed %d for item %s', 'dokan' ), $order_line_item_qty, $order_line_item->get_name() ) );
            }
        }

        return true;
    }

    /**
     * Validate item_totals
     *
     * @since 3.0.0
     *
     * @param array                                           $item_totals
     * @param \WeDevs\DokanPro\Refund\Request\WP_REST_Request $request
     *
     * @return bool|WP_Error
     */
    public static function validate_item_totals( $item_totals, $request ) {
        $item_totals = Sanitizer::sanitize_item_totals( $item_totals );

        if ( ! $item_totals ) {
            return true;
        }

        // We'll set `$order_line_items` with line_item, shipping and fees except tax.
        $order            = wc_get_order( $request['order_id'] );
        $order_line_items = array_replace( $order->get_items( 'line_item' ), $order->get_items( 'shipping' ), $order->get_items( 'fee' ) );

        foreach ( $item_totals as $item_id => $item_total ) {
            if ( ! isset( $order_line_items[ $item_id ] ) ) {
                return new WP_Error( 'dokan_pro_refund_error_item_totals', sprintf( __( 'Invalid line item id %d', 'dokan' ), $item_id ) );
            }

            $order_line_item       = $order_line_items[ $item_id ];
            $order_line_item_total = wc_format_decimal( $order_line_item->get_total(), wc_get_rounding_precision() );

            if ( $order->get_total_refunded_for_item( $item_id ) ) {
                $item_total = $item_total + $order->get_total_refunded_for_item( $item_id );
            }

            if ( $item_total > $order_line_item_total ) {
                return new WP_Error( 'dokan_pro_refund_error_item_totals', sprintf( __( 'Line item total must not exceed %s for item %s', 'dokan' ), $order_line_item_total, $order_line_item->get_name() ) );
            }
        }

        return true;
    }
}
