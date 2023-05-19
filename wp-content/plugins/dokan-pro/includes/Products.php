<?php

namespace WeDevs\DokanPro;

use WP_Error;
use WeDevs\Dokan\ProductCategory\Helper;

/**
 * Dokan Pro Product Class
 *
 * @since 2.4
 *
 * @package dokan
 */
class Products {

    /**
     * Holds the inline edit options
     *
     * @var array
     */
    private $inline_edit_options = array();

    /**
     * Load automatically when class initiate
     *
     * @since 2.4
     *
     * @uses actions
     * @uses filters
     */
    public function __construct() {
        add_action( 'dokan_product_edit_after_inventory_variants', array( $this, 'load_shipping_tax_content' ), 10, 2 );
        add_action( 'dokan_product_edit_after_inventory_variants', array( $this, 'load_linked_product_content' ), 15, 2 );
        add_action( 'dokan_product_edit_after_inventory_variants', array( $this, 'load_variations_content' ), 20, 2 );
        add_action( 'dokan_product_edit_after_inventory_variants', array( $this, 'load_lot_discount_content' ), 25, 2 );
        add_action( 'dokan_dashboard_wrap_after', array( $this, 'load_variations_js_template' ), 10, 2 );
        add_action( 'dokan_render_new_product_template', array( $this, 'render_new_product_template' ), 10 );
        add_action( 'woocommerce_product_options_advanced', array( $this, 'add_per_product_commission_options' ), 15 );
        add_action( 'woocommerce_process_product_meta_simple', array( $this, 'save_per_product_commission_options' ), 15 );
        add_action( 'woocommerce_process_product_meta_variable', array( $this, 'save_per_product_commission_options' ), 15 );
        add_action( 'dokan_new_product_added', array( $this, 'set_product_tags' ), 10, 2 );
        add_action( 'dokan_product_updated', array( $this, 'set_product_tags' ) );
        add_action( 'dokan_product_updated', array( $this, 'set_product_type' ), 11 );
        add_action( 'dokan_product_updated', array( $this, 'save_pro_product_data' ), 12 );
        add_action( 'dokan_product_updated', array( $this, 'updated_product_email' ), 20 );
        add_action( 'template_redirect', array( $this, 'handle_duplicate_product' ), 10 );
        add_action( 'dokan_product_dashboard_errors', array( $this, 'display_duplicate_message' ), 10 );
        add_action( 'dokan_product_list_table_after_row', array( $this, 'add_product_inline_edit_form' ), 10, 2 );
        add_action( 'wp_ajax_dokan_product_inline_edit', array( $this, 'product_inline_edit' ) );

        add_filter( 'dokan_product_row_actions', array( $this, 'product_row_action' ), 10, 2 );
        add_filter( 'dokan_update_product_post_data', array( $this, 'save_product_post_data' ), 10 );
        add_filter( 'dokan_product_types', array( $this, 'set_default_product_types' ), 10 );

        add_action( 'dokan_after_linked_product_fields', array( $this, 'group_product_content' ), 10, 2 );
        add_action( 'dokan_product_edit_after_title', array( $this, 'external_product_content' ), 10, 2 );
        add_filter( 'woocommerce_duplicate_product_exclude_meta', array( $this, 'remove_unwanted_meta' ) );

        add_filter( 'dokan_localized_args', array( $this, 'dokan_pro_localized_args' ) );

        //Prevent Duplicate SKU for multiple save from various vendor
        add_action( 'woocommerce_product_duplicate_before_save', [ $this, 'prevent_duplicate_sku' ], 10, 2 );
        add_filter( 'dokan_earning_by_order_item_price', [ $this, 'update_item_price_for_discount' ], 15, 3 );
    }

    /**
     * Update item total total price if have product based discount
     *
     * @param float $get_total
     * @param Obj   $item
     *
     * @since 3.4.0
     *
     * @return float $get_total
     */
    public function update_item_price_for_discount( $get_total, $item, $order ) {
        if ( empty( $item ) ) {
            return $get_total;
        }

        $item_product  = $item->get_product();
        $item_total    = $item->get_total();
        $item_quantity = $item->get_quantity();

        $product_discount = dokan_pro()->coupon->get_product_discount( $item_product, $item_total, $item_quantity );
        $order_discount   = dokan_pro()->coupon->get_order_discount( $order, $item_total );

        return $get_total - $product_discount - $order_discount;
    }

    /**
     * Render New Product Template
     *
     * @since 2.4
     *
     * @param  array $query_vars
     *
     * @return void
     */
    public function render_new_product_template( $query_vars ) {
        if ( isset( $query_vars['new-product'] ) ) {
            dokan_get_template_part( 'products/new-product' );
        }
    }

    /**
     * Load Variation Content
     *
     * @since 2.4
     *
     * @param  object $post
     * @param  integer $post_id
     *
     * @return void
     */
    public function load_variations_content( $post, $post_id ) {
        $_has_attribute       = get_post_meta( $post_id, '_has_attribute', true );
        $_create_variations   = get_post_meta( $post_id, '_create_variation', true );
        $product_attributes   = get_post_meta( $post_id, '_product_attributes', true );
        $attribute_taxonomies = wc_get_attribute_taxonomies();

        dokan_get_template_part(
            'products/product-variation',
            '',
            array(
                'pro'                  => true,
                'post_id'              => $post_id,
                '_has_attribute'       => $_has_attribute,
                '_create_variations'   => $_create_variations,
                'product_attributes'   => $product_attributes,
                'attribute_taxonomies' => $attribute_taxonomies,
            )
        );
    }

