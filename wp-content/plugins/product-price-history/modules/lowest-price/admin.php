<?php

namespace Devnet\PPH\Modules\LowestPrice;

class LP_Admin
{

    private $module = 'lowest_price';

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }


    /**
     * @since    2.0.0
     */
    public function settings_section($sections)
    {

        $sections[] = [
            'id'    => 'devnet_pph_lowest_price',
            'title' => esc_html__('Lowest Price', 'product-price-history')
        ];

        return $sections;
    }

    /**
     * @since    1.2.0
     */
    public static function defaults($option_name = '')
    {
        $options = [
            'enable_lowest_price'    => 0,
            'only_single'            => 0,
            'only_onsale'            => 1,
            'inherit_regular'        => 1,
            'variable_product_price' => 'range',
            'text'                   => esc_html__('Lowest Price in the last 30 days: {lowest_price}', 'product-price-history'),
        ];

        return $options[$option_name] ?? '';
    }

    /**
     * @since    2.0.0
     */
    public static function settings_fields($fields)
    {
        $fields['devnet_pph_lowest_price'] = [
            [
                'type'    => 'checkbox',
                'name'    => 'enable_lowest_price',
                'label'   => esc_html__('Enable', 'product-price-history'),
                'default' => self::defaults('enable_lowest_price')
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'only_single',
                'label'   => esc_html__('Only on product page', 'product-price-history'),
                'default' => self::defaults('only_single')
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'only_onsale',
                'label'   => esc_html__('Only when product on-sale', 'product-price-history'),
                'default' => self::defaults('only_onsale')
            ],
            [
                'type'    => 'checkbox',
                'name'    => 'inherit_regular',
                'label'   => esc_html__('Inherit from regular price', 'product-price-history'),
                'desc'    => esc_html__('When insufficient price history information is available, the regular price will be displayed as the lowest price.', 'product-price-history'),
                'default' => self::defaults('inherit_regular')
            ],
            [
                'type'    => 'select',
                'name'    => 'variable_product_price',
                'label'   => esc_html__('Variable product price', 'product-price-history'),
                'options' => [
                    'range' => esc_html__('Range (min - max)', 'product-price-history'),
                    'min'   => esc_html__('Min', 'product-price-history'),
                    'max'   => esc_html__('Max', 'product-price-history'),
                    'none'  => esc_html__('Don\'t display', 'product-price-history'),
                ],
                'default' => self::defaults('range')
            ],
            [
                'type'    => 'text',
                'name'    => 'text',
                'label'   => esc_html__('Text', 'product-price-history'),
                'desc'    => esc_html__('Placeholder for lowest price {lowest_price}', 'product-price-history'),
                'default' => self::defaults('text'),
                'sanitize_callback' => 'wp_filter_post_kses'
            ],
        ];

        return $fields;
    }
}
