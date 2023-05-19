<?php

namespace WeDevs\DokanPro\Refund;

use WP_Error;
use WeDevs\Dokan\Abstracts\DokanModel;
use WeDevs\Dokan\Cache;

class Refund extends DokanModel {

    /**
     * The model data
     *
     * @since 3.0.0
     *
     * @var array
     */
    protected $data = [];

    /**
     * Class constructor
     *
     * @since 3.0.0
     *
     * @param array $data
     */
    public function __construct( $data = [] ) {
        $defaults = [
            'id'              => 0,
            'order_id'        => 0,
            'seller_id'       => 0,
            'refund_amount'   => 0,
            'refund_reason'   => '',
            'item_qtys'       => null,
            'item_totals'     => null,
            'item_tax_totals' => null,
            'restock_items'   => null,
            'date'            => current_time( 'mysql' ),
            'status'          => 0,
            'method'          => '0',
        ];

        $data = wp_parse_args( $data, $defaults );

        $this->set_data( $data );
    }

    /**
     * Set model data
     *
     * @since 3.0.0
     *
     * @param array $data
     *
     * @return void
     */
    protected function set_data( $data ) {
        $data = wp_unslash( $data );

        $this->set_id( $data['id'] )
            ->set_order_id( $data['order_id'] )
            ->set_seller_id( $data['seller_id'] )
            ->set_refund_amount( $data['refund_amount'] )
            ->set_refund_reason( $data['refund_reason'] )
            ->set_item_qtys( $data['item_qtys'] )
            ->set_item_totals( $data['item_totals'] )
            ->set_item_tax_totals( $data['item_tax_totals'] )
            ->set_restock_items( $data['restock_items'] )
            ->set_date( $data['date'] )
            ->set_status( $data['status'] )
            ->set_method( $data['method'] );
    }

    /**
     * Set `id` property
     *
     * @since 3.0.0
     *
     * @param int $set_id
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_id( $id ) {
        $this->data['id'] = $id;
        return $this;
    }

    /**
     * Set `order_id` property
     *
     * @since 3.0.0
     *
     * @param int $set_order_id
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_order_id( $order_id ) {
        $this->data['order_id'] = $order_id;
        return $this;
    }

    /**
     * Set `seller_id` property
     *
     * @since 3.0.0
     *
     * @param int $set_seller_id
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_seller_id( $seller_id ) {
        $this->data['seller_id'] = $seller_id;
        return $this;
    }

    /**
     * Set `refund_amount` property
     *
     * @since 3.0.0
     *
     * @param string $set_refund_amount
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_refund_amount( $refund_amount ) {
        $this->data['refund_amount'] = $refund_amount;
        return $this;
    }

    /**
     * Set `refund_reason` property
     *
     * @since 3.0.0
     *
     * @param string $set_refund_reason
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_refund_reason( $refund_reason ) {
        $this->data['refund_reason'] = $refund_reason;
        return $this;
    }

    /**
     * Set `item_qtys` property
     *
     * @since 3.0.0
     *
     * @param array $set_item_qtys
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_item_qtys( $item_qtys ) {
        $this->data['item_qtys'] = $item_qtys;
        return $this;
    }

    /**
     * Set `item_totals` property
     *
     * @since 3.0.0
     *
     * @param array $set_item_totals
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_item_totals( $item_totals ) {
        $this->data['item_totals'] = $item_totals;
        return $this;
    }

    /**
     * Set `item_tax_totals` property
     *
     * @since 3.0.0
     *
     * @param array $set_item_tax_totals
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_item_tax_totals( $item_tax_totals ) {
        $this->data['item_tax_totals'] = $item_tax_totals;
        return $this;
    }

    /**
     * Set `restock_items` property
     *
     * @since 3.0.0
     *
     * @param array $set_restock_items
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_restock_items( $restock_items ) {
        $this->data['restock_items'] = $restock_items;
        return $this;
    }

    /**
     * Set `date` property
     *
     * @since 3.0.0
     *
     * @param string $set_date
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_date( $date ) {
        $this->data['date'] = $date;
        return $this;
    }

    /**
     * Set `status` property
     *
     * @since 3.0.0
     *
     * @param string $set_status
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_status( $status ) {
        $this->data['status'] = $status;
        return $this;
    }

    /**
     * Set `method` property
     *
     * @since 3.0.0
     *
     * @param string $set_method
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function set_method( $method ) {
        $this->data['method'] = $method;
        return $this;
    }

    /**
     * Get `id` property
     *
     * @since 3.0.0
     *
     * @return int
     */
    public function get_id() {
        return $this->data['id'];
    }

