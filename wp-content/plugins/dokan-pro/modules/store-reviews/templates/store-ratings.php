<div class="store-ratings item">
    <label>
        <?php esc_html_e( 'Rating:', 'dokan' ); ?>
    </label>

    <p class="dokan-stars">
        <?php
            foreach ( range( 1, 5 ) as $count ) {
                printf( '<i class="star-%1$s dashicons dashicons-star-empty" data-rating="%2$s"></i>', $count, $count );
            }
        ?>

        <span class="up">& <?php esc_html_e( 'Up', 'dokan' ); ?></span>
    </p>
</div>