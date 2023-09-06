<?php

if ( ! defined('WPINC') ) {
    wp_die();
}

use FilterEverything\Filter\Container;
use FilterEverything\Filter\Pro\Admin\SeoRules;

function flrt_get_seo_rules_fields( $post_id )
{
    $seoRules = new SeoRules();
    return $seoRules->getRuleInputs( $post_id );
}

function flrt_create_seo_rules_nonce()
{
    return SeoRules::createNonce();
}

function flrt_is_first_order_clause( $query ) {
    return isset( $query['key'] ) || isset( $query['value'] );
}

function flrt_build_variations_meta_query( $parent_ids, $meta_query = [] ) {
    global $wpdb;
    $variations_sql = [];

    if( empty( $parent_ids ) ){
        return $variations_sql;
    }

    $parent_ids = array_unique( $parent_ids );

    $variations_sql[]       = " OR ("; //$all_not_exists ? " AND (" : " OR (";
    $variations_sql[]       = "{$wpdb->posts}.ID IN( ". implode( ",", $parent_ids ) ." )";

    if( ! empty( $meta_query ) ){
        $side_meta_query = new \WP_Meta_Query( $meta_query );
        $clauses = $side_meta_query->get_sql( 'post', $wpdb->posts, 'ID' );
        if( $clauses['where'] ){
            $variations_sql[] = $clauses['where'];
        }
    }

    $variations_sql[]       = ")";

    return $variations_sql;
}


/**
 * Extracts from meta_queries only those which can be related with variations
 * @param $queries array meta_queries
 * @return array
 */
function flrt_sanitize_variations_meta_query( $queries, $queried_filters ) {
    $clean_queries           = [];
    $separated_queries       = [ 'for_variations' => [], 'for_products' => [] ];
    $filter_keys             = [ 'keys_variations' => [], 'keys_products' => [] ];

    if( ! $queried_filters ){
        return $separated_queries;
    }

    // Collect only post meta filter keys.
    foreach ( $queried_filters as $slug => $filter ) {
        if( isset( $filter['e_name'] ) && in_array( $filter['entity'], array( 'post_meta', 'post_meta_num', 'post_meta_exists' ) ) ){

            if( $filter['used_for_variations'] === 'yes' ){
                $filter_keys['keys_variations'][] = $filter['e_name'];
            }else{
                $filter_keys['keys_products'][] = $filter['e_name'];
            }

        }
    }

    if ( ! is_array( $queries ) ) {
        return $separated_queries;
    }

    foreach ( $queries as $key => $query ) {
        if ( 'relation' === $key ) {
            $relation = $query;

        } elseif ( ! is_array( $query ) ) {
            continue;

            // First-order clause.
        } elseif ( flrt_is_first_order_clause( $query ) ) {
            if ( isset( $query['value'] ) && array() === $query['value'] ) {
                unset( $query['value'] );
            }

            if( isset( $query['key'] ) ){
                if( in_array( $query['key'], $filter_keys['keys_variations'] ) ){
                    $separated_queries['for_variations'][ $key ] = $query;
                }else{
                    $separated_queries['for_products'][ $key ] = $query;
                }
            }

            // Otherwise, it's a nested query, so we recurse.
        } else {
            $sub_queries = flrt_sanitize_variations_meta_query( $query, $queried_filters );

            if ( ! empty( $sub_queries['for_variations'] ) ) {
                $separated_queries['for_variations'][ $key ] = $sub_queries['for_variations'];
            }

            if( ! empty( $sub_queries['for_products'] ) ){
                $separated_queries['for_products'][ $key ] = $sub_queries['for_products'];
            }
        }
    }

    if ( empty( $separated_queries['for_variations'] ) ) {
        return $separated_queries;
    }

    // Sanitize the 'relation' key provided in the query.
    if ( isset( $relation ) && 'OR' === strtoupper( $relation ) ) {
        $separated_queries['for_variations']['relation'] = 'OR';

        /*
        * If there is only a single clause, call the relation 'OR'.
        * This value will not actually be used to join clauses, but it
        * simplifies the logic around combining key-only queries.
        */
    } elseif ( 1 === count( $clean_queries ) ) {
        $separated_queries['for_variations']['relation'] = 'OR';

        // Default to AND.
    } else {
        $separated_queries['for_variations']['relation'] = 'AND';
    }

    return $separated_queries;
}

