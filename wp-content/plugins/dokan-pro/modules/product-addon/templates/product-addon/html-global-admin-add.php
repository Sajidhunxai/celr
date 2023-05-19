<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$page_title   = __( 'Create add-ons', 'dokan' );
$button_title = __( 'Publish', 'dokan' );

if ( isset( $_POST ) && ! empty( $_POST['save_addon'] ) || ! empty( $_GET['edit'] ) ) {
	$page_title   = __( 'Edit Add-on', 'dokan' );
	$button_title = __( 'Update', 'dokan' );
}
?>
<div class="woocommerce dokan-pa-create-addons">
    <a class="back-to-addon-lists-btn" href="<?php echo dokan_get_navigation_url( 'settings/product-addon' ); ?>"> 
    	&larr; <?php esc_html_e( 'Back to addon lists', 'dokan' ); ?>
	</a>

    <div><?php esc_html_e( 'Set up add-ons that apply to all products or specific product categories.', 'dokan' ); ?></div><br />

	<form method="POST" action="">
		<table class="form-table global-addons-form meta-box-sortables dokan-table">
			<tr>
				<th>
					<label for="addon-reference"><?php esc_html_e( 'Name', 'dokan' ); ?></label>
				</th>
				<td>
					<input type="text" name="addon-reference" id="addon-reference" style="width:50%;" value="<?php echo esc_attr( $reference ); ?>" />
					<p class="description"><?php esc_html_e( 'This name is for your reference only and will not be visible to customers.', 'dokan' ); ?></p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="addon-priority"><?php esc_html_e( 'Priority', 'dokan' ); ?></label>
				</th>
				<td>
					<input type="text" name="addon-priority" id="addon-priority" style="width:50%;" value="<?php echo esc_attr( $priority ); ?>" />
					<p class="description"><?php esc_html_e( 'This determines the order when there are multiple add-ons. Add-ons for individual products are set to order 10.', 'dokan' ); ?></p>
				</td>
			</tr>
			<tr>
				<th>
					<label for="addon-objects"><?php esc_html_e( 'Product Categories', 'dokan' ); ?></label>
				</th>
				<td>
					<select id="addon-objects" name="addon-objects[]" multiple="multiple" style="width:50%;" data-placeholder="<?php esc_attr_e( 'Choose categories&hellip;', 'dokan' ); ?>" class="dokan-select2 wc-enhanced-select wc-pao-enhanced-select">
						<option value="all" <?php selected( in_array( 'all', $objects ), true ); ?>><?php esc_html_e( 'All Products', 'dokan' ); ?></option>
						<optgroup label="<?php esc_attr_e( 'Product categories', 'dokan' ); ?>">
							<?php
							$terms = get_terms( 'product_cat', array( 'hide_empty' => 0 ) );

							foreach ( $terms as $term ) {
								echo '<option value="' . $term->term_id . '" ' . selected( in_array( $term->term_id, $objects ), true, false ) . '>' . $term->name . '</option>';
							}
							?>
						</optgroup>
						<?php do_action( 'woocommerce_product_addons_global_edit_objects', $objects ); ?>
					</select>
					<p class="description"><?php esc_html_e( 'Select which categories this add-on should apply to. Create add-ons for a single product when editing that product.', 'dokan' ); ?></p>
				</td>
			</tr>

			<tr>
				<td colspan="2">
					<hr />
				</td>
			</tr>

			<tr>
				<td id="poststuff" class="postbox" colspan="2">
					<?php
					$exists = false;
                    dokan_get_template_part( 'product-addon/html-addon-panel', '', array(
                        'is_product_addon' => true,
                        'exists'           => $exists,
                        'product_addons'   => $product_addons
                    ) );
					?>
				</td>
			</tr>
		</table>
		<p class="submit dokan-right">
			<input type="hidden" name="edit_id" value="<?php echo ( ! empty( $edit_id ) ? esc_attr( $edit_id ) : '' ); ?>" />
			<input type="hidden" name="save_addon" value="true" />
            <?php wp_nonce_field( 'dokan_pa_save_addons', 'dokan_pa_save_addons_nonce' ); ?>
			<input type="submit" name="submit" id="submit" class="dokan-btn dokan-btn-theme" value="<?php echo esc_attr( $button_title ); ?>">
		</p>
        <div class="dokan-clearfix"></div>
	</form>
</div>

<script type="text/javascript">
	jQuery( function( $ ) {
		$( '.wc-enhanced-select' ).on( 'select2:select', function( e ) {
			var selectedID = e.params.data.id,
				values     = $( '.wc-enhanced-select' ).val(),
				all        = 'all',
				allIndex   = values.indexOf( all );

			if ( all === selectedID ) {
				values = [ all ];
			} else if ( 0 === allIndex ) {
				values.splice( allIndex, 1 );
			}

			$( '.wc-enhanced-select' ).val( values ).trigger( 'change.select2' );
		} );
	} );
</script>
