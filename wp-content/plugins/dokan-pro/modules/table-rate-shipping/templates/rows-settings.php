<table id="dokan_shipping_rates" class="shippingrows widefat" cellspacing="0" style="position:relative;">
    <thead>
    <tr>
        <th class="check-column"><input type="checkbox"></th>
        <?php if ( is_array( $shipping_classes ) && count( $shipping_classes ) ) : ?>
            <th>
                <?php esc_html_e( 'Shipping Class', 'dokan' ); ?>
                <a class="tips" data-original-title="<?php esc_attr_e( 'Shipping class this rate applies to.', 'dokan' ); ?>">[?]</a>
            </th>
        <?php endif; ?>
        <th>
            <?php esc_html_e( 'Condition', 'dokan' ); ?>
            <a class="tips" data-original-title="<?php esc_attr_e( 'Condition vs. destination.', 'dokan' ); ?>">[?]</a>
        </th>
        <th>
            <?php esc_html_e( 'Min&ndash;Max', 'dokan' ); ?>
            <a class="tips" data-original-title="<?php esc_attr_e( 'Bottom and top range for the selected condition.', 'dokan' ); ?>">[?]</a>
        </th>
        <th width="1%" class="checkbox">
            <?php esc_html_e( 'Break', 'dokan' ); ?>
            <a class="tips" data-original-title="<?php esc_attr_e( 'Break at this point. For per-order rates, no rates other than this will be offered. For calculated rates, this will stop any further rates being matched.', 'dokan' ); ?>">[?]</a>
        </th>
        <th width="1%" class="checkbox">
            <?php esc_html_e( 'Abort', 'dokan' ); ?>
            <a class="tips" data-original-title="<?php esc_attr_e( 'Enable this option to disable all rates/this shipping method if this row matches any item/line/class being quoted.', 'dokan' ); ?>">[?]</a>
        </th>
        <th class="cost">
            <?php esc_html_e( 'Row cost', 'dokan' ); ?>
            <a class="tips" data-original-title="<?php esc_attr_e( 'Cost for shipping the order, including tax.', 'dokan' ); ?>">[?]</a>
        </th>
        <th class="cost cost_per_item">
            <?php esc_html_e( 'Item cost', 'dokan' ); ?>
            <a class="tips" data-original-title="<?php esc_attr_e( 'Cost per item, including tax.', 'dokan' ); ?>">[?]</a>
        </th>
        <th class="cost cost_per_weight">
            <?php echo esc_html( get_option( 'woocommerce_weight_unit' ) . ' ' . __( 'cost', 'dokan' ) ); ?>
            <a class="tips" data-original-title="<?php esc_attr_e( 'Cost per weight unit.', 'dokan' ); ?>">[?]</a>
        </th>
        <th class="cost cost_percent">
            <?php esc_html_e( '% cost', 'dokan' ); ?>
            <a class="tips" data-original-title="<?php esc_attr_e( 'Percentage of total to charge.', 'dokan' ); ?>">[?]</a></th>
        <th class="shipping_label">
            <?php esc_html_e( 'Label', 'dokan' ); ?>
            <a class="tips" data-original-title="<?php esc_attr_e( 'Label for the shipping method which the user will be presented.', 'dokan' ); ?>">[?]</a>
        </th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <th colspan="2">
            <button type="button" class="add-table-rate dokan-btn dokan-btn-danger dokan-btn-theme"><?php esc_html_e( 'Add Shipping Rate', 'dokan' ); ?></button>
        </th>
        <th colspan="9">
            <span class="description"><?php esc_html_e( 'Define your table rates here in order of priority.', 'dokan' ); ?></span>
            <button type="button" class="dupe-table-rate dokan-btn dokan-btn-danger dokan-btn-theme"><?php esc_html_e( 'Duplicate selected rows', 'dokan' ); ?></button>
            <button type="button" class="remove-table-rate dokan-btn dokan-btn-danger dokan-btn-theme"><?php esc_html_e( 'Delete selected rows', 'dokan' ); ?></button>
        </th>
    </tr>
    </tfoot>
    <tbody class="table_rates" data-rates="<?php echo $normalized_rates; ?>"></tbody>
