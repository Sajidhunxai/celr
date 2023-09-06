<?php

if ( ! defined('WPINC') ) {
    wp_die();
}

use FilterEverything\Filter\Pro\Admin\Admin;
use FilterEverything\Filter\Container;

if( ! class_exists('FiltersPro') ):

class FiltersPro{

    function __construct()
    {
        $wpcFilter = flrt_filter();
        $wpcFilter->define( 'FLRT_SEO_RULES_POST_TYPE', 'filter-seo-rule' );
        $wpcFilter->define( 'FLRT_FILTERS_PRO', true );
        $wpcFilter->define( 'FLRT_ENVATO_APP_CLIENT_ID', 'filter-everything-py1gbdtg' );
        $wpcFilter->define( 'FLRT_VERSION_TRANSIENT', 'wpc_plugin_version' );
        $wpcFilter->define( 'FLRT_LICENSE_SOURCE', 'https://codecanyon.net/item/filter-everything-wordpress-woocommerce-filter/31634508' );

        flrt_include('pro/Entities/PostMetaExistsEntity.php');
        flrt_include('pro/Entities/TaxonomyNumEntity.php');
        flrt_include('pro/wpc-default-hooks-pro.php');
        flrt_include('pro/wpc-utility-functions.php');
        flrt_include('pro/PluginPro.php');
        flrt_include('pro/PostTypes.php');

        flrt_include('pro/SeoFrontend.php');
        flrt_include('pro/Api/ApiRequests.php');
        flrt_include('pro/Settings/Tabs/SeoRulesTab.php');
        flrt_include('pro/Settings/Tabs/IndexingDepth.php');
        flrt_include('pro/Settings/Tabs/LicenseTab.php');
        flrt_include('pro/Admin/SeoRules.php');

        flrt_include('pro/Admin/Admin.php');
        flrt_include('pro/Admin/MetaBoxes.php');
        flrt_include('pro/Admin/ShortcodesPro.php');

        if( is_admin() ){
            new Admin();
        }

        add_action( 'init', [ $this, 'init'] );
        add_action( 'wp', [ $this, 'wpInit'], -1 );
    }

    public function wpInit()
    {
        $seoFrontend = Container::instance()->getSeoFrontendService();
        $seoFrontend->processPageSeo();
    }

    public function init()
    {
        if ( is_multisite() ) {
            if( is_main_site() ) {
                flrt_init_common();
            } else {
                flrt_init_common_multisite();
            }
        } else {
            flrt_init_common();
        }
    }
}

new FiltersPro();

endif;