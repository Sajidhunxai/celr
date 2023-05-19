<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use WeDevs\DokanPro\Modules\Elementor\Controls\DynamicHidden;
use WeDevs\DokanPro\Modules\Elementor\Widgets\StoreInfo;

class StoreTabItems extends StoreInfo {

    /**
     * Widget name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-tab-items';
    }

    /**
     * Widget title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Tab Items', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-editor-list-ul';
    }

    /**
     * Widget keywords
     *
     * @since 2.9.11
     *
     * @return array
     */
    public function get_keywords() {
        return [ 'dokan', 'store', 'vendor', 'tab', 'menu', 'items' ];
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

        $this->remove_control( 'store_info' );

        $this->update_control(
            'icon_list',
            [
                'default' => json_decode(
                    dokan_elementor()->elementor()->dynamic_tags->get_tag_data_content( null, 'dokan-store-tab-items' ),
                    true
                ),
            ]
        );

        $this->add_control(
            'tab_items',
            [
                'type'    => DynamicHidden::CONTROL_TYPE,
                'dynamic' => [
                    'active'  => true,
                    'default' => dokan_elementor()->elementor()->dynamic_tags->tag_data_to_tag_text( null, 'dokan-store-tab-items' ),
                ],
            ],
            [
                'position' => [ 'of' => 'icon_list' ],
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
        return parent::get_html_wrapper_class() . ' dokan-store-tab-items elementor-widget-' . parent::get_name();
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
        <?php if ( ! empty( $settings['icon_list'] ) && ! empty( $settings['tab_items'] ) ) : ?>
            <?php $tab_items = json_decode( $settings['tab_items'], true ); ?>
            <?php if ( is_array( $tab_items ) ) : ?>
                <ul <?php echo $this->get_render_attribute_string( 'icon_list' ); ?>>
                    <?php
                    foreach ( $settings['icon_list'] as $index => $item ) :
                        $repeater_setting_key = $this->get_repeater_setting_key( 'text', 'icon_list', $index );

                        $this->add_render_attribute( $repeater_setting_key, 'class', 'elementor-icon-list-text' );

                        $this->add_inline_editing_attributes( $repeater_setting_key );

                        if ( $item['show'] ) :
                            $tab_item = array_filter(
                                $tab_items, function ( $list_item ) use ( $item ) {
                                    return $list_item['key'] === $item['key'];
                                }
                            );

                            if ( empty( $tab_item ) ) {
                                continue;
                            }

                            $tab_item = array_pop( $tab_item );

                            $text = $tab_item['text'];
                            $url  = $tab_item['url'];

                            if ( ! $text || ! $url ) {
                                continue;
                            }

                            $link_key = 'link_' . $index;

                            $this->add_render_attribute( $link_key, 'href', $url );
							?>
                            <li class="elementor-icon-list-item" >
                                <a <?php echo $this->get_render_attribute_string( $link_key ); ?>>
                                    <?php
                                    if ( ! empty( $item['icon'] ) ) :
                                        ?>
                                        <span class="elementor-icon-list-icon">
                                            <i class="<?php echo esc_attr( $item['icon'] ); ?>" aria-hidden="true"></i>
                                        </span>
                                    <?php endif; ?>
                                    <span <?php echo $this->get_render_attribute_string( $repeater_setting_key ); ?>><?php echo $text; ?></span>
                                </a>
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
        <# if ( settings.icon_list && settings.tab_items ) { #>
            <# var tab_items = JSON.parse( settings.tab_items ); #>
            <ul {{{ view.getRenderAttributeString( 'icon_list' ) }}}>
            <# _.each( settings.icon_list, function( item, index ) {
                    var iconTextKey = view.getRepeaterSettingKey( 'text', 'icon_list', index );

                    view.addRenderAttribute( iconTextKey, 'class', 'elementor-icon-list-text' );

                    view.addInlineEditingAttributes( iconTextKey ); #>

                    <# if ( item.show ) { #>
                        <#
                            var tab_item = _.findWhere( tab_items, { key: item.key } );
                        #>
                        <li {{{ view.getRenderAttributeString( 'list_item' ) }}}>
                            <a href="{{ tab_item.url || '#' }}">
                                <# if ( item.icon ) { #>
                                <span class="elementor-icon-list-icon">
                                    <i class="{{ item.icon }}" aria-hidden="true"></i>
                                </span>
                                <# } #>
                                <span {{{ view.getRenderAttributeString( iconTextKey ) }}}>{{{ tab_item.text }}}</span>
                            </a>
                        </li>

                    <# } #>
                <#
                } ); #>
            </ul>
        <#  } #>
        <?php
    }
}
