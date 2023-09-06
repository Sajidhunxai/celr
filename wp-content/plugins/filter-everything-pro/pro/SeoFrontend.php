<?php


namespace FilterEverything\Filter\Pro;

if ( ! defined('WPINC') ) {
    wp_die();
}

use FilterEverything\Filter\Container;
use FilterEverything\Filter\Pro\Admin\SeoRules;
use FilterEverything\Filter\UrlManager;

class SeoFrontend
{
    private $vars;

    public function __construct()
    {
        $wpManager = Container::instance()->getWpManager();

        $queried_values = $wpManager->getQueryVar( 'queried_values', [] );

        foreach ( $queried_values as $key => $filter ) {
            if( in_array( $filter['entity'], [ 'post_meta_num', 'tax_numeric' ] ) ){
                unset( $queried_values[ $key ] );
            }
        }

        $this->set( 'filterRequest', $wpManager->getQueryVar( 'wpc_is_filter_request' ) );
        $this->set( 'queriedValues', $queried_values );
        $this->set( 'allowedFilterPage', $wpManager->getQueryVar( 'allowed_filter_page' ) );
        $this->set( 'queriedObject', $wpManager->getQueryVar( 'wp_queried_object' ) );
    }

    /**
     * Fires only on isFilter pages
     */
    public function registerHooks()
    {
        // Remove all filters to handle it in own way
        remove_all_filters( 'wp_robots' );
        $active_plugins = flrt_get_active_plugins();

        $seo_plugins_to_disable = array(
            'wordpress-seo/wp-seo.php',
            'wordpress-seo-premium/wp-seo-premium.php',
            'all-in-one-seo-pack/all_in_one_seo_pack.php',
            'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
            'seo-by-rank-math/rank-math.php',
            'seo-by-rank-math-pro/rank-math-pro.php',
            'squirrly-seo/squirrly.php',
            'autodescription/autodescription.php',
            'wp-seopress/seopress.php'
        );

        foreach ( $seo_plugins_to_disable as $plugin ){
            if( in_array( $plugin, $active_plugins ) ){
                add_action( 'template_redirect', 'flrt_remove_wpseo' );
                break;
            }
        }

        \add_action( 'wp_head', array( $this, 'filtersWpHead' ), 1 );
        // Filter the title for compatibility with other plugins and themes.
        \add_filter( 'wp_title', array( $this, 'wpTitle' ), 15 );

        \remove_action( 'wp_head', 'rel_canonical' );
        \remove_action( 'wp_head', 'index_rel_link' );
        \remove_action( 'wp_head', 'start_post_rel_link' );
        \remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head' );
        \remove_action( 'wp_head', 'noindex', 1 );
        \remove_action( 'wp_head', '_wp_render_title_tag', 1 );
        \remove_action( 'wp_head', 'gutenberg_render_title_tag', 1 );

        \add_filter( 'wpml_hreflangs_html', '__return_empty_string' );
    }

    public function filtersWpHead()
    {
        $this->openMetaDataBlock();

        $this->title();
        $this->description();
        $this->robots();
        $this->canonical();

        $this->closeMetaDataBlock();
        // Add related filters
        $this->h1();
        $this->seoText();
    }

    public function processPageSeo()
    {
        if( $this->isFilter() ){
            $this->configureAllQueriedValues();
            $this->setUpSeoVariables();
            $this->registerHooks();
        }
    }

    private function setUpSeoVariables()
    {
        return $this->replaceSeoVariables();
    }

