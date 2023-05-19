<?php

namespace WeDevs\DokanPro\Modules\MangoPay\Support;

defined( 'ABSPATH' ) || exit; // Exit if called directly

use WC_Product;

class Helper {

    /**
     * Gateway ID for MangoPay.
     *
     * @since 3.5.0
     *
     * @var string
     */
    private static $gateway_id = 'dokan_mangopay';

    /**
     * Currencies that are supported by MangoPay.
     *
     * @since 3.5.0
     *
     * @var array
     */
    private static $supported_currencies = array(
        'EUR',
        'GBP',
        'USD',
        'CHF',
        'NOK',
        'PLN',
        'SEK',
        'DKK',
        'CAD',
        'ZAR',
    );

    /**
     * Supported locales for MangoPay.
     *
     * @since 3.5.0
     *
     * @see: https://docs.mangopay.com/api-references/payins/payins-card-web/
     *
     * @var array
     */
    private static $supported_locales = array(
        'de',
        'en',
        'da',
        'es',
        'et',
        'fi',
        'fr',
        'el',
        'hu',
        'it',
        'nl',
        'no',
        'pl',
        'pt',
        'sk',
        'sv',
        'cs',
    );

    /**
     * Credit card types that are available in MangoPay.
     *
     * @since 3.5.0
     *
     * @var array
     */
    private static $available_card_types = array(
        'CB_VISA_MASTERCARD' => 'CB/Visa/Mastercard',
        'MAESTRO'            => 'Maestro*',
        'BCMC'               => 'Bancontact/Mister Cash',
        'P24'                => 'Przelewy24*',
        'DINERS'             => 'Diners*',
        'PAYLIB'             => 'PayLib',
        'IDEAL'              => 'iDeal*',
        'MASTERPASS'         => 'MasterPass*',
        'BANK_WIRE'          => 'Bankwire Direct*', // This is not actually a card
    );

    /**
     * Direct payment types that are available in MangoPay.
     *
     * @since 3.5.0
     *
     * @var array
     */
    private static $available_direct_payment_types = array(
        'SOFORT'  => 'Sofort*',
        'GIROPAY' => 'Giropay*',
    );

    /**
     * Default card types for MangoPay.
     *
     * @since 3.5.0
     *
     * @var array
     */
    private static $default_card_types = array(
        'CB_VISA_MASTERCARD',
        'BCMC',
        'PAYLIB',
    );

    /**
     * Cards that support 3ds.
     *
     * @since 3.5.0
     *
     * @var array
     */
    private static $threeds_cards = array(
        'CB_VISA_MASTERCARD',
        'MAESTRO',
        'BCMC',
    );

    /**
     * Countries that do not require postcodes in MangoPay.
     *
     * @since 3.5.0
     *
     * @var array
     */
    private static $no_postcode_countries = array(
        'AO',
        'AG',
        'AW',
        'BS',
        'BZ',
        'BJ',
        'BW',
        'BF',
        'BI',
        'CM',
        'CF',
        'KM',
        'CG',
        'CD',
        'CK',
        'CI',
        'DJ',
        'DM',
        'GQ',
        'ER',
        'FJ',
        'TF',
        'GM',
        'GH',
        'GD',
        'GN',
        'GY',
        'HK',
        'IE',
        'JM',
        'KE',
        'KI',
        'MO',
        'MW',
        'ML',
        'MR',
        'MU',
        'MS',
        'NR',
        'AN',
        'NU',
        'KP',
        'PA',
        'QA',
        'RW',
        'KN',
        'LC',
        'ST',
        'SA',
        'SC',
        'SL',
        'SB',
        'SO',
        'ZA',
        'SR',
        'SY',
        'TZ',
        'TL',
        'TK',
        'TO',
        'TT',
        'TV',
        'UG',
        'AE',
        'VU',
        'YE',
        'ZW',
    );

    /**
     * Countries from where users are not allowed to be created in Mangopay.
     *
     * @since 3.5.0
     *
     * @var array
     */
    private static $restricted_residences = array(
        'AF',
        'AL',
        'BS',
        'BB',
        'BW',
        'BF',
        'KH',
        'KY',
        'ET',
        'GH',
        'HT',
        'IR',
        'IQ',
        'JM',
        'MM',
        'NI',
        'KP',
        'UG',
        'PK',
        'PH',
        'SN',
        'SS',
        'SY',
        'TT',
        'VU',
        'YE',
        'ZW',
    );

