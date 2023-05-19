<?php

namespace WeDevs\DokanPro\Modules\Elementor\Abstracts;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

abstract class DokanStoreProducts extends Widget_Base {

    /**
     * Get products type.
     *
     * @since 3.7.4
     *
     * @return string
     */
    abstract protected function get_products_type();

    /**
     * Get widget control section data.
     *
     * @since 3.7.4
     *
     * @return array
     */
    abstract protected function get_widget_control_data();

    /**
     * Get widget output data.
     *
     * @since 3.7.4
     *
     * @return array
     */
    abstract protected function get_widget_output_data();

    /**
     * Widget icon class.
     *
     * @since 3.7.4
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-products';
    }

    /**
     * Widget categories.
     *
     * @since 3.7.4
     *
     * @return array
     */
    public function get_categories() {
        return [ 'dokan-store-elements-single' ];
    }

    /**
     * Widget keywords.
     *
     * @since 3.7.4
     *
     * @return array
     */
    public function get_keywords() {
        return [ 'dokan', 'product', 'vendor', 'store-products-section' ];
    }

    /**
     * Register HTML widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 3.7.4
     * @access protected
     *
     * @return void
     */
    protected function register_controls() {
        $control_data = $this->get_widget_control_data();

        $this->start_controls_section(
            'section_title',
            [
                'label' => $control_data['control_section_title'],
            ]
        );

        $this->add_control(
            'products_section_title',
            [
                'type'        => Controls_Manager::TEXT,
                'label'       => $control_data['title_input_label'],
                'default'     => $control_data['title_input_default'],
                'placeholder' => $control_data['title_input_placeholder'],
            ]
        );

        $this->add_control(
            'products_limit',
            [
                'type'        => Controls_Manager::NUMBER,
                'label'       => esc_html__( 'Number of products', 'dokan' ),
                'placeholder' => '',
                'min'         => 1,
                'max'         => 20,
                'step'        => 1,
                'default'     => get_option( 'woocommerce_catalog_columns', 3 ),
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Frontend render method.
     *
     * @since 3.7.4
     *
     * @return void
     */
    protected function render() {
        // Check if current page is store page and dokan pro elementor module activated.
        if ( ! dokan_is_store_page() && ! dokan_elementor()->is_edit_or_preview_mode() ) {
            return;
        }

        // Check if current products section visibility enabled from customizer.
        if ( ! $this->is_enabled() ) {
            return;
        }

        $vendor = dokan()->vendor->get( get_query_var( 'author' ) );

        // Check if current products section visible by vendor.
        if ( ! $this->is_visible_by_vendor( $vendor->get_id() ) ) {
            return;
        }

        $products_data = $this->get_widget_output_data();

        // Check if there is any products.
        if ( ! $products_data['products']->have_posts() ) {
            return;
        }

        // Include products section template after passing all checks.
        dokan_get_template_part(
            'store-products-section', '', [
                'section_title' => $this->get_settings( 'products_section_title' ),
                'section_id'    => $products_data['section_id'],
                'products'      => $products_data['products'],
                'vendor'        => $vendor,
            ]
        );
    }

    /**
     * Check products block visibility settings in customizer.
     *
     * @since 3.7.4
     *
     * @return bool
     */
    public function is_enabled() {
        $customizer_settings = dokan_get_option( 'product_sections', 'dokan_appearance' );

        // Check if current products section enabled by admin.
        // For customizer settings default value is hide sections.
        if ( ! isset( $customizer_settings[ $this->get_products_type() ] ) || 'on' === $customizer_settings[ $this->get_products_type() ] ) {
            return false;
        }

        return true;
    }

    /**
     * Check products block visibility settings in store.
     *
     * @since 3.7.4
     *
     * @param int $vendor_id
     *
     * @return bool
     */
    public function is_visible_by_vendor( $vendor_id ) {
        $store_info = get_user_meta( $vendor_id, 'dokan_profile_settings', true );

        // Check if current products section visible by vendor.
        // For store settings default value is show sections.
        if ( ! isset( $store_info['product_sections'][ $this->get_products_type() ] ) || 'yes' === $store_info['product_sections'][ $this->get_products_type() ] ) {
            return true;
        }

        return false;
    }
}
