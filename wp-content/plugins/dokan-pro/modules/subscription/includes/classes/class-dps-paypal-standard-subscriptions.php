<?php

use DokanPro\Modules\Subscription\Helper;

/**
 * PayPal Standard Subscription Class.
 *
 * Filters necessary functions in the WC_Paypal class to allow for subscriptions.
 *
 * @package     Dokan Product Subscription
 * @subpackage  WC_PayPal_Standard_Subscriptions
 * @category    Class
 * @author      Sabbir Ahmed
 * @since       1.0
 */

class DPS_PayPal_Standard_Subscriptions {
    protected static $log;
    protected static $debug;
    protected static $paypal_settings;
    protected static $api_username;
    protected static $api_password;
    protected static $api_signature;
    protected static $api_endpoint;

    /**
     * Bootstraps the class and hooks required actions & filters.
     *
     * @since 1.0
     */
    public static function init() {
        if ( ! self::get_wc_paypal_settings() ) {
            return;
        }

        self::set_api_credentials();
        self::subscription_paypal_credential_verify();

        // Check paypal ipn response before woocommerce does to as it's exit PHP process on payment_status_completed or pending. See WC_Gateway_Paypal_IPN_Handler::valid_response
        add_action( 'valid-paypal-standard-ipn-request', __CLASS__ . '::process_paypal_ipn_request', 0 );
        add_filter( 'woocommerce_paypal_args', __CLASS__ . '::paypal_standard_subscription_args' );
        add_action( 'woocommerce_settings_api_form_fields_paypal', __CLASS__ . '::paypal_settings_args' );
        add_action( 'woocommerce_update_options_payment_gateways_paypal', __CLASS__ . '::save_subscription_form_fields', 11 );
    }

    /**
     * Set api credentials
     *
     * @since 2.9.10
     *
     * @return void
     */
    private static function set_api_credentials() {
        self::$paypal_settings = self::get_wc_paypal_settings();

        if ( ! empty( self::$paypal_settings['debug'] ) && 'yes' === self::$paypal_settings ) {
            self::$debug = true;
        }

        if ( ! empty( self::$paypal_settings['testmode'] ) && 'yes' === self::$paypal_settings['testmode'] ) {
            self::$api_endpoint  = 'https://api-3t.sandbox.paypal.com/nvp';
            self::$api_username  = ! empty( self::$paypal_settings['sandbox_api_username'] ) ? self::$paypal_settings['sandbox_api_username'] : '';
            self::$api_password  = ! empty( self::$paypal_settings['sandbox_api_password'] ) ? self::$paypal_settings['sandbox_api_password'] : '';
            self::$api_signature = ! empty( self::$paypal_settings['sandbox_api_signature'] ) ? self::$paypal_settings['sandbox_api_signature'] : '';
        } else {
            self::$api_endpoint = 'https://api-3t.paypal.com/nvp';
            self::$api_username  = ! empty( self::$paypal_settings['api_username'] ) ? self::$paypal_settings['api_username'] : '';
            self::$api_password  = ! empty( self::$paypal_settings['api_password'] ) ? self::$paypal_settings['api_password'] : '';
            self::$api_signature = ! empty( self::$paypal_settings['api_signature'] ) ? self::$paypal_settings['api_signature'] : '';
        }
    }

    /**
     * Set log
     *
     * @param  string $message
     *
     * @return void
     */
    private static function log( $message = '' ) {
        $paypal_settings = self::get_wc_paypal_settings();

        if ( isset( $paypal_settings['debug'] ) && $paypal_settings['debug'] === 'yes' ) {
            $logger = new WC_Logger();
            $logger->add( 'paypal', $message );
        }
    }

    /**
     * Return the default WC PayPal gateway's settings.
     */
    private static function get_wc_paypal_settings() {
        $paypal_settings = get_option( 'woocommerce_paypal_settings' );

        return $paypal_settings;
    }

    /**
     * Returns a payment gateway object by gateway's ID, or false if it could not find the gateway.
     */
    public static function get_payment_gateway( $gateway_id ) {
        $found_gateway = false;

        if ( WC()->payment_gateways ) {
            foreach ( WC()->payment_gateways->payment_gateways() as $gateway ) {
                if ( $gateway_id == $gateway->id ) {
                    $found_gateway = $gateway;
                }
            }
        }

        return $found_gateway;
    }

