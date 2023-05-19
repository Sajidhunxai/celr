<?php

namespace WeDevs\DokanPro\Modules\MangoPay\PaymentMethod;

use WeDevs\DokanPro\Modules\MangoPay\Support\Config;

/**
 * Class for managing Mangopay payment method
 *
 * @since 3.5.0
 */
class Manager {

    /**
     * Class constructor
     *
     * @since 3.5.0
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * Registers necessary hooks
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function hooks() {
        // Registers Mangopay payment gateway
        add_filter( 'woocommerce_payment_gateways', array( $this, 'register_gateway' ) );
        // Decrypts api key to original form before showing on frontend
        add_filter( 'option_woocommerce_dokan_mangopay_settings', array( $this, 'decrypt_passphrase' ), 10, 1 );
    }

    /**
     * Registers payment gateway
     *
     * @since 3.5.0
     *
     * @param array $gateways
     *
     * @return array
     */
    public function register_gateway( $gateways ) {
        $gateways[] = '\WeDevs\DokanPro\Modules\MangoPay\PaymentMethod\Gateway';

        return $gateways;
    }

    /**
     * Decrypts api passphrase before showing on frontend.
     *
     * @since 3.5.0
     *
     * @param array $settings
     *
     * @return array
     */
    public function decrypt_passphrase( $settings ) {
        $config = Config::get_instance();

        if ( ! empty( $settings['sandbox_api_key'] ) ) {
            $settings['sandbox_api_key'] = $config->decrypt( $settings['sandbox_api_key'] );
        }

        if ( ! empty( $settings['api_key'] ) ) {
            $settings['api_key'] = $config->decrypt( $settings['api_key'] );
        }

        return $settings;
    }
}
