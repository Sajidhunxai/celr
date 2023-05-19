<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Support;

defined( 'ABSPATH' ) || exit;

use Stripe\Stripe;
use Stripe\StripeClient;
use WeDevs\Dokan\Traits\ChainableContainer;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Settings;

/**
 * Configuration handler class
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Support
 */
class Config {

    use ChainableContainer;

    /**
     * Determines the operation mode
     *
     * @since 3.6.1
     *
     * @var boolean
     */
    private $live_mode = false;

    /**
     * Determines if gateway is enabled.
     *
     * @since 3.6.1
     *
     * @var boolean
     */
    private $is_enabled = false;

    /**
     * Determines if configuration is loaded
     *
     * @since 3.6.1
     *
     * @var boolean
     */
    private $api_configured = false;

    /**
     * Publishable key for Stripe account
     *
     * @since 3.6.1
     *
     * @var string
     */
    private $publishable_key;

    /**
     * Secret key for Stripe account
     *
     * @since 3.6.1
     *
     * @var string
     */
    private $secret_key;

    /**
     * Holds gateway option data
     *
     * @since 3.6.1
     *
     * @var array
     */
    private $settings;

    /**
     * API configuration error.
     *
     * @since 3.6.1
     *
     * @var string
     */
    private $api_error = '';

    /**
     * App name for Stripe.
     *
     * @since 3.7.8
     *
     * @var string
     */
    private $app_name = 'Dokan Stripe Express';

    /**
     * App URL for Stripe.
     *
     * @since 3.7.8
     *
     * @var string
     */
    private $app_url = 'https://wedevs.com/dokan/modules/stripe-express/';

    /**
     * The reference to Singleton instance of this class
     *
     * @since 3.6.1
     *
     * @var object
     */
    private static $instance = null;

    /**
     * Private constructor to prevent creating a new instance of the
     * Singleton via the `new` operator from outside of this class.
     *
     * @since 3.6.1
     *
     * @return void
     */
    private function __construct() {
        $this->set_settings();
        $this->init();
    }

    /**
     * Retrieves the singletone instance of the class.
     *
     * @since 3.6.1
     *
     * @return self
     */
    public static function instance() {
        if ( null === static::$instance ) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Sets the settings options
     *
     * @since 3.6.1
     *
     * @return void
     */
    private function set_settings() {
        $this->settings = Settings::get();

        if ( empty( $this->settings ) ) {
            $this->settings = [
                'testmode'             => 'yes',
                'enabled'              => 'no',
                'test_secret_key'      => '',
                'test_publishable_key' => '',
                'test_webhook_key'     => '',
                'secret_key'           => '',
                'publishable_key'      => '',
                'webhook_key'          => '',
            ];
        }
    }

    /**
     * Initializes the configuration
     *
     * @since 3.6.1
     *
     * @return void
     */
    private function init() {
        if ( empty( $this->settings['testmode'] ) || 'yes' !== $this->settings['testmode'] ) {
            $this->live_mode = true;
        }

        $this->is_enabled      = 'yes' === $this->settings['enabled'];
        $this->publishable_key = trim( $this->live_mode ? $this->settings['publishable_key'] : $this->settings['test_publishable_key'] );
        $this->secret_key      = trim( $this->live_mode ? $this->settings['secret_key'] : $this->settings['test_secret_key'] );

        if (
            $this->is_enabled &&
            ! empty( $this->publishable_key ) &&
            ! empty( $this->secret_key ) &&
            $this->verify_api_keys()
        ) {
            try {
                $this->set_api();
                $this->api_configured = true;
                $this->api_error      = '';
                $this->set_controllers();
            } catch ( \Exception $e ) {
                $this->api_configured = false;
                $this->api_error      = $e->getMessage();
            }
        }
    }

    /**
     * Sets API configuration for Stripe
     *
     * @since 3.6.1
     *
     * @return void
     */
    private function set_api() {
        Stripe::setApiVersion( Helper::get_api_version() );
        Stripe::setAppInfo( $this->app_name, DOKAN_PRO_PLUGIN_VERSION, $this->app_url );

        if ( ! $this->is_live_mode() ) {
            Stripe::setVerifySslCerts( false );
        }

        Stripe::setApiKey( $this->secret_key );
    }

    /**
     * Sets controller class objects.
     *
     * @since 3.6.1
     *
     * @return void
     */
    private function set_controllers() {
        $this->container['client'] = new StripeClient( $this->secret_key );
    }

    /**
     * Verifies if valid key format are given.
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public function verify_api_keys() {
        if ( ! $this->is_live_mode() ) {
            return preg_match( '/^pk_test_/', $this->publishable_key )
                && preg_match( '/^[rs]k_test_/', $this->secret_key );
        }

        return preg_match( '/^pk_live_/', $this->publishable_key )
            && preg_match( '/^[rs]k_live_/', $this->secret_key );
    }

    /**
     * Retrieves secret key.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public function get_secret_key() {
        return $this->secret_key;
    }

    /**
     * Retrieves publishable key.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public function get_publishable_key() {
        return $this->publishable_key;
    }

    /**
     * To check if the Stripe express API is running in production or sandbox environment
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public function is_live_mode() {
        return $this->live_mode;
    }

    /**
     * Checks if API is ready to use.
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public function is_api_ready() {
        return $this->api_configured;
    }

    /**
     * Retrieves API configuration error.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public function get_api_config_error() {
        return $this->api_error;
    }

    /**
     * Retrieves settings option.
     *
     * @since 3.6.1
     *
     * @param string $key
     *
     * @return mixed
     */
    public function get_option( $key = '_all' ) {
        if ( empty( $key ) ) {
            return [];
        }

        if ( '_all' === $key ) {
            return $this->settings;
        }

        if ( isset( $this->settings[ $key ] ) ) {
            return $this->settings[ $key ];
        }

        return '';
    }
}