    /**
     * Adds extra PayPal credential fields required to manage subscriptions.
     */
    public static function paypal_settings_args( $form_fields ) {
        // Warn store managers not to change their PayPal Email address as it can break existing Subscriptions in WC2.0+
        $form_fields['email']['desc_tip'] = false;
        $form_fields['email']['description'] .= ' </p><p class="description">' . __( 'It is <strong>strongly recommended you do not change this email address</strong> if you have active subscriptions with PayPal. Doing so can break existing subscriptions.', 'dokan' );

        $form_fields += array(
            'api_credentials' => array(
                'title'       => __( 'API Credentials', 'dokan' ),
                'type'        => 'title',
                'description' => sprintf( __( 'Enter your PayPal API credentials to unlock subscription suspension and cancellation features. %1$sLearn More &raquo;%2$s', 'dokan' ), '<a href="http://docs.woothemes.com/document/store-manager-guide/#extrapaypalconfigurationsteps" target="_blank" tabindex="-1">', '</a>' ),
                'default'     => '',
            ),
            'api_username' => array(
                'title'       => __( 'API Username', 'dokan' ),
                'type'        => 'text',
                'description' => '',
                'default'     => '',
            ),
            'api_password' => array(
                'title'       => __( 'API Password', 'dokan' ),
                'type'        => 'text',
                'description' => '',
                'default'     => '',
            ),
            'api_signature' => array(
                'title'       => __( 'API Signature', 'dokan' ),
                'type'        => 'text',
                'description' => '',
                'default'     => '',
            ),
        );

        return $form_fields;
    }

    /**
     * In WC 2.0, settings are saved on a new instance of the PayPalpayment gateway, not
     * the global instance, so our admin fields are not set (nor saved). As a result, we
     * need to run the save routine @see WC_Settings_API::process_admin_options() again
     * to save our fields.
     */
    public static function save_subscription_form_fields() {
        $paypal_gateway = self::get_payment_gateway( 'paypal' );

        $paypal_gateway->process_admin_options();
    }

