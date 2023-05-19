<?php

namespace DokanPro\Modules\Subscription;

use DokanPro\Modules\Subscription\Abstracts\VendorSubscription;
use WeDevs\Dokan\ProductCategory\Categories;

defined( 'ABSPATH' ) || exit;

/**
 * Dokan Subscription Pack Class
 */
class SubscriptionPack extends VendorSubscription {
    /**
     * Hold Pack ID
     *
     * @var integer
     */
    public $pack_id = 0;

    /**
     * Constructor method
     *
     * @param int $id
     * @param int $vendor_id
     *
     * @return void
     */
    public function __construct( $id = null, $vendor_id = null ) {
        if ( $id ) {
            $this->pack_id = $id;
        }

        if ( $vendor_id ) {
            $this->vendor_id = $vendor_id;
        }
    }

    /**
     * Get vendor id
     *
     * @return int
     */
    public function get_vendor() {
        return $this->vendor_id;
    }

    /**
     * Get the all the subscription packages
     *
     * @param array $args
     *
     * @return object
     */
    public function all( $args = [] ) {
        return $this->get_packages( $args );
    }

    /**
     * Get all subscription packages
     *
     * @param array $args
     *
     * @return object
     */
    public function get_packages( $args = [] ) {
        $defaults = [
            'post_type' => 'product',
            'post_status' => 'publish',
            'tax_query' => [
                [
                    'taxonomy' => 'product_type',
                    'field'    => 'slug',
                    'terms'    => 'product_pack',
                ],
            ],
            'posts_per_page' => -1,
            'orderby'        => 'menu_order title',
            'order'          => 'ASC',
        ];

        $args = wp_parse_args( $args, $defaults );

        return new \WP_Query( apply_filters( 'dps_get_subscription_pack_arg', $args ) );
    }

    /**
     * Get individiual pack id (ei: dokan->subscription->get( $pack_id )->pack_details())
     *
     * @param $pack_id
     *
     * @return $this instance
     */
    public function get( $pack_id ) {
        $this->pack_id = $pack_id;

        return $this;
    }

    /**
     * Get object ID
     *
     * @return int $pack_id
     */
    public function get_id() {
        return $this->pack_id;
    }

    /**
     * Get allowed product types against a subscription pack
     *
     * @return array|empty array on failure
     */
    public function get_allowed_product_types() {
        $types = [];

        if ( $this->get_id() ) {
            $types = get_post_meta( $this->get_id(), 'dokan_subscription_allowed_product_types', true );
        }

        return $types ? $types : [];
    }

    /**
     * Get allowed categories against a subscription pack
     *
     * @return array|empty array on failure
     */
    public function get_allowed_product_categories() {
        $categories = [];

        if ( $this->get_id() ) {
            $categories = get_post_meta( $this->get_id(), '_vendor_allowed_categories', true );

            if ( empty( $categories ) ) {
                return $categories;
            }

            $category       = new Categories();
            $all_category   = $category->get_all_categories( true );
            $child_cate = [];

            // We are looping through all allowed categories and including it's child categories.
            foreach ( $categories as $cat_id ) {
                if ( ! isset( $all_category[ $cat_id ] ) || 0 !== intval( $all_category[ $cat_id ]['parent_id'] ) ) {
                    continue;
                }
                $child_cate = array_merge( $child_cate, $category->get_children( $cat_id ) );
            }

            $categories = array_unique( array_merge( $categories, $child_cate ) );
        }

        return $categories;
    }

    /**
     * Is gallary image upload restricted against a subscription pack
     *
     * @return boolean
     */
    public function is_gallery_image_upload_restricted() {
        $restricted = get_post_meta( $this->get_id(), '_enable_gallery_restriction', true );

        return 'yes' === $restricted ? true : false;
    }

    /**
     * Gallary image upload count
     *
     * @return int
     */
    public function gallery_image_upload_count() {
        $count = get_post_meta( $this->get_id(), '_gallery_image_restriction_count', true );

        return ! empty( $count ) ? intval( $count ) : -1;
    }

    /**
     * Is trial
     *
     * @return boolean
     */
    public function is_trial() {
        $is_trial = get_post_meta( $this->get_id(), 'dokan_subscription_enable_trial', true );

        return 'yes' === $is_trial;
    }

    /**
     * Get trial subscription range (ei: how many days or weeks)
     *
     * @return int
     */
    public function get_trial_range() {
        return get_post_meta( $this->get_id(), 'dokan_subscription_trail_range', true );
    }

    /**
     * Get trial subscription period typs (ei; dyas, weeks, months)
     *
     * @return string
     */
    public function get_trial_period_types() {
        return get_post_meta( $this->get_id(), 'dokan_subscription_trial_period_types', true );
    }

