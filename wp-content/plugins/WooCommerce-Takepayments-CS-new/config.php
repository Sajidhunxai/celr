<?php
// $defaultArgs = array(
//     'merchantID' => '254776',
//     'signature' => 'JtITDAG7wV30xP3XZ3uB'
// );
// $dbrow = get_option( 'woocommerce_takepayments_settings', $defaultArgs );

// $merchantID = $dbrow['merchantID'];
// $merchantSig = $dbrow['signature'];

// delete_option('woocommerce_takepayments_settings');

return [
    'gateway_title'       => 'Takepayments Card Payments',
    'method_description'  => 'Pay securely via Credit / Debit Card with Takepayments. (Module Version 2.0.7)',
    'default_merchant_id' => '119837',
    'default_secret'      => '9GXwHNVC87VqsqNM',
    'default_gateway'     => 'https://gw1.tponlinepayments.com',
    'default_logo'        => 'logo.png',
    'default_merchant_country_code' => '826'
];
