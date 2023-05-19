<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\UserMeta;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Subscription;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\WebhookEvent;

/**
 * Class to handle `invoice.payment_failed` webhook.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents
 */
class InvoicePaymentFailed extends WebhookEvent {

    /**
     * Handles the event.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function handle() {
        if ( ! Subscription::has_vendor_subscription_module() ) {
            return;
        }

        $invoice    = $this->get_payload();
        $vendor_id  = Subscription::get_vendor_id_by_subscription( $invoice->subscription );
        $product_id = UserMeta::get_product_pack_id( $vendor_id );

        if ( ! Subscription::is_recurring_vendor_subscription_product( $product_id ) ) {
            return;
        }

        // Terminate user to update product
        UserMeta::update_post_product( $vendor_id, '0' );

        /*
         * In case of final payment attempt, we ought to delete the subscription pack
         * as the subscription no longer stay in effect.
         */
        if ( isset( $invoice->next_payment_attempt ) && is_null( $invoice->next_payment_attempt ) ) {
            $order_id = UserMeta::get_product_order_id( $vendor_id );
            SubscriptionHelper::delete_subscription_pack( $vendor_id, $order_id );
        }
    }
}
