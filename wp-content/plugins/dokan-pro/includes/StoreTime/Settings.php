<?php

namespace WeDevs\DokanPro\StoreTime;

/**
 * Dokan Pro Store Open Close
 * Multiple Time Settings.
 *
 * @since 3.5.0
 */
class Settings {

    /**
     * Load automatically when class initiate
     *
     * @since 3.5.0
     *
     * @uses actions hook
     * @uses filter hook
     *
     * @return void
     */
    public function __construct() {
        // Added multiple fields for store open close multiple time settings.
        add_filter( 'dokan_pro_scripts', [ $this, 'register_scripts' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'load_frontend_scripts' ] );
        add_filter( 'dokan_store_time', [ $this, 'save_store_times' ] );
        add_filter( 'dokan_is_store_open', [ $this, 'check_seller_store_is_open' ], 10, 3 );

        // Added store all time open status & multiple slot for store multiple time settings.
        add_filter( 'dokan_store_time_template', [ $this, 'update_store_time_template' ] );
        add_filter( 'dokan_store_time_arguments', [ $this, 'update_store_time_template_args' ], 10, 2 );
        add_action( 'after_dokan_store_time_settings_form', [ $this, 'added_store_times' ], 10, 2 );
    }

    /**
     * Added script for store open close multiple time.
     *
     * @since 3.5.0
     *
     * @param array $scripts
     *
     * @return array
     */
    public function register_scripts( $scripts ) {
        // Use minified libraries if SCRIPT_DEBUG is turned off
        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

        $scripts['dokan-pro-store-open-close-time'] = [
            'src'       => DOKAN_PRO_PLUGIN_ASSEST . '/js/dokan-pro-store-open-close-time' . $suffix . '.js',
            'deps'      => [ 'jquery', 'dokan-minitoggle' ],
            'version'   => defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : DOKAN_PRO_PLUGIN_VERSION,
            'in_footer' => true,
        ];

        return $scripts;
    }

    /**
     * Load frontend scripts
     *
     * @since 3.7.6
     *
     * @return void
     */
    public function load_frontend_scripts() {
        global $wp;

        if ( ! isset( $wp->query_vars['settings'] ) || $wp->query_vars['settings'] !== 'store' ) {
            return;
        }

        $data = [
            'step'           => 30,
            'format'         => wc_time_format(),
            'placeholder'    => '00:00',
            'selectDefault'  => __( 'Select your store open days', 'dokan' ),
            'openingMaxTime' => ! is_tweleve_hour_format() ? '23:30' : '11:30 pm',
            'openingMinTime' => ! is_tweleve_hour_format() ? '00:00' : '12:00 am',
            'closingMaxTime' => ! is_tweleve_hour_format() ? '23:59' : '11:59 pm',
            'closingMinTime' => ! is_tweleve_hour_format() ? '00:29' : '12:29 am',
        ];

        wp_localize_script( 'dokan-pro-store-open-close-time', 'dokanMultipleTime', $data );
        wp_enqueue_script( 'dokan-pro-store-open-close-time' );
        wp_enqueue_style( 'dokan-select2-css' );
    }

