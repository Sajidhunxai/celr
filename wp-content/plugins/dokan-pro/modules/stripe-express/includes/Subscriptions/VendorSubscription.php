<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Subscriptions;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WC_Order;
use WP_Error;
use DokanPro\Modules\Subscription\SubscriptionPack;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Support\UserMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Support\ProductMeta;
use DokanPro\Modules\Subscription\Helper as SubscriptionHelper;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Subscription;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Traits\DokanSubscriptions;

/**
 * Vendor subscription handler class.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Subscription
 */
class VendorSubscription {

    use DokanSubscriptions;

    /**
     * The order object.
     *
     * @since 3.7.8
     *
     * @var WC_Order
     */
    protected $order;

    /**
     * ID of the subscription pack.
     *
     * @since 3.7.8
     *
     * @var integer
     */
    protected $product_id = 0;

    /**
     * ID of the stripe product.
     *
     * @since 3.7.8
     *
     * @var integer
     */
    protected $stripe_product_id = null;

    /**
     * The stripe customer object.
     *
     * @since 3.7.8
     *
     * @var \Stripe\Customer
     */
    protected $stripe_customer;

    /**
     * The source ID of the customer.
     *
     * @since 3.7.8
     *
     * @var integer
     */
    protected $payment_method_id;

    /**
     * Class constructor.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function __construct() {
        add_action( 'init', [ $this, 'hooks' ] );
    }

    /**
     * Initiates all necessary hooks.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function hooks() {
        if ( ! Subscription::has_vendor_subscription_module() ) {
            return;
        }

        // Handle dokan subscription events.
        add_action( 'dps_cancel_recurring_subscription', [ $this, 'cancel_recurring_subscription' ], 10, 3 );
        add_action( 'dps_activate_recurring_subscription', [ $this, 'activate_subscription' ], 10, 2 );
        add_action( 'dokan_subscription_cancelled', [ $this, 'before_cancelling_subscriptions' ], 10, 3 );
        add_filter( 'dps_cancel_non_recurring_subscription_immediately', [ $this, 'should_cancel_non_recurring_subscription_immediately' ], 10, 2 );
        // Process vendor subscription during payment through stripe express.
        add_action( 'dokan_stripe_express_process_payment', [ $this, 'process_recurring_subscription' ], 10, 2 );
        add_action( 'dokan_stripe_express_process_active_subscription', [ $this, 'process_active_subscription' ] );
        add_action( 'dokan_stripe_express_force_remove_subscription', [ $this, 'remove_subscription' ], 10, 2 );
        add_filter( 'dokan_stripe_express_should_not_process_order_redirect', [ $this, 'should_not_process_order_redirect' ], 10, 2 );
        add_filter( 'dokan_stripe_express_process_subscription_data', [ $this, 'process_subscription_data' ], 10, 3 );
        add_filter( 'dokan_stripe_express_is_payment_needed', [ $this, 'is_payment_needed' ], 10, 2 );
        add_filter( 'dokan_tax_fee_recipient', [ $this, 'tax_fee_recipient' ], 10, 2 );
        add_filter( 'dokan_shipping_fee_recipient', [ $this, 'shipping_fee_recipient' ], 10, 2 );
        // Change suborder notice for vendors
        add_filter( 'dokan_suborder_notice_to_customer', [ $this, 'suborder_notice_to_vendor' ], 10, 2 );
        // Manage necessary WooCommerce hooks for vendor subscription.
        add_action( 'woocommerce_process_product_meta_' . $this->get_product_type(), [ $this, 'process_subscription_after_save' ], 99, 1 );
        add_filter( 'woocommerce_cancel_unpaid_order', [ $this, 'should_cancel_unpaid_order' ], 10, 2 );
        add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'maybe_empty_cart' ], 100, 3 );
        add_filter( 'woocommerce_order_button_text', [ $this, 'get_order_button_text' ] );
        // Load email classes and actions
        add_filter( 'woocommerce_email_classes', [ $this, 'load_emails' ] );
        add_filter( 'woocommerce_email_actions', [ $this, 'load_email_actions' ] );
    }

    /**
     * Checks if payment is needed.
     *
     * @since 3.7.8
     *
     * @param bool $is_payment_needed
     * @param int  $order_id
     *
     * @return boolean
     */
    public function is_payment_needed( $is_payment_needed, $order_id ) {
        if ( ! $is_payment_needed ) {
            return $is_payment_needed;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order || ! Subscription::is_recurring_vendor_subscription_order( $order_id ) ) {
            return $is_payment_needed;
        }

        $product = SubscriptionHelper::get_vendor_subscription_product_by_order( $order );
        if ( empty( $product ) ) {
            return $is_payment_needed;
        }

        $dokan_subscription = new SubscriptionPack( $product->get_id(), $order->get_customer_id() );

        /*
         * In case the subscription has free trial and the vendor hasn't already used the trial pack,
         * the payment is not needed.
         */
        if ( $dokan_subscription->is_trial() && ! SubscriptionHelper::has_used_trial_pack( $order->get_customer_id() ) ) {
            return false;
        }

        return $is_payment_needed;
    }

