<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Admin;

defined( 'ABSPATH' ) || exit; // Exit if called directly.

/**
 * Manager class for Admin.
 *
 * @since 3.6.1
 */
class Manager {

    /**
     * Class constructor
     *
     * @since 3.6.1
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
        new SellerProfile();
    }
}
