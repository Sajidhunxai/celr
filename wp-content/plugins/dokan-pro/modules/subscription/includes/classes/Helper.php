<?php

namespace DokanPro\Modules\Subscription;

use DokanPro\Modules\Subscription\SubscriptionPack;
use WeDevs\Dokan\Product\ProductCache;
use WeDevs\Dokan\Traits\Singleton;
use WeDevs\DokanPro\Modules\Subscription\HelperChangerProductStatus;

/**
 * DPS Helper Class
 */
class Helper {

    use Singleton;

    /**
     * Get a sellers remaining product count
     *
     * @param int $vendor_id
     *
     * @return int|bool integer number (could be zero), boolean true if module is deactivated or vendor can publish unlimited product
     */
    public static function get_vendor_remaining_products( $vendor_id ) {
        // vendor subscription module is deactivated, so return true
        if ( ! self::is_subscription_module_enabled() ) {
            return true;
        }

        $vendor = dokan()->vendor->get( $vendor_id )->subscription;

        if ( ! $vendor ) {
            return 0;
        }

        $remaining_products = $vendor->get_remaining_products();

        // check if venddor can upload unlimited products
        if ( '-1' === $remaining_products ) {
            return true;
        }

        return $remaining_products;
    }

    /**
     * Check if its vendor subscribed pack
     *
     * @param integer $product_id
     *
     * @throws \Exception
     * @return boolean
     */
    public static function is_vendor_subscribed_pack( $product_id ) {
        $user_id              = get_current_user_id();
        $current_date         = dokan_current_datetime();
        $product_pack_enddate = self::get_pack_end_date( $user_id );
        $product_package_id   = get_user_meta( $user_id, 'product_package_id', true );

        // if product_id is not same as current purchased package id, return false
        if ( (int) $product_package_id !== (int) $product_id ) {
            return false;
        }

        if ( empty( $product_pack_enddate ) ) {
            return false;
        }

        if ( $product_pack_enddate === 'unlimited' ) {
            return true;
        }

        $validation_date = $current_date->modify( $product_pack_enddate );
        if ( $current_date < $validation_date ) {
            return true;
        }

        return false;
    }

    /**
     * Check package renew for seller
     *
     * @param integer $product_id
     *
     * @throws \Exception
     * @return boolean
     */
    public static function pack_renew_seller( $product_id ) {
        $user_id              = dokan_get_current_user_id(); // in case user is vendor staff, we need vendor user id
        $current_date         = dokan_current_datetime();
        $product_pack_enddate = self::get_pack_end_date( $user_id );
        $product_package_id   = get_user_meta( $user_id, 'product_package_id', true );

        // if product_id is not same as current purchased package id, return false
        if ( (int) $product_package_id !== (int) $product_id ) {
            return false;
        }

        if ( empty( $product_pack_enddate ) ) {
            return false;
        }

        // if product pack end date is unlimited, user does not need to renew their package
        if ( $product_pack_enddate === 'unlimited' ) {
            return false;
        }

        $validation_date = $current_date->modify( $product_pack_enddate );
        if ( $current_date > $validation_date ) {
            return true;
        }

        return false;
    }


    /**
     * Returns a readable recurring period
     *
     * @param string $period
     *
     * @return string
     */
    public static function recurring_period( $period, $length = 1 ) {
        switch ( $period ) {
            case 'day':
                return (int) $length === 1 ? __( 'day', 'dokan' ) : __( 'days', 'dokan' );

            case 'week':
                return (int) $length === 1 ? __( 'week', 'dokan' ) : __( 'weeks', 'dokan' );

            case 'month':
                return (int) $length === 1 ? __( 'month', 'dokan' ) : __( 'months', 'dokan' );

            case 'year':
                return (int) $length === 1 ? __( 'year', 'dokan' ) : __( 'years', 'dokan' );

            default:
                return apply_filters( 'dps_recurring_text', $period );
        }
    }

    /**
     * Get a list of options of all the product types
     *
     * @return strings
     */
    public static function get_product_types_options() {
        $selected      = dokan()->subscription->get( get_the_ID() )->get_allowed_product_types();
        $product_types = dokan_get_product_types();
        $output        = '';

        if ( ! $product_types ) {
            return;
        }

        foreach ( $product_types as $value => $label ) {
            $output .= '<option value="' . esc_attr( $value ) . '" ';
            $output .= in_array( $value, $selected ) ? ' selected="selected"' : '';
            $output .= '>' . esc_html( $label ) . '</option>';
        }

        echo $output;
    }

