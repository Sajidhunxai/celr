<?php

namespace WeDevs\DokanPro\Shipping\Methods;

/**
 * Table Rate Shipping Method Extender Class
 */

use WC_Eval_Math;
use WC_Shipping_Method;
use WeDevs\DokanPro\Shipping\ShippingZone;
use WeDevs\DokanPro\Shipping\SanitizeCost;

class VendorShipping extends WC_Shipping_Method {

    /**
     * Table Rates from Database
     */
    protected $options_save_name;

    /**
     * Table Rates from Database
     */
    public $default_option;

    /**
     * Cloning is forbidden. Will deactivate prior 'instances' users are running
     *
     * @since 4.0
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'Cloning this class could cause catastrophic disasters!', 'dokan' ), '4.0' );
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 4.0
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Unserializing is forbidden!', 'dokan' ), '4.0' );
    }

    /**
     * __construct function.
     *
     * @access public
     * @return void
     */
    public function __construct( $instance_id = 0 ) {
        $this->id                   = 'dokan_vendor_shipping';
        $this->instance_id          = absint( $instance_id );
        $this->method_title         = __( 'Vendor Shipping', 'dokan' );
        $this->method_description   = __( 'Charge varying rates based on user defined conditions', 'dokan' );
        $this->supports             = array( 'shipping-zones', 'instance-settings', 'instance-settings-modal' );
        $this->default              = '';

        // Initialize settings
        $this->init();

        // additional hooks for post-calculations settings
        add_filter( 'woocommerce_shipping_chosen_method', array( $this, 'select_default_rate' ), 10, 2 );
        add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_shipping_zone_method_deleted', array( $this, 'delete_vendor_shipping_methods' ), 10, 3 );
    }

    /**
     * Get items in package.
     *
     * @param  array $package
     *
     * @return int
     */
    public function get_package_item_qty( $package ) {
        $total_quantity = 0;
        foreach ( $package['contents'] as $item_id => $values ) {
            if ( $values['quantity'] > 0 && $values['data']->needs_shipping() ) {
                $total_quantity += $values['quantity'];
            }
        }

        return $total_quantity;
    }

    /**
     * Finds and returns shipping classes and the products with said class.
     *
     * @param mixed $package
     *
     * @return array
     */
    public function find_shipping_classes( $package ) {
        $found_shipping_classes = array();

        foreach ( $package['contents'] as $item_id => $values ) {
            if ( $values['data']->needs_shipping() ) {
                $found_class = $values['data']->get_shipping_class();

                if ( ! isset( $found_shipping_classes[ $found_class ] ) ) {
                    $found_shipping_classes[ $found_class ] = array();
                }

                $found_shipping_classes[ $found_class ][ $item_id ] = $values;
            }
        }

        return $found_shipping_classes;
    }

    /**
     * Init function.
     * initialize variables to be used
     *
     * @access public
     * @return void
     */
    public function init() {
        $this->instance_form_fields = array(
            'title' => array(
                'title'         => __( 'Method title', 'dokan' ),
                'type'          => 'text',
                'description'   => __( 'This controls the title which the user sees during checkout.', 'dokan' ),
                'default'       => __( 'Vendor Shipping', 'dokan' ),
                'desc_tip'      => true,
            ),
            'tax_status' => array(
                'title'         => __( 'Tax status', 'dokan' ),
                'type'          => 'select',
                'class'         => 'wc-enhanced-select',
                'default'       => 'taxable',
                'options'       => array(
                    'taxable'   => __( 'Taxable', 'dokan' ),
                    'none'      => _x( 'None', 'Tax status', 'dokan' ),
                ),
            ),
        );

        $this->title      = $this->get_option( 'title' );
        $this->tax_status = $this->get_option( 'tax_status' );
    }

