<?php

namespace WeDevs\DokanPro\Upgrade\Upgraders\BackgroundProcesses;

use WeDevs\Dokan\Abstracts\DokanBackgroundProcesses;

/**
 * Dokan remove store categories upgrader class.
 *
 * @since 3.7.8
 */
class V_3_7_17_RemoveStoreCategories extends DokanBackgroundProcesses {

    /**
     * Action
     *
     * Override this action in your processor class
     *
     * @since 3.7.17
     *
     * @var string
     */
    protected $action = 'dokan_pro_bg_action_3_7_17';

    /**
     * Remove store categories.
     *
     * @param array $user_ids
     *
     * @since 3.7.17
     *
     * @return bool
     */
    public function task( $user_ids ) {
        if ( empty( $user_ids ) ) {
            return false;
        }

        $category_args = [
            'taxonomy' => 'store_category',
            'fields'   => 'ids'
        ];

        $term_query         = new \WP_Term_Query( $category_args );
        $store_category_ids = $term_query->get_terms();

        foreach ( $user_ids as $user_id ) {
            // Remove store categories from non-vendor users.
            wp_remove_object_terms( $user_id, $store_category_ids, 'store_category' );
        }

        return false;
    }
}
