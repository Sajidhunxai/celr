<?php

namespace WeDevs\DokanPro\Modules\Elementor;

/**
 * Dependency Notice Handler Class.
 *
 * @since 3.5.1
 */
class DependencyNotice {

    /**
     * Whether the module is loadable or not.
     *
     * @since 3.5.1
     *
     * @var bool
     */
    protected $missing_dependency = false;

    /**
     * Class constructor.
     *
     * @since 3.5.1
     */
    public function __construct() {
        // Verify if `Elementor and Elementor Pro` plugins are activated.
        if ( ! class_exists( '\Elementor\Plugin' ) || ! class_exists( '\ElementorPro\Plugin' ) ) {
            $this->missing_dependency = true;

            add_filter( 'dokan_admin_notices', [ $this, 'elementor_not_installed' ] );

            return;
        } elseif ( defined( 'ELEMENTOR_VERSION' ) && version_compare( ELEMENTOR_VERSION, '2.5.15', '<' ) ) {
            $this->missing_dependency = true;

            add_filter( 'dokan_admin_notices', [ $this, 'elementor_lite_version_mismatch' ] );

            return;
        } elseif ( defined( 'ELEMENTOR_PRO_VERSION' ) && version_compare( ELEMENTOR_PRO_VERSION, '2.5.3', '<' ) ) {
            $this->missing_dependency = true;

            add_filter( 'dokan_admin_notices', [ $this, 'elementor_pro_version_mismatch' ] );

            return;
        }
    }

    /**
     * Elementor not installed notice.
     *
     * @since 3.5.1
     *
     * @param array $notices
     *
     * @return array $notices
     */
    public function elementor_not_installed( $notices ) {
        // translators: 1. elementor plugin name, 2. elementor pro plugin name.
        $notice    = sprintf( __( 'Dokan Elementor module requires both %1$s and %2$s to be activated', 'dokan' ), '<a href="https://wordpress.org/plugins/elementor/" target="_blank">Elementor</a>', '<a href="https://elementor.com/pro/" target="_blank">Elementor Pro</a>' );
        $notices[] = [
            'type'        => 'alert',
            'title'       => __( 'Dokan Elementor module is almost ready!', 'dokan' ),
            'description' => $notice,
            'priority'    => 10,
        ];

        return $notices;
    }

    /**
     * Elementor Lite version mismatch notice.
     *
     * @since 3.5.1
     *
     * @param array $notices
     *
     * @return array $notices
     */
    public function elementor_lite_version_mismatch( $notices ) {
        // translators: elementor requires version.
        $notice    = sprintf( __( 'Dokan Elementor module requires at least %s.', 'dokan' ), '<strong>Elementor v2.5.15</strong>' );
        $notices[] = [
            'type'        => 'alert',
            'title'       => __( 'Dokan Elementor module is almost ready!', 'dokan' ),
            'description' => $notice,
            'priority'    => 10,
        ];

        return $notices;
    }

    /**
     * Elementor Pro version mismatch notice.
     *
     * @since 3.5.1
     *
     * @param array $notices
     *
     * @return array $notices
     */
    public function elementor_pro_version_mismatch( $notices ) {
        // translators: elementor pro requires version.
        $notice    = sprintf( __( 'Dokan Elementor module requires at least %s.', 'dokan' ), '<strong>Elementor Pro v2.5.3</strong>' );
        $notices[] = [
            'type'        => 'alert',
            'title'       => __( 'Dokan Elementor module is almost ready!', 'dokan' ),
            'description' => $notice,
            'priority'    => 10,
        ];

        return $notices;
    }

    /**
     * Check dependency missing status.
     *
     * @since 3.5.1
     *
     * @return bool
     */
    public function is_missing_dependency() {
        return $this->missing_dependency;
    }
}