    /**
     * Get a list of options for trail period
     *
     * @return string
     */
    public static function get_trial_period_options() {
        $subscription   = dokan()->subscription->get( get_the_ID() );
        $selected_range = $subscription->get_trial_range();
        $range_output   = '';
        $period_range   = range( 1, 30 );
        $range_output   .= '<select name="dokan_subscription_trail_range" class="dokan-subscription-range" style="margin-right: 10px">';

        foreach ( $period_range as $range ) {
            $range_output .= '<option value="' . esc_attr( $range ) . '"';
            $range_output .= selected( $selected_range, $range, false );
            $range_output .= '>' . __( $range, 'dokan' ) . '</option>';
        }

        $range_output .= '</select>';
        echo $range_output;

        $selected_period_types = $subscription->get_trial_period_types();
        $period_types_output   = '';
        $period_types          = apply_filters(
            'dokan_subscription_trial_period_types', [
                'day'   => __( 'Day(s)', 'dokan' ),
                'week'  => __( 'Week(s)', 'dokan' ),
                'month' => __( 'Month(s)', 'dokan' ),
                'year'  => __( 'Year(s)', 'dokan' ),
            ]
        );

        $period_types_output .= '<select name="dokan_subscription_trial_period_types">';

        foreach ( $period_types as $key => $value ) {
            $period_types_output .= '<option value="' . esc_attr( $key ) . '"';
            $period_types_output .= selected( $selected_period_types, $key, false );
            $period_types_output .= '>' . $value . '</option>';
        }

        $period_types_output .= '</select>';
        echo $period_types_output;
    }

    /**
     * Get vendor subscription pack id
     *
     * @return int|null on failure
     */
    public static function get_subscription_pack_id() {
        $user_id = dokan_get_current_user_id();

        if ( ! $user_id || ! dokan_is_user_seller( $user_id ) ) {
            return null;
        }

        $subscription_pack_id = get_user_meta( $user_id, 'product_package_id', true );

        if ( ! $subscription_pack_id ) {
            return null;
        }

        return $subscription_pack_id;
    }

    /**
     * Is gallary image upload restricted
     *
     * @return boolean
     */
    public static function is_gallery_image_upload_restricted() {
        return get_post_meta( self::get_subscription_pack_id(), '_enable_gallery_restriction', true );
    }

    /**
     * Get allowed product types of a vendor
     *
     * @return array|empty array on failure
     */
    public static function get_vendor_allowed_product_types() {
        $types  = [];
        $vendor = dokan()->vendor->get( dokan_get_current_user_id() )->subscription;

        if ( $vendor ) {
            $types = $vendor->get_allowed_product_types();
        }

        return $types ? $types : [];
    }

    /**
     * Get allowed product cateogories of a vendor
     *
     * @return array|empty array on failure
     */
    public static function get_vendor_allowed_product_categories() {
        $categories = [];

        $vendor = dokan()->vendor->get( dokan_get_current_user_id() )->subscription;

        if ( $vendor ) {
            $categories = $vendor->get_allowed_product_categories();
        }

        return $categories;
    }

    /**
     * Get subscription recurring interval strings
     *
     * @return string
     */
    public static function get_subscription_period_interval_strings( $interval = '' ) {
        $intervals = [ 1 => _x( 'every', 'period interval (eg "$10 _every_ 2 weeks")', 'dokan' ) ];

        foreach ( range( 2, 6 ) as $i ) {
            // translators: period interval, placeholder is ordinal (eg "$10 every _2nd/3rd/4th_", etc)
            $intervals[ $i ] = sprintf( _x( 'every %s', 'period interval with ordinal number (e.g. "every 2nd"', 'dokan' ), self::append_numeral_suffix( $i ) );
        }

        $intervals = apply_filters( 'dokan_pro_subscription_period_interval_strings', $intervals );

        if ( empty( $interval ) ) {
            return $intervals;
        } else {
            return $intervals[ $interval ];
        }
    }

