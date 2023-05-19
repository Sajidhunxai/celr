<?php
/**
 * The template for displaying open now area in store lists filter
 *
 * This template can be overridden by copying it to yourtheme/dokan/store-lists/open-now.php
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see     https://docs.woocommerce.com/document/template-structure/
 * @package Dokan/Templates
 * @version 3.0.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="open-now item">
    <label for="open-now">
        <?php esc_html_e( 'Open Now', 'dokan' ); ?>:
    </label>
    <input type="checkbox" class="dokan-toogle-checkbox" id="open-now" name="open_now">
</div>