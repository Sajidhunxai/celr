<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\DokanButton;
use Elementor\Controls_Manager;

class StoreSupportButton extends DokanButton {

    /**
     * Widget name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-support-button';
    }

    /**
     * Widget title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Support Button', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-person';
    }

    /**
     * Widget keywords
     *
     * @since 2.9.11
     *
     * @return array
     */
    public function get_keywords() {
        return [ 'dokan', 'store', 'vendor', 'button', 'support' ];
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
                'dynamic'   => [
                    'default' => dokan_elementor()->elementor()->dynamic_tags->tag_data_to_tag_text( null, 'dokan-store-support-button-tag' ),
                    'active'  => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} > .elementor-widget-container > .elementor-button-wrapper > .dokan-store-support-btn' => 'width: auto; margin: 0;',
                ],
            ]
        );

        $this->update_control(
            'link',
            [
                'type' => Controls_Manager::HIDDEN,
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
        return parent::get_button_wrapper_class() . ' dokan-store-support-btn-wrap';
    }
    /**
     * Button class
     *
     * @since 2.9.11
     *
     * @return string
     */
    protected function get_button_class() {
        $user_logged_class = 'user_logged_out';

        if ( is_user_logged_in() ) {
            $user_logged_class = 'user_logged';
        }

        return 'dokan-store-support-btn ' . $user_logged_class;
    }

    /**
     * Render button
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function render() {
        if ( dokan_is_store_page() ) {
            if ( ! dokan_pro()->module->is_active( 'store_support' ) ) {
                return;
            }

            $id = dokan_elementor()->get_store_data( 'id' );

            if ( ! $id ) {
                return;
            }

            // get store info
            $store_info = dokan_get_store_info( $id );
            // check if vendor disabled store support button
            if ( isset( $store_info['show_support_btn'] ) && 'no' === $store_info['show_support_btn'] ) {
                return;
            }

            $store_user = dokan()->vendor->get( get_query_var( 'author' ) );

            parent::add_render_attribute( 'button', 'data-store_id', $store_user->get_id() );
        }

        parent::render();
    }
}
