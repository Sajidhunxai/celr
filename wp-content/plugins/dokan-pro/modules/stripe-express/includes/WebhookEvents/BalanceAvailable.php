<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\WebhookEvent;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\BackgroundProcesses\AwaitingDisbursement;

/**
 * Class to handle `balance.available` webhook.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents
 */
class BalanceAvailable extends WebhookEvent {

    /**
     * Handles the event.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function handle() {
        add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', [ $this, 'add_order_query_vars_for_awaiting_disbursement' ], 10, 2 );
        $query = new \WC_Order_Query(
            [
                'dokan_stripe_express_awaiting_disbursement' => true,
                'status'                                     => [ 'wc-processing', 'wc-completed' ],
                'type'                                       => 'shop_order',
                'limit'                                      => -1,
                'return'                                     => 'ids',
            ]
        );
        $orders = $query->get_orders();
        remove_filter( 'woocommerce_order_data_store_cpt_get_orders_query', [ $this, 'add_order_query_vars_for_awaiting_disbursement' ], 10, 2 );

        $bg_process = dokan_pro()->module->stripe_express->awaiting_disburse;
        if ( ! $bg_process instanceof AwaitingDisbursement ) {
            return;
        }

        foreach ( $orders as $order_id ) {
            $bg_process->push_to_queue(
                [
                    'order_id' => $order_id,
                ]
            );
        }

        $bg_process->save()->dispatch();
    }

    /**
     * Adds metadata param for
     * orders with awaiting disbursements.
     *
     * @param $query
     * @param $query_vars
     *
     * @since 3.7.8
     *
     * @return mixed
     */
    public function add_order_query_vars_for_awaiting_disbursement( $query, $query_vars ) {
        if ( empty( $query_vars['dokan_stripe_express_awaiting_disbursement'] ) ) {
            return $query;
        }

        $query['meta_query'][] = [
            'key'     => OrderMeta::awaiting_disbursement_key(),
            'value'   => 'yes',
            'compare' => '=',
        ];

        $query['meta_query'][] = [
            'key'     => OrderMeta::transfer_id_key(),
            'compare' => 'NOT EXISTS',
        ];

        $query['meta_query'][] = [
            'key'     => '_payment_method',
            'value'   => Helper::get_gateway_id(),
            'compare' => '=',
        ];

        return $query;
    }
}