    /**
     * Bank account types.
     *
     * @since 3.5.0
     *
     * @see: https://docs.mangopay.com/api-references/bank-accounts/
     *
     * @var array
     */
    private static $bank_account_types = array(
        'IBAN'  => 'IBAN',
        'GB'    => 'GB',
        'US'    => 'US',
        'CA'    => 'CA',
        'OTHER' => 'Others',
    );

    /**
     * Retrieves bank account types
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_bank_account_types() {
        return self::$bank_account_types;
    }

    /**
     * Retrieves restricted residences list.
     *
     * Users from these countries are not allowed to
     * be created in Mngopay.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_restricted_residences() {
        return self::$restricted_residences;
    }

    /**
     * Retrieves appropriate locale
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_locale() {
        $locale = 'EN';

        // Get the minor part of site locale
        list( $locale_minor ) = preg_split( '/_/', get_locale() );
        if ( in_array( trim( $locale_minor ), self::$supported_locales, true ) ) {
            $locale = strtoupper( $locale_minor );
        }

        return $locale;
    }

    /**
     * Retrieves the supported currencies.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_supported_currencies() {
        return self::$supported_currencies;
    }

    /**
     * Retrieves the supported locales.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_supported_locales() {
        return self::$supported_locales;
    }

    /**
     * Retrieves cards that support 3DS.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_3ds_supported_cards() {
        return self::$threeds_cards;
    }

    /**
     * Retrieves available credit card types.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_available_card_types() {
        return self::$available_card_types;
    }

    /**
     * Retrieves available direct payment types.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_available_direct_payment_types() {
        return self::$available_direct_payment_types;
    }

    /**
     * Retrieves default card types.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_default_card_types() {
        return self::$default_card_types;
    }

    /**
     * Retrieves countries that do not require post code.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_countries_requiring_no_postcode() {
        return self::$no_postcode_countries;
    }

    /**
     * Retrievs gateway id of MangoPay.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_gateway_id() {
        return self::$gateway_id;
    }

    /**
     * Retrievs gateway title of MangoPay.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_gateway_title() {
        return __( 'Dokan MangoPay', 'dokan' );
    }

    /**
     * Retrievs gateway description of MangoPay.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_gateway_description() {
        return __( 'Pay via MangoPay', 'dokan' );
    }

    /**
     * Retrieves text for order button on checkout page
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_order_button_text() {
        return __( 'Proceed to MangoPay', 'dokan' );
    }

    /**
     * Includes module template
     *
     * @since 3.5.0
     *
     * @param string $file_name Template file name
     * @param array  $args      Necessary variables
     *
     * @return void
     */
    public static function get_template( $file_name, $args = [] ) {
        $file_name = sanitize_key( $file_name ) . '.php';

        dokan_get_template( $file_name, $args, '', trailingslashit( DOKAN_MANGOPAY_TEMPLATE_PATH ) );
    }

    /**
     * Check if the product is a vendor subscription product.
     *
     * @since 3.5.0
     *
     * @param WC_Product|int $product
     *
     * @return bool
     **/
    public static function is_vendor_subscription_product( $product ) {
        if ( is_int( $product ) ) {
            $product = wc_get_product( $product );
        }

        if ( ! $product instanceof WC_Product ) {
            return false;
        }

        if ( ! self::is_vendor_subscription_module_active() ) {
            return false;
        }

        if ( 'product_pack' === $product->get_type() ) {
            return true;
        }

        return false;
    }

    /**
     * Check whether subscription module is enabled or not
     *
     * @since 3.5.0
     *
     * @return bool
     */
    public static function is_vendor_subscription_module_active() {
        // Don't get confused with product_subscription, id for vendor subscription module is product_subscription
        return function_exists( 'dokan_pro' ) && dokan_pro()->module->is_active( 'product_subscription' );
    }

    /**
     * Checks if gateway is ready to be used
     *
     * @since 3.5.0
     *
     * @return boolean
     */
    public static function is_gateway_ready() {
        if ( ! Settings::is_gateway_enabled() || ! self::is_api_ready() ) {
            return false;
        }

        return true;
    }

