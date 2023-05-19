<?php
/**
 * The template for displaying category area in store lists filter
 *
 * This template can be overridden by copying it to yourtheme/dokan/store-lists/category-area.php
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

defined( 'ABSPATH' ) || exit; ?>

<div class="store-lists-other-filter-wrap">
    <?php do_action( 'dokan_before_store_lists_filter_category', $stores ); ?>

    <?php if ( ! empty( $categories ) ) : ?>
        <div class="store-lists-category item">
            <div class="category-input">
                <span class="category-label">
                    <?php esc_html_e( 'Category:', 'dokan' ); ?>
                </span>
                <span class="category-items">
                    <?php esc_html_e( 'All Categories', 'dokan' ) ?>
                </span>

                <span class="dokan-icon dashicons dashicons-arrow-down-alt2"></span>
            </div>

            <div class="category-box store_category" style="display: none">
                <ul>
                    <?php foreach ( $categories as $category ) : ?>
                        <li data-slug=<?php echo esc_attr( $category['slug'] ); ?>>
                            <?php esc_html_e( $category['name'] ); ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>

    <?php
        /**
         * Hooks: dokan_after_store_lists_filter_category
         *
         * @since 3.0.0
         *
         * @hooked \StoreListsFilter::featured_store() - 10
         */
        do_action( 'dokan_after_store_lists_filter_category', $stores );
    ?>
</div>