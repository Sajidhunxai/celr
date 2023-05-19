<?php
/**
 *  Dashboard Coupon listing template
 *
 *  @since 2.4
 *  @since 3.4.0 added support for marketplace coupon tab
 */
?>

<div class="dokan-report-wrap">
    <ul class="dokan_tabs">
        <li class="<?php echo ! $marketplace_tab ? 'active' : ''; ?>">
            <a href="<?php echo esc_url( add_query_arg( array( 'coupons_type' => 'vendor_coupons' ), $link ) ); ?>"><?php esc_html_e( 'My Coupons', 'dokan' ); ?></a>
        </li>
        <li class="<?php echo $marketplace_tab ? 'active' : ''; ?>">
            <a href="<?php echo esc_url( add_query_arg( array( 'coupons_type' => 'marketplace_coupons' ), $link ) ); ?>"><?php esc_html_e( 'Marketplace Coupons', 'dokan' ); ?></a>
        </li>
    </ul>

    <div id="dokan_tabs_container">
        <?php if ( ! $marketplace_tab ) : ?>
            <div class="tab-pane active" id="vendor-own-coupon">
                <?php
                if ( ! empty( $vendor_coupons->coupons ) ) {
                    dokan_get_template_part(
                        'coupon/vendor-coupons', '', array(
                            'pro'     => true,
                            'coupons' => $vendor_coupons,
                        )
                    );
                } else {
                    dokan_get_template_part(
                        'coupon/no-coupon', '',
                        [
                            'pro'     => true,
                            'message' => __( 'No coupons found!', 'dokan' ),
                        ]
                    );
                }
                ?>
            </div>
        <?php endif ?>

        <?php if ( $marketplace_tab ) : ?>
            <div class="tab-pane" id="marketplace-coupon">
                <?php
                if ( ! empty( $marketplace_coupons ) ) {
                    dokan_get_template_part(
                        'coupon/marketplace-coupons', '', array(
                            'pro'     => true,
                            'coupons' => $marketplace_coupons,
                        )
                    );
                } else {
                    dokan_get_template_part(
                        'coupon/no-coupon', '',
                        [
                            'pro'     => true,
                            'message' => __( 'No coupons found!', 'dokan' ),
                        ]
                    );
                }
                ?>
            </div>
        <?php endif ?>
    </div>
</div>

<?php
if ( ! $marketplace_tab ) {
    $pagenum      = isset( $_GET['pagenum'] ) ? absint( $_GET['pagenum'] ) : 1;
    $num_of_pages = ceil( $vendor_coupons->total / $vendor_coupons->per_page );
    $base_url     = dokan_get_navigation_url( 'coupons' );

    $page_links = paginate_links(
        [
            'base'      => $base_url . '%_%',
            'format'    => '?pagenum=%#%',
            'add_args'  => false,
            'prev_text' => __( '&laquo;', 'dokan' ),
            'next_text' => __( '&raquo;', 'dokan' ),
            'total'     => $num_of_pages,
            'current'   => $pagenum,
            'type'      => 'array',
        ]
    );

    if ( $page_links ) {
        echo "<ul class='pagination'>\n\t<li>";
        echo join( "</li>\n\t<li>", $page_links );
        echo "</li>\n</ul>\n";
        echo '</div>';
    }
}
?>