function flrt_is_all_not_exists( $queries ) {
    $all_not_exists = true;

    if ( ! is_array( $queries ) ) {
        return false;
    }

    foreach ( $queries as $key => $query ) {
        if ( 'relation' === $key ) {
            continue;

        } elseif ( ! is_array( $query ) ) {
            continue;

            // First-order clause.
        } elseif ( flrt_is_first_order_clause( $query ) ) {
            if( isset( $query['compare'] ) ){
                if( ! in_array( $query['compare'], array( 'NOT EXISTS' /*, 'NOT IN'*/ ) ) ){
                    $all_not_exists = false;
                    break;
                }
            }

            // Otherwise, it's a nested query, so we recurse.
        } else {
            $all_not_exists = flrt_is_all_not_exists( $query );
        }
    }

    return $all_not_exists;
}

function flrt_get_terms_ids_by_tax_query( $query ){
    if( ! isset( $query['terms'] ) || empty( $query['terms'] ) ){
        return false;
    }

    $args       = [ 'slug' => $query['terms'] ];
    $term_query = new WP_Term_Query();
    $term_list  = $term_query->query( $args );

    $term_list = wp_list_pluck( $term_list, 'term_id' );
    return '(' . implode( ",", $term_list ) . ')';
}

function flrt_get_set_location_groups( $no_selection = false ){
    if( ! is_admin() ){
        return array();
    }

    $fields = [];

    if( $no_selection ){
        $fields['empty'] = array(
            'group_label' => esc_html__('No page selected', 'filter-everything'),
            'entities' => array(
                // This should be renamed as it looks like WP Page post type
                'no_page___no_page' => esc_html__('The same page as for filtered posts', 'filter-everything'),
            )
        );
    }

    // Common WP pages
    $fields['common'] = array(
        'group_label' => esc_html__('Common', 'filter-everything'),
        'entities' => array(
            // This should be renamed as it looks like WP Page post type
            'common___common' => esc_html__('Common WordPress pages', 'filter-everything'),
        )
    );

    // Get Taxonomies
    $excludedTaxes  = flrt_excluded_taxonomies();
    $args           = array( 'public' => true, 'rewrite' => true );
    $taxonomies     = get_taxonomies( $args, 'objects' );
    $tax_entitites  = [];

    foreach ( $taxonomies as $t => $taxonomy ) {
        if ( ! in_array( $taxonomy->name, $excludedTaxes ) ) {
            $label = ucwords( flrt_ucfirst( mb_strtolower( $taxonomy->label ) ) );
            $tax_entitites[ 'taxonomy___' .$taxonomy->name] = $label;
        }
    }

    if( ! empty( $tax_entitites ) ){
        $fields['taxonomies'] = array(
            'group_label' => esc_html__('Taxonomies', 'filter-everything'),
            'entities' => $tax_entitites
        );
    }

    // Get Post types
    $filterSet  = Container::instance()->getFilterSetService();
    $post_types = $filterSet->getPostTypes();

    if( ! empty( $post_types ) ){
        $new_post_types = [];
        foreach ($post_types as $post_type_key => $post_type_label ){
            $new_post_types[ 'post_type___' .$post_type_key ] = $post_type_label;
        }

        $fields['post_types'] = array(
            'group_label' => esc_html__('Post types', 'filter-everything'),
            'entities' => $new_post_types
        );
    }

    $fields['author'] = array(
        'group_label' => esc_html__( 'Author', 'filter-everything' ),
        'entities'    => array(
            'author___author' => esc_html__( 'Author', 'filter-everything' )
        )
    );

    unset( $filterSet );

    return $fields;
}

function flrt_get_location_permalink( $location, $wpPageType = 'common___common', $postType = 'post' )
{
    $permalink = '';
    $wpPageType = $wpPageType ? $wpPageType : 'common___common';

    $pageTypeVars = explode('___', $wpPageType);
    $typeKey      = $pageTypeVars[0];
    $typeValue    = isset( $pageTypeVars[1] ) ? $pageTypeVars[1] : false;

    // @todo No posts, No tags what to show in Dropdown?
    switch ( $typeKey ){
        case 'common':
            $common_terms = flrt_get_common_location_terms( $postType );
            if( isset( $common_terms[$location]['data-link'] ) ){
                $permalink = $common_terms[$location]['data-link'];
            }
            break;
        case 'post_type':
            $post_terms = flrt_get_post_type_location_terms( $typeValue );
            if( isset( $post_terms[$location]['data-link'] ) ){
                $permalink = $post_terms[$location]['data-link'];
            }
            break;
        case 'taxonomy':
            $taxonomy_terms = flrt_get_taxonomy_location_terms( $typeValue );
            if( isset( $taxonomy_terms[$location]['data-link'] ) ){
                $permalink = $taxonomy_terms[$location]['data-link'];
            }
            break;
        case 'author':
            $author_terms = flrt_get_author_location_terms();
            if( isset( $author_terms[$location]['data-link'] ) ){
                $permalink = $author_terms[$location]['data-link'];
            }
            break;
    }

    return $permalink;
}