    /**
     * Checks if mangopay api is ready
     *
     * @since 3.5.0
     *
     * @return boolean
     */
    public static function is_api_ready() {
        if ( empty( Settings::get_client_id() ) || empty( Settings::get_api_key() ) ) {
            return false;
        }

        return true;
    }

    /**
     * Modifies balance date with threshold according to disbursement mode.
     *
     * @since 3.5.0
     *
     * @return string
     */
    public static function get_modified_balance_date() {
        $disburse_mode = Settings::get_disbursement_mode();
        switch ( $disburse_mode ) {
            case 'DELAYED':
                // Add one day extra with the delay period to consider the processing
                $interval_days = (int) Settings::get_disbursement_delay_period() + 1;
                break;

            case 'ON_ORDER_COMPLETED':
                // Let's make a big assumption to avoid any risk
                $interval_days = 60;
                break;

            default:
                $interval_days = 0;
        }

        return empty( $interval_days )
            ? dokan_current_datetime()->format( 'Y-m-d H:i:s' )
            : dokan_current_datetime()->modify( "+ {$interval_days} days" )->format( 'Y-m-d H:i:s' );
    }

    /**
     * CHecks if a seller is connected to MangoPay
     * and ready to receieve payment.
     *
     * @since 3.5.0
     *
     * @param int|string $seller_id
     *
     * @return boolean
     */
    public static function is_seller_connected( $seller_id ) {
        return ! empty( Meta::get_mangopay_account_id( $seller_id ) );
    }

