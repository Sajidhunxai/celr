<?php
$vendor = dokan()->vendor->get( get_query_var( 'author' ) );
?>
<div id="store-toc-wrapper">
    <div id="store-toc">
        <?php if( ! empty( $vendor->get_store_tnc() ) ): ?>
            <h2 class="headline"><?php esc_html_e( 'Terms And Conditions', 'dokan' ); ?></h2>
            <div>
                <?php echo wp_kses_post( wpautop( wptexturize( $vendor->get_store_tnc() ) ) ); ?>
            </div>
        <?php endif; ?>
    </div><!-- #store-toc -->
</div><!-- #store-toc-wrap -->
