<?php

use WeDevs\Dokan\Cache;
use WeDevs\DokanPro\Dashboard\ProfileProgress;

/**
 *  General Functions for Dokan Pro features
 *
 *  @since 2.4
 *
 *  @package dokan
 */

/**
 * Returns Current User Profile progress bar HTML
 *
 * @since 2.1
 *
 * @return output
 */
if ( ! function_exists( 'dokan_get_profile_progressbar' ) ) {
    function dokan_get_profile_progressbar() {
        $profile_progress = new ProfileProgress();
        $progress_data    = $profile_progress->get();

        if ( isset( $progress_data['closed_by_user'] ) && $progress_data['closed_by_user'] ) {
            return '';
        }

        ob_start();
        dokan_get_template_part(
            'global/profile-progressbar', '', array(
                'pro'       => true,
                'progress'  => $progress_data['progress'],
                'next_todo' => $progress_data['next_todo'],
                'value'     => $progress_data['progress_vals'],
                'next_url'  => $progress_data['next_todo_slug'],
                'next_text' => $progress_data['next_progress_text'],
            )
        );
        return ob_get_clean();
    }
}

/**
 * Dokan progressbar translated string
 *
 * @param  string $string
 * @param  int $value
 * @param  int $progress
 *
 * @return string
 */
