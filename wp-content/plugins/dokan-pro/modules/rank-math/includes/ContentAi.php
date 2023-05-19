<?php

namespace WeDevs\DokanPro\Modules\RankMath;

defined( 'ABSPATH' ) || exit;

use RankMath\Helper;
use MyThemeShop\Helpers\WordPress;
use RankMath\ContentAI\Content_AI;

/**
 * Schema manger class
 *
 * @since 3.5.0
 */
class ContentAi extends Content_AI {

    /**
     * Class constructor
     *
     * @since 3.5.0
     */
    public function __construct() {
        parent::__construct();
        $this->hooks();
        $this->editor_scripts();
    }

    /**
     * registers necessary hooks.
     *
     * @since 3.7.10
     *
     * @return void
     */
    public function hooks() {
        add_action( 'dokan_product_edit_inside_after_rank_math_seo', [ $this, 'render_content_ai_section' ] );
    }

    /**
     * Renders content ai section.
     *
     * @since 3.7.10
     *
     * @param int $product_id
     *
     * @return void
     */
    public function render_content_ai_section( $product_id ) {
        if ( ! $this->should_render_content_ai() ) {
            return;
        }

        ob_start();
        require_once DOKAN_RANK_MATH_TEMPLATE_PATH . '/content-ai.php';
        ob_end_flush();
    }

    /**
     * Enqueue assets for post editors.
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function editor_scripts() {
        if ( ! $this->should_render_content_ai() ) {
            return;
        }

        wp_register_style(
            'rank-math-common',
            rank_math()->plugin_url() . 'assets/admin/css/common.css',
            array(),
            rank_math()->version
        );

        wp_enqueue_style(
            'rank-math-content-ai',
            rank_math()->plugin_url() . 'includes/modules/content-ai/assets/css/content-ai.css',
            [ 'rank-math-common' ],
            rank_math()->version
        );

        wp_enqueue_script(
            'rank-math-content-ai',
            rank_math()->plugin_url() . 'includes/modules/content-ai/assets/js/content-ai.js',
            [ 'rank-math-editor' ],
            rank_math()->version,
            true
        );
    }

    /**
     * Checks whether content ai should be rendered.
     *
     * @since 3.7.10
     *
     * @return boolean
     */
    public function should_render_content_ai() {
        return in_array( WordPress::get_post_type(), (array) Helper::get_settings( 'general.content_ai_post_types' ), true );
    }
}
