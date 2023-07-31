<?php
defined( 'ABSPATH' ) || exit;

if ( ! class_exists( 'Woosv_Public' ) ) {
	
	class Woosv_Public {
		protected static $instance = null;

		public static function instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		public function product_query( $args ) {
			$enable = get_option( 'woosv_enable' );

			$args->set( 'woosv_filter', 'yes' );
			$args->set( 'post_type', [ 'product', 'product_variation' ] );

			$meta_query = (array) $args->get( 'meta_query' );

			if ( empty( $enable ) ) {
				$meta_query[] = [
					'relation' => 'OR',
					[
						'key'     => '_variation_description',
						'compare' => 'NOT EXISTS'
					],
					[
						'key'     => 'woosv_enable',
						'value'   => 'enable',
						'compare' => '=='
					],
					[
						'key'     => 'woosv_enable',
						'value'   => 'reverse',
						'compare' => '=='
					],
				];
			} else {
				$meta_query[] = [
					'relation' => 'OR',
					[
						'key'     => '_variation_description',
						'compare' => 'NOT EXISTS'
					],
					[
						'key'     => 'woosv_enable',
						'compare' => 'NOT EXISTS'
					],
					[
						'key'     => 'woosv_enable',
						'value'   => 'default',
						'compare' => '=='
					],
					[
						'key'     => 'woosv_enable',
						'value'   => 'enable',
						'compare' => '=='
					],
				];
			}

			$args->set( 'meta_query', $meta_query );
		}

		public function posts_clauses( $clauses, $query ) {
			global $wpdb;
			$enable              = get_option( 'woosv_enable' ) === 'yes';
			$hide_parent         = get_option( 'woosv_hide_parent' ) === 'yes';
			$hide_parent_exclude = get_option( 'woosv_hide_parent_exclude' );

			if ( isset( $query->query_vars['woosv_filter'] ) && ( $query->query_vars['woosv_filter'] === 'yes' ) ) {
				if ( $hide_parent ) {
					$clauses['where'] .= " AND  0 = (select count(*) as totalpart from {$wpdb->posts} as oc_posttb where oc_posttb.post_parent = {$wpdb->posts}.ID and oc_posttb.post_type = 'product_variation' ";

					if ( ! empty( $hide_parent_exclude ) ) {
						$clauses['where'] .= "  AND {$wpdb->posts}.ID NOT IN ( " . $hide_parent_exclude . " ) ";
					}

					$clauses['where'] .= " ) ";
				}

				$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} as oc_posttba ON ({$wpdb->posts}.post_parent = oc_posttba.post_id AND oc_posttba.meta_key = 'woosv_enable' )";

				if ( $enable ) {
					$clauses['where'] .= " AND ( oc_posttba.meta_value IS NULL OR oc_posttba.meta_value = 'default' OR oc_posttba.meta_value = 'enable' ) ";
				} else {
					$clauses['where'] .= " AND ( oc_posttba.meta_value IS NULL OR oc_posttba.meta_value = 'enable' OR oc_posttba.meta_value = 'reverse' ) ";
				}
			}

			return $clauses;
		}

		function variation_get_name( $name, $product ) {
			if ( ( $custom_name = get_post_meta( $product->get_id(), 'woosv_name', true ) ) && ! empty( $custom_name ) ) {
				return $custom_name;
			}

			return $name;
		}

		function the_title( $post_title, $post_id ) {
			if ( ( $custom_name = get_post_meta( $post_id, 'woosv_name', true ) ) && ! empty( $custom_name ) ) {
				

				return $custom_name;
			}

			return $post_title;
		}
	}

	
}
