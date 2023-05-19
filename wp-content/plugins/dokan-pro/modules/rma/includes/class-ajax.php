<?php

/**
* Ajax handling class
*/
class Dokan_RMA_Ajax {

    /**
     * Load automatically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'wp_ajax_dokan-update-return-request', [ $this, 'update_status' ], 10 );
        add_action( 'wp_ajax_dokan-get-refund-order-data', [ $this, 'get_order_data' ], 10 );
        add_action( 'wp_ajax_dokan-get-coupon-order-data', [ $this, 'get_order_data' ], 10 );
        add_action( 'wp_ajax_dokan-send-refund-request', [ $this, 'send_refund_request' ], 10 );
        add_action( 'wp_ajax_dokan-send-coupon-request', [ $this, 'send_coupon_request' ], 10 );
    }

    /**
     * Update request status
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function update_status() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'dokan_rma_nonce' ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'dokan' ) );
        }

        if ( ! isset( $_POST['formData'] ) ) {
            wp_send_json_error( __( 'Something went wrong!', 'dokan' ) );
        }

        parse_str( $_POST['formData'], $data ); //phpcs:ignore.

        $request = new Dokan_RMA_Warranty_Request();
        $updated = $request->update( wc_clean( $data ) );

        if ( is_wp_error( $updated ) ) {
            wp_send_json_error( $updated->get_error_message() );
        }

        wp_send_json_success( [ 'message' => __( 'Status changed successfully', 'dokan' ) ] );
    }

    /**
     * Get order data for refund
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_order_data() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'dokan_rma_nonce' ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'dokan' ) );
        }

        if ( empty( $_POST['request_id'] ) ) {
            wp_send_json_error( __( 'Order id not found', 'dokan' ) );
        }

        $warranty_request = new Dokan_RMA_Warranty_Request();
        $request = $warranty_request->get( absint( $_POST['request_id'] ) );

        if ( ! $request ) {
            wp_send_json_error( __( 'Invalid Request', 'dokan' ) );
        }

        $wc_tax_enabled = ! empty( array_sum( wp_list_pluck( $request['items'], 'tax' ) ) );
        $price_placeholder = '0' . wc_get_price_decimal_separator() . '00'; // 0.00 or 0,00

        if ( ! empty( $request['items'] ) ) {
            ob_start();
            ?>
                <table class="dokan-table dokan-refund-item-list-table">
                    <thead>
                        <tr>
                            <th width="30%"><?php esc_html_e( 'Product', 'dokan' ); ?></th>
                            <th width="10%"><?php esc_html_e( 'Qty', 'dokan' ); ?></th>

                            <?php if ( $wc_tax_enabled ) : ?>
                                <th width="10%"><?php esc_html_e( 'Tax', 'dokan' ); ?></th>
                            <?php endif; ?>

                            <th width="15%"><?php esc_html_e( 'Subtotal', 'dokan' ); ?></th>

                            <?php if ( $wc_tax_enabled ) : ?>
                                <th width="15%"><?php esc_html_e( 'Tax Refund', 'dokan' ); ?></th>
                            <?php endif; ?>

                            <th width="20%"><?php esc_html_e( 'Subtotal Refund', 'dokan' ); ?></th>
<!--                            <th width="15%"></th>-->
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $request['items'] as $key => $item ) : ?>
                            <tr>
                                <td><a href="<?php echo esc_url( $item['url'] ); ?>"><?php echo esc_html( $item['title'] ); ?></a></td>
                                <td><?php echo esc_html( $item['quantity'] ); ?></td>

                                <?php if ( $wc_tax_enabled ) : ?>
                                <td><?php echo wc_price( $item['tax'] * $item['quantity'] ); ?></td>
                                <?php endif; ?>

                                <td><?php echo wc_price( $item['price'] * $item['quantity'] ); ?></td>

                                <?php if ( $wc_tax_enabled ) : ?>
                                    <td>
                                        <div style="position: relative;">
                                            <input type="text" data-max="<?php echo esc_attr( $item['tax'] * $item['quantity'] ); ?>" name="refund_tax[]" class="wc_input_price refund_item_amount dokan-w12 dokan-text-right" placeholder="<?php echo esc_attr( $price_placeholder ); ?>">
                                        </div>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <input type="hidden" name="item_id[]" value="<?php echo $item['item_id']; ?>">
                                    <div style="position: relative;">
                                        <input type="text" data-max="<?php echo esc_attr( $item['price'] * $item['quantity'] ); ?>" name="refund_amount[]" class="wc_input_price refund_item_amount dokan-w12 dokan-text-right" placeholder="<?php echo esc_attr( $price_placeholder ); ?>">
                                    </div>
                                    <input type="hidden" name="line_item_qtys[]" value="<?php echo esc_attr( $item['quantity'] ); ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <input type="hidden" name="refund_total_amount" value="0">
                <input type="hidden" name="request_id" value="<?php echo $request['id']; ?>">
                <input type="hidden" name="refund_order_id" value="<?php echo esc_attr( $request['order_id'] ); ?>">
                <input type="hidden" name="refund_vendor_id" value="<?php echo esc_attr( $request['vendor']['store_id'] ); ?>">
                <div class="dokan-popup-total-refund-amount dokan-right"><?php esc_html_e( 'Total Amount : ', 'dokan' ); ?> <strong><span><?php echo get_woocommerce_currency_symbol(); ?></span><span class="amount"><?php echo esc_html( $price_placeholder ); ?></span></strong></div>
                <div class="dokan-clearfix"></div>
            <?php
            $data = ob_get_clean();
            wp_send_json_success( $data );
        }

        wp_send_json_error( __( 'No Item found', 'dokan' ) );
    }

    /**
     * Send refund request
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function send_refund_request() {
        list( $data, $order, $request_data, $line_item_qty_array, $line_item_total_array, $line_item_tax_total_array ) = $this->validate_request();

        if ( dokan_pro()->refund->has_pending_request( absint( $data['refund_order_id'] ) ) ) {
            wp_send_json_error( __( 'You have already a processing refund request for this order.', 'dokan' ) );
        }

        $postdata = [
            'order_id'               => $data['refund_order_id'],
            'seller_id'              => $data['refund_vendor_id'],
            'refund_amount'          => $data['refund_total_amount'],
            // translators: 1:RMA request id.
            'refund_reason'          => sprintf( __( 'Warranty Request from Customer for RMA request #%1$s', 'dokan' ), $data['request_id'] ),
            'line_item_qtys'         => json_encode( $line_item_qty_array ), //phpcs:ignore.
            'line_item_totals'       => json_encode( $line_item_total_array ), //phpcs:ignore.
            'line_item_tax_totals'   => json_encode( $line_item_tax_total_array ), //phpcs:ignore.
            'api_refund'             => '1', // if the payment gateway supports then the Refund will be via API.
            'restock_refunded_items' => null,
            'status'                 => 0,
        ];

        try {
            $refund = \WeDevs\DokanPro\Refund\Ajax::create_refund_request( $postdata );
            do_action( 'dokan_rma_requested', absint( $data['refund_order_id'] ) );
            wp_send_json_success( __( 'Refund request successfully sent for admin approval.', 'dokan' ) );
        } catch ( Exception $e ) {
            \WeDevs\DokanPro\Refund\Ajax::wc_ajax_request_error_handler( $e );
        }
    }

    /**
     * Send coupon to customer
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function send_coupon_request() {
        list( $data, $order, $request_data, $line_item_qty_array, $line_item_total_array, $line_item_tax_total_array ) = $this->validate_request();

        try {
            $refund_amount = wc_format_decimal( $data['refund_total_amount'] );
            $vendor_id     = dokan_get_current_user_id();

            $coupon = new WC_Coupon();
            $coupon->set_code( dokan_rma_generate_coupon_code() );
            $coupon->set_amount( $refund_amount );
            $coupon->set_date_created( dokan_current_datetime()->setTimezone( new DateTimeZone( 'UTC' ) )->getTimestamp() );
            $coupon->set_date_expires( null );
            $coupon->set_discount_type( 'fixed_cart' );
            $coupon->set_description( '' );
            $coupon->set_usage_count( 0 );
            $coupon->set_individual_use( false );
            $coupon->set_excluded_product_ids( [] );
            $coupon->set_usage_limit( '1' );
            $coupon->set_usage_limit_per_user( '1' );
            $coupon->set_limit_usage_to_x_items( null );
            $coupon->set_free_shipping( false );
            $coupon->set_product_categories( [] );
            $coupon->set_excluded_product_categories( [] );
            $coupon->set_exclude_sale_items( false );
            $coupon->set_minimum_amount( '' );
            $coupon->set_maximum_amount( '' );
            $coupon->set_email_restrictions( [ $order->get_billing_email() ] );
            $coupon->set_used_by( [] );
            $coupon->set_virtual( false );

            /*
             * We need to include all the product IDs
             * of the vendor so that the coupon can be
             * used for the vendor's products.
             */
            $product_ids = dokan_coupon_get_seller_product_ids( $vendor_id );
            $coupon->set_product_ids( $product_ids );
            $coupon->save();

