<?php

namespace Devnet\PPH\Admin;

class Settings
{
    private  $settings_api ;
    /**
     * Initialize the class and set its properties.
     *
     * @since      1.0.0
     * @param      string    $plugin_name       The name of this plugin.
     * @param      string    $version    The version of this plugin.
     */
    public function __construct()
    {
        $this->settings_api = new Settings_API();
        $this->admin_init();
    }
    
    public function admin_init()
    {
        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );
        //initialize settings
        $this->settings_api->admin_init();
    }
    
    public function get_settings_sections()
    {
        $sections[] = [
            'id'    => 'devnet_pph_general',
            'title' => esc_html__( 'General', 'product-price-history' ),
        ];
        return apply_filters( 'pph_settings_sections', $sections );
    }
    
    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
    public function get_settings_fields()
    {
        $settings_fields = [
            'devnet_pph_general' => [
            [
            'type'    => 'checkbox',
            'name'    => 'multilingual',
            'label'   => esc_html__( 'Multilingual', 'product-price-history' ),
            'desc'    => esc_html__( 'Use your own translated strings.', 'product-price-history' ),
            'default' => 0,
        ],
            [
            'type'    => 'select',
            'name'    => 'record_interval',
            'label'   => esc_html__( 'Record price changes', 'product-price-history' ),
            'options' => [
            ''            => esc_html__( 'All price changes', 'product-price-history' ),
            '_disabled_1' => esc_html__( 'One price per hour', 'product-price-history' ),
            '_disabled_2' => esc_html__( 'One price in 3 hours', 'product-price-history' ),
            '_disabled_3' => esc_html__( 'One price in 6 hours', 'product-price-history' ),
            '_disabled_4' => esc_html__( 'One price in 12 hours', 'product-price-history' ),
            '_disabled_5' => esc_html__( 'One price in 24 hours', 'product-price-history' ),
        ],
            'default' => '',
            'desc'    => esc_html__( 'Choose the frequency at which price changes are recorded and updated in the database. Each selected option will ensure that only one price change is captured within the specified time interval.', 'product-price-history' ),
        ],
            [
            'type'    => 'select',
            'name'    => 'delete_old_data',
            'label'   => esc_html__( 'Delete price history older than', 'product-price-history' ),
            'options' => [
            '' => esc_html__( '-- Select --', 'product-price-history' ),
        ],
            'default' => '',
        ],
            [
            'type'    => 'checkbox',
            'name'    => 'delete_plugin_data',
            'label'   => esc_html__( 'Delete all plugin data on uninstall', 'product-price-history' ),
            'desc'    => esc_html__( 'Enabling this option will ensure that all data associated with the plugin, including settings, subscribers and all price histories, will be completely removed from the database upon uninstallation of the plugin.', 'product-price-history' ),
            'default' => 0,
        ]
        ],
        ];
        return apply_filters( 'pph_settings_fields', $settings_fields );
    }
    
    public function settings_page()
    {
        echo  '<div class="pph-wrap">' ;
        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();
        echo  '</div>' ;
    }
    
    /**
     * Get all the pages
     *
     * @return array page names with key value pairs
     */
    public function get_pages()
    {
        $pages = get_pages();
        $pages_options = [];
        if ( $pages ) {
            foreach ( $pages as $page ) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }
        return $pages_options;
    }

}