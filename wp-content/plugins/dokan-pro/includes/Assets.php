<?php

namespace WeDevs\DokanPro;

/**
 * Scripts and Styles Class
 */
class Assets {

    private $script_version;

    private $suffix;

    public function __construct() {
        $this->script_version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : DOKAN_PRO_PLUGIN_VERSION;

        // Use minified libraries if SCRIPT_DEBUG is turned off
        $this->suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

        if ( is_admin() ) {
            add_action( 'admin_enqueue_scripts', [ $this, 'register' ], 5 );
            add_action( 'dokan-vue-admin-scripts', [ $this, 'enqueue_admin_scripts' ] );
            add_filter( 'dokan_admin_localize_script', [ $this, 'add_localized_data' ], 5 );
        } else {
            add_action( 'wp_enqueue_scripts', [ $this, 'register' ], 5 );
            add_action( 'dokan_enqueue_scripts', [ $this, 'enqueue_frontend_scripts' ], 5 );
            add_filter( 'dokan_localized_args', [ $this, 'add_i18_localized_data' ], 5 );
        }
    }

    /**
     * Enqueue admin scripts
     *
     * @return void
     */
    public function enqueue_admin_scripts() {
        global $wp_version;

        wp_enqueue_style( 'dokan-pro-vue-admin' );
        wp_enqueue_style( 'woocommerce_select2', WC()->plugin_url() . '/assets/css/select2.css', [], WC_VERSION );
        wp_enqueue_script( 'dokan-pro-vue-admin' );

        if ( version_compare( $wp_version, '5.3', '<' ) ) {
            wp_enqueue_style( 'dokan-pro-wp-version-before-5-3' );
        }
    }

    /**
     * This method will enqueue dokan pro localize data
     *
     * @since 3.1.1
     * @param array $data
     * @return array
     */
    public function add_localized_data( $data ) {
        $data['dokan_pro_i18n'] = array( 'dokan' => dokan_get_jed_locale_data( 'dokan', DOKAN_PRO_DIR . '/languages/' ) );
        $data['current_plan']   = dokan_pro()->get_plan();
        $data['active_modules'] = dokan_pro()->module->get_active_modules();
        return $data;
    }

    /**
     * Enqueue forntend scripts
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function enqueue_frontend_scripts() {
        global $wp;

        if ( isset( $wp->query_vars['settings'] ) && $wp->query_vars['settings'] === 'shipping' ) {
            wp_enqueue_style( 'dokan-vue-bootstrap' );
            wp_enqueue_style( 'dokan-pro-vue-frontend-shipping' );
            wp_enqueue_script( 'dokan-pro-vue-frontend-shipping' );

            $continents        = WC()->countries->get_continents();
            $allowed_countries = WC()->countries->get_allowed_countries();
            $continents_data   = array();

            if ( $continents && is_array( $continents ) ) {
                foreach ( $continents as $continent => $countries ) {
                    if ( isset( $countries['countries'] ) && isset( $countries['name'] ) && is_array( $countries['countries'] ) ) {
                        $continents_data[ $continent ]['name'] = $countries['name'];
                        $countries_data = array();

                        foreach ( $countries['countries'] as $country ) {
                            if ( array_key_exists( $country, $allowed_countries ) ) {
                                $countries_data[] = $country;
                            }
                        }
                        $continents_data[ $continent ]['countries'] = $countries_data;
                    }
                }
            }

            $localize_array = array(
                'nonce'             => wp_create_nonce( 'dokan_shipping_nonce' ),
                'allowed_countries' => WC()->countries->get_allowed_countries(),
                'continents'        => ! empty( $continents_data ) ? $continents_data : $continents,
                'states'            => WC()->countries->get_states(),
                'shipping_class'    => WC()->shipping->get_shipping_classes(),
                'i18n'              => array( 'dokan' => dokan_get_jed_locale_data( 'dokan' ) ),
                'processing_time'   => dokan_get_shipping_processing_times(),
                'dashboardUrl'      => dokan_get_navigation_url(),
            );

            wp_localize_script( 'dokan-pro-vue-frontend-shipping', 'dokanShipping', $localize_array );
        }

        // Load dokan store times assets in store page.
        if ( isset( $wp->query_vars['settings'] ) && $wp->query_vars['settings'] === 'store' ) {
            wp_enqueue_style( 'dokan-pro-store-times' );
        }
    }

    /**
     * Register our app scripts and styles
     *
     * @return void
     */
    public function register() {
        $this->register_scripts( $this->get_scripts() );
        $this->register_styles( $this->get_styles() );
    }

