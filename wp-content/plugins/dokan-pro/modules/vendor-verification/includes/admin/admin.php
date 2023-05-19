<?php

add_filter( 'dokan_settings_sections', 'dokan_verification_admin_settings' );

function dokan_verification_admin_settings( $sections ) {
    $sections[] = [
        'id'                   => 'dokan_verification',
        'title'                => __( 'Seller Verification', 'dokan' ),
        'icon_url'             => DOKAN_VERFICATION_PLUGIN_ASSEST . '/images/verification.svg',
        'description'          => __( 'Vendor Verification Settings', 'dokan' ),
        'document_link'        => 'https://wedevs.com/docs/dokan/modules/dokan-seller-verification-admin-settings/',
        'settings_title'       => __( 'Seller Verification Settings', 'dokan' ),
        'settings_description' => __( 'You can authenticate your vendors by authorizing vendors to connect their social profiles to their storefront.', 'dokan' ),
    ];
    $sections[] = [
        'id'                   => 'dokan_verification_sms_gateways',
        'title'                => __( 'Verification SMS Gateways', 'dokan' ),
        'icon_url'             => DOKAN_VERFICATION_PLUGIN_ASSEST . '/images/gateways.svg',
        'description'          => __( 'Sms Gateway Verification Config', 'dokan' ),
        'settings_title'       => __( 'Verification SMS Gateways Settings', 'dokan' ),
        'settings_description' => __( 'You can integrate SMS gateways to verify the contact information of the vendor.', 'dokan' ),
    ];

    return $sections;
}

add_filter( 'dokan_settings_fields', 'dokan_verification_admin_settings_fields' );

