<?php
/**
 * Search page table row template.
 *
 * @sience 3.5.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$vendor     = dokan_get_vendor_by_product( $product );
$average    = $product->get_average_rating();
$duplicator = new \Dokan_SPMV_Product_Duplicator();

if ( ! empty( $search_word ) ) {
    $words         = explode( ' ', $search_word );
    $product_title = preg_replace( '/(' . implode( '|', $words ) . ')/iu', '<strong class="search-word-match">\0</strong>', $product->get_title() );
} else {
    $product_title = $product->get_title();
}
?>

<tr>
    <td data-title="<?php esc_attr_e( 'Product Name', 'dokan' ); ?>" class="column-primary">
        <div class="dokan-w3 product-image-area">
            <?php echo wp_kses_post( $product->get_image( 'thumbnail' ) ); ?>
        </div>
        <div class="dokan-w9 product-info-area">
            <a target="_blank" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo wp_kses_post( $product_title ); ?></a><br>
            <div class="product-review-comment-area">
                <i class="fa fa-star star-icon-color" aria-hidden="true"></i> <strong>( <?php echo esc_html( $product->get_average_rating() ); ?> )</strong>&nbsp;&nbsp;
                <i class="fa fa-comments" aria-hidden="true"></i> <strong><?php echo esc_html( $product->get_review_count() ); ?></strong><br>
            </div>
            <div class="product-type-cat-area">
                <span class="dokan-spmv-product-type"><?php echo esc_html( ucwords( $product->get_type() ) ); ?></span>
                <?php echo wp_kses_post( wc_get_product_category_list( $product->get_id() ) ); ?>
            </div>
        </div>
        <button type="button" class="toggle-row"></button>
    </td>
    <td class="price-section" data-title="<?php esc_attr_e( 'Price', 'dokan' ); ?>">
        <?php
        if ( $product->get_price_html() ) {
            echo wp_kses_post( $product->get_price_html() );
        } else {
            echo '<span class="na">&ndash;</span>';
        }
        ?>
    </td>
    <td class="vendor-section" data-title="<?php esc_attr_e( 'Vendor', 'dokan' ); ?>">
        <strong><?php echo $vendor->get_name(); ?></strong>
    </td>
    <td class="action-section" data-title="<?php esc_attr_e( 'Actions', 'dokan' ); ?>">
        <?php if ( dokan_get_current_user_id() === $vendor->get_id() ) : ?>
            <a class="dokan-btn" href="<?php echo esc_url( dokan_edit_product_url( $product->get_id() ) ); ?>"
                data-product="<?php echo esc_attr( $product->get_id() ); ?>">
                <?php esc_html_e( 'Edit', 'dokan' ); ?>
            </a>
        <?php elseif ( $duplicator->check_already_cloned( $product->get_id(), dokan_get_current_user_id() ) ) : ?>
            <button class="dokan-btn" disabled
                data-product="<?php echo esc_attr( $product->get_id() ); ?>">
                <?php esc_html_e( 'Already Cloned', 'dokan' ); ?>
            </button>
        <?php else : ?>
            <button class="dokan-btn dokan-spmv-clone-product"
                data-product="<?php echo esc_attr( $product->get_id() ); ?>">
                <?php esc_html_e( 'Add To Store', 'dokan' ); ?>
            </button>
			<?php
        endif;
        do_action( 'dokan_spmv_products_search_action_after' );
        ?>
    </td>
    <td class="diviader"></td>
</tr>
