<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Support;

defined( 'ABSPATH' ) || exit; // Exit if called directly

/**
 * User meta data handler class for Stripe gateway.
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Support
 */
class UserMeta {

    /**
     * Generates meta key for stripe account id.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function stripe_account_id_key() {
        $key = 'account_id';

        if ( Settings::is_test_mode() ) {
            $key = "test_$key";
        }

        return Helper::meta_key( $key );
    }

    /**
     * Retrieves stripe account ID of a user.
     *
     * @since 3.6.1
     *
     * @param int|string $user_id
     *
     * @return string|false
     */
    public static function get_stripe_account_id( $user_id ) {
        return get_user_meta( $user_id, self::stripe_account_id_key(), true );
    }

    /**
     * Updates stripe account id for a user.
     *
     * @since 3.6.1
     *
     * @param int|string $user_id
     * @param string     $account_id
     *
     * @return int|boolean
     */
    public static function update_stripe_account_id( $user_id, $account_id ) {
        $meta_key = self::stripe_account_id_key();
        delete_user_meta( $user_id, "{$meta_key}_trash" );
        return update_user_meta( $user_id, $meta_key, $account_id );
    }

    /**
     * Deletes stripe account id of a user
     *
     * @since 3.6.1
     *
     * @param int|string $user_id ID of the user
     * @param boolean    $force   Default `false` and store the current id in trash, If `true`, no trash will be maintained
     *
     * @return boolean
     */
    public static function delete_stripe_account_id( $user_id, $force = false ) {
        $meta_key = self::stripe_account_id_key();

        if ( ! $force ) {
            $account_id = get_user_meta( $user_id, $meta_key, true );
            update_user_meta( $user_id, "{$meta_key}_trash", $account_id );
        } else {
            delete_user_meta( $user_id, "{$meta_key}_trash" );
        }

        return delete_user_meta( $user_id, $meta_key );
    }

    /**
     * Retrieves stripe account id that was previously trashed.
     *
     * @since 3.6.1
     *
     * @param int|string $user_id
     *
     * @return string|false
     */
    public static function get_trashed_stripe_account_id( $user_id ) {
        return get_user_meta( $user_id, self::stripe_account_id_key() . '_trash', true );
    }

    /**
     * Retrieves stripe customer id meta key.
     *
     * @since 3.6.1
     *
     * @return string
     */
    public static function stripe_customer_id_key() {
        $key = 'customer_id';

        if ( Settings::is_test_mode() ) {
            $key = "test_$key";
        }

        return Helper::meta_key( $key );
    }

    /**
     * Retrieves stripe customer id.
     *
     * @since 3.6.1
     *
     * @param int|string $user_id
     *
     * @return string|false
     */
    public static function get_stripe_customer_id( $user_id ) {
        return get_user_option( self::stripe_customer_id_key(), $user_id );
    }

    /**
     * Updates stripe customer id.
     *
     * @since 3.6.1
     *
     * @param int|string $user_id
     * @param string     $stripe_id
     *
     * @return string|boolean
     */
    public static function update_stripe_customer_id( $user_id, $stripe_id ) {
        return update_user_option( $user_id, self::stripe_customer_id_key(), $stripe_id );
    }

    /**
     * Deletes stripe cutomer id.
     *
     * @since 3.6.1
     *
     * @param int|string $user_id
     *
     * @return boolean
     */
    public static function delete_stripe_customer_id( $user_id ) {
        return delete_user_option( $user_id, self::stripe_customer_id_key() );
    }

    /**
     * Retrieves meta key for stripe subscription id.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function stripe_subscription_id_key() {
        return Helper::meta_key( 'subscription_id' );
    }

    /**
     * Retrieves stripe subscription id.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     *
     * @return string|false
     */
    public static function get_stripe_subscription_id( $user_id ) {
        return get_user_meta( $user_id, self::stripe_subscription_id_key(), true );
    }

    /**
     * Updates stripe subscription id.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     * @param string     $subscription_id
     *
     * @return string|boolean
     */
    public static function update_stripe_subscription_id( $user_id, $subscription_id ) {
        return update_user_meta( $user_id, self::stripe_subscription_id_key(), $subscription_id );
    }

    /**
     * Deletes stripe subscription id.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     *
     * @return boolean
     */
    public static function delete_stripe_subscription_id( $user_id ) {
        return delete_user_meta( $user_id, self::stripe_subscription_id_key() );
    }

