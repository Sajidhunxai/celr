<?php

namespace WeDevs\DokanPro\Modules\Stripe;

defined( 'ABSPATH' ) || exit;

class StoreProgress {

    /**
     * Constructor method
     *
     * @since DOKAN_POR_SINCE
     *
     * @return void
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * Init all the hooks
     *
     * @since 3.0.3
     *
     * @return void
     */
    private function hooks() {
        add_action( 'dokan_store_profile_saved', [ $this, 'save_stripe_progress' ], 8, 2 );
    }

    /**
    * Save stripe progress settings data
    *
    * @since 2.8
    *
    * @return void
    **/
    public function save_stripe_progress( $store_id, $dokan_settings ) {
        if ( ! $store_id ) {
            return;
        }

        $dokan_settings = get_user_meta( $store_id, 'dokan_profile_settings', true );
        $posted         = wp_unslash( $_POST );

        if ( isset( $posted['settings']['stripe'] ) ) {
            $dokan_settings['payment']['stripe'] = wc_clean( $posted['settings']['stripe'] );
        }

        update_user_meta( $store_id, 'dokan_profile_settings', $dokan_settings );
    }
}
