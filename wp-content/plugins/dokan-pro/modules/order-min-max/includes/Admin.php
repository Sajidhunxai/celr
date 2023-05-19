<?php

namespace WeDevs\DokanPro\Modules\OrderMinMax;

use WC_Product;

/**
 * OrderMinMax Class.
 *
 * @since 3.5.0
 */
class Admin {

    private static $enable_min_max_quantity;
    private static $enable_min_max_amount;

    /**
     * OrderMinMax Class Constructor.
     *
     * @since 3.5.0
     */
    public function __construct() {
        self::$enable_min_max_quantity = dokan_get_option( 'enable_min_max_quantity', 'dokan_selling', 'off' );
        self::$enable_min_max_amount   = dokan_get_option( 'enable_min_max_amount', 'dokan_selling', 'off' );

        add_filter( 'dokan_settings_selling_option_vendor_capability', [ $this, 'admin_settings' ], 20 );
        add_action( 'woocommerce_product_options_general_product_data', [ $this, 'add_meta_fields' ] );
        add_action( 'woocommerce_product_after_variable_attributes', [ $this, 'variable_attributes' ], 10, 3 );
        add_action( 'save_post_product', [ $this, 'save_min_max_meta' ] );
        add_action( 'woocommerce_save_product_variation', [ $this, 'save_min_max_variation_data' ], 10, 2 );
        add_action( 'woocommerce_ajax_save_product_variations', [ $this, 'save_variation_min_max_ajax_data' ], 12 );
        add_action( 'woocommerce_variable_product_bulk_edit_actions', [ $this, 'woocommerce_variable_product_bulk_edit_actions' ] );
    }

    /**
     * Min max meta save.
     *
     * @since 3.5.0
     *
     * @param int $product_id
     *
     * @return void
     */
    public function save_variation_min_max_ajax_data( $product_id ) {
        if ( ! $product_id ) {
            return;
        }
        if ( ! is_user_logged_in() ) {
            return;
        }

        // phpcs:ignore
        if ( ! isset( $_POST['variable_product_wise_activation'] ) ) {
            return;
        }
        // phpcs:ignore
        foreach ( $_POST['variable_min_quantity'] as $loop => $data ) {
            // Save data from OrderMinMax file.
            Manager::save_min_max_variation_meta( $product_id, $loop );
        }
    }

    /**
     * Add min max capabilities.
     *
     * @since 3.5.0
     *
     * @param mixed $settings
     *
     * @return mixed
     */
    public function admin_settings( $settings ) {
        $settings['enable_min_max_quantity'] = [
            'name'    => 'enable_min_max_quantity',
            'label'   => __( 'Enable Min/Max Quantities', 'dokan' ),
            'desc'    => __( 'Activating this will set min and max quantities for selected products.', 'dokan' ),
            'type'    => 'switcher',
            'default' => 'on',
            'tooltip' => __( 'When checked, this will allow vendors to set min and max quantities for selected products of their store.', 'dokan' ),
        ];
        $settings['enable_min_max_amount']   = [
            'name'    => 'enable_min_max_amount',
            'label'   => __( 'Enable Min/Max Amount', 'dokan' ),
            'desc'    => __( 'Activating this will set min and max amount for selected products.', 'dokan' ),
            'type'    => 'switcher',
            'default' => 'on',
            'tooltip' => __( 'When checked, this will allow vendors to set min and max amount for selected products of their store.', 'dokan' ),
        ];

        return $settings;
    }

