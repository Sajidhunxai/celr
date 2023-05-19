<?php
/**
 * Admin View: Bulk Edit Products
 */

use WeDevs\Dokan\ProductCategory\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

?>
<tr id="bulk-edit" class="dokan-product-list-inline-edit-form dokan-hide">
    <td colspan="11">
        <form action="" method="post">
            <fieldset>
                <div class="dokan-clearfix">
                    <div class="dokan-w4 dokan-inline-edit-column">
                        <strong class="dokan-inline-edit-section-title"><?php esc_html_e( 'Bulk Edit', 'dokan' ); ?></strong>
                        <div id="bulk-product-list" class="cat-checklist product_cat-checklist dokan-category-checklist"></div>
                    </div>
                    <div class="dokan-w8">
                        <strong class="dokan-inline-edit-section-title"><?php esc_html_e( 'Product categories', 'dokan' ); ?></strong>
                            <?php
                            $data = Helper::get_saved_products_category( 0, false );
                            $data['from'] = 'bulk_product';
                            $data['hide_cat_title'] = 'yes';

                            dokan_get_template_part( 'products/dokan-category-header-ui', '', $data );
                            ?>
                    </div>
                    <div class="dokan-w8">
                        <div>
                            <label>
                                <strong class="dokan-inline-edit-section-title"><?php esc_html_e( 'Status', 'dokan' ); ?></strong>
                                <select class="dokan-form-control" name="post_status" style="min-width: 100px;">
                                    <?php foreach ( $post_statuses as $key => $value ) : ?>
                                        <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </label>
                        </div>
                        <div class="dokan-w12 dokan-inline-edit-column">
                            <label>
                                <strong class="dokan-inline-edit-section-title"><?php esc_html_e( 'Product Tags', 'dokan' ); ?></strong>
                            </label>
                            <select multiple="multiple" data-field-name="product_tagz" name="product_tags[]" class="product_tag_search product_tags dokan-form-control dokan-select2" data-placeholder="<?php esc_attr_e( 'Select tags', 'dokan' ); ?>">
                                <?php if ( ! empty( $product_tag ) ) { ?>
                                    <?php foreach ( $product_tag as $key => $value ) : ?>
                                        <option value="<?php echo esc_attr( $value->term_id ); ?>" selected='selected'><?php echo esc_html( $value->name ); ?></option>
                                    <?php endforeach; ?>
                                <?php } ?>
                            </select>
                        </div>
                    </div>
                </div>
                <br />
                <div class="dokan-clearfix">
                    <strong class="dokan-inline-edit-section-title"><h4><?php esc_html_e( 'Product Data', 'dokan' ); ?></h4></strong>
                    <div class="dokan-w6 dokan-inline-edit-column">
                        <div class="dokan-inline-edit-field-row dokan-clearfix">
                            <label class="dokan-w3">
                                <?php esc_html_e( 'Price', 'dokan' ); ?>
                            </label>
                            <div class="dokan-w9">
                                <select class="dokan-form-control" id="change_regular_price" name="change_regular_price">
                                    <?php foreach ( $price as $key => $value ) : ?>
                                        <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php /* translators: %s is woocommerce currency symbol. */ ?>
                                <input type="text" name="_regular_price" id="dokan_bulk_regular_price" class="dokan-mt10 dokan-form-control dokan-hide" placeholder="<?php printf( esc_attr__( 'Enter price (%s)', 'dokan' ), get_woocommerce_currency_symbol() ); ?>" value="" />
                            </div>
                        </div>
                        <div class="dokan-inline-edit-field-row dokan-clearfix">
                            <label class="dokan-w3">
                                <?php esc_html_e( 'Sale', 'dokan' ); ?>
                            </label>
                            <div class="dokan-w9">
                                <select id="change_sale_price" class="dokan-form-control" name="change_sale_price">
                                    <?php foreach ( $sale as $key => $value ) : ?>
                                        <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <?php /* translators: %s is woocommerce currency symbol. */ ?>
                                <input type="text" name="_sale_price" id="dokan_bulk_sale_price" class="dokan-mt10 dokan-form-control dokan-hide" placeholder="<?php printf( esc_attr__( 'Enter sale price (%s)', 'dokan' ), get_woocommerce_currency_symbol() ); ?>" value="" />
                            </div>
                        </div>
                        <?php if ( wc_tax_enabled() ) : ?>
                            <div class="dokan-inline-edit-field-row dokan-clearfix">
                                <label class="dokan-w3">
                                    <?php esc_html_e( 'Tax status', 'dokan' ); ?>
                                </label>
                                <div class="dokan-w9">
                                    <select class="dokan-form-control" name="_tax_status">
                                        <?php foreach ( $tax_status as $key => $value ) : ?>
                                            <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="dokan-inline-edit-field-row dokan-clearfix">
                                <label class="dokan-w3">
                                    <?php esc_html_e( 'Tax class', 'dokan' ); ?>
                                </label>
                                <div class="dokan-w9">
                                    <select class="dokan-form-control" name="_tax_class">
                                        <?php
                                        $tax_class = [];
                                        if ( ! empty( $tax_classes ) ) {
                                            foreach ( $tax_classes as $class ) {
                                                $tax_class[ sanitize_title( $class ) ] = esc_html( $class );
                                            }
                                        }
                                        ?>
                                        <?php foreach ( $tax_class as $key => $value ) : ?>
                                            <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                        <?php endif; ?>

                        <?php if ( wc_product_weight_enabled() ) : ?>
                            <div class="dokan-inline-edit-field-row dokan-clearfix">
                                <label class="dokan-w3">
                                    <?php esc_html_e( 'Weight', 'dokan' ); ?>
                                </label>
                                <div class="dokan-w9">
                                    <select class="dokan-form-control" id="change_weight" name="change_weight">
                                        <?php foreach ( $weight as $key => $value ) : ?>
                                            <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php /* translators: %1$s is woocommerce format localized decimal. %2$s is woocommerce weight unit */ ?>
                                    <input class="dokan-mt10 dokan-form-control dokan-hide" id="_weight" type="text" name="_weight" placeholder="<?php printf( esc_attr__( '%1$s (%2$s)', 'dokan' ), wc_format_localized_decimal( 0 ), get_option( 'woocommerce_weight_unit' ) ); ?>" value="">
                                </div>
                            </div>

                        <?php endif; ?>

                        <?php if ( wc_product_dimensions_enabled() ) : ?>
                            <div class="dokan-inline-edit-field-row dokan-clearfix">
                                <label class="dokan-w3">
                                    <?php esc_html_e( 'L/W/H', 'dokan' ); ?>
                                </label>
                                <div class="dokan-w9">
                                    <select class="dokan-form-control" id="change_dimensions" name="change_dimensions">
                                        <?php foreach ( $lwh as $key => $value ) : ?>
                                            <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <?php /* translators: %s is woocommerce dimension unit */ ?>
                                    <input type="text" id="_length" name="_length" class="dokan-mt10 dokan-form-control dokan-hide text length" placeholder="<?php echo sprintf( esc_attr__( 'Length (%s)', 'dokan' ), get_option( 'woocommerce_dimension_unit' ) ); ?>" value="">
                                    <?php /* translators: %s is woocommerce dimension unit */ ?>
                                    <input type="text" id="_width" name="_width" class="dokan-mt10 dokan-form-control dokan-hide text width" placeholder="<?php echo sprintf( esc_attr__( 'Width (%s)', 'dokan' ), get_option( 'woocommerce_dimension_unit' ) ); ?>" value="">
                                    <?php /* translators: %s is woocommerce dimension unit */ ?>
                                    <input type="text" id="_height" name="_height" class="dokan-mt10 dokan-form-control dokan-hide text height" placeholder="<?php echo sprintf( esc_attr__( 'Height (%s)', 'dokan' ), get_option( 'woocommerce_dimension_unit' ), true ); ?>" value="">
                                </div>
                            </div>

                        <?php endif; ?>

                        <div class="dokan-inline-edit-field-row dokan-clearfix">
                            <label class="dokan-w3">
                                <?php esc_html_e( 'Shipping class', 'dokan' ); ?>
                            </label>
                            <div class="dokan-w9">
                                <select class="dokan-form-control" name="_shipping_class">
                                    <option value=""><?php esc_html_e( '— No change —', 'dokan' ); ?></option>
                                    <option value="_no_shipping_class"><?php esc_html_e( 'No shipping class', 'dokan' ); ?></option>
                                    <?php foreach ( $shipping_class as $key => $value ) : ?>
                                        <option value="<?php echo esc_attr( $value->slug ); ?>"><?php echo esc_html( $value->name ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="dokan-w6 dokan-inline-edit-column">
                        <div class="dokan-inline-edit-field-row dokan-clearfix">
                            <label class="dokan-w3">
                                <?php esc_html_e( 'Visibility', 'dokan' ); ?>
                            </label>
                            <div class="dokan-w9">
                                <select class="dokan-form-control" name="_visibility">
                                    <?php foreach ( $visibility as $key => $value ) : ?>
                                        <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <div class="dokan-inline-edit-field-row dokan-clearfix">
                            <label class="dokan-w3">
                                <?php esc_html_e( 'In stock?', 'dokan' ); ?>
                            </label>
                            <div class="dokan-w9">
                                <select class="dokan-form-control" name="_stock_status">
                                    <?php
                                    echo '<option value="">' . esc_html__( '— No Change —', 'dokan' ) . '</option>';
                                    ?>
                                    <?php foreach ( wc_get_product_stock_status_options() as $key => $value ) : ?>
                                        <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>

                        <?php if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) : ?>
                            <div class="dokan-inline-edit-field-row dokan-clearfix">
                                <label class="dokan-w3">
                                    <?php esc_html_e( 'Manage stock?', 'dokan' ); ?>
                                </label>
                                <div class="dokan-w9">
                                    <select class="dokan-form-control" name="_manage_stock">
                                        <?php foreach ( $manage_stock as $key => $value ) : ?>
                                            <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>
                            <div class="dokan-inline-edit-field-row dokan-clearfix">
                                <label class="dokan-w3">
                                    <?php esc_html_e( 'Stock qty', 'dokan' ); ?>
                                </label>
                                <div class="dokan-w9">
                                    <select class="dokan-form-control" id="change_stock" name="change_stock">
                                        <?php foreach ( $stock_qty as $key => $value ) : ?>
                                            <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                    <input type="text" name="_stock" id="_stock" class="dokan-mt10 dokan-form-control dokan-hide text stock" placeholder="<?php esc_attr_e( 'Stock qty', 'dokan' ); ?> " step="any" value="">
                                </div>
                            </div>
                            <div class="dokan-inline-edit-field-row dokan-clearfix">
                                <label class="dokan-w3">
                                    <?php esc_html_e( 'Backorders?', 'dokan' ); ?>
                                </label>
                                <div class="dokan-w9">
                                    <select class="dokan-form-control" name="_backorders">
                                        <?php
                                        echo '<option value="">' . esc_html__( '— No Change —', 'dokan' ) . '</option>';
                                        ?>
                                        <?php foreach ( wc_get_product_backorder_options() as $key => $value ) : ?>
                                            <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                        <?php endif; ?>
                        <div class="dokan-inline-edit-field-row dokan-clearfix">
                            <label class="dokan-w3">
                                <?php esc_html_e( 'Sold individually?', 'dokan' ); ?>
                            </label>
                            <div class="dokan-w9">
                                <select class="dokan-form-control" name="_sold_individually">
                                    <?php foreach ( $sold_individually as $key => $value ) : ?>
                                        <option value="<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $value ); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="dokan-clearfix quick-edit-submit-wrap">
                    <button type="button" class="dokan-btn dokan-btn-default inline-edit-cancel">
                        <?php esc_html_e( 'Cancel', 'dokan' ); ?>
                    </button>

                    <div class="dokan-right inline-edit-submit-button">
                        <div class="dokan-spinner"></div>
                        <button id="bulk_edit_update_btn" type="submit" class="dokan-btn dokan-btn-default dokan-btn-theme dokan-right">
                            <?php esc_html_e( 'Update', 'dokan' ); ?>
                        </button>
                        <?php
                        wp_nonce_field( 'dokan-bulk-product-edit-action', 'dokan-bulk-product-edit' );
                        ?>
                    </div>
                </div>
            </fieldset>
        </form>
    </td>
</tr>
