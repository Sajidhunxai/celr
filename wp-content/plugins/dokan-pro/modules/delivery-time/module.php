<?php


namespace WeDevs\DokanPro\Modules\DeliveryTime;

use WeDevs\Dokan\Traits\ChainableContainer;
use WeDevs\DokanPro\Modules\DeliveryTime\Admin;
use WeDevs\DokanPro\Modules\DeliveryTime\Emails\Manager;
use WeDevs\DokanPro\Modules\DeliveryTime\Vendor;
use WeDevs\DokanPro\Modules\DeliveryTime\Frontend;
use WeDevs\DokanPro\Modules\DeliveryTime\StorePickup\StoreSettings;

/**
 * Class Module
 *
 * @package WeDevs\DokanPro\DeliveryTime
 */
class Module {

    use ChainableContainer;

    /**
     * Delivery Time Manager constructor
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function __construct() {
        $this->define_constant();
        $this->initiate();

        add_action( 'dokan_activated_module_delivery_time', [ $this, 'activate' ] );
        add_filter( 'dokan_set_template_path', [ $this, 'load_templates' ], 10, 3 );
        add_action( 'wp_enqueue_scripts', [ $this, 'register_frontend_scripts' ] );
        add_action( 'init', [ $this, 'register_admin_scripts' ] );

        // flush rewrite rules
        add_action( 'woocommerce_flush_rewrite_rules', [ $this, 'flush_rewrite_rules' ] );
    }

    /**
     * Define all constants
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function define_constant() {
        define( 'DOKAN_DELIVERY_TIME_DIR', dirname( __FILE__ ) );
        define( 'DOKAN_DELIVERY_INC_DIR', DOKAN_DELIVERY_TIME_DIR . '/includes' );
        define( 'DOKAN_DELIVERY_TEMPLATE_DIR', DOKAN_DELIVERY_TIME_DIR . '/templates' );
        define( 'DOKAN_DELIVERY_TIME_ASSETS_DIR', plugins_url( 'assets', __FILE__ ) );
    }

    /**
     * Initiates the classes
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function initiate() {
        // Load Delivery Time Admin class
        if ( is_admin() ) {
            $this->container['dt_admin']    = new Admin();
            $this->container['dt_settings'] = new Settings();
        }

        // Load Delivery Time Frontend class
        if ( ! is_admin() || ( defined( 'DOING_AJAX' ) && DOING_AJAX ) ) {
            $this->container['dt_frontend'] = new Frontend();
        }

        // Load Delivery Time Vendor class
        $this->container['dt_vendor'] = new Vendor();

        // Load Store Location Pickup classes
        $this->container['dt_store_location_pickup']          = new StoreSettings();
        $this->container['dt_store_location_pickup_frontend'] = new StorePickup\Frontend();
        $this->container['dt_store_location_pickup_vendor']   = new StorePickup\Vendor();

        // Load Delivery Time Email Manager class.
        new Manager();
    }

    /**
     * Activates the module
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function activate() {
        $this->create_tables();
        $this->flush_rewrite_rules();
    }

    /**
     * Flush rewrite rules
     *
     * @since 3.3.1
     *
     * @return void
     */
    public function flush_rewrite_rules() {
        dokan()->rewrite->register_rule();
        flush_rewrite_rules( true );
    }

    /**
     * Set template path for Wholesale
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function load_templates( $template_path, $template, $args ) {
        if ( isset( $args['is_delivery_time'] ) && $args['is_delivery_time'] ) {
            return DOKAN_DELIVERY_TEMPLATE_DIR;
        }

        return $template_path;
    }

    /**
     * Creates Delivery time database table
     *
     * @since 3.3.0
     *
     * @return void
     */
    private function create_tables() {
        global $wpdb;

        include_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $collate = $wpdb->get_charset_collate();

        $table = "CREATE TABLE IF NOT EXISTS `{$wpdb->prefix}dokan_delivery_time` (
                  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
                  `order_id` int(11) NOT NULL,
                  `vendor_id` int(11) NOT NULL,
                  `date` varchar(25) NOT NULL DEFAULT '',
                  `slot` varchar(25) NOT NULL DEFAULT '',
                  `delivery_type` varchar(25) DEFAULT 'delivery',
                  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
                  PRIMARY KEY (`id`),
                  KEY `key_vendor_id_date` (`vendor_id`,`date`),
                  KEY `key_vendor_id_date_type` (`vendor_id`,`date`,`delivery_type`),
                  KEY `key_slot` (`slot`)
                ) ENGINE=InnoDB {$collate}";

