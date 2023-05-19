<?php
    use WeDevs\Dokan\ProductCategory\Helper;
?>
<tr class="dokan-product-list-inline-edit-form dokan-hide">
    <td colspan="11">
        <fieldset>
            <div class="dokan-clearfix">
                <div class="dokan-w6 dokan-inline-edit-column">
                    <strong class="dokan-inline-edit-section-title"><?php esc_html_e( 'Quick Edit', 'dokan' ); ?></strong>

                    <div class="inline-edit-col dokan-clearfix">
                        <label class="dokan-w3">
                            <?php esc_html_e( 'Title', 'dokan' ); ?>
                        </label>
                        <div class="dokan-w9">
                            <input type="text" class="dokan-form-control" data-field-name="post_title" value="<?php echo esc_html( $post_title ); ?>">
                        </div>
                    </div>

                    <label>
                        <?php esc_html_e( 'Product categories', 'dokan' ); ?>
                    </label>
                    <?php
                        $data = Helper::get_saved_products_category( $product_id );
                        $data['hide_cat_title'] = 'yes';
                        $data['from'] = 'quick_edit';
                        dokan_get_template_part( 'products/dokan-category-header-ui', '', $data );
                    ?>
                </div>
                <div class="dokan-w6 dokan-inline-edit-column">
                    <label>
                        <?php esc_html_e( 'Product Tags', 'dokan' ); ?>
                    </label>

                    <select multiple="multiple" data-field-name="product_tag" class="product_tag_search product_tags dokan-form-control dokan-select2" data-placeholder="<?php esc_attr_e( 'Select tags', 'dokan' ); ?>">
                        <?php if ( ! empty( $product_tag ) ) { ?>
                            <?php foreach ( $product_tag as $tax_term ) { ?>
                                <option value="<?php echo esc_attr( $tax_term->term_id ); ?>" selected="selected" ><?php echo esc_html( $tax_term->name ); ?></option>
                            <?php } ?>
                        <?php } ?>
                    </select>

                    <label>
                        <input type="checkbox" data-field-name="reviews_allowed" value="open" <?php checked( $reviews_allowed, true ); ?>> &nbsp;
                        <?php esc_html_e( 'Enable Reviews', 'dokan' ); ?>
                    </label>

                    <label>
                        <?php esc_html_e( 'Status', 'dokan' ); ?>

                        <?php if ( 'pending' === $post_status ) { ?>
                            <span class="dokan-label dokan-label-danger">
                                <?php esc_html_e( 'Pending Review', 'dokan' ); ?>
                                <input type="hidden" data-field-name="post_status" value="<?php echo esc_attr( 'pending' ); ?>">
                            </span>
                        <?php } elseif ( function_exists( 'dokan_seller_vacation_is_seller_on_vacation' ) && dokan_seller_vacation_is_seller_on_vacation( dokan_get_current_user_id() ) ) { ?>
                            <span class="dokan-label dokan-label-info">
                                <?php esc_html_e( 'In Vacation', 'dokan' ); ?>
                                <input type="hidden" data-field-name="post_status" value="<?php echo esc_attr( 'vacation' ); ?>">
                            </span>
                        <?php } else { ?>
                            <select data-field-name="post_status" style="min-width: 100px;">
                                <?php foreach ( $options['post_statuses'] as $post_status_slug => $post_status_label ) { ?>
                                    <option value="<?php echo esc_attr( $post_status_slug ); ?>" <?php selected( $post_status, $post_status_slug ); ?>>
                                        <?php echo esc_html( $post_status_label ); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        <?php } ?>
                    </label>

                    <hr>

                    <strong class="dokan-inline-edit-section-title"><?php esc_html_e( 'Product Data', 'dokan' ); ?></strong>

                    <?php if ( $options['is_sku_enabled'] ) { ?>
                        <div class="dokan-inline-edit-field-row dokan-clearfix">
                            <label class="dokan-w3">
                                <?php esc_html_e( 'SKU', 'dokan' ); ?>
                            </label>
                            <div class="dokan-w9">
                                <input type="text" class="dokan-form-control" data-field-name="sku" value="<?php echo esc_html( $sku ); ?>">
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ( 'simple' === $product_type || 'external' === $product_type || 'subscription' === $product_type ) { ?>
                        <div class="dokan-inline-edit-field-row dokan-clearfix">
                            <label class="dokan-w3">
                                <?php esc_html_e( 'Price', 'dokan' ); ?>
                            </label>
                            <div class="dokan-w9">
                                <input type="text" class="dokan-form-control" data-field-name="_regular_price" value="<?php echo esc_html( $_regular_price ); ?>">
                            </div>
                        </div>

                        <div class="dokan-inline-edit-field-row dokan-clearfix">
                            <label class="dokan-w3">
                                <?php esc_html_e( 'Sale', 'dokan' ); ?>
                            </label>
                            <div class="dokan-w9">
                                <input type="text" class="dokan-form-control" data-field-name="_sale_price" value="<?php echo esc_html( $_sale_price ); ?>">
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ( $options['is_weight_enabled'] ) { ?>
                        <div class="dokan-inline-edit-field-row dokan-clearfix">
                            <label class="dokan-w3">
                                <?php esc_html_e( 'Weight', 'dokan' ); ?>
                            </label>
                            <div class="dokan-w9">
                                <input type="text" class="dokan-form-control" data-field-name="weight" value="<?php echo esc_html( $weight ); ?>">
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ( $options['is_dimensions_enabled'] ) { ?>
                        <div class="dokan-inline-edit-field-row dokan-clearfix">
                            <label class="dokan-w3">
                                <?php esc_html_e( 'L/W/H', 'dokan' ); ?>
                            </label>
                            <div class="dokan-w9 dokan-clearfix">
                                <div class="dokan-w4">
                                    <input type="text" class="dokan-form-control" data-field-name="length" value="<?php echo esc_html( $length ); ?>" placeholder="<?php esc_html_e( 'Length', 'dokan' ); ?>">
                                </div>
                                <div class="dokan-w4">
                                    <input type="text" class="dokan-form-control" data-field-name="width" value="<?php echo esc_html( $width ); ?>" placeholder="<?php esc_html_e( 'Width', 'dokan' ); ?>">
                                </div>
                                <div class="dokan-w4">
                                    <input type="text" class="dokan-form-control" data-field-name="height" value="<?php echo esc_html( $height ); ?>" placeholder="<?php esc_html_e( 'Height', 'dokan' ); ?>">
                                </div>
                            </div>
                        </div>
                    <?php } ?>

                    <?php if ( ( ( 'simple' === $product_type && ! $is_virtual ) || 'variable' === $product_type ) && 'sell_digital' !== $selling_type ) { ?>
                        <div class="dokan-inline-edit-field-row dokan-clearfix">
                            <label class="dokan-w3">
                                <?php esc_html_e( 'Shipping class', 'dokan' ); ?>
                            </label>
                            <div class="dokan-w9">
                                <select data-field-name="shipping_class_id" class="dokan-form-control">
                                    <option value="_no_shipping_class"><?php esc_html_e( 'No shipping class', 'dokan' ); ?></option>

                                    <?php foreach ( $options['shipping_classes'] as $shipping_class_obj ) { ?>
                                        <option value="<?php echo esc_attr( $shipping_class_obj->slug ); ?>"<?php selected( $shipping_class_id, $shipping_class_obj->term_id ); ?>>
                                            <?php echo esc_html( $shipping_class_obj->name ); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    <?php } ?>

                    <div class="dokan-inline-edit-field-row dokan-clearfix">
                        <label class="dokan-w3">
                            <?php esc_html_e( 'Visibility', 'dokan' ); ?>
                        </label>
                        <div class="dokan-w9">
                            <select data-field-name="_visibility" class="dokan-form-control">
                                <?php foreach ( $options['visibilities'] as $visibility_slug => $visibility_name ) { ?>
                                    <option value="<?php echo esc_attr( $visibility_slug ); ?>"<?php selected( $_visibility, $visibility_slug ); ?>>
                                        <?php echo esc_html( $visibility_name ); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <?php if ( ( 'simple' === $product_type || 'variable' === $product_type ) && $options['can_manage_stock'] ) { ?>
                        <label>
                            <input type="checkbox" data-field-name="manage_stock" value="open" data-field-toggler <?php checked( $manage_stock, true ); ?>> &nbsp;
                            <?php esc_html_e( 'Manage Stock', 'dokan' ); ?>
                        </label>

                        <div class="dokan-inline-edit-field-row dokan-clearfix<?php echo $manage_stock ? '' : ' dokan-hide'; ?>" data-field-toggle="manage_stock" data-field-show-on="true">
                            <label class="dokan-w3">
                                <?php esc_html_e( 'Stock Qty', 'dokan' ); ?>
                            </label>
                            <div class="dokan-w9">
                                <input type="text" class="dokan-form-control" data-field-name="stock_quantity" value="<?php echo esc_html( $stock_quantity ); ?>">
                            </div>
                        </div>

                        <div class="dokan-inline-edit-field-row dokan-clearfix<?php echo $manage_stock ? ' dokan-hide' : ''; ?>" data-field-toggle="manage_stock" data-field-show-on="false">
                            <label class="dokan-w3">
                                <?php esc_html_e( 'In Stock?', 'dokan' ); ?>
                            </label>
                            <div class="dokan-w9">
                                <select data-field-name="stock_status" class="dokan-form-control">
                                    <?php foreach ( $options['stock_statuses'] as $stock_status_slug => $stock_status_name ) { ?>
                                        <option value="<?php echo esc_attr( $stock_status_slug ); ?>"<?php selected( $stock_status, $stock_status_slug ); ?>>
                                            <?php echo esc_html( $stock_status_name ); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="dokan-inline-edit-field-row dokan-clearfix<?php echo $manage_stock ? '' : ' dokan-hide'; ?>" data-field-toggle="manage_stock" data-field-show-on="true">
                            <label class="dokan-w3">
                                <?php esc_html_e( 'Backorders?', 'dokan' ); ?>
                            </label>
                            <div class="dokan-w9">
                                <select data-field-name="backorders" class="dokan-form-control" style="width: 100%;">
                                    <?php foreach ( $options['backorder_options'] as $backorders_slug => $backorders_name ) { ?>
                                        <option value="<?php echo esc_attr( $backorders_slug ); ?>"<?php selected( $backorders, $backorders_slug ); ?>>
                                            <?php echo esc_html( $backorders_name ); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    <?php } elseif ( 'grouped' === $product_type ) { ?>
                        <div class="dokan-inline-edit-field-row dokan-clearfix">
                            <label class="dokan-w3">
                                <?php esc_html_e( 'In Stock?', 'dokan' ); ?>
                            </label>
                            <div class="dokan-w9">
                                <select data-field-name="stock_status" class="dokan-form-control">
                                    <?php foreach ( $options['stock_statuses'] as $stock_status_slug => $stock_status_name ) { ?>
                                        <option value="<?php echo esc_attr( $stock_status_slug ); ?>"<?php selected( $stock_status, $stock_status_slug ); ?>>
                                            <?php echo esc_html( $stock_status_name ); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>
                    <?php } ?>

                    <?php
                    /**
                     * Filter to add custom fields to product quick edit form
                     *
                     * @since 3.7.4
                     *
                     * @args $product_id int
                     * @args $options array
                     */
                    do_action( 'dokan_quick_edit_before_column2_ends', $product_id, $options );
                    ?>
                </div>
            </div>

            <div class="dokan-clearfix quick-edit-submit-wrap">
                <button type="button" class="dokan-btn dokan-btn-default inline-edit-cancel">
                    <?php esc_html_e( 'Cancel', 'dokan' ); ?>
                </button>

                <div class="dokan-right inline-edit-submit-button">
                    <div class="dokan-spinner"></div>
                    <button type="button" class="dokan-btn dokan-btn-default dokan-btn-theme dokan-right inline-edit-update">
                        <?php esc_html_e( 'Update', 'dokan' ); ?>
                    </button>
                </div>
            </div>
            <?php
                /**
                 * Do any action after product quick edit fields.
                 *
                 * @parm \WC_Product $product Woocommerce product object
                 *
                 * @since 3.2.1
                 */
                do_action( 'dokan_after_quick_edit_form_fields', $product_id );
            ?>
            <input type="hidden" data-field-name="ID" value="<?php echo esc_attr( $product_id ); ?>">
            <input type="hidden" data-field-name="product_type" value="<?php echo esc_attr( $product_type ); ?>">
        </fieldset>
    </td>
</tr>
