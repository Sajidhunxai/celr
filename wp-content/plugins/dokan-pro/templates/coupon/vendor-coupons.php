<table class="dokan-table">
    <thead>
        <tr>
            <th><?php esc_html_e( 'Code', 'dokan' ); ?></th>
            <th><?php esc_html_e( 'Coupon type', 'dokan' ); ?></th>
            <th><?php esc_html_e( 'Coupon amount', 'dokan' ); ?></th>
            <th><?php esc_html_e( 'Product IDs', 'dokan' ); ?></th>
            <th><?php esc_html_e( 'Usage / Limit', 'dokan' ); ?></th>
            <th><?php esc_html_e( 'Expiry date', 'dokan' ); ?></th>
        </tr>
    </thead>

    <?php
    foreach ( $coupons->coupons as $key => $coupon ) {
        ?>
            <tr>
                <td class="coupon-code column-primary" data-title="<?php esc_attr_e( 'Code', 'dokan' ); ?>">
                <?php
                $edit_url = wp_nonce_url(
                    add_query_arg(
                        [
                            'post' => $coupon->get_id(),
                            'action' => 'edit',
                            'view' => 'add_coupons',
                        ], dokan_get_navigation_url( 'coupons' )
                    ), '_coupon_nonce', 'coupon_nonce_url'
                );
                ?>
                    <div class="code">
                    <?php if ( current_user_can( 'dokan_edit_coupon' ) ) { ?>
                            <strong><a href="<?php echo esc_url( $edit_url ); ?>"><span><?php echo esc_attr( $coupon->get_code() ); ?></span></a></strong>
                        <?php } else { ?>
                            <strong><a href=""><span><?php echo esc_attr( $coupon->get_code() ); ?></span></a></strong>
                        <?php } ?>
                    </div>

                    <div class="row-actions">
                    <?php
                    $del_url = wp_nonce_url(
                        add_query_arg(
                            [
                                'post' => $coupon->get_id(),
                                'action' => 'delete',
                            ], dokan_get_navigation_url( 'coupons' )
                        ), '_coupon_del_nonce', 'coupon_del_nonce'
                    );
                    ?>

                    <?php if ( current_user_can( 'dokan_edit_coupon' ) ) { ?>
                            <span class="edit"><a href="<?php echo esc_url( $edit_url ); ?>"><?php esc_html_e( 'Edit', 'dokan' ); ?></a> | </span>
                        <?php } ?>

                    <?php if ( current_user_can( 'dokan_delete_coupon' ) ) { ?>
                            <span class="delete"><a  href="<?php echo esc_url( $del_url ); ?>"  onclick="return confirm('<?php esc_attr_e( 'Are you sure want to delete', 'dokan' ); ?>');"><?php esc_html_e( 'delete', 'dokan' ); ?></a></span>
                        <?php } ?>
                    </div>

                    <button type="button" class="toggle-row"></button>
                </td>

                <td data-title="<?php esc_attr_e( 'Coupon type', 'dokan' ); ?>">
                <?php
                $discount_type = $coupon->get_discount_type();
                $types         = dokan_get_coupon_types();

                echo esc_html( $types[ $discount_type ] );
                ?>
                </td>

                <td data-title="<?php esc_attr_e( 'Coupon amount', 'dokan' ); ?>">
                    <?php echo esc_attr( wc_format_localized_price( $coupon->get_amount() ) ); ?>
                </td>

                <td data-title="<?php esc_attr_e( 'Product IDs', 'dokan' ); ?>">
                    <?php
                    $product_ids = ! empty( $coupon->get_product_ids() ) ? $coupon->get_product_ids() : [];

                    if ( count( $product_ids ) > 0 ) {
                        if ( count( $product_ids ) > 12 ) {
                            $product_ids = array_slice( $product_ids, 0, 12 );
                            echo sprintf( '%s... <a href="%s">%s</a>', esc_html( implode( ', ', $product_ids ) ), esc_url( $edit_url ), __( 'See all', 'dokan' ) );
                        } else {
                            echo esc_html( implode( ', ', $product_ids ) );
                        }
                    } else {
                        echo '&ndash;';
                    }
                    ?>
                </td>

                <td data-title="<?php esc_attr_e( 'Usage / Limit', 'dokan' ); ?>">
                    <?php
                    $usage_count = absint( $coupon->get_usage_count() );
                    $usage_limit = esc_html( $coupon->get_usage_limit() );

                    if ( $usage_limit ) {
                        // translators: %1$s: Usage count, %2$s: Usage limit
                        printf( __( '%1$s / %2$s', 'dokan' ), $usage_count, $usage_limit );
                    } else {
                        // translators: %s: Usage count
                        printf( __( '%s / &infin;', 'dokan' ), $usage_count );
                    }

                    do_action( 'dokan_coupon_list_after_usages_limit', $coupon );
                    ?>
                </td>

                <td data-title="<?php esc_attr_e( 'Expiry date', 'dokan' ); ?>">
                    <?php
                    $expiry_date = $coupon->get_date_expires();

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
