<?php

namespace WeDevs\DokanPro;

use WeDevs\Dokan\Vendor\SetupWizard;

/**
 * Dokan Email Verification class
 *
 * @since 2.7.3
 *
 * @package dokan-pro
 */
class EmailVerification {

    /**
     * WP_User holder
     *
     * @var \WP_User
     */
    private $user;

    /**
     * My account page url holder
     *
     * @var string
     */
    private $base_url;

    /**
     * Load automatically when class instantiated
     *
     * @since 2.7.3
     *
     * @uses actions|filter hooks
     */
    public function __construct() {
        $this->base_url = dokan_get_page_url( 'myaccount', 'woocommerce' );
        $this->init_hooks();
    }

    /**
     * Call actions and hooks
     */
    public function init_hooks() {
        add_filter( 'dokan_settings_sections', array( $this, 'dokan_email_verification_settings' ) );
        add_filter( 'dokan_settings_fields', array( $this, 'dokan_email_settings_fields' ) );
        add_action( 'woocommerce_created_customer', array( $this, 'send_verification_email' ), 5, 3 );

        if ( $this->maybe_verification_not_needed() ) {
            return;
        }

        if ( is_admin() ) {
            add_action( 'wp_ajax_woocommerce_germanized_double_opt_in_ajax', array( $this, 'woocommerce_germanized_double_opt_in_ajax' ) );
        }

        if ( $this->woocommerce_germanized_double_opt_in() ) {
            $this->display_double_opt_in_admin_notice();
            return;
        }

        add_action( 'woocommerce_registration_redirect', array( $this, 'check_verification' ), 99 );
        add_action( 'woocommerce_login_redirect', array( $this, 'check_verification' ), 99, 2 );
        add_action( 'init', array( $this, 'validate_email_link' ), 100 );
        add_action( 'woocommerce_email_footer', array( $this, 'add_activation_link' ), 1 );
        add_action( 'dokan_seller_meta_fields', array( $this, 'add_email_verification_field' ) );
        add_action( 'dokan_process_seller_meta_fields', array( $this, 'verify_vendor' ) );
        add_action( 'template_redirect', array( $this, 'send_verification_email_again' ) );
        add_action( 'template_redirect', array( $this, 'show_resend_email_notification' ) );
    }

    /**
     * Set Verification meta
     *
     * @param type $customer_id
     *
     * @param type $new_customer_data
     *
     * @param type $password_generated
     *
     * @return void
     */
    public function send_verification_email( $customer_id, $new_customer_data, $password_generated ) {
        $this->user = get_user_by( 'id', $customer_id );
        $code       = sha1( $customer_id . $this->user->user_email . time() );

        add_user_meta( $customer_id, '_dokan_email_verification_key', $code, true );
        add_user_meta( $customer_id, '_dokan_email_pending_verification', true, true );
    }

    /**
     * Check for verification when a user logs in
     *
     * @param type $redirect
     *
     * @param WP_User $user
     *
     * @return String $redirect
     */
    public function check_verification( $redirect, $user = array() ) {
        $user_id = get_current_user_id();
        $notice = dokan_get_option( 'registration_notice', 'dokan_email_verification' );

        if ( ! empty( $user ) ) {
            $user_id = $user->ID;
            $notice  = dokan_get_option( 'login_notice', 'dokan_email_verification' );
        }

        $pending_verification = get_user_meta( $user_id, '_dokan_email_pending_verification', true );

        if ( ! $pending_verification ) {
            return $redirect;
        }

        wp_logout();

        $link = add_query_arg( 'dokan_email_verification_again', $user_id );

        wc_add_notice( sprintf( '%s %s <a href="%s">%s</a>', $notice, __( 'Didn\'t get the email?', 'dokan' ), $link, __( 'Send again', 'dokan' ) ) );

        do_action( 'woocommerce_set_cart_cookies', true );

        return $this->base_url;
    }

    /**
     * Validate Email from link
     */
    public function validate_email_link() {
        if ( empty( $_GET['dokan_email_verification'] ) ) {
            return;
        }

        if ( empty( $_GET['id'] ) ) {
            return;
        }

        $user_id = intval( $_GET['id'] );
        $activation_key = $_GET['dokan_email_verification'];

        if ( get_user_meta( $user_id, '_dokan_email_verification_key', true ) !== $activation_key ) {
            return;
        }

        delete_user_meta( $user_id, '_dokan_email_pending_verification' );
        delete_user_meta( $user_id, '_dokan_email_verification_key' );

        do_action( 'woocommerce_set_cart_cookies', true );

        $user = get_user_by( 'id', $user_id );

        if ( $user ) {
            clean_user_cache( $user_id );
            wp_clear_auth_cookie();
            wp_set_current_user( $user_id, $user->user_login );

            if ( is_ssl() === true ) {
                wp_set_auth_cookie( $user_id, true, true );
            } else {
                wp_set_auth_cookie( $user_id, true, false );
            }

            update_user_caches( $user );
        }

        $seller_wizard = new SetupWizard();
        $seller_wizard->setup_wizard();
    }