    private function replaceSeoVariables()
    {
        if( $this->isNoindex() ){
            return false;
        }

        $relatedRules = $this->getRelatedSeoRules();

        if( empty( $relatedRules ) ){
            $this->set( 'noIndex', true );
            return false;
        }

        $actualRule = reset( $relatedRules );
        $seoData    = isset( $actualRule['post_excerpt'] ) ? maybe_unserialize( $actualRule['post_excerpt'] ) : false;

        if( isset( $actualRule['ID'] ) && $actualRule['ID'] ){
            $this->set( 'seoRulePostId', $actualRule['ID'] );
        }

        if( isset( $seoData['rule_h1'] ) && $seoData['rule_h1'] ){
            $this->set( 'h1', $this->replaceVariableData($seoData['rule_h1']) );
        }

        if( isset( $actualRule['post_content'] ) && ! empty( $actualRule['post_content'] ) ){
            $this->set( 'seoDescription', $this->replaceVariableData( $actualRule['post_content'] ) );
        }

        if( isset( $seoData['rule_seo_title'] ) && $seoData['rule_seo_title'] ){
            $this->set( 'seoTitle', $this->replaceVariableData($seoData['rule_seo_title']) );
        }

        if( isset( $seoData['rule_meta_desc'] ) && $seoData['rule_meta_desc'] ){
            $this->set( 'metaDescription', $this->replaceVariableData($seoData['rule_meta_desc']) );
        }

        return true;
    }

    private function replaceVariableData( $content )
    {
        global $paged;

        $em = Container::instance()->getEntityManager();
        $search     = [];
        $replace    = [];

        foreach ( $this->get('allqueriedvalues') as $key => $filter ) {
            $search[]  = $this->getHumanSeoVarName( $filter );
            $termSlug  = reset( $filter['values'] );
            $entity    = $em->getEntityByFilter( $filter );
            $term      = $entity->getTerm( $entity->getTermId( $termSlug ) );

            $replace[] = apply_filters( 'wpc_seo_var_term_name', $term->name, $entity->getName() );
        }

        // Site title
        $search[] = '{site_title}';
        $replace[]  = get_option('blogname');

        // Page num
        $search[] = '{page_number}';
        $pagedReplace = $paged ? sprintf( esc_html__( 'page %d', 'filter-everything' ), $paged ) : '';
        $replace[]  = $pagedReplace;

        $search     = apply_filters( 'wpc_seo_vars_search_list', $search );
        $replace    = apply_filters( 'wpc_seo_vars_replace_list', $replace, $search );

        $replaced = str_replace( $search, $replace, $content );

        // Remove any other {vairables} from SEO data.
        $replaced = preg_replace( '/\{[a-zA-Z0-9_\W]+?\}/m', '', $replaced );

        return trim($replaced);
    }

    private function getHumanSeoVarName( $filter )
    {
        if( isset( $filter['wp_entity'] ) && $filter['wp_entity'] ){
            $seoVarName = '{wp_entity}';
        }else{
            $seoVarName = '{'.$filter['e_name'].'}';
        }

        return $seoVarName;
    }

    private function getRelatedSeoRules()
    {
        $seoRules       = $this->initSeoRules();

        $queriedValues  = $this->get( 'allqueriedvalues' );

        if( empty( $queriedValues ) ){
            return array();
        }

        $possibleRules = [];
        $valuesArray = [];

        foreach( $queriedValues as $index => $filter ){
            $filter['values'][0] = $index .'#'. $filter['values'][0];
            $filter['values'][]  = $index .'#'. $seoRules->getAnyTitle();

            $valuesArray[$index] = $filter['values'];
        }

        $combinations = $this->cartesian( $valuesArray );

        foreach( $combinations as $combination ){

            foreach ($combination as $index_value_pair) {

                $parts = explode( '#', $index_value_pair );
                $index = $parts[0];
                $value = $parts[1];

                $queriedValues[$index]['values'][0] = $value;

            }

            $possibleRules[] = $seoRules->generateRuleKey($queriedValues);
            
        }

        return $seoRules->getRules( $possibleRules );
    }

    private function cartesian($terms)
    {
        $result = array_shift($terms);

        if (count($terms)) {
            while ($next = array_shift($terms)) {
                $result = $this->merge($result, $next);
            }
        } else {
            return $this->merge($result, array(array()));
        }

        return $result;
    }

    private function merge($a, $b)
    {
        $result = array();

        foreach ($a as $aValues) {
            foreach ($b as $bValues) {
                $merged = array_merge((array)$aValues, (array)$bValues);

                if (count($merged) === count(array_unique($merged))) {
                    sort($merged);
                    $key          = implode(',', $merged);
                    $result[$key] = $merged;
                }
            }
        }

        return array_values($result);
    }

