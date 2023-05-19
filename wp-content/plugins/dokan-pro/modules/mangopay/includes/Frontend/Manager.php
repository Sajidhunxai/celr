<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Frontend;

/**
 * Class for managing frontend
 *
 * @since 3.5.0
 */
class Manager {

    /**
     * Class constructor
     *
     * @since 3.5.0
     *
     * @return void
     */
    public function __construct() {
        $this->init_classes();
    }

    /**
     * Instantiates required classes
     *
     * @since 3.5.0
     *
     * @return void
     */
    private function init_classes() {
        new Assets();
    }
}
