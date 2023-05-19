<?php

namespace WeDevs\DokanPro\Modules\Elementor\Conditions;

use ElementorPro\Modules\ThemeBuilder\Conditions\Condition_Base;

class Store extends Condition_Base {

    /**
     * Type of condition
     *
     * @since 2.9.11
     *
     * @return string
     */
    public static function get_type() {
        return 'store';
    }

    /**
     * Condition name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'store';
    }

    /**
     * Condition label
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_label() {
        return __( 'Single Store', 'dokan' );
    }

    /**
     * Condition label for all items
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_all_label() {
        return __( 'All Stores', 'dokan' );
    }

    /**
     * Check if proper conditions are met
     *
     * @since 2.9.11
     *
     * @param array $args
     *
     * @return bool
     */
    public function check( $args ) {
        return dokan_is_store_page();
    }
}
