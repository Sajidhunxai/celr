<?php

/**
 * Dokan get coupon types
 *
 * @since 3.0.0
 *
 * @return array
 */
function dokan_get_coupon_types() {
    return apply_filters(
        'dokan_get_coupon_types', [
            'percent'       => __( 'Percentage discount', 'dokan' ),
            'fixed_cart'    => __( 'Fixed cart discount', 'dokan' ),
            'fixed_product' => __( 'Fixed product discount', 'dokan' ),
        ]
    );
}

/**
 * Retrieves all the product IDs that can be included while creating a coupon by a seller.
 *
 * @since 3.7.7
 *
 * @param int|string $user_id (Optional) ID of the seller
 *
 * @return array List of the expected product IDs
 */
function dokan_coupon_get_seller_product_ids( $user_id = false ) {
    global $wpdb;

    if ( empty( $user_id ) ) {
        $user_id = dokan_get_current_user_id();
    }

    return $wpdb->get_col(
        $wpdb->prepare(
            "SELECT posts.ID
            FROM $wpdb->posts AS posts
            WHERE posts.post_author = %d
            AND posts.post_type = 'product'
            AND posts.post_status IN ( 'publish', 'draft', 'pending' )
            ORDER BY posts.post_date DESC",
            $user_id
        )
    );
}

/**
 * Check a order have admin coupons for vendors
 *
 * @since 3.4.0
 *
 * @param WC_Order $order
 * @param Int      $vendor_id
 * @param Int      $product_id
 *
 * @return boolean
 */
function dokan_is_admin_coupon_used_for_vendors( $order, $vendor_id, $product_id = 0 ) {
    if ( ! $order || ! $vendor_id ) {
        return false;
    }

    $get_current_coupon = $order->get_items( 'coupon' );

    if ( empty( $get_current_coupon ) ) {
        return false;
    }

    foreach ( $get_current_coupon as $item_id => $coupon ) {
        $coupon_meta = current( $coupon->get_meta_data() );

        if ( ! isset( $coupon_meta->get_data()['value'] ) ) {
            continue;
        }

        $coupon_meta = dokan_get_coupon_metadata_from_order( $coupon_meta->get_data()['value'] );

        if ( ! isset( $coupon_meta['coupon_commissions_type'] ) ) {
            continue;
        }

        if ( 'default' !== $coupon_meta['coupon_commissions_type'] && dokan_pro()->coupon->is_admin_coupon_valid( $coupon, [ $vendor_id ], [ $product_id ], $coupon_meta ) ) {
            return true;
            break;
        }
    }

    return false;
}

/**
 * Dokan get prepare coupons meta data
 *
 * @since 3.4.0
 *
 * @param array $coupon_meta
 *
 * @return array $coupon_meta_data
 */
function dokan_get_coupon_metadata_from_order( $coupon_meta ) {
    if ( empty( $coupon_meta ) ) {
        return;
    }

    $coupon_meta_data = [
        'coupon_id'            => isset( $coupon_meta['id'] ) ? $coupon_meta['id'] : 0,
        'code'                 => isset( $coupon_meta['code'] ) ? $coupon_meta['code'] : '',
        'amount'               => isset( $coupon_meta['amount'] ) ? $coupon_meta['amount'] : 0,
        'discount_type'        => isset( $coupon_meta['discount_type'] ) ? $coupon_meta['discount_type'] : 0,
        'product_ids'          => isset( $coupon_meta['product_ids'] ) ? $coupon_meta['product_ids'] : [],
        'excluded_product_ids' => isset( $coupon_meta['excluded_product_ids'] ) ? $coupon_meta['excluded_product_ids'] : [],
    ];

    foreach ( $coupon_meta['meta_data'] as $meta_item ) {
        $coupon_meta_item = $meta_item->get_data();

        if (
            'coupons_vendors_ids' === $coupon_meta_item['key'] ||
            'coupons_exclude_vendors_ids' === $coupon_meta_item['key']
        ) {
            $coupon_meta_data[ $coupon_meta_item['key'] ] = ! empty( $coupon_meta_item['value'] ) ? array_map( 'intval', explode( ',', $coupon_meta_item['value'] ) ) : [];
        } else {
            $coupon_meta_data[ $coupon_meta_item['key'] ] = $coupon_meta_item['value'];
        }
    }

    return $coupon_meta_data;
}

/**
 * Dokan get admin coupons meta data
 *
 * @since 3.4.0
 *
 * @param WC_Coupon $coupon
 *
 * @return array $coupon_meta
 */
