<?php

namespace WeDevs\DokanPro;

use WC_Tax;
use WC_Product;
use WC_Product_Variable;
use WeDevs\Dokan\ProductCategory\Helper;
use Automattic\WooCommerce\Utilities\NumberUtil;

/**
 * Dokan Pro Product Bulk Edit Class
 *
 * @since   3.4.0
 *
 * @package dokan
 */
class ProductBulkEdit {

    /**
     * Load automatically when class initiate
     *
     * @since 3.4.0
     *
     * @uses  actions
     * @uses  filters
     */
    public function __construct() {
        add_filter( 'dokan_bulk_product_statuses', [ $this, 'bulk_product_status' ] );
        add_action( 'dokan_product_list_before_table_body_start', [ $this, 'bulk_edit_form' ] );
        add_action( 'template_redirect', [ $this, 'bulk_edit' ] );
        add_action( 'dokan_product_dashboard_errors', [ $this, 'display_updated_message' ] );
    }

    /**
     * Show Updated success message
     *
     * @since 2.6.3
     *
     * @return void
     */
    public function display_updated_message( $type ) {
        if ( 'product_bulk_edit_save_success' === $type ) {
            dokan_get_template_part(
                'global/dokan-success',
                '',
                [
                    'deleted' => true,
                    'message' => __( 'Product successfully updated.', 'dokan' ),
                ]
            );
        }
    }

    /**
     * Add bulk edit status.
     *
     * @since 3.4.0
     *
     * @param array $bulk_statuses previous status.
     *
     * @return array
     */
    public function bulk_product_status( $bulk_statuses ) {
        if ( ! current_user_can( 'dokan_edit_product' ) ) {
            return $bulk_statuses;
        }

        return dokan_array_insert_after( $bulk_statuses, [ 'edit' => __( 'Edit', 'dokan' ) ], - 1 );
    }