    /**
     * Retrieves bank account common form fields
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_bank_account_common_fields() {
        return array(
            'name'     => array(
                'id'       => 'dokan-mangopay-vendor-account-name',
                'type'     => 'text',
                'required' => true,
                'class'    => array( 'regular-text' ),
                'label'    => __( 'Account Holder\'s Name', 'dokan' ),
                'value'    => '',
            ),
            'address1' => array(
                'id'           => 'dokan-mangopay-vendor-account-address1',
                'placeholder'  => esc_attr__( 'House number and street name', 'dokan' ),
                'required'     => true,
                'class'        => array( 'form-row-wide', 'address-field', 'inline-field' ),
                'autocomplete' => 'address-line1',
                'label'        => __( 'Account Holder\'s Address', 'dokan' ),
            ),
            'address2' => array(
                'id'           => 'dokan-mangopay-vendor-account-address2',
                'placeholder'  => __( 'Apartment, suite, unit, etc.', 'dokan' ),
                'required'     => true,
                'class'        => array( 'form-row-wide', 'address-field', 'inline-field' ),
                'autocomplete' => 'address-line2',
                'label'        => __( 'Address Details', 'dokan' ),
            ),
            'country'  => array(
                'id'           => 'dokan-mangopay-vendor-account-country',
                'type'         => 'country',
                'label'        => __( 'Account Holder\'s Country', 'dokan' ),
                'required'     => true,
                'class'        => array( 'form-row-wide', 'address-field' ),
                'autocomplete' => 'country',
            ),
            'state'    => array(
                'id'           => 'dokan-mangopay-vendor-account-state',
                'type'         => 'state',
                'label'        => __( 'Account Holder\'s State', 'dokan' ),
                'required'     => true,
                'class'        => array( 'form-row-wide', 'address-field' ),
                'validate'     => array( 'state' ),
                'autocomplete' => 'address-level1',
            ),
            'city'     => array(
                'id'           => 'dokan-mangopay-vendor-account-city',
                'label'        => __( 'City', 'dokan' ),
                'required'     => true,
                'class'        => array( 'form-row-wide', 'address-field', 'inline-field' ),
                'autocomplete' => 'address-level2',
            ),
            'postcode' => array(
                'id'           => 'dokan-mangopay-vendor-account-postcode',
                'label'        => __( 'Postcode', 'dokan' ),
                'required'     => true,
                'class'        => array( 'form-row-wide', 'address-field', 'inline-field' ),
                'validate'     => array( 'postcode' ),
                'autocomplete' => 'postal-code',
            ),
        );
    }

    /**
     * Retrieves bank account fields.
     *
     * @since 3.5.0
     *
     * @see: https://docs.mangopay.com/api-references/bank-accounts/
     *
     * @return array
     */
    public static function get_bank_account_types_fields() {
        return array(
            'IBAN'  => array(
                'iban' => array(
                    'label'       => __( 'IBAN', 'dokan' ),
                    'required'    => true,
                    'format'      => 'text',
                    'custom'      => array(
                        'maxlength' => 27,
                    ),
                    'redact'      => '4,4',
                    'validate'    => '^[a-zA-Z]{2}\d{2}\s*(\w{4}\s*){2,7}\w{1,4}\s*$',
                    'property'    => 'IBAN',
                    'class'       => array( 'inline-field' ),
                    'unique'      => true,
                ),
                'bic' => array(
                    'label'       => __( 'BIC', 'dokan' ),
                    'required'    => true,
                    'format'      => 'text',
                    'custom'      => array(
                        'maxlength' => 11,
                    ),
                    'redact'      => '0,2',
                    'validate'    => '^[a-zA-Z]{6}\w{2}(\w{3})?$',
                    'property'    => 'BIC',
                    'class'       => array( 'inline-field' ),
                ),
            ),
            'GB'    => array(
                'account_number' => array(
                    'label'       => __( 'Account Number', 'dokan' ),
                    'required'    => true,
                    'format'      => 'number',
                    'redact'      => '0,2',
                    'validate'    => '^\d{8}$',
                    'property'    => 'AccountNumber',
                    'class'       => array( 'inline-field' ),
                    'unique'      => true,
                ),
                'sort_code'   => array(
                    'label'       => __( 'Sort Code', 'dokan' ),
                    'required'    => true,
                    'format'      => 'number',
                    'maxlength'   => 6,
                    'redact'      => '0,2',
                    'validate'    => '^\d{6}$',
                    'property'    => 'SortCode',
                    'class'       => array( 'inline-field' ),
                ),
            ),
            'US'    => array(
                'account_number' => array(
                    'label'       => __( 'Account Number', 'dokan' ),
                    'required'    => true,
                    'format'      => 'number',
                    'redact'      => '0,2',
                    'validate'    => '^\d+$',
                    'property'    => 'AccountNumber',
                    'unique'      => true,
                ),
                'aba'               => array(
                    'label'       => __( 'ABA', 'dokan' ),
                    'required'    => true,
                    'format'      => 'number',
                    'maxlength'   => 9,
                    'redact'      => '0,2',
                    'validate'    => '^\d{9}$',
                    'property'    => 'ABA',
                    'class'       => array( 'inline-field' ),
                ),
                'datype'            => array(
                    'label'       => __( 'Deposit Account Type', 'dokan' ),
                    'required'    => true,
                    'format'      => 'select',
                    'type'        => 'select',
                    'options'     => array(
                        'CHECKING' => 'Checking',
                        'SAVINGS'  => 'Savings',
                    ),
                    'redact'      => '',
                    'validate'    => 'CHECKING|SAVINGS',
                    'property'    => 'DepositAccountType',
                    'class'       => array( 'inline-field' ),
                ),
            ),
            'CA'    => array(
                'bank_name'         => array(
                    'label'       => __( 'Bank Name', 'dokan' ),
                    'required'    => true,
                    'format'      => 'text',
                    'maxlength'   => 50,
                    'redact'      => '',
                    'validate'    => '^[\w\s]{1,50}$',
                    'property'    => 'BankName',
                    'class'       => array( 'inline-field' ),
                ),
                'inst_number'       => array(
                    'label'       => __( 'Institution Number', 'dokan' ),
                    'required'    => true,
                    'format'      => 'number',
                    'maxlength'   => 4,
                    'redact'      => '0,2',
                    'validate'    => '\d{3,4}',
                    'property'    => 'InstitutionNumber',
                    'class'       => array( 'inline-field' ),
                ),
                'branch_code'       => array(
                    'label'       => __( 'Branch Code', 'dokan' ),
                    'required'    => true,
                    'format'      => 'number',
                    'maxlength'   => 5,
                    'redact'      => '0,2',
                    'validate'    => '^\d{5}$',
                    'property'    => 'BranchCode',
                    'class'       => array( 'inline-field' ),
                ),
                'account_number'    => array(
                    'label'       => __( 'Account Number', 'dokan' ),
                    'required'    => true,
                    'format'      => 'number',
                    'maxlength'   => 20,
                    'redact'      => '0,2',
                    'validate'    => '^\d{1,20}$',
                    'property'    => 'AccountNumber',
                    'class'       => array( 'inline-field' ),
                    'unique'      => true,
                ),
            ),
            'OTHER' => array(
                'country'           => array(
                    'label'       => __( 'Country', 'dokan' ),
                    'required'    => true,
                    'format'      => 'country',
                    'redact'      => '',
                    'validate'    => '^[A-Z]{2}$',
                    'property'    => 'Country',
                ),
                'bic'               => array(
                    'label'       => __( 'BIC', 'dokan' ),
                    'required'    => true,
                    'format'      => 'text',
                    'maxlength'   => 9,
                    'redact'      => '0,2',
                    'validate'    => '.+',
                    'property'    => 'BIC',
                    'class'       => array( 'inline-field' ),
                ),
                'account_number'    => array(
                    'label'       => __( 'Account Number', 'dokan' ),
                    'required'    => true,
                    'format'      => 'text',
                    'redact'      => '0,2',
                    'validate'    => '.+',
                    'property'    => 'AccountNumber',
                    'class'       => array( 'inline-field' ),
                    'unique'      => true,
                ),
            ),
        );
    }

