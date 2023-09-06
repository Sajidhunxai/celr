<?php

    if ( ! defined('WPINC') ) {
        wp_die();
    }

    $set_id    = $post->ID;
    $filters    = flrt_get_configured_filters( $set_id );

    $filterSet  = \FilterEverything\Filter\Container::instance()->getFilterSetService();
    $the_set    = $filterSet->getSet( $set_id );

    $order = 1;
    $apply_button_order = ( isset( $the_set['apply_button_menu_order']['value'] ) && $the_set['apply_button_menu_order']['value'] > -1 ) ? $the_set['apply_button_menu_order']['value'] : (count($filters) + 1);
    $displayed = false;

?>
<div class="wpc-filter-set-wrapper">
    <div class="wpc-filter-set-hidden-fields">
        <input type="hidden" id="wpc_set_nonce" name="_flrt_nonce" value="<?php echo esc_attr( flrt_create_filters_nonce() ); ?>" />
    </div>
    <div class="wpc-column-labels-wrapper">
        <table class="wpc-form-fields-table">
            <?php
//            $attributes = $filterSet->getPostTypeField($set_id);

            $attributes['post_type'] = $the_set['post_type'];
            $post_type  = ( isset( $the_set['post_type']['value'] ) && $the_set['post_type']['value'] ) ? $the_set['post_type']['value'] : $the_set['post_type']['default'];

            flrt_include_admin_view('filter-field', array(
                    'field_key'  => key($attributes),
                    'attributes' =>  reset($attributes)
                )
            );

            ?>
        </table>
    </div>
    <div class="wpc-column-labels-wrapper">
        <div class="wpc-column-labels widget-title">
            <ul class="wpc-custom-row">
                <li class="wpc-filter-order">&nbsp;</li>
                <li class="wpc-filter-label"><?php esc_html_e('Label', 'filter-everything' ); ?></li>
                <li class="wpc-filter-entity"><?php esc_html_e('Filter by', 'filter-everything' ); ?></li>
                <li class="wpc-filter-view"><?php esc_html_e('View', 'filter-everything' ); ?></li>
                <li class="wpc-filter-slug"><?php esc_html_e('URL Prefix', 'filter-everything' ); ?></li>
            </ul>
        </div>
    </div>
    <div class="wpc-no-filters"<?php if( ! $filters ){ echo ' style="display: block;"'; }?>>
        <?php
            echo wp_kses(
                    __('No filters yet. Click the <strong>Add Filter</strong> button to create your first one.', 'filter-everything' ),
                    array( 'strong' => array() )
                );
            ?>
    </div>
    <div id="wpc-filters-list" class="wpc-filters-list" data-posttype="<?php echo $post_type; ?>">

        <?php if( $filters ):

                foreach( $filters as $filter ):

                    if( $apply_button_order == $order ){
                        flrt_include_admin_view( 'filter-apply-button', array(
                                'apply_button' => $the_set['apply_button_menu_order'],
                                'use_apply_button' => $the_set['use_apply_button'],
                                'apply_button_text' => $the_set['apply_button_text'],
                                'reset_button_text' => $the_set['reset_button_text'],
                                'apply_button_order' => $apply_button_order
                            )
                        );
                        $displayed = true;
                    }

                    flrt_include_admin_view( 'filter-row', array( 'filter' => $filter ) );

                    $order++;
                endforeach;

         endif; ?>
        <?php
        if( ! $displayed ) :
            flrt_include_admin_view( 'filter-apply-button', array(
                    'apply_button' => $the_set['apply_button_menu_order'],
                    'use_apply_button' => $the_set['use_apply_button'],
                    'apply_button_text' => $the_set['apply_button_text'],
                    'reset_button_text' => $the_set['reset_button_text'],
                    'apply_button_order' => $apply_button_order
                )
            );
        endif; ?>
    </div>

    <div class="wpc-add-filter-wrapper">
        <div class="wpc-add-filter-div">
            <a href="javascript:void(0);" class="button button-primary button-large wpc-add-filter"><?php esc_html_e('Add Filter','filter-everything' ); ?></a>
        </div>
    </div>

    <script type="text/html" id="wpc-new-filter">
        <?php
            flrt_include_admin_view( 'filter-row', array( 'filter' => flrt_get_empty_filter( $set_id ) ) );
        ?>
    </script>
</div>