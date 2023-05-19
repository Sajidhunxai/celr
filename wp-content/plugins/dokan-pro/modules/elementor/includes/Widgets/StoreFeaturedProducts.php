<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\DokanStoreProducts;

class StoreFeaturedProducts extends DokanStoreProducts {

    /**
     * Widget name.
     *
     * @since 3.7.4
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-featured-products';
    }

    /**
     * Widget title.
     *
     * @since 3.7.4
     *
     * @return string
     */
    public function get_title() {
        return __( 'Featured Products', 'dokan' );
    }

    /**
     * Get products type.
     *
     * @since 3.7.4
     *
     * @return string
     */
    public function get_products_type() {
        return 'featured';
    }

    /**
     * Widget icon class.
     *
     * @since 3.7.4
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-star-o';
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
            'control_section_title'   => __( 'Store Featured Products', 'dokan' ),
            'title_input_label'       => __( 'Featured Products Title', 'dokan' ),
            'title_input_default'     => __( 'Featured Products', 'dokan' ),
            'title_input_placeholder' => __( 'Enter featured products title', 'dokan' ),
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
            'section_id' => 'dokan-featured-products',
            'products'   => dokan_get_featured_products( $this->get_settings( 'products_limit' ), $vendor->get_id() ),
        ];
    }
}
