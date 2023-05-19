<?php

namespace WeDevs\DokanPro\Refund;

use WeDevs\DokanPro\Refund\Ajax;

class Hooks {

    /**
     * Hooks related to Dokan Pro Refund
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function __construct() {
        add_action( 'wp_ajax_dokan_refund_request', [ Ajax::class, 'dokan_refund_request' ] );
        add_action( 'wp_ajax_woocommerce_refund_line_items', [ Ajax::class, 'intercept_wc_ajax_request' ], 1 );
        add_action( 'dokan_pro_refund_approved', [ self::class, 'after_refund_approved' ] );
        add_filter( 'dokan_refund_insert_into_vendor_balance', [ $this, 'exclude_cod_payment' ], 10, 2 );
        add_action( 'dokan_refund_request_created', [ $this, 'add_order_note_on_refund_request_create' ], 1, 1 );
    }

    /**
     * After refund approval hook
     *
     * @since 3.0.0
     *
     * @param \WeDevs\DokanPro\Refund\Refund $refund
     *
     * @return void
     */
    public static function after_refund_approved( $refund ) {
        $vendor       = dokan()->vendor->get( $refund->get_seller_id() );
        $vendor_email = $vendor->get_email();

        do_action( 'dokan_refund_processed_notification', $vendor_email, $refund->get_order_id(), 'approved', $refund->get_refund_amount(), $refund->get_refund_reason() );
    }

    /**
     * @param bool $ret
     * @param \WeDevs\DokanPro\Refund\Refund $refund
     *
     * @since 3.3.2
     *
     * @return bool
     */
    public function exclude_cod_payment( $ret, $refund ) {
        $order = wc_get_order( $refund->get_order_id() );

        // return if $order is not instance of WC_Order
        if ( ! $order instanceof \WC_Order ) {
            return $ret;
        }

        // if cod is not payment method return
        if ( 'cod' !== $order->get_payment_method() ) {
            return $ret;
        }

        /**
         * If `exclude_cod_payment` is enabled, don't include the fund in vendor's refund balance.
         */
        $exclude_cod_payment = 'on' === dokan_get_option( 'exclude_cod_payment', 'dokan_withdraw', 'off' );

        if ( $exclude_cod_payment ) {
            return false;
        }

        return $ret;
    }

    /**
     * Add an Order note on refund request create.
     *
     * @since 3.4.2
     *
     * @param Refund $refund
     *
     * @return void
     */
    public function add_order_note_on_refund_request_create( $refund ) {
        if ( ! ProcessAutomaticRefund::instance()->is_auto_refundable_gateway( $refund ) ) {
            return;
        }
        $order = wc_get_order( $refund->get_order_id() );

        if ( ! $order ) {
            return;
        }
        $order->add_order_note(
            // translators: 1:Refund request ID, 2: Formatted Refund amount, 3: Refund reason.
            sprintf( __( 'A new request for refund is placed for the admin approval - Refund request ID: #%1$s - Refund Amount: %2$s - Reason: %3$s', 'dokan' ), $refund->get_id(), wc_price( $refund->get_refund_amount() ), $refund->get_refund_reason() )
        );
    }
}