    /**
     * Takes a number and returns the number with its relevant suffix appended, eg. for 2, the function returns 2nd
     *
     * @since 1.0
     */
    public static function append_numeral_suffix( $number ) {

        // Handle teens: if the tens digit of a number is 1, then write "th" after the number. For example: 11th, 13th, 19th, 112th, 9311th. http://en.wikipedia.org/wiki/English_numerals
        if ( strlen( $number ) > 1 && 1 == substr( $number, -2, 1 ) ) { //phpcs:ignore
            // translators: placeholder is a number, this is for the teens
            $number_string = sprintf( __( '%sth', 'dokan' ), $number );
        } else { // Append relevant suffix
            switch ( substr( $number, -1 ) ) {
                case 1:
                    // translators: placeholder is a number, numbers ending in 1
                    $number_string = sprintf( __( '%sst', 'dokan' ), $number );
                    break;
                case 2:
                    // translators: placeholder is a number, numbers ending in 2
                    $number_string = sprintf( __( '%snd', 'dokan' ), $number );
                    break;
                case 3:
                    // translators: placeholder is a number, numbers ending in 3
                    $number_string = sprintf( __( '%srd', 'dokan' ), $number );
                    break;
                default:
                    // translators: placeholder is a number, numbers ending in 4-9, 0
                    $number_string = sprintf( __( '%sth', 'dokan' ), $number );
                    break;
            }
        }

        return apply_filters( 'woocommerce_numeral_suffix', $number_string, $number );
    }


    /**
     * Returns an array of subscription lengths.
     *
     * PayPal Standard Allowable Ranges
     * D – for days; allowable range is 1 to 90
     * W – for weeks; allowable range is 1 to 52
     * M – for months; allowable range is 1 to 24
     * Y – for years; allowable range is 1 to 5
     *
     * @since 3.2.0
     */
    public static function get_non_cached_subscription_ranges() {
        foreach ( [ 'day', 'week', 'month', 'year' ] as $period ) {
            $subscription_lengths = [
                _x( 'Never expire', 'Subscription length', 'dokan' ),
            ];

            switch ( $period ) {
                case 'day':
                    $subscription_lengths[] = _x( '1 day', 'Subscription lengths. e.g. "For 1 day..."', 'dokan' );
                    $subscription_range     = range( 2, 90 );
                    break;
                case 'week':
                    $subscription_lengths[] = _x( '1 week', 'Subscription lengths. e.g. "For 1 week..."', 'dokan' );
                    $subscription_range     = range( 2, 52 );
                    break;
                case 'month':
                    $subscription_lengths[] = _x( '1 month', 'Subscription lengths. e.g. "For 1 month..."', 'dokan' );
                    $subscription_range     = range( 2, 24 );
                    break;
                case 'year':
                    $subscription_lengths[] = _x( '1 year', 'Subscription lengths. e.g. "For 1 year..."', 'dokan' );
                    $subscription_range     = range( 2, 5 );
                    break;
            }

            foreach ( $subscription_range as $number ) {
                $subscription_range[ $number ] = self::get_subscription_period_strings( $number, $period );
            }

            // Add the possible range to all time range
            $subscription_lengths += $subscription_range;

            $subscription_ranges[ $period ] = $subscription_lengths;
        }

        return $subscription_ranges;
    }

    /**
     * Return an i18n'ified associative array of all possible subscription periods.
     *
     * @since 2.0
     *
     * @param string (optional) One of day, week, month or year. If empty, all subscription ranges are returned.
     * @param int (optional) An interval in the range 1-6
     *
     * @return string|array
     */
    public static function get_subscription_period_strings( $number = 1, $period = '' ) {
        // phpcs:disable Generic.Functions.FunctionCallArgumentSpacing.TooMuchSpaceAfterComma
        $translated_periods = apply_filters(
            'dokan_pro_subscription_periods',
            [
                // translators: placeholder is number of days. (e.g. "Bill this every day / 4 days")
                'day'   => sprintf( _nx( 'day', '%s days', $number, 'Subscription billing period.', 'dokan' ), $number ), // phpcs:ignore WordPress.WP.I18n.MissingSingularPlaceholder,WordPress.WP.I18n.MismatchedPlaceholders
                // translators: placeholder is number of weeks. (e.g. "Bill this every week / 4 weeks")
                'week'  => sprintf( _nx( 'week', '%s weeks', $number, 'Subscription billing period.', 'dokan' ), $number ), // phpcs:ignore WordPress.WP.I18n.MissingSingularPlaceholder,WordPress.WP.I18n.MismatchedPlaceholders
                // translators: placeholder is number of months. (e.g. "Bill this every month / 4 months")
                'month' => sprintf( _nx( 'month', '%s months', $number, 'Subscription billing period.', 'dokan' ), $number ), // phpcs:ignore WordPress.WP.I18n.MissingSingularPlaceholder,WordPress.WP.I18n.MismatchedPlaceholders
                // translators: placeholder is number of years. (e.g. "Bill this every year / 4 years")
                'year'  => sprintf( _nx( 'year', '%s years', $number, 'Subscription billing period.', 'dokan' ), $number ), // phpcs:ignore WordPress.WP.I18n.MissingSingularPlaceholder,WordPress.WP.I18n.MismatchedPlaceholders
            ],
            $number
        );

        // phpcs:enable

        return ( ! empty( $period ) ) ? $translated_periods[ $period ] : $translated_periods;
    }

