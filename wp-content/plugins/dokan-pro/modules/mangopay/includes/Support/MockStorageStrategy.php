<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Support;

use MangoPay\Libraries\IStorageStrategy;

/**
 * Same as storage strategy implementation for tests
 *
 * @since 3.5.0
 */
class MockStorageStrategy implements IStorageStrategy {

    /**
     * Holds the oAauth token
     *
     * @since 3.5.0
     *
     * @var string
     */
    private static $_oauth_token = null;

    /**
     * Gets the current authorization token.
     *
     * @since 3.5.0
     *
     * @return \MangoPay\Libraries\OAuthToken Currently stored token instance or null.
     */
    public function Get() {
        return self::$_oauth_token;
    }
    /**
     * Stores authorization token passed as an argument.
     *
     * @since 3.5.0
     *
     * @param \MangoPay\Libraries\OAuthToken $token Token instance to be stored.
     */
    public function Store( $token ) {
        self::$_oauth_token = $token;
    }
}