    /**
     * Retrieves extra sign up fields upon condition
     *
     * @since 3.5.0
     *
     * @param int|string $user_id
     * @param array      $data
     *
     * @return array
     */
    public static function get_signup_fields( $user_id = null, $data = [] ) {
        $default_status        = Settings::get_default_vendor_status();
        $default_business_type = Settings::get_default_business_type();

        $fields = array(
            'birthday'      => array(
                'id'       => 'dokan-mangopay-user-birthday',
                'type'     => 'date',
                'label'    => __( 'Date of Birth', 'dokan' ),
                'class'    => array( 'inline-field' ),
                'required' => true,
            ),
            'nationality'   => array(
                'id'       => 'dokan-mangopay-user-nationality',
                'type'     => 'country',
                'label'    => __( 'Nationality', 'dokan' ),
                'class'    => array( 'inline-field', 'wc-enhanced-select' ),
                'required' => true,
            ),
            'status'        => array(
                'id'       => 'dokan-mangopay-user-status',
                'type'     => 'hidden',
                'value'    => ! empty( $default_status ) && 'EITHER' !== $default_status ? $default_status : '',
                'required' => true,
            ),
            'business_type' => array(
                'id'       => 'dokan-mangopay-business-type',
                'type'     => 'hidden',
                'value'    => ! empty( $default_business_type ) && 'EITHER' !== $default_business_type ? $default_business_type : '',
                'required' => true,
            ),
            'company_number' => array(
                'id'       => 'dokan-mangopay-company-number',
                'type'     => 'text',
                'label'    => __( 'Company Number', 'dokan' ),
                'required' => true,
            ),
            'address1'       => array(
                'id'           => 'dokan-mangopay-address1',
                'placeholder'  => esc_attr__( 'House number and street name', 'dokan' ),
                'class'        => array( 'form-row-wide', 'address-field', 'dokan-mp-hq-address', 'inline-field' ),
                'autocomplete' => 'address-line1',
                'label'        => __( 'Address', 'dokan' ),
            ),
            'address2'       => array(
                'id'           => 'dokan-mangopay-address2',
                'placeholder'  => __( 'Apartment, suite, unit, etc.', 'dokan' ),
                'class'        => array( 'form-row-wide', 'address-field', 'dokan-mp-hq-address', 'inline-field' ),
                'autocomplete' => 'address-line2',
                'label'        => __( 'Address Details', 'dokan' ),
            ),
            'country'        => array(
                'id'           => 'dokan-mangopay-country',
                'type'         => 'country',
                'label'        => __( 'Country', 'dokan' ),
                'class'        => array( 'form-row-wide', 'address-field', 'dokan-mp-hq-address' ),
                'autocomplete' => 'country',
            ),
            'state'           => array(
                'id'           => 'dokan-mangopay-state',
                'type'         => 'state',
                'label'        => __( 'State', 'dokan' ),
                'class'        => array( 'form-row-wide', 'address-field', 'dokan-mp-hq-address' ),
                'validate'     => array( 'state' ),
                'autocomplete' => 'address-level1',
            ),
            'city'            => array(
                'id'           => 'dokan-mangopay-city',
                'label'        => __( 'City', 'dokan' ),
                'class'        => array( 'form-row-wide', 'address-field', 'dokan-mp-hq-address', 'inline-field' ),
                'autocomplete' => 'address-level2',
            ),
            'postcode'        => array(
                'id'           => 'dokan-mangopay-postcode',
                'label'        => __( 'Postcode', 'dokan' ),
                'class'        => array( 'form-row-wide', 'address-field', 'dokan-mp-hq-address', 'inline-field' ),
                'validate'     => array( 'postcode' ),
                'autocomplete' => 'postal-code',
            ),
            'terms'           => array(
                'id'       => 'dokan-mangopay-terms',
                'label'    => wp_kses(
                    sprintf(
                        /* translators: %1$s) opening anchor tag with link, %2$s) closing anchor tag */
                        __( 'I have read and accepted the %1$sTerms and Conditions%2$s of MangoPay', 'dokan' ),
                        '<a href="https://www.mangopay.com/terms/MANGOPAY_Terms-EN.pdf" target="_blank">',
                        '</a>'
                    ),
                    [
                        'a' => [
                            'href'   => true,
                            'target' => true,
                        ],
                    ]
                ),
                'type'     => 'checkbox',
                'class'    => [ 'form-row-wide', 'dokan-mangopay-terms' ],
                'required' => true,
            ),
        );

        if ( ! empty( $user_id ) ) {
            $user_status   = Meta::get_user_status( $user_id );
            $business_type = Meta::get_user_business_type( $user_id );
            $account_id    = Meta::get_mangopay_account_id( $user_id );

            if ( empty( $account_id ) ) {
                $account_id = Meta::get_trashed_mangopay_account_id( $user_id );
            }
        }

        if ( 'EITHER' === $default_status ) {
            $fields['status']['type']     = 'select';
            $fields['status']['options']  = array(
                'NATURAL' => __( 'Individual', 'dokan' ),
                'LEGAL'   => __( 'Business', 'dokan' ),
            );
            $fields['status']['label']    = __( 'Type of User', 'dokan' );
            $fields['status']['required'] = true;

            if ( ! empty( $user_status ) ) {
                $fields['status']['value'] = $user_status;
            }
        }

        if ( 'EITHER' === $default_business_type ) {
            $fields['business_type']['type']    = 'select';
            $fields['business_type']['label']   = __( 'Type of Business', 'dokan' );
            $fields['business_type']['options'] = array(
                'BUSINESS'     => __( 'Businesses', 'dokan' ),
                'ORGANIZATION' => __( 'Organization', 'dokan' ),
                'SOLETRADER'   => __( 'Soletrader', 'dokan' ),
            );
            $fields['business_type']['required'] = true;

            if ( ! empty( $business_type ) ) {
                $fields['business_type']['value'] = $business_type;
            }
        }

        if ( ! empty( $account_id ) ) {
            $fields['status']['type']        = 'hidden';
            $fields['business_type']['type'] = 'hidden';
            unset( $fields['status']['label'], $fields['business_type']['label'] );
        }

        if ( is_array( $data ) && ! empty( $data['status'] ) ) {
            $fields['company_number']['required'] = 'LEGAL' === $data['status']
                && ! empty( $data['business_type'] )
                && 'BUSINESS' === $data['business_type'];
        }

        return $fields;
    }

