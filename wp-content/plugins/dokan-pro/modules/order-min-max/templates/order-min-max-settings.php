<?php

use WeDevs\Dokan\Vendor\Vendor;

if ( empty( $min_max_args ) ) {
    return;
}
// phpcs:ignore
extract( $min_max_args, EXTR_SKIP );

if ( 'on' === $enable_min_max_quantity ) :
    ?>
    <div class="dokan-form-group goto_vacation_settings">
        <label class="dokan-w3 dokan-control-label" for="enable_vendor_min_max_quantity"><?php esc_html_e( 'Enable Min/Max Quantities', 'dokan' ); ?></label>
        <div class="dokan-w9">
            <div class="checkbox dokan-text-left">
                <label>
                    <input type="hidden" name="enable_vendor_min_max_quantity" value="no">
                    <input <?php echo checked( $enable_vendor_min_max_quantity, 'yes' ); ?> type="checkbox" name="enable_vendor_min_max_quantity" id="enable_vendor_min_max_quantity" class="order_min_max_input_handle" value="yes"> <?php esc_html_e( 'Activating this will set min and max quantities for selected products.', 'dokan' ); ?>
                </label>
            </div>
        </div>
    </div>
    <div id="min_max_quantity" class="dokan-hide">
        <div class="dokan-form-group goto_vacation_settings">
            <label class="dokan-w3 dokan-control-label" for="min_quantity_to_order"><?php esc_html_e( 'Minimum product quantity to place an order', 'dokan' ); ?></label>
            <div class="dokan-w3">
                <div class="checkbox dokan-text-left">
                    <input value="<?php echo esc_attr( $min_quantity_to_order ); ?>" type="number" min="1" name="min_quantity_to_order" id="min_quantity_to_order" class="dokan-form-control order_min_max_input_handle"/>
                </div>
            </div>
        </div>
        <div class="dokan-form-group goto_vacation_settings">
            <label class="dokan-w3 dokan-control-label" for="max_quantity_to_order"><?php esc_html_e( 'Maximum product quantity to place an order', 'dokan' ); ?></label>
            <div class="dokan-w3">
                <div class="checkbox dokan-text-left">
                    <input value="<?php echo esc_attr( $max_quantity_to_order ); ?>" type="number" min="1" name="max_quantity_to_order" id="max_quantity_to_order" class="dokan-form-control order_min_max_input_handle" />
                </div>
            </div>
        </div>
    </div>
	<?php
endif;
if ( 'on' === $enable_min_max_amount ) :
    ?>
    <div class="dokan-form-group goto_vacation_settings">
        <label class="dokan-w3 dokan-control-label" for="enable_vendor_min_max_amount"><?php esc_html_e( 'Enable Min/Max Amount', 'dokan' ); ?></label>
        <div class="dokan-w9">
            <div class="checkbox dokan-text-left">
                <label>
                    <input type="hidden" name="enable_vendor_min_max_amount" value="no">
                    <input <?php echo checked( $enable_vendor_min_max_amount, 'yes' ); ?> type="checkbox" name="enable_vendor_min_max_amount" id="enable_vendor_min_max_amount" class='order_min_max_input_handle' value="yes"> <?php esc_html_e( 'Activating this will set min and max amount for selected products.', 'dokan' ); ?>
                </label>
            </div>
        </div>
    </div>
    <div id="min_max_amount" class="dokan-hide">
        <div class="dokan-form-group">
            <label class="dokan-w3 dokan-control-label" for="min_amount_to_order"><?php esc_html_e( 'Minimum amount to place an order', 'dokan' ); ?></label>
            <div class="dokan-w3">
                <div class="checkbox dokan-text-left">
                    <input value="<?php echo esc_attr( $min_amount_to_order ); ?>" type="number" min="1" name="min_amount_to_order" id="min_amount_to_order" class="dokan-form-control order_min_max_input_handle" />
                </div>
            </div>
        </div>
        <div class="dokan-form-group">
            <label class="dokan-w3 dokan-control-label" for="max_amount_to_order"><?php esc_html_e( 'Maximum amount to place an order', 'dokan' ); ?></label>
            <div class="dokan-w3">
                <div class="checkbox dokan-text-left">
                    <input value="<?php echo esc_attr( $max_amount_to_order ); ?>" type="number" min="1" name="max_amount_to_order" id="max_amount_to_order" class="dokan-form-control order_min_max_input_handle" />
                </div>
            </div>
        </div>
    </div>
	<?php