    /**
     * Render product lot dicount options
     *
     * @since 2.6
     *
     * @return void
     */
    public function load_lot_discount_content( $post, $post_id ) {
        $_is_lot_discount       = get_post_meta( $post_id, '_is_lot_discount', true );
        $_lot_discount_quantity = get_post_meta( $post_id, '_lot_discount_quantity', true );
        $_lot_discount_amount   = get_post_meta( $post_id, '_lot_discount_amount', true );
        $is_enable_op_discount  = dokan_get_option( 'discount_edit', 'dokan_selling' );
        $is_enable_op_discount  = $is_enable_op_discount ? $is_enable_op_discount : array();

        dokan_get_template_part(
            'products/product-lot-discount',
            '',
            array(
                'pro'                    => true,
                'post_id'                => $post_id,
                '_is_lot_discount'       => $_is_lot_discount,
                '_lot_discount_quantity' => $_lot_discount_quantity,
                '_lot_discount_amount'   => $_lot_discount_amount,
                'is_enable_op_discount'  => $is_enable_op_discount,
            )
        );
    }


    /**
     * Load Variation popup content when edit product
     *
     * @since 2.4
     *
     * @param  object $post
     * @param  integer $post_id
     *
     * @return void
     */
    public function load_variations_js_template( $post, $post_id ) {
        dokan_get_template_part(
            'products/edit/tmpl-add-attribute',
            '',
            array(
                'pro' => true,
                'post_id' => $post_id,
            )
        );
    }

    /**
     * Load Shipping and tax content
     *
     * @since 2.4
     *
     * @param  object $post
     * @param  integer $post_id
     *
     * @return void
     */
    public function load_shipping_tax_content( $post, $post_id ) {
        $user_id                 = dokan_get_current_user_id();
        $processing_time         = dokan_get_shipping_processing_times();
        $_required_tax           = get_post_meta( $post_id, '_required_tax', true );
        $_disable_shipping       = ( get_post_meta( $post_id, '_disable_shipping', true ) ) ? get_post_meta( $post_id, '_disable_shipping', true ) : 'no';
        $_additional_price       = get_post_meta( $post_id, '_additional_price', true );
        $_additional_qty         = get_post_meta( $post_id, '_additional_qty', true );
        $_processing_time        = get_post_meta( $post_id, '_dps_processing_time', true );
        $dps_shipping_type_price = get_user_meta( $user_id, '_dps_shipping_type_price', true );
        $dps_additional_qty      = get_user_meta( $user_id, '_dps_additional_qty', true );
        $dps_pt                  = get_user_meta( $user_id, '_dps_pt', true );
        $classes_options         = $this->get_tax_class_option();
        $porduct_shipping_pt     = ( $_processing_time ) ? $_processing_time : $dps_pt;
        $is_shipping_disabled    = false;

        if ( 'sell_digital' === dokan_pro()->digital_product->get_selling_product_type() ) {
            $is_shipping_disabled = true;
        }

        dokan_get_template_part(
            'products/product-shipping-content',
            '',
            array(
                'pro'                     => true,
                'post'                    => $post,
                'post_id'                 => $post_id,
                'user_id'                 => $user_id,
                'processing_time'         => $processing_time,
                '_required_tax'           => $_required_tax,
                '_disable_shipping'       => $_disable_shipping,
                '_additional_price'       => $_additional_price,
                '_additional_qty'         => $_additional_qty,
                '_processing_time'        => $_processing_time,
                'dps_shipping_type_price' => $dps_shipping_type_price,
                'dps_additional_qty'      => $dps_additional_qty,
                'dps_pt'                  => $dps_pt,
                'classes_options'         => $classes_options,
                'porduct_shipping_pt'     => $porduct_shipping_pt,
                'is_shipping_disabled'    => $is_shipping_disabled,
            )
        );
    }

    /**
     * Render linked product content
     *
     * @since 2.6.6
     *
     * @return void
     */
    public function load_linked_product_content( $post, $post_id ) {
        $upsells_ids = get_post_meta( $post_id, '_upsell_ids', true );
        $crosssells_ids = get_post_meta( $post_id, '_crosssell_ids', true );

        dokan_get_template_part(
            'products/linked-product-content',
            '',
            array(
                'pro'            => true,
                'post'           => $post,
                'post_id'        => $post_id,
                'upsells_ids'    => $upsells_ids,
                'crosssells_ids' => $crosssells_ids,
            )
        );
    }

    /**
     * Get taxes options value
     *
     * @since 2.4
     *
     * @return array
     */
    public function get_tax_class_option() {
        if ( class_exists( 'WC_Tax' ) ) {
            $tax_classes = \WC_Tax::get_tax_classes();
        } else {
            $tax_classes = array_filter( array_map( 'trim', explode( "\n", get_option( 'woocommerce_tax_classes' ) ) ) );
        }

        $classes_options     = [];
        $classes_options[''] = __( 'Standard', 'dokan' );

        if ( $tax_classes ) {
            foreach ( $tax_classes as $class ) {
                $classes_options[ sanitize_title( $class ) ] = esc_html( $class );
            }
        }

        return $classes_options;
    }

    /**
     * Add per product commission options
     *
     * @since 2.4.12
     *
     * @return void
     */
    public function add_per_product_commission_options() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        woocommerce_wp_select(
            array(
                'id'            => '_per_product_admin_commission_type',
                'label'         => __( 'Admin Commission type', 'dokan' ),
                'options'       => dokan_commission_types(),
                'wrapper_class' => 'per-product-commission-type show_if_simple show_if_variable show_if_booking',
                'description'   => __( 'Set the commission type admin will get from this product', 'dokan' ),
                'data_type'     => 'price',
            )
        );

