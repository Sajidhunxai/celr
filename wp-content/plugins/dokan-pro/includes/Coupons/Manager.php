<?php

namespace WeDevs\DokanPro\Coupons;

use WP_Query;
use WC_Coupon;
use WeDevs\Dokan\Cache;
use WeDevs\Dokan\Exceptions\DokanException;

/**
* Coupon Manager class
*
* @since 3.0.0
*/
class Manager {

    /**
     * Get all coupons
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function all( $args = [] ) {
        $default = [
            'seller_id' => dokan_get_current_user_id(),
            'paged'     => 1,
            'limit'     => 10,
            'paginate'  => true,
        ];

        $args = wp_parse_args( $args, $default );

        $query_args = apply_filters( 'dokan_get_vendor_coupons_args', [
            'post_type'              => 'shop_coupon',
            'post_status'            => [ 'publish' ],
            'posts_per_page'         => $args['limit'],
            'author'                 => $args['seller_id'],
            'paged'                  => $args['paged'],
            'fields'                 => 'ids',
            'cache_results'          => false,
            'update_post_meta_cache' => false,
            'update_post_term_cache' => false,
        ], $args );

        $cache_group  = "seller_coupons_{$args['seller_id']}";
        $cache_key    = 'coupons_' . md5( wp_json_encode( $args ) );
        $coupon_query = Cache::get( $cache_key, $cache_group );

        if ( false === $coupon_query ) {
            $coupon_query = new WP_Query( $query_args );

            Cache::set( $cache_key, $coupon_query, $cache_group );
        }

        $coupons = $coupon_query->get_posts();

        $coupons = array_map(
            function( $coupon_id ) {
                return $this->get( $coupon_id );
            }, $coupons
        );

        if ( empty( $args['paginate'] ) ) {
            return $coupons;
        } else {
            return (object) array(
                'coupons'  => $coupons,
                'total'    => intval( $coupon_query->found_posts ),
                'per_page' => $args['limit'],
            );
        }
    }

    /**
     * Get a coupon object
     *
     * @since 3.0.0
     *
     * @return void
     */
    public function get( $id = 0 ) {
        return new WC_Coupon( $id );
    }

    /**
     * Delete a coupon
     *
     * @since 3.0.0
     *
     * @return Void
     */
    public function delete( $id, $force = true ) {
        if ( empty( $id ) ) {
            throw new DokanException( 'dokan_coupon_id_not_found', __( 'Coupon not found', 'dokan' ), 401 );
        }

        $coupon = $this->get( $id );

        if ( $coupon ) {
            $coupon->delete( [ 'force_delete' => $force ] );
        }

        return $coupon;
    }

    /**
     * Admin coupon is valid for current cart items
     *
     * @since 3.4.0
     *
     * @param object $coupon
     * @param array  $vendors
     * @param array  $products
     *
     * @return boolean
     */
    public function is_admin_coupon_valid( $coupon, $vendors, $products, $coupon_meta_data = array() ) {
        if ( empty( $coupon ) ) {
            return false;
        }

        $coupon_data          = ! empty( $coupon_meta_data ) ? $coupon_meta_data : dokan_get_admin_coupon_meta( $coupon );
        $enabled_all_vendor   = isset( $coupon_data['admin_coupons_enabled_for_vendor'] ) ? $coupon_data['admin_coupons_enabled_for_vendor'] : '';
        $vendors_ids          = isset( $coupon_data['coupons_vendors_ids'] ) ? $coupon_data['coupons_vendors_ids'] : [];
        $exclude_vendors      = isset( $coupon_data['coupons_exclude_vendors_ids'] ) ? $coupon_data['coupons_exclude_vendors_ids'] : [];
        $product_ids          = isset( $coupon_data['product_ids'] ) ? $coupon_data['product_ids'] : [];
        $excluded_product_ids = isset( $coupon_data['excluded_product_ids'] ) ? $coupon_data['excluded_product_ids'] : [];
        $total_products       = count( $products );
        $total_vendors        = count( $vendors );

        if ( 'yes' === $enabled_all_vendor && empty( $exclude_vendors ) && empty( $product_ids ) && empty( $excluded_product_ids ) ) {
            return true;
        }

        // Check all product IDs excluded from the discount
        if (
            $total_products &&
            count( $excluded_product_ids ) &&
            $total_products === count( array_intersect( $products, $excluded_product_ids ) )
        ) {
            return false;
        }

        // Check any one product ID included on the discount
        if (
            $total_products &&
            count( $product_ids ) &&
            count( array_intersect( $products, $product_ids ) ) > 0
        ) {
            return true;
        }

        // Check all product IDs not excluded from the discount
        if (
            'yes' === $enabled_all_vendor &&
            empty( $product_ids ) &&
            $total_vendors &&
            count( $exclude_vendors ) &&
            $total_vendors !== count( array_intersect( $vendors, $exclude_vendors ) )
        ) {
            return true;
        }

        // Check any one vendor ID included on the discount
        if (
            'no' === $enabled_all_vendor &&
            empty( $product_ids ) &&
            $total_vendors &&
            count( $vendors_ids ) &&
            count( array_intersect( $vendors, $vendors_ids ) ) > 0
        ) {
            return true;
        }

        return false;
    }

