<?php


namespace WeDevs\DokanPro\Modules\DeliveryTime;

/**
 * Class Settings
 *
 * @since 3.3.0
 *
 * @package WeDevs\DokanPro\Modules\DeliveryTime
 */
class Settings {

    /**
     * Settings constructor
     *
     * @since 3.3.0
     */
    public function __construct() {
        // Hooks
        add_filter( 'dokan_settings_sections', [ $this, 'load_settings_section' ], 21 );
        add_filter( 'dokan_settings_fields', [ $this, 'load_settings_fields' ], 21 );
        add_action( 'dokan_before_saving_settings', [ $this, 'validate_admin_delivery_settings' ], 20, 2 );
        add_action( 'dokan_after_saving_settings', [ $this, 'generate_admin_delivery_time_settings' ], 20, 2 );
    }

    /**
     * Load admin settings section
     *
     * @since 3.3.0
     *
     * @param array $section
     *
     * @return array
     */
    public function load_settings_section( $section ) {
        $section[] = [
            'id'                   => 'dokan_delivery_time',
            'title'                => __( 'Delivery Time', 'dokan' ),
            'icon_url'             => DOKAN_DELIVERY_TIME_ASSETS_DIR . '/images/delivery-time.svg',
            'description'          => __( 'Delivery Schedule Setup', 'dokan' ),
            'document_link'        => 'https://wedevs.com/docs/dokan/modules/dokan-delivery-time/',
            'settings_title'       => __( 'Delivery Time Settings', 'dokan' ),
            'settings_description' => __( 'You can configure your site to allow customers to choose the time and date they want their products delivered.', 'dokan' ),
        ];

        return $section;
    }

    /**
     * Load all settings fields
     *
     * @since 3.3.0
     *
     * @param array $fields
     *
     * @return array
     */
    public function load_settings_fields( $fields ) {
        $days                  = dokan_get_translated_days();
        $week_starts_day       = reset( $days );
        $week_ends_day         = end( $days );
        $delivery_settings_key = [
            'delivery_status',
            'opening_time',
            'closing_time',
        ];

        $fields['dokan_delivery_time'] = [
            'allow_vendor_override_settings' => [
                'name'    => 'allow_vendor_override_settings',
                'label'   => __( 'Allow Vendor Settings', 'dokan' ),
                'desc'    => __( 'Allow vendor to override settings', 'dokan' ),
                'type'    => 'switcher',
                'default' => 'off',
                'tooltip' => __( 'Check this to allow vendors to override & customize the delivery settings. Otherwise, admin configured settings will be applied.', 'dokan' ),
            ],
            'delivery_support' => [
                'name'    => 'delivery_support',
                'label'   => __( 'Delivery support', 'dokan' ),
                'type'    => 'multicheck',
                'default' => [
                    'delivery'     => 'delivery',
                    'store-pickup' => 'store-pickup',
                ],
                'options' => [
                    'delivery'     => __( 'Home Delivery', 'dokan' ),
                    'store-pickup' => __( 'Store Pickup', 'dokan' ),
                ],
            ],
            'delivery_date_label' => [
                'name'    => 'delivery_date_label',
                'label'   => __( 'Delivery Date Label', 'dokan' ),
                'desc'    => __( 'This label will show on checkout page', 'dokan' ),
                'default' => __( 'Delivery Date', 'dokan' ),
                'type'    => 'text',
            ],
            'preorder_date' => [
                'name'    => 'preorder_date',
                'label'   => __( 'Delivery Blocked Buffer', 'dokan' ),
                'desc'    => __( 'How many days the delivery date is blocked from current date? 0 for no block buffer', 'dokan' ),
                'default' => '0',
                'type'    => 'number',
                'min'     => '0',
            ],
            'time_slot_minutes' => [
                'name'    => 'time_slot_minutes',
                'label'   => __( 'Time Slot', 'dokan' ),
                'desc'    => __( 'Time slot in minutes. Please keep opening and closing time divisible by slot minutes. E.g ( 30, 60, 120 )', 'dokan' ),
                'default' => '30',
                'type'    => 'number',
                'step'    => '30',
                'max'     => '360',
            ],
            'order_per_slot' => [
                'name'    => 'order_per_slot',
                'label'   => __( 'Order Per Slot', 'dokan' ),
                'desc'    => __( 'How many orders you can process in a single slot? 0 for unlimited orders', 'dokan' ),
                'default' => '0',
                'type'    => 'number',
            ],
            'delivery_box_info' => [
                'name'    => 'delivery_box_info',
                'label'   => __( 'Delivery Box Info', 'dokan' ),
                /* translators: %s: day */
                'desc'    => sprintf( __( 'This info will show on checkout page delivery time box. %s will be replaced by delivery blocked buffer', 'dokan' ), '%DAY%' ),
                /* translators: %s: day */
                'default' => sprintf( __( 'This store needs %s day(s) to process your delivery request', 'dokan' ), '%DAY%' ),
                'type'    => 'text',
            ],
            'select_required' => [
                'name'    => 'selection_required',
                'label'   => __( 'Require Delivery Date and Time', 'dokan' ),
                'desc'    => __( 'Make choosing a delivery date and time mandatory for customers.', 'dokan' ),
                'default' => 'on',
                'type'    => 'switcher',
            ],
            'delivery_day' => [
                'name'          => 'delivery_day',
                'type'          => 'sub_section',
                'label'         => __( 'Delivery Days', 'dokan' ),
                'description'   => __( 'Configure your delivery time settings and control access to your site.', 'dokan' ),
                'content_class' => 'sub-section-styles',
            ],
        ];

        foreach ( $days as $key => $day ) {
            $fields['dokan_delivery_time'][ "delivery_day_$key" ] = [
                'name'    => "delivery_day_$key",
                'day'     => $key,
                'label'   => $day,
                'type'    => 'day_timer',
                'options' => array_combine( $delivery_settings_key, $delivery_settings_key ),
                'default' => array_fill_keys( $delivery_settings_key, '' ),
            ];

            $fields['dokan_delivery_time'][ "delivery_day_$key" ]['content_class'] = ( $day === $week_starts_day ? 'field_top_styles' : ( $day === $week_ends_day ? 'field_bottom_styles' : '' ) );
        }

        return $fields;
    }


