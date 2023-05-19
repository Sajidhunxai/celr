<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Processor;

use Exception;
use MangoPay\Hook;
use MangoPay\EventType;
use MangoPay\Pagination;
use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;
use WeDevs\DokanPro\Modules\MangoPay\Support\Processor;

/**
 * Webhook processor class
 *
 * @since 3.5.0
 */
class Webhook extends Processor {

    /**
     * Webhook key for mangopay
     *
     * @since 3.5.0
     *
     * @var string
     */
    private static $prefix = 'dokan-mangopay';

    /**
     * Supported webhook events for MangoPay
     *
     * @since 3.5.0
     *
     * @var array
     */
    private static $supported_events = array(
        EventType::PayinNormalSucceeded    => 'PayInNormalSucceded',
        EventType::PayinRefundSucceeded    => 'PayInRefundSucceeded',
        EventType::PayoutNormalSucceeded   => 'PayOutNormalSucceeded',
        EventType::PayoutNormalFailed      => 'PayOutNormalFailed',
        EventType::PayoutRefundSucceeded   => 'PayOutRefundSucceeded',
        EventType::TransferNormalSucceeded => 'TransferNormalSucceeded',
        EventType::TransferRefundSucceeded => 'TransferRefundSucceeded',
        EventType::UserKycRegular          => 'UserKycRegular',
    );

    /**
     * Retrieves prefix for webhook
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_prefix() {
        return self::$prefix;
    }

        /**
     * Retrieves all webhooks
     *
     * @since 3.5.0
     *
     * @return object|\WP_Error
     */
    public static function all() {
        try{
            return static::config()->mangopay_api->Hooks->GetAll();
        } catch ( Exception $e ) {
            return new \WP_Error( 'error-retriving-webhooks', $e->getMessage() );
        }
    }

    /**
     * Registers a new webhook for Mangopay
     *
     * @since 3.5.0
     *
     * @param string $event_type
     *
     * @return int|false
     */
    public static function create( $event_type ) {
        $hook 			 = new Hook();
        $hook->Url		 = self::generate_url( $event_type );
        $hook->Status	 = 'ENABLED';
        $hook->Validity	 = 'VALID';
        $hook->EventType = $event_type;

        try {
            $response = static::config()->mangopay_api->Hooks->Create( $hook );
        } catch ( Exception $e ) {
            self::log(
                sprintf(
                    'Could not create %1$s. Message: %2$s',
                    $event_type,
                    $e->getMessage()
                ),
                'error'
            );
            return false;
        }

        if ( empty( $response->Id ) ) {
            return false;
        }

        return $response->Id;
    }

    /**
     * Updates an existing webhook
     *
     * @since 3.5.0
     *
     * @param object $hook
     * @param string $event_type
     *
     * @return int|false
     */
    public static function update( $existing_hook, $event_type ) {
        $hook 			 = new Hook();
        $hook->Url		 = self::generate_url( $event_type );
        $hook->Status	 = 'ENABLED';
        $hook->Validity	 = 'VALID';
        $hook->EventType = $event_type;
        $hook->Id		 = $existing_hook->Id;

        try {
            $response = static::config()->mangopay_api->Hooks->Update( $hook );
        } catch ( Exception $e ) {
            self::log(
                sprintf(
                    'Could not update %1$s. Message: %2$s',
                    $event_type,
                    $e->getMessage()
                ),
                'error'
            );
            return false;
        }

        if ( empty( $response->Id ) ) {
            return false;
        }

        return $response->Id;
    }

