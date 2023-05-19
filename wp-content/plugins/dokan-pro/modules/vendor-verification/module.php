<?php

namespace WeDevs\DokanPro\Modules\VendorVerification;

use Hybridauth\Exception\Exception;
use Hybridauth\Hybridauth;
use WeDevs\DokanPro\Storage\Session;
use WeDevs\DokanPro\Modules\Germanized\Helper;

/**
 * Dokan_Seller_Verification class
 *
 * @class Dokan_Seller_Verification The class that holds the entire Dokan_Seller_Verification plugin
 */
class Module {

    /**
     * Module version
     *
     * @since 3.1.3
     *
     * @var string
     */
    public $version = null;

    public static $plugin_prefix;
    public static $plugin_url;
    public static $plugin_path;
    public static $plugin_basename;
    private $config;
    private $base_url;

    public $e_msg = false;

    /**
     * Constructor for the Dokan_Seller_Verification class
     *
     * Sets up all the appropriate hooks and actions
     * within our plugin.
     *
     * @uses register_activation_hook()
     * @uses register_deactivation_hook()
     * @uses is_admin()
     * @uses add_action()
     */
    public function __construct() {
        $this->version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : DOKAN_PRO_PLUGIN_VERSION;

        self::$plugin_prefix   = 'Dokan_verification_';
        self::$plugin_basename = plugin_basename( __FILE__ );
        self::$plugin_url      = plugin_dir_url( self::$plugin_basename );
        self::$plugin_path     = trailingslashit( dirname( __FILE__ ) );

        $this->init_hooks();
        $this->define_constants();
        $this->includes_file();

        // plugin activation hook
        add_action( 'dokan_activated_module_vendor_verification', array( $this, 'activate' ) );
        add_action( 'init', [ $this, 'init_config' ] );
    }

    /**
     * @since 3.3.1
     *
     * @return void
     */
    public function init_config() {
        $this->base_url = dokan_get_navigation_url( 'settings/verification' );
        $this->config = $this->get_provider_config();
    }

    public function init_hooks() {
        $installed_version = get_option( 'dokan_theme_version' );

        add_action( 'template_redirect', array( $this, 'monitor_autheticate_requests' ), 99 );

        // Overriding templating system for vendor-verification
        add_filter( 'dokan_set_template_path', [ $this, 'load_verification_templates' ], 30, 3 );

        // widget
        add_action( 'widgets_init', array( $this, 'register_widgets' ) );

        // Loads frontend scripts and styles
        add_action( 'init', array( $this, 'register_scripts' ) );
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

        //filters
        add_filter( 'dokan_get_all_cap', array( $this, 'add_capabilities' ), 10 );
        add_filter( 'dokan_get_dashboard_settings_nav', array( $this, 'register_dashboard_menu' ) );
        add_filter( 'dokan_query_var_filter', array( $this, 'dokan_verification_template_endpoint' ) );
        add_filter( 'dokan_seller_address_fields', [ $this, 'required_vendor_residence_proof_field' ] );

        //ajax hooks
        add_action( 'wp_ajax_dokan_update_verify_info', array( $this, 'dokan_update_verify_info' ) );
        add_action( 'wp_ajax_dokan_id_verification_cancel', array( $this, 'dokan_id_verification_cancel' ) );
        add_action( 'wp_ajax_dokan_address_verification_cancel', array( $this, 'dokan_address_verification_cancel' ) );
        add_action( 'wp_ajax_dokan_sv_form_action', array( $this, 'dokan_sv_form_action' ) );
        add_action( 'wp_ajax_dokan_v_load_state_by_country', array( $this, 'dokan_v_load_state_by_country' ) );
        add_action( 'wp_ajax_dokan_update_verify_info_insert_address', array( $this, 'dokan_update_verify_info_insert_address' ) );
        add_action( 'wp_ajax_dokan_v_send_sms', array( $this, 'dokan_v_send_sms' ) );
        add_action( 'wp_ajax_dokan_v_verify_sms_code', array( $this, 'dokan_v_verify_sms_code' ) );
        add_action( 'wp_ajax_dokan_update_verify_info_insert_company', array( $this, 'dokan_update_verify_info_insert_company' ) );
        add_action( 'wp_ajax_dokan_company_verification_cancel', array( $this, 'dokan_company_verification_cancel' ) );
        add_action( 'dokan_vendor_address_verification_template', [ $this, 'added_vendor_residence_proof_template' ], 10, 2 );

        if ( $installed_version >= 2.4 ) {
            add_filter( 'dokan_dashboard_settings_heading_title', array( $this, 'load_verification_template_header' ), 15, 2 );
            add_action( 'dokan_render_settings_content', array( $this, 'load_verification_content' ) );
        } else {
            add_action( 'dokan_settings_template', array( $this, 'dokan_verification_set_templates' ), 10, 2 );
        }

        add_action( 'dokan_admin_menu', array( $this, 'load_verfication_admin_template' ), 15 );

        // usermeta update hook
        add_action( 'updated_user_meta', array( $this, 'dokan_v_recheck_verification_status_meta' ), 10, 4 );

        // Custom dir for vendor uploaded file
        add_filter( 'upload_dir', array( $this, 'dokan_customize_upload_dir' ), 10 );

        // flush rewrite rules
        add_action( 'woocommerce_flush_rewrite_rules', [ $this, 'flush_rewrite_rules' ] );
        // display vendor verification badge
        add_action( 'dokan_store_header_after_store_name', [ $this, 'add_vendor_verified_icon' ] );
        add_action( 'dokan_store_list_loop_after_store_name', [ $this, 'add_vendor_verified_icon' ] );
        add_action( 'dokan_product_single_after_store_name', [ $this, 'add_vendor_verified_icon' ] );
    }

