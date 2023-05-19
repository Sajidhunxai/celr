<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

class StoreProductFilter extends Widget_Base {

    /**
     * Widget name
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-product-filter';
    }

    /**
     * Widget title
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_title() {
        return __( 'Dokan Store Product Filter', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-filter';
    }

    /**
     * Widget categories
     *
     * @since 3.3.0
     *
     * @return array
     */
    public function get_categories() {
        return [ 'dokan-store-elements-single' ];
    }

    /**
     * Widget keywords
     *
     * @since 3.3.0
     *
     * @return array
     */
    public function get_keywords() {
        return [ 'dokan', 'product', 'vendor', 'store-product-filter' ];
    }

    /**
     * Register HTML widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 3.3.0
     * @access protected
     */
    protected function register_controls() {
        parent::register_controls();

        $this->start_controls_section(
            'section_title',
            [
                'label' => __( 'Store Product Filter', 'dokan' ),
            ]
        );

        $this->add_control(
            'filter_product_name',
            [
                'label'        => __( 'Filter by Product', 'dokan' ),
                'type'         => 'switcher',
                'label_on'     => __( 'Show', 'dokan' ),
                'label_off'    => __( 'Hide', 'dokan' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'filter_product_name_placeholder',
            [
                'label'       => __( 'Product Placeholder', 'dokan' ),
                'default'     => __( 'Enter product name', 'dokan' ),
                'placeholder' => __( 'Enter product name', 'dokan' ),
            ]
        );

        $this->add_control(
            'filter_orderby',
            [
                'label'        => __( 'Filter by Orderby', 'dokan' ),
                'type'         => 'switcher',
                'label_on'     => __( 'Show', 'dokan' ),
                'label_off'    => __( 'Hide', 'dokan' ),
                'return_value' => 'yes',
                'default'      => 'yes',
            ]
        );

        $this->add_control(
            'text',
            [
                'label'       => __( 'Button Label', 'dokan' ),
                'default'     => __( 'Search', 'dokan' ),
                'placeholder' => __( 'Search', 'dokan' ),
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Frontend render method
     *
     * @since 3.3.0
     *
     * @return void
     */
    protected function render() {
        if ( ! dokan_is_store_page() && ! dokan_elementor()->is_edit_or_preview_mode() ) {
            return;
        }

        $orderby_options     = function_exists( 'dokan_store_product_catalog_orderby' ) ? dokan_store_product_catalog_orderby() : array();
        $button_label        = $this->get_settings( 'text' );
        $filter_product      = $this->get_settings( 'filter_product_name' );
        $product_placeholder = $this->get_settings( 'filter_product_name_placeholder' );
        $filter_orderby      = $this->get_settings( 'filter_orderby' );
        $store_user          = dokan()->vendor->get( get_query_var( 'author' ) );
        $store_id            = $store_user->get_id();
        ?>
        <div class="dokan-store-products-filter-area dokan-clearfix">
            <form class="dokan-store-products-ordeby" method="get">
                <?php if ( 'yes' === $filter_product ) : ?>
                    <input type="text" autocomplete="off" name="product_name" class="product-name-search dokan-store-products-filter-search" placeholder="<?php echo esc_attr( $product_placeholder ); ?>" data-store_id="<?php echo esc_attr( $store_id ); ?>">
                    <div id="dokan-store-products-search-result" class="dokan-ajax-store-products-search-result"></div>
                    <input type="submit" name="search_store_products" class="search-store-products dokan-btn-theme" value="<?php echo esc_attr( $button_label ); ?>">
                <?php endif; ?>
                <?php if ( 'yes' === $filter_orderby && is_array( $orderby_options['catalogs'] ) && isset( $orderby_options['orderby'] ) ) : ?>
                    <select name="product_orderby" class="orderby orderby-search" aria-label="<?php esc_attr_e( 'Shop order', 'dokan' ); ?>" onchange='if(this.value != 0) { this.form.submit(); }'>
                        <?php foreach ( $orderby_options['catalogs'] as $id => $name ) : ?>
                            <option value="<?php echo esc_attr( $id ); ?>" <?php selected( $orderby_options['orderby'], $id ); ?>><?php echo esc_html( $name ); ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php endif; ?>
                <input type="hidden" name="paged" value="1" />
            </form>
        </div>
        <?php
    }
}
