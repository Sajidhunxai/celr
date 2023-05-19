<?php

namespace WeDevs\DokanPro\Modules\Elementor;

use WeDevs\DokanPro\Modules\Elementor\Abstracts\ModuleBase;
use WeDevs\DokanPro\Modules\Elementor\Conditions\Store as StoreCondition;
use WeDevs\DokanPro\Modules\Elementor\Documents\Store as StoreDocument;
use Elementor\Controls_Manager;

class Bootstrap extends ModuleBase {

    /**
     * Widget group
     *
     * @since 2.9.11
     */
    const DOKAN_GROUP = 'dokan';

    /**
     * Run after first instance
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function boot() {
        parent::boot();

        add_action( 'elementor/documents/register', [ $this, 'register_documents' ] );
        add_action( 'elementor/dynamic_tags/register', [ $this, 'register_tags' ] );
        add_action( 'elementor/controls/controls_registered', [ $this, 'register_controls' ] );
        add_action( 'elementor/editor/footer', [ $this, 'add_editor_templates' ], 9 );
        add_action( 'elementor/theme/register_conditions', [ $this, 'register_conditions' ] );
        add_filter( 'dokan_locate_template', [ $this, 'locate_template_for_store_page' ], 10, 3 );
        add_action( 'elementor/element/before_section_end', [ $this, 'add_column_wrapper_padding_control' ], 10, 3 );
        add_action( 'dokan_elementor_store_tab_content', [ $this, 'store_tab_content' ] );
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_editor_scripts' ] );
    }

    /**
     * Name of the elementor module
     *
     * @since 2.9.11
     *
     * @return string
     */
    public function get_name() {
        return 'dokan';
    }

    /**
     * Module widgets
     *
     * @since 2.9.11
     *
     * @return array
     */
    public function get_widgets() {
        return [
            'StoreBanner',
            'StoreName',
            'StoreProfilePicture',
            'StoreInfo',
            'StoreSocialProfile',
            'StoreTabItems',
            'StoreTabContents',
            'StoreShareButton',
            'StoreSupportButton',
            'StoreLiveChatButton',
            'StoreFollowButton',
            'StoreVacationMessage',
            'StoreCoupons',
            'StoreProductFilter',
            'StoreFeaturedProducts',
            'StoreLatestProducts',
            'StoreBestSellingProducts',
            'StoreTopRatedProducts',
            'ProductRMA',
            'ProductSPMV',
            'ProductSPMVList',
            'ProductReportAbuse',
        ];
    }

    /**
     * Register module documents
     *
     * @since 2.9.11
     *
     * @param Elementor\Core\Documents_Manager $documents_manager
     *
     * @return void
     */
    public function register_documents( $documents_manager ) {
        $this->docs_types = [
            'store' => StoreDocument::get_class_full_name(),
        ];

        foreach ( $this->docs_types as $type => $class_name ) {
            $documents_manager->register_document_type( $type, $class_name );
        }
    }

