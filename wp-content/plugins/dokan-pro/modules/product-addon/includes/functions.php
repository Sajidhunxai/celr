<?php

function dokan_pa_convert_type_name( $type = '' ) {
    switch ( $type ) {
        case 'checkboxes':
            $name = __( 'Checkbox', 'dokan' );
            break;
        case 'custom_price':
            $name = __( 'Price', 'dokan' );
            break;
        case 'input_multiplier':
            $name = __( 'Quantity', 'dokan' );
            break;
        case 'custom_text':
            $name = __( 'Short Text', 'dokan' );
            break;
        case 'custom_textarea':
            $name = __( 'Long Text', 'dokan' );
            break;
        case 'file_upload':
            $name = __( 'File Upload', 'dokan' );
            break;
        case 'select':
            $name = __( 'Dropdown', 'dokan' );
            break;
        case 'multiple_choice':
        default:
            $name = __( 'Multiple Choice', 'dokan' );
            break;
    }

    return $name;
}

function dokan_pa_get_posted_product_addons( $postdata = [] ) {
    $product_addons = [];

    if ( empty( $postdata ) ) {
        return $product_addons;
    }

    if ( isset( $postdata['product_addon_name'] ) ) {
        $addon_name               = $postdata['product_addon_name'];
        $addon_title_format       = $postdata['product_addon_title_format'];
        $addon_description_enable = isset( $postdata['product_addon_description_enable'] ) ? $postdata['product_addon_description_enable'] : [];
        $addon_description        = $postdata['product_addon_description'];
        $addon_type               = $postdata['product_addon_type'];
        $addon_display            = $postdata['product_addon_display'];
        $addon_position           = $postdata['product_addon_position'];
        $addon_required           = isset( $postdata['product_addon_required'] ) ? $postdata['product_addon_required'] : [];
        $addon_option_label       = $postdata['product_addon_option_label'];
        $addon_option_price       = $postdata['product_addon_option_price'];
        $addon_option_price_type  = $postdata['product_addon_option_price_type'];
        $addon_option_image       = $postdata['product_addon_option_image'];
        $addon_restrictions       = isset( $postdata['product_addon_restrictions'] ) ? $postdata['product_addon_restrictions'] : [];
        $addon_restrictions_type  = $postdata['product_addon_restrictions_type'];
        $addon_adjust_price       = isset( $postdata['product_addon_adjust_price'] ) ? $postdata['product_addon_adjust_price'] : [];
        $addon_price_type         = $postdata['product_addon_price_type'];
        $addon_price              = $postdata['product_addon_price'];
        $addon_min                = $postdata['product_addon_min'];
        $addon_max                = $postdata['product_addon_max'];
        $total_addon_name         = count( $addon_name );

        for ( $i = 0; $i < $total_addon_name; $i++ ) {
            if ( ! isset( $addon_name[ $i ] ) || ( '' === $addon_name[ $i ] ) ) {
                continue;
            }

            $addon_options = [];

            if ( isset( $addon_option_label[ $i ] ) ) {
                $option_label       = $addon_option_label[ $i ];
                $option_price       = $addon_option_price[ $i ];
                $option_price_type  = $addon_option_price_type[ $i ];
                $option_image       = $addon_option_image[ $i ];
                $total_option_label = count( $option_label );

                for ( $ii = 0; $ii < $total_option_label; $ii++ ) {
                    $label      = sanitize_text_field( stripslashes( $option_label[ $ii ] ) );
                    $price      = wc_format_decimal( sanitize_text_field( stripslashes( $option_price[ $ii ] ) ) );
                    $image      = sanitize_text_field( stripslashes( $option_image[ $ii ] ) );
                    $price_type = sanitize_text_field( stripslashes( $option_price_type[ $ii ] ) );

                    if ( ! empty( $label ) && ! empty( $price_type ) ) {
                        $addon_options[] = array(
                            'label'      => $label,
                            'price'      => $price,
                            'image'      => $image,
                            'price_type' => $price_type,
                        );
                    }
                }
            }

            $data                       = [];
            $data['name']               = sanitize_text_field( stripslashes( $addon_name[ $i ] ) );
            $data['title_format']       = sanitize_text_field( stripslashes( $addon_title_format[ $i ] ) );
            $data['description_enable'] = isset( $addon_description_enable[ $i ] ) ? 1 : 0;
            $data['description']        = wp_kses_post( stripslashes( $addon_description[ $i ] ) );
            $data['type']               = sanitize_text_field( stripslashes( $addon_type[ $i ] ) );
            $data['display']            = sanitize_text_field( stripslashes( $addon_display[ $i ] ) );
            $data['position']           = absint( $addon_position[ $i ] );
            $data['required']           = isset( $addon_required[ $i ] ) ? 1 : 0;
            $data['restrictions']       = isset( $addon_restrictions[ $i ] ) ? 1 : 0;
            $data['restrictions_type']  = sanitize_text_field( stripslashes( $addon_restrictions_type[ $i ] ) );
            $data['adjust_price']       = isset( $addon_adjust_price[ $i ] ) ? 1 : 0;
            $data['price_type']         = sanitize_text_field( stripslashes( $addon_price_type[ $i ] ) );
            $data['price']              = wc_format_decimal( sanitize_text_field( stripslashes( $addon_price[ $i ] ) ) );
            $data['min']                = (float) sanitize_text_field( stripslashes( $addon_min[ $i ] ) );
            $data['max']                = (float) sanitize_text_field( stripslashes( $addon_max[ $i ] ) );

            if ( ! empty( $addon_options ) ) {
                $data['options'] = $addon_options;
            }

            // Add to array.
            $product_addons[] = apply_filters( 'woocommerce_product_addons_save_data', $data, $i );
        }
    }

    if ( ! empty( $postdata['import_product_addon'] ) ) {
        $import_addons = maybe_unserialize( maybe_unserialize( stripslashes( trim( $postdata['import_product_addon'] ) ) ) );

        if ( is_array( $import_addons ) && count( $import_addons ) > 0 ) {
            $valid = true;

            foreach ( $import_addons as $addon ) {
                if ( ! isset( $addon['name'] ) || ! $addon['name'] ) {
                    $valid = false;
                }
                if ( ! isset( $addon['description'] ) ) {
                    $valid = false;
                }
                if ( ! isset( $addon['type'] ) ) {
                    $valid = false;
                }
                if ( ! isset( $addon['position'] ) ) {
                    $valid = false;
                }
                if ( ! isset( $addon['required'] ) ) {
                    $valid = false;
                }
            }

            if ( $valid ) {
                $product_addons = array_merge( $product_addons, $import_addons );
            }
        }
    }

    uasort( $product_addons, 'dokan_pa_addons_cmp' );

    return $product_addons;
}

