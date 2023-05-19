<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\UserMeta;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Subscription;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\WebhookEvent;

/**
 * Class to handle `invoice.payment_action_required` webhook.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents
 */
class InvoicePaymentActionRequired extends WebhookEvent {

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

        $invoice = $this->get_payload();
        if ( empty( $invoice->subscription ) ) {
            return;
        }

        $vendor_id  = Subscription::get_vendor_id_by_subscription( $invoice->subscription );
        $product_id = UserMeta::get_product_pack_id( $vendor_id );

        if ( ! SubscriptionHelper::is_subscription_product( $product_id ) ) {
            return;
        }

        WC()->mailer();
        do_action( 'dokan_stripe_express_invoice_payment_action_required', $invoice, $vendor_id );
    }
}