    /**
     * Retaining the API, it makes use of the transient functionality.
     *
     * @param string $period
     *
     * @return bool|mixed
     */
    public static function get_subscription_ranges( $subscription_period = '' ) {
        static $dokan_subscription_locale_ranges = [];

        if ( ! is_string( $subscription_period ) ) {
            $subscription_period = '';
        }

        $locale = function_exists( 'get_user_locale' ) ? get_user_locale() : get_locale();

        if ( ! isset( $dokan_subscription_locale_ranges[ $locale ] ) ) {
            $dokan_subscription_locale_ranges[ $locale ] = self::get_non_cached_subscription_ranges();
        }

        $subscription_ranges = apply_filters( 'woocommerce_subscription_lengths', $dokan_subscription_locale_ranges[ $locale ], $subscription_period );

        if ( ! empty( $subscription_period ) ) {
            return $subscription_ranges[ $subscription_period ];
        } else {
            return $subscription_ranges;
        }
    }

    /**
     * Is subscription module is enabled
     *
     * @return boolean
     */
    public static function is_subscription_module_enabled() {
        $is_enabled = dokan_get_option( 'enable_pricing', 'dokan_product_subscription' );

        return 'on' === $is_enabled ? true : false;
    }

    /**
     * Is subscription is enalbed on registration
     *
     * @return boolean
     */
    public static function is_subscription_enabled_on_registration() {
        $is_enabled = dokan_get_option( 'enable_subscription_pack_in_reg', 'dokan_product_subscription' );

        return 'on' === $is_enabled ? true : false;
    }

    /**
     * Check wheter the pack is recurring or not
     *
     * @since 2.9.13
     *
     * @param int $pack_id
     *
     * @return boolean
     */
    public static function is_recurring_pack( $pack_id ) {
        $subscription = new SubscriptionPack( $pack_id );

        return $subscription->is_recurring();
    }

    /**
     * Check is product is subscription or not
     *
     * @param integer $product_id
     *
     * @return boolean
     */
    public static function is_subscription_product( $product_id ) {
        $product = wc_get_product( $product_id );

        if ( $product && 'product_pack' === $product->get_type() ) {
            return true;
        }

        return false;
    }

    /**
     * Check is product is a recurring subscription product or not
     *
     * @since 3.3.7
     *
     * @param integer $product_id
     *
     * @return boolean
     */
    public static function is_recurring_subscription_product( $product_id ) {
        return self::is_subscription_product( $product_id ) && self::is_recurring_pack( $product_id );
    }

    /**
     * Checks the cart to see if it contains a subscription product
     *
     * @return bool
     */
    public static function cart_contains_subscription() {
        $contains_subscription = false;

        if ( ! empty( WC()->cart->cart_contents ) ) {
            foreach ( WC()->cart->cart_contents as $cart_item ) {
                if ( self::is_subscription_product( $cart_item['product_id'] ) ) {
                    $contains_subscription = true;
                    break;
                }
            }
        }

        return $contains_subscription;
    }

    /**
     * Check if Cart contains recurring subscription product
     *
     * @since 3.3.7
     *
     * @return int 0 if no recurring subscription product found, else recurring subscription product id
     */
    public static function cart_contains_recurring_subscription_product() {
        $contains_recurring = 0;

        if ( ! empty( WC()->cart->cart_contents ) ) {
            foreach ( WC()->cart->cart_contents as $cart_item ) {
                if ( self::is_subscription_product( $cart_item['product_id'] ) && self::is_recurring_pack( $cart_item['product_id'] ) ) {
                    $contains_recurring = absint( $cart_item['product_id'] );
                    break;
                }
            }
        }

        return $contains_recurring;
    }