    /**
     * Render vendor verified icon after store name
     *
     * @since 3.5.2
     *
     * @return void
     */
    public function add_vendor_verified_icon( $vendor ) {
        // check seller id, address or business has not verified
        if ( false === strpos( get_user_meta( $vendor->get_id(), 'dokan_verification_status', true ), 'approved' ) ) {
            return;
        }

        ?>
            <svg class="tips" title="<?php esc_html_e( 'Verified', 'dokan' ); ?>" width="18" height="17" viewBox="0 0 18 17" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M15.8957 6.51207C15.6765 6.3649 15.4785 6.18837 15.3074 5.98736C15.3201 5.70749 15.3763 5.43133 15.4741 5.16883C15.6579 4.54661 15.8868 3.77223 15.4457 3.16628C15.0013 2.55551 14.1896 2.53501 13.5374 2.51834C13.2631 2.5293 12.9887 2.4998 12.7229 2.43078C12.5768 2.20159 12.4652 1.95214 12.3918 1.69043C12.1742 1.07073 11.9031 0.299431 11.1772 0.0635171C10.4728 -0.165422 9.84062 0.269543 9.28322 0.652041C9.05921 0.830895 8.80772 0.97227 8.5385 1.0707C8.26914 0.972382 8.01751 0.830979 7.79348 0.652041C7.23605 0.269206 6.60371 -0.164385 5.89947 0.0635171C5.17372 0.299431 4.90273 1.07034 4.68489 1.69018C4.61161 1.95058 4.50127 2.1991 4.35726 2.42809C4.09068 2.49921 3.81487 2.52955 3.53921 2.51809C2.8871 2.53473 2.07535 2.55526 1.631 3.166C1.18985 3.77228 1.41873 4.54667 1.60254 5.16894C1.69918 5.43007 1.75594 5.70425 1.77087 5.98229C1.60044 6.18613 1.40173 6.36456 1.18077 6.5121C0.654604 6.91337 0 7.41293 0 8.18596C0 8.959 0.654604 9.45856 1.18089 9.85985C1.40008 10.007 1.59806 10.1836 1.76924 10.3846C1.75652 10.6644 1.70028 10.9406 1.60249 11.2031C1.4187 11.8253 1.18979 12.5998 1.63095 13.2056C2.0753 13.8164 2.88705 13.8369 3.53918 13.8536C3.81353 13.8427 4.08796 13.8722 4.35371 13.9412C4.49981 14.1704 4.61138 14.4198 4.6848 14.6815C4.90265 15.3013 5.17363 16.0725 5.89956 16.3085C6.02816 16.3507 6.16267 16.3721 6.29802 16.3721C6.84436 16.3721 7.34325 16.0292 7.79353 15.72C8.01754 15.5411 8.26903 15.3997 8.53828 15.3012C8.80764 15.3995 9.05927 15.5409 9.28336 15.7199C9.84076 16.1027 10.4729 16.536 11.1773 16.3084C11.9031 16.0725 12.1741 15.3016 12.3919 14.6817C12.4652 14.4213 12.5755 14.1728 12.7195 13.9438C12.9861 13.8727 13.2619 13.8424 13.5376 13.8538C14.1897 13.8372 15.0015 13.8167 15.4458 13.2059C15.887 12.5997 15.6581 11.8252 15.4743 11.203C15.3776 10.9419 15.3209 10.6677 15.3059 10.3896C15.4764 10.1858 15.6751 10.0074 15.896 9.85983C16.422 9.45856 17.0766 8.959 17.0766 8.18596C17.0766 7.41293 16.422 6.91337 15.8957 6.51207ZM11.7096 6.91023L8.15197 10.4678C7.87421 10.7457 7.42381 10.7458 7.14596 10.468C7.1459 10.468 7.14585 10.4679 7.14579 10.4678L5.36697 8.68902C5.08559 8.41471 5.07988 7.96422 5.3542 7.68285C5.62851 7.40147 6.079 7.39575 6.36037 7.67007C6.36469 7.67427 6.36895 7.67853 6.37315 7.68285L7.64888 8.95861L10.7034 5.90402C10.9778 5.62265 11.4282 5.61696 11.7096 5.89128C11.991 6.1656 11.9967 6.61608 11.7224 6.89746C11.7182 6.9018 11.7139 6.90603 11.7096 6.91023Z" fill="#2196F3"/>
            </svg>
        <?php
    }

    /**
     * Get plugin path
     *
     * @since 1.5.1
     *
     * @return void
     **/
    public function plugin_path() {
        return untrailingslashit( plugin_dir_path( __FILE__ ) );
    }