    /**
     * Dokan bulk edit form.
     *
     * @since 3.4.0
     *
     * @return void
     */
    public function bulk_edit_form() {
        if ( ! dokan_is_seller_enabled( dokan_get_current_user_id() ) ) {
            return;
        }

        $shipping_class = get_terms( 'product_shipping_class', [ 'hide_empty' => false ] );
        $post_statuses  = [
            ''        => __( '— No change —', 'dokan' ),
            'draft'   => __( 'Draft', 'dokan' ),
            'publish' => __( 'Online', 'dokan' ),
        ];
        $comment_status = [
            ''      => __( '— No change —', 'dokan' ),
            'open'  => __( 'Allow', 'dokan' ),
            'close' => __( 'Do not Allow', 'dokan' ),
        ];
        $price          = [
            ''  => __( '— No change —', 'dokan' ),
            '1' => __( 'Change to:', 'dokan' ),
            '2' => __( 'Increase existing price by (fixed amount or %):', 'dokan' ),
            '3' => __( 'Decrease existing price by (fixed amount or %):', 'dokan' ),
        ];
        $sale           = [
            ''  => __( '— No change —', 'dokan' ),
            '1' => __( 'Change to:', 'dokan' ),
            '2' => __( 'Increase existing sale price by (fixed amount or %):', 'dokan' ),
            '3' => __( 'Decrease existing sale price by (fixed amount or %):', 'dokan' ),
            '4' => __( 'Set to regular price decreased by (fixed amount or %):', 'dokan' ),
        ];
        $tax_status     = [
            ''         => __( '— No change —', 'dokan' ),
            'taxable'  => __( 'Taxable', 'dokan' ),
            'shipping' => __( 'Shipping only', 'dokan' ),
            'none'     => _x( 'None', 'Tax status', 'dokan' ),
        ];
        $tax_classes    = WC_Tax::get_tax_classes();
        if ( ! in_array( '', $tax_classes, true ) ) { // Make sure "Standard rate" (empty class name) is present.
            array_unshift( $tax_classes, 'Standard' );
        }
        $weight             = [
            ''  => __( '— No change —', 'dokan' ),
            '1' => __( 'Change to:', 'dokan' ),
        ];
        $lwh                = [
            ''  => __( '— No change —', 'dokan' ),
            '1' => __( 'Change to:', 'dokan' ),
        ];
        $visibility         = [
            ''        => __( '— No change —', 'dokan' ),
            'visible' => __( 'Catalog &amp; search', 'dokan' ),
            'catalog' => __( 'Catalog', 'dokan' ),
            'search'  => __( 'Search', 'dokan' ),
            'hidden'  => __( 'Hidden', 'dokan' ),
        ];
        $featured           = [
            ''    => __( '— No change —', 'dokan' ),
            'yes' => __( 'Yes', 'dokan' ),
            'no'  => __( 'No', 'dokan' ),
        ];
        $manage_stock       = [
            ''    => __( '— No change —', 'dokan' ),
            'yes' => __( 'Yes', 'dokan' ),
            'no'  => __( 'No', 'dokan' ),
        ];
        $stock_qty          = [
            ''  => __( '— No change —', 'dokan' ),
            '1' => __( 'Change to:', 'dokan' ),
            '2' => __( 'Increase existing stock by:', 'dokan' ),
            '3' => __( 'Decrease existing stock by:', 'dokan' ),
        ];
        $sold_individually  = [
            ''    => __( '— No change —', 'dokan' ),
            'yes' => __( 'Yes', 'dokan' ),
            'no'  => __( 'No', 'dokan' ),
        ];
        $is_single_category = 'single' === dokan_get_option( 'product_category_style', 'dokan_selling', 'single' );
        $args               = [
            'pro'                => true,
            'comment_status'     => $comment_status,
            'price'              => $price,
            'sale'               => $sale,
            'tax_status'         => $tax_status,
            'tax_classes'        => $tax_classes,
            'weight'             => $weight,
            'lwh'                => $lwh,
            'visibility'         => $visibility,
            'featured'           => $featured,
            'shipping_class'     => $shipping_class,
            'post_statuses'      => $post_statuses,
            'manage_stock'       => $manage_stock,
            'stock_qty'          => $stock_qty,
            'sold_individually'  => $sold_individually,
            'is_single_category' => $is_single_category,
        ];
        dokan_get_template_part( 'products/edit/bulk-edit-form', '', $args );
    }


    /**
     * Bulk saving product info.
     *
     * @since 3.4.0
     *
     * @throws \WC_Data_Exception
     *
     * @return void
     */
    public function bulk_edit() {
        if ( ! isset( $_POST['dokan-bulk-product-edit'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['dokan-bulk-product-edit'] ) ), 'dokan-bulk-product-edit-action' ) ) {
            return;
        }

        if ( ! current_user_can( 'dokan_edit_product' ) ) {
            wc_add_notice( __( 'You don\'t have the permission.', 'dokan' ), 'error' );

            return;
        }

        $request_data       = wp_unslash( $_POST );
        $bulk_products      = isset( $request_data['products_id'] ) ? array_map( 'sanitize_text_field', (array) $request_data['products_id'] ) : [];
        $is_single_category = 'single' === dokan_get_option( 'product_category_style', 'dokan_selling', 'single' );


        // $request_data['status'] is product bulk action status ( edit/publish... ), when user publishes products we have to skip updating chosen cats.
        if ( isset( $request_data['status'] ) && 'publish' !== $request_data['status'] && isset( $request_data['chosen_product_cat_bulk'] ) ) {
            $chosen_product_cat = $request_data['chosen_product_cat_bulk'];
            $chosen_cat = $is_single_category ? reset( $chosen_product_cat ) : $chosen_product_cat;

            foreach ( $bulk_products as $id ) {
                Helper::set_object_terms_from_chosen_categories( $id, (array) $chosen_cat );
            }
        }