    /**
     * Retrieves list of supported webhook events
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_supported_events() {
        return apply_filters( 'dokan_mangopay_supported_webhook_events', self::$supported_events );
    }

    /**
     * Retrieves webhook key
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_key() {
        $options = static::config()->get_options();
        return ! empty( $options['webhook_key'] ) ? $options['webhook_key'] : '';
    }

    /**
     * Retrieves payload of a webhook request
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_payload() {
        $payload = array(
            'RessourceId' => '',
            'EventType'	  => '',
            'Date'		  => strtotime( date( 'Y-m-d' ) ),
        );

        if ( isset( $_REQUEST['RessourceId'] ) ) {
            $payload['RessourceId'] = sanitize_text_field( wp_unslash( $_REQUEST['RessourceId'] ) );
        }

        if ( isset( $_REQUEST['EventType'] ) ) {
            $payload['EventType'] = sanitize_text_field( wp_unslash( $_REQUEST['EventType'] ) );
        }

        if ( isset( $_REQUEST['Date'] ) ) {
            $payload['Date'] = sanitize_text_field( wp_unslash( $_REQUEST['Date'] ) );
        }

        return $payload;
    }

    /**
     * Check that a mangopay incoming webhook is enabled & valid
     *
     * @since 3.5.0
     *
     * @param object $hook
     *
     * @return boolean
     */
    public static function is_valid( $hook ) {
        if (
            is_object( $hook ) &&
            'ENABLED' === $hook->Status &&
            'VALID' === $hook->Validity
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get the URL of the webhooks dashboard
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_dashboard_url() {
        return static::config()->get_dashboard_url() . '/Notifications';
    }

    /**
     * Generate webhook url
     *
     * @since 3.5.0
     *
     * @param string $event_type
     *
     * @return string
     */
    public static function generate_url( $event ) {
        return home_url( 'wc-api/' . self::generate_event_slug( $event ), 'https' );
    }

    /**
     * Generates event slug for webhook url
     *
     * @since 3.5.0
     *
     * @param string $event
     *
     * @return string
     */
    public static function generate_event_slug( $event ) {
        return self::get_prefix() . '/' . self::get_key() . '/' . strtolower( $event );
    }

    /**
     * Get a webhook registered in the Mangopay api by its type
     *
     * @since 3.5.0
     *
     * @return object|false
     */
    public static function get_by_type( $webhook_type ) {
        // Get the first page with 100 elements per page
        $pagination = new Pagination( 1, 100 );

        try {
            $hooks = static::config()->mangopay_api->Hooks->GetAll( $pagination );
        } catch ( Exception $e ) {
            self::log( 'Could not fetch hooks. Message: ' . $e->getMessage(), 'error' );
            return false;
        }

        foreach ( $hooks as $hook ) {
            if ( $hook->EventType === $webhook_type ) {
                return $hook;
            }
        }

        return false;
    }

    /**
     * Check that a webhook of the specified type is registered
     *
     * @since 3.5.0
     *
     * @param string $event_type
     *
     * @return boolean
     */
    public static function verify( $event_type ) {
        $hook = self::get_by_type( $event_type );
        if ( ! self::is_valid( $hook ) ) {
            return false;
        }

        if ( self::generate_url( $event_type ) !== $hook->Url ) {
            return false;
        }

        return true;
    }

    /**
     * Checks if webhook is authentic
     *
     * @since 3.5.0
     *
     * @param string $event_type
     * @param array $payload
     *
     * @return boolean
     */
    public static function is_authentic( $event_type, $payload ) {
        // Check that event_type is present in URL
        if ( empty( $event_type ) ) {
            self::log( 'URL doesn\'t include event type' );
            return false;
        }

        // Check webhook key
        if ( ! self::is_valid_event( $event_type ) ) {
            self::log( 'Incoming key is invalid' );
            return false;
        }

        // Check that payload is present
        if ( empty( $payload ) || ! is_array( $payload ) ) {
            self::log( 'No payload provided' );
            return false;
        }

        // Check that EventType is present in payload
        if ( empty( $payload['EventType'] ) ) {
            Helper::log( 'Payload doesn\'t include any event' );
            return false;
        }

        // Check that RessourceID is empty or present and numeric in payload
        if ( empty( $payload['RessourceId'] ) || ! is_numeric( $payload['RessourceId'] ) ) {
            Helper::log( 'Resource ID is empty or invalid' );
            return false;
        }

        // Check that Date is present in payload
        if ( empty( $payload['Date'] ) || ! is_numeric( $payload['Date'] ) ) {
            Helper::log( 'Date is empty or invalid' );
            return false;
        }

        // Check that URL and payload event types match
        if ( $event_type !== $payload['EventType'] ) {
            Helper::log( 'Event has not matched' );
            return false;
        }

        return true;
    }

    /**
     * Checks that the webhook key is present and valid
     *
     * @since 3.5.0
     *
     * @return boolean
     */
    public static function is_valid_event( $event_type ) {
        if ( ! preg_match(
            '|' . self::get_prefix() . '/([0-9a-f]{32})/' . strtolower( $event_type ) . '\?|', $_SERVER['REQUEST_URI'], $matches ) ) {
            self::log( 'URL doesn\'t includes a MD5 key' );
            return false;
        }

        if ( $matches[1] !== self::get_key() ) {
            self::log( 'Key in the incoming URL is not valid' );
            return false;
        }

        return true;
    }

    /**
     * Retrives endpoint suffix from webhook url
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_suffix() {
        $endpoint_suffix = preg_replace( '/\?.*$/', '', basename( esc_url_raw( $_SERVER['REQUEST_URI'] ) ) );

        if ( self::get_prefix() === $endpoint_suffix ) {
            $endpoint_suffix = '';
        }

        if ( self::get_key() === $endpoint_suffix ) {
            $endpoint_suffix = '';
        }

        return strtoupper( $endpoint_suffix );
    }

    /**
     * Registers all webhook events
     *
     * @since 3.5.0
     *
     * @return void
     */
    public static function register_all() {
        if ( ! Helper::is_api_ready() ) {
            return false;
        }

        $events = array_keys( self::get_supported_events() );
        foreach ( $events as $event ) {
            self::register( $event );
        }
    }

    /**
     * Deregisters a hook.
     *
     * @since 3.5.0
     *
     * @param object \MangoPay\Hook $hook
     *
     * @return object|false
     */
    public static function deregister( $hook ) {
        try {
            $hook->Status = 'DISABLED';
            return static::config()->mangopay_api->Hooks->Update( $hook );
        } catch ( Exception $e ) {
            self::log(
                sprintf(
                    'Could not update %1$s. Message: %2$s',
                    $hook->EventType,
                    $e->getMessage()
                ),
                'error'
            );
            return false;
        }
    }

    /**
     * Register a webhook
     *
     * @since 3.5.0
     *
     * @param string $event_type
     *
     * @return boolean
     */
    public static function register( $event_type ) {
        $hook = self::get_by_type( $event_type );

        if ( ! $hook ) {
            return self::create( $event_type );
        }

        if ( ! self::verify( $event_type ) ) {
            self::log(
                sprintf(
                    '%1$s is DISABLED or INVALID or Unusable - please update it via %2$s',
                    $event_type,
                    self::get_dashboard_url()
                )
            );
            return self::update( $hook, $event_type );
        }

        return true;
    }

    /**
     * Delete webhook on mangopay end
     *
     * @since 3.5.0
     *
     * @return boolean
     */
    public static function deregister_all() {
        if ( ! Helper::is_api_ready() ) {
            return false;
        }

        $hooks = self::all();
        if ( empty( $hooks ) || is_wp_error( $hooks ) ) {
            return false;
        }

        foreach ( $hooks as $hook ) {
            if ( $hook->Url === self::generate_url( $hook->EventType ) ) {
                self::deregister( $hook );
            }
        }
    }

    /**
     * Logs debug messages
     *
     * @since 3.5.0
     *
     * @param string $message
     * @param string $context
     *
     * @return void
     */
    public static function log( $message, $context = 'debug' ) {
        Helper::log( $message, 'Webhook', $context );
    }
}