    /**
     * Get subscription product from an order
     *
     * @since 3.3.7
     *
     * @param \WC_Order $order
     *
     * @return \WC_Product|bool|null
     */
    public static function get_vendor_subscription_product_by_order( $order ) {
        if ( ! is_a( $order, 'WC_Abstract_Order' ) ) {
            $order = wc_get_order( $order );
        }

        if ( ! $order ) {
            return false;
        }

        foreach ( $order->get_items( 'line_item' ) as $item ) {
            $product = $item->get_product();

            if ( $product && 'product_pack' === $product->get_type() ) {
                return $product;
            }
        }

        return false;
    }

    /**
     * Check if the order is a subscription order
     *
     * @since 3.3.7
     *
     * @param \WC_Order|int $order
     *
     * @return bool
     **/
    public static function is_vendor_subscription_order( $order ) {
        if ( ! is_a( $order, 'WC_Abstract_Order' ) ) {
            $order = wc_get_order( $order );
        }

        if ( ! $order ) {
            return false;
        }

        // check if  meta exists
        /**
         * @since 3.3.7
         */
        if ( 'yes' === $order->get_meta( '_dokan_vendor_subscription_order' ) ) {
            return true;
        }

        // check from order items
        $product = static::get_vendor_subscription_product_by_order( $order );

        return $product ? true : false;
    }

    /**
     * Removes all subscription products from the shopping cart.
     *
     * @return void
     */
    public static function remove_subscriptions_from_cart() {
        foreach ( WC()->cart->cart_contents as $cart_item_key => $cart_item ) {
            if ( self::is_subscription_product( $cart_item['product_id'] ) ) {
                WC()->cart->set_quantity( $cart_item_key, 0 );
            }
        }
    }

    /**
     * Helper function for loggin
     *
     * @param string $message
     */
    public static function log( $message ) {
        $message = sprintf( '[%s] %s: %s', date( 'd.m.Y h:i:s' ), __( 'Dokan Vendor Subscription: ', 'dokan' ), $message );
        dokan_log( $message );
    }

    /**
     * Delete Subscription pack
     *
     * @param integer $customer_id
     *
     * @return void
     */
    public static function delete_subscription_pack( $customer_id, $order_id ) {
        if ( absint( $order_id ) !== absint( get_user_meta( $customer_id, 'product_order_id', true ) ) ) {
            return;
        }

        /**
         * @since 3.3.7 added $order_id as hook argument
         */
        do_action( 'dokan_subscription_cancelled', $customer_id, get_user_meta( $customer_id, 'product_package_id', true ), $order_id );

        delete_user_meta( $customer_id, 'product_order_id' );
        delete_user_meta( $customer_id, 'product_pack_enddate' );
        delete_user_meta( $customer_id, 'product_package_id' );
        delete_user_meta( $customer_id, 'product_no_with_pack' );
        delete_user_meta( $customer_id, 'product_pack_startdate' );
        delete_user_meta( $customer_id, 'can_post_product' );
        delete_user_meta( $customer_id, 'dokan_admin_percentage' );
        delete_user_meta( $customer_id, 'dokan_has_active_cancelled_subscrption' );
        delete_user_meta( $customer_id, 'dokan_vendor_subscription_cancel_email' );
        delete_user_meta( $customer_id, '_paypal_subscriber_ID' );
        delete_user_meta( $customer_id, '_customer_recurring_subscription' );
        delete_user_meta( $customer_id, '_dokan_paypal_marketplace_vendor_subscription_id' );
        delete_user_meta( $customer_id, '_dokan_subscription_is_on_trial' );
        delete_user_meta( $customer_id, '_dokan_subscription_trial_until' );
        delete_user_meta( $customer_id, '_stripe_subscription_id' );

        // make product status draft after subscriptions is got cancelled.
        if ( self::check_vendor_has_existing_product( $customer_id ) ) {
            self::make_product_draft( $customer_id );
        }
    }

    /**
     * Check if a vendor has existing product
     *
     * @param int $user_id
     *
     * @return boolean
     */
    public static function check_vendor_has_existing_product( $user_id ) {
        self::log( 'Product exist check: As the package has expired of user #' . $user_id . ' we are checking if he has any product' );

        $product = wc_get_products(
            [
                'author' => $user_id,
                'status' => 'any',
                'limit'  => 1,
            ]
        );

        return ! empty( $product );
    }

    /**
     * Make product status publish
     *
     * @since 3.7.21 added background process support
     *
     * @param int $vendor_id
     *
     * @return void
     */
    public static function make_product_publish( $vendor_id ) {
        $product_status_changer = Helper::get_product_status_changer_bg_class();
        if ( null === $product_status_changer ) {
            return;
        }

        $product_status_changer->reset();
        $product_status_changer->set_vendor_id( $vendor_id );
        $product_status_changer->add_to_queue( 'revert', 'publish' );
    }