    /**
     * Add verification link in welcome email
     *
     * @param type $email
     *
     * @return void
     */
    public function add_activation_link( $email ) {
        if ( empty( $email ) ) {
            return;
        }

        if ( $email->id !== 'customer_new_account' ) {
            return;
        }

        $user = get_user_by( 'email', $email->user_email );

        $verification_key = get_user_meta( $user->ID, '_dokan_email_verification_key', true );

        if ( empty( $verification_key ) ) {
            return;
        }

        if ( in_array( 'seller', $user->roles, true ) && dokan_get_option( 'disable_welcome_wizard', 'dokan_selling' ) === 'off' ) {
            $verification_link = add_query_arg( array( 'dokan_email_verification' => $verification_key, 'id' => $user->ID, 'page' => 'dokan-seller-setup' ), $this->base_url );
        } else {
            $verification_link = add_query_arg( array( 'dokan_email_verification' => $verification_key, 'id' => $user->ID ), $this->base_url );
        }

        $message = sprintf( __( "<p><b>To Verify your Email <a href='%s'>Click Here</a></b></p>", 'dokan' ), $verification_link );

        echo apply_filters( 'dokan_email_verification_email_text', $message, $verification_link );
    }

    /**
     * Add email verification field
     *
     * @param object $user
     *
     * @return string
     */
    public function add_email_verification_field( $user ) {
        $verified_vendor = empty( get_user_meta( $user->ID, '_dokan_email_pending_verification', true ) ) ? 'yes' : 'no';
        ?>
        <tr>
            <th><?php esc_html_e( 'Verify Vendor', 'dokan' ); ?></th>
            <td>
                <label for="verify_vendor">
                    <input name="verify_vendor" type="checkbox" id="verify_vendor" value="yes" <?php checked( $verified_vendor, 'yes' ); ?> />
                    <?php esc_html_e( 'Make this vendor verified.', 'dokan' ); ?>
                </label>
            </td>
        </tr>
        <?php
    }

    /**
     * Verify a vendor from user profile page
     *
     * @param  int $user_id
     *
     * @return void
     */
    public function verify_vendor( $user_id ) {
        if ( empty( $_POST['verify_vendor'] ) ) {
            return;
        }

        if ( empty( get_user_meta( $user_id, '_dokan_email_pending_verification' ) ) ) {
            return;
        }

        if ( $_POST['verify_vendor'] === 'yes' ) {
            delete_user_meta( $user_id, '_dokan_email_pending_verification' );
            delete_user_meta( $user_id, '_dokan_email_verification_key' );
        }
    }

    /**
     * Send verification email again
     *
     * @return void
     */
    public function send_verification_email_again() {
        if ( empty( $_GET['dokan_email_verification_again'] ) ) {
            return;
        }

        if ( is_user_logged_in() ) {
            return;
        }

        $user_id = wc_clean( $_GET['dokan_email_verification_again'] );

        if ( get_userdata( $user_id ) === false ) {
            return;
        }

        if ( ! class_exists( 'WC_Email_Customer_New_Account' ) ) {
            require_once WC_ABSPATH . '/includes/emails/class-wc-email-customer-new-account.php';
        }

        $email = new \WC_Email_Customer_New_Account;

        $email->trigger( $user_id );

        wp_redirect( add_query_arg( array( 'resend_email' => 'sent' ), wc_get_page_permalink( 'myaccount' ) ) );
    }

    /**
     * Show the resend email notification message
     *
     * @return void
     */
    public function show_resend_email_notification() {
        if ( empty( $_GET['resend_email'] ) || $_GET['resend_email'] !== 'sent' ) {
            return;
        }

        if ( ! is_account_page() ) {
            return;
        }

        if ( is_user_logged_in() ) {
            return;
        }

        wc_add_notice( __( 'A new verification email has been sent to your email. Please verify to login.', 'dokan' ) );
    }

    /**
     * Filter admin menu settings section
     *
     * @param array $sections
     *
     * @return array
     */
    public function dokan_email_verification_settings( $sections ) {
        $sections[] = [
            'id'                   => 'dokan_email_verification',
            'title'                => __( 'Email Verification', 'dokan' ),
            'icon_url'             => DOKAN_PRO_PLUGIN_ASSEST . '/images/admin-settings-icons/email-verify.svg',
            'description'          => __( 'Verification Email Manage', 'dokan' ),
            'settings_title'       => __( 'Email Verification Settings', 'dokan' ),
            'settings_description' => __( 'You can set up two step authentication for email addresses used in account creation.', 'dokan' ),
        ];
        return $sections;
    }

