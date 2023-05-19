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

<div>
    <div class="dokan-clearfix dokan-shipping-container">
        <div class="dokan-form-group">
            <label class="dokan-checkbox-inline" for="variable_product_wise_activation[<?php echo $loop; ?>]">
                <input type="hidden" class='variable_product_wise_activation' name="variable_product_wise_activation[<?php echo $loop; ?>]" value="no">
                <input type="checkbox" id="variable_product_wise_activation[<?php echo $loop; ?>]" name="variable_product_wise_activation[<?php echo $loop; ?>]" <?php checked( esc_attr( $product_wise_activation ), 'yes' ); ?> class="order_min_max_input_handle variable_product_wise_activation" value="yes">
                <strong><?php esc_html_e( 'Enable Min Max Rule for this product', 'dokan' ); ?></strong>
            </label>
        </div>
        <?php if ( 'on' === $enable_min_max_quantity ) : ?>
            <div>
                <div class="dokan-form-group content-half-part" style="padding-right: 10px;">
                    <label class="form-label" for="variable_min_quantity[<?php echo $loop; ?>]"><?php esc_html_e( 'Minimum quantity: ', 'dokan' ); ?>
                        <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php esc_attr_e( 'Set Minimum product quantity to order.', 'dokan' ); ?>">
                            <i class="fa fa-question-circle"></i>
                        </span>
                    </label>
                    <input type="number" step="1" id="variable_min_quantity[<?php echo $loop; ?>]" class="variable_min_quantity wc_input_price dokan-form-control" name="variable_min_quantity[<?php echo $loop; ?>]" value="<?php echo esc_attr( $min_quantity ); ?>">
                </div>
                <div class="dokan-form-group content-half-part">
                    <label class="form-label" for="variable_max_quantity[<?php echo $loop; ?>]"><?php esc_html_e( 'Maximum quantity: ', 'dokan' ); ?>
                        <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php esc_attr_e( 'Set Maximum product quantity to order.', 'dokan' ); ?>">
                            <i class="fa fa-question-circle"></i>
                        </span>
                    </label>
                    <input type="number" class="variable_max_quantity dokan-form-control" step="1" name="variable_max_quantity[<?php echo $loop; ?>]" value="<?php echo esc_attr( $max_quantity ); ?>" id="variable_max_quantity[<?php echo $loop; ?>]">
                </div>
            </div>
            <?php
        endif;
        if ( 'on' === $enable_min_max_amount ) :
            ?>
            <div>
                <div class="dokan-form-group content-half-part" style="padding-right: 10px;">
                    <label class="form-label" for="variable_min_amount[<?php echo $loop; ?>]"><?php esc_html_e( 'Minimum amount: ', 'dokan' ); ?>
                        <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php esc_attr_e( 'Set Minimum amount to order.', 'dokan' ); ?>">
                            <i class="fa fa-question-circle"></i>
                        </span>
                    </label>
                    <input type="text" id="variable_min_amount[<?php echo $loop; ?>]" class="variable_min_amount wc_input_price dokan-form-control" name="variable_min_amount[<?php echo $loop; ?>]" value="<?php echo esc_attr( $min_amount ); ?>">
                </div>
                <div class="dokan-form-group content-half-part">
                    <label class="form-label" for="variable_max_amount[<?php echo $loop; ?>]"><?php esc_html_e( 'Maximum amount: ', 'dokan' ); ?>
                        <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php esc_attr_e( 'Set Maximum amount to order.', 'dokan' ); ?>">
                            <i class="fa fa-question-circle"></i>
                        </span>
                    </label>
                    <input type="text" class="variable_max_amount dokan-form-control" step="1" name="variable_max_amount[<?php echo $loop; ?>]" value="<?php echo esc_attr( $max_amount ); ?>" id="variable_max_amount[<?php echo $loop; ?>]">
                </div>
            </div>
        <?php endif; ?>
        <div>
            <div class="dokan-form-group content-half-part">
                <input type="hidden" name="variable__donot_count[<?php echo $loop; ?>]" value="no">
                <input type="checkbox" class="variable__donot_count" id="variable__donot_count[<?php echo $loop; ?>]" name="variable__donot_count[<?php echo $loop; ?>]" <?php checked( esc_attr( $_donot_count ), 'yes' ); ?> value="yes">
                <label class="dokan-checkbox-inline" for="variable__donot_count[<?php echo $loop; ?>]">
                    <?php esc_html_e( 'Order rules: Do not count', 'dokan' ); ?>
                    <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php esc_attr_e( 'Don\'t count this product against order rules when there are other items in the cart.', 'dokan' ); ?>">
                        <i class="fa fa-question-circle"></i>
                    </span>
                </label>
            </div>
            <div class="dokan-form-group content-half-part">
                <input type="hidden" name="variable_ignore_from_cat[<?php echo $loop; ?>]" value="no">
                <input type="checkbox" class="variable_ignore_from_cat" id="variable_ignore_from_cat[<?php echo $loop; ?>]" name="variable_ignore_from_cat[<?php echo $loop; ?>]" <?php checked( esc_attr( $ignore_from_cat ), 'yes' ); ?> value="yes">
                <label class="dokan-checkbox-inline" for="variable_ignore_from_cat[<?php echo $loop; ?>]">
                    <?php esc_html_e( 'Category rules: Exclude', 'dokan' ); ?>
                    <span class="dokan-tooltips-help tips" data-placement="bottom" data-original-title="<?php esc_attr_e( 'Exclude this product from category rules.', 'dokan' ); ?>">
                        <i class="fa fa-question-circle"></i>
                    </span>
                </label>
            </div>
        </div>
        <div class="dokan-clearfix"></div>
    </div>
</div><!-- .dokan-product-inventory -->
<?php
echo wp_nonce_field( 'min_max_product_variation_wise_activation_action', 'min_max_product_variation_wise_activation_field' );
