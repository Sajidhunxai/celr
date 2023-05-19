<?php

namespace WeDevs\DokanPro\Modules\Stripe\Subscriptions;

use WC_Email;
use stdClass;

defined( 'ABSPATH' ) || exit;

class InvoiceEmail extends WC_Email {

    /**
    * Constructor method
    *
    * @since 3.0.3
    *
    * @return void
    */
    public function __construct() {
        $this->id            = 'dokan_email_subscription_invoice';
        $this->title         = __( 'Dokan Subscription Invoice', 'dokan' );
        $this->description   = __( 'This email is set to a vendor when a `payment action is required` for subscription renew.', 'dokan' );
        $this->template_html = 'emails/invoice.php';
        $this->template_base = DOKAN_STRIPE_TEMPLATE_PATH;

        add_action( 'dokan_invoice_payment_action_required', [ $this, 'trigger' ], 10, 2 );

        parent::__construct();
        $this->recipient = 'product@subscribed.vendor';
    }

    /**
    * Get email subject
    *
    * @since 3.0.3
    *
    * @return string
    */
    public function get_default_subject() {
        return __( '[{site_title}] Subscription Bill Payment', 'dokan' );
    }

    /**
    * Get email heading
    *
    * @since 3.0.3
    *
    * @return string
    */
    public function get_default_heading() {
        return __( 'Please pay the subscription bill and confirm payment method.', 'dokan' );
    }

    /**
    * Trigger the this email.
    *
    * @since 3.0.3
    *
    * @return void
    */
    public function trigger( $vendor_id, $invoice ) {
        $this->setup_locale();

        $vendor = dokan()->vendor->get( $vendor_id );

        if ( $vendor->get_id() ) {
            $this->object              = new stdClass;
            $this->object->vendor_name = $vendor->get_name();
            $this->object->email       = $vendor->get_email();
            $this->object->invoice_url = $invoice->hosted_invoice_url;
        }

        if ( $this->is_enabled() && $this->get_recipient() ) {
            $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }

        $this->restore_locale();
    }

    /**
     * Get recipient email
     *
     * @since 3.0.3
     *
     * @return string
     */
    public function get_recipient() {
        return ! empty( $this->object->email ) && is_email( $this->object->email ) ? $this->object->email : null;
    }

    /**
    * Get content html
    *
    * @since 3.0.3
    *
    * @return string
    */
    public function get_content_html() {
        return wc_get_template_html(
            $this->template_html,
            [
                'invoice'       => $this->object,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => true,
                'plain_text'    => false,
                'email'         => $this,
            ],
            'dokan/',
            $this->template_base
        );
    }

    /**
    * Initialize settings form fields
    *
    * @since 3.0.3
    *
    * @return void
    */
    public function init_form_fields() {
        $this->form_fields = [
            'enabled' => [
                'title'   => __( 'Enable/Disable', 'dokan' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this email notification', 'dokan' ),
                'default' => 'yes',
            ],
            'subject' => [
                'title'         => __( 'Subject', 'dokan' ),
                'type'          => 'text',
                'desc_tip'      => true,
                'description'   => sprintf( __( 'Available placeholders: %s', 'dokan' ), '<code>{site_title}</code>' ),
                'placeholder'   => $this->get_default_subject(),
            ],
            'heading' => [
                'title'         => __( 'Email heading', 'dokan' ),
                'type'          => 'text',
                'desc_tip'      => true,
                'description'   => sprintf( __( 'Available placeholders: %s', 'dokan' ), '<code>{site_title}</code>' ),
                'placeholder'   => $this->get_default_heading(),
            ],
            'email_type'       => [
                'title'       => __( 'Email type', 'dokan' ),
                'type'        => 'select',
                'description' => __( 'Choose which format of email to send.', 'dokan' ),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => [ 'html' => __( 'HTML', 'dokan' ) ],
                'desc_tip'    => true,
            ],
        ];
    }
}
