<?php
/**
 * @var $product_id int
 * @var $saved_data array
 */

use WeDevs\Dokan\CatalogMode\Helper;

?>
<div class="dokan-inline-edit-field-row dokan-clearfix">
    <?php if ( Helper::hide_add_to_cart_button_option_is_enabled_by_admin() ) : ?>
        <div class="dokan-w12">
            <label>
                <input type="checkbox" value="on" class="dokan-catalog-mode-hide-cart" data-field-name="hide_add_to_cart_button" data-field-toggler
                    <?php checked( $saved_data['hide_add_to_cart_button'], 'on' ); ?> />
                <?php esc_html_e( 'Check to remove Add to Cart option from your products.', 'dokan' ); ?>
            </label>
        </div>
    <?php endif; ?>
    <?php if ( Helper::hide_product_price_option_is_enabled_by_admin() ) : ?>
        <div class="dokan-w12 <?php echo 'on' === $saved_data['hide_add_to_cart_button'] ? '' : ' dokan-hide'; ?>" data-field-toggle="hide_add_to_cart_button" data-field-show-on="true">
            <label>
                <input type="checkbox" value="on" class="dokan-catalog-mode-hide-price" data-field-name="hide_product_price"
                    <?php checked( $saved_data['hide_product_price'], 'on' ); ?> />
                <?php esc_html_e( 'Check to hide product price from your products.', 'dokan' ); ?>
            </label>
        </div>
    <?php endif; ?>
</div>
