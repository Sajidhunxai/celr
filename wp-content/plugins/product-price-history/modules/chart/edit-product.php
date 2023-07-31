<?php

namespace Devnet\PPH\Modules\Chart;


class Edit_Product
{


    public function __construct()
    {
    }


    /**
     * Add options to pph tab/panel.
     *  
     * @since     1.0.0
     */
    public function pph_hide_chart()
    {
        $id = get_the_ID();

        woocommerce_wp_checkbox([
            'id'          => 'pph_hide_chart',
            'value'       => get_post_meta($id, '_pph_hide_chart', true),
            'label'       => esc_html__('Hide chart', 'product-price-history'),
            'desc_tip'    => false,
        ]);
    }


    /**
     * Save product fields.
     *  
     * @since     1.0.0
     */
    public function save_fields($id, $post)
    {

        $hide_chart = isset($_POST['pph_hide_chart']) ? 'yes' : '';
        update_post_meta($id, '_pph_hide_chart', $hide_chart);
    }
}
