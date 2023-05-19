<?php

namespace WeDevs\DokanPro\Upgrade\Upgraders;

use WeDevs\DokanPro\Abstracts\DokanProUpgrader;
use WeDevs\DokanPro\Upgrade\Upgraders\BackgroundProcesses\V_3_0_7_ShippingLocations;

class V_3_0_7 extends DokanProUpgrader {

    /**
     * Update the missing shipping zone locations table data
     *
     * @since 3.0.7
     *
     * @return void
     */
    public static function update_shipping_zone_locations_table() {
        $processor = new V_3_0_7_ShippingLocations();

        $page = 1;

        $processor->push_to_queue( $page )->dispatch_process();
    }
}
