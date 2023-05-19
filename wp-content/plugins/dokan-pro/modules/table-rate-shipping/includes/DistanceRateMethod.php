<?php

namespace WeDevs\DokanPro\Modules\TableRate;

/**
 * Table Rate Shipping Method Extender Class
 *
 * @since 3.4.2
 */

use WC_Eval_Math;
use WC_Shipping_Method;
use WC_Tax;
use WeDevs\DokanPro\Shipping\ShippingZone;

class DistanceRateMethod extends WC_Shipping_Method {

    /**
     * Google Distance Matric API object.
     *
     * @var Object
     */
    protected $api;

    /**
     * Table Rates from Database
     *
     * @since 3.4.2
     */
    protected $options_save_name;

    /**
     * Table Rates from Database
     *
     * @since 3.4.2
     */
    public $default_option;

    /**
     * Cloning is forbidden. Will deactivate prior 'instances' users are running
     *
     * @since 3.4.2
     */
    public function __clone() {
        _doing_it_wrong( __FUNCTION__, __( 'Cloning this class could cause catastrophic disasters!', 'dokan' ), '4.0' );
    }

    /**
     * Unserializing instances of this class is forbidden.
     *
     * @since 3.4.2
     */
    public function __wakeup() {
        _doing_it_wrong( __FUNCTION__, __( 'Unserializing is forbidden!', 'dokan' ), '4.0' );
    }

    /**
     * __construct function.
     *
     * @since  3.4.2
     *
     * @access public
     * @return void
     */
    public function __construct( $instance_id = 0 ) {
        global $wpdb;

        $this->id                 = 'dokan_distance_rate_shipping';
        $this->instance_id        = absint( $instance_id );
        $this->method_title       = __( 'Vendor Distance Rate Shipping', 'dokan' );
        $this->method_description = __( 'Charge varying rates based on user defined conditions', 'dokan' );
        $this->has_settings       = false;
        $this->supports           = [ 'zones', 'shipping-zones', 'instance-settings', 'instance-settings-modal' ];
        $this->default            = '';
        $this->title              = $this->get_option( 'title', __( 'Distance Rate', 'dokan' ) );
        $this->rates_table        = $wpdb->prefix . 'dokan_distance_rate_shipping';

        // Initialize settings
        $this->init();

        // additional hooks for post-calculations settings
        add_filter( 'woocommerce_shipping_chosen_method', [ $this, 'select_default_rate' ], 10, 2 );
        add_action( 'woocommerce_update_options_shipping_' . $this->id, [ $this, 'process_admin_options' ] );
        add_action( 'woocommerce_shipping_zone_method_deleted', [ $this, 'delete_distance_rate_shipping_methods' ], 10, 3 );
    }

    /**
     * Init function.
     * initialize variables to be used
     *
     * @since  3.4.2
     *
     * @access public
     *
     * @return void
     */
    public function init() {
        $this->instance_form_fields = [
            'title' => [
                'title'       => __( 'Method title', 'dokan' ),
                'type'        => 'text',
                'description' => __( 'This controls the title which the user sees during checkout.', 'dokan' ),
                'default'     => __( 'Vendor Distance Rate', 'dokan' ),
                'desc_tip'    => true,
            ],
        ];

        $this->title = $this->get_option( 'title' );
    }

