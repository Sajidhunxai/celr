<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Utilities\Traits;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Api\Charge;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Order;
use WeDevs\DokanPro\Modules\StripeExpress\Support\UserMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Api\PaymentIntent;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Subscription;

/**
 * Trait to manage webhook utilities.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Utilities\Traits
 */
trait WebhookUtils {

    /**
     * Extracts order object from a Stripe object (Either `review` or `dispute`).
     *
     * @since 3.7.8
     *
     * @param \Stripe\Review|\Stripe\Dispute $object
     *
     * @return \WC_Order|false
     */
    public function get_order_from_stripe_object( $object ) {
        if ( ! in_array( strtolower( $object->object ), [ 'review', 'dispute' ], true ) ) {
            return false;
        }

        $this->product_id          = null;
        $this->user_id             = 0;
        $this->vendor_subscription = false;

        if ( isset( $object->payment_intent ) ) {
            $retrievable_id   = $object->payment_intent;
            $api_class        = PaymentIntent::class;
            $alternate_method = 'get_order_by_intent_id';
            $log_message      = "Could not find order via Intent ID: $object->payment_intent";
        } else {
            $retrievable_id   = $object->charge;
            $api_class        = Charge::class;
            $alternate_method = 'get_order_by_charge_id';
            $log_message      = "Could not find order via Charge ID: $object->charge";
        }

        $retrieved_object = false;
        if ( is_callable( [ $api_class, 'get' ] ) ) {
            $retrieved_object = call_user_func( [ $api_class, 'get' ], $retrievable_id, [ 'expand' => 'invoice' ] );
        }

        if ( $retrieved_object && ! empty( $retrieved_object->invoice->subscription ) ) {
            $this->user_id    = Subscription::get_vendor_id_by_subscription( $retrieved_object->invoice->subscription );
            $this->product_id = UserMeta::get_product_pack_id( $this->user_id );
        }

        $order = false;

        if (
            $this->user_id &&
            $this->product_id &&
            Subscription::is_recurring_vendor_subscription_product( $this->product_id )
        ) {
            $order_id = UserMeta::get_product_order_id( $this->user_id );
            $order    = wc_get_order( $order_id );
            $this->vendor_subscription = true;
        } elseif ( is_callable( [ Order::class, $alternate_method ] ) ) {
            $order = call_user_func( [ Order::class, $alternate_method ], $retrievable_id );
        }

        if ( ! $order instanceof \WC_Order ) {
            $this->log( $log_message );
            return false;
        }

        return $order;
    }
}
