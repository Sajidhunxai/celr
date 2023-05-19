<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Class for managing frontend
 *
 * @since 3.6.1
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Frontend
 */
class Manager {

    /**
     * Class constructor.
     *
     * @since 3.6.1
     *
     * @return void
     */
    public function __construct() {
        $this->init_classes();
    }

    /**
     * Instantiates required classes.
     *
     * @since 3.6.1
     *
     * @return void
     */
    private function init_classes() {
        new Assets();
    }
}
