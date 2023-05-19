<?php
namespace WeDevs\DokanPro\Storage;

use Hybridauth\Storage\StorageInterface;
use PasswordHash;


/**
 * Cookie Based Session Storage Manager for Dokan with Hybridauth session storage support
 *
 * @since 3.3.6
 */
class Session implements StorageInterface {

    /**
     * Key prefix
     *
     * @var string
     */
    protected $key_prefix = 'dokan_';

    /**
     * Cookie prefix
     *
     * @var string
     */
    protected $cookie_prefix = 'session_storage';

    /**
     * Customer ID.
     *
     * @var int $customer_id Customer ID.
     */
    protected $customer_id;

    /**
     * Session Data.
     *
     * @var array $_data Data array.
     */
    protected $data = [];

    /**
     * Cookie name used for the session.
     *
     * @var string cookie name
     */
    protected $cookie;

    /**
     * Stores session due to expire timestamp.
     *
     * @var string session expiration timestamp
     */
    protected $session_expiration;

    /**
     * Session constructor.
     *
     * @param string $prefix cookie prefix
     * @param float|int $expiration expiration time in second
     *
     * @since 3.3.6
     */
    public function __construct( $prefix = '', $expiration = 60 * 60 * 48 ) {
        // set cookie prefix
        if ( ! empty( $prefix ) ) {
            $this->cookie_prefix = sanitize_key( $prefix );
        }

        // set expiration
        if ( ! empty( $expiration ) && is_numeric( $expiration ) ) {
            $this->session_expiration = time() + absint( $expiration );
        } else {
            $this->session_expiration = time() + DAY_IN_SECONDS;
        }

        $this->cookie = 'dokan_pro_' . $this->cookie_prefix . '_' . COOKIEHASH;
        $this->init_session_cookie();
    }

    /**
     * Setup cookie and customer ID.
     *
     * @since 3.3.6
     */
    public function init_session_cookie() {
        $cookie = $this->get_session_cookie();
        if ( $cookie ) {
            $this->customer_id         = $cookie[0];
            $this->session_expiration  = $cookie[1];
            $this->data                = (array) $cookie[3];
        } else {
            $this->customer_id = $this->generate_customer_id();
            $this->data        = [];
            $this->set_customer_session_cookie( true );
        }
    }

    /**
     * Get the session cookie, if set. Otherwise return false.
     *
     * Session cookies without a customer ID are invalid.
     *
     * @since 3.3.6
     *
     * @return bool|array
     */
    public function get_session_cookie() {
        $cookie_value = isset( $_COOKIE[ $this->cookie ] ) ? wp_unslash( $_COOKIE[ $this->cookie ] ) : false; // @codingStandardsIgnoreLine.

        if ( empty( $cookie_value ) || ! is_string( $cookie_value ) ) {
            return false;
        }

        list( $customer_id, $session_expiration, $cookie_hash, $data ) = explode( '||', $cookie_value );

        if ( empty( $customer_id ) ) {
            return false;
        }

        // Validate hash.
        $to_hash = $customer_id . '|' . $session_expiration;
        $hash    = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );

        if ( empty( $cookie_hash ) || ! hash_equals( $hash, $cookie_hash ) ) {
            return false;
        }

        $data = maybe_unserialize( $data );

