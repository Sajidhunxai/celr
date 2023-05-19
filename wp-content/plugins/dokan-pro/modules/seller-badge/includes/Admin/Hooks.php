<?php

namespace WeDevs\DokanPro\Modules\SellerBadge\Admin;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // exit if accessed directly
}

/**
 * Class for Hooks integration.
 *
 * @since 3.7.14
 */
class Hooks {

    /**
     * Class constructor
     *
     * @since 3.7.14
     *
     * @return void
     */
    public function __construct() {
        add_action( 'dokan_admin_menu', [ $this, 'add_admin_menu' ] );
        add_filter( 'dokan-admin-routes', [ $this, 'add_admin_route' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_script' ] );
    }

    /**
     * Add Dokan submenu
     *
     * @since 3.7.14
     *
     * @param string $capability
     *
     * @return void
     */
    public function add_admin_menu( $capability ) {
        if ( current_user_can( $capability ) ) {
            global $submenu;

            $title = esc_html__( 'Seller Badge', 'dokan' );
            $slug  = 'dokan';

            $submenu[$slug][] = [ $title, $capability, 'admin.php?page=' . $slug . '#/dokan-seller-badge' ];
        }
    }

    /**
     * Add admin page Route
     *
     * @since 3.7.14
     *
     * @param array $routes
     *
     * @return array
     */
    public function add_admin_route( $routes ) {
        $routes[] = [
            'path'      => '/dokan-seller-badge',
            'name'      => 'SellerBadgeList',
            'component' => 'SellerBadgeList',
        ];
        $routes[] = [
            'path'      => '/dokan-seller-badge/new',
            'name'      => 'NewSellerBadge',
            'component' => 'NewSellerBadge',
        ];
        $routes[] = [
            'path'      => '/dokan-seller-badge/edit/:id',
            'name'      => 'EditSellerBadge',
            'component' => 'NewSellerBadge',
        ];

        return $routes;
    }

    /**
     * Enqueue admin scripts
     *
     * @since 3.7.14
     *
     * @param string $hook
     *
     * @return void
     */
    public function enqueue_admin_script( $hook ) {
        if ( 'toplevel_page_dokan' === $hook ) {
            wp_enqueue_script( 'dokan-seller-badge-admin' );
            wp_enqueue_style( 'dokan-seller-badge-admin' );
        }
    }
}
