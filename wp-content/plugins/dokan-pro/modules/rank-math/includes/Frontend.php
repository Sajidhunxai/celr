<?php

namespace WeDevs\DokanPro\Modules\RankMath;

use RankMath\Helper;
use RankMath\Runner;
use RankMath\Traits\Hooker;

class Frontend implements Runner {

    use Hooker;

    /**
     * Screen object.
     *
     * @var Screen
     */
    private $screen;

    /**
     * Renders the frontend view
     *
     * @since 3.4.0
     *
     * @return void
     */
    public function process() {
        // Instantiate assets manager
        new Assets();
        // Instantiate Content AI
        new ContentAi();
        // Register hooks
        $this->hooks();
        // Instantiate Schema generator
        new Schema();
    }

    /**
     * Registers hooks.
     *
     * @since 3.4.0
     *
     * @return void
     */
    public function hooks() {
        $this->screen = new Screen();

        if ( $this->screen->is_loaded() ) {
            $this->enqueue();
        }

        $this->action( 'cmb2_save_field', 'invalidate_facebook_object_cache', 10, 4 );
    }

    /**
     * Enqueue styles and scripts for the metabox.
     *
     * @since 3.4.0
     *
     * @return void
     */
    public function enqueue() {
        $this->enqueue_commons();
        $this->screen->enqueue();
        $this->screen->localize();
        $this->enqueue_translation();
        rank_math()->variables->setup_json();

        Helper::add_json( 'knowledgegraphType', Helper::get_settings( 'titles.knowledgegraph_type' ) );

        \CMB2_Hookup::enqueue_cmb_css();

        wp_enqueue_style(
            'rank-math-metabox',
            rank_math()->plugin_url() . 'assets/admin/css/metabox.css',
            array(
                'rank-math-common',
                'rank-math-cmb2',
                'rank-math-editor',
                'wp-components',
            ),
            rank_math()->version
        );

        wp_enqueue_script(
            'rank-math-editor',
            rank_math()->plugin_url() . 'assets/admin/js/classic.js',
            array(
                'clipboard',
                'wp-hooks',
                'moment',
                'wp-date',
                'wp-data',
                'wp-api-fetch',
                'wp-components',
                'wp-element',
                'wp-i18n',
                'wp-url',
                'wp-media-utils',
                'rank-math-common',
                'rank-math-analyzer',
                'wp-block-editor',
                'rank-math-app',
            ),
            rank_math()->version,
            true
        );

        $this->do_action( 'enqueue_scripts/assessor' );
    }

    /**
     * Enqueque scripts common for all builders
     *
     * @since 3.4.0
     *
     * @return void
     */
    private function enqueue_commons() {
        wp_register_style(
            'rank-math-editor',
            rank_math()->plugin_url() . 'assets/admin/css/gutenberg.css',
            array( 'rank-math-common' ),
            rank_math()->version
        );

        wp_register_script(
            'rank-math-analyzer',
            rank_math()->plugin_url() . 'assets/admin/js/analyzer.js',
            array( 'lodash', 'wp-autop', 'wp-wordcount' ),
            rank_math()->version,
            true
        );
    }

    /**
     * Enqueues translation
     *
     * @since 3.4.0
     *
     * @return void
     */
    private function enqueue_translation() {
        if ( function_exists( 'wp_set_script_translations' ) ) {
            $this->filter( 'load_script_translation_file', 'load_script_translation_file', 10, 3 );
            wp_set_script_translations( 'rank-math-analyzer', 'rank-math', rank_math()->plugin_dir() . 'languages/' );
            wp_set_script_translations( 'rank-math-gutenberg', 'rank-math', rank_math()->plugin_dir() . 'languages/' );
        }
    }

    /**
     * Function to replace domain with seo-by-rank-math in translation file
     *
     * @since 3.4.0
     *
     * @param string|false $file   Path to the translation file to load. False if there isn't one.
     * @param string       $handle Name of the script to register a translation domain to.
     * @param string       $domain The text domain.
     *
     * @return void
     */
    public function load_script_translation_file( $file, $handle, $domain ) {
        if ( 'rank-math' !== $domain ) {
            return $file;
        }

        $data                       = explode( '/', $file );
        $data[ count( $data ) - 1 ] = preg_replace( '/rank-math/', 'seo-by-rank-math', $data[ count( $data ) - 1 ], 1 );

        return implode( '/', $data );
    }

    /**
     * Invalidate facebook object cache for the post
     *
     * @since 3.4.0
     *
     * @param string     $field_id The current field id paramater.
     * @param bool       $updated  Whether the metadata update action occurred.
     * @param string     $action   Action performed. Could be "repeatable", "updated", or "removed".
     * @param CMB2_Field $field    This field object.
     *
     * @return void
     */
    public function invalidate_facebook_object_cache( $field_id, $updated, $action, $field ) {
        if ( ! in_array( $field_id, array( 'rank_math_facebook_title', 'rank_math_facebook_image', 'rank_math_facebook_description' ), true ) || ! $updated ) {
            return;
        }

        $app_id = Helper::get_settings( 'titles.facebook_app_id' );
        $secret = Helper::get_settings( 'titles.facebook_secret' );

        if ( ! $app_id || ! $secret ) {
            return;
        }

        wp_remote_post(
            'https://graph.facebook.com/',
            array(
                'body' => array(
                    'id'           => get_permalink( $field->object_id() ),
                    'scrape'       => true,
                    'access_token' => $app_id . '|' . $secret,
                ),
            )
        );
    }
}
