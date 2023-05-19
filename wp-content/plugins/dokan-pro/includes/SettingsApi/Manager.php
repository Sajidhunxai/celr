<?php

namespace WeDevs\DokanPro\SettingsApi;

defined( 'ABSPATH' ) || exit;

/**
 * Dokan Pro Vendor Settings API Manager.
 *
 * @since 3.7.13
 */
class Manager {

    /**
     * Constructor
     *
     * @return void
     */
    public function __construct() {
        $this->init();
    }

    /**
     * Initialize settings class instance.
     *
     * @since 3.7.13
     *
     * @return void
     */
    public function init() {
        new Store();
    }
}
