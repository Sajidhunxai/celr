<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use WeDevs\DokanPro\Modules\Elementor\Controls\DynamicHidden;
use WeDevs\DokanPro\Modules\Elementor\Widgets\StoreName;

class StoreTabContents extends StoreName {

    /**
     * Widget name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-tab-contents';
    }

    /**
     * Widget title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Tab Contents', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-products';
    }

    /**
     * Widget categories
     *
     * @since 2.9.11
     *
     * @return array
     */
    public function get_categories() {
        return [ 'dokan-store-elements-single' ];
    }

    /**
     * Widget keywords
     *
     * @since 2.9.11
     *
     * @return array
     */
    public function get_keywords() {
        return [ 'dokan', 'store', 'vendor', 'tab', 'content' ];
    }

    /**
     * Register widget controls
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function register_controls() {
        $this->add_control(
            'products',
            [
                'type' => DynamicHidden::CONTROL_TYPE,
                'dynamic' => [
                    'active' => true,
                    'default' => dokan_elementor()->elementor()->dynamic_tags->tag_data_to_tag_text( null, 'dokan-store-dummy-products' ),
                ],
            ],
            [
                'position' => [ 'of' => '_title' ],
            ]
        );
    }

    /**
     * Set wrapper classes
     *
     * @since 2.9.11
     *
     * @return string
     */
    protected function get_html_wrapper_class() {
        return parent::get_html_wrapper_class() . ' dokan-store-tab-content elementor-widget-' . parent::get_name();
    }

    /**
     * Frontend render method
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function render() {
        if ( dokan_is_store_page() ) {
            do_action( 'dokan_elementor_store_tab_content' );
        } else {
            $settings = $this->get_settings_for_display();

            echo $settings['products'];
        }
    }

    /**
     * Elementor builder content template
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function content_template() {
        ?>
            <#
                print( settings.products );
            #>
        <?php
    }
}
