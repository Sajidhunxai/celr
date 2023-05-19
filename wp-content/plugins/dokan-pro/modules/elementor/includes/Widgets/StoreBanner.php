<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use WeDevs\DokanPro\Modules\Elementor\Traits\PositionControls;
use Elementor\Controls_Manager;
use Elementor\Widget_Image;

class StoreBanner extends Widget_Image {

    use PositionControls;

    /**
     * Widget name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-banner';
    }

    /**
     * Widget title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Banner', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-image-box';
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
        return [ 'dokan', 'store', 'vendor', 'banner', 'picture', 'image', 'avatar' ];
    }

    /**
     * Register widget controls
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function register_controls() {
        parent::register_controls();

        $this->update_control(
            'section_image',
            [
                'label' => __( 'Banner', 'dokan' ),
            ]
        );

        $this->update_control(
            'image',
            [
                'dynamic' => [
                    'default' => dokan_elementor()->elementor()->dynamic_tags->tag_data_to_tag_text( null, 'dokan-store-banner' ),
                ],
                'selectors' => [
                    '{{WRAPPER}} > .elementor-widget-container > .elementor-image > img' => 'width: 100%;',
                ],
            ],
            [
                'recursive' => true,
            ]
        );

        $this->update_control(
            'caption_source',
            [
                'type' => Controls_Manager::HIDDEN,
            ]
        );

        $this->update_control(
            'caption',
            [
                'type' => Controls_Manager::HIDDEN,
            ]
        );

        $this->update_control(
            'link_to',
            [
                'type' => Controls_Manager::HIDDEN,
            ]
        );

        $this->add_position_controls();
    }

    /**
     * Html wrapper class
     *
     * @since 2.9.11
     *
     * @return string
     */
    protected function get_html_wrapper_class() {
        return parent::get_html_wrapper_class() . ' elementor-widget-' . parent::get_name();
    }
}