    /**
     * Calculate_shipping function.
     *
     * @access public
     * @param array $package (default: array())
     * @return void
     */
    public function calculate_shipping( $package = array() ) {
        $rates = array();
        $zone = ShippingZone::get_zone_matching_package( $package );

        $seller_id = $package['seller_id'];

        if ( empty( $seller_id ) ) {
            return;
        }

        $shipping_methods = ShippingZone::get_shipping_methods( $zone->get_id(), $seller_id );

        if ( empty( $shipping_methods ) ) {
            return;
        }

        $sanitizer = new SanitizeCost();

        foreach ( $shipping_methods as $key => $method ) {
            $tax_rate  = ( $method['settings']['tax_status'] === 'none' ) ? false : '';
            $has_costs = false;
            $cost      = 0;

            if (
                'yes' !== $method['enabled'] ||
                'dokan_table_rate_shipping' === $method['id'] ||
                'dokan_distance_rate_shipping' === $method['id']
            ) {
                continue;
            }

            if ( $method['id'] === 'flat_rate' ) {
                $setting_cost = isset( $method['settings']['cost'] ) ? stripslashes_deep( $method['settings']['cost'] ) : '';

                if ( '' !== $setting_cost ) {
                    $has_costs = true;
                    $cost = $sanitizer->evaluate_cost(
                        $setting_cost, array(
                            'qty'  => $this->get_package_item_qty( $package ),
                            'cost' => $package['contents_cost'],
                        )
                    );
                }

                // Add shipping class costs.
                $shipping_classes = WC()->shipping->get_shipping_classes();

                if ( ! empty( $shipping_classes ) ) {
                    $found_shipping_classes = $this->find_shipping_classes( $package );
                    $highest_class_cost     = 0;
                    $calculation_type       = ! empty( $method['settings']['calculation_type'] ) ? $method['settings']['calculation_type'] : 'class';
                    foreach ( $found_shipping_classes as $shipping_class => $products ) {
                        // Also handles BW compatibility when slugs were used instead of ids
                        $shipping_class_term = get_term_by( 'slug', $shipping_class, 'product_shipping_class' );
                        $class_cost_string   = $shipping_class_term && $shipping_class_term->term_id
                                                ? ( ! empty( $method['settings'][ 'class_cost_' . $shipping_class_term->term_id ] ) ? stripslashes_deep( $method['settings'][ 'class_cost_' . $shipping_class_term->term_id ] ) : '' )
                                                : ( ! empty( $method['settings']['no_class_cost'] ) ? $method['settings']['no_class_cost'] : '' );

                        if ( '' === $class_cost_string ) {
                            continue;
                        }

                        $has_costs = true;

                        $class_cost = $sanitizer->evaluate_cost(
                            $class_cost_string, array(
                                'qty'  => array_sum( wp_list_pluck( $products, 'quantity' ) ),
                                'cost' => array_sum( wp_list_pluck( $products, 'line_total' ) ),
                            )
                        );

                        if ( 'class' === $calculation_type ) {
                            $cost += $class_cost;
                        } else {
                            $highest_class_cost = $class_cost > $highest_class_cost ? $class_cost : $highest_class_cost;
                        }
                    }

                    if ( 'order' === $calculation_type && $highest_class_cost ) {
                        $cost += $highest_class_cost;
                    }
                }
            } elseif ( 'free_shipping' === $method['id'] ) {
                $is_available = $this->free_shipping_is_available( $package, $method );

                if ( $is_available ) {
                    $cost      = '0';
                    $has_costs = true;
                }
            } else {
                if ( ! empty( $method['settings']['cost'] ) ) {
                    $has_costs = true;
                    $cost      = $method['settings']['cost'];
                } else {
                    $has_costs = true;
                    $cost      = '0';
                }
            }

            if ( ! $has_costs ) {
                continue;
            }

            $rates[] = array(
                'id'          => $this->get_method_rate_id( $method ),
                'label'       => $method['title'],
                'cost'        => $cost,
                'description' => ! empty( $method['settings']['description'] ) ? $method['settings']['description'] : '',
                'taxes'       => $tax_rate,
                'default'     => 'off',
            );
        }

        // send shipping rates to WooCommerce
        if ( is_array( $rates ) && count( $rates ) > 0 ) {

            // cycle through rates to send and alter post-add settings
            foreach ( $rates as $key => $rate ) {
                $this->add_rate(
                    array(
                        'id'        => $rate['id'],
                        'label'     => apply_filters( 'dokan_vendor_shipping_rate_label', $rate['label'], $rate ),
                        'cost'      => $rate['cost'],
                        'meta_data' => array( 'description' => $rate['description'] ),
                        'package'   => $package,
                        'taxes'     => $rate['taxes'],
                    )
                );

                if ( $rate['default'] === 'on' ) {
                    $this->default = $rate['id'];
                }
            }
        }
    }