</table>
<script type="text/template" id="tmpl-table-rate-shipping-row-template">
    <tr class="table_rate">
        <td class="check-column">
            <input type="checkbox" name="select" />
            <input type="hidden" class="rate_id" name="rate_id[{{{ data.index }}}]" value="{{{ data.rate.rate_id }}}" />
        </td>
        <?php if ( is_array( $shipping_classes ) && count( $shipping_classes ) ) : ?>
            <td>
                <select class="dokan-form-control select" name="shipping_class[{{{ data.index }}}]" style="min-width:100px;">
                    <option value="" <# if ( "" === data.rate.rate_class ) { #>selected="selected"<# } #>><?php esc_html_e( 'Any class', 'dokan' ); ?></option>
                    <option value="0" <# if ( "0" === data.rate.rate_class ) { #>selected="selected"<# } #>><?php esc_html_e( 'No class', 'dokan' ); ?></option>
                    <?php foreach ( $shipping_classes as $class ) : ?>
                        <option value="<?php echo esc_attr( $class->term_id ); ?>" <# if ( "<?php echo esc_attr( $class->term_id ); ?>" === data.rate.rate_class ) { #>selected="selected"<# } #>><?php echo esc_html( $class->name ); ?></option>
                    <?php endforeach; ?>
                </select>
            </td>
        <?php endif; ?>
        <td>
            <select class="dokan-form-control select" name="shipping_condition[{{{ data.index }}}]" style="min-width:100px;">
                <option value="" <# if ( "" === data.rate.rate_condition ) { #>selected="selected"<# } #>><?php esc_html_e( 'None', 'dokan' ); ?></option>
                <option value="price" <# if ( "price" === data.rate.rate_condition ) { #>selected="selected"<# } #>><?php esc_html_e( 'Price', 'dokan' ); ?></option>
                <option value="weight" <# if ( "weight" === data.rate.rate_condition ) { #>selected="selected"<# } #>><?php esc_html_e( 'Weight', 'dokan' ); ?></option>
                <option value="items" <# if ( "items" === data.rate.rate_condition ) { #>selected="selected"<# } #>><?php esc_html_e( 'Item count', 'dokan' ); ?></option>
                <?php if ( count( $shipping_classes ) ) : ?>
                    <option value="items_in_class" <# if ( "items_in_class" === data.rate.rate_condition ) { #>selected="selected"<# } #>><?php esc_html_e( 'Item count (same class)', 'dokan' ); ?></option>
                <?php endif; ?>
            </select>
        </td>
        <td class="minmax">
            <input type="text" class="dokan-form-control text" value="{{{ data.rate.rate_min }}}" name="shipping_min[{{{ data.index }}}]" placeholder="<?php esc_attr_e( 'n/a', 'dokan' ); ?>" size="4" /><input type="text" class="text" value="{{{ data.rate.rate_max }}}" name="shipping_max[{{{ data.index }}}]" placeholder="<?php esc_attr_e( 'n/a', 'dokan' ); ?>" size="4" />
        </td>
        <td width="1%" class="checkbox"><input type="checkbox" <# if ( '1' === data.rate.rate_priority ) { #>checked="checked"<# } #> class="checkbox" name="shipping_priority[{{{ data.index }}}]" /></td>
        <td width="1%" class="checkbox"><input type="checkbox" <# if ( '1' === data.rate.rate_abort ) { #>checked="checked"<# } #> class="checkbox" name="shipping_abort[{{{ data.index }}}]" /></td>
        <td colspan="4" class="abort_reason">
            <input type="text" class="dokan-form-control text" value="{{{ data.rate.rate_abort_reason }}}" placeholder="<?php esc_attr_e( 'Optional abort reason text', 'dokan' ); ?>" name="shipping_abort_reason[{{{ data.index }}}]" />
        </td>
        <td class="cost">
            <input type="text" class="dokan-form-control text" value="{{{ data.rate.rate_cost }}}" name="shipping_cost[{{{ data.index }}}]" placeholder="<?php esc_attr_e( '0', 'dokan' ); ?>" size="4" />
        </td>
        <td class="cost cost_per_item">
            <input type="text" class="dokan-form-control text" value="{{{ data.rate.rate_cost_per_item }}}" name="shipping_per_item[{{{ data.index }}}]" placeholder="<?php esc_attr_e( '0', 'dokan' ); ?>" size="4" />
        </td>
        <td class="cost cost_per_weight">
            <input type="text" class="dokan-form-control text" value="{{{ data.rate.rate_cost_per_weight_unit }}}" name="shipping_cost_per_weight[{{{ data.index }}}]" placeholder="<?php esc_attr_e( '0', 'dokan' ); ?>" size="4" />
        </td>
        <td class="cost cost_percent">
            <input type="text" class="dokan-form-control text" value="{{{ data.rate.rate_cost_percent }}}" name="shipping_cost_percent[{{{ data.index }}}]" placeholder="<?php esc_attr_e( '0', 'dokan' ); ?>" size="4" />
        </td>
        <td class="shipping_label">
            <input type="text" class="dokan-form-control text" value="{{{ data.rate.rate_label }}}" name="shipping_label[{{{ data.index }}}]" size="8" />
        </td>
    </tr>
</script>
<?php
wc_prices_include_tax();
