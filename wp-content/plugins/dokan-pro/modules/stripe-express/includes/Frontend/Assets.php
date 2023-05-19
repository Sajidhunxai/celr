<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Frontend;

defined( 'ABSPATH' ) || exit; // Exit if called directly

/**
 * Class for handling frontend assets
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Frontend
 */
class Assets {

    /**
     * Class constructor
     *
     * @since 3.6.1
     */
    public function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'register_scripts' ] );
    }

    /**
     * Registers necessary scripts
     *
     * @since 3.6.1
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
            'dokan-stripe-express-cdn',
            'https://js.stripe.com/v3',
            [],
            $version,
            true
        );

        wp_register_script(
            'dokan-stripe-express-payment-request',
            DOKAN_STRIPE_EXPRESS_ASSETS . "js/payment-request{$suffix}.js",
            [ 'jquery', 'dokan-stripe-express-cdn', 'dokan-sweetalert2' ],
            $version,
            true
        );

        wp_register_script(
            'dokan-stripe-express-checkout',
            DOKAN_STRIPE_EXPRESS_ASSETS . "js/checkout{$suffix}.js",
            [ 'jquery', 'dokan-stripe-express-cdn', 'dokan-sweetalert2' ],
            $version,
            true
        );

        wp_register_style(
            'dokan-stripe-express-checkout',
            DOKAN_STRIPE_EXPRESS_ASSETS . "css/checkout{$suffix}.css",
            [],
            $version
        );

        wp_register_script(
            'dokan-stripe-express-vendor',
            DOKAN_STRIPE_EXPRESS_ASSETS . "js/vendor{$suffix}.js",
            [ 'jquery', 'dokan-sweetalert2' ],
            $version,
            true
        );

        wp_register_style(
            'dokan-stripe-express-vendor',
            DOKAN_STRIPE_EXPRESS_ASSETS . "css/vendor{$suffix}.css",
            [],
            $version
        );

        wp_localize_script(
            'dokan-stripe-express-vendor',
            'dokanStripeExpressData',
            [
                'ajaxurl' => admin_url( 'admin-ajax.php' ),
                'nonce'   => wp_create_nonce( 'dokan_stripe_express_vendor_payment_settings' ),
                'i18n'    => [
                    'country_select_error' => __( 'Please select your country to proceed.', 'dokan' ),
                    'cancel_onboarding'    => [
                        'is_setup_wizard'   => isset( $_GET['page'] ) && 'dokan-seller-setup' === sanitize_text_field( wp_unslash( $_GET['page'] ) ), // phpcs:ignore WordPress.Security.NonceVerification.Recommended
                        'title'             => __( 'Cancel Onboarding?', 'dokan' ),
                        'text'              => __( 'Are you sure you want to cancel the current onboarding process? Note that, this process is permanent and you can\'t undo this action. However, you\'ll be able to start the onboarding process again.', 'dokan' ),
                        'confirmButtonText' => __( 'Yes, cancel it!', 'dokan' ),
                        'cancelButtonText'  => __( 'No, keep it!', 'dokan' ),
                        'successTitle'      => __( 'Success', 'dokan' ),
                        'successMessage'    => __( 'Onboarding process has been cancelled successfully.', 'dokan' ),
                        'errorMessage'      => __( 'Something went wrong! Please try again.', 'dokan' ),
                    ],
                ],
            ]
        );
    }
}