    /**
     * Upadate Product Status
     *
     * @since 3.7.21 added background process support
     *
     * @param int $vendor_id
     *
     * @return void
     */
    public static function make_product_draft( $vendor_id ) {
        $product_status_changer = Helper::get_product_status_changer_bg_class();
        if ( null === $product_status_changer ) {
            return;
        }

        $status = dokan_get_option( 'product_status_after_end', 'dokan_product_subscription', 'draft' );
        $product_status_changer->reset();
        $product_status_changer->set_vendor_id( $vendor_id );
        $product_status_changer->add_to_queue( 'change_status', $status );
    }

    /**
     * Alert before 2 days end of subscription
     *
     * @return boolean
     */
    public static function alert_before_two_days( $user_id ) {
        // check if email already sent to client
        if ( 'yes' === get_user_meta( $user_id, 'dokan_vendor_subscription_cancel_email', true ) ) {
            return false;
        }

        // if product pack end date is unlimited return false
        if ( 'unlimited' === self::get_pack_end_date( $user_id ) ) {
            return false;
        }

        $alert_days = dokan_get_option( 'no_of_days_before_mail', 'dokan_product_subscription' );

        if ( (int) $alert_days === 0 ) {
            $alert_days = 2;
        }

        $current_date = dokan_current_datetime();
        $alert_date   = dokan_current_datetime()->modify( self::get_pack_end_date( $user_id ) )->modify( "- $alert_days days" );

        if ( $current_date >= $alert_date ) {
            return true;
        }

        return false;
    }

    /**
     * Get pack end date
     *
     * @return string
     */
    public static function get_pack_end_date( $vendor_id ) {
        return get_user_meta( $vendor_id, 'product_pack_enddate', true );
    }

    /**
     * Update can_post_product flag on subscripton expire
     *
     * @return boolean
     */
    public static function maybe_cancel_subscription( $vendor_id ) {
        if ( 'unlimited' === self::get_pack_end_date( $vendor_id ) ) {
            return false;
        }

        $current_date    = dokan_current_datetime();
        $validation_date = $current_date->modify( self::get_pack_end_date( $vendor_id ) );

        if ( $current_date > $validation_date ) {
            self::log( 'Subscription validity check ( ' . $current_date->format( 'Y-m-d' ) . ' ): checking subscription pack validity of user #' . $vendor_id . '. This users subscription pack will expire on ' . $validation_date->format( 'Y-m-d' ) );

            return true;
        }

        return false;
    }

    /**
     * Determine if the user has used a free pack before
     *
     * @param int $user_id
     *
     * @return boolean
     */
    public static function has_used_trial_pack( $user_id ) {
        $has_used = get_user_meta( $user_id, 'dokan_used_trial_pack', true );

        if ( ! $has_used ) {
            return false;
        }

        return true;
    }

    /**
     * Add used trial pack
     *
     * @param int $user_id
     * @param int $pack_id
     *
     * @return void
     */
    public static function add_used_trial_pack( $user_id, $pack_id ) {
        $subscription = dokan()->subscription->get( $pack_id );

        if ( empty( $subscription->pack_id ) ) {
            return;
        }

        if ( ! $subscription->is_trial() ) {
            return;
        }

        update_user_meta( $user_id, 'dokan_used_trial_pack', true );
    }

    /**
     * Check wheter vendor is subscribed or not
     *
     * @since 2.9.13
     *
     * @param int $vendor_id
     *
     * @return boolean
     */
    public static function vendor_has_subscription( $vendor_id ) {
        return get_user_meta( $vendor_id, 'product_package_id', true );
    }

    /**
     * Check wheter vendor can publish unlimited products or not
     *
     * @since 2.9.13
     *
     * @param int $vendor_id
     *
     * @return boolean
     */
    public static function vendor_can_publish_unlimited_products( $vendor_id ) {
        return true === self::get_vendor_remaining_products( $vendor_id );
    }

