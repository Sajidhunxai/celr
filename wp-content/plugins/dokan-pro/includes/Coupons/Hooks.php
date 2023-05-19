<?php

namespace WeDevs\DokanPro\Coupons;

use WP_Error;
use Exception;
use WeDevs\DokanPro\Coupons\CouponCache;

/**
* Hooks Class
*
* Loaded all hooks releated with coupon
*
* @since 3.0.0
*/
class Hooks {

    /**
     * Validated coupon form error
     *
     * @var WP_Error Object
     */
    public static $validated;

    /**
     * Load autometically when class initiate
     *
     * @since 3.0.0
     */
    public function __construct() {
        $this->init_classes();

        add_action( 'dokan_load_custom_template', array( $this, 'load_coupon_template' ) );
        add_action( 'dokan_coupon_content_area_header', array( $this, 'render_coupon_header_template' ), 10 );
        add_action( 'dokan_coupon_content', array( $this, 'render_coupon_content_template' ), 10 );
        add_action( 'template_redirect', array( $this, 'handle_coupons' ) );

        add_filter( 'dokan_get_dashboard_nav', array( $this, 'add_coupon_menu' ) );
        add_filter( 'dokan_ensure_admin_have_create_coupon', array( $this, 'ensure_admin_have_create_coupon' ), 15, 4 );
        add_filter( 'dokan_is_order_have_admin_coupon', array( $this, 'is_order_have_admin_coupon' ), 15, 4 );
        add_filter( 'woocommerce_coupon_validate_minimum_amount', array( $this, 'validate_coupon_minimum_amount' ), 10, 2 );
        add_action( 'dokan_new_product_added', array( $this, 'associate_new_product_with_vendor_coupon' ), 10, 2 );
        add_action( 'dokan_admin_coupon_options_save', array( $this, 'attach_coupon_to_seller' ), 10, 1 );
        add_action( 'dokan_admin_coupon_options_before_save', array( $this, 'detach_coupon_from_seller' ), 10, 2 );
        add_action( 'delete_user', array( $this, 'handle_vendor_deletion' ), 10, 2 );
        add_action( 'before_delete_post', array( $this, 'detach_coupon_from_seller_on_delete' ), 10, 2 );
    }

    /**
     * Initialization of Classes related to coupons
     *
     * @since 3.4.2
     *
     * @return void
     */
    public function init_classes() {
        new CouponCache();
    }

    /*
     * Check order have admin coupon
     *
     * @param bool       $valid
     * @param \WC_Coupon $coupon
     * @param array      $seller_ids
     * @param array      $product_ids
     *
     * @return boolean
     */
    public function is_order_have_admin_coupon( $valid, $coupon, $seller_ids, $product_ids ) {
        return dokan_pro()->coupon->is_admin_coupon_valid( $coupon, $seller_ids, $product_ids );
    }

    /**
     * Ensure vendor have coupon created by admin
     *
     * @param bool       $valid
     * @param \WC_Coupon $coupon
     * @param array      $available_vendors
     * @param array      $available_products
     *
     * @return boolean
     */
    public function ensure_admin_have_create_coupon( $valid, $coupon, $available_vendors, $available_products ) {
        $commissions_type = $coupon->get_meta( 'coupon_commissions_type' );

        if ( empty( $commissions_type ) ) {
            return false;
        }

        return dokan_pro()->coupon->is_admin_coupon_valid( $coupon, $available_vendors, $available_products );
    }

    /**
     * Associate new product with vendor coupon
     *
     * @param int $product_id
     * @param array $product
     *
     * @since 3.3.4
     *
     * @return void
     */
    public function associate_new_product_with_vendor_coupon( $product_id, $product ) {
        $coupons = $this->get_apply_coupons_for_new_products();

        if ( empty( $coupons ) ) {
            return;
        }

        foreach ( $coupons as $coupon ) {
            $product_ids = get_post_meta( $coupon->ID, 'product_ids', true );

            if ( ! empty( $product_ids ) ) {
                $product_ids = $product_ids . ',' . $product_id;
            } else {
                $product_ids = $product_id;
            }

            update_post_meta( $coupon->ID, 'product_ids', $product_ids );
        }
    }

    /**
     * Get coupons apply for new products
     *
     * @since 3.3.4
     *
     * @return array $coupons
     */
    public function get_apply_coupons_for_new_products() {
        $args = array(
            'post_type'   => 'shop_coupon',
            'post_status' => 'publish',
            'author'      => dokan_get_current_user_id(),
            'meta_query'  => array(
                array(
                    'key'   => 'apply_new_products',
                    'value' => 'yes',
                ),
            ),
        );

        $coupons = get_posts( $args );

        return $coupons;
    }