    public function get_provider_config() {
        $config = array(
            'callback'   => $this->base_url,
            'debug_mode' => false,

            'providers' => array(
                'Facebook' => array(
                    'enabled' => true,
                    'keys'    => array(
                        'id' => '',
                        'secret' => '',
                    ),
                    'scope'   => 'email, public_profile',
                ),
                'Google'   => array(
                    'enabled'         => true,
                    'keys'            => array(
                        'id' => '',
                        'secret' => '',
                    ),
                    // @codingStandardsIgnoreLine
                    'scope'           => 'https://www.googleapis.com/auth/userinfo.profile ' . 'https://www.googleapis.com/auth/userinfo.email', // optional
                    'access_type'     => 'offline',
                    'approval_prompt' => 'force',
                    'hd'              => home_url(),
                ),
                'LinkedIn' => array(
                    'enabled' => true,
                    'keys'    => array(
                        'id' => '',
                        'secret' => '',
                    ),
                ),
                'Twitter'  => array(
                    'enabled' => true,
                    'keys'    => array(
                        'key' => '',
                        'secret' => '',
                    ),
                ),
            ),
        );

        //facebook config from admin
        $fb_id     = dokan_get_option( 'fb_app_id', 'dokan_verification' );
        $fb_secret = dokan_get_option( 'fb_app_secret', 'dokan_verification' );
        if ( ! empty( $fb_id ) && ! empty( $fb_secret ) ) {
            $config['providers']['Facebook']['keys']['id']     = $fb_id;
            $config['providers']['Facebook']['keys']['secret'] = $fb_secret;
        }
        //google config from admin
        $g_id     = dokan_get_option( 'google_app_id', 'dokan_verification' );
        $g_secret = dokan_get_option( 'google_app_secret', 'dokan_verification' );
        if ( ! empty( $g_id ) && ! empty( $g_secret ) ) {
            $config['providers']['Google']['keys']['id']     = $g_id;
            $config['providers']['Google']['keys']['secret'] = $g_secret;
        }
        //linkedin config from admin
        $l_id     = dokan_get_option( 'linkedin_app_id', 'dokan_verification' );
        $l_secret = dokan_get_option( 'linkedin_app_secret', 'dokan_verification' );
        if ( ! empty( $l_id ) && ! empty( $l_secret ) ) {
            $config['providers']['LinkedIn']['keys']['id']     = $l_id;
            $config['providers']['LinkedIn']['keys']['secret'] = $l_secret;
        }
        //Twitter config from admin
        $twitter_id     = dokan_get_option( 'twitter_app_id', 'dokan_verification' );
        $twitter_secret = dokan_get_option( 'twitter_app_secret', 'dokan_verification' );
        if ( ! empty( $twitter_id ) && ! empty( $twitter_secret ) ) {
            $config['providers']['Twitter']['keys']['key']    = $twitter_id;
            $config['providers']['Twitter']['keys']['secret'] = $twitter_secret;
        }

        /**
         * Filter the Config array of Hybridauth
         *
         * @since 1.0.0
         *
         * @param array $config
         */
        $config = apply_filters( 'dokan_verify_providers_config', $config );

        return $config;
    }

    public function load_verification_template_header( $heading, $query_vars ) {
        if ( isset( $query_vars ) && (string) $query_vars === 'verification' ) {
            $heading = __( 'Verification', 'dokan' );
        }

        return $heading;
    }

    public function load_verification_content( $query_vars ) {
        if ( isset( $query_vars['settings'] ) && (string) $query_vars['settings'] === 'verification' ) {
            if ( current_user_can( 'dokan_view_store_verification_menu' ) ) {
                dokan_get_template_part(
                    'vendor-verification/verification-new', '', array(
                        'is_vendor_verification'   => true,
                    )
                );
            } else {
                dokan_get_template_part(
                    'global/dokan-error', '', array(
                        'deleted' => false,
                        'message' => __( 'You have no permission to view this verification page', 'dokan' ),
                    )
                );
            }

            return;
        }
    }

    public function load_verfication_admin_template() {
        add_submenu_page( 'dokan', __( 'Vendor Verifications', 'dokan' ), __( 'Verifications', 'dokan' ), 'manage_options', 'dokan-seller-verifications', array( $this, 'seller_verfications_page' ) );
    }

    public function seller_verfications_page() {
        require_once dirname( __FILE__ ) . '/templates/admin-verifications.php';
    }

    /**
     * Placeholder for activation function
     *
     * Nothing being called here yet.
     */
    public function activate() {
        global $wp_roles;

        if ( class_exists( 'WP_Roles' ) && ! isset( $wp_roles ) ) {
            // @codingStandardsIgnoreLine
            $wp_roles = new \WP_Roles();
        }

        $wp_roles->add_cap( 'seller', 'dokan_view_store_verification_menu' );
        $wp_roles->add_cap( 'administrator', 'dokan_view_store_verification_menu' );
        $wp_roles->add_cap( 'shop_manager', 'dokan_view_store_verification_menu' );

        // flash rewrite rules
        $this->flush_rewrite_rules();
    }

    /**
     * Flush rewrite rules
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function flush_rewrite_rules() {
        dokan()->rewrite->register_rule();
        flush_rewrite_rules();
    }

    /**
     * Define module constants
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function define_constants() {
        define( 'DOKAN_VERIFICATION_PLUGIN_VERSION', '1.2.0' );
        define( 'DOKAN_VERFICATION_DIR', dirname( __FILE__ ) );
        define( 'DOKAN_VERFICATION_INC_DIR', dirname( __FILE__ ) . '/includes/' );
        define( 'DOKAN_VERFICATION_LIB_DIR', dirname( __FILE__ ) . '/lib/' );
        define( 'DOKAN_VERFICATION_PLUGIN_ASSEST', plugins_url( 'assets', __FILE__ ) );
        // give a way to turn off loading styles and scripts from parent theme

        if ( ! defined( 'DOKAN_VERFICATION_LOAD_STYLE' ) ) {
            define( 'DOKAN_VERFICATION_LOAD_STYLE', true );
        }

        if ( ! defined( 'DOKAN_VERFICATION_LOAD_SCRIPTS' ) ) {
            define( 'DOKAN_VERFICATION_LOAD_SCRIPTS', true );
        }
    }

    /**
     * Include all the required files
     *@since 1.0.0
     *
     * @return void
     */
    public function includes_file() {
        $inc_dir = DOKAN_VERFICATION_INC_DIR;
        $lib_dir = DOKAN_VERFICATION_LIB_DIR;

        require_once $lib_dir . 'sms-verification/gateways.php';
        require_once $inc_dir . 'theme-functions.php';

        //widgets
        require_once $inc_dir . '/widgets/verifications-list.php';

        if ( is_admin() ) {
            require_once $inc_dir . 'admin/admin.php';
        }

        // Init Vendor Verification Cache
        require_once $inc_dir . 'DokanVendorVerificationCache.php';
        new \DokanVendorVerificationCache();
    }