    private function isNoindex()
    {
        global $wp_query;
        $noindex = true;

        if ( '0' == get_option( 'blog_public' ) ) {
            return $noindex;
        }

        /**
         * @feature to allow pages to be indexed when permalinks disabled.
         */
//        if( ! FLRT_PERMALINKS_ENABLED ){
//            return $noindex;
//        }

        if( is_404() ){
            return $noindex;
        }

        if( $this->get('noIndex') ){
            return $noindex;
        }

        if( $wp_query->is_search() ){
            return $noindex;
        }

        if( ! $this->get('filterRequest') ){
            $noindex = false;
            return $noindex;
        }

        $seoRules       = $this->initSeoRules();


        $queriedFilters     = $this->get('queriedValues');
        $allqueriedValues   = $this->get('allqueriedvalues');
        $queriedObject      = $this->get('queriedObject');
        $wpManager          = Container::instance()->getWpManager();
        $sets               = $wpManager->getQueryVar('wpc_page_related_set_ids');
        $mainSet            = reset( $sets );

        $postType           = isset( $mainSet['filtered_post_type'] ) ? $mainSet['filtered_post_type'] : '';
        $indexedFilters     = $seoRules->getIndexedFilters($postType);

        if( ! $postType ){
            return $noindex;
        }

        $indexingDepth = $this->getIndexDepth( $postType );

        /**
         * @feature It seems it is better to compare with Queried values instead of with AllQueried Values.
         * The first way is more obviously.
         */

        if( count( $queriedFilters ) > $indexingDepth ){
            return $noindex;
        }

        $indexedEnames  = [];

        foreach ( $indexedFilters as $filter ) {
            $indexedEnames[] = $filter['e_name'];
        }

        foreach( $allqueriedValues as $slug => $filter  ){
            if( count( $filter['values'] ) > 1 ){
                return $noindex;
            }
        }

        foreach ( $queriedFilters as $slug => $filter ) {

            if( ! in_array( $filter['e_name'], $indexedEnames ) ){
                return $noindex;
            }
        }

        $noindex = false;
        return $noindex;
    }

    public function configureAllQueriedValues()
    {
        // if we have additional tax query variable in WP_Query
        // it presents in queriedObject
        $term = false;
        $filterQueriedValues = $this->get( 'queriedValues' );

        /**
         * @todo Do not allow non path filters affects on SEO rules
         */

        $queriedObject = $this->get( 'queriedObject' );

        if( isset( $queriedObject['taxonomy'] ) && $queriedObject['taxonomy'] ){
            $em = Container::instance()->getEntityManager();
            $filter = $em->getFilterBy( 'e_name', $queriedObject['taxonomy'], array('entity', 'e_name', 'slug', 'logic') );

            if ( ! isset( $filter['entity'] ) ) {
                $filter = [];
                $term = get_term( $queriedObject['term_id'], $queriedObject['taxonomy'] );

                $filter['entity'] = 'taxonomy';
                $filter['e_name'] = $queriedObject['taxonomy'];
                $filter['logic']  = '';
                $filter['slug']   = 0;

            } else {
                $entity = $em->getEntityByFilter($filter);
                $term = $entity->getTerm( $queriedObject['term_id'] );
            }

        }

        if( isset( $queriedObject['author'] ) && $queriedObject['author'] ){
            $em = Container::instance()->getEntityManager();
            $filter = $em->getFilterBy( 'e_name', 'author', array('entity', 'e_name', 'slug', 'logic') );

            if( ! isset( $filter['entity'] ) ){

                $user = get_user_by( 'slug', $queriedObject['author'] );

                $filter['entity'] = 'author';
                $filter['e_name'] = 'author';
                $filter['logic']   = '';
                $filter['slug']   = 0;

                $term = new \stdClass();
                $term->slug = $user->data->user_nicename;

            }else{
                $entity     = $em->getEntityByFilter($filter);
                $term_id    = $entity->getTermId( $queriedObject['author'] );
                $term       = $entity->getTerm( $term_id );
            }

        }

        if( $term ){
            $filter['values'][] = $term->slug;
            $filter['wp_entity'] = true;

            // Situation, when we have the same wp_entity and filter in request
            if( isset( $filterQueriedValues[$filter['slug']] ) ){
                $filterQueriedValues[$filter['slug']]['values'][] = $term->slug;
            }else{
                $filterQueriedValues = array_merge( array( $filter['slug'] => $filter ), $filterQueriedValues );
            }

        }

        $this->set( 'allqueriedvalues', $filterQueriedValues );
    }

