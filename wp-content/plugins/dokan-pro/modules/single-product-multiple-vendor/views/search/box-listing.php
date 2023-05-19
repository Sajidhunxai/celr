<?php
/**
 * Search Box template.
 * this will be displayed before add new product form.
 *
 * @sience 3.5.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$new_product_url = add_query_arg(
    [
        '_dokan_add_product_nonce' => wp_create_nonce( 'dokan_add_product_nonce' ),
    ],
    dokan_get_navigation_url( 'new-product' )
);
?>

<div class="dokan-spmv-add-new-product-search-box-area dokan-w13">
    <div class="info-section">
        <p class="sub-header"><?php esc_html_e( 'Search similar products in this marketplace', 'dokan' ); ?></p>
    </div>
    <form action="<?php echo esc_url( $action ); ?>" type="GET" class="listing-product-search-form dokan-form-inline listing">
        <div class="dokan-input-group input-group-center">
            <input type="text" name="search" value="<?php echo esc_attr( $search ); ?>" class="dokan-form-control" placeholder="<?php esc_attr_e( 'Search Product', 'dokan' ); ?>">
            <span class="dokan-input-group-btn">
                <input class="dokan-btn dokan-btn-search" type="submit" value="<?php esc_attr_e( 'Search', 'dokan' ); ?>">
            </span>
        </div>
        <input type="hidden" name="type" value="<?php echo esc_attr( $type ); ?>">
    </form>
    <div class="footer-create-new-section dokan-product-listing">
        <?php esc_html_e( 'Or', 'dokan' ); ?>

        <?php if ( 'booking' === $type ) : ?>
            <a href="<?php echo esc_url( dokan_get_navigation_url( 'booking/new-product' ) ); ?>"><?php esc_html_e( 'Create New Booking Product', 'dokan' ); ?></a>
        <?php elseif ( 'auction' === $type ) : ?>
            <a href="<?php echo esc_url( dokan_get_navigation_url( 'new-auction-product' ) ); ?>"><?php esc_html_e( 'Create New Auction Product', 'dokan' ); ?></a>
        <?php else : ?>
            <a class="<?php echo ( 'on' === dokan_get_option( 'disable_product_popup', 'dokan_selling', 'off' ) ) ? '' : 'dokan-add-new-product'; ?>" href="<?php echo esc_url( $new_product_url ); ?>"><?php esc_html_e( 'Create New', 'dokan' ); ?></a>
        <?php endif; ?>

    </div>
</div>
