<table id="dokan_shipping_rates" class="shippingrows widefat" cellspacing="0" style="position:relative;">
    <thead>
    <tr>
        <th class="check-column"><input type="checkbox"></th>
        <th>
            <?php esc_html_e( 'Condition', 'dokan' ); ?>
            <i class="fas fa-question-circle tips"
                data-original-title="<?php esc_attr_e( 'On what condition must the rule be applied.', 'dokan' ); ?>"></i>
        </th>
        <th>
            <?php esc_html_e( 'Min', 'dokan' ); ?>
            <i class="fas fa-question-circle tips"
                data-original-title="<?php esc_attr_e( 'Minimum condition value, leave blank for no limit. Travel time based in minutes.', 'dokan' ); ?>"></i>
        </th>
        <th>
            <?php esc_html_e( 'Max', 'dokan' ); ?>
            <i class="fas fa-question-circle tips"
                data-original-title="<?php esc_attr_e( 'Maximum condition value, leave blank for no limit. Travel time based in minutes.', 'dokan' ); ?>"></i>
        </th>
        <th class="cost">
            <?php esc_html_e( 'Base cost', 'dokan' ); ?>
            <i class="fas fa-question-circle tips"
                data-original-title="<?php esc_attr_e( 'Base cost for rule, exluding tax. Other calculations will be added on top of this cost.', 'dokan' ); ?>"></i>
        </th>
        <th class="cost">
            <?php esc_html_e( 'Cost Per Distance / Minute', 'dokan' ); ?>
            <i class="fas fa-question-circle tips"
                data-original-title="<?php esc_attr_e( 'Cost per distance unit, or cost per minute for total travel time, excluding tax. Will be added to Base cost.', 'dokan' ); ?>"></i>
        </th>
        <th class="cost cost_per_item">
            <?php esc_html_e( 'Handling Fee', 'dokan' ); ?>
            <i class="fas fa-question-circle tips"
                data-original-title="<?php esc_attr_e( 'Fee excluding tax. Enter an amount, e.g. 2.50, or a percentage, e.g. 5%. Will be added to Base cost.', 'dokan' ); ?>"></i>
        </th>
        <th width="1%" class="checkbox">
            <?php esc_html_e( 'Break', 'dokan' ); ?>
            <i class="fas fa-question-circle tips"
                data-original-title="<?php esc_attr_e( 'Check to not continue processing rules below the selected rule.', 'dokan' ); ?>"></i>
        </th>
        <th width="1%" class="checkbox">
            <?php esc_html_e( 'Abort', 'dokan' ); ?>
            <i class="fas fa-question-circle tips"
                data-original-title="<?php esc_attr_e( 'Check to disable the shipping method if the rule matches.', 'dokan' ); ?>"></i>
        </th>
    </tr>
    </thead>
    <tfoot>
    <tr>
        <th colspan="2">
            <button type="button" class="add-table-rate dokan-btn dokan-btn-danger dokan-btn-theme"><?php esc_html_e( 'Add Distance Rule', 'dokan' ); ?></button>
        </th>
        <th colspan="9">
            <span class="description"><?php esc_html_e( 'Define your distance rates here.', 'dokan' ); ?></span>
            <button type="button" class="dupe-table-rate dokan-btn dokan-btn-danger dokan-btn-theme"><?php esc_html_e( 'Duplicate selected rows', 'dokan' ); ?></button>
            <button type="button" class="remove-distance-rate dokan-btn dokan-btn-danger dokan-btn-theme"><?php esc_html_e( 'Delete selected rows', 'dokan' ); ?></button>
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
        <td>
            <select class="dokan-form-control select" name="rate_condition[{{{ data.index }}}]" style="min-width:100px;">
                <option value="distance" <# if ( "distance" === data.rate.rate_condition ) { #>selected="selected"<# } #>><?php esc_html_e( 'Distance', 'dokan' ); ?></option>
                <option value="time" <# if ( "time" === data.rate.rate_condition ) { #>selected="selected"<# } #>><?php esc_html_e( 'Total Travel Time', 'dokan' ); ?></option>
                <option value="weight" <# if ( "weight" === data.rate.rate_condition ) { #>selected="selected"<# } #>><?php esc_html_e( 'Weight', 'dokan' ); ?></option>
                <option value="total" <# if ( "total" === data.rate.rate_condition ) { #>selected="selected"<# } #>><?php esc_html_e( 'Order Total', 'dokan' ); ?></option>
                <option value="quantity" <# if ( "quantity" === data.rate.rate_condition ) { #>selected="selected"<# } #>><?php esc_html_e( 'Quantity', 'dokan' ); ?></option>
            </select>
        </td>
        <td class="minmax">
            <input type="text" class="text dokan-form-control wc_input_price" value="{{{ data.rate.rate_min }}}" name="rate_min[{{{ data.index }}}]" placeholder="<?php esc_attr_e( 'n/a', 'dokan' ); ?>" size="4" />
        </td>
        <td class="minmax">
            <input type="text" class="text dokan-form-control wc_input_price" value="{{{ data.rate.rate_max }}}" name="rate_max[{{{ data.index }}}]" placeholder="<?php esc_attr_e( 'n/a', 'dokan' ); ?>" size="4" />
        </td>

        <td class="cost">
            <input type="text" class="text dokan-form-control" value="{{{ data.rate.rate_cost }}}" name="rate_cost[{{{ data.index }}}]" placeholder="<?php esc_attr_e( '0', 'dokan' ); ?>" size="4" />
        </td>
        <td class="cost">
            <input type="text" class="text dokan-form-control" value="{{{ data.rate.rate_cost_unit }}}" name="rate_cost_unit[{{{ data.index }}}]" placeholder="<?php esc_attr_e( '0', 'dokan' ); ?>" size="4" />
        </td>
        <td class="cost">
            <input type="text" class="text dokan-form-control" value="{{{ data.rate.rate_fee }}}" name="rate_fee[{{{ data.index }}}]" placeholder="<?php esc_attr_e( '0', 'dokan' ); ?>" size="4" />
        </td>

        <td width="1%" class="checkbox"><input class="checkbox" type="checkbox" <# if ( '1' === data.rate.rate_break ) { #>checked="checked"<# } #> class="checkbox" name="rate_break[{{{ data.index }}}]" /></td>
        <td width="1%" class="checkbox"><input class="checkbox" type="checkbox" <# if ( '1' === data.rate.rate_abort ) { #>checked="checked"<# } #> class="checkbox" name="rate_abort[{{{ data.index }}}]" /></td>
    </tr>
</script>
<?php
wc_prices_include_tax();
