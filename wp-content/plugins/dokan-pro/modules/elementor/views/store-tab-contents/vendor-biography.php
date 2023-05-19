<?php
$store_user = get_userdata( get_query_var( 'author' ) );
$store_info = dokan_get_store_info( $store_user->ID );
?>
<div id="vendor-biography">
    <?php do_action( 'dokan_vendor_biography_tab_before', $store_user, $store_info ); ?>

    <h2 class="headline"><?php echo apply_filters( 'dokan_vendor_biography_title', __( 'Vendor Biography', 'dokan' ) ); ?></h2>

    <?php
        if ( ! empty( $store_info['vendor_biography'] ) ) {
            printf( '%s', apply_filters( 'the_content', $store_info['vendor_biography'] ) );
        }
    ?>

    <?php do_action( 'dokan_vendor_biography_tab_after', $store_user, $store_info ); ?>
</div>
