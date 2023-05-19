<div id="dokan-mangopay-registered-cards">
<?php if ( ! empty( $registered_cards ) ) : ?>
    <input type="hidden" id="dokan-mp-card-saved" value="1">

    <?php foreach ( $registered_cards as $card ) : ?>
    <div class="saved-cards" id="dokan-mp-saved-card-line-<?php echo esc_attr( $card->Id ); ?>">
        <div class="card-type">
            <input type="radio" name="registered_card_selected" value="<?php echo esc_attr( $card->Id ); ?>" checked="checked">
            <label for="registered_card_selected"><?php echo esc_html( $card->Alias ); ?></label>
            <div class="dashicons dashicons-trash" id="dokan-mp-cancel-card" data-cardId="<?php echo esc_attr( $card->Id ); ?>"></div>
        </div>
    </div>
    <?php endforeach; ?>
<?php else : ?>
    <div class="dokan-mp-card-empty">
        <label>
            <?php esc_html_e( 'You have not registered a card yet. You can add one below.', 'dokan' ); ?>
        </label>
    </div>
<?php endif; ?>
</div>