    /**
     * Register widgets
     *
     * @since 2.8
     *
     * @return void
     */
    public function register_widgets() {
        register_widget( 'Dokan_Store_Verification_list' );
    }

    /**
     * Monitors Url for Hauth Request and process Hauth for authentication
     *
     * @global type $current_user
     *
     * @return void
     */
    public function monitor_autheticate_requests() {
        $vendor_id = dokan_get_current_user_id();

        if ( ! $vendor_id ) {
            return;
        }

        if ( isset( $_GET['dokan_auth_dc'] ) ) { // phpcs:ignore
            $seller_profile = dokan_get_store_info( $vendor_id );
            $provider_dc    = sanitize_text_field( wp_unslash( $_GET['dokan_auth_dc'] ) ); //phpcs:ignore

            $seller_profile['dokan_verification'][ $provider_dc ] = '';

            update_user_meta( $vendor_id, 'dokan_profile_settings', $seller_profile );
            return;
        }

        try {
            /**
             * Feed the config array to Hybridauth
             *
             * @var Hybridauth
             */
            $hybridauth = new Hybridauth( $this->config );

            /**
             * Initialize session storage.
             *
             * @var Session
             */
            $storage = new Session( 'vendor_verify', 5 * 60 );

            /**
             * Hold information about provider when user clicks on Sign In.
             */
            $provider = ! empty( $_GET['dokan_auth'] ) ? sanitize_text_field( wp_unslash( $_GET['dokan_auth'] ) ) : ''; // phpcs:ignore

            if ( $provider ) {
                $storage->set( 'provider', $provider );
            }

            if ( $provider = $storage->get( 'provider' ) ) { //phpcs:ignore
                $adapter = $hybridauth->getAdapter( $provider );
                $adapter->setStorage( $storage );
                $adapter->authenticate();
            }

            if ( ! isset( $adapter ) ) {
                return;
            }

            $user_profile = $adapter->getUserProfile();

            if ( ! $user_profile ) {
                $storage->clear();
                wc_add_notice( __( 'Something went wrong! please try again', 'dokan' ), 'success' );
                wp_safe_redirect( $this->callback );
            }

            $seller_profile = dokan_get_store_info( $vendor_id );
            $seller_profile['dokan_verification'][ $provider ] = (array) $user_profile;

            update_user_meta( $vendor_id, 'dokan_profile_settings', $seller_profile );
            $storage->clear();
        } catch ( Exception $e ) {
            $this->e_msg = $e->getMessage();
        }
    }

    /**
     * Load rma templates. so that it can overide from theme
     *
     * Just create `rma` folder inside dokan folder then
     * override your necessary template.
     *
     * @since 1.0.0
     *
     * @return void
     **/
    public function load_verification_templates( $template_path, $template, $args ) {
        if ( isset( $args['is_vendor_verification'] ) && $args['is_vendor_verification'] ) {
            return $this->plugin_path() . '/templates';
        }

        return $template_path;
    }

    /**
     * Enqueue admin scripts
     *
     * Allows plugin assets to be loaded.
     *
     * @uses wp_enqueue_script()
     * @uses wp_localize_script()
     * @uses wp_enqueue_style
     */
    public function enqueue_scripts() {
        global $wp;

        if ( isset( $wp->query_vars['settings'] ) && 'verification' === $wp->query_vars['settings'] ) {
            $data = [
                'upload_title' => __( 'Upload Proof', 'dokan' ),
                'insert_title' => __( 'Insert Proof', 'dokan' ),
            ];

            wp_enqueue_style( 'dokan-verification-styles' );
            wp_enqueue_script( 'dokan-verification-scripts' );
            wp_localize_script( 'dokan-verification-scripts', 'verify_data', $data );

            wp_enqueue_script( 'wc-country-select' );
            wp_enqueue_script( 'dokan-form-validate' );
        }
    }

    public function register_scripts() {
        list( $suffix, $script_version ) = dokan_get_script_suffix_and_version();

        wp_register_style( 'dokan-verification-styles', plugins_url( 'assets/css/style.css', __FILE__ ), true, $script_version );
        wp_register_script( 'dokan-verification-scripts', plugins_url( 'assets/js/script.js', __FILE__ ), array( 'jquery' ), $this->version, true );
    }

    /**
     * Add capabilities
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_capabilities( $capabilities ) {
        $capabilities['menu']['dokan_view_store_verification_menu'] = __( 'View verification settings menu', 'dokan' );

        return $capabilities;
    }

    /**
     * Adds Verification menu on Dokan Seller Dashboard
     *
     * @since 1.0.0
     * @param array() $urls
     * @return array() $urls
     */
    public function register_dashboard_menu( $urls ) {
        $urls['verification'] = array(
            'title'      => __( 'Verification', 'dokan' ),
            'icon'       => '<i class="fas fa-check"></i>',
            'url'        => dokan_get_navigation_url( 'settings/verification' ),
            'pos'        => 55,
            'permission' => 'dokan_view_store_verification_menu',
        );

        return $urls;
    }

    public function dokan_verification_set_templates( $path, $part ) {
        if ( 'verification' === (string) $part ) {
            dokan_get_template_part(
                'vendor-verification/verification', '', array(
                    'is_vendor_verification' => true,
                )
            );
            // return DOKAN_VERFICATION_DIR . '/templates/verification.php';
        }

        return $path;
    }

    public function dokan_verification_template_endpoint( $query_var ) {
        $query_var[] = 'verification';
        return $query_var;
    }