    /**
     * Add Coupon menu
     *
     * @param array $urls
     *
     * @since 2.4
     *
     * @return array $urls
     */
    public function add_coupon_menu( $urls ) {
        $urls['coupons'] = array(
            'title'      => __( 'Coupons', 'dokan' ),
            'icon'       => '<i class="fas fa-gift"></i>',
            'url'        => dokan_get_navigation_url( 'coupons' ),
            'pos'        => 55,
            'permission' => 'dokan_view_coupon_menu',
        );

        return $urls;
    }

    /**
     * Ensure coupon amount is valid or throw exception.
     *
     * @since 2.9.10
     *
     * @param bool $invalid
     * @param WC_Coupon $coupon
     * @param float $total
     *
     * @return bool
     */
    public function validate_coupon_minimum_amount( $valid, $coupon ) {
        if ( ! apply_filters( 'dokan_ensure_vendor_coupon', true ) ) {
            return $valid;
        }

        $line_item_total               = 0;
        $coupon_applicable_product_ids = $coupon->get_product_ids();

        foreach ( WC()->cart->get_cart() as $item ) {
            $product_id = $item['data']->get_id();
            $seller_id  = intval( get_post_field( 'post_author', $product_id ) );

            if (
                in_array( $product_id, $coupon_applicable_product_ids, true ) ||
                in_array( $item['product_id'], $coupon_applicable_product_ids, true ) ||
                dokan_pro()->coupon->is_admin_coupon_valid( $coupon, [ $seller_id ], [ $product_id ] )
            ) {
                $line_sub_total  = ! empty( $item['line_subtotal'] ) ? $item['line_subtotal'] + ( WC()->cart->display_prices_including_tax() ? $item['line_subtotal_tax'] : 0.0 ) : 0;
                $line_item_total += $line_sub_total;
            }
        }

        if ( $coupon->get_minimum_amount() > $line_item_total ) {
            // Return validation message when coupon amount greater than total amount
            // translators: %s : showing minimun amount for coupon
            throw new Exception( sprintf( __( 'The minimun spend for this coupon is %s', 'dokan' ), wc_price( $coupon->get_minimum_amount() ) ), 108 );
        }

        return $valid;
    }

    /**
     * Render Coupon Header template
     *
     * @since 2.4
     *
     * @return void
     */
    public function render_coupon_header_template() {
        $is_edit      = ( isset( $_GET['view'] ) && 'add_coupons' === $_GET['view'] ) ? true : false; // phpcs:ignore
        $is_edit_page = ( ! empty( $_GET['post'] ) && $is_edit ) ? true : false; // phpcs:ignore
        dokan_get_template_part(
            'coupon/header', '', array(
                'pro' => true,
                'is_edit_page' => $is_edit_page,
                'is_edit' => $is_edit,
            )
        );
    }

    /**
     * Render Coupon Content
     *
     * @since 2.4
     *
     * @return void
     */
    public function render_coupon_content_template() {
        if ( ! dokan_is_seller_enabled( get_current_user_id() ) ) {
            echo dokan_seller_not_enabled_notice();
        } else {
            $this->list_user_coupons();

            if ( is_wp_error( self::$validated ) ) {
                $messages = self::$validated->get_error_messages();

                foreach ( $messages as $message ) {
                    dokan_get_template_part(
                        'global/dokan-error', '',
                        [
                            'deleted' => true,
                            'message' => $message,
                        ]
                    );
                }
            }

            $this->add_coupons_form( self::$validated );
        }
    }

    /**
     * Load Coupon template
     *
     * @since 2.4
     *
     * @param  array $query_vars
     *
     * @return void [require once template]
     */
    public function load_coupon_template( $query_vars ) {
        if ( isset( $query_vars['coupons'] ) ) {
            dokan_get_template_part( 'coupon/coupons', '', array( 'pro' => true ) );
            return;
        }
    }

    /**
     * Render listing of coupon
     *
     * @since 2.4
     *
     * @return void
     */
    public function list_user_coupons() {
        //click add coupon then hide this function
        if ( isset( $_GET['view'] ) && 'add_coupons' === $_GET['view'] ) { // phpcs:ignore
            return;
        }

        if ( isset( $_GET['post'] ) && 'edit' === $_GET['action'] ) { // phpcs:ignore
            return;
        }

        $pagenum             = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1; // phpcs:ignore
        $coupons_type        = isset( $_GET['coupons_type'] ) ? sanitize_text_field( $_GET['coupons_type'] ) : ''; // phpcs:ignore
        $marketplace_tab     = 'marketplace_coupons' === $coupons_type;
        $link                = dokan_get_navigation_url( 'coupons' );

        $vendor_coupons      = [];
        $marketplace_coupons = [];

        if ( $marketplace_tab ) {
            $marketplace_coupons = dokan_get_marketplace_seller_coupon( dokan_get_current_user_id(), false );
        } else {
            $vendor_coupons      = dokan_pro()->coupon->all( [ 'paged' => $pagenum ] );
        }

        $this->get_messages();
        dokan_get_template_part(
            'coupon/listing', '',
            [
                'pro'                 => true,
                'vendor_coupons'      => $vendor_coupons,
                'marketplace_coupons' => $marketplace_coupons,
                'link'                => $link,
                'marketplace_tab'     => $marketplace_tab,
            ]
        );
    }

