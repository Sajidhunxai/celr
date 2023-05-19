<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Emails;

defined( 'ABSPATH' ) || exit;

use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\AuthenticationEmail;

/**
 * Invoice email handler class.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Emails
 */
class InvoiceAuthentication extends AuthenticationEmail {

    /**
    * Class constructor.
    *
    * @since 3.7.8
    *
    * @return void
    */
    public function __construct() {
        $this->id             = 'dokan_stripe_express_email_invoice_authentication';
        $this->title          = __( 'Dokan Stripe Express Subscription Invoice Authentication', 'dokan' );
        $this->description    = __( 'This email is set to be sent to a vendor when a `payment action is required` for subscription renew.', 'dokan' );
        $this->template_html  = 'emails/invoice-authentication.php';
        $this->template_plain = 'emails/plain/invoice-authentication.php';
        $this->template_base  = DOKAN_STRIPE_EXPRESS_TEMPLATE_PATH;
        $this->object_type    = 'invoice';

        add_action( 'dokan_stripe_express_invoice_payment_action_required', [ $this, 'trigger' ], 10, 2 );

        parent::__construct();
        $this->recipient = 'product@subscribed.vendor';
    }

    /**
    * Retrieves default email subject.
    *
    * @since 3.7.8
    *
    * @return string
    */
    public function get_default_subject() {
        return __( '[{site_title}] Subscription Bill Payment', 'dokan' );
    }

    /**
    * Retrieves default email heading.
    *
    * @since 3.7.8
    *
    * @return string
    */
    public function get_default_heading() {
        return __( 'Please pay the subscription bill and confirm payment method.', 'dokan' );
    }

    /**
     * Generates the URL, which will be used to authenticate the payment.
     *
     * @since 3.7.8
     *
     * @param object $invoice_data
     *
     * @return string
     */
    public function get_authorization_url( $invoice_data ) {
        return isset( $invoice_data->invoice_url ) ? $invoice_data->invoice_url : '';
    }

    /**
     * Triggers the email.
     *
     * @since 3.7.8
     *
     * @param \Stripe\Invoice $invoice
     * @param string          $vendor_id
     *
     * @return void
     */
    public function trigger( $invoice, $vendor_id ) {
        $vendor = dokan()->vendor->get( $vendor_id );
        $this->object = new \stdClass();

        if ( $vendor->get_id() ) {
            $this->object->vendor_name = $vendor->get_name();
            $this->object->email       = $vendor->get_email();
            $this->object->invoice_url = $invoice->hosted_invoice_url;
        }

        $this->recipient = ! empty( $this->object->email ) && is_email( $this->object->email ) ? $this->object->email : null;

        $this->send_email();
    }
}
