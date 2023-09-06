<?php


namespace FilterEverything\Filter\Pro\Settings\Tabs;

if ( ! defined('WPINC') ) {
    wp_die();
}

use FilterEverything\Filter\BaseSettings;
use FilterEverything\Filter\Container;

class IndexingDepth extends BaseSettings
{
    private $em;

    private $fse;

    protected $page = 'wpc-filter-indexing-deep';

    protected $group = 'wpc_indexing_deep';

    public $optionName = 'wpc_indexing_deep_settings';

    public function init()
    {
        add_action( 'admin_init', array( $this, 'initSettings') );
        add_action( 'wpc_before_sections_settings_fields', array( $this, 'indexingDepthExplanationMessage' ) );

        $this->em   = Container::instance()->getEntityManager();
        $this->fse  = Container::instance()->getFilterSetService();
    }

    public function initSettings()
    {
        $settings = [];

        $postTypes = $this->fse->getPostTypes();

        foreach( $postTypes as $postType => $postLabel ){

            if( ! $this->em->hasPostTypeFilters( $postType ) ){
                continue;
            }

            $settings['wpc_indexing_deep_' . $postType] = array(
                'label'  => wp_kses(
                    sprintf(
                        __('%s ( <span class="wpc-settings-post-type-label">%s</span> )', 'filter-everything' ),
                        $postLabel,
                        $postType),
                    array('span' => array( 'class' => true ) )
                ),
                'fields' => array(
                    $postType.'_index_deep' => array(
                        'type'  => 'text',
                        'id' => $postType.'_index_deep'
                    )
                )
            );

        }

        if( empty( $settings ) ){
            add_action( 'wpc_before_sections_settings_fields', array( $this, 'noPostTypesFiltersMessage' ) );
        }

        register_setting($this->group, $this->optionName);

        /**
         * @see https://developer.wordpress.org/reference/functions/add_settings_field/
         */

        $this->registerSettings($settings, $this->page, $this->optionName);
    }

    public function indexingDepthExplanationMessage( $page )
    {
        if( $page === $this->page ) {
            echo '<p class="wpc-setting-description">';
            echo wp_kses(
                        __('By default all filtering results pages are closed from indexing.<br />These settings determines maximum number of filters (only filters, not archive page)<br /> will be indexed by Search Engines.', 'filter-everything'),
                        array( 'br' => array() )
            );
            echo flrt_tooltip( array(
                        'tooltip' => wp_kses(
                                __('For example for Post Type Products Indexing depth is 2. It means the page with URL path:<br />/color-blue/size-large/<br />will be indexed.<br />But the page with URL path:<br />/color-blue/size-large/shape-round/<br />will NOT be indexed, because it contains more than 2 filters.', 'filter-everything'),
                                array('br' => array() )
                        )
                    )
                );
            echo '</p>';
        }
    }

    public function noPostTypesFiltersMessage($page)
    {
        if( $page === $this->page ) {
            echo '<p>' . esc_html__('There are no Post types to filter. Create a Filter Set first.', 'filter-everything') . '</p>';
        }
    }

    public function getLabel()
    {
        return esc_html__('Indexing Depth', 'filter-everything');
    }

    public function getName()
    {
        return 'indexingepth';
    }

    public function valid()
    {
        return true;
    }
}