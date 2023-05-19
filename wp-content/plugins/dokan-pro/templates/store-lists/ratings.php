<?php
/**
 * The template for displaying ratings area in store lists filter
 *
 * This template can be overridden by copying it to yourtheme/dokan/store-lists/ratings.php
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

<div class="ratings item">
    <label for="ratings">
        <?php esc_html_e( 'Open Now:', 'dokan' ); ?>
    </label>
    <input id="ratings" type="checkbox" name="ratings">
</div>