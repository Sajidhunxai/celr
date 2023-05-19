<div class="dokan-mangopay-container hidden" id="dokan-mangopay-wallets">
    <?php if ( $no_wallet ) : ?>
    <p class="dokan-mangopay-no-wallet">
        <?php esc_html_e( 'No MangoPay wallets found. Please check that all required fields have been completed.', 'dokan' ); ?>
    </p>
    <?php else : ?>
    <table class="wallet-list list-table">
        <thead>
            <tr>
                <td><?php esc_html_e( 'Wallet', 'dokan' ); ?></td>
                <td><?php esc_html_e( 'Balance', 'dokan' ); ?></td>
                <td><?php esc_html_e( 'Description', 'dokan' ); ?></td>
                <td><?php esc_html_e( 'Created', 'dokan' ); ?></td>
            </tr>
        </thead>

        <tbody>
            <?php foreach ( $wallets as $wallet ) : ?>
            <tr>
                <td>#<?php esc_html_e( $wallet->Id, 'dokan' ); ?></td>
                <td><?php echo esc_html( get_woocommerce_currency_symbol( $wallet->Currency ) ) . esc_html( wc_format_decimal( $wallet->Balance->Amount/100, 2 ) ); ?></td>
                <td><?php esc_html_e( $wallet->Description, 'dokan' ); ?></td>
                <td><?php esc_html_e( get_date_from_gmt( date( 'Y-m-d H:i:s', $wallet->CreationDate ), 'F j, Y' ), 'dokan' ); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>
</div>
