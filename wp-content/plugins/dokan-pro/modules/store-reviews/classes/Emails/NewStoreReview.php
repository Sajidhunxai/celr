<?php
namespace WeDevs\DokanPro\Modules\StoreReviews\Emails;

use WC_Email;

/**
 * New Quote Email.
 *
 * An email sent to the admin, vendor and customer when a new quote is created.
 *
 * @class       NewQuote
 * @version     3.5.5
 * @package     Dokan/Modules/RequestAQuote/Emails
 * @author      weDevs
 * @extends     WC_Email
 */
class NewStoreReview extends WC_Email {

    /**
     * Constructor.
     */
    public function __construct() {
        $this->id             = 'dokan_new_store_review';
        $this->title          = __( 'Dokan New Store Review', 'dokan' );
        $this->description    = __( 'New emails are sent to chosen recipient(s) when a new store review is submitted.', 'dokan' );
        $this->template_html  = 'emails/new-store-review-email.php';
        $this->template_plain = 'emails/plain/new-store-review-email.php';
        $this->template_base  = DOKAN_SELLER_RATINGS_DIR . '/templates/';
        $this->reviewer_name  = wp_get_current_user()->user_login;

        // Triggers for this email.
        add_action( 'dokan_store_review_saved', [ $this, 'trigger' ], 10, 3 );

        // Call parent constructor.
        parent::__construct();

        // Other settings.
        $this->recipient = $this->get_default_recipient();
    }

    /**
     * Get email subject.
     *
     * @since  3.5.5
     *
     * @return string
     */
    public function get_default_subject() {
        return __( 'New review on #{store_name}', 'dokan' );
    }

    /**
     * Get email recipient.
     *
     * @since  3.5.5
     *
     * @return string
     */
    public function get_default_recipient() {
        return get_option( 'admin_email' ) . ', owner@ofthe.store';
    }

    /**
     * Get email heading.
     *
     * @since  3.5.5
     *
     * @return string
     */
    public function get_default_heading() {
        return __( 'New review on #{store_name}', 'dokan' );
    }

    /**
     * Trigger the sending of this email.
     *
     * @since 3.5.5
     *
     * @param int   $post_id
     * @param array $post_data
     * @param int   $rating
     *
     * @return void
     */
    public function trigger( $post_id, $post_data, $rating ) {
        if ( ! $post_id ) {
            return;
        }

        if ( ! $this->is_enabled() ) {
            return;
        }

        $this->setup_locale();

        $this->post_id      = $post_id;
        $store_id           = sanitize_text_field( wp_unslash( $post_data['store_id'] ) );
        $this->post_title   = ! empty( $post_data['dokan-review-title'] ) ? sanitize_text_field( wp_unslash( $post_data['dokan-review-title'] ) ) : '';
        $this->post_details = ! empty( $post_data['dokan-review-details'] ) ? sanitize_text_field( wp_unslash( $post_data['dokan-review-details'] ) ) : '';
        $this->rating       = $rating;
        $shop               = dokan_get_store_info( $store_id );
        $this->store_name   = $shop['store_name'];
        $this->placeholders = [
            '{store_name}' => $this->store_name,
        ];

        $recipients = explode( ',', $this->get_default_recipient() );
        foreach ( $recipients as $recipient ) {
            $recipient = trim( $recipient );
            if ( 'owner@ofthe.store' === $recipient ) {
                $recipient = dokan()->vendor->get( $store_id )->get_email();
            }
            $this->send( $recipient, $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }

        $this->restore_locale();
    }

    /**
     * Get content html.
     *
     * @since 3.5.5
     *
     * @return false|string
     */
    public function get_content_html() {
        ob_start();
        wc_get_template(
            $this->template_html, [
                'post_id'       => $this->post_id,
                'post_title'    => $this->post_title,
                'post_details'  => $this->post_details,
                'reviewer_name' => $this->reviewer_name,
                'rating'        => $this->rating,
                'store_name'    => $this->store_name,
                'email_heading' => $this->get_heading(),
                'email'         => $this,
            ], 'dokan/', $this->template_base
        );

        return ob_get_clean();
    }

    /**
     * Get content plain.
     *
     * @since 3.5.5
     *
     * @return false|string
     */
    public function get_content_plain() {
        ob_start();
        wc_get_template(
            $this->template_plain, [
                'post_id'       => $this->post_id,
                'post_title'    => $this->post_title,
                'post_details'  => $this->post_details,
                'reviewer_name' => $this->reviewer_name,
                'rating'        => $this->rating,
                'store_name'    => $this->store_name,
                'email_heading' => $this->get_heading(),
                'email'         => $this,
            ], 'dokan/', $this->template_base
        );

        return ob_get_clean();
    }

    /**
     * Initialise settings form fields.
     *
     * @since 3.5.5
     *
     * @return void
     */
    public function init_form_fields() {
        $this->form_fields = [
            'enabled'    => [
                'title'   => __( 'Enable/Disable', 'dokan' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable this email notification', 'dokan' ),
                'default' => 'yes',
            ],
            'recipient'  => [
                'title'       => __( 'Recipient(s)', 'dokan' ),
                'type'        => 'text',
                /* translators: %s: list of recipient */
                'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %s.', 'dokan' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
                'placeholder' => $this->get_default_recipient(),
                'default'     => '',
                'desc_tip'    => true,
            ],
            'subject'    => [
                'title'       => __( 'Subject', 'dokan' ),
                'type'        => 'text',
                'desc_tip'    => true,
                /* translators: %s: list of placeholders */
                'description' => sprintf( __( 'Available placeholders: %s', 'dokan' ), '<code>{site_title}, {quote_date}, {quote_number}</code>' ),
                'placeholder' => $this->get_default_subject(),
                'default'     => '',
            ],
            'heading'    => [
                'title'       => __( 'Email heading', 'dokan' ),
                'type'        => 'text',
                'desc_tip'    => true,
                /* translators: %s: list of placeholders */
                'description' => sprintf( __( 'Available placeholders: %s', 'dokan' ), '<code>{site_title}, {quote_date}, {quote_number}</code>' ),
                'placeholder' => $this->get_default_heading(),
                'default'     => '',
            ],
            'email_type' => [
                'title'       => __( 'Email type', 'dokan' ),
                'type'        => 'select',
                'description' => __( 'Choose which format of email to send.', 'dokan' ),
                'default'     => 'html',
                'class'       => 'email_type wc-enhanced-select',
                'options'     => $this->get_email_type_options(),
                'desc_tip'    => true,
            ],
        ];
    }

}
