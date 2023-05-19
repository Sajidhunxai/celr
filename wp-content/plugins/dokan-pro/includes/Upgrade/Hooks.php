<?php

namespace WeDevs\DokanPro\Upgrade;

class Hooks {

    /**
     * Class constructor
     *
     * @since 3.0.0
     */
    public function __construct() {
        add_filter( 'dokan_upgrade_is_upgrade_required', [ Upgrades::class, 'is_upgrade_required' ], 2 );
        add_filter( 'dokan_upgrade_upgrades', [ Upgrades::class, 'get_upgrades' ], 2 );
        add_action( 'dokan_upgrade_is_not_required', [ Upgrades::class, 'update_db_dokan_pro_version' ] );
        add_action( 'dokan_upgrade_finished', [ Upgrades::class, 'update_db_dokan_pro_version' ] );
    }
}
