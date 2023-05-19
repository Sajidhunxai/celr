<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Processors;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WC_Order;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Support\UserMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Api\PaymentMethod;

/**
 * Class for processing orders.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Processors
 */
class Order {

    /**
     * Saves source to order.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     * @param object   $source
     *
     * @return void
     */
    public static function save_source( WC_Order $order, $source ) {
        if ( $source->customer ) {
            OrderMeta::update_customer_id( $order, $source->customer );
        }

        if ( $source->payment_method ) {
            OrderMeta::update_payment_method_id( $order, $source->payment_method );
        }

        OrderMeta::save( $order );

        /**
         * Hooks when payment method data are updated for an order.
         *
         * @since 3.7.8
         *
         * @param WC_Order              $order
         * @param \Stripe\PaymentMethod $payment_method
         */
        do_action( 'dokan_stripe_express_save_payment_method_data', $order, $source->payment_method_object );
    }

    /**
     * Get payment source from an order. This could be used in the future for
     * a subscription as an example, therefore using the current user ID would
     * not work when the customer won't be logged in.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return object{customer:string|false,payment_method:string|null,payment_method_object:\Stripe\PaymentMethod|false}
     */
    public static function prepare_source( $order = null ) {
        $stripe_customer   = Customer::set();
        $payment_method_id = '';
        $payment_method    = false;

        if ( $order instanceof WC_Order ) {
            $stripe_customer_id = self::get_stripe_customer_id_from_order( $order );

            if ( $stripe_customer_id ) {
                $stripe_customer->set_id( $stripe_customer_id );
            }

            $payment_method_id = OrderMeta::get_payment_method_id( $order );

            if ( $payment_method_id ) {
                $payment_method = PaymentMethod::get( $payment_method_id );
            } elseif ( apply_filters( 'dokan_stripe_express_use_default_customer_payment_method', true ) ) {
                /*
                 * We can attempt to charge the customer's default source
                 * by sending empty source id.
                 */
                $payment_method_id = '';
            }
        }

        return (object) [
            'customer'              => $stripe_customer ? $stripe_customer->get_id() : false,
            'payment_method'        => $payment_method_id,
            'payment_method_object' => $payment_method,
        ];
    }

    /**
     * Extracts an order to all its suborders if exists
     * and returns data containg all of those orders.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return WC_Order[]
     */
    public static function get_all_orders_to_be_processed( $order ) {
        $all_orders = [];

        if ( $order->get_meta( 'has_sub_order' ) ) {
            $sub_order_ids = get_children(
                [
                    'post_parent' => $order->get_id(),
                    'post_type'   => 'shop_order',
                    'fields'      => 'ids',
                ]
            );

            foreach ( $sub_order_ids as $sub_order_id ) {
                $sub_order    = wc_get_order( $sub_order_id );
                $all_orders[] = $sub_order;
            }
        } else {
            $all_orders[] = $order;
        }

        return apply_filters( 'dokan_get_all_orders_to_be_processed', $all_orders );
    }

    /**
     * Get charge id from from an order
     *
     * @since 3.6.1
     *
     * @param WC_Order              $order
     * @param \Stripe\PaymentIntent $intent
     *
     * @return string|false
     */
    public static function get_charge_id( WC_Order $order, $intent = false ) {
        if ( ! $intent || ! is_object( $intent ) ) {
            $intent = Payment::get_intent( $order );
        }

        if ( ! $intent ) {
            return false;
        }

        $charge = Payment::get_latest_charge_from_intent( $intent );

        if ( $charge instanceof \Stripe\Charge ) {
            return $charge->id;
        }

        return false;
    }

    /**
     * Retrieves the processing for suborder.
     *
     * @since 3.6.1
     *
     * @param float    $processing_fee
     * @param WC_Order $suborder
     * @param WC_Order $order
     *
     * @return float
     */
    public static function get_fee_for_suborder( $processing_fee, $suborder, $order ) {
        if ( ! Helper::is_payment_needed( $order->get_id() ) ) {
            return 0;
        }
        $stripe_fee_for_vendor = $processing_fee * ( $suborder->get_total() / $order->get_total() );
        return number_format( $stripe_fee_for_vendor, 10 );
    }