    /**
     * Render coupon Message
     *
     * @return void
     */
    public function get_messages() {
        if ( isset( $_GET['message'] ) && 'delete_succefully' === $_GET['message'] ) { // phpcs:ignore
            dokan_get_template_part( 'global/dokan-message', '', array( 'message' => __( 'Coupon has been deleted successfully!', 'dokan' ) ) );
        }

        if ( isset( $_GET['message'] ) && 'coupon_saved' === $_GET['message'] ) { // phpcs:ignore
            dokan_get_template_part( 'global/dokan-message', '', array( 'message' => __( 'Coupon has been saved successfully!', 'dokan' ) ) );
        }

        if ( isset( $_GET['message'] ) && 'coupon_update' === $_GET['message'] ) { // phpcs:ignore
            dokan_get_template_part( 'global/dokan-message', '', array( 'message' => __( 'Coupon has been updated successfully!', 'dokan' ) ) );
        }
    }

        /**
     * Render Add Coupon Form
     *
     * @param object $validated
     *
     * @return void
     */
    public function add_coupons_form( $validated ) {
        $get_data  = wp_unslash( $_GET ); // phpcs:ignore
        $post_data = wp_unslash( $_POST ); // phpcs:ignore

        //intial time hide this function
        if ( ! isset( $get_data['view'] ) ) {
            return;
        } elseif ( 'add_coupons' !== $get_data['view'] ) {
            return;
        }

        $button_name = __( 'Create Coupon', 'dokan' );

        if ( isset( $get_data['post'] ) && 'edit' === $get_data['action'] ) {
            $post                       = get_post( $get_data['post'] );
            $button_name                = __( 'Update Coupon', 'dokan' );
            $discount_type              = get_post_meta( $post->ID, 'discount_type', true );
            $amount                     = get_post_meta( $post->ID, 'coupon_amount', true );
            $products                   = get_post_meta( $post->ID, 'product_ids', true );
            $exclude_products           = get_post_meta( $post->ID, 'exclude_product_ids', true );
            $product_categories         = get_post_meta( $post->ID, 'product_categories', true );
            $exclude_product_categories = get_post_meta( $post->ID, 'exclude_product_categories', true );
            $usage_limit                = get_post_meta( $post->ID, 'usage_limit', true );
            $usage_limit_per_user       = get_post_meta( $post->ID, 'usage_limit_per_user', true );
            $expire                     = get_post_meta( $post->ID, 'date_expires', true );
            $apply_before_tax           = get_post_meta( $post->ID, 'apply_before_tax', true );
            $exclide_sale_item          = get_post_meta( $post->ID, 'exclude_sale_items', true );
            $minimum_amount             = get_post_meta( $post->ID, 'minimum_amount', true );
            $customer_email             = get_post_meta( $post->ID, 'customer_email', true );
            $show_on_store              = get_post_meta( $post->ID, 'show_on_store', true );
            $apply_new_products         = get_post_meta( $post->ID, 'apply_new_products', true );
        }

        $post_id     = isset( $post->ID ) ? $post->ID : '';
        $post_title  = isset( $post->post_title ) ? $post->post_title : '';
        $description = isset( $post->post_content ) ? $post->post_content : '';

        if ( ! empty( $post_id ) && ! dokan_is_valid_owner( $post_id, dokan_get_current_user_id() ) ) {
            wp_safe_redirect( dokan_get_navigation_url( 'coupons' ) );
            exit();
        }

        $discount_type              = isset( $discount_type ) ? $discount_type : '';
        $amount                     = isset( $amount ) ? $amount : '';
        $products                   = isset( $products ) ? $products : '';
        $exclude_products           = isset( $exclude_products ) ? $exclude_products : '';
        $product_categories         = ! empty( $product_categories ) ? $product_categories : array();
        $exclude_product_categories = ! empty( $exclude_product_categories ) ? $exclude_product_categories : array();

        $usage_limit          = isset( $usage_limit ) ? $usage_limit : '';
        $usage_limit_per_user = isset( $usage_limit_per_user ) ? $usage_limit_per_user : '';

        $now = dokan_current_datetime();

        if ( isset( $expire ) && ( (string) (int) $expire === $expire )
            && ( $expire <= PHP_INT_MAX )
            && ( $expire >= ~PHP_INT_MAX ) ) {
            $expire = $now->setTimestamp( $expire )->format( 'Y-m-d' );
        } else {
            $expire = ! empty( $expire ) && strtotime( $expire ) ? $now->modify( $expire )->format( 'Y-m-d' ) : '';
        }

        $products_id = str_replace( ' ', '', $products );
        $products_id = explode( ',', $products_id );

        if ( isset( $apply_before_tax ) && 'yes' === $apply_before_tax ) {
            $apply_before_tax = 'checked';
        } else {
            $apply_before_tax = '';
        }

        if ( isset( $exclide_sale_item ) && 'yes' === $exclide_sale_item ) {
            $exclide_sale_item = 'checked';
        } else {
            $exclide_sale_item = '';
        }

        if ( isset( $show_on_store ) && 'yes' === $show_on_store ) {
            $show_on_store = 'checked';
        } else {
            $show_on_store = '';
        }

        if ( isset( $apply_new_products ) && 'yes' === $apply_new_products ) {
            $apply_new_products = 'checked';
        } else {
            $apply_new_products = '';
        }

        $minimum_amount = isset( $minimum_amount ) ? $minimum_amount : '';
        $customer_email = ! empty( $customer_email ) ? implode( ',', $customer_email ) : '';

        if ( is_wp_error( self::$validated ) ) {
            $post_id       = $post_data['post_id'];
            $post_title    = $post_data['title'];
            $description   = $post_data['description'];
            $discount_type = $post_data['discount_type'];
            $amount        = $post_data['amount'];

            if ( isset( $post_data['product_drop_down'] ) ) {
                $products = implode( ',', array_filter( array_map( 'intval', (array) $post_data['product_drop_down'] ) ) );
            } else {
                $products = '';
            }

            if ( isset( $post_data['exclude_product_ids'] ) ) {
                $exclude_products = implode( ',', array_filter( array_map( 'intval', (array) $post_data['exclude_product_ids'] ) ) );
            } else {
                $exclude_products = '';
            }

            if ( isset( $post_data['product_categories'] ) ) {
                $product_categories = implode( ',', array_filter( array_map( 'intval', (array) $post_data['product_categories'] ) ) );
            } else {
                $product_categories = '';
            }

            if ( isset( $post_data['exclude_product_categories'] ) ) {
                $exclude_product_categories = implode( ',', array_filter( array_map( 'intval', (array) $post_data['exclude_product_categories'] ) ) );
            } else {
                $exclude_product_categories = '';
            }

            $usage_limit          = $post_data['usage_limit'];
            $usage_limit_per_user = $post_data['usage_limit_per_user'];
            $expire               = $post_data['expire'];

            if ( isset( $post_data['apply_before_tax'] ) && 'yes' === $post_data['apply_before_tax'] ) {
                $apply_before_tax = 'checked';
            } else {
                $apply_before_tax = '';
            }

            if ( isset( $post_data['exclude_sale_items'] ) && 'yes' === $post_data['exclude_sale_items'] ) {
                $exclide_sale_item = 'checked';
            } else {
                $exclide_sale_item = '';
            }

            if ( isset( $post_data['show_on_store'] ) && 'yes' === $post_data['show_on_store'] ) {
                $show_on_store = 'checked';
            } else {
                $show_on_store = '';
            }

            if ( isset( $post_data['apply_new_products'] ) && 'yes' === $post_data['apply_new_products'] ) {
                $apply_new_products = 'checked';
            } else {
                $apply_new_products = '';
            }

            $minimum_amount = $post_data['minium_ammount'];
            $customer_email = $post_data['email_restrictions'];
        }

        $exclude_products = str_replace( ' ', '', $exclude_products );
        $exclude_products = explode( ',', $exclude_products );

        if ( empty( $post_id ) && ! current_user_can( 'dokan_add_coupon' ) ) {
            dokan_get_template_part(
                'global/dokan-error', '',
                [
                    'deleted' => false,
                    'message' => __( 'You have no permission to add coupon', 'dokan' ),
                ]
            );
        } elseif ( ! empty( $post_id ) && ! current_user_can( 'dokan_edit_coupon' ) ) {
            dokan_get_template_part(
                'global/dokan-error', '',
                [
                    'deleted' => false,
                    'message' => __( 'You have no permission to edit this coupon', 'dokan' ),
                ]
            );
        } else {
            dokan_get_template_part(
                'coupon/form', '', array(
                    'pro'                        => true,
                    'post_id'                    => $post_id,
                    'post_title'                 => $post_title,
                    'discount_type'              => $discount_type,
                    'description'                => $description,
                    'amount'                     => wc_format_localized_price( $amount ),
                    'products'                   => $products,
                    'exclude_products'           => $exclude_products,
                    'product_categories'         => $product_categories,
                    'exclude_product_categories' => $exclude_product_categories,
                    'usage_limit'                => $usage_limit,
                    'usage_limit_per_user'       => $usage_limit_per_user,
                    'expire'                     => $expire,
                    'minimum_amount'             => $minimum_amount,
                    'customer_email'             => $customer_email,
                    'button_name'                => $button_name,
                    'exclide_sale_item'          => $exclide_sale_item,
                    'apply_new_products'         => $apply_new_products,
                    'show_on_store'              => $show_on_store,
                    'products_id'                => $products_id,
                )
            );
        }
    }

