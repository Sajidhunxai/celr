<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Support;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WeDevs\DokanPro\Modules\StripeExpress\Support\Config;

/**
 * API handler class
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Support
 */
class Api {

    /**
     * The configuration instance.
     *
     * @since 3.7.17
     *
     * @var Config
     */
    private static $config = null;

    /**
     * Retrieves the desired API object.
     *
     * @since 3.6.1
     *
     * @return \Stripe\StripeClient
     */
    protected static function api() {
        if ( ! self::config()->is_api_ready() ) {
            return new \Stripe\StripeClient();
        }

        return self::config()->client;
    }

    /**
     * Returns instance of configuration.
     *
     * @since 3.6.1
     *
     * @return Config
     */
    protected static function config() {
        if ( null === self::$config ) {
            self::$config = Config::instance();
        }

        return self::$config;
    }
}
