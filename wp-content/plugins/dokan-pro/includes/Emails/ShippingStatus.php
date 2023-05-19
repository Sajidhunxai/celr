<?php

namespace WeDevs\DokanPro\Emails;

use WC_Email;

class ShippingStatus extends WC_Email {

    /**
     * Customer note.
     *
     * @var array
     */
    public $shipping_info;

    /**
     * Customer note.
     *
     * @var string
     */
    public $ship_info;

    /**
     * Constructor
     */
    public function __construct() {
        $this->id               = 'dokan_email_shipping_status_tracking';
        $this->title            = __( 'Shipping Status Notification for Customer', 'dokan' );
        $this->description      = __( 'This email is set to a customer when add new shipping status on their order', 'dokan' );
        $this->template_html    = 'emails/shipping-status.php';
        $this->template_plain   = 'emails/plain/shipping-status.php';
        $this->template_base    = DOKAN_PRO_DIR . '/templates/';

        // Triggers for this email
        add_action( 'dokan_order_shipping_status_tracking_notify', [ $this, 'trigger' ], 11, 5 );

        // Call parent constructor
        parent::__construct();

        $this->recipient = 'customer@ofthe.order';
    }

    /**
     * Get email subject.
     * @return string
     */
    public function get_default_subject() {
        return __( '[{site_name}] {set_email_subject}', 'dokan' );
    }

    /**
     * Get email heading.
     * @return string
     */
    public function get_default_heading() {
        return __( '{set_email_subject}', 'dokan' );
    }

    /**
     * Trigger the this email.
     */
    public function trigger( $order_id, $tracking_info, $ship_info, $seller_id, $new_shipment = false ) {
        if ( ! $this->is_enabled() ) {
            return;
        }

        if ( $order_id ) {
            $this->object = wc_get_order( $order_id );
            $default_heading = __( 'Your order shipping status changed', 'dokan' );

            if ( $new_shipment ) {
                $default_heading = __( 'New shipment created on your order', 'dokan' );
            }

            if ( $this->object ) {
                $this->recipient     = $this->object->get_billing_email();
                $this->shipping_info = $tracking_info;
                $this->ship_info     = $ship_info;

                $this->find['site_name']         = '{site_name}';
                $this->find['set_email_subject'] = '{set_email_subject}';

                $this->replace['site_name']         = $this->get_from_name();
                $this->replace['set_email_subject'] = $default_heading;
            }
        }

        if ( $this->is_enabled() && $this->get_recipient() ) {
            $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }

        $this->restore_locale();
    }

    /**
     * Get content html.
     *
     * @access public
     * @return string
     */
    public function get_content_html() {
        ob_start();
        wc_get_template(
            $this->template_html, array(
                'order'         => $this->object,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => false,
                'additional_content' => $this->get_additional_content(),
                'plain_text'    => false,
                'email'         => $this,
                'tracking_info' => $this->shipping_info,
                'ship_info'     => $this->ship_info,
            ), 'dokan/', $this->template_base
        );
        return ob_get_clean();
    }

    /**
     * Get content plain.
     *
     * @access public
     * @return string
     */
    public function get_content_plain() {
        ob_start();
        wc_get_template(
            $this->template_html, array(
                'order'         => $this->object,
                'email_heading' => $this->get_heading(),
                'sent_to_admin' => false,
                'additional_content' => $this->get_additional_content(),
                'plain_text'    => true,
                'email'         => $this,
                'tracking_info' => $this->shipping_info,
            ), 'dokan/', $this->template_base
        );
        return ob_get_clean();
    }

    /**
     * Initialize settings form fields.
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'         => __( 'Enable/Disable', 'dokan' ),
                'type'          => 'checkbox',
                'label'         => __( 'Enable this email notification', 'dokan' ),
                'default'       => 'yes',
            ),

            'subject' => array(
                'title'         => __( 'Subject', 'dokan' ),
                'type'          => 'text',
                'desc_tip'      => true,
                /* translators: %s: list of placeholders */
                'description'   => sprintf( __( 'Available placeholders: %s', 'dokan' ), '<code>{title}, {message}, {site_name}</code>' ),
                'placeholder'   => $this->get_default_subject(),
                'default'       => '',
            ),
            'heading' => array(
                'title'         => __( 'Email heading', 'dokan' ),
                'type'          => 'text',
                'desc_tip'      => true,
                /* translators: %s: list of placeholders */
                'description'   => sprintf( __( 'Available placeholders: %s', 'dokan' ), '<code>{title}, {message}, {site_name}</code>' ),
                'placeholder'   => $this->get_default_heading(),
                'default'       => '',
            ),
            'additional_content' => array(
                'title'       => __( 'Additional content', 'dokan' ),
                'description' => __( 'Text to appear below the main email content.', 'dokan' ) . ' <code>{title}, {message}, {site_name}</code>',
                'css'         => 'width:400px; height: 75px;',
                'placeholder' => __( 'N/A', 'dokan' ),
                'type'        => 'textarea',
                'default'     => $this->get_default_additional_content(),
                'desc_tip'    => true,
            ),
            'email_type' => array(
                'title'         => __( 'Email type', 'dokan' ),
                'type'          => 'select',
                'description'   => __( 'Choose which format of email to send.', 'dokan' ),
                'default'       => 'html',
                'class'         => 'email_type wc-enhanced-select',
                'options'       => $this->get_email_type_options(),
                'desc_tip'      => true,
            ),
        );
    }
}