    /**
     * Retrieves form fields for UBO creation.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_ubo_form_field() {
        $user_id = get_current_user_id();
        if ( ! $user_id ) {
            return array();
        }

        return array(
            'first_name' => array(
                'id'           => 'dokan_mp_first_name',
                'required'     => true,
                'type'         => 'text',
                'class'        => array( 'regular-text', 'inline-field' ),
                'autocomplete' => 'first_name',
                'label'        => __( 'First Name', 'dokan' ),
            ),
            'last_name' => array(
                'id'           => 'dokan_mp_last_name',
                'required'     => true,
                'type'         => 'text',
                'class'        => array( 'regular-text', 'inline-field' ),
                'autocomplete' => 'first_name',
                'label'        => __( 'Last Name', 'dokan' ),
            ),
            'birthday'    => array(
                'id'       => 'dokan_mp_birthday',
                'type'     => 'date',
                'label'    => __( 'Date of Birth', 'dokan' ),
                'class'    => array( 'inline-field' ),
                'required' => true,
            ),
            'nationality' => array(
                'id'       => 'dokan_mp_nationality',
                'class'    => array( 'form-row-wide', 'address-field', 'inline-field' ),
                'required' => true,
                'type'     => 'country',
                'label'    => __( 'Nationality', 'dokan' ),
            ),
            'address_line1' => array(
                'id'           => 'dokan_mp_address_line1',
                'placeholder'  => esc_attr__( 'House number and street name', 'dokan' ),
                'required'     => true,
                'class'        => array( 'form-row-wide', 'address-field', 'inline-field' ),
                'autocomplete' => 'address-line1',
                'label'        => __( 'Address', 'dokan' ),
            ),
            'address_line2' => array(
                'id'           => 'dokan_mp_address_line2',
                'placeholder'  => __( 'Apartment, suite, unit, etc.', 'dokan' ),
                'required'     => true,
                'class'        => array( 'form-row-wide', 'address-field', 'inline-field' ),
                'autocomplete' => 'address-line2',
                'label'        => __( 'Address Details', 'dokan' ),
            ),
            'country'  => array(
                'id'           => 'dokan_mp_country',
                'type'         => 'country',
                'label'        => __( 'Country', 'dokan' ),
                'required'     => true,
                'class'        => array( 'form-row-wide', 'address-field' ),
                'autocomplete' => 'country',
            ),
            'region'    => array(
                'id'           => 'dokan_mp_region',
                'type'         => 'state',
                'label'        => __( 'State', 'dokan' ),
                'required'     => true,
                'class'        => array( 'form-row-wide', 'address-field' ),
                'validate'     => array( 'state' ),
                'autocomplete' => 'address-level1',
            ),
            'city'     => array(
                'id'           => 'dokan_mp_city',
                'label'        => __( 'City', 'dokan' ),
                'required'     => true,
                'class'        => array( 'form-row-wide', 'address-field', 'inline-field' ),
                'autocomplete' => 'address-level2',
            ),
            'postal_code' => array(
                'id'           => 'dokan_mp_postal_code',
                'label'        => __( 'Postcode', 'dokan' ),
                'required'     => true,
                'class'        => array( 'form-row-wide', 'address-field', 'inline-field' ),
                'validate'     => array( 'postcode' ),
                'autocomplete' => 'postal-code',
            ),
            'birthplace_city'    => array(
                'id'       => 'dokan_mp_birthplace_city',
                'class'    => array( 'regular-text' ),
                'type'     => 'text',
                'required' => true,
                'label'    => __( 'Birthplace City', 'dokan' ),
            ),
            'birthplace_country' => array(
                'id'       => 'dokan_mp_birthplace_country',
                'class'    => array( 'form-row-wide', 'address-field' ),
                'required' => true,
                'type'     => 'country',
                'label'    => __( 'Birthplace Country', 'dokan' ),
            ),
        );
    }

    /**
     * Retrieves mangopay payment settings for vendors
     *
     * @since 3.5.0
     *
     * @param int|string $vendor_id
     * @param string $section
     *
     * @return mixed
     */
    public static function get_vendor_payment_settings( $vendor_id, $section = '', $sub_section = '' ) {
        $profile_settings = get_user_meta( $vendor_id, 'dokan_profile_settings', true );

        if ( empty( $profile_settings ) ) {
            return '';
        }

        if ( empty( $profile_settings['payment'] ) || empty( $profile_settings['payment']['mangopay'] ) ) {
            return '';
        }

        if ( empty( $section ) ) {
            return $profile_settings['payment']['mangopay'];
        }

        if ( ! isset( $profile_settings['payment']['mangopay'][ $section ] ) ) {
            return '';
        }

        if ( empty( $sub_section ) ) {
            return $profile_settings['payment']['mangopay'][ $section ];
        }

        if ( ! isset( $profile_settings['payment']['mangopay'][ $section ][ $sub_section ] ) ) {
            return '';
        }

        return $profile_settings['payment']['mangopay'][ $section ][ $sub_section ];
    }

