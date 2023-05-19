<?php

/**
 * Include module templates
 *
 * @since 2.9.8
 *
 * @param string $name
 * @param array  $args
 *
 * @return void
 */
function dokan_spmv_get_template( $name, $args = [] ) {
    dokan_get_template( "$name.php", $args, DOKAN_SPMV_VIEWS, trailingslashit( DOKAN_SPMV_VIEWS ) );
}

/**
 * Get other reseller vendors
 *
 * @since 2.9.8
 *
 * @param int $product_id
 *
 * @return array
 */
function dokan_spmv_get_product_clones( $product ) {
    global $wpdb;

    $clones  = [];
    $product = wc_get_product( $product );

    if ( $product->get_id() ) {
        $product_id      = $product->get_id();
        $has_multivendor = get_post_meta( $product_id, '_has_multi_vendor', true );

        if ( ! empty( $has_multivendor ) ) {
            $clones = $wpdb->get_col( $wpdb->prepare(
                  "select product_id from {$wpdb->prefix}dokan_product_map"
                . " where map_id = %d and product_id != %d",
                $has_multivendor,
                $product_id
            ) );
        }
    }

    return $clones;
}

/**
 * Get product show order options
 *
 * @since 2.9.11
 *
 * @return array
 */
function dokan_spmv_get_show_order_options() {
    return apply_filters( 'dokan_spmv_show_order_options', [
        [
            'name'  => 'show_all',
            'label' => __( 'Show all products', 'dokan' ),
        ],
        [
            'name'  => 'min_price',
            'label' => __( 'Min price', 'dokan' ),
        ],
        [
            'name'  => 'max_price',
            'label' => __( 'Max price', 'dokan' ),
        ],
    ] );
}

/**
 * Update visibilities for a group of cloned products
 *
 * @since 2.9.11
 *
 * @param int $map_id
 *
 * @return array
 */
function dokan_spmv_update_clone_visibilities( $map_id ) {
    global $wpdb;

    $table_name = $wpdb->prefix . 'dokan_product_map';
    $show_order = dokan_get_option( 'show_order', 'dokan_spmv', 'show_all' );

    $product_ids = $wpdb->get_col( $wpdb->prepare(
        "select product_id from {$table_name} where map_id = %d",
        $map_id
    ) );

    $clones = wc_get_products( [
        'post_status' => 'publish',
        'include'    => $product_ids,
        'orderby'     => 'ID',
        'order'       => 'ASC'
    ] );

    $has_diff = false;

    @usort( $clones, function ( $a, $b ) use ( $show_order, &$has_diff ) {
        if ( $a instanceof WC_Product_Variable && $b instanceof WC_Product_Variable ) {
            $min_or_max = ( 'max_price' === $show_order ) ? 'max' : 'min';
            $a_price = $a->get_variation_price( $min_or_max );
            $b_price = $b->get_variation_price( $min_or_max );
        } else {
            $a_price = $a->get_price();
            $b_price = $b->get_price();
        }

        switch ( $show_order ) {
            case 'max_price':
                $diff = (float) $b_price - (float) $a_price;
                break;

            default:
                $diff = (float) $a_price - (float) $b_price;
                break;
        }

        $has_diff = $diff || false;

        $diff = apply_filters( 'dokan_spmv_cloned_product_order', $diff, $a, $b, $show_order );

        if ( $diff < 0 ) { // because the return value must be an integer
            $diff = -1;
        } elseif ( $diff > 0 ) {
            $diff = 1;
        } else {
            $diff = 0;
        }

        return $diff;
    } );

    // if we don't have a diff based on admin settings, then show only first created product
    if ( ! $has_diff && ! empty( $clones ) ) {
        $has_diff = true;
    }

    // If a group of products has no difference, then we should show them all.
    // If there is a difference, then we'll hide them all first by making visibilty 0
    // and set 1 for the first one from sorted array.
    $wpdb->update(
        $table_name,
        [
            'visibility' => $has_diff ? 0 : 1
        ],
        [
            'map_id' => $map_id
        ],
        [
            '%d',
        ],
        [
            '%d',
        ]
    );

    if ( $has_diff ) {
        $clone = $clones[0];

        $wpdb->update(
            $table_name,
            [
                'visibility' => 1,
            ],
            [
                'map_id'     => $map_id,
                'product_id' => $clone->get_id(),
            ],
            [
                '%d',
            ],
            [
                '%d',
                '%d',
            ]
        );
    }

    return $clones;
}

/**
 * We are checking if vendor subscription is active,
 * if true, we are getting the subscription of the vendor
 * and checking if the vendor has remaining product based on active subscription
 *
 * @since 3.3.0
 *
 * @param $user_id
 *
 * @return bool
 */
function dokan_spmv_can_vendor_create_new_product( $user_id ) {
    if ( dokan_pro()->module->is_active( 'product_subscription' ) ) {
        if ( ! \DokanPro\Modules\Subscription\Helper::get_vendor_remaining_products( $user_id ) ) {
            return false;
        }
    }

    return true;
}
