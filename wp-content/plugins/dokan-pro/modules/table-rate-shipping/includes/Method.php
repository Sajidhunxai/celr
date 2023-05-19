<?php

namespace WeDevs\DokanPro\Modules\TableRate;

/**
 * Table Rate Shipping Method Extender Class
 *
 * @since 3.4.0
 */

use WC_Eval_Math;
use WC_Shipping_Method;
use WC_Tax;
use WeDevs\DokanPro\Shipping\ShippingZone;

class Method extends WC_Shipping_Method {

    /**
     * Table Rates from Database
     *
     * @since 3.4.0
     */
    protected $options_save_name;

    /**
     * Table Rates from Database
     *
     * @since 3.4.0
     */
    public $default_option;

    /**
     * Cloning is forbidden. Will deactivate prior 'instances' users are running
     *
     * @since 3.4.0
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'Cloning this class could cause catastrophic disasters!', 'dokan' ), '4.0' );
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 3.4.0
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Unserializing is forbidden!', 'dokan' ), '4.0' );
    }

    /**
     * __construct function.
     *
     * @since 3.4.0
     *
     * @access public
     * @return void
     */
    public function __construct( $instance_id = 0 ) {
        global $wpdb;

        $this->id                 = 'dokan_table_rate_shipping';
        $this->instance_id        = absint( $instance_id );
        $this->method_title       = __( 'Vendor Table Rate Shipping', 'dokan' );
        $this->method_description = __( 'Charge varying rates based on user defined conditions', 'dokan' );
        $this->has_settings       = false;
        $this->supports           = array( 'zones', 'shipping-zones', 'instance-settings', 'instance-settings-modal' );
        $this->default            = '';
        $this->title              = $this->get_option( 'title', __( 'Table Rate', 'dokan' ) );
        $this->rates_table        = $wpdb->prefix . 'dokan_table_rate_shipping';

        // Initialize settings
        $this->init();

        // additional hooks for post-calculations settings
        add_filter( 'woocommerce_shipping_chosen_method', [ $this, 'select_default_rate' ], 10, 2 );
        add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
        add_action( 'woocommerce_shipping_zone_method_deleted', [ $this, 'delete_table_rate_shipping_methods' ], 10, 3 );
    }

    /**
     * Init function.
     * initialize variables to be used
     *
     * @since 3.4.0
     *
     * @access public
     *
     * @return void
     */
    public function init() {
        $this->instance_form_fields = array(
            'title' => array(
                'title'       => __( 'Method title', 'dokan' ),
                'type'        => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'dokan' ),
                'default'     => __( 'Vendor Table Rate', 'dokan' ),
                'desc_tip'    => true,
            ),
        );

