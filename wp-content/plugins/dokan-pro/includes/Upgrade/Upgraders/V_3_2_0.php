<?php

namespace WeDevs\DokanPro\Upgrade\Upgraders;

use WeDevs\DokanPro\Abstracts\DokanProUpgrader;
use WeDevs\DokanPro\Upgrade\Upgraders\BackgroundProcesses\V_3_2_0_UpdateSubscriptionMeta;
use WC_Product_Query;

class V_3_2_0 extends DokanProUpgrader {

    /**
     * Update the missing shipping zone locations table data
     *
     * @since 3.2.0
     *
     * @return void
     */
    public static function update_dokan_subscription_meta() {
        $processor = new V_3_2_0_UpdateSubscriptionMeta();

        // get all Dokan Subscription Products
        $query = new WC_Product_Query(
            [
				'type' => 'product_pack',
				'return' => 'ids',
				'limit' => -1,
            ]
        );

        $products = $query->get_products();

        foreach ( $products as $product_id ) {
            $processor->push_to_queue(
                [
                    'task' => 'update_subscription_meta',
                    'product_id'   => $product_id,
                ]
            );
        }

        $processor->dispatch_process();
    }
}
