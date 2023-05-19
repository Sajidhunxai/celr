<?php

namespace WeDevs\DokanPro\Modules\SellerBadge;

use WeDevs\Dokan\Vendor\Vendor;
use WeDevs\DokanPro\Modules\SellerBadge\Models\BadgeEvent;
use WP_Error;

/**
 * Seller Badge Helper
 */
class Helper {

	/**
	 * Get dokan seller badge events.
	 *
	 * @since 3.7.14
	 *
	 * @param string $event
	 * @param bool   $get_event_keys
	 *
	 * @return BadgeEvent|WP_Error|array
	 */
	public static function get_dokan_seller_badge_events( $event = '', $get_event_keys = false ) {
		/**
		 * ! Do not change the key.
		 * Do not add filter to events type. It will break the compatibility.
		 * If you need to add another event, just add it to the array.
		 * $event_type is the key of the array.
		 * $event_type_key is the key of the $event_type array.
		 */
		$events = [
			'product_published'     => [
				'title'               => __( 'Products Published', 'dokan' ),
				'description'         => __( 'When a vendor publishes a certain number of products', 'dokan' ),
				'condition_text'      => [
					'prefix' => __( 'When vendor publishes more than', 'dokan' ),
					'suffix' => __( 'products', 'dokan' ),
					'type'   => 'count',
				],
				'responsible_class'   => 'WeDevs\DokanPro\Modules\SellerBadge\Events\ProductPublished',
				'responsible_hooks'   => [
					'save_post_product',
				],
				'hover_text'          => '{badge_name}: Added {badge_data} products to their store',
				'group'               => [
					'key'   => 'product',
					'title' => __( 'Product Related Badges', 'dokan' ),
				],
				'has_multiple_levels' => true,
				'badge_logo'          => 'vendor-first-product.svg',
				'input_group_icon'    => [
					'condition' => 'icon-compare',
					'data'      => 'icon-count',
				],
			],
			'number_of_items_sold'  => [
				'title'               => __( 'Number of Items Sold', 'dokan' ),
				'description'         => __( 'When a vendor sells a certain number of items', 'dokan' ),
				'condition_text'      => [
					'prefix' => __( 'When a vendor sells more than', 'dokan' ),
					'suffix' => __( 'items', 'dokan' ),
					'type'   => 'count',
				],
				'responsible_class'   => 'WeDevs\DokanPro\Modules\SellerBadge\Events\NumberOfItemSold',
				'responsible_hooks'   => [
					'woocommerce_order_status_changed',
				],
				'hover_text'          => '{badge_name}: Successfully sold {badge_data} items',
				'group'               => [
					'key'   => 'product',
					'title' => __( 'Product', 'dokan' ),
				],
				'has_multiple_levels' => true,
				'badge_logo'          => 'number-of-item-sold.svg',
				'input_group_icon'    => [
					'condition' => 'icon-compare',
					'data'      => 'icon-count',
				],
			],
			'featured_products'     => [
				'title'               => __( 'Featured Products', 'dokan' ),
				'description'         => __( 'When a vendor\'s product gets featured in the marketplace', 'dokan' ),
				'condition_text'      => [
					'prefix' => __( 'When a vendor\'s product gets featured in the marketplace', 'dokan' ),
					'suffix' => __( '', 'dokan' ),
				],
				'responsible_class'   => 'WeDevs\DokanPro\Modules\SellerBadge\Events\FeaturedProducts',
				'responsible_hooks'   => [
					'save_post_product',
				],
				'hover_text'          => '{badge_name}: Had their product featured',
				'group'               => [
					'key'   => 'product',
					'title' => __( 'Product', 'dokan' ),
					'type'  => '',
				],
				'has_multiple_levels' => false,
				'badge_logo'          => 'featured-product-count.svg',
				'input_group_icon'    => [
					'condition' => 'icon-timing',
					'data'      => 'icon-count',
				],
			],
			'trending_product'      => [
				'title'               => __( 'Trending Product', 'dokan' ),
				'description'         => __( 'When a vendor has a trending product', 'dokan' ),
				'condition_text'      => [
					'prefix' => __( 'When a vendor has a trending product', 'dokan' ),
					'suffix' => __( '', 'dokan' ),
				],
				'responsible_class'   => 'WeDevs\DokanPro\Modules\SellerBadge\Events\TrendingProduct',
				'responsible_hooks'   => [
					'dokan_seller_badge_daily_at_midnight_cron',
				],
				'hover_text'          => '{badge_name}: Had a product that was trending',
				'group'               => [
					'key'   => 'product',
					'title' => __( 'Product', 'dokan' ),
					'type'  => '',
				],
				'has_multiple_levels' => false,
				'badge_logo'          => 'trending-product.svg',
				'input_group_icon'    => [
					'condition' => 'icon-trending',
					'data'      => 'icon-count',
				],
			],
			'featured_seller'       => [
				'title'               => __( 'Featured Seller', 'dokan' ),
				'description'         => __( 'When a vendor gets featured', 'dokan' ),
				'condition_text'      => [
					'prefix' => __( 'When vendor gets featured', 'dokan' ),
					'suffix' => __( '', 'dokan' ),
				],
				'responsible_class'   => 'WeDevs\DokanPro\Modules\SellerBadge\Events\FeaturedSeller',
				'responsible_hooks'   => [
					'dokan_new_vendor',
					'dokan_update_vendor',
				],
				'hover_text'          => '{badge_name}: Had their store featured',
				'group'               => [
					'key'   => 'vendor',
					'title' => __( 'Seller Related Badges', 'dokan' ),
					'type'  => '',
				],
				'has_multiple_levels' => false,
				'badge_logo'          => 'vendor-featured.svg',
				'input_group_icon'    => [
					'condition' => 'icon-compare',
					'data'      => 'icon-count',
				],
			],
			'exclusive_to_platform' => [
				'title'               => __( 'Exclusive to Platform', 'dokan' ),
				'description'         => __( 'When a vendor exclusively sells products only on this platform', 'dokan' ),
				'condition_text'      => [
					'prefix' => __( 'When vendor sells product only on this platform', 'dokan' ),
					'suffix' => __( '', 'dokan' ),
				],
				'responsible_class'   => 'WeDevs\DokanPro\Modules\SellerBadge\Events\ExclusiveToPlatform',
				'responsible_hooks'   => [
					'dokan_new_vendor',
					'dokan_update_vendor',
				],
				'hover_text'          => '{badge_name}: Sells exclusively only on this platform',
				'group'               => [
					'key'   => 'vendor',
					'title' => __( 'Different Type of Sellers', 'dokan' ),
					'type'  => '',
				],
				'has_multiple_levels' => false,
				'badge_logo'          => 'sale-only-here.svg',
				'input_group_icon'    => [
					'condition' => 'icon-compare',
					'data'      => 'icon-count',
				],
			],
			'verified_seller'       => [
				'title'               => __( 'Verified Seller', 'dokan' ),
				'description'         => __( 'When a vendor has verified their identity', 'dokan' ),
				'condition_text'      => [
					'prefix' => __( 'When vendor completes their', 'dokan' ),
					'suffix' => __( '', 'dokan' ),
					'type'   => '',
				],
				'responsible_class'   => 'WeDevs\DokanPro\Modules\SellerBadge\Events\VerifiedSeller',
				'responsible_hooks'   => [
					'dokan_verification_status_change',
				],
				'hover_text'          => '{badge_name}: Had verified their identity',
				'group'               => [
					'key'   => 'vendor',
					'title' => __( 'Different Type of Sellers', 'dokan' ),
					'type'  => '',
				],
				'has_multiple_levels' => true,
				'badge_logo'          => 'verified-seller.svg',
				'input_group_icon'    => [
					'condition' => 'icon-document',
					'data'      => 'icon-count',
				],
			],
			'years_active'          => [
				'title'               => __( 'Years Active', 'dokan' ),
				'description'         => __( 'When a vendor actively sells in the marketplace for a certain number of years', 'dokan' ),
				'condition_text'      => [
					'prefix' => __( 'When a vendor sells in this platform for more than a year', 'dokan' ),
					'suffix' => '',
				],
				'responsible_class'   => 'WeDevs\DokanPro\Modules\SellerBadge\Events\YearsActive',
				'responsible_hooks'   => [
					'dokan_seller_badge_daily_at_midnight_cron', // Daily CRON
				],
				'hover_text'          => '{badge_name}: Selling products on this platform for {badge_data} years',
				'group'               => [
					'key'   => 'vendor',
					'title' => __( 'Different Type of Sellers', 'dokan' ),
					'type'  => 'count',
				],
				'has_multiple_levels' => true,
				'badge_logo'          => 'number-of-years.svg',
				'input_group_icon'    => [
					'condition' => 'icon-timing',
					'data'      => 'icon-count',
				],
			],
			'number_of_orders'      => [
				'title'               => __( 'Number of Orders', 'dokan' ),
				'description'         => __( 'When a vendor receives a certain number of orders', 'dokan' ),
				'condition_text'      => [
					'prefix' => __( 'When vendor receives more than', 'dokan' ),
					'suffix' => __( 'orders', 'dokan' ),
					'type'   => 'count',
				],
				'responsible_class'   => 'WeDevs\DokanPro\Modules\SellerBadge\Events\NumberOfOrders',
				'responsible_hooks'   => [
					'woocommerce_order_status_changed',
				],
				'hover_text'          => '{badge_name}: Successfully completed {badge_data} orders',
				'group'               => [
					'key'   => 'order',
					'title' => __( 'Order Related Badges', 'dokan' ),
				],
				'has_multiple_levels' => true,
				'badge_logo'          => 'number-of-orders.svg',
				'input_group_icon'    => [
					'condition' => 'icon-compare',
					'data'      => 'icon-order',
				],
			],
			'sale_amount'           => [
				'title'               => __( 'Sale Amount', 'dokan' ),
				'description'         => __( 'When Sale amount is a certain amount', 'dokan' ),
				'condition_text'      => [
					'prefix' => __( 'When Sales amount is more than', 'dokan' ),
					'suffix' => __( '', 'dokan' ),
					'type'   => 'price',
				],
				'responsible_class'   => 'WeDevs\DokanPro\Modules\SellerBadge\Events\SalesAmount',
				'responsible_hooks'   => [
					'woocommerce_order_status_changed',
				],
				'hover_text'          => '{badge_name}: Sold more than {badge_data_price}',
				'group'               => [
					'key'   => 'amount',
					'title' => __( 'Sale Amount Related Badges', 'dokan' ),
				],
				'has_multiple_levels' => true,
				'badge_logo'          => 'sales-amount.svg',
				'input_group_icon'    => [
					'condition' => 'icon-compare',
					'data'      => 'icon-price',
				],
			],
			'customer_review'       => [
				'title'               => __( 'Customer Review', 'dokan' ),
				'description'         => __( 'When a vendor gets a certain number of reviews ', 'dokan' ),
				'condition_text'      => [
					'prefix' => __( 'When vendor gets more than', 'dokan' ),
					'suffix' => __( 'five star reviews', 'dokan' ),
				],
				'responsible_class'   => 'WeDevs\DokanPro\Modules\SellerBadge\Events\CustomerReview',
				'responsible_hooks'   => [
					'comment_post',
					'wp_set_comment_status',
				],
				'hover_text'          => '{badge_name}: Has received {badge_data} five star reviews',
				'group'               => [
					'key'   => 'customer_basis',
					'title' => __( 'Customer Related Badges', 'dokan' ),
					'type'  => 'count',
				],
				'has_multiple_levels' => true,
				'badge_logo'          => 'customer-review.svg',
				'input_group_icon'    => [
					'condition' => 'icon-compare',
					'data'      => 'icon-review',
				],
			],
			'store_support_count'   => [
				'title'               => __( 'Store Support Count', 'dokan' ),
				'description'         => __( 'When a vendor gives a certain number of customer support', 'dokan' ),
				'condition_text'      => [
					'prefix' => __( 'When vendor provides more than', 'dokan' ),
					'suffix' => __( 'store support', 'dokan' ),
				],
				'responsible_class'   => 'WeDevs\DokanPro\Modules\SellerBadge\Events\StoreSupportCount',
				'responsible_hooks'   => [
					'save_post_dokan_store_support',
				],
				'hover_text'          => '{badge_name}: Has provided {badge_data} store support',
				'group'               => [
					'key'   => 'customer_basis',
					'title' => __( 'Customer Basis', 'dokan' ),
					'type'  => 'count',
				],
				'has_multiple_levels' => true,
				'badge_logo'          => 'store-support-count.svg',
				'input_group_icon'    => [
					'condition' => 'icon-compare',
					'data'      => 'icon-support',
				],
			],
		];

		if ( true === $get_event_keys ) {
			return array_keys( $events );
		}

		if ( array_key_exists( $event, $events ) ) {
			return new BadgeEvent( $event, $events[ $event ] );
		}

		return new WP_Error(
			'invalid-badge-event',
			sprintf(
				__( 'No event data was found with given event id: %s', 'dokan' ),
				$event
			)
		);
	}

