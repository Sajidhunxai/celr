<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Support;

use Exception;
use MangoPay\Sorting;
use MangoPay\Pagination;
use MangoPay\MangoPayApi;
use MangoPay\SortDirection;
use MangoPay\Libraries\ResponseException;
use MangoPay\Libraries\Exception as MangoException;

defined( 'ABSPATH' ) || exit;

/**
 * Class for managing configuration of MangoPay
 *
 * @since 3.5.0
 */
class Config {

    /**
     * Mangopay api url for sandbox account
     *
     * @since 3.5.0
     *
     * @var string
     */
    const SANDBOX_API_URL = 'https://api.sandbox.mangopay.com';

    /**
     * Mangopay api url for production account
     *
     * @since 3.5.0
     *
     * @var string
     */
    const PROD_API_URL = 'https://api.mangopay.com';

    /**
     * Mangopay dashboard url for sandbox account
     *
     * @since 3.5.0
     *
     * @var string
     */
    const SANDBOX_DASHBOARD = 'https://dashboard.sandbox.mangopay.com';

    /**
     * Mangopay dashboard url for production account
     *
     * @since 3.5.0
     *
     * @var string
     */
    const PRODUCTION_DASHBOARD = 'https://dashboard.mangopay.com';

    /**
     * Holds the filename that stores account key
     *
     * @since 3.5.0
     *
     * @var string
     */
    const KEY_FILE = 'secret.key.php';

    /**
     * Holds the filename for log
     *
     * @since 3.5.0
     *
     * @var string
     */
    const LOG_FILE = 'dokan-mangopay-transactions.log.php';

    /**
     * Directory name for storing temporary data
     *
     * @since 3.5.0
     *
     * @var string
     */
    const TEMP_DIRECTORY = 'dokan-mangopay-temp';

    /**
     * Indicates debugging mode
     *
     * @since 3.5.0
     *
     * @var boolean
     */
    const DEBUG_MODE = false;

    /**
     * Determines the operation mode
     *
     * @since 3.5.0
     *
     * @var boolean
     */
    private $production_mode = true;

    /**
     * Determines if configuration is loaded
     *
     * @since 3.5.0
     *
     * @var boolean
     */
    private $gateway_configured = false;

    /**
     * CLient id for Mangopay account
     *
     * @since 3.5.0
     *
     * @var string
     */
    private $client_id;

    /**
     * Holds error for mangopay client id
     *
     * @since 3.5.0
     *
     * @var string
     */
    private $client_id_error;

    /**
     * API key for mangopay account
     *
     * @since 3.5.0
     *
     * @var string
     */
    private $api_key;

    /**
     * Holds path for log file
     *
     * @since 3.5.0
     *
     * @var string
     */
    private $log_file_path;

    /**
     * Holds gateway option data
     *
     * @since 3.5.0
     *
     * @var array
     */
    private $options;

    /**
     * Holds mangopay api object
     *
     * @since 3.5.0
     *
     * @var object
     */
    public $mangopay_api;

    /**
     * The reference to Singleton instance of this class
     *
     * @since 3.5.0
     *
     * @var object
     */
    private static $instance = null;

    /**
     * Private constructor to prevent creating a new instance of the
     * Singleton via the `new` operator from outside of this class.
     *
     * @since 3.5.0
     */
    private function __construct() {
        $this->set_options();
        $this->init();
    }

