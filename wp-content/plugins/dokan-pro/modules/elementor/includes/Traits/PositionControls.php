<?php

namespace WeDevs\DokanPro\Modules\Elementor\Traits;

use Elementor\Controls_Manager;

trait PositionControls {
    /**
     * Add css position controls
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function add_position_controls() {
        $this->start_injection(
            [
                'type' => 'section',
                'at'   => 'start',
                'of'   => '_section_style',
            ]
        );

        $this->start_controls_section(
            'section_position',
            [
                'label' => __( 'Position', 'dokan' ),
                'tab'   => Controls_Manager::TAB_ADVANCED,
            ]
        );

        $this->add_responsive_control(
            '_dokan_position',
            [
                'label'   => __( 'Position', 'dokan' ),
                'type'    => Controls_Manager::SELECT,
                'options' => [
                    'static'   => __( 'Static', 'dokan' ),
                    'relative' => __( 'Relative', 'dokan' ),
                    'absolute' => __( 'Absolute', 'dokan' ),
                    'sticky'   => __( 'Sticky', 'dokan' ),
                    'fixed'    => __( 'Fixed', 'dokan' ),
                ],
                'desktop_default' => 'relative',
                'tablet_default'  => 'relative',
                'mobile_default'  => 'relative',
                'selectors' => [
                    '{{WRAPPER}}' => 'position: relative; min-height: 1px',
                    '{{WRAPPER}} > .elementor-widget-container' => 'position: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            '_dokan_position_top',
            [
                'label'     => __( 'Top', 'dokan' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} > .elementor-widget-container' => 'top: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            '_dokan_position_right',
            [
                'label'     => __( 'Right', 'dokan' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} > .elementor-widget-container' => 'right: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            '_dokan_position_bottom',
            [
                'label'     => __( 'Bottom', 'dokan' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} > .elementor-widget-container' => 'bottom: {{VALUE}};',
                ],
            ]
        );

        $this->add_responsive_control(
            '_dokan_position_left',
            [
                'label'     => __( 'Left', 'dokan' ),
                'type'      => Controls_Manager::TEXT,
                'default'   => '',
                'selectors' => [
                    '{{WRAPPER}} > .elementor-widget-container' => 'left: {{VALUE}};',
                ],
            ]
        );

        $this->end_controls_section();

        $this->end_injection();
    }
}
