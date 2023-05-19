<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Processors;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WP_Error;
use Stripe\ErrorObject;
use WeDevs\Dokan\Cache;
use WeDevs\Dokan\Exceptions\DokanException;
use WeDevs\DokanPro\Modules\StripeExpress\Api\Account;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Support\Settings;
use WeDevs\DokanPro\Modules\StripeExpress\Support\UserMeta;

/**
 * Class for processing orders.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Processors
 */
class User {

    /**
     * WP User ID.
     *
     * @since 3.7.8
     *
     * @var integer
     */
    private $user_id = 0;

    /**
     * Stripe account object.
     *
     * @since 3.7.8
     *
     * @var \Stripe\Account
     */
    private $stripe_account = false;

    /**
     * Trashed account ID.
     *
     * @since 3.7.18
     *
     * @var string
     */
    protected $trashed_account_id = false;

    /**
     * Class instance
     *
     * @since 3.7.8
     *
     * @var static
     */
    private static $instance = null;

    /**
     * Private constructor for singletone instance
     *
     * @since 3.7.8
     *
     * @return void
     */
    private function __construct() {}

    /**
     * Sets required data.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     * @param array      $args    (Optional)
     *
     * @return static
     */
    public static function set( $user_id, $args = [] ) {
        if ( ! static::$instance ) {
            static::$instance = new static();
        }

        if ( $user_id && intval( $user_id ) !== static::$instance->get_user_id() ) {
            static::$instance->set_user_id( $user_id );
            static::$instance->set_account( $args );
        }

        return static::$instance;
    }

    /**
     * Onboards user for a stripe express account.
     *
     * @since 3.6.1
     *
     * @param int|string $user_id
     * @param array      $args
     *
     * @return object|WP_Error
     */
    public static function onboard( $user_id, $args = [] ) {
        $user = get_userdata( $user_id );
        if ( ! $user ) {
            return new WP_Error( 'dokan-stripe-express-invalid-user', __( 'No valid user found', 'dokan' ) );
        }

        /*
         * If a vendor previously signed up for a account and then disconnected it,
         * we would store the account id in the user meta as trash.
         * So we need to check the case, and if an account is found in the trash,
         * we will reconnect the same account instead of creating a new one.
         */
        $trashed_account_id = UserMeta::get_trashed_stripe_account_id( $user->ID );
        if ( ! empty( $trashed_account_id ) ) {
            UserMeta::update_stripe_account_id( $user->ID, $trashed_account_id );

            // Country cannot be updated.
            unset( $args['country'] );
        }

        $self = self::set( $user->ID );

        try {
            if ( empty( $self->get_account_id() ) ) {
                $account_data = [
                    'email' => $user->user_email,
                ];

                if (
                    ! empty( $args['country'] ) &&
                    Settings::is_cross_border_transfer_enabled() &&
                    array_key_exists( $args['country'], Helper::get_supported_countries_for_vendors() )
                ) {
                    $account_data['country'] = $args['country'];
                }

                $response = Account::create( $account_data );

                UserMeta::update_stripe_account_id( $self->get_user_id(), $response->id );
            } else {
                $response = $self->get_data();
            }

            $account_id   = $response->id;
            $redirect_url = Helper::get_payment_settings_url();

            // Check if the onboarding request came from seller setup page.
            if ( ! empty( $args['url_args'] ) && false !== strpos( $args['url_args'], 'page=dokan-seller-setup' ) ) {
                $redirect_url = add_query_arg(
                    [
                        'page' => 'dokan-seller-setup',
                        'step' => 'payment',
                        '_admin_sw_nonce' => wp_create_nonce( 'dokan_admin_setup_wizard_nonce' ),
                    ],
                    home_url( '/' )
                );
            }

            $account_link_data['refresh_url'] = $redirect_url;
            $account_link_data['return_url']  = add_query_arg(
                [
                    'action'    => 'stripe_express_onboarding',
                    'seller_id' => $user->ID,
                    '_wpnonce'  => wp_create_nonce( 'dokan_stripe_express_onboarding' ),
                ],
                $redirect_url
            );

            return Account::create_onboarding_link( $account_id, $account_link_data );
        } catch ( DokanException $e ) {
            /*
             * Account invalid error can be thrown in case the account
             * is not found in Stripe base.
             * The reason could be changing the API keys of Stripe.
             * In that case, we will delete the backdated account id
             * and try to create a new account to ignore the inconsistency.
             */
            if ( ErrorObject::CODE_ACCOUNT_INVALID === $e->get_error_code() ) {
                UserMeta::delete_stripe_account_id( $self->get_user_id(), true );
                return self::onboard( $self->get_user_id(), $args );
            }

            if ( 'dokan-stripe-express-invalid-request-error' === $e->get_error_code() ) {
                return new WP_Error( 'dokan-stripe-express-onboard-error', $e->get_message() );
            }

            return new WP_Error(
                'dokan-stripe-express-onboard-error',
                __( 'Something went wrong! Account could not be created. Please try again later.', 'dokan' )
            );
        }
    }