    /**
     * Retrieves meta key for stripe subscription id for debugging.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function stripe_debug_subscription_id_key() {
        return Helper::meta_key( 'debug_subscription_id' );
    }

    /**
     * Retrieves stripe debug subscription id.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     *
     * @return string|false
     */
    public static function get_stripe_debug_subscription_id( $user_id ) {
        return get_user_meta( $user_id, self::stripe_debug_subscription_id_key(), true );
    }

    /**
     * Updates stripe debug subscription id.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     * @param string     $subscription_id
     *
     * @return string|boolean
     */
    public static function update_stripe_debug_subscription_id( $user_id, $subscription_id ) {
        return update_user_meta( $user_id, self::stripe_debug_subscription_id_key(), $subscription_id );
    }

    /**
     * Deletes stripe debug subscription id.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     *
     * @return boolean
     */
    public static function delete_stripe_debug_subscription_id( $user_id ) {
        return delete_user_meta( $user_id, self::stripe_debug_subscription_id_key() );
    }

    /**
     * Retrieves meta key for customer recurring subscription.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function customer_recurring_subscription_key() {
        return '_customer_recurring_subscription';
    }

    /**
     * Checks if a user has active recurring subscription.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     *
     * @return boolean
     */
    public static function has_customer_recurring_subscription( $user_id ) {
        return 'active' === get_user_meta( $user_id, self::customer_recurring_subscription_key(), true );
    }

    /**
     * Updates the status of customer recurring subscription.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     * @param string     $status
     *
     * @return string|boolean
     */
    public static function update_customer_recurring_subscription( $user_id, $status = 'active' ) {
        return update_user_meta( $user_id, self::customer_recurring_subscription_key(), $status );
    }

    /**
     * Retrieves meta key for product order id.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function product_order_id_key() {
        return 'product_order_id';
    }

    /**
     * Retrieves product order id of a user.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     *
     * @return string|false
     */
    public static function get_product_order_id( $user_id ) {
        return get_user_meta( $user_id, self::product_order_id_key(), true );
    }

    /**
     * Updates product order id meta value.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     * @param string     $order_id
     *
     * @return int|boolean
     */
    public static function update_product_order_id( $user_id, $order_id ) {
        return update_user_meta( $user_id, self::product_order_id_key(), $order_id );
    }

    /**
     * Retrieves meta key for product order id.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function product_pack_id_key() {
        return 'product_package_id';
    }

    /**
     * Retrieves subscribed product pack id of a user.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     *
     * @return integer|false
     */
    public static function get_product_pack_id( $user_id ) {
        return get_user_meta( $user_id, self::product_pack_id_key(), true );
    }

    /**
     * Updates product pack id meta value.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     * @param string     $product_pack_id
     *
     * @return int|boolean
     */
    public static function update_product_pack_id( $user_id, $product_pack_id ) {
        return update_user_meta( $user_id, self::product_pack_id_key(), $product_pack_id );
    }

    /**
     * Deletes product pack id meta value.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     *
     * @return boolean
     */
    public static function delete_product_pack_id( $user_id ) {
        return delete_user_meta( $user_id, self::product_pack_id_key() );
    }

    /**
     * Retrieves meta key for initial product package id.
     *
     * Although there is already a meta key to store this.
     * This one will work as a supporting meta key to avoid
     * any inconsistency at any point where the original meta
     * key might be unavailable for asynchronous process.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function initial_product_pack_id_key() {
        return Helper::meta_key( 'product_package_id' );
    }

    /**
     * Retrieves initial product pack id of a user.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     *
     * @return integer|false
     */
    public static function get_initial_product_pack_id( $user_id ) {
        return get_user_meta( $user_id, self::initial_product_pack_id_key(), true );
    }

    /**
     * Updates meta data of initial product pack id.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     * @param string     $product_pack_id
     *
     * @return boolean
     */
    public static function update_initial_product_pack( $user_id, $product_pack_id ) {
        return update_user_meta( $user_id, self::initial_product_pack_id_key(), $product_pack_id );
    }

    /**
     * Deletes initial product pack id meta value.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     *
     * @return boolean
     */
    public static function delete_initial_product_pack_id( $user_id ) {
        return delete_user_meta( $user_id, self::initial_product_pack_id_key() );
    }

    /**
     * Returns meta key for product no with pack key.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function product_no_with_pack_key() {
        return 'product_no_with_pack';
    }

    /**
     * Retrieves product no with pack of a user.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     *
     * @return string|false
     */
    public static function get_product_no_with_pack( $user_id ) {
        return get_user_meta( $user_id, self::product_no_with_pack_key(), true );
    }

