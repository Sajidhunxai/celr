<?php

namespace WeDevs\DokanPro\Upgrade\Upgraders;

use WeDevs\DokanPro\Abstracts\DokanProUpgrader;
use WeDevs\DokanPro\Upgrade\Upgraders\BackgroundProcesses\V_3_7_17_RemoveStoreCategories;

class V_3_7_17 extends DokanProUpgrader {

    /**
     * Remove store categories from non-seller users.
     *
     * @since 3.7.17
     *
     * @return void
     */
    public static function remove_store_categories() {
        $i         = 1;
        $processor = new V_3_7_17_RemoveStoreCategories();

        while ( true ) {
            $user_args            = [
                'role__not_in' => [ 'seller', 'administrator' ],
                'number'       => 20,
                'paged'        => $i++,
                'fields'       => 'ID',
            ];
            $user_query           = new \WP_User_Query( $user_args );
            $users_except_sellers = $user_query->get_results();

            if ( empty( $users_except_sellers ) ) {
                break;
            }

            $processor->push_to_queue( $users_except_sellers );
        }

        $processor->dispatch_process();
    }
}