    /**
     * Handle the coupons submission
     *
     * @return void
     */
    public function handle_coupons() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        if ( ! dokan_is_user_seller( get_current_user_id() ) ) {
            return;
        }

        // Coupon functionality
        if ( isset( $_POST['coupon_creation'] ) ) { // phpcs:ignore
            self::$validated = $this->validate();
            if ( ! is_wp_error( self::$validated ) ) {
                $this->coupons_create();
            }
        }

        if ( isset( $_GET['coupon_del_nonce'] ) ) {
            $this->coupun_delete();
        }
    }

    /**
     * Validate Coupon handler form
     *
     * @since 2.4
     *
     * @return object WP_Error|error
     */
    public function validate() {
        if ( ! isset( $_POST['coupon_nonce_field'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['coupon_nonce_field'] ) ), 'coupon_nonce' ) ) {
            wp_die( __( 'Are you cheating?', 'dokan' ) );
        }

        $errors = new WP_Error();

        $title   = isset( $_POST['title'] ) ? sanitize_text_field( wp_unslash( $_POST['title'] ) ) : '';
        $post_id = isset( $_POST['post_id'] ) ? absint( wp_unslash( $_POST['post_id'] ) ) : '';

        // Checking permissions for adding and editing
        if ( empty( $post_id ) ) {
            if ( ! current_user_can( 'dokan_add_coupon' ) ) {
                $errors->add( 'title', __( 'You have no permission to add this coupon', 'dokan' ) );
            }
        } else {
            if ( ! current_user_can( 'dokan_edit_coupon' ) ) {
                $errors->add( 'title', __( 'You have no permission to edit this coupon', 'dokan' ) );
            }
        }

        if ( empty( $title ) ) {
            $errors->add( 'title', __( 'Please enter the coupon title', 'dokan' ) );
        }

        if ( empty( wp_unslash( $_POST['amount'] ) ) ) { // phpcs:ignore
            $errors->add( 'amount', __( 'Please enter the amount', 'dokan' ) );
        }

        if ( ! isset( $_POST['product_drop_down'] ) || ! count( wp_unslash( $_POST['product_drop_down'] ) ) ) { // phpcs:ignore
            $errors->add( 'products', __( 'Please specify any products', 'dokan' ) );
        }

        $this->is_coupon_exist( $post_id, $title, $errors );

        if ( $errors->get_error_codes() ) {
            return $errors;
        }

        return true;
    }

    /**
     * Coupon Delete Functionality
     *
     * @since 2.4
     *
     * @return void
     */
    public function coupun_delete() {
        if ( ! isset( $_GET['coupon_del_nonce'] ) || ! wp_verify_nonce( $_GET['coupon_del_nonce'], '_coupon_del_nonce' ) ) { // phpcs:ignore
            wp_die( __( 'Are you cheating?', 'dokan' ) );
        }

        if ( ! current_user_can( 'dokan_delete_coupon' ) ) {
            wp_die( __( 'You have not permission to delete this coupon', 'dokan' ) );
        }

        $post_id = isset( $_GET['post'] ) ? absint( $_GET['post'] ) : 0;

        if ( ! $post_id ) {
            return;
        }

        dokan_pro()->coupon->delete( $post_id, true ); // phpcs:ignore

        /**
         * Action: Dokan Delete Coupon
         *
         * @since 3.4.2
         */
        do_action( 'dokan_after_coupon_delete', $post_id );

        wp_safe_redirect( add_query_arg( array( 'message' => 'delete_succefully' ), dokan_get_navigation_url( 'coupons' ) ) );
    }

    /**
     * Create Coupon hanlder function
     *
     * @since 2.4
     *
     * @return void
     */
    public function coupons_create() {
        if ( ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['coupon_nonce_field'] ) ), 'coupon_nonce' ) ) { // phpcs:ignore
            wp_die( __( 'Are you cheating?', 'dokan' ) );
        }

        $post_data = wp_unslash( $_POST ); // phpcs:ignore
        $user_id   = dokan_get_current_user_id();

        if ( empty( $post_data['post_id'] ) ) {
            $post = array(
                'post_title'   => sanitize_text_field( $post_data['title'] ),
                'post_content' => sanitize_textarea_field( $post_data['description'] ),
                'post_status'  => 'publish',
                'post_type'    => 'shop_coupon',
                'post_author'  => $user_id,
            );

            $post_id = wp_insert_post( $post );
            $message = 'coupon_saved';
        } else {
            $post = array(
                'ID'           => absint( $post_data['post_id'] ),
                'post_title'   => sanitize_text_field( $post_data['title'] ),
                'post_content' => sanitize_textarea_field( $post_data['description'] ),
                'post_status'  => 'publish',
                'post_type'    => 'shop_coupon',
                'post_author'  => $user_id,
            );
            $post_id = wp_update_post( $post );
            $message = 'coupon_update';
        }

        if ( ! $post_id ) {
            return;
        }

        $customer_email     = array_filter( array_map( 'trim', explode( ',', sanitize_text_field( $post_data['email_restrictions'] ) ) ) );
        $type               = sanitize_text_field( $post_data['discount_type'] );
        $amount             = wc_format_decimal( sanitize_text_field( $post_data['amount'] ) );
        $usage_limit        = empty( $post_data['usage_limit'] ) ? '' : absint( $post_data['usage_limit'] );
        $usage_limit_per_user = empty( $post_data['usage_limit_per_user'] ) ? '' : absint( $post_data['usage_limit_per_user'] );
        $expiry_date        = ! empty( $post_data['expire'] ) ? dokan_current_datetime()->modify( sanitize_text_field( $post_data['expire'] ) . ' 00:00:00' )->getTimestamp() : '';
        $apply_before_tax   = isset( $post_data['apply_before_tax'] ) ? 'yes' : 'no';
        $exclude_sale_items = isset( $post_data['exclude_sale_items'] ) ? 'yes' : 'no';
        $show_on_store      = isset( $post_data['show_on_store'] ) ? 'yes' : 'no';
        $apply_new_products = isset( $post_data['apply_new_products'] ) ? 'yes' : 'no';
        $minimum_amount     = wc_format_decimal( sanitize_text_field( $post_data['minium_ammount'] ) );

        if ( isset( $post_data['product_drop_down'][0] ) && 'select_all' === $post_data['product_drop_down'][0] ) {
            $product_ids = dokan_coupon_get_seller_product_ids( $user_id );
            $product_ids = implode( ',', $product_ids );
        } elseif ( isset( $post_data['product_drop_down'] ) ) {
            $product_ids = implode( ',', array_filter( array_map( 'intval', (array) $post_data['product_drop_down'] ) ) );
        } else {
            $product_ids = '';
        }

        if ( isset( $post_data['exclude_product_ids'] ) ) {
            $exclude_product_ids = implode( ',', array_filter( array_map( 'intval', (array) $post_data['exclude_product_ids'] ) ) );
        } else {
            $exclude_product_ids = '';
        }

        if ( isset( $post_data['product_categories'] ) ) {
            $product_categories = array_filter( array_map( 'intval', (array) $post_data['product_categories'] ) );
        } else {
            $product_categories = array();
        }

        if ( isset( $post_data['exclude_product_categories'] ) ) {
            $exclude_product_categories = array_filter( array_map( 'intval', (array) $post_data['exclude_product_categories'] ) );
        } else {
            $exclude_product_categories = array();
        }

        update_post_meta( $post_id, 'discount_type', $type );
        update_post_meta( $post_id, 'coupon_amount', $amount );
        update_post_meta( $post_id, 'product_ids', $product_ids );
        update_post_meta( $post_id, 'exclude_product_ids', $exclude_product_ids );
        update_post_meta( $post_id, 'product_categories', $product_categories );
        update_post_meta( $post_id, 'exclude_product_categories', $exclude_product_categories );
        update_post_meta( $post_id, 'usage_limit', $usage_limit );
        update_post_meta( $post_id, 'usage_limit_per_user', $usage_limit_per_user );
        update_post_meta( $post_id, 'date_expires', $expiry_date );
        update_post_meta( $post_id, 'apply_before_tax', $apply_before_tax );
        update_post_meta( $post_id, 'free_shipping', 'no' );
        update_post_meta( $post_id, 'exclude_sale_items', $exclude_sale_items );
        update_post_meta( $post_id, 'apply_new_products', $apply_new_products );
        update_post_meta( $post_id, 'show_on_store', $show_on_store );
        update_post_meta( $post_id, 'minimum_amount', $minimum_amount );
        update_post_meta( $post_id, 'customer_email', $customer_email );

        do_action( 'dokan_after_coupon_create', $post_id );

        if ( ! defined( 'DOING_AJAX' ) ) {
            wp_safe_redirect( add_query_arg( array( 'message' => $message ), dokan_get_navigation_url( 'coupons' ) ) );
        }
    }

    /**
    * Get the orders total from a specific seller
    *
    * @since version 3
    *
    * @param string $title
    * @param object $error
    *
    * @return object $error
    */
    public function is_coupon_exist( $post_id, $title, $errors ) {
        $args = array(
            'post_type' => 'shop_coupon',
            'name'      => $title,
        );

        $query = get_posts( $args );

        if ( $title ) {
            if ( ! empty( $query ) ) {
                if ( $post_id !== $query[0]->ID ) {
                    return $errors->add( 'duplicate', __( 'Coupon title already exists', 'dokan' ) );
                }
            }
        }
    }

    /**
     * Handle coupon meta related to vendor limits
     *
     * @since 3.7.4
     *
     * @param int $user_id Deleted user's id
     * @param int|null $reassigned_user
     *
     * @retun void
     */
    public function handle_vendor_deletion( $user_id, $reassigned_user ) {
        if ( ! dokan_is_user_seller( $user_id ) ) {
            return;
        }

        $included_marketplace_coupons = get_user_meta( $user_id, 'included_marketplace_coupons', true ); // included_marketplace_coupons is an array saving the marketplace coupon ids that include the $user_id
        $included_marketplace_coupons = ! empty( $included_marketplace_coupons ) ? $included_marketplace_coupons : [];

        // update the included vendors meta
        $this->update_vendor_restriction_meta_of_coupon( $included_marketplace_coupons, $user_id, $reassigned_user, 'coupons_vendors_ids' );

        $excluded_marketplace_coupons = get_user_meta( $user_id, 'excluded_marketplace_coupons', true ); // excluded_marketplace_coupons is an array saving the marketplace coupon ids that exclude the $user_id
        $excluded_marketplace_coupons = ! empty( $excluded_marketplace_coupons ) ? $excluded_marketplace_coupons : [];

        // update the excluded vendors meta
        $this->update_vendor_restriction_meta_of_coupon( $excluded_marketplace_coupons, $user_id, $reassigned_user, 'coupons_exclude_vendors_ids' );
    }

    /**
     * Remove old vendor and Reassign new vendor
     *
     * @since 3.7.4
     *
     * @param string $vendor_ids
     * @param int $user_id
     * @param int|null $reassigned_user
     *
     * @return array
     */
    private function remove_and_reassign_seller( $vendor_ids, $user_id, $reassigned_user ) {
        $vendor_ids = ! empty( $vendor_ids ) ? $vendor_ids : '';
        $vendor_ids = array_map( 'absint', explode( ',', $vendor_ids ) );
        // exclude the $user_id
        $vendor_ids = array_diff( $vendor_ids, [ $user_id ] );

        $new_user_assigned = false;

        // handle the reassignment of the coupon to $reassigned user
        if ( $reassigned_user ) {
            // if $reassigned_user is already in the list nothing to do otherwise we need to add the user into the list
            if ( ! in_array( $reassigned_user, $vendor_ids, true ) ) {
                array_push( $vendor_ids, $reassigned_user );
                $new_user_assigned = true;
            }
        }

        return array( $vendor_ids, $new_user_assigned );
    }

    /**
     * Remove coupons from vendors meta list
     *
     * @since 3.7.4
     *
     * @param array $vendors_ids
     * @param int $coupon_id
     *
     * @return void
     */
    private function remove_coupon_id_from_vendors_meta_list( $vendors_ids, $coupon_id, $meta_key ) {
        foreach ( $vendors_ids as $vendors_id ) {
            $marketplace_coupons = get_user_meta( $vendors_id, $meta_key, true );
            $marketplace_coupons = ! empty( $marketplace_coupons ) ? $marketplace_coupons : [];

            if ( in_array( $coupon_id, $marketplace_coupons, true ) ) {
                $marketplace_coupons = array_diff( $marketplace_coupons, [ $coupon_id ] ); // remove the coupon id from list
                update_user_meta( $vendors_id, $meta_key, $marketplace_coupons ); //save the new list after deleting $coupon_id
            }
        }
    }

    /**
     * Detach Coupons from user on delete coupon
     *
     * @since 3.7.4
     *
     * @param int $coupon_id
     * @param \WP_Post $post
     */
    public function detach_coupon_from_seller_on_delete( $coupon_id, $post ) {
        if ( 'shop_coupon' !== $post->post_type ) {
            return;
        }

        $vendors_ids     = get_post_meta( $coupon_id, 'coupons_vendors_ids', true );
        $vendors_ids     = ! empty( $vendors_ids ) ? array_map( 'intval', explode( ',', $vendors_ids ) ) : [];
        $exclude_vendors = get_post_meta( $coupon_id, 'coupons_exclude_vendors_ids', true );
        $exclude_vendors = ! empty( $exclude_vendors ) ? array_map( 'intval', explode( ',', $exclude_vendors ) ) : [];

        // remove the coupon id from vendor's include list
        $this->remove_coupon_id_from_vendors_meta_list( $vendors_ids, $coupon_id, 'included_marketplace_coupons' );

        // remove the coupon id from vendor's exclude list
        $this->remove_coupon_id_from_vendors_meta_list( $exclude_vendors, $coupon_id, 'excluded_marketplace_coupons' );
    }

    /**
     * Detach Coupons from user
     *
     * @since 3.7.4
     *
     * @param int $coupon_id
     * @param array $new_data
     *
     * @retun void
     */
    public function detach_coupon_from_seller( $coupon_id, $new_data ) {
        $old_vendors_ids     = get_post_meta( $coupon_id, 'coupons_vendors_ids', true );
        $old_vendors_ids     = ! empty( $old_vendors_ids ) ? array_map( 'intval', explode( ',', $old_vendors_ids ) ) : [];
        $old_exclude_vendors = get_post_meta( $coupon_id, 'coupons_exclude_vendors_ids', true );
        $old_exclude_vendors = ! empty( $old_exclude_vendors ) ? array_map( 'intval', explode( ',', $old_exclude_vendors ) ) : [];

        $new_vendors_ids     = $new_data['coupons_vendors_ids'];
        $new_vendors_ids     = ! empty( $new_vendors_ids ) ? array_map( 'intval', explode( ',', $new_vendors_ids ) ) : [];
        $new_exclude_vendors = $new_data['coupons_exclude_vendors_ids'];
        $new_exclude_vendors = ! empty( $new_exclude_vendors ) ? array_map( 'intval', explode( ',', $new_exclude_vendors ) ) : [];

        // get the vendor ids that are not present in new_vendors_ids but was present in $old_vendors_ids
        $vendor_id_diff = array_diff( $old_vendors_ids, $new_vendors_ids );
        // remove the coupon id from include vendor list
        $this->remove_coupon_id_from_vendors_meta_list( $vendor_id_diff, $coupon_id, 'included_marketplace_coupons' );

        // get the excluded vendor ids that are not present in new_exclude_vendors but was present in $old_vendors_ids
        $excluded_vendor_id_diff = array_diff( $old_exclude_vendors, $new_exclude_vendors );
        // remove the coupon id from exclude vendor list
        $this->remove_coupon_id_from_vendors_meta_list( $excluded_vendor_id_diff, $coupon_id, 'excluded_marketplace_coupons' );
    }

    /**
     * Save marketplace coupons list as seller meta
     *
     * @since 3.7.4
     *
     * @param int $coupon_id
     *
     * @retun void
     */
    public function attach_coupon_to_seller( $coupon_id ) {
        $vendors_ids     = get_post_meta( $coupon_id, 'coupons_vendors_ids', true );
        $vendors_ids     = ! empty( $vendors_ids ) ? array_map( 'intval', explode( ',', $vendors_ids ) ) : [];
        $exclude_vendors = get_post_meta( $coupon_id, 'coupons_exclude_vendors_ids', true );
        $exclude_vendors = ! empty( $exclude_vendors ) ? array_map( 'intval', explode( ',', $exclude_vendors ) ) : [];

        // add the coupon_id to vendors include coupon list
        $this->add_coupon_id_to_vendors_meta_list( $vendors_ids, $coupon_id, 'included_marketplace_coupons' );

        // add the coupon_id to vendors exclude coupon list
        $this->add_coupon_id_to_vendors_meta_list( $exclude_vendors, $coupon_id, 'excluded_marketplace_coupons' );
    }

    /**
     * Update vendor restriction meta for marketplace coupons
     *
     * @since 3.7.4
     *
     * @param array $coupon_ids
     * @param int $user_id
     * @param int $reassigned_user
     * @param string $meta_key
     *
     * @return void
     */
    private function update_vendor_restriction_meta_of_coupon( $coupon_ids, $user_id, $reassigned_user, $meta_key ) {
        foreach ( $coupon_ids as $coupon_id ) {
            $wc_coupon = new \WC_Coupon( $coupon_id );

            if ( empty( $wc_coupon->get_id() ) ) {
                continue;
            }

            $vendor_ids = $wc_coupon->get_meta( $meta_key );
            list( $vendor_ids, $user_assigned ) = $this->remove_and_reassign_seller( $vendor_ids, $user_id, $reassigned_user );

            // update the meta values
            $wc_coupon->update_meta_data( $meta_key, implode( ',', $vendor_ids ) );
            $wc_coupon->save();

            if ( $user_assigned ) {
                $this->attach_coupon_to_seller( $wc_coupon->get_id() );
            }
        }
    }

    /**
     * Add coupon id to vendors meta list
     *
     * @since 3.7.4
     *
     * @param array $vendors_ids
     * @param int $coupon_id
     *
     * @return void
     */
    private function add_coupon_id_to_vendors_meta_list( $vendors_ids, $coupon_id, $meta_key ) {
        foreach ( $vendors_ids as $vendors_id ) {
            $coupons = get_user_meta( $vendors_id, $meta_key, true );
            $coupons = ! empty( $coupons ) ? $coupons : [];

            if ( ! in_array( $coupon_id, $coupons, true ) ) {
                array_push( $coupons, $coupon_id ); //add coupon id to list
                update_user_meta( $vendors_id, $meta_key, $coupons ); // save the new list
            }
        }
    }
}
