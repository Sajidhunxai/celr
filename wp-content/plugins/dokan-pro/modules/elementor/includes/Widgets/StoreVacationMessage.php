<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use WeDevs\DokanPro\Modules\Elementor\Controls\DynamicHidden;
use Elementor\Controls_Manager;
use Elementor\Widget_Alert;

class StoreVacationMessage extends Widget_Alert {

    /**
     * Widget name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-vacation-message';
    }

    /**
     * Widget title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Vacation Message', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-alert';
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
        return [ 'dokan', 'store', 'vendor', 'vacation', 'message', 'alert' ];
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
            'alert_title',
            [
                'label'   => __( 'Title', 'dokan' ),
                'default' => __( 'We are on vacation!', 'dokan' ),
            ]
        );

        $this->update_control(
            'alert_description',
            [
                'type'      => DynamicHidden::CONTROL_TYPE,
                'dynamic'   => [
                    'default' => dokan_elementor()->elementor()->dynamic_tags->tag_data_to_tag_text( null, 'dokan-store-vacation-message' ),
                    'active' => true,
                ],
            ],
            [
                'recursive' => true,
            ]
        );

        $this->update_control(
            'show_dismiss',
            [
                'type' => Controls_Manager::HIDDEN,
            ]
        );

        $this->add_control(
            'show_title',
            [
                'label' => __( 'Show Title', 'dokan' ),
                'type' => Controls_Manager::SELECT,
                'default' => 'hide',
                'options' => [
                    'show' => __( 'Show', 'dokan' ),
                    'hide' => __( 'Hide', 'dokan' ),
                ],
            ],
            [
                'position' => [ 'of' => 'show_dismiss' ],
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
        return parent::get_html_wrapper_class() . ' dokan-store-vacation-message elementor-widget-' . parent::get_name();
    }

    /**
     * Frontend render method
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        if ( empty( $settings['alert_description'] ) ) {
            return;
        }

        if ( ! empty( $settings['alert_title'] ) ) {
            $this->add_render_attribute( 'alert_title', 'class', 'elementor-alert-title' );

            $this->add_inline_editing_attributes( 'alert_title', 'none' );
        }

        if ( ! empty( $settings['alert_type'] ) ) {
            $this->add_render_attribute( 'wrapper', 'class', 'elementor-alert elementor-alert-' . $settings['alert_type'] );
        }

        $this->add_render_attribute( 'wrapper', 'role', 'alert' );
        ?>
        <div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
            <?php if ( ! empty( $settings['show_title'] ) && 'show' === $settings['show_title'] && ! empty( $settings['alert_title'] ) ) : ?>
                <span <?php echo $this->get_render_attribute_string( 'alert_title' ); ?>><?php echo $settings['alert_title']; ?></span>
            <?php endif; ?>

            <?php
            if ( ! empty( $settings['alert_description'] ) ) :
                $this->add_render_attribute( 'alert_description', 'class', 'elementor-alert-description' );

                $this->add_inline_editing_attributes( 'alert_description' );
                ?>
                <span <?php echo $this->get_render_attribute_string( 'alert_description' ); ?>><?php echo $settings['alert_description']; ?></span>
            <?php endif; ?>
        </div>
        <?php
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
            if ( settings.alert_title ) {
                view.addRenderAttribute( {
                    alert_title: { class: 'elementor-alert-title' },
                } );

                view.addInlineEditingAttributes( 'alert_title', 'none' );
            }

            view.addRenderAttribute( {
                alert_description: { class: 'elementor-alert-description' }
            } );

            view.addInlineEditingAttributes( 'alert_description' );
        #>
        <div class="elementor-alert elementor-alert-{{ settings.alert_type }}" role="alert">
            <# if ( 'show' === settings.show_title && settings.alert_title ) { #>
                <span {{{ view.getRenderAttributeString( 'alert_title' ) }}}>{{{ settings.alert_title }}}</span>
            <# } #>
            <span {{{ view.getRenderAttributeString( 'alert_description' ) }}}>{{{ settings.alert_description }}}</span>
        </div>
        <?php
    }

    /**
     * Render widget plain content
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function render_plain_content() {}
}
