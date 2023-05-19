<?php

namespace WeDevs\DokanPro\Upgrade\Upgraders\BackgroundProcesses;

use WeDevs\Dokan\Abstracts\DokanBackgroundProcesses;
use WeDevs\Dokan\ProductCategory\Categories;

class V_3_7_4_UpdateSubscriptionVendorCategories extends DokanBackgroundProcesses {

    /**
     * Update vendor subscription category meta data.
     *
     * @since 3.7.4
     *
     * @param array $data
     *
     * @return bool
     */
    public function task( $data ) {
        // check task type is update_subscription_meta
        if ( ! isset( $data['task'] ) || $data['task'] !== 'update_vendor_subscription_cat' ) {
            return false;
        }

        // check product id exist
        if ( empty( $data['vendors'] ) ) {
            return false;
        }

        $vendors        = $data['vendors'];
        $dokan_category = new Categories();
        $dokan_category->get_all_categories();

        foreach ( $vendors as $vendor_id ) {
            $categories = get_user_meta( $vendor_id, 'vendor_allowed_categories', true );
            if ( ! is_array( $categories ) || empty( $categories ) ) {
                continue;
            }
            $rearranged_categories = [];
            foreach ( $categories as $cat_id ) {
                // get topmost parent id of an category
                $parent_id = $dokan_category->get_topmost_parent( $cat_id );
                if ( ! in_array( $parent_id, $rearranged_categories, true ) ) {
                    $rearranged_categories[] = $parent_id;
                }
            }

            update_user_meta( $vendor_id, 'vendor_allowed_categories', $rearranged_categories );
        }
    }
}
