<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="table_rate_title"><?php esc_html_e( 'Method Title', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <input id="table_rate_title" value="<?php echo esc_attr( $title ); ?>" name="table_rate_title" placeholder="<?php esc_attr_e( 'Method Title', 'dokan' ); ?>" class="dokan-form-control input-md" type="text">
    </div>
</div>

<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="table_rate_tax_status"><?php esc_html_e( 'Tax Status', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <select name="table_rate_tax_status" class="dokan-on-off dokan-form-control">
            <option value="taxable" <?php selected( $tax_status, 'taxable' ); ?>><?php esc_html_e( 'Taxable', 'dokan' ); ?></option>
            <option value="none" <?php selected( $tax_status, 'none' ); ?>><?php esc_html_e( 'None', 'dokan' ); ?></option>
        </select>
    </div>
</div>

<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="table_rate_prices_include_tax"><?php esc_html_e( 'Tax included in shipping costs', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <select name="table_rate_prices_include_tax" class="dokan-on-off dokan-form-control">
            <option value="yes" <?php selected( $prices_include_tax, 'yes' ); ?>><?php esc_html_e( 'Yes, I will enter costs below inclusive of tax', 'dokan' ); ?></option>
            <option value="no" <?php selected( $prices_include_tax, 'no' ); ?>><?php esc_html_e( 'No, I will enter costs below exclusive of tax', 'dokan' ); ?></option>
        </select>
    </div>
</div>

<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="table_rate_order_handling_fee"><?php esc_html_e( 'Handling Fee', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <input id="table_rate_order_handling_fee" value="<?php echo esc_attr( $order_handling_fee ); ?>" name="table_rate_order_handling_fee" placeholder="<?php esc_attr_e( 'n/a', 'dokan' ); ?>" class="dokan-form-control input-md" type="number">
    </div>
</div>

<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="table_rate_max_shipping_cost"><?php esc_html_e( 'Maximum Shipping Cost', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <input id="table_rate_max_shipping_cost" value="<?php echo esc_attr( $max_shipping_cost ); ?>" name="table_rate_max_shipping_cost" placeholder="<?php esc_attr_e( 'n/a', 'dokan' ); ?>" class="dokan-form-control input-md" type="text">
    </div>
</div>

<h3 class="dokan-text-left"><?php esc_html_e( 'Rates', 'dokan' ); ?></h3>
<p class="dokan-text-left"><?php esc_html_e( 'This is where you define your table rates which are applied to an order.', 'dokan' ); ?></p>

<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="table_rate_calculation_type"><?php esc_html_e( 'Calculation Type', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <select name="table_rate_calculation_type" id="dokan_table_rate_calculation_type" class="dokan-on-off dokan-form-control">
            <option value=""><?php esc_html_e( 'Per order', 'dokan' ); ?></option>
            <option value="item" <?php selected( $calculation_type, 'item' ); ?>><?php esc_html_e( 'Calculated rates per item', 'dokan' ); ?></option>
            <option value="line" <?php selected( $calculation_type, 'line' ); ?>><?php esc_html_e( 'Calculated rates per line item', 'dokan' ); ?></option>
            <option value="class" <?php selected( $calculation_type, 'class' ); ?>><?php esc_html_e( 'Calculated rates per shipping class', 'dokan' ); ?></option>
        </select>
    </div>
</div>

<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="dokan_table_rate_handling_fee"><?php esc_html_e( 'Handling Fee Per [item_label]', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <input id="dokan_table_rate_handling_fee" value="<?php echo esc_attr( $handling_fee ); ?>" name="table_rate_handling_fee" placeholder="<?php esc_attr_e( 'n/a', 'dokan' ); ?>" class="dokan-form-control input-md" type="text">
    </div>
</div>

<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="dokan_table_rate_min_cost"><?php esc_html_e( 'Minimum Cost Per [item_label]', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <input id="dokan_table_rate_min_cost" value="<?php echo esc_attr( $min_cost ); ?>" name="table_rate_min_cost" placeholder="<?php esc_attr_e( 'n/a', 'dokan' ); ?>" class="dokan-form-control input-md" type="text">
    </div>
</div>

<div class="dokan-form-group">
    <label class="dokan-w3 dokan-control-label" for="dokan_table_rate_max_cost"><?php esc_html_e( 'Maximum Cost Per [item_label]', 'dokan' ); ?></label>
    <div class="dokan-w5 dokan-text-left">
        <input id="dokan_table_rate_max_cost" value="<?php echo esc_attr( $max_cost ); ?>" name="table_rate_max_cost" placeholder="<?php esc_attr_e( 'n/a', 'dokan' ); ?>" class="dokan-form-control input-md" type="text">
    </div>
</div>