    /**
     * Get earning use admin coupon for vendor
     *
     * @since 3.4.0
     *
     * @param obj   $order
     * @param obj   $item
     * @param obj   $context[admin|seller]
     * @param obj   $product
     * @param int   $vendor_id
     * @param float $refund
     *
     * @return float $earning
     */
    public function get_earning_by_admin_coupon( $order, $item, $context, $product, $vendor_id, $refund ) {
        $used_coupons = $order->get_items( 'coupon' );

        if ( empty( $used_coupons ) ) {
            return false;
        }

        $get_total          = $item->get_total();
        $get_subtotal       = $item->get_subtotal();
        $get_quantity       = $item->get_quantity();
        $product_discount   = $this->get_product_discount( $product, $get_total, $get_quantity );
        $order_discount     = $this->get_order_discount( $order, $get_total );
        $commission_product = $get_subtotal - $product_discount - $order_discount;
        $product_price      = $refund ? $commission_product - $refund : $commission_product;
        $real_product_price = $refund ? $get_subtotal - $refund : $get_subtotal;
        $get_items_count    = count( $order->get_items() );
        $vendor_earning     = dokan()->commission->calculate_commission( $product->get_id(), $product_price, $vendor_id );
        $admin_earning      = $product_price - $vendor_earning;
        $current_data       = [
            'real_product_price'    => $real_product_price,
            'current_product_price' => $real_product_price,
            'admin_earning'         => $admin_earning,
            'vendor_earning'        => $vendor_earning,
            'coupon_applied'        => 1,
        ];

        foreach ( $used_coupons as $item_id => $coupon ) {
            $coupon_meta = current( $coupon->get_meta_data() );

            if ( ! isset( $coupon_meta->get_data()['value'] ) ) {
                continue;
            }

            $coupon_meta      = dokan_get_coupon_metadata_from_order( $coupon_meta->get_data()['value'] );
            $price_for_coupon = 'yes' === get_option( 'woocommerce_calc_discounts_sequentially', 'no' ) ? $current_data['current_product_price'] : $current_data['real_product_price'];
            $discount_price   = $this->get_coupon_amount( $coupon_meta, $price_for_coupon, $get_quantity, $get_items_count );

            if (
                isset( $coupon_meta['coupon_commissions_type'] ) &&
                'default' !== $coupon_meta['coupon_commissions_type'] &&
                $this->is_admin_coupon_valid( $coupon, [ $vendor_id ], [ $product->get_id() ], $coupon_meta )
            ) {
                $current_data = $this->get_earning_for_admin_coupon( $current_data, $coupon_meta, $discount_price );
            } else {
                $current_data = $this->get_earning_for_vendor_coupon( $current_data, $coupon_meta, $discount_price );
            }
        }

        return 'seller' === $context ? $current_data['vendor_earning'] : $current_data['admin_earning'];
    }

    /**
     * Get earning by product if have created admin coupon for vendors
     *
     * @since 3.4.0
     *
     * @param array $current_data
     * @param array $coupon_meta
     * @param float $discount_price
     *
     * @return array
     */
    public function get_earning_for_admin_coupon( $current_data, $coupon_meta, $discount_price ) {
        /**
         * Commissions types: from_vendor, from_admin, shared_coupon
         * Here we checking 3 types of coupon price deduct from vendors or admin or shared.
         */
        switch ( $coupon_meta['coupon_commissions_type'] ) {
            case 'from_vendor':
                return $this->get_earning_for_vendor_only_coupon( $current_data, $coupon_meta, $discount_price );
            case 'from_admin':
                return $this->get_earning_for_admin_only_coupon( $current_data, $coupon_meta, $discount_price );
            case 'shared_coupon':
                return $this->get_earning_for_shared_coupon( $current_data, $coupon_meta, $discount_price );
            default:
                return $current_data;
        }
    }

