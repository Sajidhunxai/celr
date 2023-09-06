<?php

namespace FilterEverything\Filter\Pro\Admin;

if ( ! defined('WPINC') ) {
    wp_die();
}

class MetaBoxes
{
    public function __construct()
    {
        add_action('admin_head', array( $this, 'adminHead' ) );
    }

    public function adminHead()
    {
        add_meta_box(
            'filters-seo-rules',
            esc_html__( "Rule Settings", 'filter-everything' ),
            array( $this, 'seoRulesMetabox' ),
            FLRT_SEO_RULES_POST_TYPE,
            'normal',
            'high'
        );

        remove_meta_box(
            'submitdiv',
            array( FLRT_SEO_RULES_POST_TYPE ),
            'side'
        );

        add_meta_box(
            'submitdiv',
            esc_html__( 'Publish' ),
            [ 'FilterEverything\Filter\MetaBoxes', 'commonSideMetaBox' ],
            array( FLRT_SEO_RULES_POST_TYPE ),
            'side'
        );
    }

    public function seoRulesMetabox( $post, $meta ){
        $args = array(
            'post'  => $post,
            'meta'  => $meta
        );

        flrt_include_admin_view('filters-seo-rules', $args );
    }

}

new MetaBoxes();