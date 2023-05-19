<?php

namespace WeDevs\DokanPro\Upgrade\Upgraders\BackgroundProcesses;

use WeDevs\Dokan\Abstracts\DokanBackgroundProcesses;

class V_3_0_7_ShippingLocations extends DokanBackgroundProcesses {

    /**
     * Sync the missing shipping locations data
     *
     * @since 3.0.7
     *
     * @param int $page
     *
     * @return int|bool
     */
    public function task( $page ) {
        global $wpdb;

        $limit  = 20;
        $offset = ( $page - 1 ) * $limit;

        $shipping_methods = $wpdb->get_results(
            $wpdb->prepare(
                "SELECT
                    methods.seller_id,
                    methods.zone_id,
                    wclocation.location_code,
                    wclocation.location_type
                FROM
                    {$wpdb->prefix}dokan_shipping_zone_methods as methods
                    LEFT JOIN {$wpdb->prefix}dokan_shipping_zone_locations as locations on locations.zone_id = methods.zone_id
                    AND locations.seller_id = methods.seller_id
                    LEFT JOIN {$wpdb->prefix}woocommerce_shipping_zone_locations as wclocation on wclocation.zone_id = methods.zone_id
                WHERE
                    methods.settings IS NOT NULL
                    AND locations.seller_id IS NULL
                    AND locations.zone_id IS NULL
                LIMIT
                    %d,
                    %d",
                $offset,
                $limit
            ),
            ARRAY_A
        );

        // If we don't get any $shipping_methods for $page
        // number, then terminate the background process.
        if ( empty( $shipping_methods ) ) {
            return false;
        }

        foreach ( $shipping_methods as $method ) {
            $wpdb->insert(
                $wpdb->prefix . 'dokan_shipping_zone_locations',
                $method,
                [ '%d', '%d', '%s', '%s' ]
            );
        }

        return ++$page;
    }
}
