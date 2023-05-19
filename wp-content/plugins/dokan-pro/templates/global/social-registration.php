<div class="jssocials-shares">
<?php foreach ( $providers as $provider ) : ?>
    <div class="jssocials-share jssocials-share-<?php echo $provider ?>">
        <a href="<?php echo add_query_arg( array( 'vendor_social_reg' => $provider ), $base_url ); ?>" class="jssocials-share-link">
            <i class="fab fa-<?php echo $provider ?> jssocials-share-logo"></i>
        </a>
    </div>
<?php  endforeach; ?>
</div>