    /**
     * Prepares a refund.
     *
     * @since 3.6.1
     *
     * @param array $args
     *
     * @return \WeDevs\DokanPro\Refund\Refund|\WP_Error
     */
    public static function prepare_refund( $args = [] ) {
        global $wpdb;

        $default_args = [
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
            'method'          => 'false',
        ];

        $args = wp_parse_args( $args, $default_args );

        $inserted = $wpdb->insert(
            $wpdb->dokan_refund,
            $args,
            [
                '%d',
                '%d',
                '%f',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
            ]
        );

        if ( $inserted !== 1 ) {
            return new \WP_Error( 'dokan_refund_create_error', __( 'Could not create new refund', 'dokan' ) );
        }

        $refund = dokan_pro()->refund->get( $wpdb->insert_id );

        return $refund;
    }

    /**
     * Processes a refund.
     *
     * @since 3.6.1
     *
     * @param WC_Order                       $order
     * @param \WeDevs\DokanPro\Refund\Refund $dokan_refund
     * @param \Stripe\Refund                 $stripe_refund
     *
     * @return boolean
     */
    public static function refund( $order, $dokan_refund, $stripe_refund ) {
        $order->add_order_note(
            sprintf(
                /* translators: 1) gateway title, 2) refund amount, 3) refund id, 4) refund reason */
                __( '[%1$s]. Refunded %2$s. Refund ID: %3$s.%4$s', 'dokan' ),
                Helper::get_gateway_title(),
                wc_price( $dokan_refund->get_refund_amount(), [ 'currency' => $order->get_currency() ] ),
                $stripe_refund->id,
                /* translators: refund reason */
                ! empty( $dokan_refund->get_refund_reason() ) ? sprintf( __( 'Reason - %s', 'dokan' ), $dokan_refund->get_refund_reason() ) : ''
            )
        );

        $refund_args = [
            Helper::get_gateway_id() => true,
        ];

        $transfer_id = OrderMeta::get_transfer_id( $order );
        if ( ! empty( $transfer_id ) ) {
            $refund_args['transfer_id'] = $transfer_id;
        }

        // Get balance transaction for refund amount, we need to deduct gateway charge from vendor refund amount
        $gateway_fee_refunded                = abs( Helper::format_balance_fee( $stripe_refund->balance_transaction ) );
        $refund_args['gateway_fee_refunded'] = ! empty( $gateway_fee_refunded ) ? $gateway_fee_refunded : 0;

        OrderMeta::update_refund_id( $order, $stripe_refund->id );
        OrderMeta::update_last_refund_id( $order, $stripe_refund->id );
        OrderMeta::save( $order );

        // Now try to approve the refund.
        $refund = $dokan_refund->approve( $refund_args );
        if ( is_wp_error( $refund ) ) {
            Helper::log( $refund->get_error_message(), 'Refund', 'error' );
        }
    }

    /**
     * Locks an order for processing specific operation for 5 minutes.
     *
     * @since 3.6.1
     * @since 3.7.8 Replaced parameter `$order` by `$order_id`. Added `$processing_type` as an optional parameter instead of `$intent`. Also added optional `$data` to serve the purpose of passing extra data.
     *
     * @param int    $order_id        ID of the order that is being paid.
     * @param string $processing_type (Optional) The operation type that is being processed. Default is `intent`.
     * @param string $data            (Optional) A specific data to be used for locking. For example, it can be the intent id while processing an intent. Default is `-1`
     *
     * @return bool A flag that indicates whether the order is already locked.
     */
    public static function lock_processing( $order_id, $processing_type = 'intent', $data = '-1' ) {
        $transient  = "dokan_stripe_express_processing_{$processing_type}_{$order_id}";
        $processing = get_transient( $transient );

        // Block the process if the same intent is already being handled.
        if ( '-1' === $processing || ( '-1' !== $data && $processing === $data ) ) {
            return true;
        }

        // Save the new intent as a transient, eventually overwriting another one.
        set_transient( $transient, empty( $data ) ? '-1' : $data, 5 * MINUTE_IN_SECONDS );

        return false;
    }

