<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\PaymentGateways;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use Exception;
use Stripe\Stripe;
use Stripe\ApplePayDomain;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Settings;
use WeDevs\DokanPro\Modules\StripeExpress\Utilities\Abstracts\PaymentGateway;

/**
 * Stripe Apple Pay Registration Class.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\PaymentGateways
 */
class ApplePay extends PaymentGateway {

    /**
     * Apple domain association file name.
     *
     * @since 3.6.1
     *
     * @var string
     */
    const DOMAIN_ASSOCIATION_FILE_NAME = 'apple-developer-merchantid-domain-association';

    /**
     * Apple domain association file directory.
     *
     * @since 3.6.1
     *
     * @var string
     */
    const DOMAIN_ASSOCIATION_FILE_DIR = '.well-known';

    /**
     * Stripe gateway settings.
     *
     * @since 3.6.1
     *
     * @var array
     */
    public $stripe_settings;

    /**
     * Apple Pay Domain Set.
     *
     * @since 3.6.1
     *
     * @var bool
     */
    public $apple_pay_domain_set;

    /**
     * Current domain name.
     *
     * @since 3.6.1
     *
     * @var bool
     */
    private $domain_name;

    /**
     * Stores Apple Pay domain verification issues.
     *
     * @since 3.6.1
     *
     * @var string
     */
    public $apple_pay_verify_notice;

    /**
     * Class constructor.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function __construct() {
        $this->hooks();
        $this->stripe_settings = Settings::get();

        /*
         * Note: This `$_SERVER['HTTP_HOST']` should not be excaped or sanitized in this case.
         * Only the domain without `http://` or `https://` is required here.
         */
        $this->domain_name             = isset( $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : str_replace( [ 'https://', 'http://' ], '', get_site_url() ); // phpcs:ignore
        $this->apple_pay_domain_set    = 'yes' === $this->get_option( 'apple_pay_domain_set', 'no' );
        $this->apple_pay_verify_notice = '';
    }

    /**
     * Registers necessary hooks.
     *
     * @since 3.6.1
     *
     * @return void
     */
    protected function hooks() {
        add_action( 'init', [ $this, 'add_domain_association_rewrite_rule' ] );
        add_action( 'admin_init', [ $this, 'verify_domain_on_domain_name_change' ] );
        add_filter( 'admin_notices', [ $this, 'show_verification_status_on_payment_settings' ] );
        add_filter( 'dokan_admin_notices', [ $this, 'show_verification_status' ] );
        add_filter( 'query_vars', [ $this, 'whitelist_domain_association_query_param' ], 10, 1 );
        add_action( 'parse_request', [ $this, 'parse_domain_association_request' ], 10, 1 );

        add_action( 'dokan_activated_module_stripe_express', [ $this, 'verify_domain_if_configured' ] );
        add_action( 'add_option_' . Settings::key(), [ $this, 'verify_domain_on_new_settings' ], 10, 2 );
        add_action( 'update_option_' . Settings::key(), [ $this, 'verify_domain_on_updated_settings' ], 10, 2 );
    }

    /**
     * Retrieves main settings value.
     *
     * @since 3.6.1
     *
     * @param string $key
     * @param mixed  $empty_value
     *
     * @return mixed
     */
    public function get_option( $key, $empty_value = null ) {
        return ! empty( $this->stripe_settings[ $key ] ) ? $this->stripe_settings[ $key ] : $empty_value;
    }

    /**
     * Whether the gateway and Payment Request Button (prerequisites for Apple Pay) are enabled.
     *
     * @since 3.6.1
     *
     * @return string Whether Apple Pay required settings are enabled.
     */
    public function is_enabled() {
        return 'yes' === $this->get_option( 'enabled', 'no' ) && 'yes' === $this->get_option( 'payment_request', 'yes' );
    }

    /**
     * Gets the Stripe secret key for the current mode.
     *
     * @since 3.6.1
     *
     * @return string Secret key.
     */
    private function get_secret_key() {
        return $this->get_option( 'secret_key' );
    }

