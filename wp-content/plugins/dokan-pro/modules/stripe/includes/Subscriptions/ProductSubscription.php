<?php

namespace WeDevs\DokanPro\Modules\Stripe\Subscriptions;

use Exception;
use Stripe\Coupon;
use Stripe\Product;
use Stripe\Customer;
use Stripe\Subscription;
use WeDevs\DokanPro\Modules\Stripe\Helper as StripeHelper;
use WeDevs\DokanPro\Modules\Stripe\Subscriptions\InvoiceEmail;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use DokanPro\Modules\Subscription\SubscriptionPack;
use WeDevs\DokanPro\Modules\Stripe\Abstracts\StripePaymentGateway;
use WP_Error;

defined( 'ABSPATH' ) || exit;

/**
 * Dokan Stripe Subscriptoin Class
 *
 * @since 2.9.13
 */
class ProductSubscription extends StripePaymentGateway {

    /**
     * Source id holder
     *
     * @var string
     */
    protected $source_id;

    /**
     * Stripe product id holder
     *
     * @var string
     */
    protected $stripe_product_id;

    /**
     * Product id holder
     *
     * @var int
     */
    protected $product_id;

    /**
     * Order object holder
     *
     * @var object
     */
    protected $order;

    /**
     * Stripe Customer id holder
     *
     * @var string
     */
    protected $stripe_customer;

    /**
     * Constructor method
     *
     * @since 2.9.13
     */
    public function __construct() {
        StripeHelper::bootstrap_stripe();
        $this->order = null;
        $this->hooks();
    }

    /**
     * All the hooks
     *
     * @since 2.9.13
     *
     * @return void
     */
    public function hooks() {
        // this hook is to process recurring vendor subscription payment via stripe 3ds method
        add_action( 'wp_ajax_dokan_send_source', [ $this, 'process_recurring_subscription' ] );

        // this hook is to handle non recurring vendor subscription order via stripe 3ds and non3ds method
        add_action( 'dokan_process_subscription_order', [ $this, 'process_subscription' ], 10, 3 );
        add_action( 'dps_cancel_recurring_subscription', [ $this, 'cancel_subscription' ], 10, 3 );
        add_action( 'dps_activate_recurring_subscription', [ $this, 'activate_subscription' ], 10, 2 );
        add_action( 'dokan_remove_subscription_forcefully', [ $this, 'remove_subscription' ], 10, 2 );

        // dokan_invoice_payment_action_required send an email agains this hook with `hosted_invoice_url`;
        add_filter( 'woocommerce_email_classes', [ $this, 'load_emails' ] );
        add_filter( 'woocommerce_email_actions', [ $this, 'load_actions' ] );
    }

    /**
     * Process recurring subscription paid with stripe 3ds
     *
     * @return void
     */
    public function process_recurring_subscription() {
        $data = wp_unslash( $_POST );

        if ( empty( $data['nonce'] ) || ! wp_verify_nonce( $data['nonce'], 'dokan_reviews' ) ) {
            return;
        }

        if ( empty( $data['action'] ) || 'dokan_send_source' !== $data['action'] ) {
            return;
        }

        $this->source_id  = ! empty( $data['source_id'] ) ? wc_clean( $data['source_id'] ) : '';
        $this->product_id = ! empty( $data['product_id'] ) ? wc_clean( $data['product_id'] ) : '';

        $subscription = $this->setup_subscription();

        if ( is_wp_error( $subscription ) ) {
            $error = [
                'code'    => 'subscription_not_created',
                'message' => $subscription->get_error_message(),
            ];

            wp_send_json_error( $error );
        }

        wp_send_json( $subscription );
    }