    /**
     * Unlocks an order for processing specific operation.
     *
     * @since 3.6.1
     * @since 3.7.8 Replaced parameter `$order` by `$order_id`. Added `$processing_type` as an optional parameter.
     *
     * @param int    $order_id        ID of the order that is being unlocked.
     * @param string $processing_type (Optional) The operation type that is being processed. Default is `intent`.
     *
     * @return void
     */
    public static function unlock_processing( $order_id, $processing_type = 'intent' ) {
        delete_transient( "dokan_stripe_express_processing_{$processing_type}_{$order_id}" );
    }

    /**
     * Retrieves transaction url of an order.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return string
     */
    public static function get_transaction_url( $order ) {
        $gateway = Helper::get_gateway_instance();

        if ( $gateway->testmode ) {
            $gateway->view_transaction_url = 'https://dashboard.stripe.com/test/payments/%s';
        } else {
            $gateway->view_transaction_url = 'https://dashboard.stripe.com/payments/%s';
        }

        return $gateway->get_transaction_url( $order );
    }

    /**
     * Retrieves order from charge id.
     *
     * @since 3.6.1
     *
     * @param string $charge_id
     *
     * @return WC_Order|false
     */
    public static function get_order_by_charge_id( $charge_id ) {
        global $wpdb;

        if ( empty( $charge_id ) ) {
            return false;
        }

        $order_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT posts.ID
                FROM $wpdb->posts AS posts
                LEFT JOIN $wpdb->postmeta AS meta
                ON posts.ID = meta.post_id
                WHERE meta.meta_value = %s
                AND meta.meta_key = %s
                AND posts.post_parent = '0'",
                $charge_id,
                OrderMeta::transaction_id_key()
            )
        );

        if ( empty( $order_id ) ) {
            return false;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order instanceof WC_Order ) {
            return false;
        }

        return $order;
    }

    /**
     * Retrieves order by intent id.
     *
     * @since 3.6.1
     *
     * @param string  $intent_id
     * @param boolean $is_setup
     *
     * @return WC_Order|false
     */
    public static function get_order_by_intent_id( $intent_id, $is_setup = false ) {
        global $wpdb;

        $order_id = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT DISTINCT ID
                FROM $wpdb->posts as posts
                LEFT JOIN $wpdb->postmeta as meta
                ON posts.ID = meta.post_id
                WHERE meta.meta_value = %s
                AND (
                    meta.meta_key = %s
                    OR meta.meta_key = %s
                )",
                $intent_id,
                OrderMeta::intent_id_key( $is_setup ),
                OrderMeta::debug_intent_id_key( $is_setup )
            )
        );

        if ( empty( $order_id ) ) {
            return false;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order instanceof WC_Order ) {
            return false;
        }

        return $order;
    }

    /**
     * Retrieves stripe customer id from order.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return string
     */
    public static function get_stripe_customer_id_from_order( WC_Order $order ) {
        // Try to get it via the order first.
        $customer = OrderMeta::get_customer_id( $order );

        if ( empty( $customer ) ) {
            $customer = UserMeta::get_stripe_customer_id( $order->get_customer_id() );
        }

        return $customer;
    }

    /**
     * Retrieves user from order.
     *
     * @since 3.6.1
     *
     * @param WC_Order $order
     *
     * @return WP_User
     */
    public static function get_user_from_order( WC_Order $order ) {
        $user = $order->get_user();
        if ( false === $user ) {
            $user = wp_get_current_user();
        }
        return $user;
    }

    /**
     * Validates cart contents to ensure they're allowed to be paid through Stripe Express.
     *
     * @since 3.7.8
     *
     * @return boolean
     */
    public static function validate_cart_items() {
        $is_valid = true;

        /*
         * This payment method can't be used if a Vendor is not connected
         * to Stripe express. So we need to traverse all the cart items
         * to check if any vendor is not connected.
         */
        if ( apply_filters( 'dokan_stripe_express_needs_cart_validation', true ) && ! empty( WC()->cart->cart_contents ) ) {
            foreach ( WC()->cart->cart_contents as $item ) {
                $product_id = $item['data']->get_id();

                /*
                 * If it contains vendor subscription product,
                 * we don't need to check whether the vendor is connected or not.
                 * Because in this case, the vendors themselves are customers.
                 */
                if ( Subscription::is_vendor_subscription_product( $product_id ) ) {
                    $is_valid = true;
                    break;
                }

                // Get vendor id from product id
                $vendor_id = dokan_get_vendor_by_product( $product_id, true );
                if ( ! $vendor_id ) {
                    $is_valid = false;
                    break;
                }

                /*
                 * If any vendor is not registered for a Stripe express account,
                 * and/or not enabled for payouts, the gateway is not available for checkout.
                 */
                if ( ! Helper::is_seller_activated( $vendor_id ) ) {
                    $is_valid = false;
                    break;
                }
            }
        }

        /**
         * Filter to validate cart items.
         * It can be used in case of any other validation logic.
         *
         * @since 3.7.8
         *
         * @param boolean $is_valid
         */
        return apply_filters( 'dokan_stripe_express_validate_cart_items', $is_valid );
    }

    /**
     * Checks if authentication has already failed for order.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     *
     * @return boolean
     */
    public static function check_if_authentication_failed( $order ) {
        $existing_intent = Payment::get_intent( $order );

        if (
            ! $existing_intent
            || 'requires_payment_method' !== $existing_intent->status
            || empty( $existing_intent->last_payment_error )
            || 'authentication_required' !== $existing_intent->last_payment_error->code
        ) {
            return false;
        }

        /**
         * Triggers when a payment attempt failed because SCA is required.
         *
         * @since 3.7.8
         *
         * @param WC_Order $order The order that is being renewed.
         */
        do_action( 'dokan_stripe_express_process_payment_authentication_required', $order );

        // Fail the payment attempt (order would be currently pending because of retry rules).
        $charge = Payment::get_latest_charge_from_intent( $existing_intent );

        $order->update_status(
            'failed',
            /* translators: %s) stripe charge id */
            sprintf(
                __( 'Stripe charge%s has awaiting authentication by user.', 'dokan' ),
                isset( $charge->id ) ? " ($charge->id)" : ''
            )
        );

        return true;
    }

    /**
     * Checks if the payment intent associated with an order failed and records the event.
     *
     * @since 3.7.8
     *
     * @param WC_Order              $order  The order which should be checked.
     * @param \Stripe\PaymentIntent $intent The intent, associated with the order.
     *
     * @return void
     */
    public static function process_failed_sca_auth( $order, $intent ) {
        // If the order has already failed, do not repeat the same message.
        if ( $order->has_status( 'failed' ) ) {
            return;
        }

        $order->update_status(
            'failed',
            isset( $intent->last_payment_error )
                /* translators: error message */
                ? sprintf( __( 'Stripe SCA authentication failed. Reason: %s', 'dokan' ), $intent->last_payment_error->message )
                : __( 'Stripe SCA authentication failed.', 'dokan' )
        );
    }

    /**
     * Adds order note.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     * @param string   $note
     *
     * @return void
     */
    public static function add_note( WC_Order $order, $note ) {
        /* translators: order note */
        $order->add_order_note( sprintf( __( '[Stripe Express] %s', 'dokan' ), $note ) );
    }
}
