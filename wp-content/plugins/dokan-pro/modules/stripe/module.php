<?php

namespace WeDevs\DokanPro\Modules\Stripe;

use WeDevs\Dokan\Traits\ChainableContainer;
use WeDevs\DokanPro\Admin\Announcement;
use WeDevs\DokanPro\Modules\Stripe\Gateways\RegisterGateways;
use WeDevs\DokanPro\Modules\Stripe\Subscriptions\ProductSubscription;
use WeDevs\DokanPro\Modules\Stripe\WithdrawMethods\RegisterWithdrawMethods;
use Stripe\Account;

class Module {

    use ChainableContainer;

    /**
     * Constructor method
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function __construct() {
        $this->define_constants();
        $this->set_controllers();

        // Activation and Deactivation hook
        add_action( 'dokan_activated_module_stripe', [ self::class, 'activate' ], 10, 1 );
        add_action( 'dokan_deactivated_module_stripe', [ self::class, 'deactivate' ], 10, 1 );

        // display notices
        add_action( 'dokan_dashboard_before_widgets', [ $this, 'check_vendor_access_key_is_valid' ], 10 );
    }

    /**
     * Define module constants
     *
     * @since 3.0.3
     *
     * @return void
     */
    private function define_constants() {
        define( 'DOKAN_STRIPE_FILE', __FILE__ );
        define( 'DOKAN_STRIPE_PATH', dirname( DOKAN_STRIPE_FILE ) );
        define( 'DOKAN_STRIPE_ASSETS', plugin_dir_url( DOKAN_STRIPE_FILE ) . 'assets/' );
        define( 'DOKAN_STRIPE_TEMPLATE_PATH', dirname( DOKAN_STRIPE_FILE ) . '/templates/' );
    }

    /**
     * Set controllers
     *
     * @since 3.0.3
     *
     * @return void
     */
    private function set_controllers() {
        $this->container['register_gateways'] = new RegisterGateways();
        $this->container['webhook']           = new WebhookHandler();

        // return if payment gateway is not enabled
        if ( ! Helper::is_enabled() ) {
            return;
        }

        $this->container['register_withdraw_methods'] = new RegisterWithdrawMethods();
        $this->container['intent_controller']         = new IntentController();
        $this->container['product_subscription']      = new ProductSubscription();
        $this->container['payment_tokens']            = new PaymentTokens();
        $this->container['refund']                    = new Refund();
        $this->container['validation']                = new Validation();
        $this->container['store_progress']            = new StoreProgress();
        $this->container['vendor_profile']            = new VendorProfile();
    }

    /**
     *
     * @since 3.2.0
     */
    public static function activate( $instance ) {
        $instance->container['webhook']->register_webhook();
    }

    /**
     *
     * @since 3.2.0
     */
    public static function deactivate( $instance ) {
        $instance->container['webhook']->deregister_webhook();
    }

    /**
     * This method will check if connected vendor access key is valid.
     *
     * @since 3.2.2
     */
    public function check_vendor_access_key_is_valid() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        // check stripe payment gateway is enabled
        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        if ( ! array_key_exists( 'dokan-stripe-connect', $available_gateways ) ) {
            return;
        }

        if ( ! dokan_is_withdraw_method_enabled( Helper::get_gateway_id() ) ) {
            return;
        }

        // check if stripe is ready
        if ( ! Helper::is_ready() ) {
            return;
        }

        // get current user id
        $seller_id = dokan_get_current_user_id();

        // check if current user is vendor
        if ( ! dokan_is_user_seller( $seller_id ) ) {
            return;
        }

        // check vendor is connected to stripe account
        $access_token = get_user_meta( $seller_id, 'dokan_connected_vendor_id', true );
        /**
         * @var $announcement Announcement
         */
        $announcement = dokan_pro()->announcement;

        if ( empty( $access_token ) ) {
            if ( Helper::display_notice_to_non_connected_sellers() && false === get_transient( "non_connected_sellers_notice_intervals_$seller_id" ) ) {
                // sent announcement message
                $args   = [
                    'title'       => __( 'Your account is not connected with Stripe. Connect your Stripe account to receive automatic payouts.', 'dokan' ),
                    'sender_type' => 'selected_seller',
                    'sender_ids'  => [ $seller_id ],
                    'status'      => 'publish',
                ];
                $notice = $announcement->create_announcement( $args );

                if ( is_wp_error( $notice ) ) {
                    dokan_log( sprintf( 'Error Creating Stripe Connect Announcement For Seller %1$s, Error Message: %2$s', $seller_id, $notice->get_error_message() ) );

                    return;
                }

                // notice is sent, now store transient
                set_transient( "non_connected_sellers_notice_intervals_$seller_id", 'sent', DAY_IN_SECONDS * Helper::non_connected_sellers_display_notice_intervals() );
            }

            return;
        }

