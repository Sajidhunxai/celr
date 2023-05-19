<?php

namespace WeDevs\DokanPro\Modules\TableRate;

/**
 * Table Rate Shipping Template Class
 */

class Hooks {

    /**
     * Constructor for the Table Rate
     * Shipping Template class
     *
     * @since 3.4.0
     */
    public function __construct() {
        add_action( 'template_redirect', [ $this, 'save_table_rate_settings' ], 12 );
        add_action( 'template_redirect', [ $this, 'save_distance_rate_settings' ], 12 );
        add_action( 'dokan_delete_shipping_zone_data', [ $this, 'delete_table_rates_by_zone' ] );
        add_action( 'dokan_delete_shipping_zone_methods', [ $this, 'delete_table_rates_by_method' ], 11, 2 );
        add_filter( 'dokan_available_shipping_methods', [ $this, 'add_table_rate_method' ], 11, 2 );
    }

    /**
     * Get all avilable shipping methods
     *
     * @param array  $available_methods
     * @param object $zone
     *
     * @return array
     */
    public function add_table_rate_method( $available_methods, $zone ) {
        $enabled_methods     = $zone->get_shipping_methods( true );
        $methods_ids         = wp_list_pluck( $enabled_methods, 'id' );
        $table_rate_label    = apply_filters( 'dokan_table_rate_shipping_label', __( 'Table Rate', 'dokan' ) );
        $distance_rate_label = apply_filters( 'dokan_distance_rate_shipping_label', __( 'Distance Rate', 'dokan' ) );

        $available_methods['dokan_table_rate_shipping']    = $table_rate_label;
        $available_methods['dokan_distance_rate_shipping'] = $distance_rate_label;

        if ( ! in_array( 'dokan_table_rate_shipping', $methods_ids, true ) ) {
            unset( $available_methods['dokan_table_rate_shipping'] );
        }

        if ( ! in_array( 'dokan_distance_rate_shipping', $methods_ids, true ) ) {
            unset( $available_methods['dokan_distance_rate_shipping'] );
        }

        if ( ! in_array( 'dokan_vendor_shipping', $methods_ids, true ) ) {
            unset( $available_methods['flat_rate'], $available_methods['local_pickup'], $available_methods['free_shipping'] );
        }

        return $available_methods;
    }

