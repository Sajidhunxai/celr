<?php

namespace WeDevs\DokanPro\Modules\Stripe;

class VendorProfile {

    /**
     * Constructor method
     *
     * @since 3.0.3
     *
     * @return void
     */
    public function __construct() {
        $this->hooks();
    }

    /**
     * Init hooks
     *
     * @since 3.0.3
     *
     * @return void
     */
    protected function hooks() {
        add_action( 'edit_user_profile', [ $this, 'stripe_menu' ], 50 );
        add_action( 'show_user_profile', [ $this, 'stripe_menu' ], 50 );
        add_action( 'personal_options_update', [ $this, 'update_profile' ] , 50 );
        add_action( 'edit_user_profile_update', [ $this, 'update_profile' ] , 50 );
        add_filter( 'dokan_vendor_to_array', [ $this, 'attach_stripe' ] );
    }

    /**
     * Add stripe menu in vendor profile
     *
     * @since 3.0.3
     *
     * @param \WP_User $user
     *
     * @return void
     */
    public function stripe_menu( $user ) {
        if ( ! dokan_is_user_seller( $user->ID ) || ! current_user_can( 'manage_woocommerce' )  ) {
            return $user;
        }

        $stripe_key          = get_user_meta( $user->ID, '_stripe_connect_access_key', true );
        $connected_vendor_id = get_user_meta( $user->ID, 'dokan_connected_vendor_id', true );
        ?>

        <h3><?php esc_html_e( 'Dokan Stripe Settings', 'dokan' );?></h3>

        <?php
        if ( ! empty( $stripe_key ) || ! empty( $connected_vendor_id ) ) {
            submit_button( __( 'Disconnect User Stripe Account', 'dokan' ), 'delete', 'disconnect_user_stripe' );
        } else { ?>
            <h4><?php esc_html_e( 'User account not connected to Stripe', 'dokan' );?></h4>
        <?php }
    }

    /**
     * Update vendor profile
     *
     * @since 3.0.3
     *
     * @param  int $vendor_id
     *
     * @return int
     */
    public function update_profile( $vendor_id ) {
        if ( ! dokan_is_user_seller( $vendor_id ) || ! current_user_can( 'manage_woocommerce' )  ) {
            return $vendor_id;
        }

        if ( ! Helper::is_enabled() ) {
            return $vendor_id;
        }

        if ( isset( $_POST['disconnect_user_stripe'] ) ) {
            delete_user_meta( $vendor_id, 'dokan_connected_vendor_id' );
            delete_user_meta( $vendor_id, '_stripe_connect_access_key' );
            // delete announcement notice nonce
            delete_transient( "dokan_check_stripe_access_key_valid_$vendor_id" );
            delete_transient( 'non_connected_sellers_notice_intervals_' . $vendor_id );
        }

        return $vendor_id;
    }

    /**
     * Attach whether vendor has stripe or not to payment object
     *
     * @since 3.0.3
     *
     * @param $array $data
     *
     * @return array
     */
    public function attach_stripe( $data ) {
        $vendor_id = ! empty( $data['id'] ) ? absint( $data['id'] ) : 0;

        if ( ! current_user_can( 'manage_woocommerce' ) && $vendor_id !== dokan_get_current_user_id() ) {
            return $data;
        }

        $stripe_key          = get_user_meta( $vendor_id, '_stripe_connect_access_key', true );
        $connected_vendor_id = get_user_meta( $vendor_id, 'dokan_connected_vendor_id', true );

        if ( ! empty( $stripe_key ) || ! empty( $connected_vendor_id ) ) {
            $data['payment']['stripe'] = true;
        } else {
            $data['payment']['stripe'] = false;
        }

        return $data;
    }
}
