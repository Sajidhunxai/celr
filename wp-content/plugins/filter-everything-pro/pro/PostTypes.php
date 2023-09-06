<?php

namespace FilterEverything\Filter\Pro;

if ( ! defined('WPINC') ) {
    wp_die();
}

class PostTypes
{
    public function __construct()
    {
        add_action( 'init', array( $this, 'registerPostType' ) );
    }

    function registerPostType() {

        register_post_type( FLRT_SEO_RULES_POST_TYPE, array(
            'label'    => esc_html__( 'SEO Rules', 'filter-everything' ),
            'labels'			=> array(
                'name'					=> esc_html__( 'SEO Rules', 'filter-everything' ),
                'singular_name'			=> esc_html__( 'SEO Rule', 'filter-everything' ),
                'add_new'				=> esc_html__( 'Add SEO Rule' , 'filter-everything' ),
                'add_new_item'			=> esc_html__( 'Add New SEO Rule' , 'filter-everything' ),
                'edit_item'				=> esc_html__( 'Edit SEO Rule' , 'filter-everything' ),
                'new_item'				=> esc_html__( 'New SEO Rule' , 'filter-everything' ),
                'view_item'				=> esc_html__( 'View SEO Rule', 'filter-everything' ),
                'search_items'			=> esc_html__( 'Search SEO Rule', 'filter-everything' ),
                'not_found'				=> esc_html__( 'No SEO Rules found. Create your first SEO Rule.', 'filter-everything' ),
                'not_found_in_trash'	=> esc_html__( 'No SEO Rules found in Trash', 'filter-everything' ),
            ),
            'has_archive'       => false,
            'public'			=> false,
            'show_ui'			=> true,
            '_builtin'			=> false,
            'capability_type'	=> 'post',
            'hierarchical'		=> true,
            'rewrite'			=> false,
            'query_var'			=> false,
            'supports' 			=> array('title'),
            'show_in_menu'		=> false,
        ) );

    }
}

new PostTypes();
?>