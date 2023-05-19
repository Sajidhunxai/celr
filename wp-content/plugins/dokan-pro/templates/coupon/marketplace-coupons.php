<table class="dokan-table">
    <thead>
        <tr>
            <th><?php esc_html_e( 'Code', 'dokan' ); ?></th>
            <th><?php esc_html_e( 'Coupon type', 'dokan' ); ?></th>
            <th><?php esc_html_e( 'Coupon amount', 'dokan' ); ?></th>
            <th><?php esc_html_e( 'Amount Deduct From', 'dokan' ); ?></th>
            <th><?php esc_html_e( 'Product IDs', 'dokan' ); ?></th>
            <th><?php esc_html_e( 'Usage / Limit', 'dokan' ); ?></th>
            <th><?php esc_html_e( 'Expiry date', 'dokan' ); ?></th>
        </tr>
    </thead>

    <?php
    foreach ( $coupons as $coupon ) {
        $wc_coupon = new \WC_Coupon( $coupon->ID );

        if ( empty( $wc_coupon ) ) {
            continue;
        }

        $enabled_all_vendor  = $wc_coupon->get_meta( 'admin_coupons_enabled_for_vendor' );
        $commissions_type    = $wc_coupon->get_meta( 'coupon_commissions_type' );
        $admin_shared_amount = $wc_coupon->get_meta( 'admin_shared_coupon_amount' );
        $shared_coupon_type  = $wc_coupon->get_meta( 'admin_shared_coupon_type' );
        $get_product_ids     = $wc_coupon->get_product_ids();
        $coupon_product_ids  = array();

        if ( ! empty( $get_product_ids ) ) {
            foreach ( $get_product_ids as $product_id ) {
                $author = get_post_field( 'post_author', $product_id );

                if ( absint( $author ) === dokan_get_current_user_id() ) {
                    $coupon_product_ids[] = $product_id;
                }
            }
        }
        ?>
            <tr>
                <td class="coupon-code column-primary" data-title="<?php esc_attr_e( 'Code', 'dokan' ); ?>">
                    <div class="code">
                        <strong><a href=""><span><?php echo esc_attr( $wc_coupon->get_code() ); ?></span></a></strong>
                    </div>

                    <button type="button" class="toggle-row"></button>
                </td>

                <td data-title="<?php esc_attr_e( 'Coupon type', 'dokan' ); ?>">
                <?php
                $discount_type = $wc_coupon->get_discount_type();
                $types         = dokan_get_coupon_types();

                echo esc_html( $types[ $discount_type ] );
                ?>
                </td>

                <td data-title="<?php esc_attr_e( 'Coupon amount', 'dokan' ); ?>">
                    <?php echo esc_attr( wc_format_localized_price( $wc_coupon->get_amount() ) ); ?>
                </td>

                <td data-title="<?php esc_attr_e( 'Amount Deduct From', 'dokan' ); ?>">
                    <strong><?php echo esc_attr( dokan_get_admin_coupon_commissions_type()[ $commissions_type ] ); ?></strong>
                    <?php if ( 'shared_coupon' === $commissions_type ) : ?>
                        <p>
                            <i>
                            <?php esc_html_e( 'Admin Shared ', 'dokan' ); ?>
                            <?php
                            if ( 'percentage' === $shared_coupon_type ) {
                                echo esc_attr( $admin_shared_amount ) . '%';
                            } else {
                                echo wp_kses_post( wc_price( $admin_shared_amount ) );
                            }
                            ?>
                            </i>
                        </p>
                    <?php endif; ?>
                </td>

                <td data-title="<?php esc_attr_e( 'Product IDs', 'dokan' ); ?>">
                    <?php echo dokan_get_seller_products_ids_by_coupon( $wc_coupon, dokan_get_current_user_id() ); ?>
                </td>

                <td data-title="<?php esc_attr_e( 'Usage / Limit', 'dokan' ); ?>">
                    <?php
                    $usage_count = absint( $wc_coupon->get_usage_count() );
                    $usage_limit = esc_html( $wc_coupon->get_usage_limit() );

                    if ( $usage_limit ) {
                        // translators: %1$s: Usage count, %2$s: Usage limit
                        printf( __( '%1$s / %2$s', 'dokan' ), $usage_count, $usage_limit );
                    } else {
                        // translators: %s: Usage count
                        printf( __( '%s / &infin;', 'dokan' ), $usage_count );
                    }

                    do_action( 'dokan_coupon_list_after_usages_limit', $wc_coupon );
                    ?>
                </td>

                <td data-title="<?php esc_attr_e( 'Expiry date', 'dokan' ); ?>">
                    <?php
                    $expiry_date = $wc_coupon->get_date_expires();

                    if ( $expiry_date ) {
                        echo esc_html( $expiry_date->date_i18n( 'F j, Y' ) );
                    } else {
                        echo '&ndash;';
                    }
                    ?>
                </td>
                <td class="diviader"></td>
            </tr>
            <?php
    }
    ?>
</table>