    /**
     * Disconnect user account from Stripe.
     *
     * @since 3.7.18
     *
     * @param int  $user_id    The user ID to be dosconnected.
     * @param bool $skip_trash Indicates whether the id should be trashed or not.
     *
     * @return bool
     */
    public static function disconnect( $user_id, $skip_trash = false ) {
        $disconnected = UserMeta::delete_stripe_account_id( $user_id, $skip_trash );

        if ( ! $disconnected ) {
            return false;
        }

        /**
         * Dokan hook to do additional action after this payment gateway is disconnected by seller.
         *
         * @since 3.7.1
         */
        do_action( 'dokan_stripe_express_seller_deactivated', $user_id );

        return true;
    }

    /**
     * Retrieves stripe login url.
     *
     * @since 3.6.1
     *
     * @param array $args
     *
     * @return string|false Login url for stripe express, false in case of error
     */
    public function get_stripe_login_url( $args = [] ) {
        if ( empty( $this->get_account_id() ) ) {
            return false;
        }

        try {
            $defaults = [
                'redirect_url' => dokan_get_page_url( 'dashboard', 'dokan', 'settings/payment' ),
            ];

            $args         = wp_parse_args( $args, $defaults );
            $stripe_login = Account::create_login_link( $this->get_account_id(), $args );
            return $stripe_login->url;
        } catch ( DokanException $e ) {
            return false;
        }
    }

    /**
     * Gets atripe account data of an user.
     *
     * @since 3.6.1
     *
     * @return object|false
     */
    public function get_data() {
        return $this->stripe_account;
    }

    /**
     * Retrieves the WP user id.
     *
     * @since 3.7.8
     *
     * @return int
     */
    public function get_user_id() {
        return $this->user_id;
    }

    /**
     * Sets user id for customer.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     *
     * @return void
     */
    protected function set_user_id( $user_id ) {
        $this->user_id = absint( $user_id );
    }

    /**
     * Retrieves the stripe account id.
     *
     * @since 3.7.8
     *
     * @return string|false
     */
    public function get_account_id() {
        return ! empty( $this->stripe_account->id ) ? $this->stripe_account->id : false;
    }

    /**
     * Sets Stripe account data.
     *
     * @since 3.7.8
     *
     * @param array $args (Optional)
     *
     * @return void
     */
    protected function set_account( $args = [] ) {
        try {
            $account_id = UserMeta::get_stripe_account_id( $this->user_id );

            if ( empty( $account_id ) ) {
                $this->stripe_account = false;
                $this->trashed_account_id = UserMeta::get_trashed_stripe_account_id( $this->user_id );
            } else {
                $this->stripe_account = Account::get( $account_id, $args );
            }
        } catch ( DokanException $e ) {
            if ( ErrorObject::CODE_ACCOUNT_INVALID === $e->get_error_code() ) {
                static::disconnect( $this->user_id, true );
            }
            $this->stripe_account = false;
        }
    }

    /**
     * Checks if user is connected to stripe.
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public function is_connected() {
        return ! empty( $this->stripe_account->charges_enabled );
    }

    /**
     * Checks if an user has completed onboarding.
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public function is_onboarded() {
        return ! empty( $this->stripe_account->details_submitted );
    }

    /**
     * Checks if an user is enabled for payout.
     *
     * @since 3.6.1
     *
     * @return boolean
     */
    public function is_payout_enabled() {
        return ! empty( $this->stripe_account->payouts_enabled );
    }

    /**
     * Checks if an user is connected and enabled for payout.
     *
     * @since 3.7.8
     *
     * @return boolean
     */
    public function is_activated() {
        return ! empty( $this->stripe_account->charges_enabled ) &&
               ( ! empty( $this->stripe_account->transfers_enabled ) || $this->is_payout_enabled() );
    }

    /**
     * Checks if user account is trashed.
     *
     * @since 3.7.17
     *
     * @return boolean
     */
    public function is_trashed() {
        return ! $this->stripe_account && ! empty( $this->trashed_account_id );
    }

    /**
     * Retrieves the trashed account id.
     *
     * @since 3.7.18
     *
     * @return string|false
     */
    public function get_trashed_account_id() {
        return $this->trashed_account_id;
    }

    /**
     * Retrives account data of the platform.
     *
     * @since 3.7.17
     *
     * @return \Stripe\Account|false
     */
    public static function get_platform_data() {
        try {
            $cache_key   = "stripe_express_get_platform_data";
            $cache_group = 'stripe_express_platform_data';
            $platform    = Cache::get_transient( $cache_key, $cache_group );

            if ( false === $platform ) {
                $platform = Account::get( null );
                Cache::set_transient( $cache_key, $platform, $cache_group );
            }

            return $platform;
        } catch ( DokanException $e ) {
            return false;
        }
    }

    /**
     * Retrives the country of the platform.
     *
     * @since 3.7.17
     *
     * @return string|false The two-letter ISO code of the country or `false` if no data found.
     */
    public static function get_platform_country() {
        $platform = self::get_platform_data();

        if ( ! $platform ) {
            return false;
        }

        return $platform->country;
    }
}
