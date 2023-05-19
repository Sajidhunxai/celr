<?php

namespace WeDevs\DokanPro\Modules\Elementor\Abstracts;

use WeDevs\DokanPro\Modules\Elementor\Traits\PositionControls;
use Elementor\Controls_Manager;
use Elementor\Widget_Button;

abstract class DokanButton extends Widget_Button {

    use PositionControls;

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
     * Register widget controls
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function register_controls() {
        parent::register_controls();

        // Set default colors.
        $btn_text           = '#fff';
        $btn_primary        = '#f05025';
        $btn_primary_border = '#DA502B';

        // If colors module activated then get admin settings color.
        if ( dokan_pro()->module->is_active( 'color_scheme_customizer' ) ) {
            $colors             = dokan_get_option( 'store_color_pallete', 'dokan_colors', [] );
            $btn_text           = ! empty( $colors['btn_text'] ) ? $colors['btn_text'] : $btn_text;
            $btn_primary        = ! empty( $colors['btn_primary'] ) ? $colors['btn_primary'] : $btn_primary;
            $btn_primary_border = ! empty( $colors['btn_primary_border'] ) ? $colors['btn_primary_border'] : $btn_primary_border;
        }

        $this->update_control(
            'icon_align',
            [
                'default' => 'right',
            ]
        );

        $this->update_control(
            'button_text_color',
            [
                'default' => $btn_text,
            ]
        );

        $this->update_control(
            'background_color',
            [
                'default' => $btn_primary,
            ]
        );

        $this->update_control(
            'border_color',
            [
                'default' => $btn_primary_border,
            ]
        );

        $this->add_position_controls();
    }

    /**
     * Button wrapper class
     *
     * @since 2.9.11
     *
     * @return string
     */
    protected function get_button_wrapper_class() {
        return 'dokan-btn-wrap';
    }

    /**
     * Set wrapper classes
     *
     * @since 2.9.11
     *
     * @return string
     */
    protected function get_html_wrapper_class() {
        return parent::get_html_wrapper_class() . ' ' . $this->get_button_wrapper_class() . ' elementor-widget-' . parent::get_name();
    }

    /**
     * Button class
     *
     * @since 2.9.11
     *
     * @return string
     */
    protected function get_button_class() {
        return 'dokan-btn';
    }

    /**
     * Frontend render method
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function render() {
        $this->add_render_attribute(
            'button',
            'class',
            [ $this->get_button_class() ]
        );

        parent::render();
    }
}