    /**
     * Get earning if only coupon amount deduct from vendor.
     *
     * If coupon price deduct from vendor then full coupon price deducts from vendor earning.
     *
     * @since 3.4.0
     *
     * @param array $current_data
     * @param array $coupon_meta
     * @param float $discount_price
     *
     * @return array
     */
    public function get_earning_for_vendor_only_coupon( $current_data, $coupon_meta, $discount_price ) {
        if ( $current_data['vendor_earning'] < $discount_price ) {
            $vendor_earning = 0;
        } else {
            $vendor_earning = $current_data['vendor_earning'] - $discount_price;
        }

        $current_data['current_product_price'] -= $discount_price;
        $current_data['vendor_earning']         = $vendor_earning;
        $current_data['coupon_applied']        += 1;

        return apply_filters( 'dokan_get_earning_vendor_only_shared_coupon', $current_data, $coupon_meta, $discount_price );
    }

    /**
     * Get earning if only coupon amount deduct from admin.
     *
     * If coupon price deduct from admin then full coupon price deducts from admin earning,
     * and if coupon price greater than admin earning than additional amount deducts from vendor earning.
     *
     * @since 3.4.0
     *
     * @param array $current_data
     * @param array $coupon_meta
     * @param float $discount_price
     *
     * @return array
     */
    public function get_earning_for_admin_only_coupon( $current_data, $coupon_meta, $discount_price ) {
        $admin_earning  = $current_data['admin_earning'];
        $vendor_earning = $current_data['vendor_earning'];

        if ( $admin_earning < $discount_price ) {
            $vendor_earning -= abs( $admin_earning - $discount_price );
            $vendor_earning  = $vendor_earning < 0 ? 0 : $vendor_earning;
            $admin_earning   = 0;
        } else {
            $admin_earning -= $discount_price;
        }

        $current_data['admin_earning']          = $admin_earning;
        $current_data['vendor_earning']         = $vendor_earning;
        $current_data['current_product_price'] -= $discount_price;
        $current_data['coupon_applied']        += 1;

        return apply_filters( 'dokan_get_earning_admin_only_shared_coupon', $current_data, $coupon_meta, $discount_price );
    }

    /**
     * Get earning when coupon amount shared admin and vendor boths.
     *
     * If coupon price shared then coupon price minus from admin earning and vendor earning as per
     * shared coupon type. Here 2 types shared flat and percentage. Flat amount minus from admin
     * earning and other amount minus from vendor earning. Here also if admin earning greater than
     * coupon amount then additional amount minus from vendor earning.
     *
     * @since 3.4.0
     *
     * @param array $current_data
     * @param array $coupon_meta
     * @param float $discount_price
     *
     * @return array
     */
    public function get_earning_for_shared_coupon( $current_data, $coupon_meta, $discount_price ) {
        $shared_coupon_amount  = isset( $coupon_meta['admin_shared_coupon_amount'] ) ? $coupon_meta['admin_shared_coupon_amount'] : 0;
        $shared_coupon_type    = isset( $coupon_meta['admin_shared_coupon_type'] ) ? $coupon_meta['admin_shared_coupon_type'] : '';
        $admin_earning         = $current_data['admin_earning'];
        $seller_discount_price = 0;
        $admin_discount_price  = 0;

        if ( 'flat' === $shared_coupon_type ) {
            $seller_discount_price = $discount_price - (float) $shared_coupon_amount;
            $admin_discount_price  = $shared_coupon_amount;
        } elseif ( 'percentage' === $shared_coupon_type ) {
            $shared_percentage     = $discount_price * (int) $shared_coupon_amount / 100;
            $seller_discount_price = $discount_price - (float) $shared_percentage;
            $admin_discount_price  = $shared_percentage;
        }

        $extended_admin_amount = $admin_earning < $admin_discount_price ? $admin_discount_price - $admin_earning : 0;

        if ( $seller_discount_price === 0 || $seller_discount_price < 0 ) {
            $admin_earning  = 0;
            $vendor_earning = $current_data['vendor_earning'];
        } else {
            $admin_earning  = $admin_earning - $admin_discount_price;
            $vendor_earning = $current_data['vendor_earning'] - $seller_discount_price - $extended_admin_amount;
        }

        $current_data['admin_earning']          = $admin_earning;
        $current_data['vendor_earning']         = $vendor_earning;
        $current_data['current_product_price'] -= $discount_price;
        $current_data['coupon_applied']        += 1;

        return apply_filters( 'dokan_get_earning_for_shared_coupon', $current_data, $coupon_meta, $discount_price );
    }