    /**
     * Render settings fields for admin settings section
     *
     * @param array $settings_fields
     *
     * @return array
     */
    public function dokan_email_settings_fields( $settings_fields ) {
        $settings_fields['dokan_email_verification'] = [
            'enabled' => [
                'name'    => 'enabled',
                'label'   => __( 'Enable Email Verification', 'dokan' ),
                'type'    => 'switcher',
                'desc'    => __( 'Enabling this will send a verification email to allow users to verify their email address after the registration form is submitted.', 'dokan' ),
                'tooltip' => __( 'Check this if you want user to verify their email address upon registration.', 'dokan' ),
            ],
            'registration_notice' => [
                'name'  => 'registration_notice',
                'label' => __( 'Registration Notice', 'dokan' ),
                'type'  => 'textarea',
                'rows'  => 4,
                'desc'  => __( 'This notice will be shown after a user has registered with pending email verification.', 'dokan' ),
                'default' => __( 'Please check your email and complete email verification to login.', 'dokan' ),
            ],
            'login_notice' => [
                'name'    => 'login_notice',
                'label'   => __( 'Login Notice', 'dokan' ),
                'type'    => 'textarea',
                'rows'    => 4,
                'desc'    => __( 'This notice will be shown when a user tries to login without email verification.', 'dokan' ),
                'default' => __( 'Please check your email and complete email verification to login.', 'dokan' ),
            ],
        ];

        return $settings_fields;
    }

    /**
     * Get Post Type array
     *
     * @param  string $post_type
     *
     * @return array
     */
    public function get_post_type( $post_type ) {
        $pages_array = array( '-1' => __( '- select -', 'dokan' ) );
        $pages = get_posts(
            array(
                'post_type' => $post_type,
                'numberposts' => -1,
            )
        );

        if ( $pages ) {
            foreach ( $pages as $page ) {
                $pages_array[ $page->ID ] = $page->post_title;
            }
        }

        return $pages_array;
    }

    /**
     * Check whether email verification is needed or not
     *
     * @since 2.9.13
     *
     * @return bool
     */
    public function maybe_verification_not_needed() {
        $ret = false;
        // check if email verification is enabled from admin
        if ( 'on' !== dokan_get_option( 'enabled', 'dokan_email_verification' ) ) {
            $ret = true;
        }

        return apply_filters( 'dokan_maybe_email_verification_not_needed', $ret );
    }

    /**
     * Check if the user is vendor
     *
     * @since 3.0.0
     *
     * @return boolean
     */
    protected function is_vendor() {
        return ! empty( $this->user->roles ) && in_array( 'seller', $this->user->roles, true );
    }

    /**
     * Check if Germanized for WooCommerce Double Opt In activated
     *
     * @since 3.2.3
     *
     * @return bool
     */
    private function woocommerce_germanized_double_opt_in() {
        if ( is_plugin_active( 'woocommerce-germanized/woocommerce-germanized.php' ) && 'yes' === get_option( 'woocommerce_gzd_customer_activation', 'no' ) ) {
            return true;
        }
        return false;
    }

    /**
     * Show `Double Opt-In` admin notice
     *
     * @since 3.2.3
     *
     * @param array $notices
     *
     * @return array
     */
    public function double_opt_in_admin_notice( $notices ) {
        $germanized_option_url       = sprintf(
            '<a href="%1$s" title="%2$s">%3$s</a>',
            esc_attr( admin_url( 'admin.php?page=wc-settings&tab=germanized-double_opt_in' ) ),
            esc_attr__( 'Germanized for WooCommerce Double Opt In', 'dokan' ),
            esc_html__( 'Double Opt In', 'dokan' )
        );
        $email_verification_option_url = sprintf(
            '<a href="%1$s" title="%2$s">%3$s</a>',
            esc_attr( admin_url( 'admin.php?page=dokan#/settings' ) ),
            esc_attr__( 'Dokan Email Verification', 'dokan' ),
            esc_attr__( 'Email Verification', 'dokan' )
        );

        // translators: Germanized for WooCommerce double opt-in option page anchor URL; Dokan admin settings page anchor URL.
        $message = sprintf( __( 'Please disable %1$s in Germanized for WooCommerce to enable Dokan %2$s', 'dokan' ), $germanized_option_url, $email_verification_option_url );

        $notices[] = [
            'type'              => 'warning',
            'description'       => $message,
            'show_close_button' => true,
            'priority'          => 10,
            'ajax_data'         => [
                'action'          => 'woocommerce_germanized_double_opt_in_ajax',
                'opt_in_security' => wp_create_nonce( 'email_verification_double_opt_in_admin_notice_nonce' ),
            ],
        ];

        return $notices;
    }

    /**
     * Admin Notice ajax action
     *
     * @since 3.2.3
     *
     * @return void
     */
    public function woocommerce_germanized_double_opt_in_ajax() {
        check_ajax_referer( 'email_verification_double_opt_in_admin_notice_nonce', 'opt_in_security' );
        set_transient( 'dokan_email_verification_double_opt_in_admin_notice', true, 30 * DAY_IN_SECONDS );
        wp_send_json_success();
    }

    /**
     * Display admin notice if needed
     *
     * @since 3.2.3
     *
     * @return void
     */
    private function display_double_opt_in_admin_notice() {
        if ( ! get_transient( 'dokan_email_verification_double_opt_in_admin_notice' ) ) {
            add_filter( 'dokan_admin_notices', [ $this, 'double_opt_in_admin_notice' ] );
        }
    }
}
