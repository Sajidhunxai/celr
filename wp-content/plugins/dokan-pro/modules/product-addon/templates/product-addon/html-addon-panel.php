<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$has_addons = ( ! empty( $product_addons ) && 0 < count( $product_addons ) ) ? 'wc-pao-has-addons' : '';
?>
<div id="product_addons_data" class="woocommerce_options_panel">
	<?php do_action( 'woocommerce_product_addons_panel_start' ); ?>
	<div class="wc-pao-field-header">
		<p><strong><?php esc_html_e( 'Add-on fields', 'dokan' ); ?><?php echo wc_help_tip( __( 'Add fields to get additional information from customers', 'dokan' ) ); ?></strong></p>
		<p class="wc-pao-toolbar <?php echo esc_attr( $has_addons ); ?>">
			<a href="#" class="wc-pao-expand-all"><?php esc_html_e( 'Expand all', 'dokan' ); ?></a>&nbsp;/&nbsp;<a href="#" class="wc-pao-close-all"><?php esc_html_e( 'Close all', 'dokan' ); ?></a>
		</p>
	</div>

	<div class="wc-pao-addons <?php echo esc_attr( $has_addons ); ?>">

		<?php
		$loop = 0;

		foreach ( $product_addons as $addon ) {
            dokan_get_template_part( 'product-addon/html-addon', '', array(
                'is_product_addon' => true,
                'loop'             => $loop,
                'addon'            => $addon,
            ) );

			$loop++;
		}
		?>

	</div>

	<div class="wc-pao-actions">
		<button type="button" class="dokan-btn dokan-btn-theme dokan-btn-sm wc-pao-add-field"><?php esc_html_e( 'Add Field', 'dokan' ); ?></button>

		<div class="wc-pao-toolbar__import-export">
			<button type="button" class="dokan-btn dokan-btn-default dokan-btn-sm wc-pao-import-addons"><?php esc_html_e( 'Import', 'dokan' ); ?></button>
			<button type="button" class="dokan-btn dokan-btn-default dokan-btn-sm wc-pao-export-addons"><?php esc_html_e( 'Export', 'dokan' ); ?></button>
		</div>
	</div>
	<div class="wc-pao-import-export-container">
		<textarea name="export_product_addon" class="wc-pao-export-field" cols="20" rows="5" readonly="readonly"><?php echo esc_textarea( serialize( $product_addons ) ); ?></textarea>

		<textarea name="import_product_addon" class="wc-pao-import-field" cols="20" rows="5" placeholder="<?php esc_attr_e( 'Paste exported form data here and then save to import fields. The imported fields will be appended.', 'dokan' ); ?>"></textarea>
	</div>
	<?php if ( $exists ) : ?>
		<div class="wc-pao-product-global-addon">
			<strong><?php esc_html_e( 'Additional add-ons', 'dokan' ); ?></strong>
			<p>
				<?php
				/* translators: %s URL to addons page */
				printf( __( 'You can create additional <a href="%s">add-ons</a> that apply to all products or to certain categories.', 'dokan' ), esc_url( dokan_get_navigation_url( 'settings/product-addon') ) );
				?>
			</p>

			<p class="dokan-form-group">
    			<label for="_product_addons_exclude_global" class="dokan-checkbox-inline">
                    <input id="_product_addons_exclude_global" name="_product_addons_exclude_global" type="checkbox" value="1" <?php checked( $exclude_global, 1 ); ?>/>
                    &nbsp;<?php esc_html_e( 'Exclude add-ons', 'dokan' ); ?>&nbsp;&nbsp;
                </label>
    			<span class="description"><?php esc_html_e( 'Hide additional add-ons that may apply to this product.', 'dokan' ); ?></span>
			</p>
		</div>
	<?php endif; ?>
</div>
