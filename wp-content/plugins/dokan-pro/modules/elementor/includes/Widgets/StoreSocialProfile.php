<?php

namespace WeDevs\DokanPro\Modules\Elementor\Widgets;

use WeDevs\DokanPro\Modules\Elementor\Controls\DynamicHidden;
use WeDevs\DokanPro\Modules\Elementor\Traits\PositionControls;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Repeater;
use Elementor\Widget_Social_Icons;

class StoreSocialProfile extends Widget_Social_Icons {

    use PositionControls;

    /**
     * Widget name
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan-store-social-profile';
    }

    /**
     * Widget title
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_title() {
        return __( 'Store Social Profile', 'dokan' );
    }

    /**
     * Widget icon class
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_icon() {
        return 'eicon-social-icons';
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
        return [ 'dokan', 'store', 'vendor', 'social', 'profile', 'icons' ];
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

        $repeater = new Repeater();

        $repeater->add_control(
            'social',
            [
                'label'       => __( 'Icon', 'dokan' ),
                'type'        => Controls_Manager::ICON,
                'label_block' => true,
                'default'     => 'fab fa-wordpress',
                'include'     => [
                    'fab fa-facebook',
                    'fab fa-twitter',
                    'fab fa-pinterest',
                    'fab fa-linkedin',
                    'fab fa-youtube',
                    'fab fa-instagram',
                    'fab fa-flickr',
                    'fab fa-wordpress',
                ],
            ]
        );

        $repeater->add_control(
            'link',
            [
                'label'       => __( 'Link', 'dokan' ),
                'type'        => Controls_Manager::URL,
                'dynamic'     => [
                    'default' => '',
                    'active'  => true,
                ],
                'label_block' => true,
                'default'     => [
                    'is_external' => 'true',
                ],
                'placeholder' => __( 'https://your-link.com', 'dokan' ),
            ]
        );

        $this->update_control(
            'social_icon_list',
            [
                'fields'  => $repeater->get_controls(),
                'item_actions' => [
                    'add'       => false,
                    'duplicate' => false,
                ],
                'default' => [
                    [
                        'social_icon' => [
                            'value'   => 'fab fa-facebook',
                            'library' => 'fa-brands',
                        ],
                        'social' => 'fab fa-facebook',
                    ],
                    [
                        'social_icon' => [
                            'value'   => 'fab fa-twitter',
                            'library' => 'fa-brands',
                        ],
                        'social' => 'fab fa-twitter',
                    ],
                    [
                        'social_icon' => [
                            'value'   => 'fab fa-pinterest',
                            'library' => 'fa-brands',
                        ],
                        'social' => 'fab fa-pinterest',
                    ],
                    [
                        'social_icon' => [
                            'value'   => 'fab fa-linkedin',
                            'library' => 'fa-brands',
                        ],
                        'social' => 'fab fa-linkedin',
                    ],
                    [
                        'social_icon' => [
                            'value'   => 'fab fa-youtube',
                            'library' => 'fa-brands',
                        ],
                        'social' => 'fab fa-youtube',
                    ],
                    [
                        'social_icon' => [
                            'value'   => 'fab fa-instagram',
                            'library' => 'fa-brands',
                        ],
                        'social' => 'fab fa-instagram',
                    ],
                    [
                        'social_icon' => [
                            'value'   => 'fab fa-flickr',
                            'library' => 'fa-brands',
                        ],
                        'social' => 'fab fa-flickr',
                    ],
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name'     => 'box_shadow',
                'selector' => '{{WRAPPER}} .elementor-social-icon',
            ],
            [
                'position' => [ 'of' => 'icon_spacing' ],
            ]
        );

        $this->add_control(
            'store_social_links',
            [
                'type'    => DynamicHidden::CONTROL_TYPE,
                'dynamic' => [
                    'default' => dokan_elementor()->elementor()->dynamic_tags->tag_data_to_tag_text( null, 'dokan-store-social-profile-tag' ),
                    'active'  => true,
                ],
            ],
            [
                'position' => [ 'of' => 'social_icon_list' ],
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
        return parent::get_html_wrapper_class() . ' dokan-store-social-profile elementor-widget-' . parent::get_name();
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

        $store_social_links = json_decode( $settings['store_social_links'], true );

        if ( dokan_is_store_page() && empty( $store_social_links ) ) {
            echo '<div></div>';
            return;
        }

        $class_animation = '';

        if ( ! empty( $settings['hover_animation'] ) ) {
            $class_animation = ' elementor-animation-' . $settings['hover_animation'];
        }
        ?>
        <div class="elementor-social-icons-wrapper elementor-grid">
            <?php
            foreach ( $settings['social_icon_list'] as $index => $item ) {
                if ( dokan_is_store_page() && empty( $store_social_links[ $item['social'] ] ) ) {
                    continue;
                }

                $social = str_replace( 'fab fa-', '', $item['social'] );

                $link_key = 'link_' . $index;

                $this->add_render_attribute( $link_key, 'href', $store_social_links[ $item['social'] ] );

                if ( $item['link']['is_external'] ) {
                    $this->add_render_attribute( $link_key, 'target', '_blank' );
                }

                if ( $item['link']['nofollow'] ) {
                    $this->add_render_attribute( $link_key, 'rel', 'nofollow' );
                }
                ?>
                <a class="elementor-icon elementor-social-icon elementor-social-icon-<?php echo $social . $class_animation; ?>" <?php echo $this->get_render_attribute_string( $link_key ); ?>>
                    <span class="elementor-screen-only"><?php echo ucwords( $social ); ?></span>
                    <i class="<?php echo $item['social']; ?>"></i>
                </a>
            <?php } ?>
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
        <# let iconsHTML = {}; #>
		<div class="elementor-social-icons-wrapper elementor-grid">
			<# _.each( settings.social_icon_list, function( item, index ) {
				let link      = item.link ? item.link.url : '',
					migrated  = elementor.helpers.isIconMigrated( item, 'social_icon' );
					social    = elementor.helpers.getSocialNetworkNameFromIcon( item.social_icon, item.social, false, migrated ),
                    icon_name = social.replace( 'fab fa-', '' );
				#>
				<span class="elementor-grid-item">
					<a class="elementor-icon elementor-social-icon elementor-social-icon-{{ icon_name }} elementor-animation-{{ settings.hover_animation }} elementor-repeater-item-{{item._id}}" href="{{ link }}">
						<span class="elementor-screen-only">{{{ icon_name }}}</span>
						<#
							iconsHTML[ index ] = elementor.helpers.renderIcon( view, item.social_icon, {}, 'i', 'object' );
							if ( ( ! item.social || migrated ) && iconsHTML[ index ] && iconsHTML[ index ].rendered ) { #>
								{{{ iconsHTML[ index ].value }}}
							<# } else { #>
								<i class="{{ item.social }}"></i>
							<# }
						#>
					</a>
				</span>
			<# } ); #>
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