function dokan_progressbar_translated_string( $string = '', $value = 15, $progress = 0 ) {
    $translated_string = '';
    if ( 100 === absint( $progress ) ) :
        $translated_string = __( 'Congratulation, your profile is fully completed', 'dokan' );
    else :
        switch ( $string ) {
            case 'profile_picture_val':
                // translators: %s%% is the progressbar progress value
                $translated_string = sprintf( __( 'Add Profile Picture to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );
                break;

            case 'phone_val':
                // translators: %s%% is the progressbar progress value
                $translated_string = sprintf( __( 'Add Phone to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );
                break;

            case 'banner_val':
                // translators: %s%% is the progressbar progress value
                $translated_string = sprintf( __( 'Add Banner to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );
                break;

            case 'store_name_val':
                // translators: %s%% is the progressbar progress value
                $translated_string = sprintf( __( 'Add Store Name to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );
                break;

            case 'address_val':
                // translators: %s%% is the progressbar progress value
                $translated_string = sprintf( __( 'Add address to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );
                break;

            case 'payment_method_val':
                // translators: %s%% is the progressbar progress value
                $translated_string = sprintf( __( 'Add a Payment method to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );
                break;

            case 'map_val':
                // translators: %s%% is the progressbar progress value
                $translated_string = sprintf( __( 'Add Map location to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );
                break;

            case 'fb':
                // translators: %s%% is the progressbar progress value
                $translated_string = sprintf( __( 'Add facebook to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );
                break;

            case 'twitter':
                // translators: %s%% is the progressbar progress value
                $translated_string = sprintf( __( 'Add Twitter to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );
                break;

            case 'youtube':
                // translators: %s%% is the progressbar progress value
                $translated_string = sprintf( __( 'Add Youtube to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );
                break;

            case 'linkedin':
                // translators: %s%% is the progressbar progress value
                $translated_string = sprintf( __( 'Add LinkedIn to gain %s%% progress', 'dokan' ), number_format_i18n( $value ) );
                break;

            default:
                $translated_string = __( 'Start with adding a Banner to gain profile progress', 'dokan' );
                break;
        }
    endif;

    return apply_filters( 'dokan_progressbar_translated_string', $translated_string, $string, $value, $progress );
}

/**
 * Get refund counts, used in admin area
 *
 *  @since 2.4.11
 *  @since 3.0.0 Move the logic to Refund manager class
 *
 * @global WPDB $wpdb
 * @return array
 */
function dokan_get_refund_count( $seller_id = null ) {
    return dokan_pro()->refund->get_status_counts( $seller_id );
}


/**
 * Get get seller coupon
 *
 *  @since 2.4.12
 *
 * @param int $seller_id
 *
 * @return array
 */
function dokan_get_seller_coupon( $seller_id, $show_on_store = false ) {
    $args = array(
        'post_type'   => 'shop_coupon',
        'post_status' => 'publish',
        'author'      => $seller_id,
    );

    if ( $show_on_store ) {
        $args['meta_query'][] = array(
            'key'   => 'show_on_store',
            'value' => 'yes',
        );
    }

    $coupons = get_posts( $args );

    return $coupons;
}

/**
 * Get marketplace seller coupons
 *
 * @since 3.4.0
 *
 * @param int  $seller_id
 * @param bool $show_on_store
 *
 * @return array
 */
function dokan_get_marketplace_seller_coupon( $seller_id, $show_on_store = false ) {
    $args = array(
        'post_type'   => 'shop_coupon',
        'post_status' => 'publish',
    );

    if ( $show_on_store ) {
        $args['meta_query'][] = array(
            'key'   => 'admin_coupons_show_on_stores',
            'value' => 'yes',
        );
    }

    $coupons     = get_posts( $args );
    $get_coupons = array();

    if ( empty( $coupons ) ) {
        return $get_coupons;
    }

    foreach ( $coupons as $coupon ) {
        $vendors_ids     = get_post_meta( $coupon->ID, 'coupons_vendors_ids', true );
        $vendors_ids     = ! empty( $vendors_ids ) ? array_map( 'intval', explode( ',', $vendors_ids ) ) : [];
        $exclude_vendors = get_post_meta( $coupon->ID, 'coupons_exclude_vendors_ids', true );
        $exclude_vendors = ! empty( $exclude_vendors ) ? array_map( 'intval', explode( ',', $exclude_vendors ) ) : [];

        $coupon_meta = [
            'admin_coupons_enabled_for_vendor' => get_post_meta( $coupon->ID, 'admin_coupons_enabled_for_vendor', true ),
            'coupons_vendors_ids'              => $vendors_ids,
            'coupons_exclude_vendors_ids'      => $exclude_vendors,
        ];

        if ( dokan_is_admin_created_vendor_coupon_by_meta( $coupon_meta, $seller_id ) ) {
            $get_coupons[] = $coupon;
        }
    }

    return $get_coupons;
}

/**
* Get refund localize data
*
* @since 2.6
*
* @return void
**/
function dokan_get_refund_localize_data() {
    return array(
        'mon_decimal_point'             => wc_get_price_decimal_separator(),
        'remove_item_notice'            => __( 'Are you sure you want to remove the selected items? If you have previously reduced this item\'s stock, or this order was submitted by a customer, you will need to manually restore the item\'s stock.', 'dokan' ),
        'i18n_select_items'             => __( 'Please select some items.', 'dokan' ),
        'i18n_do_refund'                => __( 'Are you sure you wish to process this refund request? This action cannot be undone.', 'dokan' ),
        'i18n_delete_refund'            => __( 'Are you sure you wish to delete this refund? This action cannot be undone.', 'dokan' ),
        'remove_item_meta'              => __( 'Remove this item meta?', 'dokan' ),
        'ajax_url'                      => admin_url( 'admin-ajax.php' ),
        'order_item_nonce'              => wp_create_nonce( 'order-item' ),
        'post_id'                       => isset( $_GET['order_id'] ) ? absint( wp_unslash( $_GET['order_id'] ) ) : '',
        'currency_format_num_decimals'  => wc_get_price_decimals(),
        'currency_format_symbol'        => get_woocommerce_currency_symbol(),
        'currency_format_decimal_sep'   => esc_attr( wc_get_price_decimal_separator() ),
        'currency_format_thousand_sep'  => esc_attr( wc_get_price_thousand_separator() ),
        'currency_format'               => esc_attr( str_replace( array( '%1$s', '%2$s' ), array( '%s', '%v' ), get_woocommerce_price_format() ) ), // For accounting JS
        'round_at_subtotal'             => get_option( 'woocommerce_tax_round_at_subtotal', 'no' ),
        'rounding_precision'            => wc_get_rounding_precision(),
    );
}

/**
 * Get review page url of a seller
 *
 * @param int $user_id
 * @return string
 */
function dokan_get_review_url( $user_id ) {
    if ( ! $user_id ) {
        return '';
    }

    $userstore = dokan_get_store_url( $user_id );

    return apply_filters( 'dokan_get_seller_review_url', $userstore . 'reviews' );
}

/**
 *
 */
function dokan_render_order_table_items( $order_id ) {
    $data  = get_post_meta( $order_id );
    $order = new WC_Order( $order_id );

    dokan_get_template_part(
        'orders/views/html-order-items', '', array(
            'pro'   => true,
            'data'  => $data,
            'order' => $order,
        )
    );
}

/**
 * Get best sellers list
 *
 * @param  int $limit
 * @return array
 */
function dokan_get_best_sellers( $limit = 5 ) {
    global  $wpdb;

    $cache_key = 'best_seller_' . $limit;
    $seller    = Cache::get( $cache_key, 'widget' );

    if ( false === $seller ) {
        $qry = "SELECT seller_id, display_name, SUM( net_amount ) AS total_sell
            FROM {$wpdb->prefix}dokan_orders AS o,{$wpdb->users} AS u
            LEFT JOIN {$wpdb->usermeta} AS umeta on umeta.user_id=u.ID
            WHERE o.seller_id = u.ID AND umeta.meta_key = 'dokan_enable_selling' AND umeta.meta_value = 'yes'
            GROUP BY o.seller_id
            ORDER BY total_sell DESC LIMIT " . $limit;

        $seller = $wpdb->get_results( $qry );
        Cache::set( $cache_key, $seller, 'widget', 3600 * 6 );
    }

    return $seller;
}

/**
 * Get featured sellers list
 *
 * @param int $count
 *
 * @return array
 */
function dokan_get_feature_sellers( $count = 5 ) {
    $args = [
        'role__in'   => [ 'administrator', 'seller' ],
        'meta_query' => [
            [
                'key'   => 'dokan_feature_seller',
                'value' => 'yes',
            ],
            [
                'key'   => 'dokan_enable_selling',
                'value' => 'yes',
            ],
        ],
        'number'     => $count,
    ];

    $sellers = get_users( apply_filters( 'dokan_get_feature_sellers_args', $args ) );

    return $sellers;
}


/**
 * Generate Customer to Vendor migration template
 *
 * @since 2.6.4
 *
 * @param array $atts ShortCode attributes
 *
 * @return void Render template for account update
 */
if ( ! function_exists( 'dokan_render_customer_migration_template' ) ) {

    function dokan_render_customer_migration_template( $atts ) {
        ob_start();
        dokan_get_template_part( 'global/update-account', '', array( 'pro' => true ) );
        ?>
            <script>
            // Dokan Register
            jQuery(function($) {
                $('.user-role input[type=radio]').on('change', function() {
                    var value = $(this).val();

                    if ( value === 'seller') {
                        $('.show_if_seller').slideDown();
                        if ( $( '.tc_check_box' ).length > 0 )
                            $('input[name=register]').attr('disabled','disabled');
                    } else {
                        $('.show_if_seller').slideUp();
                        if ( $( '.tc_check_box' ).length > 0 )
                            $( 'input[name=register]' ).removeAttr( 'disabled' );
                    }
                });

               $( '.tc_check_box' ).on( 'click', function () {
                    var chk_value = $( this ).val();
                    if ( $( this ).prop( "checked" ) ) {
                        $( 'input[name=register]' ).removeAttr( 'disabled' );
                        $( 'input[name=dokan_migration]' ).removeAttr( 'disabled' );
                    } else {
                        $( 'input[name=register]' ).attr( 'disabled', 'disabled' );
                        $( 'input[name=dokan_migration]' ).attr( 'disabled', 'disabled' );
                    }
                } );

                if ( $( '.tc_check_box' ).length > 0 ){
                    $( 'input[name=dokan_migration]' ).attr( 'disabled', 'disabled' );
                }

                $('#company-name').on('focusout', function() {
                    var value = $(this).val().toLowerCase().replace(/-+/g, '').replace(/\s+/g, '-').replace(/[^a-z0-9-]/g, '');
                    $('#seller-url').val(value);
                    $('#url-alart').text( value );
                    $('#seller-url').focus();
                });

                $('#seller-url').on( 'keydown', function(e) {
                    var text = $(this).val();

                    // Allow: backspace, delete, tab, escape, enter and .
                    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 91, 109, 110, 173, 189, 190]) !== -1 ||
                         // Allow: Ctrl+A
                        (e.keyCode == 65 && e.ctrlKey === true) ||
                         // Allow: home, end, left, right
                        (e.keyCode >= 35 && e.keyCode <= 39)) {
                             // let it happen, don't do anything
                            return;
                    }

                    if ((e.shiftKey || (e.keyCode < 65 || e.keyCode > 90) && (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105) ) {
                        e.preventDefault();
                    }
                });

                $('#seller-url').on( 'keyup', function(e) {
                    $('#url-alart').text( $(this).val() );
                });

                $('#shop-phone').on( 'keydown', function(e) {
                    // Allow: backspace, delete, tab, escape, enter and .
                    if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 91, 107, 109, 110, 187, 189, 190]) !== -1 ||
                         // Allow: Ctrl+A
                        (e.keyCode == 65 && e.ctrlKey === true) ||
                         // Allow: home, end, left, right
                        (e.keyCode >= 35 && e.keyCode <= 39)) {
                             // let it happen, don't do anything
                             return;
                    }

                    // Ensure that it is a number and stop the keypress
                    if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
                        e.preventDefault();
                    }
                });

                $('#seller-url').on('focusout', function() {
                    var self = $(this),
                    data = {
                        action : 'shop_url',
                        url_slug : self.val(),
                        _nonce : dokan.nonce,
                    };

                    if ( self.val() === '' ) {
                        return;
                    }

                    var row = self.closest('.form-row');
                    row.block({ message: null, overlayCSS: { background: '#fff url(' + dokan.ajax_loader + ') no-repeat center', opacity: 0.6 } });

                    $.post( dokan.ajaxurl, data, function(resp) {

                        if ( resp == 0){
                            $('#url-alart').removeClass('text-success').addClass('text-danger');
                            $('#url-alart-mgs').removeClass('text-success').addClass('text-danger').text(dokan.seller.notAvailable);
                        } else {
                            $('#url-alart').removeClass('text-danger').addClass('text-success');
                            $('#url-alart-mgs').removeClass('text-danger').addClass('text-success').text(dokan.seller.available);
                        }

                        row.unblock();

                    } );

                });
            });
            </script>
        <?php

        return ob_get_clean();
    }
}

add_shortcode( 'dokan-customer-migration', 'dokan_render_customer_migration_template' );

/**
 * Send announcement email
 *
 * @since 2.8.2
 *
 * @param $announcement_id
 *
 * @return void
 */
function dokan_send_announcement_email( $announcement_id ) {
    dokan_pro()->announcement->trigger_mail( $announcement_id );
}

add_action( 'dokan_after_announcement_saved', 'dokan_send_announcement_email' );

/**
 * Send email for scheduled announcement
 *
 * @since 2.9.13
 *
 * @param WP_Post $post
 *
 * @return void
 */
function dokan_send_scheduled_announcement_email( $post ) {
    if ( 'dokan_announcement' !== $post->post_type ) {
        return;
    }

    dokan_pro()->announcement->trigger_mail( $post->ID );
}

add_action( 'future_to_publish', 'dokan_send_scheduled_announcement_email' );

/**
 * Set store categories
 *
 * @since 2.9.2
 *
 * @param int            $store_id
 * @param array|int|null $categories
 *
 * @return array|WP_Error Term taxonomy IDs of the affected terms.
 */
function dokan_set_store_categories( $store_id, $categories = null ) {
    if ( ! is_array( $categories ) ) {
        $categories = array( $categories );
    }

    $categories = array_map( 'absint', $categories );
    $categories = array_filter( $categories );

    if ( empty( $categories ) ) {
        $categories = array( dokan_get_default_store_category_id() );
    }

    return wp_set_object_terms( $store_id, $categories, 'store_category' );
}

/**
 * Checks if store category feature is on or off
 *
 * @since 2.9.2
 *
 * @return bool
 */
function dokan_is_store_categories_feature_on() {
    return 'none' !== dokan_get_option( 'store_category_type', 'dokan_general', 'none' );
}

/**
 * Get the default store category id
 *
 * @since 2.9.2
 *
 * @return int
 */
function dokan_get_default_store_category_id() {
    $default_category = get_option( 'default_store_category', null );

    if ( ! $default_category ) {
        $uncategorized_id = term_exists( 'Uncategorized', 'store_category' );

        if ( ! $uncategorized_id ) {
            $uncategorized_id = wp_insert_term( 'Uncategorized', 'store_category' );
        }

        $default_category = $uncategorized_id['term_id'];

        dokan_set_default_store_category_id( $default_category );
    }

    return absint( $default_category );
}

/**
 * Set the default store category id
 *
 * Make sure to category exists before calling
 * this function.
 *
 * @since 2.9.2
 *
 * @param int $category_id
 *
 * @return bool
 */
function dokan_set_default_store_category_id( $category_id ) {
    $general_settings = get_option( 'dokan_general', array() );
    $general_settings['store_category_default'] = $category_id;

    $updated_settings = update_option( 'dokan_general', $general_settings );
    $updated_default = update_option( 'default_store_category', $category_id, false );

    return $updated_settings && $updated_default;
}

/**
 * Check if the refund request is allowed to be approved
 *
 * @param int $order_id
 *
 * @return boolean
 */
function dokan_is_refund_allowed_to_approve( $order_id ) {
    if ( ! $order_id ) {
        return false;
    }

    $order                       = wc_get_order( $order_id );
    $order_status                = 'wc-' . $order->get_status();
    $active_order_status         = dokan_withdraw_get_active_order_status();

    if ( in_array( $order_status, $active_order_status ) ) {
        return true;
    }

    return false;
}

/**
 * Nomalize shipping postcode that contains '-' or space
 *
 * @since  2.9.14
 *
 * @param  string $code
 *
 * @return string
 */
function dokan_normalize_shipping_postcode( $code ) {
    return str_replace( [ ' ', '-' ], '', $code );
}

/**
 * Dokan add combine commission
 *
 * @since  2.9.14
 *
 * @param  float $earning  [earning for a vendor or admin]
 * @param  float $commission_rate
 * @param  string $commission_type
 * @param  float $additional_fee
 * @param  float $product_price
 * @param  int $order_id
 *
 * @return float
 */
function dokan_add_combine_commission( $earning, $commission_rate, $commission_type, $additional_fee, $product_price, $order_id ) {
    if ( 'combine' === $commission_type ) {
        // vendor will get 100 percent if commission rate > 100
        if ( $commission_rate > 100 ) {
            return (float) wc_format_decimal( $product_price );
        }

        // If `_dokan_item_total` returns `non-falsy` value that means, the request comes from the `order refund request`.
        // So modify `additional_fee` to the correct amount to get refunded. (additional_fee/item_total)*product_price.
        // Where `product_price` means item_total - refunded_total_for_item.
        $item_total    = get_post_meta( $order_id, '_dokan_item_total', true );
        $product_price = (float) wc_format_decimal( $product_price );
        if ( $order_id && $item_total ) {
            $order          = wc_get_order( $order_id );
            $additional_fee = ( $additional_fee / $item_total ) * $product_price;
        }

        $earning       = ( (float) $product_price * $commission_rate ) / 100;
        $total_earning = $earning + $additional_fee;
        $earning       = (float) $product_price - $total_earning;
    }

    return floatval( wc_format_decimal( $earning ) );
}

add_filter( 'dokan_prepare_for_calculation', 'dokan_add_combine_commission', 10, 6 );

/**
 * Dokan save admin additional_fee
 *
 * @since 2.9.14
 *
 * @param int $vendor_id
 * @param array $data
 *
 * @return void
 */
function dokan_save_admin_additional_commission( $vendor_id, $data ) {
    if ( ! current_user_can( 'manage_woocommerce' ) ) {
        return;
    }

    if ( isset( $data['admin_additional_fee'] ) ) {
        $vendor = dokan()->vendor->get( $vendor_id );
        $vendor->update_meta( 'dokan_admin_additional_fee', wc_format_decimal( $data['admin_additional_fee'] ) );
    }
}

add_action( 'dokan_before_update_vendor', 'dokan_save_admin_additional_commission', 10, 2 );

/**
 * Include Dokan Pro template
 *
 * Modules should have their own get
 * template function, like `dokan_geo_get_template`
 * used in Geolocation module.
 *
 * @since 3.0.0
 *
 * @param string $name
 * @param array  $args
 *
 * @return void
 */
function dokan_pro_get_template( $name, $args = [] ) {
    dokan_get_template( "$name.php", $args, 'dokan', trailingslashit( DOKAN_PRO_TEMPLATE_DIR ) );
}

/**
 * Dokan register deactivation hook description
 *
 * @param string $file     full file path
 * @param array|string $function callback function
 *
 * @return void
 */
function dokan_register_deactivation_hook( $file, $function ) {
    if ( file_exists( $file ) ) {
        require_once $file;
        $base_name = plugin_basename( $file );
        add_action( "dokan_deactivate_{$base_name}", $function );
    }
}

/**
 * Dokan is single seller mode enable
 *
 * @since 3.1.3
 *
 * @return boolean
 */
function dokan_is_single_seller_mode_enable() {
    $is_single_seller_mode = apply_filters_deprecated( 'dokan_signle_seller_mode', [ dokan_get_option( 'enable_single_seller_mode', 'dokan_general', 'off' ) ], '3.0.0', 'dokan_single_seller_mode' );

    return apply_filters( 'dokan_single_seller_mode', $is_single_seller_mode );
}


/**
 * Dokan get shipping tracking providers list
 *
 * @since 3.2.4
 *
 * @return array
 */
function dokan_shipping_status_tracking_providers_list() {
    $providers = array(
        'sp-australia-post' => array(
            'label' => __( 'Australia Post', 'dokan' ),
            'url'   => 'https://auspost.com.au/mypost/track/#/search?tracking={tracking_number}',
        ),
        'sp-canada-post' => array(
            'label' => __( 'Canada Post', 'dokan' ),
            'url'   => 'https://www.canadapost.ca/track-reperage/en#/home/?tracking={tracking_number}',
        ),
        'sp-city-link' => array(
            'label' => __( 'City Link', 'dokan' ),
            'url'   => 'https://www.citylinkexpress.com/tracking-result/?track0={tracking_number}',
        ),
        'sp-dhl' => array(
            'label' => __( 'DHL', 'dokan' ),
            'url'   => 'https://www.dhl.com/en/express/tracking.html?AWB={tracking_number}&brand=DHL',
        ),
        'sp-dpd' => array(
            'label' => __( 'DPD', 'dokan' ),
            'url'   => 'https://tracking.dpd.de/status/en_NL/parcel/{tracking_number}',
        ),
        'sp-fastway-south-africa' => array(
            'label' => __( 'Fastway South Africa', 'dokan' ),
            'url'   => 'https://www.fastway.co.za/our-services/track-your-parcel/?track={tracking_number}',
        ),
        'sp-fedex' => array(
            'label' => __( 'Fedex', 'dokan' ),
            'url'   => 'https://www.fedex.com/fedextrack/no-results-found?trknbr={tracking_number}',
        ),
        'sp-ontrac' => array(
            'label' => __( 'OnTrac', 'dokan' ),
            'url'   => 'https://www.ontrac.com/trackingdetail.asp/?track={tracking_number}',
        ),
        'sp-parcelforce' => array(
            'label' => __( 'ParcelForce', 'dokan' ),
            'url'   => 'https://www.parcelforce.com/track-trace/?trackNumber={tracking_number}',
        ),
        'sp-polish-shipping-providers' => array(
            'label' => __( 'Polish shipping providers', 'dokan' ),
            'url'   => 'https://www.parcelmonitor.com/track-poland/track-it-online/?pParcelIds={tracking_number}',
        ),
        'sp-royal-mail' => array(
            'label' => __( 'Royal Mail', 'dokan' ),
            'url'   => 'https://www.royalmail.com/track-your-item#/?track={tracking_number}',
        ),
        'sp-sapo' => array(
            'label' => __( 'SAPO', 'dokan' ),
            'url'   => 'https://tracking.postoffice.co.za/TrackNTrace/TrackNTrace.aspx?id={tracking_number}',
        ),
        'sp-tnt-express-consignment' => array(
            'label' => __( 'TNT Express (consignment)', 'dokan' ),
            'url'   => 'https://www.tnt.com/express/site/tracking.html/?track={tracking_number}',
        ),
        'sp-tnt-express-reference' => array(
            'label' => __( 'TNT Express (reference)', 'dokan' ),
            'url'   => 'https://www.tnt.com/express/site/tracking.html/?track={tracking_number}',
        ),
        'sp-fedex-sameday' => array(
            'label' => __( 'FedEx Sameday', 'dokan' ),
            'url'   => 'https://www.fedex.com/fedextrack/?action=track&tracknumbers={tracking_number}',
        ),
        'sp-ups' => array(
            'label' => __( 'UPS', 'dokan' ),
            'url'   => 'https://www.ups.com/track/?trackingNumber={tracking_number}',
        ),
        'sp-usps' => array(
            'label' => __( 'USPS', 'dokan' ),
            'url'   => 'https://tools.usps.com/go/TrackConfirmAction?tRef=fullpage&tLabels={tracking_number}',
        ),
        'sp-dhl-us' => array(
            'label' => __( 'DHL US', 'dokan' ),
            'url'   => 'https://www.dhl.com/us-en/home/tracking/tracking-global-forwarding.html?submit=1&tracking-id={tracking_number}',
        ),
        'sp-other' => array(
            'label' => __( 'Other', 'dokan' ),
            'url'   => '',
        ),
    );

    return apply_filters( 'dokan_shipping_status_tracking_providers_list', $providers );
}

/**
 * Dokan get shipping tracking providers list
 *
 * @since 3.2.4
 *
 * @return array
 */
function dokan_get_shipping_tracking_providers_list() {
    $providers = [];

    if ( ! empty( dokan_shipping_status_tracking_providers_list() ) && is_array( dokan_shipping_status_tracking_providers_list() ) ) {
        foreach ( dokan_shipping_status_tracking_providers_list() as $data_key => $data_label ) {
            $providers[ $data_key ] = $data_label['label'];
        }
    }

    return apply_filters( 'dokan_get_shipping_tracking_providers_list', $providers );
}

/**
 * Dokan get shipping tracking default providers list
 *
 * @since 3.2.4
 *
 * @return array
 */
function dokan_get_shipping_tracking_default_providers_list() {
    $providers = array(
        'sp-dhl'                       => 'sp-dhl',
        'sp-dpd'                       => 'sp-dpd',
        'sp-fedex'                     => 'sp-fedex',
        'sp-polish-shipping-providers' => 'sp-polish-shipping-providers',
        'sp-ups'                       => 'sp-ups',
        'sp-usps'                      => 'sp-usps',
        'sp-other'                     => 'sp-other',
    );

    return apply_filters( 'dokan_shipping_status_default_providers', $providers );
}

/**
 * Dokan get shipping tracking default providers list
 *
 * @since 3.2.4
 *
 * @param string $key_data
 *
 * @return string
 */
function dokan_get_shipping_tracking_status_by_key( $key_data ) {
    $status_list = dokan_get_option( 'shipping_status_list', 'dokan_shipping_status_setting' );

    if ( ! empty( $status_list ) && is_array( $status_list ) ) {
        foreach ( $status_list as $s_status ) {
            if ( isset( $s_status['id'] ) && $s_status['id'] === $key_data ) {
                return $s_status['value'];
            }
        }
    }

    return '';
}

/**
 * Dokan get shipping tracking provider name by key
 *
 * @since 3.2.4
 *
 * @param string $key_data
 * @param string $return_type
 * @param string $tracking_number
 *
 * @return string
 */
function dokan_get_shipping_tracking_provider_by_key( $key_data, $return_type = 'label', $tracking_number = '' ) {
    if ( empty( $key_data ) ) {
        return;
    }

    $providers_list = dokan_shipping_status_tracking_providers_list();

    if ( ! empty( $providers_list ) && is_array( $providers_list ) && isset( $providers_list[ $key_data ] ) && isset( $providers_list[ $key_data ][ $return_type ] ) ) {
        $provider = $providers_list[ $key_data ][ $return_type ];

        if ( 'url' === $return_type && ! empty( $tracking_number ) ) {
            $provider = str_replace( '{tracking_number}', $tracking_number, $provider );
        }

        return $provider;
    }

    return 'N/A';
}

/**
 * Dokan get shipping tracking current status by order id
 *
 * @since 3.2.4
 *
 * @param id $order_id
 * @param id $need_label
 *
 * @return string
 */
function dokan_shipping_tracking_status_by_orderid( $order_id, $need_label = 0 ) {
    if ( empty( $order_id ) ) {
        return;
    }

    $order = dokan()->order->get( $order_id );

    if ( $order ) {
        $tracking_info = $order->get_meta( '_dokan_shipping_status_tracking_info' );
    }

    if ( is_array( $tracking_info ) && isset( $tracking_info['status'] ) ) {
        return $need_label === 0 ? $tracking_info['status'] : dokan_get_shipping_tracking_status_by_key( $tracking_info['status'] );
    }
}

/**
 * Dokan get shipping tracking current provider by oder id
 *
 * @since 3.2.4
 *
 * @param id $order_id
 *
 * @return string
 */
function dokan_shipping_tracking_provider_by_orderid( $order_id ) {
    if ( empty( $order_id ) ) {
        return;
    }

    $order = dokan()->order->get( $order_id );

    if ( $order ) {
        $tracking_info = $order->get_meta( '_dokan_shipping_status_tracking_info' );
    }

    if ( is_array( $tracking_info ) && isset( $tracking_info['provider'] ) ) {
        return $tracking_info['provider'];
    }
}

/**
 * Get order current shipment status
 *
 * @since 3.2.4
 *
 * @param int $order_id
 *
 * @param mix
 */
function dokan_get_order_shipment_current_status( $order_id, $get_only_status = false ) {
    if ( empty( $order_id ) ) {
        return;
    }

    $cache_group = "seller_shipment_tracking_data_{$order_id}";
    $cache_key   = "order_shipment_tracking_status_{$order_id}";
    $get_status  = Cache::get( $cache_key, $cache_group );

    // early return if cached data found
    if ( false !== $get_status ) {
        if ( $get_only_status ) {
            return $get_status;
        }
        return dokan_get_order_shipment_status_html( $get_status );
    }

    $shipment_tracking_data = dokan_pro()->shipment->get_shipping_tracking_data( $order_id );

    // early return if no shipment tracking data found
    if ( empty( $shipment_tracking_data ) ) {
        $get_status = '--';
        // set cache
        Cache::set( $cache_key, $get_status, $cache_group );
        if ( $get_only_status ) {
            return $get_status;
        }
        return dokan_get_order_shipment_status_html( $get_status );
    }

    $order = wc_get_order( $order_id );
    // total item remaining for shipping
    $shipment_remaining_count = 0;
    // order line items total count
    $order_qty_count = 0;
    // total delivered shipping status count
    $delivered_count = isset( $shipment_tracking_data['shipping_status_count']['ss_delivered'] ) ? intval( $shipment_tracking_data['shipping_status_count']['ss_delivered'] ) : 0;
    // no of shipping item without cancel status
    $total_shipments = isset( $shipment_tracking_data['total_except_cancelled'] ) ? intval( $shipment_tracking_data['total_except_cancelled'] ) : 0;

    // count total order items
    $line_item_count = $shipment_tracking_data['line_item_count'];
    foreach ( $order->get_items() as $item_id => $item ) {
        // count remaining item
        $shipped_item   = isset( $line_item_count[ $item_id ] ) ? intval( $line_item_count[ $item_id ] ) : 0;
        $remaining_item = intval( $item['qty'] ) - $shipped_item;
        $shipment_remaining_count += $remaining_item;

        // order line item total count
        $order_qty_count += intval( $item['qty'] );
    }

    if ( 0 === $shipment_remaining_count && $delivered_count === $total_shipments ) {
        $get_status = 'shipped';
    } elseif ( $shipment_remaining_count < $order_qty_count && $delivered_count > 0 ) {
        $get_status = 'partially';
    } else {
        $get_status = 'not_shipped';
    }

    Cache::set( $cache_key, $get_status, $cache_group );

    if ( $get_only_status ) {
        return $get_status;
    }

    return dokan_get_order_shipment_status_html( $get_status );
}

/**
 * Get main order current shipment status
 *
 * @since 3.2.4
 *
 * @param int $order_id
 *
 * @param mix
 */
function dokan_get_main_order_shipment_current_status( $order_id ) {
    if ( empty( $order_id ) ) {
        return;
    }

    $user_id     = dokan_get_current_user_id();
    $cache_group = "seller_shipment_tracking_data_{$order_id}";
    $cache_key   = "order_shipment_tracking_status_{$order_id}";
    $get_status  = Cache::get( $cache_key, $cache_group );

    if ( false === $get_status ) {
        $sub_orders = get_children(
            array(
                'post_parent' => $order_id,
                'post_type'   => 'shop_order',
            )
        );

        $shipped       = 0;
        $partially     = 0;
        $others_status = 0;
        $count_total   = 0;

        if ( $sub_orders ) {
            foreach ( $sub_orders as $order_post ) {
                $get_status = dokan_get_order_shipment_current_status( $order_post->ID, true );

                if ( 'shipped' === $get_status ) {
                    $shipped++;
                }

                if ( 'partially' === $get_status ) {
                    $partially++;
                }

                if ( 'shipped' === $get_status || 'partially' === $get_status || 'not_shipped' === $get_status ) {
                    $others_status++;
                }

                $count_total++;
            }
        }

        if ( $count_total === $shipped ) {
            $get_status = 'shipped';
        } elseif ( $partially > 0 || $shipped > 0 ) {
            $get_status = 'partially';
        } elseif ( $others_status > 0 ) {
            $get_status = 'not_shipped';
        } else {
            $get_status = '--';
        }

        Cache::set( $cache_key, $get_status, $cache_group );
    }

    return dokan_get_order_shipment_status_html( $get_status );
}

/**
 * Get order current shipment status html view
 *
 * @since 3.2.4
 *
 * @param string $get_status
 *
 * @param string
 */
function dokan_get_order_shipment_status_html( $get_status ) {
    if ( 'shipped' === $get_status ) {
        return sprintf( '<span class="dokan-label dokan-label-success">%s</span>', apply_filters( 'dokan_shipment_status_label_shipped', __( 'Shipped', 'dokan' ) ) );
    } elseif ( 'partially' === $get_status ) {
        return sprintf( '<span class="dokan-label dokan-label-info">%s</span>', apply_filters( 'dokan_shipment_status_label_partially_shipped', __( 'Partially', 'dokan' ) ) );
    } elseif ( 'not_shipped' === $get_status ) {
        return sprintf( '<span class="dokan-label dokan-label-default">%s</span>', apply_filters( 'dokan_shipment_status_label_not_shipped', __( 'Not-Shipped', 'dokan' ) ) );
    } else {
        return apply_filters( 'dokan_shipment_status_label_null', '--' );
    }
}

/**
 * Shipping clear cache values by group name
 *
 * @since 3.2.4
 *
 * @param int $order_id
 *
 * @return void
 */
function dokan_shipment_cache_clear_group( $order_id ) {
    $group                    = 'seller_shipment_tracking_data_' . $order_id;
    $tracking_data_key        = 'shipping_tracking_data_' . $order_id;
    $tracking_status_key      = 'order_shipment_tracking_status_' . $order_id;

    Cache::delete( $tracking_data_key, $group );
    Cache::delete( $tracking_status_key, $group );
}

/**
 * This method will return a random string
 *
 * @param int $length should be positive even number
 *
 * @return string
 */
function dokan_get_random_string( $length = 8 ) {
    // ensure a minimum length
    if ( ! isset( $length ) || $length < 4 ) {
        $length = 8;
    }
    // make length as even number
    if ( $length % 2 !== 0 ) {
        $length++;
    }
    // get random bytes via available methods
    $random_bytes = '';
    if ( function_exists( 'random_bytes' ) ) {
        try {
            $random_bytes = random_bytes( $length / 2 );
        } catch ( TypeError $e ) {
            $random_bytes = '';
        } catch ( Error $e ) {
            $random_bytes = '';
        } catch ( Exception $e ) {
            $random_bytes = '';
        }
    }
    // random_bytes failed, try another method
    if ( empty( $random_bytes ) && function_exists( 'openssl_random_pseudo_bytes' ) ) {
        $random_bytes = openssl_random_pseudo_bytes( $length / 2 );
    }

    if ( ! empty( $random_bytes ) ) {
        return bin2hex( $random_bytes );
    }
    // builtin method failed, try manual method
    return substr( str_shuffle( str_repeat( '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', wp_rand( 1, 10 ) ) ), 1, $length );
}

/**
 * Disable entire Dokan withdraw mechanism.
 *
 * @param bool $is_disabled
 *
 * @return bool
 */
function dokan_withdraw_disable_withdraw_operation( $is_disabled ) {
    return 'on' === dokan_get_option( 'hide_withdraw_option', 'dokan_withdraw', 'off' );
}
add_filter( 'dokan_withdraw_disable', 'dokan_withdraw_disable_withdraw_operation', 3 );

/**
 * Get script suffic and version for dokan
 *
 * @since DOKAN_PRO
 *
 * @return array first element is script file suffix and second element is script file version
 */
function dokan_get_script_suffix_and_version() {
    $suffix         = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
    $script_version = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? time() : DOKAN_PRO_PLUGIN_VERSION;

    return [ $suffix, $script_version ];
}

/**
 * Delete 'dokan_is_upgrading_db' from option table because we had some issue on lite-> v_3_6_4 and pro-> v_3_7_4
 * Make sure to REMOVE this function 'dokan_pro_temporary_remove_upgrader' on the next release 3.7.3
 *
 * @since DOKAN_SINCE
 *
 * @return void
 */
function dokan_pro_temporary_remove_upgrader() {
    if ( false === get_transient( 'dokan_pro_temporary_remove_upgrader' ) ) {
        // delete from database
        delete_option( 'dokan_is_upgrading_db' );

        set_transient( 'dokan_pro_temporary_remove_upgrader', '1', YEAR_IN_SECONDS );
    }

}
add_action( 'init', 'dokan_pro_temporary_remove_upgrader' );
