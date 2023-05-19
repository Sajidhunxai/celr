<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\DokanStoreProducts;

class StoreTopRatedProducts extends DokanStoreProducts {

    /**
     * Widget name.
     *
     * @since 3.7.4
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-top-rated-products';
    }

    /**
     * Widget title.
     *
     * @since 3.7.4
     *
     * @return string
     */
    public function get_title() {
        return __( 'Top Rated Products', 'dokan' );
    }

    /**
     * Get products type.
     *
     * @since 3.7.4
     *
     * @return string
     */
    public function get_products_type() {
        return 'top_rated';
    }

    /**
     * Widget icon class.
     *
     * @since 3.7.4
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-icon-box';
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
            'control_section_title'   => __( 'Store Top Rated Products', 'dokan' ),
            'title_input_label'       => __( 'Top Rated Products Title', 'dokan' ),
            'title_input_default'     => __( 'Top Rated Products', 'dokan' ),
            'title_input_placeholder' => __( 'Enter top rated products title', 'dokan' ),
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
            'section_id' => 'dokan-top-rated-products',
            'products'   => dokan_get_top_rated_products( $this->get_settings( 'products_limit' ), $vendor->get_id() ),
        ];
    }
}