    /**
     * Overwrite paypal arguments
     * @param  array $paypal_args
     * @return [type]              [description]
     */
    public static function paypal_standard_subscription_args( $paypal_args ) {
        $custom      = (array) json_decode( $paypal_args['custom'] );
        $order_id    = $custom['order_id'];
        $order_key   = $custom['order_key'];
        $order       = new WC_Order( $order_id );

        // Only one subscription allowed in the cart when PayPal Standard is active
        $subs_product       = $order->get_items();
        $product            = reset( $subs_product );

        if ( ! Helper::is_subscription_product( $product['product_id'] ) ) {
            return $paypal_args;
        }

        if ( self::has_subscription() ) {
            throw new Exception( __( 'Sorry, with paypal you can\'t switch subscription plan.', 'dokan' ) );
        }

        $subscription = dokan()->subscription->get( $product['product_id'] );

        $paypal_args['cmd']       = '_xclick-subscriptions';
        $paypal_args['item_name'] = $subscription->get_package_title();

        $unconverted_periods = array(
            'billing_period' => $subscription->get_period_type(),
            'trial_period'   => $subscription->get_trial_period_types(),
        );

        $converted_periods = array();

        foreach ( $unconverted_periods as $key => $period ) {
            switch ( strtolower( $period ) ) {
                case 'day':
                    $converted_periods[ $key ] = 'D';
                    break;
                case 'week':
                    $converted_periods[ $key ] = 'W';
                    break;
                case 'year':
                    $converted_periods[ $key ] = 'Y';
                    break;
                case 'month':
                default:
                    $converted_periods[ $key ] = 'M';
                    break;
            }
        }

        // max trial period length in days for paypal
        $max_trial_length_in_days  = 720;
        $initial_payment           = $order->get_total();
        $subscription_interval     = $subscription->get_recurring_interval();
        $subscription_installments = $subscription->get_period_length();
        $trial_end_timestamp       = $subscription->get_trial_end_time();
        $subscription_trial_lenth  = $subscription->get_trial_period_length();

        // We have a recurring payment
        if ( $subscription->is_recurring() ) {
            // if vendor has already used a trial pack, then make the tiral to a normal recurring pack
            if ( ! Helper::has_used_trial_pack( get_current_user_id() ) && $subscription->is_trial() ) {
                // Trial period 1 price. For a free trial period, specify 0.
                $paypal_args['a1'] = 0;

                if ( $subscription_trial_lenth > $max_trial_length_in_days ) {
                    throw new Exception( __( 'Trial subscription can\'t be more than 720 days for PayPal', 'dokan' ) );
                }

                // trail subscription
                $paypal_args['p1'] = $subscription->get_trial_range();

                // trail period
                $paypal_args['t1'] = $converted_periods['trial_period'];

                // Subscription price
                $paypal_args['a3'] = $initial_payment;

                // Subscription duration
                $paypal_args['p3'] = $subscription_interval;

                // Subscription period
                $paypal_args['t3'] = $converted_periods['billing_period'];
            } else {
                // Subscription price
                $paypal_args['a3'] = $initial_payment;

                // Subscription duration
                $paypal_args['p3'] = $subscription_interval;

                // Subscription period
                $paypal_args['t3'] = $converted_periods['billing_period'];
            }

            /**
             * If number of subscription installments is 0 (unlimted) or greater than 1. Set `src` to 1 else make it 0.
             *
             * @see https://developer.paypal.com/docs/paypal-payments-standard/integration-guide/Appx-websitestandard-htmlvariables/?mark=srt#recurring-payment-variables
             */
            if ( 0 === $subscription_installments ) {
                // Recurring for unlimted period
                $paypal_args['src'] = 1;
            } elseif ( 1 === $subscription_installments ) {
                // One time subscription
                $paypal_args['src'] = 0;
            } elseif ( $subscription_installments > 1 && $subscription_installments <= 52 ) {
                // Recurring for certain time (number of subscription installments)
                $paypal_args['src'] = 1;
                $paypal_args['srt'] = $subscription_installments;
            } else {
                throw new Exception( __( 'Invalid subscription length', 'dokan' ) );
            }
        }

        // if non-recurring pack
        if ( ! $subscription->is_recurring() ) {
            $paypal_args['src'] = 0;

            // Subscription price
            $paypal_args['a3'] = $initial_payment;


            if ( absint( $subscription->get_pack_valid_days() ) > 0 ) {
                // Subscription duration
                $paypal_args['p3'] = $subscription->get_pack_valid_days();

                // Subscription period
                $paypal_args['t3'] = 'D';
            } else {
                // set unlimited validity for 5 years
                // Subscription duration
                $paypal_args['p3'] = '5';

                // Subscription period
                $paypal_args['t3'] = 'Y';
            }
        }

        // Force return URL so that order description & instructions display
        $paypal_args['rm'] = 2;

        return $paypal_args;
    }