function dokan_get_admin_coupon_meta( $coupon ) {
    if ( empty( $coupon ) ) {
        return;
    }

    $vendors_ids     = $coupon->get_meta( 'coupons_vendors_ids' );
    $vendors_ids     = ! empty( $vendors_ids ) ? array_map( 'intval', explode( ',', $vendors_ids ) ) : [];
    $exclude_vendors = $coupon->get_meta( 'coupons_exclude_vendors_ids' );
    $exclude_vendors = ! empty( $exclude_vendors ) ? array_map( 'intval', explode( ',', $exclude_vendors ) ) : [];

    return [
        'coupon_id'                        => $coupon->get_id(),
        'admin_coupons_enabled_for_vendor' => $coupon->get_meta( 'admin_coupons_enabled_for_vendor' ),
        'coupon_commissions_type'          => $coupon->get_meta( 'coupon_commissions_type' ),
        'coupons_vendors_ids'              => $vendors_ids,
        'coupons_exclude_vendors_ids'      => $exclude_vendors,
        'admin_shared_coupon_type'         => $coupon->get_meta( 'admin_shared_coupon_type' ),
        'admin_shared_coupon_amount'       => $coupon->get_meta( 'admin_shared_coupon_amount' ),
        'product_ids'                      => $coupon->get_product_ids(),
        'excluded_product_ids'             => $coupon->get_excluded_product_ids(),
    ];
}

/**
 * Check the coupon created by admin for vendor
 *
 * @since 3.4.0
 *
 * @param array $coupon
 *
 * @return bool
 */
function dokan_is_coupon_created_by_admin_for_vendor( $coupon ) {
    if ( empty( $coupon ) ) {
        return;
    }

    return empty( $coupon->get_meta( 'admin_coupons_enabled_for_vendor' ) ) ? false : true;
}

/**
 * Check admin created vendor coupon by coupon meta data
 *
 * @since 3.4.0
 *
 * @param array $coupon_meta
 * @param int   $vendor_id
 *
 * @return bool
 */
function dokan_is_admin_created_vendor_coupon_by_meta( $coupon_meta, $vendor_id ) {
    $enabled_all_vendor = isset( $coupon_meta['admin_coupons_enabled_for_vendor'] ) ? $coupon_meta['admin_coupons_enabled_for_vendor'] : '';
    $vendors_ids        = isset( $coupon_meta['coupons_vendors_ids'] ) ? $coupon_meta['coupons_vendors_ids'] : [];
    $exclude_vendors    = isset( $coupon_meta['coupons_exclude_vendors_ids'] ) ? $coupon_meta['coupons_exclude_vendors_ids'] : [];

    if ( 'yes' === $enabled_all_vendor && empty( $exclude_vendors ) ) {
        return true;
    }

    if ( 'yes' === $enabled_all_vendor && ! empty( $exclude_vendors ) && ! in_array( (int) $vendor_id, $exclude_vendors, true ) ) {
        return true;
    }

    if ( 'no' === $enabled_all_vendor && ! empty( $vendors_ids ) && in_array( (int) $vendor_id, $vendors_ids, true ) ) {
        return true;
    }

    return false;
}

/**
 * Dokan admin coupon commission types
 *
 * @since 3.4.0
 *
 * @return array
 */
function dokan_get_admin_coupon_commissions_type() {
    return apply_filters(
        'dokan_get_admin_coupon_commissions_type', [
            'default'       => __( 'Default', 'dokan' ),
            'from_vendor'   => __( 'Vendor Earning', 'dokan' ),
            'from_admin'    => __( 'Admin Commissions', 'dokan' ),
            'shared_coupon' => __( 'Shared', 'dokan' ),
        ]
    );
}

/**
 * Dokan get seller products ids by coupon
 *
 * @since 3.4.0
 *
 * @param \WC_Coupon
 * @param int $seller_id
 *
 * @return string
 */
function dokan_get_seller_products_ids_by_coupon( $coupon, $seller_id ) {
    if ( empty( $coupon ) || empty( $seller_id ) ) {
        return;
    }

    $coupon_data        = dokan_get_admin_coupon_meta( $coupon );
    $get_product_ids    = $coupon_data['product_ids'];
    $enabled_all_vendor = $coupon_data['admin_coupons_enabled_for_vendor'];
    $vendors_ids        = $coupon_data['coupons_vendors_ids'];
    $exclude_vendors    = $coupon_data['coupons_exclude_vendors_ids'];
    $coupon_product_ids = array();

    if ( ! empty( $get_product_ids ) ) {
        foreach ( $get_product_ids as $product_id ) {
            $author = get_post_field( 'post_author', $product_id );

            if ( absint( $author ) === $seller_id ) {
                $coupon_product_ids[] = $product_id;
            }
        }
    }

    if ( count( $coupon_product_ids ) > 0 ) {
        if ( count( $coupon_product_ids ) > 15 ) {
            $product_ids = array_slice( $coupon_product_ids, 0, 15 );
            return sprintf( '%s... <a href="#">%s</a>', esc_html( implode( ', ', $product_ids ) ), __( 'have more', 'dokan' ) );
        } else {
            return esc_html( implode( ', ', $coupon_product_ids ) );
        }
    } elseif ( 'yes' === $enabled_all_vendor && ! in_array( $seller_id, $exclude_vendors, true ) ) {
        return __( 'All', 'dokan' );
    } elseif ( 'no' === $enabled_all_vendor && in_array( $seller_id, $vendors_ids, true ) ) {
        return __( 'All', 'dokan' );
    } else {
        return '&ndash;';
    }
}
