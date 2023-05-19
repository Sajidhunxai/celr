<?php do_action( 'dokan_product_edit_before_rank_math_seo', $product_id ); ?>

<style>
    .rank-math-tooltip input {
        display: none !important;
    }

    .rank-math-checklist li {
        padding-left: 24px !important;
    }

    #rank-math-metabox-wrapper .hidden {
        display: none;
    }

    #rank-math-metabox-wrapper .rank-math-tabs .components-panel__body .advanced-robots .components-checkbox-control input {
        width: inherit !important;
    }

    #rank-math-metabox-wrapper .components-notice.is-warning {
        display: none !important;
    }
</style>

<div class="dokan-edit-row dokan-clearfix dokan-border-top">
    <div class="dokan-section-heading">
        <h2><i class="fab fa-superpowers" aria-hidden="true"></i> <?php esc_html_e( 'Rank Math SEO', 'dokan' ); ?></h2>
        <p><?php esc_html_e( 'Manage SEO for this product', 'dokan' ); ?></p>
        <a href="#" class="dokan-section-toggle">
            <i class="fas fa-sort-down fa-flip-vertical" aria-hidden="true"></i>
        </a>
        <div class="dokan-clearfix"></div>
    </div>

    <div class="dokan-section-content">
        <input type="hidden" id="post_name" size="13" value="<?php echo esc_attr( $product->post_name ); ?>" />
        <input type="hidden" id="title" size="30" value="<?php echo esc_attr( $product->post_title ); ?>" />
        <div id="rank-math-metabox-wrapper">
            <!-- Rank math seo section will be injected here -->
        </div>
    </div>

    <?php do_action( 'dokan_product_edit_inside_after_rank_math_seo', $product_id ); ?>
</div>

<?php do_action( 'dokan_product_edit_after_rank_math_seo', $product_id ); ?>