    /**
     * See if free shipping is available based on the package and cart.
     *
     * @param array $package Shipping package.
     *
     * @return bool
     */
    public function free_shipping_is_available( $package, $method ) {
        $has_met_min_amount    = false;
        $min_amount            = ! empty( $method['settings']['min_amount'] ) ? $method['settings']['min_amount'] : 0;
        $apply_before_discount = ! empty( $method['settings']['apply_before_coupon_discount'] ) && 'true' === $method['settings']['apply_before_coupon_discount'];

        $line_subtotal      = wp_list_pluck( $package['contents'], 'line_subtotal', null );
        $line_total         = wp_list_pluck( $package['contents'], 'line_total', null );
        $discount_total     = array_sum( $line_subtotal ) - array_sum( $line_total );
        $line_subtotal_tax  = wp_list_pluck( $package['contents'], 'line_subtotal_tax', null );
        $line_total_tax     = wp_list_pluck( $package['contents'], 'line_tax', null );
        $discount_tax_total = array_sum( $line_subtotal_tax ) - array_sum( $line_total_tax );
        $total              = array_sum( $line_subtotal );

        if ( version_compare( WC_VERSION, '3.7.2', '<' ) ) {
            $total += array_sum( $line_subtotal_tax );
        }

        if ( WC()->cart->display_prices_including_tax() ) {
            $total = round( $total - ( $discount_total + $discount_tax_total ), wc_get_price_decimals() );
        } else {
            $total = round( $total - $discount_total, wc_get_price_decimals() );
        }

        if ( $apply_before_discount ) {
            $total += $this->get_coupon_discount_amount( $package );
        }

        if ( $total >= $min_amount ) {
            $has_met_min_amount = true;
        }

        return apply_filters( 'dokaan_shipping_free_shipping_is_available', $has_met_min_amount, $package, $method );
    }

    /**
     * Is available in specific zone locations
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function is_available( $package ) {
        $seller_id = $package['seller_id'];

        if ( empty( $seller_id ) ) {
            return false;
        }

        $shipping_zone = ShippingZone::get_zone_matching_package( $package );
        $is_available  = ( $shipping_zone instanceof \WC_Shipping_Zone ) && $shipping_zone->get_id();

        if ( ! $is_available ) {
            $shipping_methods = ShippingZone::get_shipping_methods( $shipping_zone->get_id(), $seller_id );

            if ( ! empty( $shipping_methods ) ) {
                $is_available = true;
            }
        }

        return apply_filters( $this->id . '_is_available', $is_available, $package, $this );
    }

    /**
     * Split state code from country:state string
     *
     * @param string $value [like: BD:DHA]
     *
     * @return string [like: DHA ]
     */
    public function split_state_code( $value ) {
        $state_code = explode( ':', $value );
        return $state_code[1];
    }

    /**
     * Alter the default rate if one is chosen in settings.
     *
     * @access public
     *
     *  @param mixed $package
     *
     * @return bool
     */
    public function select_default_rate( $chosen_method, $_available_methods ) {
        //Select the 'Default' method from WooCommerce settings
        if ( array_key_exists( $this->default, $_available_methods ) ) {
            return $this->default;
        }

        return $chosen_method;
    }


