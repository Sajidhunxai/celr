<?php


namespace FilterEverything\Filter\Pro\Settings\Tabs;

if ( ! defined('WPINC') ) {
    wp_die();
}

use FilterEverything\Filter\BaseSettings;
use FilterEverything\Filter\Container;
use FilterEverything\Filter\EntityManager;

class SeoRulesTab extends BaseSettings
{
    private $em;

    private $fse;

    private $fs;

    protected $page = 'wpc-filter-seo-rules';

    protected $group = 'wpc_seo_rules';

    public $optionName = 'wpc_seo_rules_settings';

    public function init()
    {
        add_action( 'admin_init', array( $this, 'initSettings') );
        add_action( 'wpc_before_sections_settings_fields', array( $this, 'markCheckboxesMessage' ) );

        $this->em   = Container::instance()->getEntityManager();
        $this->fse  = Container::instance()->getFilterSetService();
        $this->fs   = Container::instance()->getFilterService();
    }

    public function markCheckboxesMessage( $page )
    {
        if( $page === $this->page ){
            echo '<p>'.wp_kses(
                                sprintf(
                                    __( 'Check filters, pages of which should be available for indexing by search engines.<br />Besides this you will also need to <a href="%s" target="_blank">create %s</a> to make filter pages available for indexing.', 'filter-everything' ),
                                    admin_url('post-new.php?post_type='.FLRT_SEO_RULES_POST_TYPE),
                                    __('SEO Rules', 'filter-everything')
                                ),
                                array('a'=> array('href'=> true, 'target'=> true), 'br'=> array() )
                ).'</p>'."\r\n";
        }
    }

    private function taxFields( $postType, $taxonomies )
    {
        $fields = [];
        $allowedTaxonomies = [];
        $postTypeTaxFilters     = $this->em->getFiltersRelatedWithPostType( $postType, 'taxonomy' );

        // Collect all filer taxonomies
        foreach ( $postTypeTaxFilters as $postTypeTaxFilter ) {
            $allowedTaxonomies[] = $postTypeTaxFilter['e_name'];
        }

        foreach ( $taxonomies as $index => $taxonomy ){

            if( ! in_array( $taxonomy->name, $allowedTaxonomies ) ){
                continue;
            }

            if( in_array( $postType, $taxonomy->object_type ) ){
                $entityKey = $postType.':taxonomy#'.$taxonomy->name;
                $fields[ $entityKey ] = array(
                    'type'  => 'checkbox',
                    'id' => $entityKey,
                    'label' => ucwords( flrt_ucfirst( mb_strtolower( $taxonomy->label ) ) )
                );
            }
        }

        return $fields;
    }

    private function fields( $postType, $filters )
    {
        $fields = [];

        if ( empty( $filters ) ) {
            return $fields;
        }

        foreach ( $filters as $filter ) {
            if( in_array( $filter['entity'], [ 'post_meta_num', 'tax_numeric' ] ) ){
                continue;
            }

            $entityKey = $postType.':'.$filter['entity'].'#'.$filter['e_name'];
            $fields[ $entityKey ] = array(
                'type'  => 'checkbox',
                'id' => $entityKey,
                'label' => $filter['label']
            );
        }

        return $fields;
    }

    public function initSettings()
    {
        $settings = [];
        $postTypes = $this->fse->getPostTypes();

        /**
         * @todo remove indexed entity if all its filters were deleted !!! IMPORTANT
         */

        $taxonomies = EntityManager::getTaxonomies();

        foreach( $postTypes as $postType => $postLabel ){

            $postTypeMetaFilters    = $this->em->getFiltersRelatedWithPostType( $postType, 'post_meta' );
            $postTypeAuthorFilters  = $this->em->getFiltersRelatedWithPostType( $postType, 'author_author' );

            $taxFields      = $this->taxFields( $postType, $taxonomies );
            $postMetaFields = $this->fields( $postType, $postTypeMetaFilters );
            $authorFields   = $this->fields( $postType, $postTypeAuthorFilters );

            $mergedFields = array_merge( $taxFields, $authorFields, $postMetaFields );

            if( ! empty( $mergedFields ) ){
                $settings['wpc_seo_rules_' . $postType] = array(
                    'label'  => wp_kses(
                                        sprintf(
                                            __('%s ( <span class="wpc-settings-post-type-label">%s</span> )', 'filter-everything'),
                                            $postLabel,
                                            $postType
                                        ),
                                        array(
                                            'span' => array( 'class' => true )
                                        )
                    ),
                    'fields' => $mergedFields
                );
            }

        }

        register_setting($this->group, $this->optionName);

        $this->registerSettings($settings, $this->page, $this->optionName);
    }

    public function getLabel()
    {
        return esc_html__('Indexed Filters', 'filter-everything');
    }

    public function getName()
    {
        return 'seorules';
    }

    public function valid()
    {
        return true;
    }
}