    /**
     * Save store open close times here.
     *
     * @since 3.5.0
     *
     * @param array $dokan_store_time
     *
     * @return array
     */
    public function save_store_times( $dokan_store_time ) {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'dokan_store_settings_nonce' ) ) {
            return;
        }

        $store_days = ! empty( $_POST['store_day'] ) ? wc_clean( wp_unslash( $_POST['store_day'] ) ) : [];
        foreach ( dokan_get_translated_days() as $day => $value ) {
            if ( ! $store_days[ $day ] ) {
                $dokan_store_time[ $day ] = [
                    'status'       => 'close',
                    'opening_time' => [],
                    'closing_time' => [],
                ];

                continue;
            }

            $opening_times = ! empty( $_POST['opening_time'][ $day ] ) ? wc_clean( wp_unslash( $_POST['opening_time'][ $day ] ) ) : [];
            $closing_times = ! empty( $_POST['closing_time'][ $day ] ) ? wc_clean( wp_unslash( $_POST['closing_time'][ $day ] ) ) : [];

            // Save working status & opening, closing times array in 12 hours format.
            $dokan_store_time[ $day ] = [
                'status'       => 'open',
                'opening_time' => ! empty( $opening_times ) ? dokan_convert_date_format( $opening_times, 'g:i a', 'g:i a' ) : [],
                'closing_time' => ! empty( $closing_times ) ? dokan_convert_date_format( $closing_times, 'g:i a', 'g:i a' ) : [],
            ];
        }

        return $dokan_store_time;
    }

    /**
     * Check vendor store is open or close.
     *
     * @since 3.5.0
     *
     * @param bool   $store_open
     * @param string $today
     * @param array  $dokan_store_times
     *
     * @return bool
     */
    public function check_seller_store_is_open( $store_open, $today, $dokan_store_times ) {
        // If already true then return true.
        if ( $store_open ) {
            return $store_open;
        }

        $current_time = dokan_current_datetime();

        // Check if status is closed.
        if (
            empty( $dokan_store_times[ $today ] ) ||
            ( isset( $dokan_store_times[ $today ]['status'] ) &&
            'close' === $dokan_store_times[ $today ]['status'] )
        ) {
            return false;
        }

        // Get store opening, closing time
        $opening_times = ! empty( $dokan_store_times[ $today ]['opening_time'] ) ? $dokan_store_times[ $today ]['opening_time'] : [];
        $closing_times = ! empty( $dokan_store_times[ $today ]['closing_time'] ) ? $dokan_store_times[ $today ]['closing_time'] : [];

        if ( empty( $opening_times ) || empty( $closing_times ) ) {
            return false;
        }

        // we are checking for multiple opening/closing times, if not array return from here
        // this will prevent fatal error if user didn't run dokan migrator
        if ( ! array( $opening_times ) ) {
            return false;
        }

        $times_length = count( $opening_times );
        for ( $i = 1; $i < $times_length; $i++ ) {
            // Convert to timestamp
            $opening_time = $current_time->modify( $opening_times[ $i ] );
            $closing_time = $current_time->modify( $closing_times[ $i ] );

            // Check vendor picked time and current time for show store open.
            if ( $opening_time <= $current_time && $closing_time >= $current_time ) {
                return true;
            }
        }

        return $store_open;
    }

    /**
     * Update store time template location for multi slot times.
     *
     * @since 3.5.0
     *
     * @param string $template
     *
     * @return string
     */
    public function update_store_time_template( $template ) {
        $data = [
            'place_end'     => __( 'Closes at', 'dokan' ),
            'add_action'    => __( 'Add hours', 'dokan' ),
            'place_start'   => __( 'Opens at', 'dokan' ),
            'fullDayString' => __( 'Full Day', 'dokan' ),
        ];

        wp_localize_script( 'dokan-pro-store-open-close-time', 'dokanMultipleTime', $data );
        wp_enqueue_style( 'dokan-minitoggle' );
        wp_enqueue_script( 'dokan-minitoggle' );
        wp_enqueue_script( 'dokan-pro-store-open-close-time' );

        // Load store open close action button from here.
        return 'store-times/store-times';
    }

    /**
     * Update store time template arguments for load multiple store times.
     *
     * @since 3.5.0
     *
     * @param array $args
     *
     * @return array
     */
    public function update_store_time_template_args( $args ) {
        $pro_args = [
            'pro'         => true,
            'all_day'     => __( 'Full Day', 'dokan' ),
            'place_end'   => __( 'Closes at', 'dokan' ),
            'add_action'  => __( 'Add hours', 'dokan' ),
            'place_start' => __( 'Opens at', 'dokan' ),
        ];

        return array_merge( $pro_args, $args );
    }

    /**
     * Show stores multiple time settings field here.
     *
     * @since 3.5.0
     *
     * @param string $current_day
     * @param string $store_status
     *
     * @return void
     */
    public function added_store_times( $current_day, $store_status ) {
        $store_info       = dokan_get_store_info( dokan_get_current_user_id() );
        $dokan_store_time = isset( $store_info['dokan_store_time'] ) ? $store_info['dokan_store_time'] : '';

        if (
            empty( $dokan_store_time[ $current_day ] ) ||
            empty( $dokan_store_time[ $current_day ]['opening_time'] ) ||
            empty( $dokan_store_time[ $current_day ]['closing_time'] )
        ) {
            return;
        }

        $times_length = count( (array) $dokan_store_time[ $current_day ]['opening_time'] );

        for ( $index = 1; $index < $times_length; ++$index ) {
            $args = [
                'pro'              => true,
                'index'            => $index,
                'status'           => $store_status,
                'place_end'        => __( 'Closes at', 'dokan' ),
                'add_action'       => __( 'Add hours', 'dokan' ),
                'place_start'      => __( 'Opens at', 'dokan' ),
                'current_day'      => $current_day,
                'dokan_store_time' => $dokan_store_time,
            ];

            // Load multiple store times from here.
            dokan_get_template_part( 'store-times/add-new-time', '', $args );
        }
    }
}
