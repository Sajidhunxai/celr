<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\DokanStoreProducts;

class StoreBestSellingProducts extends DokanStoreProducts {

    /**
     * Widget name.
     *
     * @since 3.7.4
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-best-selling-products';
    }

    /**
     * Widget title.
     *
     * @since 3.7.4
     *
     * @return string
     */
    public function get_title() {
        return __( 'Best Selling Products', 'dokan' );
    }

    /**
     * Get products type.
     *
     * @since 3.7.4
     *
     * @return string
     */
    public function get_products_type() {
        return 'best_selling';
    }

    /**
     * Widget icon class.
     *
     * @since 3.7.4
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-cart-medium';
    }

    /**
     * Get widget control section data.
     *
     * @since 3.7.4
     *
     * @return array
     */
    protected function get_widget_control_data() {
        return [
            'control_section_title'   => __( 'Store Best Selling Products', 'dokan' ),
            'title_input_label'       => __( 'Best Selling Products Title', 'dokan' ),
            'title_input_default'     => __( 'Best Selling Products', 'dokan' ),
            'title_input_placeholder' => __( 'Enter best selling products title', 'dokan' ),
        ];
    }

    /**
     * Get widget output data.
     *
     * @since 3.7.4
     *
     * @return array
     */
    protected function get_widget_output_data() {
        $vendor = dokan()->vendor->get( get_query_var( 'author' ) );

        return [
            'section_id' => 'dokan-best-selling-products',
            'products'   => dokan_get_best_selling_products( $this->get_settings( 'products_limit' ), $vendor->get_id() ),
        ];
    }
}
