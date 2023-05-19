<?php

namespace WeDevs\DokanPro\Brands;

use WeDevs\Dokan\Walkers\TaxonomyDropdown;

class FormFields {

    /**
     * Print form fields
     *
     * @since 2.9.7
     *
     * @param int    $post_id
     * @param string $selected
     * @param bool   $show_label
     *
     * @return void
     */
    protected static function print_form_field( $post_id = 0, $selected = '', $show_label = false ) {
        $drop_down_args = [
            'hierarchical'      => 1,
            'hide_empty'        => 0,
            'name'              => 'product_brand[]',
            'id'                => 'product_brand',
            'taxonomy'          => dokan_pro()->brands->get_taxonomy(),
            'title_li'          => '',
            'class'             => 'dokan_product_brand dokan-form-control dokan-select2',
            'exclude'           => '',
            'selected'          => $selected,
            'echo'              => 0,
        ];

        if ( 'single' === dokan_pro()->brands->settings['mode'] ) {
            $drop_down_args['show_option_none']  = '&mdash; ' . esc_html__( 'Select product brand', 'dokan' ) . ' &mdash;';
            $drop_down_args['option_none_value'] = 0;
        }

        if ( is_array( $selected ) ) {
            $drop_down_args['walker'] = new TaxonomyDropdown( $post_id );
        }

        $drop_down_args     = apply_filters( 'dokan_product_brand_dropdown_args', $drop_down_args, $post_id );
        $drop_down_category = wp_dropdown_categories( $drop_down_args );

        if ( 'single' !== dokan_pro()->brands->settings['mode'] ) {
            $replace_with = '<select data-placeholder="' . esc_html__( 'Select product brands', 'dokan' ) . '" multiple="multiple" ';
        } else {
            $replace_with = '<select data-allow-clear="true" data-placeholder="' . esc_html__( 'Select product brand', 'dokan' ) . '"';
        }

        $drop_down_category = str_replace(
            '<select',
            $replace_with,
            $drop_down_category
        );

        dokan_get_template_part( 'brands/product-edit-form-field', '', [
            'pro'            => true,
            'brand_dropdown' => $drop_down_category,
            'show_label'     => $show_label,
        ] );
    }

    /**
     * New product form field
     *
     * @since 2.9.7
     *
     * @return void
     */
    public static function new_product_form_field() {
        self::print_form_field();
    }

    /**
     * Add brands dropdown
     *
     * @since 2.9.7
     *
     * @param \WP_Post $post
     * @param int      $post_id
     *
     * @return void
     */
    public static function product_edit_form_field( $post, $post_id ) {
        require_once DOKAN_LIB_DIR . '/class.taxonomy-walker.php';

        $selected = wp_get_post_terms( $post_id, dokan_pro()->brands->get_taxonomy(), [ 'fields' => 'ids' ] );

        if ( 'single' === dokan_pro()->brands->settings['mode'] ) {
            $selected = ( count( $selected ) > 0 ) ? $selected[0]: '';
        }

        self::print_form_field( $post_id, $selected, true );
    }

    /**
     * Save form data
     *
     * @since 2.9.7
     *
     * @param int   $product_id
     * @param array $data
     *
     * @return void
     */
    public static function set_product_brands( $product_id, $data ) {
        $brand_ids = [];

        if ( isset( $data['product_brand'] ) && is_array( $data['product_brand'] ) ) {

            foreach ( $data['product_brand'] as $brand_id ) {
                $brand_id = absint( $brand_id );

                if ( $brand_id ) {
                    $brand_ids[] = $brand_id;
                }
            }
        }

        wp_set_object_terms( $product_id, $brand_ids, dokan_pro()->brands->get_taxonomy() );
    }
}