    /**
     * Updates the product no with pack.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     * @param int|string $product_no
     *
     * @return int|boolean
     */
    public static function update_product_no_with_pack( $user_id, $product_no ) {
        return update_user_meta( $user_id, self::product_no_with_pack_key(), $product_no );
    }

    /**
     * Deletes meta data of no of product with pack of a vendor.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     *
     * @return boolean
     */
    public static function delete_product_no_with_pack( $user_id ) {
        return delete_user_meta( $user_id, self::product_no_with_pack_key() );
    }

    /**
     * Retrieves active cancelled subscription meta key.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function active_cancelled_subscription_key() {
        return 'dokan_has_active_cancelled_subscrption';
    }

    /**
     * Checks if a user has active cancelled subscription.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     *
     * @return boolean
     */
    public static function has_active_cancelled_subscrption( $user_id ) {
        return wc_string_to_bool( get_user_meta( $user_id, self::active_cancelled_subscription_key(), true ) );
    }

    /**
     * Updates flag for active cancelled subscription.
     *
     * @since 3.7.8
     *
     * @param int|string  $user_id
     * @param boolean     $status
     *
     * @return boolean|string
     */
    public static function update_active_cancelled_subscription( $user_id, $status = true ) {
        return update_user_meta( $user_id, self::active_cancelled_subscription_key(), $status );
    }

    /**
     * Retrieves product pack end date meta key.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function product_pack_end_key() {
        return 'product_pack_enddate';
    }

    /**
     * Retrieves product pack end date of a user.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     *
     * @return string|false
     */
    public static function get_product_pack_end_date( $user_id ) {
        return get_user_meta( $user_id, self::product_pack_end_key(), true );
    }

    /**
     * Updates product pack end date of a user.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     * @param string     $end_date
     *
     * @return int|boolean
     */
    public static function update_product_pack_end_date( $user_id, $end_date ) {
        return update_user_meta( $user_id, self::product_pack_end_key(), $end_date );
    }

    /**
     * Retrieves product pack start date meta key.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function product_pack_start_key() {
        return 'product_pack_startdate';
    }

    /**
     * Retrieves product pack end date of a user.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     *
     * @return string|false
     */
    public static function get_product_pack_start_date( $user_id ) {
        return get_user_meta( $user_id, self::product_pack_start_key(), true );
    }

    /**
     * Updates product pack start date of a user.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     * @param string     $end_date
     *
     * @return int|boolean
     */
    public static function update_product_pack_start_date( $user_id, $start_date ) {
        return update_user_meta( $user_id, self::product_pack_start_key(), $start_date );
    }

    /**
     * Retrieves has pending subscription meta key.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function has_pending_subscription_key() {
        return 'has_pending_subscription';
    }

    /**
     * Retrieves if a user has pending subscription.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     *
     * @return string|false
     */
    public static function has_pending_subscription( $user_id ) {
        return get_user_meta( $user_id, self::has_pending_subscription_key(), true );
    }

    /**
     * Update flag for pending subscription.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     * @param boolean    $status
     *
     * @return int|boolean
     */
    public static function update_pending_subscription( $user_id, $status = true ) {
        return update_user_meta( $user_id, self::has_pending_subscription_key(), ( true === $status ) );
    }

    /**
     * Retrieves can post product meta key.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function can_post_product_key() {
        return 'can_post_product';
    }

    /**
     * Checks if a user is allowed to post product.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     *
     * @return boolean
     */
    public static function can_post_product( $user_id ) {
        return wc_string_to_bool( get_user_meta( $user_id, self::can_post_product_key(), true ) );
    }

    /**
     * Update the can post product status.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     * @param int|string $status
     *
     * @return boolean
     */
    public static function update_post_product( $user_id, $status = '1' ) {
        return update_user_meta( $user_id, self::can_post_product_key(), $status );
    }

    /**
     * Retrieves seller enabled meta key.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function seller_enabled_key() {
        return 'dokan_enable_selling';
    }

    /**
     * Update whether seller is enabled.
     *
     * @since 3.7.8
     *
     * @param int|string $user_id
     * @param int|string $status
     *
     * @return boolean
     */
    public static function update_seller_enabled( $user_id, $status = 'yes' ) {
        return update_user_meta( $user_id, self::seller_enabled_key(), $status );
    }
}