    /**
     * Handle payapl IPN response
     * @param  array $transaction_details [description]
     * @return [type]                      [description]
     */
    public static function process_paypal_ipn_request( $transaction_details ) {
        global $wpdb;

        //Helper::log( 'Transaction details check: ' . print_r( $transaction_details, true ) );

        if ( ! in_array( $transaction_details['txn_type'], array( 'subscr_signup', 'subscr_payment', 'subscr_cancel', 'subscr_eot', 'subscr_failed', 'subscr_modify' ) ) ) {
            return;
        }

        if ( empty( $transaction_details['custom'] ) || empty( $transaction_details['invoice'] ) ) {
            return;
        }

        $custom      = (array) json_decode( $transaction_details['custom'] );
        $order_id    = $custom['order_id'];
        $order_key   = $custom['order_key'];

        $transaction_details['txn_type'] = strtolower( $transaction_details['txn_type'] );

        $order              = new WC_Order( $order_id );
        $subs_product       = $order->get_items();
        $product            = reset( $subs_product );

        if ( ! Helper::is_subscription_product( $product['product_id'] ) ) {
            return;
        }

        $customer_id         = get_post_meta( $order_id, '_customer_user', true );
        $vendor_subscription = dokan()->subscription->get( $product['product_id'] );
        $subs_interval       = $vendor_subscription->get_recurring_interval();
        $no_of_product_pack  = $vendor_subscription->get_number_of_products();
        $subscription_id     = $transaction_details['subscr_id'];

        if ( dokan_get_prop( $order, 'order_key' ) !== $order_key ) {
            self::log( 'Subscription IPN Error: Order Key does not match invoice.' );
            return false;
        }

        if ( isset( $transaction_details['subscr_id'] ) ) {
            update_post_meta( $order_id, '_paypal_subscriber_ID', $transaction_details['subscr_id'] );
        }

        switch ( $transaction_details['txn_type'] ) {
            case 'subscr_signup':
                // Store PayPal Details
                update_post_meta( $order_id, 'Payer PayPal address', $transaction_details['payer_email'] );
                update_post_meta( $order_id, 'Payer PayPal first name', $transaction_details['first_name'] );
                update_post_meta( $order_id, 'Payer PayPal last name', $transaction_details['last_name'] );

                $vendor_subscription->activate_subscription( $order );

                // check subscription has trial,
                if ( $vendor_subscription->is_trial() ) {
                    // translators: 1) PayPal Subscription ID
                    $order->add_order_note( sprintf( __( 'IPN subscription trial activated. Subscription ID: %s', 'dokan' ), $subscription_id ) );

                    // store trial information as user meta
                    update_user_meta( $customer_id, '_dokan_subscription_is_on_trial', 'yes' );

                    // store trial period also
                    $trial_interval_unit  = $vendor_subscription->get_trial_period_types(); //day, week, month, year
                    $trial_interval_count = absint( $vendor_subscription->get_trial_range() ); //int

                    $time = dokan_current_datetime();
                    $time = $time->modify( "$trial_interval_count $trial_interval_unit" );

                    if ( $time ) {
                        update_user_meta( $customer_id, '_dokan_subscription_trial_until', $time->format( 'Y-m-d H:i:s' ) );
                    }
                } else {
                    // translators: 1) PayPal Subscription ID
                    $order->add_order_note( sprintf( __( 'IPN subscription activated. Subscription ID: %s', 'dokan' ), $subscription_id ) );
                }

                break;

            case 'subscr_payment':
                if ( 'completed' === strtolower( $transaction_details['payment_status'] ) ) {
                    // check if this is a renewal
                    $is_renewal    = ! empty( $order->get_meta( '_dokan_paypal_payment_capture_id' ) );
                    $renewal_order = null;

                    if ( $is_renewal ) {
                        // check if transaction already recorded
                        add_filter( 'woocommerce_order_data_store_cpt_get_orders_query', __CLASS__ . '::handle_custom_query_var', 10, 2 );

                        $query = new WC_Order_Query(
                            array(
                                'search_transaction' => $transaction_details['txn_id'],
                                'customer_id'        => $order->get_customer_id(),
                                'limit'              => 1,
                                'type'               => 'shop_order',
                                'orderby'            => 'date',
                                'order'              => 'DESC',
                                'return'             => 'ids',
                            )
                        );

                        $orders = $query->get_orders();

                        remove_filter( 'woocommerce_order_data_store_cpt_get_orders_query', __CLASS__ . '::handle_custom_query_var' );

                        if ( ! empty( $orders ) ) {
                            // transaction is already recorded
                            $order->payment_complete( $transaction_details['txn_id'] );
                            return;
                        }

                        // create new renewal order
                        $renewal_order = Helper::create_renewal_order( $order );

                        if ( is_wp_error( $renewal_order ) ) {
                            dokan_log( '[PayPal] Create Renewal Order Failed. Error: ' . $renewal_order->get_error_message() );
                            return;
                        }

                        // translators: %s: order number.
                        $order_number = sprintf( _x( '#%s', 'hash before order number', 'dokan' ), $renewal_order->get_order_number() );

                        // translators: %s: order number.
                        $subscription_order_number = sprintf( _x( '#%s', 'hash before order number', 'dokan' ), $order->get_order_number() );

                        // translators: placeholder is order ID
                        $order->add_order_note( sprintf( __( 'Order %s created to record renewal.', 'dokan' ), sprintf( '<a href="%s">%s</a> ', esc_url( Helper::get_edit_post_link( $renewal_order->get_id() ) ), $order_number ) ) );

                        // add order note on renewal order
                        // translators: 1) subscription order number
                        $renewal_order->add_order_note( sprintf( __( 'Order created to record renewal subscription for %s.', 'dokan' ), sprintf( '<a href="%s">%s</a> ', esc_url( Helper::get_edit_post_link( $order->get_id() ) ), $subscription_order_number ) ) );

                        // set subscription to renewal order
                        $order = $renewal_order;
                    }

                    // Mark the order's payment as completed
                    $order->payment_complete();

                    // Subscription Payment completed
                    $order->add_order_note( sprintf( __( 'IPN subscription payment completed. Transaction ID: %s', 'dokan' ), $transaction_details['txn_id'] ) );

                    // Record payment capture id
                    $order->update_meta_data( '_dokan_paypal_payment_capture_id', $transaction_details['txn_id'] );

                    // Delete trail metas
                    delete_user_meta( $customer_id, '_dokan_subscription_is_on_trial' );
                    delete_user_meta( $customer_id, '_dokan_subscription_trial_until' );
                } elseif ( 'failed' === strtolower( $transaction_details['payment_status'] ) ) {

                    // Subscription Payment completed
                    $order->add_order_note( __( 'IPN subscription payment failed.', 'dokan' ) );

                    self::log( 'IPN subscription payment failed for order ' . $order_id );

                    // First payment on order, don't generate a renewal order
                } else {
                    self::log( 'IPN subscription payment notification received for order ' . $order_id . ' with status ' . $transaction_details['payment_status'] );
                }

                break;

            case 'subscr_cancel':
                self::log( 'IPN subscription cancelled for order ' . $order_id );

                $order->add_order_note( __( 'IPN subscription cancelled.', 'dokan' ) );

                if ( get_user_meta( $customer_id, 'product_order_id', true ) == $order_id ) {
                    Helper::log( 'Subscription cancel check: PayPal ( subscr_cancel ) has canceled Subscription of User #' . $customer_id . ' on order #' . $order_id );
                    Helper::delete_subscription_pack( $customer_id, $order_id );
                }

                break;

            case 'subscr_eot':
                // if not recurring product, return from here
                if ( ! $vendor_subscription->is_recurring() ) {
                    break;
                }

                $subscription_length = $vendor_subscription->get_period_length();

                // PayPal fires the 'subscr_eot' notice immediately if a subscription is only for one billing period, so ignore the request when we only have one billing period
                if ( 1 === $subscription_length ) {
                    break;
                }

                // cancel subscription after end of billing period
                self::log( 'IPN subscription end-of-term for order ' . $order_id );

                // Record subscription ended
                $order->add_order_note( __( 'IPN subscription end-of-term for order.', 'dokan' ) );

                // Ended due to failed payments so cancel the subscription
                Helper::log( 'Subscription cancel check: PayPal ( subscr_eot ) has canceled Subscription of User #' . $customer_id . ' on order #' . $order_id );
                Helper::delete_subscription_pack( $customer_id, $order_id );

                break;

            case 'subscr_failed':
                self::log( 'IPN subscription payment failure for order ' . $order_id );

                // Subscription Payment completed
                $order->add_order_note( __( 'IPN subscription payment failure.', 'dokan' ) );

                // First payment on order, don't generate a renewal order
                Helper::log( 'Subscription cancel check: PayPal ( subscr_failed ) has canceled Subscription of User #' . $customer_id . ' on order #' . $order_id );
                Helper::delete_subscription_pack( $customer_id, $order_id );

                break;
        }

        // Prevent default IPN handling for subscription txn_types
        exit;
    }

