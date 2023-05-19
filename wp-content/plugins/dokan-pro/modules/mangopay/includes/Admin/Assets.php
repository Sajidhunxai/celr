<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Admin;

/**
 * Class to handle admin assets.
 *
 * @since 3.5.0
 */
class Assets {

    /**
     * Classs constructor.
     *
     * @since 3.5.0
     */
    public function __construct() {
        add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
    }

    /**
     * Registers admin scripts
     *
     * @since 3.5.0
     *
     * @return void
     */
     public function register_scripts() {
        $suffix  = '.min';
        $version = DOKAN_PRO_PLUGIN_VERSION;
        if ( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
            $suffix  = '';
            $version = time();
        }

        wp_register_script(
            'dokan-mangopay-admin',
            DOKAN_MANGOPAY_ASSETS . "js/admin{$suffix}.js",
            array( 'jquery' ),
            $version,
            true
        );
    }
}
