<?php

namespace WeDevs\DokanPro\Modules\OrderMinMax;

use WC_Product;

/**
 * OrderMinMax Module Helper.
 *
 * @since 3.7.13
 */
class Helper {

    /**
     * Min-max meta key.
     *
     * @since 3.7.13
     *
     * @var string
     */
    const MIN_MAX_META_KEY = '_dokan_min_max_meta';

    /**
     * Is min-max-qty is enabled for dokan.
     *
     * @since 3.7.13
     *
     * @return boolean
     */
    public static function is_enabled_min_max_qty() {
        return 'on' === dokan_get_option( 'enable_min_max_quantity', 'dokan_selling', 'off' );
    }

    /**
     * Is min-max-amount is enabled for dokan.
     *
     * @since 3.7.13
     *
     * @return boolean
     */
    public static function is_enabled_min_max_amount() {
        return 'on' === dokan_get_option( 'enable_min_max_amount', 'dokan_selling', 'off' );
    }

    /**
     * Is min-max qty and amount is enabled or not.
     *
     * @since 3.7.13
     *
     * @return boolean
     */
    public static function is_enabled_min_max_qty_or_amount() {
        return self::is_enabled_min_max_qty() || self::is_enabled_min_max_amount();
    }

    /**
     * Get product min-max meta.
     *
     * @since 3.7.13
     *
     * @param WC_Product $product
     *
     * @return array|null
     */
    public static function get_product_min_max_meta( $product ) {
        return $product->get_meta( self::MIN_MAX_META_KEY );
    }

    /**
     * Set product min-max meta.
     *
     * @since 3.7.13
     *
     * @param WC_Product $product
     * @param array      $meta    Order min max meta data.
     *
     * @return void
     */
    public static function set_product_min_max_meta( $product, $meta = [] ) {
        $product->update_meta_data( self::MIN_MAX_META_KEY, $meta );
        $product->save();
    }
}