    /**
     * Generates dropdown of years.
     *
     * The starting year is the current year and
     * rest of the years are generated based on
     * the limit.
     *
     * @since 3.5.0
     *
     * @param integer $limit
     *
     * @return array
     */
    public static function get_years_dropdown( $limit = 10 ) {
        $years     = array();
        $this_year = dokan_current_datetime()->format( 'Y' );
        $limit     = intval( $this_year ) + intval( $limit );

        for ( $year = $this_year; $year <= $limit; ++$year ) {
            /* translators: year */
            $years[ substr( $year, 2, 2 ) ] = sprintf( __( '%s', 'dokan' ), $year ); // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings
        }

        return $years;
    }

    /**
     * Generates dropdown for all tweleve months of a year.
     *
     * @since 3.5.0
     *
     * @return array
     */
    public static function get_months_dropdown() {
        $months = array();

        for ( $i = 1; $i <= 12; ++$i ) {
            /* translators: month name */
            $months[ $i ] = sprintf( __( '%s', 'dokan' ), gmdate( 'F', mktime( 0, 0, 0, $i, 1 ) ) ); // phpcs:ignore WordPress.WP.I18n.NoEmptyStrings
        }

        return $months;
    }

    /**
     * Exit with a 404 header when incoming webhook looks suspicious
     *
     * @since 3.5.0
     *
     * @return void
     */
    public static function exit_with_404() {
        global $wp_query;
        $wp_query->set_404();
        status_header( 404 );
        nocache_headers();
        include get_query_template( '404' );
        die();
    }

