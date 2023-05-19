<?php

namespace WeDevs\DokanPro\Upgrade\Upgraders;

use WeDevs\DokanPro\Abstracts\DokanProUpgrader;
use WeDevs\DokanPro\Upgrade\Upgraders\BackgroundProcesses\V_3_7_17_RemoveStoreCategories;

class V_3_7_21 extends DokanProUpgrader {

    /**
     * Remove store categories from deleted users.
     *
     * @since 3.7.21
     *
     * @return void
     */
    public static function remove_store_categories() {
        global $wpdb;

        $processor = new V_3_7_17_RemoveStoreCategories();
        $limit     = 20;
        $offset    = 0;

        while ( true ) {
            $user_ids = $wpdb->get_col(
                $wpdb->prepare(
                    "SELECT
                            tr.object_id
                        FROM
                            {$wpdb->prefix}terms AS t
                            LEFT JOIN {$wpdb->prefix}term_taxonomy AS tt ON t.term_id = tt.term_taxonomy_id
                            LEFT JOIN {$wpdb->prefix}term_relationships AS tr ON t.term_id = tr.term_taxonomy_id
                            LEFT JOIN {$wpdb->prefix}users AS u ON tr.object_id = u.ID
                        WHERE
                            tt.taxonomy = 'store_category'
                            AND u.ID IS NULL
                        LIMIT %d OFFSET %d",
                    $limit,
                    $offset
                )
            );

            if ( empty( $user_ids ) ) {
                break;
            }

            $processor->push_to_queue( $user_ids );
            $offset += 20;
        }

        $processor->dispatch_process();
    }

    /**
     * Add missing store name meta key for admin users.
     *
     * @since 3.7.21
     *
     * @return void
     */
    public static function add_store_name_metakey() {
        // get admin only users via WP_User_Query
        $args = [
            'role__in'    => [ 'administrator', 'shop_manager' ],
            'fields'  => 'ID',
        ];

        $users = new \WP_User_Query( $args );

        if ( ! empty( $users->get_results() ) ) {
            foreach ( $users->get_results() as $user_id ) {
                $meta = get_user_meta( $user_id, 'dokan_store_name', true );
                if ( ! empty( $meta ) ) {
                    continue;
                }

                $user = get_user_by( 'id', $user_id );
                update_user_meta( $user_id, 'dokan_store_name', $user->display_name );
            }
        }
    }
}