function dokan_verification_admin_settings_fields( $settings_fields ) {
    $callback = dokan_get_navigation_url( 'settings/verification' );

    $settings_fields['dokan_verification'] = [
        'facebook_app_details' => [
            'name'                => 'facebook_app_details',
            'type'                => 'social',
            'desc'                => sprintf(
                /* translators: 1) Opening anchor tag, 2) Closing anchor tag */
                __( 'Configure your facebook API settings. %1$sGet Help%2$s', 'dokan' ),
                '<a href="https://wedevs.com/docs/dokan/settings/dokan-social-login/configuring-facebook/" target="_blank">',
                '</a>'
            ),
            'label'               => __( 'Connect to Facebook', 'dokan' ),
            'icon_url'            => DOKAN_PRO_PLUGIN_ASSEST . '/images/scl-icons/fb.svg',
            'social_desc'         => __( 'You can successfully connect Facebook with your website.', 'dokan' ),
            'facebook_app_label'  => [
                'name'         => 'fb_app_label',
                'label'        => __( 'Facebook App Settings', 'dokan' ),
                'type'         => 'html',
                'desc'         => sprintf(
                    /* translators: 1) Opening anchor tag, 2) Closing anchor tag */
                    __( '%1$sCreate an App%2$s. If you don\'t have one and fill App ID and Secret below.', 'dokan' ),
                    '<a target="_blank" href="https://developers.facebook.com/apps/">',
                    '</a>'
                ),
                'social_field' => true,
            ],
            'facebook_app_url'    => [
                'url'          => $callback,
                'name'         => 'fb_app_url',
                'label'        => __( 'Site Url', 'dokan' ),
                'type'         => 'html',
                'tooltip'      => __( 'Your store URL, which will be required in creating the App.', 'dokan' ),
                'social_field' => true,
            ],
            'facebook_app_id'     => [
                'name'         => 'fb_app_id',
                'label'        => __( 'App Id', 'dokan' ),
                'type'         => 'text',
                'tooltip'      => __( 'You can get it from Facebook Developer platform -> Login -> Select  "Add A New App" -> Collect App ID.', 'dokan' ),
                'social_field' => true,
            ],
            'facebook_app_secret' => [
                'name'         => 'fb_app_secret',
                'label'        => __( 'App Secret', 'dokan' ),
                'type'         => 'text',
                'tooltip'      => __( 'You can get it from Facebook Developer platform -> Login -> Select  "Add A New App" -> Collect App secret.', 'dokan' ),
                'social_field' => true,
            ],
        ],
        'twitter_app_details'  => [
            'name'               => 'twitter_app_details',
            'type'               => 'social',
            'desc'               => sprintf(
                /* translators: 1) Opening anchor tag, 2) Closing anchor tag */
                __( 'Configure your twitter API settings. %1$sGet Help%2$s', 'dokan' ),
                '<a target="_blank" href="https://wedevs.com/docs/dokan/settings/dokan-social-login/configuring-twitter/">',
                '</a>'
            ),
            'label'              => __( 'Connect to Twitter', 'dokan' ),
            'icon_url'           => DOKAN_PRO_PLUGIN_ASSEST . '/images/scl-icons/twt.svg',
            'social_desc'        => __( 'You can successfully connect Twitter with your website.', 'dokan' ),
            'twitter_app_label'  => [
                'name'         => 'twitter_app_label',
                'label'        => __( 'Twitter App Settings', 'dokan' ),
                'type'         => 'html',
                'desc'         => sprintf(
                    /* translators: 1) Opening anchor tag, 2) Closing anchor tag */
                    __( 'If you don\'t have one and fill Consumer key and Secret below. %1$sCreate an App%2$s', 'dokan' ),
                    '<a target="_blank" href="https://apps.twitter.com/">',
                    '</a>'
                ),
                'social_field' => true,
            ],
            'twitter_app_url'    => [
                'url'          => $callback,
                'name'         => 'twitter_app_url',
                'label'        => __( 'Callback URL', 'dokan' ),
                'type'         => 'html',
                'tooltip'      => __( 'Your store URL, which will be required in creating the App.', 'dokan' ),
                'social_field' => true,
            ],
            'twitter_app_id'     => [
                'name'         => 'twitter_app_id',
                'label'        => __( 'Consumer Key', 'dokan' ),
                'type'         => 'text',
                'tooltip'      => __( 'You can get it from Twitter Developer platform -> Login -> Select  "Create an App" -> Input URL & integrate Twitter with Dokan. Go to "Keys and Tokens" -> View Keys -> Collect API key and use as Consumer Key.', 'dokan' ),
                'social_field' => true,
            ],
            'twitter_app_secret' => [
                'name'          => 'twitter_app_secret',
                'label'         => __( 'Consumer Secret', 'dokan' ),
                'type'          => 'text',
                'tooltip'       => __( 'You can get it from Twitter Developer platform -> Login -> Select  "Create an App" -> Input URL & integrate Twitter with Dokan. Go to "Keys and Tokens" -> View Keys -> Collect API secret and use as Consumer secret.', 'dokan' ),
                'social_field'  => true,
            ],
        ],
        'google_app_details'   => [
            'name'              => 'google_details',
            'type'              => 'social',
            'desc'              => sprintf(
                /* translators: 1) Opening anchor tag, 2) Closing anchor tag */
                __( 'Configure your google API settings. %1$sGet Help%2$s', 'dokan' ),
                '<a href="https://wedevs.com/docs/dokan/settings/dokan-social-login/configuring-google/" target="_blank">',
                '</a>'
            ),
            'label'             => __( 'Connect to Google', 'dokan' ),
            'icon_url'          => DOKAN_PRO_PLUGIN_ASSEST . '/images/scl-icons/google.svg',
            'social_desc'       => __( 'You can successfully connect to your Google account with your website.', 'dokan' ),
            'google_app_label'  => [
                'name'          => 'google_app_label',
                'label'         => __( 'Google App Settings', 'dokan' ),
                'type'          => 'html',
                'desc'          => sprintf(
                    /* translators: 1) Opening anchor tag, 2) Closing anchor tag */
                    __( 'If if you don\'t have one and fill Client ID and Secret below. %1$sCreate an App%2$s', 'dokan' ),
                    '<a target="_blank" href="https://console.developers.google.com/project">',
                    '</a>'
                ),
                'social_field'  => true,
            ],
            'google_app_url'    => [
                'url'          => $callback,
                'name'         => 'google_app_url',
                'label'        => __( 'Redirect URI', 'dokan' ),
                'type'         => 'html',
                'tooltip'      => __( 'Your store URL, which will be required in syncing with Google API.', 'dokan' ),
                'social_field' => true,
            ],
            'google_app_id'     => [
                'name'         => 'google_app_id',
                'label'        => __( 'Client ID', 'dokan' ),
                'type'         => 'text',
                'tooltip'      => __( 'You can get it from Google Console Platform -> Google+API -> Enable -> Manage -> Credentials -> Create Credentials -> OAuth client ID -> Web Application -> Fill in the information & click Create. A pop up will show "Client ID".', 'dokan' ),
                'social_field' => true,
            ],
            'google_app_secret' => [
                'name'         => 'google_app_secret',
                'label'        => __( 'Client Secret', 'dokan' ),
                'type'         => 'text',
                'tooltip'      => __( 'You can get it from Google Console Platform -> Google+API -> Enable -> Manage -> Credentials -> Create Credentials -> OAuth client ID -> Web Application -> Fill in the information & click Create. A pop up will show "Client Credentials".', 'dokan' ),
                'social_field' => true,
            ],
        ],
        'linkedin_app_details' => [
            'name'                => 'linkedin_details',
            'type'                => 'social',
            'desc'                => sprintf(
                /* translators: 1) Opening anchor tag, 2) Closing anchor tag */
                __( 'Configure your linkedin API settings. %1$sGet Help%2$s', 'dokan' ),
                '<a href="https://wedevs.com/docs/dokan/settings/dokan-social-login/configuring-linkedin/" target="_blank">',
                '</a>'
            ),
            'label'               => __( 'Connect to Linkedin', 'dokan' ),
            'icon_url'            => DOKAN_PRO_PLUGIN_ASSEST . '/images/scl-icons/linkedin.svg',
            'social_desc'         => __( 'You can successfully connect LinkedIn with your website.', 'dokan' ),
            'linkedin_app_label'  => [
                'name'         => 'linkedin_app_label',
                'label'        => __( 'Linkedin App Settings', 'dokan' ),
                'type'         => 'html',
                'desc'         => sprintf(
                    /* translators: 1) Opening anchor tag, 2) Closing anchor tag */
                    __( 'If you don\'t have one and fill Client ID and Secret below. %1$sCreate an App%2$s', 'dokan' ),
                    '<a target="_blank" href="https://www.linkedin.com/developer/apps">',
                    '</a>'
                ),
                'social_field' => true,
            ],
            'linkedin_app_url'    => [
                'url'          => $callback,
                'name'         => 'linkedin_app_url',
                'label'        => __( 'Redirect URL', 'dokan' ),
                'type'         => 'html',
                'tooltip'      => __( 'Your store URL, which will be required in creating the App.', 'dokan' ),
                'social_field' => true,
            ],
            'linkedin_app_id'     => [
                'name'         => 'linkedin_app_id',
                'label'        => __( 'Client ID', 'dokan' ),
                'type'         => 'text',
                'tooltip'      => __( 'You can get it from LinkedIn Developers platform -> Create an App -> Fill necessary info -> Click "Create app" -> "Auth" section -> Collect Client ID.', 'dokan' ),
                'social_field' => true,
            ],
            'linkedin_app_secret' => [
                'name'         => 'linkedin_app_secret',
                'label'        => __( 'Client Secret', 'dokan' ),
                'type'         => 'text',
                'tooltip'      => __( 'You can get it from LinkedIn Developers platform -> Create an App -> Fill necessary info -> Click "Create app" -> "Auth" section -> Collect Client Secret.', 'dokan' ),
                'social_field' => true,
            ],
        ],
    ];

    $gateways            = [];
    $gateway_obj         = WeDevs_dokan_SMS_Gateways::instance();
    $registered_gateways = $gateway_obj->get_gateways();

    foreach ( $registered_gateways as $gateway => $option ) {
        $gateways[ $gateway ] = $option['label'];
    }

    $settings_fields['dokan_verification_sms_gateways'] = [
        'sender_name'      => [
            'name'    => 'sender_name',
            'label'   => __( 'Sender Name', 'dokan' ),
            'default' => 'weDevs Team',
            'type'    => 'text',
            'tooltip' => __( 'Customized what name is displayed for "Sender".', 'dokan' ),
        ],
        'sms_text'         => [
            'name'    => 'sms_text',
            'label'   => __( 'SMS Text', 'dokan' ),
            'type'    => 'textarea',
            'rows'     => 3,
            'default' => __( 'Your verification code is: %CODE%', 'dokan' ),
            'desc'    => sprintf(
                /* translators: 1) Opening strong tag, 2) Closing strong tag */
                __( 'This will be displayed in SMS. %1$s%%CODE%%%2$s will be replaced by verification code', 'dokan' ),
                '<strong>',
                '</strong>'
            ),
        ],
        'sms_sent_msg'     => [
            'name'    => 'sms_sent_msg',
            'label'   => __( 'SMS Sent Success', 'dokan' ),
            'default' => __( 'SMS sent. Please enter your verification code', 'dokan' ),
            'type'    => 'textarea',
            'rows'     => 3,
            'tooltip' => __( 'Customize the pop up message on verification successful message delivery.', 'dokan' ),
        ],
        'sms_sent_error'   => [
            'name'    => 'sms_sent_error',
            'label'   => __( 'SMS Sent Error', 'dokan' ),
            'default' => __( 'Unable to send sms. Contact admin', 'dokan' ),
            'type'    => 'textarea',
            'rows'     => 3,
            'tooltip' => __( 'Customize the pop up message for failed verification message delivery.', 'dokan' ),
        ],
        'active_gateway'   => [
            'name'    => 'active_gateway',
            'label'   => __( 'Active Gateway', 'dokan' ),
            'type'    => 'radio',
            'options' => $gateways,
            'tooltip' => __( 'Select your preferred SMS Gateway.', 'dokan' ),
        ],
        'nexmo_details'    => [
            'name'           => 'nexmo_details',
            'label'          => __( 'Connect to Vonage', 'dokan' ),
            'desc'           => __( 'Configure Vonage and connect to your site.', 'dokan' ),
            'type'           => 'social',
            'icon_url'       => DOKAN_PRO_PLUGIN_ASSEST . '/images/scl-icons/vonage.svg',
            'social_desc'    => __( 'You can successfully connect to your Vonage account and log in from here.', 'dokan' ),
            'nexmo_header'   => [
                'name'         => 'nexmo_header',
                'type'         => 'html',
                'desc'         => sprintf(
                    /* translators: 1) Opening anchor tag, 2) Closing anchor tag */
                    __( 'Configure your gateway from %1$shere%2$s and fill the details below', 'dokan' ),
                    '<a target="_blank" href="https://www.vonage.com/">',
                    '</a>'
                ),
                'label'        => __( 'Vonage App Settings', 'dokan' ),
                'social_field' => true,
            ],
            'nexmo_username' => [
                'name'         => 'nexmo_username',
                'type'         => 'text',
                'label'        => __( 'API Key', 'dokan' ),
                'tooltip'      => __( 'You can get it from https://www.vonage.com/ -> Create an Account -> Collect Key.', 'dokan' ),
                'social_field' => true,
            ],
            'nexmo_pass'     => [
                'name'         => 'nexmo_pass',
                'label'        => __( 'API Secret', 'dokan' ),
                'type'         => 'text',
                'tooltip'      => __( 'You can get it from https://www.vonage.com/ -> Create an Account -> Collect Secret.', 'dokan' ),
                'social_field' => true,
            ],
        ],
        'twilio_details'   => [
            'name'             => 'twilio_details',
            'label'            => __( 'Connect to Twilio', 'dokan' ),
            'desc'             => __( 'Configure Twilio and connect to your site.', 'dokan' ),
            'type'             => 'social',
            'icon_url'         => DOKAN_PRO_PLUGIN_ASSEST . '/images/scl-icons/twilio.svg',
            'social_desc'      => __( 'You can successfully connect to your Twilio account and log in from here.', 'dokan' ),
            'twilio_header'    => [
                'name'         => 'twilio_header',
                'type'         => 'html',
                'desc'         => sprintf(
                    /* translators: 1) Opening anchor tag, 2) Closing anchor tag */
                    __( 'Configure your gateway from %1$shere%2$s and fill the details below', 'dokan' ),
                    '<a target="_blank" href="https://www.twilio.com/">',
                    '</a>'
                ),
                'label'        => __( 'Twilio App Settings', 'dokan' ),
                'social_field' => true,
            ],
            'twilio_number'    => [
                'name'         => 'twilio_number',
                'label'        => __( 'From Number', 'dokan' ),
                'type'         => 'text',
                'tooltip'      => __( 'Type in the number to which recipients can respond to.', 'dokan' ),
                'social_field' => true,
            ],
            'twilio_username'  => [
                'name'         => 'twilio_username',
                'label'        => __( 'Account SID', 'dokan' ),
                'type'         => 'text',
                'tooltip'      => __( 'You can get it from https://www.twilio.com/ -> Create an Account -> Collect SID.', 'dokan' ),
                'social_field' => true,
            ],
            'twilio_pass'      => [
                'name'         => 'twilio_pass',
                'label'        => __( 'Auth Token', 'dokan' ),
                'type'         => 'text',
                'tooltip'      => __( 'You can get it from https://www.twilio.com/ -> Create an Account -> Collect Token.', 'dokan' ),
                'social_field' => true,
            ],
            'twilio_code_type' => [
                'name'         => 'twilio_code_type',
                'label'        => __( 'SMS Code Type', 'dokan' ),
                'type'         => 'radio',
                'options'      => [
                    'numeric'      => 'Numeric',
                    'alphanumeric' => 'Alphanumeric',
                ],
                'default'      => 'numeric',
                'social_field' => true,
            ],
        ],
    ];

    return $settings_fields;
}
