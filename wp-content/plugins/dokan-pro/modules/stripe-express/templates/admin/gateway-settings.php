<?php

defined( 'ABSPATH' ) || exit;

use WeDevs\DokanPro\Modules\StripeExpress\Support\Helper;

$form_fields = [
    'enabled'                          => [
        'title'       => __( 'Enable/Disable', 'dokan' ),
        'label'       => __( 'Enable Stripe Express', 'dokan' ),
        'type'        => 'checkbox',
        'description' => '',
        'default'     => 'no',
    ],
    'title'                            => [
        'title'       => __( 'Title', 'dokan' ),
        'type'        => 'text',
        'description' => __( 'This controls the title which the user sees during checkout. This title will be shown only when multiple payment methods are enabled for Stripe Express', 'dokan' ),
        'default'     => __( 'Stripe Express', 'dokan' ),
        'desc_tip'    => true,
    ],
    'description'                      => [
        'title'       => __( 'Description', 'dokan' ),
        'type'        => 'textarea',
        'description' => __( 'This controls the description which the user sees during checkout.', 'dokan' ),
        'default'     => __( 'Pay with your credit card via Stripe.', 'dokan' ),
        'desc_tip'    => true,
    ],
    'api_details'                      => [
        'title'       => __( 'API Credentials', 'dokan' ),
        'type'        => 'title',
        'description' => Helper::get_api_keys_description(),
    ],
    'testmode'                         => [
        'title'       => __( 'Test Mode', 'dokan' ),
        'type'        => 'checkbox',
        'label'       => __( 'Enable Stripe Test Mode', 'dokan' ),
        'default'     => 'no',
        'description' => __( 'Stripe test mode can be used to test payments.', 'dokan' ),
    ],
    'publishable_key'                  => [
        'title'       => __( 'Publishable Key', 'dokan' ),
        'type'        => 'password',
        'class'       => 'input-text regular-input ',
        'description' => __( 'Publishable key for Stripe', 'dokan' ),
        'default'     => '',
        'desc_tip'    => true,
        'placeholder' => __( 'Publishable Key', 'dokan' ),
    ],
    'secret_key'                       => [
        'title'       => __( 'Secret Key', 'dokan' ),
        'type'        => 'password',
        'class'       => 'input-text regular-input ',
        'description' => __( 'Secret key for Stripe', 'dokan' ),
        'default'     => '',
        'desc_tip'    => true,
        'placeholder' => __( 'Secret key', 'dokan' ),
    ],
    'test_publishable_key'             => [
        'title'       => __( 'Test Publishable Key', 'dokan' ),
        'type'        => 'password',
        'class'       => 'input-text regular-input ',
        'description' => __( 'Test Publishable key for Stripe', 'dokan' ),
        'default'     => '',
        'desc_tip'    => true,
        'placeholder' => __( 'Test Publishable Key', 'dokan' ),
    ],
    'test_secret_key'                  => [
        'title'       => __( 'Test Secret Key', 'dokan' ),
        'type'        => 'password',
        'class'       => 'input-text regular-input ',
        'description' => __( 'Test Secret key for Stripe', 'dokan' ),
        'default'     => '',
        'desc_tip'    => true,
        'placeholder' => __( 'Test Secret key', 'dokan' ),
    ],
    'webhook'                          => [
        'title'       => __( 'Webhook Endpoints', 'dokan' ),
        'type'        => 'title',
        'description' => Helper::get_webhook_description(),
    ],
    'webhook_key'                      => [
        'title'       => __( 'Webhook Secret', 'dokan' ),
        'type'        => 'password',
        'description' => __( 'Get your webhook signing secret from the webhooks section in your stripe account.', 'dokan' ),
        'default'     => '',
        'desc_tip'    => true,
    ],
    'test_webhook_key'                 => [
        'title'       => __( 'Test Webhook Secret', 'dokan' ),
        'type'        => 'password',
        'description' => __( 'Get your webhook signing secret from the webhooks section in your stripe account.', 'dokan' ),
        'default'     => '',
        'desc_tip'    => true,
    ],
    'payment_options'                  => [
        'title'       => __( 'Payment and Disbursement', 'dokan' ),
        'type'        => 'title',
        'description' => __( 'Manage the payment and fund disbursements.', 'dokan' ),
    ],
    'enabled_payment_methods'          => [
        'title'       => __( 'Choose Payment Methods', 'dokan' ),
        'type'        => 'multiselect',
        'class'       => 'wc-enhanced-select',
        'default'     => [ 'card' ],
        'options'     => Helper::get_payment_methods_dropdown(),
        'description' => __( 'Selected payment methods will be appeared on checkout if requiorements are fulfilled.', 'dokan' ),
    ],
    'sellers_pay_processing_fee'       => [
        'title'       => __( 'Take Processing Fees from Sellers', 'dokan' ),
        'label'       => __( 'If activated, Sellers will pay the Stripe processing fee instead of Admin/Site Owner.', 'dokan' ),
        'type'        => 'checkbox',
        'description' => __( 'By default Admin/Site Owner pays the Stripe processing fee.', 'dokan' ),
        'default'     => 'no',
    ],
    'saved_cards'                      => [
        'title'       => __( 'Saved Cards', 'dokan' ),
        'type'        => 'checkbox',
        'label'       => __( 'Enable payment via saved cards', 'dokan' ),
        'description' => __( 'If enabled, customers will be able to save cards during checkout. Card data will be saved on Stripe server, not on the store.', 'dokan' ),
        'default'     => 'no',
    ],
    'capture'                          => [
        'title'       => __( 'Capture Payments Manually', 'dokan' ),
        'type'        => 'checkbox',
        'label'       => __( 'Issue an authorization on checkout, and capture later', 'dokan' ),
        'description' => __( 'Only cards support manual capture. When enabled, all other payment methods will be hidden from checkout. Charge must be captured on the order details screen within 7 days of authorization, otherwise the authorization and order will be canceled.', 'dokan' ),
        'default'     => 'no',
    ],
    'disburse_mode'                    => [
        'title'       => __( 'Disburse Funds', 'dokan' ),
        'type'        => 'select',
        'class'       => 'wc-enhanced-select',
        'label'       => __( 'Choose when you want to disburse funds to the vendors', 'dokan' ),
        'default'     => 'no',
        'description' => __(
            'You can choose when whether you want to transfer funds to vendors after the order is completed, or immediately after the payment is completed, or delay the transfer even if the order is processing or completed.',
            'dokan'
        ),
        'options'     => [
            'ON_ORDER_PROCESSING' => __( 'On payment completed', 'dokan' ),
            'ON_ORDER_COMPLETED'  => __( 'On order completed', 'dokan' ),
            'DELAYED'             => __( 'Delayed', 'dokan' ),
        ],
    ],
    'disbursement_delay_period'        => [
        'title'             => __( 'Delay Period (Days)', 'dokan' ),
        'type'              => 'number',
        'class'             => 'input-text regular-input ',
        'description'       => __( 'Specify after how many days funds will be disburse to corresponding vendor. The funcds will be transferred to vendors after this period automatically', 'dokan' ),
        'default'           => '14',
        'desc_tip'          => true,
        'placeholder'       => __( 'Delay Period', 'dokan' ),
        'custom_attributes' => [
            'min' => 1,
        ],
    ],
    'statement_descriptor'             => [
        'title'       => __( 'Customer Bank Statement', 'dokan' ),
        'type'        => 'text',
        'class'       => 'input-text regular-input ',
        'description' => __( 'Enter the name your customers will see on their transactions. Use a recognizable name – e.g. the legal entity name or website address–to avoid potential disputes and chargebacks.', 'dokan' ),
    ],
    'appearance'                       => [
        'title'       => __( 'Payment Element Appearance', 'dokan' ),
        'type'        => 'title',
        'description' => __( 'Customize theme and appearance of Payment Element', 'dokan' ),
    ],
    'element_theme'                    => [
        'title'       => __( 'Theme', 'dokan' ),
        'type'        => 'select',
        'description' => __( 'Select the theme you would like to choose for Stripe element.', 'dokan' ),
        'default'     => 'stripe',
        'desc_tip'    => true,
        'options'     => [
            'stripe'    => __( 'Light', 'dokan' ),
            'flat'      => __( 'Flat', 'dokan' ),
            'night'     => __( 'Dark', 'dokan' ),
            'dark_blue' => __( 'Dark Blue', 'dokan' ),
            'none'      => __( 'None', 'dokan' ),
        ],
    ],
    'payment_request_options'          => [
        'title'       => __( 'Payment Request Options (Apple Pay / Google Pay)', 'dokan' ),
        'type'        => 'title',
        'description' => sprintf(
            /* translators: 1) br tag 2) Stripe anchor tag 3) Apple anchor tag 4) Stripe dashboard opening anchor tag 5) Stripe dashboard closing anchor tag */
            __( 'Enable payment via Apple Pay and Google Pay. %1$sBy using Apple Pay, you agree to %2$s and %3$s\'s terms of service. (Apple Pay domain verification is performed automatically in live mode; configuration can be found on the %4$sStripe dashboard%5$s.)', 'dokan' ),
            '<br />',
            '<a href="https://stripe.com/apple-pay/legal" target="_blank">Stripe</a>',
            '<a href="https://developer.apple.com/apple-pay/acceptable-use-guidelines-for-websites/" target="_blank">Apple</a>',
            '<a href="https://dashboard.stripe.com/settings/payments/apple_pay" target="_blank">',
            '</a>'
        ),
    ],
    'payment_request'                  => [
        'title'       => __( 'Payment Request Buttons', 'dokan' ),
        'label'       => __( 'Enable Payment Request Buttons. (Apple Pay/Google Pay)', 'dokan' ),
        'type'        => 'checkbox',
        'description' => __( 'If enabled, users will be able to pay using Apple Pay or Chrome Payment Request if supported by the browser. Depending on the web browser and wallet configurations, your customers will see either Apple Pay or Google Pay, but not both.', 'dokan' ),
        'default'     => 'yes',
        'desc_tip'    => true,
    ],
    'payment_request_button_type'      => [
        'title'       => __( 'Button Type', 'dokan' ),
        'type'        => 'select',
        'description' => __( 'Select the button type you would like to show.', 'dokan' ),
        'default'     => 'default',
        'desc_tip'    => true,
        'options'     => [
            'default' => __( 'Only Icon', 'dokan' ),
            'buy'     => __( 'Buy', 'dokan' ),
            'donate'  => __( 'Donate', 'dokan' ),
            'book'    => __( 'Book', 'dokan' ),
        ],
    ],
    'payment_request_button_theme'     => [
        'title'       => __( 'Button Theme', 'dokan' ),
        'type'        => 'select',
        'description' => __( 'Select the button theme you would like to show.', 'dokan' ),
        'default'     => 'dark',
        'desc_tip'    => true,
        'options'     => [
            'dark'          => __( 'Dark', 'dokan' ),
            'light'         => __( 'Light', 'dokan' ),
            'light-outline' => __( 'Light-Outline', 'dokan' ),
        ],
    ],
    'payment_request_button_locations' => [
        'title'             => __( 'Button Locations', 'dokan' ),
        'type'              => 'multiselect',
        'description'       => __( 'Select where you would like Payment Request Buttons to be displayed', 'dokan' ),
        'desc_tip'          => true,
        'class'             => 'wc-enhanced-select',
        'options'           => [
            'product' => __( 'Product', 'dokan' ),
            'cart'    => __( 'Cart', 'dokan' ),
        ],
        'default'           => [ 'product' ],
        'custom_attributes' => [
            'data-placeholder' => __( 'Select pages', 'dokan' ),
        ],
    ],
    'payment_request_button_size'      => [
        'title'       => __( 'Button Size', 'dokan' ),
        'type'        => 'select',
        'description' => __( 'Select the size of the button.', 'dokan' ),
        'default'     => 'default',
        'desc_tip'    => true,
        'options'     => [
            'default' => __( 'Default (40px)', 'dokan' ),
            'medium'  => __( 'Medium (48px)', 'dokan' ),
            'large'   => __( 'Large (56px)', 'dokan' ),
        ],
    ],
    'cross_border_transfer_options'    => [
        'title'       => __( 'Cross-border Transfers and Onboarding', 'dokan' ),
        'type'        => 'title',
        'description' => wp_kses(
            __( 'Transfer options outside the marketplace\'s country under European Union or SEPA.', 'dokan' ),
            [
                'a' => [
                    'href'   => true,
                    'target' => true,
                ],
            ]
        )
    ],
    'cross_border_transfer'            => [
        'title'       => __( 'Cross-border Transfer', 'dokan' ),
        'label'       => __( 'Enable onboarding for vendors outside the country/region of the marketplace inside EU and SEPA.', 'dokan' ),
        'type'        => 'checkbox',
        'description' => __( 'If enabled and if the marketplace is inside European Union or SEPA, vendors will be able to choose their country among the transfer supported countries during signing up. The available countries will be determined by Stripe based on the country of the marketplace.', 'dokan' ),
        'default'     => 'no',
    ],
    'restricted_countries'             => [
        'title'       => __( 'Restrict Countries/Regions', 'dokan' ),
        'type'        => 'multiselect',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'Select countries from where vendors will not be able to onboard.', 'dokan' ),
        'options'     => [],
    ],
    'advanced'                         => [
        'title'       => __( 'Advanced Settings', 'dokan' ),
        'type'        => 'title',
        'description' => __( 'Set up advanced settings to manage some extra options.', 'dokan' ),
    ],
    'notice_on_vendor_dashboard'       => [
        'title' => __( 'Display Notice to Non-connected Sellers', 'dokan' ),
        'label' => __(
            'If checked, non-connected sellers will see a notice to sign up for a Stripe Express account on their vendor dashboard.',
            'dokan'
        ),
        'type'        => 'checkbox',
        'description' => __(
            'If this is enabled, non-connected sellers will see a notice to sign up for a Stripe Express account on their vendor dashboard.',
            'dokan'
        ),
        'default'  => 'no',
        'desc_tip' => true,
    ],
    'announcement_to_sellers'          => [
        'title'       => __( 'Send Announcement to Non-connected Sellers', 'dokan' ),
        'label'       => __( 'If checked, non-connected sellers will receive announcement notice to sign up for a Stripe Express account. ', 'dokan' ),
        'type'        => 'checkbox',
        'description' => __(
            'If this is enabled non-connected sellers will receive announcement notice to sign up for a Stripe Express account.',
            'dokan'
        ),
        'default'     => 'no',
        'desc_tip'    => true,
    ],
    'notice_interval'                  => [
        'title'             => __( 'Announcement Interval', 'dokan' ),
        'type'              => 'number',
        'description'       => __(
            'If Send Announcement to Connect Seller setting is enabled, non-connected sellers will receive announcement notice to sign up for a Stripe Express account once in a week by default. You can control notice display interval from here. The interval value will be considered in days.',
            'dokan'
        ),
        'default'           => '7',
        'desc_tip'          => false,
        'custom_attributes' => [
            'min' => 1,
        ],
    ],
    'debug'                            => [
        'title'       => __( 'Debug Log', 'dokan' ),
        'type'        => 'checkbox',
        'label'       => __( 'Enable logging', 'dokan' ),
        'default'     => 'no',
        /* translators: %s: URL */
        'description' => sprintf(
            __( 'Log gateway events such as Webhook requests, Payment oprations etc. inside %s. Note: this may log personal information. We recommend using this for debugging purposes only and deleting the logs when finished.', 'dokan' ),
            '<code>' . \WC_Log_Handler_File::get_log_file_path( 'dokan' ) . '</code>'
        ),
    ],
];

if ( Helper::is_api_ready() ) {
    $supported_countries = Helper::get_supported_countries_for_vendors( true );
    if ( ! empty( $supported_countries ) ) {
        $form_fields['restricted_countries']['options'] = $supported_countries;
    } else {
        unset( $form_fields['restricted_countries'] );
    }
} else {
    $form_fields['restricted_countries']['custom_attributes'] = [ 'disabled' => 'disabled' ];
}

return apply_filters( 'dokan_stripe_express_admin_settings_fields', $form_fields );
