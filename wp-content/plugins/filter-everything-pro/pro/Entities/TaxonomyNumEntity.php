<?php


namespace FilterEverything\Filter\Pro\Entities;

if ( ! defined('WPINC') ) {
    wp_die();
}

use \FilterEverything\Filter\Container;
use \FilterEverything\Filter\PostMetaNumEntity;

class TaxonomyNumEntity extends PostMetaNumEntity
{
    public $items           = [];

    public $entityName      = '';

    public $excludedTerms   = [];

    public $isInclude       = false;

    public $new_meta_query  = [];

    public $postTypes       = [];

    public function __construct( $taxNameLong, $postType ){
        $this->entityName = $taxNameLong;
        $this->setPostTypes( array( $postType ) );
        $this->getAllExistingTerms();

    }

    function getAllExistingTerms( $force = false )
    {
        if( empty( $this->items ) || $force ) {
            $this->items = $this->selectTerms();
        }
        return $this->items;
    }

    public function selectTerms( $alreadyFilteredPosts = [] )
    {
        global $wpdb;

        $IN                  = false;
        $key_in              = '';
        $new_result          = [];
        $min_and_max         = [
            'min' => 0,
            'max' => 0
        ];
        $post_and_types      = [];
        $is_tax_translatable = false;

        if( flrt_wpml_active() ){
            $wpml_settings = get_option( 'icl_sitepress_settings' );
            if( isset( $wpml_settings['taxonomies_sync_option'][ $this->getTaxName() ] ) ){
                if( $wpml_settings['taxonomies_sync_option'][ $this->getTaxName() ] === '1' ){
                    $is_tax_translatable = true;
                }
            }
        }

        if( ! empty( $this->postTypes ) && isset($this->postTypes[0]) && $this->postTypes[0] ){
            foreach ( $this->postTypes as $postType ){
                $key_in .= '_' . $postType;
                $pieces[] = $wpdb->prepare( "%s", $postType );
            }

            $IN = implode(", ", $pieces );
        }

        $transient_key = flrt_get_terms_transient_key( 'tax_numeric_'. $this->getName() . $key_in );

        if ( false === ( $result = get_transient( $transient_key ) ) ) {

            $sql[] = "SELECT {$wpdb->terms}.term_id,{$wpdb->terms}.name,{$wpdb->terms}.slug,{$wpdb->posts}.post_type,{$wpdb->posts}.ID";
            $sql[] = "FROM {$wpdb->terms}";
            $sql[] = "LEFT JOIN {$wpdb->term_taxonomy} ON ({$wpdb->term_taxonomy}.term_id = {$wpdb->terms}.term_id)";
            $sql[] = "LEFT JOIN {$wpdb->term_relationships} ON ({$wpdb->term_relationships}.term_taxonomy_id = {$wpdb->term_taxonomy}.term_taxonomy_id)";
            $sql[] = "LEFT JOIN {$wpdb->posts} ON ({$wpdb->term_relationships}.object_id = {$wpdb->posts}.ID)";

            if( flrt_wpml_active() && defined( 'ICL_LANGUAGE_CODE' ) && $is_tax_translatable ){
                $sql[] = "LEFT JOIN {$wpdb->prefix}icl_translations AS wpml_translations";
                $sql[] = "ON {$wpdb->terms}.term_id = wpml_translations.element_id";

                if( ! empty( $this->postTypes ) ){
                    $sql[] = "AND wpml_translations.element_type IN(";
                    $sql[] = $wpdb->prepare( "CONCAT('tax_', '%s')", $this->getTaxName() );
                    $sql[] = ")";
                }
            }

            $sql[] = "WHERE {$wpdb->term_taxonomy}.taxonomy = %s";

            if( $IN ) {
                $sql[] = "AND {$wpdb->posts}.post_type IN( {$IN} )";
            }

            if( flrt_wpml_active() && defined( 'ICL_LANGUAGE_CODE' ) && $is_tax_translatable ){
                $sql[] = $wpdb->prepare("AND wpml_translations.language_code = '%s'", ICL_LANGUAGE_CODE);
            }

            $sql = implode(' ', $sql);

            $tax_name   = wp_unslash( $this->getTaxName() );
            $sql        = $wpdb->prepare( $sql, $tax_name );

            $result     = $wpdb->get_results( $sql, ARRAY_A );

            $clean_from_non_numeric = [];
            foreach ( $result as $single_term ) {
                if ( preg_match( '/[^\d\.]+/', $single_term['name'] ) ) {
                    continue;
                }

                $clean_from_non_numeric[] = $single_term;
            }
            $result = $clean_from_non_numeric;

            set_transient( $transient_key, $result, FLRT_TRANSIENT_PERIOD_HOURS * HOUR_IN_SECONDS );
        }

        if( ! empty( $result ) ) {

                $postsIn_flipped = array_flip( $alreadyFilteredPosts );
                $wpManager      = Container::instance()->getWpManager();
                $queried_values = $wpManager->getQueryVar( 'queried_values', [] );
                $filter_slug    = false;

                foreach ( $queried_values as $slug => $filter ) {
                    if ( $filter[ 'e_name' ] === $this->getName() ) {
                        $filter_slug = $slug;
                        break;
                    }
                }

                $max = false;
                $min = false;

                if ( $filter_slug ) {
                    if ( isset( $queried_values[ $filter_slug ][ 'values' ][ 'max' ] ) ) {
                        $max = (float) $queried_values[ $filter_slug ][ 'values' ][ 'max' ];
                    }

                    if ( isset( $queried_values[ $filter_slug ][ 'values' ][ 'min' ] ) ) {
                        $min = (float) $queried_values[ $filter_slug ][ 'values' ][ 'min' ];
                    }
                }

                foreach ( $result as $single_term ) {

                        if( ! empty( $alreadyFilteredPosts ) ) {
                            if( ! isset( $postsIn_flipped[ $single_term['ID'] ] ) ) {
                                continue;
                            }
                        }

                        $new_result[] = (float) $single_term['name'];

                        if ( $min !== false && $single_term['name'] < $min ){
                            continue;
                        }

                        if ( $max !== false && $single_term['name'] > $max ){
                            continue;
                        }

                        $post_and_types[ $single_term['ID'] ] = $single_term['post_type'];
                }

        }

        if( ! empty( $new_result ) ) {
            $min_and_max = [
                'min' => floor( apply_filters( 'wpc_set_num_shift', min( $new_result ), $this->getName() ) ),
                'max' => ceil( apply_filters( 'wpc_set_num_shift', max( $new_result ), $this->getName() ) ),
            ];
        }

        $terms = $this->convertSelectResult( $min_and_max, $post_and_types );

        return $terms;
    }

