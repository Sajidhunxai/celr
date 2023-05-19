<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Support\UserMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\WebhookEvent;

/**
 * Class to handle `charge.dispute.closed` webhook.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents
 */
class ChargeDisputeClosed extends WebhookEvent {

    /**
     * Handles the event.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function handle() {
        $dispute = $this->get_payload();
        $order   = $this->get_order_from_stripe_object( $dispute );

        if ( ! $order ) {
            return;
        }

        $order_status = OrderMeta::get_status_before_hold( $order );

        switch ( $dispute->status ) {
            case 'won':
                $message = sprintf(
                    /* translators: 1) gateway title */
                    __( '[%s] The dispute was resolved in your favor for this order. The order status has been updated.', 'dokan' ),
                    Helper::get_gateway_title()
                );

                if ( $this->vendor_subscription ) {
                    UserMeta::update_post_product( $this->user_id );
                }
                break;

            case 'lost':
                $message = sprintf(
                    /* translators: 1) gateway title */
                    __( '[%s] The dispute was lost or accepted.', 'dokan' ),
                    Helper::get_gateway_title()
                );
                $order_status = 'failed';

                if ( $this->vendor_subscription ) {
                    SubscriptionHelper::delete_subscription_pack( $this->user_id, $order->get_id() );
                }
                break;

            case 'warning_closed':
                $message = sprintf(
                    /* translators: 1) gateway title */
                    __( '[%s] The inquiry or retrieval was closed. The order status has been updated.', 'dokan' ),
                    Helper::get_gateway_title()
                );
                break;

            default:
                return;
        }

        // Mark final so that order status is not overridden by out-of-sequence events.
        OrderMeta::make_status_final( $order );

        // Fail order if dispute is lost, or else revert to pre-dispute status.
        $order->update_status( $order_status, $message );
        OrderMeta::save( $order );
    }
}
