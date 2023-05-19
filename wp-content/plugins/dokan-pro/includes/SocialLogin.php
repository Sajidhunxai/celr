<?php

namespace WeDevs\DokanPro;

use Hybridauth\Exception\Exception;
use Hybridauth\Hybridauth;
use WeDevs\DokanPro\Storage\Session;

class SocialLogin {

    private $callback;
    private $config;

    /**
     * Load automatically when class instantiated
     *
     * @since 2.4
     *
     * @uses actions|filter hooks
     */
    public function __construct() {
        $this->callback = dokan_get_page_url( 'myaccount', 'woocommerce' );
        $this->init_hooks();
    }

    /**
     * Call actions and hooks
     */
    public function init_hooks() {
        //add settings menu page
        add_filter( 'dokan_settings_sections', array( $this, 'dokan_social_api_settings' ) );
        add_filter( 'dokan_settings_fields', array( $this, 'dokan_social_settings_fields' ) );

        if ( 'on' !== dokan_get_option( 'enabled', 'dokan_social_api' ) ) {
            return;
        }

        //Hybrid auth action
        add_action( 'template_redirect', array( $this, 'monitor_autheticate_requests' ), 1 );

        // add social buttons on registration form and login form
        add_action( 'woocommerce_register_form_end', array( $this, 'render_social_logins' ) );
        add_action( 'woocommerce_login_form_end', array( $this, 'render_social_logins' ) );
        add_action( 'dokan_vendor_reg_form_end', array( $this, 'render_social_logins' ) );
        add_action( 'dokan_vendor_reg_form_end', array( $this, 'enqueue_style' ) );

        //add custom my account end-point
        add_filter( 'dokan_query_var_filter', array( $this, 'register_support_queryvar' ) );
        add_action( 'dokan_load_custom_template', array( $this, 'load_template_from_plugin' ) );

        // load providers config
        $this->config = $this->get_providers_config();
    }

    /**
     * Get configuration values for HybridAuth
     *
     * @return array
     */
    private function get_providers_config() {
        $config = [
            'callback' => $this->callback,
            'providers' => [
                'Google' => [
                    'enabled' => false,
                    'keys'    => [
                        'id'     => dokan_get_option( 'google_app_id', 'dokan_social_api' ),
                        'secret' => dokan_get_option( 'google_app_secret', 'dokan_social_api' ),
                    ],
                ],
                'Facebook' => [
                    'enabled'        => false,
                    'trustForwarded' => false,
                    'scope'          => 'email, public_profile',
                    'keys'           => [
                        'id'     => dokan_get_option( 'fb_app_id', 'dokan_social_api' ),
                        'secret' => dokan_get_option( 'fb_app_secret', 'dokan_social_api' ),
                    ],
                ],
                'Twitter' => [
                    'enabled'      => false,
                    'includeEmail' => true,
                    'keys'         => [
                        'key'    => dokan_get_option( 'twitter_app_id', 'dokan_social_api' ),
                        'secret' => dokan_get_option( 'twitter_app_secret', 'dokan_social_api' ),
                    ],
                ],
                'LinkedIn' => [
                    'enabled' => false,
                    'keys'    => [
                        'id' => dokan_get_option( 'linkedin_app_id', 'dokan_social_api' ),
                        'secret' => dokan_get_option( 'linkedin_app_secret', 'dokan_social_api' ),
                    ],
                ],
                'Apple' => [
                    'enabled' => false,
                    'scope'   => 'name email',
                    'keys'    => [
                        'id'          => dokan_get_option( 'apple_service_id', 'dokan_social_api' ),
                        'team_id'     => dokan_get_option( 'apple_team_id', 'dokan_social_api' ),
                        'key_id'      => dokan_get_option( 'apple_key_id', 'dokan_social_api' ),
                        'key_content' => dokan_get_option( 'apple_key_content', 'dokan_social_api' ),
                    ],
                    'verifyTokenSignature' => false,
                    'authorize_url_parameters' => [
                        'response_mode' => 'form_post',
                    ],
                ],
            ],
        ];

        //facebook config from admin
        if ( $config['providers']['Facebook']['keys']['id'] !== '' && $config['providers']['Facebook']['keys']['secret'] !== '' ) {
            $config['providers']['Facebook']['enabled'] = true;
        }

        //google config from admin
        if ( $config['providers']['Google']['keys']['id'] !== '' && $config['providers']['Google']['keys']['secret'] !== '' ) {
            $config['providers']['Google']['enabled'] = true;
        }

        //linkedin config from admin
        if ( $config['providers']['LinkedIn']['keys']['id'] !== '' && $config['providers']['LinkedIn']['keys']['secret'] !== '' ) {
            $config['providers']['LinkedIn']['enabled'] = true;
        }

        //Twitter config from admin
        if ( $config['providers']['Twitter']['keys']['key'] !== '' && $config['providers']['Twitter']['keys']['secret'] !== '' ) {
            $config['providers']['Twitter']['enabled'] = true;
        }

        // apple config from the admin
        if ( $config['providers']['Apple']['keys']['id'] !== '' &&
            $config['providers']['Apple']['keys']['team_id'] !== '' &&
            $config['providers']['Apple']['keys']['key_id'] !== '' &&
            $config['providers']['Apple']['keys']['key_content'] !== ''
        ) {
            $config['providers']['Apple']['enabled'] = true;
        }

        /**
         * Filter the Config array of Hybridauth
         *
         * @since 1.0.0
         *
         * @param array $config
         */
        $config = apply_filters( 'dokan_social_providers_config', $config );

        return $config;
    }