    /**
     * Add min max meta box.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function add_meta_fields() {
        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            return;
        }

        // Early return.
        if ( 'on' !== self::$enable_min_max_quantity && 'on' !== self::$enable_min_max_amount ) {
            return;
        }

        $dokan_min_max_meta = get_post_meta( get_the_ID(), '_dokan_min_max_meta', true );

        echo '<div class="options_group show_if_simple">';
        // Check if admin active min/max feature.
        if ( 'on' === self::$enable_min_max_quantity || 'on' === self::$enable_min_max_amount ) {
            woocommerce_wp_checkbox(
                [
                    'id'          => 'product_wise_activation',
                    'value'       => isset( $dokan_min_max_meta['product_wise_activation'] ) ? $dokan_min_max_meta['product_wise_activation'] : 'no',
                    'label'       => __( 'Enable Min Max Rule', 'dokan' ),
                    'description' => __( 'Enable Min Max Rule for this product', 'dokan' ),
                ]
            );
        }

        if ( 'on' === self::$enable_min_max_quantity ) {
            woocommerce_wp_text_input(
                [
                    'id'    => 'min_quantity',
                    'value' => isset( $dokan_min_max_meta['min_quantity'] ) ? $dokan_min_max_meta['min_quantity'] : '',
                    'type'  => 'number',
                    'custom_attributes' => array(
                        'step' => 'any',
                        'min'  => '1',
                    ),
                    'label' => __( 'Minimum quantity to order', 'dokan' ),
                ]
            );
            woocommerce_wp_text_input(
                [
                    'id'    => 'max_quantity',
                    'value' => isset( $dokan_min_max_meta['max_quantity'] ) ? $dokan_min_max_meta['max_quantity'] : '',
                    'type'  => 'number',
                    'custom_attributes' => array(
                        'step' => 'any',
                        'min'  => '1',
                    ),
                    'label' => __( 'Maximum quantity to order', 'dokan' ),
                ]
            );
        }

        // Check if admin active min/max feature.
        if ( 'on' === self::$enable_min_max_amount ) {
            woocommerce_wp_text_input(
                [
                    'id'        => 'min_amount',
                    'value'     => isset( $dokan_min_max_meta['min_amount'] ) ? $dokan_min_max_meta['min_amount'] : '',
                    'data_type' => 'price',
                    'custom_attributes' => array(
                        'step' => 'any',
                        'min'  => '1',
                    ),
                    'label'     => __( 'Minimum amount to order', 'dokan' ),
                ]
            );
            woocommerce_wp_text_input(
                [
                    'id'        => 'max_amount',
                    'value'     => isset( $dokan_min_max_meta['max_amount'] ) ? $dokan_min_max_meta['max_amount'] : '',
                    'data_type' => 'price',
                    'custom_attributes' => array(
                        'step' => 'any',
                        'min'  => '1',
                    ),
                    'label'     => __( 'Maximum amount to order', 'dokan' ),
                ]
            );
        }

        if ( 'on' === self::$enable_min_max_quantity || 'on' === self::$enable_min_max_amount ) {
            woocommerce_wp_checkbox(
                [
                    'id'          => '_donot_count',
                    'value'       => isset( $dokan_min_max_meta['_donot_count'] ) ? $dokan_min_max_meta['_donot_count'] : 'no',
                    'label'       => __( 'Order rules: ', 'dokan' ),
                    'description' => __( 'Don\'t count this product against order rules when there are other items in the cart.', 'dokan' ),
                ]
            );
            woocommerce_wp_checkbox(
                [
                    'id'          => 'ignore_from_cat',
                    'value'       => isset( $dokan_min_max_meta['ignore_from_cat'] ) ? $dokan_min_max_meta['ignore_from_cat'] : 'no',
                    'label'       => __( 'Category rules: ', 'dokan' ),
                    'description' => __( 'Exclude this product from category rules.', 'dokan' ),
                ]
            );
        }

        echo wp_nonce_field( 'min_max_product_wise_activation_action', 'min_max_product_wise_activation_field' ) . '</div>';
    }

    /**
     * Add min max meta box for variable attribute.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function variable_attributes( $loop, $variation_data, $variation ) {
        $product_id = ! empty( $variation->ID ) ? $variation->ID : 0;
        $product    = wc_get_product( $product_id );

        if ( ! $product instanceof WC_Product ) {
            return;
        }

        // Early return.
        if ( 'on' !== self::$enable_min_max_quantity && 'on' !== self::$enable_min_max_amount ) {
            return;
        }

        $dokan_min_max_meta = $product->get_meta( '_dokan_min_max_meta', true );
        // Check if admin active min/max feature.
        if ( 'on' === self::$enable_min_max_quantity || 'on' === self::$enable_min_max_amount ) {
            woocommerce_wp_checkbox(
                [
                    'id'            => "variable_product_wise_activation{$loop}",
                    'name'          => "variable_product_wise_activation[{$loop}]",
                    'class'         => 'variable_product_wise_activation',
                    'value'         => isset( $dokan_min_max_meta['product_wise_activation'] ) ? $dokan_min_max_meta['product_wise_activation'] : 'no',
                    'style'         => 'margin: 2px 5px !important',
                    'wrapper_class' => 'form-row form-row-full form-field',
                    'description'   => __( 'Enable Min Max Rule for this product', 'dokan' ),
                ]
            );
        }

        echo '<div class="options_group">';
        echo wp_nonce_field( 'min_max_product_variation_wise_activation_action', 'min_max_product_variation_wise_activation_field' );

        if ( 'on' === self::$enable_min_max_quantity ) {
            woocommerce_wp_text_input(
                [
                    'id'    => "variable_min_quantity{$loop}",
                    'name'  => "variable_min_quantity[{$loop}]",
                    'class' => 'variable_min_quantity',
                    'value' => isset( $dokan_min_max_meta['min_quantity'] ) ? $dokan_min_max_meta['min_quantity'] : '',
                    'type'  => 'number',
                    'label' => __( 'Minimum quantity to order', 'dokan' ),
                ]
            );
            woocommerce_wp_text_input(
                [
                    'id'    => "variable_max_quantity{$loop}",
                    'name'  => "variable_max_quantity[{$loop}]",
                    'class' => 'variable_max_quantity',
                    'value' => isset( $dokan_min_max_meta['max_quantity'] ) ? $dokan_min_max_meta['max_quantity'] : '',
                    'type'  => 'number',
                    'label' => __( 'Maximum quantity to order', 'dokan' ),
                ]
            );
        }

        // Check if admin active min/max feature.
        if ( 'on' === self::$enable_min_max_amount ) {
            woocommerce_wp_text_input(
                [
                    'id'        => "variable_min_amount{$loop}",
                    'name'      => "variable_min_amount[{$loop}]",
                    'class'     => 'variable_min_amount',
                    'value'     => isset( $dokan_min_max_meta['min_amount'] ) ? $dokan_min_max_meta['min_amount'] : '',
                    'data_type' => 'price',
                    'label'     => __( 'Minimum amount to order', 'dokan' ),
                ]
            );
            woocommerce_wp_text_input(
                [
                    'id'        => "variable_max_amount{$loop}",
                    'name'      => "variable_max_amount[{$loop}]",
                    'class'     => 'variable_max_amount',
                    'value'     => isset( $dokan_min_max_meta['max_amount'] ) ? $dokan_min_max_meta['max_amount'] : '',
                    'data_type' => 'price',
                    'label'     => __( 'Maximum amount to order', 'dokan' ),
                ]
            );
        }

        if ( 'on' === self::$enable_min_max_quantity || 'on' === self::$enable_min_max_amount ) {
            woocommerce_wp_checkbox(
                [
                    'id'            => "variable__donot_count{$loop}",
                    'name'          => "variable__donot_count[{$loop}]",
                    'value'         => isset( $dokan_min_max_meta['_donot_count'] ) ? $dokan_min_max_meta['_donot_count'] : 'no',
                    'label'         => __( 'Order rules: ', 'dokan' ),
                    'style'         => 'margin: 2px 5px !important',
                    'wrapper_class' => 'form-row form-row-full form-field',
                    'description'   => __( 'Don\'t count this product against order rules when there are other items in the cart.', 'dokan' ),
                ]
            );
            woocommerce_wp_checkbox(
                [
                    'id'            => "variable_ignore_from_cat{$loop}",
                    'name'          => "variable_ignore_from_cat[{$loop}]",
                    'value'         => isset( $dokan_min_max_meta['ignore_from_cat'] ) ? $dokan_min_max_meta['ignore_from_cat'] : 'no',
                    'label'         => __( 'Category rules: ', 'dokan' ),
                    'style'         => 'margin: 2px 5px !important',
                    'wrapper_class' => 'form-row form-row-full form-field',
                    'description'   => __( 'Exclude this product from category rules.', 'dokan' ),
                ]
            );
        }

        echo wp_nonce_field( 'min_max_product_wise_activation_action_variation', 'min_max_product_wise_activation_field_variation' ) . '</div>';
    }

    /**
     * Min max meta save.
     *
     * @since 3.5.0
     *
     * @param int $product_id
     *
     * @return void
     */
    public static function save_min_max_meta( $product_id ) {
        // Save data from OrderMinMax file.
        Manager::save_min_max_meta( $product_id );
    }

