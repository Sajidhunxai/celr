<?php
/**
 * Search page template.
 *
 * @sience 3.5.2
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}
?>

<?php do_action( 'dokan_dashboard_wrap_start' ); ?>

<div class="dokan-dashboard-wrap">

    <?php

    /**
     *  Dokan_dashboard_content_before hook.
     *
     *  @hooked get_dashboard_side_navigation
     *
     *  @since 2.4
     */
    do_action( 'dokan_dashboard_content_before' );
    do_action( 'dokan_spmv_products_search_before' );

    ?>

    <div class="dokan-dashboard-content dokan-orders-content">

        <?php

        /**
         *  Dokan SPMV products search content inside before.
         *
         *  @since 3.5.2
         */
        do_action( 'dokan_spmv_products_search_content_inside_before' );
        ?>

        <header class="dokan-spmv-products-search-box-area">
            <?php do_action( 'dokan_spmv_products_search_box' ); ?>
        </header>
        <article class="dokan-spmv-products-search-result-area">
            <?php

            /**
             *  Dokan SPMV products search content table before.
             *
             *  @since 3.5.2
             */
            do_action( 'dokan_spmv_products_search_content_table_before' );
            ?>
            <table class="dokan-table dokan-table-striped product-listing-table"
                id="dokan-spmv-product-list-table"
                data-security="<?php echo esc_attr( wp_create_nonce( 'dokan_spmv_product_clone_from_search' ) ); ?>"
            >
                <thead>
                    <tr>
                        <th><?php esc_html_e( 'Product Name', 'dokan' ); ?></th>
                        <th><?php esc_html_e( 'Price', 'dokan' ); ?></th>
                        <th><?php esc_html_e( 'Vendor', 'dokan' ); ?></th>
                        <th><?php esc_html_e( 'Actions', 'dokan' ); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php
                if ( ! empty( $search_results->products ) ) {
                    woocommerce_product_loop_start();
                    foreach ( $search_results->products as $product ) {
                        dokan_spmv_get_template(
                            'search/result-row',
                            [
                                'product'     => $product,
                                'search_word' => $search_word,
                            ]
                        );
                    }
                    woocommerce_product_loop_end();
                    wc_reset_loop();
                } else {
                    echo '<tr><td colspan="4">' . __( 'No product found.', 'dokan' ) . '</td></tr>';
                }
                ?>
                </tbody>

            </table>

            <?php
            /**
             *  Dokan SPMV products search content table after.
             *
             *  @since 3.5.2
             */
            do_action( 'dokan_spmv_products_search_content_table_after' );

            if ( $search_results->max_num_pages > 1 ) :
                ?>
                <div class="pagination-wrap">
                    <ul class="pagination">
                        <li>
                            <?php
                            $page_links = paginate_links(
                                [
                                    'current'   => $paged,
                                    'total'     => $search_results->max_num_pages,
                                    'base'      => dokan_get_navigation_url( 'products-search' ) . '%_%',
                                    'format'    => '?pagenum=%#%',
                                    'add_args'  => false,
                                    'type'      => 'array',
                                    'prev_text' => __( '&laquo; Previous', 'dokan' ),
                                    'next_text' => __( 'Next &raquo;', 'dokan' ),
                                ]
                            );
                            echo join( "</li>\n\t<li>", $page_links );
                            ?>
                        </li>
                    </ul>
                </div>
            <?php endif; ?>
        </article>

        <?php

        /**
         *  Dokan SPMV product search content inside after.
         *
         *  @since 3.5.2
         */
        do_action( 'dokan_spmv_products_search_content_inside_after' );
        do_action( 'dokan_after_listing_product' );
        ?>

    </div> <!-- #primary .content-area -->

    <?php

    /**
     *  Dokan_dashboard_content_after hook.
     *
     *  @since 2.4
     */
    do_action( 'dokan_dashboard_content_after' );
    do_action( 'dokan_spmv_products_search_after' );

    ?>

</div><!-- .dokan-dashboard-wrap -->

<?php do_action( 'dokan_dashboard_wrap_end' ); ?>