    /**
     * Register scripts
     *
     * @param  array $scripts
     *
     * @return void
     */
    private function register_scripts( $scripts ) {
        foreach ( $scripts as $handle => $script ) {
            $deps      = isset( $script['deps'] ) ? $script['deps'] : false;
            $in_footer = isset( $script['in_footer'] ) ? $script['in_footer'] : false;
            $version   = isset( $script['version'] ) ? $script['version'] : DOKAN_PRO_PLUGIN_VERSION;

            wp_register_script( $handle, $script['src'], $deps, $version, $in_footer );
        }
    }

    /**
     * Register styles
     *
     * @param  array $styles
     *
     * @return void
     */
    public function register_styles( $styles ) {
        foreach ( $styles as $handle => $style ) {
            $deps    = isset( $style['deps'] ) ? $style['deps'] : false;
            $version = isset( $style['version'] ) ? $style['version'] : DOKAN_PRO_PLUGIN_VERSION;

            wp_register_style( $handle, $style['src'], $deps, $version );
        }
    }

    /**
     * Get all registered scripts
     *
     * @return array
     */
    public function get_scripts() {
        $scripts = [
            'dokan-pro-vue-admin' => [
                'src'       => DOKAN_PRO_PLUGIN_ASSEST . '/js/vue-pro-admin' . $this->suffix . '.js',
                'deps'      => [ 'jquery', 'dokan-vue-vendor', 'dokan-vue-bootstrap', 'selectWoo' ],
                'version'   => $this->script_version,
                'in_footer' => true,
            ],

            'dokan-pro-vue-frontend-shipping' => [
                'src'       => DOKAN_PRO_PLUGIN_ASSEST . '/js/vue-pro-frontend-shipping' . $this->suffix . '.js',
                'deps'      => [ 'jquery', 'dokan-vue-vendor', 'dokan-vue-bootstrap', 'underscore' ],
                'version'   => $this->script_version,
                'in_footer' => true,
            ],

            'dokan-magnific-popup' => [
                'src'       => DOKAN_PRO_PLUGIN_ASSEST . '/vendor/magnific/jquery.magnific-popup.min.js',
                'deps'      => [ 'jquery' ],
            ],
        ];

        /**
         * To allow add/remove js that registers vue these filter
         *
         * @since 3.3.9
         *
         * @args array $scripts
         */
        return apply_filters( 'dokan_pro_scripts', $scripts );
    }

    /**
     * Get registered styles
     *
     * @return array
     */
    public function get_styles() {
        $styles = [
            'dokan-pro-vue-admin' => [
                'src'     => DOKAN_PRO_PLUGIN_ASSEST . '/css/vue-pro-admin' . $this->suffix . '.css',
                'version' => $this->script_version,
            ],
            'dokan-pro-vue-frontend-shipping' => [
                'src'     => DOKAN_PRO_PLUGIN_ASSEST . '/css/vue-pro-frontend-shipping' . $this->suffix . '.css',
                'version' => $this->script_version,
            ],
            'dokan-pro-store-times' => [
                'src'     => DOKAN_PRO_PLUGIN_ASSEST . '/css/dokan-pro-store-times.css',
                'version' => $this->script_version,
            ],
            'dokan-pro-wp-version-before-5-3' => [
                'src'     => DOKAN_PRO_PLUGIN_ASSEST . '/css/wp-version-before-5-3.css',
                'version' => $this->script_version,
            ],
            'dokan-magnific-popup' => [
                'src'     => DOKAN_PRO_PLUGIN_ASSEST . '/vendor/magnific/magnific-popup.css',
            ],
        ];

        return $styles;
    }

    /**
     * Register i18n Scripts
     *
     * @since DOKAN_PRO
     *
     * @param array $default_script
     *
     * @return void
     */
    public function add_i18_localized_data( $default_script ) {
        $localize_script = [
            'i18n_location_name'             => __( 'Please provide a location name!', 'dokan' ),
            'i18n_location_state'            => __( 'Please provide', 'dokan' ),
            'i18n_country_name'              => __( 'Please provide a country!', 'dokan' ),
            'i18n_invalid'                   => __( 'Failed! Somthing went wrong', 'dokan' ),
            'i18n_chat_message'              => __( 'Facebook SDK is not found, or blocked by the browser. Can not initialize the chat.', 'dokan' ),
            'i18n_sms_code'                  => __( 'Insert SMS code', 'dokan' ),
            'i18n_gravater'                  => __( 'Upload a Photo', 'dokan'),
            'i18n_phone_number'              => __( 'Insert Phone No.', 'dokan' ),
        ];

        $default_script = array_merge( $default_script , $localize_script );
        return $default_script;
    }
}
