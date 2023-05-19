<?php

namespace WeDevs\DokanPro\Modules\Elementor\Tags;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\TagBase;

class StoreSupportButton extends TagBase {
    /**
     * Tag name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-support-button-tag';
    }

    /**
     * Tag title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Support Button', 'dokan' );
    }

    /**
     * Render tag
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function render() {
        if ( ! dokan_pro()->module->is_active( 'store_support' ) ) {
            echo __( 'Dokan Store Support module is not active', 'dokan' );
            return;
        }

        $text = __( 'Get Support', 'dokan' );

        if ( ! dokan_is_store_page() ) {
            echo $text;
            return;
        }

        $id = dokan_elementor()->get_store_data( 'id' );

        if ( ! $id ) {
            echo $text;
            return;
        }

        // get store info
        $store_info = dokan_get_store_info( $id );
        // check if vendor disabled store support button
        if ( isset( $store_info['show_support_btn'] ) && 'no' === $store_info['show_support_btn'] ) {
            echo $text;
            return;
        }

        $store_support  = dokan_pro()->module->store_support;
        $support_button = $store_support->get_support_button( $id );

        $text = $support_button['text'];

        echo $text;
    }
}
