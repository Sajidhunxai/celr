<?php

namespace WeDevs\DokanPro\Shipping;

use Automattic\WooCommerce\Utilities\NumberUtil;
use WC_Countries;
use WC_Coupon;
use WeDevs\DokanPro\Shipping\Methods\ProductShipping;
use WeDevs\DokanPro\Shipping\Methods\VendorShipping;
use WeDevs\DokanPro\Shipping\ShippingZone;

/**
 * Dokan Shipping Class
 *
 * @author weDevs
 */
class Hooks {

    /**
     * Load automatically when class inistantiate
     *
     * @since 2.4
     *
     * @uses actions|filter hooks
     */
    public function __construct() {
        add_action( 'woocommerce_shipping_methods', array( $this, 'register_shipping' ) );
        add_filter( 'dokan_settings_selling_option_vendor_capability', array( $this, 'add_settings_shipping_tab' ), 20 );
        add_action( 'woocommerce_product_tabs', array( $this, 'register_product_tab' ) );
        add_action( 'woocommerce_after_checkout_validation', array( $this, 'validate_country' ) );
        add_action( 'template_redirect', array( $this, 'handle_shipping' ) );
        add_filter( 'woocommerce_package_rates', array( $this, 'calculate_shipping_tax' ), 10, 2 );
        add_filter( 'woocommerce_shipping_packages', array( $this, 'filter_packages' ) );
        add_action( 'woocommerce_delete_shipping_zone', array( $this, 'delete_shipping_zone_data' ), 35, 1 );
        add_action( 'woocommerce_after_shipping_zone_object_save', array( $this, 'vendor_zone_data_sync' ), 10, 2 );
        add_filter( 'woocommerce_shipping_free_shipping_is_available', array( $this, 'handle_free_shipping_validity' ), 10, 3 );
    }

    /**
     * Disable product shipping tab
     *
     * @since 3.3.0
     *
     * @param array $settings_fields
     *
     * @return array
     */
    public function add_settings_shipping_tab( $settings_fields ) {
        $settings_fields['disable_shipping_tab'] = [
            'name'               => 'disable_shipping_tab',
            'label'              => __( 'Disable Shipping Tab', 'dokan' ),
            'desc'               => __( 'Disable shipping tab on single product page', 'dokan' ),
            'type'               => 'switcher',
            'default'            => 'off',
            'refresh_after_save' => true,
        ];

        return $settings_fields;
    }

    /**
     * Register shipping method
     *
     * @since 2.0
     *
     * @param array $methods
     *
     * @return array
     */
    public function register_shipping( $methods ) {
        if ( 'sell_digital' === dokan_get_option( 'disable_shipping_tab', 'dokan_selling', 'off' ) ) {
            return $methods;
        }

        $methods['dokan_product_shipping'] = ProductShipping::class;
        $methods['dokan_vendor_shipping']  = VendorShipping::class;

        return $methods;
    }

