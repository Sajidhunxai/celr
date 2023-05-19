<?php

namespace WeDevs\DokanPro\Modules\Stripe\Settings;

use WeDevs\Dokan\Traits\Singleton;

defined( 'ABSPATH' ) || exit;

class RetrieveSettings {

    use Singleton;

    /**
     * Settings array holder
     *
     * @var array
     */
    public $settings;

    /**
     * Boot method
     *
     * @since 3.0.3
     *
     * @param string $gateway
     *
     * @return array
     */
    public function boot( $gateway = 'dokan-stripe-connect' ) {
        $this->settings = get_option( "woocommerce_{$gateway}_settings", [] );
    }
}
