<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Support;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WC_Product;

/**
 * Product meta data handler class for Stripe express gateway.
 *
 * @since 3.7.8
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Support
 */
class ProductMeta {

    /**
     * The product object.
     *
     * @since 3.7.8
     *
     * @var WC_Product
     */
    private $product = null;

    /**
     * Class instance
     *
     * @since 3.6.1
     *
     * @var static
     */
    private static $instance = null;

    /**
     * Sets the product object.
     *
     * @since 3.7.8
     *
     * @param int|WC_Product $product
     *
     * @return static
     */
    public static function set( $product ) {
        if ( ! is_object( $product ) ) {
            $product = wc_get_product( $product );
        }

        if ( ! $product instanceof WC_Product ) {
            $product = new WC_Product();
        }

        if ( ! static::$instance ) {
            static::$instance = new static();
        }

        static::$instance->set_product( $product );

        return static::$instance;
    }

    /**
     * Sets the product object.
     *
     * @since 3.7.8
     *
     * @param WC_Product $product
     *
     * @return self
     */
    private function set_product( WC_Product $product ) {
        $this->product = $product;
        return $this;
    }

    /**
     * Saves the product data.
     *
     * @since 3.7.8
     *
     * @return void
     */
    public function save() {
        if ( is_callable( [ $this->product, 'save' ] ) ) {
            $this->product->save();
        }
    }

    /**
     * Retrieves the Stripe sproduct id for a product.
     *
     * @since 3.7.8
     *
     * @return string|false
     */
    public function get_stripe_product_id() {
        $stripe_product_id = $this->product->get_meta( Helper::meta_key( 'product_id' ), true );
        return empty( $stripe_product_id ) ? false : $stripe_product_id;
    }

    /**
     * Updates the Stripe product id for a product.
     *
     * @since 3.7.8
     *
     * @param string $stripe_product_id
     *
     * @return self
     */
    public function update_stripe_product_id( $stripe_product_id ) {
        $this->product->update_meta_data( Helper::meta_key( 'product_id' ), $stripe_product_id );
        return $this;
    }

    /**
     * Retrieves the Stripe price id for a product.
     *
     * @since 3.7.8
     *
     * @return string|false
     */
    public function get_stripe_price_id() {
        $stripe_price_id = $this->product->get_meta( Helper::meta_key( 'price_id' ), true );
        return empty( $stripe_price_id ) ? false : $stripe_price_id;
    }

    /**
     * Updates the Stripe product id for a product.
     *
     * @since 3.7.8
     *
     * @param string $stripe_price_id
     *
     * @return self
     */
    public function update_stripe_price_id( $stripe_price_id ) {
        $this->product->update_meta_data( Helper::meta_key( 'price_id' ), $stripe_price_id );
        return $this;
    }

    /**
     * Returns meta key for no of product key.
     *
     * @since 3.7.8
     *
     * @return string
     */
    public static function no_of_product_key() {
        return '_no_of_product';
    }

    /**
     * Retrieves no of product.
     *
     * @since 3.7.8
     *
     * @return string|false
     */
    public function get_no_of_product() {
        return $this->product->get_meta( self::no_of_product_key(), true );
    }

    /**
     * Updates the number of product.
     *
     * @since 3.7.8
     *
     * @param int|string $no_of_product
     *
     * @return self
     */
    public function update_no_of_product( $no_of_product ) {
        $this->product->update_meta_data( self::no_of_product_key(), $no_of_product );
        return $this;
    }

    /**
     * Deletes metadata of no of product.
     *
     * @since 3.7.8
     *
     * @return self
     */
    public function delete_no_of_product() {
        $this->product->delete_meta_data( self::no_of_product_key() );
        return $this;
    }
}
