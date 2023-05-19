<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\DokanStoreProducts;

class StoreLatestProducts extends DokanStoreProducts {

    /**
     * Widget name.
     *
     * @since 3.7.4
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-latest-products';
    }

    /**
     * Widget title.
     *
     * @since 3.7.4
     *
     * @return string
     */
    public function get_title() {
        return __( 'Latest Products', 'dokan' );
    }

    /**
     * Get products type.
     *
     * @since 3.7.4
     *
     * @return string
     */
    public function get_products_type() {
        return 'latest';
    }

    /**
     * Widget icon class.
     *
     * @since 3.7.4
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-facebook-like-box';
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
            'control_section_title'   => __( 'Store Latest Products', 'dokan' ),
            'title_input_label'       => __( 'Latest Products Title', 'dokan' ),
            'title_input_default'     => __( 'Latest Products', 'dokan' ),
            'title_input_placeholder' => __( 'Enter latest products title', 'dokan' ),
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
            'section_id' => 'dokan-latest-products',
            'products'   => dokan_get_latest_products( $this->get_settings( 'products_limit' ), $vendor->get_id() ),
        ];
    }
}
