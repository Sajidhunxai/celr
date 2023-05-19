<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WC_Email;
use WC_Order;

/**
 * Authentication email handler class.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts
 */
abstract class AuthenticationEmail extends WC_Email {

    /**
     * An instance of the email, which would normally be sent after a failed payment.
     *
     * @since 3.7.8
     *
     * @var WC_Email
     */
    public $original_email;

    /**
     * Data to pass to the template.
     *
     * @since 3.7.8
     *
     * @var array
     */
    public $template_data = [];

    /**
     * Type of the object.
     *
     * @since 3.7.8
     *
     * @var string
     */
    public $object_type = 'order';

    /**
     * The object to maintain all data.
     *
     * @since 3.7.8
     *
     * @var object
     */
    public $object;

    /**
     * Generates the HTML for the email while keeping the `template_base` in mind.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public function get_content_html() {
        return wc_get_template_html( $this->template_html, $this->template_data, 'dokan/', $this->template_base );
    }

    /**
     * Generates the plain text for the email while keeping the `template_base` in mind.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public function get_content_plain() {
        return wc_get_template_html( $this->template_plain, $this->template_data, 'dokan/', $this->template_base );
    }

    /**
     * Generates the URL, which will be used to authenticate the payment.
     *
     * @since 3.7.8
     *
     * @param WC_Order $order The order whose payment needs authentication.
     *
     * @return string
     */
    public function get_authorization_url( $order ) {
        if ( ! $order instanceof WC_Order ) {
            return '';
        }
        return add_query_arg( 'dokan_stripe_express_confirmation', 1, $order->get_checkout_payment_url( false ) );
    }

    /**
     * Uses specific fields from `WC_Email_Customer_Invoice` for this email.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function init_form_fields() {
        parent::init_form_fields();
        $base_fields = $this->form_fields;

        $this->form_fields = [
            'enabled'    => [
                'title'   => _x( 'Enable/Disable', 'an email notification', 'dokan' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this email notification', 'dokan' ),
                'default' => 'yes',
            ],
            'subject'    => $base_fields['subject'],
            'heading'    => $base_fields['heading'],
            'email_type' => $base_fields['email_type'],
        ];
    }

    /**
     * Triggers the email.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function send_email() {
        if ( ! $this->is_enabled() || ! $this->get_recipient() ) {
            return;
        }

        $this->template_data = array_merge(
            [
                $this->object_type  => $this->object,
                'email_heading'     => $this->get_heading(),
                'sent_to_admin'     => false,
                'plain_text'        => false,
                'authorization_url' => $this->get_authorization_url( $this->object ),
                'email'             => $this,
            ],
            $this->template_data
        );

        $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
    }
}
