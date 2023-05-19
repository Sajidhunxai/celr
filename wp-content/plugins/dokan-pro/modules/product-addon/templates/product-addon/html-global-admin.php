<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<div class="woocommerce dokan-pa-all-addons">
	<a class="dokan-btn dokan-btn-theme dokan-pa-create-btn" href="<?php echo add_query_arg( 'add', true, dokan_get_navigation_url( 'settings/product-addon' ) ); ?>" class="add-new-h2"><?php esc_html_e( 'Create New addon', 'dokan' ); ?></a>
    <div style="margin-bottom: 10px">
        <a href="<?php echo add_query_arg( 'add', true, dokan_get_navigation_url( 'settings/product-addon' ) ); ?>"><?php esc_html_e( 'Create New', 'dokan' ); ?></a>
    </div>
	<table id="global-addons-table" class="dokan-table" cellspacing="0">
		<thead>
			<tr>
				<th scope="col"><?php esc_html_e( 'Name', 'dokan' ); ?></th>
				<th><?php esc_html_e( 'Priority', 'dokan' ); ?></th>
				<th><?php esc_html_e( 'Product Categories', 'dokan' ); ?></th>
				<th><?php esc_html_e( 'Number of Fields', 'dokan' ); ?></th>
			</tr>
		</thead>
		<tbody id="the-list">
			<?php
            add_action( 'pre_get_posts', 'dokan_pa_view_addon_for_vendor_staff_vendor' );

            $global_addons = WC_Product_Addons_Groups::get_all_global_groups();

            remove_action( 'pre_get_posts', 'dokan_pa_view_addon_for_vendor_staff_vendor' );

			if ( $global_addons ) {
				foreach ( $global_addons as $global_addon ) {
					?>
					<tr>
						<td><a href="<?php echo esc_url( add_query_arg( 'edit', $global_addon['id'], dokan_get_navigation_url( 'settings/product-addon' ) ) ); ?>"><?php echo $global_addon['name']; ?></a>
							<div class="row-actions">
                                <span class="edit"><a href="<?php echo esc_url( add_query_arg( 'edit', $global_addon['id'], dokan_get_navigation_url( 'settings/product-addon' ) ) ); ?>"><?php esc_html_e( 'Edit', 'dokan' ); ?></a> | </span>
                                <span class="delete"><a class="delete" href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'delete', $global_addon['id'], dokan_get_navigation_url( 'settings/product-addon' ) ), 'delete_addon' ) ); ?>"><?php esc_html_e( 'Delete', 'dokan' ); ?></a></span>
                            </div>
						</td>
						<td><?php echo $global_addon['priority']; ?></td>
						<td>
						<?php
						$all_products = '1' === get_post_meta( $global_addon['id'], '_all_products', true ) ? true : false;
						$restrict_to_categories = $global_addon['restrict_to_categories'];

						if ( $all_products ) {
							esc_html_e( 'All Products', 'dokan' );
						} elseif ( 0 === count( $restrict_to_categories ) ) {
							esc_html_e( 'No Products Assigned', 'dokan' );
						} else {
							$objects = array_keys( $restrict_to_categories );
							$term_names = array_values( $restrict_to_categories );
							$term_names = apply_filters( 'woocommerce_product_addons_global_display_term_names', $term_names, $objects );
							echo implode( ', ', $term_names );
						}
						?>
						</td>
						<td><?php echo sizeof( $global_addon['fields'] ); ?></td>
					</tr>
					<?php
				}
			} else {
				?>
				<tr>
				</tr>
                <td colspan="5"><?php esc_html_e( 'No add-ons found.', 'dokan' ); ?> </td>
                <?php
			}
			?>
		</tbody>
	</table>
</div>
