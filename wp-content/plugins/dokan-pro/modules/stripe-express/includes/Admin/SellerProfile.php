<?php

namespace WeDevs\DokanPro\Modules\StripeExpress\Admin;

defined( 'ABSPATH' ) || exit; // Exit if called directly.

use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;
use WeDevs\DokanPro\Modules\StripeExpress\Processors\User;

/**
 * Seller profile handler class for admin.
 *
 * @since 3.7.18
 *
 * @package WeDevs\DokanPro\Modules\StripeExpress\Admin
 */
class SellerProfile {

    /**
     * Class constructor.
     *
     * @since 3.7.18
     *
     * @return void
     */
    public function __construct() {
        if ( ! Helper::is_api_ready() ) {
            return;
        }
        $this->hooks();
    }

    /**
     * Registers all necessary hooks.
     *
     * @since 3.7.18
     *
     * @return void
     */
    protected function hooks() {
        add_action( 'edit_user_profile', [ $this, 'render_stripe_express_status' ], 50 );
        add_action( 'show_user_profile', [ $this, 'render_stripe_express_status' ], 50 );
        add_action( 'personal_options_update', [ $this, 'update_seller' ] , 50 );
        add_action( 'edit_user_profile_update', [ $this, 'update_seller' ] , 50 );
        add_filter( 'dokan_vendor_to_array', [ $this, 'process_stripe_express_data' ] );
    }

    /**
     * Renders a menu for stripe express status of vendor.
     *
     * @since 3.7.18
     *
     * @param \WP_User $user
     *
     * @return void
     */
    public function render_stripe_express_status( $user ) {
        $user_id = $user->ID;

        if ( ! dokan_is_user_seller( $user_id ) || ! current_user_can( 'manage_woocommerce' )  ) {
            return;
        }

        $args = [
            'user_id'        => $user_id,
            'stripe_account' => User::set( $user_id ),
            'gateway_title'  => Helper::get_gateway_title(),
        ];

        Helper::get_admin_template( 'seller-profile-settings', $args );
    }

    /**
     * Update vendor profile
     *
     * @since 3.7.18
     *
     * @param int $vendor_id
     *
     * @return void
     */
    public function update_seller( $vendor_id ) {
        if ( ! dokan_is_user_seller( $vendor_id ) || ! current_user_can( 'manage_woocommerce' )  ) {
            return;
        }

        if (
            ! isset( $_POST['dokan_stripe_express_user_edit_nonce'] ) ||
            ! wp_verify_nonce( sanitize_key( $_POST['dokan_stripe_express_user_edit_nonce'] ), 'dokan_stripe_express_user_edit' )
        ) {
            return;
        }

        if ( isset( $_POST['dokan_stripe_express_admin_disconnect'] ) ) {
            User::disconnect( $vendor_id );
        } elseif ( isset( $_POST['dokan_stripe_express_admin_delete'] ) ) {
            User::disconnect( $vendor_id, true );
        }
    }

    /**
     * Process stripe express data for vendor.
     *
     * @since 3.7.18
     *
     * @param array $data
     *
     * @return array
     */
    public function process_stripe_express_data( $data ) {
        $vendor_id = ! empty( $data['id'] ) ? absint( $data['id'] ) : 0;

        if ( ! current_user_can( 'manage_woocommerce' ) && $vendor_id !== dokan_get_current_user_id() ) {
            return $data;
        }

        $data['payment']['stripe_express'] = Helper::is_seller_connected( $vendor_id );

        return $data;
    }
}