    /**
     * Load Settings Content
     *
     * @since 3.4.0
     *
     * @return void
     */
    public function save_table_rate_settings() {
        if ( ! isset( $_POST['dokan_table_rate_shipping_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['dokan_table_rate_shipping_settings_nonce'] ), 'dokan_table_rate_shipping_settings' )
        ) {
            return;
        }

        $zone_id     = dokan_pro()->module->table_rate_shipping->get_zone();
        $instance_id = dokan_pro()->module->table_rate_shipping->get_instance();

        if ( ! $instance_id ) {
            return;
        }

        global $wpdb;

        // Save general settings
        $general_data = $this->get_general_data();
        $table_name   = "{$wpdb->prefix}dokan_shipping_zone_methods";
        $updated      = $wpdb->update( $table_name, $general_data, array( 'instance_id' => $instance_id ), array( '%s', '%d', '%d', '%s' ) );

        // Save table rate rows
        $this->save_table_rate_rows( $zone_id, $instance_id );

        if ( ! defined( 'DOING_AJAX' ) ) {
            wp_safe_redirect( add_query_arg( array( 'message' => 'table_rate_saved' ) ) );
        }
    }

    /**
     * Load Settings Content
     *
     * @since 3.4.2
     *
     * @return void
     */
    public function save_distance_rate_settings() {
        if (
            ! isset( $_POST['dokan_distance_rate_shipping_settings_nonce'] ) ||
            ! wp_verify_nonce( sanitize_key( $_POST['dokan_distance_rate_shipping_settings_nonce'] ), 'dokan_distance_rate_shipping_settings' )
        ) {
            return;
        }

        $zone_id     = dokan_pro()->module->table_rate_shipping->get_zone();
        $instance_id = dokan_pro()->module->table_rate_shipping->get_instance();

        if ( ! $instance_id ) {
            return;
        }

        global $wpdb;

        // Save general settings
        $general_data = $this->get_distance_general_data();
        $table_name   = "{$wpdb->prefix}dokan_shipping_zone_methods";
        $updated      = $wpdb->update( $table_name, $general_data, array( 'instance_id' => $instance_id ), array( '%s', '%d', '%d', '%s' ) );

        // Save table rate rows
        $this->save_distance_rate_rows( $zone_id, $instance_id );

        if ( ! defined( 'DOING_AJAX' ) ) {
            wp_safe_redirect( add_query_arg( array( 'message' => 'distance_rate_saved' ) ) );
        }
    }

    /**
     * Get distance rate general data
     *
     * @since 3.4.2
     *
     * @return void
     */
    public function get_distance_general_data() {
        if (
            ! isset( $_POST['dokan_distance_rate_shipping_settings_nonce'] ) ||
            ! wp_verify_nonce( sanitize_key( $_POST['dokan_distance_rate_shipping_settings_nonce'] ), 'dokan_distance_rate_shipping_settings' )
        ) {
            return;
        }

        $zone_id        = isset( $_GET['zone_id'] ) ? absint( wp_unslash( $_GET['zone_id'] ) ) : 0;
        $title          = isset( $_POST['distance_rate_title'] ) ? sanitize_text_field( wp_unslash( $_POST['distance_rate_title'] ) ) : '';
        $tax_status     = isset( $_POST['distance_rate_tax_status'] ) ? sanitize_text_field( wp_unslash( $_POST['distance_rate_tax_status'] ) ) : '';
        $mode           = isset( $_POST['distance_rate_mode'] ) ? sanitize_text_field( wp_unslash( $_POST['distance_rate_mode'] ) ) : '';
        $avoid          = isset( $_POST['distance_rate_avoid'] ) ? sanitize_text_field( wp_unslash( $_POST['distance_rate_avoid'] ) ) : '';
        $unit           = isset( $_POST['distance_rate_unit'] ) ? sanitize_text_field( wp_unslash( $_POST['distance_rate_unit'] ) ) : '';
        $show_distance  = isset( $_POST['distance_rate_show_distance'] ) ? sanitize_text_field( wp_unslash( $_POST['distance_rate_show_distance'] ) ) : '';
        $show_duration  = isset( $_POST['distance_rate_show_duration'] ) ? sanitize_text_field( wp_unslash( $_POST['distance_rate_show_duration'] ) ) : '';
        $address_1      = isset( $_POST['distance_rate_address_1'] ) ? sanitize_text_field( wp_unslash( $_POST['distance_rate_address_1'] ) ) : '';
        $address_2      = isset( $_POST['distance_rate_address_2'] ) ? sanitize_text_field( wp_unslash( $_POST['distance_rate_address_2'] ) ) : '';
        $city           = isset( $_POST['distance_rate_city'] ) ? sanitize_text_field( wp_unslash( $_POST['distance_rate_city'] ) ) : '';
        $postal_code    = isset( $_POST['distance_rate_postal_code'] ) ? sanitize_text_field( wp_unslash( $_POST['distance_rate_postal_code'] ) ) : '';
        $state_province = isset( $_POST['distance_rate_state_province'] ) ? sanitize_text_field( wp_unslash( $_POST['distance_rate_state_province'] ) ) : '';
        $country        = isset( $_POST['distance_rate_country'] ) ? sanitize_text_field( wp_unslash( $_POST['distance_rate_country'] ) ) : '';

        $general_settings = array(
            'title'                        => $title,
            'tax_status'                   => $tax_status,
            'distance_rate_mode'           => $mode,
            'distance_rate_avoid'          => $avoid,
            'distance_rate_unit'           => $unit,
            'distance_rate_show_distance'  => $show_distance,
            'distance_rate_show_duration'  => $show_duration,
            'distance_rate_address_1'      => $address_1,
            'distance_rate_address_2'      => $address_2,
            'distance_rate_city'           => $city,
            'distance_rate_postal_code'    => $postal_code,
            'distance_rate_state_province' => $state_province,
            'distance_rate_country'        => $country,
        );

        return array(
            'method_id' => 'dokan_distance_rate_shipping',
            'zone_id'   => $zone_id,
            'seller_id' => dokan_get_current_user_id(),
            'settings'  => maybe_serialize( $general_settings ),
        );
    }

    /**
     * Get general data
     *
     * @since 3.4.0
     *
     * @return void
     */
    public function get_general_data() {
        if ( ! isset( $_POST['dokan_table_rate_shipping_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['dokan_table_rate_shipping_settings_nonce'] ), 'dokan_table_rate_shipping_settings' )
        ) {
            return;
        }

        $zone_id            = isset( $_GET['zone_id'] ) ? absint( wp_unslash( $_GET['zone_id'] ) ) : 0;
        $title              = isset( $_POST['table_rate_title'] ) ? sanitize_text_field( wp_unslash( $_POST['table_rate_title'] ) ) : '';
        $tax_status         = isset( $_POST['table_rate_tax_status'] ) ? sanitize_text_field( wp_unslash( $_POST['table_rate_tax_status'] ) ) : '';
        $prices_include_tax = isset( $_POST['table_rate_prices_include_tax'] ) ? sanitize_text_field( wp_unslash( $_POST['table_rate_prices_include_tax'] ) ) : '';
        $order_handling_fee = isset( $_POST['table_rate_order_handling_fee'] ) ? sanitize_text_field( wp_unslash( $_POST['table_rate_order_handling_fee'] ) ) : '';
        $max_shipping_cost  = isset( $_POST['table_rate_max_shipping_cost'] ) ? sanitize_text_field( wp_unslash( $_POST['table_rate_max_shipping_cost'] ) ) : '';
        $calculation_type   = isset( $_POST['table_rate_calculation_type'] ) ? sanitize_text_field( wp_unslash( $_POST['table_rate_calculation_type'] ) ) : '';
        $handling_fee       = isset( $_POST['table_rate_handling_fee'] ) ? sanitize_text_field( wp_unslash( $_POST['table_rate_handling_fee'] ) ) : '';
        $min_cost           = isset( $_POST['table_rate_min_cost'] ) ? sanitize_text_field( wp_unslash( $_POST['table_rate_min_cost'] ) ) : '';
        $max_cost           = isset( $_POST['table_rate_max_cost'] ) ? sanitize_text_field( wp_unslash( $_POST['table_rate_max_cost'] ) ) : '';
        $classes_priorities = array();
        $default_priority   = 0;

        if ( empty( $_POST['table_rate_calculation_type'] ) && isset( $_POST['dokan_table_rate_priorities'] ) ) {
            $classes_priorities = array_map( 'intval', (array) $_POST['dokan_table_rate_priorities'] );
        }

        if ( empty( $_POST['table_rate_calculation_type'] ) && isset( $_POST['dokan_table_rate_default_priority'] ) ) {
            $default_priority = (int) sanitize_text_field( wp_unslash( $_POST['dokan_table_rate_default_priority'] ) );
        }

        $general_settings = array(
            'title'              => $title,
            'tax_status'         => $tax_status,
            'prices_include_tax' => $prices_include_tax,
            'order_handling_fee' => $order_handling_fee,
            'max_shipping_cost'  => $max_shipping_cost,
            'calculation_type'   => $calculation_type,
            'handling_fee'       => $handling_fee,
            'min_cost'           => $min_cost,
            'max_cost'           => $max_cost,
            'classes_priorities' => $classes_priorities,
            'default_priority'   => $default_priority,
        );

        return array(
            'method_id' => 'dokan_table_rate_shipping',
            'zone_id'   => $zone_id,
            'seller_id' => dokan_get_current_user_id(),
            'settings'  => maybe_serialize( $general_settings ),
        );
    }

    /**
     * Delete table rates when main shipping zone deleted by admin
     *
     * @since 3.4.0
     *
     * @param id $zone_id
     *
     * @return void
     */
    public function delete_table_rates_by_zone( $zone_id ) {
        global $wpdb;

        // Delete dokan table rates data when deleted zone from admin area
        $wpdb->delete( $wpdb->prefix . 'dokan_table_rate_shipping', array( 'zone_id' => $zone_id ) );

        do_action( 'dokan_delete_table_rates_by_zone', $zone_id );
    }

    /**
     * Delete table rates when main shipping method deleted by vendor
     *
     * @since 3.4.0
     *
     * @param id $zone_id
     * @param id $instance_id
     *
     * @return void
     */
    public function delete_table_rates_by_method( $zone_id, $instance_id ) {
        if ( ! $instance_id ) {
            return;
        }
        global $wpdb;

        // Delete dokan table rates data when deleted method from vendor area
        $wpdb->delete(
            $wpdb->prefix . 'dokan_table_rate_shipping', array(
                'zone_id'     => $zone_id,
                'instance_id' => $instance_id,
            )
        );

        do_action( 'dokan_after_delete_table_rates_by_method', $zone_id, $instance_id );
    }

    /**
     * Save/update table rate rows
     *
     * @since 3.4.0
     *
     * @param int $instance_id
     *
     * @return void
     */
    public function save_table_rate_rows( $zone_id, $instance_id ) {
        if ( ! isset( $_POST['dokan_table_rate_shipping_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['dokan_table_rate_shipping_settings_nonce'] ), 'dokan_table_rate_shipping_settings' )
        ) {
            return;
        }

        if ( ! $instance_id ) {
            return;
        }

        global $wpdb;

        // Clear transient if have
        $wpdb->query( "DELETE FROM `$wpdb->options` WHERE `option_name` LIKE ('_transient_dokan_ship_%')" );

        // Save dokan table rates
        // @codingStandardsIgnoreStart
        $rate_ids                 = isset( $_POST['rate_id'] ) ? array_map( 'intval', $_POST['rate_id'] ) : array();
        $shipping_class           = isset( $_POST['shipping_class'] ) ? array_map( 'wc_clean', $_POST['shipping_class'] ) : array();
        $shipping_condition       = isset( $_POST['shipping_condition'] ) ? array_map( 'wc_clean', $_POST['shipping_condition'] ) : array();
        $shipping_min             = isset( $_POST['shipping_min'] ) ? array_map( 'wc_clean', $_POST['shipping_min'] ) : array();
        $shipping_max             = isset( $_POST['shipping_max'] ) ? array_map( 'wc_clean', $_POST['shipping_max'] ) : array();
        $shipping_cost            = isset( $_POST['shipping_cost'] ) ? array_map( 'wc_clean', $_POST['shipping_cost'] ) : array();
        $shipping_per_item        = isset( $_POST['shipping_per_item'] ) ? array_map( 'wc_clean', $_POST['shipping_per_item'] ) : array();
        $shipping_cost_per_weight = isset( $_POST['shipping_cost_per_weight'] ) ? array_map( 'wc_clean', $_POST['shipping_cost_per_weight'] ) : array();
        $cost_percent             = isset( $_POST['shipping_cost_percent'] ) ? array_map( 'wc_clean', $_POST['shipping_cost_percent'] ) : array();
        $shipping_label           = isset( $_POST['shipping_label'] ) ? array_map( 'wc_clean', $_POST['shipping_label'] ) : array();
        $shipping_priority        = isset( $_POST['shipping_priority'] ) ? array_map( 'wc_clean', $_POST['shipping_priority'] ) : array();
        $shipping_abort           = isset( $_POST['shipping_abort'] ) ? array_map( 'wc_clean', $_POST['shipping_abort'] ) : array();
        $shipping_abort_reason    = isset( $_POST['shipping_abort_reason'] ) ? array_map( 'wc_clean', $_POST['shipping_abort_reason'] ) : array();
        // @codingStandardsIgnoreEnd
        $max_key                  = ( $rate_ids ) ? max( array_keys( $rate_ids ) ) : 0;
        $precision                = function_exists( 'wc_get_rounding_precision' ) ? wc_get_rounding_precision() : 4;

        for ( $i = 0; $i <= $max_key; $i++ ) {
            if ( ! isset( $rate_ids[ $i ] ) ) {
                continue;
            }

            $rate_id                   = $rate_ids[ $i ];
            $rate_class                = isset( $shipping_class[ $i ] ) ? $shipping_class[ $i ] : '';
            $rate_condition            = $shipping_condition[ $i ];
            $rate_min                  = isset( $shipping_min[ $i ] ) ? $shipping_min[ $i ] : '';
            $rate_max                  = isset( $shipping_max[ $i ] ) ? $shipping_max[ $i ] : '';
            $rate_cost                 = isset( $shipping_cost[ $i ] ) ? wc_format_decimal( $shipping_cost[ $i ], $precision, true ) : '';
            $rate_cost_per_item        = isset( $shipping_per_item[ $i ] ) ? wc_format_decimal( $shipping_per_item[ $i ], $precision, true ) : '';
            $rate_cost_per_weight_unit = isset( $shipping_cost_per_weight[ $i ] ) ? wc_format_decimal( $shipping_cost_per_weight[ $i ], $precision, true ) : '';
            $rate_cost_percent         = isset( $cost_percent[ $i ] ) ? wc_format_decimal( str_replace( '%', '', $cost_percent[ $i ] ), $precision, true ) : '';
            $rate_label                = isset( $shipping_label[ $i ] ) ? $shipping_label[ $i ] : '';
            $rate_priority             = isset( $shipping_priority[ $i ] ) ? 1 : 0;
            $rate_abort                = isset( $shipping_abort[ $i ] ) ? 1 : 0;
            $rate_abort_reason         = isset( $shipping_abort_reason[ $i ] ) ? $shipping_abort_reason[ $i ] : '';

            // Format min and max
            switch ( $rate_condition ) {
                case 'weight':
                case 'price':
                    if ( $rate_min ) {
                        $rate_min = wc_format_decimal( $rate_min, $precision, true );
                    }
                    if ( $rate_max ) {
                        $rate_max = wc_format_decimal( $rate_max, $precision, true );
                    }
                    break;
                case 'items':
                case 'items_in_class':
                    if ( $rate_min ) {
                        $rate_min = round( $rate_min );
                    }
                    if ( $rate_max ) {
                        $rate_max = round( $rate_max );
                    }
                    break;
                default:
                    $rate_min = '';
                    $rate_max = '';
                    break;
            }

            // Insert dokan table rate data if found rate_id
            if ( $rate_id > 0 ) {
                $wpdb->update(
                    $wpdb->prefix . 'dokan_table_rate_shipping',
                    array(
                        'vendor_id'                 => dokan_get_current_user_id(),
                        'zone_id'                   => $zone_id,
                        'instance_id'               => $instance_id,
                        'rate_class'                => $rate_class,
                        'rate_condition'            => sanitize_title( $rate_condition ),
                        'rate_min'                  => $rate_min,
                        'rate_max'                  => $rate_max,
                        'rate_cost'                 => $rate_cost,
                        'rate_cost_per_item'        => $rate_cost_per_item,
                        'rate_cost_per_weight_unit' => $rate_cost_per_weight_unit,
                        'rate_cost_percent'         => $rate_cost_percent,
                        'rate_label'                => $rate_label,
                        'rate_priority'             => $rate_priority,
                        'rate_order'                => $i,
                        'rate_abort'                => $rate_abort,
                        'rate_abort_reason'         => $rate_abort_reason,
                    ),
                    array(
                        'rate_id' => $rate_id,
                    ),
                    array(
                        '%d',
                        '%d',
                        '%d',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%d',
                        '%d',
                        '%s',
                    ),
                    array(
                        '%d',
                    )
                );

            // Update dokan table rate data if not found rate_id
            } else {
                $result = $wpdb->insert(
                    $wpdb->prefix . 'dokan_table_rate_shipping',
                    array(
                        'vendor_id'                 => dokan_get_current_user_id(),
                        'zone_id'                   => $zone_id,
                        'instance_id'               => $instance_id,
                        'rate_class'                => $rate_class,
                        'rate_condition'            => sanitize_title( $rate_condition ),
                        'rate_min'                  => $rate_min,
                        'rate_max'                  => $rate_max,
                        'rate_cost'                 => $rate_cost,
                        'rate_cost_per_item'        => $rate_cost_per_item,
                        'rate_cost_per_weight_unit' => $rate_cost_per_weight_unit,
                        'rate_cost_percent'         => $rate_cost_percent,
                        'rate_label'                => $rate_label,
                        'rate_priority'             => $rate_priority,
                        'rate_order'                => $i,
                        'rate_abort'                => $rate_abort,
                        'rate_abort_reason'         => $rate_abort_reason,
                    ),
                    array(
                        '%d',
                        '%d',
                        '%d',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%d',
                        '%d',
                        '%s',
                    )
                );
            }
        }
    }

    /**
     * Save/update distance rate rows
     *
     * @since 3.4.2
     *
     * @param int $instance_id
     *
     * @return void
     */
    public function save_distance_rate_rows( $zone_id, $instance_id ) {
        if ( ! isset( $_POST['dokan_distance_rate_shipping_settings_nonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['dokan_distance_rate_shipping_settings_nonce'] ), 'dokan_distance_rate_shipping_settings' )
        ) {
            return;
        }

        if ( ! $instance_id ) {
            return;
        }

        global $wpdb;

        // Save dokan distance rates
        // @codingStandardsIgnoreStart
        $rate_ids           = isset( $_POST['rate_id'] ) ? array_map( 'intval', $_POST['rate_id'] ) : array();
        $shipping_condition = isset( $_POST['rate_condition'] ) ? array_map( 'wc_clean', $_POST['rate_condition'] ) : array();
        $shipping_min       = isset( $_POST['rate_min'] ) ? array_map( 'wc_clean', $_POST['rate_min'] ) : array();
        $shipping_max       = isset( $_POST['rate_max'] ) ? array_map( 'wc_clean', $_POST['rate_max'] ) : array();
        $shipping_cost      = isset( $_POST['rate_cost'] ) ? array_map( 'wc_clean', $_POST['rate_cost'] ) : array();
        $shipping_cost_unit = isset( $_POST['rate_cost_unit'] ) ? array_map( 'wc_clean', $_POST['rate_cost_unit'] ) : array();
        $shipping_fee       = isset( $_POST['rate_fee'] ) ? array_map( 'wc_clean', $_POST['rate_fee'] ) : array();
        $shipping_break     = isset( $_POST['rate_break'] ) ? array_map( 'wc_clean', $_POST['rate_break'] ) : array();
        $shipping_abort     = isset( $_POST['rate_abort'] ) ? array_map( 'wc_clean', $_POST['rate_abort'] ) : array();
        // @codingStandardsIgnoreEnd
        $max_key                  = ( $rate_ids ) ? max( array_keys( $rate_ids ) ) : 0;
        $precision                = function_exists( 'wc_get_rounding_precision' ) ? wc_get_rounding_precision() : 4;

        for ( $i = 0; $i <= $max_key; $i++ ) {
            if ( ! isset( $rate_ids[ $i ] ) ) {
                continue;
            }

            $rate_id            = $rate_ids[ $i ];
            $rate_condition     = $shipping_condition[ $i ];
            $rate_min           = isset( $shipping_min[ $i ] ) ? $shipping_min[ $i ] : '';
            $rate_max           = isset( $shipping_max[ $i ] ) ? $shipping_max[ $i ] : '';
            $rate_cost          = isset( $shipping_cost[ $i ] ) ? wc_format_decimal( $shipping_cost[ $i ], $precision, true ) : '';
            $rate_cost_unit     = isset( $shipping_cost_unit[ $i ] ) ? wc_format_decimal( $shipping_cost_unit[ $i ], $precision, true ) : '';
            $rate_fee           = isset( $shipping_fee[ $i ] ) ? wc_format_decimal( $shipping_fee[ $i ], $precision, true ) : '';
            $rate_break         = isset( $shipping_break[ $i ] ) ? 1 : 0;
            $rate_abort         = isset( $shipping_abort[ $i ] ) ? 1 : 0;

            // Format min and max
            switch ( $rate_condition ) {
                case 'weight':
                case 'total':
                    if ( $rate_min ) {
                        $rate_min = wc_format_decimal( $rate_min, $precision, true );
                    }
                    if ( $rate_max ) {
                        $rate_max = wc_format_decimal( $rate_max, $precision, true );
                    }
                    break;
                case 'distance':
                case 'time':
                case 'quantity':
                    if ( $rate_min ) {
                        $rate_min = round( $rate_min );
                    }
                    if ( $rate_max ) {
                        $rate_max = round( $rate_max );
                    }
                    break;
                default:
                    $rate_min = '';
                    $rate_max = '';
                    break;
            }

            // Insert dokan table rate data if found rate_id
            if ( $rate_id > 0 ) {
                $wpdb->update(
                    $wpdb->prefix . 'dokan_distance_rate_shipping',
                    array(
                        'vendor_id'      => dokan_get_current_user_id(),
                        'zone_id'        => $zone_id,
                        'instance_id'    => $instance_id,
                        'rate_condition' => sanitize_title( $rate_condition ),
                        'rate_min'       => $rate_min,
                        'rate_max'       => $rate_max,
                        'rate_cost'      => $rate_cost,
                        'rate_cost_unit' => $rate_cost_unit,
                        'rate_fee'       => $rate_fee,
                        'rate_break'     => $rate_break,
                        'rate_abort'     => $rate_abort,
                        'rate_order'     => $i,
                    ),
                    array(
                        'rate_id' => $rate_id,
                    ),
                    array(
                        '%d',
                        '%d',
                        '%d',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%d',
                        '%d',
                        '%d',
                    ),
                    array(
                        '%d',
                    )
                );

                // Update dokan table rate data if not found rate_id
            } else {
                $result = $wpdb->insert(
                    $wpdb->prefix . 'dokan_distance_rate_shipping',
                    array(
                        'vendor_id'      => dokan_get_current_user_id(),
                        'zone_id'        => $zone_id,
                        'instance_id'    => $instance_id,
                        'rate_condition' => sanitize_title( $rate_condition ),
                        'rate_min'       => $rate_min,
                        'rate_max'       => $rate_max,
                        'rate_cost'      => $rate_cost,
                        'rate_cost_unit' => $rate_cost_unit,
                        'rate_fee'       => $rate_fee,
                        'rate_break'     => $rate_break,
                        'rate_abort'     => $rate_abort,
                        'rate_order'     => $i,
                    ),
                    array(
                        '%d',
                        '%d',
                        '%d',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%s',
                        '%d',
                        '%d',
                        '%d',
                    )
                );
            }
        }
    }
}