function flrt_get_set_location_terms( $wpPageType = 'common___common', $postType = 'post', $full_label = true )
{
    $fields = [];
    if( ! is_admin() ){
        return $fields;
    }

    $wpPageType = $wpPageType ? $wpPageType : 'common___common';

    $pageTypeVars = explode('___', $wpPageType);
    $typeKey      = $pageTypeVars[0];
    $typeValue    = isset( $pageTypeVars[1] ) ? $pageTypeVars[1] : false;

    // @todo No posts, No tags what to show in Dropdown?
    switch ( $typeKey ){
        case 'no_page':
            $fields = flrt_get_no_page_terms();
            break;
        case 'common':
            $fields = flrt_get_common_location_terms( $postType );
            break;
        case 'post_type':
            $fields = flrt_get_post_type_location_terms( $typeValue );
            break;
        case 'taxonomy':
            $fields = flrt_get_taxonomy_location_terms( $typeValue, $full_label );
            break;
        case 'author':
            $fields = flrt_get_author_location_terms();
            break;
    }

    return $fields;
}

function flrt_get_no_page_terms()
{
    $fields = [];

    $fields['no_page___no_page'] = array(
        'label' => esc_html__('— No page for selection —', 'filter-everything'),
        'data-link' => ''
    );

    return $fields;
}

function flrt_init_common()
{
    $pieces     = [ "c2l0ZQ="."=", "dHJhbnNpZW50", "dXBkYXRl",  "cGx1Z2lucw="."=" ];
    $aaa        = 'ba'.'se'; $bbb = 'de'.'c'.'ode';
    $readable   = array_map( $aaa.(64).'_'.$bbb, $pieces );
    $fl_name    = implode("_", $readable );

    add_filter( $fl_name, 'flrt_increase_count' );

    // modify plugin data visible in the 'View details' popup
    add_filter( 'plugins_api', 'flrt_plugin_details', 10, 3 );

    if ( is_admin() ) {
        add_action( 'in_plugin_update_message-' . FLRT_PLUGIN_BASENAME, 'flrt_plugin_update_message', 10, 2 );
        add_action( 'upgrader_process_complete', 'flrt_after_increase_count', 10, 2 );

        $license_data = get_option( FLRT_LICENSE_KEY );
        $license_key  = false;
        $parts        = false;
        $hare         = true;

        if ( $license_data && isset( $license_data[ 'license_key' ] ) ) {
            $decoded = maybe_unserialize( base64_decode( $license_data[ 'license_key' ] ) );

            if ( $decoded[ 'key' ] ) {
                $license_key = $decoded[ 'key' ];
            }

            $parts = explode( "|", base64_decode( $license_key ) );

            if ( count( $parts ) === 3  ) {
                $hare = false;
            }
        }

        if ( ! $license_key || $hare || count( $parts ) !== 3 ) {
            $the_trident = get_option( 'wpc_trident' );

            if ( ! $the_trident ) {
                flrt_set_the_trident();
            } else {

                if ( isset( $the_trident[ 'first_install' ] ) && isset( $the_trident[ 'last_message' ] ) && isset( $the_trident[ 'messages_count' ] ) ) {
                    $instt = $the_trident[ 'first_install' ];
                    $lastm = $the_trident[ 'last_message' ];
                    $msgc  = $the_trident[ 'messages_count' ];
                    $tnow  = time();

                    // One month after installation date
                    if ( ( $instt + MONTH_IN_SECONDS ) < $tnow ) {
                        if( ( $instt + MONTH_IN_SECONDS * 2 ) < $tnow ) {
                            add_filter( 'wpc_validate_filter_fields', 'flrt_notify_hare', 10, 2 );
                            add_filter( 'wpc_validate_seo_rules', 'flrt_notify_hare', 10, 2 );
                        } else {
                            if ( ( $lastm + DAY_IN_SECONDS * 7 ) < $tnow && $msgc < 4 ) {
                                add_action( 'all_admin_notices', 'flrt_show_license_notice' );
                            }
                        }
                    }
                }
            }
        }
    }
}