    /**
     * Hide shipping rates when free shipping is available.
     * Updated to support WooCommerce 2.6 Shipping Zones.
     *
     * @access public
     *
     * @param array $rates Array of rates found for the package.
     *
     * @return array
     */
    public function hide_shipping_when_free_is_available( $rates ) {
        if ( $this->hide_method !== 'yes' ) {
            return $rates;
        }

        // determine if free shipping is available
        $free_shipping = false;
        foreach ( $rates as $rate_id => $rate ) {
            if ( 'free_shipping' === $rate->method_id ) {
                $free_shipping = true;
                break;
            }
        }
        // if available, remove all options from this method
        if ( $free_shipping ) {
            foreach ( $rates as $rate_id => $rate ) {
                if ( $this->id === $rate->method_id && strpos( $rate_id, $this->id . ':' . $this->instance_id . '-' ) !== false ) {
                    unset( $rates[ $rate_id ] );
                }
            }
        }

        return $rates;
    }


    /**
     * Hide shipping rates when one has option enabled.
     *
     * @access public
     *
     * @param array $rates Array of rates found for the package.
     *
     * @return array
     */
    public function hide_other_options( $rates ) {
        $hide_key = false;

        // return if no rates have been added
        if ( ! isset( $rates ) || empty( $rates ) ) {
            return $rates;
        }

        // cycle through available rates
        foreach ( $rates as $key => $rate ) {
            if ( $rate['hide_ops'] === 'on' ) {
                $hide_key = $key;
            }
        }

        if ( $hide_key ) {
            return array( $hide_key => $rates[ $hide_key ] );
        }

        return $rates;
    }

    /**
     * Get shpping method id
     *
     * @since 2.8.0
     *
     * @return void
     */
    public function get_method_rate_id( $method ) {
        return apply_filters( 'dokan_get_vendor_shipping_method_id', $method['id'] . ':' . $method['instance_id'] );
    }

    /**
     * Get Coupon discount amount for a shipping package
     *
     * @since 3.6.0
     *
     * @param array $package Current shipping package
     *
     * @return float
     */
    private function get_coupon_discount_amount( $package ) {
        $coupon_discount = 0.0;
        $coupons         = [];

        foreach ( $package['applied_coupons'] as $coupon_code ) {
            $coupon    = new \WC_Coupon( $coupon_code );
            $coupons[] = $coupon;
        }

        if ( 'yes' === get_option( 'woocommerce_calc_discounts_sequentially', 'no' ) ) {
            $coupons = $this->get_coupons_from_cart( $coupons );

            $counted_fixed_cart_coupons = [];

            foreach ( $package['contents'] as $product_line ) {
                $sequential_subtotal = $product_line['line_subtotal'];

                foreach ( $coupons as $coupon ) {
                    if ( ! $coupon->is_valid_for_product( wc_get_product( $product_line['product_id'] ) ) ) { // all other checks(like max spend, validity, usage limit etc) not performed here because they are done when we press 'Apply coupon' in Cart page
                        continue;
                    }

                    switch ( $coupon->get_discount_type() ) {
                        case 'percent':
                            $discount = wc_round_discount( $sequential_subtotal * $coupon->get_amount() / 100, wc_get_price_decimals() );
                            $coupon_discount += $discount;
                            break;

                        case 'fixed_product':
                            $discount = wc_round_discount( $coupon->get_amount() * $product_line['quantity'], wc_get_price_decimals() );
                            $coupon_discount += $discount;
                            break;

                        case 'fixed_cart':
                            if ( ! in_array( $coupon->get_code(), $counted_fixed_cart_coupons, true ) ) {
                                $discount = wc_round_discount( $coupon->get_amount(), wc_get_price_decimals() );
                                $coupon_discount += $discount;
                                $counted_fixed_cart_coupons[] = $coupon->get_code();
                            }
                            break;

                        default:
                            $discount = 0;
                    }

                    $sequential_subtotal -= $discount;
                }
            }

            return $coupon_discount;
        }

        foreach ( $coupons as $coupon ) {
            foreach ( $package['contents'] as $product_line ) {
                if ( ! $coupon->is_valid_for_product( wc_get_product( $product_line['product_id'] ) ) ) { // all other checks(like max spend, validity, usage limit etc) not performed here because they are done when we press 'Apply coupon' in Cart page
                    continue;
                }

                $product_line_total = $product_line['line_subtotal'];

                if ( 'percent' === $coupon->get_discount_type() ) {
                    $coupon_discount += wc_round_discount( $product_line_total * $coupon->get_amount() / 100, wc_get_price_decimals() );
                } elseif ( 'fixed_product' === $coupon->get_discount_type() ) {
                    $coupon_discount += wc_round_discount( $coupon->get_amount() * $product_line['quantity'], wc_get_price_decimals() );
                }
            }

            if ( 'fixed_cart' === $coupon->get_discount_type() ) {
                $coupon_discount += wc_round_discount( $coupon->get_amount(), wc_get_price_decimals() );
            }
        }

        return $coupon_discount;
    }