    /**
     * Setup subscription data
     *
     * @return Subscriptoin|WP_Error|void
     * @since 3.0.3
     */
    public function setup_subscription() {
        $dokan_subscription = dokan()->subscription->get( $this->product_id );
        $product_pack       = $dokan_subscription->get_product();
        $product_pack_id    = get_post_meta( $product_pack->get_id(), '_dokan_stripe_product_id', true );
        $vendor_id          = get_current_user_id();

        if ( $this->order instanceof \WC_Order ) {
            $initial_payment = $this->order->get_total();
        } else {
            $initial_payment = WC()->cart->get_total( '' );
        }

        if ( ! $dokan_subscription->is_recurring() ) {
            return;
        }

        $subscription_interval = $dokan_subscription->get_recurring_interval();
        $subscription_period   = $dokan_subscription->get_period_type();
        $trial_period_days     = $dokan_subscription->is_trial() ? $dokan_subscription->get_trial_period_length() : 0;

        // if vendor already has used a trial pack, create a new plan without trial period
        if ( SubscriptionHelper::has_used_trial_pack( $vendor_id ) ) {
            $trial_period_days = 0;
        }

        // retrieve stripe product object
        try {
            if ( ! empty( $product_pack_id ) ) {
                $stripe_product   = Product::retrieve( $product_pack_id );
                $this->stripe_product_id = $stripe_product->id;
            }
        } catch ( Exception $exception ) {
            $this->stripe_product_id = null;
        }

        // if product not exist on stripe end, create a new product
        if ( empty( $this->stripe_product_id ) ) {
            try {
                $product_pack_name  = __( 'Vendor Subscription', 'dokan' ) . ': ' . $product_pack->get_title() . ' #' . $product_pack->get_id();
                $stripe_product = Product::create(
                    [
                        'name' => $product_pack_name,
                        'type' => 'service',
                    ]
                );

                $this->stripe_product_id = $stripe_product->id;

                // store this value for future use
                update_post_meta( $product_pack->get_id(), '_dokan_stripe_product_id', $stripe_product->id );
            } catch ( Exception $exception ) {
                return new WP_Error( 'stripe_api_error', $exception->getMessage() );
            }
        }

        // create subscription plan
        $subscription_args = [
            'items' => [
                [
                    'price_data' => [
                        'currency' => strtolower( get_woocommerce_currency() ),
                        'product' => $this->stripe_product_id,
                        'recurring' => [
                            'interval' => $subscription_period,
                            'interval_count' => $subscription_interval,
                        ],
                        'unit_amount' => StripeHelper::get_stripe_amount( $initial_payment ),
                    ],
                ],
            ],
        ];

        if ( ! empty( $trial_period_days ) ) {
            try {
                $date_time = dokan_current_datetime()->modify( "+ {$trial_period_days} days" );
                $subscription_args['trial_end'] = $date_time->getTimestamp();
            } catch ( Exception $exception ) {
                $subscription_args['trial_end'] = time();
            }
        }

        $subscription = $this->maybe_create_subscription( $subscription_args );

        if ( is_wp_error( $subscription ) ) {
            return $subscription;
        } elseif ( empty( $subscription->id ) ) {
            return new WP_Error( 'subscription_not_created', __( 'Unable to create subscription', 'dokan' ) );
        }

        if ( $product_pack && 'product_pack' === $product_pack->get_type() ) {
            // need to remove these meta data. Update it on webhook reponse
            update_user_meta( $vendor_id, 'can_post_product', '1' );
            update_user_meta( $vendor_id, '_stripe_subscription_id', $subscription->id );
            update_user_meta( $vendor_id, 'product_package_id', $product_pack->get_id() );
            update_user_meta( $vendor_id, 'product_no_with_pack', get_post_meta( $product_pack->get_id(), '_no_of_product', true ) );
            update_user_meta( $vendor_id, 'product_pack_startdate', dokan_current_datetime()->format( 'Y-m-d H:i:s' ) );
            update_user_meta( $vendor_id, '_customer_recurring_subscription', 'active' );
            update_user_meta( $vendor_id, 'dokan_has_active_cancelled_subscrption', false );
            update_user_meta( $vendor_id, 'product_pack_enddate', $dokan_subscription->get_product_pack_end_date() );
        }

        return $subscription;
    }

    /**
     * Process recurring, non-recurring and (stripe 3ds non-recurring subscriptions)
     *
     * @since 3.0.3
     *
     * @param \WC_Order $order
     * @param \Stripe\Intent $intent
     * @param bool $is_recurring
     *
     * @return void
     */
    public function process_subscription( $order, $intent, $is_recurring = false ) {
        $product_pack       = StripeHelper::get_subscription_product_by_order( $order );
        $dokan_subscription = dokan()->subscription->get( $product_pack->get_id() );
        $vendor_id          = get_current_user_id();
        $this->order        = $order;

        if ( is_object( $intent ) ) {
            $this->stripe_customer = $intent->customer;
        } else {
            $this->stripe_customer = $intent;
        }

        if ( $is_recurring ) {
            $this->product_id = $product_pack->get_id();
            $subscription     = $this->setup_subscription();

            if ( $subscription instanceof Subscription ) {
                update_user_meta( get_current_user_id(), 'product_order_id', $order->get_id() );
                /* Translators: First argument is order number and second argument is payment gateway name */
                $order->add_order_note( sprintf( __( 'Order %1$s payment is completed via %2$s on (Charge IDs: %3$s)', 'dokan' ), $order->get_order_number(), StripeHelper::get_gateway_title(), $subscription->id ) );
                $order->payment_complete();
            } elseif ( is_wp_error( $subscription ) ) {
                /* Translators: Error message from stripe api response. */
                dokan_log( sprintf( __( 'Problem creating subscription with stripe. More details: %s', 'dokan' ), $subscription->get_error_message() ) );
            }
        } elseif ( $product_pack && 'product_pack' === $product_pack->get_type() ) {
            // Vendor is purchasing non-recurring subscription, so if there is any recurring pack, cancel it first
            $previous_subscription = get_user_meta( $vendor_id, '_stripe_subscription_id', true );

            if ( $previous_subscription ) {
                $this->cancel_now( $previous_subscription, $dokan_subscription );
            }

            $dokan_subscription->activate_subscription( $order );
            $order->payment_complete();
        }
    }

