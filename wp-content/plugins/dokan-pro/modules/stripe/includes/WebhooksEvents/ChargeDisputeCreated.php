<?php

namespace WeDevs\DokanPro\Modules\Stripe\WebhooksEvents;

use Stripe\Charge;
use Stripe\Invoice;
use WeDevs\DokanPro\Modules\Stripe\Helper;
use WeDevs\DokanPro\Modules\Stripe\Interfaces\WebhookHandleable;

defined( 'ABSPATH' ) || exit;

class ChargeDisputeCreated implements WebhookHandleable {

    /**
     * Event holder
     *
     * @var null
     */
    private $event = null;

    /**
     * Constructor method
     *
     * @since 3.0.3
     *
     * @param \Stripe\Event $event
     *
     * @return void
     */
    public function __construct( $event ) {
        $this->event = $event;
    }

    /**
     * Hanle the event
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function handle() {
        $invoice        = $this->event->data->object;
        $charge_id      = $invoice->charge;
        $charge         = Charge::retrieve( $charge_id );
        $charge_invoice = Invoice::retrieve( $charge->invoice );
        $vendor_id      = Helper::get_vendor_id_by_subscription( $charge_invoice->subscription );
        $order_id       = get_user_meta( $vendor_id, 'product_order_id', true );
        $order          = wc_get_order( $order_id );

        update_user_meta( $vendor_id, 'can_post_product', '0' );
        $order->set_status( 'on-hold' );
        $order->add_order_note( sprintf( __( 'Order %s status is now on-hold due to dispute via %s on (Charge IDs: %s)', 'dokan' ), $order->get_order_number(), Helper::get_gateway_title(), $charge_id ) );
        $order->save();
    }
}
