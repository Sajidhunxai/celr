<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Order;
use WeDevs\DokanPro\Modules\StripeExpress\Support\UserMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\WebhookEvent;

/**
 * Class to handle `review.opened` webhook.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents
 */
class ReviewOpened extends WebhookEvent {

    /**
     * Handles the event.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function handle() {
        $review = $this->get_payload();
        $order  = $this->get_order_from_stripe_object( $review );

        if ( ! $order ) {
            return;
        }

        OrderMeta::update_status_before_hold( $order, $order->get_status() );

        $message = sprintf(
            /* translators: 1) gateway title, 2) opening anchor tag with url, 3) closing anchor tag, 4) reason */
            __( '[%1$s] A review has been opened for this order. Action is needed. Please go to your %2$sStripe Dashboard%3$s to review this issue. Reason: %4$s', 'dokan' ),
            Helper::get_gateway_title(),
            sprintf( '<a href="%s" title="Stripe Dashboard" target="_blank">', Order::get_transaction_url( $order ) ),
            '</a>',
            $review->reason
        );

        if ( $this->vendor_subscription ) {
            UserMeta::update_post_product( $this->user_id, '0' );
        }

        if ( ! OrderMeta::get_status_final( $order ) ) {
            $order->update_status( 'on-hold', $message );
        } else {
            $order->add_order_note( $message );
        }

        OrderMeta::save( $order );
    }
}
