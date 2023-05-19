<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Orders;

/**
 * Manager class for orders.
 *
 * @since 3.5.0
 */
class Manager {

    /**
     * Class constructor.
     *
     * @since 3.5.0
     */
    public function __construct() {
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
        new Payment();
        new Refund();
    }
}
