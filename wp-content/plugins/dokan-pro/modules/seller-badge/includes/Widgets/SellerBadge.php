<?php

namespace WeDevs\DokanPro\Modules\SellerBadge\Widgets;

use WC_Widget;
use WeDevs\DokanPro\Modules\SellerBadge\Helper;
use WeDevs\DokanPro\Modules\SellerBadge\Manager;
use WeDevs\DokanPro\Modules\SellerBadge\Models\BadgeEvent;
use WeDevs\DokanPro\Modules\SellerBadge\Models\BadgeEvent as BadgeEventModel;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

/**
 * Seller Badge Widget
 *
 * @since 3.7.14
 */
class SellerBadge extends WC_Widget {

    /**
     * Class Constructor.
     *
     * @since 3.7.14
     */
    public function __construct() {
        $this->widget_cssclass    = 'woocommerce dokan_widget widget_dokan_seller_badges';
        $this->widget_description = __( "A list of your store's acquired badges.", 'dokan' );
        $this->widget_id          = 'dokan_seller_badges';
        $this->widget_name        = __( 'Dokan: Seller Badge', 'dokan' );
        $this->settings           = [
            'title'            => [
                'type'  => 'text',
                'std'   => __( 'Badges', 'dokan' ),
                'label' => __( 'Title', 'dokan' ),
            ],
            'show_level_count' => [
                'type'  => 'checkbox',
                'std'   => 1,
                'label' => __( 'Show badge level count', 'dokan' ),
            ],
            'show_hover_text'  => [
                'type'  => 'checkbox',
                'std'   => 1,
                'label' => __( 'Show badge hover text', 'dokan' ),
            ],
        ];

        parent::__construct();

        if ( is_active_widget(false, false, $this->id_base) ) {
            add_action( 'wp_head', [ $this, 'load_widget_css' ] );
        }
    }

    /**
     * Get seller badge for a specific vendor
     *
     * @since 3.7.14
     *
     * @param int $vendor_id
     * @param array $instance
     *
     * @return array|object|object[]|\WP_Error|null
     */
    private function get_seller_badges( $vendor_id, $instance ) {
        $manager = new Manager();
        $args    = [
            'vendor_id'             => $vendor_id,
            'acquired_badge_status' => 'published',
            'per_page'              => -1,
            'page'                  => 1,
        ];

        $acquired_badges = $manager->get_seller_badges( $args );

        if ( is_wp_error( $acquired_badges ) || empty( $acquired_badges ) ) {
            return [];
        }

        $acquired_badges = array_filter( $acquired_badges, function ( $badge ) {
            return $badge->vendor_count > 0;
        } );

        return $acquired_badges;
    }

    /**
     * Front-end display of widget.
     *
     * @since 3.7.14
     *
     * @param array $args     Widget arguments.
     * @param array $instance Saved values from database.
     *
     * @see   WP_Widget::widget()
     */
    public function widget( $args, $instance ) {
        if ( ! dokan_is_store_listing() && ! dokan_is_store_page() ) {
            return;
        }

        $seller_id = get_query_var( 'author' );
        if ( empty( $seller_id ) ) {
            return;
        }

        $acquired_badges = $this->get_seller_badges( $seller_id, $instance );
        if ( empty( $acquired_badges ) ) {
            return;
        }

        $show_level_count = ! empty( $instance['show_level_count'] );
        $show_hover_text  = ! empty( $instance['show_hover_text'] );

        ob_start();
        $this->widget_start( $args, $instance );
        $start_div = '<div class="dokan-seller-badge-list">';

        echo apply_filters( 'dokan_seller_badge_before_widget', $start_div );

        foreach ( $acquired_badges as $badge ) {
            $event = Helper::get_dokan_seller_badge_events( $badge->event_type );
            if ( ! is_a( $event, BadgeEventModel::class ) ) {
                continue;
            }

            // setting badge logo via model is important at this point, since this url can come from two different places
            $event->set_badge_logo( $badge->badge_logo );

            $level_count = '';
            $acquired_level_count = absint( $badge->acquired_level_count );
            if ( $acquired_level_count &&  $show_level_count && $event->has_multiple_levels() ) {
                // fix year_active level count
                if ( 'years_active' === $event->get_event_id() ) {
                    $acquired_level_count = Helper::get_vendor_year_count( $seller_id );
                }
                $level_count = '<strong class="bottom-center">' . $acquired_level_count . '</strong>';
            }

            $hover_text = '';
            if ( $show_hover_text ) {
                $hover_text = 'title="' . esc_attr( $event->get_formatted_hover_text( $badge ) ) . '"';
            }

            echo '<div class="container tips" ' . $hover_text . '>
                <img src="' . esc_url( $event->get_formatted_badge_logo() ) . '" alt="' . esc_attr( $badge->badge_name ) . '" />
                ' . $level_count . '
                </div>';
        }

        echo '</div>';

        $this->widget_end( $args );

        echo ob_get_clean();
    }

    public function load_widget_css() {
        echo <<<EOD
<style>
    div.dokan-seller-badge-list {
        display: flex;
        flex-flow: row wrap;
        grid-column-gap: 15px;
        grid-row-gap: 25px;
    }
    div.dokan-seller-badge-list div.container {
        position: relative;
        width: 40px;
        height: 43px;
    }

    div.dokan-seller-badge-list div.container img {
        width: 100%;
    }

    div.dokan-seller-badge-list div.container strong.bottom-center {
        position: absolute;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 15px;
        height: 15px;
        bottom: -8px;
        left: 50%;
        font-size: 0.6875rem;
        font-weight: 500;
        color: #000;
        background-color: #fff;
        border-radius: 50%;
        box-shadow: 0 3px 6px rgba(0, 0, 0, 0.25);
        transform: translateX(-50%);
    }
</style>
EOD;
    }
}