    /**
     * Creates term name that will be used for Chips
     * @param $edge string possible values min|max
     * @param $value string numeric selected value
     * @param $queried_values array queried filters
     * @return string
     */
    private function createTermName( $edge, $value, $queried_values )
    {
        $queriedFilter  = false;
        $taxonomy       = get_taxonomy( $this->getTaxName() );

        $label          = mb_strtolower( $taxonomy->labels->singular_name );

        if ( ! $label ) {
            $label = mb_strtolower( $taxonomy->name );
        }

        if ( $queried_values ) {
            foreach ( $queried_values as $slug => $filter ) {
                if ( $filter['e_name'] === $this->getName() ) {
                    $queriedFilter = $filter;
                    break;
                }
            }
        }

        $name = $edge.' '.$label;

        if( isset( $queriedFilter['values'][$edge] ) ) {
            $name = $name .' '. $queriedFilter[ 'values' ][ $edge ];
        }else{
            $name = $name .' '. $value;
        }

        return apply_filters( 'wpc_filter_tax_numeric_term_name', $name, $this->getName() );
    }

    /**
     * The method does nothing. All thing already were done before.
     * @param $setId
     * @param $post_type
     */
    function populateTermsWithPostIds( $setId, $post_type )
    {
        // Does nothing. It was already done before.
        // Do not remove this method because the parent will be applied!
    }

    public function setExcludedTerms( $excludedTerms, $isInclude )
    {
        $this->excludedTerms = $excludedTerms;
        $this->isInclude     = $isInclude;
    }

    function excludeTerms( $terms )
    {
        //@todo may not work properly!!!

        return $terms;
    }

    public function addTermsToWpQuery( $queried_value, $wp_query )
    {
        $new_tax_num_query = [];
        $min = isset( $queried_value['values']['min'] ) ? $queried_value['values']['min'] : false;
        $max = isset( $queried_value['values']['max'] ) ? $queried_value['values']['max'] : false;

        $tax_num_query = $wp_query->get( 'tax_num_query' );

        if ( $min !== false ) {
            $min  = apply_filters( 'wpc_unset_num_shift', $min, $this->getName() );

            $type = $this->isDecimal( $queried_value['step'], $min ) ? 'DECIMAL(15,6)' : 'SIGNED';
            $new_tax_num_query[ $this->getTaxName() ]['min'] = array(
                'type'  => $type,
                'value' => $min,
            );
        }

        if ( $max !== false ) {
            $max  = apply_filters( 'wpc_unset_num_shift', $max, $this->getName() );

            $type = $this->isDecimal( $queried_value['step'], $max ) ? 'DECIMAL(15,6)' : 'SIGNED';
            $new_tax_num_query[ $this->getTaxName() ]['max'] = array(
                'type'  => $type,
                'value' => $max,
            );
        }

        if( is_array( $tax_num_query ) ) {
            $tax_num_query = array_merge( $tax_num_query, $new_tax_num_query );
        } else {
            $tax_num_query = $new_tax_num_query;
        }

        $wp_query->set( 'tax_num_query', $tax_num_query );

        return $wp_query;
    }


    public function getTaxName()
    {
        return mb_strcut( $this->getName(), strlen( 'taxonomy_' ) );
    }

}