<?php


namespace WeDevs\DokanPro\Refund;

use WeDevs\Dokan\Traits\Singleton;
use WP_Error;

/**
 * Class AutomaticRefundProcess
 *
 * Processing refund request and refunding with API automatically for
 * non Dokan payment gateways.
 *
 * @since 3.3.7
 *
 * @package WeDevs\DokanPro\Refund
 */
class ProcessAutomaticRefund {
    use Singleton;

    /**
     * Payment gateways that are excluded from auto process API refund.
     *
     * @since 3.5.0
     *
     * @access private
     *
     * @var array
     */
    private $excluded_payment_gateways = array();

    /**
     * Private Constructor
     *
     * @access private
     *
     * @since 3.3.7
     * @since 3.5.0 Added `dokan_excluded_gateways_from_auto_process_api_refund` filter hook
     *                        while setting excluded payment gateways from auto process API refund.
     *
     * @return void
     */
    private function __construct() {
        $this->excluded_payment_gateways = apply_filters(
            'dokan_excluded_gateways_from_auto_process_api_refund',
            array(
                'dokan-moip-connect'       => __( 'Dokan Wirecard Connect', 'dokan' ),
                'dokan-stripe-connect'     => __( 'Dokan Stripe Connect', 'dokan' ),
                'dokan_paypal_adaptive'    => __( 'Dokan Paypal Adaptive Payment', 'dokan' ),
            )
        );

        add_action( 'dokan_refund_request_created', array( $this, 'auto_approve_api_refund_request' ) );
        add_filter( 'dokan_settings_selling_option_commission', array( $this, 'add_automatic_process_refund_request_settings_field' ) );
        add_filter( 'dokan_pro_auto_process_api_refund', array( $this, 'set_auto_process_api_refund' ), 10, 2 );
    }

    /**
     * Process Refund request after creation.
     *
     * If the refund auto process settings is `true` means it is a refund request for
     * API processing if the gateway allow it.
     *
     * @since 3.3.7
     * @since 3.4.2 Manual refund button support added. We are no longer automatically approving the refund request.
     *
     * @param Refund $refund Created refund request.
     *
     * @return void|WP_Error
     */
    public function auto_approve_api_refund_request( $refund ) {
        if ( $refund->is_manual() ) {
            return;
        }

        if ( ! $this->is_auto_refund_process_enabled() ) {
            return;
        }

        if ( ! $this->is_auto_refundable_gateway( $refund ) ) {
            return;
        }

        /**
         * Approve refund request after the request creation.
         *
         * @since 3.3.7
         *
         * @param bool $approve_allowed
         * @param Refund $refund
         */
        $approve_api_refund = apply_filters( 'dokan_pro_auto_approve_api_refund_request', false, $refund );
        if ( $approve_api_refund ) {
            try {
                $refund->approve();
            } catch ( \Exception $exception ) {
                // translators: %s Error message from exception.
                dokan_log( sprintf( __( 'Refund request could not be approved. Error: %s', 'dokan' ), $exception->getMessage() ) );
                return new WP_Error( 'dokan_pro_refund_error_processing', __( 'This refund is failed to process.', 'dokan' ) );
            }
        }
    }

    /**
     * Add automatic process refund request admin settings
     *
     * @since 3.3.7
     *
     * @param array $settings_fields
     *
     * @return array
     */
    public function add_automatic_process_refund_request_settings_field( $settings_fields ) {
        $settings_fields['automatic_process_api_refund'] = [
            'name'    => 'automatic_process_api_refund',
            'label'   => __( 'Process Refund via API', 'dokan' ),
            /* translators: %s: excluded payment gateways */
            'desc'    => sprintf(
                __( 'Automatically process refund from payment gateways when admin approves refund request. This settings doesn\'t interfere with any of Dokan\'s payment integrations.', 'dokan' ),
                implode( ', ', array_values( $this->excluded_payment_gateways ) )
            ),
            'type'    => 'switcher',
            'default' => 'off',
        ];
        return $settings_fields;
    }

    /**
     * Is auto refund request processing enabled from admin.
     *
     * @since 3.3.7
     *
     * @return bool
     */
    public function is_auto_refund_process_enabled() {
        $process_auto_refund = dokan_get_option(
            'automatic_process_api_refund',
            'dokan_selling',
            'off'
        );
        return ! ( 'off' === $process_auto_refund );
    }

    /**
     * Check if gateway can process refund.
     *
     * @since 3.3.7
     *
     * @param Refund $refund
     *
     * @return bool
     */
    public function is_auto_refundable_gateway( $refund ) {
        $order_id = $refund->get_order_id();

        // Check if it is sub order.
        if ( dokan_is_sub_order( $order_id ) ) {
            $order_id = wp_get_post_parent_id( $order_id );
        }

        $order = wc_get_order( $order_id );
        if ( ! $order ) {
            return false;
        }

        $all_gateways   = WC()->payment_gateways->get_available_payment_gateways();
        $payment_method = $order->get_payment_method();
        $gateway        = isset( $all_gateways[ $payment_method ] ) ? $all_gateways[ $payment_method ] : false;

        if ( ! $gateway || ! $gateway->supports( 'refunds' ) ) {
            return false;
        }

        /**
         * The payment methods that we do not want to auto approve the refund requests.
         *
         * @since 3.3.7
         *
         * @param array
         * @param Refund $refund
         */
        $not_allowed_payment_methods = apply_filters(
            'dokan_pro_exclude_auto_approve_api_refund_request',
            array_keys( $this->excluded_payment_gateways ),
            $refund
        );

        return ! in_array( $payment_method, $not_allowed_payment_methods, true );
    }

    /**
     * Set api_refund in refund.
     *
     * @since 3.3.7
     * @since 3.4.2 Manual refund button support added.
     *
     * @param bool $api_refund
     * @param Refund $refund
     *
     * @return bool
     */
    public function set_auto_process_api_refund( $api_refund, $refund ) {
        return $api_refund && $this->is_auto_refund_process_enabled() && $this->is_auto_refundable_gateway( $refund );
    }
}