    /**
     * Validates admin delivery settings
     *
     * @since 3.3.0
     *
     * @param string $option_name
     * @param array $option_value
     *
     * @return void
     */
    public function validate_admin_delivery_settings( $option_name, $option_value ) {
        if ( 'dokan_delivery_time' !== $option_name ) {
            return;
        }

        $errors = [];

        $delivery_date_label     = ! empty( $option_value['delivery_date_label'] ) ? sanitize_text_field( wp_unslash( $option_value['delivery_date_label'] ) ) : '';
        $delivery_blocked_buffer = ! empty( $option_value['preorder_date'] ) ? sanitize_text_field( wp_unslash( $option_value['preorder_date'] ) ) : '';
        $delivery_box_info       = ! empty( $option_value['delivery_box_info'] ) ? sanitize_text_field( wp_unslash( $option_value['delivery_box_info'] ) ) : '';
        $time_slot_minutes       = ! empty( $option_value['time_slot_minutes'] ) ? sanitize_text_field( wp_unslash( $option_value['time_slot_minutes'] ) ) : '';
        $order_per_slot          = ! empty( $option_value['order_per_slot'] ) ? sanitize_text_field( wp_unslash( $option_value['order_per_slot'] ) ) : '';

        if ( empty( $delivery_date_label ) ) {
            $errors[] = [
                'name'  => 'delivery_date_label',
                'error' => __( 'Delivery date label can not be empty', 'dokan' ),
            ];
        }

        if ( null === $delivery_blocked_buffer || intval( $delivery_blocked_buffer ) < 0 ) {
            $errors[] = [
                'name'  => 'preorder_date',
                'error' => __( 'Delivery blocked buffer can not be empty or less than 0', 'dokan' ),
            ];
        }

        if ( empty( $delivery_box_info ) ) {
            $errors[] = [
                'name'  => 'delivery_box_info',
                'error' => __( 'Delivery box information can not be empty', 'dokan' ),
            ];
        }

        // Check all delivery time settings field and throw error messages.
        foreach ( dokan_get_translated_days() as $day_key => $day_name ) {
            $delivery_status = $option_value[ 'delivery_day_' . $day_key ]['delivery_status'];
            if ( empty( $delivery_status ) ) {
                continue;
            }

            $opening_time = $option_value[ 'delivery_day_' . $day_key ]['opening_time'];
            $closing_time = $option_value[ 'delivery_day_' . $day_key ]['closing_time'];
            if ( empty( $opening_time ) || empty( $closing_time ) ) {
                $errors[] = [
                    'name'  => 'delivery_day_' . $day_key,
                    'error' => __( 'Delivery time can not be empty', 'dokan' ),
                ];
                continue;
            }

            if ( strtotime( $opening_time ) >= strtotime( $closing_time ) ) {
                $errors[] = [
                    'name'  => 'delivery_day_' . $day_key,
                    'error' => __( 'Opening time must be greater than closing time', 'dokan' ),
                ];
            }
        }

        if ( ! is_int( intval( $time_slot_minutes ) ) || intval( $time_slot_minutes ) < 10 || intval( $time_slot_minutes ) > 1440 ) {
            $errors[] = [
                'name'  => 'time_slot_minutes',
                'error' => __( 'Time slot minutes can not be empty, less than 10 minutes or greater than 1440 minutes', 'dokan' ),
            ];
        }

        if ( null === $order_per_slot || intval( $order_per_slot ) < 0 ) {
            $errors[] = [
                'name'  => 'order_per_slot',
                'error' => __( 'Order per slot can not be empty or less than 0', 'dokan' ),
            ];
        }

        if ( ! empty( $errors ) ) {
            wp_send_json_error(
                [
                    'settings' => [
                        'name'  => $option_name,
                        'value' => $option_value,
                    ],
                    'message' => __( 'Validation error', 'dokan' ),
                    'errors'  => $errors,
                ],
                400
            );
        }
    }

