<?php
/*
Plugin Name: wpDataTables - Tables & Table Charts
Plugin URI: https://wpdatatables.com
Description: Create responsive, sortable tables & charts from Excel, CSV or PHP. Add tables & charts to any post in minutes with DataTables.
Version: 2.1.67
Author: TMS-Plugins
Author URI: https://tmsproducts.io
Text Domain: wpdatatables
Domain Path: /languages
*/

?>
<?php

defined('ABSPATH') or die("Cannot access pages directly.");

/******************************
 * Includes and configuration *
 ******************************/

define('WDT_ROOT_PATH', plugin_dir_path(__FILE__)); // full path to the wpDataTables root directory
define('WDT_ROOT_URL', plugin_dir_url(__FILE__)); // URL of wpDataTables plugin
if (!defined('WDT_BASENAME')) {
    define('WDT_BASENAME', plugin_basename(__FILE__));
}
// Config file
require_once(WDT_ROOT_PATH . 'config/config.inc.php');

// Plugin functions
require_once(WDT_ROOT_PATH . 'controllers/wdt_functions.php');

if (defined('PHP_VERSION_ID') && PHP_VERSION_ID >= 50600) {
    require_once WDT_ROOT_PATH . 'lib/autoload.php';
}

function wpdatatables_load()
{
    if (is_admin()) {
        // Admin panel controller
        require_once(WDT_ROOT_PATH . 'controllers/wdt_admin.php');
        // Admin panel AJAX actions
        require_once(WDT_ROOT_PATH . 'controllers/wdt_admin_ajax_actions.php');

    }
    require_once(WDT_ROOT_PATH . 'source/class.wdttools.php');
    require_once(WDT_ROOT_PATH . 'source/class.wdtconfigcontroller.php');
    require_once(WDT_ROOT_PATH . 'source/class.wdtsettingscontroller.php');
    require_once(WDT_ROOT_PATH . 'source/class.wdtexception.php');
    require_once(WDT_ROOT_PATH . 'source/class.sql.php');
    require_once(WDT_ROOT_PATH . 'source/class.wpdatatable.php');
    require_once(WDT_ROOT_PATH . 'source/class.wpdatacolumn.php');
    require_once(WDT_ROOT_PATH . 'source/class.wpdatatablerows.php');
    require_once(WDT_ROOT_PATH . 'source/class.wpdatatablecache.php');
    require_once(WDT_ROOT_PATH . 'source/class.wdtnestedjson.php');
    require_once(WDT_ROOT_PATH . 'source/class.wpdatachart.php');
    require_once(WDT_ROOT_PATH . 'source/class.wdtbrowsetable.php');
    require_once(WDT_ROOT_PATH . 'source/class.wdtbrowsechartstable.php');
    require_once(WDT_ROOT_PATH . 'integrations/page_builders/gutenberg/GutenbergBlock.php');
    require_once(WDT_ROOT_PATH . 'integrations/page_builders/gutenberg/WpDataTablesGutenbergBlock.php');
    require_once(WDT_ROOT_PATH . 'integrations/page_builders/gutenberg/WpDataChartsGutenbergBlock.php');
    require_once(WDT_ROOT_PATH . 'integrations/page_builders/elementor/class.wdtelementorblock.php');
    require_once(WDT_ROOT_PATH . 'integrations/page_builders/divi-wpdt/divi-wpdt.php');
    require_once(WDT_ROOT_PATH . 'integrations/page_builders/avada/class.wdtavadaelements.php');
    require_once(WDT_ROOT_PATH . 'integrations/page_builders/wpbakery/wdtBakeryBlock.php');

    add_action('plugins_loaded', 'wdtLoadTextdomain');

    if (is_admin()) {

        if (WDT_CURRENT_VERSION !== get_option('wdtVersion')) {
            if (!function_exists('is_plugin_active_for_network')) {
                include_once(ABSPATH . 'wp-admin/includes/plugin.php');
            }

            wdtActivation(is_plugin_active_for_network(__FILE__));
            update_option('wdtVersion', WDT_CURRENT_VERSION);
        }
    }

}


/********
 * Hooks *
 ********/
register_activation_hook(__FILE__, 'wdtActivation');
register_deactivation_hook(__FILE__, 'wdtDeactivation');
register_uninstall_hook(__FILE__, 'wdtUninstall');

add_shortcode('wpdatatable', 'wdtWpDataTableShortcodeHandler');
add_shortcode('wpdatachart', 'wdtWpDataChartShortcodeHandler');
add_shortcode('wpdatatable_cell', 'wdtWpDataTableCellShortcodeHandler');


wpdatatables_load();
?>