    /**
     * Register module tags
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function register_tags() {
        $tags = [
            'StoreBanner',
            'StoreName',
            'StoreProfilePicture',
            'StoreInfo',
            'StoreSocialProfile',
            'StoreTabItems',
            'StoreSupportButton',
            'StoreLiveChatButton',
            'StoreFollowButton',
            'StoreDummyProducts',
            'StoreVacationMessage',
            'StoreCoupons',
            'StoreProductFilter',
            'ProductRMA',
            'ProductSPMV',
            'ProductSPMVList',
            'ProductReportAbuse',
        ];

        $module = dokan_elementor()->elementor()->dynamic_tags;

        $module->register_group(
            self::DOKAN_GROUP, [
				'title' => __( 'Dokan', 'dokan' ),
			]
        );

        foreach ( $tags as $tag ) {
            $class_name = __NAMESPACE__ . '\Tags\\' . $tag;
            $instance = new $class_name();
            $module->register( $instance );
        }
    }

    /**
     * Register controls
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function register_controls() {
        $controls = [
            'SortableList',
            'DynamicHidden',
        ];

        $controls_manager = dokan_elementor()->elementor()->controls_manager;

        foreach ( $controls as $control ) {
            $control_class = "\\WeDevs\\DokanPro\\Modules\\Elementor\\Controls\\{$control}";
            $controls_manager->register( new $control_class() );
        }
    }

    /**
     * Add editor templates
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function add_editor_templates() {
        $template_names = [
            'sortable-list-row',
        ];

        foreach ( $template_names as $template_name ) {
            dokan_elementor()->elementor()->common->add_template( DOKAN_ELEMENTOR_VIEWS . "/editor-templates/$template_name.php" );
        }
    }

    /**
     * Register condition for the module
     *
     * @since 2.9.11
     *
     * @param \ElementorPro\Modules\ThemeBuilder\Classes\Conditions_Manager $conditions_manager
     *
     * @return void
     */
    public function register_conditions( $conditions_manager ) {
        $condition = new StoreCondition();
        $conditions_manager->get_condition( 'general' )->register_sub_condition( $condition );
    }

    /**
     * Filter to show the elementor built store template
     *
     * @since 2.9.11
     *
     * @param string $template
     * @param string $template_name
     * @param string $template_path
     *
     * @return string
     */
    public static function locate_template_for_store_page( $template, $template_name, $template_path ) {
        if ( dokan_is_store_page() ) {
            $documents = \ElementorPro\Modules\ThemeBuilder\Module::instance()->get_conditions_manager()->get_documents_for_location( 'single' );

            if ( empty( $documents ) ) {
                return $template;
            }

            $page_templates_module = dokan_elementor()->elementor()->modules_manager->get_modules( 'page-templates' );

            $page_templates_module->set_print_callback(
                function() {
                    \ElementorPro\Modules\ThemeBuilder\Module::instance()->get_locations_manager()->do_location( 'single' );
                }
            );

            $template_path = $page_templates_module->get_template_path( $page_templates_module::TEMPLATE_HEADER_FOOTER );

            return $template_path;
        }

        return $template;
    }

    /**
     * Add column wrapper padding control for sections
     *
     * @since 1.0.0
     *
     * @return void
     */
    public static function add_column_wrapper_padding_control( $control_stack, $section_id, $args ) {
        if ( 'section' === $control_stack->get_name() && 'section_advanced' === $section_id ) {
            $control_stack->add_responsive_control(
                'column_wrapper_padding',
                [
                    'label'      => __( 'Column Wrapper Padding', 'dokan' ),
                    'type'       => Controls_Manager::DIMENSIONS,
                    'size_units' => [ 'px', 'em', '%' ],
                    'selectors'  => [
                        '{{WRAPPER}} .elementor-column-wrap' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                    ],
                ],
                [
                    'position' => [ 'of' => 'padding' ],
                ]
            );
        }
    }

    /**
     * Store tab contents
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function store_tab_content() {
        $tab = 'products';

        if ( get_query_var( 'toc' ) ) {
            $tab = 'toc';
        }

        if ( get_query_var( 'store_review' ) ) {
            $tab = 'reviews';
        }

        if ( get_query_var( 'biography' ) ) {
            $tab = 'vendor-biography';
        }

        $template = DOKAN_ELEMENTOR_VIEWS . '/store-tab-contents/' . $tab . '.php';
        $template = apply_filters( 'dokan_elementor_store_tab_content_template', $template );

        include_once $template;
    }

    /**
     * Enqueue scripts in editing or preview mode
     *
     * @since 2.9.11
     *
     * @return void
     */
    public function enqueue_editor_scripts() {
        if ( dokan_elementor()->is_edit_or_preview_mode() ) {
            dokan()->scripts->load_gmap_script();
        }
    }
}