    /**
     * Get ordered coupons for sequential application. This function follows the method `get_coupons_from_cart` from class-wc-cart-totals.php
     *
     * @since 3.6.0
     *
     * @param array $coupons
     *
     * @return array
     */
    private function get_coupons_from_cart( $coupons ) {
        foreach ( $coupons as $coupon ) {
            switch ( $coupon->get_discount_type() ) {
                case 'fixed_product':
                    $coupon->sort = 1;
                    break;
                case 'percent':
                    $coupon->sort = 2;
                    break;
                case 'fixed_cart':
                    $coupon->sort = 3;
                    break;
                default:
                    $coupon->sort = 0;
                    break;
            }

            // Allow plugins to override the default order.
            $coupon->sort = apply_filters( 'woocommerce_coupon_sort', $coupon->sort, $coupon );
        }

        uasort( $coupons, array( $this, 'sort_coupons_callback' ) );

        return $coupons;
    }

    /**
     * Sort coupons so discounts apply consistently across installs. . This function follows the method `sort_coupons_callback` from class-wc-cart-totals.php
     *
     * @since 3.6.0
     *
     * In order of priority;
     *  - sort param
     *  - usage restriction
     *  - coupon value
     *  - ID
     *
     * @param \WC_Coupon $a Coupon object.
     * @param \WC_Coupon $b Coupon object.
     * @return int
     */
    protected function sort_coupons_callback( $a, $b ) {
        if ( $a->sort !== $b->sort ) {
            return ( $a->sort < $b->sort ) ? -1 : 1;
        }

        if ( $a->get_limit_usage_to_x_items() !== $b->get_limit_usage_to_x_items() ) {
            return ( $a->get_limit_usage_to_x_items() < $b->get_limit_usage_to_x_items() ) ? -1 : 1;
        }

        if ( $a->get_amount() !== $b->get_amount() ) {
            return ( $a->get_amount() < $b->get_amount() ) ? -1 : 1;
        }

        return $b->get_id() - $a->get_id();
    }

    /**
     * Delete Vendor shipping methods if Admin delete 'Vendor Shipping' in WC > Settings > Shipping > Zone
     *
     * @since 3.7.0
     *
     * @param int $instance_id
     * @param string $method_id
     * @param int $zone_id
     */
    public function delete_vendor_shipping_methods( $instance_id, $method_id, $zone_id ) {
        global $wpdb;

        if ( 'dokan_vendor_shipping' !== $method_id ) {
            return;
        }

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}dokan_shipping_zone_methods WHERE zone_id = %d AND method_id IN ( 'flat_rate', 'free_shipping', 'local_pickup' )",
                $zone_id
            )
        );
    }
}