    /**
     * Required vendor residence proof field for vendor verification.
     *
     * @since 3.5.5
     *
     * @param array $fields Required verification fields
     *
     * @return array
     */
    public function required_vendor_residence_proof_field( $fields ) {
        $fields['proof']['required'] = 1;

        return $fields;
    }

    /** Updates photo Id for verification
     *
     * @since 1.0.0
     * @return void
     */
    public function dokan_update_verify_info() {
        // @codingStandardsIgnoreLine
        parse_str( $_POST['data'], $postdata );

        if ( ! wp_verify_nonce( $postdata['dokan_verify_action_nonce'], 'dokan_verify_action' ) ) {
            wp_send_json_error( __( 'Are you cheating?', 'dokan' ) );
        }

        $user_id        = get_current_user_id();
        $seller_profile = dokan_get_store_info( $user_id );

        if ( isset( $postdata['dokan_v_id_type'] ) && isset( $postdata['dokan_gravatar'] ) ) {
            $seller_profile['dokan_verification']['info']['photo_id']          = $postdata['dokan_gravatar'];
            $seller_profile['dokan_verification']['info']['dokan_v_id_type']   = $postdata['dokan_v_id_type'];
            $seller_profile['dokan_verification']['info']['dokan_v_id_status'] = 'pending';

            update_user_meta( $user_id, 'dokan_profile_settings', $seller_profile );

            do_action( 'dokan_verification_updated', $user_id );

            dokan_verification_request_submit_email();

            $msg = sprintf( __( 'Your ID verification request is Sent and %s approval', 'dokan' ), self::get_translated_status( $seller_profile['dokan_verification']['info']['dokan_v_id_status'] ) );
            wp_send_json_success( $msg );
        }
    }

    /**
     * Get translated version of approval statuses
     *
     * @since 3.5.4
     *
     * @param $status
     *
     * @return string Translated Status
     */
    public static function get_translated_status( $status ) {
        switch ( $status ) {
            case 'approved':
                return __( 'approved', 'dokan' );
            case 'pending':
                return __( 'pending', 'dokan' );
            case 'rejected':
                return __( 'rejected', 'dokan' );
            default:
                return $status;
        }
    }

    /*
     * Clears Verify Info value for ID verification via AJAX
     *
     * @since 1.0.0
     *
     * @return AJAX Success/fail
     */

    public function dokan_id_verification_cancel() {
        $user_id        = get_current_user_id();
        $seller_profile = dokan_get_store_info( $user_id );

        unset( $seller_profile['dokan_verification']['info']['photo_id'] );
        unset( $seller_profile['dokan_verification']['info']['dokan_v_id_type'] );
        unset( $seller_profile['dokan_verification']['info']['dokan_v_id_status'] );
        //update user meta pending here
        update_user_meta( $user_id, 'dokan_profile_settings', $seller_profile );

        do_action( 'dokan_id_verification_cancelled', $user_id );

        $msg = __( 'Your ID Verification request is cancelled', 'dokan' );

        wp_send_json_success( $msg );
    }

    /*
     * Clears Verify Info value for Address verification via AJAX
     *
     * @since 1.0.0
     *
     * @return AJAX Success/fail
     */

    public function dokan_address_verification_cancel() {
        $user_id        = get_current_user_id();
        $seller_profile = dokan_get_store_info( $user_id );

        unset( $seller_profile['dokan_verification']['info']['store_address'] );
        //update user meta pending here
        update_user_meta( $user_id, 'dokan_profile_settings', $seller_profile );

        $msg = __( 'Your Address Verification request is cancelled', 'dokan' );

        do_action( 'dokan_address_verification_cancel', $user_id );

        wp_send_json_success( $msg );
    }

    /* Admin panel verification actions managed here
     * @since 1.0.0
     *
     * @return Ajax Success/fail
     */