        if ( ! empty( $request_data['product_tags'] ) ) {
            $product_tags    = [];
            $can_create_tags = 'on' === dokan_get_option( 'product_vendors_can_create_tags', 'dokan_selling', 'off' );

            foreach ( (array) $request_data['product_tags'] as $tag ) {
                // include existing tags
                if ( is_numeric( $tag ) ) {
                    $product_tags[] = intval( $tag );
                    continue;
                }
                // check if vendors can create tags
                if ( ! $can_create_tags ) {
                    continue;
                }
                // create new tag
                $new_tag = wp_insert_term( $tag, 'product_tag' );
                if ( ! is_wp_error( $new_tag ) ) {
                    $product_tags[] = $new_tag['term_id'];
                }
            }

            $request_data['product_tags'] = $product_tags;
        }

        // set product status
        $post_status    = '';
        $pending_review = 'on' === dokan_get_option( 'edited_product_status', 'dokan_selling', 'off' );

        if ( $pending_review ) {
            $post_status = 'pending';
        } elseif ( ! empty( $request_data['post_status'] ) && in_array( $request_data['post_status'], [ 'draft', 'publish' ], true ) ) {
            $post_status = $request_data['post_status'];
        }

        $request_data['post_status'] = $post_status;

        if ( ! empty( $bulk_products ) ) {
            foreach ( $bulk_products as $post_id ) {
                // Get the product and save.
                $product = wc_get_product( $post_id );

                // Make sure it's a product object
                if ( ! is_a( $product, 'WC_Product' ) ) {
                    continue;
                }

                // Make sure it's current vendor's product
                if ( dokan_get_vendor_by_product( $product, true ) !== (int) dokan_get_current_user_id() ) {
                    continue;
                }

                $this->bulk_edit_save( $product, $request_data );
            }

            /**
             * Hook for bulk edit after save single item.
             *
             * @since 3.4.0
             *
             * @param WC_Product $product
             */
            do_action( 'dokan_after_bulk_edit_save', $bulk_products );

            wc_add_notice( __( 'Product successfully updated.', 'dokan' ), 'success' );

            global $wp;
            $redirect_url = home_url(
                add_query_arg(
                    [
                        $_GET,
                    ], $wp->request
                )
            );

            wp_safe_redirect( $redirect_url );
            exit;
        }
    }

    /**
     * Bulk edit saving.
     *
     * @since 3.4.0
     *
     * @param mixed|WC_Product $product WC_Product object.
     *
     * @throws \WC_Data_Exception
     *
     * @return void
     */
    public function bulk_edit_save( $product, $request_data ) {
        $data_store = $product->get_data_store();

        if ( ! empty( $request_data['change_weight'] ) && isset( $request_data['_weight'] ) ) {
            $product->set_weight( wc_clean( $request_data['_weight'] ) );
        }

        if ( ! empty( $request_data['change_dimensions'] ) ) {
            if ( isset( $request_data['_length'] ) ) {
                $product->set_length( wc_clean( $request_data['_length'] ) );
            }
            if ( isset( $request_data['_width'] ) ) {
                $product->set_width( wc_clean( $request_data['_width'] ) );
            }
            if ( isset( $request_data['_height'] ) ) {
                $product->set_height( wc_clean( $request_data['_height'] ) );
            }
        }

        if ( ! empty( $request_data['_tax_status'] ) ) {
            $product->set_tax_status( wc_clean( $request_data['_tax_status'] ) );
        }

        if ( ! empty( $request_data['_tax_class'] ) ) {
            $tax_class = wc_clean( $request_data['_tax_class'] );
            if ( 'standard' === $tax_class ) {
                $tax_class = '';
            }
            $product->set_tax_class( $tax_class );
        }

        if ( ! $product->is_virtual() ) {
            if ( ! empty( $request_data['_shipping_class'] ) ) {
                if ( '_no_shipping_class' === $request_data['_shipping_class'] ) {
                    $product->set_shipping_class_id( 0 );
                } else {
                    $shipping_class_id = $data_store->get_shipping_class_id_by_slug( wc_clean( $request_data['_shipping_class'] ) );
                    $product->set_shipping_class_id( $shipping_class_id );
                }
            }
        }

        if ( ! empty( $request_data['_visibility'] ) ) {
            $product->set_catalog_visibility( wc_clean( $request_data['_visibility'] ) );
        }

        if ( ! empty( $request_data['_featured'] ) ) {
            // phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
            $product->set_featured( $request_data['_featured'] );
            // phpcs:enable WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
        }

        if ( ! empty( $request_data['_sold_individually'] ) ) {
            if ( 'yes' === $request_data['_sold_individually'] ) {
                $product->set_sold_individually( 'yes' );
            } else {
                $product->set_sold_individually( '' );
            }
        }

        // Handle price - remove dates and set to lowest.
        $change_price_product_types    = [ 'simple', 'external' ];
        $can_product_type_change_price = false;

        foreach ( $change_price_product_types as $product_type ) {
            if ( $product->is_type( $product_type ) ) {
                $can_product_type_change_price = true;
                break;
            }
        }

        if ( $can_product_type_change_price ) {
            $regular_price_changed = $this->set_new_price( $product, 'regular', $request_data );
            $sale_price_changed    = $this->set_new_price( $product, 'sale', $request_data );

            if ( $regular_price_changed || $sale_price_changed ) {
                $product->set_date_on_sale_to( '' );
                $product->set_date_on_sale_from( '' );

                if ( $product->get_regular_price() < $product->get_sale_price() ) {
                    $product->set_sale_price( '' );
                }
            }
        }

        // Handle Stock Data.
        $was_managing_stock = $product->get_manage_stock() ? 'yes' : 'no';
        $backorders         = $product->get_backorders();
        $backorders         = ! empty( $request_data['_backorders'] ) ? wc_clean( $request_data['_backorders'] ) : $backorders;

        if ( ! empty( $request_data['_manage_stock'] ) ) {
            $manage_stock = 'yes' === wc_clean( $request_data['_manage_stock'] ) && 'grouped' !== $product->get_type() ? 'yes' : 'no';
        } else {
            $manage_stock = $was_managing_stock;
        }

        $stock_amount = 'yes' === $manage_stock && ! empty( $request_data['change_stock'] ) && isset( $request_data['_stock'] ) ? wc_stock_amount( $request_data['_stock'] ) : $product->get_stock_quantity();

        $product->set_manage_stock( $manage_stock );

        if ( 'external' !== $product->get_type() ) {
            $product->set_backorders( $backorders );
        }

        if ( 'yes' === get_option( 'woocommerce_manage_stock' ) ) {
            $change_stock = absint( $request_data['change_stock'] );
            switch ( $change_stock ) {
                case 2:
                    wc_update_product_stock( $product, $stock_amount, 'increase', true );
                    break;
                case 3:
                    wc_update_product_stock( $product, $stock_amount, 'decrease', true );
                    break;
                default:
                    wc_update_product_stock( $product, $stock_amount, 'set', true );
                    break;
            }
        } else {
            // Reset values if WooCommerce Setting - Manage Stock status is disabled.
            $product->set_stock_quantity( '' );
            $product->set_manage_stock( 'no' );
        }

        // set product status
        if ( ! empty( $request_data['post_status'] ) && $product->get_status() !== 'pending' ) {
            $product->set_status( $request_data['post_status'] );
        }

        if ( ! empty( $request_data['product_tags'] ) ) {
            $tags_to_add                = array_merge( $product->get_tag_ids(), $request_data['product_tags'] );
            $maximum_tags_select_length = apply_filters( 'dokan_product_tags_select_max_length', - 1 );

            // Setting limitation for how many product tags that vendor can input.
            if ( $maximum_tags_select_length !== - 1 && count( $tags_to_add ) > $maximum_tags_select_length ) {
                $tags_to_add = array_slice( $tags_to_add, 0, $maximum_tags_select_length );
            }

            if ( ! empty( $tags_to_add ) ) {
                $product->set_tag_ids( $tags_to_add );
            }
        }

        if ( ! empty( $request_data['product_cat'] ) ) {
            $product_cat = array_merge( $product->get_category_ids(), $request_data['product_cat'] );
            $product->set_category_ids( $product_cat );
        }

        $stock_status = empty( $request_data['_stock_status'] ) ? null : wc_clean( $request_data['_stock_status'] );
        $product      = $this->maybe_update_stock_status( $product, $stock_status );

        /**
         * Hook for bulk edit before save single item.
         *
         * @since 3.4.0
         *
         * @param WC_Product $product
         * @param mixed      $request_data
         */
        do_action( 'dokan_before_bulk_edit_save_single_item', $product, $request_data );

        $product->save();

        /**
         * Hook for bulk edit after save single item.
         *
         * @since 3.4.0
         *
         * @param WC_Product $product
         */
        do_action( 'dokan_after_bulk_edit_save_single_item', $product );
    }

    /**
     * Set new price.
     *
     * @since 3.4.0
     *
     * @param \WC_Product $product      WC Product object.
     * @param string      $price_type   which type of price.
     * @param mixed       $request_data super global post data.
     *
     * @return bool
     */
    private function set_new_price( $product, $price_type, $request_data ) {
        if ( empty( $request_data[ "change_{$price_type}_price" ] ) || ! isset( $request_data[ "_{$price_type}_price" ] ) ) {
            return false;
        }

        $old_price     = empty( $product->{"get_{$price_type}_price"}() ) ? 0 : $product->{"get_{$price_type}_price"}();
        $price_changed = false;

        $change_price  = absint( $request_data[ "change_{$price_type}_price" ] );
        $raw_price     = wc_clean( wp_unslash( $request_data[ "_{$price_type}_price" ] ) );
        $is_percentage = (bool) strstr( $raw_price, '%' );
        $price         = wc_format_decimal( $raw_price );

        switch ( $change_price ) {
            case 1:
                $new_price = $price;
                break;
            case 2:
                if ( $is_percentage ) {
                    $percent   = $price / 100;
                    $new_price = $old_price + ( $old_price * $percent );
                } else {
                    $new_price = $old_price + $price;
                }
                break;
            case 3:
                if ( $is_percentage ) {
                    $percent   = $price / 100;
                    $new_price = max( 0, $old_price - ( $old_price * $percent ) );
                } else {
                    $new_price = max( 0, $old_price - $price );
                }
                break;
            case 4:
                if ( 'sale' !== $price_type ) {
                    break;
                }
                $regular_price = $product->get_regular_price();
                if ( $is_percentage ) {
                    $percent   = $price / 100;
                    $new_price = max( 0, $regular_price - ( NumberUtil::round( $regular_price * $percent, wc_get_price_decimals() ) ) );
                } else {
                    $new_price = max( 0, $regular_price - $price );
                }
                break;

            default:
                break;
        }

        if ( isset( $new_price ) && $new_price !== $old_price ) {
            $price_changed = true;
            $new_price     = NumberUtil::round( $new_price, wc_get_price_decimals() );
            $product->{"set_{$price_type}_price"}( $new_price );
        }

        return $price_changed;
    }

    /**
     * Apply product type constraints to stock status.
     *
     * @since 3.4.0
     *
     * @param \WC_Product $product      The product whose stock status will be adjusted.
     * @param string|null $stock_status The stock status to use for adjustment, or null if no new stock status has been supplied in the request.
     *
     * @return \WC_Product The supplied product, or the synced product if it was a variable product.
     */
    private function maybe_update_stock_status( $product, $stock_status ) {
        if ( $product->is_type( 'external' ) ) {
            // External products are always in stock.
            $product->set_stock_status( 'instock' );
        } elseif ( isset( $stock_status ) ) {
            if ( $product->is_type( 'variable' ) && ! $product->get_manage_stock() ) {
                // Stock status is determined by children.
                foreach ( $product->get_children() as $child_id ) {
                    $child = wc_get_product( $child_id );
                    if ( ! $product->get_manage_stock() ) {
                        $child->set_stock_status( $stock_status );
                        $child->save();
                    }
                }
                $product = WC_Product_Variable::sync( $product, false );
            } else {
                $product->set_stock_status( $stock_status );
            }
        }

        return $product;
    }

}
