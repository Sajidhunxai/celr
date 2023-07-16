<?php

// Get the location for a product
function get_product_location( $product_id ) {
    // Get the product object
    $product = wc_get_product( $product_id );

    // Check if the product has attributes
    if ( $product->is_type( 'variable' ) ) {
        // For variable products, retrieve the attribute value from the selected variation
        $variation_id = $product->get_variation_default_attributes();
        $variation = wc_get_product( reset( $variation_id ) );

        if ( $variation ) {
            $location = $variation->get_attribute( 'pa_location' );
        }
    } else {
        // For simple products, retrieve the attribute value directly
        $location = $product->get_attribute( 'pa_location' );
    }

    return $location;
}

function calculate_location_fee( $fee, $product_id ) {
    $product_location = get_product_location( $product_id );

    if ( $product_location === $fee['location'] ) {
        return $fee['price'];
    }

    return 0;
}
function apply_location_fee( $cart ) {
    if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
        return;
    }

    if ( did_action( 'woocommerce_before_calculate_totals' ) >= 2 ) {
        return;
    }

    $custom_fees = get_custom_fees();

    foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
        $product_id = $cart_item['product_id'];
        $product_location = get_product_location( $product_id );

        foreach ( $custom_fees as $fee ) {
            if ( $product_location === $fee['location'] ) {
                $amount = $fee['price'];
                $cart->add_fee( 'Location Fee', $amount );
                break; // Apply only one fee per product
            }
        }
    }
}
add_action( 'woocommerce_cart_calculate_fees', 'apply_location_fee' );

// Retrieve fees from the WordPress admin
function get_custom_fees() {
    $custom_fees = array();
    $warehouses = get_option( 'custom_warehouses', array() );

    foreach ( $warehouses as $location_name => $warehouse_fee ) {
        $custom_fees[] = array(
            'id'        => sanitize_title( $location_name ),
            'name'      => $location_name,
            'location'  => $location_name,
            'price'     => $warehouse_fee
        );
    }

    return $custom_fees;
}
