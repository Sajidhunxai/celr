<?php
/**
 * Search result filter template.
 * this will be displayed before search result table.
 *
 * @sience 3.5.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<div class="dokan-spmv-search-result-filter">
    <?php woocommerce_catalog_ordering(); ?>
</div>