    /**
     * Processes data to create a subscription.
     *
     * @since 3.7.8
     *
     * @param array      $subscription_data
     * @param int|string $product_id
     * @param int|string $order_id
     *
     * @return \Stripe\Subscription|WP_Error
     */
    public function process_subscription_data( $subscription_data, $product_id, $order_id ) {
        if ( ! Subscription::is_recurring_vendor_subscription_product( $product_id ) ) {
            return $subscription_data;
        }

        $this->order        = wc_get_order( $order_id );
        $this->product_id   = $product_id;
        $dokan_subscription = dokan()->subscription->get( $product_id );

        UserMeta::update_initial_product_pack( get_current_user_id(), $product_id );

        return $this->prepare_data( $dokan_subscription, $subscription_data );
    }

    /**
     * Processes recurring subscription.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order
     * @param string   $payment_method_id
     *
     * @return \Stripe\Subscription|false
     */
    public function process_recurring_subscription( $order, $payment_method_id ) {
        if ( ! $order instanceof WC_Order ) {
            return false;
        }

        if ( ! Subscription::is_recurring_vendor_subscription_order( $order ) ) {
            return false;
        }

        $product = SubscriptionHelper::get_vendor_subscription_product_by_order( $order );
        if ( empty( $product ) ) {
            return false;
        }

        $vendor_id          = $order->get_customer_id();
        $dokan_subscription = new SubscriptionPack( $product->get_id(), $vendor_id );
        if ( ! $dokan_subscription->is_recurring() ) {
            return false;
        }

        $this->order             = $order;
        $this->product_id        = $product->get_id();
        $this->payment_method_id = $payment_method_id;

        $subscription = $this->setup_subscription( $dokan_subscription );
        return $subscription;
    }

    /**
     * Processes recurring subscription that is active in Stripe end.
     *
     * @since 3.7.8
     *
     * @param \Stripe\Subscription
     *
     * @return \Stripe\Subscription|false
     */
    public function process_active_subscription( $subscription ) {
        $vendor_id  = Subscription::get_vendor_id_by_subscription( $subscription );
        $product_id = UserMeta::get_product_pack_id( $vendor_id );

        if ( empty( $product_id ) ) {
            $product_id = UserMeta::get_initial_product_pack_id( $vendor_id );
        }

        if ( ! SubscriptionHelper::is_subscription_product( $product_id ) ) {
            return;
        }

        if ( ! empty( $subscription->cancel_at_period_end ) ) {
            UserMeta::update_product_pack_end_date(
                $vendor_id,
                dokan_current_datetime()
                    ->setTimestamp( $subscription->current_period_end )
                        ->format( 'Y-m-d H:i:s' )
            );
            UserMeta::update_active_cancelled_subscription( $vendor_id );
        } else {
            UserMeta::update_active_cancelled_subscription( $vendor_id, false );
            UserMeta::update_seller_enabled( $vendor_id );
            UserMeta::update_post_product( $vendor_id );
            UserMeta::update_pending_subscription( $vendor_id, false );

            $dokan_subscription = dokan()->subscription->get( $product_id );
            $end_date           = empty( $subscription->cancel_at )
                ? $dokan_subscription->get_product_pack_end_date()
                : dokan_current_datetime()
                    ->setTimestamp( $subscription->cancel_at )
                        ->format( 'Y-m-d H:i:s' );

            UserMeta::update_product_pack_end_date( $vendor_id, $end_date );

            do_action( 'dokan_vendor_purchased_subscription', $vendor_id );
        }

        $order = Subscription::get_order_by_subscription( $subscription );

        if ( ! $order ) {
            return $subscription;
        }

        if ( 'trialing' === $subscription->status ) {
            UserMeta::update_product_order_id( $vendor_id, $order->get_id() );

            $order->add_order_note(
                sprintf(
                    /* translators: 1) gateway title, 2) order number, 3) subscription id */
                    __( '[%1$s] Trial period for order %2$s has started (Subscription ID: %3$s)', 'dokan' ),
                    Helper::get_gateway_title(),
                    $order->get_order_number(),
                    $subscription->id
                )
            );
        } elseif ( 'active' === $subscription->status ) {
            UserMeta::update_product_order_id( $vendor_id, $order->get_id() );

            $order->add_order_note(
                sprintf(
                    /* translators: 1) gateway title, 2) order number, 3) subscription id */
                    __( '[%1$s] Payment for subscription order %2$s is completed (Subscription ID: %3$s)', 'dokan' ),
                    Helper::get_gateway_title(),
                    $order->get_order_number(),
                    $subscription->id
                )
            );

            $order->payment_complete();
        }

        return $subscription;
    }

