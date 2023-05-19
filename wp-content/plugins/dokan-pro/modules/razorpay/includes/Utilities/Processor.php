<?php

namespace WeDevs\DokanPro\Modules\Razorpay\Utilities;

use WP_Error;
use WeDevs\DokanPro\Modules\Razorpay\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

/**
 * Class Processor
 *
 * @package WeDevs\DokanPro\Modules\Razorpay\Utilities
 *
 * @since 3.5.0
 */
class Processor {
    /**
     * Instance of self
     *
     * @var Processor
     */
    protected static $instance = null;

    /**
     * Razorpay API mode
     *
     * @var bool
     */
    protected $test_mode = false;

    /**
     * Processor constructor.
     *
     * @since 3.5.0
     */
    protected function __construct() {
        $this->api_base_url = 'https://api.razorpay.com/';

        if ( Helper::is_test_mode() ) {
            $this->test_mode = true;
        }
    }

    /**
     * Initialize Processor() class
     *
     * @since 3.5.0
     *
     * @return Processor
     */
    public static function init() {
        if ( static::$instance === null ) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Headers data for curl request.
     *
     * @since 3.5.0
     *
     * @param bool $content_type_json
     * @param bool $request_with_token, default false. We'll use basic auth.
     *
     * @return array|WP_Error
     */
    public function get_header( $content_type_json = true, $request_with_token = false ) {
        $content_type = $content_type_json ? 'json' : 'x-www-form-urlencoded';

        $headers = [
            'Content-Type' => 'application/' . $content_type,
        ];

        if ( ! $request_with_token ) {
            $headers['Authorization'] = 'Basic ' . $this->get_authorization_data();
            $headers['Ignorecache']   = true;

            return $headers;
        }

        return $headers;
    }

    /**
     * Get base64 encoded authorization data.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public function get_authorization_data() {
        $key_id     = Helper::get_key_id();
        $key_secret = Helper::get_key_secret();

        return base64_encode( $key_id . ':' . $key_secret ); //phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.obfuscation_base64_encode
    }

    /**
     * Make remote URL request.
     *
     * @since 3.5.0
     *
     * @param array $data
     *
     * @return array|WP_Error
     */
    public function make_request( $data = [] ) {
        $defaults = [
            'url'                => '',
            'data'               => [],
            'method'             => 'post',
            'header'             => true,
            'content_type_json'  => true,
            'request_with_token' => false,
        ];

        $parsed_args = wp_parse_args( $data, $defaults );

        $header = $parsed_args['header'] === true ? $this->get_header( $parsed_args['content_type_json'], $parsed_args['request_with_token'] ) : [];
        if ( is_wp_error( $header ) ) {
            return $header;
        }

        $args = [
            'timeout'     => '120',
            'redirection' => '120',
            'httpversion' => '1.0',
            'blocking'    => true,
            'headers'     => $header,
            'cookies'     => [],
        ];

        if ( ! empty( $parsed_args['data'] ) ) {
            $args['body'] = $parsed_args['data'];
        }

        switch ( strtolower( $parsed_args['method'] ) ) {
            case 'get':
                $args['method'] = 'GET';
                break;
            case 'post':
                $args['method'] = 'POST';
                break;
            case 'delete':
                $args['method'] = 'DELETE';
                break;
            case 'patch':
                $args['method'] = 'PATCH';
                break;
            default:
                $args['method'] = 'POST';
        }

        $response = wp_remote_request( esc_url_raw( $parsed_args['url'] ), $args );

        if ( is_wp_error( $response ) ) {
            return $response;
        }

        $body              = json_decode( wp_remote_retrieve_body( $response ), true );
        $razorpay_debug_id = wp_remote_retrieve_header( $response, 'razorpay-debug-id' );

        if (
            200 !== wp_remote_retrieve_response_code( $response ) &&
            201 !== wp_remote_retrieve_response_code( $response ) &&
            202 !== wp_remote_retrieve_response_code( $response ) &&
            204 !== wp_remote_retrieve_response_code( $response )
        ) {
            return new WP_Error( 'dokan_razorpay_request_error', $body, [ 'razorpay_debug_id' => $razorpay_debug_id ] );
        }

        if ( $razorpay_debug_id ) {
            $body['razorpay_debug_id'] = $razorpay_debug_id;
        }

        return $body;
    }

    /**
     * Make Razorpay full url.
     *
     * @since 3.5.0
     *
     * @param string $path
     *
     * @return string
     */
    public function make_razorpay_url( $path ) {
        return $this->api_base_url . $path;
    }
}