    /**
     * Get trial period length (ei: number of days)
     *
     * @return int
     */
    public function get_trial_period_length() {
        $range  = $this->get_trial_range();
        $types  = $this->get_trial_period_types();
        $length = 0;

        if ( ! $range || ! $types ) {
            return 0;
        }

        switch ( $types ) {
            case 'week':
                $length = 7 * $range;
                break;

            case 'month':
                $length = 30 * $range;
                break;

            case 'year':
                $length = 365 * $range;
                break;

            default:
                $length = $range;
                break;
        }

        return absint( $length );
    }

    /**
     * Get trial end time (ei: required for paypal)
     *
     * @return int
     */
    public function get_trial_end_time() {
        $length = $this->get_trial_period_length();

        if ( ! $length ) {
            return 0;
        }

        $date_time = dokan_current_datetime()->modify( "+ {$length} days" );

        return intval( $date_time->getTimestamp() );
    }

    /**
     * @return string
     */
    public function get_product_pack_end_date() {
        $end_date       = 'unlimited';
        $subscription_length    = $this->get_period_length(); //_dokan_subscription_length : billing cycle stops
        $subscription_period    = $this->get_period_type(); //_dokan_subscription_period : day week month year
        $pack_validity          = absint( $this->get_pack_valid_days() ); //_pack_validity

        if ( $this->is_recurring() && $subscription_length > 0 ) {
            // if subscription_length is greater that zero product pack enddate will be equal to subscription_length
            try {
                $add_s          = $subscription_length > 1 ? 's' : '';
                $date_time      = dokan_current_datetime()
                                    ->modify( "+ {$subscription_length} {$subscription_period}{$add_s}" );

                // now add trial time if exists.
                if ( $this->is_trial() ) {
                    $vendor_used_trial = false;
                    // if vendor already has used a trial pack, create a new plan without trial period
                    if ( ! empty( $this->get_vendor() ) && Helper::has_used_trial_pack( $this->get_vendor() ) ) {
                        $vendor_used_trial = true;
                    }
                    // check if user is set and user didn;t used their trial period
                    if ( ! $vendor_used_trial ) {
                        $trial_length = $this->get_trial_period_length();
                        $date_time    = $date_time->modify( "+ $trial_length days" );
                    }
                }
                // finally get formatted end date
                $end_date = $date_time->format( 'Y-m-d H:i:s' );

            } catch ( \Exception $exception ) {
                $end_date = 'unlimited';
            }
        } elseif ( ! $this->is_recurring() && $pack_validity !== 0 ) {
            try {
                $date_time = dokan_current_datetime()
                            ->modify( "+{$pack_validity} days" );
                $end_date = $date_time->format( 'Y-m-d H:i:s' );
            } catch ( \Exception $exception ) {
                $end_date = 'unlimited';
            }
        }

        return $end_date;
    }

    /**
     * Get number of products against a subscripton pack
     *
     * @return int
     */
    public function get_number_of_products() {
        return get_post_meta( $this->get_id(), '_no_of_product', true );
    }

    /**
     * Get subscription product instance
     *
     * @return \WC_Product|null|false
     */
    public function get_product() {
        return wc_get_product( $this->get_id() );
    }

    /**
     * Get subscirption pack title
     *
     * @return string
     */
    public function get_package_title() {
        $package = $this->get_product();

        return $package ? $package->get_title() : '';
    }

    /**
     * Get valid days of a subscription pack
     *
     * @return int
     */
    public function get_pack_valid_days() {
        return get_post_meta( $this->get_id(), '_pack_validity', true );
    }

    /**
     * Check if is recurring pack
     *
     * @return boolean
     */
    public function is_recurring() {
        $is_recurring = get_post_meta( $this->get_id(), '_enable_recurring_payment', true );

        return 'yes' === $is_recurring ? true : false;
    }

    /**
     * Get subscription pack recurring interval
     *
     * @return int
     */
    public function get_recurring_interval() {
        return (int) get_post_meta( $this->get_id(), '_dokan_subscription_period_interval', true );
    }

    /**
     * Get subscription pack period type (ei: day, month, year)
     *
     * @return string
     */
    public function get_period_type() {
        return get_post_meta( $this->get_id(), '_dokan_subscription_period', true );
    }

    /**
     * Get subscription pack period lenght
     *
     * @return int
     */
    public function get_period_length() {
        return absint( get_post_meta( $this->get_id(), '_dokan_subscription_length', true ) );
    }

    /**
     * Get subscription pack price
     *
     * @return float
     */
    public function get_price() {
        $package = $this->get_product();

        return $package ? $package->get_price() : 0;
    }

    /**
     * Get All Non recurring packages.
     *
     * @since 3.3.1
     *
     * @param array $args
     *
     * @return \WP_Post[]
     */
    public function get_nonrecurring_packages( $args = [] ) {
        $defaults = [
            'meta_query' => [
                [
                    'key' => '_enable_recurring_payment',
                    'value' => 'no',
                ],
            ],
        ];

        $args = wp_parse_args( apply_filters( 'dps_get_non_recurring_pack_arg', $args ), $defaults );
        return $this->get_packages( $args )->get_posts();
    }