    /**
     * Sets up a recurring subscription in Stripe.
     *
     * @since 3.7.8
     *
     * @param SubscriptionPack $dokan_subscription
     *
     * @return \Stripe\Subscription|false
     */
    protected function setup_subscription( $dokan_subscription ) {
        $product_pack = $dokan_subscription->get_product();
        $vendor_id    = $dokan_subscription->get_vendor();

        if ( empty( $vendor_id ) ) {
            $vendor_id = get_current_user_id();
        }

        UserMeta::update_product_pack_id( $vendor_id, $product_pack->get_id() );
        UserMeta::update_product_no_with_pack(
            $vendor_id,
            ProductMeta::set( $product_pack )->get_no_of_product()
        );

        $vendor_subscription = dokan()->vendor->get( $vendor_id )->subscription;
        $stripe_subscription = OrderMeta::get_stripe_subscription_id( $this->order );

        if ( empty( $stripe_subscription ) || ! $vendor_subscription ) {
            return false;
        }

        $subscription = Subscription::get( $stripe_subscription );

        if ( is_wp_error( $subscription ) || empty( $subscription->id ) ) {
            UserMeta::delete_product_pack_id( $vendor_id );
            UserMeta::delete_product_no_with_pack( $vendor_id );
            return false;
        }

        if ( $product_pack && $this->get_product_type() === $product_pack->get_type() ) {
            UserMeta::update_stripe_subscription_id( $vendor_id, $subscription->id );
            UserMeta::update_customer_recurring_subscription( $vendor_id );
        }

        return $subscription;
    }

    /**
     * Cancels stripe subscription.
     *
     * @since 3.7.8
     *
     * @param int|string $order_id
     * @param int|string $vendor_id
     * @param boolean    $cancel_immediately
     *
     * @return void
     */
    public function cancel_recurring_subscription( $order_id, $vendor_id, $cancel_immediately ) {
        $order = wc_get_order( $order_id );
        if ( ! $order || Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return;
        }

        $vendor_subscription = dokan()->vendor->get( $vendor_id )->subscription;

        if ( ! $vendor_subscription || ! $vendor_subscription->has_recurring_pack() ) {
            return;
        }

        if ( $cancel_immediately ) {
            return $this->cancel_now( $vendor_subscription );
        }

        $this->cancel_subscription( $vendor_subscription );
    }

    /**
     * Activates a vendor subscription
     *
     * @since 3.7.8
     *
     * @param int|string $order_id
     * @param int|string $vendor_id
     *
     * @return void
     */
    public function activate_subscription( $order_id, $vendor_id ) {
        $order = wc_get_order( $order_id );

        if ( ! $order || Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return;
        }

        $vendor_subscription = dokan()->vendor->get( $vendor_id )->subscription;
        $subscription_id     = UserMeta::get_stripe_subscription_id( $vendor_id );

        if ( ! $vendor_subscription || ! $vendor_subscription->has_recurring_pack() ) {
            return;
        }

        $subscription_data = [];
        $billing_cycle_end = $vendor_subscription->get_product_pack_end_date();

        if ( ! empty( $billing_cycle_end ) && 'unlimited' !== $billing_cycle_end ) {
            try {
                $subscription_data['cancel_at'] = dokan_current_datetime()->modify( $billing_cycle_end )->getTimestamp();
            } catch ( \Exception $e ) {
                Helper::log( 'Could not process the billing cycle stop date for Dokan Subscription', __METHOD__ );
            }
        } else {
            $subscription_data['cancel_at_period_end'] = false;
        }

        $subscription = Subscription::update( $subscription_data, $subscription_id );
        if ( is_wp_error( $subscription ) ) {
            Helper::log( sprintf( 'Unable to re-activate subscription with stripe. More details: %s', $subscription->get_error_message() ) );
            return;
        }

        // Add order re-activation note
        $order->add_order_note( __( 'Subscription reactivated.', 'dokan' ) );

        $vendor_subscription->reset_active_cancelled_subscription();
    }

