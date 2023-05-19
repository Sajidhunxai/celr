<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Support\UserMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\WebhookEvent;

/**
 * Class to handle `review.closed` webhook.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents
 */
class ReviewClosed extends WebhookEvent {

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

        $message = sprintf(
            /* translators: 1) gateway title, 2) reason */
            __( '[%1$s] The opened review for this order is now closed. Reason: %2$s', 'dokan' ),
            Helper::get_gateway_title(),
            $review->reason
        );

        if ( $this->vendor_subscription ) {
            UserMeta::update_post_product( $this->user_id );
        }

        if ( ! OrderMeta::get_status_final( $order ) ) {
            $before_hold_status = OrderMeta::get_status_before_hold( $order );
            $before_hold_status = $before_hold_status ? $before_hold_status : 'processing';
            $order->update_status( $before_hold_status, $message );
        } else {
            $order->add_order_note( $message );
        }
    }
}
