<?php

namespace WeDevs\DokanPro;

use WeDevs\Dokan\Traits\ChainableContainer;

class Module {

    use ChainableContainer;

    /**
     * The wp option key which contains active module ids
     *
     * @since 3.0.0
     *
     * @var string
     */
    const ACTIVE_MODULES_DB_KEY = 'dokan_pro_active_modules';

    /**
     * Active module ids
     *
     * @since 3.0.0
     *
     * @var array
     */
    private $active_modules = [];

    /**
     * Contains all module informations
     *
     *  @since 3.0.0
     *
     * @var array
     */
    private $dokan_pro_modules = [];

    /**
     * Tells us if modules activated or not
     *
     * @since 3.0.0
     *
     * @var bool
     */
    private static $modules_activated = false;

    /**
     * Update db option containing active module ids
     *
     * @since 3.0.0
     *
     * @param array $value
     *
     * @return bool
     */
    protected function update_db_option( $value ) {
        return update_option( self::ACTIVE_MODULES_DB_KEY, $value );
    }

    /**
     * Load active modules
     *
     * @since 3.0.0
     *
     * @param array $newly_activated_modules Useful after module activation
     *
     * @return void
     */
    public function load_active_modules( $newly_activated_modules = [] ) {
        if ( self::$modules_activated ) {
            return;
        }

        $active_modules    = $this->get_active_modules();
        $dokan_pro_modules = $this->get_all_modules();
        $activated_modules = [];

        foreach ( $active_modules as $module_id ) {
            if ( ! isset( $dokan_pro_modules[ $module_id ] ) ) {
                continue;
            }

            $module = $dokan_pro_modules[ $module_id ];

            // store this module as activated modules
            if ( file_exists( $module['module_file'] ) ) {
                $activated_modules[] = $module_id;
            }

            if ( ! isset( $this->container[ $module_id ] ) && file_exists( $module['module_file'] ) ) {
                require_once $module['module_file'];

                $module_class = $module['module_class'];
                $this->container[ $module_id ] = new $module_class();

                if ( in_array( $module_id, $newly_activated_modules, true ) ) {
                    /**
                     * Module activation hook
                     *
                     * @since 3.0.0
                     *
                     * @param object $module Module class instance
                     */
                    do_action( 'dokan_activated_module_' . $module_id, $this->container[ $module_id ] );
                }
            }
        }

        // store activated module as active module
        if ( $activated_modules !== $active_modules ) {
            update_option( self::ACTIVE_MODULES_DB_KEY, $activated_modules );
        }
        self::$modules_activated = true;
    }

