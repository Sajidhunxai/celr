<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Emails;

defined( 'ABSPATH' ) || exit;

use WeDevs\DokanPro\Modules\StripeExpress\Processors\Subscription;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\AuthenticationEmail;

/**
 * Subscription failed renewal authentication handler class.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Emails
 */
class RenewalAuthentication extends AuthenticationEmail {

    /**
    * Class constructor.
    *
    * @since 3.7.8
    *
    * @return void
    */
    public function __construct() {
        $this->id             = 'dokan_stripe_express_email_renewal_order_authentication';
        $this->title          = __( 'Dokan Stripe Express Subscription Renewal SCA Authentication', 'dokan' );
        $this->description    = __( 'Sent to a customer when a renewal fails because the transaction requires an SCA verification. The email contains renewal order information and payment links.', 'dokan' );
        $this->customer_email = true;
        $this->template_html  = 'emails/renewal-authentication.php';
        $this->template_plain = 'emails/plain/renewal-authentication.php';
        $this->template_base  = DOKAN_STRIPE_EXPRESS_TEMPLATE_PATH;
        $this->recipient      = $this->get_option( 'recipient', get_option( 'admin_email' ) );

        if ( isset( $email_classes['WCS_Email_Customer_Renewal_Invoice'] ) ) {
            $this->original_email = $email_classes['WCS_Email_Customer_Renewal_Invoice'];
        }

        add_action( 'dokan_stripe_express_process_payment_authentication_required', [ $this, 'trigger' ], 10, 2 );

        parent::__construct();
    }

    /**
     * Returns the default subject of the email (modifyable in settings).
     *
     * @since 3.7.8
     *
     * @return string
     */
    public function get_default_subject() {
        return __( '[{site_title}] Payment authorization needed for renewal of order {order_number}', 'dokan' );
    }

    /**
     * Returns the default heading of the email (modifyable in settings).
     *
     * @since 3.7.8
     *
     * @return string
     */
    public function get_default_heading() {
        return __( 'Payment authorization needed for renewal of order {order_number}', 'dokan' );
    }

    /**
     * Triggers the email while also disconnecting the original Subscriptions email.
     *
     * @since 3.7.8
     *
     * @param WC_Order              $order
     * @param \Stripe\PaymentIntent $intent (Optional)
     *
     * @return void
     */
    public function trigger( $order, $intent = null ) {
        $this->object       = $order;
        $this->recipient    = $order->get_billing_email();
        $this->placeholders = [
            '{order_date}'   => dokan_format_date( $order->get_date_created() ),
            '{order_number}' => $order->get_order_number(),
        ];

        if ( ! Subscription::is_wc_subscription_order( $order->get_id() ) ) {
            return;
        }

        $this->send_email();

        // Prevent the renewal email from WooCommerce Subscriptions from being sent.
        if ( isset( $this->original_email ) ) {
            remove_action( 'woocommerce_generated_manual_renewal_order_renewal_notification', [ $this->original_email, 'trigger' ] );
            remove_action( 'woocommerce_order_status_failed_renewal_notification', [ $this->original_email, 'trigger' ] );
        }

        // Prevent the retry email from WooCommerce Subscriptions from being sent.
        add_filter( 'wcs_get_retry_rule_raw', [ $this, 'prevent_retry_notification_email' ], 100, 3 );

        // Send email to store owner indicating communication is happening with the customer to request authentication.
        add_filter( 'wcs_get_retry_rule_raw', [ $this, 'set_store_owner_custom_email' ], 100, 3 );
    }

    /**
     * Prevent all customer-facing retry notifications from being sent after this email.
     *
     * @since 3.7.8
     *
     * @param array $rule_array   The raw details about the retry rule.
     * @param int   $retry_number The number of the retry.
     * @param int   $order_id     The ID of the order that needs payment.
     *
     * @return array
     */
    public function prevent_retry_notification_email( $rule_array, $retry_number, $order_id ) {
        if ( wcs_get_objects_property( $this->object, 'id' ) === $order_id ) {
            $rule_array['email_template_customer'] = '';
        }

        return $rule_array;
    }

    /**
     * Send store owner a different email when the retry is related to an authentication required error.
     *
     * @since 3.7.8
     *
     * @param array $rule_array   The raw details about the retry rule.
     * @param int   $retry_number The number of the retry.
     * @param int   $order_id     The ID of the order that needs payment.
     *
     * @return array
     */
    public function set_store_owner_custom_email( $rule_array, $retry_number, $order_id ) {
        if (
            wcs_get_objects_property( $this->object, 'id' ) === $order_id &&
            '' !== $rule_array['email_template_admin'] // Only send our email if a retry admin email was already going to be sent.
        ) {
            $rule_array['email_template_admin'] = '\WeDevs\DokanPro\Modules\StripeExpress\Emails\RenewalAuthenticationRetry';
        }

        return $rule_array;
    }
}
