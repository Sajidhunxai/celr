<?php
use \WeDevs\DokanPro\Modules\Germanized\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$_product           = wc_get_product( $variation );
$_parent            = wc_get_product( $_product->get_parent_id() );
$gzd_product        = wc_gzd_get_product( $_product );
$gzd_parent_product = wc_gzd_get_product( $_parent );
$product_delivery_time = $gzd_product->get_delivery_time( 'edit' );
$delivery_time      = is_object( $product_delivery_time ) ? $product_delivery_time->term_id : '';
$variation_delivery_times = array( '' => __( 'Same as Parent', 'dokan' ) ) + Helper::get_terms( 'product_delivery_time', 'id' );


// get trusted source fields
$variation_meta   = get_post_meta( $_product->get_id() );
$variation_data   = array();
$variation_fields = array(
    '_ts_gtin' => '',
    '_ts_mpn'  => '',
);

foreach ( $variation_fields as $field => $value ) {
    $variation_data[ $field ] = isset( $variation_meta[ $field ][0] ) ? maybe_unserialize( $variation_meta[ $field ][0] ) : $value;
}
?>
<div class="dokan-germanized-options dokan-clearfix">
    <div class="variable_pricing_labels">
        <div class="dokan-form-group content-half-part">
            <label for="variable_sale_price_label_<?php echo $loop; ?>" class="form-label"><?php esc_html_e( 'Sale Label', 'dokan' ); ?></label>
            <select id="variable_sale_price_label_<?php echo $loop; ?>" name="variable_sale_price_label[<?php echo $loop; ?>]" class="dokan-form-control">
                <option value="" <?php selected( empty( $gzd_product->get_sale_price_label( 'edit' ) ), true ); ?>><?php esc_html_e( 'Same as Parent', 'dokan' ); ?></option>
                <?php foreach ( WC_germanized()->price_labels->get_labels() as $key => $value ) : ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key === $gzd_product->get_sale_price_label( 'edit' ), true ); ?>><?php echo esc_html( $value ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="dokan-form-group content-half-part">
            <label for="variable_sale_price_regular_label<?php echo $loop; ?>" class="form-label"><?php esc_html_e( 'Sale Regular Label', 'dokan' ); ?></label>
            <select id="variable_sale_price_regular_label<?php echo $loop; ?>" name="variable_sale_price_regular_label[<?php echo $loop; ?>]" class="dokan-form-control">
                <option value="" <?php selected( empty( $gzd_product->get_sale_price_regular_label( 'edit' ) ), true ); ?>><?php esc_html_e( 'Same as Parent', 'dokan' ); ?></option>
                <?php foreach ( WC_germanized()->price_labels->get_labels() as $key => $value ) : ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key === $gzd_product->get_sale_price_regular_label( 'edit' ), true ); ?>><?php echo esc_html( $value ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="variable_pricing_unit">
        <input type="hidden" name="variable_parent_unit_product[<?php echo $loop; ?>]" class="wc-gzd-parent-unit_product" value=""/>
        <input type="hidden" name="variable_parent_unit[<?php echo $loop; ?>]" class="wc-gzd-parent-unit" value=""/>
        <input type="hidden" name="variable_parent_unit_base[<?php echo $loop; ?>]" class="wc-gzd-parent-unit_base" value=""/>

        <div class="dokan-form-group content-half-part">
            <label for="variable_unit_product_<?php echo $loop; ?>" class="form-label"><?php esc_html_e( 'Product Units', 'dokan' ); ?><?php echo wc_help_tip( __( 'Number of units included per default product price. Example: 1000 ml. Leave blank to use parent value.', 'dokan' ) ); ?></label>
            <input class="dokan-form-control wc_input_decimal" size="6" type="text"
                id="variable_unit_product_<?php echo $loop; ?>"
                name="variable_unit_product[<?php echo $loop; ?>]"
                value="<?php echo( ! empty( $gzd_product->get_unit_product( 'edit' ) ) ? esc_attr( wc_format_localized_decimal( $gzd_product->get_unit_product( 'edit' ) ) ) : '' ); ?>"
                placeholder="<?php echo esc_attr( wc_format_localized_decimal( $gzd_parent_product->get_unit_product( 'edit' ) ) ); ?>"/>
        </div>
        <div class="dokan-form-group content-half-part">
            <label for="variable_min_age_<?php echo $loop; ?>" class="form-label"><?php esc_html_e( 'Minimum Age', 'dokan' ); ?></label>
            <select name="variable_min_age[<?php echo $loop; ?>]" id="variable_min_age_<?php echo $loop; ?>" class="dokan-form-control">
                <option value="" <?php selected( $gzd_product->get_min_age( 'edit' ) === '', true ); ?>><?php esc_html_e( 'Same as Parent', 'dokan' ); ?></option>
                <?php foreach ( wc_gzd_get_age_verification_min_ages_select() as $key => $value ) : ?>
                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key === (int) $gzd_product->get_min_age( 'edit' ), true ); ?>><?php echo esc_html( $value ); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div class="dokan-clearfix"></div>

    <div class="variable_price_unit_auto">
        <?php if ( WC_germanized()->is_pro() ) : ?>
            <div class="dokan-form-group _unit_price_auto_field">
                <label for="variable_unit_price_auto_<?php echo $loop; ?>" class="form-label"><?php esc_html_e( 'Calculation', 'dokan' ); ?>
                <input class="wc_input_price" id="variable_unit_price_auto_<?php echo $loop; ?>"
                    type="checkbox" name="variable_unit_price_auto[<?php echo $loop; ?>]"
                    value="yes" <?php checked( 'yes', $gzd_product->get_unit_price_auto( 'edit' ) ? 'yes' : 'no' ); ?> />
                <span class="description">
                    <span class="wc-gzd-premium-desc"><?php echo __( 'Calculate unit prices automatically', 'dokan' ); ?></span>
                </span>
                </label>
            </div>
        <?php endif; ?>
        <div class="dokan-form-group content-half-part">
            <label for="variable_unit_price_regular_<?php echo $loop; ?>" class="form-label"><?php echo __( 'Regular Unit Price', 'dokan' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
            <input class="dokan-form-control wc_input_price width_98" size="5" type="text"
                id="variable_unit_price_regular_<?php echo $loop; ?>"
                name="variable_unit_price_regular[<?php echo $loop; ?>]"
                value="<?php echo( ! empty( $gzd_product->get_unit_price_regular( 'edit' ) ) ? esc_attr( wc_format_localized_price( $gzd_product->get_unit_price_regular( 'edit' ) ) ) : '' ); ?>"
                placeholder=""/>
        </div>
        <div class="dokan-form-group content-half-part">
            <label for="variable_unit_price_sale_<?php echo $loop; ?>" class="form-label"><?php echo __( 'Sale Unit Price', 'dokan' ) . ' (' . get_woocommerce_currency_symbol() . ')'; ?></label>
            <input class="dokan-form-control wc_input_price" size="5" type="text"
                id="variable_unit_price_sale_<?php echo $loop; ?>"
                name="variable_unit_price_sale[<?php echo $loop; ?>]"
                value="<?php echo( ! empty( $gzd_product->get_unit_price_sale( 'edit' ) ) ? esc_attr( wc_format_localized_price( $gzd_product->get_unit_price_sale( 'edit' ) ) ) : '' ); ?>"
                placeholder=""/>
        </div>
        <p class="form-row form-row-first wc-gzd-unit-price-disabled-notice notice notice-warning">
            <?php
            // translators: 1) general product data url
            printf( __( 'To enable unit prices on variation level please choose a unit and unit price units within %s.', 'dokan' ), '<a href="#general_product_data" class="wc-gzd-general-product-data-tab">' . __( 'general product data', 'dokan' ) . '</a>' );
            ?>
        </p>
    </div>

    <div class="dokan-clearfix"></div>
    <?php if ( function_exists( 'WC_trusted_shops' ) && WC_trusted_shops()->trusted_shops->is_enabled() ) : ?>
        <div class="variable_gzd_ts_labels">
            <div class="dokan-form-group content-half-part">
                <label for="variable_ts_gtin_<?php echo $loop; ?>" class="form-label"><?php echo esc_attr_x( 'GTIN', 'trusted-shops', 'dokan' ); ?> <?php echo Helper::display_help_tips( _x( 'ID that allows your products to be identified worldwide. If you want to display your Trusted Shops Product Reviews in Google Shopping and paid Google adverts, Google needs the GTIN.', 'trusted-shops', 'dokan' ) ); ?></label>
                <input class="input-text dokan-form-control" type="text" name="variable_ts_gtin[<?php echo $loop; ?>]" value="<?php echo ( ! empty( $variation_data['_ts_gtin'] ) ? esc_attr( $variation_data['_ts_gtin'] ) : '' ); ?>" placeholder="<?php echo esc_attr( wc_ts_get_crud_data( $_parent, '_ts_gtin' ) ); ?>" />
            </div>
            <div class="dokan-form-group content-half-part">
                <label for="variable_ts_mpn_<?php echo $loop; ?>" class="form-label"><?php echo esc_attr_x( 'MPN', 'trusted-shops', 'dokan' ); ?> <?php echo Helper::display_help_tips( _x( 'If you don\'t have a GTIN for your products, you can pass the brand name and the MPN on to Google to use the Trusted Shops Google Integration.', 'trusted-shops', 'dokan' ) ); ?></label>
                <input class="input-text dokan-form-control" type="text" name="variable_ts_mpn[<?php echo $loop; ?>]" value="<?php echo ( ! empty( $variation_data['_ts_mpn'] ) ? esc_attr( $variation_data['_ts_mpn'] ) : '' ); ?>" placeholder="<?php echo esc_attr( wc_ts_get_crud_data( $_parent, '_ts_mpn' ) ); ?>" />
            </div>
        </div>
    <?php endif; ?>

    <div class="variable_shipping_time hide_if_variation_virtual">
        <div class="dokan-form-group">
            <label for="variable_delivery_time_<?php echo $loop; ?>" class="form-label"><?php esc_html_e( 'Delivery Time', 'dokan' ); ?></label>
            <select id="variable_delivery_time_<?php echo $loop; ?>" name="variable_delivery_time[<?php echo $loop; ?>]" class="dokan-form-control" >
                <?php foreach ( $variation_delivery_times as $value => $label ) { ?>
                    <option value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $delivery_time, true ); ?>><?php echo esc_html( $label ); ?></option>
                <?php } ?>
            </select>
        </div>
    </div>

    <div class="dokan-clearfix"></div>

    <div class="variable_cart_mini_desc">
        <div class="dokan-form-group">
            <label for="variable_mini_desc_<?php echo $loop; ?>" class="form-label"><?php esc_html_e( 'Optional Mini Description', 'dokan' ); ?></label>
            <textarea rows="3" style="width: 100%" name="variable_mini_desc[<?php echo $loop; ?>]"
                id="variable_mini_desc_<?php echo $loop; ?>"
                class="variable_mini_desc"><?php echo htmlspecialchars_decode( $gzd_product->get_mini_desc( 'edit' ) ); ?></textarea>
        </div>
    </div>
</div>
