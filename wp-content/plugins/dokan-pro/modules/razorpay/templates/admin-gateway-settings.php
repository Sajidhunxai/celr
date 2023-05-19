<?php

// use WeDevs\DokanPro\Modules\Razorpay\Helper;

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

return apply_filters(
    'dokan_razorpay_admin_gateway_settings', [
        'enabled' => [
            'title'       => __( 'Enable/Disable', 'dokan' ),
            'type'        => 'checkbox',
            'label'       => __( 'Enable Dokan Razorpay', 'dokan' ),
            'default'     => 'no',
        ],
        'title' => [
            'title'       => __( 'Title', 'dokan' ),
            'type'        => 'text',
            'class'       => 'input-text regular-input ',
            'description' => __( 'This controls the title which the user sees during checkout.', 'dokan' ),
            'default'     => __( 'Razorpay', 'dokan' ),
            'desc_tip'    => true,
        ],
        'description' => [
            'title'       => __( 'Description', 'dokan' ),
            'type'        => 'textarea',
            'description' => __( 'This controls the description which the user sees during checkout.', 'dokan' ),
            'default'     => __( 'Pay securely by Credit or Debit card or Internet Banking through Razorpay.', 'dokan' ),
        ],
        'api_details' => [
            'title'       => __( 'API credentials', 'dokan' ),
            'type'        => 'title',
            'description' => wp_kses(
                sprintf(
                    /* translators: 1: API Keys URL */
                    __( 'Your API credentials are a API Key and secret, which authenticate API requests from your account. You get these credentials from a REST API app in the Developer Dashboard. Visit <a href="%s">this link</a> for more information about getting your api details.', 'dokan' ),
                    'https://dashboard.razorpay.com/app/keys'
                ),
                [
                    'a'         => [
                        'href'   => true,
                        'target' => true,
                    ],
                ]
            ),
        ],
        'test_mode' => [
            'title'       => __( 'Razorpay sandbox', 'dokan' ),
            'type'        => 'checkbox',
            'label'       => __( 'Enable Razorpay sandbox', 'dokan' ),
            'default'     => 'no',
            /* translators: 1: Razorpay dashboard developer url */
            'description' => sprintf( __( 'Razorpay sandbox can be used to test payments. Sign up for a developer account <a href="%s">here</a>.', 'dokan' ), 'https://dashboard.razorpay.com/' ),
        ],
        'key_id' => [
            'title'       => __( 'Key ID', 'dokan' ),
            'type'        => 'text',
            'class'       => 'input-text regular-input ',
            'description' => wp_kses(
                sprintf(
                    /* translators: 1: Razorpay API Key Link */
                    __( 'The key Id can be generated from Razorpay Dashboard --> Settings <a href="%1$s" target="_blank">here</a>.', 'dokan' ),
                    'https://dashboard.razorpay.com/app/keys'
                ),
                [
                    'a'      => [
                        'href'   => true,
                        'target' => true,
                    ],
                ]
            ),
            'placeholder' => 'rzp_xxx',
        ],
        'key_secret' => [
            'title'       => __( 'Key Secret', 'dokan' ),
            'type'        => 'password',
            'class'       => 'input-text regular-input ',
            'description' => wp_kses(
                sprintf(
                    /* translators: 1: Razorpay API Key Link */
                    __( 'The key secret can be generated from Razorpay Dashboard --> Settings <a href="%1$s" target="_blank">here</a>.', 'dokan' ),
                    'https://dashboard.razorpay.com/app/keys'
                ),
                [
                    'a'      => [
                        'href'   => true,
                        'target' => true,
                    ],
                ]
            ),
            'placeholder' => '',
        ],
        'test_key_id' => [
            'title'       => __( 'Test Key ID', 'dokan' ),
            'type'        => 'text',
            'class'       => 'input-text regular-input ',
            'description' => wp_kses(
                sprintf(
                    /* translators: 1: Razorpay API Key Link */
                    __( 'The Test key Id can be generated from Razorpay Dashboard --> Settings <a href="%1$s" target="_blank">here</a>.', 'dokan' ),
                    'https://dashboard.razorpay.com/app/keys'
                ),
                [
                    'a'      => [
                        'href'   => true,
                        'target' => true,
                    ],
                ]
            ),
            'placeholder' => 'rzp_xxx',
        ],
        'test_key_secret' => [
            'title'       => __( 'Test Key Secret', 'dokan' ),
            'type'        => 'password',
            'class'       => 'input-text regular-input ',
            'description' => wp_kses(
                sprintf(
                    /* translators: 1: Razorpay API Key Link */
                    __( 'The Test key secret can be generated from Razorpay Dashboard --> Settings <a href="%1$s" target="_blank">here</a>.', 'dokan' ),
                    'https://dashboard.razorpay.com/app/keys'
                ),
                [
                    'a'      => [
                        'href'   => true,
                        'target' => true,
                    ],
                ]
            ),
            'placeholder' => '',
        ],
        'enable_route_transfer' => [
            'title'       => __( 'Enable Route Transfer', 'dokan' ),
            'type'        => 'title',
            'description' => wp_kses(
                sprintf(
                    /* translators: 1: Razorpay Route Payment link */
                    __( 'To make split payment enabled, you must <strong>Activate Route Transfer</strong> from Razorpay Dashboard <a href="%1$s" target="_blank">here</a>.', 'dokan' ),
                    'https://dashboard.razorpay.com/app/route/payments'
                ),
                [
                    'a'      => [
                        'href'   => true,
                        'target' => true,
                    ],
                    'strong' => [],
                ]
            ),
        ],
        'disbursement_mode' => [
            'title'       => __( 'Disbursement Mode', 'dokan' ),
            'type'        => 'select',
            'class'       => 'wc-enhanced-select',
            'description' => __( 'Choose whether you wish to disburse funds to the vendors immediately or hold the funds. Holding funds gives you time to conduct additional vetting or enforce other platform-specific business logic.', 'dokan' ),
            'default'     => 'INSTANT',
            'desc_tip'    => true,
            'options'     => [
                'INSTANT'           => __( 'Immediate', 'dokan' ),
                'ON_ORDER_COMPLETE' => __( 'On Order Complete', 'dokan' ),
                'DELAYED'           => __( 'Delayed', 'dokan' ),
            ],
        ],
        'razorpay_disbursement_delay_period' => [
            'title'       => __( 'Disbursement Delay Period', 'dokan' ),
            'type'        => 'number',
            'class'       => 'input-text regular-input ',
            'description' => __( 'Specify after how many days funds will be disbursed to the corresponding vendor. No Maximum holding days. After given days, fund will be disbursed to corresponding vendor.', 'dokan' ),
            'default'     => '7',
            'desc_tip'    => true,
            'placeholder' => __( 'Disbursement Delay Period', 'dokan' ),
            'custom_attributes' => [
                'min' => 1,
            ],
        ],
        'seller_pays_the_processing_fee' => [
            'title'       => __( 'Seller pays the processing fee', 'dokan' ),
            'label'       => __( 'If unchecked, Admin/Site Owner will pay the Razorpay processing fee instead of Seller.', 'dokan' ),
            'type'        => 'checkbox',
            'description' => __( 'By default Seller pays the Razorpay processing fee.', 'dokan' ),
            'default'     => 'yes',
            'desc_tip'    => true,
        ],
        'display_notice_on_vendor_dashboard' => [
            'title'       => __( 'Display Notice to Connect Seller', 'dokan' ),
            'label'       => __( 'If checked, non-connected sellers will see a notice to connect their Razorpay account on their vendor dashboard.', 'dokan' ),
            'type'        => 'checkbox',
            'description' => __( 'If this is enabled, non-connected sellers will see a notice to connect their Razorpay account on their vendor dashboard.', 'dokan' ),
            'default'     => 'no',
            'desc_tip'    => true,
        ],
        'display_notice_to_non_connected_sellers' => [
            'title'       => __( 'Send Announcement to Connect Seller', 'dokan' ),
            'label'       => __( 'If checked, non-connected sellers will receive announcement notice to connect their Razorpay account. ', 'dokan' ),
            'type'        => 'checkbox',
            'description' => __( 'If this is enabled non-connected sellers will receive announcement notice to connect their Razorpay account once in a week by default.', 'dokan' ),
            'default'     => 'no',
            'desc_tip'    => true,
        ],
        'display_notice_interval' => [
            'title'       => __( 'Send Announcement Interval', 'dokan' ),
            'type'        => 'number',
            'description' => __( 'If Send Announcement to Connect Seller setting is enabled, non-connected sellers will receive announcement notice to connect their Razorpay account once in a week by default. You can control notice display interval from here.', 'dokan' ),
            'default'     => '7',
            'desc_tip'    => false,
            'custom_attributes' => [
                'min' => 1,
            ],
        ],

        // 'webhook_url' => [
        //     'title'       => __( 'Webhook URL', 'dokan' ),
        //     'type'        => 'title',
        //     'description' => wp_kses(
        //         sprintf(
        //             /* translators: 1: Webhook URL dor dokan razorpay 2: Razorpay webhook setup dashboard link */
        //             __( 'Webhook URL will be set <strong>automatically</strong> in your application settings with required events after you provide <strong>correct API information</strong>. You don\'t have to setup webhook url manually. Only make sure webhook url is available to <code>%1$s</code> in your Razorpay <a href="%2$s" target="_blank">application settings</a>.', 'dokan' ),
        //             Helper::get_webhook_url(),
        //             'https://dashboard.razorpay.com/app/webhooks'
        //         ),
        //         [
        //             'a'         => [
        //                 'href'   => true,
        //                 'target' => true,
        //             ],
        //             'code'      => [],
        //             'strong'    => [],
        //         ]
        //     ),
        // ],
        // 'webhook_secret' => [
        //     'title'       => __( 'Webhook Secret', 'dokan' ),
        //     'type'        => 'password',
        //     'description' => wp_kses(
        //         sprintf(
        //             /* translators: 1: Webhook Setup URL */
        //             __( 'Webhook secret is used for webhook signature verification. This has to match the one added <a href="%s" target="_blank">here</a>', 'dokan' ),
        //             'https: //dashboard.razorpay.com/#/app/webhooks'
        //         ),
        //         [
        //             'a'         => [
        //                 'href'   => true,
        //                 'target' => true,
        //             ],
        //         ]
        //     ),
        //     'default'     => '',
        // ],
    ]
);
