<?php

namespace WeDevs\DokanPro;

/**
 * Dokan Update class
 *
 * Performs license validation and update checking
 */
class Update {

    /**
     * Appsero License Instance
     *
     * @var \Appsero\License
     */
    private $license;

    /**
     * The license product ID
     *
     * @var string
     */
    private $product_id = 'dokan-pro';

    /**
     * Initialize the class
     */
    public function __construct() {
        if ( ! class_exists( '\Appsero\Client' ) ) {
            return;
        }

        $this->init_appsero();

        if ( is_multisite() ) {
            if ( is_main_site() ) {
                add_filter( 'dokan_admin_notices', [ $this, 'license_enter_notice' ] );
            }
        } else {
            add_filter( 'dokan_admin_notices', [ $this, 'license_enter_notice' ] );
        }

        add_action( 'in_plugin_update_message-' . plugin_basename( DOKAN_PRO_FILE ), [ $this, 'plugin_update_message' ] );
    }

    /**
     * Initialize the updater
     *
     * @return void
     */
    protected function init_appsero() {
        $client = new \Appsero\Client( '8f0a1669-b8db-46eb-9fc4-02ac5bfe89e7', __( 'Dokan Pro', 'dokan' ), DOKAN_PRO_FILE );

        // track plugin install
        $this->insights = $client->insights();

        if ( false === $this->insights->tracking_allowed() ) {
            $this->insights->optin();
        }

        $this->insights->add_extra(
            function () {
                return [
                    'dokan_pro_version' => DOKAN_PRO_PLUGIN_VERSION,
                    'dokan_pro_plan'    => dokan_pro()->get_plan(),
                    'available_modules' => dokan_pro()->module->get_available_modules(),
                    'activate_modules'  => dokan_pro()->module->get_active_modules(),
                    'wc_version'        => function_exists( 'WC' ) ? WC()->version : null,
                    'dokan_version'     => DOKAN_PLUGIN_VERSION,
                ];
            }
        );

        $this->insights->hide_notice()->init_plugin();

        // Active license page and checker
        $args = [
            'type'        => 'submenu',
            'menu_title'  => __( 'License', 'dokan' ),
            'page_title'  => __( 'Dokan Pro License', 'dokan' ),
            'capability'  => 'manage_options',
            'parent_slug' => 'dokan',
            'menu_slug'   => 'dokan_updates',
        ];

        $this->license = $client->license();

        // just to be safe if old Appsero SDK is being used
        if ( method_exists( $this->license, 'set_option_key' ) ) {
            $this->license->set_option_key( 'dokan_pro_license' );
        }

        $this->license->add_settings_page( $args );

        // Active automatic updater
        $client->updater();
    }

    /**
     * Prompts the user to add license key if it's not already filled out
     *
     * @param array $notices
     *
     * @return array
     */
    public function license_enter_notice( $notices ) {
        if ( $this->license->is_valid() ) {
            return $notices;
        }

        $notices[] = [
            'type'        => 'alert',
            'title'       => __( 'Activate Dokan Pro License', 'dokan' ),
            'description' => sprintf( __( 'Please <a href="%1$s">enter</a> your valid <strong>Dokan Pro</strong> plugin license key to unlock more features, premium support and future updates.', 'dokan' ), admin_url( 'admin.php?page=dokan_updates' ) ),
            'priority'    => 1,
            'actions'     => [
                [
                    'type'   => 'primary',
                    'text'   => __( 'Activate License', 'dokan' ),
                    'action' => admin_url( 'admin.php?page=dokan_updates' ),
                ],
            ],
        ];

        return $notices;
    }

    /**
     * Show plugin udpate message
     *
     * @since  2.7.1
     *
     * @param array $args
     *
     * @return void
     */
    public function plugin_update_message( $args ) {
        if ( $this->license->is_valid() ) {
            return;
        }

        $upgrade_notice = sprintf(
            '</p><p class="dokan-pro-plugin-upgrade-notice" style="background: #dc4b02;color: #fff;padding: 10px;">Please <a href="%s" target="_blank">activate</a> your license key for getting regular updates and support',
            admin_url( 'admin.php?page=dokan_updates' )
        );

        echo apply_filters( $this->product_id . '_in_plugin_update_message', wp_kses_post( $upgrade_notice ) );
    }
}