        $this->title = $this->get_option( 'title' );
    }

    /**
     * Calculate_shipping function.
     *
     * @since 3.4.0
     *
     * @access public
     *
     * @param array $package (default: array())
     *
     * @return void
     */
    public function calculate_shipping( $package = array() ) {
        $rates     = array();
        $zone      = ShippingZone::get_zone_matching_package( $package );
        $seller_id = $package['seller_id'];

        if ( empty( $seller_id ) ) {
            return;
        }

        $shipping_methods = ShippingZone::get_shipping_methods( $zone->get_id(), $seller_id );

        if ( empty( $shipping_methods ) ) {
            return;
        }

        foreach ( $shipping_methods as $key => $method ) {
            $tax_rate  = ( $method['settings']['tax_status'] === 'none' ) ? false : '';
            $has_costs = false;

            if ( 'yes' !== $method['enabled'] || 'dokan_table_rate_shipping' !== $method['id'] ) {
                continue;
            }

            $get_rates = $this->get_rates( $package, $method );

            if ( $get_rates ) {
                foreach ( $get_rates as $rate ) {
                    $this->add_rate( $rate );
                }
            }
        }
    }

    /**
     * Query rates function.
     *
     * @since 3.4.0
     *
     * @param array $args
     * @param int   $instance_id
     *
     * @return array
     */
    public function query_rates( $args, $instance_id ) {
        global $wpdb;

        $defaults = array(
            'price'             => '',
            'weight'            => '',
            'count'             => 1,
            'count_in_class'    => 1,
            'shipping_class_id' => '',
        );

        $args = apply_filters( 'dokan_table_rate_query_rates_args', wp_parse_args( $args, $defaults ) );

        extract( $args, EXTR_SKIP ); // phpcs:ignore

        if ( $shipping_class_id === '' ) {
            $shipping_class_id_in = " AND rate_class IN ( '', '0' )";
        } else {
            $shipping_class_id_in = " AND rate_class IN ( '', '" . absint( $shipping_class_id ) . "' )";
        }

        // @codingStandardsIgnoreStart
        $rates = $wpdb->get_results(
            $wpdb->prepare(
                "
                SELECT rate_id, rate_cost, rate_cost_per_item, rate_cost_per_weight_unit, rate_cost_percent, rate_label, rate_priority, rate_abort, rate_abort_reason
                FROM {$this->rates_table}
                WHERE instance_id IN ( %s )
                {$shipping_class_id_in}
                AND
                (
                    rate_condition = ''
                    OR
                    (
                        rate_condition = 'price'
                        AND
                        (
                            ( ( rate_min + 0 ) = '' AND ( rate_max + 0 ) = '' )
                            OR
                            ( ( rate_min + 0 ) >= 0 AND ( rate_max + 0 ) >=0 AND '{$price}' >= ( rate_min + 0 ) AND '{$price}' <= ( rate_max + 0 ) )
                            OR
                            ( ( rate_min + 0 ) >= 0 AND ( rate_max + 0 ) = '' AND '{$price}' >= ( rate_min + 0 ) )
                            OR
                            ( ( rate_min + 0 ) = '' AND ( rate_max + 0 ) >= 0 AND '{$price}' <= ( rate_max + 0 ) )
                        )
                    )
                    OR
                    (
                        rate_condition = 'weight'
                        AND
                        (
                            ( ( rate_min + 0 ) = '' AND ( rate_max + 0 ) = '' )
                            OR
                            ( ( rate_min + 0 ) >= 0 AND ( rate_max + 0 ) >=0 AND '{$weight}' >= ( rate_min + 0 ) AND '{$weight}' <= ( rate_max + 0 ) )
                            OR
                            ( ( rate_min + 0 ) >= 0 AND ( rate_max + 0 ) = '' AND '{$weight}' >= ( rate_min + 0 ) )
                            OR
                            ( ( rate_min + 0 ) = '' AND ( rate_max + 0 ) >= 0 AND '{$weight}' <= ( rate_max + 0 ) )
                        )
                    )
                    OR
                    (
                        rate_condition = 'items'
                        AND
                        (
                            ( ( rate_min + 0 ) = '' AND ( rate_max + 0 ) = '' )
                            OR
                            ( ( rate_min + 0 ) >= 0 AND ( rate_max + 0 ) >=0 AND '{$count}' >= ( rate_min + 0 ) AND '{$count}' <= ( rate_max + 0 ) )
                            OR
                            ( ( rate_min + 0 ) >= 0 AND ( rate_max + 0 ) = '' AND '{$count}' >= ( rate_min + 0 ) )
                            OR
                            ( ( rate_min + 0 ) = '' AND ( rate_max + 0 ) >= 0 AND '{$count}' <= ( rate_max + 0 ) )
                        )
                    )
                    OR
                    (
                        rate_condition = 'items_in_class'
                        AND
                        (
                            ( ( rate_min + 0 ) = '' AND ( rate_max + 0 ) = '' )
                            OR
                            ( ( rate_min + 0 ) >= 0 AND ( rate_max + 0 ) >= 0 AND '{$count_in_class}' >= ( rate_min + 0 ) AND '{$count_in_class}' <= ( rate_max + 0 ) )
                            OR
                            ( ( rate_min + 0 ) >= 0 AND ( rate_max + 0 ) = '' AND '{$count_in_class}' >= ( rate_min + 0 ) )
                            OR
                            ( ( rate_min + 0 ) = '' AND ( rate_max + 0 ) >= 0 AND '{$count_in_class}' <= ( rate_max + 0 ) )
                        )
                    )
                )
                ORDER BY rate_order ASC
            ", $instance_id
            )
        );
        // @codingStandardsIgnoreEnd

        return apply_filters( 'dokan_table_rate_query_rates', $rates );
    }

    /**
     * Get method settings option.
     *
     * @since 3.4.0
     *
     * @param array  $method
     * @param string $key
     * @param bool   $is_int
     *
     * @return mix
     */
    public function get_method_option( $method, $key, $is_int = false ) {
        if ( isset( $method['settings'][ $key ] ) && $is_int ) {
            return intval( $method['settings'][ $key ] );
        } elseif ( isset( $method['settings'][ $key ] ) && ! $is_int ) {
            return $method['settings'][ $key ];
        }

        return $is_int ? 0 : '';
    }

    /**
     * Get rates function.
     *
     * @since 3.4.0
     *
     * @param obj   $package
     * @param array $method
     *
     * @return bool
     */
    public function get_rates( $package, $method ) {
        global $wpdb;

        $rates = array();

        if ( empty( $method ) ) {
            return false;
        }

        $fee                = $this->get_method_option( $method, 'handling_fee' );
        $order_handling_fee = $this->get_method_option( $method, 'order_handling_fee', true );
        $tax_status         = $this->get_method_option( $method, 'tax_status' );
        $calculation_type   = $this->get_method_option( $method, 'calculation_type' );
        $min_cost           = $this->get_method_option( $method, 'min_cost', true );
        $max_cost           = $this->get_method_option( $method, 'max_cost', true );
        $max_shipping_cost  = $this->get_method_option( $method, 'max_shipping_cost', true );
        $instance_id        = intval( $method['instance_id'] );

        // Get rates, depending on type
        if ( $calculation_type === 'item' ) {

            // For each ITEM get matching rates
            $costs   = array();
            $matched = false;

            foreach ( $package['contents'] as $item_id => $values ) {
                $_product = $values['data'];

                if ( $values['quantity'] > 0 && $_product->needs_shipping() ) {
                    $product_price  = $this->get_product_price( $_product, 1, $values );
                    $matching_rates = $this->query_rates(
                        array(
                            'price'             => $product_price,
                            'weight'            => (float) $_product->get_weight(),
                            'count'             => 1,
                            'count_in_class'    => $this->count_items_in_class( $package, $_product->get_shipping_class_id() ),
                            'shipping_class_id' => $_product->get_shipping_class_id(),
                        ), $instance_id
                    );

                    $item_weight = round( (float) $_product->get_weight(), 2 );
                    $item_fee    = (float) $this->get_fee( $fee, $product_price );
                    $item_cost   = 0;

                    foreach ( $matching_rates as $rate ) {
                        $item_cost += (float) $rate->rate_cost;
                        $item_cost += (float) $rate->rate_cost_per_weight_unit * $item_weight;
                        $item_cost += ( (float) $rate->rate_cost_percent / 100 ) * $product_price;
                        $matched    = true;
                        if ( $rate->rate_abort ) {
                            if ( ! empty( $rate->rate_abort_reason ) && ! wc_has_notice( $rate->rate_abort_reason, 'notice' ) ) {
                                $this->add_notice( $rate->rate_abort_reason, 'notice', $instance_id );
                            }
                            return;
                        }
                        if ( $rate->rate_priority ) {
                            break;
                        }
                    }

                    $cost = ( $item_cost + $item_fee ) * $values['quantity'];

                    if ( $min_cost && $cost < $min_cost ) {
                        $cost = $min_cost;
                    }
                    if ( $max_cost && $cost > $max_cost ) {
                        $cost = $max_cost;
                    }

                    $costs[ $item_id ] = $cost;
                }
            }

            if ( $matched ) {
                if ( $order_handling_fee ) {
                    $costs['order'] = $order_handling_fee;
                } else {
                    $costs['order'] = 0;
                }

                if ( $max_shipping_cost && ( $costs['order'] + array_sum( $costs ) ) > $max_shipping_cost ) {
                    $rates[] = array(
                        'id'    => is_callable( array( $this, 'get_rate_id' ) ) ? $this->get_rate_id() : $instance_id,
                        'label' => $method['title'],
                        'cost'  => $max_shipping_cost,
                    );
                } else {
                    $rates[] = array(
                        'id'       => is_callable( array( $this, 'get_rate_id' ) ) ? $this->get_rate_id() : $this->instance_id,
                        'label'    => $method['title'],
                        'cost'     => $costs,
                        'calc_tax' => 'per_item',
                        'package'  => $package,
                    );
                }
            }
        } elseif ( $calculation_type === 'line' ) {

            // For each LINE get matching rates
            $costs   = array();
            $matched = false;

            foreach ( $package['contents'] as $item_id => $values ) {
                $_product = $values['data'];

                if ( $values['quantity'] > 0 && $_product->needs_shipping() ) {
                    $product_price = $this->get_product_price( $_product, $values['quantity'], $values );

                    $matching_rates = $this->query_rates(
                        array(
                            'price'             => $product_price,
                            'weight'            => (float) $_product->get_weight() * $values['quantity'],
                            'count'             => $values['quantity'],
                            'count_in_class'    => $this->count_items_in_class( $package, $_product->get_shipping_class_id() ),
                            'shipping_class_id' => $_product->get_shipping_class_id(),
                        ), $instance_id
                    );

                    $item_weight = round( (float) $_product->get_weight() * $values['quantity'], 2 );
                    $item_fee    = (float) $this->get_fee( $fee, $product_price );
                    $item_cost   = 0;

                    foreach ( $matching_rates as $rate ) {
                        $item_cost += (float) $rate->rate_cost;
                        $item_cost += (float) $rate->rate_cost_per_item * $values['quantity'];
                        $item_cost += (float) $rate->rate_cost_per_weight_unit * $item_weight;
                        $item_cost += ( (float) $rate->rate_cost_percent / 100 ) * $product_price;
                        $matched    = true;

                        if ( $rate->rate_abort ) {
                            if ( ! empty( $rate->rate_abort_reason ) ) {
                                $this->add_notice( $rate->rate_abort_reason, 'notice', $instance_id );
                            }
                            return;
                        }
                        if ( $rate->rate_priority ) {
                            break;
                        }
                    }

                    $item_cost = $item_cost + $item_fee;

                    if ( $min_cost && $item_cost < $min_cost ) {
                        $item_cost = $min_cost;
                    }
                    if ( $max_cost && $item_cost > $max_cost ) {
                        $item_cost = $max_cost;
                    }

                    $costs[ $item_id ] = $item_cost;
                }
            }

            if ( $matched ) {
                if ( $order_handling_fee ) {
                    $costs['order'] = $order_handling_fee;
                } else {
                    $costs['order'] = 0;
                }

                if ( $max_shipping_cost && ( $costs['order'] + array_sum( $costs ) ) > $max_shipping_cost ) {
                    $rates[] = array(
                        'id'      => is_callable( array( $this, 'get_rate_id' ) ) ? $this->get_rate_id() : $instance_id,
                        'label'   => $method['title'],
                        'cost'    => $max_shipping_cost,
                        'package' => $package,
                    );
                } else {
                    $rates[] = array(
                        'id'       => is_callable( array( $this, 'get_rate_id' ) ) ? $this->get_rate_id() : $this->instance_id,
                        'label'    => $method['title'],
                        'cost'     => $costs,
                        'calc_tax' => 'per_item',
                        'package'  => $package,
                    );
                }
            }
        } elseif ( $calculation_type === 'class' ) {

            // For each CLASS get matching rates
            $total_cost = 0;

            // First get all the rates in the table
            $all_rates = dokan_pro()->module->table_rate_shipping->get_shipping_rates( OBJECT, $instance_id );

            // Now go through cart items and group items by class
            $classes = array();

            foreach ( $package['contents'] as $item_id => $values ) {
                $_product = $values['data'];

                if ( $values['quantity'] > 0 && $_product->needs_shipping() ) {
                    $shipping_class = $_product->get_shipping_class_id();

                    if ( ! isset( $classes[ $shipping_class ] ) ) {
                        $classes[ $shipping_class ] = new \stdClass();
                        $classes[ $shipping_class ]->price = 0;
                        $classes[ $shipping_class ]->weight = 0;
                        $classes[ $shipping_class ]->items = 0;
                        $classes[ $shipping_class ]->items_in_class = 0;
                    }

                    $classes[ $shipping_class ]->price          += $this->get_product_price( $_product, $values['quantity'], $values );
                    $classes[ $shipping_class ]->weight         += (float) $_product->get_weight() * $values['quantity'];
                    $classes[ $shipping_class ]->items          += $values['quantity'];
                    $classes[ $shipping_class ]->items_in_class += $values['quantity'];
                }
            }

            $matched    = false;
            $total_cost = 0;
            $stop       = false;

            // Now we have groups, loop the rates and find matches in order
            foreach ( $all_rates as $rate ) {
                foreach ( $classes as $class_id => $class ) {
                    if ( $class_id === '' ) {
                        if ( intval( $rate->rate_class ) !== 0 && $rate->rate_class !== '' ) {
                            continue;
                        }
                    } else {
                        if ( intval( $rate->rate_class ) !== $class_id && $rate->rate_class !== '' ) {
                            continue;
                        }
                    }

                    $rate_match = false;

                    switch ( $rate->rate_condition ) {
                        case '':
                            $rate_match = true;
                            break;
                        case 'price':
                        case 'weight':
                        case 'items_in_class':
                        case 'items':
                            $condition = $rate->rate_condition;
                            $value     = $class->$condition;

                            if ( $rate->rate_min === '' && $rate->rate_max === '' ) {
                                $rate_match = true;
                            }

                            if ( $value >= $rate->rate_min && $value <= $rate->rate_max ) {
                                $rate_match = true;
                            }

                            if ( $value >= $rate->rate_min && ! $rate->rate_max ) {
                                $rate_match = true;
                            }

                            if ( $value <= $rate->rate_max && ! $rate->rate_min ) {
                                $rate_match = true;
                            }

                            break;
                    }

                    // Rate matched class
                    if ( $rate_match ) {
                        if ( ! empty( $rate->rate_label ) ) {
                            $rate_label = $rate->rate_label;
                        } elseif ( ! empty( $method['title'] ) ) {
                            $rate_label = $method['title'];
                        } else {
                            $rate_label = $this->title;
                        }

                        $class_cost = 0;
                        $class_cost += (float) $rate->rate_cost;
                        $class_cost += (float) $rate->rate_cost_per_item * $class->items_in_class;
                        $class_cost += (float) $rate->rate_cost_per_weight_unit * $class->weight;
                        $class_cost += ( (float) $rate->rate_cost_percent / 100 ) * $class->price;

                        if ( $rate->rate_abort ) {
                            if ( ! empty( $rate->rate_abort_reason ) ) {
                                $this->add_notice( $rate->rate_abort_reason, 'notice', $instance_id );
                            }
                            return;
                        }

                        if ( $rate->rate_priority ) {
                            $stop = true;
                        }

                        $matched = true;

                        $class_fee  = (float) $this->get_fee( $fee, $class->price );
                        $class_cost += $class_fee;

                        if ( $min_cost && $class_cost < $min_cost ) {
                            $class_cost = $min_cost;
                        }
                        if ( $max_cost && $class_cost > $max_cost ) {
                            $class_cost = $max_cost;
                        }

                        $total_cost += $class_cost;
                    }
                }

                // Breakpoint
                if ( $stop ) {
                    break;
                }
            }

            if ( $order_handling_fee ) {
                $total_cost += $this->get_fee( $order_handling_fee, $total_cost );
            }

            if ( $max_shipping_cost && $total_cost > $max_shipping_cost ) {
                $total_cost = $max_shipping_cost;
            }

            if ( $matched ) {
                $rates[] = array(
                    'id'      => is_callable( array( $this, 'get_rate_id' ) ) ? $this->get_rate_id() : $instance_id,
                    'label'   => $rate_label,
                    'cost'    => $total_cost,
                    'package' => $package,
                );
            }
        } else {

            // For the ORDER get matching rates
            $shipping_class = $this->get_cart_shipping_class_id( $package, $instance_id );
            $price          = 0;
            $weight         = 0;
            $count          = 0;
            $count_in_class = 0;

            foreach ( $package['contents'] as $item_id => $values ) {
                $_product = $values['data'];

                if ( $values['quantity'] > 0 && $_product->needs_shipping() ) {
                    $price  += $this->get_product_price( $_product, $values['quantity'], $values );
                    $weight += (float) $_product->get_weight() * (float) $values['quantity'];
                    $count  += $values['quantity'];

                    if ( $_product->get_shipping_class_id() == $shipping_class ) {
                        $count_in_class += $values['quantity'];
                    }
                }
            }

            $matching_rates = $this->query_rates(
                array(
                    'price'             => $price,
                    'weight'            => $weight,
                    'count'             => $count,
                    'count_in_class'    => $count_in_class,
                    'shipping_class_id' => $shipping_class,
                ), $instance_id
            );

            foreach ( $matching_rates as $rate ) {
                $label = $rate->rate_label;
                if ( ! $label ) {
                    $label = $method['title'];
                }

                if ( $rate->rate_abort ) {
                    if ( ! empty( $rate->rate_abort_reason ) ) {
                        $this->add_notice( $rate->rate_abort_reason, 'notice', $instance_id );
                    }
                    $rates = array(); // Clear rates
                    break;
                }

                if ( $rate->rate_priority ) {
                    $rates = array();
                }

                $cost  = (float) $rate->rate_cost;
                $cost += (float) $rate->rate_cost_per_item * $count;
                $cost += (float) $this->get_fee( $fee, $price );
                $cost += (float) $rate->rate_cost_per_weight_unit * $weight;
                $cost += ( (float) $rate->rate_cost_percent / 100 ) * $price;

                if ( $order_handling_fee ) {
                    $cost += $order_handling_fee;
                }

                if ( $min_cost && $cost < $min_cost ) {
                    $cost = $min_cost;
                }

                if ( $max_cost && $cost > $max_cost ) {
                    $cost = $max_cost;
                }

                if ( $max_shipping_cost && $cost > $max_shipping_cost ) {
                    $cost = $max_shipping_cost;
                }

                $rates[] = array(
                    'id'      => is_callable( array( $this, 'get_rate_id' ) ) ? $this->get_rate_id( $rate->rate_id ) : $instance_id . ' : ' . $rate->rate_id,
                    'label'   => $label,
                    'cost'    => $cost,
                    'package' => $package,
                );

                if ( $rate->rate_priority ) {
                    break;
                }
            }
        }

        $is_customer_vat_exempt = WC()->cart->get_customer()->get_is_vat_exempt();

        if ( wc_prices_include_tax() && ( $this->is_taxable() || $is_customer_vat_exempt ) ) {
            // We allow the table rate to be entered inclusive of taxes just like product prices.
            foreach ( $rates as $key => $rate ) {
                $tax_rates = WC_Tax::get_shipping_tax_rates();

                // Temporarily override setting since our shipping rate will always include taxes here.
                add_filter( 'woocommerce_prices_include_tax', array( $this, 'override_prices_include_tax_setting' ) );

                $base_tax_rates = WC_Tax::get_shipping_tax_rates( null, false );

                remove_filter( 'woocommerce_prices_include_tax', array( $this, 'override_prices_include_tax_setting' ) );

                $total_cost = is_array( $rate['cost'] ) ? array_sum( $rate['cost'] ) : $rate['cost'];

                if ( apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ) {
                    $taxes = WC_Tax::calc_tax( $total_cost, $base_tax_rates, true );
                } else {
                    $taxes = WC_Tax::calc_tax( $total_cost, $tax_rates, true );
                }

                $rates[ $key ]['cost'] = $total_cost - array_sum( $taxes );

                $rates[ $key ]['taxes'] = $is_customer_vat_exempt ? array() : WC_Tax::calc_shipping_tax( $rates[ $key ]['cost'], $tax_rates );

                $rates[ $key ]['price_decimals'] = '4'; // Prevent the cost from being rounded before the tax is added.
            }
        }

        // None found?
        if ( count( $rates ) === 0 ) {
            return false;
        }

        // Set available
        return $rates;
    }

    /**
     * Unique function for overriding the prices including tax setting.
     *
     * @since 3.4.0
     *
     * @return bool
     */
    public function override_prices_include_tax_setting() {
        return true;
    }

    /**
     * Get cart shipping class id by package and instance.
     *
     * @since 3.4.0
     *
     * @param array $package
     * @param ing   $instance_id
     *
     * @return int
     */
    public function get_cart_shipping_class_id( $package, $instance_id ) {
        // Find shipping class for cart
        $found_shipping_classes = array();
        $shipping_class_id      = 0;
        $shipping_class_slug    = '';

        // Find shipping classes for products in the package
        if ( count( $package['contents'] ) > 0 ) {
            foreach ( $package['contents'] as $item_id => $values ) {
                if ( $values['data']->needs_shipping() ) {
                    $found_shipping_classes[ $values['data']->get_shipping_class_id() ] = $values['data']->get_shipping_class();
                }
            }
        }

        $found_shipping_classes = array_unique( $found_shipping_classes );

        if ( count( $found_shipping_classes ) === 1 ) {
            $shipping_class_slug = current( $found_shipping_classes );
        } elseif ( $found_shipping_classes > 1 ) {

            $method_info = dokan_pro()->module->table_rate_shipping->get_shipping_method( $instance_id );
            $priorities  = isset( $method_info['settings']['classes_priorities'] ) ? $method_info['settings']['classes_priorities'] : array();
            $priority    = isset( $method_info['settings']['default_priority'] ) ? $method_info['settings']['default_priority'] : 10;

            foreach ( $found_shipping_classes as $class ) {
                if ( isset( $priorities[ $class ] ) && $priorities[ $class ] < $priority ) {
                    $priority = $priorities[ $class ];
                    $shipping_class_slug = $class;
                }
            }
        }

        $found_shipping_classes = array_flip( $found_shipping_classes );

        if ( isset( $found_shipping_classes[ $shipping_class_slug ] ) ) {
            $shipping_class_id = $found_shipping_classes[ $shipping_class_slug ];
        }

        return $shipping_class_id;
    }

    /**
     * Count items in class
     *
     * @since 3.4.0
     *
     * @param array $package
     * @param ing   $class_id
     *
     * @return int
     */
    public function count_items_in_class( $package, $class_id ) {
        $count = 0;

        // Find shipping classes for products in the package
        foreach ( $package['contents'] as $item_id => $values ) {
            if ( $values['data']->needs_shipping() && $values['data']->get_shipping_class_id() == $class_id ) {
                $count += $values['quantity'];
            }
        }

        return $count;
    }

    /**
     * Adds a notice to the cart/checkout header.
     *
     * @since 3.4.0
     *
     * @param string $message Message to show
     * @param int    $instance_id
     *
     * @return void
     */
    private function add_notice( $message, $instance_id ) {
        $this->save_abort_message( $message, $instance_id );

        // Only display shipping notices in cart/checkout.
        if ( ! is_cart() && ! is_checkout() ) {
            return;
        }

        if ( ! wc_has_notice( $message ) ) {
            wc_add_notice( $message );
        }
    }

    /**
     * Save the abort notice in the session (to display when shipping methods are loaded from cache).
     *
     * @since 3.4.0
     *
     * @param string $message Message to show
     * @param int    $instance_id
     *
     * @param string $message Abort message.
     */
    private function save_abort_message( $message, $instance_id ) {
        $abort = WC()->session->get( 'dokan_table_rate_abort' );

        if ( empty( $abort ) ) {
            $abort = array();
        }

        $abort[ $instance_id ] = $message;

        WC()->session->set( 'dokan_table_rate_abort', $abort );
    }

    /**
     * Unset the abort notice in the session.
     *
     * @since 3.4.0
     *
     * @param int $instance_id
     *
     * @return void
     */
    private function unset_abort_message( $instance_id ) {
        $abort = WC()->session->get( 'dokan_table_rate_abort' );

        unset( $abort[ $instance_id ] );

        WC()->session->set( 'dokan_table_rate_abort', $abort );
    }

    /**
     * Retrieve the product price from a line item.
     *
     * @since 3.4.0
     *
     * @param object $_product Product object.
     * @param int    $qty      Line item quantity.
     * @param array  $item     Array of line item data.
     *
     * @return float
     */
    public function get_product_price( $_product, $qty = 1, $item = array() ) {
        // Use the product price based on the line item totals (including coupons and discounts).
        // This is not enabled by default (since it can be interpreted differently).
        if ( apply_filters( 'dokan_table_rate_compare_price_limits_after_discounts', false, $item ) && isset( $item['line_total'] ) ) {
            return $item['line_total'] + ( ! empty( $item['line_tax'] ) ? $item['line_tax'] : 0 );
        }

        $row_base_price = $_product->get_price() * $qty;
        $row_base_price = apply_filters( 'dokan_table_rate_package_row_base_price', $row_base_price, $_product, $qty );

        if ( $_product->is_taxable() && wc_prices_include_tax() ) {
            $base_tax_rates = WC_Tax::get_base_tax_rates( $_product->get_tax_class() );

            $tax_rates = WC_Tax::get_rates( $_product->get_tax_class() );

            if ( $tax_rates !== $base_tax_rates && apply_filters( 'dokan_adjust_non_base_location_prices', true ) ) {
                $base_taxes     = WC_Tax::calc_tax( $row_base_price, $base_tax_rates, true, true );
                $modded_taxes   = WC_Tax::calc_tax( $row_base_price - array_sum( $base_taxes ), $tax_rates, false );
                $row_base_price = ( $row_base_price - array_sum( $base_taxes ) ) + array_sum( $modded_taxes );
            }
        }

        return $row_base_price;
    }

    /**
     * Is available in specific zone locations
     *
     * @since 3.4.0
     *
     * @param array $package
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
     * @since 3.4.0
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
     * @since 3.4.0
     *
     * @access public
     *
     * @param mixed $chosen_method
     * @param array $_available_methods
     *
     * @return bool
     */
    public function select_default_rate( $chosen_method, $available_methods ) {
        //Select the 'Default' method from WooCommerce settings
        if ( array_key_exists( $this->default, $available_methods ) ) {
            return $this->default;
        }

        return $chosen_method;
    }

    /**
     * Delete Table Rate shipping methods, created by Vendor, if Admin delete 'Vendor Table Rate' in WC > Settings > Shipping > Zone
     *
     * @since 3.7.0
     *
     * @param int $instance_id
     * @param string $method_id
     * @param int $zone_id
     */
    public function delete_table_rate_shipping_methods( $instance_id, $method_id, $zone_id ) {
        global $wpdb;

        if ( 'dokan_table_rate_shipping' !== $method_id ) {
            return;
        }

        $table_rate_shipping_method_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT instance_id FROM {$wpdb->prefix}dokan_shipping_zone_methods WHERE zone_id = %d AND method_id = 'dokan_table_rate_shipping'",
                $zone_id
            )
        );

        if ( empty( $table_rate_shipping_method_ids ) ) {
            return;
        }

        $table_rate_shipping_method_ids = array_map( 'absint', $table_rate_shipping_method_ids );
        $table_rate_shipping_method_ids = implode( ',', $table_rate_shipping_method_ids );

        //delete the table rate rules first
        $wpdb->query(
            $wpdb->prepare(
                "DELETE from {$wpdb->prefix}dokan_table_rate_shipping WHERE instance_id IN ($table_rate_shipping_method_ids)"
            )
        );

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}dokan_shipping_zone_methods WHERE zone_id = %d AND method_id = 'dokan_table_rate_shipping'",
                $zone_id
            )
        );
    }
}
