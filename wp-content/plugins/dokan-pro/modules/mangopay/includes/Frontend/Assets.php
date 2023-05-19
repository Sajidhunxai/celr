<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Frontend;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;

/**
 * Class for handling frontend assets
 *
 * @since 3.5.0
 */
class Assets {

    /**
     * Class constructor
     *
     * @since 3.5.0
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
    }

    /**
     * Registers necessary scripts
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function register_scripts() {
        $suffix  = '.min';
        $version = DOKAN_PRO_PLUGIN_VERSION;
        if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
            $suffix  = '';
            $version = time();
        }

        wp_register_script(
            'dokan-mangopay-kit',
            DOKAN_MANGOPAY_ASSETS . "vendor/mangopay-kit{$suffix}.js",
            array( 'jquery' ),
            $version,
            true
        );

        wp_register_script(
            'dokan-mangopay-checkout',
            DOKAN_MANGOPAY_ASSETS . "js/checkout{$suffix}.js",
            array( 'dokan-mangopay-kit' ),
            $version,
            true
        );

        wp_register_style(
            'dokan-mangopay-checkout',
            DOKAN_MANGOPAY_ASSETS . "css/checkout{$suffix}.css",
            array(),
            $version
        );

        wp_register_script(
            'dokan-mangopay-vendor',
            DOKAN_MANGOPAY_ASSETS . "js/vendor{$suffix}.js",
            array( 'jquery' ),
            $version,
            true
        );

        wp_register_style(
            'dokan-mangopay-vendor',
            DOKAN_MANGOPAY_ASSETS . "css/vendor{$suffix}.css",
            array(),
            $version
        );

        wp_localize_script( 'dokan-mangopay-checkout', 'dokanMangopay', array(
            'regErrors'   => $this->card_registration_errors(),
            'ajaxurl'     => admin_url( 'admin-ajax.php' ),
            'emptyFields' => esc_html__( 'Please fill all the fields' ),
            'nonce'       => wp_create_nonce( 'dokan_mangopay_checkout_nonce' ),
        ) );

        wp_localize_script( 'dokan-mangopay-vendor', 'dokanMangopay', array(
            'ajaxurl'      => admin_url( 'admin-ajax.php' ),
            'signUpFields' => Helper::get_signup_fields( get_current_user_id() ),
            'message'      => [
                'emptyReqFields' => esc_html__( 'Please fill all the *Required fields!' ),
            ],
            'i18n'         => [
                'stateSelector' => esc_html__( 'Select a state...', 'dokan' ),
                'optional'      => esc_html__( 'Optional', 'dokan' ),
                'processing'    => esc_html__( 'Processing', 'dokan' ),
                'makeActive'    => esc_html__( 'Make Active', 'dokan' ),
            ],
        ) );
    }

    /**
     * Translate error messages for localization.
     *
     * @since 3.5.0
     *
     * @return array
     */
    private function card_registration_errors() {
        return apply_filters(
            'dokan_mangopay_card_registration_errors',
            array(
                'base_message' => __( 'Card registration error: ', 'dokan' ),
                '009999'       => __( 'Browser does not support making cross-origin Ajax calls', 'dokan' ),
                '001596'       => __( 'An HTTP request was blocked by the User\'s computer (probably due to an antivirus)', 'dokan' ),
                '001597'       => __( 'An HTTP request failed', 'dokan' ),
                '001599'       => __( 'Token processing error', 'dokan' ),
                '101699'       => __( 'Invalid response', 'dokan' ),
                '105204'       => __( 'The CVV is missing or not in the required length/format', 'dokan' ),
                '105203'       => __( 'The expiry date is not valid', 'dokan' ),
                '105202'       => __( 'The card number is not in a valid format', 'dokan' ),
            )
        );
    }
}
