<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\DokanButton;
use Elementor\Controls_Manager;

class StoreShareButton extends DokanButton {

    /**
     * Widget name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-share-button';
    }

    /**
     * Widget title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Share Button', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-share';
    }

    /**
     * Widget keywords
     *
     * @since 2.9.11
     *
     * @return array
     */
    public function get_keywords() {
        return [ 'dokan', 'store', 'vendor', 'button', 'share' ];
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
            'text',
            [
                'default'     => __( 'Share', 'dokan' ),
                'placeholder' => __( 'Share', 'dokan' ),
            ]
        );

        $this->update_control(
            'link',
            [
                'type' => Controls_Manager::HIDDEN,
            ]
        );

        $this->update_control(
            'icon',
            [
                'default' => 'fa fa-external-link',
            ]
        );
    }

    /**
     * Button wrapper class
     *
     * @since 2.9.11
     *
     * @return string
     */
    protected function get_button_wrapper_class() {
        return parent::get_button_wrapper_class() . ' dokan-share-btn-wrap';
    }
    /**
     * Button class
     *
     * @since 2.9.11
     *
     * @return string
     */
    protected function get_button_class() {
        return 'dokan-share-btn';
    }
}
