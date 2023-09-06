<?php

namespace FilterEverything\Filter;

if ( ! defined('WPINC') ) {
    wp_die();
}

class Filter
{
    public $filters = [];

    private $logicSeparators;

    public $sep = '#';

    public function __construct( $filter_operators ){
        $this->logicSeparators = $filter_operators;
    }

    public function getEmptyFilter()
    {
        return apply_filters( 'wpc_filter_defaults', array(
                    'ID'         => '',
                    'parent'     => '',
                    'menu_order' => '',
                    'entity'     => '',
                    'e_name'     => '',
                    'label'      => '',
                    'slug'       => '',
                    'view'       => '',
                    'show_term_names' => '',
                    'logic'      => '',
                    'orderby'    => '',
                    'in_path'    => '',
                    'exclude'    => '',
                    'include'    => '',
                    'collapse'   => '',
                    'a_labels'   => '',
                    'hierarchy'  => '',
                    'used_for_variations' => '',
                    'range_slider' => '',
                    'step'       => '',
                    'search'     => '',
                    'parent_filter' => '',
                    'hide_until_parent' => '',
                    'tooltip'    => '',
                    'more_less'  => '',
                    'show_chips' => ''
            )
        );
    }

    public function getEntityKey( $entity, $e_name = '' )
    {
        // Meta field or Tax numeric
        if( $e_name ){
//            if ( mb_strpos( $e_name, 'taxonomy_' ) !== false  ) {
//                $e_name = mb_strcut( $e_name, strlen( 'taxonomy_' ) );
//            }

            return $entity . $this->sep . $e_name;
        }

        // Replace first "_" with $this->sep
        if( mb_strpos( $entity, '_' ) !== false ){
            $_position = strpos( $entity, '_' );
            return substr_replace( $entity, $this->sep, $_position, 1 );
        }

        return $entity;
    }

    public function getEntityCanonicalName( $entity )
    {
        if( mb_strpos( $entity, 'post_meta' ) !== false || mb_strpos( $entity, 'tax_numeric' ) !== false ){
            $canonical = explode( $this->sep, $entity, 2 );
            return $canonical[0];
        } else {
            return str_replace( $this->sep, '_', $entity );
        }

    }

    public function getEntityFullName( $entity, $e_name )
    {
        if( ! empty( $e_name ) ){
            return $entity . $this->sep . $e_name;
        }
        return $entity;
    }

    public function getEntityEname( $entityFullName )
    {
        if( mb_strpos( $entityFullName, $this->sep ) !== false ){
            $pieces = explode( $this->sep, $entityFullName, 2 );
            return $pieces[1];
        }

        return $entityFullName;
    }

    public function needEntityToModifyEname( $filter )
    {
        if( mb_strpos( $filter['entity'], 'taxonomy' ) === false
            &&
            mb_strpos( $filter['entity'], 'author' ) === false
            &&
            mb_strpos( $filter['entity'], 'tax_numeric' ) === false ){
            return false;
        }
        return true;
    }

    public function splitEntityFullNameInFilter( $filter )
    {
        if ( ! $this->needEntityToModifyEname( $filter ) ) {
            return $filter;
        }

        if ( mb_strpos( $filter['entity'], 'taxonomy' ) !== false ) {
            $filter['e_name'] = mb_strcut( $filter['entity'], strlen( 'taxonomy_' ) );
            $filter['entity'] = 'taxonomy';
            return $filter;
        }

        if ( mb_strpos( $filter['entity'], 'author' ) !== false ) {
            $filter['e_name'] = 'author';
            $filter['entity'] = 'author';
            return $filter;
        }

        return $filter;
    }

    public function combineEntityNameInFilter( $filter )
    {

        if( ! $this->needEntityToModifyEname( $filter ) ){
            return $filter;
        }

        if( mb_strpos( $filter['entity'], 'taxonomy' ) !== false ){
            $filter['entity'] = $filter['entity'] . '_' . $filter['e_name'];
            $filter['e_name']   = '';
            return $filter;
        }

        if( mb_strpos( $filter['entity'], 'author' ) !== false ){
            $filter['entity'] = 'author_author';
            $filter['e_name'] = '';
            return $filter;
        }

        return $filter;
    }

    public function filterExists( $newFilter ){
        foreach ( $this->filters as $filter ) {
            if( $filter['e_name'] === $newFilter['e_name'] ){
                return true;
            }

            if( $filter['slug'] === $newFilter['slug']  ){
                return true;
            }
        }

        return false;
    }

    public function isFilterInPath( $filter ){
        if( $filter['in_path'] === 'yes' ){
            return true;
        }
        return false;
    }

    public function isFilterInQueryString( $filter ){
        if( $filter['in_path'] === 'no' ){
            return true;
        }
        return false;
    }

    public function addTermValue( $filter, $term ){
        if( isset( $filter['values'] ) ){
            if( ! in_array( $term, $filter['values'] ) ) {
                $filter['values'][] = $term;
                sort($filter['values'] );
            }
        } else {
            $filter['values'] = $term;
        }

        return $filter;
    }

    public function getFiltersOrder()
    {
        $permalinksTab = new PermalinksTab();
        return array_keys( get_option( $permalinksTab->optionName, [] ) );
    }

    public function sortTerms( $terms = [] ){
        asort( $terms );
        return $terms;
    }

    public function getLogicSeparator( $filter ){
        return $this->logicSeparators[ $filter['logic'] ];
    }

    public function getAllFilters(){
        return $this->filters;
    }
}