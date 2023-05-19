<?php
/**
 * Class DokanReplyToAdminSupportTicket file
 *
 * @package Dokan/Emails
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'DokanReplyToAdminSupportTicket' ) ) :

    /**
     * Support Ticket Replay Email.
     *
     * An email sent to the admin when vendor or customer replies on a ticket depending on settings.
     *
     * @class       DokanReplyToAdminSupportTicket
     * @version     3.6.0
     * @package     Dokan/Classes/Emails
     * @extends     WC_Email
     */
    class DokanReplyToAdminSupportTicket extends WC_Email {

        /**
         * Constructor.
         */
        public function __construct() {
            $this->id             = 'DokanReplyToAdminSupportTicket_vendor_customer';
            $this->title          = __( 'Dokan Reply To Admin Support Ticket From Vendor & Customer', 'dokan' );
            $this->description    = __( 'An email sent to the admin when vendor or customer replies on a ticket depending on settings.', 'dokan' );
            $this->template_html  = 'emails/reply-to-admin-support-ticket.php';
            $this->template_plain = 'emails/plain/reply-to-admin-support-ticket.php';
            $this->template_base  = DOKAN_STORE_SUPPORT_DIR . '/templates/';

            // Triggers for this email.
            add_action( 'dokan_reply_to_admin_ticket_created_notify', array( $this, 'trigger' ), 10, 2 );

            // Call parent constructor.
            parent::__construct();

            // Other settings.
            $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
        }

        /**
         * Get email subject.
         *
         * @since  3.6.0
         * @return string
         */
        public function get_default_subject() {
            return __( '[{site_title}] A New Reply On Ticket #{ticket_id}', 'dokan' );
        }

        /**
         * Get email heading.
         *
         * @since  3.6.0
         * @return string
         */
        public function get_default_heading() {
            return __( 'A New Reply On Ticket #{ticket_id}', 'dokan' );
        }

        /**
         * Trigger the sending of this email.
         *
         * @param int            $order_id The order ID.
         * @param WC_Order|false $order Order object.
         */
        public function trigger( $store_id, $email_data ) {
            // Getting global settings from admin panel store support.
            $admin_global_settings = dokan_get_option( 'dokan_admin_email_notification', 'dokan_store_support_setting', 'off' );

            // Getting vendor settings from specific store settings.
            $topic_specific_settings = get_post_meta( $email_data['ticket_id'], 'dokan_admin_email_notification', true );

            // Return if global admin settings is off or global is on and topic specific settings is off.
            if ( 'off' === $admin_global_settings || ( 'on' === $admin_global_settings && 'off' === $topic_specific_settings ) ) {
                return;
            }

            $this->email_data             = $email_data;
            $this->find['ticket_id']      = '{ticket_id}';
            $this->replace['ticket_id']   = $email_data['ticket_id'];
            $mail_to                      = $this->get_option( 'recipient', get_option( 'admin_email' ) );

            $this->setup_locale();
            $this->send( $mail_to, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );

            $this->restore_locale();
        }

        /**
         * Get content html.
         *
         * @access public
         * @return string
         */
        public function get_content_html() {
            return wc_get_template_html(
                $this->template_html, array(
                    'email_heading' => $this->get_heading(),
                    'plain_text'    => false,
                    'email'         => $this,
                    'email_data'    => $this->email_data,
                ), 'dokan', $this->template_base
            );
        }

        /**
         * Get content plain.
         *
         * @access public
         * @return string
         */
        public function get_content_plain() {
            return wc_get_template_html(
                $this->template_plain, array(
                    'email_heading' => $this->get_heading(),
                    'plain_text'    => true,
                    'email'         => $this,
                    'email_data'    => $this->email_data,
                ), 'dokan/', $this->template_base
            );
        }

        /**
         * Initialise settings form fields.
         */
        public function init_form_fields() {
            $this->form_fields = array(
                'subject'    => array(
                    'title'       => __( 'Subject', 'dokan' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    /* translators: %s: list of placeholders */
                    'description' => sprintf( __( 'Available placeholders: %s', 'dokan' ), '<code>{site_title}</code>' ),
                    'placeholder' => $this->get_default_subject(),
                    'default'     => '',
                ),
                'heading'    => array(
                    'title'       => __( 'Email heading', 'dokan' ),
                    'type'        => 'text',
                    'desc_tip'    => true,
                    /* translators: %s: list of placeholders */
                    'description' => sprintf( __( 'Available placeholders: %s', 'dokan' ), '<code>{site_title}</code>' ),
                    'placeholder' => $this->get_default_heading(),
                    'default'     => '',
                ),
                'email_type' => array(
                    'title'       => __( 'Email type', 'dokan' ),
                    'type'        => 'select',
                    'description' => __( 'Choose which format of email to send.', 'dokan' ),
                    'default'     => 'html',
                    'class'       => 'email_type wc-enhanced-select',
                    'options'     => $this->get_email_type_options(),
                    'desc_tip'    => true,
                ),
            );
        }
    }

endif;
