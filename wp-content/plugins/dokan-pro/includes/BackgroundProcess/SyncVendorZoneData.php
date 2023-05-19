<?php
namespace WeDevs\DokanPro\BackgroundProcess;

defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'WC_Background_Process', false ) ) {
    include_once dirname( WC_PLUGIN_FILE ) . 'includes/abstracts/class-wc-background-process.php';
}

use WC_Background_Process;
use WeDevs\DokanPro\Shipping\ShippingZone;

/**
 * Class SyncVendorZoneData
 * @package WeDevs\DokanPro\BackgroundProcesses
 * @since 3.2.2
 */
class SyncVendorZoneData extends WC_Background_Process {

    /**
     * Initiate new background process.
     */
    public function __construct() {
        // Uses unique prefix per blog so each blog has separate queue.
        $this->prefix = 'wp_' . get_current_blog_id();
        $this->action = 'dokan_pro_sync_vendor_zone_data';

        parent::__construct();
    }

    /**
     * Dispatch updater.
     *
     * Updater will still run via cron job if this fails for any reason.
     */
    public function dispatch() {
        $dispatched = parent::dispatch();

        if ( is_wp_error( $dispatched ) ) {
            dokan_log(
                sprintf( 'Unable to dispatch Dokan Vendor Zone Data Sync: %s', $dispatched->get_error_message() ),
                'error'
            );
        }
    }

    /**
     * Handle cron healthcheck
     *
     * Restart the background process if not already running
     * and data exists in the queue.
     */
    public function handle_cron_healthcheck() {
        if ( $this->is_process_running() ) {
            // Background process already running.
            return;
        }

        if ( $this->is_queue_empty() ) {
            // No data to process.
            $this->clear_scheduled_event();
            return;
        }

        $this->handle();
    }

    /**
     * Schedule fallback event.
     */
    protected function schedule_event() {
        if ( ! wp_next_scheduled( $this->cron_hook_identifier ) ) {
            wp_schedule_event( time() + 10, $this->cron_interval_identifier, $this->cron_hook_identifier );
        }
    }

    /**
     * Task
     *
     * Override this method to perform any actions required on each
     * queue item. Return the modified item for further processing
     * in the next pass through. Or, return false to remove the
     * item from the queue.
     *
     * @param object $vendor Update callback function.
     * @return string|bool
     */
    protected function task( $args ) {
        $seller_id      = isset( $args['seller_id'] ) ? absint( $args['seller_id'] ) : null;
        $zone           = isset( $args['zone'] ) ? $args['zone'] : null;
        $zone_locations = isset( $args['zone_locations'] ) ? $args['zone_locations'] : [];

        if ( ! $zone instanceof \WC_Shipping_Zone ) {
            dokan_log( sprintf( 'Invalid Vendor Zone Data Provided: %s', print_r( $args, true ) ) );
            return false;
        }

        if ( empty( $zone_locations ) ) {
            return false;
        }

        global $wpdb;
        $zone_id   = $zone->get_id();
        $location  = array();

        // check vendor added zone data
        $table_name = "{$wpdb->prefix}dokan_shipping_zone_locations";
        $vendor_locations = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT 1 FROM {$table_name} WHERE zone_id=%d AND seller_id=%d",
                array( $zone_id, $seller_id )
            )
        );

        if ( null === $vendor_locations ) {
            // no zone data is added via vendors, so we don't need to process further
            return false;
        }

        foreach ( $zone_locations as $zone_location ) {
            if ( 'continent' === $zone_location->type ) {
                $continent_array = array();
                $continent_array[] = array(
                    'code' => $zone_location->code,
                    'type' => 'continent',
                );

                $location = array_merge( $location, $continent_array );
            }

            if ( 'country' === $zone_location->type ) {
                $country_array = array();
                $country_array[] = array(
                    'code' => $zone_location->code,
                    'type' => 'country',
                );

                $location = array_merge( $location, $country_array );
            }

            if ( 'state' === $zone_location->type ) {
                $state_array = array();
                $state_array[] = array(
                    'code' => $zone_location->code,
                    'type' => 'state',
                );

                $location = array_merge( $location, $state_array );
            }

            if ( $zone_location->type === 'postcode' ) {
                $postcodes      = explode( ',', $zone_location->code );
                $postcode_array = array();

                foreach ( $postcodes as $postcode ) {
                    if ( false !== strpos( $postcode, '...' ) ) {
                        $postcode = implode( '...', array_map( 'trim', explode( '...', $postcode ) ) );
                    }

                    $postcode_array[] = array(
                        'code' => trim( $postcode ),
                        'type' => 'postcode',
                    );
                }

                $location = array_merge( $location, $postcode_array );
            }
        }

        // update shipping data
        ShippingZone::save_location( $location, $zone_id, $seller_id );

        return false;
    }

    /**
     * Complete
     *
     * Override if applicable, but ensure that the below actions are
     * performed, or, call parent::complete().
     */
    protected function complete() {
        dokan_log( 'Vendor Zone Data Sync completed', 'info' );
        parent::complete();
    }
}