function dokan_pa_addons_cmp( $a, $b ) {
    if ( $a['position'] === $b['position'] ) {
        return 0;
    }

    return ( $a['position'] < $b['position'] ) ? -1 : 1;
}

function dokan_get_vendor_staff( $vendor_id = null ) {
    $author_in    = [];
    $current_user = empty( $vendor_id ) ? get_current_user_id() : absint( $vendor_id );

    array_push( $author_in, $current_user );

    $vendor    = get_user_meta( $current_user, '_vendor_id', true );
    $author_in = array_merge( $author_in, get_staff_ids( $vendor ) );
    $author_in = array_merge( $author_in, get_staff_ids( $current_user ) );

    if ( ! empty( $staffs ) ) {
        foreach ( $staffs as $staff ) {
            array_push( $author_in, $staff->ID );
        }
    }

    if ( ! empty( $other_staff ) ) {
        foreach ( $other_staff as $staff ) {
            array_push( $author_in, $staff->ID );
        }
    }

    if ( ! empty( $vendor ) ) {
        array_push( $author_in, get_user_by( 'ID', $vendor )->ID );
    }

    return $author_in;
}

function get_staff_ids( $user_id ) {
    $ids    = [];
    $staffs = get_users(
        [
			'meta_key'   => '_vendor_id', // phpcs:ignore
			'meta_value' => $user_id, // phpcs:ignore
		]
    );

    if ( ! empty( $staffs ) ) {
        foreach ( $staffs as $staff ) {
            array_push( $ids, $staff->ID );
        }
    }

    return $ids;
}

function dokan_pa_view_addon_for_vendor_staff_vendor( $query ) {
    $query->set( 'author__in', dokan_get_vendor_staff() );
}
