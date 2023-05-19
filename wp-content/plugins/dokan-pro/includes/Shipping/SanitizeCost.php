<?php

namespace WeDevs\DokanPro\Shipping;

/**
 * This class holds some helper methods to sanitize cost.
 *
 * @since 3.7.7
 */
class SanitizeCost {

    /*
     * @var $fee_cost
     */
    public $fee_cost;

    /**
     * Evaluate a cost from a sum/string.
     *
     * @param  string $sum
     * @param  array  $args
     *
     * @return string
     */
    public function evaluate_cost( $sum, $args = [] ) {
		// Add warning for subclasses.
		if ( ! is_array( $args ) || ! array_key_exists( 'qty', $args ) || ! array_key_exists( 'cost', $args ) ) {
			wc_doing_it_wrong( __FUNCTION__, '$args must contain `cost` and `qty` keys.', '4.0.1' );
		}

		include_once WC()->plugin_path() . '/includes/libraries/class-wc-eval-math.php';

		// Allow 3rd parties to process shipping cost arguments.
		$args           = apply_filters( 'dokan_evaluate_shipping_cost_args', $args, $sum, $this );
		$locale         = localeconv();
		$decimals       = [ wc_get_price_decimal_separator(), $locale['decimal_point'], $locale['mon_decimal_point'], ',' ];
		$this->fee_cost = $args['cost'];

		// Expand shortcodes.
		add_shortcode( 'fee', [ $this, 'fee' ] );

		$sum = do_shortcode(
			str_replace(
				[
					'[qty]',
					'[cost]',
                ],
				[
					$args['qty'],
					$args['cost'],
                ],
				$sum
			)
		);

		remove_shortcode( 'fee', [ $this, 'fee' ] );

		// Remove whitespace from string.
		$sum = preg_replace( '/\s+/', '', $sum );

		// Remove locale from string.
		$sum = str_replace( $decimals, '.', $sum );

		// Trim invalid start/end characters.
		$sum = rtrim( ltrim( $sum, "\t\n\r\0\x0B+*/" ), "\t\n\r\0\x0B+-*/" );

		// Do the math.
		return $sum ? \WC_Eval_Math::evaluate( $sum ) : 0;
	}


    /**
	 * Sanitize the cost field.
	 *
	 * @param string $value Un-sanitized value.
     *
	 * @throws \Exception Last error triggered.
     *
	 * @return string
	 */
	public function sanitize_cost( $value ) {
		$value = is_null( $value ) ? '' : $value;
		$value = wp_kses_post( trim( wp_unslash( $value ) ) );
		$value = str_replace( [ get_woocommerce_currency_symbol(), html_entity_decode( get_woocommerce_currency_symbol() ) ], '', $value );
		// Thrown an error on the front end if the evaluate_cost will fail.
		$dummy_cost = $this->evaluate_cost(
			$value,
			[
				'cost' => 1,
				'qty'  => 1,
            ]
		);
		if ( false === $dummy_cost ) {
			throw new \Exception( \WC_Eval_Math::$last_error );
		}
		return $value;
	}

    /**
	 * Work out fee (shortcode).
	 *
	 * @param  array $atts Attributes.
     *
	 * @return string
	 */
	public function fee( $atts ) {
		$atts = shortcode_atts(
			[
				'percent' => '',
				'min_fee' => '',
				'max_fee' => '',
            ],
			$atts,
			'fee'
		);

		$calculated_fee = 0;

		if ( $atts['percent'] ) {
			$calculated_fee = $this->fee_cost * ( floatval( $atts['percent'] ) / 100 );
		}

		if ( $atts['min_fee'] && $calculated_fee < $atts['min_fee'] ) {
			$calculated_fee = $atts['min_fee'];
		}

		if ( $atts['max_fee'] && $calculated_fee > $atts['max_fee'] ) {
			$calculated_fee = $atts['max_fee'];
		}

		return $calculated_fee;
	}
}
