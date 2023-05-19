<?php

namespace WeDevs\DokanPro\REST;

use WeDevs\Dokan\REST\ProductBlockController;
use WP_Error;
use WP_REST_Server;

/**
 * Product Variation Block API.
 *
 * @since 3.7.13
 *
 * @package dokan
 */
class ProductVariationBlockController extends ProductBlockController {

    /**
     * Route base name.
     *
     * @var string
     */
    protected $base = 'blocks/product-variation';

    /**
     * Register all routes related with stores.
     *
     * @since 3.7.13
     *
     * @return void
     */
    public function register_routes() {
        register_rest_route(
            $this->namespace, '/' . $this->base . '/(?P<id>[\d]+)/', [
                'args' => [
                    'id' => [
                        'description' => __( 'Unique identifier for the object.', 'dokan' ),
                        'type'        => 'integer',
                    ],
                ],
                [
                    'methods'             => WP_REST_Server::READABLE,
                    'callback'            => [ $this, 'get_item' ],
                    'args'                => $this->get_collection_params(),
                    'permission_callback' => [ $this, 'get_single_product_permissions_check' ],
                ],
            ]
        );
    }

    /**
     * Get Variable product block detail.
     *
     * @since 3.7.13
     *
     * @param \WP_Request $request
     * @return array
     */
    public function get_item( $request ) {
        $product_id = absint( $request['id'] );
        $product    = wc_get_product( $product_id );

        if ( ! $product ) {
            return new WP_Error( 'product_not_found', __( 'Product variation not found', 'dokan' ), array( 'status' => 404 ) );
        }

        $context = ! empty( $request['context'] ) ? $request['context'] : 'view';

        return apply_filters(
            'dokan_rest_get_product_variable_block_data',
            [
                'general' => [
                    'enabled'             => in_array( $product->get_status( 'edit' ), [ 'publish', false ], true ),
                    'name'                => $product->get_title( $context ),
                    'slug'                => $product->get_slug( $context ),
                    'price'               => $product->get_price(),
                    'type'                => $product->get_type(),
                    'downloadable'        => $product->is_downloadable(),
                    'virtual'             => $product->is_virtual(),
                    'regular_price'       => $product->get_regular_price( $context ),
                    'sale_price'          => $product->get_sale_price( $context ) ? $product->get_sale_price( $context ) : '',
                    'date_on_sale_from'   => wc_rest_prepare_date_response( $product->get_date_on_sale_from( $context ), false ),
                    'date_on_sale_to'     => wc_rest_prepare_date_response( $product->get_date_on_sale_to( $context ), false ),
                    'images'              => $this->get_images( $product ),
                    'tags'                => $this->get_taxonomy_terms( $product, 'tag' ),
                    'description'         => 'view' === $context ? wpautop( do_shortcode( $product->get_description() ) ) : $product->get_description( $context ),
                    'catalog_visibility'  => $product->get_catalog_visibility( $context ),
                    'categories'          => $this->get_taxonomy_terms( $product ),
                    'attributes'          => $product->get_attributes(),
                ],
                'inventory' => [
                    'sku'               => $product->get_sku( $context ),
                    'stock_status'      => $product->is_in_stock() ? 'instock' : 'outofstock',
                    'manage_stock'      => $product->managing_stock(),
                    'stock_quantity'    => $product->get_stock_quantity( $context ),
                    'low_stock_amount'  => version_compare( WC_VERSION, '3.4.7', '>' ) ? $product->get_low_stock_amount( $context ) : '',
                    'backorders'        => $product->get_backorders( $context ),
                    'sold_individually' => $product->is_sold_individually(),
                ],
                'downloadable' => [
                    'downloads'           => $this->get_downloads( $product ),
                    'enable_limit_expiry' => ! empty( $product->get_download_limit( $context ) ) || ! $product->get_download_expiry( $context ),
                    'download_limit'      => $product->get_download_limit( $context ),
                    'download_expiry'     => $product->get_download_expiry( $context ),
                ],
                'advanced' => [
                    'purchase_note'   => 'view' === $context ? wpautop( do_shortcode( wp_kses_post( $product->get_purchase_note() ) ) ) : $product->get_purchase_note( $context ),
                    'reviews_allowed' => $product->get_reviews_allowed( $context ),
                ],
            ],
            $product,
            $context
        );
    }
}
