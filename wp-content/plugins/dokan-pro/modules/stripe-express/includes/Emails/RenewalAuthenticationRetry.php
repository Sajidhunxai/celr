<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Emails;

defined( 'ABSPATH' ) || exit;

use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\AuthenticationEmail;

/**
 * Subscription failed renewal handler class.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Emails
 */
class RenewalAuthenticationRetry extends AuthenticationEmail {

    /**
    * Class constructor.
    *
    * @since 3.7.8
    *
    * @return void
    */
    public function __construct() {
        $this->id             = 'dokan_stripe_express_email_renewal_order_authentication_requested';
        $this->title          = __( 'Dokan Stripe Express Subscription Renewal Order Authentication Requested', 'dokan' );
        $this->description    = __( 'Payment authentication requested emails are sent to chosen recipient(s) when an attempt to automatically process a subscription renewal payment fails because the transaction requires an SCA verification, the customer is requested to authenticate the payment, and a retry rule has been applied to notify the customer again within a certain time period.', 'dokan' );
        $this->heading        = __( 'Automatic renewal payment failed due to authentication required', 'dokan' );
        $this->subject        = __( '[{site_title}] Automatic payment failed for {order_number}. Customer asked to authenticate payment and will be notified again {retry_time}', 'dokan' );
        $this->template_html  = 'emails/renewal-authentication-requested.php';
        $this->template_plain = 'emails/plain/renewal-authentication-requested.php';
        $this->template_base  = DOKAN_STRIPE_EXPRESS_TEMPLATE_PATH;
        $this->recipient      = $this->get_option( 'recipient', get_option( 'admin_email' ) );

        parent::__construct();
    }

    /**
     * Retrieves the default email subject.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public function get_default_subject() {
        return $this->subject;
    }

    /**
     * Retrieves the default email heading.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public function get_default_heading() {
        return $this->heading;
    }

    /**
     * Triggers the email.
     *
     * @since 3.7.8
     *
     * @param int      $order_id
     * @param \WC_Order $order    (Optional)
     *
     * @return void
     */
    public function trigger( $order_id, $order = null ) {
        if ( ! $order instanceof \WC_Order ) {
            return;
        }

        $this->object = $order;

        if ( class_exists( 'WCS_Retry_Manager' ) && function_exists( 'wcs_get_human_time_diff' ) ) {
            $this->retry = \WCS_Retry_Manager::store()->get_last_retry_for_order( wcs_get_objects_property( $order, 'id' ) );
            $this->template_data['retry']       = $this->retry;
            $this->placeholders['{retry_time}'] = wcs_get_human_time_diff( $this->retry->get_time() );
        } else {
            Helper::log( 'WCS_Retry_Manager class or does not exist. Not able to send admnin email about customer notification for authentication required for renewal payment.' );
            return;
        }

        $this->placeholders['{order_number}'] = $order->get_order_number();

        $this->send_email();
    }
}