    /**
     * Create New Order From Parent Order
     *
     * @since 3.3.7
     *
     * @param null|float $order_total
     *
     * @param \WC_Order  $parent_order
     *
     * @return \WC_Order|\WP_Error
     */
    public static function create_renewal_order( $parent_order, $order_total = null ) {
        if ( ! is_a( $parent_order, 'WC_Abstract_Order' ) ) {
            $parent_order = wc_get_order( $parent_order );
        }

        global $wpdb;

        try {
            $wpdb->query( 'START TRANSACTION' );

            $new_order = wc_create_order(
                [
                    'customer_id'   => $parent_order->get_customer_id(),
                    'customer_note' => $parent_order->get_customer_note(),
                    'created_via'   => 'vendor_subscription',
                    'parent'        => $parent_order->get_id(),
                ]
            );

            // Copy over line items and allow extensions to add/remove items or item meta
            foreach ( $parent_order->get_items( [ 'line_item' ] ) as $item_index => $item ) {
                // Create order line item on the renewal order
                $order_item_id = wc_add_order_item(
                    $new_order->get_id(), [
                        'order_item_name' => $item['name'],
                        'order_item_type' => $item['type'],
                    ]
                );

                // Remove recurring line items and set item totals based on recurring line totals
                $order_item = $new_order->get_item( $order_item_id );

                $order_item->set_props(
                    [
                        'product_id' => $item->get_product_id(),
                        'quantity'   => $item->get_quantity(),
                        'tax_class'  => $item->get_tax_class(),

                    ]
                );

                $order_item->save();
            }

            // copy order billing address
            $meta_query = $wpdb->prepare(
                "SELECT `meta_key`, `meta_value`
                 FROM {$wpdb->postmeta}
                 WHERE `post_id` = %d
                 AND ( `meta_key` LIKE '_billing_%%' OR `meta_key` = '_dokan_vendor_id' )",
                [ $parent_order->get_id() ]
            );

            $meta = $wpdb->get_results( $meta_query, 'ARRAY_A' );

            foreach ( $meta as $meta_item ) {
                /*
                 * For setting internal billing meta data, using setter methods is recommended.
                 * The metta setter methods are typically named. For example, the setter
                 * method for `_billing_email` meta is `set_billing_email`
                 */
                if ( is_callable( [ $new_order, "set{$meta_item['meta_key']}" ] ) ) {
                    call_user_func( [ $new_order, "set{$meta_item['meta_key']}" ], $meta_item['meta_value'] );
                } else {
                    $new_order->update_meta_data( $meta_item['meta_key'], $meta_item['meta_value'] );
                }
            }
            $new_order->save_meta_data();

            // copy payment gateway data
            $new_order->set_currency( $parent_order->get_currency() );
            $new_order->set_payment_method( $parent_order->get_payment_method() );
            $new_order->set_payment_method_title( $parent_order->get_payment_method_title() );

            // set order total
            if ( null !== $order_total ) {
                $new_order->set_total( $order_total );
            } else {
                $new_order->set_total( $parent_order->get_total( 'edit' ) );
            }

            $new_order->save();
            // If we got here, the subscription was created without problems
            $wpdb->query( 'COMMIT' );

            return $new_order;
        } catch ( \Exception $e ) {
            // There was an error adding the subscription
            $wpdb->query( 'ROLLBACK' );

            return new \WP_Error( 'new-order-error', $e->getMessage() );
        }
    }

    /**
     * Inserts a new key/value after the key in the array.
     *
     * @param array  $needle    The array key to insert the element after
     * @param array  $haystack  An array to insert the element into
     * @param string $new_key   The key to insert
     * @param string $new_value An value to insert
     *
     * @return array new array if the $needle key exists, otherwise an unmodified $haystack
     */
    public static function array_insert_after( $needle, $haystack, $new_key, $new_value ) {
        if ( array_key_exists( $needle, $haystack ) ) {
            $new_array = [];

            foreach ( $haystack as $key => $value ) {
                $new_array[ $key ] = $value;

                if ( $key === $needle ) {
                    $new_array[ $new_key ] = $new_value;
                }
            }

            return $new_array;
        }

        return $haystack;
    }

    /**
     * Generates post edit link
     *
     * @since 3.4.3
     *
     * @param integer $post_id
     *
     * @return string
     */
    public static function get_edit_post_link( $post_id = null ) {
        $post = get_post( $post_id );
        $link = '';

        if ( ! $post ) {
            return $link;
        }

        $post_type_object = get_post_type_object( $post->post_type );
        if ( ! $post_type_object ) {
            return $link;
        }

        if ( $post_type_object->_edit_link ) {
            $link = admin_url( sprintf( $post_type_object->_edit_link . '&action=edit', $post->ID ) );
        }

        return $link;
    }

