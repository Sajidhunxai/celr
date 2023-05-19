<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Processors\Subscription;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\WebhookEvent;

/**
 * Class to handle `customer.subscription.updated` webhook.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents
 */
class SubscriptionUpdated extends WebhookEvent {

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

        $subscription = $this->get_payload();
        if ( 'active' === $subscription->status || 'trialing' === $subscription->status ) {
            do_action( 'dokan_stripe_express_process_active_subscription', $subscription );
        }
    }
}
