<?php

namespace WeDevs\DokanPro\Upgrade;

class Upgrades {

    /**
     * List of upgrades
     *
     * Add array element like
     * `2.5.0 => [ 'upgrader' => Upgrades\V_2_5_0::class, 'require' => '2.8.0' ]`
     * where `require` is the the last version found in \WeDevs\Dokan\Upgrade\Upgrades
     * class.
     *
     * @since 3.0.0
     *
     * @var array
     */
    private static $upgrades = [
        '3.0.7' => [
            'upgrader' => Upgraders\V_3_0_7::class,
            'require'  => '3.0.4',
        ],
        '3.0.8' => [
            'upgrader' => Upgraders\V_3_0_8::class,
            'require'  => '3.0.4',
        ],
        '3.1.1' => [
            'upgrader' => Upgraders\V_3_1_1::class,
            'require'  => '3.0.14',
        ],
        '3.2.0' => [
            'upgrader' => Upgraders\V_3_2_0::class,
            'require'  => '3.2.0',
        ],
        '3.2.4' => [
            'upgrader' => Upgraders\V_3_2_4::class,
            'require'  => '3.2.6',
        ],
        '3.3.7' => [
            'upgrader' => Upgraders\V_3_3_7::class,
            'require'  => '3.2.13',
        ],
        '3.5.2' => [
            'upgrader' => Upgraders\V_3_5_2::class,
            'require'  => null,
        ],
        '3.7.0' => [
            'upgrader' => Upgraders\V_3_7_0::class,
            'require'  => '3.6.0',
        ],
        '3.7.4' => [
            'upgrader' => Upgraders\V_3_7_4::class,
            'require'  => '3.6.4',
        ],
        '3.7.8' => [
            'upgrader' => Upgraders\V_3_7_8::class,
            'require'  => null,
        ],
        '3.7.17' => [
            'upgrader' => Upgraders\V_3_7_17::class,
            'require'  => null,
        ],
        '3.7.21' => [
            'upgrader' => Upgraders\V_3_7_21::class,
            'require'  => null,
        ],
    ];

    /**
     * Get DB installed version number
     *
     * @since 3.0.0
     *
     * @return string
     */
    public static function get_db_installed_version() {
        return get_option( dokan_pro()->get_db_version_key(), null );
    }

    /**
     * Detects if upgrade is required
     *
     * @since 3.0.0
     *
     * @param bool $is_required
     *
     * @return bool
     */
    public static function is_upgrade_required( $is_required = false ) {
        $installed_version = self::get_db_installed_version();
        $upgrade_versions  = array_keys( self::$upgrades );

        if ( ! $installed_version ) {
            return true;
        }

        if ( $installed_version && version_compare( $installed_version, end( $upgrade_versions ), '<' ) ) {
            return true;
        }

        return $is_required;
    }

    /**
     * Update Dokan Pro version number in DB
     *
     * @since 3.0.0
     *
     * @return void
     */
    public static function update_db_dokan_pro_version() {
        $installed_version = self::get_db_installed_version();

        if ( version_compare( $installed_version, DOKAN_PRO_PLUGIN_VERSION, '<' ) ) {
            update_option( dokan_pro()->get_db_version_key(), DOKAN_PRO_PLUGIN_VERSION );
        }
    }

    /**
     * Get upgrades
     *
     * @since 3.0.0
     *
     * @param array $upgrades
     *
     * @return array
     */
    public static function get_upgrades( $upgrades = [] ) {
        if ( ! self::is_upgrade_required() ) {
            return $upgrades;
        }

        $installed_version = self::get_db_installed_version();

        foreach ( self::$upgrades as $version => $upgrade ) {
            if ( version_compare( $installed_version, $version, '<' ) ) {
                $upgrades[ $upgrade['require'] ][] = $upgrade;
            }
        }

        return $upgrades;
    }
}
