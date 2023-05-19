<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Utilities\Traits;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use Exception;
use DokanPro\Modules\Subscription\SubscriptionPack;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Subscription;

/**
 * Trait for vendor subscription utility functions.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Utilities\Traits
 */
trait DokanSubscriptions {

    /**
     * Retrieves the product type of dokan subscription.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public function get_product_type() {
        return Subscription::VENDOR_SUBSCRIPTION_PRODUCT_TYPE;
    }

    /**
     * Cancel a subscription immediately.
     *
     * @since 3.7.8
     *
     * @param SubscriptionPack $vendor_subscription
     * @param string           $subscription_id     (Optional)
     *
     * @return void
     */
    protected function cancel_now( $vendor_subscription, $subscription_id = false ) {
        if ( false === $subscription_id ) {
            $subscription_id = Subscription::parse_subscription_id( $vendor_subscription->get_vendor() );
        }

        if ( empty( $subscription_id ) ) {
            do_action( 'dokan_stripe_express_force_remove_subscription', $vendor_subscription );
            return;
        }

        $subscription = Subscription::get( $subscription_id );

        if ( is_wp_error( $subscription ) ) {
            if ( Helper::is_no_such_subscription_error( $subscription->get_error_message() ) ) {
                do_action( 'dokan_stripe_express_force_remove_subscription', $vendor_subscription );
            } else {
                Helper::log(
                    sprintf(
                        /* translators: Error message */
                        __( 'Unable to cancel subscription with stripe. More details: %s', 'dokan' ),
                        $subscription->get_error_message()
                    )
                );
            }
        }

        try {
            $subscription->cancel();
            $vendor_subscription->reset_active_cancelled_subscription();
        } catch ( Exception $e ) {
            Helper::log( 'Error: ' . $e->getMessage() );
            return;
        }
    }

    /**
     * Activates a subscription pack.
     *
     * @since 3.7.8
     *
     * @param SubscriptionPack $vendor_subscription
     *
     * @return void
     */
    protected function cancel_subscription( $vendor_subscription ) {
        $vendor_id       = $vendor_subscription->get_vendor();
        $subscription_id = Subscription::parse_subscription_id( $vendor_id );
        $subscription    = Subscription::update(
            [
                'cancel_at_period_end' => true,
            ],
            $subscription_id
        );

        if ( ! is_wp_error( $subscription ) ) {
            $vendor_subscription->set_active_cancelled_subscription();
            return;
        }

        if ( Helper::is_no_such_subscription_error( $subscription->get_error_message() ) || empty( $subscription_id ) ) {
            do_action( 'dokan_stripe_express_force_remove_subscription', $vendor_subscription );
        } else {
            $subscription = Subscription::get( $subscription_id );
            if ( is_wp_error( $subscription ) ) {
                Helper::log( 'Error: ' . $subscription->get_error_message() );
                return;
            }

            if ( 'canceled' === $subscription->status || 'incomplete_expired' === $subscription->status ) {
                do_action( 'dokan_stripe_express_force_remove_subscription', $vendor_subscription );
            }
        }
    }

    /**
     * Prepares data to create a subscription.
     *
     * @since 3.7.8
     *
     * @param SubscriptionPack $dokan_subscription
     * @param array            $subscription_data
     *
     * @return array
     */
    protected function prepare_data( $dokan_subscription, $subscription_data = [] ) {
        if ( ! $dokan_subscription instanceof SubscriptionPack ) {
            return $subscription_data;
        }

        if ( empty( $subscription_data['metadata'] ) ) {
            $subscription_data['metadata'] = [];
        }

        // If order is set, get the order total, else look for the cart total
        if ( $this->order instanceof \WC_Order ) {
            $total    = $this->order->get_total();
            $discount = $this->order->get_discount_total();
            $currency = $this->order->get_currency();

            $subscription_data['metadata']['order_id'] = $this->order->get_id();
        } else {
            $cart     = WC()->cart;
            $total    = ! empty( $cart ) ? $cart->get_total( '' ) : 0;
            $discount = ! empty( $cart ) ? $cart->get_discount_total() : 0;
            $currency = get_woocommerce_currency();
        }

        $subscription_data['metadata']['product_id'] = $this->product_id;

        $stripe_product = Subscription::get_product( $this->product_id );

        // If product not exist on stripe end, create a new one
        if ( ! $stripe_product || is_wp_error( $stripe_product ) ) {
            $product_pack = $dokan_subscription->get_product();
            $data = [
                'name' => sprintf(
                    /* translators: 1) product title, 2) product id */
                    __( 'Vendor Subscription: %1$s #%2$s', 'dokan' ),
                    $product_pack->get_title(),
                    $product_pack->get_id()
                ),
                'type' => 'service',
            ];

            $stripe_product = Subscription::create_product( $product_pack, $data );
        }

        if ( $stripe_product instanceof \Stripe\Product ) {
            $this->stripe_product_id = $stripe_product->id;
        }

        if ( ! empty( $discount ) ) {
            $coupon = Subscription::process_discount( [], $discount );

            if ( $coupon && ! is_wp_error( $coupon ) ) {
                $subscription_data['coupon'] = $coupon;
                $total = $total + $discount;
            }
        }

        $trial_period_days = $dokan_subscription->is_trial() ? $dokan_subscription->get_trial_period_length() : 0;
        $vendor_id         = ! empty( WC()->session ) ? WC()->session->get_customer_id() : get_current_user_id();

        // If vendor already has used a trial pack, create a new plan without trial period
        if ( SubscriptionHelper::has_used_trial_pack( $vendor_id ) ) {
            $trial_period_days = 0;
        }

        if ( ! empty( $trial_period_days ) ) {
            try {
                $date_time = dokan_current_datetime()->modify( "+ {$trial_period_days} days" );
                $subscription_data['trial_end'] = $date_time->getTimestamp();
            } catch ( Exception $e ) {
                $subscription_data['trial_end'] = time();
            }
        }

        $billing_cycle_end = $dokan_subscription->get_product_pack_end_date();
        if ( ! empty( $billing_cycle_end ) && 'unlimited' !== $billing_cycle_end ) {
            try {
                $subscription_data['cancel_at'] = dokan_current_datetime()->modify( $billing_cycle_end )->getTimestamp();
            } catch ( Exception $e ) {
                Helper::log( 'Could not process the billing cycle stop date for Dokan Subscription', __METHOD__ );
            }
        } else {
            $subscription_data['cancel_at_period_end'] = false;
        }

        $payment_method_types = Helper::get_enabled_retrievable_payment_methods();
        if ( ! empty( $payment_method_types ) ) {
            if ( ! isset( $subscription_data['payment_settings'] ) ) {
                $subscription_data['payment_settings'] = [];
            }

            $subscription_data['payment_settings']['payment_method_types'] = $payment_method_types;
        }

        $subscription_data['proration_behavior'] = 'create_prorations';
        $subscription_data['items']              = [
            [
                'price_data' => [
                    'currency'    => strtolower( $currency ),
                    'product'     => $this->stripe_product_id,
                    'unit_amount' => Helper::get_stripe_amount( $total ),
                    'recurring'   => [
                        'interval'       => $dokan_subscription->get_period_type(),
                        'interval_count' => $dokan_subscription->get_recurring_interval(),
                    ],
                ],
            ],
        ];

        return $subscription_data;
    }
}
