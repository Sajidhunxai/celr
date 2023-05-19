<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use Elementor\Widget_Base;

class ProductReportAbuse extends Widget_Base {

    /**
     * Widget name
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-product-report-abuse';
    }

    /**
     * Widget title
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_title() {
        return __( 'Dokan Product Report Abuse', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since 3.3.0
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-alert';
    }

    /**
     * Widget categories
     *
     * @since 3.3.0
     *
     * @return array
     */
    public function get_categories() {
        return [ 'woocommerce-elements-single' ];
    }

    /**
     * Widget keywords
     *
     * @since 3.3.0
     *
     * @return array
     */
    public function get_keywords() {
        return [ 'dokan', 'product', 'vendor', 'report-abuse' ];
    }

    /**
     * Register HTML widget controls.
     *
     * Adds different input fields to allow the user to change and customize the widget settings.
     *
     * @since 3.3.0
     * @access protected
     */
    protected function register_controls() {
        parent::register_controls();

        $this->start_controls_section(
            'section_title',
            [
                'label' => __( 'Report Abuse', 'dokan' ),
            ]
        );

        $this->add_control(
            'text',
            [
                'label'       => __( 'Label', 'dokan' ),
                'default'     => __( 'Report Abuse', 'dokan' ),
                'placeholder' => __( 'Report Abuse', 'dokan' ),
            ]
        );

        $this->end_controls_section();
    }

    /**
     * Frontend render method
     *
     * @since 3.3.0
     *
     * @return void
     */
    protected function render() {
        if ( ! is_singular( 'product' ) ) {
            return;
        }
        ?>
        <div <?php echo $this->get_render_attribute_string( 'wrapper' ); ?>>
            <a href="#report-abuse" class="dokan-report-abuse-button">
                <i class="fas fa-flag"></i> <?php echo $this->get_settings( 'text' ); ?>
            </a>
        </div>
        <?php
    }
}