        dbDelta( $table );
    }

    /**
     * Registers frontend scripts
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function register_frontend_scripts() {
        // Use minified libraries if SCRIPT_DEBUG is turned off
        $suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
        $version = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? time() : DOKAN_PRO_PLUGIN_VERSION;

        $data = [
            'delivery_date'         => __( 'Delivery Date:', 'dokan' ),
            'pickup_date'           => __( 'Store Pickup Date:', 'dokan' ),
            'delivery_placeholder'  => __( 'Select delivery date', 'dokan' ),
            'pickup_placeholder'    => __( 'Select pickup date', 'dokan' ),
            'delivery_time_heading' => __( 'Delivery Time', 'dokan' ),
            'pickup_time_heading'   => __( 'Store Pickup Time', 'dokan' ),
            'time_format'           => wc_time_format(),
        ];

        wp_register_script( 'dokan-delivery-time-main-script', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/js/script-main' . $suffix . '.js', [ 'jquery' ], $version, true );
        wp_register_script( 'dokan-delivery-time-vendor-script', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/js/script-vendor' . $suffix . '.js', [ 'jquery' ], $version, true );

        wp_register_script( 'dokan-delivery-time-flatpickr-script', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/vendor/flatpickr.min.js', [], $version, true );
        wp_register_style( 'dokan-delivery-time-flatpickr-style', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/vendor/flatpickr.min.css', [], $version, 'all' );

        wp_register_script( 'dokan-delivery-time-fullcalender-script', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/vendor/fullcalender.min.js', [], $version, true );
        wp_register_script( 'dokan-delivery-time-fullcalender-local', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/vendor/fullcalender.locales-all.min.js', [ 'dokan-delivery-time-fullcalender-script' ], $version, true );
        wp_register_style( 'dokan-delivery-time-fullcalender-style', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/vendor/fullcalender.min.css', [], $version, 'all' );

        wp_register_style( 'dokan-delivery-time-vendor-style', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/css/script-style' . $suffix . '.css', [], $version, 'all' );

        wp_register_script( 'dokan-store-location-pickup-script', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/js/script-store-location-pickup' . $suffix . '.js', [ 'jquery' ], $version, true );

        $local           = strtolower( str_replace( '_', '-', get_locale() ) );
        $localize_script = [
            'code'   => $local,
            'code_1' => substr( $local, 0, 2 ),
        ];

        $flatpickr_localize_script = [
            'firstDayOfWeek'   => intval( get_option( 'start_of_week', 0 ) ),
            'rangeSeparator'   => _x( ' to ', 'Flatpickr calendar date range separator', 'dokan' ),
            'weekAbbreviation' => _x( 'Wk', 'Flatpickr calendar week abbreviation', 'dokan' ),
            'scrollTitle'      => _x( 'Scroll to increment', 'Flatpickr calendar title for scroll to increment', 'dokan' ),
            'toggleTitle'      => _x( 'Click to toggle', 'Flatpickr calendar title for click to toggle', 'dokan' ),
            'yearAriaLabel'    => _x( 'Year', 'Flatpickr calendar aria label for year', 'dokan' ),
            'monthAriaLabel'   => _x( 'Month', 'Flatpickr calendar aria label for month', 'dokan' ),
            'hourAriaLabel'    => _x( 'Hour', 'Flatpickr calendar aria label for hour', 'dokan' ),
            'minuteAriaLabel'  => _x( 'Minute', 'Flatpickr calendar aria label for minute', 'dokan' ),
            'amPM'             => [
                _x( 'AM', 'Flatpickr calendar time AM label', 'dokan' ),
                _x( 'PM', 'Flatpickr calendar time PM label', 'dokan' )
            ],
            'weekdays'         => [
                'shorthand' => [
                    _x( 'Sun', 'Flatpickr calender shorthand weekday', 'dokan' ),
                    _x( 'Mon', 'Flatpickr calender shorthand weekday', 'dokan' ),
                    _x( 'Tue', 'Flatpickr calender shorthand weekday', 'dokan' ),
                    _x( 'Wed', 'Flatpickr calender shorthand weekday', 'dokan' ),
                    _x( 'Thu', 'Flatpickr calender shorthand weekday', 'dokan' ),
                    _x( 'Fri', 'Flatpickr calender shorthand weekday', 'dokan' ),
                    _x( 'Sat', 'Flatpickr calender shorthand weekday', 'dokan' )
                ],
                'longhand'  => [
                    _x( 'Sunday', 'Flatpickr calender long weekday', 'dokan' ),
                    _x( 'Monday', 'Flatpickr calender long weekday', 'dokan' ),
                    _x( 'Tuesday', 'Flatpickr calender long weekday', 'dokan' ),
                    _x( 'Wednesday', 'Flatpickr calender long weekday', 'dokan' ),
                    _x( 'Thursday', 'Flatpickr calender long weekday', 'dokan' ),
                    _x( 'Friday', 'Flatpickr calender long weekday', 'dokan' ),
                    _x( 'Saturday', 'Flatpickr calender long weekday', 'dokan' )
                ],
            ],
            'months'           => [
                'shorthand' => [
                    _x( 'Jan', 'Flatpickr calender shorthand month name', 'dokan' ),
                    _x( 'Feb', 'Flatpickr calender shorthand month name', 'dokan' ),
                    _x( 'Mar', 'Flatpickr calender shorthand month name', 'dokan' ),
                    _x( 'Apr', 'Flatpickr calender shorthand month name', 'dokan' ),
                    _x( 'May', 'Flatpickr calender shorthand month name', 'dokan' ),
                    _x( 'Jun', 'Flatpickr calender shorthand month name', 'dokan' ),
                    _x( 'Jul', 'Flatpickr calender shorthand month name', 'dokan' ),
                    _x( 'Aug', 'Flatpickr calender shorthand month name', 'dokan' ),
                    _x( 'Sep', 'Flatpickr calender shorthand month name', 'dokan' ),
                    _x( 'Oct', 'Flatpickr calender shorthand month name', 'dokan' ),
                    _x( 'Nov', 'Flatpickr calender shorthand month name', 'dokan' ),
                    _x( 'Dec', 'Flatpickr calender shorthand month name', 'dokan' )
                ],
                'longhand' => [
                    _x( 'January', 'Flatpickr calender long month name', 'dokan' ),
                    _x( 'February', 'Flatpickr calender long month name', 'dokan' ),
                    _x( 'March', 'Flatpickr calender long month name', 'dokan' ),
                    _x( 'April', 'Flatpickr calender long month name', 'dokan' ),
                    _x( 'May', 'Flatpickr calender long month name', 'dokan' ),
                    _x( 'June', 'Flatpickr calender long month name', 'dokan' ),
                    _x( 'July', 'Flatpickr calender long month name', 'dokan' ),
                    _x( 'August', 'Flatpickr calender long month name', 'dokan' ),
                    _x( 'September', 'Flatpickr calender long month name', 'dokan' ),
                    _x( 'October', 'Flatpickr calender long month name', 'dokan' ),
                    _x( 'November', 'Flatpickr calender long month name', 'dokan' ),
                    _x( 'December', 'Flatpickr calender long month name', 'dokan' )
                ],
            ],
        ];

        wp_enqueue_style( 'dokan-timepicker' );

        wp_localize_script( 'dokan-delivery-time-vendor-script', 'Vendor_Delivery_Data', $data );
        wp_localize_script( 'dokan-delivery-time-fullcalender-script', 'dokan_full_calendar_i18n', $localize_script );
        wp_localize_script( 'dokan-delivery-time-flatpickr-script', 'dokan_flatpickr_i18n', $flatpickr_localize_script );
    }

    /**
     * Registers admin scripts
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function register_admin_scripts() {
        // Use minified libraries if SCRIPT_DEBUG is turned off
        $suffix  = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';
        $version = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? time() : DOKAN_PRO_PLUGIN_VERSION;

        $data = [
            'pickup_slot'            => __( 'Pickup time slot:', 'dokan' ),
            'pickup_date'            => __( 'Pickup date:', 'dokan' ),
            'delivery_date'          => __( 'Delivery date:', 'dokan' ),
            'delivery_slot'          => __( 'Delivery time slot:', 'dokan' ),
            'pickup_header'          => __( 'Store Pickup Time', 'dokan' ),
            'pickup_location'        => __( 'Pickup location:', 'dokan' ),
            'delivery_header'        => __( 'Delivery Time', 'dokan' ),
            'pickup_placeholder'     => __( 'Select pickup date', 'dokan' ),
            'delivery_placeholder'   => __( 'Select delivery date', 'dokan' ),
            'pickup_current_label'   => __( 'Current pickup time: ', 'dokan' ),
            'delivery_current_label' => __( 'Current delivery time: ', 'dokan' ),
        ];

        wp_register_script( 'dokan-delivery-time-admin-script', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/js/script-admin' . $suffix . '.js', [ 'jquery', 'jquery-ui-datepicker' ], $version, true );
        wp_register_script( 'dokan-admin-delivery-time', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/js/script-components' . $suffix . '.js', [ 'jquery', 'dokan-vue-bootstrap' ], $version, true );

        wp_register_style( 'dokan-admin-delivery-time', DOKAN_DELIVERY_TIME_ASSETS_DIR . '/css/script-components' . $suffix . '.css', false, $version, 'all' );

        wp_localize_script( 'dokan-delivery-time-admin-script', 'Admin_Delivery_Meta', $data );
    }
}