    /**
     * Monitors Url for Hauth Request and process Hauth for authentication
     *
     * @return void
     */
    public function monitor_autheticate_requests() {

        // if not my account page, return early
        if ( ! is_account_page() ) {
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
            $storage = new Session( 'social_login', 5 * 60 );

            /**
             * Hold information about provider when user clicks on Sign In.
             */
            $provider = ! empty( $_GET['vendor_social_reg'] ) ? sanitize_text_field( wp_unslash( $_GET['vendor_social_reg'] ) ) : ''; //phpcs:ignore

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
            $storage->clear();

            if ( ! $user_profile ) {
                wc_add_notice( __( 'Something went wrong! please try again', 'dokan' ), 'error' );
                wp_safe_redirect( $this->callback );
            }

            if ( empty( $user_profile->email ) ) {
                wc_add_notice( __( 'User email is not found. Try again.', 'dokan' ), 'error' );
                wp_safe_redirect( $this->callback );
            }

            $wp_user = get_user_by( 'email', $user_profile->email );

            if ( ! $wp_user ) {
                try {
                    $this->register_new_user( $user_profile );
                } catch ( \Exception $exception ) {
                    wc_add_notice( $exception->getMessage(), 'error' );
                    wp_safe_redirect( $this->callback );
                }
            } else {
                $this->login_user( $wp_user );
            }
        } catch ( Exception $e ) {
            wc_add_notice( $e->getMessage(), 'error' );
        }
    }

