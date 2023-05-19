<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Subscriptions;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WC_Order;
use Exception;
use WC_Subscription;
use WC_Subscriptions_Change_Payment_Gateway;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Support\UserMeta;
use WeDevs\DokanPro\Modules\StripeExpress\Support\OrderMeta;
use WeDevs\DokanPro\Modules\StripeExpress\PaymentMethods\Card;
use WeDevs\DokanPro\Modules\StripeExpress\PaymentMethods\Sepa;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Customer;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\Subscription;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Traits\SubscriptionUtils;

/**
 * Vendor subscription handler class.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Subscription
 */
class ProductSubscription {

    use SubscriptionUtils;

    /**
     * Gateway ID.
     *
     * @since 3.7.8
     *
     * @var string
     */
    private $gateway_id;

    /**
     * Class constructor.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function __construct() {
        $this->gateway_id = Helper::get_gateway_id();
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
        if ( ! Subscription::has_wc_subscription() ) {
            return;
        }

        // Add subscription support for Stripe Express
        add_filter( 'dokan_stripe_express_gateway_support', [ $this, 'add_subscription_support_for_gateway' ] );
        add_filter( 'dokan_stripe_express_payment_request_supported_types', [ $this, 'add_payment_request_supported_types' ] );
        add_action( 'dokan_stripe_express_payment_fields', [ $this, 'show_update_subscription_checkbox' ] );
        add_filter( 'dokan_stripe_express_display_save_payment_method_checkbox', [ $this, 'display_save_payment_method_checkbox' ], 10, 2 );
        add_action( 'dokan_stripe_express_add_payment_method_success', [ $this, 'handle_add_payment_method_success' ], 10, 2 );
        add_action( 'dokan_stripe_express_save_payment_method_data', [ $this, 'update_payment_method_on_subscription_order' ], 10, 2 );
        // Manage wc subscription hooks
        add_action( 'wcs_resubscribe_order_created', [ $this, 'delete_resubscribe_meta' ], 10 );
        add_action( 'wcs_renewal_order_created', [ $this, 'delete_renewal_meta' ], 10 );
        add_action( "woocommerce_scheduled_subscription_payment_{$this->gateway_id}", [ $this, 'process_scheduled_subscription_payment' ], 10, 2 );
        add_action( "woocommerce_subscription_failing_payment_method_updated_{$this->gateway_id}", [ $this, 'update_failed_payment_meta' ], 10, 2 );
        add_action( 'woocommerce_subscriptions_change_payment_before_submit', [ $this, 'differentiate_change_payment_method_form' ] );
        // Display the payment method used for a subscription in the "My Subscriptions" table.
        add_filter( 'woocommerce_my_subscriptions_payment_method', [ $this, 'render_subscription_payment_method' ], 10, 2 );
        // Allow store managers to manually set Stripe as the payment method on a subscription.
        add_filter( 'woocommerce_subscription_payment_meta', [ $this, 'add_subscription_payment_meta' ], 10, 2 );
        add_filter( 'woocommerce_subscription_validate_payment_meta', [ $this, 'validate_subscription_payment_meta' ], 10, 2 );

        // Load email classes and actions
        add_filter( 'woocommerce_email_classes', [ $this, 'load_emails' ] );
        add_filter( 'woocommerce_email_actions', [ $this, 'load_email_actions' ] );

        /*
         * WC subscriptions hooks into the "template_redirect" hook with priority 100.
         * If the screen is "Pay for order" and the order is a subscription renewal, it redirects to the plain checkout.
         *
         * @see https://github.com/woocommerce/woocommerce-subscriptions/blob/99a75687e109b64cbc07af6e5518458a6305f366/includes/class-wcs-cart-renewal.php#L165
         *
         * If we are in the "You just need to authorize SCA" flow, we don't want that redirection to happen.
         */
        add_action( 'template_redirect', [ $this, 'remove_order_pay_var' ], 99 );
        add_action( 'template_redirect', [ $this, 'restore_order_pay_var' ], 101 );
    }

    /**
     * Adds subscription support for Stripe Express.
     *
     * @since 3.7.8
     *
     * @param array $supports
     *
     * @return array
     */
    public function add_subscription_support_for_gateway( $supports ) {
        return array_merge(
            $supports,
            [
                'subscriptions',
                'subscription_cancellation',
                'subscription_suspension',
                'subscription_reactivation',
                'subscription_amount_changes',
                'subscription_date_changes',
                'subscription_payment_method_change',
                'subscription_payment_method_change_customer',
                'subscription_payment_method_change_admin',
                'multiple_subscriptions',
            ]
        );
    }

    /**
     * Adds subscription product type support for Payment request API.
     *
     * @since 3.7.8
     *
     * @param array $supported_types
     *
     * @return array
     */
    public function add_payment_request_supported_types( $supported_types ) {
        return array_merge(
            $supported_types,
            [
                'subscription',
                'variable-subscription',
                'subscription_variation',
            ]
        );
    }

    /**
     * Don't transfer Stripe customer/token meta to resubscribe orders.
     *
     * @since 3.7.8
     *
     * @param WC_Order $resubscribe_order The order created for the customer to resubscribe to the old expired/cancelled subscription
     *
     * @return void
     */
    public function delete_resubscribe_meta( $resubscribe_order ) {
        OrderMeta::delete_customer_id( $resubscribe_order );
        OrderMeta::delete_payment_method_id( $resubscribe_order );
        OrderMeta::save( $resubscribe_order );

        $this->delete_renewal_meta( $resubscribe_order );
    }

    /**
     * Don't transfer Stripe fee/ID meta to renewal orders.
     *
     * @since 3.7.8
     *
     * @param WC_Order $renewal_order
     *
     * @return WC_Order
     */
    public function delete_renewal_meta( $renewal_order ) {
        OrderMeta::delete_stripe_fee( $renewal_order );
        OrderMeta::delete_payment_intent( $renewal_order );
        OrderMeta::save( $renewal_order );

        return $renewal_order;
    }

    /**
     * Manages the scheduled subscription payment.
     *
     * @since 3.7.8
     *
     * @param float    $amount        The amount to charge.
     * @param WC_Order $renewal_order Order object created to record the renewal payment.
     *
     * @return void
     */
    public function process_scheduled_subscription_payment( $amount, $renewal_order ) {
        $this->process_subscription_payment( $amount, $renewal_order, true, false );
    }

    /**
     * Update postmeta for subscription after using Stripe to complete a payment to make up for
     * an automatic renewal payment which previously failed.
     *
     * @since 3.7.8
     *
     * @param WC_Subscription $subscription The subscription for which the failing payment method relates.
     * @param WC_Order        $renewal_order The order which recorded the successful payment (to make up for the failed automatic payment).
     *
     * @return void
     */
    public function update_failed_payment_meta( $subscription, $renewal_order ) {
        OrderMeta::update_customer_id( $subscription, OrderMeta::get_customer_id( $renewal_order ) );
        OrderMeta::update_payment_method_id( $subscription, OrderMeta::get_payment_method_id( $renewal_order ) );
        OrderMeta::save( $subscription );
    }

    /**
     * Updates payment method data for subscription order.
     * It operates as soon as an subscription order is initiated
     * and payment method is updated for that order.
     *
     * @since 3.7.8
     *
     * @param WC_Order              $order
     * @param \Stripe\PaymentMethod $payment_method
     *
     * @return void
     */
    public function update_payment_method_on_subscription_order( $order, $payment_method ) {
        // Also store it on the subscriptions being purchased or paid for in the order
        $subscriptions = Subscription::get_wc_subscriptions_from_order( $order );

        foreach ( $subscriptions as $subscription ) {
            if ( $subscription instanceof WC_Subscription ) {
                OrderMeta::update_customer_id( $subscription, $payment_method->customer );
                OrderMeta::update_payment_method_id( $subscription, $payment_method->id );
                OrderMeta::save( $subscription );
            }
        }
    }

    /**
     * Displays a checkbox to allow users
     * to update all subs payments with new payment.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function show_update_subscription_checkbox() {
        if (
            apply_filters( 'dokan_stripe_express_display_update_subs_payment_method_card_checkbox', true ) &&
            is_add_payment_method_page() &&
            wcs_user_has_subscription( get_current_user_id(), '', $this->get_payment_method_card_statuses() )
        ) {
            woocommerce_form_field(
                "wc-{$this->gateway_id}-update-subs-payment-method-card",
                [
                    'type'    => 'checkbox',
                    'label'   => esc_html__( 'Update the Payment Method used for all of my active subscriptions.', 'dokan' ),
                    'default' => apply_filters( 'dokan_stripe_express_save_to_subscription_checked', false ),
                ]
            );
        }
    }

    /**
     * Returns a boolean value indicating whether the save payment checkbox should be
     * displayed during checkout.
     *
     * Returns `false` if the cart currently has a subscriptions or if the request has a
     * `change_payment_method` GET parameter. Returns the value in `$display` otherwise.
     *
     * @since 3.7.8
     *
     * @param bool $display Indicating whether to show the save payment checkbox in the absence of subscriptions.
     *
     * @return bool Indicates whether the save payment method checkbox should be displayed or not.
     */
    public function display_save_payment_method_checkbox( $display ) {
        // Only render the "Save payment method" checkbox if there are no subscription products in the cart.
        if ( \WC_Subscriptions_Cart::cart_contains_subscription() || Subscription::is_changing_payment_method() ) {
            return false;
        }

        return $display;
    }

    /**
     * Updates all active subscriptions payment method.
     *
     * @since 3.7.8
     *
     * @param string $payment_method_id
     * @param object $payment_method_object
     *
     * @return void
     */
    public function handle_add_payment_method_success( $payment_method_id, $payment_method_object ) {
        if ( ! isset( $_POST[ "wc-{$this->gateway_id}-update-subs-payment-method-card" ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
            return;
        }

        $subscriptions = wcs_get_users_subscriptions();
        if ( empty( $subscriptions ) ) {
            return;
        }

        $subscr_statuses = $this->get_payment_method_card_statuses();
        $stripe_customer = Customer::set( get_current_user_id() );

        foreach ( $subscriptions as $subscription ) {
            if ( ! $subscription->has_status( $subscr_statuses ) ) {
                continue;
            }

            WC_Subscriptions_Change_Payment_Gateway::update_payment_method(
                $subscription,
                $this->gateway_id,
                [
                    'post_meta' => [
                        OrderMeta::payment_method_id_key()   => [
                            'value' => $payment_method_id,
                        ],
                        OrderMeta::customer_id_key() => [
                            'value' => $stripe_customer->get_id(),
                        ],
                    ],
                ]
            );
        }
    }

    /**
     * Render a dummy element in the "Change payment method" form (that does not appear in the "Pay for order" form)
     * which can be checked to determine proper SCA handling to apply for each form.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function differentiate_change_payment_method_form() {
        echo '<input type="hidden" id="wc-' . $this->gateway_id . '-change-payment-method" />';
    }

    /**
     * Render the payment method used for a subscription in the "My Subscriptions" table
     *
     * @since 3.7.8
     *
     * @param string          $payment_method_to_display the default payment method text to display
     * @param WC_Subscription $subscription the subscription details
     *
     * @return string the subscription payment method
     */
    public function render_subscription_payment_method( $payment_method_to_display, $subscription ) {
        $customer = $subscription->get_customer_id();

        // Bail if not this payment method
        if ( $subscription->get_payment_method() !== $this->gateway_id || ! $customer ) {
            return $payment_method_to_display;
        }

        $stripe_payment_method     = OrderMeta::get_payment_method_id( $subscription );
        $stripe_customer_id        = OrderMeta::get_customer_id( $subscription );
        $payment_method_to_display = __( 'N/A', 'dokan' );

        // If we couldn't find a Stripe customer linked to the subscription, fallback to the user meta data.
        if ( empty( $stripe_customer_id ) ) {
            $stripe_customer_id = UserMeta::get_stripe_customer_id( $customer );
        }

        // If we couldn't find a Stripe customer linked to the account, fallback to the parent order meta data.
        if ( empty( $stripe_customer_id ) && false !== $subscription->get_parent() ) {
            $parent_subscription   = wcs_get_subscription( $subscription->get_parent_id() );
            $stripe_payment_method = OrderMeta::get_payment_method_id( $parent_subscription );
            $stripe_customer_id    = OrderMeta::get_customer_id( $parent_subscription );
        }

        if ( empty( $stripe_payment_method ) ) {
            return $payment_method_to_display;
        }

        $stripe_customer = Customer::set();
        $stripe_customer->set_id( $stripe_customer_id );

        // Retrieve all possible payment methods for subscriptions.
        $payment_methods = array_merge(
            $stripe_customer->get_payment_methods( Card::STRIPE_ID ),
            $stripe_customer->get_payment_methods( Sepa::STRIPE_ID )
        );

        if ( empty( $payment_methods ) ) {
            return $payment_method_to_display;
        }

        foreach ( $payment_methods as $payment_method ) {
            if ( $payment_method->id !== $stripe_payment_method ) {
                continue;
            }

            switch ( $payment_method->type ) {
                case Card::STRIPE_ID:
                    $payment_method_to_display = sprintf(
                        /* translators: 1) card brand 2) last 4 digits */
                        __( 'Via %1$s card ending in %2$s', 'dokan' ),
                        isset( $payment_method->card->brand ) ? $payment_method->card->brand : __( 'N/A', 'dokan' ),
                        $payment_method->card->last4
                    );
                    break;

                case Sepa::STRIPE_ID:
                    $payment_method_to_display = sprintf(
                        /* translators: 1) last 4 digits of SEPA Direct Debit */
                        __( 'Via SEPA Direct Debit ending in %1$s', 'dokan' ),
                        $payment_method->sepa_debit->last4
                    );
                    break;
            }

            break;
        }

        return $payment_method_to_display;
    }

    /**
     * Includes the payment meta data required to process automatic recurring payments so that store managers can
     * manually set up automatic recurring payments for a customer via the Edit Subscriptions screen in 2.0+.
     *
     * @since 3.7.8
     *
     * @param array           $payment_meta associative array of meta data required for automatic payments
     * @param WC_Subscription $subscription An instance of a subscription object
     *
     * @return array
     */
    public function add_subscription_payment_meta( $payment_meta, $subscription ) {
        $payment_meta[ $this->gateway_id ] = [
            'post_meta' => [
                OrderMeta::customer_id_key() => [
                    'value' => OrderMeta::get_customer_id( $subscription ),
                    'label' => 'Stripe Customer ID',
                ],
                OrderMeta::payment_method_id_key()   => [
                    'value' => OrderMeta::get_payment_method_id( $subscription ),
                    'label' => 'Stripe Payment Method ID',
                ],
            ],
        ];

        return $payment_meta;
    }

    /**
     * Validates the payment meta data required to process automatic recurring payments so that store managers can
     * manually set up automatic recurring payments for a customer via the Edit Subscriptions screen in 2.0+.
     *
     * @since 3.7.8
     *
     * @param string $payment_method_id The ID of the payment method to validate
     * @param array  $payment_meta associative array of meta data required for automatic payments
     *
     * @return array
     */
    public function validate_subscription_payment_meta( $payment_method_id, $payment_meta ) {
        if ( $this->gateway_id !== $payment_method_id ) {
            return;
        }

        $customer_id_key       = OrderMeta::customer_id_key();
        $payment_method_id_key = OrderMeta::payment_method_id_key();

        if ( empty( $payment_meta['post_meta'][ $customer_id_key ]['value'] ) ) {
            // Allow empty stripe customer id during subscription renewal. It will be added when processing payment if required.
            if ( ! isset( $_POST['wc_order_action'] ) || 'wcs_process_renewal' !== $_POST['wc_order_action'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing
                throw new Exception( __( 'A "Stripe Customer ID" value is required.', 'dokan' ) );
            }
        } elseif ( 0 !== strpos( $payment_meta['post_meta'][ $customer_id_key ]['value'], 'cus_' ) ) {
            throw new Exception( __( 'Invalid customer ID. A valid "Stripe Customer ID" must begin with "cus_".', 'dokan' ) );
        }

        if (
            ! empty( $payment_meta['post_meta'][ $payment_method_id_key ]['value'] ) &&
            (
                0 !== strpos( $payment_meta['post_meta'][ $payment_method_id_key ]['value'], 'card_' ) &&
                0 !== strpos( $payment_meta['post_meta'][ $payment_method_id_key ]['value'], 'src_' ) &&
                0 !== strpos( $payment_meta['post_meta'][ $payment_method_id_key ]['value'], 'pm_' )
            )
        ) {
            throw new Exception( __( 'Invalid source ID. A valid source "Stripe Source ID" must begin with "src_", "pm_", or "card_".', 'dokan' ) );
        }
    }

    /**
     * If this is the "Pass the SCA challenge" flow, remove a variable that is checked by WC Subscriptions
     * so WC Subscriptions doesn't redirect to the checkout
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function remove_order_pay_var() {
        global $wp;
        if ( isset( $_GET['dokan_stripe_express_confirmation'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
            $this->order_pay_var         = $wp->query_vars['order-pay'];
            $wp->query_vars['order-pay'] = null;
        }
    }

    /**
     * Restore the variable that was removed in remove_order_pay_var()
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function restore_order_pay_var() {
        global $wp;
        if ( isset( $this->order_pay_var ) ) {
            $wp->query_vars['order-pay'] = $this->order_pay_var;
        }
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
        $emails['RenewalAuthentication']      = new \WeDevs\DokanPro\Modules\StripeExpress\Emails\RenewalAuthentication();
        $emails['RenewalAuthenticationRetry'] = new \WeDevs\DokanPro\Modules\StripeExpress\Emails\RenewalAuthenticationRetry();
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
        $actions[] = 'dokan_stripe_express_process_payment_authentication_required';
        return $actions;
    }
}