    /**
     * Get `order_id` property
     *
     * @since 3.0.0
     *
     * @return int
     */
    public function get_order_id() {
        return $this->data['order_id'];
    }

    /**
     * Get `seller_id` property
     *
     * @since 3.0.0
     *
     * @return int
     */
    public function get_seller_id() {
        return $this->data['seller_id'];
    }

    /**
     * Get `refund_amount` property
     *
     * @since 3.0.0
     *
     * @return string
     */
    public function get_refund_amount() {
        return $this->data['refund_amount'];
    }

    /**
     * Get `refund_reason` property
     *
     * @since 3.0.0
     *
     * @return string
     */
    public function get_refund_reason() {
        return $this->data['refund_reason'];
    }

    /**
     * Get `item_qtys` property
     *
     * @since 3.0.0
     *
     * @return array
     */
    public function get_item_qtys() {
        return $this->data['item_qtys'];
    }

    /**
     * Get `item_totals` property
     *
     * @since 3.0.0
     *
     * @return array
     */
    public function get_item_totals() {
        return $this->data['item_totals'];
    }

    /**
     * Get `item_tax_totals` property
     *
     * @since 3.0.0
     *
     * @return array
     */
    public function get_item_tax_totals() {
        return $this->data['item_tax_totals'];
    }

    /**
     * Get `restock_items` property
     *
     * @since 3.0.0
     *
     * @return array
     */
    public function get_restock_items() {
        return $this->data['restock_items'];
    }

    /**
     * Get `date` property
     *
     * @since 3.0.0
     *
     * @return string
     */
    public function get_date() {
        return $this->data['date'];
    }

    /**
     * Get `status` property
     *
     * @since 3.0.0
     *
     * @return string
     */
    public function get_status() {
        return $this->data['status'];
    }

    /**
     * Get `status_name` property
     *
     * @since 3.0.0
     *
     * @return string
     */
    public function get_status_name() {
        $status_name = dokan_pro()->refund->get_status_names();
        return $status_name[ $this->get_status() ];
    }

    /**
     * Get `method` property
     *
     * @since 3.0.0
     *
     * @return string
     */
    public function get_method() {
        return $this->data['method'];
    }

    /**
     * Prepare model for DB insertion
     *
     * @since 3.0.0
     * @since 3.4.2 Refund method changed to `1` for API, `0` for manual.
     *
     * @return array
     */
    protected function prepare_for_db() {
        $data = $this->get_data();

        $data['item_qtys']       = is_array( $data['item_qtys'] ) ? json_encode( $data['item_qtys'] ) : null;
        $data['item_totals']     = is_array( $data['item_totals'] ) ? json_encode( $data['item_totals'] ) : null;
        $data['item_tax_totals'] = is_array( $data['item_tax_totals'] ) ? json_encode( $data['item_tax_totals'] ) : null;

        // we are setting WC provided method `true` or `false` to `1` or `0`
        $data['method'] = dokan_validate_boolean( $data['method'] ) ? '1' : '0';

        return $data;
    }