    /**
     * Remove subscription forcefully. In case webhook is disabled or didn't work for some reason
     * Cancel the subscription in vendor's end. subscription is already removed in stripe's end.
     *
     * @since 3.7.8
     *
     * @param SubscriptionPack $vendor_subscription The vendor subscription pack object.
     *
     * @return void
     */
    public function remove_subscription( $vendor_subscription ) {
        $vendor_id = $vendor_subscription->get_vendor();
        $order_id  = UserMeta::get_product_order_id( $vendor_id );

        if ( $vendor_subscription->has_recurring_pack() ) {
            SubscriptionHelper::delete_subscription_pack( $vendor_id, $order_id );
            UserMeta::delete_stripe_subscription_id( $vendor_id );
        }
    }

    /**
     * Filters the flag to indicate whether or not order redirect should be processed.
     *
     * @since 3.7.8
     *
     * @param bool     $should_not_process
     * @param WC_order $order
     *
     * @return boolean
     */
    public function should_not_process_order_redirect( $should_not_process, $order ) {
        if ( ! $should_not_process || ! $order instanceof WC_Order ) {
            return $should_not_process;
        }

        if ( Subscription::is_recurring_vendor_subscription_order( $order->get_id() ) ) {
            return $should_not_process = false;
        }

        return $should_not_process;
    }

    /**
     * Processes subscription pack after saving.
     *
     * @since 3.7.8
     *
     * @param int $product_id
     *
     * @return void
     */
    public function process_subscription_after_save( $product_id ) {
        if ( ! Helper::is_gateway_ready() ) {
            return;
        }

        global $pagenow;
        $screen = get_current_screen();

        if ( ! current_user_can( 'manage_woocommerce' ) && ( 'edit' !== $screen->parent_base || 'post.php' !== $pagenow ) ) {
            return;
        }

        if ( ! SubscriptionHelper::is_recurring_subscription_product( $product_id ) ) {
            return;
        }

        // If product already exists in Stripe, no further processing is needed.
        if ( ProductMeta::set( $product_id )->get_stripe_product_id() ) {
            return;
        }

        $product = Subscription::create_product( $product_id );

        if ( is_wp_error( $product ) ) {
            Helper::log( 'Could not process subscription after save. Error: ' . $product->get_error_message() );
            return;
        }
    }

    /**
     * Empty cart if the vendor is already in a subscription.
     *
     * @since 3.7.8
     *
     * @param boolean    $is_valid
     * @param int|string $product_id
     * @param int        $quantity
     *
     * @return mixed
     */
    public function maybe_empty_cart( $is_valid, $product_id, $quantity ) {
        if ( ! SubscriptionHelper::is_subscription_product( $product_id ) ) {
            return $is_valid;
        }

        // check if user has active subscription pack
        $vendor_id           = dokan_get_current_user_id();
        $vendor_subscription = dokan()->vendor->get( $vendor_id )->subscription;

        if ( ! $vendor_subscription instanceof SubscriptionPack || ! $vendor_subscription->has_subscription() || $vendor_subscription->has_active_cancelled_subscrption() ) {
            return $is_valid;
        }

        // get current users subscription order
        $subscription_order = SubscriptionHelper::get_subscription_order( $vendor_id );
        if ( ! $subscription_order || $subscription_order->get_payment_method() !== Helper::get_gateway_id() ) {
            return $is_valid;
        }

        WC()->cart->empty_cart();

        wc_add_notice( __( 'You are already under a subscription plan. You need to cancel it first.', 'dokan' ) );

        $page_url = dokan_get_navigation_url( 'subscription' );

        if ( wp_safe_redirect( add_query_arg( [ 'already-has-subscription' => 'true' ], $page_url ) ) ) {
            exit;
        }
    }