    /**
     * Maybe create subscription
     *
     * @since 3.1.2
     */
    protected function maybe_create_subscription( $subscription_args ) {
        $vendor_subscription      = dokan()->vendor->get( get_current_user_id() )->subscription;
        $already_has_subscription = get_user_meta( get_current_user_id(), '_stripe_subscription_id', true );

        if ( $already_has_subscription && $vendor_subscription && $vendor_subscription->has_recurring_pack() ) {
            try {
                $subscription = Subscription::retrieve( $already_has_subscription );
            } catch ( Exception $e ) {
                return $this->create_subscription( $subscription_args );
            }

            // if subscription status is incomplete, cancel it first as incomplete subscription can't be updated
            if ( 'incomplete' === $subscription->status ) {
                try {
                    $subscription->cancel();
                } catch ( Exception $exception ) { // phpcs:ignore
                    // do nothing incase of api error
                }
                return $this->create_subscription( $subscription_args );
            }

            // if subscription status is incomplete_expired, try to create a new subscription
            if ( 'incomplete_expired' === $subscription->status ) {
                return $this->create_subscription( $subscription_args );
            }

            // update current subscription
            $subscription_args['cancel_at_period_end']  = false;
            $subscription_args['proration_behavior']    = 'create_prorations';
            $subscription_args['items'][0]['id']        = $subscription->items->data[0]->id;

            // Lets charge the vendor while creating new subscription
            if ( empty( $this->stripe_customer ) ) {
                $prepared_source = $this->prepare_source( get_current_user_id(), true );
                // check for source id
                if ( isset( $prepared_source->source ) ) {
                    $subscription_args['default_source'] = $prepared_source->source;
                }
            }

            try {
                $upgrade = Subscription::update(
                    $already_has_subscription, $subscription_args
                );

                $vendor_subscription->reset_active_cancelled_subscription();
            } catch ( Exception $exception ) {
                return new WP_Error( 'stripe_api_error', $exception->getMessage() );
            }

            return $upgrade;
        }

        return $this->create_subscription( $subscription_args );
    }

    /**
     * Create subscription
     *
     * @return \Stripe\Subscription|WP_Error
     * @since 2.9.13
     */
    protected function create_subscription( $subscription_args ) {
        try {
            // Lets charge the vendor while creating new subscription
            if ( empty( $this->stripe_customer ) ) {
                $prepared_source = $this->prepare_source( get_current_user_id(), true );
                $this->validate_source( $prepared_source );
                $this->stripe_customer = $prepared_source->customer;
            }
            $subscription_args['expand']    = [ 'latest_invoice.payment_intent' ];
            $subscription_args['customer']  = $this->stripe_customer;
            $subscription = Subscription::create( $subscription_args );
        } catch ( Exception $exception ) {
            return new WP_Error( 'stripe_api_error', $exception->getMessage() );
        }

        return $subscription;
    }

    /**
     * Get coupon id for a subscription
     *
     * @return Stripe\Coupon::id |null on failure
     * @since  2.9.14
     */
    protected function get_coupon() {
        $discount = WC()->cart->get_discount_total();

        if ( ! $discount ) {
            return;
        }
        try {
            $coupon = Coupon::create(
                [
                    'duration'   => 'once',
                    'id'         => StripeHelper::get_stripe_amount( $discount ) . '_OFF_' . random_int( 1, 999999 ),
                    'amount_off' => StripeHelper::get_stripe_amount( $discount ),
                    'currency'   => strtolower( get_woocommerce_currency() ),
                ]
            );
        } catch ( Exception $exception ) {
            return new WP_Error( 'stripe_api_error', $exception->getMessage() );
        }

        return $coupon->id;
    }