    public function dokan_sv_form_action() {
        // @codingStandardsIgnoreStart
        parse_str( $_POST['formData'], $postdata );
        if ( ! wp_verify_nonce( $postdata['dokan_sv_nonce'], 'dokan_sv_nonce_action' ) ) {
            wp_send_json_error( __( 'Are you cheating?', 'dokan' ) );
        }

        $postdata['type']               = ! empty( $_POST['type'] ) ? sanitize_text_field( wp_unslash( $_POST['type'] ) ) : '';
        $postdata['status']             = ! empty( $_POST['status'] ) ? sanitize_text_field( wp_unslash( $_POST['status'] ) ) : '';
        $postdata['seller_id']          = ! empty( $_POST['seller_id'] ) ? absint( wp_unslash( $_POST['seller_id'] ) ) : '';
        $postdata['street_1']           = ! empty( $postdata['street_1'] ) ? sanitize_text_field( wp_unslash( $postdata['street_1'] ) ) : '';
        $postdata['street_2']           = ! empty( $postdata['street_2'] ) ? sanitize_text_field( wp_unslash( $postdata['street_2'] ) ) : '';
        $postdata['store_zip']          = ! empty( $postdata['store_zip'] ) ? sanitize_text_field( wp_unslash( $postdata['store_zip'] ) ) : '';
        $postdata['store_city']         = ! empty( $postdata['store_city'] ) ? sanitize_text_field( wp_unslash( $postdata['store_city'] ) ) : '';
        $postdata['store_state']        = ! empty( $postdata['store_state'] ) ? sanitize_text_field( wp_unslash( $postdata['store_state'] ) ) : '';
        $postdata['store_country']      = ! empty( $postdata['store_country'] ) ? sanitize_text_field( wp_unslash( $postdata['store_country'] ) ) : '';
        $postdata['dokan_gravatar']     = ! empty( $postdata['dokan_gravatar'] ) ? absint( wp_unslash( $postdata['dokan_gravatar'] ) ) : '';
        $postdata['dokan_v_id_type']    = ! empty( $postdata['dokan_v_id_type'] ) ? sanitize_text_field( wp_unslash( $postdata['dokan_v_id_type'] ) ) : '';
        $postdata['proof_of_residence'] = ! empty( $postdata['proof_of_residence'] ) ? sanitize_text_field( wp_unslash( $postdata['proof_of_residence'] ) ) : '';
        // @codingStandardsIgnoreEnd

        $user_id        = $postdata['seller_id'];
        $seller_profile = dokan_get_store_info( $user_id );

        switch ( $postdata['status'] ) {
            case 'approved':
                if ( 'id' === $postdata['type'] ) {
                    $seller_profile['dokan_verification']['verified_info']['photo'] = array(
                        'photo_id'        => $postdata['dokan_gravatar'],
                        'dokan_v_id_type' => $postdata['dokan_v_id_type'],
                    );

                    $seller_profile['dokan_verification']['info']['dokan_v_id_status'] = 'approved';
                } elseif ( 'address' === $postdata['type'] ) {
                    $seller_profile['dokan_verification']['verified_info']['store_address'] = [
                        'street_1' => $postdata['street_1'],
                        'street_2' => $postdata['street_2'],
                        'city'     => $postdata['store_city'],
                        'zip'      => $postdata['store_zip'],
                        'country'  => $postdata['store_country'],
                        'state'    => $postdata['store_state'],
                        'proof'    => $postdata['proof_of_residence'],
                    ];
                    $seller_profile['address'] = [
                        'street_1' => $postdata['street_1'],
                        'street_2' => $postdata['street_2'],
                        'city'     => $postdata['store_city'],
                        'zip'      => $postdata['store_zip'],
                        'country'  => $postdata['store_country'],
                        'state'    => $postdata['store_state'],
                        'proof'    => $postdata['proof_of_residence'],
                    ];

                    $seller_profile['dokan_verification']['info']['store_address']['v_status'] = 'approved';
                } elseif ( 'company_verification_files' === $postdata['type'] ) {
                    $seller_profile['dokan_verification']['info']['company_v_status'] = 'approved';
                }

                update_user_meta( $user_id, 'dokan_profile_settings', $seller_profile );

                break;

            case 'pending':
                if ( 'id' === $postdata['type'] ) {
                    $seller_profile['dokan_verification']['info']['dokan_v_id_status'] = 'pending';
                } elseif ( 'address' === $postdata['type'] ) {
                    $seller_profile['dokan_verification']['info']['store_address']['v_status'] = 'pending';
                } elseif ( 'company_verification_files' === $postdata['type'] ) {
                    $seller_profile['dokan_verification']['info']['company_v_status'] = 'pending';
                }

                update_user_meta( $user_id, 'dokan_profile_settings', $seller_profile );

                break;

            case 'rejected':
                if ( 'id' === $postdata['type'] ) {
                    $seller_profile['dokan_verification']['info']['dokan_v_id_status'] = 'rejected';
                } elseif ( 'address' === $postdata['type'] ) {
                    $seller_profile['dokan_verification']['info']['store_address']['v_status'] = 'rejected';
                } elseif ( 'company_verification_files' === $postdata['type'] ) {
                    $seller_profile['dokan_verification']['info']['company_v_status'] = 'rejected';
                }

                update_user_meta( $user_id, 'dokan_profile_settings', $seller_profile );

                break;

            case 'disapproved':
                if ( 'id' === $postdata['type'] ) {
                    unset( $seller_profile['dokan_verification']['verified_info']['photo'] );
                    $seller_profile['dokan_verification']['info']['dokan_v_id_status'] = 'pending';
                } elseif ( 'address' === $postdata['type'] ) {
                    unset( $seller_profile['dokan_verification']['verified_info']['store_address'] );

                    $seller_profile['dokan_verification']['info']['store_address']['v_status'] = 'pending';
                } elseif ( 'company_verification_files' === $postdata['type'] ) {
                    $seller_profile['dokan_verification']['info']['company_v_status'] = 'pending';
                }

                update_user_meta( $user_id, 'dokan_profile_settings', $seller_profile );

                break;
        }

        do_action( 'dokan_verification_status_change', $user_id, $seller_profile, $postdata );

        dokan_verification_request_changed_by_admin_email( $seller_profile, $postdata );

        $msg = __( 'Information updated', 'dokan' );
        wp_send_json_success( $msg );
    }

    /*
     * Insert Verification page Address fields into Verify info via AJAX
     *
     * @since 1.0.0
     *
     * @return Ajax Success/fail
     */

    public function dokan_update_verify_info_insert_address() {
        // @codingStandardsIgnoreLine
        $address_field = $_POST['dokan_address'];

        // @codingStandardsIgnoreLine
        if ( ! wp_verify_nonce( $_POST['dokan_verify_action_address_form_nonce'], 'dokan_verify_action_address_form' ) ) {
            wp_send_json_error( __( 'Are you cheating?', 'dokan' ) );
        }

        $current_user   = get_current_user_id();
        $seller_profile = dokan_get_store_info( $current_user );

        $default = [
            'street_1' => '',
            'street_2' => '',
            'city'     => '',
            'zip'      => '',
            'country'  => '',
            'state'    => '',
            'proof'    => '',
            'v_status' => 'pending',
        ];

        if ( $address_field['state'] === 'N/A' ) {
            $address_field['state'] = '';
        }

        $store_address = wp_parse_args( $address_field, $default );

        $msg = __( 'Please fill all the required fields', 'dokan' );

        $seller_profile['dokan_verification']['info']['store_address'] = $store_address;

        update_user_meta( $current_user, 'dokan_profile_settings', $seller_profile );

        do_action( 'dokan_after_address_verification_added', $current_user );

        $msg = __( 'Your Address verification request is Sent and Pending approval', 'dokan' );

        dokan_verification_request_submit_email();
        wp_send_json_success( $msg );
    }

