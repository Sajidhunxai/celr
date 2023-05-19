<?php

namespace WeDevs\DokanPro;

class BlockEditorBlockTypes {

    /**
     * Class constructor
     *
     * @since 2.9.16
     *
     * @return void
     */
    public function __construct() {
        global $wp_version;
        if ( version_compare( $wp_version, '5.8.0', '<' ) ) {
            add_filter( 'block_categories', [ $this, 'add_block_category' ], 10, 2 );
        } else {
            add_filter( 'block_categories_all', [ $this, 'add_block_category' ], 10, 2 );
        }
        add_action( 'admin_enqueue_scripts', [ $this, 'register_scripts' ], 9 );
        add_action( 'init', [ $this, 'register_block_types' ] );
    }

    /**
     * Add Dokan Block category
     *
     * @since 2.9.16
     *
     * @param array $block_categories
     * @param mixed $block_editor_context
     *
     * @return array
     */
    public function add_block_category( $block_categories, $block_editor_context ) {
        // Check the context of this filter, return default if not in the post/page block editor.
        // Alternatively, use this check to add custom categories to only the customizer or widget screens.
        if ( ! ( $block_editor_context instanceof \WP_Block_Editor_Context ) ) {
            return $block_categories;
        }

        return array_merge( $block_categories, [
            [
                'slug'  => 'dokan',
                'title' => __( 'Dokan', 'dokan' ),
                'icon'  => 'wordpress',
            ]
        ] );
    }

    /**
     * Register block scripts
     *
     * @since 2.9.16
     *
     * @return void
     */
    public function register_scripts() {
        $screen = get_current_screen();

        if ( 'page' !== $screen->post_type ) {
            return;
        }

        $version = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? time() : DOKAN_PRO_PLUGIN_VERSION;

        // Use minified libraries if SCRIPT_DEBUG is turned off
        $suffix = ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

        wp_register_script(
            'dokan-blocks-editor-script',
            DOKAN_PRO_PLUGIN_ASSEST . '/js/dokan-blocks-editor-script' . $suffix . '.js',
            [ 'wp-blocks', 'wp-i18n', 'wp-element' ],
            $version,
            true
        );
    }

    /**
     * Register block types
     *
     * @since 2.9.16
     *
     * @return void
     */
    public function register_block_types() {
        register_block_type( 'dokan/shortcode', [
            'editor_script'   => 'dokan-blocks-editor-script',
            'render_callback' => [ $this, 'render_shortcode' ],
        ] );
    }

    /**
     * Render shortcode block content
     *
     * @since 2.9.16
     *
     * @param array  $attributes
     * @param string $content
     *
     * @return void
     */
    public function render_shortcode( $attributes, $content ) {
        if ( ! empty( $content ) ) {
            $content = trim( $content );
            return wpautop( $content );
        }
    }
}