    private function initSeoRules()
    {
        $wpManager          = Container::instance()->getWpManager();
        $sets               = $wpManager->getQueryVar('wpc_page_related_set_ids');
        $mainSet            = reset( $sets );
        $postType           = isset( $mainSet['filtered_post_type'] ) ? $mainSet['filtered_post_type'] : '';
        $seoRules           = new SeoRules();
        $seoRules->setPostType($postType);

        return $seoRules;
    }

    private function isFilter()
    {
        if(  $this->get( 'filterRequest' ) && $this->get( 'allowedFilterPage') ){
            return true;
        }
        return false;
    }

    private function title()
    {
        if( ! $this->get('seoTitleDisplayed') ){
            $seoTitle = $this->get('seoTitle') ? $this->get('seoTitle') : wp_get_document_title();
            $seoTitle = apply_filters( 'wpc_seo_title', $seoTitle );
            echo sprintf( '<title>%s</title>', esc_html($seoTitle) )."\r\n";
        }
    }

    private function description()
    {
        $description = $this->get('metaDescription');
        $description = apply_filters( 'wpc_seo_description', $description );
        if( $description ){
            echo sprintf( '<meta name="description" content="%s" />', esc_attr($description) )."\r\n";
        }

    }

    private function robots()
    {
        $robots = $this->getRobots();

        foreach ( $robots as $directive => $value ) {
            if ( is_string( $value ) ) {
                // If a string value, include it as value for the directive.
                $robots_strings[] = "{$directive}:{$value}";
            } elseif ( $value ) {
                // Otherwise, include the directive if it is truthy.
                $robots_strings[] = $directive;
            }
        }

        if ( empty( $robots_strings ) ) {
            return;
        }
        $robots_strings = apply_filters( 'wpc_seo_robots', $robots_strings );
        echo "<meta name='robots' content='" . esc_attr( implode( ', ', $robots_strings ) ) . "' />\n";

    }

    public function getRobots()
    {
        if( $this->isNoindex() ){
            $robots['noindex']  = true;
            $robots['nofollow'] = true;
        }else{
            $robots['index']  = true;
            $robots['follow'] = true;
        }

        return $robots;
    }

    private function canonical()
    {   $urlManager = new UrlManager();

        if( ! $this->isNoindex() ){
            $canonical  = $urlManager->getFiltersUrl( $this->get('queriedValues') );
            $parts      = explode("?", $canonical);
            $canonical  = FLRT_PERMALINKS_ENABLED ? user_trailingslashit($parts[0]) : $parts[0];

            if( isset( $parts[1] ) ){
                parse_str( $parts[1], $get );

                foreach ( $this->get('allqueriedvalues') as $slug => $filterData ){

                    $slug = ( isset( $filterData['wp_entity'] ) &&  $filterData['wp_entity'] ) ? $filterData['e_name'] : $filterData['slug'];

                    if(
                        isset( $get[ $slug ] )
                        &&
                        isset( $filterData['values'][0] )
                        &&
                        ( $get[ $slug ] === $filterData['values'][0] ) ){
                        $canonical = flrt_add_query_arg( $slug, $filterData['values'][0], $canonical );
                    }
                }
            }

        }else{
            $canonical = $urlManager->getResetUrl();
        }
        $canonical = apply_filters( 'wpc_seo_canonical', $canonical );
        echo sprintf('<link rel="canonical" href="%s" />', esc_attr($canonical) )."\r\n";
    }

    private function h1()
    {
        if( is_singular() ){
            add_filter('the_title', [ $this, 'seoH1' ], 10, 2 );
        } else {
            add_filter( 'woocommerce_page_title', array( $this, 'seoH1'), -5 );
            add_filter( 'get_the_archive_title', array( $this, 'seoH1'), -5 );
            add_filter( 'avada_page_title_bar_contents', [$this, 'seoH1'], -5 );
            //@todo maybe remove it from 'get_the_archive_title' to aviod duplicates
            add_filter( 'post_type_archive_title', [$this, 'seoH1'], -5 );
            add_filter( 'elementor/utils/get_the_archive_title', [$this, 'seoH1'], -5 );
        }

    }