    /**
     * Cancel stripe subscription
     *
     * @since 3.0.3
     *
     * @param int $order_id
     * @param int $vendor_id
     * @param bool $immediately Force subscription to be cancelled immediately. [since 3.0.3]
     *
     * @return void
     **/
    public function cancel_subscription( $order_id, $vendor_id, $cancel_immediately ) {
        $order = wc_get_order( $order_id );

        if ( ! $order || 'dokan-stripe-connect' !== $order->get_payment_method() ) {
            return;
        }

        $vendor_subscription = dokan()->vendor->get( $vendor_id )->subscription;
        $subscription_id     = get_user_meta( $vendor_id, '_stripe_subscription_id', true );

        if ( ! $vendor_subscription || ! $vendor_subscription->has_recurring_pack() ) {
            return;
        }

        if ( $cancel_immediately ) {
            return $this->cancel_now( $subscription_id, $vendor_subscription );
        }

        try {
            Subscription::update(
                $subscription_id,
                [
                    // Cancel the subscription at the end of the current billing period
                    'cancel_at_period_end' => true,
                ]
            );
            $vendor_subscription->set_active_cancelled_subscription();
        } catch ( Exception $e ) {
            if ( StripeHelper::is_no_such_subscription_error( $e->getMessage() ) ) {
                do_action( 'dokan_remove_subscription_forcefully', $vendor_subscription, $subscription_id );
            } else {
                /* Translators: Error message from stripe api response. */
                dokan_log( sprintf( __( 'Unable to cancel subscription with stripe. More details: %s', 'dokan' ), $e->getMessage() ) );
            }
        }
    }

    /**
     * Cancel stripe subscription
     *
     * @since 3.0.3
     *
     * @return void
     **/
    public function activate_subscription( $order_id, $vendor_id ) {
        $order = wc_get_order( $order_id );

        if ( ! $order || 'dokan-stripe-connect' !== $order->get_payment_method() ) {
            return;
        }

        $vendor_subscription = dokan()->vendor->get( $vendor_id )->subscription;
        $subscription_id     = get_user_meta( $vendor_id, '_stripe_subscription_id', true );

        if ( ! $vendor_subscription || ! $vendor_subscription->has_recurring_pack() ) {
            return;
        }

        try {
            // Reactivate the subscription
            Subscription::update(
                $subscription_id,
                [
                    'cancel_at_period_end' => false,
                ]
            );

            // Add order re-activation note
            $order->add_order_note( __( 'Subscription reactivated.', 'dokan' ) );

            $vendor_subscription->reset_active_cancelled_subscription();
        } catch ( Exception $e ) {
            /* Translators: Error message from stripe api response. */
            dokan_log( sprintf( __( 'Unable to re-activate subscription with stripe. More details: %s', 'dokan' ), $e->getMessage() ) );
        }
    }

    /**
     * Cancel the subscription immediately
     *
     * @since 3.0.3
     *
     * @param string $subscription_id
     * @param Object Vendor_subscription
     *
     * @return void
     */
    protected function cancel_now( $subscription_id, $vendor_subscription ) {
        try {
            $subscription = Subscription::retrieve( $subscription_id );
            $subscription->cancel();
            $vendor_subscription->reset_active_cancelled_subscription();
        } catch ( Exception $e ) {
            if ( StripeHelper::is_no_such_subscription_error( $e->getMessage() ) ) {
                do_action( 'dokan_remove_subscription_forcefully', $vendor_subscription, $subscription_id );
            } else {
                /* Translators: Error message from stripe api response. */
                dokan_log( sprintf( __( 'Unable to cancel subscription with stripe. More details: %s', 'dokan' ), $e->getMessage() ) );
            }
        }
    }

    /**
     * Load email class
     *
     * @since 3.0.3
     *
     * @param array $emails
     *
     * @return array
     */
    public function load_emails( $emails ) {
        $emails['InvoiceEmail'] = new InvoiceEmail();

        return $emails;
    }

    /**
     * Load email actions
     *
     * @since 3.0.3
     *
     * @param array $actions
     *
     * @return array
     */
    public function load_actions( $actions ) {
        $actions[] = 'dokan_invoice_payment_action_required';

        return $actions;
    }

    /**
     * Remove subscription forcefully. In case webhook is disabled or didn't work for some reason
     * Cancel the subscription in vendor's end. subscription is already removed in stripe's end.
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function remove_subscription( $vendor_subscription, $subscription_id ) {
        $vendor_id = $vendor_subscription->get_vendor();
        $order_id  = get_user_meta( $vendor_id, 'product_order_id', true );

        if ( $vendor_subscription->has_recurring_pack() ) {
            SubscriptionHelper::delete_subscription_pack( $vendor_id, $order_id );
        }
    }
}
