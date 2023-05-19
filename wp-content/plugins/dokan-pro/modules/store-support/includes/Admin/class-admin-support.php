<?php
/**
 * Class Dokan_Admin_Support file
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once DOKAN_STORE_SUPPORT_INC_DIR . '/class-store-support-helper.php';

if ( ! class_exists( 'Dokan_Admin_Support' ) ) :

    /**
     * Support ticket class for admin
     *
     * This class creates menu in admin dashboard, registers vue.js routes for admin
     * dashboard and enqueue scripts and styles.
     *
     * @class Dokan_Admin_Support
     *
     * @version 3.5.0
     */
    class Dokan_Admin_Support {

        /**
         * Class constructor.
         *
         * @since 3.5.0
         */
        public function __construct() {
            add_action( 'dokan_admin_menu', [ $this, 'add_admin_menu' ] );
            add_filter( 'dokan-admin-routes', [ $this, 'add_admin_route' ] );
            add_action( 'init', [ $this, 'register_scripts' ] );
            add_action( 'dokan-vue-admin-scripts', [ $this, 'enqueue_admin_script' ] );
        }

        /**
         * Add Dokan submenu
         *
         * @since 3.5.0
         *
         * @param string $capability
         *
         * @return void
         */
        public function add_admin_menu( $capability ) {
            if ( current_user_can( $capability ) ) {
                global $submenu;

                $counts        = StoreSupportHelper::get_unread_support_topic_count();
                $unread_ticket = $counts < 1 ? 'display:none;' : '';
                $title = sprintf(
                    /* translators: 1) two opening span tags, 2) unread tickets count, 3) two closing span tags */
                    __( 'Store Support %1$s%2$s%3$s', 'dokan' ),
                    '<span class = "awaiting-mod count-1 dokan-unread-ticket-count-in-list" style="' . $unread_ticket . '"><span class="pending-count dokan-unread-ticket-count-badge-in-list">',
                    $counts,
                    '</span></span>'
                );

                $slug = 'dokan';

                $submenu[ $slug ][] = [ $title, $capability, 'admin.php?page=' . $slug . '#/admin-store-support' ]; //phpcs:ignore
            }
        }

        /**
         * Add admin page Route
         *
         * @since 3.5.0
         *
         * @param array $routes
         *
         * @return array
         */
        public function add_admin_route( $routes ) {
            $routes[] = [
                'path'      => '/admin-store-support',
                'name'      => 'AdminStoreSupport',
                'component' => 'AdminStoreSupport',
            ];

            return $routes;
        }

        /**
         * Register scripts
         *
         * @since 3.7.4
         */
        public function register_scripts() {
            list( $suffix, $version ) = dokan_get_script_suffix_and_version();

            wp_register_style(
                'dokan-admin-store-support-vue',
                DOKAN_STORE_SUPPORT_PLUGIN_ASSEST . '/dist/css/dokan-admin-store-support' . $suffix . '.css',
                [],
                $version
            );
            wp_register_script(
                'dokan-admin-store-support-vue',
                DOKAN_STORE_SUPPORT_PLUGIN_ASSEST . '/dist/js/dokan-admin-store-support' . $suffix . '.js',
                [ 'jquery', 'dokan-vue-vendor', 'dokan-vue-bootstrap' ],
                $version,
                true
            );
        }

        /**
         * Enqueue admin script
         *
         * @since 3.5.0
         *
         * @return void
         */
        public function enqueue_admin_script() {
            wp_enqueue_style( 'dokan-admin-store-support-vue' );
            wp_enqueue_script( 'dokan-admin-store-support-vue' );
        }
    }
endif;