    private function seoText()
    {
        if( flrt_is_woocommerce() ){
            add_filter( 'woocommerce_after_shop_loop', array( $this, 'showSeoDescription' ), 5 );
        }else{
            add_filter( 'get_the_archive_description', array( $this, 'archiveDescription' ) );
        }
    }

    public function openMetaDataBlock()
    {
        /**
         * @todo Add credentials link to this section 
         */
        echo sprintf( '<!-- This page was genereated by the Filter Everything plugin v %s - %s -->', FLRT_PLUGIN_VER, FLRT_PLUGIN_URL )."\r\n";

    }

    public function closeMetaDataBlock()
    {
        if( $seoRulePostId = $this->get( 'seoRulePostId' ) ){
            if( is_user_logged_in() && current_user_can('administrator') ){
                echo sprintf( '<!-- For logged in administrators only: matched SEO rule ID is "%d" -->', $seoRulePostId )."\r\n";
            }
            echo '<style type="text/css" id="wpc-seo-rule-id" data-seoruleid="'.$seoRulePostId.'"></style>'."\r\n";
        }

        echo sprintf( '<!-- / %s -->', esc_html__( 'Filter Everything plugin', 'filter-everything' ) )."\r\n";
    }

    public function wpTitle( $wp_title )
    {
        if( $seoTitle = $this->get('seoTitle') ){
            $this->set('seoTitleDisplayed', true);
            return esc_html( $seoTitle );
        }
        return $wp_title;
    }

    public function seoH1( $h1, $post_id = 0 )
    {
        if( $filtersH1 = $this->get('h1') ){

            if( $post_id > 0 ){
                $queriedObject = $this->get( 'queriedObject' );

                if( isset( $queriedObject['post_id'] ) && $post_id === $queriedObject['post_id'] ){
                    return apply_filters( 'wpc_seo_h1', esc_html( $filtersH1 ) );
                }else{
                    return apply_filters( 'wpc_seo_h1', $h1);
                }
            }

            // For archives
            if( is_array( $h1 ) ){
                // Avada title
                $h1[0] = apply_filters( 'wpc_seo_h1', esc_html( $filtersH1 ) );
                return $h1;
            }else{
                return apply_filters( 'wpc_seo_h1', esc_html( $filtersH1 ) );
            }
        }

        if ( is_array( $h1 ) ) {
            $h1[0] = apply_filters( 'wpc_seo_h1', $h1[0]);
            return $h1;
        } else {
            return apply_filters( 'wpc_seo_h1', $h1);
        }
    }

    public function showSeoDescription()
    {
        if( $seoText = $this->get('seoDescription') ){
            $seoText = apply_filters( 'the_content', wp_kses_post($seoText) );
            $seoText = apply_filters( 'wpc_seo_text', $seoText );
            echo sprintf( '<div class="wpc-page-seo-description">%s</div>', $seoText )."\r\n";
        }
    }

    public function archiveDescription( $archiveDescription )
    {
        if( $seoText = $this->get('seoDescription') ){
            $seoText = apply_filters( 'the_content', wp_kses_post($seoText) );
        }else{
            $seoText = $archiveDescription;
        }
        $seoText = apply_filters( 'wpc_seo_text', $seoText );
        return sprintf( '<div class="wpc-page-seo-description">%s</div>', $seoText )."\r\n";
    }

    private function getIndexDepth( $postType )
    {
        $indexDeep = 2;
        $indexDeepOptions = get_option( 'wpc_indexing_deep_settings' );

        if( isset( $indexDeepOptions[$postType . '_index_deep'] ) && $indexDeepOptions[$postType . '_index_deep'] ){
            return $indexDeepOptions[$postType . '_index_deep'];
        }

        return $indexDeep;
    }

    public function get( $key )
    {
        if( isset( $this->vars[$key] ) ){
            return $this->vars[$key];
        }
        return false;
    }

    private function set( $key, $value )
    {
        if( ! isset( $this->vars[$key] ) ){
            $this->vars[$key] = $value;
        }
        return true;
    }
}