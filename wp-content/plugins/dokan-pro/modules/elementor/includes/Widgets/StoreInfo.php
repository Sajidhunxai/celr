<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use WeDevs\DokanPro\Modules\Elementor\Controls\DynamicHidden;
use WeDevs\DokanPro\Modules\Elementor\Controls\SortableList;
use WeDevs\DokanPro\Modules\Elementor\Traits\PositionControls;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Widget_Icon_List;

class StoreInfo extends Widget_Icon_List {

    use PositionControls;

    /**
     * Widget name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-info';
    }

    /**
     * Widget title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Info', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-bullet-list';
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
        return [ 'dokan', 'store', 'vendor', 'info', 'summery', 'informations' ];
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
            'section_icon',
            [
                'label' => __( 'Store Info', 'dokan' ),
            ]
        );

        $repeater = new Repeater();

        $repeater->add_control(
            'title',
            [
                'label'   => __( 'Title', 'dokan' ),
                'type'    => Controls_Manager::HIDDEN,
                'default' => __( 'Title', 'dokan' ),
            ]
        );

        $repeater->add_control(
            'text',
            [
                'type' => Controls_Manager::HIDDEN,
            ]
        );

        $repeater->add_control(
            'icon',
            [
                'label'       => __( 'Icon', 'dokan' ),
                'type'        => Controls_Manager::ICON,
                'label_block' => true,
                'default'     => 'fas fa-check',
            ]
        );

        $repeater->add_control(
            'show',
            [
                'type'    => Controls_Manager::HIDDEN,
                'default' => true,
            ]
        );

        $this->update_control(
            'icon_list',
            [
                'type'    => SortableList::CONTROL_TYPE,
                'fields'  => $repeater->get_controls(),
                'default' => json_decode(
                    dokan_elementor()->elementor()->dynamic_tags->get_tag_data_content( null, 'dokan-store-info' ),
                    true
                ),
            ]
        );

        $this->add_control(
            'store_info',
            [
                'type'    => DynamicHidden::CONTROL_TYPE,
                'dynamic' => [
                    'active'  => true,
                    'default' => dokan_elementor()->elementor()->dynamic_tags->tag_data_to_tag_text( null, 'dokan-store-info' ),
                ],
            ],
            [
                'position' => [ 'of' => 'icon_list' ],
            ]
        );

        $this->add_position_controls();
    }

    /**
     * Set wrapper classes
     *
     * @since 2.9.11
     *
     * @return string
     */
    protected function get_html_wrapper_class() {
        return parent::get_html_wrapper_class() . ' dokan-store-info elementor-widget-' . parent::get_name();
    }