endif;
if ( ( 'on' === $enable_min_max_quantity || 'on' === $enable_min_max_amount ) ) :
    ?>
    <div class="dokan-form-group show_if_min_max_active">
        <label class="dokan-w3 dokan-control-label" for="product-dropdown"><?php esc_html_e( 'Select Products', 'dokan' ); ?></label>
        <div class="dokan-w5 dokan-text-left">
            <select class="dokan-form-control dokan-coupon-product-select dokan-product-search order_min_max_input_handle" multiple="multiple" style="width: 100%;" id="product_drop_down" name="product_drop_down[]" data-placeholder="<?php esc_attr_e( 'Search for a product', 'dokan' ); ?>" data-action="dokan_json_search_products_and_variations" data-user_ids="<?php echo dokan_get_current_user_id(); ?>">
                <option value="-1"><?php esc_html_e( 'Select All', 'dokan' ); ?></option>
                <?php
                foreach ( $vendor_min_max_products as $product_id ) {
                    if ( '-1' !== $product_id ) {
                        $product = wc_get_product( $product_id );
                        if ( is_object( $product ) ) {
                            echo '<option value="' . esc_attr( $product_id ) . '"' . selected( true, true, false ) . '>' . wp_kses_post( $product->get_formatted_name() ) . '</option>';
                        }
                    } else {
                        echo '<option value="-1" ' . selected( true, true, false ) . '>' . __( 'Select All', 'dokan' ) . '</option>';
                    }
                }
                ?>
            </select>
            <a href='#' style='margin-top: 5px;' class='dokan-btn dokan-btn-default dokan-btn-sm dokan-min-max-product-select-all'><?php esc_html_e( 'Select all', 'dokan' ); ?></a>
            <a href="#" style="margin-top: 5px;" class="dokan-btn dokan-btn-default dokan-btn-sm dokan-min-max-product-clear-all"><?php esc_html_e( 'Clear', 'dokan' ); ?></a>
        </div>
    </div>
    <div class="dokan-form-group show_if_min_max_active">
        <label for="product_cat" class="dokan-w3 dokan-control-label"><?php esc_html_e( 'Select Category', 'dokan' ); ?></label>
        <div class="dokan-w5 dokan-text-left">
            <?php
            $seller_id = get_current_user_id();
            $vendor    = dokan()->vendor->get( $seller_id );

            if ( $vendor instanceof Vendor ) {
                $categories = $vendor->get_store_categories();
            }
            ?>
            <?php if ( dokan_get_option( 'product_category_style', 'dokan_selling', 'single' ) === 'single' ) : ?>
                <?php if ( ! empty( $categories ) ) : ?>
                    <select  name="product_cat" id="product_cat" class="dokan-form-control order_min_max_input_handle">
                        <option value=""><?php esc_html_e( 'Select one', 'dokan' ); ?></option>
                        <?php foreach ( $categories as $category ) : ?>
                            <option value="<?php echo esc_attr( $category->term_id ); ?>" <?php echo selected( absint( $vendor_min_max_product_cat ), $category->term_id ); ?> ><?php echo esc_html( $category->name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <div class="dokan-product-cat-alert dokan-hide">
                        <?php esc_html_e( 'Please choose a category!', 'dokan' ); ?>
                    </div>
                <?php endif; ?>
            <?php elseif ( dokan_get_option( 'product_category_style', 'dokan_selling', 'single' ) === 'multiple' ) : ?>
                <?php if ( ! empty( $categories ) ) : ?>
					<?php $vendor_min_max_product_cat = is_array( $vendor_min_max_product_cat ) ? $vendor_min_max_product_cat : [ $vendor_min_max_product_cat ]; ?>
                    <select data-placeholder="<?php echo esc_attr__( 'Select product category', 'dokan' ); ?>" multiple="multiple" name="product_cat[]" id="product_cat" class="dokan-form-control product_cat dokan-form-control dokan-select2 order_min_max_input_handle">
                        <?php foreach ( $categories as $category ) : ?>
                            <option value="<?php echo esc_attr( $category->term_id ); ?>" <?php echo in_array( (string)$category->term_id, $vendor_min_max_product_cat, true ) ? 'selected' : ''; // phpcs:ignore ?> ><?php echo esc_html( $category->name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>
	<?php
endif;
?>
