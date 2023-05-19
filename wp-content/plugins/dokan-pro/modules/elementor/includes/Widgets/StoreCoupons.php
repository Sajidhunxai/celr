<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use WeDevs\DokanPro\Modules\Elementor\Controls\DynamicHidden;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Text_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Core\Schemes\Typography;
use Elementor\Core\Schemes\Color;
use Elementor\Widget_Base;

class StoreCoupons extends Widget_Base {

    /**
     * Widget name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-coupons';
    }

    /**
     * Widget title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Coupons', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_icon() {
        return 'fa fa-scissors';
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
        return [ 'dokan', 'store', 'vendor', 'coupon' ];
    }

    /**
     * Register widget controls
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function register_controls() {
        $fields = [
            'title' => [
                'section_title' => __( 'Title', 'dokan' ),
            ],

            'description' => [
                'section_title' => __( 'Description', 'dokan' ),
            ],
            'code' => [
                'section_title' => __( 'Code', 'dokan' ),
            ],
            'expiration' => [
                'section_title' => __( 'Expiration', 'dokan' ),
            ],
        ];

        foreach ( $fields as $field_name => $field ) {
            $selector = '{{WRAPPER}} .dokan-elementor-store-coupon-' . $field_name;

            $this->start_controls_section(
                'section_title_' . $field_name,
                [
                    'label' => $field['section_title'],
                ]
            );

            $this->add_responsive_control(
                'align_' . $field_name,
                [
                    'label'     => __( 'Alignment', 'dokan' ),
                    'type'      => Controls_Manager::CHOOSE,
                    'options'   => [
                        'left'    => [
                            'title' => __( 'Left', 'dokan' ),
                            'icon'  => 'fa fa-align-left',
                        ],
                        'center'  => [
                            'title' => __( 'Center', 'dokan' ),
                            'icon'  => 'fa fa-align-center',
                        ],
                        'right'   => [
                            'title' => __( 'Right', 'dokan' ),
                            'icon'  => 'fa fa-align-right',
                        ],
                        'justify' => [
                            'title' => __( 'Justified', 'dokan' ),
                            'icon'  => 'fa fa-align-justify',
                        ],
                    ],
                    'default'   => 'center',
                    'selectors' => [
                        '{{WRAPPER}} .dokan-elementor-store-coupon-' . $field_name . '-container' => 'text-align: {{VALUE}};',
                    ],
                ]
            );

            $this->add_control(
                'color_' . $field_name,
                [
                    'label'     => __( 'Text Color', 'dokan' ),
                    'type'      => Controls_Manager::COLOR,
                    'scheme'    => [
                        'type'  => Color::get_type(),
                        'value' => Color::COLOR_1,
                    ],
                    'selectors' => [
                        $selector => 'color: {{VALUE}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name'     => 'typography_' . $field_name,
                    'scheme'   => Typography::TYPOGRAPHY_1,
                    'selector' => $selector,
                ]
            );

            $this->add_group_control(
                Group_Control_Text_Shadow::get_type(),
                [
                    'name'     => 'text_shadow_' . $field_name,
                    'selector' => $selector,
                ]
            );

            $this->add_responsive_control(
                'margin_' . $field_name,
                [
                    'label'      => __( 'Margin', 'dokan' ),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'selectors'  => [
                        '{{WRAPPER}} .dokan-elementor-store-coupon-' . $field_name . '-container' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_responsive_control(
                'padding_' . $field_name,
                [
                    'label'      => __( 'Padding', 'dokan' ),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'selectors'  => [
                        $selector => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Border::get_type(),
                [
                    'name'     => 'border_' . $field_name,
                    'selector' => $selector,
                ]
            );

            $this->add_control(
                'border_radius_' . $field_name,
                [
                    'label'      => __( 'Border Radius', 'dokan' ),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', '%' ],
                    'selectors'  => [
                        $selector => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Box_Shadow::get_type(),
                [
                    'name'     => 'box_shadow_' . $field_name,
                    'selector' => $selector,
                ]
            );

            $this->end_controls_section();
        }

        // Style Tab
        $this->start_controls_section(
            'coupon_inner_styles',
            [
                'label' => __( 'Coupon Inner Style', 'dokan' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'coupon_inner_padding',
            [
                'label'      => __( 'Padding', 'dokan' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                ],
                'default'    => [
                    'unit' => 'px',
                    'size' => 10,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .dokan-elementor-store-coupon-inner' => 'padding: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'coupon_inner_border_type',
                'selector' => '{{WRAPPER}} .dokan-elementor-store-coupon-inner',
            ]
        );

        $this->add_control(
            'coupon_inner_border_radius',
            [
                'label'      => __( 'Border Radius', 'dokan' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .dokan-elementor-store-coupon-inner' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'coupon_inner_box_shadow',
                'selector' => '{{WRAPPER}} .dokan-elementor-store-coupon-inner',
            ]
        );

        $this->end_controls_section();

        $this->start_controls_section(
            'coupon_background',
            [
                'label' => __( 'Coupon Background', 'dokan' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs( 'coupon_tabs_background' );

        $this->start_controls_tab(
            'coupon_tab_background_normal',
            [
                'label' => __( 'Normal', 'dokan' ),
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'coupon_background',
                'selector' => '{{WRAPPER}} .dokan-elementor-store-coupon-single',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'default' => 'rgba(0,174,255,0.06)',
                    ],
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'coupon_tab_background_hover',
            [
                'label' => __( 'Hover', 'dokan' ),
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'coupon_background_hover',
                'selector' => '{{WRAPPER}} .dokan-elementor-store-coupon-single:hover',
            ]
        );

        $this->add_control(
            'coupon_background_hover_transition',
            [
                'label' => __( 'Transition Duration', 'dokan' ),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'max'  => 3,
                        'step' => 0.1,
                    ],
                ],
                'render_type' => 'ui',
                'separator' => 'before',
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->end_controls_section();

        $this->start_controls_section(
            'coupon_outer_styles',
            [
                'label' => __( 'Coupon Outer Style', 'dokan' ),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'coupon_outer_padding',
            [
                'label'      => __( 'Padding', 'dokan' ),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => [ 'px' ],
                'range'      => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                ],
                'default'    => [
                    'unit' => 'px',
                    'size' => 10,
                ],
                'selectors'  => [
                    '{{WRAPPER}} .dokan-elementor-store-coupon-single' => 'padding: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name'     => 'coupon_outer_border_type',
                'selector' => '{{WRAPPER}} .dokan-elementor-store-coupon-single',
                'fields_options' => [
                    'border' => [
                        'default' => 'dashed',
                    ],
                    'width' => [
                        'default'    => [
                            'unit' => 'px',
                            'size' => 2,
                        ],
                    ],
                    'color' => [
                        'default' => 'rgba(0,174,255,0.25)',
                    ],
                ],
            ]
        );

        $this->add_control(
            'coupon_outer_border_radius',
            [
                'label'      => __( 'Border Radius', 'dokan' ),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => [ 'px', '%' ],
                'selectors'  => [
                    '{{WRAPPER}} .dokan-elementor-store-coupon-single' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'coupon_outer_box_shadow',
                'selector' => '{{WRAPPER}} .dokan-elementor-store-coupon-single',
            ]
        );

        $this->add_control(
            'store_coupons',
            [
                'type' => DynamicHidden::CONTROL_TYPE,
                'dynamic' => [
                    'default' => dokan_elementor()->elementor()->dynamic_tags->tag_data_to_tag_text( null, 'dokan-store-coupons-tag' ),
                    'active'  => true,
                ],
            ]
        );

        $this->end_controls_section();
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

        if ( empty( $settings['store_coupons'] ) ) {
            return;
        }

        $store_coupons = json_decode( $settings['store_coupons'], true );

        if ( ! empty( $store_coupons ) ) :
            ?>
            <div class="dokan-elementor-store-coupons">
                <?php foreach ( $store_coupons as $store_coupon ) : ?>
                    <div class="dokan-elementor-store-coupon-single">
                        <div class="dokan-elementor-store-coupon-inner">
                            <div class="dokan-elementor-store-coupon-title-container">
                                <span class="dokan-elementor-store-coupon-title"><?php echo $store_coupon['coupon_title']; ?></span>
                            </div>
                            <div class="dokan-elementor-store-coupon-description-container">
                                <span class="dokan-elementor-store-coupon-description"><?php echo esc_html( $store_coupon['coupon']['post_content'] ); ?></span>
                            </div>
                            <div class="dokan-elementor-store-coupon-code-container">
                                <span class="dokan-elementor-store-coupon-code"><?php echo esc_html( $store_coupon['coupon']['post_title'] ); ?></span>
                            </div>
                            <div class="dokan-elementor-store-coupon-expiration-container">
                                <span class="dokan-elementor-store-coupon-expiration"><?php echo esc_html( $store_coupon['expiry_date'] ); ?></span>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
            <?php
        endif;

        $this->add_inline_style();
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
                if ( ! settings.store_coupons ) {
                    return;
                }

                var store_coupons = JSON.parse(settings.store_coupons);
            #>
            <# if ( store_coupons.length ) { #>
                <div class="dokan-elementor-store-coupons">
                    <# for( i = 0; i < store_coupons.length; i++ ) { var store_coupon = store_coupons[i]; #>
                        <div class="dokan-elementor-store-coupon-single">
                            <div class="dokan-elementor-store-coupon-inner">
                                <div class="dokan-elementor-store-coupon-title-container">
                                    <span class="dokan-elementor-store-coupon-title">{{ store_coupon.coupon_title }}</span>
                                </div>
                                <div class="dokan-elementor-store-coupon-description-container">
                                    <span class="dokan-elementor-store-coupon-description">{{ store_coupon.coupon.post_content }}</span>
                                </div>
                                <div class="dokan-elementor-store-coupon-code-container">
                                    <span class="dokan-elementor-store-coupon-code">{{ store_coupon.coupon.post_title }}</span>
                                </div>
                                <div class="dokan-elementor-store-coupon-expiration-container">
                                    <span class="dokan-elementor-store-coupon-expiration">{{ store_coupon.expiry_date }}</span>
                                </div>
                            </div>
                        </div>
                    <# } #>
                </div>
            <# } #>
        <?php

        $this->add_inline_style();
    }

    /**
     * Render widget plain content
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function render_plain_content() {}

    /**
     * Add default inline style for coupons
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function add_inline_style() {
        ?>
            <style>
                .dokan-elementor-store-coupons {
                    display: flex;
                    flex-wrap: wrap;
                }

                .dokan-elementor-store-coupon-single {
                    padding: 10px;
                    width: 23%;
                    margin: 1%;
                }

                .dokan-elementor-store-coupon-single:nth-of-type(4n) {
                    margin-right: 0;
                }

                .dokan-elementor-store-coupon-single:nth-of-type(4n+1) {
                    margin-left: 0;
                }

                .elementor-widget-dokan-store-coupons .dokan-elementor-store-coupon-title {
                    display: inline-block;
                    color: #006789;
                    font-size: 20px;
                    font-weight: 400;
                    font-family: inherit;
                    margin: 0 0 5px 0;
                }

                .elementor-widget-dokan-store-coupons .dokan-elementor-store-coupon-description {
                    display: inline-block;
                    color: #444444;
                    font-size: 13px;
                    font-weight: 400;
                    font-family: inherit;
                    margin: 0 0 5px 0;
                }

                .elementor-widget-dokan-store-coupons .dokan-elementor-store-coupon-code {
                    display: inline-block;
                    color: #006789;
                    font-size: 14px;
                    font-weight: 500;
                    font-family: inherit;
                    padding: 5px;
                    margin: 6px 0 8px 0;
                    border: 2px dotted #006789;
                }

                .elementor-widget-dokan-store-coupons .dokan-elementor-store-coupon-expiration {
                    display: inline-block;
                    color: rgba(0,103,137,0.78);
                    font-size: 12px;
                    font-weight: 500;
                    font-family: inherit;
                    margin: 0 0 0 0;
                }
            </style>
        <?php
    }
}