    /**
     * Checks if non-recurring Subscription should be canceled Immediately.
     *
     * @since 3.7.8
     *
     * @param bool $cancel_immediately
     * @param int  $order_id
     * @param int  $vendor_id
     *
     * @return boolean
     */
    public function should_cancel_non_recurring_subscription_immediately( $cancel_immediately, $order_id ) {
        if ( true === $cancel_immediately ) {
            return $cancel_immediately;
        }

        $order = wc_get_order( $order_id );
        if ( ! $order || Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return $cancel_immediately;
        }

        /*
         * If $cancel_immediately is false,
         * check if this hook was called from frontend,
         * if so cancel subscription immediately.
         */
        if (
            isset( $_POST['dps_cancel_subscription'], $_POST['_wpnonce'] ) &&
            wp_verify_nonce( sanitize_key( wp_unslash( $_POST['_wpnonce'] ) ), 'dps-sub-cancel' )
        ) {
            $cancel_immediately = true;
        }

        return $cancel_immediately;
    }

    /**
     * Cancel main order before cancelling non-recurring subscription.
     *
     * @since 3.7.8
     *
     * @param int $vendor_id
     * @param int $product_id
     * @param int $order_id
     *
     * @return void
     */
    public function before_cancelling_subscriptions( $vendor_id, $product_id, $order_id ) {
        $order = wc_get_order( $order_id );

        if ( ! $order || Helper::get_gateway_id() !== $order->get_payment_method() ) {
            return;
        }

        $subscription = new SubscriptionPack( $product_id, $vendor_id );

        if ( ! $subscription->is_recurring() ) {
            $order->add_order_note( __( 'Subscription Cancelled.', 'dokan' ) );
        }
    }

    /**
     * Restricts cancelling unpaid orders
     * if order is vendor subscription order.
     *
     * @since 3.7.8
     *
     * @param boolean  $cancel
     * @param WC_Order $order
     *
     * @return boolean
     */
    public function should_cancel_unpaid_order( $cancel, $order ) {
        if ( OrderMeta::is_vendor_subscription( $order ) ) {
            return false;
        }

        return $cancel;
    }

    /**
     * Change notice to vendors for recurring payments.
     *
     * @since 3.7.8
     *
     * @param string   $message
     * @param WC_Order $order
     *
     * @return string
     */
    public function suborder_notice_to_vendor( $message, $order ) {
        if ( OrderMeta::is_vendor_subscription( $order ) ) {
            $message = esc_html__( 'Vendor Subscriptions Related Orders.', 'dokan' );
        }

        return $message;
    }

    /**
     * For vendor subscription, the tax fee recipient will be admin.
     *
     * @since 3.7.8
     *
     * @param string $recipient
     * @param int    $order_id
     *
     * @return string
     */
    public function tax_fee_recipient( $recipient, $order_id ) {
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return $recipient;
        }

        if ( $order->get_payment_method() !== Helper::get_gateway_id() ) {
            return $recipient;
        }

        if ( OrderMeta::is_vendor_subscription( $order ) ) {
            return 'admin';
        }

        return $recipient;
    }

    /**
     * For vendor subscription, the shipping fee recipient will be admin.
     *
     * @since 3.7.8
     *
     * @param string $recipient
     * @param int    $order_id
     *
     * @return string
     */
    public function shipping_fee_recipient( $recipient, $order_id ) {
        $order = wc_get_order( $order_id );

        if ( ! $order ) {
            return $recipient;
        }

        if ( $order->get_payment_method() !== Helper::get_gateway_id() ) {
            return $recipient;
        }

        if ( OrderMeta::is_vendor_subscription( $order ) ) {
            return 'admin';
        }

        return $recipient;
    }

    /**
     * Modifies and retrieves order button text.
     *
     * @since 3.7.8
     *
     * @param string $btn_text
     *
     * @return string
     */
    public function get_order_button_text( $btn_text ) {
        if ( ! Subscription::cart_contains_vendor_subscription() ) {
            return $btn_text;
        }

        return __( 'Subscribe', 'dokan' );
    }

    /**
     * Loads email classes.
     *
     * @since 3.7.8
     *
     * @param array $emails
     *
     * @return array
     */
    public function load_emails( $emails ) {
        $emails['InvoiceAuthentication'] = new \WeDevs\DokanPro\Modules\StripeExpress\Emails\InvoiceAuthentication();
        return $emails;
    }

    /**
     * Loads email actions.
     *
     * @since 3.7.8
     *
     * @param array $actions
     *
     * @return array
     */
    public function load_email_actions( $actions ) {
        $actions[] = 'dokan_stripe_express_invoice_payment_action_required';
        return $actions;
    }
}