    /**
     * Activate the subscription after purchase
     *
     * This method doesn't check if user is currently on a subscription, so remember this while using this method.
     *
     * @param \WC_Order $order
     *
     * @since 3.3.7
     *
     * @return void
     *
     * @throws \Exception
     */
    public function activate_subscription( \WC_Order $order ) {
        $product_pack = $this->get_product();
        $pack_id      = $product_pack->get_id();
        $user_id      = $order->get_customer_id();

        if ( ! $product_pack || 'product_pack' !== $product_pack->get_type() ) {
            return;
        }

        update_user_meta( $user_id, 'can_post_product', '1' );
        update_user_meta( $user_id, 'product_package_id', $pack_id );

        //number of products
        update_user_meta( $user_id, 'product_no_with_pack', get_post_meta( $product_pack->get_id(), '_no_of_product', true ) );
        update_user_meta( $user_id, 'product_pack_startdate', dokan_current_datetime()->format( 'Y-m-d H:i:s' ) );
        update_user_meta( $user_id, 'product_order_id', $order->get_id() );
        update_user_meta( $user_id, 'product_pack_enddate', $this->get_product_pack_end_date() );
        update_user_meta( $user_id, 'dokan_has_active_cancelled_subscrption', false );

        if ( $this->is_recurring() ) {
            update_user_meta( $user_id, '_customer_recurring_subscription', 'active' );
        } else {
            update_user_meta( $user_id, '_customer_recurring_subscription', '' );
        }

        $this->setup_commissions( $user_id );

        do_action( 'dokan_vendor_purchased_subscription', $user_id );
    }

    /**
     * Setup admin commissions
     *
     * @param $user_id
     *
     * @since 3.3.7
     *
     * @return void
     */
    public function setup_commissions( $user_id ) {
        $product_pack = $this->get_product();
        $pack_id      = $product_pack->get_id();

        $admin_commission      = get_post_meta( $pack_id, '_subscription_product_admin_commission', true );
        $admin_additional_fee  = get_post_meta( $pack_id, '_subscription_product_admin_additional_fee', true );
        $admin_commission_type = get_post_meta( $pack_id, '_subscription_product_admin_commission_type', true );

        if ( ! empty( $admin_commission ) && ! empty( $admin_additional_fee ) && ! empty( $admin_commission_type ) ) {
            update_user_meta( $user_id, 'dokan_admin_percentage', $admin_commission );
            update_user_meta( $user_id, 'dokan_admin_additional_fee', $admin_additional_fee );
            update_user_meta( $user_id, 'dokan_admin_percentage_type', $admin_commission_type );
        } elseif ( ! empty( $admin_commission ) && ! empty( $admin_commission_type ) ) {
            update_user_meta( $user_id, 'dokan_admin_percentage', $admin_commission );
            update_user_meta( $user_id, 'dokan_admin_percentage_type', $admin_commission_type );
        } else {
            update_user_meta( $user_id, 'dokan_admin_percentage', '' );
            update_user_meta( $user_id, 'dokan_admin_additional_fee', '' );
        }
    }

    /**
     * Temporary suspend a subscription till provided date
     *
     * @param string $enddate Time string formatted as Y-m-d H:i:s
     *
     * @since 3.3.7
     *
     * @return bool
     */
    public function suspend_subscription( $enddate ) {
        if ( empty( $this->get_vendor() ) || empty( $this->get_id() ) ) {
            return false;
        }

        // store old enddate into another meta
        $cancelled_pack_enddate = get_user_meta( $this->get_vendor(), 'product_pack_enddate', true );
        update_user_meta( $this->get_vendor(), 'cancelled_product_pack_enddate', $cancelled_pack_enddate );

        // set product pack enddate
        update_user_meta( $this->get_vendor(), 'product_pack_enddate', $enddate );

        // set active cancel subscription status
        $this->set_active_cancelled_subscription();

        return true;
    }

    /**
     * Reactivate suspended subscription
     *
     * @since 3.3.7
     *
     * @return bool
     */
    public function reactivate_subscription() {
        if ( empty( $this->get_vendor() ) || empty( $this->get_id() ) ) {
            return false;
        }
        // get old product pack enddate
        $previous_pack_enddate = get_user_meta( $this->get_vendor(), 'cancelled_product_pack_enddate', true );
        if ( ! empty( $previous_pack_enddate ) ) {
            update_user_meta( $this->get_vendor(), 'product_pack_enddate', $previous_pack_enddate );
        } else {
            update_user_meta( $this->get_vendor(), 'product_pack_enddate', $this->get_product_pack_end_date() );
        }

        // reset subscription cancelled status
        $this->reset_active_cancelled_subscription();

        return true;
    }
}
