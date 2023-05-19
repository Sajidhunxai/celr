<?php

namespace WeDevs\DokanPro\Upgrade\Upgraders;

use WeDevs\Dokan\ProductCategory\Categories;
use WeDevs\DokanPro\Abstracts\DokanProUpgrader;
use WeDevs\DokanPro\Upgrade\Upgraders\BackgroundProcesses\V_3_7_4_UpdateSubscriptionVendorCategories;
use WP_User_Query;

class V_3_7_4 extends DokanProUpgrader {

    /**
     * Updates categories for vendor subscription products.
     *
     * @since DOKAN_SINCE
     *
     * @return void
     */
    public static function update_vendor_subscription_products_categories() {
        $args = [
            'type'   => 'product_pack',
            'status' => 'any',
            'return' => 'ids',
            'limit'  => -1,
        ];

        $products       = wc_get_products( $args );
        $dokan_category = new Categories();
        $bg_processor   = new V_3_7_4_UpdateSubscriptionVendorCategories();
        // make sure category data exists
        $dokan_category->get_all_categories();

        foreach ( $products as $product_id ) {
            $categories = get_post_meta( $product_id, '_vendor_allowed_categories', true );

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
            // save rearranged categories to database
            update_post_meta( $product_id, '_vendor_allowed_categories', $rearranged_categories );

            // now update subscribed vendors
            $user_args = [
                'number'     => 5,
                'paged'      => 1,
                'meta_key'   => 'product_package_id', // phpcs:ignore
                'meta_value' =>  $product_id, // phpcs:ignore
                'fields'     => 'ID',
            ];
            $users = [];

            while ( null !== $users ) {
                $users = new WP_User_Query( $user_args );

                if ( ! empty( $users->get_results() ) ) {
                    $bg_processor->push_to_queue(
                        [
                            'task'     => 'update_vendor_subscription_cat',
                            'vendors'  => $users->get_results(),
                        ]
                    );
                } else {
                    $users = null;
                }

                $user_args['paged'] += 1;
            }
        }

        $bg_processor->dispatch_process();
    }

    /**
     * Remove unfiltered_html capability from vendor staff role.
     *
     * @since 3.7.4
     *
     * @return void
     */
    public static function remove_unfiltered_html_capability() {
        // remove cap from vendor_staff role
        $role = get_role( 'vendor_staff' );
        if ( $role ) {
            $role->remove_cap( 'unfiltered_html' );
        }

        // remove cap from individual users
        $users_query = new \WP_User_Query(
            [
                'role' => 'vendor_staff',
            ]
        );

        if ( ! empty( $users_query->get_results() ) ) {
            foreach ( $users_query->get_results() as $staff ) {
                $staff->remove_cap( 'unfiltered_html' );
            }
        }
    }
}
