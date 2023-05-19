<?php

namespace WeDevs\DokanPro\Modules\Stripe;

use Stripe\OAuth;

class Auth extends OAuth {

    /**
     * Get vendor authorization URL
     *
     * @since 3.1.0
     *
     * @return string
     */
    public static function get_vendor_authorize_url() {
        return self::authorizeUrl(
            [
                'scope'        => 'read_write',
                'redirect_uri' => dokan_get_navigation_url( 'settings/payment-manage-dokan-stripe-connect' ),
                'state'        => 'dokan-stripe-connect:' . wp_create_nonce( 'dokan-stripe-vendor-authorize' )
            ]
        );
    }

    /**
     * Get vendor deauthorization URL
     *
     * @since 3.1.0
     *
     * @return string
     */
    public static function get_vendor_deauthorize_url() {
        return wp_nonce_url(
            add_query_arg(
                [ 'action' => 'dokan-disconnect-stripe' ],
                dokan_get_navigation_url( 'settings/payment-manage-dokan-stripe-connect' )
            ),
            'dokan-stripe-vendor-deauthorize'
        );
    }

    /**
     * Retrieve vendor token from Stripe API
     *
     * @since 3.1.0
     *
     * @param string $code
     *
     * @throws \Stripe\Exception\OAuth\OAuthErrorException if the request fails
     *
     * @return StripeObject Object containing the response from the API.
     */
    public static function get_vendor_token( $code ) {
        return self::token( [
            'code'       => $code,
            'grant_type' => 'authorization_code',
        ] );
    }
}