    /**
     * Handles custom query variables
     *
     * @since 3.4.3
     *
     * @param array $query
     * @param array $query_vars
     *
     * @return array
     */
    public static function handle_custom_query_var( $query, $query_vars ) {
        if ( ! empty( $query_vars['search_transaction'] ) ) {
            $query['meta_query'][] = [
                'key'       => '_dokan_paypal_payment_capture_id',
                'value'     => $query_vars['search_transaction'],
                'compare'   => '=',
            ];
        }

        return $query;
    }

    /**
     * When a store manager or user cancels a subscription in the store, also cancel the subscription with PayPal.
     */
    public static function cancel_subscription_with_paypal( $order_id, $user_id ) {
        $order        = new WC_Order( $order_id );
        $profile_id   = get_post_meta( $order->get_id(), '_paypal_subscriber_ID', true );

        // Make sure a subscriptions status is active with PayPal
        $response = self::change_subscription_status( $profile_id, 'Cancel' );

        if ( $response ) {
            update_user_meta( $user_id, '_dps_user_subscription_status', 'cancelled' );
            Helper::delete_subscription_pack( $user_id, $order_id );
            delete_user_meta( $user_id, '_paypal_subscriber_ID' );

            $order->add_order_note( __( 'Subscription cancelled with PayPal', 'dokan' ) );
        }
    }