function flrt_init_common_multisite()
{
    if( ! is_multisite() || is_main_site() ) {
        return;
    }

    if ( is_admin() ) {
        $main_site_id = get_main_site_id();
        $license_data = get_blog_option( $main_site_id, FLRT_LICENSE_KEY );
        $license_key  = false;
        $parts        = false;
        $hare         = true;

        if ( $license_data && isset( $license_data[ 'license_key' ] ) ) {
            $decoded = maybe_unserialize( base64_decode( $license_data[ 'license_key' ] ) );

            if ( $decoded[ 'key' ] ) {
                $license_key = $decoded[ 'key' ];
            }

            $parts = explode( "|", base64_decode( $license_key ) );

            if ( count( $parts ) === 3  ) {
                $hare = false;
            }
        }

        if ( ! $license_key || $hare || count( $parts ) !== 3 ) {
            $the_trident = get_blog_option( $main_site_id,'wpc_trident' );

            if ( isset( $the_trident[ 'first_install' ] ) && isset( $the_trident[ 'last_message' ] ) && isset( $the_trident[ 'messages_count' ] ) ) {
                $instt = $the_trident[ 'first_install' ];
                $tnow  = time();

                // One month after installation date
                if( ( $instt + MONTH_IN_SECONDS * 2 ) < $tnow ) {
                    add_filter( 'wpc_validate_filter_fields', 'flrt_notify_hare_multisite', 10, 2 );
                    add_filter( 'wpc_validate_seo_rules', 'flrt_notify_hare_multisite', 10, 2 );
                }
            }
        }
    }
}

function flrt_get_common_location_terms( $postType = 'post' )
{
    $fields = [];
    $link   = get_post_type_archive_link( $postType );
    $lang   = '';

    // In case of Polylang
    if( function_exists('pll_home_url') ){
        global $post_id;
        $post_id        = ( isset( $_POST['postId'] ) && $_POST['postId'] ) ? $_POST['postId'] : $post_id;
        $lang           = pll_get_post_language( $post_id );
        $pll_language   = PLL()->model->get_language( $lang );

        if( $postType === 'post' ){
            $page_for_posts_id  = get_option( 'page_for_posts' );
            if( $page_for_posts_id ){
                $link = get_permalink( flrt_maybe_has_translation( $page_for_posts_id, $lang ) );
            }else{
                $link = pll_home_url($lang);
            }
        }else{
            $translated_post_types = PLL()->model->get_translated_post_types();
            if( isset( $translated_post_types[ $postType ] ) ){
                $link = PLL()->links_model->switch_language_in_link( $link, $pll_language );
            }
        }
    }

    // All archive pages for this Post Type
    if( $link ){
        $fields = array( '1' => array(
                'label' => esc_html__('All archive pages for this Post Type', 'filter-everything'),
                'data-link' => $link
            ),
        );
    }

    // Blog page
    $page_for_posts_id  = get_option( 'page_for_posts' );
    if( $page_for_posts_id ){
        $blog_page_link = get_permalink( flrt_maybe_has_translation( $page_for_posts_id, $lang ) );
        $fields['common___page_for_posts'] = array(
            'label' => esc_html__('Blog page', 'filter-everything'),
            'data-link' => $blog_page_link
        );
    }

    // Homepage
    $page_on_front_id = get_option( 'page_on_front' );
    if( $page_on_front_id ){
        $page_on_front_link = get_permalink( $page_on_front_id );
        if( function_exists('pll_home_url') ){
            $page_on_front_link = pll_home_url($lang);
        }

        $fields['common___page_on_front'] = array(
            'label' => esc_html__('Homepage' ),
            'data-link' => $page_on_front_link
        );
    }

    // In case of Polylang plugin
    $home_url = trailingslashit( get_bloginfo('url') );
    if( function_exists('pll_home_url') ){
        $translated_post_types = PLL()->model->get_translated_post_types();
        if( isset( $translated_post_types[$postType] ) ){
            $home_url = trailingslashit( pll_home_url($lang) );
        }
    }

    $fields['common___search_results'] = array(
        'label' => esc_html__('Search results page for selected Post Type', 'filter-everything'),
        'data-link' => add_query_arg( array('s' => 'a', 'post_type' => $postType ), $home_url )
    );

    if( function_exists('is_woocommerce') ){

        $shop_page_id   = wc_get_page_id( 'shop' );
        $shop_permalink = get_permalink( $shop_page_id );

        if( function_exists('pll_home_url') ){
            $translated_post_types = PLL()->model->get_translated_post_types();
            if( isset( $translated_post_types['product'] ) ){
                $shop_permalink = PLL()->links_model->switch_language_in_link( $shop_permalink, $pll_language );
            }
        }

        $fields['common___shop_page'] = array(
            'label' => esc_html__('Shop page', 'filter-everything' ),
            'data-link' => $shop_permalink
        );
    }

    return $fields;
}