        $product          = wc_get_product( get_the_ID() );
        $commission_type  = $product->get_meta( '_per_product_admin_commission_type' );
        $admin_commission = $product->get_meta( '_per_product_admin_commission' );
        $additional_fee   = $product->get_meta( '_per_product_admin_additional_fee' );
        $wc_input_class   = ( $commission_type === 'flat' || $commission_type === '' ) ? 'wc_input_price' : 'wc_input_decimal';
        $admin_commission = ( $commission_type === 'flat' || $commission_type === '' ) ? wc_format_localized_price( $admin_commission ) : $admin_commission;
        ?>

        <div class="commission show_if_simple show_if_variable show_if_booking">
            <p class="form-field dimensions_field">
                <label for="admin_commission"><?php esc_html_e( 'Admin Commission', 'dokan' ); ?></label>
                <span class="wrapper">
                    <input id="admin_commission" class="input-text <?php echo $wc_input_class; ?>" type="text" name="_per_product_admin_commission" value="<?php echo $admin_commission; ?>">
                    <span class="additional_fee dokan-hide">
                        <?php echo esc_html( '% &nbsp;&nbsp; +' ); ?>
                        <input class="input-text wc_input_price" type="text" name="_per_product_admin_additional_fee" value="<?php echo wc_format_localized_price( $additional_fee ); ?>">
                    </span>
                    <span class="combine-commission-description"></span>
                </span>
            </p>
        </div>

        <style type="text/css">
            .dokan-hide {
                display: none;
            }
            .commission .wrapper input {
                width: 60px;
                float: none;
            }
            span.combine-commission-description {
                margin-left: 5px;
                font-size: 13px;
                font-style: italic;
            }
        </style>

