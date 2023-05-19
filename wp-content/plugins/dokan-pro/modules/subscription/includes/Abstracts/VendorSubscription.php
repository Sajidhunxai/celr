<?php

namespace DokanPro\Modules\Subscription\Abstracts;

defined( 'ABSPATH' ) || exit;

/**
 * Dokan Abstract Vendor Subscription Class
 */
abstract class VendorSubscription {

    /**
     * Hold vendor id
     *
     * @var integer
     */
    protected $vendor_id = 0;

    /**
     * Get vendor id
     *
     * @return integer
     */
    abstract public function get_vendor();

    /**
     * Get all the info of a vendor regarding subscription
     *
     * @return array
     */
    public function get_info() {
        if ( ! $this->get_id() ) {
            return null;
        }

        return [
            'subscription_id'    => $this->get_id(),
            'has_subscription'   => $this->has_subscription(),
            'expiry_date'        => $this->get_pack_end_date(),
            'published_products' => $this->get_published_product_count(),
            'remaining_products' => $this->get_remaining_products(),
        ];
    }

    /**
     * Check if vendor has a subscription
     *
     * @return boolean
     */
    public function has_subscription() {
        $pack_id = get_user_meta( $this->get_vendor(), 'product_package_id', true );

        return $pack_id ? true : false;
    }

    /**
     * Get pack end date
     *
     * @return string
     */
    public function get_pack_end_date() {
        return get_user_meta( $this->get_vendor(), 'product_pack_enddate', true );
    }

    /**
     * Can post product
     *
     * @return boolean
     */
    public function can_post_product() {
        return get_user_meta( $this->get_vendor(), 'can_post_product', true );
    }

    /**
     * Get pack starting date
     *
     * @return string
     */
    public function get_pack_start_date() {
        return get_user_meta( $this->get_vendor(), 'product_pack_startdate', true );
    }

    /**
     * Check if trial is running for current vendor
     *
     * @since 3.3.7
     *
     * @return bool
     */
    public function is_on_trial() {
        return 'yes' === get_user_meta( $this->get_vendor(), '_dokan_subscription_is_on_trial', true );
    }

    /**
     * Get trial end date for a subscription
     *
     * @since 3.3.7
     *
     * @return false|string
     */
    public function get_trial_end_date() {
        $trial_end_date = get_user_meta( $this->get_vendor(), '_dokan_subscription_trial_until', true );
        if ( ! empty( $trial_end_date ) ) {
            return dokan_format_date( $trial_end_date );
        }
    }
    /**
     * Check package validity for seller
     *
     * @param int $pack_id
     *
     * @throws \Exception
     * @return boolean
     */
    public function check_pack_validity_for_vendor( $pack_id ) {
        $current_date         = dokan_current_datetime();
        $product_pack_enddate = $this->get_pack_end_date();
        $product_package_id   = $this->get_id();

        // if product_id is not same as current purchased package id, return false
        if ( (int) $product_package_id !== (int) $pack_id ) {
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
     * Get number of product has publisedh by seller
     *
     * @param integer
     *
     * @return integer
     */
    public function get_published_product_count() {
        global $wpdb;

        $allowed_status = apply_filters( 'dps_get_product_by_seller_allowed_statuses', array( 'publish', 'pending' ) );

        $query = "SELECT COUNT(*) FROM $wpdb->posts WHERE post_author = {$this->get_vendor()} AND post_type = 'product' AND post_status IN ( '" . implode( "','", $allowed_status ). "' )";
        $count = $wpdb->get_var( $query ); //phpcs:ignore

        return $count;
    }

    /**
     * Get a vendor remaining product count against a subscription pack
     *
     * @return int
     */
    public function get_remaining_products() {
        $pack_product_no = $this->get_number_of_products();

        if ( '-1' === $pack_product_no ) {
            return '-1';
        }

        $remaining_product = absint( $pack_product_no ) - $this->get_published_product_count();
        $remaining_product = $remaining_product < 0 ? 0 : $remaining_product;

        return $remaining_product;
    }

    /**
     * Vendor has recurring subscription pack
     *
     * @return boolean
     */
    public function has_recurring_pack() {
        $status = get_user_meta( $this->get_vendor(), '_customer_recurring_subscription', true );

        if ( 'active' === $status ) {
            return true;
        }

        return false;
    }

    /**
     * Check wheter vendor has any unpaid or pending subscription or not
     *
     * @since 2.9.13
     *
     * @return boolean
     */
    public function has_pending_subscription() {
        return get_user_meta( $this->get_vendor(), 'has_pending_subscription', true );
    }

    /**
     * Check whter the vendor has active cancelled subscription or not
     *
     * @since 3.0.3
     *
     * @return bool
     */
    public function has_active_cancelled_subscrption() {
        return (bool) get_user_meta( $this->get_vendor(), 'dokan_has_active_cancelled_subscrption', true );
    }

    public function set_active_cancelled_subscription() {
        update_user_meta( $this->get_vendor(), 'dokan_has_active_cancelled_subscrption', true );
    }

    public function reset_active_cancelled_subscription() {
        update_user_meta( $this->get_vendor(), 'dokan_has_active_cancelled_subscrption', false );
    }
}
