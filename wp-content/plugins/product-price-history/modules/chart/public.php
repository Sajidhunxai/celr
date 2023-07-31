<?php

namespace Devnet\PPH\Modules\Chart;

use  Devnet\PPH\Includes\Helper as GlobalHelper ;
use  Devnet\PPH\Modules\PriceAlerts\PA_Admin ;
class Chart_Public
{
    private  $module = 'chart' ;
    private  $plugin_name ;
    private  $version ;
    private  $chart_options ;
    private  $chart_module ;
    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param      string    $plugin_name       The name of the plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
        $this->chart_options = DEVNET_PPH_OPTIONS['chart'];
        $this->chart_module = $this->chart_options['enable_chart'] ?? false;
    }
    
    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        $name = $this->plugin_name . '-' . $this->module;
        wp_enqueue_style(
            $name,
            plugin_dir_url( __DIR__ ) . '../assets/build/public-chart.css',
            [],
            $this->version,
            'all'
        );
    }
    
    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        $name = $this->plugin_name . '-' . $this->module;
        $script_asset_path = plugin_dir_url( __DIR__ ) . '../assets/build/public-chart.asset.php';
        $script_info = ( file_exists( $script_asset_path ) ? include $script_asset_path : [
            'dependencies' => [ 'jquery' ],
            'version'      => $this->version,
        ] );
        // Go ahead only if Chart module enabled.
        
        if ( $this->chart_module ) {
            $pphData = $this->single_product_price_history_data();
            // Go ahead and load scripts only if we found entries.
            
            if ( !empty($pphData) ) {
                wp_enqueue_script(
                    $name,
                    plugin_dir_url( __DIR__ ) . '../assets/build/public-chart.js',
                    $script_info['dependencies'],
                    $script_info['version'],
                    true
                );
                wp_localize_script( $name, 'devnet_pph_chart_data', [
                    'ajaxurl' => admin_url( 'admin-ajax.php' ),
                    'pphData' => $pphData,
                    'strings' => [
                    'price' => esc_html__( 'Price', 'product-price-history' ),
                    'date'  => esc_html__( 'Date', 'product-price-history' ),
                    'title' => esc_html__( 'Price History', 'product-price-history' ),
                ],
                ] );
            }
        
        }
    
    }
    
    /**
     * Format pph data for chart.
     *  
     * @since     1.0.0
     */
    public function get_pph_data_by_id( $product_id, $product = null )
    {
        $data = [];
        $date_range = $this->chart_options['date_range'] ?? null;
        $range_selector = [];
        $min_prices_to_display = $this->chart_options['min_prices_to_display'] ?? 1;
        $border_color = $this->chart_options['border_color'] ?? null;
        $only_on_variation = $this->chart_options['only_for_variation'] ?? false;
        $text_color = $this->chart_options['text_color'] ?? 'black';
        $type = 'bar';
        $price_history = GlobalHelper::get_price_history( $product_id, false, $date_range );
        $found_entries = count( $price_history );
        if ( $found_entries < $min_prices_to_display ) {
            return;
        }
        $data['display'] = true;
        if ( $product && 'variable' === $product->get_type() && $only_on_variation ) {
            $data['display'] = false;
        }
        if ( $product ) {
            foreach ( $price_history as &$entry ) {
                $entry['regular_price'] = wc_get_price_to_display( $product, [
                    'price' => $entry['regular_price'],
                ] );
                $entry['sale_price'] = wc_get_price_to_display( $product, [
                    'price' => $entry['sale_price'],
                ] );
                $entry['price'] = wc_get_price_to_display( $product, [
                    'price' => $entry['price'],
                ] );
            }
        }
        $data['entries'] = $price_history;
        $data['chartSettings']['border_color'] = $border_color;
        $data['chartSettings']['text_color'] = $text_color;
        $data['chartSettings']['type'] = $type;
        return $data;
    }
    
    /**
     * Get formatted pph data on single product page.
     *  
     * @since     1.0.0
     */
    public function single_product_price_history_data()
    {
        $data = [];
        
        if ( function_exists( 'is_product' ) && is_product() ) {
            global  $product ;
            if ( empty($product) || !is_a( $product, 'WC_Product' ) ) {
                $product = wc_get_product( get_the_id() );
            }
            if ( get_post_meta( $product->get_id(), '_pph_hide_chart', true ) ) {
                return;
            }
            $data = $this->get_pph_data_by_id( $product->get_id(), $product );
        }
        
        return $data;
    }
    
    /**
     * output html wrapper where chart will initiate..
     *  
     * @since     1.0.0
     */
    public function price_history_output()
    {
        
        if ( $this->chart_module ) {
            $bg_color = $this->chart_options['background_color'] ?? 'transparent';
            $max_width = ( isset( $this->chart_options['max_width'] ) ? $this->chart_options['max_width'] . '%' : '100%' );
            $title = $this->chart_options['title'] ?? PA_Admin::defaults( 'title' );
            $style = '--pph-chart--background-color:' . $bg_color . ';';
            $style .= '--pph-chart--max-width:' . $max_width . ';';
            $html = '<div id="pphWrapper" style="' . esc_attr( $style ) . '">';
            if ( $title ) {
                $html .= '<span class="pph-chart-title">' . esc_html( $title ) . '</span>';
            }
            $html .= '</div>';
            
            if ( $this->chart_options['position'] === 'custom' ) {
                return $html;
            } else {
                echo  $html ;
            }
        
        }
    
    }
    
    /**
     * Ajax action for fetching variation lowest price.
     *  
     * @since     1.0.0
     */
    public function get_variation_price_history()
    {
        $variation_id = ( isset( $_GET['args']['id'] ) ? intval( $_GET['args']['id'] ) : null );
        $data = $this->get_pph_data_by_id( $variation_id );
        wp_send_json( $data );
        wp_die();
    }

}