        <script type="text/javascript">
            ;(function($) {
                $(document).ready(function(){
                    $('#_per_product_admin_commission_type').on('change', function() {
                        if ( 'combine' === $(this).val() ) {
                            $('.additional_fee').removeClass('dokan-hide');
                            $('.combine-commission-description').text( dokan_admin.combine_commission_desc );
                            $('input[name=_per_product_admin_commission]').attr('required', true);
                            $('input[name=_per_product_admin_additional_fee]').attr('required', true);
                        } else {
                            $('.additional_fee').addClass('dokan-hide');
                            $('.combine-commission-description').text( dokan_admin.default_commission_desc );
                            $('input[name=_per_product_admin_commission]').removeAttr('required');
                            $('input[name=_per_product_admin_additional_fee]').removeAttr('required');
                        }

                        if ( 'flat' == $(this).val() ) {
                            $('input#admin_commission').removeClass( 'wc_input_decimal' ).addClass( 'wc_input_price' );
                        } else {
                            $('input#admin_commission').removeClass( 'wc_input_price' ).addClass( 'wc_input_decimal' );
                        }
                }).trigger('change');
                });
            })(jQuery);
        </script>
        <?php
    }

    /**
     * Save per product commission options
     *
     * @since 2.4.12
     *
     * @param  integer $post_id
     *
     * @return void
     */
    public static function save_per_product_commission_options( $post_id ) {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        $commission_type  = '';
        $admin_commission = '';
        $additional_fee   = '';

        if ( isset( $_POST['_per_product_admin_commission_type'] ) ) { // phpcs:ignore
            $commission_type = ! empty( $_POST['_per_product_admin_commission_type'] ) ? sanitize_text_field( $_POST['_per_product_admin_commission_type'] ) : 'percentage'; // phpcs:ignore
            update_post_meta( $post_id, '_per_product_admin_commission_type', $commission_type );
        }

        if ( isset( $_POST['_per_product_admin_commission'] ) ) { // phpcs:ignore
            $admin_commission = ( '' === $_POST['_per_product_admin_commission'] ) ? '' : sanitize_text_field( $_POST['_per_product_admin_commission'] ); // phpcs:ignore
        }

        if ( isset( $_POST['_per_product_admin_additional_fee'] ) ) { // phpcs:ignore
            $additional_fee = ( '' === $_POST['_per_product_admin_additional_fee'] ) ? '' : sanitize_text_field( $_POST['_per_product_admin_additional_fee'] ); // phpcs:ignore
        }

        // Combine commission requires both fields to be field.
        if ( 'combine' === $commission_type && ( '' === $admin_commission || '' === $additional_fee ) ) {
            update_post_meta( $post_id, '_per_product_admin_commission', '' );
            update_post_meta( $post_id, '_per_product_admin_additional_fee', '' );
        } else {
            update_post_meta( $post_id, '_per_product_admin_commission', wc_format_decimal( $admin_commission ) );
            update_post_meta( $post_id, '_per_product_admin_additional_fee', wc_format_decimal( $additional_fee ) );
        }
    }

    /**
     * Save extra product data
     *
     * @since  2.5.3
     *
     * @param  integer $post_id
     *
     * @return void
     */
    public function save_pro_product_data( $post_id ) {
        // phpcs:disable WordPress.Security.NonceVerification.Missing
        if ( ! $post_id ) {
            return;
        }

        $post_data = wp_unslash( $_POST );

        $is_virtual   = isset( $post_data['_virtual'] ) ? 'yes' : 'no';
        $product_type = empty( $post_data['product_type'] ) ? 'simple' : $post_data['product_type'];

        // Save lot discount options
        $is_lot_discount = isset( $post_data['_is_lot_discount'] ) ? sanitize_text_field( $post_data['_is_lot_discount'] ) : 'no';
        if ( $is_lot_discount === 'yes' ) {
            $lot_discount_quantity = isset( $post_data['_lot_discount_quantity'] ) ? $post_data['_lot_discount_quantity'] : 0;
            $lot_discount_amount   = isset( $post_data['_lot_discount_amount'] ) ? $post_data['_lot_discount_amount'] : 0;
            if ( $lot_discount_quantity === '0' || $lot_discount_amount === '0' ) {
                update_post_meta( $post_id, '_lot_discount_quantity', $lot_discount_quantity );
                update_post_meta( $post_id, '_lot_discount_amount', $lot_discount_amount );
                update_post_meta( $post_id, '_is_lot_discount', 'no' );
            } else {
                update_post_meta( $post_id, '_lot_discount_quantity', $lot_discount_quantity );
                update_post_meta( $post_id, '_lot_discount_amount', $lot_discount_amount );
                update_post_meta( $post_id, '_is_lot_discount', $is_lot_discount );
            }
        } elseif ( $is_lot_discount === 'no' ) {
            update_post_meta( $post_id, '_lot_discount_quantity', 0 );
            update_post_meta( $post_id, '_lot_discount_amount', 0 );
            update_post_meta( $post_id, '_is_lot_discount', 'no' );
        }

        // Dimensions
        if ( 'no' === $is_virtual ) {
            if ( isset( $post_data['_weight'] ) ) {
                update_post_meta( $post_id, '_weight', ( '' === $post_data['_weight'] ) ? '' : wc_format_decimal( $post_data['_weight'] ) );
            }

            if ( isset( $post_data['_length'] ) ) {
                update_post_meta( $post_id, '_length', ( '' === $post_data['_length'] ) ? '' : wc_format_decimal( $post_data['_length'] ) );
            }

            if ( isset( $post_data['_width'] ) ) {
                update_post_meta( $post_id, '_width', ( '' === $post_data['_width'] ) ? '' : wc_format_decimal( $post_data['_width'] ) );
            }

            if ( isset( $post_data['_height'] ) ) {
                update_post_meta( $post_id, '_height', ( '' === $post_data['_height'] ) ? '' : wc_format_decimal( $post_data['_height'] ) );
            }
        } else {
            update_post_meta( $post_id, '_weight', '' );
            update_post_meta( $post_id, '_length', '' );
            update_post_meta( $post_id, '_width', '' );
            update_post_meta( $post_id, '_height', '' );
        }

        //Save shipping meta data
        update_post_meta( $post_id, '_disable_shipping', isset( $post_data['_disable_shipping'] ) ? $post_data['_disable_shipping'] : 'no' );

        if ( isset( $post_data['_overwrite_shipping'] ) && $post_data['_overwrite_shipping'] === 'yes' ) {
            update_post_meta( $post_id, '_overwrite_shipping', $post_data['_overwrite_shipping'] );
        } else {
            update_post_meta( $post_id, '_overwrite_shipping', 'no' );
        }

        update_post_meta( $post_id, '_additional_price', isset( $post_data['_additional_price'] ) ? $post_data['_additional_price'] : '' );
        update_post_meta( $post_id, '_additional_qty', isset( $post_data['_additional_qty'] ) ? $post_data['_additional_qty'] : '' );
        update_post_meta( $post_id, '_dps_processing_time', isset( $post_data['_dps_processing_time'] ) ? $post_data['_dps_processing_time'] : '' );

        // Save shipping class
        $product_shipping_class = ( isset( $post_data['product_shipping_class'] ) && $post_data['product_shipping_class'] > 0 && 'external' !== $product_type ) ? absint( $post_data['product_shipping_class'] ) : '';
        wp_set_object_terms( $post_id, $product_shipping_class, 'product_shipping_class' );

        // Cross sells and upsells
        $upsells    = isset( $post_data['upsell_ids'] ) ? array_map( 'intval', $post_data['upsell_ids'] ) : array();
        $crosssells = isset( $post_data['crosssell_ids'] ) ? array_map( 'intval', $post_data['crosssell_ids'] ) : array();

        update_post_meta( $post_id, '_upsell_ids', $upsells );
        update_post_meta( $post_id, '_crosssell_ids', $crosssells );

        // Save variations
        if ( 'variable' === $product_type ) {
            dokan_save_variations( $post_id );
        }

        // Save external
        if ( 'external' === $product_type ) {
            update_post_meta( $post_id, '_product_url', isset( $post_data['_product_url'] ) ? $post_data['_product_url'] : '' );
            update_post_meta( $post_id, '_button_text', isset( $post_data['_button_text'] ) ? $post_data['_button_text'] : '' );
        }

        if ( 'grouped' === $product_type && version_compare( WC_VERSION, '2.7', '>' ) ) {
            $product = wc_get_product( $post_id );
            $group_product_ids = isset( $post_data['grouped_products'] ) ? array_filter( array_map( 'intval', (array) $post_data['grouped_products'] ) ) : array();
            $product->set_props( array( 'children' => $group_product_ids ) );
            $product->save();

            //set product type to 'grouped'
            wp_set_object_terms( $post_id, $product_type, 'product_type' );
        }
    }

    /**
     * Set new product tags
     *
     * @since 2.8.4
     *
     * @param int   $product_id
     * @param array $posted_data
     *
     * @return void
     */
    public function set_product_tags( $product_id, $posted_data = [] ) {
        if ( empty( $posted_data ) && ! empty( $_POST ) ) {
            $posted_data = $_POST;
        }

        if ( ! isset( $posted_data['product_tag'] ) || ! is_array( $posted_data['product_tag'] ) ) {
            return;
        }

        // Newly added tags will be string typed data
        $tags = array_filter(
            $posted_data['product_tag'],
            function ( $tag ) {
                return ! absint( $tag );
            }
        );

        if ( ! empty( $tags ) ) {
            $tags_ids = array();

            foreach ( $tags as $tag ) {
                $new_tag = wp_insert_term( $tag, 'product_tag' );

                if ( ! is_wp_error( $new_tag ) ) {
                    $tags_ids[] = $new_tag['term_id'];
                }
            }

            if ( ! empty( $tags_ids ) ) {
                wp_set_object_terms( $product_id, $tags_ids, 'product_tag', true );
            }
        }
    }

    /**
     * Added duplicate row action
     *
     * @since 2.6.3
     *
     * @return void
     */
    public function product_row_action( $row_action, $post ) {
        if ( empty( $post->ID ) ) {
            return $row_action;
        }

        if ( current_user_can( 'dokan_edit_product' ) ) {
            $row_action['quick-edit'] = array(
                'title' => __( 'Quick Edit', 'dokan' ),
                'url'   => '#quick-edit',
                'class' => 'item-inline-edit',
                'other' => 'data-product-id="' . $post->ID . '"',
            );
        }

        $can_duplicate_product = apply_filters( 'dokan_can_duplicate_product', current_user_can( 'dokan_duplicate_product' ) );
        $vendor_can_duplicate_product = dokan_get_option( 'vendor_duplicate_product', 'dokan_selling', 'on' );

        if ( $can_duplicate_product && 'on' === $vendor_can_duplicate_product ) {
            $row_action['duplicate'] = array(
                'title' => __( 'Duplicate', 'dokan' ),
                'url'   => wp_nonce_url( add_query_arg( array( 'action' => 'dokan-duplicate-product', 'product_id' => $post->ID, ), dokan_get_navigation_url('products') ), 'dokan-duplicate-product' ), // phpcs:ignore
                'class' => 'duplicate',
            );
        }

        return $row_action;
    }

    /**
     * Handle duplicate product action
     *
     * @since 2.6.3
     *
     * @return void
     */
    public function handle_duplicate_product() {
        if ( ! isset( $_GET['action'] ) || 'dokan-duplicate-product' !== $_GET['action'] ) {
            return;
        }

        $product_id = isset( $_GET['product_id'] ) ? (int) $_GET['product_id'] : 0;
        $redirect_url = dokan_get_navigation_url( 'products' );

        if ( ! $product_id ) {
            wp_safe_redirect( add_query_arg( array( 'message' => 'error' ), $redirect_url ) );
            return;
        }

        if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_GET['_wpnonce'] ), 'dokan-duplicate-product' ) ) {
            wp_safe_redirect( add_query_arg( array( 'message' => 'error' ), $redirect_url ) );
            return;
        }

        $clone_product = $this->duplicate_product( $product_id );

        if ( is_wp_error( $clone_product ) ) {
            wp_safe_redirect( add_query_arg( array( 'message' => 'error' ), $redirect_url ) );
            return;
        }

        $clone_product_id = $clone_product->get_id();

        if ( isset( $_GET['product_type'] ) && 'booking' === $_GET['product_type'] ) {
            $redirect_url = dokan_get_navigation_url( 'booking' );
        }

        wp_safe_redirect(
            add_query_arg(
                array( 'message' => 'product_duplicated' ),
                apply_filters( 'dokan_redirect_after_product_duplicating', $redirect_url, $product_id, $clone_product_id )
            )
        );
        exit;
    }

    /**
     * Duplicates a product.
     *
     * @since 3.7.14
     *
     * @param int $product_id The ID of the product to be duplicated
     *
     * @return \WC_Product|WP_Error
     */
    public function duplicate_product( $product_id ) {
        $no_permission_error = new WP_Error(
            'dokan-no-permission',
            __( 'You do not have permission to perform this action', 'dokan' )
        );

        $user_id = dokan_get_current_user_id();

        if ( empty( $user_id ) || ! dokan_is_user_seller( $user_id ) ) {
            return $no_permission_error;
        }

        if ( dokan_get_option( 'vendor_duplicate_product', 'dokan_selling', 'on' ) === 'off' ) {
            return $no_permission_error;
        }

        if ( ! apply_filters( 'dokan_vendor_can_duplicate_product', true ) ) {
            return $no_permission_error;
        }

        $product = wc_get_product( $product_id );

        if ( ! $product ) {
            return new WP_Error(
                'dokan-no-such-product',
                __( 'No such product found!', 'dokan' )
            );
        }

        if ( ! dokan_is_product_author( $product_id ) ) {
            return $no_permission_error;
        }

        $wc_duplicator = new \WC_Admin_Duplicate_Product();
        $clone_product = $wc_duplicator->product_duplicate( $product );

        // Make the newly created product status to "draft", before saving.
        $clone_product->set_status( 'draft' );
        $clone_product->save();

        do_action( 'dokan_product_duplicate_after_save', $clone_product, $product );

        return $clone_product;
    }

    /**
     * Show duplicate success message
     *
     * @since 2.6.3
     *
     * @return void
     */
    public function display_duplicate_message( $type ) {
        if ( 'product_duplicated' === $type ) {
            dokan_get_template_part(
                'global/dokan-success',
                '',
                array(
                    'deleted' => true,
                    'message' => __( 'Product succesfully duplicated', 'dokan' ),
                )
            );
        }
    }

    /**
     * Set product type
     *
     * @since 2.5.3
     *
     * @param integer $post_id
     */
    public function set_product_type( $post_id ) {
        $post_data = wp_unslash( $_POST );
        if ( isset( $post_data['product_type'] ) ) {
            wp_set_object_terms( $post_id, $post_data['product_type'], 'product_type' );
        }
    }

    /**
     * Set Additional product Post Data
     *
     * @since 2.6.3
     *
     * @param Object $product
     *
     * @return $product
     */
    public function save_product_post_data( $product ) {
        if ( 'yes' === get_user_meta( dokan_get_current_user_id(), 'dokan_publishing', true ) ) {
            return $product;
        }
        //update product status to pending-review if set by admin
        if ( 'on' === dokan_get_option( 'edited_product_status', 'dokan_selling' ) ) {
            $product['post_status'] = 'pending';

            return $product;
        }

        return $product;
    }

    /**
     * Set default product types
     *
     * @since 2.6
     *
     * @param array $product_types
     *
     * @return $product_types
     */
    public function set_default_product_types( $product_types ) {
        $product_types = array(
            'simple'   => __( 'Simple', 'dokan' ),
            'variable' => __( 'Variable', 'dokan' ),
            'external' => __( 'External/Affiliate product', 'dokan' ),
        );

        if ( version_compare( WC_VERSION, '2.7', '>' ) ) {
            $product_types['grouped'] = __( 'Group Product', 'dokan' );
        }

        return $product_types;
    }

    /**
     * Send email to admin once a product is updated
     *
     * @since 2.6.5
     *
     * @param int $product_id
     *
     * @param string $status
     */
    public function updated_product_email( $product_id ) {
         if ( dokan_get_option( 'edited_product_status', 'dokan_selling', 'off' ) !== 'on' ) {
            return;
        }

        $product = wc_get_product( $product_id );
        $seller  = dokan_get_vendor_by_product( $product );

        // check if product status is pending
        if ( 'pending' !== $product->get_status() ) {
            return;
        }

        // get category
        $category = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'names' ) );

        do_action( 'dokan_edited_product_pending_notification', $product, $seller, $category );
    }

    /**
     * Group product content
     *
     * @since 2.6.6
     *
     * @return void
     */
    public function group_product_content( $post, $post_id ) {
        dokan_get_template_part(
            'products/group-product',
            '',
            array(
                'pro'     => true,
                'post'    => $post,
                'post_id' => $post_id,
                'product' => wc_get_product( $post_id ),
            )
        );
    }

    /**
     * External product content
     *
     * @since 3.2.1
     *
     * @return void
     */
    public function external_product_content( $post, $post_id ) {
        $product_types = apply_filters( 'dokan_product_types', 'simple' );

        if ( ! array_key_exists( 'external', $product_types ) ) {
            return;
        }

        dokan_get_template_part(
            'products/external-product',
            '',
            array(
                'pro'     => true,
                'post'    => $post,
                'post_id' => $post_id,
                'product' => wc_get_product( $post_id ),
            )
        );
    }

    /**
     * Remove unwanted meta_keys while duplicating product
     *
     * @param  array $meta_keys
     *
     * @since 2.7.6
     *
     * @return array $meta_keys
     */
    public function remove_unwanted_meta( $meta_keys ) {
        $meta_keys[] = 'pageview';

        return $meta_keys;
    }

    /**
     * Add Dokan Pro localized vars
     *
     * @since 2.8.4
     *
     * @param array $args
     *
     * @return array
     */
    public function dokan_pro_localized_args( $args ) {
        $dokan_pro_args = array(
            'product_vendors_can_create_tags' => dokan_get_option( 'product_vendors_can_create_tags', 'dokan_selling' ),
            'product_inline_edit_nonce'       => wp_create_nonce( 'product-inline-edit' ),
            'is_vendor_enabled'               => dokan_is_seller_enabled( dokan_get_current_user_id() ),
            'not_enable_message'              => __( 'Error! Your account is not enabled for selling, please contact the admin', 'dokan' ),
        );

        return array_merge( $args, $dokan_pro_args );
    }

    /**
     * Inline edit form
     *
     * @since 2.9.0
     *
     * @param WC_Product $product
     * @param WP_Post    $post
     *
     * @return void
     */
    public function add_product_inline_edit_form( $product, $post ) {
        $options = $this->get_inline_edit_options();

        $wp_cats = get_the_terms( $post, 'product_cat' );
        $cats    = ! empty( $wp_cats ) && ! is_wp_error( $wp_cats ) ? wp_list_pluck( $wp_cats, 'term_id' ) : '';

        if ( $options['using_single_category_style'] && ! empty( $cats ) ) {
            $cats = array_pop( $cats );
        }

        $tags = get_the_terms( $post, 'product_tag' );

        $args = array(
            'pro'     => true,
            'id'      => 'dokan-product-list-table',
            'options' => $this->get_inline_edit_options(),

            // product informations
            'product_id'        => $product->get_id(),
            'post_title'        => $product->get_title(),
            'product_cat'       => (array) $cats,
            'product_tag'       => $tags,
            'product_type'      => $product->get_type(),
            'is_virtual'        => $product->is_virtual(),
            'reviews_allowed'   => $product->get_reviews_allowed(),
            'post_status'       => $post->post_status,
            'sku'               => $product->get_sku(),
            '_regular_price'    => $product->get_regular_price(),
            '_sale_price'       => $product->get_sale_price(),
            'weight'            => $product->get_weight(),
            'length'            => $product->get_length(),
            'width'             => $product->get_width(),
            'height'            => $product->get_height(),
            'shipping_class_id' => $product->get_shipping_class_id(),
            '_visibility'       => ( version_compare( WC_VERSION, '2.7', '>' ) ) ? $product->get_catalog_visibility() : get_post_meta( $post->ID, '_visibility', true ),
            'manage_stock'      => $product->get_manage_stock(),
            'stock_quantity'    => $product->get_stock_quantity(),
            'stock_status'      => $product->get_stock_status(),
            'backorders'        => $product->get_backorders(),
            'selling_type'      => dokan_pro()->digital_product->get_selling_product_type(),
        );

        dokan_get_template_part( 'products/edit/product-list-table-inline-edit-form', '', $args );
    }

    /**
     * Reusable inline edit options
     *
     * @since 2.9.0
     *
     * @return array
     */
    private function get_inline_edit_options() {
        if ( ! empty( $this->inline_edit_options ) ) {
            return $this->inline_edit_options;
        }

        $args = apply_filters(
            'dokan_product_cat_dropdown_args', [
                'taxonomy'   => 'product_cat',
                'number'     => false,
                'orderby'    => 'name',
                'order'      => 'asc',
                'hide_empty' => false,
            ]
        );

        $categories = get_terms( $args );

        $using_single_category_style = ( 'single' === dokan_get_option( 'product_category_style', 'dokan_selling', 'single' ) ) || false;

        $args = array(
            'taxonomy'   => 'product_tag',
            'number'     => false,
            'orderby'    => 'name',
            'order'      => 'asc',
            'hide_empty' => false,
        );

        $tags = get_terms( $args );

        $this->inline_edit_options = array(
            'using_single_category_style' => $using_single_category_style,
            'categories' => $categories,
            'tags'  => $tags,
            'post_statuses' => array(
                'publish' => __( 'Online', 'dokan' ),
                'draft'   => __( 'Draft', 'dokan' ),
            ),
            'is_sku_enabled' => wc_product_sku_enabled(),
            'is_weight_enabled' => wc_product_weight_enabled(),
            'is_dimensions_enabled' => wc_product_dimensions_enabled(),
            'shipping_classes' => WC()->shipping->get_shipping_classes(),
            'visibilities' => dokan_get_product_visibility_options(),
            'can_manage_stock' => get_option( 'woocommerce_manage_stock' ),
            'stock_statuses' => array(
                'instock'    => __( 'In Stock', 'dokan' ),
                'outofstock' => __( 'Out of Stock', 'dokan' ),
            ),
            'backorder_options' => array(
                'no'     => __( 'Do not allow', 'dokan' ),
                'notify' => __( 'Allow but notify customer', 'dokan' ),
                'yes'    => __( 'Allow', 'dokan' ),
            ),
        );

        return $this->inline_edit_options;
    }

    /**
     * Save quick edit product data
     *
     * @since 2.9.0
     *
     * @return void
     */
    public function product_inline_edit() {
        if ( ! isset( $_POST['security'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['security'] ) ), 'product-inline-edit' ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'dokan' ) );
        }
        $post_data = wp_unslash( $_POST );

        if ( empty( $post_data['data'] ) ) {
            wp_send_json_error( __( 'data is empty', 'dokan' ), 422 );
        }

        $cleaned_data = apply_filters( 'dokan_update_product_post_data', wc_clean( $post_data['data'] ) );

        if ( empty( $cleaned_data['ID'] ) ) {
            wp_send_json_error( __( 'Product ID field is required', 'dokan' ), 422 );
        }

        if ( ! isset( $cleaned_data['chosen_product_cat'] ) ) {
            if ( Helper::product_category_selection_is_single() ) {
                if ( absint( $cleaned_data['product_cat'] ) < 0 ) {
                    return new WP_Error( 'no-category', __( 'Please select a category', 'dokan' ) );
                }
            } else {
                if ( ! isset( $cleaned_data['product_cat'] ) && empty( $cleaned_data['product_cat'] ) ) {
                    return new WP_Error( 'no-category', __( 'Please select at least one category', 'dokan' ) );
                }
            }
        } elseif ( empty( $cleaned_data['chosen_product_cat'] ) ) {
            return new WP_Error( 'no-category', __( 'Please select a category', 'dokan' ) );
        }

        if ( isset( $cleaned_data['sku'] ) && ! empty( $cleaned_data['sku'] ) && ! wc_product_has_unique_sku( $cleaned_data['ID'], $cleaned_data['sku'] ) ) {
            wp_send_json_error( __( 'Invalid or duplicated SKU.', 'dokan' ), 422 );
        }

        $data = array(
            'id' => $cleaned_data['ID'],
            'name' => $cleaned_data['post_title'],
        );

        if ( isset( $cleaned_data['reviews_allowed'] ) ) {
            $data['reviews_allowed'] = $cleaned_data['reviews_allowed'];
        }

        if ( isset( $cleaned_data['post_status'] ) ) {
            $data['status'] = $cleaned_data['post_status'];
        }

        if ( isset( $cleaned_data['sku'] ) ) {
            $data['sku'] = $cleaned_data['sku'];
        }

        if ( isset( $cleaned_data['_regular_price'] ) ) {
            $data['regular_price'] = $cleaned_data['_regular_price'];
        }

        if ( isset( $cleaned_data['_sale_price'] ) ) {
            $data['sale_price'] = $cleaned_data['_sale_price'];
        }

        if ( isset( $cleaned_data['width'] ) ) {
            $data['dimensions']['width'] = $cleaned_data['width'];
        }

        if ( isset( $cleaned_data['length'] ) ) {
            $data['dimensions']['length'] = $cleaned_data['length'];
        }

        if ( isset( $cleaned_data['height'] ) ) {
            $data['dimensions']['height'] = $cleaned_data['height'];
        }

        if ( isset( $cleaned_data['weight'] ) ) {
            $data['weight'] = $cleaned_data['weight'];
        }

        if ( isset( $cleaned_data['shipping_class_id'] ) ) {
            $data['shipping_class'] = $cleaned_data['shipping_class_id'];
        }

        if ( isset( $cleaned_data['_visibility'] ) ) {
            $data['catalog_visibility'] = $cleaned_data['_visibility'];
        }

        if ( isset( $cleaned_data['manage_stock'] ) ) {
            $data['manage_stock'] = $cleaned_data['manage_stock'];
        }

        if ( isset( $cleaned_data['stock_quantity'] ) ) {
            $data['stock_quantity'] = $cleaned_data['stock_quantity'];
        }

        if ( isset( $cleaned_data['stock_status'] ) ) {
            $data['stock_status'] = $cleaned_data['stock_status'];
        }

        if ( isset( $cleaned_data['backorders'] ) ) {
            $data['backorders'] = $cleaned_data['backorders'];
        }

        if ( isset( $cleaned_data['product_type'] ) ) {
            $data['type'] = $cleaned_data['product_type'];
        }

        $data['categories'] = [];

        if ( isset( $cleaned_data['product_tag'] ) ) {

            /**
             * Filter for vendor product tags select maximum length.
             *
             * @since 3.3.7
             *
             * @param integer default -1
             */
            $maximum_tags_select_length = apply_filters( 'dokan_product_tags_select_max_length', -1 );

            // Setting limitation for how many product tags that vendor can input.
            if ( $maximum_tags_select_length !== -1 && count( $cleaned_data['product_tag'] ) !== 0 && count( $cleaned_data['product_tag'] ) > $maximum_tags_select_length ) {
                /* translators: %s: maximum tag length */
                wp_send_json_error( sprintf( __( 'You can only select %s tags', 'dokan' ), number_format_i18n( $maximum_tags_select_length ) ), 422 );
            } else {
                $tags = [];
                foreach ( (array) $cleaned_data['product_tag'] as $tag ) {
                    if ( is_numeric( $tag ) ) {
                        $tags[] = $tag;
                        continue;
                    }
                    //insert new tag
                    $new_tag = wp_insert_term( $tag, 'product_tag' );
                    if ( ! is_wp_error( $new_tag ) ) {
                        $tags[] = $new_tag['term_id'];
                    }
                }
                $data['tags'] = $tags;
            }
        }

        $data    = apply_filters( 'dokan_update_product_quick_edit_data', $data );
        $product = dokan()->product->update( $data );

        if ( $product ) {
            $chosen_cat = Helper::product_category_selection_is_single() ? [ reset( $cleaned_data['chosen_product_cat'] ) ] : $cleaned_data['chosen_product_cat'];
            Helper::set_object_terms_from_chosen_categories( $product->get_id(), $chosen_cat );
        }

        if ( empty( $product ) ) {
            wp_send_json_error( __( 'Error updating product data', 'dokan' ), 422 );
        }

        /**
         * Run when product data update in quick edit.
         *
         * @parm int $product_id Product id.
         * @parm array $data Data of the updated product.
         *
         * @since 3.2.1
         */
        do_action( 'dokan_product_quick_edit_updated', $product->get_id(), $data );

        $post           = get_post( $product->get_id() );
        $row_actions    = dokan_product_get_row_action( $post );
        $tr_class       = ( $post->post_status === 'pending' ) ? 'danger' : '';

        $row_args = array(
            'post' => $post,
            'product' => $product,
            'tr_class' => $tr_class,
            'row_actions' => $row_actions,
        );

        ob_start();
        dokan_get_template_part( 'products/products-listing-row', '', $row_args );
        $html = ob_get_clean();

        $message = array(
            'message' => __( 'Product updated successfully', 'dokan' ),
            'row' => $html,
        );

        wp_send_json_success( $message );
    }

    /**
     * Prevent duplicate sku when multiple vendor add same product
     *
     * @param $duplicate
     *
     * @return void
     */
    public function prevent_duplicate_sku( $duplicate, $product ) {
        $sku        = $duplicate->get_sku( 'edit' );
        $unique_sku = $this->get_unique_sku( $sku );
        $duplicate->set_sku( $unique_sku );
    }

    /**
     * Check recursively if sku exist
     *
     * @param $sku
     *
     * @return mixed
     */
    public function get_unique_sku( $sku ) {
        $unique_sku = $sku;

        // If SKU is already empty, we don't need to create a new SKU
        if ( empty( $unique_sku ) ) {
            return $unique_sku;
        }

        global $wpdb;
        $result = (int) $wpdb->get_var( $wpdb->prepare( "SELECT COUNT(*) FROM {$wpdb->prefix}postmeta WHERE meta_key='_sku' AND meta_value =%s ", $sku ) );
        if ( $result >= 1 ) {
            if ( strpos( $sku, '-' ) !== false ) {
                $arr                      = explode( '-', $sku );
                $arr[ count( $arr ) - 1 ] = $arr[ count( $arr ) - 1 ] + 1;
                $unique_sku               = implode( '-', $arr );
            } else {
                $unique_sku = $sku . '-1';
            }

            return $this->get_unique_sku( $unique_sku );
        } else {
            return $unique_sku;
        }
    }
}