    /**
     * Warns owner/vendor/admin by sending a mail.
     *
     * @since 3.5.0
     *
     * @uses get_option() Retrievs the admin email
     *
     * @param string $message
     * @param int|string $order_id
     *
     * @return boolean
     */
    public static function warn_owner( $message, $order_id = 0 ) {
        $recipients = array(
            get_option( 'admin_email' ),
        );

        $order = wc_get_order( $order_id );

        if ( $order ) {
            /* translators: 1) gateway title, 2) hook note */
            $order->add_order_note( sprintf( __( '[%1$s] Hook note: %2$s', 'dokan' ), self::get_gateway_title(), $message ) );

            // Get email of all vendors concerned by this order
            $items = $order->get_items();

            foreach ( $items as $item ) {
                $vendor_id    = dokan_get_vendor_by_product( $item['product_id'], true );
                $vendor_email = get_the_author_meta( 'email', $vendor_id );

                if ( ! in_array( $vendor_email, $recipients, true ) ) {
                    $recipients[] = $vendor_email;
                }
            }
        }

        self::log( 'Sending warning email to recipients:' . print_r( $recipients, true ) );
        self::log( 'Email content: ' . $message );

        return wp_mail(
            $recipients,
            __( 'Dokan MangoPay Bank Wire webhook warning', 'dokan' ),
            $message
        );
    }

    /**
     * Checks if a date is valid.
     *
     * @since 3.5.0
     *
     * @param string $date
     *
     * @return boolean
     */
    public static function is_valid_date( $date ) {
        if ( ! preg_match( '/^(\d{4,4})\-(\d{2,2})\-(\d{2,2})$/', $date, $offset ) ) {
            return false;
        }

        if ( ! wp_checkdate( $offset[2], $offset[3], $offset[1], $date ) ) {
            return false;
        }

        return true;
    }

    /**
     * Formats date to Mangopay standard.
     *
     * @since 3.5.0
     *
     * @param string $date
     *
     * @return string
     */
    public static function format_date( $date ) {
        $offset = get_option( 'gmt_offset' );
        $date   = strtotime( $date );

        if ( ! empty( $offset ) ) {
            $date += ( $offset * 60 * 60 );
        }

        return $date;
    }

    /**
     * Writes error log message.
     *
     * @since 3.5.0
     *
     * @param string $message
     * @param string $level
     *
     * @return void
     */
    public static function log( $message, $category = '', $level = 'debug' ) {
        dokan_log( sprintf( '[Dokan MangoPay] %s: ', $category ) . print_r( $message, true ), $level );
    }
}