    /**
     * Filter admin menu settings section
     *
     * @param array $sections
     *
     * @return array
     */
    public function dokan_social_api_settings( $sections ) {
        $sections[] = [
            'id'                   => 'dokan_social_api',
            'title'                => __( 'Social API', 'dokan' ),
            'icon_url'             => DOKAN_PRO_PLUGIN_ASSEST . '/images/admin-settings-icons/social.svg',
            'description'          => __( 'Configure Social Api', 'dokan' ),
            'document_link'        => 'https://wedevs.com/docs/dokan/settings/dokan-social-login/',
            'settings_title'       => __( 'Social Settings', 'dokan' ),
            'settings_description' => __( 'Define settings to allow vendors to use their social profiles to register or log in to the marketplace.', 'dokan' ),
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
    public function dokan_social_settings_fields( $settings_fields ) {
        $settings_fields['dokan_social_api'] = [
            'section_title'    => [
                'name'        => 'section_title',
                'label'       => __( 'Social API', 'dokan' ),
                'type'        => 'sub_section',
                'description' => __( 'Configure your site social settings and control access to your site.', 'dokan' ),
            ],
            'enabled'          => [
                'name'    => 'enabled',
                'label'   => __( 'Enable Social Login', 'dokan' ),
                'type'    => 'switcher',
                'desc'    => __( 'Enabling this will add Social Icons under registration form to allow users to login or register using Social Profiles.', 'dokan' ),
                'tooltip' => __( 'Check this to allow social login/signup for customers and vendors.', 'dokan' ),
            ],
            'facebook_details' => [
                'name'          => 'facebook_details',
                'type'          => 'social',
                'desc'          => sprintf(
                    /* translators: 1) Opening anchor tag, 2) Closing anchor tag */
                    __( 'Configure your facebook API settings. %1$sGet Help%2$s', 'dokan' ),
                    '<a href="https://wedevs.com/docs/dokan/settings/dokan-social-login/configuring-facebook/" target="_blank">',
                    '</a>'
                ),
                'label'         => __( 'Connect to Facebook', 'dokan' ),
                'icon_url'      => DOKAN_PRO_PLUGIN_ASSEST . '/images/scl-icons/fb.svg',
                'social_desc'   => __( 'You can successfully connect Facebook with your website.', 'dokan' ),
                'fb_app_label'  => [
                    'name'         => 'fb_app_label',
                    'type'         => 'html',
                    'desc'         => sprintf(
                        /* translators: 1) Opening anchor tag, 2) Closing anchor tag */
                        __( 'If you don\'t have one and fill App ID and Secret below. %1$sCreate an App%2$s', 'dokan' ),
                        '<a target="_blank" href="https://developers.facebook.com/apps/">',
                        '</a>'
                    ),
                    'label'        => __( 'Facebook App Settings', 'dokan' ),
                    'social_field' => true,
                ],
                'fb_app_url'    => [
                    'url'          => $this->callback,
                    'name'         => 'fb_app_url',
                    'type'         => 'html',
                    'label'        => __( 'Site URL', 'dokan' ),
                    'tooltip'      => __( 'Your store URL, which will be required in creating the App.', 'dokan' ),
                    'social_field' => true,
                ],
                'fb_app_id'     => [
                    'name'         => 'fb_app_id',
                    'type'         => 'text',
                    'label'        => __( 'App ID', 'dokan' ),
                    'tooltip'      => __( 'You can get it from Facebook Developer platform -> Login -> Select  "Add A New App" -> Collect App ID.', 'dokan' ),
                    'social_field' => true,
                ],
                'fb_app_secret' => [
                    'name'         => 'fb_app_secret',
                    'label'        => __( 'App Secret', 'dokan' ),
                    'type'         => 'text',
                    'tooltip'      => __( 'You can get it from Facebook Developer platform -> Login -> Select  "Add A New App" -> Collect App secret.', 'dokan' ),
                    'social_field' => true,
                ],
            ],
            'twitter_details'  => [
                'name'               => 'twitter_details',
                'type'               => 'social',
                'desc'               => sprintf(
                    /* translators: 1) Opening anchor tag, 2) Closing anchor tag */
                    __( 'Configure your twitter API settings. %1$sGet Help%2$s', 'dokan' ),
                    '<a href="https://wedevs.com/docs/dokan/settings/dokan-social-login/configuring-twitter/" target="_blank">',
                    '</a>'
                ),
                'label'              => __( 'Connect to Twitter', 'dokan' ),
                'icon_url'           => DOKAN_PRO_PLUGIN_ASSEST . '/images/scl-icons/twt.svg',
                'social_desc'        => __( 'You can successfully connect Twitter with your website.', 'dokan' ),
                'twitter_app_label'  => [
                    'name'         => 'twitter_app_label',
                    'type'         => 'html',
                    'desc'         => sprintf(
                        /* translators: 1) Opening anchor tag, 2) Closing anchor tag */
                        __( 'If you don\'t have one and fill Consumer key and Secret below. %1$sCreate an App%2$s', 'dokan' ),
                        '<a target="_blank" href="https://apps.twitter.com/">',
                        '</a>'
                    ),
                    'label'        => __( 'Twitter App Settings', 'dokan' ),
                    'social_field' => true,
                ],
                'twitter_app_url'    => [
                    'url'          => $this->callback,
                    'name'         => 'twitter_app_url',
                    'type'         => 'html',
                    'label'        => __( 'Callback URL', 'dokan' ),
                    'tooltip'      => __( 'Your store URL, which will be required in creating the App.', 'dokan' ),
                    'social_field' => true,
                ],
                'twitter_app_id'     => [
                    'name'         => 'twitter_app_id',
                    'type'         => 'text',
                    'label'        => __( 'Consumer Key', 'dokan' ),
                    'tooltip'      => __( 'You can get it from Twitter Developer platform -> Login -> Select  "Create an App" -> Input URL & integrate Twitter with Dokan. Go to "Keys and Tokens" -> View Keys -> Collect API key and use as Consumer Key.', 'dokan' ),
                    'social_field' => true,
                ],
                'twitter_app_secret' => [
                    'name'         => 'twitter_app_secret',
                    'label'        => __( 'Consumer Secret', 'dokan' ),
                    'type'         => 'text',
                    'tooltip'      => __( 'You can get it from Twitter Developer platform -> Login -> Select  "Create an App" -> Input URL & integrate Twitter with Dokan. Go to "Keys and Tokens" -> View Keys -> Collect API secret and use as Consumer secret.', 'dokan' ),
                    'social_field' => true,
                ],
            ],
            'google_details'   => [
                'name'              => 'google_details',
                'type'              => 'social',
                'desc'              => sprintf(
                    /* translators: 1) Opening anchor tag, 2) Closing anchor tag */
                    __( 'Configure your google API settings. %1$sGet Help%2$s', 'dokan' ),
                    '<a href="https://wedevs.com/docs/dokan/settings/dokan-social-login/configuring-google/" target="_blank">',
                    '</a>'
                ),
                'label'             => __( 'Connect to Google', 'dokan' ),
                'icon_url'          => DOKAN_PRO_PLUGIN_ASSEST . '/images/scl-icons/google.svg',
                'social_desc'       => __( 'You can successfully connect to your Google account with your website.', 'dokan' ),
                'google_app_label'  => [
                    'name'         => 'google_app_label',
                    'type'         => 'html',
                    'desc'         => sprintf(
                        /* translators: 1) Opening anchor tag, 2) Closing anchor tag */
                        __( 'If you don\'t have one and fill Client ID and Secret below. %1$sCreate an App%2$s', 'dokan' ),
                        '<a target="_blank" href="https://console.developers.google.com/project">',
                        '</a>'
                    ),
                    'label'        => __( 'Google App Settings', 'dokan' ),
                    'social_field' => true,
                ],
                'google_app_url'    => [
                    'url'          => $this->callback,
                    'name'         => 'google_app_url',
                    'type'         => 'html',
                    'label'        => __( 'Redirect URL', 'dokan' ),
                    'tooltip'      => __( 'Your store URL, which will be required in syncing with Google API.', 'dokan' ),
                    'social_field' => true,
                ],
                'google_app_id'     => [
                    'name'         => 'google_app_id',
                    'type'         => 'text',
                    'label'        => __( 'Client ID', 'dokan' ),
                    'tooltip'      => __( 'You can get it from Google Console Platform -> Google+API -> Enable -> Manage -> Credentials -> Create Credentials -> OAuth client ID -> Web Application -> Fill in the information & click Create. A pop up will show "Client ID".', 'dokan' ),
                    'social_field' => true,
                ],
                'google_app_secret' => [
                    'name'         => 'google_app_secret',
                    'label'        => __( 'Client Secret', 'dokan' ),
                    'type'         => 'text',
                    'tooltip'      => __( 'You can get it from Google Console Platform -> Google+API -> Enable -> Manage -> Credentials -> Create Credentials -> OAuth client ID -> Web Application -> Fill in the information & click Create. A pop up will show "Client Credentials".', 'dokan' ),
                    'social_field' => true,
                ],
            ],
            'linkedin_details' => [
                'name'                => 'linkedin_details',
                'type'                => 'social',
                'desc'                => sprintf(
                    /* translators: 1) Opening anchor tag, 2) Closing anchor tag */
                    __( 'Configure your linkedin API settings. %1$sGet Help%2$s', 'dokan' ),
                    '<a href="https://wedevs.com/docs/dokan/settings/dokan-social-login/configuring-linkedin/" target="_blank">',
                    '</a>'
                ),
                'label'               => __( 'Connect to Linkedin', 'dokan' ),
                'icon_url'            => DOKAN_PRO_PLUGIN_ASSEST . '/images/scl-icons/linkedin.svg',
                'social_desc'         => __( 'You can successfully connect LinkedIn with your website.', 'dokan' ),
                'linkedin_app_label'  => [
                    'name'         => 'linkedin_app_label',
                    'type'         => 'html',
                    'desc'         => sprintf(
                        /* translators: 1) Opening anchor tag, 2) Closing anchor tag */
                        __( 'If you don\'t have one and fill Client ID and Secret below. %1$sCreate an App%2$s', 'dokan' ),
                        '<a target="_blank" href="https://www.linkedin.com/developer/apps">',
                        '</a>'
                    ),
                    'label'        => __( 'Linkedin App Settings', 'dokan' ),
                    'social_field' => true,
                ],
                'linkedin_app_url'    => [
                    'url'          => $this->callback,
                    'name'         => 'linkedin_app_url',
                    'type'         => 'html',
                    'label'        => __( 'Redirect URL', 'dokan' ),
                    'tooltip'      => __( 'Your store URL, which will be required in creating the App.', 'dokan' ),
                    'social_field' => true,
                ],
                'linkedin_app_id'     => [
                    'name'         => 'linkedin_app_id',
                    'type'         => 'text',
                    'label'        => __( 'Client ID', 'dokan' ),
                    'tooltip'      => __( 'You can get it from LinkedIn Developers platform -> Create an App -> Fill necessary info -> Click "Create app" -> "Auth" section -> Collect Client ID.', 'dokan' ),
                    'social_field' => true,
                ],
                'linkedin_app_secret' => [
                    'name'         => 'linkedin_app_secret',
                    'label'        => __( 'Client Secret', 'dokan' ),
                    'type'         => 'text',
                    'tooltip'      => __( 'You can get it from LinkedIn Developers platform -> Create an App -> Fill necessary info -> Click "Create app" -> "Auth" section -> Collect Client Secret.', 'dokan' ),
                    'social_field' => true,
                ],
            ],
            'apple_details'    => [
                'name'               => 'apple_details',
                'desc'               => sprintf(
                    /* translators: 1) Opening anchor tag, 2) Closing anchor tag */
                    __( 'Configure your apple API settings. %1$sGet Help%2$s', 'dokan' ),
                    '<a href="https://wedevs.com/docs/dokan/settings/dokan-social-login/configuring-apple/">',
                    '</a>'
                ),
                'type'               => 'social',
                'label'              => __( 'Connect to Apple', 'dokan' ),
                'icon_url'           => DOKAN_PRO_PLUGIN_ASSEST . '/images/scl-icons/apple.svg',
                'social_desc'        => __( 'You can successfully connect your Apple account with your website.', 'dokan' ),
                'apple_app_label'    => [
                    'name'         => 'apple_app_label',
                    'type'         => 'html',
                    'desc'         => '<a href="https://wedevs.com/docs/dokan/settings/dokan-social-login/configuring-apple/" target="_blank">' . __( 'Get Help', 'dokan' ) . '</a>',
                    'desc'         => sprintf(
                        /* translators: 1) Opening anchor tag, 2) Closing anchor tag */
                        __( 'You can successfully connect to your Apple account and log in from here. %1$sCreate an App%2$s', 'dokan' ),
                        '<a target="_blank" href="https://appleid.apple.com/">',
                        '</a>'
                    ),
                    'label'        => __( 'Apple App Settings', 'dokan' ),
                    'social_field' => true,
                ],
                'apple_redirect_url' => [
                    'url'          => $this->callback,
                    'name'         => 'apple_redirect_url',
                    'type'         => 'html',
                    'label'        => __( 'Redirect URL', 'dokan' ),
                    'tooltip'      => __( 'Your store URL, which will be required in creating the app.', 'dokan' ),
                    'social_field' => true,
                ],
                'apple_service_id'   => [
                    'name'         => 'apple_service_id',
                    'type'         => 'text',
                    'label'        => __( 'Apple Service ID', 'dokan' ),
                    'tooltip'      => __( 'You can get it from Apple Developer platform -> login -> Certificates, IDs & Profiles -> Indentifiers -> Service IDs (drop down) -> Register for Service ID -> Collect Service ID.', 'dokan' ),
                    'social_field' => true,
                ],
                'apple_team_id'      => [
                    'name'         => 'apple_team_id',
                    'type'         => 'text',
                    'label'        => __( 'Apple Team ID', 'dokan' ),
                    'tooltip'      => __( 'You can get it from Apple Developer platform -> login -> Membership ->  Collect Team ID.', 'dokan' ),
                    'social_field' => true,
                ],
                'apple_key_id'       => [
                    'name'         => 'apple_key_id',
                    'type'         => 'text',
                    'label'        => __( 'Apple Key ID', 'dokan' ),
                    'tooltip'      => __( 'You can get it from Apple Developer platform -> login -> Certificates, IDs & Profiles -> Keys -> Click " + " -> Register for new Key -> Download "Apple Key Content" -> Collect Key ID.', 'dokan' ),
                    'social_field' => true,
                ],
                'apple_key_content'  => [
                    'name'         => 'apple_key_content',
                    'label'        => __( 'Apple Key Content (including BEGIN and END lines)', 'dokan' ),
                    'type'         => 'textarea',
                    'tooltip'      => __( 'You can get it from Apple Developer platform -> login -> Certificates, IDs & Profiles -> Keys -> Click " + " -> Register for new Key -> Download "Apple Key Content" -> Collect Key Content.', 'dokan' ),
                    'social_field' => true,
                ],
            ],
        ];

        return $settings_fields;
    }

    /**
     * Register dokan query vars
     *
     * @since 1.0
     *
     * @param array $vars
     *
     * @return array new $vars
     */
    public function register_support_queryvar( $vars ) {
        $vars[] = 'social-register';
        $vars[] = 'dokan-registration';

        return $vars;
    }

    /**
     * Register page templates
     *
     * @since 1.0
     *
     * @param array $query_vars
     *
     * @return array $query_vars
     */
    public function load_template_from_plugin( $query_vars ) {
        if ( isset( $query_vars['dokan-registration'] ) ) {
            $template = DOKAN_PRO_DIR . '/templates/global/social-register.php';
            include $template;
        }
    }

    /**
     * Render social login icons
     *
     * @return void
     */
    public function render_social_logins() {
        $configured_providers = [];

        if ( ! isset( $this->config['providers'] ) ) {
            return $configured_providers;
        }

        foreach ( $this->config['providers'] as $provider_name => $provider_settings ) {
            if ( true === $provider_settings['enabled'] ) {
                $configured_providers[] = strtolower( $provider_name );
            }
        }

        /**
         * Filter the list of Providers connect links to display
         *
         * @since 1.0.0
         *
         * @param array $providers
         */
        $providers = apply_filters( 'dokan_social_provider_list', $configured_providers );

        $data = array(
            'base_url'  => $this->callback,
            'providers' => $providers,
            'pro'       => true,
        );

        dokan_get_template_part( 'global/social-registration', '', $data );
    }

    /**
     * Register a new user
     *
     * @param object $data
     *
     * @param string $provider
     *
     * @return void
     * @throws Exception
     */
    private function register_new_user( $data ) {
        // @codingStandardsIgnoreStart
        $userdata = array(
            'user_login' => dokan_generate_username( ! empty( $data->displayName ) ? $data->displayName : 'user' ),
            'user_email' => $data->email,
            'user_pass'  => wp_generate_password(),
            'first_name' => ! empty( $data->firstName ) ? $data->firstName : 'name1',
            'last_name'  => ! empty( $data->lastName ) ? $data->lastName : 'name2',
            'role'       => 'customer',
        );
        // @codingStandardsIgnoreEnd

        $user_id = wp_insert_user( $userdata );

        if ( is_wp_error( $user_id ) ) {
            throw new Exception( $user_id->get_error_message() );
        }

        $this->login_user( get_userdata( $user_id ) );
    }

    /**
     * Log in existing users
     *
     * @param WP_User $wp_user
     *
     * return void
     */
    private function login_user( $wp_user ) {
        clean_user_cache( $wp_user->ID );
        wp_clear_auth_cookie();
        wp_set_current_user( $wp_user->ID );

        if ( is_ssl() === true ) {
            wp_set_auth_cookie( $wp_user->ID, true, true );
        } else {
            wp_set_auth_cookie( $wp_user->ID, true, false );
        }

        update_user_caches( $wp_user );
        wp_safe_redirect( dokan_get_page_url( 'myaccount', 'woocommerce' ) );
        exit;
    }

    /**
     * Enqueue social style on vendor registration page created via [dokan-vendor-registration] shortcode
     *
     * @since 2.9.13
     *
     * @return void
     */
    public function enqueue_style() {
        wp_enqueue_style( 'dokan-social-style' );
        wp_enqueue_style( 'dokan-social-theme-flat' );
    }
}
