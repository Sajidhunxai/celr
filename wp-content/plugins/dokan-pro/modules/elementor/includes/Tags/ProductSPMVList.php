<?php

namespace WeDevs\DokanPro\Modules\Elementor\Tags;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\TagBase;

class ProductSPMVList extends TagBase {
    /**
     * Tag name
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-product-spmv-list';
    }

    /**
     * Tag title
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_title() {
        return __( 'Single Product MultiVendor List', 'dokan' );
    }

    /**
     * Render tag
     *
     * @since 3.3.0
     *
     * @return void
     */
    public function render() {
    }
}