	/**
	 * Get formatted event badge status
	 *
	 * @since 3.7.14
	 *
	 * @param $status
	 *
	 * @return array|string
	 */
	public static function get_formatted_event_status( $status = '' ) {
		$event_status = [
			'published' => __( 'Published', 'dokan' ),
			'draft'     => __( 'Draft', 'dokan' ),
		];
		if ( ! empty( $status ) && array_key_exists( $status, $event_status ) ) {
			return $event_status[ $status ];
		}

		return $event_status;
	}

	/**
	 * @return string
	 */
	public static function get_week_start_day() {
		$week_day_start_at = absint( get_option( 'start_of_week', 0 ) );
		$days              = [ 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday' ];

		return $days[ $week_day_start_at ];
	}

	/**
	 * Get condition data for a given event type.
	 *
	 * @since 3.7.14
	 *
	 * @param $condition
	 *
	 * @return string
	 */
	public static function get_condition_data( $condition ) {
		switch ( $condition ) {
			case '>':
				$condition = __( 'More than', 'dokan' );
				break;
			case '<':
				$condition = __( 'Less than', 'dokan' );
				break;
			case '=':
				$condition = __( 'Equal to', 'dokan' );
				break;
			default:
				$condition = '';
				break;
		}

		return $condition;
	}

	/**
	 * Get no of years a vendor has been active on marketplace
	 *
	 * @since 3.7.14
	 *
	 * @param int $vendor_id
	 *
	 * @return int
	 */
	public static function get_vendor_year_count( $vendor_id ) {
		/**
		 * @var Vendor $vendor
		 */
		$vendor = dokan()->vendor->get( $vendor_id );
		if ( ! $vendor->get_id() ) {
			return 0;
		}

		$now           = dokan_current_datetime();
		$register_date = $now->modify( $vendor->get_register_date() );
		if ( false === $register_date ) {
			return 0;
		}

		$diff = $now->diff( $register_date );
		if ( empty( $diff->y ) ) {
			return 0;
		}

		return $diff->y;
	}
}