    /**
     * Get earning by product if have created vendor coupon default option
     *
     * @since 3.4.0
     *
     * @param array $current_data
     * @param array $coupon_meta
     * @param float $discount_price
     *
     * @return float
     */
    public function get_earning_for_vendor_coupon( $current_data, $coupon_meta, $discount_price ) {
        $earning               = $current_data['vendor_earning'];
        $admin_earning         = $current_data['admin_earning'];
        $admin_discount_price  = $admin_earning * $discount_price / $current_data['current_product_price'];
        $seller_discount_price = $discount_price - (float) $admin_discount_price;

        $extended_admin_amount = $admin_earning < $admin_discount_price ? $admin_discount_price - $admin_earning : 0;

        if ( $seller_discount_price === 0 || $seller_discount_price < 0 ) {
            $admin_earning  = 0;
            $vendor_earning = $earning;
        } else {
            $admin_earning  = $admin_earning - $admin_discount_price;
            $vendor_earning = $earning - $seller_discount_price - $extended_admin_amount;
        }

        $current_data['admin_earning']          = $admin_earning;
        $current_data['vendor_earning']         = $vendor_earning;
        $current_data['current_product_price'] -= $discount_price;
        $current_data['coupon_applied']        += 1;

        return apply_filters( 'dokan_get_earning_by_product_if_have_vendor_coupon', $current_data, $coupon_meta, $discount_price );
    }

    /**
     * Apply a discount to all items using a coupon.
     *
     * @since 3.4.0
     *
     * @param array $coupon_meta
     * @param float $product_price
     */
    public function get_coupon_amount( $coupon_meta, $product_price, $get_quantity, $get_items_count ) {
        // Apply coupon type and get amount
        switch ( $coupon_meta['discount_type'] ) {
            case 'percent':
                return $this->apply_coupon_percent( $coupon_meta, $product_price );
            case 'fixed_product':
                return $this->apply_coupon_fixed_product( $coupon_meta, $get_quantity );
            case 'fixed_cart':
                return $this->apply_coupon_fixed_cart( $coupon_meta, $get_items_count );
            default:
                return $this->apply_coupon_custom( $coupon_meta, $product_price, $get_quantity );
        }
    }

    /**
     * Apply percent discount to items and return an array of discounts granted.
     *
     * @since 3.4.0
     *
     * @param array $coupon_meta
     * @param float $get_subtotal
     */
    protected function apply_coupon_percent( $coupon_meta, $get_subtotal ) {
        return wc_round_discount( $get_subtotal * ( $coupon_meta['amount'] / 100 ), 0 );
    }

    /**
     * Apply fixed product discount to item.
     *
     * @since 3.4.0
     *
     * @param array $coupon_meta
     * @param int   $quantity
     */
    protected function apply_coupon_fixed_product( $coupon_meta, $quantity ) {
        return wc_round_discount( $coupon_meta['amount'] * $quantity, 0 );
    }

    /**
     * Apply fixed cart discount to item.
     *
     * @since 3.4.0
     *
     * @param array $coupon_meta
     * @param int   $get_items_count
     */
    protected function apply_coupon_fixed_cart( $coupon_meta, $get_items_count ) {
        return wc_round_discount( $coupon_meta['amount'] / $get_items_count, 0 );
    }

    /**
     * Apply custom coupon discount to item.
     *
     * @since 3.4.0
     *
     * @param array $coupon_meta
     * @param float $get_subtotal
     */
    protected function apply_coupon_custom( $coupon_meta, $get_subtotal ) {
        return wc_round_discount( $get_subtotal * ( $coupon_meta['amount'] / 100 ), 0 );
    }

    /**
     * Get product discount by item
     *
     * @since 3.4.0
     *
     * @param object $product
     * @param float  $get_total
     * @param int    $get_quantity
     *
     * @return float
     */
    public function get_product_discount( $product, $get_total, $get_quantity ) {
        $product_discount = $product->get_meta( '_is_lot_discount', true );
        $discount_total   = 0;

        if ( 'yes' !== $product_discount  ) {
            return $discount_total;
        }

        $lot_discount_percentage = (float) $product->get_meta( '_lot_discount_amount', true );
        $lot_discount_quantity   = absint( $product->get_meta( '_lot_discount_quantity', true ) );

        if ( $get_quantity >= $lot_discount_quantity ) {
            $discount_total += ( $get_total * $lot_discount_percentage / 100 );
        }

        return $discount_total;
    }

    /**
     * Get order discount by item
     *
     * @since 3.4.0
     *
     * @param object $order
     * @param float  $get_total
     *
     * @return float
     */
    public function get_order_discount( $order, $get_total ) {
        $is_min_order_discount = $order->get_meta( 'dokan_is_min_order_discount', true );
        $discount_total        = 0;

        if ( 'yes' !== $is_min_order_discount ) {
            return $discount_total;
        }

        $min_order_discount_percentage = $order->get_meta( 'dokan_setting_order_percentage' );
        $discount_total                = ( $get_total * $min_order_discount_percentage / 100 );

        return $discount_total;
    }
}
