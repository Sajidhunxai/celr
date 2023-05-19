<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Admin;

/**
 * Manager class for Admin.
 *
 * @since 3.5.0
 */
class Manager {

    /**
     * Class constructor
     *
     * @since 3.5.0
     */
    public function __construct() {
        if ( ! is_admin() ) {
            return;
        }

        $this->init_classes();
    }

    /**
     * Instantiates required classes.
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function init_classes() {
        new Assets();
    }
}