    /**
     * Save a model
     *
     * @since 3.0.0
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function save() {
        if ( ! $this->get_id() ) {
            return $this->create();
        } else {
            return $this->update();
        }
    }

    /**
     * Create a model
     *
     * @since 3.0.0
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    protected function create() {
        global $wpdb;

        unset( $this->data['id'] );

        $data = $this->prepare_for_db();

        $inserted = $wpdb->insert(
            $wpdb->dokan_refund,
            $data,
            [ '%d', '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' ]
        );

        if ( $inserted !== 1 ) {
            return new WP_Error( 'dokan_refund_create_error', __( 'Could not create new refund', 'dokan' ) );
        }

        $refund = dokan_pro()->refund->get( $wpdb->insert_id );

        /**
         * Fires after created a refund request
         *
         * @since 3.0.0
         *
         * @param \WeDevs\DokanPro\Refund\Refund $refund
         */
        do_action( 'dokan_refund_request_created', $refund );

        return $refund;
    }

    /**
     * Update a model
     *
     * @since 3.0.0
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    protected function update() {
        global $wpdb;

        $data = $this->prepare_for_db();

        $updated = $wpdb->update(
            $wpdb->dokan_refund,
            [
                'order_id'        => $data['order_id'],
                'seller_id'       => $data['seller_id'],
                'refund_amount'   => $data['refund_amount'],
                'refund_reason'   => $data['refund_reason'],
                'item_qtys'       => $data['item_qtys'],
                'item_totals'     => $data['item_totals'],
                'item_tax_totals' => $data['item_tax_totals'],
                'restock_items'   => $data['restock_items'],
                'date'            => $data['date'],
                'status'          => $data['status'],
                'method'          => $data['method'],

            ],
            [ 'id' => $this->get_id() ],
            [ '%d', '%d', '%f', '%s', '%s', '%s', '%s', '%s', '%s', '%d', '%s' ],
            [ '%d' ]
        );

        if ( $updated !== 1 ) {
            return new WP_Error( 'dokan_refund_update_error', __( 'Could not update refund', 'dokan' ) );
        }

        /**
         * Action based on refund status
         *
         * @since 3.0.0
         *
         * @param \WeDevs\Dokan\Refund\Refund $this
         */
        do_action( 'dokan_refund_request_' . dokan_pro()->refund->get_status_name( $this->get_status() ), $this );

        /**
         * Fires after update a refund
         *
         * @since 3.0.0
         *
         * @param \WeDevs\Dokan\Refund\Refund $this
         */
        do_action( 'dokan_refund_updated', $this );

        return $this;
    }

    /**
     * Delete a model
     *
     * @since 3.0.0
     *
     * @return \WeDevs\DokanPro\Refund\Refund
     */
    public function delete() {
        global $wpdb;

        $deleted = $wpdb->delete(
            $wpdb->dokan_refund,
            [ 'id' => $this->data['id'] ],
            [ '%d' ]
        );

        if ( ! $deleted ) {
            return new WP_Error( 'dokan_pro_refund_error_delete', __( 'Could not delete refund request', 'dokan' ) );
        }

        /**
         * Fires after delete a refund
         *
         * @since 3.0.0
         *
         * @param array $data
         */
        do_action( 'dokan_pro_refund_deleted', $this );

        return $this;
    }

    /**
     * Approve a refund
     *
     * @since 3.0.0
     * @param array $args
     * @return \WeDevs\DokanPro\Refund\Refund|WP_Error
     * @throws \Exception
     */
    public function approve( $args = [] ) {
        global $wpdb;

        if ( ! dokan_pro()->refund->is_approvable( $this->get_order_id() ) ) {
            return new WP_Error( 'dokan_pro_refund_error_approve', __( 'This refund is not allowed to approve', 'dokan' ) );
        }

        $order                  = wc_get_order( $this->get_order_id() );
        $api_refund             = dokan_validate_boolean( $this->get_method() );
        $restock_refunded_items = dokan_validate_boolean( $this->get_restock_items() );
        $vendor_refund          = 0;
        $tax_refund             = 0;
        $shipping_refund        = 0;
        $current_user           = is_user_logged_in() ? wp_get_current_user() : '';
        $approved_by            = ! empty( $current_user ) ? $current_user->get( 'user_nicename' ) : 'admin';
        $payment_method_title   = $order->get_payment_method_title();
        $shipping_fee_recipient = dokan_get_option( 'shipping_fee_recipient', 'dokan_general', 'seller' );
        $tax_fee_recipient      = dokan_get_option( 'tax_fee_recipient', 'dokan_general', 'seller' );

        // Prepare line items which we are refunding.
        $line_items = [];
        $item_ids   = array_unique( array_merge( array_keys( $this->get_item_qtys(), $this->get_item_totals(), true ) ) );

        foreach ( $item_ids as $item_id ) {
            $line_items[ $item_id ] = array(
                'qty'          => 0,
                'refund_total' => 0,
                'refund_tax'   => array(),
            );
        }

        foreach ( $this->get_item_qtys() as $item_id => $qty ) {
            $line_items[ $item_id ]['qty'] = max( $qty, 0 );
        }

        // If `_dokan_admin_fee` is found means, the commission has been calculated for this order without the `Dokan_Commission` class.
        // So we'll calculate refund without using the `Dokan_Commission` class to keep backward compatability.
        if ( get_post_meta( $this->get_order_id(), '_dokan_admin_fee', true ) ) {
            foreach ( $this->get_item_totals() as $item_id => $total ) {
                $item = $order->get_item( $item_id );

                if ( 'line_item' === (string) $item['type'] ) {
                    $percentage_type    = dokan_get_commission_type( $this->get_seller_id(), $item['product_id'] );
                    $vendor_percentage  = dokan_get_seller_percentage( $this->get_seller_id(), $item['product_id'] );
                    $vendor_refund      += $percentage_type === 'percentage' ? (float) ( $total * $vendor_percentage ) / 100 : (float) ( $total * ( ( $item['subtotal'] - $vendor_percentage ) / $item['subtotal'] ) );
                }

                $line_items[ $item_id ]['refund_total'] = wc_format_decimal( $total );
            }
        } else {
            // Set `order_id` so that `Dokan_Commission::prepare_for_calculation()` method can access the intended WC_Order.
            dokan()->commission->set_order_id( $this->get_order_id() );

            foreach ( $this->get_item_totals() as $item_id => $requested_refund ) {
                $item                                 = $order->get_item( $item_id );
                $line_items[ $item_id ]['refund_total'] = wc_format_decimal( $requested_refund );

                if ( 'line_item' === $item->get_type() ) {
                    $existing_refunds = $order->get_total_refunded_for_item( $item_id );

                    // If quantity of line_item is more than one, set `line_item_quantity` to increase commission by quantity for flat commission type.
                    if ( $item->get_quantity() > 1 ) {
                        dokan()->commission->set_order_qunatity( $item->get_quantity() );
                    }

                    // On order refund, set `_dokan_item_total` to `item->get_total()` so that `flat commission rate && additional_fee` can be splited properly among all the line_items.
                    update_post_meta( $this->get_order_id(), '_dokan_item_total', $item->get_total() );

                    $vendor_earning  = dokan()->commission->get_earning_by_product( $item['product_id'], 'seller', $item->get_total() - $existing_refunds );
                    $line_item_total = $item->get_total() - $existing_refunds;

                    if ( ! $line_item_total || is_wp_error( $vendor_earning ) ) {
                        continue;
                    }

                    $vendor_percentage = ( $vendor_earning * 100 ) / $line_item_total;

                    if ( $requested_refund ) {
                        $vendor_refund += ( $requested_refund * $vendor_percentage ) / 100;
                    }
                }

                if ( 'shipping' === $item->get_type() ) {
                    $shipping_refund += $requested_refund;
                }
            }
        }

        foreach ( $this->get_item_tax_totals() as $item_id => $tax_totals ) {
            foreach ( $tax_totals as $total_tax_key => $total_tax ) {
                $tax_refund += $total_tax;
                $line_items[ $item_id ]['refund_tax'][ $total_tax_key ] = wc_format_decimal( $total_tax );
            }
        }

        /**
         * Set auto process API refund for main order.
         *
         * @since 3.3.7
         *
         * @param bool $api_refund
         * @param Refund $this
         */
        $api_refund = apply_filters( 'dokan_pro_auto_process_api_refund', $api_refund, $this );
        $arr = [
            'amount'         => $this->get_refund_amount(),
            'reason'         => $this->get_refund_reason(),
            'order_id'       => $this->get_order_id(),
            'line_items'     => $line_items,
            'refund_payment' => dokan_is_sub_order( $this->get_order_id() ) ? false : $api_refund,
            'restock_items'  => $restock_refunded_items,
        ];

        /*
         * First, Create the refund object for order or suborder depending on condition.
         * If it is sub order, Only a refund record will be created.
         * No request will be sent to payment processor.
         */
        $refund = wc_create_refund( $arr );

        if ( is_wp_error( $refund ) ) {
            // translators: 1: Order number.
            dokan_log( sprintf( __( 'Refund processing for the order #%d failed.', 'dokan' ), $this->get_order_id() ) );
            $this->cancel();
            return new WP_Error( 'dokan_pro_refund_error_processing', __( 'This refund is failed to process.', 'dokan' ) );
        }

        /**
         * If refund is a sub-order, then create a refund for parent order also.
         * It's just for keeping a track of refund amount.
         */
        if ( dokan_is_sub_order( $this->get_order_id() ) ) {

            /*
             * Here the parent order is being refunded. So, we need to remove the hook for order status changed
             * so that it doesn't traverse the commission calculation process unnecessarily. Also, if it is
             * recalculated, there will more likely be unexpected vendor earning and gateway fee values which
             * will cause incorrect amounts in orders, reports and logs.
             *
             * @since DOKAN_SINCE
             */
            remove_action( 'woocommerce_order_status_changed', [ dokan()->commission, 'calculate_gateway_fee' ], 100 );

            /**
             * Stock management for a sub-order is handled from parent order.
             * We need to pass the refund line items for the parent order so that restock order item function work correctly.
             * Shipping refund line items would be difficult to map out with parent shipping line item, so we are intentionally leaving it out.
             */
            $line_items_product_map = [];
            foreach ( $order->get_items( 'line_item' ) as $item ) {
                if ( ! array_key_exists( $item->get_id(), $line_items ) ) {
                    continue;
                }

                $line_items_product_map[ $item['product_id'] ] = $item->get_id();
            }

            $parent_order_id   = wp_get_post_parent_id( $this->get_order_id() );
            $parent_order      = wc_get_order( $parent_order_id );
            $parent_line_items = [];

            foreach ( $parent_order->get_items( 'line_item' ) as $item ) {
                if ( ! array_key_exists( $item['product_id'], $line_items_product_map ) ) {
                    continue;
                }

                $line_item_id                         = $line_items_product_map[ $item['product_id'] ];
                $parent_line_items[ $item->get_id() ] = $line_items[ $line_item_id ];
            }

            // Create the refund object for parent order.
            $parent_refund = wc_create_refund(
                [
                    'amount'         => $this->get_refund_amount(),
                    'reason'         => $this->get_refund_reason(),
                    'order_id'       => $parent_order_id,
                    'line_items'     => $parent_line_items,
                    'refund_payment' => $api_refund,
                    'restock_items'  => true,
                ]
            );

            /*
             * As the refund is completed here, we can add the removed hook back
             * so that it doesn't affect other execution.
             */
            add_action( 'woocommerce_order_status_changed', [ dokan()->commission, 'calculate_gateway_fee' ], 100 );

            if ( is_wp_error( $parent_refund ) ) {
                // Delete the refund which is created for the order/sub-order.
                $refund->delete();

                // translators: 1: Order number.
                dokan_log( sprintf( __( 'Refund processing for the suborder #%d failed.', 'dokan' ), $this->get_order_id() ) );
                $this->cancel();
                return new WP_Error( 'dokan_pro_refund_error_processing', __( 'This refund is failed to process.', 'dokan' ) );
            }

            $payment_method_title = $parent_order->get_payment_method_title();
            $parent_order->add_order_note(
                sprintf(
                    // translators: 1: Payment gateway name 2: Refund Reason 3:Suborder ID 4: Approved by.
                    __( 'Refund Processed via %1$s – Reason: %2$s - Suborder %3$d - Approved by %4$s', 'dokan' ),
                    $api_refund || ! empty( $args ) ? $payment_method_title : __( 'Manual Processing', 'dokan' ),
                    $parent_refund->get_reason(),
                    $this->get_order_id(),
                    $approved_by
                )
            );
        }

        // Add refund note
        $order->add_order_note(
            sprintf(
                // translators: 1: Refund amount 2: Payment gateway name 3: Refund reason.
                __( 'Refunded %1$s via %2$s – Reason: %3$s ', 'dokan' ),
                $refund->get_formatted_refund_amount(),
                $api_refund || ! empty( $args ) ? $payment_method_title : __( 'Manual Processing', 'dokan' ),
                $refund->get_reason()
            )
        );

        if ( 'seller' === $shipping_fee_recipient ) {
            $vendor_refund += $shipping_refund;
        }

        if ( 'seller' === $tax_fee_recipient ) {
            $vendor_refund += $tax_refund;
        }

        /**
         * @since 3.3.0 add filter dokan_refund_approve_vendor_refund_amount
         * @param $vendor_refund float vendor refund amount
         * @param $this \WeDevs\DokanPro\Refund\Refund
         * @param $args array
         */
        $vendor_refund = apply_filters( 'dokan_refund_approve_vendor_refund_amount', $vendor_refund, $args, $this );

        /**
         * @since 3.3.0
         * @param $this \WeDevs\DokanPro\Refund\Refund
         * @param $args array
         * @param $vendor_refund float
         */
        do_action( 'dokan_refund_approve_before_insert', $this, $args, $vendor_refund );

        /**
         * @since 3.3.2 filter dokan_refund_insert_into_vendor_balance added
         * @param bool true return false if you don't want to insert into vendor balance table
         * @param \WeDevs\DokanPro\Refund\Refund $this
         * @param array $args
         * @param float $vendor_refund
         */
        if ( apply_filters( 'dokan_refund_insert_into_vendor_balance', true, $this, $args, $vendor_refund ) ) {
            $wpdb->insert(
                $wpdb->dokan_vendor_balance,
                [
                    'vendor_id'     => $this->get_seller_id(),
                    'trn_id'        => $this->get_order_id(),
                    'trn_type'      => 'dokan_refund',
                    'perticulars'   => $this->get_refund_reason(),
                    'debit'         => 0,
                    'credit'        => $vendor_refund,
                    'status'        => 'approved',
                    'trn_date'      => current_time( 'mysql' ),
                    'balance_date'  => current_time( 'mysql' ),
                ],
                [
                    '%d',
                    '%d',
                    '%s',
                    '%s',
                    '%f',
                    '%f',
                    '%s',
                    '%s',
                    '%s',
                ]
            );
        }

        // update the order table with new refund amount
        $order_data = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM $wpdb->dokan_orders WHERE order_id = %d",
                $this->get_order_id()
            )
        );

        if ( isset( $order_data->order_total, $order_data->net_amount ) ) {
            $new_total_amount = $order_data->order_total - $this->get_refund_amount();
            $new_net_amount   = $order_data->net_amount - $vendor_refund;

            // we are not including gateway fee to net_amount, so this value is getting deducted
            /**
             * Issues with stripe refund:
             * 1. we are not deducting gateway fee from refund amount, so total refund amount for a
             * particular order number is greater than actual order amount stored in dokan_vendor_balance table
             * 2. We are storing net amount which vendor got from a particular order (eg: after deducting commission, gateway fee etc)
             * but in case of refund we are not deducting gateway fee, so net_amount fee is a negative value, hence it was effecting
             * calculation in various places.
             * 3. setting net_amount value to zero in case of negative balance solved this issue temporarily.
             * 4. In future we need to consider this gateway fee in case of refund and needs to use proper formatting to display refunded
             * amount both for gateway fees and application fees.
             */
            $new_net_amount = ( $new_net_amount < 0 ) ? 0.00 : $new_net_amount;

            // insert on dokan sync table
            $wpdb->update(
                $wpdb->dokan_orders,
                [
                    'order_total' => $new_total_amount,
                    'net_amount'  => $new_net_amount,
                ],
                [
                    'order_id' => $this->get_order_id(),
                ],
                [
                    '%f',
                    '%f',
                ],
                [
                    '%d',
                ]
            );
        }

        $order->add_order_note(
            sprintf(
                /* translators: 1) user name */
                __( 'Refund request approved by %1$s', 'dokan' ),
                $approved_by
            )
        );

        $this->set_status( dokan_pro()->refund->get_status_code( 'completed' ) );

        $refund = $this->save();

        //remove cache for seller earning
        $cache_key = "get_earning_from_order_table_{$this->get_order_id()}_seller";
        Cache::delete( $cache_key );

        // remove cache for seller earning
        $cache_key = "get_earning_from_order_table_{$this->get_order_id()}_admin";
        Cache::delete( $cache_key );

        if ( is_wp_error( $refund ) ) {
            return $refund;
        }

        /**
         * Fires after approve a refund request
         *
         * @since 3.0.0
         *
         * @since 3.3.0 added $args and $vendor_refund param
         *
         * @param \WeDevs\DokanPro\Refund\Refund $refund
         * @param array $args
         * @param float $vendor_refund
         */
        do_action( 'dokan_pro_refund_approved', $this, $args, $vendor_refund );

        return $this;
    }

    /**
     * Cancel a refund request
     *
     * @since 3.0.0
     * @since 3.3.6 Adding Order note to suborder and parent order.
     *
     * @return Refund|WP_Error
     */
    public function cancel() {
        $this->set_status( dokan_pro()->refund->get_status_code( 'cancelled' ) );

        $refund = $this->save();

        if ( is_wp_error( $refund ) ) {
            return $refund;
        }

        $order = wc_get_order( $refund->get_order_id() );

        if ( $order ) {
            $order_id = $order->get_id();

            $order->add_order_note(
                sprintf(
                    // translators: 1: Refund amount 2: Refund reason.
                    __( 'Refund Request for the amount: %1$s – Reason: %2$s - Got canceled.', 'dokan' ),
                    $refund->get_refund_amount(),
                    $refund->get_refund_reason()
                )
            );

            if ( dokan_is_sub_order( $order_id ) ) {
                $parent_order_id = wp_get_post_parent_id( $order_id );
                $parent_order    = wc_get_order( $parent_order_id );

                if ( $parent_order ) {
                    $parent_order->add_order_note(
                        sprintf(
                            // translators: 1: Suborder ID 2: Refund amount 2: Refund reason.
                            __( 'Refund Request for the Suborder #%1$s - Amount %2$s – Reason: %3$s - Got canceled.', 'dokan' ),
                            $order_id,
                            $refund->get_refund_amount(),
                            $refund->get_refund_reason()
                        )
                    );
                }
            }
        }

        /**
         * Fires after cancel a refund request
         *
         * @since 3.0.0
         *
         * @param Refund $refund
         */
        do_action( 'dokan_pro_refund_cancelled', $this );

        return $this;
    }

    /**
     * Check if refund is via API.
     *
     * @since 3.4.2
     *
     * @return bool
     */
    public function is_via_api() {
        return dokan_validate_boolean( $this->get_method() );
    }

    /**
     * Check if refund is manual.
     *
     * @since 3.4.2
     *
     * @return bool
     */
    public function is_manual() {
        return dokan_validate_boolean( $this->get_method() ) === false;
    }
}
