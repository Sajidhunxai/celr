<?php

use WeDevs\DokanPro\Modules\MangoPay\Support\Helper;

defined( 'ABSPATH' ) || exit;

$form_fields = array(
    'enabled'                    => array(
        'title'	  => __( 'Enable/Disable', 'dokan' ),
        'type'	  => 'checkbox',
        'label'	  => __( 'Enable MangoPay Payment', 'dokan' ),
        'default' => 'no'
    ),
    'title'                      => array(
        'title'       => __( 'Title', 'dokan' ),
        'type'        => 'text',
        'class'       => 'input-text regular-input ',
        'description' => __( 'This controls the title which the user sees during checkout.', 'dokan' ),
        'desc_tip'    => true,
        'default'     => __( 'MangoPay', 'dokan' ),
    ),
    'description'                => array(
        'title'       => __( 'Description', 'dokan' ),
        'type'        => 'textarea',
        'description' => __( 'This controls the description which the user sees during checkout.', 'dokan' ),
        'desc_tip'    => true,
        'default'     => __( 'Pay via MangoPay', 'dokan' ),
    ),
    'api_details'                => array(
        'title'       => __( 'API Credentials', 'dokan' ),
        'type'        => 'title',
        'description' => wp_kses(
            sprintf(
                // translators: 1) help documentation link
                __( 'Your API credentials are a client ID and API key, which authenticate API requests from your account. You can collect these credentials from a REST API app in the Developer Dashboard. Visit %s for more information about getting your api details.', 'dokan' ),
                '<a href="https://mangopay.com/start" target="_blank">this link</a>'
            ),
            array(
                'a' => array(
                    'href'   => true,
                    'target' => true,
                ),
            )
        ),
    ),
    'sandbox_mode'               => array(
        'title'       => __( 'MangoPay Sandbox', 'dokan' ),
        'type'        => 'checkbox',
        'label'       => __( 'Enable MangoPay sandbox', 'dokan' ),
        'default'     => 'no',
        'description' => __( 'MangoPay sandbox can be used to test payments.', 'dokan' ),
    ),
    'client_id'                  => array(
        'title'       => __( 'Client ID', 'dokan' ),
        'type'        => 'password',
        'class'       => 'input-text regular-input ',
        'description' => __( 'Credential for the main MangoPay account', 'dokan' ),
        'default'     => '',
        'desc_tip'    => true,
        'placeholder' => __( 'Client ID', 'dokan' ),
    ),
    'api_key'                    => array(
        'title'       => __( 'API Key', 'dokan' ),
        'type'        => 'password',
        'class'       => 'input-text regular-input ',
        'description' => __( 'Credential for the main MangoPay account', 'dokan' ),
        'default'     => '',
        'desc_tip'    => true,
        'placeholder' => __( 'API Key', 'dokan' ),
    ),
    'sandbox_client_id'          => array(
        'title'       => __( 'Sandbox Client ID', 'dokan' ),
        'type'        => 'password',
        'class'       => 'input-text regular-input ',
        'description' => __( 'Credential for the MangoPay sandbox account', 'dokan' ),
        'default'     => '',
        'desc_tip'    => true,
        'placeholder' => __( 'Sandbox Client ID', 'dokan' ),
    ),
    'sandbox_api_key'            => array(
        'title'       => __( 'Sandbox API Key', 'dokan' ),
        'type'        => 'password',
        'class'       => 'input-text regular-input ',
        'description' => __( 'Credential for the MangoPay sandbox account', 'dokan' ),
        'default'     => '',
        'desc_tip'    => true,
        'placeholder' => __( 'Sandbox API Key', 'dokan' ),
    ),
    'webhook_key'                => array(
        'title'       => '',
        'type'        => 'hidden',
        'default'     => md5( time() ),
        'desc_tip'    => true,
    ),
    'payment_options'            => array(
        'title'       => __( 'Payment Options', 'dokan' ),
        'type'        => 'title',
        'description' => __( 'Configure the environment for payment to control how the customers will be able to pay.', 'dokan' ),
    ),
    'cards'                      => array(
        'title'       => __( 'Choose Available Credit Cards', 'dokan' ),
        'type'        => 'multiselect',
        'class'       => 'wc-enhanced-select',
        'default'     => array( 'CB_VISA_MASTERCARD' ),
        'options'     => Helper::get_available_card_types(),
        'description' => wp_kses(
            sprintf(
                // translators: 1) contact support link
                __( 'Payment types marked with asterisk(*) needs to be activated for your account. Please contact %s.', 'dokan' ),
                '<a href="https://support.mangopay.com/s/contactsupport" target="_blank">MangoPay</a>'
            ),
            array(
                'a' => array(
                    'href'   => true,
                    'target' => true,
                ),
            )
        ),
    ),
    'direct_pay'                 => array(
        'title'       => __( 'Choose Available Direct Payment Services', 'dokan' ),
        'type'        => 'multiselect',
        'class'       => 'wc-enhanced-select',
        'default'     => array(),
        'options'     => Helper::get_available_direct_payment_types(),
        'description' => wp_kses(
            sprintf(
                // translators: 1) contact support link
                __( 'Payment types marked with asterisk(*) needs to be activated for your account. Please contact %s.', 'dokan' ),
                '<a href="https://support.mangopay.com/s/contactsupport" target="_blank">MangoPay</a>'
            ),
            array(
                'a' => array(
                    'href'   => true,
                    'target' => true,
                ),
            )
        ),
    ),
    'saved_cards'                => array(
        'title'       => __( 'Saved Cards', 'dokan' ),
        'type'        => 'checkbox',
        'label'       => __( 'Enable saved cards', 'dokan' ),
        'description' => __( 'If enabled, customers will be able to save cards during checkout. Card data will be saved on MangoPay server, not on the store.', 'dokan' ),
        'desc_tip'    => true,
        'default'     => 'no',
    ),
    'platform_fees'              => array(
        'title'       => __( 'Platform Fees', 'dokan' ),
        'type'        => 'title',
        'description' => wp_kses(
            sprintf(
                __(
                    // translators: 1) pricing plan link 2) platform fees documentation
                    'MangoPay collects platform fees from the marketplace owner. That means, all platform fees will be collected from your commission. If you need information about the amount charged as platform fees, please see the %1$s here. Also, if you want to know how the platform fees are collected, please read their %2$s.',
                    'dokan'
                ),
                '<a href="https://www.mangopay.com/pricing/" target="_blank">pricing plan</a>',
                '<a href="https://docs.mangopay.com/guide/collecting-platform-fees" target="_blank">documentation</a>'
            ),
            array(
                'a' => array(
                    'href'   => true,
                    'target' => true,
                ),
            )
        ),
    ),
    'fund_transfers'             => array(
        'title'       => __( 'Fund Transfers and Payouts', 'dokan' ),
        'type'        => 'title',
        'description' => __( 'Configure how and when you want to transfer/payout funds to the vendors.', 'dokan' ),
    ),
    'disburse_mode'              => array(
        'title'	      => __( 'Transfer Funds', 'dokan' ),
        'type'	      => 'select',
        'class'       => 'wc-enhanced-select',
        'label'	      => __( 'Choose when you want to disburse funds to the vendors', 'dokan' ),
        'default'     => 'no',
        'description' => __(
            'You can choose when whether you want to transfer funds to vendors after the order is completed, or immediately after the payment is completed, or delay the transfer even if the order is processing or completed.',
            'dokan'
        ),
        'options'     => array(
            'ON_ORDER_PROCESSING' => 'On payment completed',
            'ON_ORDER_COMPLETED'  => 'On order completed',
            'DELAYED'             => 'Delayed',
        ),
    ),
    'disbursement_delay_period'  => array(
        'title'             => __( 'Delay Period (Days)', 'dokan' ),
        'type'              => 'number',
        'class'             => 'input-text regular-input ',
        'description'       => __( 'Specify after how many days funds will be disburse to corresponding vendor. The funcds will be transferred to vendors after this period automatically', 'dokan' ),
        'default'           => '14',
        'desc_tip'          => true,
        'placeholder'       => __( 'Delay Period', 'dokan' ),
        'custom_attributes' => array(
            'min' => 1,
        ),
    ),
    'instant_payout'             => array(
        'title'	      => __( 'Payout Mode', 'dokan' ),
        'type'	      => 'checkbox',
        'label'	      => __( 'Enable instant payout mode', 'dokan' ),
        'default'     => 'no',
        'description' => wp_kses(
            sprintf(
                __(
                    // translators: 1) instant payout doc link
                    'Enable instant payout so that the payout can be processed within 25 seconds, whereas, the standard payouts get processed within 48 hours. This feature is limited and requires some prerequisites to be fulfiled. Please check out the requirements %s.',
                    'dokan'
                ),
                '<a href="https://docs.mangopay.com/guide/instant-payment-payout" target="_blank">here</a>'
            ),
            array(
                'a' => array(
                    'href'   => true,
                    'target' => true,
                ),
            )
        ),
    ),
    'user_types'                 => array(
        'title'       => __( 'Types and Requirements of Vendors', 'dokan' ),
        'type'        => 'title',
        'description' => __( 'Configure the types of vendors. It will define the types of vendors who are going to use the gateway. This way you can bound the types of vendors, according to which the verification process will be applied.', 'dokan' ),
    ),
    'default_vendor_status'      => array(
        'title'         => __( 'Type of Vendors', 'dokan' ),
        'type'          => 'select',
        'class'         => 'wc-enhanced-select',
        'description'   => __( 'All the vendors are bound to this type and they will be verified according to this. Choose \'Either\' if no restriction is needed.', 'dokan' ),
        'default'       => 'either',
        'desc_tip'      => true,
        'options'       => array(
            'NATURAL' => __( 'Individuals', 'dokan' ),
            'LEGAL'   => __( 'Business', 'dokan' ),
            'EITHER'  => __( 'Either', 'dokan' ),
        ),
    ),
    'default_business_type'      => array(
        'title'         => __( 'Business Requirement', 'dokan' ),
        'type'          => 'select',
        'class'         => 'wc-enhanced-select',
        'description'   => __(
            'All the business are bound to this type and the verification process will be applied accordingly. Choose \'Any\' if no restriction is needed.',
            'dokan'
        ),
        'desc_tip'      => true,
        'default'       => 'either',
        'options'       => array(
            'ORGANIZATION' => __( 'Organizations', 'dokan' ),
            'SOLETRADER'   => __( 'Soletraders', 'dokan' ),
            'BUSINESS'     => __( 'Businesses', 'dokan' ),
            'EITHER'       => __( 'Any', 'dokan' ),
        ),
    ),
    'advanced'                   => array(
        'title'       => __( 'Advanced Settings', 'dokan' ),
        'type'        => 'title',
        'description' => __( 'Set up advanced settings to manage some extra options.', 'dokan' ),
    ),
    'notice_on_vendor_dashboard' => array(
        'title'       => __( 'Display Notice to Non-connected Sellers', 'dokan' ),
        'label'       => __(
            'If checked, non-connected sellers will see a notice to connect their MangoPay account on their vendor dashboard.',
            'dokan'
        ),
        'type'        => 'checkbox',
        'description' => __(
            'If this is enabled, non-connected sellers will see a notice to connect their MangoPay account on their vendor dashboard.',
            'dokan'
        ),
        'default'     => 'no',
        'desc_tip'    => true,
    ),
    'announcement_to_sellers'    => array(
        'title'       => __( 'Send Announcement to Non-connected Sellers', 'dokan' ),
        'label'       => __( 'If checked, non-connected sellers will receive announcement notice to connect their MangoPay account. ', 'dokan' ),
        'type'        => 'checkbox',
        'description' => __(
            'If this is enabled non-connected sellers will receive announcement notice to connect their MangoPay account.',
            'dokan'
        ),
        'default'     => 'no',
        'desc_tip'    => true,
    ),
    'notice_interval'            => array(
        'title'             => __( 'Announcement Interval', 'dokan' ),
        'type'              => 'number',
        'description'       => __(
            'If Send Announcement to Connect Seller setting is enabled, non-connected sellers will receive announcement notice to connect their MangoPay account once in a week by default. You can control notice display interval from here. The interval value will be considered in days.',
            'dokan'
        ),
        'default'           => '7',
        'desc_tip'          => false,
        'custom_attributes' => array(
            'min' => 1,
        ),
    ),
);

return apply_filters( 'dokan_mangopay_gateway_admin_settings_fields', $form_fields );