    /**
     * Validate the shipping area
     *
     * @since 2.0
     *
     * @param  array $posted
     *
     * @return void
     */
    public function validate_country( $posted ) {
        $shipping_method = WC()->session->get( 'chosen_shipping_methods' );

        // per product shipping was not chosen
        if ( ! is_array( $shipping_method ) || ! in_array( 'dokan_product_shipping', $shipping_method, true ) ) {
            return;
        }

        if ( isset( $posted['ship_to_different_address'] ) && $posted['ship_to_different_address'] === '1' ) {
            $shipping_country = $posted['shipping_country'];
        } else {
            $shipping_country = $posted['billing_country'];
        }

        // echo $shipping_country;
        $packages = WC()->shipping->get_packages();

        reset( $packages );

        if ( ! isset( $packages[0]['contents'] ) ) {
            return;
        }

        $products = array();

        foreach ( $packages as $package ) {
            array_push( $products, $package['contents'] );
        }

        $destination_country = isset( $packages[0]['destination']['country'] ) ? $packages[0]['destination']['country'] : '';
        $destination_state   = isset( $packages[0]['destination']['state'] ) ? $packages[0]['destination']['state'] : '';

        // hold all the errors
        $errors = array();

        foreach ( $products as $key => $product ) {
            $dokan_regular_shipping = new ProductShipping();

            foreach ( $product as $product_obj ) {
                $seller_id = get_post_field( 'post_author', $product_obj['product_id'] );

                if ( ! $dokan_regular_shipping->is_method_enabled() ) {
                    continue;
                }

                if ( ! ProductShipping::is_shipping_enabled_for_seller( $seller_id ) ) {
                    continue;
                }

                if ( ProductShipping::is_product_disable_shipping( $product_obj['product_id'] ) ) {
                    continue;
                }

                $dps_country_rates = get_user_meta( $seller_id, '_dps_country_rates', true );
                $dps_state_rates   = get_user_meta( $seller_id, '_dps_state_rates', true );

                $has_found   = false;
                $dps_country = ( isset( $dps_country_rates ) ) ? $dps_country_rates : array();
                $dps_state   = ( isset( $dps_state_rates[ $destination_country ] ) ) ? $dps_state_rates[ $destination_country ] : array();

                if ( array_key_exists( $destination_country, $dps_country ) ) {
                    if ( $dps_state ) {
                        if ( array_key_exists( $destination_state, $dps_state ) ) {
                            $has_found = true;
                        } elseif ( array_key_exists( 'everywhere', $dps_state ) ) {
                            $has_found = true;
                        }
                    } else {
                        $has_found = true;
                    }
                } elseif ( array_key_exists( 'everywhere', $dps_country ) ) {
                        $has_found = true;
                }

                if ( ! $has_found ) {
                    $errors[] = sprintf( '<a href="%s">%s</a>', get_permalink( $product_obj['product_id'] ), get_the_title( $product_obj['product_id'] ) );
                }
            }
        }

        if ( $errors ) {
            if ( count( $errors ) === 1 ) {
                // translators: Error message.
                $message = sprintf( __( 'This product does not ship to your chosen location: %s', 'dokan' ), implode( ', ', $errors ) );
            } else {
                // translators: Error message.
                $message = sprintf( __( 'These products do not ship to your chosen location.: %s', 'dokan' ), implode( ', ', $errors ) );
            }

            wc_add_notice( $message, 'error' );
        }
    }

    /**
     *  Handle Shipping post submit
     *
     *  @since  2.0
     *
     *  @return void
     */
    public function handle_shipping() {
        if ( ! is_user_logged_in() ) {
            return;
        }

        if ( ! dokan_is_user_seller( get_current_user_id() ) ) {
            return;
        }

        if ( isset( $_POST['dokan_update_shipping_options'] ) && isset( $_POST['dokan_shipping_form_field_nonce'] ) && wp_verify_nonce( sanitize_key( wp_unslash( $_POST['dokan_shipping_form_field_nonce'] ) ), 'dokan_shipping_form_field' ) ) {
            if ( ! current_user_can( 'dokan_view_store_shipping_menu' ) ) {
                wp_die( __( 'You have no access to save this shipping options', 'dokan' ) );
            }

            $user_id = dokan_get_current_user_id();
            $s_rates = array();
            $rates   = array();

            // Additional extra code

            if ( isset( $_POST['dps_enable_shipping'] ) ) {
                update_user_meta( $user_id, '_dps_shipping_enable', $_POST['dps_enable_shipping'] );
            }

            if ( isset( $_POST['dokan_shipping_type'] ) ) {
                update_user_meta( $user_id, '_dokan_shipping_type', $_POST['dokan_shipping_type'] );
            }

            if ( isset( $_POST['dps_shipping_type_price'] ) ) {
                update_user_meta( $user_id, '_dps_shipping_type_price', $_POST['dps_shipping_type_price'] );
            }

            if ( isset( $_POST['dps_additional_product'] ) ) {
                update_user_meta( $user_id, '_dps_additional_product', $_POST['dps_additional_product'] );
            }

            if ( isset( $_POST['dps_additional_qty'] ) ) {
                update_user_meta( $user_id, '_dps_additional_qty', $_POST['dps_additional_qty'] );
            }

            if ( isset( $_POST['dps_pt'] ) ) {
                update_user_meta( $user_id, '_dps_pt', $_POST['dps_pt'] );
            }

            if ( isset( $_POST['dps_ship_policy'] ) ) {
                update_user_meta( $user_id, '_dps_ship_policy', $_POST['dps_ship_policy'] );
            }

            if ( isset( $_POST['dps_refund_policy'] ) ) {
                update_user_meta( $user_id, '_dps_refund_policy', $_POST['dps_refund_policy'] );
            }

            if ( isset( $_POST['dps_form_location'] ) ) {
                update_user_meta( $user_id, '_dps_form_location', $_POST['dps_form_location'] );
            }

            if ( isset( $_POST['dps_country_to'] ) ) {
                foreach ( $_POST['dps_country_to'] as $key => $value ) {
                    $country = $value;
                    $c_price = wc_format_decimal( $_POST['dps_country_to_price'][ $key ] );

                    if ( ! $c_price && empty( $c_price ) ) {
                        $c_price = 0;
                    }

                    if ( ! empty( $value ) ) {
                        $rates[ $country ] = $c_price;
                    }
                }
            }

            update_user_meta( $user_id, '_dps_country_rates', $rates );

            if ( isset( $_POST['dps_state_to'] ) ) {
                foreach ( $_POST['dps_state_to'] as $country_code => $states ) {
                    foreach ( $states as $key_val => $name ) {
                        $country_c = $country_code;
                        $state_code = $name;
                        $s_price = wc_format_decimal( $_POST['dps_state_to_price'][ $country_c ][ $key_val ] );

                        if ( ! $s_price || empty( $s_price ) ) {
                            $s_price = 0;
                        }

                        if ( ! empty( $name ) ) {
                            $s_rates[ $country_c ][ $state_code ] = $s_price;
                        }
                    }
                }
            }

            update_user_meta( $user_id, '_dps_state_rates', $s_rates );

            do_action( 'dokan_after_shipping_options_updated', $rates, $s_rates );

            $shipping_url = dokan_get_navigation_url( 'settings/regular-shipping' );
            wp_redirect( add_query_arg( array( 'message' => 'shipping_saved' ), $shipping_url ) );
            exit();
        }
    }

