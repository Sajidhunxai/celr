<?php

namespace FilterEverything\Filter\Pro;

if ( ! defined('WPINC') ) {
    wp_die();
}

use FilterEverything\Filter\FiltersWidget;

class ShortcodesPro
{
    function __construct(){
        remove_shortcode( 'fe_open_widget' );
        remove_shortcode( 'fe_widget_open_button' );
        add_shortcode( 'fe_open_widget', [$this, 'widgetOpenButton'] );
        add_shortcode( 'fe_open_button', [$this, 'widgetOpenButton'] );
    }

    public function widgetOpenButton( $atts )
    {
        ob_start();
        $setId = 0;
        if( isset( $atts['id'] ) ){
            $setId = preg_replace('/[^\d]?/', '', $atts['id']);
        }

        flrt_filters_button( $setId );

        $html = ob_get_clean();
        return $html;
    }
}