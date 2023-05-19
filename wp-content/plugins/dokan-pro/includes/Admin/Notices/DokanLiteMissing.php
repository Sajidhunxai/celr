<?php

namespace WeDevs\DokanPro\Admin\Notices;

use Plugin_Upgrader;
use WP_Ajax_Upgrader_Skin;

/**
 * Dokan lite missing notice handler class
 *
 * @since 3.4.3
 */
class DokanLiteMissing {
    /**
     * Class constructor
     */
    public function __construct() {
        add_action( 'admin_notices', [ $this, 'activation_notice' ] );
        add_action( 'wp_ajax_dokan_pro_install_dokan_lite', [ $this, 'install_dokan_lite' ] );
    }

    /**
     * Dokan main plugin activation notice
     *
     * @since 2.5.2
     *
     * @return void
     * */
    public function activation_notice() {
        if ( ! class_exists( 'WeDevs_Dokan' ) && current_user_can( 'activate_plugins' ) ) {
            $plugin_file        = 'dokan-pro/dokan-pro.php';
            $core_plugin_file   = dokan_pro()->get_core_plugin_file();
            $is_dokan_installed = dokan_pro()->is_dokan_lite_installed();

            include_once DOKAN_PRO_TEMPLATE_DIR . '/dokan-lite-activation-notice.php';
        }
    }

    /**
     * Install dokan lite
     *
     * @since 2.5.2
     *
     * @return void
     * */
    public function install_dokan_lite() {
        if ( ! isset( $_REQUEST['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_REQUEST['_wpnonce'] ), 'dokan-pro-installer-nonce' ) ) { // phpcs:ignore
            wp_send_json_error( __( 'Error: Nonce verification failed', 'dokan' ) );
        }

        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $plugin = 'dokan-lite';
        $api    = plugins_api(
            'plugin_information', [
                'slug'   => $plugin,
                'fields' => [ 'sections' => false ],
            ]
        );

        $upgrader = new Plugin_Upgrader( new WP_Ajax_Upgrader_Skin() );
        $result   = $upgrader->install( $api->download_link );
        activate_plugin( 'dokan-lite/dokan.php' );

        wp_send_json_success();
    }
}