    /**
     * Adds a seller tab in product single page
     *
     * @since 2.0
     *
     * @param array $tabs
     *
     * @return array
     */
    public function register_product_tab( $tabs ) {
        if ( 'on' === dokan_get_option( 'disable_shipping_tab', 'dokan_selling', 'off' ) ) {
            return $tabs;
        }

        global $post;

        if ( get_post_meta( $post->ID, '_disable_shipping', true ) === 'yes' ) {
            return $tabs;
        }

        if ( get_post_meta( $post->ID, '_downloadable', true ) === 'yes' ) {
            return $tabs;
        }

        if ( 'yes' !== get_option( 'woocommerce_calc_shipping' ) ) {
            return $tabs;
        }

        if ( 'sell_digital' === dokan_pro()->digital_product->get_selling_product_type() ) {
            return $tabs;
        }

        $tabs['shipping'] = array(
            'title'    => __( 'Shipping', 'dokan' ),
            'priority' => 12,
            'callback' => array( $this, 'shipping_tab' ),
        );

        return $tabs;
    }

    /**
     * Callback for Register_prouduct_tab function
     *
     * @since 2.0
     *
     * @return void
     */
    public function shipping_tab() {
        global $wpdb, $post;

        $vendor_id = $post->post_author;

        $shipping_zone = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT locations.zone_id, locations.seller_id, locations.location_type as vendor_location_type, locations.location_code as vendor_location_code, wc_zones.location_code, wc_zones.location_type FROM {$wpdb->prefix}dokan_shipping_zone_locations as locations INNER JOIN {$wpdb->prefix}woocommerce_shipping_zone_locations as wc_zones ON locations.zone_id = wc_zones.zone_id INNER JOIN {$wpdb->prefix}dokan_shipping_zone_methods as dokan_methods ON dokan_methods.zone_id = locations.zone_id AND dokan_methods.seller_id = locations.seller_id WHERE locations.seller_id =%d AND locations.location_type != 'postcode' ORDER BY wc_zones.zone_id ASC", $vendor_id
            ), ARRAY_A
        );

        $_overwrite_shipping     = get_post_meta( $post->ID, '_overwrite_shipping', true );
        $dps_processing          = get_user_meta( $vendor_id, '_dps_pt', true );
        $from                    = get_user_meta( $vendor_id, '_dps_form_location', true );
        $dps_country_rates       = get_user_meta( $vendor_id, '_dps_country_rates', true );
        $shipping_policy         = get_user_meta( $vendor_id, '_dps_ship_policy', true );
        $refund_policy           = get_user_meta( $vendor_id, '_dps_refund_policy', true );
        $product_processing_time = get_post_meta( $post->ID, '_dps_processing_time', true );
        $processing_time         = $dps_processing;

        if ( 'yes' === $_overwrite_shipping ) {
            $processing_time = ( $product_processing_time ) ? $product_processing_time : $dps_processing;
        }

        $country_obj = new WC_Countries();
        $countries   = $country_obj->countries;
        $states      = $country_obj->states;
        $continents  = $country_obj->get_continents();

        $shipping_countries  = '';
        $shipping_states     = '';
        $shipping_continents = '';
        $location_code       = '';
        $check_countries     = array();
        $check_states        = array();
        $check_continents    = array();

        if ( $shipping_zone ) {
            foreach ( $shipping_zone as $zone ) {
                $location_code = $zone['vendor_location_code'];

                if ( $zone['vendor_location_type'] === 'state' ) {
                    $location_codes = explode( ':', $location_code );
                    $country_code   = isset( $location_codes[0] ) ? $location_codes[0] : '';
                    $state_code     = isset( $location_codes[1] ) ? $location_codes[1] : '';

                    if ( isset( $states[ $country_code ][ $state_code ] ) && isset( $countries[ $country_code ] ) && ! in_array( $states[ $country_code ][ $state_code ], $check_states, true ) ) {
                        $get_state_name = $states[ $country_code ][ $state_code ];

                        $check_states[ $get_state_name ] = $get_state_name;
                        $shipping_states                .= $get_state_name . ' (' . $countries[ $country_code ] . '), ';
                    }
                }

                if ( $zone['vendor_location_type'] === 'country' && $countries[ $location_code ] && ! in_array( $countries[ $location_code ], $check_countries, true ) ) {
                    $location_code                     = $countries[ $location_code ];
                    $check_countries[ $location_code ] = $location_code;
                    $shipping_countries               .= $location_code . ', ';
                }

                if ( $zone['vendor_location_type'] === 'continent' && $continents[ $location_code ] && ! in_array( $continents[ $location_code ]['name'], $check_continents, true ) ) {
                    $location_code                      = $continents[ $location_code ]['name'];
                    $check_continents[ $location_code ] = $location_code;
                    $shipping_continents               .= $location_code . ', ';
                }
            }
        }
        ?>

        <?php if ( $shipping_continents ) { ?>
            <p>
                <?php esc_html_e( 'Shipping Continents', 'dokan' ); ?>:
                <strong><?php echo rtrim( $shipping_continents, ', ' ); ?></strong>
            </p>
            <hr>
        <?php } ?>

        <?php if ( $shipping_countries ) { ?>
            <p>
                <?php esc_html_e( 'Shipping Countries', 'dokan' ); ?>:
                <strong><?php echo rtrim( $shipping_countries, ', ' ); ?></strong>
            </p>
            <hr>
        <?php } ?>

        <?php if ( $shipping_states ) { ?>
            <p>
                <?php esc_html_e( 'Shipping States', 'dokan' ); ?>:
                <strong><?php echo rtrim( $shipping_states, ', ' ); ?></strong>
            </p>
            <hr>
        <?php } ?>

        <?php if ( $processing_time ) { ?>
            <p>
                <strong>
                    <?php esc_html_e( 'Ready to ship in', 'dokan' ); ?> <?php echo dokan_get_processing_time_value( $processing_time ); ?>

                    <?php
                    if ( $from ) {
                        echo __( 'from', 'dokan' ) . ' ' . $countries[ $from ];
                    }
                    ?>
                </strong>
            </p>
            <hr>
        <?php } ?>

        <?php if ( $shipping_policy ) { ?>
            <p>&nbsp;</p>
            <strong><?php esc_html_e( 'Shipping Policy', 'dokan' ); ?></strong>
            <hr>
            <?php echo wpautop( $shipping_policy ); ?>
        <?php } ?>

        <?php if ( $refund_policy ) { ?>
            <hr>
            <p>&nbsp;</p>
            <strong><?php esc_html_e( 'Refund Policy', 'dokan' ); ?></strong>
            <hr>
            <?php echo wpautop( $refund_policy ); ?>
        <?php } ?>
        <?php
    }

    /**
     * WooCommerce calculate taxes cart wise (cart as a whole), not vendor wise.
     * So if there is any tax for non-taxable product, lets remove that tax
     *
     * @since 3.0.3
     *
     * @see https://github.com/weDevsOfficial/dokan/issues/820
     * @see https://github.com/woocommerce/woocommerce/issues/20600
     *
     * @param \WC_Shipping_Rate $package_rates
     * @param array $packages
     *
     * @return array
     */
    public function calculate_shipping_tax( $package_rates, $package ) {
        if ( ! isset( $package['contents'] ) ) {
            return $package_rates;
        }

        foreach ( $package['contents'] as $pack ) {
            if ( ! isset( $pack['data'] ) || ! is_callable( [ $pack['data'], 'get_tax_status' ] ) ) {
                return $package_rates;
            }

            if ( 'none' !== $pack['data']->get_tax_status() ) {
                continue;
            }

            // so it's a non taxable shipping, lets remove the taxes
            foreach ( $package_rates as $shipping_rate ) {
                $rfc = new \ReflectionClass( $shipping_rate );

                if ( ! $rfc->hasProperty( 'data' ) ) {
                    return $package_rates;
                }

                $data = $rfc->getProperty( 'data' );
                $data->setAccessible( true );
                $data->setValue(
                    $shipping_rate,
                    array_merge(
                        $data->getValue( $shipping_rate ),
                        [
                            'taxes' => [],
                        ]
                    )
                );
            }
        }

        return $package_rates;
    }

    /**
     * Filter pakcages, remove shipping data from cart if no shipping is required.
     *
     * Vendor A sales digital product with no shipping, but Vendor B sales physical product with shipping.
     * When Vendor A’s product is added in the cart, there is no shipping as expected.
     * But on adding Vendor B’s product, shipping is shown for both products.
     *
     * We'll remove package only if all the products of a vendor are non-shippable
     *
     * @since 3.0.3
     *
     * @param array $packages
     *
     * @return array
     */
    public function filter_packages( $packages ) {
        $package_to_keep   = [];
        $package_to_remove = [];

        foreach ( $packages as $key => $package ) {
            if ( empty( $package['contents'] ) ) {
                return $package;
            }

            $p_seller_id = isset( $package['seller_id'] ) ? (int) $package['seller_id'] : 0;

            foreach ( $package['contents'] as $content ) {
                $product = ! empty( $content['product_id'] ) ? wc_get_product( $content['product_id'] ) : '';
                if ( $product && $product->needs_shipping() ) {
                    $seller_id = (int) get_post_field( 'post_author', $content['product_id'] );

                    if ( isset( $p_seller_id ) && $p_seller_id !== $seller_id ) {
                        $packages[ $key ]['seller_id'] = $seller_id;
                    }

                    array_push( $package_to_keep, $key );

                    // check if we already added same vendor under removed package
                    $item_exists_on_remove_package = array_search( $key, $package_to_remove, true );
                    if ( false !== $item_exists_on_remove_package ) {
                        unset( $package_to_remove[ $item_exists_on_remove_package ] );
                    }
                }

                if ( $product && ! $product->needs_shipping() && ! in_array( $key, $package_to_keep, true ) ) {
                    array_push( $package_to_remove, $key );
                }
            }
        }

        foreach ( $package_to_remove as $package ) {
            unset( $packages[ $package ] );
        }

        /**
         * @since 3.5.0
         */
        return apply_filters( 'dokan_shipping_packages', $packages, $package_to_keep );
    }

    /**
     * Delete shipping data when zone deleted from admin
     *
     * @since 3.2.2
     *
     * @param id $zone_id
     *
     * @return void
     */
    public function delete_shipping_zone_data( $zone_id ) {
        if ( $zone_id ) {
            global $wpdb;

            // Delete dokan shipping data when deleted zone from admin area
            $wpdb->delete( $wpdb->prefix . 'dokan_shipping_zone_locations', array( 'zone_id' => $zone_id ) );
            $wpdb->delete( $wpdb->prefix . 'dokan_shipping_zone_methods', array( 'zone_id' => $zone_id ) );

            do_action( 'dokan_delete_shipping_zone_data', $zone_id );
        }
    }

    /**
     * Vendors shipping data syncronize when zone update by admin
     *
     * @since 3.2.2
     *
     * @param \WC_Shipping_Zone $zone Shipping zone.
     * @param \WC_Data_Store    $data_store Shipping zone data store.
     */
    public function vendor_zone_data_sync( $zone, $data_store ) {
        if ( empty( $zone->get_id() ) ) {
            return;
        }

        $zone_data      = $zone->get_data();
        $zone_locations = $zone_data['zone_locations'];

        if ( empty( $zone_locations ) ) {
            return;
        }

        $all_vendors = dokan()->vendor->get_vendors( [ 'number' => -1, 'fields' => 'ID' ] ); //phpcs:ignore
        foreach ( $all_vendors as $vendor_id ) {
            $args = [
                'seller_id'         => $vendor_id,
                'zone'              => $zone,
                'zone_locations'    => $zone_locations,
            ];
            dokan_pro()->bg_sync_vendor_zone_data->push_to_queue( $args );
        }

        dokan_pro()->bg_sync_vendor_zone_data->save()->dispatch();
    }

    /**
     * Handle free shipping availability for vendors.
     *
     * @since 3.7.16
     *
     * @param bool $is_available Is available.
     * @param array $package Package to check with.
     * @param \WC_Shipping_Free_Shipping $shipping_instance Shipping instance.
     *
     * @return bool
     */
    public function handle_free_shipping_validity( bool $is_available, array $package, \WC_Shipping_Free_Shipping $shipping_instance ): bool {
        if ( ! $is_available ) {
            return false;
        }

        $has_coupon         = false;
        $has_met_min_amount = false;

        if ( in_array( $shipping_instance->requires, array( 'coupon', 'either', 'both' ), true ) ) {
            $coupons = $package['applied_coupons'];

            if ( $coupons ) {
                foreach ( $coupons as $code ) {
                    $coupon = new WC_Coupon( $code );
                    $discounts = new \WC_Discounts( WC()->cart );
                    $valid     = $discounts->is_coupon_valid( $coupon );
                    if ( ! is_wp_error( $valid ) && $valid && $coupon->get_free_shipping() ) {
                        $has_coupon = true;
                        break;
                    }
                }
            }
        }

        if ( in_array( $shipping_instance->requires, array( 'min_amount', 'either', 'both' ), true ) ) {
            $total          = 0.0;
            $discount_total = 0.0;
            $discount_tax   = 0.0;

            foreach ( $package['contents'] as $line_item ) {
                $total          += WC()->cart->display_prices_including_tax() ? ( $line_item['line_subtotal'] + $line_item['line_subtotal_tax'] ) : $line_item['line_subtotal'];
                $discount_total += ( $line_item['line_subtotal'] - $line_item['line_total'] );
                $discount_tax   += ( $line_item['line_subtotal_tax'] - $line_item['line_tax'] );
            }

            if ( WC()->cart->display_prices_including_tax() ) {
                $total = $total - $discount_tax;
            }

            if ( 'no' === $shipping_instance->ignore_discounts ) {
                $total = $total - $discount_total;
            }

            $total = NumberUtil::round( $total, wc_get_price_decimals() );

            if ( $total >= $shipping_instance->min_amount ) {
                $has_met_min_amount = true;
            }
        }

        switch ( $shipping_instance->requires ) {
            case 'min_amount':
                $is_available = $has_met_min_amount;
                break;
            case 'coupon':
                $is_available = $has_coupon;
                break;
            case 'both':
                $is_available = $has_met_min_amount && $has_coupon;
                break;
            case 'either':
                $is_available = $has_met_min_amount || $has_coupon;
                break;
            default:
                $is_available = true;
                break;
        }

        return $is_available;
    }
}