        // check transient, we will check send notification once in a week
        if ( false !== get_transient( "dokan_check_stripe_access_key_valid_$seller_id" ) ) {
            return;
        }

        // get vendor stripe account info
        try {
            $account = Account::retrieve( $access_token );

            // check vendor account Stripe currency is same as site currency
            if ( Helper::is_3d_secure_enabled() && $account->default_currency !== strtolower( get_woocommerce_currency() ) ) {
                $args   = [
                    /* translators: 1) Three-letter ISO currency code 2) Three-letter ISO currency code */
                    'title'       => sprintf( __( 'Your connected Stripe account currency (%1$s) is different from platform default currency (%2$s). Automatic transfer is not possible.', 'dokan' ), strtoupper( $account->default_currency ), get_woocommerce_currency() ),
                    'sender_type' => 'selected_seller',
                    'sender_ids'  => [ $seller_id ],
                    'status'      => 'publish',
                ];
                $notice = $announcement->create_announcement( $args );

                if ( is_wp_error( $notice ) ) {
                    dokan_log( sprintf( 'Error Creating Stripe Connect Announcement For Seller %1$s, Error Message: %2$s', $seller_id, $notice->get_error_message() ) );

                    return;
                }

                // notice is sent, now store transient
                set_transient( "dokan_check_stripe_access_key_valid_$seller_id", 'sent', WEEK_IN_SECONDS );
            }
        } catch ( \Stripe\Exception\RateLimitException $e ) {
            // Too many requests made to the API too quickly
            dokan_log( sprintf( 'Error Retrieving Vendor Stripe Account Information: (%1$s)', $e->getMessage() ) );
        } catch ( \Stripe\Exception\InvalidRequestException $e ) {
            // Invalid parameters were supplied to Stripe's API
            dokan_log( sprintf( 'Error Retrieving Vendor Stripe Account Information: (%1$s)', $e->getMessage() ) );
        } catch ( \Stripe\Exception\AuthenticationException $e ) {
            // Authentication with Stripe's API failed
            dokan_log( sprintf( 'Error Retrieving Vendor Stripe Account Information: (%1$s)', $e->getMessage() ) );
        } catch ( \Stripe\Exception\ApiConnectionException $e ) {
            // Network communication with Stripe failed
            dokan_log( sprintf( 'Error Retrieving Vendor Stripe Account Information: (%1$s)', $e->getMessage() ) );
        } catch ( \Stripe\Exception\ApiErrorException $e ) {
            dokan_log( sprintf( 'Error Retrieving Vendor Stripe Account Information: (%1$s)', $e->getMessage() ) );

            // reset user access key so that they can connect later
            delete_user_meta( $seller_id, '_stripe_connect_access_key' );
            delete_user_meta( $seller_id, 'dokan_connected_vendor_id' );

            $args   = [
                'title'       => __( 'Your Dokan Stripe Connect access key is invalid or has been expired, hence it has been revoked. You need to reconnect your Stripe account to receive automatic payouts.', 'dokan' ),
                'sender_type' => 'selected_seller',
                'sender_ids'  => [ $seller_id ],
                'status'      => 'publish',
                'content'     => $e->getMessage(),
            ];
            $notice = $announcement->create_announcement( $args );

            if ( is_wp_error( $notice ) ) {
                dokan_log( sprintf( 'Error Creating Stripe Connect Announcement For Seller %1$s, Error Message: %2$s', $seller_id, $notice->get_error_message() ) );

                return;
            }
            // notice is sent, now store transient
            set_transient( "dokan_check_stripe_access_key_valid_$seller_id", 'sent', WEEK_IN_SECONDS );

            // we need to set this transient so that vendor doesn't receive non connect account notification immediately
            set_transient( "non_connected_sellers_notice_intervals_$seller_id", 'sent', DAY_IN_SECONDS * Helper::non_connected_sellers_display_notice_intervals() );
        } catch ( \Exception $e ) {
            dokan_log( sprintf( 'Error Retrieving Vendor Stripe Account Information: (%1$s)', $e->getMessage() ) );
        }
    }
}
