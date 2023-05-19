<?php
/**
 * Dokan Min Max Product settings Content.
 *
 * @since   3.5.0
 *
 * @package dokan
 */

if ( empty( $product_settings_args ) ) {
    return;
}
// phpcs:ignore
extract( $product_settings_args, EXTR_SKIP );

?>

<?php do_action( 'dokan_order_min_max_product_settings_before', $post_id ); ?>
<div class="dokan-edit-row dokan-clearfix dokan-border-top dokan-form-group dokan-product-type-container show_if_simple">
    <div class="dokan-section-heading" data-togglehandler="dokan_product_shipping_tax">
        <h2><i class="fa fa-usd" aria-hidden="true"></i> <?php echo isset( $tab_title ) ? esc_html( $tab_title ) : ''; ?></h2>
        <p><?php echo isset( $tab_desc ) ? esc_html( $tab_desc ) : ''; ?></p>
        <a href="#" class="dokan-section-toggle">
            <i class="fa fa-sort-desc fa-flip-vertical" aria-hidden="true"></i>
        </a>
        <div class="dokan-clearfix"></div>
    </div>
    <div class="dokan-section-content">
        <div class="dokan-clearfix dokan-shipping-container">
            <div class="dokan-form-group">
                <label class="dokan-checkbox-inline" for="product_wise_activation">
                    <input type="hidden" name="product_wise_activation" value="no">
                    <input type="checkbox" id="product_wise_activation" name="product_wise_activation" <?php checked( esc_attr( $product_wise_activation ), 'yes' ); ?> class="order_min_max_input_handle" value="yes">
                    <?php esc_html_e( 'Enable Min Max Rule for this product', 'dokan' ); ?>
                </label>
            </div>
            <div class="show_if_min_max <?php echo 'yes' !== $product_wise_activation ? 'dokan-hide' : ''; ?>">
                <div class="dokan-form-group content-half-part" style="padding-right: 10px;">
                    <label class="form-label" for="min_quantity"><?php esc_html_e( 'Minimum quantity: ', 'dokan' ); ?>
                        <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php esc_attr_e( 'Set Minimum product quantity to order.', 'dokan' ); ?>">
                            <i class="fa fa-question-circle"></i>
                        </span>
                    </label>
                    <input type="number" step="1" id="min_quantity" class="wc_input_price dokan-form-control" min="0" name="min_quantity" value="<?php echo esc_attr( $min_quantity ); ?>">
                </div>
                <div class="dokan-form-group content-half-part">
                    <label class="form-label" for="max_quantity"><?php esc_html_e( 'Maximum quantity: ', 'dokan' ); ?>
                        <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php esc_attr_e( 'Set Maximum product quantity to order.', 'dokan' ); ?>">
                            <i class="fa fa-question-circle"></i>
                        </span>
                    </label>
                    <input type="number" class="dokan-form-control" step="1" name="max_quantity" value="<?php echo esc_attr( $max_quantity ); ?>" id="max_quantity">
                </div>
            </div>
            <div class="show_if_min_max <?php echo 'yes' !== $product_wise_activation ? esc_attr( 'dokan-hide' ) : ''; ?>">
                <div class="dokan-form-group content-half-part" style="padding-right: 10px;">
                    <label class="form-label" for="min_amount"><?php esc_html_e( 'Minimum amount: ', 'dokan' ); ?>
                        <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php esc_attr_e( 'Set Minimum amount to order.', 'dokan' ); ?>">
                            <i class="fa fa-question-circle"></i>
                        </span>
                    </label>
                    <input type="text" id="min_amount" min="1" class="wc_input_price dokan-form-control" name="min_amount" value="<?php echo esc_attr( $min_amount ); ?>">
                </div>
                <div class="dokan-form-group content-half-part">
                    <label class="form-label" for="max_amount"><?php esc_html_e( 'Maximum amount: ', 'dokan' ); ?>
                        <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php esc_attr_e( 'Set Maximum amount to order.', 'dokan' ); ?>">
                            <i class="fa fa-question-circle"></i>
                        </span>
                    </label>
                    <input type="text" class="dokan-form-control" step="1" name="max_amount" value="<?php echo esc_attr( $max_amount ); ?>" id="max_amount">
                </div>
            </div>
            <div class="show_if_min_max <?php echo 'yes' !== $product_wise_activation ? 'dokan-hide' : ''; ?>">
                <div class="dokan-form-group content-half-part">
                    <input type="hidden" name="_donot_count" value="no">
                    <input type="checkbox" id="_donot_count" name="_donot_count" <?php checked( esc_attr( $_donot_count ), 'yes' ); ?> value="yes">
                    <label class="dokan-checkbox-inline" for="_donot_count">
                        <?php esc_html_e( 'Order rules: Do not count', 'dokan' ); ?>
                        <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php esc_attr_e( 'Don\'t count this product against order rules when there are other items in the cart.', 'dokan' ); ?>">
                            <i class="fa fa-question-circle"></i>
                        </span>
                    </label>
                </div>
                <div class="dokan-form-group content-half-part">
                    <input type="hidden" name="ignore_from_cat" value="no">
                    <input type="checkbox" id="ignore_from_cat" name="ignore_from_cat" <?php checked( esc_attr( $ignore_from_cat ), 'yes' ); ?> value="yes">
                    <label class="dokan-checkbox-inline" for="ignore_from_cat">
                        <?php esc_html_e( 'Category rules: Exclude', 'dokan' ); ?>
                        <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php esc_attr_e( 'Exclude this product from category rules.', 'dokan' ); ?>">
                            <i class="fa fa-question-circle"></i>
                        </span>
                    </label>
                </div>
            </div>
            <div class="dokan-clearfix"></div>
        </div>
    </div><!-- .dokan-side-right -->
</div><!-- .dokan-product-inventory -->
<?php
echo wp_nonce_field( 'min_max_product_wise_activation_action', 'min_max_product_wise_activation_field' );
do_action( 'dokan_order_min_max_product_settings_after', $post_id );
