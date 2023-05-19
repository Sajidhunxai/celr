<?php

namespace WeDevs\DokanPro\Upgrade\Upgraders;

use WeDevs\DokanPro\Abstracts\DokanProUpgrader;
use WeDevs\DokanPro\Upgrade\Upgraders\BackgroundProcesses\V_3_1_1_UpdateSubscriptionEnddate;

class V_3_1_1 extends DokanProUpgrader {

    /**
     * Upgrade the license key
     *
     * @return void
     */
    public static function upgrade_license_key() {
        $dokan_key            = 'dokan_license';
        $dokan_license_status = 'dokan_license_status';
        $new_key              = 'dokan_pro_license';

        $license        = get_option( $dokan_key, false );
        $license_status = get_option( $dokan_license_status, false );

        if ( ! $license ) {
            return false;
        }

        $new_option = [
            'title'            => 'dokan-pro',
            'key'              => $license['key'],
            'status'           => false,
            'source_id'        => null,
            'recurring'        => 0,
            'expiry_days'      => false,
            'activation_limit' => 0,
            'remaining'        => 0,
        ];

        if ( is_object( $license_status ) ) {
            $new_option['status'] = $license_status->activated ? 'activated' : 'deactivated';

            if ( $license_status->message ) {
                $message     = str_replace( ' activations remaining', '', $license_status->message );
                $activations = explode( ' out of ', $message );

                if ( isset( $activations[0] ) ) {
                    $new_option['remaining'] = intval( $activations[0] );
                }

                if ( isset( $activations[1] ) ) {
                    $new_option['activation_limit'] = intval( $activations[1] );
                }
            }
        }

        update_option( $new_key, $new_option );

        if ( ! class_exists( '\Appsero\Client' ) ) {
            return;
        }

        // call to appsero for forcefully check the status
        $client = new \Appsero\Client( '8f0a1669-b8db-46eb-9fc4-02ac5bfe89e7', __( 'Dokan Pro', 'dokan' ), DOKAN_PRO_FILE );

        $appsero_license = $client->license();

        // just to be safe if old Appsero SDK is being used
        if ( method_exists( $appsero_license, 'set_option_key' ) ) {
            $appsero_license->set_option_key( 'dokan_pro_license' );
        }

        $appsero_license->license_form_submit(
            [
                '_nonce'      => wp_create_nonce( 'Dokan Pro' ),
                '_action'     => 'active',
                'license_key' => $license['key'],
            ]
        );
    }

    /**
     * Update the missing shipping zone locations table data
     *
     * @since 3.0.7
     *
     * @return void
     */
    public static function update_subscription_product_pack_enddate() {
        $processor = new V_3_1_1_UpdateSubscriptionEnddate();

        // get all seller and add them to queue for further processing.
        $users = get_users(
            [
                'role__in'   => [ 'seller', 'administrator' ],
                'fields' => [ 'ID', 'user_email' ],
            ]
        );

        foreach ( $users as $user ) {
            $processor->push_to_queue(
                [
                    'type' => 'vendor',
                    'id'   => $user->ID,
                ]
            );
        }

        $processor->dispatch_process();
    }
}