    /**
     * Performs an Express Checkout NVP API operation as passed in $api_method.
     *
     * Although the PayPal Standard API provides no facility for cancelling a subscription, the PayPal
     * Express Checkout  NVP API can be used.
     */
    public static function change_subscription_status( $profile_id, $new_status ) {
        switch ( $new_status ) {
            case 'Cancel':
                $new_status_string = __( 'cancelled', 'dokan' );
                break;
            case 'Suspend':
                $new_status_string = __( 'suspended', 'dokan' );
                break;
            case 'Reactivate':
                $new_status_string = __( 'reactivated', 'dokan' );
                break;
        }

        $request = wp_remote_post(
            self::$api_endpoint, array(
                'timeout'   => 15,
                'sslverify' => false,
                'body'      => array(
                    'USER'      => self::$api_username,
                    'PWD'       => self::$api_password,
                    'SIGNATURE' => self::$api_signature,
                    'VERSION'   => '76.0',
                    'METHOD'    => 'ManageRecurringPaymentsProfileStatus',
                    'PROFILEID' => $profile_id,
                    'ACTION'    => $new_status,
                    'NOTE'      => sprintf( __( 'Subscription %1$s at %2$s', 'dokan' ), $new_status_string, get_bloginfo( 'name' ) ),
                ),
            )
        );

        if ( is_wp_error( $request ) || $request['response']['code'] != 200 ) {
            self::log( 'Subscription Cancel - HTTP error' );
            return false;
        }

        $response = wp_remote_retrieve_body( $request );
        parse_str( $response, $parsed_response );

        if ( isset( $parsed_response['ACK'] ) && $parsed_response['ACK'] == 'Failure' ) {
            self::log( $parsed_response['L_LONGMESSAGE0'] );
            return false;
        }

        if ( isset( $parsed_response['ACK'] ) && $parsed_response['ACK'] == 'Success' ) {
            return true;
        }

        return false;
    }

    /**
     * Check for paypal information and save logs
     *
     * @since  [1.1.4]
     * @return set $debug,$log and $endpoint variables
     */
    public static function subscription_paypal_credential_verify() {
        $paypal_settings = self::get_wc_paypal_settings();

        if ( $paypal_settings && ! isset( $paypal_settings['debug'] ) ) {

            function dokan_paypal_credential_error() {
                ?>
                <div id="message" class="error notice is-dismissible">
                    <p><?php esc_html_e( 'Your Paypal Credentials are not complete', 'dokan' ); ?>.</p>
                    <button type="button" class="notice-dismiss">
                        <span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'dokan' ); ?></span>
                    </button>
                </div>
                <?php
            }
            add_action( 'admin_notices', 'dokan_paypal_credential_error' );
        } else {
            self::$debug = ( $paypal_settings['debug'] === 'yes' ) ? true : false;
            self::$log = ( self::$debug ) ? new WC_Logger() : '';
            self::$api_endpoint = ( $paypal_settings['testmode'] === 'no' ) ? 'https://api-3t.paypal.com/nvp' : 'https://api-3t.sandbox.paypal.com/nvp';
        }
    }

    /**
     * Check whether vendor has subscriptoin or not
     *
     * @since 2.9.13
     *
     * @return boolean
     */
    public static function has_subscription() {
        $vendor_id = dokan_get_current_user_id();
        $vendor    = dokan()->vendor->get( $vendor_id )->subscription;

        if ( ! $vendor ) {
            return false;
        }

        return $vendor->has_subscription();
    }
}

DPS_PayPal_Standard_Subscriptions::init();