        return array( $customer_id, $session_expiration, $cookie_hash, $data );
    }

    /**
     * Sets the session cookie on-demand (usually after adding an item to the cart).
     *
     * Warning: Cookies will only be set if this is called before the headers are sent.
     *
     * @param bool $set Should the session cookie be set.
     *
     * @since 3.3.6
     *
     * @return void
     */
    public function set_customer_session_cookie( $set ) {
        if ( $set ) {
            $to_hash           = $this->customer_id . '|' . $this->session_expiration;
            $cookie_hash       = hash_hmac( 'md5', $to_hash, wp_hash( $to_hash ) );
            $cookie_value      = $this->customer_id . '||' .
                                    $this->session_expiration . '||' .
                                    $cookie_hash . '||' .
                                    maybe_serialize( $this->data );

            if ( ! isset( $_COOKIE[ $this->cookie ] ) || $_COOKIE[ $this->cookie ] !== $cookie_value ) {
                setcookie( $this->cookie, $cookie_value, $this->session_expiration, defined( 'COOKIEPATH' ) ? COOKIEPATH : '/', COOKIE_DOMAIN, $this->use_secure_cookie(), true );
            }
        }
    }

    /**
     * Should the session cookie be secure?
     *
     * @since 3.3.6
     *
     * @return bool
     */
    protected function use_secure_cookie() {
        return apply_filters( 'dokan_session_storage_use_secure_cookie', $this->site_is_https() && is_ssl(), $this->cookie, $this->cookie_prefix, $this->key_prefix );
    }

    /**
     * Check if the home URL is https. If it is, we don't need to do things such as 'force ssl'.
     *
     * @since  3.3.6
     * @return bool
     */
    public function site_is_https() {
        return false !== strstr( get_option( 'home' ), 'https:' );
    }

    /**
     * Generate a unique customer ID.
     *
     * Uses Portable PHP password hashing framework to generate a unique cryptographically strong ID.
     *
     * @since 3.3.6
     *
     * @return string
     */
    public function generate_customer_id() {
        require_once ABSPATH . 'wp-includes/class-phpass.php';
        $hasher      = new PasswordHash( 8, false );
        $customer_id = md5( $hasher->get_random_bytes( 32 ) );

        return $customer_id;
    }

    /**
     * Get customer ID.
     *
     * @since 3.3.6
     *
     * @return int
     */
    public function get_customer_id() {
        return $this->customer_id;
    }

    /**
     * Destroy all session data.
     *
     * @since 3.3.6
     */
    public function destroy_session() {
        $this->forget_session();
    }

    /**
     * Forget all session data without destroying it.
     *
     * @since 3.3.6
     */
    public function forget_session() {
        setcookie( $this->cookie, '', 0, defined( 'COOKIEPATH' ) ? COOKIEPATH : '/', COOKIE_DOMAIN, $this->use_secure_cookie(), true );

        $this->data        = array();
        $this->customer_id = $this->generate_customer_id();
    }

    /**
     * @param string $key
     *
     * @since 3.3.6
     *
     * @return mixed|null
     */
    public function get( $key ) {
        $key = sanitize_key( $this->key_prefix . strtolower( $key ) );

        return isset( $this->data[ $key ] ) ? $this->data[ $key ] : null;
    }

    /**
     * @param string $key
     * @param string|array $value
     *
     * @since 3.3.6
     */
    public function set( $key, $value ) {
        $key = sanitize_key( $this->key_prefix . strtolower( $key ) );

        $this->data[ $key ] = $value;

        $this->set_customer_session_cookie( true );
    }

    /**
     * Clear session data
     *
     * @since 3.3.6
     */
    public function clear() {
        $this->destroy_session();
    }

    /**
     * @param string $key
     *
     * @since 3.3.6
     */
    public function delete( $key ) {
        $key = sanitize_key( $this->key_prefix . strtolower( $key ) );

        if ( isset( $this->data[ $key ] ) ) {
            unset( $this->data[ $key ] );

            $this->set_customer_session_cookie( true );
        }
    }

    /**
     * @param string $key
     *
     * @since 3.3.6
     */
    public function deleteMatch( $key ) {
        $key = sanitize_key( $this->key_prefix . strtolower( $key ) );

        if ( count( $this->data ) ) {
            foreach ( $this->data as $k => $v ) {
                if ( strstr( $k, $key ) ) {
                    unset( $this->data[ $k ] );
                }
            }

            $this->set_customer_session_cookie( true );
        }
    }
}
