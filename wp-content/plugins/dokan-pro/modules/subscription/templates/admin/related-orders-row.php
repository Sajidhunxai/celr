<?php
/**
 * Display a row in the related orders table for a subscription or order
 *
 * @var array $order A WC_Order order object to display
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

// WC 3.0+ compatibility
$order_post = get_post( $order->get_id() );

?>
<tr>
	<td>
		<a href="<?php echo esc_url( get_edit_post_link( $order->get_id() ) ); ?>">
			<?php
			// translators: placeholder is an order number.
			echo sprintf( esc_html_x( '#%s', 'hash before order number', 'dokan' ), esc_html( $order->get_order_number() ) );
			?>
		</a>
	</td>
	<td>
		<?php
        echo ( $order->get_parent_id() > 0 )
            ? esc_html_x( 'Renewal Order', 'vendor subscription admin related order list', 'dokan' )
            : esc_html_x( 'Parent Order', 'vendor subscription admin related order list', 'dokan' );
		?>
	</td>
	<td>
		<?php
		$timestamp_gmt = $order->get_date_created()->getTimestamp();
		if ( $timestamp_gmt > 0 ) {
			// translators: php date format
			$t_time          = get_the_time( _x( 'Y/m/d g:i:s A', 'post date', 'dokan' ), $order_post );
			$date_to_display = human_time_diff( $timestamp_gmt, time() );
		} else {
			$t_time = $date_to_display = __( 'Unpublished', 'dokan' ); //phpcs:ignore
		}
        ?>
		<abbr title="<?php echo esc_attr( $t_time ); ?>">
			<?php echo esc_html( apply_filters( 'post_date_column_time', $date_to_display, $order_post ) ); ?>
		</abbr>
	</td>
	<td>
		<?php
		$classes = [
			'order-status',
			sanitize_html_class( 'status-' . $order->get_status() ),
		];

        $status_name = wc_get_order_status_name( $order->get_status() );

		printf( '<mark class="%s"><span>%s</span></mark>', esc_attr( implode( ' ', $classes ) ), esc_html( $status_name ) );
		?>
	</td>
	<td>
		<span class="amount"><?php echo wp_kses( $order->get_formatted_order_total(), [ 'small' => [], 'span' => [ 'class' => [] ], 'del' => [], 'ins' => [] ] ); // phpcs:ignore WordPress.Arrays.ArrayDeclarationSpacing.AssociativeArrayFound ?></span>
	</td>
</tr>
