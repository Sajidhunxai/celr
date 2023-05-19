<?php

namespace WeDevs\DokanPro\Modules\Elementor\Tags;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\TagBase;

class StoreProductFilter extends TagBase {
    /**
     * Tag name
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-product-filter';
    }

    /**
     * Tag title
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Product Filter', 'dokan' );
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
