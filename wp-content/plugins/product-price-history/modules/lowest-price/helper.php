<?php

namespace Devnet\PPH\Modules\LowestPrice;

use Devnet\PPH\Includes\Helper as GlobalHelper;

class Helper
{

    private static $options = DEVNET_PPH_OPTIONS['lowest_price'];

    /**
     * Get product lowest price.
     *
     * @since     1.0.0
     */
    public static function get_lowest_price($product_id = null, $product = null)
    {

        if (!$product_id || !$product) {
            return;
        }

        $inherit_regular = self::$options['inherit_regular'] ?? true;

        $entries = GlobalHelper::get_price_history($product_id, false, '30_days');
        $entries_count = count($entries);

        if ($entries_count > 1) {
            array_pop($entries);
        }

        $lowest_price = null;

        foreach ($entries as $item) {
            if ($lowest_price === null || $item['price'] < $lowest_price) {
                $lowest_price = $item['price'];
            }
        }

        if ($inherit_regular) {

            if (empty($entries) || $entries_count === 1) {
                if ($product->get_type() === 'variable') {
                    $lowest_price = $product->get_variation_regular_price('max', true);
                } else {
                    $lowest_price = $product->get_regular_price();
                }
            }
        }


        return $lowest_price;
    }

    /**
     * Get product lowest price ranges.
     *
     * @since     1.0.0
     */
    public static function get_variable_product_lowest_price_info($ids = [])
    {
        $info = [
            'min' => null,
            'max' => null,
            'all' => [],
            'variations' => [],
        ];

        foreach ($ids as $id) {
            $product = wc_get_product($id);
            $lowest_price =  self::get_lowest_price($id, $product);
            $info['all'][] = $lowest_price;
            $info['variations'][$id] = $lowest_price;
        }

        $info['min'] = min($info['all']);
        $info['max'] = max($info['all']);

        return $info;
    }

    /**
     * Find lowest price from the entries array.
     *
     * @since     1.0.0
     */
    public static function find_lowest_price($entries = [], $range = '')
    {

        // Define a variable to hold the lowest price
        $lowest_price = null;

        /*
         * TODO: get user input.
         */
        $range = esc_html('-30 days');

        // Iterate through each item in the array
        foreach ($entries as $item) {
            // Check if the item's date_created is within the last 30 days
            if (strtotime($item['date_created']) >= strtotime($range)) {
                // Check if the current lowest price is null or greater than the current item's price
                if ($lowest_price === null || $item['price'] < $lowest_price) {
                    // Set the lowest price to the current item's price
                    $lowest_price = $item['price'];
                }
            }
        }

        return $lowest_price;
    }
}
