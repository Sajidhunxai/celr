<?php

namespace Devnet\PPH\Admin;

use  Devnet\PPH\Includes\Helper ;
class Edit_Product
{
    public function __construct()
    {
    }
    
    /**
     * Add custom tab to product tabs.
     *  
     * @since     1.0.0
     */
    public function product_data_tab( $tabs )
    {
        $tabs['pph_price_history'] = [
            'label'    => esc_html__( 'Product Price History', 'product-price-history' ),
            'target'   => 'pph_price_history',
            'class'    => [],
            'priority' => 100,
        ];
        return $tabs;
    }
    
    /**
     * Add options to pph tab/panel.
     *  
     * @since     1.0.0
     */
    public function product_data_panels()
    {
        $id = get_the_ID();
        ?>

        <style>
            li.pph_price_history_tab a:before {
                content: "\f238" !important;
            }
        </style>

        <div id="pph_price_history" class="panel woocommerce_options_panel hidden">

            <?php 
        do_action( 'pph_before_editable_table' );
        ?>

            <?php 
        $this->editable_product_price_history( $id );
        ?>

        </div>

<?php 
    }
    
    /**
     * Build editable table.
     *  
     * @since     1.0.0
     */
    public function editable_product_price_history( $product_id, $no_entries_notice = true )
    {
        $entries = Helper::get_price_history( $product_id );
        
        if ( !empty($entries) ) {
            $count = count( $entries );
            $limit = 1000;
            echo  '<div class="pph-table-wrapper">' ;
            
            if ( $count > $limit ) {
                $entries = array_slice( $entries, -$limit, $limit );
                echo  '<p><small>' . esc_html__( 'To ensure optimal performance, only a limited number of results are shown in the table.', 'product-price-history' ) . '</small></p>' ;
                printf( '<p>' . esc_html__( 'Displaying %d out of %d results.', 'product-price-history' ) . '</p>', $limit, $count );
            }
            
            echo  '<table class="pph-table">' ;
            echo  '<tr>' ;
            echo  '<th class="short">' . esc_html__( 'Price', 'product-price-history' ) . '</th>' ;
            echo  '<th class="short">' . esc_html__( 'Currency', 'product-price-history' ) . '</th>' ;
            echo  '<th>' . esc_html__( 'Date', 'product-price-history' ) . '</th>' ;
            echo  '<th class="short">' . esc_html__( 'Hide', 'product-price-history' ) . '</th>' ;
            echo  '</tr>' ;
            foreach ( $entries as $entry ) {
                echo  '<tr data-id="' . esc_attr( $entry['id'] ) . '">' ;
                echo  '<td><input type="number" name="pph_sale_price" value="' . esc_attr( $entry['price'] ) . '" disabled /></td>' ;
                echo  '<td><input type="text" name="pph_currency" value="' . esc_attr( $entry['currency'] ) . '" disabled /></td>' ;
                echo  '<td><input type="text" name="pph_date_created" value="' . esc_attr( $entry['date_created'] ) . '" disabled /></td>' ;
                echo  '<td class="center"><input type="checkbox" name="pph_hidden" value="1" ' . checked( esc_attr( $entry['hidden'] ), true, false ) . ' /></td>' ;
                echo  '</tr>' ;
            }
            echo  '</table>' ;
            echo  '</div>' ;
        } else {
            
            if ( $no_entries_notice ) {
                echo  '<div style="padding: 1rem;">' ;
                echo  esc_html__( 'No pricing data recorded since plugin activation - This message indicates that no pricing data has been saved in the database since the activation of the plugin. This can occur if the plugin was recently installed or if the product price has not changed since the activation of the plugin.', 'product-price-history' ) ;
                echo  '</div>' ;
            }
        
        }
    
    }
    
    /**
     * Output editable table to variation panel.
     *  
     * @since     1.0.0
     */
    public function variation_panel( $loop, $variation_data, $variation )
    {
        $this->editable_product_price_history( $variation->ID, false );
    }
    
    /**
     * Create entry on price change.
     *  
     * @since     1.0.0
     */
    public function update_product( $product, $data_store )
    {
        $interval = null;
        Helper::create_entry( $product, $interval );
    }
    
    /**
     * Ajax action for updating editable table fields.
     *  
     * @since     1.0.0
     */
    public function update_pph_db_row()
    {
        $id = ( isset( $_POST['args']['id'] ) ? intval( $_POST['args']['id'] ) : null );
        $is_hidden = ( isset( $_POST['args']['hidden'] ) ? absint( $_POST['args']['hidden'] ) : 0 );
        Helper::update_entry( $id, [
            'hidden' => $is_hidden,
        ] );
        wp_send_json( $is_hidden );
        wp_die();
    }
    
    /**
     * When deleting the product or variation, delete also entries from pph table.
     *  
     * @since     1.0.0
     */
    public function remove_deleted_products_from_pph_table( $post_id, $post )
    {
        if ( $post->post_type === 'product' || $post->post_type === 'product_variation' ) {
            Helper::delete_product_entries( $post_id );
        }
    }

}