    /**
     * Sets the value of main verification status meta automatically
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function dokan_v_recheck_verification_status_meta( $meta_id, $object_id, $meta_key, $_meta_value ) {
        if ( 'dokan_profile_settings' !== (string) $meta_key ) {
            return;
        }
        $current_user   = $object_id;
        $seller_profile = dokan_get_store_info( $current_user );

        if ( ! isset( $seller_profile['dokan_verification']['info'] ) ) {
            return;
        }

        $id_status        = isset( $seller_profile['dokan_verification']['info']['dokan_v_id_status'] ) ? $seller_profile['dokan_verification']['info']['dokan_v_id_status'] : '';
        $address_status   = isset( $seller_profile['dokan_verification']['info']['store_address']['v_status'] ) ? $seller_profile['dokan_verification']['info']['store_address']['v_status'] : '';
        $company_v_status = isset( $seller_profile['dokan_verification']['info']['company_v_status'] ) ? $seller_profile['dokan_verification']['info']['company_v_status'] : '';

        $get_status = array_map(
            function( $status ) {
                if ( in_array( $status, array( 'approved', 'rejected', 'pending' ), true ) ) {
                    return $status;
                }
            }, array_unique( [ $id_status, $address_status, $company_v_status ] )
        );

        $get_status = implode( ',', array_filter( $get_status, 'strlen' ) );
        update_user_meta( $current_user, 'dokan_verification_status', $get_status );

        //clear info meta if empty
        if ( empty( $seller_profile['dokan_verification']['info'] ) ) {
            unset( $seller_profile['dokan_verification']['info'] );
            update_user_meta( $current_user, 'dokan_profile_settings', $seller_profile );
        }
    }

    /*
     * Sends SMS from verification template
     *
     * @since 1.0.0
     *
     * @return Ajax Success/fail
     *
     */

    public function dokan_v_send_sms() {
        // @codingStandardsIgnoreLine
        parse_str( $_POST['data'], $postdata );

        if ( ! wp_verify_nonce( $postdata['dokan_verify_action_nonce'], 'dokan_verify_action' ) ) {
            wp_send_json_error( __( 'Are you cheating?', 'dokan' ) );
        }
        $info['success'] = false;

        $sms  = \WeDevs_dokan_SMS_Gateways::instance();
        $info = $sms->send( $postdata['phone'] );

        // @codingStandardsIgnoreLine
        if ( $info['success'] == true ) {
            $current_user   = get_current_user_id();
            $seller_profile = dokan_get_store_info( $current_user );

            $seller_profile['dokan_verification']['info']['phone_no']        = $postdata['phone'];
            $seller_profile['dokan_verification']['info']['phone_code']   = $info['code'];
            $seller_profile['dokan_verification']['info']['phone_status'] = 'pending';

            update_user_meta( $current_user, 'dokan_profile_settings', $seller_profile );
        }
        wp_send_json_success( $info );
    }

    /*
     * Verify sent SMS code and update corresponding meta
     *
     * @since 1.0.0
     *
     * @return Ajax Success/fail
     *
     */
    public function dokan_v_verify_sms_code() {
        // @codingStandardsIgnoreLine
        parse_str( $_POST['data'], $postdata );

        if ( ! wp_verify_nonce( $postdata['dokan_verify_action_nonce'], 'dokan_verify_action' ) ) {
            wp_send_json_error( __( 'Are you cheating?', 'dokan' ) );
        }

        $current_user   = get_current_user_id();
        $seller_profile = dokan_get_store_info( $current_user );

        $saved_code = $seller_profile['dokan_verification']['info']['phone_code'];

        // @codingStandardsIgnoreLine
        if ( $saved_code == $postdata['sms_code'] ) {
            $seller_profile['dokan_verification']['info']['phone_status'] = 'verified';
            $seller_profile['dokan_verification']['info']['phone_no'] = $seller_profile['dokan_verification']['info']['phone_no'];
            update_user_meta( $current_user, 'dokan_profile_settings', $seller_profile );

            $resp = array(
                'success' => true,
                'message' => 'Your Phone is verified now',
            );
            wp_send_json_success( $resp );
        } else {
            $resp = array(
                'success' => false,
                'message' => 'Your SMS code is not valid, please try again',
            );
            wp_send_json_success( $resp );
        }
    }

    /*
     * Custom dir for vendor uploaded file
     *
     * @since 2.9.0
     *
     * @return array
     *
     */
    public function dokan_customize_upload_dir( $upload ) {
        global $wp;

        if ( ! isset( $_SERVER['HTTP_REFERER'] ) ) {
            return $upload;
        }

        // @codingStandardsIgnoreLine
        if ( strpos( $_SERVER['HTTP_REFERER'], 'settings/verification' ) != false ) {

            remove_filter( 'upload_dir', array( $this, 'dokan_customize_upload_dir' ), 10 );
            // apply security patch
            $this->disallow_direct_access();
            add_filter( 'upload_dir', array( $this, 'dokan_customize_upload_dir' ), 10 );

            $user_id = get_current_user_id();
            $user = get_user_by( 'id', $user_id );

            $vendor_verification_hash = get_user_meta( $user_id, 'dokan_vendor_verification_folder_hash', true );

            if ( empty( $vendor_verification_hash ) ) {
                $vendor_verification_hash = $this->generate_random_string();
                update_user_meta( $user_id, 'dokan_vendor_verification_folder_hash', $vendor_verification_hash );
            }

            $dirname = $user_id . '-' . $user->user_login . '/' . $vendor_verification_hash;
            $upload['subdir'] = '/verification/' . $dirname;
            $upload['path']   = $upload['basedir'] . $upload['subdir'];
            $upload['url']    = $upload['baseurl'] . $upload['subdir'];
        }

        return $upload;
    }

