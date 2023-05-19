<?php

use WeDevs\DokanPro\Modules\ProductAddon;

/**
 * Dependency Notice Handler Class
 *
 * @since 3.5.0
 */
class DependencyNotice {
    /**
     * Whether the module is loadable or not.
     *
     * @var bool
     */
    protected $missing_dependency = false;

    /**
     * Class constructor
     *
     * @since 3.5.0
     */
    public function __construct() {
        // Check WooCommerce Product Addon plugin is activated
        if ( ! function_exists( 'woocommerce_product_addons_init' ) ) {
            $this->missing_dependency = true;

            if ( current_user_can( 'activate_plugins' ) ) {
                add_filter( 'dokan_admin_notices', [ $this, 'product_addon_activation_notice' ] );
                add_action( 'wp_ajax_dokan_activate_wc_product_addon', [ $this, 'activate_wc_product_addon' ] );
            }
        }
    }

    /**
     * Check has missing dependency
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public function is_missing_dependency() {
        return $this->missing_dependency;
    }

    /**
     * WooCommerce Product Addon activation notice
     *
     * @since 3.5.0
     *
     * @param array $notices
     *
     * @return array
     */
    public function product_addon_activation_notice( $notices ) {
        if ( $this->is_wc_product_addon_installed() ) {
            $notices[] = [
                'type'        => 'success',
                'title'       => __( 'Dokan Product Addon module is almost ready!', 'dokan' ),
                /* translators: %s: plugin name */
                'description' => sprintf( __( 'You just need to activate the %s plugin to make it functional.', 'dokan' ), '<strong>WooCommerce Product Addons</strong>' ),
                'priority'    => 10,
                'actions'     => [
                    [
                        'type'           => 'primary',
                        'text'           => __( 'Activate this plugin', 'dokan' ),
                        'loading_text'   => __( 'Activating...', 'dokan' ),
                        'completed_text' => __( 'Activated', 'dokan' ),
                        'reload'         => true,
                        'ajax_data'      => [
                            'action'   => 'dokan_activate_wc_product_addon',
                            '_wpnonce' => wp_create_nonce( 'dokan-wc-product-addon-nonce' ),
                        ],
                    ],
                ],
            ];
        } else {
            $notices[] = [
                'type'        => 'alert',
                'title'       => __( 'Dokan Product Addon module is almost ready!', 'dokan' ),
                /* translators: %s plugin name */
                'description' => sprintf( __( 'Dokan Product Addon requires %s plugin to be installed & activated first !', 'dokan' ), '<strong>WooCommerce Product Addons</strong>' ),
                'priority'    => 10,
                'actions'     => [
                    [
                        'type'   => 'primary',
                        'text'   => __( 'Get Now', 'dokan' ),
                        'target' => '_blank',
                        'action' => esc_url( 'https://woocommerce.com/products/product-add-ons/' ),
                    ],
                ],
            ];
        }

        return $notices;
    }

    /**
     * Checks if WC Product Addon plugin is installed
     *
     * @since 3.5.0
     *
     * @return bool
     */
    private function is_wc_product_addon_installed() {
        $plugins = array_keys( get_plugins() );

        return in_array( 'woocommerce-product-addons/woocommerce-product-addons.php', $plugins, true );
    }

    /**
     * Activate WooCommerce Product Addon plugin
     *
     * @since 3.5.0
     *
     * @return void
     * */
    public function activate_wc_product_addon() {
        if (
            ! isset( $_REQUEST['_wpnonce'] ) ||
            ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'dokan-wc-product-addon-nonce' ) // phpcs:ignore
        ) {
            wp_send_json_error( __( 'Error: Nonce verification failed', 'dokan' ) );
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( __( 'You have no permission to do that', 'dokan' ) );
        }

        activate_plugin( 'woocommerce-product-addons/woocommerce-product-addons.php' );

        wp_send_json_success();
    }
}
