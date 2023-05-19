<?php
use \WeDevs\DokanPro\Modules\Germanized\Helper;
// todo: check product type, skip if product type is auction product, same hook is called from auction product also

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

$_product       = wc_get_product( $post_id );
$_gzd_product   = wc_gzd_get_product( $_product );

$sale_price_lable_options   = array_merge( array( '-1' => __( 'Select Price Label', 'dokan' ) ), WC_germanized()->price_labels->get_labels() );
$unit_options               = array_merge( array( '-1' => __( 'Select Price Label', 'dokan' ) ), WC_germanized()->units->get_units() );

$age_select     = wc_gzd_get_age_verification_min_ages_select();
$_free_shipping = get_post_meta( $post_id, '_free_shipping', true );

$delivery_times         = array( '-1' => __( 'Select Delivery Time', 'dokan' ) ) + Helper::get_terms( 'product_delivery_time', 'id' );
$product_delivery_time  = $_gzd_product->get_delivery_time();
$delivery_time          = is_object( $product_delivery_time ) ? $product_delivery_time->term_id : '';

$_unit_price_auto = get_post_meta( $post_id, '_unit_price_auto', true );
?>

<div class="show_if_simple show_if_external show_if_variable">
    <div id="dokan-germanized-options" class="dokan-germanized-options dokan-edit-row dokan-clearfix">
        <div class="dokan-section-heading" data-togglehandler="dokan_germanized_options">
            <h2><i class="far fa-list-alt" aria-hidden="true"></i> <?php esc_attr_e( 'EU Compliance Fields', 'dokan' ); ?><span class=""></h2>
            <p class=""><?php esc_attr_e( 'Manage extra EU compliance fields for this product.', 'dokan' ); ?></p>

            <a href="#" class="dokan-section-toggle">
                <i class="fas fa-sort-down fa-flip-vertical" aria-hidden="true"></i>
            </a>

            <div class="dokan-clearfix">
            </div>
        </div>
        <div class="dokan-section-content">

            <div class="show_if_simple show_if_external show_if_variable">

                <div class="dokan-form-group content-half-part">
                    <label for="_sale_price_label" class="form-label">
                        <?php esc_html_e( 'Sale Label', 'dokan' ); ?> &nbsp;
                        <?php echo Helper::display_help_tips( esc_attr__( 'If the product is on sale you may want to show a price label right before outputting the old price to inform the customer.', 'dokan' ) ); ?>
                    </label>
                    <div class="dokan-input-group">
                        <?php
                        dokan_post_input_box(
                            $post_id, '_sale_price_label', array(
                                'class'     => 'dokan_sale_price_label dokan-form-control',
                                'options'   => $sale_price_lable_options,
                            ), 'select'
                        );
                        ?>
                    </div>
                </div>

                <div class="dokan-form-group content-half-part">
                    <label for="_sale_price_regular_label" class="form-label">
                        <?php esc_html_e( 'Sale Regular Label', 'dokan' ); ?> &nbsp;
                        <?php echo Helper::display_help_tips( esc_attr__( 'If the product is on sale you may want to show a price label right before outputting the new price to inform the customer.', 'dokan' ) ); ?>
                    </label>
                    <div class="dokan-input-group">
                        <?php
                        dokan_post_input_box(
                            $post_id, '_sale_price_regular_label', array(
                                'class'     => 'dokan_sale_price_regular_label dokan-form-control',
                                'options'   => $sale_price_lable_options,
                            ), 'select'
                        );
                        ?>
                    </div>
                </div>

                <div class="dokan-clearfix"></div>

                <div class="dokan-form-group content-half-part">
                    <label for="_unit" class="form-label">
                        <?php esc_html_e( 'Unit', 'dokan' ); ?> &nbsp;
                        <?php echo Helper::display_help_tips( esc_attr__( 'Needed if selling on a per unit basis', 'dokan' ) ); ?>
                    </label>
                    <div class="dokan-input-group">
                        <?php
                        dokan_post_input_box(
                            $post_id, '_unit', array(
                                'class'     => 'dokan_unit dokan-form-control',
                                'options'   => $unit_options,
                            ), 'select'
                        );
                        ?>
                    </div>
                </div>

                <div class="dokan-form-group content-half-part">
                    <label for="_min_age" class="form-label">
                        <?php esc_html_e( 'Minimum Age', 'dokan' ); ?> &nbsp;
                        <?php echo Helper::display_help_tips( esc_attr__( 'Adds an age verification checkbox while purchasing this product.', 'dokan' ) ); ?>
                    </label>
                    <div class="dokan-input-group">
                        <?php
                        dokan_post_input_box(
                            $post_id, '_min_age', array(
                                'class' => 'dokan_min_age dokan-form-control',
                                'options' => $age_select,
                            ), 'select'
                        );
                        ?>
                    </div>
                </div>

                <div class="dokan-form-group content-half-part">
                    <label for="_unit_product" class="form-label">
                        <?php esc_html_e( 'Product Units', 'dokan' ); ?> &nbsp;
                        <?php echo Helper::display_help_tips( esc_attr__( 'Number of units included per default product price. Example: 1000 ml.', 'dokan' ) ); ?>
                    </label>
                    <div class="dokan-input-group">
                        <?php dokan_post_input_box( $post_id, '_unit_product', array( 'class' => 'dokan_unit_product' ), 'text' ); ?>
                    </div>
                </div>

                <div class="dokan-form-group content-half-part">
                    <label for="_unit_base" class="form-label">
                        <?php esc_html_e( 'Base Price Units', 'dokan' ); ?>&nbsp;
                        <?php echo Helper::display_help_tips( esc_attr__( 'Unit price units. Example unit price: 0,99 € / 100 ml. Insert 100 as unit price unit amount.', 'dokan' ) ); ?>
                    </label>
                    <div class="dokan-input-group">
                        <?php dokan_post_input_box( $post_id, '_unit_base', array( 'class' => 'dokan_unit_base' ), 'text' ); ?>
                    </div>
                </div>

                <div class="dokan-clearfix"></div>
                <hr>
            </div>

            <?php
            // Show delivery time selection fallback if is virtual but delivery time should be visible on product
            $types = get_option( 'woocommerce_gzd_display_delivery_time_hidden_types', array() );
            if ( ! $_product->is_virtual() || ! in_array( 'virtual', $types, true ) ) {
                ?>
                <div class="hide_if_virtual">
                    <div class="dokan-form-group content-half-part">
                        <label for="_delivery_time" class="form-label">
                            <?php esc_html_e( 'Delivery Time', 'dokan' ); ?> &nbsp;
                            <?php echo Helper::display_help_tips( esc_attr__( 'Search for a delivery time', 'dokan' ) ); ?>
                        </label>
                        <div class="dokan-input-group">
                            <select id="_delivery_time" name="delivery_time" class="dokan-form-control dokan-select2">
                                <?php foreach ( $delivery_times as $value => $label ) { ?>
                                    <option
                                        value="<?php echo esc_attr( $value ); ?>" <?php selected( $value, $delivery_time, true ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                    <div class="dokan-form-group content-half-part">
                        <label>
                            <input type="checkbox" <?php checked( $_free_shipping, 'yes' ); ?>
                            class="dokan_free_shipping" name="_free_shipping" id="_free_shipping" value="yes">
                            <?php esc_html_e( 'Free shipping?', 'dokan' ); ?>&nbsp;
                            <?php echo Helper::display_help_tips( esc_attr__( 'This option disables the "plus shipping costs" notice on product page.', 'dokan' ) ); ?>
                        </label>
                    </div>

                    <div class="dokan-clearfix"></div>
                    <hr />
                </div>
                <?php
            }
            ?>

            <div class="show_if_simple show_if_external">
                <?php if ( function_exists( 'WC_germanized' ) && WC_germanized()->is_pro() ) : ?>
                    <div class="dokan-form-group">
                        <label>
                            <input type="checkbox" <?php checked( $_unit_price_auto, 'yes' ); ?>
                            class="dokan_unit_price_auto" name="_unit_price_auto" id="_unit_price_auto" value="yes"> <?php esc_html_e( 'Calculation', 'dokan' ); ?>&nbsp;
                            <?php echo Helper::display_help_tips( esc_attr__( 'Calculate base prices automatically.', 'dokan' ) ); ?>
                        </label>
                    </div>

                    <div class="dokan-clearfix"></div>
                <?php endif; ?>

                <div class="dokan-form-group content-half-part">
                    <label for="_unit_price_regular" class="form-label">
                        <?php esc_html_e( 'Regular Unit Price', 'dokan' ); ?>
                    </label>
                    <div class="dokan-input-group">
                        <span class="dokan-input-group-addon"><?php echo esc_html( get_woocommerce_currency_symbol() ); ?></span>
                        <?php
                        dokan_post_input_box(
                            $post_id, '_unit_price_regular', array(
                                'class' => 'dokan_unit_price_regular',
                                'placeholder' => __( '0.00', 'dokan' ),
                            ), 'text'
                        );
                        ?>
                    </div>
                </div>

                <div class="dokan-form-group content-half-part">
                    <label for="_unit_price_sale" class="form-label">
                        <?php esc_html_e( 'Sale Unit Price', 'dokan' ); ?>
                    </label>
                    <div class="dokan-input-group">
                        <span class="dokan-input-group-addon"><?php echo esc_html( get_woocommerce_currency_symbol() ); ?></span>
                        <?php
                        dokan_post_input_box(
                            $post_id, '_unit_price_sale', array(
                                'class' => 'dokan_unit_price_sale',
                                'placeholder' => __( '0.00', 'dokan' ),
                            ), 'text'
                        );
                        ?>
                    </div>
                </div>

                <div class="dokan-clearfix"></div>
                <hr />
            </div>

            <?php if ( function_exists( 'WC_trusted_shops' ) && WC_trusted_shops()->trusted_shops->is_enabled() ) : ?>
                <div class="show_if_simple show_if_external show_if_variable">

                <div class="dokan-form-group content-half-part">
                    <label for="_ts_gtin" class="form-label">
                        <?php esc_html_e( 'GTIN', 'dokan' ); ?> &nbsp;
                        <?php echo Helper::display_help_tips( esc_attr_x( 'ID that allows your products to be identified worldwide. If you want to display your Trusted Shops Product Reviews in Google Shopping and paid Google adverts, Google needs the GTIN.', 'trusted-shops', 'dokan' ) ); ?>
                    </label>
                    <div class="dokan-input-group">
                        <?php
                        dokan_post_input_box(
                            $post_id, '_ts_gtin', array(
                                'class' => 'dokan_ts_gtin',
                                'placeholder' => '',
                            ), 'text'
                        );
                        ?>
                    </div>
                </div>

                <div class="dokan-form-group content-half-part">
                    <label for="_ts_mpn" class="form-label">
                        <?php esc_html_e( 'MPN', 'dokan' ); ?> &nbsp;
                        <?php echo Helper::display_help_tips( esc_attr_x( 'If you don\'t have a GTIN for your products, you can pass the brand name and the MPN on to Google to use the Trusted Shops Google Integration.', 'trusted-shops', 'dokan' ) ); ?>
                    </label>
                    <div class="dokan-input-group">
                        <?php
                        dokan_post_input_box(
                            $post_id, '_ts_mpn', array(
                                'class' => 'dokan_ts_mpn',
                                'placeholder' => '',
                            ), 'text'
                        );
                        ?>
                    </div>
                </div>

                <div class="dokan-clearfix"></div>
                <hr>
            </div>
            <?php endif; ?>

            <div class="dokan-form-group">
                <label for="_mini_desc" class="form-label">
                    <?php esc_html_e( 'Optional Mini Description', 'dokan' ); ?> &nbsp;
                    <?php echo Helper::display_help_tips( esc_attr__( 'This content will be shown as short product description within checkout and emails.', 'dokan' ) ); ?>
                </label>
                <?php
                wp_editor(
                    htmlspecialchars_decode( get_post_meta( $post_id, '_mini_desc', true ) ), 'dokan_product_mini_desc', array(
                        'textarea_name' => '_mini_desc',
                        'textarea_rows' => 3,
                        'media_buttons' => false,
                        'editor_height' => 50,
                        'quicktags' => false,
                        'teeny' => true,
                        'editor_class' => 'post_excerpt',
                    )
                );
                ?>
            </div>

        </div>
    </div>
</div>