    /**
     * Trigger Apple Pay registration upon domain name change.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function verify_domain_on_domain_name_change() {
        if ( $this->domain_name !== $this->get_option( 'apple_pay_verified_domain' ) ) {
            $this->verify_domain_if_configured();
        }
    }

    /**
     * Vefifies if hosted domain association file is up to date
     * with the file from the plugin directory.
     *
     * @since 3.6.1
     *
     * @return bool Whether file is up to date or not.
     */
    private function verify_hosted_domain_association_file_is_up_to_date() {
        Helper::init_filesystem();
        global $wp_filesystem;

        $file_path = $this->get_registration_file_path();
        if ( ! file_exists( $file_path ) ) {
            return false;
        }

        // Contents of domain association file from plugin dir.
        $new_contents = $wp_filesystem->get_contents( $file_path );

        // Get file contents from local path and remote URL and check if either of which matches.
        $local_path = untrailingslashit( ABSPATH ) . '/' . self::DOMAIN_ASSOCIATION_FILE_DIR . '/' . self::DOMAIN_ASSOCIATION_FILE_NAME;

        $local_contents = '';
        if ( file_exists( $local_path ) ) {
            $local_contents = $wp_filesystem->get_contents( $local_path );
        }

        // Check if the file is hosted correctly and then check if the contents are same.
        $url             = get_site_url() . '/' . self::DOMAIN_ASSOCIATION_FILE_DIR . '/' . self::DOMAIN_ASSOCIATION_FILE_NAME;
        $response        = wp_remote_get( $url );
        $remote_contents = wp_remote_retrieve_body( $response );

        return $local_contents === $new_contents || $remote_contents === $new_contents;
    }

    /**
     * Copies and overwrites domain association file.
     *
     * @since 3.6.1
     *
     * @return array{success:bool,message:string}
     */
    private function copy_and_overwrite_domain_association_file() {
        Helper::init_filesystem();
        global $wp_filesystem;

        $well_known_dir = untrailingslashit( ABSPATH ) . '/' . self::DOMAIN_ASSOCIATION_FILE_DIR;
        $fullpath       = $well_known_dir . '/' . self::DOMAIN_ASSOCIATION_FILE_NAME;

        if ( ! file_exists( $well_known_dir ) ) {
            if ( ! $wp_filesystem->mkdir( $well_known_dir, 0755 ) ) {
                return [
                    'success' => false,
                    'message' => __( 'Unable to create domain association folder to domain root.', 'dokan' ),
                ];
            }
        }

        if ( ! $wp_filesystem->copy( $this->get_registration_file_path(), $fullpath ) ) {
            return [
                'success' => false,
                'message' => __( 'Unable to copy domain association file to domain root.', 'dokan' ),
            ];
        }

        return [
            'success' => true,
            'message' => '',
        ];
    }