    /**
     * @since 3.1.3
     * Creates .htaccess & index.html files if not exists that prevent direct folder access
     */
    public function disallow_direct_access() {
        $uploads_dir   = trailingslashit( wp_upload_dir()['basedir'] ) . 'verification';
        $file_htaccess = $uploads_dir . '/.htaccess';
        $file_html     = $uploads_dir . '/index.html';
        $rule = <<<EOD
Options -Indexes
deny from all
<FilesMatch '\.(jpg|jpeg|png|gif|pdf|doc|docx|odt)$'>
    Order Allow,Deny
    Allow from all
</FilesMatch>
EOD;
        if ( get_transient( 'dokan_vendor_verification_access_check' ) ) {
            return;
        }

        if ( ! is_dir( $uploads_dir ) ) {
            wp_mkdir_p( $uploads_dir );
        }

        global $wp_filesystem;

        // protect if the the global filesystem isn't setup yet
        if ( is_null( $wp_filesystem ) ) { // phpcs:ignore
            require_once ( ABSPATH . '/wp-admin/includes/file.php' );// phpcs:ignore
            WP_Filesystem();
        }

        // phpcs:ignore
        if ( ( file_exists( $file_htaccess ) && $wp_filesystem->get_contents( $file_htaccess ) !== $rule ) || ! file_exists( $file_htaccess ) )  {

            $ret = $wp_filesystem->put_contents(
                $file_htaccess,
                '',
                FS_CHMOD_FILE
            ); // returns a status of success or failure

            $wp_filesystem->put_contents(
                $file_htaccess,
                $rule,
                FS_CHMOD_FILE
            ); // returns a status of success or failure

            $wp_filesystem->put_contents(
                $file_html,
                '',
                FS_CHMOD_FILE
            ); // returns a status of success or failure

            if ( $ret ) {
                // Sets transient for 7 days
                set_transient( 'dokan_vendor_verification_access_check', true, DAY_IN_SECONDS * 7 );
            }
        }
    }

    /**
     * @param int $length
     *
     * @return string
     * @since 3.1.3
     * Generates a random string
     */
    public function generate_random_string( $length = 20 ) {
        $characters        = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $characters_length = strlen( $characters );
        $random_string     = '';
        for ( $i = 0; $i < $length; $i ++ ) {
            $random_string .= $characters[ wp_rand( 0, $characters_length - 1 ) ];
        }

        return $random_string;
    }

    /*
     * Insert Verification page Company fields into Verify info via AJAX
     *
     * @since 1.0.0
     *
     * @return Ajax Success/fail
     */
    public function dokan_update_verify_info_insert_company() {
        if ( ! isset( $_POST['dokan_verify_action_company_form_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['dokan_verify_action_company_form_nonce'] ), 'dokan_verify_action_company_form' ) ) {
            wp_send_json_error( __( 'Are you cheating?', 'dokan' ) );
        }

        $current_user   = dokan_get_current_user_id();

        if ( ! dokan_is_user_seller( $current_user ) ) {
            wp_send_json_error( __( 'Are you cheating?', 'dokan' ) );
        }

        $seller_profile = dokan_get_store_info( $current_user );

        $msg = __( 'Please upload minimum one document.', 'dokan' );

        if ( ! isset( $_POST['vendor_verification_files_ids'] ) || ! is_array( $_POST['vendor_verification_files_ids'] ) || count( $_POST['vendor_verification_files_ids'] ) < 1 ) {
            wp_send_json_error( $msg );
        }

        $seller_profile['company_verification_files'] = wp_unslash( $_POST['vendor_verification_files_ids'] );

        $seller_profile['dokan_verification']['info']['company_v_status'] = 'pending';

        update_user_meta( $current_user, 'dokan_profile_settings', $seller_profile );

        do_action( 'dokan_company_verification_submitted', $current_user, $seller_profile );

        $msg = __( 'Your company verification request is sent and pending approval', 'dokan' );

        dokan_verification_request_submit_email();
        wp_send_json_success( $msg );
    }

    /*
     * Clears Verify Info value for Company verification via AJAX
     *
     * @since 1.0.0
     *
     * @return String Ajax string message.
     */
    public function dokan_company_verification_cancel() {
        $user_id        = get_current_user_id();
        $seller_profile = dokan_get_store_info( $user_id );

        unset( $seller_profile['dokan_verification']['info']['company_v_status'] );
        //update user meta pending here
        update_user_meta( $user_id, 'dokan_profile_settings', $seller_profile );

        do_action( 'dokan_company_verification_cancelled', $user_id, $seller_profile );

        $msg = __( 'Your company verification request is cancelled', 'dokan' );

        wp_send_json_success( $msg );
    }

    /**
     * Added vendor residence proof template for vendor verification.
     *
     * @since 3.5.5
     *
     * @param array $address
     * @param array $seller_address_fields
     *
     * @return void
     */
    public function added_vendor_residence_proof_template( $address, $seller_address_fields ) {
        global $wp;

        // If this page is verification settings page then show address proof template.
        if ( isset( $wp->query_vars['settings'] ) && $wp->query_vars['settings'] === 'verification' ) {
            dokan_get_template_part(
                'vendor-verification/verification-address', '', [
                    'is_vendor_verification' => true,
                    'address'                => $address,
                    'seller_address_fields'  => $seller_address_fields,
                    'btn_text'               => __( 'Upload Proof', 'dokan' ),
                    'label'                  => __( 'Proof of Residence', 'dokan' ),
                    'required_text'          => __( 'Vendor residence proof is required!', 'dokan' ),
                ]
            );
        }
    }
}
