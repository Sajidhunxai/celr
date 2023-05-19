<?php

namespace WeDevs\DokanPro\Modules\RankMath;

use RankMath\Helper;
use RankMath\Schema\DB;
use RankMath\Schema\Admin;

defined( 'ABSPATH' ) || exit;

/**
 * Schema manger class
 *
 * @since 3.4.0
 */
class Schema extends Admin {

    /**
     * Class constructor
     *
     * @since 3.4.0
     */
    public function __construct() {
        parent::__construct();
        $this->enqueue();
    }

    /**
     * Retrieves post id
     *
     * @since 3.4.0
     *
     * @return integer
     */
    private function get_post_id() {
        return ! empty( $_GET['product_id'] ) ? absint( wp_unslash( $_GET['product_id'] ) ) : 0; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
    }

    /**
     * Enqueue Styles and Scripts required for the metabox on the post screen
     *
     * @since 3.4.0
     *
     * @return integer
     */
    public function enqueue() {
        Helper::add_json( 'schemas', $this->get_schema_data( $this->get_post_id() ) );
        Helper::add_json( 'customSchemaImage', esc_url( rank_math()->plugin_url() . 'includes/modules/schema/assets/img/custom-schema-builder.jpg' ) );

        wp_enqueue_style(
            'rank-math-schema',
            rank_math()->plugin_url() . 'includes/modules/schema/assets/css/schema.css',
            array( 'wp-components', 'rank-math-metabox' ),
            rank_math()->version
        );

        $this->enqueue_translation();

        wp_enqueue_script(
            'rank-math-schema',
            rank_math()->plugin_url() . 'includes/modules/schema/assets/js/schema-gutenberg.js',
            array( 'rank-math-editor' ),
            rank_math()->version,
            true
        );
    }

    /**
     * Enqueues translation
     *
     * @since 3.4.0
     *
     * @return integer
     */
    private function enqueue_translation() {
        if ( function_exists( 'wp_set_script_translations' ) ) {
            wp_set_script_translations( 'rank-math-schema', 'rank-math', rank_math()->plugin_dir() . 'languages/' );
        }
    }

    /**
     * Get Schema Data.
     *
     * @param int $post_id Post ID.
     *
     * @since 3.4.0
     *
     * @return array $schemas Schema Data.
     */
    private function get_schema_data( $post_id ) {
        $schemas = DB::get_schemas( $post_id );

        if ( ! empty( $schemas ) ) {
            return $schemas;
        }

        $default_type = $this->get_default_schema_type( $post_id );

        if ( ! $default_type ) {
            return [];
        }

        $schemas['new-9999'] = [
            '@type'    => $default_type,
            'metadata' => array(
                'title'     => Helper::sanitize_schema_title( $default_type ),
                'type'      => 'template',
                'shortcode' => uniqid( 's-' ),
                'isPrimary' => true,
            ),
        ];

        return $schemas;
    }

    /**
     * Get default schema type.
     *
     * @param int $post_id Post ID.
     *
     * @return mixed Schema type.
     */
    private function get_default_schema_type( $post_id ) {
        $default_type = ucfirst( Helper::get_default_schema_type( $post_id ) );

        switch ( $default_type ) {
            case 'Video':
                return 'VideoObject';

            case 'Software':
                return 'SoftwareApplication';

            case 'Jobposting':
                return 'JobPosting';

            default:
                return $default_type;
        }
    }
}
