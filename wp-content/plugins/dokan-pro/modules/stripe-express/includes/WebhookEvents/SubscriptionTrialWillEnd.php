<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Processors\Subscription;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\WebhookEvent;

/**
 * Class to handle `customer.subscription.trial_will_end` webhook.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\WebhookEvents
 */
class SubscriptionTrialWillEnd extends WebhookEvent {

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

        /**
         * Fires when a subscription trial will end within three days.
         *
         * @param \Stripe\Subscription $subscription
         */
        do_action( 'dokan_stripe_express_vendor_subscription_will_end', $subscription );
    }
}