    /**
     * Render icon list widget output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function render() {
        $settings = $this->get_settings_for_display();

        $this->add_render_attribute( 'icon_list', 'class', 'elementor-icon-list-items' );
        $this->add_render_attribute( 'list_item', 'class', 'elementor-icon-list-item' );

        if ( 'inline' === $settings['view'] ) {
            $this->add_render_attribute( 'icon_list', 'class', 'elementor-inline-items' );
            $this->add_render_attribute( 'list_item', 'class', 'elementor-inline-item' );
        }
        ?>
        <?php if ( ! empty( $settings['icon_list'] ) && ! empty( $settings['store_info'] ) ) : ?>
            <?php $store_info = json_decode( $settings['store_info'], true ); ?>
            <?php if ( is_array( $store_info ) ) : ?>
                <ul <?php echo $this->get_render_attribute_string( 'icon_list' ); ?>>
                    <?php
                    foreach ( $settings['icon_list'] as $index => $item ) :
                        $repeater_setting_key = $this->get_repeater_setting_key( 'text', 'icon_list', $index );

                        $this->add_render_attribute( $repeater_setting_key, 'class', 'elementor-icon-list-text' );

                        $this->add_inline_editing_attributes( $repeater_setting_key );

                        if ( $item['show'] ) :
                            $info_item = array_filter(
                                $store_info, function ( $list_item ) use ( $item ) {
									return $list_item['key'] === $item['key'];
								}
                            );

                            if ( empty( $info_item ) ) {
                                continue;
                            }

                            $info_item = array_pop( $info_item );

                            $text = $info_item['text'];

                            if ( ! $text ) {
                                continue;
                            }
							?>
                            <li class="elementor-icon-list-item" >
                                <?php if ( ! empty( $item['icon'] ) ) : ?>
                                    <span class="elementor-icon-list-icon">
                                        <i class="<?php echo esc_attr( $item['icon'] ); ?>" aria-hidden="true"></i>
                                    </span>
                                <?php endif; ?>
                                <span <?php echo $this->get_render_attribute_string( $repeater_setting_key ); ?>><?php echo $text; ?></span>
                                <?php if ( ! empty( $item['times'] ) ) : ?>
                                    <span class="elementor-icon-list-icon elementor-times-list-icon">
                                        <i class="<?php echo esc_attr( $item['times'] ); ?>" aria-hidden="true"></i>
                                    </span>

                                    <?php
                                    $seller_id         = (int) get_query_var( 'author' );
                                    $store_info        = dokan()->vendor->get( $seller_id )->get_shop_info();
                                    $dokan_store_times = ! empty( $store_info['dokan_store_time'] ) ? $store_info['dokan_store_time'] : [];
                                    $current_time      = dokan_current_datetime();
                                    $today             = strtolower( $current_time->format( 'l' ) );

                                    // Vendor store times template shown here.
                                    dokan_get_template_part(
                                        'store-header-times', '', [
                                            'today'             => $today,
                                            'dokan_days'        => dokan_get_translated_days(),
                                            'current_time' => $current_time,
                                            'times_heading' => __( 'Weekly Store Timing', 'dokan' ),
                                            'closed_status' => __( 'CLOSED', 'dokan' ),
                                            'dokan_store_times' => $dokan_store_times,
                                        ]
                                    );
                                    ?>
                                <?php endif; ?>
                            </li>
							<?php
                        endif;
                    endforeach;
                    ?>
                </ul>
            <?php endif; ?>
        <?php endif; ?>
        <?php
    }

    /**
     * Render icon list widget output in the editor.
     *
     * Written as a Backbone JavaScript template and used to generate the live preview.
     *
     * @since 2.9.11
     *
     * @return void
     */
    protected function content_template() {
        ?>
        <#
            view.addRenderAttribute( 'icon_list', 'class', 'elementor-icon-list-items' );
            view.addRenderAttribute( 'list_item', 'class', 'elementor-icon-list-item' );

            if ( 'inline' == settings.view ) {
                view.addRenderAttribute( 'icon_list', 'class', 'elementor-inline-items' );
                view.addRenderAttribute( 'list_item', 'class', 'elementor-inline-item' );
            }
        #>
        <# if ( settings.icon_list && settings.store_info ) { #>
            <# var store_info = JSON.parse( settings.store_info ); #>
            <ul {{{ view.getRenderAttributeString( 'icon_list' ) }}}>
            <# _.each( settings.icon_list, function( item, index ) {
                    var iconTextKey = view.getRepeaterSettingKey( 'text', 'icon_list', index );

                    view.addRenderAttribute( iconTextKey, 'class', 'elementor-icon-list-text' );

                    view.addInlineEditingAttributes( iconTextKey ); #>

                    <# if ( item.show ) { #>
                        <#
                            var text = _.findWhere( store_info, { key: item.key } ).text;
                        #>
                        <li {{{ view.getRenderAttributeString( 'list_item' ) }}}>
                            <# if ( item.icon ) { #>
                            <span class="elementor-icon-list-icon">
                                <i class="{{ item.icon }}" aria-hidden="true"></i>
                            </span>
                            <# } #>
                            <span {{{ view.getRenderAttributeString( iconTextKey ) }}}>{{{ text }}}</span>
                        </li>
                    <# } #>
                <#
                } ); #>
            </ul>
        <#  } #>
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
