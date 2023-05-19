<?php

namespace WeDevs\DokanPro\Modules\Elementor;

final class Module {

    /**
     * Module version
     *
     * @since 2.9.11
     *
     * @var string
     */
    public $version = '2.9.11';

    /**
     * Module Dependencies
     *
     * @since 3.7.4
     *
     * @var bool $missing_dependencies
     */
    protected $missing_dependencies = true;

    /**
     * Exec after first instance has been created
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function __construct() {
        $this->define_constants();
        add_action( 'plugins_loaded', [ $this, 'init' ], 99 );
        add_action( 'init', [ $this, 'register_scripts' ] );
    }

    /**
     * Register scripts
     *
     * @since 3.7.4
     */
    public function register_scripts() {
        list( $suffix, $version ) = dokan_get_script_suffix_and_version();

        wp_register_style(
            'dokan-elementor-control-sortable-list',
            DOKAN_ELEMENTOR_ASSETS . '/css/dokan-elementor-control-sortable-list.css',
            [],
            $version
        );

        wp_register_script(
            'dokan-elementor-control-sortable-list',
            DOKAN_ELEMENTOR_ASSETS . '/js/dokan-elementor-control-sortable-list.js',
            [ 'elementor-editor' ],
            $version,
            true
        );
    }

    /**
     * Load module
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function init() {
        $dependency = new DependencyNotice();

        $this->missing_dependencies = $dependency->is_missing_dependency();
        // Check if dependencies are not missing.
        if ( ! $this->missing_dependencies ) {
            $this->instances();
        }
    }

    /**
     * This method will check if Elementor dependencies is missing
     *
     * @since 3.7.4
     *
     * @return bool
     */
    public function missing_dependencies() {
        return $this->missing_dependencies;
    }

    /**
     * Module constants
     *
     * @since 2.9.11
     *
     * @return void
     */
    private function define_constants() {
        define( 'DOKAN_ELEMENTOR_VERSION', $this->version );
        define( 'DOKAN_ELEMENTOR_FILE', __FILE__ );
        define( 'DOKAN_ELEMENTOR_PATH', dirname( DOKAN_ELEMENTOR_FILE ) );
        define( 'DOKAN_ELEMENTOR_INCLUDES', DOKAN_ELEMENTOR_PATH . '/includes' );
        define( 'DOKAN_ELEMENTOR_URL', plugins_url( '', DOKAN_ELEMENTOR_FILE ) );
        define( 'DOKAN_ELEMENTOR_ASSETS', DOKAN_ELEMENTOR_URL . '/assets' );
        define( 'DOKAN_ELEMENTOR_VIEWS', DOKAN_ELEMENTOR_PATH . '/views' );
    }

    /**
     * Create module related class instances
     *
     * @since 2.9.11
     *
     * @return void
     */
    private function instances() {
        \WeDevs\DokanPro\Modules\Elementor\Templates::instance();
        \WeDevs\DokanPro\Modules\Elementor\StoreWPWidgets::instance();
        \WeDevs\DokanPro\Modules\Elementor\Bootstrap::instance();
    }

    /**
     * Elementor\Plugin instance
     *
     * @since 2.9.11
     *
     * @return \Elementor\Plugin
     */
    public function elementor() {
        return \Elementor\Plugin::instance();
    }

    /**
     * Is editing or preview mode running
     *
     * @since 2.9.11
     *
     * @return bool
     */
    public function is_edit_or_preview_mode() {
        $is_edit_mode    = $this->elementor() ? $this->elementor()->editor->is_edit_mode() : null;
        $is_preview_mode = $this->elementor() ? $this->elementor()->preview->is_preview_mode() : null;

        if ( empty( $is_edit_mode ) && empty( $is_preview_mode ) ) {
            // @codingStandardsIgnoreStart
            if ( ! empty( $_REQUEST['action'] ) && ! empty( $_REQUEST['editor_post_id'] ) ) {
                $is_edit_mode = true;
            } elseif ( ! empty( $_REQUEST['preview'] ) && ! empty( $_REQUEST['theme_template_id'] ) ) {
                $is_preview_mode = true;
            }
            // @codingStandardsIgnoreEnd
        }

        if ( $is_edit_mode || $is_preview_mode ) {
            return true;
        }

        return false;
    }

    /**
     * Default dynamic store data for widgets
     *
     * @since 2.9.11
     *
     * @param string $prop
     *
     * @return mixed
     */
    public function get_store_data( $prop = null ) {
        $store_data = \WeDevs\DokanPro\Modules\Elementor\StoreData::instance();

        return $store_data->get_data( $prop );
    }

    /**
     * Social network name mapping to elementor icon names
     *
     * @since 2.9.11
     *
     * @return array
     */
    public function get_social_networks_map() {
        $map = [
            'fb'        => 'fab fa-facebook',
            'twitter'   => 'fab fa-twitter',
            'pinterest' => 'fab fa-pinterest',
            'linkedin'  => 'fab fa-linkedin',
            'youtube'   => 'fab fa-youtube',
            'instagram' => 'fab fa-instagram',
            'flickr'    => 'fab fa-flickr',
        ];

        return apply_filters( 'dokan_elementor_social_network_map', $map );
    }
}