    /**
     * List of Dokan Pro modules
     *
     * @since 3.0.0
     *
     * @return array
     */
    public function get_all_modules() {
        if ( ! $this->dokan_pro_modules ) {
            $thumbnail_dir = DOKAN_PRO_PLUGIN_ASSEST . '/images/modules';

            $this->dokan_pro_modules = apply_filters(
                'dokan_pro_modules', [
                    'booking' => [
                        'id'             => 'booking',
                        'name'           => __( 'WooCommerce Booking Integration', 'dokan' ),
                        'description'    => __( 'Integrates WooCommerce Booking with Dokan.', 'dokan' ),
                        'thumbnail'      => $thumbnail_dir . '/booking.svg',
                        'module_file'    => DOKAN_PRO_MODULE_DIR . '/booking/module.php',
                        'module_class'   => 'WeDevs\DokanPro\Modules\Booking\Module',
                        'plan'           => [ 'business', 'enterprise' ],
                        'doc_id'         => 93500,
                        'doc_link'       => 'https://wedevs.com/docs/dokan/modules/dokan-bookings/',
                        'mod_link'       => 'https://wedevs.com/dokan/extensions/woocommerce-booking-integration/',
                        'pre_requisites' => 'Requirements: WooCommerce Bookings plugin',
                        'categories'     => [ 'Product Management', 'Integration' ],
                        'video_id'       => 'F5oofXmuUqo',
                    ],
                    'color_scheme_customizer' => [
                        'id'           => 'color_scheme_customizer',
                        'name'         => __( 'Color Scheme Customizer', 'dokan' ),
                        'description'  => __( 'A Dokan plugin Add-on to Customize Colors of Dokan Dashboard', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/color-scheme-customizer.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/color-scheme-customizer/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\ColorSchemeCustomizer\Module',
                        'plan'         => [ 'starter', 'liquidweb', 'professional', 'business', 'enterprise' ],
                        'doc_id'       => 102550,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/color-scheme/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/color-scheme-customizer/',
                        'categories'   => [ 'UI & UX' ],
                        'video_id'     => 'EXaJGzeKWHg',
                    ],
                    'delivery_time' => [
                        'id'           => 'delivery_time',
                        'name'         => __( 'Delivery Time', 'dokan' ),
                        'description'  => __( 'Let customer choose their order delivery date & time', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/delivery-time.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/delivery-time/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\DeliveryTime\Module',
                        'plan'         => [ 'starter', 'liquidweb', 'professional', 'business', 'enterprise' ],
                        'doc_id'       => 157825,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/dokan-delivery-time/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/delivery-time',
                        'categories'   => [ 'Shipping' ],
                    ],
                    'elementor' => [
                        'id'             => 'elementor',
                        'name'           => __( 'Elementor', 'dokan' ),
                        'description'    => __( 'Elementor Page Builder widgets for Dokan', 'dokan' ),
                        'thumbnail'      => $thumbnail_dir . '/elementor.svg',
                        'module_file'    => DOKAN_PRO_MODULE_DIR . '/elementor/module.php',
                        'module_class'   => 'WeDevs\DokanPro\Modules\Elementor\Module',
                        'plan'           => [ 'professional', 'business', 'enterprise' ],
                        'doc_id'         => 181872,
                        'doc_link'       => 'https://wedevs.com/docs/dokan/modules/elementor-dokan/',
                        'mod_link'       => 'https://wedevs.com/dokan/modules/elementor/',
                        'pre_requisites' => 'Requirements: Elementor Free and Elementor Pro',
                        'categories'     => [ 'UI & UX', 'Integration' ],
                    ],
                    'export_import' => [
                        'id'           => 'export_import',
                        'name'         => __( 'Vendor Product Importer and Exporter', 'dokan' ),
                        'description'  => __( 'This is simple product import and export plugin for vendor', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/import-export.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/export-import/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\ExIm\Module',
                        'plan'         => [ 'business', 'enterprise' ],
                        'doc_id'       => 93320,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/how-to-install-and-use-dokan-exportimport-add/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/export-import/',
                        'categories'   => [ 'Product Management' ],
                    ],
                    'follow_store' => [
                        'id'           => 'follow_store',
                        'name'         => __( 'Follow Store', 'dokan' ),
                        'description'  => __( 'Send emails to customers when their favorite store updates.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/follow-store.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/follow-store/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\FollowStore\Module',
                        'plan'         => [ 'professional', 'business', 'enterprise' ],
                        'doc_id'       => 152781,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/follow-store/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/follow-store/',
                        'video_id'     => 'v76PnEN5ceQ',
                        'categories'   => [ 'Store Management' ],
                    ],
                    'geolocation' => [
                        'id'           => 'geolocation',
                        'name'         => __( 'Geolocation', 'dokan' ),
                        'description'  => __( 'Search Products and Vendors by geolocation.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/geolocation.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/geolocation/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\Geolocation\Module',
                        'plan'         => [ 'business', 'enterprise' ],
                        'doc_id'       => 138048,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/dokan-geolocation/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/geolocation/',
                        'categories'   => [ 'Store Management', 'Product Management' ],
                    ],
                    'germanized' => [
                        'id'           => 'germanized',
                        'name'         => __( 'EU Compliance Fields', 'dokan' ),
                        'description'  => __( 'EU Compliance Fields Support for Vendors.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/germanized.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/germanized/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\Germanized\Module',
                        'plan'         => [ 'starter', 'liquidweb', 'professional', 'business', 'enterprise' ],
                        'doc_id'       => 138048,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/eu-compliance-fields/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/eu-compliance-fields',
                        'categories'   => [ 'Store Management' ],
                    ],
                    'live_chat' => [
                        'id'           => 'live_chat',
                        'name'         => __( 'Live Chat', 'dokan' ),
                        'description'  => __( 'Live Chat Between Vendor & Customer.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/live-chat.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/live-chat/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\LiveChat\Module',
                        'plan'         => [ 'business', 'enterprise' ],
                        'doc_id'       => 126767,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/dokan-live-chat/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/live-chat/',
                        'video_id'     => 'BHuTLjY78cY',
                        'categories'   => [ 'Store Management' ],
                    ],
                    'live_search' => [
                        'id'           => 'live_search',
                        'name'         => __( 'Live Search', 'dokan' ),
                        'description'  => __( 'Live product search for WooCommerce store.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/ajax-live-search.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/live-search/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\LiveSearch\Module',
                        'plan'         => [ 'professional', 'business', 'enterprise' ],
                        'doc_id'       => 93303,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/how-to-install-configure-use-dokan-live-search/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/ajax-live-search/',
                        'video_id'     => 'lvuR-QCJDIo',
                        'categories'   => [ 'Product Management' ],
                    ],
                    'moip' => [
                        'id'           => 'moip',
                        'name'         => __( 'Wirecard', 'dokan' ),
                        'description'  => __( 'Wirecard payment gateway for Dokan.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/wirecard-connect.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/moip/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\Moip\Module',
                        'plan'         => [ 'professional', 'business', 'enterprise' ],
                        'doc_id'       => 138385,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/dokan-moip-connect/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/moip/',
                        'categories'   => [ 'Payment' ],
                    ],
                    'dokan_paypal_ap' => [
                        'id'           => 'dokan_paypal_ap',
                        'name'         => __( 'PayPal Adaptive Payment', 'dokan' ),
                        'description'  => __( 'Allows to send split payments to vendor via PayPal Adaptive Payment gateway.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/paypal-adaptive.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/paypal-adaptive-payments/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\PayPalAP\Module',
                        'plan'         => [ 'professional', 'business', 'enterprise' ],
                        'categories'   => [ 'Payment' ],
                    ],
                    'paypal_marketplace' => [
                        'id'           => 'paypal_marketplace',
                        'name'         => __( 'PayPal Marketplace', 'dokan' ),
                        'description'  => __( 'Enable Split payments, Multi-seller payments and all PayPal Commerce Platform (PCP) features.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/paypal-marketplace.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/paypal-marketplace/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\PayPalMarketplace\Module',
                        'plan'         => [ 'business', 'enterprise' ],
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/paypal-marketplace/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/dokan-paypal-marketplace',
                        'categories'   => [ 'Payment' ],
                    ],
                    'product_addon' => [
                        'id'             => 'product_addon',
                        'name'           => __( 'Product Addon', 'dokan' ),
                        'description'    => __( 'WooCommerce Product Addon support', 'dokan' ),
                        'thumbnail'      => $thumbnail_dir . '/product-addon.svg',
                        'module_file'    => DOKAN_PRO_MODULE_DIR . '/product-addon/module.php',
                        'module_class'   => 'WeDevs\DokanPro\Modules\ProductAddon\Module',
                        'plan'           => [ 'professional', 'business', 'enterprise' ],
                        'doc_id'         => 247645,
                        'doc_link'       => 'https://wedevs.com/docs/dokan/modules/product-addon/',
                        'mod_link'       => 'https://wedevs.com/dokan/modules/product-addons/',
                        'pre_requisites' => 'Requirements: WooCommerce Product Addon extension',
                        'video_id'       => 'goKBE5L-3cg',
                        'categories'     => [ 'Product Management', 'Integration' ],
                    ],
                    'product_enquiry' => [
                        'id'           => 'product_enquiry',
                        'name'         => __( 'Product Enquiry', 'dokan' ),
                        'description'  => __( 'Enquiry for a specific product to a seller.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/product-enquiry.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/product-enquiry/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\ProductEnquiry\Module',
                        'plan'         => [ 'business', 'enterprise' ],
                        'doc_id'       => 93453,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/how-to-install-configure-use-dokan-product-enquiry/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/product-enquiry/',
                        'video_id'     => 'edRLlpmOf-E',
                        'categories'   => [ 'Product Management' ],
                    ],
                    'report_abuse' => [
                        'id'           => 'report_abuse',
                        'name'         => __( 'Report Abuse', 'dokan' ),
                        'description'  => __( 'Let customers report fraudulent or fake products.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/report-abuse.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/report-abuse/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\ReportAbuse\Module',
                        'plan'         => [ 'professional', 'business', 'enterprise' ],
                        'doc_id'       => 176173,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/dokan-report-abuse/',
                        'categories'   => [ 'Store Management' ],
                    ],
                    'rma' => [
                        'id'           => 'rma',
                        'name'         => __( 'Return and Warranty Request', 'dokan' ),
                        'description'  => __( 'Manage return and warranty from vendor end.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/rma.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/rma/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\RMA\Module',
                        'plan'         => [ 'professional', 'business', 'enterprise' ],
                        'doc_id'       => 157608,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/vendor-rma/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/rma/',
                        'video_id'     => 'j0s8d8u6qYs',
                        'categories'   => [ 'Order Management' ],
                    ],
                    'seller_vacation' => [
                        'id'           => 'seller_vacation',
                        'name'         => __( 'Seller Vacation', 'dokan' ),
                        'description'  => __( 'Using this plugin seller can go to vacation by closing their stores.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/seller-vacation.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/seller-vacation/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\SellerVacation\Module',
                        'plan'         => [ 'business', 'enterprise' ],
                        'doc_id'       => 2880,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/dokan-vendor-vacation/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/vendor-vacation/',
                        'video_id'     => '6pd7_3ZPKH4',
                        'categories'   => [ 'Store Management' ],
                    ],
                    'shipstation' => [
                        'id'           => 'shipstation',
                        'name'         => __( 'ShipStation Integration', 'dokan' ),
                        'description'  => __( 'Adds ShipStation label printing support to Dokan. Requires server DomDocument support.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/shipstation.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/shipstation/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\ShipStation\Module',
                        'plan'         => [ 'business', 'enterprise' ],
                        'doc_id'       => 152770,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/shipstation-dokan-wedevs/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/shipstation/',
                        'categories'   => [ 'Shipping' ],

                    ],
                    'auction' => [
                        'id'             => 'auction',
                        'name'           => __( 'Auction Integration', 'dokan' ),
                        'description'    => __( 'A plugin that combined WooCommerce simple auction and Dokan plugin.', 'dokan' ),
                        'thumbnail'      => $thumbnail_dir . '/auction.svg',
                        'module_file'    => DOKAN_PRO_MODULE_DIR . '/simple-auction/module.php',
                        'module_class'   => 'WeDevs\DokanPro\Modules\Auction\Module',
                        'plan'           => [ 'business', 'enterprise' ],
                        'doc_id'         => 93366,
                        'doc_link'       => 'https://wedevs.com/docs/dokan/modules/woocommerce-auctions-frontend-multivendor-marketplace/',
                        'mod_link'       => 'https://wedevs.com/dokan/modules/dokan-simple-auctions/',
                        'pre_requisites' => 'Requirements: WooCommerce Simple Auctions',
                        'video_id'       => 'TvwSvMSu8Rg',
                        'categories'     => [ 'Product Management', 'Integration' ],
                    ],
                    'spmv' => [
                        'id'           => 'spmv',
                        'name'         => __( 'Single Product Multiple Vendor', 'dokan' ),
                        'description'  => __( 'A module that offers multiple vendor to sell a single product.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/single-product-multivendor.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/single-product-multiple-vendor/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\SPMV\Module',
                        'plan'         => [ 'professional', 'business', 'enterprise' ],
                        'doc_id'       => 106646,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/single-product-multiple-vendor/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/single-product-multivendor/',
                        'video_id'     => 'ByiWWObvF0c',
                        'categories'   => [ 'Product Management' ],
                    ],
                    'store_reviews' => [
                        'id'           => 'store_reviews',
                        'name'         => __( 'Store Reviews', 'dokan' ),
                        'description'  => __( 'A plugin that allows customers to rate the sellers.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/vendor-review.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/store-reviews/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\StoreReviews\Module',
                        'plan'         => [ 'professional', 'business', 'enterprise' ],
                        'doc_id'       => 93511,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/vendor-review/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/dokan-vendor-review/',
                        'video_id'     => 'rX7ZTGa3GzI',
                        'categories'   => [ 'Store Management' ],
                    ],
                    'store_support' => [
                        'id'           => 'store_support',
                        'name'         => __( 'Store Support', 'dokan' ),
                        'description'  => __( 'Enable vendors to provide support to customers from store page.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/store-support.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/store-support/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\StoreSupport\Module',
                        'plan'         => [ 'professional', 'business', 'enterprise' ],
                        'doc_id'       => 93425,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/how-to-install-and-use-store-support/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/store-support/',
                        'video_id'     => 'YWnRWIhFlLM',
                        'categories'   => [ 'Store Management' ],
                    ],
                    'stripe' => [
                        'id'           => 'stripe',
                        'name'         => __( 'Stripe Connect', 'dokan' ),
                        'description'  => __( 'Accept credit card payments and allow your sellers to get automatic split payment in Dokan via Stripe.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/stripe.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/stripe/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\Stripe\Module',
                        'plan'         => [ 'liquidweb', 'professional', 'business', 'enterprise' ],
                        'doc_id'       => 93416,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/how-to-install-and-configure-dokan-stripe-connect/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/stripe-connect/',
                        'video_id'     => 'SVpRMSXMXtA',
                        'categories'   => [ 'Payment' ],
                    ],
                    'product_advertising' => [
                        'id'           => 'product_advertising',
                        'name'         => __( 'Product Advertising', 'dokan' ),
                        'description'  => __( 'Admin can earn more by allowing vendors to advertise their products and give them the right exposure.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/product-adv.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/product-adv/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\ProductAdvertisement\Module',
                        'plan'         => [ 'business', 'enterprise' ],
                        'doc_id'       => 93321,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/product-advertising/',
                        'mod_link'     => 'https://wedevs.com//dokan/modules/product-advertising',
                    ],
                    'product_subscription' => [
                        'id'           => 'product_subscription',
                        'name'         => __( 'Vendor Subscription', 'dokan' ),
                        'description'  => __( 'Subscription pack add-on for Dokan vendors.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/subscription.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/subscription/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\ProductSubscription\Module',
                        'plan'         => [ 'professional', 'business', 'enterprise' ],
                        'doc_id'       => 93321,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/how-to-install-use-dokan-subscription/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/subscription/',
                        'categories'   => [ 'Store Management' ],
                    ],
                    'vendor_analytics' => [
                        'id'           => 'vendor_analytics',
                        'name'         => __( 'Vendor Analytics', 'dokan' ),
                        'description'  => __( 'A plugin for store and product analytics for vendor.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/analytics.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/vendor-analytics/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\VendorAnalytics\Module',
                        'plan'         => [ 'business', 'enterprise' ],
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/dokan-vendor-analytics/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/vendor-analytics',
                        'video_id'     => 'IegbUHYA8R4',
                    ],
                    'vendor_staff' => [
                        'id'           => 'vendor_staff',
                        'name'         => __( 'Vendor Staff Manager', 'dokan' ),
                        'description'  => __( 'A plugin for manage store via vendor staffs.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/vendor-staff.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/vendor-staff/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\VendorStaff\Module',
                        'plan'         => [ 'business', 'enterprise' ],
                        'doc_id'       => 111397,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/dokan-vendor-staff-manager/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/vendor-staff-manager/',
                        'video_id'     => 'z4yinwCxabI',
                        'categories'   => [ 'Store Management' ],
                    ],
                    'vsp'         => [
                        'id'             => 'vsp',
                        'name'           => __( 'Product Subscription', 'dokan' ),
                        'description'    => __( 'WooCommerce Subscription integration for Dokan', 'dokan' ),
                        'thumbnail'      => $thumbnail_dir . '/vendor-subscription-product.svg',
                        'module_file'    => DOKAN_PRO_MODULE_DIR . '/vendor-subscription-product/module.php',
                        'module_class'   => 'WeDevs\DokanPro\Modules\VSP\Module',
                        'plan'           => [ 'business', 'enterprise' ],
                        'doc_id'         => 294770,
                        'doc_link'       => 'https://wedevs.com/docs/dokan/modules/dokan-vendor-subscription-product/',
                        'mod_link'       => 'https://wedevs.com/dokan/modules/vendor-subscription-product/',
                        'pre_requisites' => 'Requirements: WooCommerce Subscription Module',
                        'categories'     => [ 'Product Management', 'Integration' ],
                    ],
                    'vendor_verification' => [
                        'id'           => 'vendor_verification',
                        'name'         => __( 'Vendor Verification', 'dokan' ),
                        'description'  => __( 'Dokan add-on to verify sellers.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/vendor-verification.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/vendor-verification/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\VendorVerification\Module',
                        'plan'         => [ 'professional', 'business', 'enterprise' ],
                        'doc_id'       => 93421,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/dokan-seller-verification-admin-settings/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/seller-verification/',
                    ],
                    'wholesale' => [
                        'id'           => 'wholesale',
                        'name'         => __( 'Wholesale', 'dokan' ),
                        'description'  => __( 'Offer any customer to buy product as a wholesale price from any vendors.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/wholesale.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/wholesale/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\Wholesale\Module',
                        'plan'         => [ 'business', 'enterprise' ],
                        'doc_id'       => 157825,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/dokan-wholesale/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/wholesale/',
                        'categories'   => [ 'Product Management' ],
                    ],
                    'rank_math' => [
                        'id'             => 'rank_math',
                        'name'           => __( 'Rank Math SEO', 'dokan' ),
                        'description'    => __( 'Manage SEO for products with Rank Math', 'dokan' ),
                        'thumbnail'      => $thumbnail_dir . '/rank-math.svg',
                        'module_file'    => DOKAN_PRO_MODULE_DIR . '/rank-math/module.php',
                        'module_class'   => 'WeDevs\DokanPro\Modules\RankMath\Module',
                        'plan'           => [ 'professional', 'business', 'enterprise' ],
                        'doc_link'       => 'https://wedevs.com/docs/dokan/modules/rank-math-seo/',
                        'mod_link'       => 'https://wedevs.com/dokan/modules/rank-math-seo/',
                        'pre_requisites' => 'Requirements: Rank Math SEO (v1.0.80 or Later)',
                        'video_id'       => 'V7UcyAe7QAs',
                        'categories'     => [ 'Product Management', 'Integration' ],
                    ],
                    'table_rate_shipping' => [
                        'id'           => 'table_rate_shipping',
                        'name'         => __( 'Table Rate Shipping', 'dokan' ),
                        'description'  => __( 'Deliver Products at the Right Time, With the Right Pay.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/table-rate-shipping.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/table-rate-shipping/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\TableRateShipping\Module',
                        'plan'         => [ 'professional', 'business', 'enterprise' ],
                        'doc_id'       => 1527799,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/dokan-table-rate-shipping/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/table-rate-shipping/',
                        'categories'   => [ 'Shipping' ],
                    ],
                    'mangopay' => [
                        'id'           => 'mangopay',
                        'name'         => __( 'MangoPay', 'dokan' ),
                        'description'  => __( 'Enable split payments, multi-seller payments, and other marketplace features given by MangoPay.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/mangopay.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/mangopay/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\MangoPay\Module',
                        'plan'         => [ 'professional', 'business', 'enterprise' ],
                        'doc_id'       => '',
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/dokan-mangopay/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/dokan-mangopay/',
                        'categories'   => [ 'Payment' ],
                    ],
                    'order_min_max' => [
                        'id'           => 'order_min_max',
                        'name'         => __( 'Min Max Quantities', 'dokan' ),
                        'description'  => __( 'Set a minimum or maximum purchase quantity or amount for the products of your marketplace. ', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/order-min-max.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/order-min-max/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\OrderMinMax\Module',
                        'plan'         => [ 'professional', 'business', 'enterprise' ],
                        'doc_id'       => 1527799,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/how-to-enable-minimum-maximum-order-amount-for-dokan/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/minimum-maximum-order',
                        'categories'     => [ 'Product Management', 'Order Management' ],
                    ],
                    'razorpay' => [
                        'id'           => 'razorpay',
                        'name'         => __( 'Razorpay', 'dokan' ),
                        'description'  => __( 'Accept credit card payments and allow your sellers to get automatic split payment in Dokan via Razorpay.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/razorpay.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/razorpay/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\Razorpay\Module',
                        'plan'         => [ 'professional', 'business', 'enterprise' ],
                        'doc_id'       => 399718,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/dokan-razorpay/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/dokan-razorpay/',
                        'categories'   => [ 'Payment' ],
                    ],
                    'seller_badge' => [
                        'id'           => 'seller_badge',
                        'name'         => __( 'Seller Badge', 'dokan' ),
                        'description'  => __( 'Offer vendors varieties of badges by their performance in your marketplace.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/seller-badge.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/seller-badge/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\SellerBadge\Module',
                        'plan'         => [ 'professional', 'business', 'enterprise' ],
                        'doc_id'       => 15277999,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/seller-badge/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/seller-badge/',
                        'categories'   => [ 'Vendor Management' ],
                    ],
					'stripe_express' => [
						'id'           => 'stripe_express',
						'name'         => __( 'Stripe Express', 'dokan' ),
						'description'  => __( 'Enable split payments, multi-seller payments, Apple Pay, Google Pay, iDEAL and other marketplace features available in Stripe Express.', 'dokan' ),
						'thumbnail'    => $thumbnail_dir . '/stripe-express.svg',
						'module_file'  => DOKAN_PRO_MODULE_DIR . '/stripe-express/module.php',
						'module_class' => 'WeDevs\DokanPro\Modules\StripeExpress\Module',
						'plan'         => [ 'professional', 'business', 'enterprise' ],
						'doc_id'       => '',
						'doc_link'     => 'https://wedevs.com/docs/dokan/modules/dokan-stripe-express-module/',
						'mod_link'     => 'https://wedevs.com/dokan/modules/stripe-express/',
						'categories'   => [ 'Payment' ],
					],
                    'request_for_quotation' => [
                        'id'           => 'request_for_quotation',
                        'name'         => __( 'Request for Quotation', 'dokan' ),
                        'description'  => __( 'Facilitate wholesale orders between merchants and customers with the option for quoted prices.', 'dokan' ),
                        'thumbnail'    => $thumbnail_dir . '/request-for-quotation.svg',
                        'module_file'  => DOKAN_PRO_MODULE_DIR . '/request-for-quotation/module.php',
                        'module_class' => 'WeDevs\DokanPro\Modules\RequestForQuotation\Module',
                        'plan'         => [ 'business', 'enterprise' ],
                        'doc_id'       => 1527799,
                        'doc_link'     => 'https://wedevs.com/docs/dokan/modules/dokan-request-for-quotation-module/',
                        'mod_link'     => 'https://wedevs.com/dokan/modules/dokan-request-for-quotation-module/',
                        'categories'   => [ 'Product Management' ],
                    ],
                ]
            );
        }

        return $this->dokan_pro_modules;
    }

    /**
     * Set Dokan Pro modules
     *
     * @since 3.0.0
     *
     * @param array $modules
     *
     * @return void
     */
    public function set_modules( $modules ) {
        $this->dokan_pro_modules = $modules;
    }

    /**
     * Get a list of module ids
     *
     * @since 3.0.0
     *
     * @return array
     */
    public function get_all_module_ids() {
        static $module_ids = [];

        if ( ! $module_ids ) {
            $modules = $this->get_all_modules();
            $module_ids = array_keys( $modules );
        }

        return $module_ids;
    }

    /**
     * Get Dokan Pro active modules
     *
     * @since 3.0.0
     *
     * @return array
     */
    public function get_active_modules() {
        if ( $this->active_modules ) {
            return $this->active_modules;
        }

        $this->active_modules = get_option( self::ACTIVE_MODULES_DB_KEY, [] );

        if ( empty( $this->active_modules ) ) {
            return [];
        } if ( isset( $this->active_modules[0] ) && preg_match( '/php$/', $this->active_modules[0] ) ) {
            $old_convention_name_map = $this->get_compatibility_naming_map();
            $mapped_active_modules   = [];
            $test = [];

            foreach ( $this->active_modules as $module_file_name ) {
                if ( isset( $old_convention_name_map[ $module_file_name ] ) ) {
                    $mapped_active_modules[] = $old_convention_name_map[ $module_file_name ];
                }
            }

            sort( $mapped_active_modules );

            $this->update_db_option( $mapped_active_modules );

            $this->active_modules = $mapped_active_modules;
        }

        return $this->active_modules;
    }

    /**
     * Get a list of available modules
     *
     * @since 3.0.0
     *
     * @return array
     */
    public function get_available_modules() {
        $modules           = $this->get_all_modules();
        $available_modules = [];

        foreach ( $modules as $module_id => $module ) {
            if ( file_exists( $module['module_file'] ) ) {
                $available_modules[] = $module['id'];
            }
        }

        return $available_modules;
    }

    /**
     * Backward compatible module naming map
     *
     * @since 3.0.0
     *
     * @return array
     */
    public function get_compatibility_naming_map() {
        return [
            'appearance/appearance.php'                                         => 'color_scheme_customizer',
            'booking/booking.php'                                               => 'booking',
            'elementor/elementor.php'                                           => 'elementor',
            'export-import/export-import.php'                                   => 'export_import',
            'follow-store/follow-store.php'                                     => 'follow_store',
            'geolocation/geolocation.php'                                       => 'geolocation',
            'live-chat/live-chat.php'                                           => 'live_chat',
            'live-search/live-search.php'                                       => 'live_search',
            'moip/moip.php'                                                     => 'moip',
            'paypal-adaptive-payments/dokan-paypal-ap.php'                      => 'dokan_paypal_ap',
            'product-enquiry/enquiry.php'                                       => 'product_enquiry',
            'report-abuse/report-abuse.php'                                     => 'report_abuse',
            'rma/rma.php'                                                       => 'rma',
            'seller-vacation/seller-vacation.php'                               => 'seller_vacation',
            'shipstation/shipstation.php'                                       => 'shipstation',
            'simple-auction/auction.php'                                        => 'auction',
            'single-product-multiple-vendor/single-product-multiple-vendor.php' => 'spmv',
            'store-reviews/store-reviews.php'                                   => 'store_reviews',
            'store-support/store-support.php'                                   => 'store_support',
            'stripe/gateway-stripe.php'                                         => 'stripe',
            'subscription/product-subscription.php'                             => 'product_subscription',
            'vendor-analytics/vendor-analytics.php'                             => 'vendor_analytics',
            'vendor-staff/vendor-staff.php'                                     => 'vendor_staff',
            'vendor-verification/vendor-verification.php'                       => 'vendor_verification',
            'wholesale/wholesale.php'                                           => 'wholesale',
        ];
    }

    /**
     * Activate Dokan Pro modules
     *
     * @since 3.0.0
     *
     * @param array $modules
     *
     * @return array
     */
    public function activate_modules( $modules ) {
        $active_modules = $this->get_active_modules();

        $this->active_modules = array_unique( array_merge( $active_modules, $modules ) );

        $this->update_db_option( $this->active_modules );

        self::$modules_activated = false;

        $this->load_active_modules( $modules );

        return $this->active_modules;
    }

    /**
     * Deactivate Dokan Pro modules
     *
     * @since 3.0.0
     *
     * @param array $modules
     *
     * @return array
     */
    public function deactivate_modules( $modules ) {
        $active_modules = $this->get_active_modules();

        foreach ( $modules as $module_id ) {
            $active_modules = array_diff( $active_modules, [ $module_id ] );
        }

        $active_modules = array_values( $active_modules );

        $this->active_modules = $active_modules;

        $this->update_db_option( $this->active_modules );

        add_action(
            'shutdown', function () use ( $modules ) {
                foreach ( $modules as $module_id ) {
                    /**
                     * Module deactivation hook
                     *
                     * @since 3.0.0
                     *
                     * @param object $module deactivated module class instance
                     */
                    do_action( 'dokan_deactivated_module_' . $module_id, dokan_pro()->module->$module_id );
                }
            }
        );

        return $this->active_modules;
    }

    /**
     * Checks if a module is active or not
     *
     * @since 3.0.0
     *
     * @param string $module_id
     *
     * @return bool
     */
    public function is_active( $module_id ) {
        $active_modules = $this->get_active_modules();

        if ( in_array( $module_id, $active_modules, true ) ) {
            return true;
        }

        return false;
    }
}
