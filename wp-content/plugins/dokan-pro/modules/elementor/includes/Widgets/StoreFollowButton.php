<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\DokanButton;
use Elementor\Controls_Manager;
use Elementor\Widget_Base;

class StoreFollowButton extends DokanButton {

    protected $button_args = [];

    /**
     * Widget name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-follow-store-button';
    }

    /**
     * Widget title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Follow Button', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-eye';
    }

    /**
     * Widget keywords
     *
     * @since 2.9.11
     *
     * @return array
     */
    public function get_keywords() {
        return [ 'dokan', 'store', 'vendor', 'button', 'follow' ];
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
                    'default' => dokan_elementor()->elementor()->dynamic_tags->tag_data_to_tag_text( null, 'dokan-store-follow-store-button-tag' ),
                    'active'  => true,
                ],
                'selectors' => [
                    '{{WRAPPER}} > .elementor-widget-container > .elementor-button-wrapper > .dokan-store-follow-store-btn' => 'width: auto; margin: 0;',
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
        return parent::get_button_wrapper_class() . ' dokan-store-follow-store-btn-wrap';
    }
    /**
     * Button class
     *
     * @since 2.9.11
     *
     * @return string
     */
    protected function get_button_class() {
        $classes = 'dokan-store-follow-store-btn dokan-follow-store-button dokan-follow-store-buttons-wrapper';

        return $classes;
    }

    /**
     * Render button
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function render() {
        if ( ! dokan_is_store_page() ) {
            parent::render();
            return;
        }

        if ( ! class_exists( \WeDevs\DokanPro\Modules\FollowStore\Module::class ) ) {
            return;
        }

        $store_data = dokan_elementor()->get_store_data();
        $vendor = dokan()->vendor->get( $store_data['id'] );

        $this->button_args = dokan_follow_store_get_button_args( $vendor->data );

        $this->add_render_attribute( 'button', 'data-vendor-id', $this->button_args['vendor_id'] );
        $this->add_render_attribute( 'button', 'data-status', $this->button_args['status'] );
        $this->add_render_attribute( 'button', 'data-is-logged-in', $this->button_args['is_logged_in'] );

        parent::render();
    }

    /**
     * Render button text.
     *
     * Render button widget text.
     *
     * @since 1.5.0
     *
     * @param \Elementor\Widget_Base|null $instance
     * @access protected
     */
    protected function render_text( Widget_Base $instance = null ) {
        if ( ! dokan_is_store_page() ) {
            parent::render_text( $instance );
            return;
        }

        $settings = $this->get_settings_for_display();

        $this->add_render_attribute(
            [
                'content-wrapper' => [
                    'class' => 'elementor-button-content-wrapper',
                ],
                'icon-align' => [
                    'class' => [
                        'elementor-button-icon',
                        'elementor-align-icon-' . $settings['icon_align'],
                    ],
                ],
                'text' => [
                    'class' => 'elementor-button-text',
                ],
            ]
        );

        $this->add_inline_editing_attributes( 'text', 'none' );
        ?>
        <?php if ( ! empty( $settings['selected_icon']['value'] ) ) : ?>
            <span <?php echo $this->get_render_attribute_string( 'icon-align' ); ?>>
                <i class="<?php echo esc_attr( $settings['selected_icon']['value'] ); ?>" aria-hidden="true"></i>
            </span>
        <?php endif; ?>

        <span class="dokan-follow-store-button-label-current elementor-button-text"><?php echo $this->button_args['label_current']; ?></span>
        <span class="dokan-follow-store-button-label-unfollow elementor-button-text"><?php echo $this->button_args['label_unfollow']; ?></span>
        <?php
    }
}