    /**
     * Updates the Apple Pay domain association file.
     * Reports failure only if file isn't already being served properly.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function update_domain_association_file() {
        if ( $this->verify_hosted_domain_association_file_is_up_to_date() ) {
            return;
        }

        $response = $this->copy_and_overwrite_domain_association_file();

        if ( empty( $response['success'] ) ) {
            $url = get_site_url() . '/' . self::DOMAIN_ASSOCIATION_FILE_DIR . '/' . self::DOMAIN_ASSOCIATION_FILE_NAME;
            Helper::log(
                sprintf(
                    /* translators: 1) error message, 2) expected domain association file URL */
                    __( 'Error: %1$s. To enable Apple Pay, domain association file must be hosted at %2$s.', 'dokan' ),
                    $response['message'],
                    $url
                )
            );
        } else {
            Helper::log( __( 'Domain association file updated.', 'dokan' ) );
        }
    }

    /**
     * Adds a rewrite rule for serving the domain association file from the proper location.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function add_domain_association_rewrite_rule() {
        $regex    = '^\\' . self::DOMAIN_ASSOCIATION_FILE_DIR . '\/' . self::DOMAIN_ASSOCIATION_FILE_NAME . '$';
        $redirect = 'index.php?' . self::DOMAIN_ASSOCIATION_FILE_NAME . '=1';

        add_rewrite_rule( $regex, $redirect, 'top' );
    }

    /**
     * Add to the list of publicly allowed query variables.
     *
     * @since 3.6.1
     *
     * @param  array $query_vars - provided public query vars.
     *
     * @return array Updated public query vars.
     */
    public function whitelist_domain_association_query_param( $query_vars ) {
        $query_vars[] = self::DOMAIN_ASSOCIATION_FILE_NAME;
        return $query_vars;
    }

    /**
     * Serve domain association file when proper query param is provided.
     *
     * @since 3.6.1
     *
     * @param WP WordPress environment object.
     *
     * @return void
     */
    public function parse_domain_association_request( $wp ) {
        if (
            ! isset( $wp->query_vars[ self::DOMAIN_ASSOCIATION_FILE_NAME ] ) ||
            '1' !== $wp->query_vars[ self::DOMAIN_ASSOCIATION_FILE_NAME ]
        ) {
            return;
        }

        Helper::init_filesystem();
        global $wp_filesystem;

        header( 'Content-Type: text/plain;charset=utf-8' );
        echo esc_html( $wp_filesystem->get_contents( $this->get_registration_file_path() ) );
        exit;
    }

    /**
     * Retrieves path for domain registration file.
     *
     * @since 3.6.1
     *
     * @return string
     */
    protected function get_registration_file_path() {
        return DOKAN_STRIPE_EXPRESS_PATH . '/' . self::DOMAIN_ASSOCIATION_FILE_NAME;
    }

    /**
     * Makes request to register the domain with Stripe/Apple Pay.
     *
     * @since 3.6.1
     *
     * @param string $secret_key
     *
     * @return void
     * @throws Exception
     */
    private function register_domain( $secret_key ) {
        if ( empty( $secret_key ) ) {
            throw new Exception( __( 'Unable to verify domain - missing secret key.', 'dokan' ) );
        }

        try {
            Stripe::setApiKey( $secret_key );
            $response = ApplePayDomain::create(
                [
                    'domain_name' => $this->domain_name,
                ]
            );

            Helper::log( 'Apple Pay Domain Registration: ' . print_r( $response, true ) );
        } catch ( Exception $e ) {
            $this->apple_pay_verify_notice = $e->getMessage();
            /* translators: error message */
            throw new Exception( sprintf( __( 'Unable to verify domain - %s', 'dokan' ), $e->getMessage() ) );
        }
    }

    /**
     * Processes the Apple Pay domain verification.
     *
     * @since 3.6.1
     *
     * @param string $secret_key
     *
     * @return bool Whether domain verification succeeded.
     */
    public function register_domain_with_apple( $secret_key ) {
        try {
            $this->register_domain( $secret_key );

            // No errors to this point, verification success!
            $this->stripe_settings['apple_pay_verified_domain'] = $this->domain_name;
            $this->stripe_settings['apple_pay_domain_set']      = 'yes';
            $this->apple_pay_domain_set                         = true;

            Settings::update( $this->stripe_settings );
            Helper::log( 'Your domain has been verified with Apple Pay!' );

            return true;
        } catch ( Exception $e ) {
            $this->stripe_settings['apple_pay_verified_domain'] = $this->domain_name;
            $this->stripe_settings['apple_pay_domain_set']      = 'no';
            $this->apple_pay_domain_set                         = false;

            Settings::update( $this->stripe_settings );
            Helper::log( 'Error: ' . $e->getMessage() );

            return false;
        }
    }

    /**
     * Process the Apple Pay domain verification if proper settings are configured.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function verify_domain_if_configured() {
        $secret_key = $this->get_secret_key();
        if ( ! $this->is_enabled() || empty( $secret_key ) ) {
            return;
        }

        // Ensure that domain association file will be served.
        flush_rewrite_rules();

        // The rewrite rule method doesn't work if permalinks are set to Plain.
        // Create/update domain association file by copying it from the plugin folder as a fallback.
        $this->update_domain_association_file();

        // Register the domain with Apple Pay.
        $this->register_domain_with_apple( $secret_key );
    }

    /**
     * Conditionally process the Apple Pay domain verification after settings are initially set.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function verify_domain_on_new_settings( $option, $settings ) {
        $this->verify_domain_on_updated_settings( [], $settings );
    }

    /**
     * Conditionally process the Apple Pay domain verification after settings are updated.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function verify_domain_on_updated_settings( $prev_settings, $settings ) {
        // Grab previous state and then update cached settings.
        $this->stripe_settings = $prev_settings;
        $prev_secret_key       = $this->get_secret_key();
        $prev_is_enabled       = $this->is_enabled();

        // Restore current settings for further processing.
        $this->stripe_settings = $settings;

        // If Stripe or Payment Request Button wasn't enabled (or secret key was different) then might need to verify now.
        if ( ! $prev_is_enabled || ( $this->get_secret_key() !== $prev_secret_key ) ) {
            $this->verify_domain_if_configured();
        }
    }

    /**
     * Adds admin notices regarding apple pay verification status.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function show_verification_status_on_payment_settings() {
        if ( ! $this->should_show_verification_status() ) {
            return;
        }

        // phpcs:disable WordPress.Security.NonceVerification.Recommended
        // For dokan pages, the notice will be showed as dokan admin notice.
        if ( ! empty( $_GET['page'] ) && 'dokan' === sanitize_text_field( wp_unslash( $_GET['page'] ) ) ) {
            return;
        }

        // Only show the notice on the payment settings page.
        if (
            empty( $_GET['page'] ) ||
            'wc-settings' !== sanitize_text_field( wp_unslash( $_GET['page'] ) ) ||
            empty( $_GET['tab'] ) ||
            'checkout' !== sanitize_text_field( wp_unslash( $_GET['tab'] ) )
        ) {
            return;
        }
        // phpcs:enable WordPress.Security.NonceVerification.Recommended

        $notice = $this->get_domain_verification_notice();

        ?>
        <div class="error dokan-stripe-express-apple-pay-message">
            <?php if ( isset( $notice['title'] ) ) : ?>
                <p><?php echo esc_html( $notice['title'] ); ?></p>
            <?php endif; ?>
            <?php if ( isset( $notice['description'] ) ) : ?>
                <p><?php echo wp_kses_post( $notice['description'] ); ?></p>
            <?php endif; ?>
        </div>
        <?php
    }

    /**
     * Adds dokan admin notices regarding apple pay verification status.
     *
     * @since 3.7.8
     *
     * @param array $notices
     *
     * @return array
     */
    public function show_verification_status( $notices ) {
        if ( ! $this->should_show_verification_status() ) {
            return;
        }

        $notice = $this->get_domain_verification_notice();

        $notices[] = [
            'type'        => 'alert',
            'title'       => isset( $notice['title'] ) ? $notice['title'] : '',
            'description' => isset( $notice['description'] ) ? $notice['description'] : '',
            'priority'    => 10,
        ];

        return $notices;
    }

    /**
     * Checks whether or not domain verification status should be showed.
     *
     * @since 3.7.8
     *
     * @return boolean
     */
    public function should_show_verification_status() {
        if ( ! $this->is_enabled() ) {
            return false;
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return false;
        }

        if ( empty( $this->apple_pay_verify_notice ) && ( $this->apple_pay_domain_set || empty( $this->secret_key ) ) ) {
            return false;
        }

        return true;
    }

    /**
     * Generates notice regarding the status of Apple Pay domain verification.
     * Apple pay is enabled by default and domain verification initializes
     * when setting screen is displayed. So if domain verification is not set,
     * something went wrong so lets notify user.
     *
     * @since 3.7.8
     *
     * @return array{title:string,description:string}
     */
    public function get_domain_verification_notice() {
        if ( empty( $this->apple_pay_verify_notice ) ) {
            $title       = __( 'Apple Pay domain verification failed!', 'dokan' );
            $description = '';
        } else {
            $title       = __( 'Apple Pay domain verification failed with the following error!', 'dokan' );
            $description = sprintf(
                '<p><i>%s</i></p>',
                wp_kses(
                    make_clickable( esc_html( $this->apple_pay_verify_notice ) ),
                    [
                        'a' => [
                            'href'  => [],
                            'title' => [],
                        ],
                    ]
                )
            );
        }

        $description .= sprintf(
            /* translators: 1) HTML anchor open tag 2) HTML anchor closing tag */
            esc_html__(
                'Please check the %1$slogs%2$s for more details on this issue. Logging must be enabled to see recorded logs.',
                'dokan'
            ),
            sprintf( '<a href="%s">', esc_url( admin_url( 'admin.php?page=wc-status&tab=logs' ) ) ),
            '</a>'
        );

        return [
            'title'       => $title,
            'description' => $description,
        ];
    }
}