    /**
     * Handle Subscription Activation on trial.
     *
     * Before calling this function, must confirms that the subscription is on trial.
     *
     * @since 3.4.3
     *
     * @param \WC_Order        $order
     * @param SubscriptionPack $subscription
     * @param string           $subscription_id
     *
     * @return void
     */
    public static function activate_trial_subscription( \WC_Order $order, SubscriptionPack $subscription, $subscription_id ) {
        // Get vendor from Order
        $vendor_id = $order->get_customer_id();

        // translators: 1) Stripe Subscription ID coming from stripe event
        $order->add_order_note( sprintf( __( 'Subscription Trial activated. Subscription ID: %s', 'dokan' ), $subscription_id ) );

        // store trial information as user meta
        update_user_meta( $vendor_id, '_dokan_subscription_is_on_trial', 'yes' );

        // store trial period also
        $trial_interval_unit  = $subscription->get_trial_period_types(); //day, week, month, year
        $trial_interval_count = absint( $subscription->get_trial_range() ); //int

        $time = dokan_current_datetime();
        $time = $time->modify( "$trial_interval_count $trial_interval_unit" );

        if ( $time ) {
            update_user_meta( $vendor_id, '_dokan_subscription_trial_until', $time->format( 'Y-m-d H:i:s' ) );
        }
    }

    /**
     * Delete Trial Meta data for vendor.
     *
     * @since 3.4.3
     *
     * @param int $vendor_id
     *
     * @return void
     */
    public static function delete_trial_meta_data( $vendor_id ) {
        delete_user_meta( $vendor_id, '_dokan_subscription_is_on_trial' );
        delete_user_meta( $vendor_id, '_dokan_subscription_trial_until' );
    }

    /**
     * Get subscription order by user_id
     *
     * @param $user_id
     *
     * @return false|\WC_Order|\WC_Order_Refund
     */
    public static function get_subscription_order( $user_id ) {
        $order_id = get_user_meta( $user_id, 'product_order_id', true );

        return wc_get_order( $order_id );
    }

    /**
     * Check if subscription packs are available
     *
     * @since 3.5.4
     *
     * @return bool
     */
    public static function is_subscription_pack_available() {
        /**
         * @var $subscription_packs \WP_Query
         */
        $subscription_packs = dokan()->subscription->all();

        return $subscription_packs->have_posts();
    }

    /**
     * Filter arguments for product list filtering.
     *
     * @since 3.7.13
     *
     * @param array  $args
     * @param string $filter
     *
     * @return array
     */
    public static function filter_products_by_filter_by_other_helper( $args, $filter ) {
        if ( 'best_selling' === $filter ) {
            $args['orderby']  = 'meta_value_num';
            $args['meta_key'] = 'total_sales';

            self::set_default_tax_query( $args );
        }

        if ( 'top_rated' === $filter ) {
            self::set_default_tax_query( $args );

            add_filter( 'posts_clauses', [ 'WC_Shortcodes', 'order_by_rating_post_clauses' ] );
        }

        if ( 'featured' === $filter ) {
            self::set_default_tax_query( $args );

            $product_visibility_term_ids = wc_get_product_visibility_term_ids();
            $args['tax_query'][]         = [
                'taxonomy' => 'product_visibility',
                'field'    => 'term_taxonomy_id',
                'terms'    => $product_visibility_term_ids['featured'],
            ];
        }

        return $args;
    }


    /**
     * Set default tax query
     *
     * @since 2.9.13
     *
     * @param array $args
     *
     * @return array
     */
    public static function set_default_tax_query( $args ) {
        $product_visibility_term_ids = wc_get_product_visibility_term_ids();

        $args['tax_query'][] = [
            'taxonomy' => 'product_visibility',
            'field'    => 'term_taxonomy_id',
            'terms'    => is_search() ? $product_visibility_term_ids['exclude-from-search'] : $product_visibility_term_ids['exclude-from-catalog'],
            'operator' => 'NOT IN',
        ];

        return $args;
    }

    /**
     * Get subscription product status changer background process class
     *
     * @since 3.3.0
     *
     * @return HelperChangerProductStatus|null
     */
    public static function get_product_status_changer_bg_class() {
        if ( ! class_exists( 'WeDevs\Dokan\Abstracts\ProductStatusChanger' ) ) {
            return null;
        }
        return dokan()->bg_process->subscription_product_status_changer;
    }
}

Helper::instance();