    /**
     * Calculate_shipping function.
     *
     * @since  3.4.2
     *
     * @access public
     *
     * @param array $package (default: array())
     *
     * @return void
     */
    public function calculate_shipping( $package = [] ) {
        $rates     = [];
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

            if (
                'yes' !== $method['enabled'] ||
                'dokan_distance_rate_shipping' !== $method['id']
            ) {
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
     * Query rules function.
     *
     * @since 3.4.2
     *
     * @param int $instance_id
     *
     * @return array
     */
    public function query_rules( $instance_id ) {
        global $wpdb;

        $rates = $wpdb->get_results( $wpdb->prepare( "SELECT * from {$wpdb->prefix}dokan_distance_rate_shipping WHERE instance_id = %d ORDER BY rate_order ASC", $instance_id ), ARRAY_A );

        return apply_filters( 'dokan_distance_rate_query_rules', $rates );
    }

    /**
     * Get method settings option.
     *
     * @since 3.4.2
     *
     * @param array  $method
     * @param string $key
     * @param bool   $is_int
     *
     * @return mix
     */
    public function get_method_option( $method, $key, $is_int = false ) {
        if ( isset( $method['settings'][ $key ] ) ) {
            return $is_int ? intval( $method['settings'][ $key ] ) : $method['settings'][ $key ];
        }

        return $is_int ? 0 : '';
    }

    /**
     * Return the API object.
     *
     * @since 3.4.2
     *
     * @return object WC_Google_Distance_Matrix_API
     */
    public function get_api() {
        $dokan_appearance = get_option( 'dokan_appearance', [] );
        $gmap_api_key     = $dokan_appearance['gmap_api_key'];

        $this->api = new DokanGoogleDistanceMatrixAPI( $gmap_api_key, false );

        return $this->api;
    }

    /**
     * Get rates function.
     *
     * @since 3.4.2
     *
     * @param obj   $package
     * @param array $method
     *
     * @return bool|array
     */
    public function get_rates( $package, $method ) {
        global $wpdb;

        if ( empty( $method ) ) {
            return false;
        }

        $cost                        = $this->get_method_option( $method, 'cost', true );
        $tax_status                  = $this->get_method_option( $method, 'tax_status' );
        $distance_rate_mode          = $this->get_method_option( $method, 'distance_rate_mode' );
        $distance_rate_avoid         = $this->get_method_option( $method, 'distance_rate_avoid' );
        $distance_rate_unit          = $this->get_method_option( $method, 'distance_rate_unit' );
        $distance_rate_show_distance = $this->get_method_option( $method, 'distance_rate_show_distance' );
        $distance_rate_show_duration = $this->get_method_option( $method, 'distance_rate_show_duration' );
        $instance_id                 = intval( $method['instance_id'] );
        $label_suffix                = [];

        $rates = [];

        // If they update information on the checkout and calls ajax,
        if (
            isset( $_GET['wc-ajax'] ) &&
            'update_order_review' === $_GET['wc-ajax'] &&
            empty( $package['destination']['address'] )
        ) {
            return false;
        }

        /*
         * Make sure the customer address is not only the country code.
         * as this means the customer has not yet entered an address.
         */
        if ( 2 === strlen( $this->get_customer_address_string( $package, false ) ) ) {
            return false;
        }

        // Get region based on customer address
        $region = empty( $package['destination']['country'] ) ? '' : strtolower( $package['destination']['country'] );

        if ( 'gb' === $region ) {
            $region = 'uk';
        }

        // Get distance distance matrix api
        $distance = $this->get_api()->get_distance(
            $this->get_shipping_address_string( $method['settings'], $instance_id ),
            $this->get_customer_address_string( $package ),
            false,
            $distance_rate_mode,
            $distance_rate_avoid,
            $distance_rate_unit,
            $region
        );

        // Check if a valid response was received.
        if ( ! isset( $distance->rows[0] ) || 'OK' !== $distance->rows[0]->elements[0]->status ) {
            return false;
        }

        // Maybe display distance next to the cost.
        if ( 'yes' === $distance_rate_show_distance && is_object( $distance ) ) {
            $label_suffix[] = $distance->rows[0]->elements[0]->distance->text;
        }

        // Maybe display duration next to the cost.
        if ( 'yes' === $distance_rate_show_duration && is_object( $distance ) ) {
            $label_suffix[] = $distance->rows[0]->elements[0]->duration->text;
        }

        $label_suffix        = ! empty( $label_suffix ) ? sprintf( ' (%s)', implode( '; ', $label_suffix ) ) : '';
        $label_suffix        = apply_filters( 'dokan_distance_rate_shipping_label_suffix', $label_suffix, $distance, $package );
        $travel_time_minutes = round( $distance->rows[0]->elements[0]->duration->value / 60 );
        $rounding_precision  = apply_filters( 'dokan_distance_rate_shipping_distance_rounding_precision', 1 );
        $distance_value      = $distance->rows[0]->elements[0]->distance->value;

        if ( 'imperial' === $distance_rate_unit ) {
            $distance = round( $distance_value * 0.000621371192, $rounding_precision );
        } else {
            $distance = round( $distance_value / 1000, $rounding_precision );
        }

        /**
         * Filter the distance received by the api before the shipping rules are checked
         *
         * @param stdClass $distance
         * @param integer  $distance_value
         * @param string   $unit
         */
        $distance = apply_filters( 'dokan_distance_rate_shipping_calculated_distance', $distance, $distance_value, $distance_rate_unit );

        $shipping_total          = 0;
        $at_least_one_rule_found = false;
        $matching_rules          = $this->query_rules( $instance_id );

        foreach ( $matching_rules as $rule ) {
            $rule_found = false;

            switch ( $rule['rate_condition'] ) {
                case 'distance':
                    $rule_cost = $this->distance_shipping( $rule, $distance, $package );

                    if ( ! is_null( $rule_cost ) ) {
                        $rule_found     = true;
                        $shipping_total += $rule_cost;
                    } else {
                        $this->show_notice( __( 'Sorry, that shipping location is beyond our shipping radius.', 'dokan' ) );
                    }
                    break;

                case 'time':
                    $rule_cost = $this->time_shipping( $rule, $travel_time_minutes, $package );

                    if ( ! is_null( $rule_cost ) ) {
                        $rule_found     = true;
                        $shipping_total += $rule_cost;
                    } else {
                        $this->show_notice( __( 'Sorry, that shipping location is beyond our shipping travel time.', 'dokan' ) );
                    }
                    break;

                case 'weight':
                    $rule_cost = $this->weight_shipping( $rule, $distance, $package );

                    if ( ! is_null( $rule_cost ) ) {
                        $rule_found     = true;
                        $shipping_total += $rule_cost;
                    } else {
                        $this->show_notice( __( 'Sorry, the total weight of your chosen items is beyond what we can ship.', 'dokan' ) );
                    }
                    break;

                case 'total':
                    $rule_cost = $this->order_total_shipping( $rule, $distance, $package );

                    if ( ! is_null( $rule_cost ) ) {
                        $rule_found     = true;
                        $shipping_total += $rule_cost;
                    } else {
                        $this->show_notice( __( 'Sorry, the total order cost is beyond what we can ship.', 'dokan' ) );
                    }
                    break;

                case 'quantity':
                    $rule_cost = $this->quantity_shipping( $rule, $distance, $package );

                    if ( ! is_null( $rule_cost ) ) {
                        $rule_found     = true;
                        $shipping_total += $rule_cost;
                    } else {
                        $this->show_notice( __( 'Sorry, the total quantity of items is beyond what we can ship.', 'dokan' ) );
                    }
                    break;
            }

            $at_least_one_rule_found = $at_least_one_rule_found || $rule_found;

            // Skip all rules if abort.
            if ( isset( $rule['rate_abort'] ) && 1 === absint( $rule['rate_abort'] ) && $rule_found ) {
                return false;
            }

            // Skip other rules if break.
            if ( isset( $rule['rate_break'] ) && 1 === absint( $rule['rate_break'] ) && $rule_found ) {
                break;
            }
        }

        if ( ! $at_least_one_rule_found ) {
            return false;
        }

        // Set available
        $this->available_rates = [
            [
                'id'    => $this->get_rate_id(),
                'label' => $method['title'] . $label_suffix,
                'cost'  => $shipping_total,
            ],
        ];

        return $this->available_rates;
    }

    /**
     * Unique function for overriding the prices including tax setting.
     *
     * @since 3.4.2
     *
     * @return bool
     */
    public function override_prices_include_tax_setting() {
        return true;
    }

    /**
     * Is available in specific zone locations
     *
     * @since 3.4.2
     *
     * @param array $package
     *
     * @return bool
     */
    public function is_available( $package ) {
        if ( empty( $package['seller_id'] ) ) {
            return false;
        }

        $shipping_zone = ShippingZone::get_zone_matching_package( $package );
        $is_available  = ( $shipping_zone instanceof \WC_Shipping_Zone ) && $shipping_zone->get_id();

        if (
            ! $is_available &&
            ! empty( ShippingZone::get_shipping_methods( $shipping_zone->get_id(), $package['seller_id'] ) )
        ) {
            $is_available = true;
        }

        return apply_filters( $this->id . '_is_available', $is_available, $package, $this );
    }

    /**
     * Split state code from country:state string
     *
     * @since 3.4.2
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
     * @since  3.4.2
     *
     * @access public
     *
     * @param mixed $chosen_method
     * @param array $_available_methods
     *
     * @return mixed
     */
    public function select_default_rate( $chosen_method, $available_methods ) {
        //Select the 'Default' method from WooCommerce settings
        if ( array_key_exists( $this->default, $available_methods ) ) {
            return $this->default;
        }

        return $chosen_method;
    }

    /**
     * Shows notices when shipping is not available.
     *
     * @since 3.4.2
     *
     * @param string $notice        Notice message.
     * @param bool   $cart_checkout Determine if we need to show in both cart and checkout pages.
     */
    public function show_notice( $notice = '', $cart_checkout = true ) {
        $this->notice = $notice;

        add_filter( 'dokan_no_shipping_available_html', [ $this, 'get_notice' ] );

        if ( $cart_checkout ) {
            add_filter( 'dokan_cart_no_shipping_available_html', [ $this, 'get_notice' ] );
        }
    }

    /**
     * Gets the currently set notice.
     *
     * @since 3.4.2
     *
     * @return string Notice.
     */
    public function get_notice() {
        return $this->notice;
    }


    /**
     * Calculate shipping based on distance.
     *
     * @since 3.4.2
     *
     * @param array  $rule     Rule.
     * @param int    $distance Distance.
     * @param object $package  Package to ship.
     *
     * @return int
     */
    public function distance_shipping( $rule, $distance, $package ) {
        $min_match = empty( $rule['rate_min'] ) || $distance >= $rule['rate_min'];
        $max_match = empty( $rule['rate_max'] ) || $distance <= $rule['rate_max'];
        $rule_cost = null;

        if ( $min_match && $max_match ) {
            $rule_cost = 0;

            if ( ! empty( $rule['rate_cost_unit'] ) ) {
                $rule_cost = $rule['rate_cost_unit'] * $distance;
            }

            if ( ! empty( $rule['rate_cost'] ) ) {
                $rule_cost += $rule['rate_cost'];
            }

            if ( ! empty( $rule['rate_fee'] ) ) {
                $rule_cost += $this->get_fee( $rule['rate_fee'], $package['contents_cost'] );
            }
        }

        /**
         * Filter the rule cost for distance shipping.
         *
         * @since 3.4.2
         *
         * @param float $rule_cost Calculated cost.
         * @param array $rule      Rule in DRS' row.
         * @param float $distance  Distance.
         * @param array $package   Package to ship.
         */
        return apply_filters(
            'dokan_distance_rate_shipping_rule_cost_distance_shipping',
            $rule_cost,
            $rule,
            $distance,
            $package
        );
    }

    /**
     * Calculate shipping based on total travel time.
     *
     * @since 3.4.2
     *
     * @param array  $rule                Rule.
     * @param int    $travel_time_minutes Travel time in minutes.
     * @param object $package             Package to ship.
     *
     * @return int
     */
    public function time_shipping( $rule, $travel_time_minutes, $package ) {
        $min_match = empty( $rule['rate_min'] ) || $travel_time_minutes >= $rule['rate_min'];
        $max_match = empty( $rule['rate_max'] ) || $travel_time_minutes <= $rule['rate_max'];
        $rule_cost = null;

        if ( $min_match && $max_match ) {
            $rule_cost = 0;

            if ( ! empty( $rule['rate_cost_unit'] ) ) {
                $rule_cost = $rule['rate_cost_unit'] * $travel_time_minutes;
            }

            if ( ! empty( $rule['rate_cost'] ) ) {
                $rule_cost += $rule['rate_cost'];
            }

            if ( ! empty( $rule['rate_fee'] ) ) {
                $rule_cost += $this->get_fee( $rule['rate_fee'], $package['contents_cost'] );
            }
        }

        /**
         * Filter the rule cost for time shipping.
         *
         * @since 3.4.2
         *
         * @param float $rule_cost           Calculated cost.
         * @param array $rule                Rule in DRS' row.
         * @param int   $travel_time_minutes Travel time in minutes.
         * @param array $package             Package to ship.
         */
        return apply_filters(
            'dokan_distance_rate_shipping_rule_cost_time_shipping',
            $rule_cost,
            $rule,
            $travel_time_minutes,
            $package
        );
    }

    /**
     * Calculate shipping based on weight.
     *
     * @since 3.4.2
     *
     * @param array  $rule     Rule.
     * @param int    $distance Distance.
     * @param object $package  Package to ship.
     *
     * @return int
     */
    public function weight_shipping( $rule, $distance, $package ) {
        $rule_cost    = null;
        $total_weight = WC()->cart->cart_contents_weight;

        if ( isset( $total_weight ) && $total_weight > 0 ) {
            $min_match = empty( $rule['rate_min'] ) || $total_weight >= $rule['rate_min'];
            $max_match = empty( $rule['rate_max'] ) || $total_weight <= $rule['rate_max'];

            if ( $min_match && $max_match ) {
                $rule_cost = 0;

                if ( ! empty( $rule['rate_cost_unit'] ) ) {
                    $rule_cost = $rule['rate_cost_unit'] * $distance;
                }

                if ( ! empty( $rule['rate_cost'] ) ) {
                    $rule_cost += $rule['rate_cost'];
                }

                if ( ! empty( $rule['rate_fee'] ) ) {
                    $rule_cost += $this->get_fee( $rule['rate_fee'], $package['contents_cost'] );
                }
            }
        }

        /**
         * Filter the rule cost for distance shipping.
         *
         * @since 3.4.2
         *
         * @param float $rule_cost Calculated cost.
         * @param array $rule      Rule in DRS' row.
         * @param float $distance  Distance.
         * @param array $package   Package to ship.
         */
        return apply_filters(
            'dokan_distance_rate_shipping_rule_cost_weight_shipping',
            $rule_cost,
            $rule,
            $distance,
            $package
        );
    }

    /**
     * Calculate shipping based on order total.
     *
     * @since 3.4.2
     *
     * @param array  $rule     Rule.
     * @param int    $distance Distance.
     * @param object $package  Package to ship.
     *
     * @return int
     */
    public function order_total_shipping( $rule, $distance, $package ) {
        $order_total = $package['contents_cost'];
        $rule_cost   = null;

        if ( isset( $order_total ) && $order_total > 0 ) {
            $min_match = empty( $rule['rate_min'] ) || $order_total >= $rule['rate_min'];
            $max_match = empty( $rule['rate_max'] ) || $order_total <= $rule['rate_max'];

            if ( $min_match && $max_match ) {
                $rule_cost = 0;

                if ( ! empty( $rule['rate_cost_unit'] ) ) {
                    $rule_cost = $rule['rate_cost_unit'] * $distance;
                }

                if ( ! empty( $rule['rate_cost'] ) ) {
                    $rule_cost += $rule['rate_cost'];
                }

                if ( ! empty( $rule['rate_fee'] ) ) {
                    $rule_cost += $this->get_fee( $rule['rate_fee'], $package['contents_cost'] );
                }
            }
        }

        /**
         * Filter the rule cost for distance shipping.
         *
         * @since 3.4.2
         *
         * @param float $rule_cost Calculated cost.
         * @param array $rule      Rule in DRS' row.
         * @param float $distance  Distance.
         * @param array $package   Package to ship.
         */
        return apply_filters(
            'dokan_distance_rate_shipping_rule_cost_order_total_shipping',
            $rule_cost,
            $rule,
            $distance,
            $package
        );
    }

    /**
     * Calculate shipping based on quantity.
     *
     * @since 3.4.2
     *
     * @param array  $rule     Rule.
     * @param int    $distance Distance.
     * @param object $package  Package to ship.
     *
     * @return int
     */
    public function quantity_shipping( $rule, $distance, $package ) {
        $rule_cost     = null;
        $content_count = WC()->cart->get_cart_contents_count();

        if ( isset( $content_count ) && $content_count > 0 ) {
            $min_match = empty( $rule['rate_min'] ) || $content_count >= $rule['rate_min'];
            $max_match = empty( $rule['rate_max'] ) || $content_count <= $rule['rate_max'];

            if ( $min_match && $max_match ) {
                $rule_cost = 0;

                if ( ! empty( $rule['rate_cost_unit'] ) ) {
                    $rule_cost = $rule['rate_cost_unit'] * $distance;
                }

                if ( ! empty( $rule['rate_cost'] ) ) {
                    $rule_cost += $rule['rate_cost'];
                }

                if ( ! empty( $rule['rate_fee'] ) ) {
                    $rule_cost += $this->get_fee( $rule['rate_fee'], $package['contents_cost'] );
                }
            }
        }

        /**
         * Filter the rule cost for distance shipping.
         *
         * @since 3.4.2
         *
         * @param float $rule_cost Calculated cost.
         * @param array $rule      Rule in DRS' row.
         * @param float $distance  Distance.
         * @param array $package   Package to ship.
         */
        return apply_filters(
            'dokan_distance_rate_shipping_rule_cost_quantity_shipping',
            $rule_cost,
            $rule,
            $distance,
            $package
        );
    }

    /**
     * Build customer address string from package.
     *
     * @since 3.4.2
     *
     * @param array $package              Package to ship.
     * @param bool  $convert_country_code Use full country name or just the country code ( France vs. FR )
     *
     * @return string
     */
    public function get_customer_address_string( $package, $convert_country_code = true ) {
        $address = [];

        if ( ! empty( $package['destination']['address'] ) ) {
            $address['address_1'] = $package['destination']['address'];
        } elseif ( ! empty( WC()->customer ) && ! empty( WC()->customer->get_shipping_address() ) ) {
            $address['address_1'] = WC()->customer->get_shipping_address();
        }

        if ( ! empty( $package['destination']['address_2'] ) ) {
            $address['address_2'] = $package['destination']['address_2'];
        } elseif ( ! empty( WC()->customer ) && ! empty( WC()->customer->get_shipping_address_2() ) ) {
            $address['address_2'] = WC()->customer->get_shipping_address_2();
        }

        if ( ! empty( $package['destination']['city'] ) ) {
            $address['city'] = $package['destination']['city'];
        } elseif ( ! empty( WC()->customer ) && ! empty( WC()->customer->get_shipping_city() ) ) {
            $address['city'] = WC()->customer->get_shipping_city();
        }

        if ( ! empty( $package['destination']['state'] ) ) {
            $state   = $package['destination']['state'];
            $country = $package['destination']['country'];

            // Convert state code to full name if available
            if ( isset( WC()->countries->states[ $country ], WC()->countries->states[ $country ][ $state ] ) ) {
                $state   = WC()->countries->states[ $country ][ $state ];
                $country = WC()->countries->countries[ $country ];
            }
            $address['state'] = $state;
        }

        // Cart page only has country, state and zipcodes.
        if ( ! empty( $package['destination']['postcode'] ) ) {
            $address['postcode'] = $package['destination']['postcode'];
        }

        if ( ! empty( $package['destination']['country'] ) ) {
            $country = $package['destination']['country'];

            // Convert country code to full name if available
            if ( $convert_country_code && isset( WC()->countries->countries[ $country ] ) ) {
                $country = WC()->countries->countries[ $country ];
            }
            $address['country'] = $country;
        }

        return implode( ', ', apply_filters( 'dokan_shipping_' . $this->id . '_get_customer_address_string', $address ) );
    }

    /**
     * Get the shipping from address as string.
     *
     * @since 3.4.2
     *
     * @param array $settings
     * @param int   $instance_id
     *
     * @return string
     */
    public function get_shipping_address_string( $settings, $instance_id ) {
        $address = [];

        if ( ! empty( $settings['distance_rate_address_1'] ) ) {
            $address['address_1'] = $settings['distance_rate_address_1'];
        }

        if ( ! empty( $settings['distance_rate_address_2'] ) ) {
            $address['address_2'] = $settings['distance_rate_address_2'];
        }

        if ( ! empty( $settings['distance_rate_city'] ) ) {
            $address['city'] = $settings['distance_rate_city'];
        }

        if ( ! empty( $settings['distance_rate_postal_code'] ) ) {
            $address['postcode'] = $settings['distance_rate_postal_code'];
        }

        if ( ! empty( $settings['distance_rate_state_province'] ) ) {
            $address['state'] = $settings['distance_rate_state_province'];
        }

        if ( ! empty( $settings['distance_rate_country'] ) ) {
            $address['country'] = $settings['distance_rate_country'];
        }

        return implode( ', ', apply_filters( 'dokan_distance_rate_shipping_' . $instance_id . '_get_shipping_address_string', $address ) );
    }

    /**
     * Delete Distance Rate shipping methods, created by Vendor, if Admin delete 'Vendor Distance Rate' in WC > Settings > Shipping > Zone
     *
     * @since 3.7.0
     *
     * @param int    $instance_id
     * @param string $method_id
     * @param int    $zone_id
     */
    public function delete_distance_rate_shipping_methods( $instance_id, $method_id, $zone_id ) {
        global $wpdb;

        if ( 'dokan_distance_rate_shipping' !== $method_id ) {
            return;
        }

        $distance_rate_shipping_method_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT instance_id FROM {$wpdb->prefix}dokan_shipping_zone_methods WHERE zone_id = %d AND method_id = 'dokan_distance_rate_shipping'",
                $zone_id
            )
        );

        if ( empty( $distance_rate_shipping_method_ids ) ) {
            return;
        }

        $distance_rate_shipping_method_ids = array_map( 'absint', $distance_rate_shipping_method_ids );
        $distance_rate_shipping_method_ids = implode( ',', $distance_rate_shipping_method_ids );

        //delete the distance rate rules first
        $wpdb->query(
            $wpdb->prepare(
                "DELETE from {$wpdb->prefix}dokan_distance_rate_shipping WHERE instance_id IN ($distance_rate_shipping_method_ids)"
            )
        );

        $wpdb->query(
            $wpdb->prepare(
                "DELETE FROM {$wpdb->prefix}dokan_shipping_zone_methods WHERE zone_id = %d AND method_id = 'dokan_distance_rate_shipping'",
                $zone_id
            )
        );
    }
}
