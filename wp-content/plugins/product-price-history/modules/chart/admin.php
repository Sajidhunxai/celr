<?php

namespace Devnet\PPH\Modules\Chart;

class Chart_Admin
{
    private  $module = 'chart' ;
    private  $plugin_name ;
    private  $version ;
    public function __construct( $plugin_name, $version )
    {
        $this->plugin_name = $plugin_name;
        $this->version = $version;
    }
    
    /**
     * Format range_selector options for select2 picker.
     *
     * @since    2.0.0
     */
    static function formatted_range_selector_options()
    {
        $formatted_options = [];
        $option = DEVNET_PPH_OPTIONS['chart'];
        $selected = ( isset( $option['range_selector'] ) ? array_flip( $option['range_selector'] ) : [] );
        $options = [
            'all'       => esc_html__( 'All', 'product-price-history' ),
            '7_days'    => esc_html__( 'Last 7 days', 'product-price-history' ),
            '30_days'   => esc_html__( 'Last 30 days', 'product-price-history' ),
            '3_months'  => esc_html__( 'Last 3 months', 'product-price-history' ),
            '6_months'  => esc_html__( 'Last 6 months', 'product-price-history' ),
            '12_months' => esc_html__( 'Last 12 months', 'product-price-history' ),
        ];
        foreach ( $options as $key => $label ) {
            $formatted_options[] = [
                'id'       => $key,
                'text'     => $label,
                'selected' => isset( $selected[$key] ),
            ];
        }
        return $formatted_options;
    }
    
    /**
     * @since    1.2.0
     */
    public static function defaults( $option_name = '' )
    {
        $options = [
            'enable_chart'          => 0,
            'position'              => 'woocommerce_product_meta_start',
            'date_range'            => '',
            'range_selector'        => '',
            'min_prices_to_display' => 2,
            'only_for_variation'    => 0,
            'title'                 => esc_html_x( '', 'Chart title', 'product-price-history' ),
            'daily_average'         => 0,
            'type'                  => 'bar',
            'border_color'          => 'rgba(130,36,227,0.5)',
            'text_color'            => 'rgba(0,0,0,1)',
            'background_color'      => 'rgba(255,255,255,0)',
            'max_width'             => '100',
        ];
        return $options[$option_name] ?? '';
    }
    
    /**
     * @since    2.0.0
     */
    public function settings_section( $sections )
    {
        $sections[] = [
            'id'    => 'devnet_pph_chart',
            'title' => esc_html__( 'Chart', 'product-price-history' ),
        ];
        return $sections;
    }
    
    /**
     * @since    2.0.0
     */
    public static function settings_fields( $fields )
    {
        $chart = [
            [
            'type'    => 'checkbox',
            'name'    => 'enable_chart',
            'label'   => esc_html__( 'Enable', 'product-price-history' ),
            'default' => self::defaults( 'enable_chart' ),
        ],
            [
            'type'    => 'select',
            'name'    => 'position',
            'label'   => esc_html__( 'Position', 'product-price-history' ),
            'options' => [
            'woocommerce_product_meta_start'           => esc_html__( 'After product meta', 'product-price-history' ),
            'woocommerce_after_single_product_summary' => esc_html__( 'After product summary', 'product-price-history' ),
            '_disabled_1'                              => esc_html__( 'Custom - I\'ll insert a shortcode', 'product-price-history' ),
        ],
            'default' => self::defaults( 'position' ),
        ],
            [
            'type'    => 'select',
            'name'    => 'date_range',
            'label'   => esc_html__( 'Date range', 'product-price-history' ),
            'options' => [
            ''          => esc_html__( 'All', 'product-price-history' ),
            '7_days'    => esc_html__( 'Last 7 days', 'product-price-history' ),
            '30_days'   => esc_html__( 'Last 30 days', 'product-price-history' ),
            '3_months'  => esc_html__( 'Last 3 months', 'product-price-history' ),
            '6_months'  => esc_html__( 'Last 6 months', 'product-price-history' ),
            '12_months' => esc_html__( 'Last 12 months', 'product-price-history' ),
        ],
            'default' => self::defaults( 'date_range' ),
        ],
            [
            'type'    => 'select2',
            'name'    => 'range_selector__disabled',
            'label'   => esc_html__( 'Range selector', 'product-price-history' ),
            'options' => [],
            'default' => self::defaults( 'range_selector' ),
        ],
            [
            'type'              => 'number',
            'name'              => 'min_prices_to_display',
            'label'             => esc_html__( 'Minimum prices to display', 'product-price-history' ),
            'step'              => '1',
            'default'           => self::defaults( 'min_prices_to_display' ),
            'sanitize_callback' => 'absint',
        ],
            [
            'type'    => 'checkbox',
            'name'    => 'daily_average__disabled',
            'label'   => esc_html__( 'Daily average price', 'product-price-history' ),
            'desc'    => esc_html__( 'Helpful when there are frequent price fluctuations within a single day', 'product-price-history' ),
            'default' => self::defaults( 'daily_average' ),
        ],
            [
            'type'    => 'checkbox',
            'name'    => 'only_for_variation',
            'label'   => esc_html__( 'Show chart on variable products only when variation is selected', 'product-price-history' ),
            'default' => self::defaults( 'only_for_variation' ),
        ],
            [
            'type'    => 'select',
            'name'    => 'chart_type',
            'label'   => esc_html__( 'Chart type', 'product-price-history' ),
            'options' => [
            'bar'         => esc_html__( 'Bar', 'product-price-history' ),
            '_disabled_1' => esc_html__( 'Stepped', 'product-price-history' ),
            '_disabled_2' => esc_html__( 'Line', 'product-price-history' ),
        ],
            'default' => self::defaults( 'chart_type' ),
        ],
            [
            'type'              => 'text',
            'name'              => 'title',
            'label'             => esc_html__( 'Title', 'product-price-history' ),
            'default'           => self::defaults( 'title' ),
            'sanitize_callback' => 'sanitize_text_field',
        ],
            [
            'type'    => 'color',
            'name'    => 'border_color',
            'label'   => esc_html__( 'Graph border color', 'product-price-history' ),
            'default' => self::defaults( 'border_color' ),
        ],
            [
            'type'              => 'number',
            'name'              => 'max_width',
            'label'             => esc_html__( 'Chart maximal width', 'product-price-history' ),
            'unit'              => '%',
            'min'               => 0,
            'step'              => '1',
            'default'           => self::defaults( 'max_width' ),
            'sanitize_callback' => 'absint',
        ],
            [
            'type'    => 'color',
            'name'    => 'text_color',
            'label'   => esc_html__( 'Chart text color', 'product-price-history' ),
            'default' => self::defaults( 'text_color' ),
        ],
            [
            'type'    => 'color',
            'name'    => 'background_color',
            'label'   => esc_html__( 'Chart background color', 'product-price-history' ),
            'default' => self::defaults( 'background_color' ),
        ]
        ];
        $fields['devnet_pph_chart'] = $chart;
        return $fields;
    }

}