    /**
     * Retrieves the singletone instance of the class.
     *
     * @since 3.5.0
     *
     * @return object
     */
    public static function get_instance() {
        if ( null === static::$instance ) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * To check if the Mangopay API is running in production or sandbox environment
     *
     * @since 3.5.0
     *
     * @return boolean
     */
    public function is_production() {
        return $this->production_mode;
    }

    /**
     * Checks if debugging enabled
     *
     * @since 3.5.0
     *
     * @return boolean
     */
    public function is_debug_mode() {
        return self::DEBUG_MODE;
    }

    /**
     * Get Mangopay dashboard url
     *
     * @since 3.5.0
     *
     * @return string
     */
    public function get_dashboard_url() {
        return $this->production_mode ? self::PRODUCTION_DASHBOARD : self::SANDBOX_DASHBOARD;
    }

    /**
     * Retrieves the option values of configuration
     *
     * @since 3.5.0
     *
     * @return array
     */
    public function get_options() {
        return $this->options;
    }

    /**
     * Sets the settings options
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function set_options() {
        $this->options = Settings::get();

        if ( empty( $this->options ) ) {
            $this->options = array(
                'sandbox_mode'          => 'no',
                'sandbox_client_id'     => '',
                'sandbox_api_key'       => '',
                'client_id'             => '',
                'api_key'               => '',
                'default_vendor_status' => 'either',
                'default_business_type' => 'either',
                'webhook_key'           => '',
            );
        }

        if ( ! empty( $this->options['api_key'] ) ) {
            $this->options['api_key'] = $this->decrypt( $this->options['api_key'] );
        }

        if ( ! empty( $this->options['sandbox_api_key'] ) ) {
            $this->options['sandbox_api_key'] = $this->decrypt( $this->options['sandbox_api_key'] );
        }
    }

    /**
     * Initializes the configuration
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function init() {
        if ( ! empty( $this->options['sandbox_mode'] ) && 'yes' === $this->options['sandbox_mode'] ) {
            $this->production_mode = false;
        }

        $this->client_id             = $this->production_mode ? trim( $this->options['client_id'] ) : trim( $this->options['sandbox_client_id'] );
        $this->api_key               = $this->production_mode ? trim( $this->options['api_key'] ) : trim( $this->options['sandbox_api_key'] );
        $this->default_vendor_status = $this->options['default_vendor_status'];
        $this->default_business_type = $this->options['default_business_type'];

        $this->set_env();
    }

    /**
     * Sets environment by loading and instantiating Mangopay API
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function set_env() {
        // phpcs:disable WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
        // Setup temporary directory
        $temp_path = $this->set_temp_dir();

        $this->log_file_path = $temp_path . '/' . self::LOG_FILE;

        // Initialize log file if not present
        if ( ! file_exists( $this->log_file_path ) ) {
            file_put_contents( $this->log_file_path, '<?php header("HTTP/1.0 404 Not Found"); echo "File not found."; exit; /*' );
        }

        // Add a .htaccess to temporary directory dir for additional security
        $htaccess_file = $temp_path . '/.htaccess';
        if ( ! file_exists( $htaccess_file ) ) {
            file_put_contents( $htaccess_file, "order deny,allow\ndeny from all\nallow from 127.0.0.1" );
        }

        $htaccess_path = dirname( $temp_path ) . '/.htaccess';
        if ( ! file_exists( $htaccess_path ) ) {
            file_put_contents( $htaccess_path, "order deny,allow\ndeny from all\nallow from 127.0.0.1" );
        }

        $this->set_api();
        // phpcs:enable WordPress.WP.AlternativeFunctions.file_system_read_file_put_contents
    }

    /**
     * Sets API configuration for MangoPay
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function set_api() {
        $this->mangopay_api                          = new MangoPayApi();
        $this->mangopay_api->Config->ClientId        = $this->client_id;
        $this->mangopay_api->Config->ClientPassword  = $this->api_key;
        $this->mangopay_api->Config->TemporaryFolder = $this->set_temp_dir() . '/';
        $this->mangopay_api->Config->BaseUrl         = $this->production_mode ? self::PROD_API_URL : self::SANDBOX_API_URL;
        $this->mangopay_api->OAuthTokenManager->RegisterCustomStorageStrategy( new MockStorageStrategy() );
    }

    /**
     * Get temporary folder path
     *
     * @since 3.5.0
     *
     * @return string
     */
    public function get_temp_dir() {
        if ( ! $this->gateway_configured ) {
            return $this->set_temp_dir();
        }

        return $this->mangopay_api->Config->TemporaryFolder;
    }

    /**
     * Setup temporary directory
     *
     * @since 3.5.0
     *
     * @return string
     */
    private function set_temp_dir() {
        $uploads      = wp_upload_dir();
        $uploads_path = $uploads['basedir'];
        $mode         = $this->production_mode ? 'production' : 'sandbox';
        $temp_path    = $uploads_path . '/' . self::TEMP_DIRECTORY . '/' . $mode;

        // Creates the directory
        wp_mkdir_p( $temp_path );
        return $temp_path;
    }

    /**
     * Retrieves log file path
     *
     * @since 3.5.0
     *
     * @return string
     */
    public function get_log_file_path() {
        return $this->log_file_path;
    }

    /**
     * Simple API connection test
     *
     * @since 3.5.0
     *
     * @see: https://gist.github.com/hobailey/105c53717b8547ba66d7
     *
     * @return boolean
     */
    public function test_connection() {
        if ( ! $this->gateway_configured ) {
            $this->set_env();
        }

        if ( ! $this->is_client_id_valid( $this->client_id ) ) {
            return false;
        }

        try {
            $pagination = new Pagination( 1, 1 );
            $sorting    = new Sorting();
            $sorting->AddField( 'CreationDate', SortDirection::DESC );
            $result = $this->mangopay_api->Users->GetAll( $pagination, $sorting );

            $this->gateway_configured = true;
            return true;
        } catch ( ResponseException $e ) {
            return false;
        } catch ( MangoException $e ) {
            return false;
        } catch ( Exception $e ) {
            return false;
        }

        return false;
    }

    /**
     * Test if the Mangopay client id is well-formed
     *
     * @since 3.5.0
     *
     * @return boolean
     */
    private function is_client_id_valid() {
        // Check if client id empty
        if ( empty( $this->client_id ) ) {
            $this->client_id_error = __( 'Client ID empty.', 'dokan' );
            return false;
        }

        // Test if string is less than 2 char
        if ( strlen( $this->client_id ) < 2 ) {
            $this->client_id_error = __( 'Client ID has too few characters. Minimum 2 charecters are required.', 'dokan' );
            return false;
        }

        // Test if URL encoding will be same as original
        if ( $this->client_id !== rawurlencode( $this->client_id ) ) {
            $this->client_id_error = __( 'Client ID is not URL-compatible.', 'dokan' );
            return false;
        }

        return true;
    }

    /**
     * Retrieves client id error
     *
     * @since 3.5.0
     *
     * @return string
     */
    public function get_client_id_error() {
        return ! empty( $this->client_id_error ) ? $this->client_id_error : '';
    }

    /**
     * Encrypts passphrase data
     *
     * @since 3.5.0
     *
     * @param string $data
     *
     * @return string
     */
    public function encrypt( $data ) {
        // phpcs:disable
        $key_file = dirname( $this->get_temp_dir() ) . '/' . self::KEY_FILE;

        if ( ! file_exists( $key_file ) ) {
            $key     = substr( str_shuffle( MD5( microtime() ) ), 0, 16 );
            $content = '<?php header("HTTP/1.0 404 Not Found"); echo "File not found."; exit; //' . $key . ' ?>';

            file_put_contents( $key_file, $content );
        } else {
            $content = file_get_contents( $key_file );

            if ( ! preg_match( '|//(\w+)|', $content, $matches ) ) {
                return $data;
            }

            $key = $matches[1];
        }

        if ( function_exists( 'openssl_encrypt' ) ) {
            $cipher     = 'AES-128-CBC';
            $ivlen      = openssl_cipher_iv_length( $cipher );
            $iv         = openssl_random_pseudo_bytes( $ivlen );
            $ciphertext = openssl_encrypt( $data, $cipher, $key, OPENSSL_RAW_DATA, $iv );
            $hmac       = hash_hmac( 'sha256', $ciphertext, $key, true );
            $ciphertext = $iv . $hmac . $ciphertext;
        } else {
            $iv_size    = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC );
            $iv         = mcrypt_create_iv( $iv_size, MCRYPT_RAND );
            $ciphertext = mcrypt_encrypt( MCRYPT_RIJNDAEL_128, $key, $data, MCRYPT_MODE_CBC, $iv );
            $ciphertext = $iv . $ciphertext;
        }

        return base64_encode( $ciphertext );
        // phpcs:enable
    }

