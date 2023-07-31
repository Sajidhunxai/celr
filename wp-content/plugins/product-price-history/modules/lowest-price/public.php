<?php

namespace Devnet\PPH\Modules\LowestPrice;

use Devnet\PPH\Modules\LowestPrice\Helper;
use Devnet\PPH\Modules\LowestPrice\LP_Admin;


class LP_Public
{

    private $module = 'lowest_price';

    private $plugin_name;
    private $version;
    private $multilingual;
    private $lowest_price_module;
    private $lowest_price_options;

    public function __construct($plugin_name, $version)
    {

        $this->plugin_name = $plugin_name;
        $this->version = $version;

        $general_options = DEVNET_PPH_OPTIONS['general'];

        $this->multilingual = $general_options['multilingual'] ?? false;

        $this->lowest_price_options = DEVNET_PPH_OPTIONS['lowest_price'];
        $this->lowest_price_module = $this->lowest_price_options['enable_lowest_price'] ?? false;
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    2.0.0
     */
    public function enqueue_scripts()
    {
        // Go ahead only if Lowest Price module enabled.
        if (!$this->lowest_price_module) return;


        $name = $this->plugin_name . '-' . $this->module;

        $script_asset_path = plugin_dir_url(__DIR__) . '../assets/build/public-lowest-price.asset.php';
        $script_info       = file_exists($script_asset_path)
            ? include $script_asset_path
            : ['dependencies' => ['jquery'], 'version' => $this->version];


        wp_enqueue_script(
            $name,
            plugin_dir_url(__DIR__) . '../assets/build/public-lowest-price.js',
            $script_info['dependencies'],
            $script_info['version'],
            true
        );
    }

    /**
     * Price html filter for adding lowest price info.
     *  
     * @since     1.0.0
     */
    public function price_html($price_html, $product)
    {
        if (is_admin() && !wp_doing_ajax()) {
            return $price_html;
        }

        $is_enabled = $this->lowest_price_options['enable_lowest_price'] ?? false;
        $only_single = $this->lowest_price_options['only_single'] ?? false;
        $price_display = $this->lowest_price_options['variable_product_price'] ?? '';
        $only_onsale = $this->lowest_price_options['only_onsale'] ?? true;
        $text = $this->lowest_price_options['text'] ?? LP_Admin::defaults('text');
        $variation_ids = [];

        if (!$is_enabled) {
            return $price_html;
        }

        $product_type = $product->get_type();
        $is_variable  = 'variable' === $product_type;
        $is_variation = 'variation' === $product_type;

        if ($only_single) {

            // Single product page.
            if (is_product()) {

                $post_id = get_queried_object_id();

                $main_product_on_page = $product->get_id() === $post_id;
                $related_to_main_product = $is_variation && $product->get_parent_id() === $post_id;

                if (!$related_to_main_product && !$main_product_on_page) {
                    return $price_html;
                }
            } else {
                return $price_html;
            }
        }





        if ($only_onsale && !$product->is_on_sale()) {
            return $price_html;
        }

        if ($is_variable && $price_display === 'none') {
            return $price_html;
        }

        if ($this->multilingual) {
            $text = LP_Admin::defaults('text');
        }

        $lowest_price = Helper::get_lowest_price($product->get_id(), $product);

        $formatted_lp_range = '';

        if ($lowest_price) {

            if ($is_variable) {

                $var_min_price = $product->get_variation_price('min', true);
                $var_max_price = $product->get_variation_price('max', true);

                if ($var_min_price === $var_max_price) {
                    $price_html = wc_price(wc_get_price_to_display($product));
                }

                $variation_ids = $product->get_children();

                $vplp_info = Helper::get_variable_product_lowest_price_info($variation_ids);

                // Skip this block if min and max are the same, then we'll show just one price.
                if (($vplp_info['min'] && $vplp_info['max']) && ($vplp_info['min'] !== $vplp_info['max'])) {

                    if ($price_display === 'min') {
                        $lowest_price = $vplp_info['min'];
                    } elseif ($price_display === 'max') {
                        $lowest_price = $vplp_info['max'];
                    } else {
                        // Show price range (min - max) and format right away to include/exclude taxes.
                        $formatted_lp_range = wc_price(wc_get_price_to_display($product, ['price' => $vplp_info['min']]));
                        $formatted_lp_range .= ' - ';
                        $formatted_lp_range .= wc_price(wc_get_price_to_display($product, ['price' => $vplp_info['max']]));
                    }
                }
            } else {
                // wc_get_price_to_display() returns the price including or excluding tax, 
                // based on the ‘woocommerce_tax_display_shop’ setting.
                // Here we want to remove striked out regular price.
                $price_html = wc_price(wc_get_price_to_display($product));
            }

            $formatted_lowest_price = $formatted_lp_range ? $formatted_lp_range : wc_price(wc_get_price_to_display($product, ['price' => $lowest_price]));

            $lowest_price_html = str_replace('{lowest_price}', $formatted_lowest_price, $text);

            $price_html .= '<div class="pph-lowest-price">' . $lowest_price_html . '</div>';
        }

        return $price_html;
    }
}