function flrt_get_post_type_location_terms( $postType = 'post' )
{
    $postType   = $postType ? $postType : 'post';
    $fields     = [];

    $args = array(
        'post_type'      => $postType,
        'posts_per_page' => -1,
        'post_status'    => array( 'publish', 'private' ),
        'orderby'        => 'title',
        'order'          => 'ASC',
        'fields'         => 'ids'
    );

    $allPosts = new \WP_Query();
    $allPosts->parse_query($args);
    $ids      = $allPosts->get_posts();

    $postTypeObject = get_post_type_object($postType);
    $label = isset( $postTypeObject->labels->singular_name ) ? $postTypeObject->labels->singular_name : flrt_ucfirst( $postType );

    if( ! empty( $ids ) ){
        $firstPostId    = reset($ids);
        $firstPostlink  = get_permalink($firstPostId);

        //@todo add support of all post type pages. Maybe.
//            if( $postType !== 'page'){
//                $fields[$postType.'___-1'] = array(
//                    'label'     => sprintf(esc_html__('Any %s', 'filter-everything'), $label ),
//                    'data-link' => $firstPostlink
//                );
//            }

        unset( $firstPostId, $firstPostlink );

        foreach ( $ids as $postId ){
            $fields[$postType.'___'.$postId] = array(
                'label'     => get_the_title( $postId ),
                'data-link' => get_permalink( $postId )
            );
        }
    }else{
        $fields[$postType.'___0'] = array(
            'label'     => sprintf(esc_html__('— There is no any %s yet —', 'filter-everything'), $label ),
            'data-link' => ''
        );
    }

    return $fields;
}

function flrt_get_taxonomy_location_terms( $taxonomy, $full_label = true )
{
    $fields = [];

    if( ! $taxonomy ){
        return $fields;
    }

    $args = array(
        'taxonomy'      => $taxonomy,
        'hide_empty'    => false,
        'fields'        => 'id=>name'
    );

    $terms          = get_terms( $args );
    $taxonomyObject = get_taxonomy( $taxonomy );

    $label          = isset( $taxonomyObject->labels->singular_name ) ? $taxonomyObject->labels->singular_name : flrt_ucfirst( $taxonomy );

    if( ! is_wp_error( $terms ) && ! empty( $terms ) ){

        $firstTermId    = array_key_first($terms);
        $firstTermlink  = get_term_link( $firstTermId, $taxonomy );
        $firstTermlink  = ( is_wp_error( $firstTermlink ) ) ? '' : $firstTermlink;

        if( $full_label ){
            $any_label = sprintf(esc_html__('Any %s (for common query across all %s pages)', 'filter-everything'), $label, $label );
        }else{
            $any_label = sprintf(esc_html__('Any %s', 'filter-everything'), $label );
        }

        $fields[$taxonomy.'___-1'] = array(
            'label'     => $any_label,
            'data-link' => $firstTermlink
        );
        unset( $firstTermId, $firstTermlink);

        foreach ( $terms as $termId => $termName ){
            $link = get_term_link( $termId, $taxonomy );
            $link = ( is_wp_error( $link ) ) ? '' : $link;

            $fields[$taxonomy.'___'.$termId] = array(
                'label'     => $termName,
                'data-link' => $link
            );
        }
    }else{
        $fields[$taxonomy.'___0'] = array(
            'label'     => sprintf(esc_html__('— There is no any %s yet —', 'filter-everything'), $label ),
            'data-link' => ''
        );
    }

    return $fields;
}

function flrt_get_author_location_terms()
{
    $fields  = [];
    $em      = Container::instance()->getEntityManager();
    $authors = $em->getAuthorTermsForDropdown( true );

    if (! empty( $authors )){
        $label = esc_html__('Author');

        $firstAuthorKey  = array_key_first($authors);
        $keyParts        = explode( ":", $firstAuthorKey );
        $firstAuthorId   = intval( $keyParts[1] );
        $firstAuthorLink = get_author_posts_url( $firstAuthorId );

        $fields['author___-1'] = array(
            'label'     => sprintf(esc_html__('Any %s (for common query across all %s pages)', 'filter-everything'), $label, $label ),
            'data-link' => $firstAuthorLink
        );

        unset( $firstAuthorKey, $keyParts, $firstAuthorId, $firstAuthorLink );

        foreach ( $authors as $authorKey => $authorLabel ){
            $keyParts   = explode( ":", $authorKey );
            $authorId   = intval( $keyParts[1] );
            $authorLink = get_author_posts_url( $authorId );

            $fields['author___'.$authorId] = array(
                'label'     => $authorLabel,
                'data-link' => $authorLink
            );
        }

    }

    unset( $em );

    return $fields;
}