<?php

namespace WeDevs\DokanPro\Modules\OrderMinMax;

use WP_Error;

/**
 * OrderMinMax Class.
 *
 * @since 3.5.0
 */
class Vendor {

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

        add_action( 'dokan_settings_form_bottom', [ $this, 'vendor_settings' ], 20 );
        add_filter( 'dokan_store_profile_settings_args', [ $this, 'store_profile_settings' ], 20 );
        add_action( 'dokan_product_edit_after_inventory_variants', [ $this, 'load_min_max_meta_box' ], 31, 2 );
        add_action( 'dokan_product_after_variable_attributes', [ $this, 'vendor_available_variation' ], 31, 3 );
        add_action( 'dokan_process_product_meta', [ $this, 'save_min_max_meta' ] );
        add_action( 'dokan_variable_product_bulk_edit_actions', [ $this, 'dokan_variable_product_bulk_edit_actions' ] );
        add_action( 'dokan_save_product_variation', [ $this, 'save_variation_min_max_data' ], 10, 2 );
        add_action( 'dokan_ajax_save_product_variations', [ $this, 'save_variation_min_max_ajax_data' ], 12 );
    }

    /**
     * Add min max vendor settings.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function vendor_settings() {
        // Check if admin active min/max feature.
        $store_id       = dokan_get_current_user_id();
        $dokan_settings = dokan_get_store_info( $store_id );
        $min_max_args   = [
            'enable_min_max_quantity'        => self::$enable_min_max_quantity,
            'enable_vendor_min_max_quantity' => isset( $dokan_settings['order_min_max']['enable_vendor_min_max_quantity'] ) ? $dokan_settings['order_min_max']['enable_vendor_min_max_quantity'] : 'no',
            'min_quantity_to_order'          => isset( $dokan_settings['order_min_max']['min_quantity_to_order'] ) ? $dokan_settings['order_min_max']['min_quantity_to_order'] : '',
            'max_quantity_to_order'          => isset( $dokan_settings['order_min_max']['max_quantity_to_order'] ) ? $dokan_settings['order_min_max']['max_quantity_to_order'] : '',
            'enable_min_max_amount'          => self::$enable_min_max_amount,
            'enable_vendor_min_max_amount'   => isset( $dokan_settings['order_min_max']['enable_vendor_min_max_amount'] ) ? $dokan_settings['order_min_max']['enable_vendor_min_max_amount'] : 'no',
            'min_amount_to_order'            => isset( $dokan_settings['order_min_max']['min_amount_to_order'] ) ? $dokan_settings['order_min_max']['min_amount_to_order'] : '',
            'max_amount_to_order'            => isset( $dokan_settings['order_min_max']['max_amount_to_order'] ) ? $dokan_settings['order_min_max']['max_amount_to_order'] : '',
            'vendor_min_max_products'        => isset( $dokan_settings['order_min_max']['vendor_min_max_products'] ) ? $dokan_settings['order_min_max']['vendor_min_max_products'] : [],
            'vendor_min_max_product_cat'     => isset( $dokan_settings['order_min_max']['vendor_min_max_product_cat'] ) ? $dokan_settings['order_min_max']['vendor_min_max_product_cat'] : [],
        ];

        dokan_get_template_part(
            'order-min-max-settings', '', [
                'order_min_max_template' => true,
                'min_max_args'           => $min_max_args,
            ]
        );
    }

    /**
     * Store profile settings.
     *
     * @since 3.5.0
     *
     * @param array $dokan_settings
     *
     * @return array
     */
    public function store_profile_settings( $dokan_settings ) {
        if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( sanitize_key( $_POST['_wpnonce'] ), 'dokan_store_settings_nonce' ) ) {
            return $dokan_settings;
        }

        $error = false;
        $dokan_settings['order_min_max']['enable_vendor_min_max_quantity'] = 'no';
        $dokan_settings['order_min_max']['min_quantity_to_order']          = '';
        $dokan_settings['order_min_max']['max_quantity_to_order']          = '';
        $dokan_settings['order_min_max']['vendor_min_max_products']        = isset( $_POST['product_drop_down'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['product_drop_down'] ) ) : [];
        $dokan_settings['order_min_max']['vendor_min_max_product_cat']     = isset( $_POST['product_cat'] ) ? ( is_array( $_POST['product_cat'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['product_cat'] ) ) : sanitize_text_field( wp_unslash( $_POST['product_cat'] ) ) ) : [];

        if ( isset( $_POST['enable_vendor_min_max_quantity'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['enable_vendor_min_max_quantity'] ) ) ) {
            if ( empty( $dokan_settings['order_min_max']['vendor_min_max_product_cat'] ) && empty( $dokan_settings['order_min_max']['vendor_min_max_products'] ) ) {
                $error = true;
            }

            $dokan_settings['order_min_max']['enable_vendor_min_max_quantity'] = sanitize_text_field( wp_unslash( $_POST['enable_vendor_min_max_quantity'] ) );
            $dokan_settings['order_min_max']['min_quantity_to_order']          = isset( $_POST['min_quantity_to_order'] ) && $_POST['min_quantity_to_order'] > 0 ? absint( wp_unslash( $_POST['min_quantity_to_order'] ) ) : 0;
            $dokan_settings['order_min_max']['max_quantity_to_order']          = isset( $_POST['max_quantity_to_order'] ) && $_POST['max_quantity_to_order'] > 0 ? absint( wp_unslash( $_POST['max_quantity_to_order'] ) ) : 0;
        }

        $dokan_settings['order_min_max']['enable_vendor_min_max_amount'] = 'no';
        $dokan_settings['order_min_max']['min_amount_to_order']          = '';
        $dokan_settings['order_min_max']['max_amount_to_order']          = '';

        if ( isset( $_POST['enable_vendor_min_max_amount'] ) && 'yes' === sanitize_text_field( wp_unslash( $_POST['enable_vendor_min_max_amount'] ) ) ) {
            if ( empty( $dokan_settings['order_min_max']['vendor_min_max_product_cat'] ) && empty( $dokan_settings['order_min_max']['vendor_min_max_products'] ) ) {
                $error = true;
            }

            $dokan_settings['order_min_max']['enable_vendor_min_max_amount'] = sanitize_text_field( wp_unslash( $_POST['enable_vendor_min_max_amount'] ) );
            $dokan_settings['order_min_max']['min_amount_to_order']          = isset( $_POST['min_amount_to_order'] ) && $_POST['min_amount_to_order'] > 0 ? wc_format_decimal( sanitize_text_field( wp_unslash( $_POST['min_amount_to_order'] ) ) ) : 0;
            $dokan_settings['order_min_max']['max_amount_to_order']          = isset( $_POST['max_amount_to_order'] ) && $_POST['max_amount_to_order'] > 0 ? wc_format_decimal( sanitize_text_field( wp_unslash( $_POST['max_amount_to_order'] ) ) ) : 0;
        }

        if ( $error ) {
            wp_send_json_error( __( 'Please select either some product or category to set min max.', 'dokan' ) );
        }

        return $dokan_settings;
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
    public function save_min_max_meta( $product_id ) {
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
    public function save_variation_min_max_data( $product_id, $loop ) {
        // Save data from OrderMinMax file.
        Manager::save_min_max_variation_meta( $product_id, $loop );
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
     * Dokan_variable_product_bulk_edit_actions.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function dokan_variable_product_bulk_edit_actions() {
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

    /**
     * Load_min_max_meta_box.
     *
     * @since 3.5.0
     *
     * @param \WP_Post $post
     * @param int      $post_id
     *
     * @return void
     */
    public function load_min_max_meta_box( $post, $post_id ) {
        if ( 'on' === self::$enable_min_max_quantity || 'on' === self::$enable_min_max_amount ) {
            $dokan_min_max_meta    = get_post_meta( $post->ID, '_dokan_min_max_meta', true );
            $dokan_settings        = $this->dokan_get_store_info_by_product_id( $post->ID );
            $tab_title             = __( 'Min/Max Options', 'dokan' );
            $tab_desc              = __( 'Manage min max options for this product', 'dokan' );
            $product_settings_args = [
                'post_id'                        => $post_id,
                'tab_title'                      => $tab_title,
                'tab_desc'                       => $tab_desc,
                'enable_min_max_quantity'        => self::$enable_min_max_quantity,
                'enable_min_max_amount'          => self::$enable_min_max_amount,
                'dokan_min_max_meta'             => $dokan_min_max_meta,
                'enable_vendor_min_max_quantity' => isset( $dokan_settings['order_min_max']['enable_vendor_min_max_quantity'] ) ? $dokan_settings['order_min_max']['enable_vendor_min_max_quantity'] : 'no',
                'enable_vendor_min_max_amount'   => isset( $dokan_settings['order_min_max']['enable_vendor_min_max_amount'] ) ? $dokan_settings['order_min_max']['enable_vendor_min_max_amount'] : 'no',
                'product_wise_activation'        => isset( $dokan_min_max_meta['product_wise_activation'] ) ? $dokan_min_max_meta['product_wise_activation'] : '',
                'min_quantity'                   => isset( $dokan_min_max_meta['min_quantity'] ) ? $dokan_min_max_meta['min_quantity'] : '',
                'max_quantity'                   => isset( $dokan_min_max_meta['max_quantity'] ) ? $dokan_min_max_meta['max_quantity'] : '',
                'min_amount'                     => isset( $dokan_min_max_meta['min_amount'] ) ? $dokan_min_max_meta['min_amount'] : '',
                'max_amount'                     => isset( $dokan_min_max_meta['max_amount'] ) ? $dokan_min_max_meta['max_amount'] : '',
                '_donot_count'                   => isset( $dokan_min_max_meta['_donot_count'] ) ? $dokan_min_max_meta['_donot_count'] : '',
                'ignore_from_cat'                => isset( $dokan_min_max_meta['ignore_from_cat'] ) ? $dokan_min_max_meta['ignore_from_cat'] : '',
            ];

            dokan_get_template_part(
                'order-min-max-product-settings', '', [
                    'order_min_max_template' => true,
                    'product_settings_args'  => $product_settings_args,
                ]
            );
        }
    }

    /**
     * Dokan get store info by product id. A wrapper for dokan_get_store_info.
     *
     * @since 3.5.0
     *
     * @param $product_id
     *
     * @return array
     */
    public function dokan_get_store_info_by_product_id( $product_id ) {
        $store_id = dokan_get_vendor_by_product( $product_id, true );

        return dokan_get_store_info( $store_id );
    }

    /**
     * Adds variation min max settings to the vendor dashboard.
     *
     * @since 3.5.0
     *
     * @param $loop
     * @param $variation_data
     * @param $variation
     *
     * @return void
     */
    public function vendor_available_variation( $loop, $variation_data, $variation ) {
        if ( 'on' === self::$enable_min_max_quantity || 'on' === self::$enable_min_max_amount ) {
            $dokan_min_max_meta    = get_post_meta( $variation->ID, '_dokan_min_max_meta', true );
            $dokan_settings        = $this->dokan_get_store_info_by_product_id( $variation->ID );
            $product_settings_args = [
                'post_id'                        => $variation->ID,
                'enable_min_max_quantity'        => self::$enable_min_max_quantity,
                'enable_min_max_amount'          => self::$enable_min_max_amount,
                'loop'                           => $loop,
                'enable_vendor_min_max_quantity' => isset( $dokan_settings['order_min_max']['enable_vendor_min_max_quantity'] ) ? $dokan_settings['order_min_max']['enable_vendor_min_max_quantity'] : 'no',
                'enable_vendor_min_max_amount'   => isset( $dokan_settings['order_min_max']['enable_vendor_min_max_amount'] ) ? $dokan_settings['order_min_max']['enable_vendor_min_max_amount'] : 'no',
                'product_wise_activation'        => isset( $dokan_min_max_meta['product_wise_activation'] ) ? $dokan_min_max_meta['product_wise_activation'] : '',
                'min_quantity'                   => isset( $dokan_min_max_meta['min_quantity'] ) ? $dokan_min_max_meta['min_quantity'] : '',
                'max_quantity'                   => isset( $dokan_min_max_meta['max_quantity'] ) ? $dokan_min_max_meta['max_quantity'] : '',
                'min_amount'                     => isset( $dokan_min_max_meta['min_amount'] ) ? $dokan_min_max_meta['min_amount'] : '',
                'max_amount'                     => isset( $dokan_min_max_meta['max_amount'] ) ? $dokan_min_max_meta['max_amount'] : '',
                '_donot_count'                   => isset( $dokan_min_max_meta['_donot_count'] ) ? $dokan_min_max_meta['_donot_count'] : '',
                'ignore_from_cat'                => isset( $dokan_min_max_meta['ignore_from_cat'] ) ? $dokan_min_max_meta['ignore_from_cat'] : '',
            ];

            dokan_get_template_part(
                'order-min-max-variation-product-settings', '', [
                    'order_min_max_template' => true,
                    'product_settings_args'  => $product_settings_args,
                ]
            );
        }
    }
}