    /**
     * Decrypts passphrase data
     *
     * @since 3.5.0
     *
     * @param string $data
     *
     * @return string|false
     */
    public function decrypt( $data ) {
        // phpcs:disable
        $keyfile = dirname( $this->get_temp_dir() ) . '/' . self::KEY_FILE;
        if ( ! file_exists( $keyfile ) ) {
            return $data;
        }

        $content = file_get_contents( $keyfile );
        if ( ! preg_match( '|//(\w+)|', $content, $matches ) ) {
            return $data;
        }

        $key = $matches[1];

        if ( ! function_exists( 'openssl_encrypt' ) ) {
            $ciphertext_dec = base64_decode( $data );
            $iv_size        = mcrypt_get_iv_size( MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC );
            $iv_dec         = substr( $ciphertext_dec, 0, $iv_size );
            $ciphertext_dec = substr( $ciphertext_dec, $iv_size );
            $plaintext_dec  = mcrypt_decrypt( MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec );

            return str_replace( "\0", '', $plaintext_dec );
        }

        $decoded    = base64_decode( $data );
        $cipher     = 'AES-128-CBC';
        $ivlen      = openssl_cipher_iv_length( $cipher );
        $iv         = substr( $decoded, 0, $ivlen );
        $sha2len    = 32;
        $hmac       = substr( $decoded, $ivlen, $sha2len );
        $ciphertext = substr( $decoded, $ivlen + $sha2len );
        $plaintext  = openssl_decrypt( $ciphertext, $cipher, $key, OPENSSL_RAW_DATA, $iv );
        $calcmac    = hash_hmac( 'sha256', $ciphertext, $key, true );

        // PHP 5.6+ timing attack safe comparison
        if ( ! hash_equals( $hmac, $calcmac ) ) {
            return false;
        }

        return $plaintext;
        // phpcs:enable
    }
}