    /**
     * Generates admin default delivery time settings for vendors
     *
     * @since 3.3.0
     *
     * @param string $option_name
     * @param array  $option_value
     *
     * @return void
     */
    public function generate_admin_delivery_time_settings( $option_name, $option_value ) {
        if ( 'dokan_delivery_time' !== $option_name ) {
            return;
        }

        $time_slots                        = [];
        $option_value['preorder_date']     = intval( $option_value['preorder_date'] );
        $option_value['order_per_slot']    = intval( $option_value['order_per_slot'] );
        $option_value['time_slot_minutes'] = intval( $option_value['time_slot_minutes'] );

        foreach ( dokan_get_translated_days() as $day_key => $day ) {
            $delivery_opening_time = ! empty( $option_value[ 'delivery_day_' . $day_key ]['opening_time'] ) ? $option_value[ 'delivery_day_' . $day_key ]['opening_time'] : '';
            $delivery_closing_time = ! empty( $option_value[ 'delivery_day_' . $day_key ]['closing_time'] ) ? $option_value[ 'delivery_day_' . $day_key ]['closing_time'] : '';

            $option_value[ 'delivery_day_' . $day_key ]['opening_time'] = ! empty( $delivery_opening_time ) ? dokan_convert_date_format( $delivery_opening_time, 'g:i a', 'g:i a' ) : '';
            $option_value[ 'delivery_day_' . $day_key ]['closing_time'] = ! empty( $delivery_closing_time ) ? dokan_convert_date_format( $delivery_closing_time, 'g:i a', 'g:i a' ) : '';

            // Generating time slots
            $time_slots[ $day_key ] = Helper::generate_delivery_time_slots(
                $option_value['time_slot_minutes'],
                $option_value[ 'delivery_day_' . $day_key ]['opening_time'],
                $option_value[ 'delivery_day_' . $day_key ]['closing_time']
            );
        }

        if ( ! empty( $time_slots ) ) {
            update_option( '_dokan_delivery_slot_settings', $time_slots );
            update_option( $option_name, $option_value );
        }
    }
}