    /**
     * Min max meta save.
     *
     * @since 3.5.0
     *
     * @param int $product_id
     *
     * @return void
     */
    public static function save_min_max_variation_data( $product_id, $loop ) {
        Manager::save_min_max_variation_meta( $product_id, $loop );
    }

    /**
     * Woocommerce_variable_product_bulk_edit_actions.
     * @since 3.5.0
     *
     * @return void
     */
    public function woocommerce_variable_product_bulk_edit_actions() {
        if ( 'on' === self::$enable_min_max_quantity || 'on' === self::$enable_min_max_amount ) {
            ?>
        <optgroup label="<?php esc_attr_e( 'Min Max Quantity / Amount', 'dokan' ); ?>">
            <?php if ( 'on' === self::$enable_min_max_quantity ) : ?>
                <option value='variable_min_quantity'><?php esc_html_e( 'Min quantity', 'dokan' ); ?></option>
                <option value='variable_max_quantity'><?php esc_html_e( 'Max quantity', 'dokan' ); ?></option>
            <?php endif; ?>
            <?php if ( 'on' === self::$enable_min_max_amount ) : ?>
                <option value='variable_min_amount'><?php esc_html_e( 'Min amount', 'dokan' ); ?></option>
                <option value='variable_max_amount'><?php esc_html_e( 'Max amount', 'dokan' ); ?></option>
            <?php endif; ?>
        </optgroup>
        <optgroup label="<?php esc_attr_e( 'Deactivate Min Max Quantity / Amount', 'dokan' ); ?>">
            <option value='min_max_deactivate_for_all'><?php esc_html_e( 'Deactivate for all variations', 'dokan' ); ?></option>
        </optgroup>
			<?php
        }
    }

}
