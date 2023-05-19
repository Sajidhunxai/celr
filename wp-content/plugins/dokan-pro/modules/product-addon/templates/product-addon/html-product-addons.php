<?php
$product        = wc_get_product( $post );
$exists         = (bool) $product->get_id();
$exclude_global = ! empty( $product->get_meta( '_product_addons_exclude_global' ) ) ? 1 : 0;
$product_addons = array_filter( (array) $product->get_meta( '_product_addons' ) );
?>


<div id="dokan-product-addons-options" class="dokan-product-addons-options dokan-edit-row dokan-clearfix hide_if_grouped hide_if_external">
    <div class="dokan-section-heading" data-togglehandler="dokan_product_addons_options">
        <h2><i class="fas fa-wrench" aria-hidden="true"></i> <?php esc_html_e( 'Add-ons', 'dokan' ); ?><span class=""></h2>
        <p class=""><?php esc_html_e( 'Manage addon fields for this product.', 'dokan' ); ?></p>

        <a href="#" class="dokan-section-toggle">
            <i class="fas fa-sort-down fa-flip-vertical" aria-hidden="true"></i>
        </a>
        <div class="dokan-clearfix"></div>
    </div>
    <div class="dokan-section-content">
        <?php
        dokan_get_template_part(
            'product-addon/html-addon-panel', '', array(
                'is_product_addon' => true,
                'exists'           => $exists,
                'product_addons'   => $product_addons,
                'exclude_global'   => $exclude_global,
            )
        );
        ?>
    </div>
</div>
