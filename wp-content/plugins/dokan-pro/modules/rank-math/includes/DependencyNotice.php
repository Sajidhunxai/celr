<?php

namespace WeDevs\DokanPro\Modules\RankMath;

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
        // Verify if `Rank Math SEO` plugin is activated
        if ( ! class_exists( 'RankMath' ) ) {
            $this->missing_dependency = true;

            if ( current_user_can( 'activate_plugins' ) ) {
                add_filter( 'dokan_admin_notices', array( $this, 'rank_math_activation_notice' ) );
                add_action( 'wp_ajax_dokan_install_rank_math_seo', array( $this, 'install_rank_math_seo' ) );
                add_action( 'wp_ajax_dokan_activate_rank_math_seo', array( $this, 'activate_rank_math_seo' ) );
            }

            return;
        }

        // Check if Rank Math plugin version is 1.0.80 or later
        if ( version_compare( rank_math()->version, '1.0.80', '<' ) ) {
            $this->missing_dependency = true;

            if ( current_user_can( 'activate_plugins' ) ) {
                add_filter( 'dokan_admin_notices', array( $this, 'rank_math_update_notice' ) );
            }

            return;
        }

        /*
         * Check if rank math setup is completed as it'sneeded
         * to be completed for further processing in the frontend.
         */
        if ( class_exists( 'RankMath\Helper' ) && \RankMath\Helper::is_invalid_registration() ) {
            $this->missing_dependency = true;

            if ( current_user_can( 'manage_options' ) ) {
                add_filter( 'dokan_admin_notices', array( $this, 'rank_math_setup_needed_notice' ) );
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
     * Rank Math SEO plugin activation notice
     *
     * @since 3.4.0
     *
     * @param array $notices
     *
     * @return array
     * */
    public function rank_math_activation_notice( $notices ) {
        if ( $this->is_rank_math_installed() ) {
            $notices[] = [
                'type'        => 'success',
                'title'       => __( 'Dokan Rank Math SEO module is almost ready!', 'dokan' ),
                /* translators: %s: plugin name */
                'description' => sprintf( __( 'You just need to activate the %s plugin to make it functional.', 'dokan' ), '<strong>Rank Math SEO</strong>' ),
                'priority'    => 10,
                'actions'     => [
                    [
                        'type'           => 'primary',
                        'text'           => __( 'Activate this plugin', 'dokan' ),
                        'loading_text'   => __( 'Activating...', 'dokan' ),
                        'completed_text' => __( 'Activated', 'dokan' ),
                        'reload'         => true,
                        'ajax_data'      => [
                            'action'   => 'dokan_activate_rank_math_seo',
                            '_wpnonce' => wp_create_nonce( 'dokan-rank-math-activate-nonce' ),
                        ],
                    ],
                ],
            ];
        } else {
            $notices[] = [
                'type'        => 'alert',
                'title'       => __( 'Dokan Rank Math SEO module is almost ready!', 'dokan' ),
                /* translators: %s plugin name */
                'description' => sprintf( __( 'You just need to install the %s plugin to make it functional.', 'dokan' ), '<strong>Rank Math SEO</strong>' ),
                'priority'    => 10,
                'actions'     => [
                    [
                        'type'           => 'secondary',
                        'text'           => __( 'Install Now', 'dokan' ),
                        'loading_text'   => __( 'Installing...', 'dokan' ),
                        'completed_text' => __( 'Installed', 'dokan' ),
                        'reload'         => true,
                        'ajax_data'      => [
                            'action'   => 'dokan_install_rank_math_seo',
                            '_wpnonce' => wp_create_nonce( 'dokan-rank-math-installer-nonce' ),
                        ],
                    ],
                ],
            ];
        }

        return $notices;
    }

    /**
     * Adds notice to update Rank math SEO plugin.
     *
     * @since 3.5.0
     *
     * @param array $notices
     *
     * @return array
     */
    public function rank_math_update_notice( $notices ) {
        $notices[] = array(
            'type'        => 'success',
            'title'       => __( 'Dokan Rank Math SEO module is almost ready!', 'dokan' ),
            'description' => sprintf(
                /* translators: %1$s: Rank Math SEO plugin title, %2$s: Rank math plugin version */
                __( 'You just need to update the %1$s plugin to the version %2$s or later to make it functional.', 'dokan' ),
                '<strong>Rank Math SEO</strong>',
                '<strong>1.0.80</strong>'
            ),
            'priority'    => 10,
            'actions'     => array(),
        );

        return $notices;
    }

    /**
     * Adds notice to configure the Rank Math SEO.
     *
     * @since 3.7.6
     *
     * @param array $notices
     *
     * @return array
     */
    public function rank_math_setup_needed_notice( $notices ) {
        $notices[] = [
            'type'        => 'success',
            'title'       => __( 'Dokan Rank Math SEO module is almost ready!', 'dokan' ),
            'priority'    => 10,
            'actions'     => [],
            'description' => sprintf(
                /* translators: %1$s: opening anchor tag with link, %2$s: closing anchor tag, %3$s: Rank Math SEO plugin title */
                __( 'You just need to %1$sconfigure%2$s the %3$s plugin to make it functional.', 'dokan' ),
                sprintf( '<strong><a href="%s">', \RankMath\Helper::get_admin_url( 'registration' ) ),
                '</a></strong>',
                '<strong>Rank Math SEO</strong>'
            ),
        ];

        return $notices;
    }

    /**
     * Activate Rank Math SEO plugin
     *
     * @since 3.4.3
     *
     * @return void
     * */
    public function activate_rank_math_seo() {
        if (
            ! isset( $_REQUEST['_wpnonce'] ) ||
            ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'dokan-rank-math-activate-nonce' ) // phpcs:ignore
        ) {
            wp_send_json_error( __( 'Error: Nonce verification failed', 'dokan' ) );
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( __( 'You have no permission to do that', 'dokan' ) );
        }

        activate_plugin( 'seo-by-rank-math/rank-math.php' );

        wp_send_json_success();
    }

    /**
     * Installs Rank Math SEO plugin
     *
     * @since 3.4.0
     *
     * @return void
     * */
    public function install_rank_math_seo() {
        if (
            ! isset( $_REQUEST['_wpnonce'] ) ||
            ! wp_verify_nonce( sanitize_key( wp_unslash( $_REQUEST['_wpnonce'] ) ), 'dokan-rank-math-installer-nonce' ) // phpcs:ignore
        ) {
            wp_send_json_error( __( 'Error: Nonce verification failed', 'dokan' ) );
        }

        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';

        $api = plugins_api(
            'plugin_information', array(
                'slug'   => 'seo-by-rank-math',
                'fields' => array(
                    'sections' => false,
                ),
            )
        );

        $upgrader = new \Plugin_Upgrader( new \WP_Ajax_Upgrader_Skin() );
        $upgrader->install( $api->download_link );
        activate_plugin( 'seo-by-rank-math/rank-math.php' );

        wp_send_json_success();
    }

    /**
     * Checks if Rank Math SEO plugin is installed
     *
     * @since 3.4.0
     *
     * @return bool
     */
    private function is_rank_math_installed() {
        $plugins = array_keys( get_plugins() );

        return in_array( 'seo-by-rank-math/rank-math.php', $plugins, true );
    }
}
