<?php

/**
 * External product template
 *
 * @since Dokan_PRO_SINCE
 *
 * @package Dokan
 */

$_product_url = get_post_meta( $post_id, '_product_url', true );
$_button_text = get_post_meta( $post_id, '_button_text', true );
?>

<div class="dokan-external-product-content show_if_external">
    <div class="dokan-form-group">
        <label class="form-label"><?php echo esc_html_e( 'Product URL', 'dokan' ); ?></label>
        <input type="text" class="dokan-form-control" style="" name="_product_url" id="_product_url" value="<?php echo esc_url_raw( $_product_url ); ?>" placeholder="https://">
        <span><?php echo esc_html_e( 'Enter the external URL to the product.', 'dokan' ); ?></span>
    </div>

    <div class="dokan-form-group">
        <label class="form-label"><?php echo esc_html_e( 'Button text', 'dokan' ); ?></label>
        <input type="text" class="dokan-form-control" name="_button_text" id="_button_text" value="<?php echo esc_html( $_button_text ); ?>" placeholder="<?php echo esc_attr_e( 'Buy product', 'dokan' ); ?>"> <span class="description"><?php echo esc_html_e( 'This text will be shown on the button linking to the external product.', 'dokan' ); ?></span>
    </div>
</div>