            $coupon_id = $coupon->get_id();
            wp_update_post(
                [
                    'ID'          => $coupon_id,
                    'post_author' => $vendor_id,
                ]
            );

            /*
             * This will allow the coupon to be used for new products
             * that will be created in future as well.
             */
            update_post_meta( $coupon_id, 'apply_new_products', 'yes' );

            dokan_update_warranty_request_status( absint( $data['request_id'] ), 'completed' );
            do_action( 'dokan_send_coupon_to_customer', $coupon, $data );

            wp_send_json_success( __( 'Coupon has been created successfully and send to customer email', 'dokan' ) );
        } catch ( Exception $exception ) {
            wp_send_json_error( __( 'Something is wrong, Please try again', 'dokan' ) );
        }
    }

    /**
     * Validate form submit request for rma refund or coupon form
     *
     * @since 3.4.0
     *
     * @return array
     */
    protected function validate_request() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'dokan_rma_nonce' ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'dokan' ) );
        }

        if ( ! isset( $_POST['formData'] ) ) {
            wp_send_json_error( __( 'Something went wrong!', 'dokan' ) );
        }

        parse_str( $_POST['formData'], $data ); //phpcs:ignore.

        if ( empty( $data['refund_order_id'] ) ) {
            wp_send_json_error( __( 'No order found', 'dokan' ) );
        }

        $order = wc_get_order( absint( $data['refund_order_id'] ) );

        if ( ! $order ) {
            wp_send_json_error( __( 'No order found', 'dokan' ) );
        }

        if ( empty( $data['refund_vendor_id'] ) ) {
            wp_send_json_error( __( 'No vendor found', 'dokan' ) );
        }

        if ( absint( $data['refund_vendor_id'] ) !== dokan_get_current_user_id() ) {
            wp_send_json_error( __( 'Error! This is not your request.', 'dokan' ) );
        }

        if ( wc_format_decimal( $data['refund_total_amount'] ) <= 0 ) {
            wp_send_json_error( __( 'Refund amount must be greater than 0', 'dokan' ) );
        }

        $warranty_request          = new Dokan_RMA_Warranty_Request();
        $request_data              = $warranty_request->get( absint( $data['request_id'] ) );
        $line_item_qty_array       = array();
        $line_item_total_array     = array();
        $line_item_tax_total_array = array();

        foreach ( $data['item_id'] as $key => $single_item_id ) {
            $single_item_id   = absint( $single_item_id );
            $line_item        = $request_data['items'][ $key ];
            $requested_qty    = $data['line_item_qtys'][ $key ];
            $requested_amount = ! empty( $data['refund_amount'][ $key ] ) ? wc_format_decimal( $data['refund_amount'][ $key ] ) : 0;
            $requested_tax    = ! empty( $data['refund_tax'][ $key ] ) ? wc_format_decimal( $data['refund_tax'][ $key ] ) : 0;
            $allowed_amount   = (float) $line_item['price'] * absint( $line_item['quantity'] );
            $allowed_tax      = (float) $line_item['tax'] * absint( $line_item['quantity'] );

            if ( $requested_amount > $allowed_amount ) {
                // translators: Product name.
                wp_send_json_error( sprintf( __( 'Invalid subtotal refund amount for product %s', 'dokan' ), $line_item['title'] ) );
            }

            if ( $requested_tax > $allowed_tax ) {
                // translators: Product name.
                wp_send_json_error( sprintf( __( 'Invalid tax refund amount for product %s', 'dokan' ), $line_item['title'] ) );
            }

            $line_item_qty_array[ $single_item_id ]       = absint( $requested_qty );
            $line_item_total_array[ $single_item_id ]     = $requested_amount;
            $line_item_tax_total_array[ $single_item_id ] = array( 1 => $requested_tax ); // need to revisit for multiple tax class support

            unset( $line_item, $requested_qty, $requested_amount, $requested_tax );
        }

        return array( $data, $order, $request_data, $line_item_qty_array, $line_item_total_array, $line_item_tax_total_array );
    }
}
